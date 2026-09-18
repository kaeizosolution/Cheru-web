<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Cart extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Cart_model');
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

    private function _require_customer()
    {
        $token = $this->_get_bearer_token();
        if ($token === '') {
            $customer = $this->session->userdata('customer');
            $customer_id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
            if ($customer_id > 0) {
                return $customer_id;
            }
            $this->fail('unauthorized', ['Missing Authorization Bearer token'], 401);
            return 0;
        }

        $claims = $this->verify_token($token);
        if (!$claims || !isset($claims['sub']) || ($claims['type'] ?? '') !== 'access' || ($claims['role'] ?? '') !== 'customer') {
            $this->fail('unauthorized', ['Invalid or expired access token'], 401);
            return 0;
        }

        $customer_id = (int)$claims['sub'];
        if ($customer_id <= 0) {
            $this->fail('unauthorized', ['Invalid token subject'], 401);
            return 0;
        }

        // Merge session
        $cust = $this->session->userdata('customer');
        $cust = is_array($cust) ? $cust : [];
        $cust['login_id'] = $customer_id;
        $cust['customer_id'] = $cust['customer_id'] ?? $customer_id;
        $cust['logged_in'] = $cust['logged_in'] ?? 1;
        $this->session->set_userdata('customer', $cust);
        $this->session->set_userdata('type', 'customer');
        $_SESSION['type'] = 'customer';

        return $customer_id;
    }

    private function _require_customer_or_session($require_csrf = false)
    {
        $token = $this->_get_bearer_token();
        if ($token !== '') {
            $claims = $this->verify_token($token);
            if (!$claims || !isset($claims['sub']) || ($claims['type'] ?? '') !== 'access' || ($claims['role'] ?? '') !== 'customer') {
                $this->fail('unauthorized', ['Invalid or expired access token'], 401);
                return null;
            }

            $customer_id = (int)$claims['sub'];
            if ($customer_id <= 0) {
                $this->fail('unauthorized', ['Invalid token subject'], 401);
                return null;
            }

            // Ensure Cart_model (which is session-backed) can load the correct DB cart for this customer.
            // Merge into existing session to avoid wiping `logged_in` and other fields.
            $cust = $this->session->userdata('customer');
            $cust = is_array($cust) ? $cust : [];
            $cust['login_id'] = $customer_id;
            $cust['customer_id'] = $cust['customer_id'] ?? $customer_id;
            $cust['logged_in'] = $cust['logged_in'] ?? 1;
            $this->session->set_userdata('customer', $cust);
            $this->session->set_userdata('type', 'customer');
            $_SESSION['type'] = 'customer';

            return [
                'mode' => 'token',
                'customer_id' => $customer_id,
            ];
        }

        if ($require_csrf) {
            $csrf_enabled = (bool)$this->config->item('csrf_protection');
            if (!$csrf_enabled) {
                return [
                    'mode' => 'session',
                    'customer_id' => 0,
                ];
            }
            $payload = array_merge($this->get_json_input(), $_GET ?: [], $this->input->post(NULL, true) ?: []);
            $csrf_name = $this->security->get_csrf_token_name();
            $csrf_val = isset($payload[$csrf_name]) ? (string)$payload[$csrf_name] : '';
            $csrf_hash = (string)$this->security->get_csrf_hash();
            if ($csrf_hash === '' && method_exists($this->security, 'get_csrf_cookie_name')) {
                $cookie_name = (string)$this->security->get_csrf_cookie_name();
                $cookie_val = (string)$this->input->cookie($cookie_name, true);
                if ($cookie_val !== '') {
                    $csrf_hash = $cookie_val;
                }
            }
            if ($csrf_val === '' || $csrf_hash === '' || $csrf_val !== $csrf_hash) {
                $this->fail('csrf_error', ['Invalid CSRF token'], 403);
                return null;
            }
        }

        return [
            'mode' => 'session',
            'customer_id' => 0,
        ];
    }

    private function _cart_response_payload()
    {
        $cart_data = $this->Cart_model->cart_items();
        $items = $cart_data['cart_items'] ?? [];
        $summary = $cart_data['cart_summary'] ?? (object)[];

        $mapped_items = [];
        foreach ($items as $it) {
            $product_id = (int)($it->product_id ?? 0);
            $attr_val = $it->attribute_item_id ?? [];
            if (is_string($attr_val)) {
                $attr_val = trim($attr_val);
                if ($attr_val === '') {
                    $attr_val = [];
                } else {
                    $decoded = json_decode($attr_val, true);
                    if (is_array($decoded)) {
                        $attr_val = $decoded;
                    } else {
                        $attr_val = preg_split('/\s*,\s*/', $attr_val, -1, PREG_SPLIT_NO_EMPTY);
                    }
                }
            }
            if (!is_array($attr_val)) {
                $attr_val = [$attr_val];
            }

            $variation_id = (int)($it->variation_id ?? 0);
            $cart_item_id = ($variation_id > 0)
                ? ('variation_' . $variation_id)
                : ($product_id . '_' . md5(json_encode($attr_val)));

            $price = (float)($it->sale_price ?? $it->regular_price ?? 0);
            $regular_price = (float)($it->regular_price ?? $price);
            $qty = (int)($it->quantity ?? 0);
            $subtotal = (float)($it->total ?? ($price * $qty));
            $image_url = (string)($it->image_url ?? '');
            if ($image_url === '' && isset($it->image) && (string)$it->image !== '') {
                $image_url = base_url('uploads/products/' . (string)$it->image);
            }

            $mapped_items[] = [
                'cart_item_id' => $cart_item_id,
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'attribute_item_id' => $attr_val,
                'attributes' => (string)($it->attributes ?? ''),
                'product_name' => (string)($it->post_title ?? ''),
                'image_url' => $image_url,
                'regular_price' => $regular_price,
                'price' => $price,
                'quantity' => $qty,
                'subtotal' => $subtotal,
            ];
        }

        $subtotal = (float)($summary->subtotal ?? $summary->total ?? 0);
        $shipping = (float)($summary->shipping ?? 0);
        $additional_charges = (float)($summary->additional_charges ?? 0);
        $grand_total = (float)($summary->grand_total ?? ($subtotal + $shipping + $additional_charges));

        // Backward compatibility: cart_total historically represented the amount shown as total.
        $cart_total = $grand_total;

        $coupon = $this->session->userdata('api_coupon');
        $coupon_discount = 0;
        $coupon_code = null;
        if (is_array($coupon) && !empty($coupon['code'])) {
            $coupon_code = (string)$coupon['code'];
            $new_discount = $this->_revalidate_and_get_coupon_discount($coupon_code, $grand_total, $items);
            if ($new_discount === null) {
                $this->session->unset_userdata('api_coupon');
                $coupon_code = null;
                $coupon_discount = 0;
            } else {
                $coupon_discount = $new_discount;
                $this->session->set_userdata('api_coupon', [
                    'code' => $coupon_code,
                    'discount' => $coupon_discount,
                ]);
            }
        }

        $final_total = max(0, $grand_total - $coupon_discount);

        return [
            'items' => $mapped_items,
            'cart_total' => $cart_total,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'additional_charges' => $additional_charges,
            'grand_total' => $grand_total,
            'coupon' => $coupon_code ? [
                'coupon_code' => $coupon_code,
                'discount' => $coupon_discount,
                'final_total' => $final_total,
            ] : null,
            'final_total' => $final_total,
        ];
    }

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $ctx = $this->_require_customer_or_session(false);
        if ($ctx === null) {
            return;
        }

        return $this->ok($this->_cart_response_payload(), 'success');
    }

    private function _validate_stock($product, $quantity)
    {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            $quantity = 1;
        }

        if (!$product) {
            return 'Product not found';
        }

        // If no obvious stock field exists, do not block (avoid breaking existing product types)
        $stock = null;

        if (is_object($product)) {
            if (isset($product->stock) && is_numeric($product->stock)) {
                $stock = (int)$product->stock;
            } elseif (isset($product->_stock) && is_numeric($product->_stock)) {
                $stock = (int)$product->_stock;
            } elseif (isset($product->quantity) && is_numeric($product->quantity)) {
                $stock = (int)$product->quantity;
            }
        } elseif (is_array($product)) {
            if (isset($product['stock']) && is_numeric($product['stock'])) {
                $stock = (int)$product['stock'];
            } elseif (isset($product['_stock']) && is_numeric($product['_stock'])) {
                $stock = (int)$product['_stock'];
            } elseif (isset($product['quantity']) && is_numeric($product['quantity'])) {
                $stock = (int)$product['quantity'];
            }
        }

        if ($stock === null) {
            return null;
        }

        if ($stock <= 0) {
            return 'OUT OF STOCK';
        }

        if ($quantity > $stock) {
            return 'You can not buy more then ' . $stock . ' quantity';
        }

        return null;
    }

    private function _find_cart_item($cart_item_id)
    {
        $cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : [];
        if (!is_array($cart) || empty($cart)) {
            return null;
        }

        if (!isset($cart[$cart_item_id])) {
            return null;
        }

        $row = $cart[$cart_item_id];
        $product_id = (int)($row['product_id'] ?? 0);
        $variation_id = (int)($row['variation_id'] ?? 0);
        $attr = $row['attribute_item_id'] ?? [];
        if (!is_array($attr)) {
            $attr = [$attr];
        }

        return [
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'attribute_item' => $attr,
        ];
    }

    private function _get_product_row($product_id)
    {
        // Prefer new products table
        $product = $this->db->get_where('products', ['id' => (int)$product_id])->row();
        if ($product) {
            $product->_source = 'products';
            return $product;
        }

        // Fallback legacy
        $product = $this->db->get_where('ec_product', ['id' => (int)$product_id])->row();
        if ($product) {
            $product->_source = 'ec_product';
            return $product;
        }
        $product = $this->db->get_where('ec_product', ['product_id' => (int)$product_id])->row();
        if ($product) {
            $product->_source = 'ec_product';
            return $product;
        }
        return null;
    }

    private function _normalize_attribute_items($raw)
    {
        $attribute_items = [];
        if (empty($raw)) {
            return [];
        }
        if (is_string($raw)) {
            $s = trim($raw);
            if ($s === '') {
                return [];
            }
            $decoded = json_decode($s, true);
            if (is_array($decoded)) {
                $attribute_items = $decoded;
            } else {
                $attribute_items = preg_split('/\s*,\s*/', $s, -1, PREG_SPLIT_NO_EMPTY);
            }
        } elseif (is_array($raw)) {
            $attribute_items = $raw;
        } else {
            $attribute_items = [$raw];
        }

        $attribute_items = array_values(array_filter($attribute_items, function ($v) {
            return $v !== null && $v !== '';
        }));

        $attribute_items = array_map(function ($v) {
            if (is_int($v)) {
                return $v;
            }
            if (is_float($v) && (int)$v == $v) {
                return (int)$v;
            }
            if (is_string($v)) {
                $s = trim($v);
                if ($s !== '' && ctype_digit($s)) {
                    return (int)$s;
                }
                return $s;
            }
            return $v;
        }, $attribute_items);

        if (!empty($attribute_items)) {
            sort($attribute_items);
        }
        return $attribute_items;
    }

    private function _derive_attribute_items_from_variation($variation_row)
    {
        if (!$variation_row) {
            return [];
        }

        $raw = isset($variation_row->attr_json) ? (string)$variation_row->attr_json : '';
        $decoded = [];
        if (trim($raw) !== '') {
            $tmp = json_decode($raw, true);
            if (is_array($tmp)) {
                $decoded = $tmp;
            }
        }

        $vals = [];
        if (is_array($decoded) && $decoded) {
            $is_assoc = array_keys($decoded) !== range(0, count($decoded) - 1);
            if ($is_assoc) {
                foreach ($decoded as $k => $v) {
                    if (is_scalar($v)) {
                        $vals[] = trim((string)$v);
                    }
                }
            } else {
                foreach ($decoded as $pair) {
                    if (is_array($pair) && isset($pair['value']) && is_scalar($pair['value'])) {
                        $vals[] = trim((string)$pair['value']);
                    }
                }
            }
        }

        $ids = [];
        $names = [];
        foreach ($vals as $v) {
            if ($v === '') {
                continue;
            }
            if (ctype_digit($v)) {
                $iv = (int)$v;
                if ($iv > 0) {
                    $ids[] = $iv;
                }
            } else {
                $names[] = $v;
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids) && !empty($names) && $this->db->table_exists('ec_attribute_item')) {
            $names = array_values(array_unique(array_filter(array_map('trim', $names), function ($x) {
                return $x !== '';
            })));
            if (!empty($names)) {
                $rows = $this->db
                    ->select('attribute_item_id')
                    ->from('ec_attribute_item')
                    ->where_in('name', $names)
                    ->get()->result();
                foreach ($rows as $r) {
                    $rid = (int)($r->attribute_item_id ?? 0);
                    if ($rid > 0) {
                        $ids[] = $rid;
                    }
                }
                $ids = array_values(array_unique(array_filter($ids)));
            }
        }

        if (!empty($ids)) {
            sort($ids);
        }
        return $ids;
    }

    public function add()
    {
        if (!$this->require_post()) {
            return;
        }

        $ctx = $this->_require_customer_or_session(true);
        if ($ctx === null) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $product_id = (int)($payload['product_id'] ?? 0);
        $variation_id = (int)($payload['variation_id'] ?? 0);
        $quantity = (int)($payload['quantity'] ?? 0);
        $attr = $this->_normalize_attribute_items($payload['attribute_item'] ?? ($payload['attribute_item_id'] ?? []));

        if ($product_id <= 0 || $quantity <= 0) {
            return $this->fail('validation_error', ['product_id and quantity are required'], 422);
        }

        // Variation validation + inherit product_id
        if ($variation_id > 0) {
            $vrow = $this->db->get_where('product_variations', ['id' => $variation_id], 1)->row();
            if (!$vrow) {
                return $this->fail('not_found', ['Variation not found'], 404);
            }
            $product_id = (int)($vrow->product_id ?? $product_id);
            $stock = isset($vrow->stock) ? (int)$vrow->stock : 0;
            if ($stock <= 0) {
                return $this->fail('out_of_stock', ['OUT OF STOCK'], 409);
            }
            if ($quantity > $stock) {
                return $this->fail('out_of_stock', ['You can not buy more then ' . $stock . ' quantity'], 409);
            }

            // Align with previous working customer controller
            if (empty($attr)) {
                $derived = $this->_derive_attribute_items_from_variation($vrow);
                if (!empty($derived)) {
                    $attr = $derived;
                }
            }
        }

        $product = $this->_get_product_row($product_id);
        if (!$product) {
            return $this->fail('not_found', ['Product not found'], 404);
        }

        // If this is a new products-table item and variation_id not provided, try resolve via attributes
        if ($variation_id <= 0 && isset($product->_source) && (string)$product->_source === 'products') {
            $resolved_vid = (int)$this->Cart_model->resolve_variation_id_for_attrs($product_id, $attr);
            if ($resolved_vid > 0) {
                $variation_id = $resolved_vid;
                $vrow2 = $this->db->get_where('product_variations', ['id' => $variation_id], 1)->row();
                if ($vrow2) {
                    $stock2 = isset($vrow2->stock) ? (int)$vrow2->stock : 0;
                    if ($stock2 <= 0) {
                        return $this->fail('out_of_stock', ['OUT OF STOCK'], 409);
                    }
                    if ($quantity > $stock2) {
                        return $this->fail('out_of_stock', ['You can not buy more then ' . $stock2 . ' quantity'], 409);
                    }
                }
            }
        }

        $stock_error = $this->_validate_stock($product, $quantity);
        if ($stock_error) {
            return $this->fail('out_of_stock', [$stock_error], 409);
        }

        $args = [
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'quantity' => $quantity,
            'attribute_item' => $attr,
            'action' => 'add',
        ];
        $this->Cart_model->add_to_cart($args);

        return $this->ok($this->_cart_response_payload(), 'success');
    }

    public function update()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST' && $method !== 'PATCH') {
            return $this->fail('method_not_allowed', ['Only POST or PATCH is allowed'], 405);
        }

        $ctx = $this->_require_customer_or_session(true);
        if ($ctx === null) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $cart_item_id = isset($payload['cart_item_id']) ? (string)$payload['cart_item_id'] : '';
        $quantity = (int)($payload['quantity'] ?? 0);

        $product_id = (int)($payload['product_id'] ?? 0);
        $variation_id = (int)($payload['variation_id'] ?? 0);
        $attr = $payload['attribute_item'] ?? ($payload['attribute_item_id'] ?? []);
        if (is_string($attr)) {
            $attr = trim($attr);
            if ($attr === '') {
                $attr = [];
            } else {
                $decoded = json_decode($attr, true);
                if (is_array($decoded)) {
                    $attr = $decoded;
                } else {
                    $attr = preg_split('/\s*,\s*/', $attr, -1, PREG_SPLIT_NO_EMPTY);
                }
            }
        }
        if (!is_array($attr)) {
            $attr = [$attr];
        }

        if ($quantity <= 0) {
            return $this->fail('validation_error', ['quantity is required (quantity > 0)'], 422);
        }

        // Primary mode: update by cart_item_id
        if ($cart_item_id !== '') {
            $item = $this->_find_cart_item($cart_item_id);
            if (!$item) {
                return $this->fail('not_found', ['Cart item not found'], 404);
            }

            $product = $this->_get_product_row($item['product_id']);
            if (!$product) {
                return $this->fail('not_found', ['Product not found'], 404);
            }

            $stock_error = $this->_validate_stock($product, $quantity);
            if ($stock_error) {
                return $this->fail('out_of_stock', [$stock_error], 409);
            }

            $args = [
                'product_id' => $item['product_id'],
                'variation_id' => (int)($item['variation_id'] ?? 0),
                'quantity' => $quantity,
                'attribute_item' => $item['attribute_item'],
                'action' => 'update',
            ];
            $this->Cart_model->add_to_cart($args);

            return $this->ok($this->_cart_response_payload(), 'success');
        }

        // Option 1 mode: update by product_id/variation_id + attribute_item
        if ($variation_id > 0) {
            $vrow = $this->db->get_where('product_variations', ['id' => $variation_id], 1)->row();
            if (!$vrow) {
                return $this->fail('not_found', ['Variation not found'], 404);
            }
            $product_id = (int)($vrow->product_id ?? 0);
            $stock = isset($vrow->stock) ? (int)$vrow->stock : 0;
            if ($stock <= 0) {
                return $this->fail('out_of_stock', ['OUT OF STOCK'], 409);
            }
            if ($quantity > $stock) {
                return $this->fail('out_of_stock', ['You can not buy more then ' . $stock . ' quantity'], 409);
            }
        }

        if ($product_id <= 0) {
            return $this->fail('validation_error', ['cart_item_id or product_id or variation_id is required'], 422);
        }

        $product = $this->_get_product_row($product_id);
        if (!$product) {
            return $this->fail('not_found', ['Product not found'], 404);
        }

        $stock_error = $this->_validate_stock($product, $quantity);
        if ($stock_error) {
            return $this->fail('out_of_stock', [$stock_error], 409);
        }

        $this->Cart_model->add_to_cart([
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'quantity' => $quantity,
            'attribute_item' => $attr,
            'action' => 'update',
        ]);

        return $this->ok($this->_cart_response_payload(), 'success');
    }

    public function remove()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'DELETE' && $method !== 'POST') {
            return $this->fail('method_not_allowed', ['Only DELETE or POST is allowed'], 405);
        }

        $ctx = $this->_require_customer_or_session(true);
        if ($ctx === null) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

        $cart_item_id = isset($payload['cart_item_id']) ? (string)$payload['cart_item_id'] : '';
        if ($cart_item_id === '') {
            $cart_item_id = (string)($payload['rowid'] ?? '');
        }
        if ($cart_item_id === '') {
            $cart_item_id = (string)$this->input->get('cart_item_id');
        }

        // Optional alternative remove signature (similar to add): product_id/variation_id + attributes
        $product_id = (int)($payload['product_id'] ?? 0);
        $variation_id = (int)($payload['variation_id'] ?? 0);
        $attr = $payload['attribute_item'] ?? ($payload['attribute_item_id'] ?? []);
        if (is_string($attr)) {
            $attr = trim($attr);
            if ($attr === '') {
                $attr = [];
            } else {
                $decoded = json_decode($attr, true);
                if (is_array($decoded)) {
                    $attr = $decoded;
                } else {
                    $attr = preg_split('/\s*,\s*/', $attr, -1, PREG_SPLIT_NO_EMPTY);
                }
            }
        }
        if (!is_array($attr)) {
            $attr = [$attr];
        }

        // 1) Primary: remove by cart_item_id (recommended)
        if ($cart_item_id !== '') {
            $item = $this->_find_cart_item($cart_item_id);
            if (!$item) {
                return $this->fail('not_found', ['Cart item not found'], 404);
            }

            $args = [
                'product_id' => $item['product_id'],
                'variation_id' => (int)($item['variation_id'] ?? 0),
                'attribute_item' => $item['attribute_item'],
                'action' => 'delete',
            ];
            $this->Cart_model->add_to_cart($args);

            return $this->ok($this->_cart_response_payload(), 'success');
        }

        // 2) Alternative: remove by variation_id
        if ($variation_id > 0) {
            $vrow = $this->db->get_where('product_variations', ['id' => $variation_id], 1)->row();
            if ($vrow && isset($vrow->product_id)) {
                $product_id = (int)$vrow->product_id;
            }

            if ($product_id <= 0) {
                return $this->fail('validation_error', ['variation_id is invalid'], 422);
            }

            $this->Cart_model->add_to_cart([
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => 1,
                'attribute_item' => $attr,
                'action' => 'delete',
            ]);

            return $this->ok($this->_cart_response_payload(), 'success');
        }

        // 3) Alternative: remove by product_id (if multiple matches, remove all matching product_id)
        if ($product_id > 0) {
            $cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : [];
            if (!is_array($cart) || empty($cart)) {
                return $this->ok($this->_cart_response_payload(), 'success');
            }

            $removed_any = false;

            // If attributes are provided, delete only that single key
            $attr_for_key = $attr;
            if (!is_array($attr_for_key)) {
                $attr_for_key = [$attr_for_key];
            }
            $attr_for_key = array_values(array_filter($attr_for_key, function($v){ return $v !== null && $v !== ''; }));
            sort($attr_for_key);

            if (!empty($attr_for_key)) {
                $key = $product_id . '_' . md5(json_encode($attr_for_key));
                if (isset($cart[$key])) {
                    $row = $cart[$key];
                    $this->Cart_model->add_to_cart([
                        'product_id' => (int)($row['product_id'] ?? $product_id),
                        'variation_id' => (int)($row['variation_id'] ?? 0),
                        'quantity' => 1,
                        'attribute_item' => ($row['attribute_item_id'] ?? $attr_for_key),
                        'action' => 'delete',
                    ]);
                    $removed_any = true;
                }
            } else {
                // No attrs provided: remove all cart rows with this product_id
                foreach ($cart as $k => $row) {
                    if ((int)($row['product_id'] ?? 0) !== $product_id) {
                        continue;
                    }
                    $this->Cart_model->add_to_cart([
                        'product_id' => (int)($row['product_id'] ?? $product_id),
                        'variation_id' => (int)($row['variation_id'] ?? 0),
                        'quantity' => 1,
                        'attribute_item' => ($row['attribute_item_id'] ?? []),
                        'action' => 'delete',
                    ]);
                    $removed_any = true;
                }
            }

            if (!$removed_any) {
                return $this->fail('not_found', ['Cart item not found'], 404);
            }

            return $this->ok($this->_cart_response_payload(), 'success');
        }

        return $this->fail('validation_error', ['cart_item_id (recommended) or product_id or variation_id is required'], 422);

        return $this->ok($this->_cart_response_payload(), 'success');
    }

    public function apply_coupon()
    {
        if (!$this->require_post()) {
            return;
        }

        $ctx = $this->_require_customer_or_session(true);
        if ($ctx === null) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, false) ?: []);
        $coupon_code = isset($payload['coupon_code']) ? trim((string)$payload['coupon_code']) : '';
        if ($coupon_code === '') {
            $coupon_code = isset($payload['coupon']) ? trim((string)$payload['coupon']) : '';
        }

        if ($coupon_code === '') {
            return $this->fail('validation_error', ['coupon_code is required'], 422);
        }

        $cart_data = $this->Cart_model->cart_items();
        $summary = $cart_data['cart_summary'] ?? (object)[];
        $cart_total = (float)($summary->grand_total ?? $summary->total ?? 0);
        $items = $cart_data['cart_items'] ?? [];

        // Use native parameterized query — bypasses all CI3 query builder escaping/state issues
        $coupon_code_lower = strtolower(trim($coupon_code));
        $q = $this->db->query(
            "SELECT * FROM ec_coupon WHERE LOWER(name) = ? AND status = '1' LIMIT 1",
            [$coupon_code_lower]
        );
        $coupon = $q->num_rows() ? $q->row() : null;

        if (!$coupon) {
            return $this->fail('invalid_coupon', ['Invalid or expired coupon code.'], 200);
        }

        // Use Asia/Kolkata timezone to match the date used when admin creates coupons
        $tz = new DateTimeZone('Asia/Kolkata');
        $today = (new DateTime('now', $tz))->format('Y-m-d');
        $start = !empty($coupon->start_date) ? (new DateTime($coupon->start_date, $tz))->format('Y-m-d') : null;
        $end   = !empty($coupon->end_date)   ? (new DateTime($coupon->end_date,   $tz))->format('Y-m-d') : null;

        if ($start && $today < $start) {
            return $this->fail('invalid_coupon', ['Coupon not started yet'], 200);
        }
        if ($end && $today > $end) {
            return $this->fail('invalid_coupon', ['Coupon expired'], 200);
        }

        // Validate brand, category, and product coupon applicability
        if (!$this->_is_coupon_applicable($coupon, $items)) {
            $msg = 'This coupon is not applicable to the items in your cart.';
            $ctype = (int)($coupon->ctype ?? 1);
            if ($ctype === 1) { // Category coupon
                $msg = 'invalid';
            } elseif ($ctype === 2) { // Brand coupon
                $msg = 'not valid for cart items';
            }
            return $this->fail('invalid_coupon', [$msg], 200);
        }

        $min = isset($coupon->minimum_order_value) ? (float)$coupon->minimum_order_value : 0;
        if ($min > 0 && $cart_total < $min) {
            return $this->fail('invalid_coupon', ['Minimum order value is ' . $min], 200);
        }

        $matching_subtotal = $this->_get_matching_items_subtotal($coupon, $items);
        $discount_val = (float)($coupon->discount ?? 0);
        $discount = 0;
        $type = (int)($coupon->type ?? 1);
        if ($type === 2) {
            $discount = ($matching_subtotal * $discount_val / 100);
        } else {
            $discount = $discount_val;
        }

        $discount = max(0, min($discount, $matching_subtotal));

        $this->session->set_userdata('api_coupon', [
            'code' => $coupon_code,
            'discount' => $discount,
        ]);

        return $this->ok($this->_cart_response_payload(), 'success');
    }

    private function _parse_coupon_ids($raw)
    {
        if (empty($raw)) {
            return [];
        }
        if (is_array($raw)) {
            $vals = $raw;
        } else {
            $s = trim((string)$raw);
            if ($s === '') {
                return [];
            }
            $decoded = json_decode($s, true);
            if (is_array($decoded)) {
                $vals = $decoded;
            } else {
                $vals = preg_split('/\s*,\s*/', $s, -1, PREG_SPLIT_NO_EMPTY);
            }
        }
        $out = [];
        foreach ($vals as $v) {
            $v_str = trim((string)$v);
            if ($v_str !== '' && $v_str !== '0' && strtolower($v_str) !== 'all') {
                $out[] = $v_str;
            }
        }
        return array_values(array_unique($out));
    }

    private function _get_matching_items_subtotal($coupon, $cart_items)
    {
        if (empty($cart_items)) {
            return 0;
        }
        $ctype = (int)($coupon->ctype ?? 1);
        $subtotal = 0;

        if ($ctype === 1) { // Category coupon
            $allowed_categories = $this->_parse_coupon_ids($coupon->category_id);
            if (empty($allowed_categories)) {
                foreach ($cart_items as $item) {
                    $item_arr = (array)$item;
                    $subtotal += (float)($item_arr['total'] ?? 0);
                }
                return $subtotal;
            }

            foreach ($cart_items as $item) {
                $item_arr = (array)$item;
                $cats = [];
                if (isset($item_arr['category']) && (string)$item_arr['category'] !== '') {
                    $cats[] = (string)$item_arr['category'];
                }
                if (isset($item_arr['sub_category']) && (string)$item_arr['sub_category'] !== '') {
                    $cats[] = (string)$item_arr['sub_category'];
                }
                if (isset($item_arr['sub_sub_category']) && (string)$item_arr['sub_sub_category'] !== '') {
                    $cats[] = (string)$item_arr['sub_sub_category'];
                }

                $matched = false;
                foreach ($cats as $cat) {
                    if (in_array($cat, $allowed_categories, true)) {
                        $matched = true;
                        break;
                    }
                }
                if ($matched) {
                    $subtotal += (float)($item_arr['total'] ?? 0);
                }
            }
            return $subtotal;
        }

        if ($ctype === 2) { // Brand coupon
            $allowed_brands = $this->_parse_coupon_ids($coupon->brand_id);
            if (empty($allowed_brands)) {
                foreach ($cart_items as $item) {
                    $item_arr = (array)$item;
                    $subtotal += (float)($item_arr['total'] ?? 0);
                }
                return $subtotal;
            }

            foreach ($cart_items as $item) {
                $item_arr = (array)$item;
                $item_brand = isset($item_arr['brand_id']) ? (string)$item_arr['brand_id'] : '';
                if ($item_brand !== '' && in_array($item_brand, $allowed_brands, true)) {
                    $subtotal += (float)($item_arr['total'] ?? 0);
                }
            }
            return $subtotal;
        }

        if ($ctype === 3) { // Product coupon
            $allowed_products = $this->_parse_coupon_ids($coupon->product_id);
            if (empty($allowed_products)) {
                foreach ($cart_items as $item) {
                    $item_arr = (array)$item;
                    $subtotal += (float)($item_arr['total'] ?? 0);
                }
                return $subtotal;
            }

            foreach ($cart_items as $item) {
                $item_arr = (array)$item;
                $item_product = isset($item_arr['product_id']) ? (string)$item_arr['product_id'] : (isset($item_arr['id']) ? (string)$item_arr['id'] : '');
                if ($item_product !== '' && in_array($item_product, $allowed_products, true)) {
                    $subtotal += (float)($item_arr['total'] ?? 0);
                }
            }
            return $subtotal;
        }

        foreach ($cart_items as $item) {
            $item_arr = (array)$item;
            $subtotal += (float)($item_arr['total'] ?? 0);
        }
        return $subtotal;
    }

    private function _is_coupon_applicable($coupon, $cart_items)
    {
        if (empty($cart_items)) {
            return false;
        }

        $ctype = (int)($coupon->ctype ?? 1);


        if ($ctype === 1) { // Category coupon
            $allowed_categories = $this->_parse_coupon_ids($coupon->category_id);
            if (empty($allowed_categories)) {
                return true; // Applicable to all categories
            }

            foreach ($cart_items as $item) {
                $item_arr = (array)$item;
                $cats = [];
                if (isset($item_arr['category']) && (string)$item_arr['category'] !== '') {
                    $cats[] = (string)$item_arr['category'];
                }
                if (isset($item_arr['sub_category']) && (string)$item_arr['sub_category'] !== '') {
                    $cats[] = (string)$item_arr['sub_category'];
                }
                if (isset($item_arr['sub_sub_category']) && (string)$item_arr['sub_sub_category'] !== '') {
                    $cats[] = (string)$item_arr['sub_sub_category'];
                }

                $item_matched = false;
                foreach ($cats as $cat) {
                    if (in_array($cat, $allowed_categories, true)) {
                        $item_matched = true;
                        break;
                    }
                }
                if (!$item_matched) {
                    return false;
                }
            }
            return true;
        }

        if ($ctype === 2) { // Brand coupon
            $allowed_brands = $this->_parse_coupon_ids($coupon->brand_id);
            if (empty($allowed_brands)) {
                return true; // Applicable to all brands
            }

            foreach ($cart_items as $item) {
                $item_arr = (array)$item;
                $item_brand = isset($item_arr['brand_id']) ? (string)$item_arr['brand_id'] : '';
                if ($item_brand === '' || !in_array($item_brand, $allowed_brands, true)) {
                    return false;
                }
            }
            return true;
        }

        if ($ctype === 3) { // Product coupon
            $allowed_products = $this->_parse_coupon_ids($coupon->product_id);
            if (empty($allowed_products)) {
                return true; // Applicable to all products
            }

            foreach ($cart_items as $item) {
                $item_arr = (array)$item;
                $item_product = isset($item_arr['product_id']) ? (string)$item_arr['product_id'] : (isset($item_arr['id']) ? (string)$item_arr['id'] : '');
                if ($item_product === '' || !in_array($item_product, $allowed_products, true)) {
                    return false;
                }
            }
            return true;
        }

        return true;
    }

    private function _revalidate_and_get_coupon_discount($coupon_code, $cart_total, $items)
    {
        $coupon_code = trim((string)$coupon_code);
        if ($coupon_code === '') {
            return null;
        }

        $coupon_code_lower = strtolower(trim($coupon_code));
        $q = $this->db->query(
            "SELECT * FROM ec_coupon WHERE LOWER(name) = ? AND status = '1' LIMIT 1",
            [$coupon_code_lower]
        );
        $coupon = $q->num_rows() ? $q->row() : null;
        if (!$coupon) {
            return null;
        }

        // Use Asia/Kolkata timezone to match the date used when admin creates coupons
        $tz = new DateTimeZone('Asia/Kolkata');
        $today = (new DateTime('now', $tz))->format('Y-m-d');
        $start = !empty($coupon->start_date) ? (new DateTime($coupon->start_date, $tz))->format('Y-m-d') : null;
        $end   = !empty($coupon->end_date)   ? (new DateTime($coupon->end_date,   $tz))->format('Y-m-d') : null;

        if ($start && $today < $start) {
            return null;
        }
        if ($end && $today > $end) {
            return null;
        }

        // Validate brand, category, and product coupon applicability
        if (!$this->_is_coupon_applicable($coupon, $items)) {
            return null;
        }

        $min = isset($coupon->minimum_order_value) ? (float)$coupon->minimum_order_value : 0;
        if ($min > 0 && $cart_total < $min) {
            return null;
        }

        $matching_subtotal = $this->_get_matching_items_subtotal($coupon, $items);
        $discount_val = (float)($coupon->discount ?? 0);
        $discount = 0;
        $type = (int)($coupon->type ?? 1);
        if ($type === 2) {
            $discount = ($matching_subtotal * $discount_val / 100);
        } else {
            $discount = $discount_val;
        }

        $discount = max(0, min($discount, $matching_subtotal));
        return $discount;
    }

    public function remove_coupon()
    {
        // Accept POST or DELETE (frontend uses POST for reliability with CSRF)
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST' && $method !== 'DELETE') {
            return $this->fail('method_not_allowed', ['Only POST and DELETE are allowed'], 405);
        }

        // Validate Customer/Session Context (CSRF required for session-based, skipped for token)
        $ctx = $this->_require_customer_or_session(true);
        if ($ctx === null) {
            return;
        }

        // Simply clear the coupon from the session — no re-validation needed on removal
        $this->session->unset_userdata('api_coupon');

        return $this->ok($this->_cart_response_payload(), 'Coupon removed successfully');
    }
}
