<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CurrencySwitcher - Vendor Panel
 *
 * Switches the active currency for the current vendor session.
 * - Sets $_SESSION['cur'] = $currency_id
 * - For logged-in vendors, also persists preferred_currency_id to ec_vendor table
 *
 * URL pattern: /vendor/currency-switcher/{currency_id}
 */
class CurrencySwitcher extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->database();
    }

    /**
     * Switch the active currency by ID.
     *
     * @param int|string $currency_id  The ID of the currency to switch to.
     */
    public function switchCurrency($currency_id = '') {

        // Validate: must be a positive integer
        $currency_id = (int)$currency_id;
        if ($currency_id <= 0) {
            $currency_id = 1;
        }

        // Verify currency exists and is active
        $row = $this->db
            ->select('currency_id')
            ->from('ec_currency')
            ->where('currency_id', $currency_id)
            ->where('status', '1')
            ->get()->row();

        if (!$row) {
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('vendor/dashboard'));
            return;
        }

        // 1. Store selected currency in session
        $this->session->set_userdata('cur', $currency_id);
        $_SESSION['cur'] = $currency_id;

        // 2. Persist to ec_vendor table so preference survives logout/login
        $vend = $this->session->userdata('vendor');
        $vendor_id = 0;
        if (is_array($vend)) {
            $vendor_id = (int)($vend['vendor_id'] ?? $vend['login_id'] ?? 0);
        }
        if ($vendor_id > 0 && $this->db->field_exists('preferred_currency_id', 'ec_vendor')) {
            $this->db
                ->where('vendor_id', $vendor_id)
                ->update('ec_vendor', ['preferred_currency_id' => $currency_id]);
        }

        // 3. Redirect back to previous page
        redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : base_url('vendor/dashboard'));
    }
}
?>
