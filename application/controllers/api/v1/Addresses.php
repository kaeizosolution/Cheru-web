<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Addresses extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');
    }

    private function _require_customer()
    {
        $token = $this->get_bearer_token();
        if ($token === '') {
            $customer = $this->session->userdata('customer');
            $customer_id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
            if ($customer_id > 0) {
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
            $this->fail('unauthorized', ['Please login to continue shopping'], 401);
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

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $rows = $this->Query_model->get_data('ec_shipping_address', [
            'customer_id' => $customer_id,
            'status' => '1'
        ], [
            'shipping_address_id' => 'ASC'
        ]);

        $addresses = [];
        if ($rows) {
            foreach ($rows as $r) {
                $addresses[] = [
                    'shipping_address_id' => (int)($r->shipping_address_id ?? 0),
                    'fullname' => $r->fullname ?? null,
                    'mobile' => $r->mobile ?? null,
                    'address_1' => $r->address_1 ?? null,
                    'address_2' => $r->address_2 ?? null,
                    'city' => $r->city ?? null,
                    'state' => $r->state ?? null,
                    'postcode' => $r->postcode ?? null,
                    'address_type' => $r->address_type ?? null,
                    'default_address' => (int)($r->default_address ?? 0),
                ];
            }
        }

        return $this->ok(['addresses' => $addresses], 'success');
    }

    public function create()
    {
        if (!$this->require_post()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

        $insert = [
            'customer_id' => $customer_id,
            'fullname' => isset($payload['fullname']) ? trim((string)$payload['fullname']) : '',
            'address_1' => isset($payload['address_1']) ? trim((string)$payload['address_1']) : '',
            'address_2' => isset($payload['address_2']) ? trim((string)$payload['address_2']) : '',
            'city' => isset($payload['city']) ? trim((string)$payload['city']) : '',
            'state' => isset($payload['state']) ? trim((string)$payload['state']) : '',
            'mobile' => isset($payload['mobile']) ? trim((string)$payload['mobile']) : '',
            'postcode' => isset($payload['postcode']) ? trim((string)$payload['postcode']) : '',
            'address_type' => isset($payload['address_type']) ? trim((string)$payload['address_type']) : 'Home',
            'status' => '1'
        ];

        $errors = [];
        if ($insert['fullname'] === '') $errors[] = 'fullname is required';
        if ($insert['address_1'] === '') $errors[] = 'address_1 is required';
        if ($insert['city'] === '') $errors[] = 'city is required';
        if ($insert['mobile'] === '') $errors[] = 'mobile is required';
        
        if ($errors) {
            return $this->fail('validation_error', $errors, 422);
        }

        // Check if any addresses exist to set default
        $count = (int)$this->Query_model->count_all('ec_shipping_address', [
            'customer_id' => $customer_id,
            'status' => '1'
        ]);

        $is_default = (isset($payload['default_address']) && (int)$payload['default_address'] === 1) || $count === 0;

        if ($is_default) {
            $this->Query_model->update_data('ec_shipping_address', ['default_address' => '0'], ['customer_id' => $customer_id]);
            $insert['default_address'] = '1';
        } else {
            $insert['default_address'] = '0';
        }

        $new_id = $this->Query_model->insert_data('ec_shipping_address', $insert);

        if (!$new_id) {
            return $this->fail('server_error', ['Failed to add address'], 500);
        }

        return $this->ok(['shipping_address_id' => (int)$new_id], 'Address added successfully');
    }

    public function update($id = null)
    {
        $method = strtoupper((string)$this->input->method());
        if (!in_array($method, ['PUT', 'POST'], true)) {
            return $this->fail('method_not_allowed', ['Only PUT or POST is allowed'], 405);
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

        $address_id = (int)$id;
        if ($address_id <= 0) {
            $address_id = (int)($payload['shipping_address_id'] ?? ($payload['address_id'] ?? ($payload['id'] ?? 0)));
        }
        if ($address_id <= 0) {
            return $this->fail('validation_error', ['shipping_address_id is required'], 422);
        }

        $address = $this->Query_model->get_data_obj('ec_shipping_address', [
            'shipping_address_id' => $address_id,
            'customer_id' => $customer_id,
            'status' => '1'
        ]);

        if (!$address) {
            return $this->fail('not_found', ['Address not found'], 404);
        }

        $allowed = ['fullname', 'address_1', 'address_2', 'city', 'state', 'mobile', 'postcode', 'address_type'];
        $update = [];
        foreach ($allowed as $key) {
            if (isset($payload[$key])) {
                $update[$key] = trim((string)$payload[$key]);
            }
        }

        if (isset($payload['default_address']) && (int)$payload['default_address'] === 1) {
            $this->Query_model->update_data('ec_shipping_address', ['default_address' => '0'], ['customer_id' => $customer_id]);
            $update['default_address'] = '1';
        }

        if (!empty($update)) {
            $this->Query_model->update_data('ec_shipping_address', $update, ['shipping_address_id' => $address_id]);
        }

        return $this->ok(['shipping_address_id' => $address_id], 'Address updated successfully');
    }

    public function delete($id = null)
    {
        $method = strtoupper((string)$this->input->method());
        if (!in_array($method, ['DELETE', 'POST'], true)) {
            return $this->fail('method_not_allowed', ['Only DELETE is allowed'], 405);
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

        $address_id = (int)$id;
        if ($address_id <= 0) {
            $address_id = (int)($payload['shipping_address_id'] ?? ($payload['address_id'] ?? ($payload['id'] ?? 0)));
        }
        if ($address_id <= 0) {
            return $this->fail('validation_error', ['shipping_address_id is required'], 422);
        }

        $address = $this->Query_model->get_data_obj('ec_shipping_address', [
            'shipping_address_id' => $address_id,
            'customer_id' => $customer_id,
            'status' => '1'
        ]);

        if (!$address) {
            return $this->fail('not_found', ['Address not found'], 404);
        }

        $this->Query_model->update_data('ec_shipping_address', [
            'status' => '0',
            'default_address' => '0'
        ], ['shipping_address_id' => $address_id]);

        // If customer still has addresses and none is default, make one default automatically
        $has_default = $this->db
            ->from('ec_shipping_address')
            ->where('customer_id', $customer_id)
            ->where('status', '1')
            ->where('default_address', '1')
            ->count_all_results();

        if ((int)$has_default === 0) {
            $next_default = $this->db
                ->select('shipping_address_id')
                ->from('ec_shipping_address')
                ->where('customer_id', $customer_id)
                ->where('status', '1')
                ->order_by('shipping_address_id', 'ASC')
                ->limit(1)
                ->get()
                ->row();

            if (!empty($next_default) && !empty($next_default->shipping_address_id)) {
                $this->Query_model->update_data('ec_shipping_address', ['default_address' => '0'], ['customer_id' => $customer_id]);
                $this->Query_model->update_data('ec_shipping_address', ['default_address' => '1'], ['shipping_address_id' => $next_default->shipping_address_id]);
            }
        }

        return $this->ok(['shipping_address_id' => $address_id], 'Address removed successfully');
    }

    public function set_default($id = null)
    {
        if (!$this->require_post()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

        $address_id = (int)$id;
        if ($address_id <= 0) {
            $address_id = (int)($payload['shipping_address_id'] ?? ($payload['address_id'] ?? ($payload['id'] ?? 0)));
        }
        if ($address_id <= 0) {
            return $this->fail('validation_error', ['shipping_address_id is required'], 422);
        }

        $address = $this->Query_model->get_data_obj('ec_shipping_address', [
            'shipping_address_id' => $address_id,
            'customer_id' => $customer_id,
            'status' => '1'
        ]);

        if (!$address) {
            return $this->fail('not_found', ['Address not found'], 404);
        }

        $this->Query_model->update_data('ec_shipping_address', ['default_address' => '0'], ['customer_id' => $customer_id]);
        $this->Query_model->update_data('ec_shipping_address', ['default_address' => '1'], ['shipping_address_id' => $address_id]);

        return $this->ok(['shipping_address_id' => $address_id], 'Default address updated');
    }
}
