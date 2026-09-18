<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

/**
 * Dashboard
 *
 * GET /api/v1/vendor/dashboard
 *
 * Returns summary metrics for the authenticated vendor's dashboard.
 */
class Dashboard extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');
    }

    // ─── Auth Helpers (inlined from VendorBase) ──────────────────────────────

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

        // Merge session so browser session elements are not wiped
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
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->load->model('Dashboard_model');
        $this->load->model('Order_model');

        // ---------------------------------------------------------------
        // CURRENCY RESOLUTION
        // Currency comes exclusively from the session (set via /api/v1/vendor/set-currency).
        // Never read from URL params — keeps dashboard URL clean (/vendor/dashboard).
        // ---------------------------------------------------------------
        $display_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;

        // If session has no currency yet, restore from vendor's preferred_currency_id in DB
        if ($display_cur_id <= 0 && $this->db->field_exists('preferred_currency_id', 'ec_vendor')) {
            $vcurr = $this->db
                ->select('preferred_currency_id')
                ->from('ec_vendor')
                ->where('vendor_id', $vendor_id)
                ->get()
                ->row();
            $saved_id = (int)($vcurr->preferred_currency_id ?? 0);
            if ($saved_id > 0) {
                $display_cur_id = $saved_id;
                // Persist back to session so subsequent requests don't need DB hit
                $this->session->set_userdata('cur', $display_cur_id);
                $_SESSION['cur'] = $display_cur_id;
            }
        }

        // Fetch all active currencies in one query
        $all_currencies = $this->db
            ->select('currency_id, symbol, iso_code, rate, basic')
            ->from('ec_currency')
            ->where('status', '1')
            ->get()->result();

        $display_rate   = 1.0;
        $display_symbol = '$';
        $display_code   = 'USD';

        // Default to base currency first
        foreach ($all_currencies as $c) {
            if ((int)$c->basic === 1) {
                $display_rate   = (float)$c->rate ?: 1.0;
                $display_symbol = html_entity_decode($c->symbol);
                $display_code   = $c->iso_code;
                break;
            }
        }
        // Override with vendor's selected display currency if set
        if ($display_cur_id > 0) {
            foreach ($all_currencies as $c) {
                if ((int)$c->currency_id === $display_cur_id) {
                    $display_rate   = (float)$c->rate ?: 1.0;
                    $display_symbol = html_entity_decode($c->symbol);
                    $display_code   = $c->iso_code;
                    break;
                }
            }
        }

        // SQL multiplier for currency conversion in queries
        $display_rate_sql = (float)$display_rate;

        // Dynamic Product Count
        $product_table = $this->Dashboard_model->_product_table();
        $vendor_col    = $this->Dashboard_model->_product_vendor_column($product_table);
        $total_products = 0;
        if ($vendor_col) {
            $total_products = $this->Query_model->count_all($product_table, [$vendor_col => $vendor_id]);
        }

        // Total Orders (scoped to vendor's items)
        $orderCounts  = $this->Order_model->item_count_vendor_wise(['vendor_id' => $vendor_id]);
        $total_orders = $orderCounts['total_items'] ?? 0;

        // ---------------------------------------------------------------
        // Total Earnings (currency-aware)
        // Formula: subtotal / IFNULL(currency_rate,1) => base amount
        //          base amount * display_rate => display amount
        // ---------------------------------------------------------------
        $earn_row = $this->db
            ->select("ROUND(SUM(oi.subtotal / IFNULL(oi.currency_rate, 1)) * {$display_rate_sql}, 2) AS total", false)
            ->from('ec_order_items oi')
            ->where('oi.login_id', $vendor_id)
            ->get()->row();
        $total_earnings = (float)($earn_row->total ?? 0.0);

        // ---------------------------------------------------------------
        // 7-day sales graph (currency-aware)
        // ---------------------------------------------------------------
        $graph_rows = $this->db
            ->select("DATE_FORMAT(date_added, '%a') as days, ROUND(SUM(subtotal / IFNULL(currency_rate, 1)) * {$display_rate_sql}, 2) as sale_value", false)
            ->from('ec_order_items')
            ->where('login_id', $vendor_id)
            ->where('date_added >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->group_by("DATE_FORMAT(date_added, '%a')")
            ->get()->result();

        $salevalue_arr = [];
        foreach ($graph_rows as $v) {
            $salevalue_arr[] = [
                'days'       => $v->days,
                'sale_value' => (float)$v->sale_value,
            ];
        }

        // Orders trend (vs last week) — count only, no currency needed
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $orders_this_week = $this->db->count_all_results();

        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-14 days')));
        $this->db->where('date_added <', date('Y-m-d H:i:s', strtotime('-7 days')));
        $orders_last_week = $this->db->count_all_results();

        $orders_trend_pct = $orders_last_week > 0
            ? round((($orders_this_week - $orders_last_week) / $orders_last_week) * 100)
            : ($orders_this_week > 0 ? 100 : 0);

        // ---------------------------------------------------------------
        // Sales value trend (currency-aware)
        // ---------------------------------------------------------------
        $this_month_row = $this->db
            ->select("ROUND(SUM(subtotal / IFNULL(currency_rate, 1)) * {$display_rate_sql}, 2) as total", false)
            ->from('ec_order_items')
            ->where('login_id', $vendor_id)
            ->where('date_added >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->get()->row();
        $sales_this_month = (float)($this_month_row->total ?? 0);

        $last_month_row = $this->db
            ->select("ROUND(SUM(subtotal / IFNULL(currency_rate, 1)) * {$display_rate_sql}, 2) as total", false)
            ->from('ec_order_items')
            ->where('login_id', $vendor_id)
            ->where('date_added >=', date('Y-m-d H:i:s', strtotime('-60 days')))
            ->where('date_added <', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->get()->row();
        $sales_last_month = (float)($last_month_row->total ?? 0);

        $sales_trend_pct = $sales_last_month > 0
            ? round((($sales_this_month - $sales_last_month) / $sales_last_month) * 100)
            : ($sales_this_month > 0 ? 100 : 0);

        // ---------------------------------------------------------------
        // Next payout date & estimation (currency-aware)
        // ---------------------------------------------------------------
        $today_day = (int)date('j');
        $next_payout_date = $today_day <= 15
            ? date('M 15')
            : date('M 15', strtotime('next month'));

        $vendor_obj       = $this->Query_model->get_data_obj('ec_vendor', ['vendor_id' => $vendor_id]);
        $commission_pct   = isset($vendor_obj->commission) ? (float)$vendor_obj->commission : 10.0;

        $cur_month_row = $this->db
            ->select("ROUND(SUM(subtotal / IFNULL(currency_rate, 1)) * {$display_rate_sql}, 2) as total", false)
            ->from('ec_order_items')
            ->where('login_id', $vendor_id)
            ->where('MONTH(date_added)', date('n'))
            ->where('YEAR(date_added)', date('Y'))
            ->get()->row();
        $current_month_sales     = (float)($cur_month_row->total ?? 0);
        $estimated_payout_amount = $current_month_sales * (100 - $commission_pct) / 100;
        $estimated_payout        = number_format($estimated_payout_amount, 2);

        // Action required counts
        $this->db->from('ec_order_items as eoi');
        $this->db->join('ec_orders as eo', 'eo.id = eoi.order_id', 'left');
        $this->db->where('eoi.login_id', $vendor_id);
        $this->db->group_start()
            ->where('eo.payment_status IS NULL')
            ->or_where('LOWER(eo.payment_status) !=', 'paid')
            ->group_end();
        $unpaid_invoices_count = $this->db->count_all_results();

        $out_of_stock_count = 0;
        if ($vendor_col) {
            $this->db->from($product_table);
            $this->db->where($vendor_col, $vendor_id);
            $this->db->where('stock <=', 0);
            $out_of_stock_count = $this->db->count_all_results();
        }

        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('(oi.login_id = ' . (int)$vendor_id . ' OR np.vendor_id = ' . (int)$vendor_id . ')', null, false);
        $this->db->group_start()
            ->where('r.status', 'requested')
            ->or_where('r.status', 'pending')
            ->or_where('r.status', 'initiated')
            ->group_end();
        $returns_pending_count = $this->db->count_all_results();

        // Vendor health score
        $this->db->from('ec_order_items'); $this->db->where('login_id', $vendor_id); $this->db->where('status', 10);
        $delivered_count = $this->db->count_all_results();

        $this->db->from('ec_order_items'); $this->db->where('login_id', $vendor_id); $this->db->where('status', 3);
        $shipped_count = $this->db->count_all_results();

        $this->db->from('ec_order_items'); $this->db->where('login_id', $vendor_id); $this->db->where_in('status', [5, 17]);
        $cancelled_count = $this->db->count_all_results();

        $total_for_score = $delivered_count + $shipped_count + $cancelled_count;
        $health_score = $total_for_score > 0
            ? round((($delivered_count + $shipped_count) / $total_for_score) * 100)
            : 100;

        if ($health_score >= 90) {
            $health_label = 'Excellent Performance';
            $health_color = 'var(--success)';
            $health_desc  = 'Your store metrics are in the top 5%';
        } elseif ($health_score >= 70) {
            $health_label = 'Good Performance';
            $health_color = 'var(--primary)';
            $health_desc  = 'Keep up the good work to maintain high scores';
        } else {
            $health_label = 'Needs Attention';
            $health_color = 'var(--danger)';
            $health_desc  = 'Focus on processing orders faster to reduce cancellations';
        }

        return $this->ok([
            'total_products'        => $total_products,
            'total_orders'          => $total_orders,
            'total_earnings'        => $total_earnings,
            'orders_trend_pct'      => $orders_trend_pct,
            'sales_trend_pct'       => $sales_trend_pct,
            'next_payout_date'      => $next_payout_date,
            'estimated_payout'      => $estimated_payout,
            'unpaid_invoices_count' => $unpaid_invoices_count,
            'out_of_stock_count'    => $out_of_stock_count,
            'returns_pending_count' => $returns_pending_count,
            'health_score'          => $health_score,
            'health_label'          => $health_label,
            'health_color'          => $health_color,
            'health_desc'           => $health_desc,
            'salevalue_past7days'   => $salevalue_arr,
            // Currency context so the frontend knows which symbol to display
            'currency_symbol'       => $display_symbol,
            'currency_code'         => $display_code,
            'currency_id'           => $display_cur_id > 0 ? $display_cur_id : null,
        ], 'success');
    }
}
