<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

/**
 * VendorCurrency — Vendor API: set / get preferred currency.
 *
 * Routes (add to config/routes.php):
 *   POST|GET api/v1/vendor/set-currency  -> vendor/VendorCurrency/set_currency
 *   GET      api/v1/vendor/currency      -> vendor/VendorCurrency/get_currency
 *
 * The set_currency endpoint:
 *   1. Validates the currency exists and is active.
 *   2. Writes preferred_currency_id to ec_vendor (persists across sessions).
 *   3. Sets $_SESSION['cur'] so the web panel reflects the change immediately.
 */
class VendorCurrency extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Query_model');
    }

    // ── Auth helper ───────────────────────────────────────────────────────────

    /**
     * Verify the Bearer token and return the vendor_id, or 0 on failure.
     * Does NOT send an HTTP response; caller must check the return value.
     */
    private function _get_vendor_id()
    {
        $header = $this->input->get_request_header('Authorization', true);
        if (!$header) {
            $header = $this->input->server('HTTP_AUTHORIZATION');
        }
        $header = trim((string)$header);

        // --- Token-based auth ---
        if (stripos($header, 'Bearer ') === 0) {
            $token  = trim(substr($header, 7));
            $claims = $this->verify_token($token);

            if ($claims
                && isset($claims['sub'])
                && ($claims['type'] ?? '') === 'access'
                && ($claims['role'] ?? '') === 'vendor'
            ) {
                return (int)$claims['sub'];
            }
            return 0; // token present but invalid
        }

        // --- Session-based auth (browser / web panel fallback) ---
        $vend = $this->session->userdata('vendor');
        if (is_array($vend) && !empty($vend['logged_in'])) {
            $vid = (int)($vend['vendor_id'] ?? $vend['login_id'] ?? 0);
            if ($vid > 0) {
                return $vid;
            }
        }

        return 0;
    }

    // ── Endpoints ─────────────────────────────────────────────────────────────

    /**
     * POST api/v1/vendor/set-currency
     * Body: { "currency_id": <int> }
     *
     * Sets the vendor's preferred currency both in the database and the session.
     */
    public function set_currency()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST' && $method !== 'GET') {
            return $this->fail('method_not_allowed', ['Only POST and GET are allowed'], 405);
        }

        // Authenticate
        $vendor_id = $this->_get_vendor_id();
        if ($vendor_id <= 0) {
            return $this->fail('unauthorized', ['Missing or invalid vendor Authorization token'], 401);
        }

        // Parse input
        $payload     = array_merge($this->get_json_input(), $_GET ?: [], $_POST ?: []);
        $currency_id = isset($payload['currency_id']) ? (int)$payload['currency_id'] : 0;

        if ($currency_id <= 0) {
            return $this->fail('validation_error', ['currency_id is required and must be a positive integer'], 422);
        }

        // Verify the currency exists and is active
        $currency = $this->db
            ->select('currency_id, name, iso_code, symbol, rate')
            ->from('ec_currency')
            ->where('currency_id', $currency_id)
            ->where('status', '1')
            ->get()
            ->row();

        if (!$currency) {
            return $this->fail('not_found', ['Currency not found or inactive'], 404);
        }

        // 1. Persist to database
        if ($this->db->field_exists('preferred_currency_id', 'ec_vendor')) {
            $this->db
                ->where('vendor_id', $vendor_id)
                ->update('ec_vendor', ['preferred_currency_id' => $currency_id]);
        }

        // 2. Update the active session so the web panel reflects the change immediately
        $this->session->set_userdata('cur', $currency_id);
        $_SESSION['cur'] = $currency_id;

        return $this->ok(
            ['currency' => $currency],
            'Vendor currency preference saved successfully'
        );
    }

    /**
     * GET api/v1/vendor/currency
     *
     * Returns the vendor's currently stored preferred currency.
     */
    public function get_currency()
    {
        // Authenticate
        $vendor_id = $this->_get_vendor_id();
        if ($vendor_id <= 0) {
            return $this->fail('unauthorized', ['Missing or invalid vendor Authorization token'], 401);
        }

        // Read from DB if the column exists
        $preferred_id = 0;
        if ($this->db->field_exists('preferred_currency_id', 'ec_vendor')) {
            $row = $this->db
                ->select('preferred_currency_id')
                ->from('ec_vendor')
                ->where('vendor_id', $vendor_id)
                ->get()
                ->row();
            $preferred_id = (int)($row->preferred_currency_id ?? 0);
        }

        // Fall back to session
        if ($preferred_id <= 0) {
            $preferred_id = (int)$this->session->userdata('cur');
        }

        // Return full currency details if we have an ID
        $currency = null;
        if ($preferred_id > 0) {
            $currency = $this->db
                ->select('currency_id, name, iso_code, symbol, rate')
                ->from('ec_currency')
                ->where('currency_id', $preferred_id)
                ->where('status', '1')
                ->get()
                ->row();
        }

        return $this->ok(
            ['currency' => $currency, 'currency_id' => $preferred_id],
            'Vendor preferred currency'
        );
    }
}
