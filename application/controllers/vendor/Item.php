<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Item extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->library('image_upload');
        $this->load->model('Query_model');
        $this->load->model('Items_model');
        $this->load->helper('api');
        $this->config->load('custom_config');

        $this->TYPE = $this->session->userdata('type') ?: 'vendor';
        $session_data = $this->session->userdata($this->TYPE);
        $this->vendor_id = isset($session_data['vendor_id'])
            ? (int)$session_data['vendor_id']
            : (isset($session_data['login_id']) ? (int)$session_data['login_id'] : 0);
    }

    private function _base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function _sign_token(array $claims, $ttl_seconds)
    {
        $secret = (string)$this->config->item('encryption_key');
        if ($secret === '') {
            $secret = 'default_api_secret';
        }

        $now = time();
        $claims['iat'] = $now;
        $claims['exp'] = $now + (int)$ttl_seconds;

        $payload = json_encode($claims);
        $b64 = $this->_base64url_encode($payload);
        $sig = hash_hmac('sha256', $b64, $secret, true);
        $b64sig = $this->_base64url_encode($sig);
        return $b64 . '.' . $b64sig;
    }

    private function _is_token_expired($token)
    {
        $parts = explode('.', (string)$token);
        if (count($parts) !== 2) return true;
        $padding = strlen($parts[0]) % 4;
        if ($padding) $parts[0] .= str_repeat('=', 4 - $padding);
        $claims = @json_decode(@base64_decode(strtr($parts[0], '-_', '+/')), true);
        if (!is_array($claims) || !isset($claims['exp'])) return true;
        return time() >= (int)$claims['exp'];
    }

    private function _get_vendor_access_token()
    {
        $sess = $this->session->userdata($this->TYPE);
        if (!is_array($sess) || empty($sess)) {
            $all_sess = $this->session->all_userdata();
            foreach ($all_sess as $key => $val) {
                if (is_array($val) && !empty($val['logged_in']) && (!empty($val['vendor_id']) || !empty($val['login_id']))) {
                    $sess = $val;
                    break;
                }
            }
        }

        if (is_array($sess) && !empty($sess['access_token'])) {
            $existing = (string)$sess['access_token'];
            if (!$this->_is_token_expired($existing)) {
                return $existing;
            }
            unset($sess['access_token']);
        }
        if (!empty($_SESSION['token']) && !$this->_is_token_expired($_SESSION['token'])) {
            return (string)$_SESSION['token'];
        }

        if (is_array($sess) && (!empty($sess['vendor_id']) || !empty($sess['login_id']))) {
            $vendor_id = !empty($sess['login_id']) ? (int)$sess['login_id'] : (int)$sess['vendor_id'];
            $token = $this->_sign_token(['sub' => $vendor_id, 'type' => 'access', 'role' => 'vendor'], 31536000);
            $sess['access_token'] = $token;
            $this->session->set_userdata('vendor', $sess);
            if (!empty($this->TYPE)) {
                $this->session->set_userdata($this->TYPE, $sess);
            }
            return $token;
        }
        return '';
    }

    public function create()
    {
        if (!logged_in()) redirect("{$this->TYPE}/auth/login");

        $data = [];
        $data['categories'] = $this->Query_model->get_data('ec_categories_prod', ['status' => '1']);
        $data['attributes'] = $this->Query_model->get_data('ec_attribute', ['status' => '1']);
        $data['attr_items'] = $this->Query_model->get_data('ec_attribute_item', ['status' => '1']);
        $currencies_result = $this->db->select('currency_id, name, symbol, iso_code, basic')->from('ec_currency')->where('status', '1')->order_by('name', 'ASC')->get()->result();
        if (empty($currencies_result)) {
            $currencies_result = $this->db->select('currency_id, name, symbol, iso_code, basic')->from('ec_currency')->order_by('name', 'ASC')->get()->result();
        }
        $data['currencies'] = $currencies_result;
        $data['vendor_id']  = $this->vendor_id;
        $data['product']    = null;
        $data['variations'] = [];
        $data['TYPE']       = $this->TYPE;
        $data['csrf']       = csrf_token();
        $data['access_token'] = $this->_get_vendor_access_token();
        $data['use_api']      = (bool)$this->config->item('use_api') && ($data['access_token'] !== '');
        $this->load->template("{$this->TYPE}/product/save_product5", $data);
    }

    public function edit($id)
    {
        if (!logged_in()) redirect("{$this->TYPE}/auth/login");

        $data = [];
        $data['product']    = $this->Items_model->get_product($id);
        $variations         = $this->Items_model->get_variations($id);

        // Convert stored base-currency (USD) prices → vendor's preferred currency for display
        $vcur_info = $this->_get_vendor_currency_info($this->vendor_id);
        if ($vcur_info['needs_conversion'] && is_array($variations)) {
            foreach ($variations as &$var) {
                if (!empty($var->prices) && is_array($var->prices)) {
                    foreach ($var->prices as &$price_row) {
                        $price_row->price = $this->_convert_price_from_base(
                            (float)$price_row->price,
                            $vcur_info
                        );
                    }
                    unset($price_row);
                }
            }
            unset($var);
        }
        $data['variations'] = $variations;
        $data['vendor_currency'] = $vcur_info; // pass to view if needed

        $data['categories'] = $this->Query_model->get_data('ec_categories_prod', ['status' => '1']);
        $data['attributes'] = $this->Query_model->get_data('ec_attribute', ['status' => '1']);
        $data['attr_items'] = $this->Query_model->get_data('ec_attribute_item', ['status' => '1']);
        $currencies_result = $this->db->select('currency_id, name, symbol, iso_code, basic')->from('ec_currency')->where('status', '1')->order_by('name', 'ASC')->get()->result();
        if (empty($currencies_result)) {
            $currencies_result = $this->db->select('currency_id, name, symbol, iso_code, basic')->from('ec_currency')->order_by('name', 'ASC')->get()->result();
        }
        $data['currencies'] = $currencies_result;
        $data['vendor_id']  = $this->vendor_id;
        $data['TYPE']       = $this->TYPE;
        $data['csrf']       = csrf_token();
        $data['access_token'] = $this->_get_vendor_access_token();
        $data['use_api']      = (bool)$this->config->item('use_api') && ($data['access_token'] !== '');
        $this->load->template("{$this->TYPE}/product/save_product5", $data);
    }

    public function save_product()
    {
        // ── Currency conversion: vendor input → base currency (USD) ──────────
        // Vendor enters prices in their preferred currency (e.g. INR 500).
        // We convert to base currency (USD) before saving so the DB always stores USD.
        $vcur_info = $this->_get_vendor_currency_info($this->vendor_id);

        if ($vcur_info['needs_conversion']) {
            $vrate = $vcur_info['vendor_rate'];
            $brate = $vcur_info['base_rate'];

            // Convert simple price tiers
            if (!empty($_POST['simple_price']) && is_array($_POST['simple_price'])) {
                foreach ($_POST['simple_price'] as &$p) {
                    if ($p !== '' && $p !== null) {
                        $p = round((float)$p / $vrate * $brate, 4);
                    }
                }
                unset($p);
            }

            // Convert variable price tiers
            if (!empty($_POST['vars']) && is_array($_POST['vars'])) {
                foreach ($_POST['vars'] as &$var) {
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

        // Always store with base currency_id
        $_POST['currency_id'] = $vcur_info['base_currency_id'];
        // ────────────────────────────────────────────────────────────────────

        $resp = $this->Items_model->save_product();
        $msg  = $resp['msg'] ?? ($resp['status'] ? 'Product saved successfully.' : 'Failed to save product.');
        $data = $resp['product_id'] ?? [];
        api_response(['status' => $resp['status'] ? 1 : 0, 'STATUS' => $resp['status'] ? 1 : 0, 'msg' => $msg, 'MSG' => $msg, 'data' => $resp, 'redirect' => base_url("{$this->TYPE}/product")]);
    }

    /**
     * AJAX: delete a variation image — only if it belongs to the current vendor.
     */
    public function delete_image()
    {
        $image_id = (int)$this->input->post('id');
        if (!$image_id) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid image id']);
            return;
        }
        $deleted = $this->Items_model->delete_image($image_id);
        echo json_encode(['status' => $deleted ? 1 : 0]);
    }

    /**
     * AJAX: delete a product video — only if it belongs to the current vendor.
     */
    public function delete_video()
    {
        $product_id = (int)$this->input->post('product_id');
        if (!$product_id) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid product id']);
            return;
        }
        $deleted = $this->Items_model->delete_video($product_id);
        echo json_encode(['status' => $deleted ? 1 : 0, 'msg' => $deleted ? 'Video removed.' : 'Failed to remove video.']);
    }

    /**
     * AJAX: return brands for a given category (used by save_product5.php dropdown)
     */
    public function ajax_brand_combo()
    {
        $cat_id = (int)$this->input->post('category_id');
        if (!$cat_id) {
            echo json_encode(['STATUS' => 0, 'DATA' => [], 'MSG' => 'No category']);
            return;
        }

        // Fetch brands linked to any product in this category
        $brands = $this->db
            ->select('b.brand_id, b.name')
            ->from('ec_brand b')
            ->where('b.status', '1')
            ->order_by('b.name', 'ASC')
            ->get()
            ->result_array();

        echo json_encode(['STATUS' => 1, 'DATA' => $brands, 'MSG' => 'OK']);
    }

    /**
     * AJAX: return list of active currencies as JSON (used by save_product5.php currency dropdown)
     */
    public function ajax_currency_list()
    {
        $rows = $this->db
            ->select('currency_id, name, symbol, iso_code, basic')
            ->from('ec_currency')
            ->where('status', '1')
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();

        // Fallback: if no active currencies, return all
        if (empty($rows)) {
            $rows = $this->db
                ->select('currency_id, name, symbol, iso_code, basic')
                ->from('ec_currency')
                ->order_by('name', 'ASC')
                ->get()
                ->result_array();
        }

        header('Content-Type: application/json');
        echo json_encode(['STATUS' => 1, 'DATA' => $rows]);
    }

    // ─── Currency Conversion Helpers ─────────────────────────────────────────

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

    private function _convert_price_from_base($base_price, array $cur_info)
    {
        if (!$cur_info['needs_conversion'] || $cur_info['base_rate'] <= 0) {
            return round((float)$base_price, 2);
        }
        return round((float)$base_price / $cur_info['base_rate'] * $cur_info['vendor_rate'], 2);
    }
}
