<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

/**
 * Earnings
 *
 * POST /api/v1/vendor/earnings
 *
 * Returns vendor earnings in DataTables-compatible format.
 */
class Earnings extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');
    }

    // ─── Auth Helpers (inlined to avoid VendorBase dependency) ────────────────

    protected function _get_bearer_token()
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

    protected function _require_vendor()
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

        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id']  = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? TRUE;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');

        return $vendor_id;
    }

    public function index()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        // Determine which currency the vendor has selected and its rate.
        $display_cur_id = (int)$this->input->get_post('cur') ?: (isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0);
        $all_currencies = $this->db
            ->select('currency_id, symbol, iso_code, rate, basic')
            ->from('ec_currency')
            ->where('status', '1')
            ->get()->result();

        $display_rate   = 1.0;
        $display_symbol = '$';
        foreach ($all_currencies as $c) {
            if ((int)$c->basic === 1) {
                $display_rate   = (float)$c->rate ?: 1.0;
                $display_symbol = html_entity_decode($c->symbol);
                break;
            }
        }
        if ($display_cur_id > 0) {
            foreach ($all_currencies as $c) {
                if ((int)$c->currency_id === $display_cur_id) {
                    $display_rate   = (float)$c->rate ?: 1.0;
                    $display_symbol = html_entity_decode($c->symbol);
                    break;
                }
            }
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $length  = isset($payload['length']) ? (int)$payload['length'] : 10;
        $start   = isset($payload['start'])  ? (int)$payload['start']  : 0;
        $draw    = isset($payload['draw'])   ? $payload['draw']         : '';

        // Vendor commission rate
        $vendor_obj      = $this->Query_model->get_data_obj('ec_vendor', ['vendor_id' => $vendor_id]);
        $global_commission = isset($vendor_obj->commission) ? $vendor_obj->commission : 0;

        // Fetch order items (no JOIN on ec_earnings to avoid CI Active Record
        // escaping DATE() calls in JOIN ON clauses which causes HTTP 500)
        $this->db->select('eoi.id, eoi.order_id, eoi.login_id, eoi.product_name, eoi.qty, eoi.subtotal, eoi.currency_rate, eoi.date_added, eo.order_number, eo.payment_method, eo.date_added as order_date_added');
        $this->db->from('ec_order_items as eoi');
        $this->db->join('ec_orders as eo', 'eo.id = eoi.order_id', 'left');
        $this->db->where('eoi.login_id', $vendor_id);
        $this->db->limit($length, $start);
        $this->db->order_by('eoi.id', 'DESC');
        $list = $this->db->get()->result();

        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $total_records = $this->db->count_all_results();

        // Fetch all earning periods for this vendor to map paid status in PHP
        $earnings_periods = $this->db
            ->select('earning_id, from_date, to_date, paid_status, paid_type, paid_date')
            ->from('ec_earnings')
            ->where('vendor_id', $vendor_id)
            ->get()->result();

        // Helper: find whether a given order date falls in a paid period
        $get_earning_for_date = function($date_str) use ($earnings_periods) {
            if (!$date_str) return null;
            $ts = strtotime($date_str);
            foreach ($earnings_periods as $ep) {
                $from_ts = strtotime($ep->from_date);
                $to_ts   = strtotime($ep->to_date);
                if ($ts >= $from_ts && $ts <= $to_ts) {
                    return $ep;
                }
            }
            return null;
        };

        $data = [];
        foreach ($list as $obj) {
            $commission_pct = $global_commission;
            $raw_subtotal   = isset($obj->subtotal) ? (float)$obj->subtotal : 0;

            // Convert to base amount first
            $item_rate = (float)($obj->currency_rate ?? 1.0) ?: 1.0;
            $base_subtotal = $raw_subtotal / $item_rate;

            // Convert to display currency
            $total_earning = $base_subtotal * $display_rate;

            $admin_share    = ($total_earning * $commission_pct) / 100;
            $vendor_share   = $total_earning - $admin_share;

            $row = [];
            $row['order_uid']      = isset($obj->order_number) && $obj->order_number
                ? $obj->order_number
                : (isset($obj->order_id) ? $obj->order_id : (isset($obj->id) ? $obj->id : ''));

            $row['total_earning']  = $display_symbol . number_format($total_earning, 2);
            $row['commission_val'] = $display_symbol . number_format($admin_share, 2);
            $row['vendor_earning'] = $display_symbol . number_format($vendor_share, 2);
            $row['commission_pct'] = $commission_pct . '%';

            // Look up admin-to-vendor payment status from ec_earnings by matching order date
            $order_date = isset($obj->date_added) && $obj->date_added ? $obj->date_added
                        : (isset($obj->order_date_added) && $obj->order_date_added ? $obj->order_date_added : '');
            $ep      = $get_earning_for_date($order_date);
            $is_paid = ($ep && (string)$ep->paid_status === '1');

            // payment_mode: show the actual transfer method (cash/bank/cheque) when paid
            $row['payment_mode'] = ($is_paid && isset($ep->paid_type) && $ep->paid_type)
                ? ucfirst((string)$ep->paid_type)
                : '-';

            // paid_date: use the date admin marked the payment, not the order date
            $row['paid_date'] = ($is_paid && isset($ep->paid_date) && $ep->paid_date && $ep->paid_date !== '0000-00-00 00:00:00')
                ? date('d-M-Y', strtotime($ep->paid_date))
                : '-';

            $row['status']       = $is_paid ? 'Paid' : 'Unpaid';
            $row['status_badge'] = $is_paid ? 'badge-success' : 'badge-warning';
            $row['status_html']  = '<span class="badge ' . $row['status_badge'] . '">' . $row['status'] . '</span>';

            $data[] = $row;
        }

        $output = [
            'draw'            => $draw,
            'recordsTotal'    => (int)$total_records,
            'recordsFiltered' => (int)$total_records,
            'data'            => $data,
        ];

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode($output));
    }
}
