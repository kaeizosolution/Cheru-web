<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Products extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Product_model');
        $this->load->library('session');
    }

    private function _get_bearer_token()
    {
        $header = $this->input->get_request_header('Authorization', true);
        if (!$header) {
            $header = $this->input->server('HTTP_AUTHORIZATION');
        }
        $header = trim((string)$header);
        if ($header === '') {
            return '';
        }
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return '';
    }

    private function _require_vendor()
    {
        $token = $this->_get_bearer_token();
        if ($token === '') {
            $this->fail('unauthorized', ['Missing Authorization Bearer token'], 401);
            return 0;
        }

        $claims = $this->verify_token($token);
        if (!$claims || !isset($claims['sub']) || ($claims['type'] ?? '') !== 'access' || ($claims['role'] ?? '') !== 'vendor') {
            $this->fail('unauthorized', ['Invalid or expired vendor access token'], 401);
            return 0;
        }

        $vendor_id = (int)$claims['sub'];
        if ($vendor_id <= 0) {
            $this->fail('unauthorized', ['Invalid token subject'], 401);
            return 0;
        }

        // Merge session so browser session elements are not wiped
        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id'] = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? TRUE;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');

        return $vendor_id;
    }

    // ─── Currency Conversion Helpers ─────────────────────────────────────────

    /**
     * Get the vendor's preferred currency details and the base currency rate.
     *
     * Returns:
     *   vendor_currency_id  – currency the vendor uses for input
     *   vendor_rate         – exchange rate (e.g. 83.5 for INR when 1 USD = 83.5 INR)
     *   base_currency_id    – base storage currency (USD)
     *   base_rate           – base currency rate (usually 1.0)
     *   needs_conversion    – false when vendor uses base currency (no-op)
     */
    private function _get_vendor_currency_info($vendor_id)
    {
        // Load base currency
        $base = $this->db
            ->select('currency_id, rate')
            ->from('ec_currency')
            ->where('basic', 1)
            ->where('status', '1')
            ->limit(1)
            ->get()
            ->row();

        $base_id   = $base ? (int)$base->currency_id   : 1;
        $base_rate = $base ? (float)$base->rate          : 1.0;
        if ($base_rate <= 0) $base_rate = 1.0;

        // Read vendor preferred currency from DB column
        $preferred_id = 0;
        if ($this->db->field_exists('preferred_currency_id', 'ec_vendor')) {
            $vrow = $this->db
                ->select('preferred_currency_id')
                ->from('ec_vendor')
                ->where('vendor_id', $vendor_id)
                ->limit(1)
                ->get()
                ->row();
            $preferred_id = (int)($vrow->preferred_currency_id ?? 0);
        }

        // Fallback: session currency
        if ($preferred_id <= 0) {
            $preferred_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
        }

        // Same as base or not set — no conversion needed
        if ($preferred_id <= 0 || $preferred_id === $base_id) {
            return [
                'vendor_currency_id' => $base_id,
                'vendor_rate'        => $base_rate,
                'base_currency_id'   => $base_id,
                'base_rate'          => $base_rate,
                'needs_conversion'   => false,
            ];
        }

        // Fetch vendor currency rate
        $vcurr = $this->db
            ->select('currency_id, rate')
            ->from('ec_currency')
            ->where('currency_id', $preferred_id)
            ->where('status', '1')
            ->limit(1)
            ->get()
            ->row();

        if (!$vcurr || (float)$vcurr->rate <= 0) {
            return [
                'vendor_currency_id' => $base_id,
                'vendor_rate'        => $base_rate,
                'base_currency_id'   => $base_id,
                'base_rate'          => $base_rate,
                'needs_conversion'   => false,
            ];
        }

        return [
            'vendor_currency_id' => (int)$vcurr->currency_id,
            'vendor_rate'        => (float)$vcurr->rate,
            'base_currency_id'   => $base_id,
            'base_rate'          => $base_rate,
            'needs_conversion'   => true,
        ];
    }

    /**
     * Convert all price fields in $post from vendor currency -> base currency (USD).
     * Modifies array in-place.
     *
     * Formula: base_price = vendor_price / vendor_rate * base_rate
     * (When base_rate = 1: base_price = vendor_price / vendor_rate)
     */
    private function _convert_prices_to_base(array &$post, array $cur_info)
    {
        // Always store prices tagged with base currency_id (USD = 1)
        $post['currency_id'] = $cur_info['base_currency_id'];

        if (!$cur_info['needs_conversion']) {
            return;
        }

        $vrate = $cur_info['vendor_rate'];
        $brate = $cur_info['base_rate'];

        // Simple product price tiers
        if (!empty($post['simple_price']) && is_array($post['simple_price'])) {
            foreach ($post['simple_price'] as &$p) {
                if ($p !== '' && $p !== null) {
                    $p = round((float)$p / $vrate * $brate, 4);
                }
            }
            unset($p);
        }

        // Variable product price tiers
        if (!empty($post['vars']) && is_array($post['vars'])) {
            foreach ($post['vars'] as &$var) {
                if (!empty($var['price']) && is_array($var['price'])) {
                    foreach ($var['price'] as &$p) {
                        if ($p !== '' && $p !== null) {
                            $p = round((float)$p / $vrate * $brate, 4);
                        }
                    }
                    unset($p);
                }
            }
            unset($var);
        }
    }

    /**
     * Convert a single price from base currency -> vendor's preferred currency.
     *
     * Formula: vendor_price = base_price / base_rate * vendor_rate
     */
    private function _convert_price_from_base($base_price, array $cur_info)
    {
        if (!$cur_info['needs_conversion'] || $cur_info['base_rate'] <= 0) {
            return round((float)$base_price, 2);
        }
        return round((float)$base_price / $cur_info['base_rate'] * $cur_info['vendor_rate'], 2);
    }

    // ─── Slug Helper ──────────────────────────────────────────────────────────

    private function _get_slug($string)
    {
        $string = trim((string)$string);
        $string = strtolower($string);
        $string = str_replace(' ', '-', $string);
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '', $string);
        $slug = preg_replace("/[\-]+/", '-', $slug);
        return $slug;
    }

    public function index()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method === 'GET' || $method === 'POST') {
            return $this->_list();
        }
        return $this->fail('method_not_allowed', ['Only GET and POST are allowed'], 405);
    }

    private function _list()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        // Support both POST body (clean URL) and GET query string
        $post = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

        $page  = (int)($post['page']  ?? $this->input->get('page'));
        $limit = (int)($post['limit'] ?? $this->input->get('limit'));
        if ($page  <= 0)           { $page  = 1; }
        if ($limit <= 0 || $limit > 100) { $limit = 20; }
        $offset = ($page - 1) * $limit;

        $name       = $post['name']       ?? $this->input->get('name');
        $date_added = $post['date_added'] ?? $this->input->get('date_added');
        $status     = $post['status']     ?? $this->input->get('status');

        $this->db->from('products')->where('vendor_id', $vendor_id);
        if ($name !== null && $name !== '') {
            $this->db->like('name', $name);
        }
        if ($date_added !== null && $date_added !== '') {
            $this->db->like('date_added', $date_added);
        }
        if ($status !== null && $status !== '') {
            $this->db->where('status', $status);
        }

        $total = (int)$this->db->count_all_results('', FALSE);

        $rows = $this->db
            ->order_by('id', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();

        // Get vendor currency info once for all products in list
        $vcur_info = $this->_get_vendor_currency_info($vendor_id);
        $vcurr_display = $this->_get_currency_info($vcur_info['vendor_currency_id']);

        $products = [];
        foreach ($rows as $p) {
            $images = $this->_fetch_product_images((int)($p->id ?? 0));
            // Convert primary price from base to vendor's currency
            $base_price    = (float)($p->price ?? 0);
            $display_price = $this->_convert_price_from_base($base_price, $vcur_info);
            $products[] = [
                'id'             => (int)($p->id ?? 0),
                'name'           => $p->name ?? null,
                'sku'            => $p->sku ?? null,
                'product_type'   => $p->product_type ?? 'simple',
                'price'          => $display_price,       // in vendor's preferred currency
                'price_base'     => $base_price,          // raw USD price for reference
                'stock'          => (int)($p->stock ?? 0),
                'category_id'    => isset($p->category_id) ? (int)$p->category_id : (isset($p->cat_id) ? (int)$p->cat_id : null),
                'description'    => $p->description ?? (isset($p->post_content) ? $p->post_content : null),
                'status'         => (string)($p->status ?? ''),
                'date_added'     => $p->date_added ?? null,
                'images'         => $images,
                'image'          => isset($images[0]) ? $images[0] : null,
                'currency_id'    => $vcurr_display['currency_id'],
                'currency_code'  => $vcurr_display['currency_code'],
                'currency_symbol'=> $vcurr_display['currency_symbol'],
            ];
        }

        return $this->ok([
            'products' => $products,
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => $limit ? (int)ceil($total / $limit) : 1,
            ]
        ], 'success');
    }

    /**
     * POST api/v1/vendor/products/add
     * Creates a new product. Accepts multipart/form-data (for images/video)
     * OR application/json.
     *
     * Required fields:  product_name (or post_title), product_type
     * Optional fields:  cat, sub_cat, sub_sub_cat, brand_id, description,
     *                   simple_min_qty[], simple_max_qty[], simple_price[],
     *                   variation_images[0][], product_video
     */
    public function create()
    {
        try {
            if (strtoupper((string)$this->input->method()) !== 'POST') {
                return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
            }

            $vendor_id = $this->_require_vendor();
            if (!$vendor_id) {
                return;
            }

            // For multipart/form-data requests (files), php://input is empty.
            // Read directly from superglobals so CI's raw_input_stream does not block us.
            $post = [];
            if (!empty($_POST)) {
                $post = $_POST;
            } else {
                // Fallback for JSON body (non-file requests)
                $json = $this->get_json_input();
                if (!empty($json)) {
                    $post = $json;
                }
            }

            $this->_debug_log('POST', 'create_start', $post, $_FILES);

            // Normalise common field aliases and price tiers
            $this->_normalize_payload($post);

            $val_errors = $this->_validate_product($post);
            if (!empty($val_errors)) {
                $this->_debug_log('POST', 'create_validation_error', $val_errors, []);
                return $this->fail('validation_error', $val_errors, 422);
            }

            if (empty($post['product_type'])) {
                $post['product_type'] = 'simple';
            }

            // ── Currency conversion: vendor input → base currency (USD) ──────
            // Vendor enters prices in their preferred currency (e.g. INR 500).
            // We convert to base currency before saving so DB always stores USD.
            $vcur_info = $this->_get_vendor_currency_info($vendor_id);
            $this->_convert_prices_to_base($post, $vcur_info);
            // ────────────────────────────────────────────────────────────────

            // Populate $_POST so Items_model->save_product() can read via $this->input->post()
            foreach ($post as $k => $v) {
                $_POST[$k] = $v;
            }
            $_POST['product_id'] = 0; // Force insert (not update)

            $this->load->model('Items_model');
            $this->Items_model->vendor_id = $vendor_id;

            $resp = $this->Items_model->save_product();
            if ($resp['status']) {
                $product_id = (int)$resp['product_id'];
                $saved = $this->db->from('products')->where('id', $product_id)->get()->row();
                // Return price tiers in vendor's currency (convert back from base)
                $price_tiers = $this->_fetch_price_tiers($product_id, $post, $vcur_info);
                $images = $this->_fetch_product_images($product_id);
                $vcurr_display = $this->_get_currency_info($vcur_info['vendor_currency_id']);

                $response_payload = [
                    'product_id'      => $product_id,
                    'product_name'    => is_object($saved) ? $saved->name : ($post['product_name'] ?? null),
                    'product_type'    => is_object($saved) ? $saved->product_type : ($post['product_type'] ?? 'simple'),
                    'sku'             => is_object($saved) ? ($saved->sku ?? null) : ($post['sku'] ?? null),
                    'stock'           => is_object($saved) ? (int)($saved->stock ?? 0) : (int)($post['stock'] ?? 0),
                    'category'        => is_object($saved) ? $saved->category : ($post['cat'] ?? null),
                    'description'     => is_object($saved) ? $saved->description : ($post['description'] ?? null),
                    'status'          => is_object($saved) ? $saved->status : null,
                    'price_tiers'     => $price_tiers,
                    'variations'      => (is_object($saved) && $saved->product_type === 'variable') ? $this->_fetch_product_variations($product_id, $vcur_info) : [],
                    'images'          => $images,
                    'image'           => isset($images[0]) ? $images[0] : null,
                    'currency_id'     => $vcurr_display['currency_id'],
                    'currency_code'   => $vcurr_display['currency_code'],
                    'currency_symbol' => $vcurr_display['currency_symbol'],
                ];
                $this->_debug_log('POST', 'create_success', $response_payload, []);
                return $this->ok($response_payload, 'Product added successfully.');
            }
            $this->_debug_log('POST', 'create_failed_resp', $resp, []);
            return $this->fail('server_error', [$resp['msg'] ?? 'Failed to add product'], 500);

        } catch (\Throwable $t) {
            $this->_debug_log('POST', 'create_error', ['message' => $t->getMessage(), 'trace' => $t->getTraceAsString()], $_FILES);
            return $this->fail('server_error', [$t->getMessage()], 500);
        }
    }


    public function update($id = null)
    {
        try {
            $method = strtoupper((string)$this->input->method());
            if ($method !== 'POST') {
                return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
            }

            $vendor_id = $this->_require_vendor();
            if (!$vendor_id) {
                return;
            }

            // For multipart/form-data (file uploads), php://input is empty.
            // Read $_POST directly; fall back to JSON body for non-file requests.
            $payload = [];
            if (!empty($_POST)) {
                $payload = $_POST;
            } else {
                $json = $this->get_json_input();
                if (!empty($json)) {
                    $payload = $json;
                }
            }

            $this->_debug_log('POST', 'update_start', $payload, $_FILES);

            $product_id = (int)($id ?: ($payload['product_id'] ?? 0));
            if ($product_id <= 0) {
                return $this->fail('validation_error', ['Invalid product id'], 422);
            }

            $product = $this->db->from('products')->where('id', $product_id)->get()->row();
            if (!$product) {
                return $this->fail('not_found', ['Product not found'], 404);
            }
            if ((int)($product->vendor_id ?? 0) !== $vendor_id) {
                return $this->fail('unauthorized', ['You are not allowed to update this product'], 403);
            }

            // Check if this is a full form submit (it has product_name or post_title)
            $is_full_form = (isset($payload['product_name']) || isset($payload['post_title']));

            if ($is_full_form) {
                $this->load->model('Items_model');
                $this->Items_model->vendor_id = $vendor_id;

                // Populate $_POST from payload
                foreach ($payload as $k => $v) {
                    $_POST[$k] = $v;
                }
                $_POST['product_id'] = $product_id;

                // Normalize fields expected by Items_model
                $this->_normalize_payload($_POST);

                $val_errors = $this->_validate_product($_POST);
                if (!empty($val_errors)) {
                    $this->_debug_log('POST', 'update_validation_error', $val_errors, []);
                    return $this->fail('validation_error', $val_errors, 422);
                }

                // ── Currency conversion: vendor input → base currency (USD) ──
                $vcur_info = $this->_get_vendor_currency_info($vendor_id);
                $this->_convert_prices_to_base($_POST, $vcur_info);
                // ─────────────────────────────────────────────────────────────

                $resp = $this->Items_model->save_product();
                if ($resp['status']) {
                    $saved = $this->db->from('products')->where('id', $product_id)->get()->row();
                    // Return prices in vendor's currency
                    $price_tiers = $this->_fetch_price_tiers($product_id, $payload, $vcur_info);
                    $images = $this->_fetch_product_images($product_id);
                    $vcurr_display = $this->_get_currency_info($vcur_info['vendor_currency_id']);
                    $response_payload = [
                        'product_id'      => $product_id,
                        'product_name'    => is_object($saved) ? $saved->name : ($payload['product_name'] ?? null),
                        'product_type'    => is_object($saved) ? $saved->product_type : ($payload['product_type'] ?? 'simple'),
                        'sku'             => is_object($saved) ? ($saved->sku ?? null) : ($payload['sku'] ?? null),
                        'stock'           => is_object($saved) ? (int)($saved->stock ?? 0) : (int)($payload['stock'] ?? 0),
                        'category'        => is_object($saved) ? $saved->category : ($payload['cat'] ?? null),
                        'description'     => is_object($saved) ? $saved->description : ($payload['description'] ?? null),
                        'status'          => is_object($saved) ? $saved->status : null,
                        'price_tiers'     => $price_tiers,
                        'variations'      => (is_object($saved) && $saved->product_type === 'variable') ? $this->_fetch_product_variations($product_id, $vcur_info) : [],
                        'images'          => $images,
                        'image'           => isset($images[0]) ? $images[0] : null,
                        'currency_id'     => $vcurr_display['currency_id'],
                        'currency_code'   => $vcurr_display['currency_code'],
                        'currency_symbol' => $vcurr_display['currency_symbol'],
                    ];
                    $this->_debug_log('POST', 'update_success', $response_payload, []);
                    return $this->ok($response_payload, 'Product updated successfully.');
                } else {
                    $this->_debug_log('POST', 'update_failed_resp', $resp, []);
                    return $this->fail('server_error', [$resp['msg'] ?? 'Failed to update product'], 500);
                }
            } else {
                // Simple update (status, stock, sku, etc.)
                $vcur_info = $this->_get_vendor_currency_info($vendor_id);
                $allowed = ['name', 'price', 'stock', 'sku', 'category_id', 'description', 'status', 'currency_id'];
                $update = [];
                foreach ($allowed as $key) {
                    if (!array_key_exists($key, $payload)) {
                        continue;
                    }
                    if ($key === 'category_id') {
                        $cid = (int)$payload[$key];
                        $update['category_id'] = $cid;
                        $update['cat_id'] = $cid;
                        $update['category'] = $cid;
                    } elseif ($key === 'description') {
                        $desc = trim((string)$payload[$key]);
                        $update['description'] = $desc;
                        $update['post_content'] = $desc;
                    } elseif ($key === 'price') {
                        // Convert single price from vendor currency to base
                        $raw_price = (float)$payload[$key];
                        $update['price'] = $vcur_info['needs_conversion']
                            ? round($raw_price / $vcur_info['vendor_rate'] * $vcur_info['base_rate'], 4)
                            : $raw_price;
                        $update['currency_id'] = $vcur_info['base_currency_id'];
                    } elseif ($key === 'stock') {
                        $update['stock'] = (int)$payload[$key];
                    } elseif ($key === 'sku') {
                        $sku_val = trim((string)$payload[$key]);
                        if ($sku_val !== '') {
                            $update['sku'] = $sku_val;
                        }
                    } elseif ($key === 'status') {
                        $update['status'] = (string)$payload[$key];
                    } elseif ($key === 'name') {
                        $nm = trim((string)$payload[$key]);
                        $update['name'] = $nm;
                        $update['slug'] = $this->_get_slug($nm);
                    } elseif ($key === 'currency_id') {
                        $update['currency_id'] = (int)$payload[$key];
                    }
                }

                if (empty($update)) {
                    return $this->fail('validation_error', ['No updatable fields provided'], 422);
                }

                $this->Query_model->update_data('products', $update, ['id' => $product_id]);
                $saved = $this->db->from('products')->where('id', $product_id)->get()->row();
                // Return prices in vendor's preferred currency
                $price_tiers = $this->_fetch_price_tiers($product_id, $payload, $vcur_info);
                $images = $this->_fetch_product_images($product_id);
                $vcurr_display = $this->_get_currency_info($vcur_info['vendor_currency_id']);
                $response_payload = [
                    'product_id'      => $product_id,
                    'product_name'    => is_object($saved) ? $saved->name : ($product->name ?? null),
                    'product_type'    => is_object($saved) ? $saved->product_type : ($product->product_type ?? 'simple'),
                    'sku'             => is_object($saved) ? ($saved->sku ?? null) : ($product->sku ?? null),
                    'stock'           => is_object($saved) ? (int)($saved->stock ?? 0) : (int)($product->stock ?? 0),
                    'category'        => is_object($saved) ? $saved->category : ($product->category ?? null),
                    'description'     => is_object($saved) ? $saved->description : ($product->description ?? null),
                    'status'          => is_object($saved) ? $saved->status : ($product->status ?? null),
                    'price_tiers'     => $price_tiers,
                    'variations'      => (is_object($saved) && $saved->product_type === 'variable') ? $this->_fetch_product_variations($product_id, $vcur_info) : [],
                    'images'          => $images,
                    'image'           => isset($images[0]) ? $images[0] : null,
                    'currency_id'     => $vcurr_display['currency_id'],
                    'currency_code'   => $vcurr_display['currency_code'],
                    'currency_symbol' => $vcurr_display['currency_symbol'],
                ];
                $this->_debug_log('POST', 'update_simple_success', $response_payload, []);
                return $this->ok($response_payload, 'Product updated successfully.');
            }
        } catch (\Throwable $t) {
            $this->_debug_log('POST', 'update_error', ['message' => $t->getMessage(), 'trace' => $t->getTraceAsString()], $_FILES);
            return $this->fail('server_error', [$t->getMessage()], 500);
        }
    }

    /**
     * GET api/v1/vendor/products/get_details/{id}
     * GET api/v1/vendor/products/get_details?product_id={id}
     *
     * Returns full product details suitable for pre-filling an edit form.
     * All prices are converted back to the vendor's preferred currency.
     *
     * Response shape mirrors the payload accepted by update():
     *   product_id, product_name, product_type, sku, stock, status,
     *   description, category_id, sub_category_id, sub_sub_category_id,
     *   brand_id, price_tiers (simple), variations[] (variable),
     *   images[], image, currency_*
     */
    public function get_details($id = null)
    {
        try {
            $method = strtoupper((string)$this->input->method());
            if ($method !== 'POST') {
                return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
            }

            $vendor_id = $this->_require_vendor();
            if (!$vendor_id) {
                return;
            }

            // Accept product_id from URL segment or query/body param
            $params = array_merge(
                $this->get_json_input(),
                $this->input->post(NULL, true) ?: [],
                $this->input->get(NULL, true) ?: []
            );
            $product_id = (int)($id ?: ($params['product_id'] ?? 0));
            if ($product_id <= 0) {
                return $this->fail('validation_error', ['product_id is required'], 422);
            }

            // Fetch base product row
            $product = $this->db->from('products')->where('id', $product_id)->get()->row();
            if (!$product) {
                return $this->fail('not_found', ['Product not found'], 404);
            }
            // Ensure this product belongs to the authenticated vendor
            if ((int)($product->vendor_id ?? 0) !== $vendor_id) {
                return $this->fail('unauthorized', ['You are not allowed to view this product'], 403);
            }

            // Vendor currency for price display
            $vcur_info     = $this->_get_vendor_currency_info($vendor_id);
            $vcurr_display = $this->_get_currency_info($vcur_info['vendor_currency_id']);

            // ── Category hierarchy ──────────────────────────────────────────
            $cat_id         = isset($product->category)         ? (int)$product->category         : null;
            $sub_cat_id     = isset($product->sub_category)     ? (int)$product->sub_category     : null;
            $sub_sub_cat_id = isset($product->sub_sub_category) ? (int)$product->sub_sub_category : null;

            // ── Brand ───────────────────────────────────────────────────────
            $brand_id   = null;
            $brand_name = null;
            if ($this->db->field_exists('brand_id', 'products') && isset($product->brand_id) && $product->brand_id) {
                $brand_id = (int)$product->brand_id;
                $brand_row = $this->db->select('name')->from('ec_brand')->where('brand_id', $brand_id)->limit(1)->get()->row();
                if ($brand_row) {
                    $brand_name = $brand_row->name;
                }
            }

            // ── Images ──────────────────────────────────────────────────────
            $images = $this->_fetch_product_images($product_id);

            // ── Variations and price tiers ───────────────────────────────────
            $product_type = (string)($product->product_type ?? 'simple');

            $this->load->model('Items_model');
            $raw_variations = $this->Items_model->get_variations($product_id);

            $variations   = [];
            $price_tiers  = [];  // For simple products

            foreach ($raw_variations as $var) {
                // Build price tiers for this variation (converted to vendor currency)
                $var_price_tiers = [];
                if (!empty($var->prices)) {
                    foreach ($var->prices as $p) {
                        $base_price    = (float)$p->price;
                        $display_price = $this->_convert_price_from_base($base_price, $vcur_info);
                        $var_price_tiers[] = [
                            'price_id'   => (int)($p->id ?? 0),
                            'min_qty'    => (int)$p->min_qty,
                            'max_qty'    => ($p->max_qty !== null && $p->max_qty !== '') ? (int)$p->max_qty : null,
                            'price'      => $display_price,
                            'price_base' => $base_price,
                        ];
                    }
                }

                // Build image details for this variation (including id, image_path, and full url)
                $var_images = [];
                if (!empty($var->images)) {
                    foreach ($var->images as $img) {
                        if (!empty($img->image_path)) {
                            $var_images[] = [
                                'id'         => (int)$img->id,
                                'image_path' => $img->image_path,
                                'url'        => $this->_resolve_image_url($img->image_path)
                            ];
                        }
                    }
                }

                if ($product_type === 'simple') {
                    // Simple product — only one variation row, no attributes
                    $price_tiers = $var_price_tiers;
                    $images = $var_images;
                } else {
                    // Variable product — include attr data
                    $variations[] = [
                        'variation_id' => (int)$var->id,
                        'attr'         => is_array($var->attr) ? $var->attr : [],
                        'price_tiers'  => $var_price_tiers,
                        'images'       => $var_images,
                        'image'        => isset($var_images[0]) ? $var_images[0]['url'] : null,
                        'stock'        => (int)($var->stock ?? 0)
                    ];
                }
            }

            // ── Build response ───────────────────────────────────────────────
            $response = [
                'product_id'         => $product_id,
                'product_name'       => $product->name ?? null,
                'product_type'       => $product_type,
                'sku'                => $product->sku ?? null,
                'stock'              => isset($product->stock) ? (int)$product->stock : 0,
                'status'             => (string)($product->status ?? ''),
                'description'        => $product->description ?? ($product->post_content ?? null),
                'category_id'        => $cat_id,
                'sub_category_id'    => $sub_cat_id,
                'sub_sub_category_id'=> $sub_sub_cat_id,
                'brand_id'           => $brand_id,
                'brand_name'         => $brand_name,
                'price_tiers'        => $price_tiers,   // filled for simple; empty for variable
                'variations'         => $variations,     // filled for variable; empty for simple
                'images'             => $images,
                'image'              => (isset($images[0]) && is_array($images[0])) ? $images[0]['url'] : (isset($images[0]) ? $images[0] : null),
                'currency_id'        => $vcurr_display['currency_id'],
                'currency_code'      => $vcurr_display['currency_code'],
                'currency_symbol'    => $vcurr_display['currency_symbol'],
                'date_added'         => $product->date_added ?? null,
            ];

            return $this->ok($response, 'Product details fetched successfully.');

        } catch (\Throwable $t) {
            return $this->fail('server_error', [$t->getMessage()], 500);
        }
    }

    public function delete($id = null)
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST') {
            return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $product_id = (int)$id;
        if ($product_id <= 0) {
            return $this->fail('validation_error', ['Invalid product id'], 422);
        }

        $product = $this->db->from('products')->where('id', $product_id)->get()->row();
        if (!$product) {
            return $this->fail('not_found', ['Product not found'], 404);
        }
        if ((int)($product->vendor_id ?? 0) !== $vendor_id) {
            return $this->fail('unauthorized', ['You are not allowed to delete this product'], 403);
        }

        // Soft delete: mark inactive.
        $this->Query_model->update_data('products', ['status' => '0'], ['id' => $product_id]);
        return $this->ok(['product_id' => $product_id], 'success');
    }

    private function _validate_product($post)
    {
        $errors = [];
        
        if (isset($post['currency_id']) && $post['currency_id'] !== '') {
            $currency_id = (int)$post['currency_id'];
            $exists = $this->db->where('currency_id', $currency_id)->where('status', '1')->count_all_results('ec_currency');
            if ($exists === 0) {
                $errors[] = 'currency_id is invalid or inactive';
            }
        }

        if (empty($post['product_name'])) {
            $errors[] = 'product_name is required';
        }
        if (empty($post['description'])) {
            $errors[] = 'description is required';
        }
        if (empty($post['cat'])) {
            $errors[] = 'cat (category) is required';
        }

        $type = $post['product_type'] ?? 'simple';
        if (!in_array($type, ['simple', 'variable'])) {
            $errors[] = 'product_type must be simple or variable';
        }

        if ($type === 'simple') {
            $prices = $post['simple_price'] ?? [];
            $min_qtys = $post['simple_min_qty'] ?? [];

            if (!is_array($prices)) { $prices = [$prices]; }
            if (!is_array($min_qtys)) { $min_qtys = [$min_qtys]; }

            $valid_prices = array_filter($prices, function($p) { return $p !== null && $p !== ''; });
            $valid_min_qtys = array_filter($min_qtys, function($q) { return $q !== null && $q !== ''; });

            if (empty($valid_prices)) {
                $errors[] = 'price is required';
            }
            if (empty($valid_min_qtys)) {
                $errors[] = 'quantity is required';
            }
        } else {
            $vars = $post['vars'] ?? [];
            if (empty($vars) || !is_array($vars)) {
                $errors[] = 'At least one variation is required';
            } else {
                foreach ($vars as $idx => $v) {
                    $prices = $v['price'] ?? [];
                    $min_qtys = $v['min_qty'] ?? [];

                    if (!is_array($prices)) { $prices = [$prices]; }
                    if (!is_array($min_qtys)) { $min_qtys = [$min_qtys]; }

                    $valid_prices = array_filter($prices, function($p) { return $p !== null && $p !== ''; });
                    $valid_min_qtys = array_filter($min_qtys, function($q) { return $q !== null && $q !== ''; });

                    if (empty($valid_prices)) {
                        $errors[] = 'price is required for variation #' . ($idx + 1);
                    }
                    if (empty($valid_min_qtys)) {
                        $errors[] = 'quantity is required for variation #' . ($idx + 1);
                    }
                }
            }
        }

        return $errors;
    }

    private function _normalize_payload(&$post)
    {
        // 1. Normalize common field aliases
        if (!isset($post['currency_id']) || $post['currency_id'] === '') {
            $base_curr = $this->db->select('currency_id')->from('ec_currency')->where('basic', 1)->limit(1)->get()->row();
            $post['currency_id'] = $base_curr ? (int)$base_curr->currency_id : 1;
        }
        if (isset($post['post_title'])   && !isset($post['product_name'])) { $post['product_name'] = $post['post_title']; }
        if (isset($post['type'])         && !isset($post['product_type'])) { $post['product_type'] = $post['type']; }
        if (isset($post['cat_id'])       && !isset($post['cat']))          { $post['cat']          = $post['cat_id']; }
        if (isset($post['sub_cat_id'])   && !isset($post['sub_cat']))      { $post['sub_cat']      = $post['sub_cat_id']; }
        if (isset($post['post_content']) && !isset($post['description']))  { $post['description']  = $post['post_content']; }

        // 2. Normalize simple product price tiers
        if (isset($post['price']) && (!isset($post['simple_price']) || empty($post['simple_price']))) {
            $post['simple_price'] = [$post['price']];
            if (!isset($post['simple_min_qty']) || empty($post['simple_min_qty'])) {
                $post['simple_min_qty'] = [0];
            }
            if (!isset($post['simple_max_qty']) || empty($post['simple_max_qty'])) {
                $post['simple_max_qty'] = [null];
            }
        }

        if (isset($post['price_tiers']) && is_array($post['price_tiers'])) {
            $post['simple_min_qty'] = [];
            $post['simple_max_qty'] = [];
            $post['simple_price'] = [];
            foreach ($post['price_tiers'] as $tier) {
                if (isset($tier['price']) && $tier['price'] !== '') {
                    $post['simple_min_qty'][] = $tier['min_qty'] ?? 1;
                    $post['simple_max_qty'][] = $tier['max_qty'] ?? null;
                    $post['simple_price'][]   = $tier['price'];
                }
            }
        }

        // 3. Normalize variation price tiers
        if (isset($post['vars']) && is_array($post['vars'])) {
            foreach ($post['vars'] as $idx => $v) {
                if (isset($v['price_tiers']) && is_array($v['price_tiers'])) {
                    $post['vars'][$idx]['min_qty'] = [];
                    $post['vars'][$idx]['max_qty'] = [];
                    $post['vars'][$idx]['price'] = [];
                    foreach ($v['price_tiers'] as $tier) {
                        if (isset($tier['price']) && $tier['price'] !== '') {
                            $post['vars'][$idx]['min_qty'][] = $tier['min_qty'] ?? 1;
                            $post['vars'][$idx]['max_qty'][] = $tier['max_qty'] ?? null;
                            $post['vars'][$idx]['price'][]   = $tier['price'];
                        }
                    }
                }
            }
        }
    }

    private function _fetch_product_variations($product_id, array $vcur_info)
    {
        $this->load->model('Items_model');
        $raw_variations = $this->Items_model->get_variations($product_id);
        $variations = [];
        foreach ($raw_variations as $var) {
            $var_price_tiers = [];
            if (!empty($var->prices)) {
                foreach ($var->prices as $p) {
                    $base_price    = (float)$p->price;
                    $display_price = $this->_convert_price_from_base($base_price, $vcur_info);
                    $var_price_tiers[] = [
                        'price_id'   => (int)($p->id ?? 0),
                        'min_qty'    => (int)$p->min_qty,
                        'max_qty'    => ($p->max_qty !== null && $p->max_qty !== '') ? (int)$p->max_qty : null,
                        'price'      => $display_price,
                        'price_base' => $base_price,
                    ];
                }
            }
            $var_images = [];
            if (!empty($var->images)) {
                foreach ($var->images as $img) {
                    if (!empty($img->image_path)) {
                        $var_images[] = [
                            'id'         => (int)$img->id,
                            'image_path' => $img->image_path,
                            'url'        => $this->_resolve_image_url($img->image_path)
                        ];
                    }
                }
            }
            $variations[] = [
                'variation_id' => (int)$var->id,
                'attr'         => is_array($var->attr) ? $var->attr : [],
                'price_tiers'  => $var_price_tiers,
                'images'       => $var_images,
                'image'        => isset($var_images[0]) ? $var_images[0]['url'] : null,
                'stock'        => (int)($var->stock ?? 0)
            ];
        }
        return $variations;
    }

    /**
     * Fetch saved price tiers from DB for the given product.
     * If the DB returns nothing (e.g., table doesn't exist on this server),
     * falls back to building the tiers from the submitted $post data.
     *
     * @param int        $product_id
     * @param array      $post       The normalised request payload
     * @param array|null $cur_info   Optional: vendor currency info from _get_vendor_currency_info()
     *                               When provided, prices are converted from base currency to vendor currency.
     * @return array
     */
    private function _fetch_price_tiers($product_id, $post, array $cur_info = null)
    {
        $price_tiers = [];

        try {
            $variations = $this->db->from('product_variations')
                ->where('product_id', $product_id)
                ->get()->result();

            foreach ($variations as $var) {
                $prices = $this->db->from('product_variation_price')
                    ->where('variation_id', $var->id)
                    ->order_by('min_qty', 'ASC')
                    ->get()->result();
                foreach ($prices as $p) {
                    $base_price = (float)$p->price;
                    // Convert from base currency back to vendor's display currency
                    $display_price = ($cur_info !== null)
                        ? $this->_convert_price_from_base($base_price, $cur_info)
                        : $base_price;
                    $price_tiers[] = [
                        'min_qty'    => (int)$p->min_qty,
                        'max_qty'    => ($p->max_qty !== null && $p->max_qty !== '') ? (int)$p->max_qty : null,
                        'price'      => $display_price,
                        'price_base' => $base_price, // Always include raw base price for debugging
                    ];
                }
            }
        } catch (\Throwable $e) {
            // DB query failed — fall through to post fallback
        }

        // Fallback: if nothing came from DB, build from submitted form data
        if (empty($price_tiers)) {
            $price_tiers = $this->_build_price_tiers_from_post($post);
        }

        return $price_tiers;
    }

    /**
     * Build a price_tiers array from submitted flat form-data keys.
     * Handles:
     *   simple product  → simple_min_qty[], simple_max_qty[], simple_price[]
     *   variable product → vars[0][min_qty][], vars[0][max_qty][], vars[0][price][]
     */
    private function _build_price_tiers_from_post($post)
    {
        $type = $post['product_type'] ?? 'simple';

        if ($type === 'simple') {
            $min_qtys = (array)($post['simple_min_qty'] ?? []);
            $max_qtys = (array)($post['simple_max_qty'] ?? []);
            $prices   = (array)($post['simple_price']   ?? []);

            $tiers = [];
            foreach ($prices as $i => $price) {
                if ($price === '' || $price === null) continue;
                $tiers[] = [
                    'min_qty' => (int)($min_qtys[$i] ?? 1),
                    'max_qty' => (isset($max_qtys[$i]) && $max_qtys[$i] !== '' && $max_qtys[$i] !== null)
                                    ? (int)$max_qtys[$i] : null,
                    'price'   => (float)$price,
                ];
            }
            return $tiers;
        }

        // Variable: collect tiers from all variations
        $vars = $post['vars'] ?? [];
        $tiers = [];
        if (is_array($vars)) {
            foreach ($vars as $idx => $v) {
                $min_qtys = (array)($v['min_qty'] ?? []);
                $max_qtys = (array)($v['max_qty'] ?? []);
                $prices   = (array)($v['price']   ?? []);
                foreach ($prices as $i => $price) {
                    if ($price === '' || $price === null) continue;
                    $tiers[] = [
                        'variation_index' => $idx,
                        'min_qty' => (int)($min_qtys[$i] ?? 1),
                        'max_qty' => (isset($max_qtys[$i]) && $max_qtys[$i] !== '' && $max_qtys[$i] !== null)
                                        ? (int)$max_qtys[$i] : null,
                        'price'   => (float)$price,
                    ];
                }
            }
        }
        return $tiers;
    }

    /**
     * Fetch currency details from ec_currency table.
     * Defaults to base currency (USD) if invalid/not found.
     *
     * @param int $currency_id
     * @return array
     */
    private function _get_currency_info($currency_id)
    {
        $currency_id = (int)$currency_id;
        if ($currency_id <= 0) {
            $currency_id = 1;
        }
        $row = $this->db->select('currency_id, symbol, iso_code')->from('ec_currency')->where('currency_id', $currency_id)->limit(1)->get()->row();
        if ($row) {
            return [
                'currency_id'     => (int)$row->currency_id,
                'currency_code'   => $row->iso_code,
                'currency_symbol' => $row->symbol
            ];
        }
        return [
            'currency_id'     => 1,
            'currency_code'   => 'USD',
            'currency_symbol' => '$'
        ];
    }

    /**
     * Fetch all image URLs for a product across all its variations.
     *
     * @param int $product_id
     * @return array
     */
    private function _fetch_product_images($product_id)
    {
        $images = [];
        try {
            $variations = $this->db->select('id')
                ->from('product_variations')
                ->where('product_id', $product_id)
                ->get()->result();

            if (!empty($variations)) {
                $variation_ids = array_column($variations, 'id');
                $rows = $this->db->select('image_path')
                    ->from('product_variation_images')
                    ->where_in('variation_id', $variation_ids)
                    ->order_by('id', 'ASC')
                    ->get()->result();

                foreach ($rows as $row) {
                    if (!empty($row->image_path)) {
                        $images[] = $this->_resolve_image_url($row->image_path);
                    }
                }
            }
        } catch (\Throwable $e) {
            // DB query failed or table not found
        }
        return $images;
    }

    /**
     * Helper to resolve a file name or path to a full public image URL.
     *
     * @param string $raw
     * @return string
     */
    private function _resolve_image_url($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        $rel = ltrim($raw, '/\\');
        if ($rel !== '' && @file_exists(FCPATH . $rel)) {
            return base_url($rel);
        }

        return base_url('uploads/products/' . $rel);
    }

    /**
     * Log request and response payloads for debugging API calls.
     *
     * @param string     $method
     * @param string     $action
     * @param array|null $payload
     * @param array      $files
     * @param array|null $response
     * @return void
     */
    private function _debug_log($method, $action, $payload, $files, $response = null)
    {
        try {
            $log_file = FCPATH . 'debug_api.log';
            $log_data = [
                'timestamp' => date('Y-m-d H:i:s'),
                'method'    => $method,
                'action'    => $action,
                'payload'   => $payload,
                'files'     => isset($files['variation_images']['name']) ? ['variation_images' => $files['variation_images']['name']] : $files,
                'response'  => $response
            ];
            @file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        } catch (\Throwable $e) {
            // Silence logger errors
        }
    }

    public function categories()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $post = array_merge(
            $this->get_json_input(),
            $this->input->post(NULL, true) ?: [],
            $this->input->get(NULL, true) ?: []
        );

        $parent_id_raw = $post['parent_id'] ?? null;
        $has_parent_filter = $parent_id_raw !== null && $parent_id_raw !== '';
        $parent_id = $has_parent_filter ? (int)$parent_id_raw : 0;

        $this->db->select('id, parent_id, name, slug, thumbnail, banner_image, icon, status');
        $this->db->from('ec_categories_prod');
        $this->db->where('status', '1');
        if ($parent_id > 0) {
            $this->db->where('parent_id', $parent_id);
        } else {
            $this->db->group_start();
            $this->db->where('parent_id', 0);
            $this->db->or_where('parent_id IS NULL', null, false);
            $this->db->group_end();
        }
        $this->db->order_by('name', 'ASC');
        $categories = $this->db->get()->result();

        $has_children_map = [];
        $cat_ids = [];
        if ($categories) {
            foreach ($categories as $c) {
                if (isset($c->id)) {
                    $cat_ids[] = (int)$c->id;
                }
            }
        }
        $cat_ids = array_values(array_unique(array_filter($cat_ids)));

        if ($cat_ids) {
            $rows = $this->db
                ->select('parent_id AS pid, COUNT(*) AS cnt', false)
                ->from('ec_categories_prod')
                ->where('status', '1')
                ->where_in('parent_id', $cat_ids)
                ->group_by('parent_id')
                ->get()->result();
            if ($rows) {
                foreach ($rows as $r) {
                    $pid = isset($r->pid) ? (int)$r->pid : 0;
                    $has_children_map[$pid] = isset($r->cnt) && (int)$r->cnt > 0;
                }
            }
        }

        $out = [];
        if ($categories) {
            foreach ($categories as $c) {
                $cid = isset($c->id) ? (int)$c->id : 0;
                $has_children = $cid > 0 ? (bool)($has_children_map[$cid] ?? false) : false;

                $out[] = [
                    'id' => $cid,
                    'parent_id' => isset($c->parent_id) ? (int)$c->parent_id : 0,
                    'name' => isset($c->name) ? (string)$c->name : '',
                    'slug' => isset($c->slug) ? (string)$c->slug : '',
                    'thumbnail' => isset($c->thumbnail) ? (string)$c->thumbnail : null,
                    'banner_image' => isset($c->banner_image) ? (string)$c->banner_image : null,
                    'icon' => isset($c->icon) ? (string)$c->icon : null,
                    'status' => isset($c->status) ? (string)$c->status : null,
                    'has_children' => $has_children,
                ];
            }
        }

        return $this->ok(['categories' => $out], 'success');
    }

    public function brands()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $post = array_merge(
            $this->get_json_input(),
            $this->input->post(NULL, true) ?: [],
            $this->input->get(NULL, true) ?: []
        );

        $cat_id = $post['category_id'] ?? ($post['cat_id'] ?? null);

        $this->db->select('brand_id, name, status, cat_id');
        $this->db->from('ec_brand');
        $this->db->where('status', '1');
        if ($cat_id !== null && $cat_id !== '') {
            $this->db->where('cat_id', (int)$cat_id);
        }
        $this->db->order_by('name', 'ASC');
        $brands = $this->db->get()->result_array();

        $out = [];
        if ($brands) {
            foreach ($brands as $b) {
                $out[] = [
                    'brand_id' => (int)$b['brand_id'],
                    'name' => (string)$b['name'],
                    'status' => (string)$b['status'],
                    'cat_id' => (int)$b['cat_id']
                ];
            }
        }

        return $this->ok(['brands' => $out], 'success');
    }

    // ── Attributes list (POST) ─────────────────────────────────────────────
    public function attributes()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->db->select('attribute_id, name, status');
        $this->db->from('ec_attribute');
        $this->db->where('status', '1');
        $this->db->order_by('name', 'ASC');
        $rows = $this->db->get()->result_array();

        $out = [];
        foreach ($rows as $a) {
            $out[] = [
                'attribute_id' => (int)$a['attribute_id'],
                'name'         => (string)$a['name'],
                'status'       => (string)$a['status']
            ];
        }

        return $this->ok(['attributes' => $out], 'success');
    }

    // ── Attribute values / items (POST) ────────────────────────────────────
    public function attribute_values()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $raw_input = file_get_contents('php://input');
        $json_data = json_decode($raw_input, true) ?: [];

        $post = array_merge(
            $json_data,
            $this->input->post(NULL, true) ?: [],
            $this->input->get(NULL, true)  ?: []
        );

        $attribute_id = $post['attribute_id'] ?? ($post['attr_id'] ?? ($post['id'] ?? ($post['attribute'] ?? null)));

        $this->db->select('attribute_item_id, attribute_id, name, status');
        $this->db->from('ec_attribute_item');
        $this->db->where('status', '1');
        if ($attribute_id !== null && $attribute_id !== '' && $attribute_id !== 'undefined' && $attribute_id !== 'null') {
            if (is_array($attribute_id)) {
                $ids = array_map('intval', $attribute_id);
                if (!empty($ids)) {
                    $this->db->where_in('attribute_id', $ids);
                }
            } else {
                $this->db->where('attribute_id', (int)$attribute_id);
            }
        }
        $this->db->order_by('name', 'ASC');
        $rows = $this->db->get()->result_array();

        $out = [];
        foreach ($rows as $item) {
            $out[] = [
                'attribute_item_id' => (int)$item['attribute_item_id'],
                'attribute_id'      => (int)$item['attribute_id'],
                'name'              => (string)$item['name'],
                'status'            => (string)$item['status']
            ];
        }

        return $this->ok(['attribute_values' => $out], 'success');
    }
}
