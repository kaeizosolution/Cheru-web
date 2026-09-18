<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Currency extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
    }

    // NOTE: method is named set_currency() not switch() because 'switch' is a PHP reserved keyword.
    public function set_currency()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST' && $method !== 'GET') {
            return $this->fail('method_not_allowed', ['Only POST and GET are allowed'], 405);
        }

        $payload = array_merge($this->get_json_input(), $_GET ?: [], $_POST ?: []);
        $currency_id = isset($payload['currency_id']) ? (int)$payload['currency_id'] : 0;
        if ($currency_id <= 0) {
            return $this->fail('validation_error', ['currency_id is required and must be positive'], 422);
        }

        // Verify currency exists and is active
        $row = $this->db
            ->select('currency_id, name, iso_code, symbol, rate')
            ->from('ec_currency')
            ->where('currency_id', $currency_id)
            ->where('status', '1')
            ->get()->row();

        if (!$row) {
            return $this->fail('not_found', ['Currency not found or inactive'], 404);
        }

        // Set session currency
        $this->session->set_userdata('cur', $currency_id);
        $_SESSION['cur'] = $currency_id;

        // ── Persist to ec_customer if a customer is identified ─────────────
        // Resolve customer_id from Bearer token first, then from session.
        $token = $this->get_bearer_token();
        $customer_id = 0;
        if ($token !== '') {
            $claims = $this->verify_token($token);
            if ($claims && isset($claims['sub']) && ($claims['role'] ?? '') === 'customer') {
                $customer_id = (int)$claims['sub'];
            }
        }
        // Fallback: session-based (browser / web panel context)
        if ($customer_id <= 0) {
            $customer = $this->session->userdata('customer');
            if (is_array($customer)) {
                $customer_id = (int)($customer['login_id'] ?? $customer['customer_id'] ?? 0);
            }
        }

        if ($customer_id > 0) {
            // Auto-create the preferred_currency_id column if it doesn't exist yet,
            // so the update is never silently skipped on fresh installations.
            if (!$this->db->field_exists('preferred_currency_id', 'ec_customer')) {
                $this->db->query(
                    'ALTER TABLE `ec_customer` ADD COLUMN `preferred_currency_id` INT(11) NULL DEFAULT NULL'
                );
            }
            $this->db
                ->where('customer_id', $customer_id)
                ->update('ec_customer', ['preferred_currency_id' => $currency_id]);
        }

        return $this->ok(['currency' => $row], 'Currency switched successfully');
    }
}
