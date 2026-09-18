<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Vendor extends API_Controller
{
    private $refresh_ttl = 2592000; // 30 days
    private $access_ttl = 900; // 15 minutes

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
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

    private function _token_store_enabled()
    {
        return isset($this->db) && $this->db->table_exists('ec_api_tokens');
    }

    private function _store_refresh_token($vendor_id, $refresh_token)
    {
        if (!$this->_token_store_enabled()) {
            return;
        }

        $hash = hash('sha256', $refresh_token);
        $data = [
            'user_type' => 'vendor',
            'user_id' => (int)$vendor_id,
            'token_hash' => $hash,
            'expires_at' => date('Y-m-d H:i:s', time() + $this->refresh_ttl),
            'revoked' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('ec_api_tokens', $data);
    }

    private function _is_refresh_token_valid($vendor_id, $refresh_token)
    {
        if (!$this->_token_store_enabled()) {
            return true;
        }

        $hash = hash('sha256', $refresh_token);
        $row = $this->db
            ->from('ec_api_tokens')
            ->where('user_type', 'vendor')
            ->where('user_id', (int)$vendor_id)
            ->where('token_hash', $hash)
            ->where('revoked', 0)
            ->get()->row();
        return (bool)$row;
    }

    private function _revoke_refresh_token($vendor_id, $refresh_token)
    {
        if (!$this->_token_store_enabled()) {
            return;
        }

        $hash = hash('sha256', $refresh_token);
        $this->db
            ->where('user_type', 'vendor')
            ->where('user_id', (int)$vendor_id)
            ->where('token_hash', $hash)
            ->update('ec_api_tokens', ['revoked' => 1]);
    }

    // Login, Register, Refresh Token, and Logout endpoints have been moved to VendorAuth.php


    public function dashboard()
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

        // Dynamic Product Count
        $product_table = $this->Dashboard_model->_product_table();
        $vendor_col = $this->Dashboard_model->_product_vendor_column($product_table);
        $total_products = 0;
        if ($vendor_col) {
            $total_products = $this->Query_model->count_all($product_table, array($vendor_col => $vendor_id));
        }

        // Total Orders (scoped to vendor's items)
        $orderCounts = $this->Order_model->item_count_vendor_wise(array('vendor_id' => $vendor_id));
        $total_orders = $orderCounts['total_items'] ?? 0;

        // Total Earnings / Sales Value
        $earn_res = $this->Dashboard_model->total_income(null);
        $total_earnings = (float)($earn_res->total ?? 0.0);

        // Fetch 7-day sales graph data
        $salevalue_past7days = $this->Dashboard_model->salevalue_past7days_graph($vendor_id);
        $salevalue_arr = [];
        if ($salevalue_past7days) {
            foreach ($salevalue_past7days as $v) {
                $salevalue_arr[] = [
                    'days' => $v->days,
                    'sale_value' => (float)$v->sale_value
                ];
            }
        }

        // Orders Trend Calculation (vs Last Week)
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $orders_this_week = $this->db->count_all_results();

        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-14 days')));
        $this->db->where('date_added <', date('Y-m-d H:i:s', strtotime('-7 days')));
        $orders_last_week = $this->db->count_all_results();

        if ($orders_last_week > 0) {
            $orders_trend_pct = round((($orders_this_week - $orders_last_week) / $orders_last_week) * 100);
        } else {
            $orders_trend_pct = $orders_this_week > 0 ? 100 : 0;
        }

        // Sales Value Trend Calculation (vs Last Month)
        $this->db->select('SUM(subtotal) as total');
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-30 days')));
        $sales_this_month = (float)($this->db->get()->row()->total ?? 0);

        $this->db->select('SUM(subtotal) as total');
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-60 days')));
        $this->db->where('date_added <', date('Y-m-d H:i:s', strtotime('-30 days')));
        $sales_last_month = (float)($this->db->get()->row()->total ?? 0);

        if ($sales_last_month > 0) {
            $sales_trend_pct = round((($sales_this_month - $sales_last_month) / $sales_last_month) * 100);
        } else {
            $sales_trend_pct = $sales_this_month > 0 ? 100 : 0;
        }

        // Next Payout Date & Estimation
        $today_day = (int)date('j');
        if ($today_day <= 15) {
            $next_payout_date = date('M 15');
        } else {
            $next_payout_date = date('M 15', strtotime('next month'));
        }

        $vendor_obj = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
        $commission_pct = isset($vendor_obj->commission) ? (float)$vendor_obj->commission : 10.0;

        $this->db->select('SUM(subtotal) as total');
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('MONTH(date_added)', date('n'));
        $this->db->where('YEAR(date_added)', date('Y'));
        $current_month_sales = (float)($this->db->get()->row()->total ?? 0);

        $estimated_payout_amount = $current_month_sales * (100 - $commission_pct) / 100;
        $estimated_payout = number_format($estimated_payout_amount, 2);

        // Action Required Counts
        // 1. Unpaid Invoices
        $this->db->from('ec_order_items as eoi');
        $this->db->join('ec_orders as eo', 'eo.id = eoi.order_id', 'left');
        $this->db->where('eoi.login_id', $vendor_id);
        $this->db->group_start()
            ->where('eo.payment_status IS NULL')
            ->or_where('LOWER(eo.payment_status) !=', 'paid')
        ->group_end();
        $unpaid_invoices_count = $this->db->count_all_results();

        // 2. Out of Stock
        $out_of_stock_count = 0;
        if ($vendor_col) {
            $this->db->from($product_table);
            $this->db->where($vendor_col, $vendor_id);
            $this->db->where('stock <=', 0);
            $out_of_stock_count = $this->db->count_all_results();
        }

        // 3. Returns Pending
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('(oi.login_id = '.(int)$vendor_id.' OR np.vendor_id = '.(int)$vendor_id.')', null, false);
        $this->db->group_start()
            ->where('r.status', 'requested')
            ->or_where('r.status', 'pending')
            ->or_where('r.status', 'initiated')
        ->group_end();
        $returns_pending_count = $this->db->count_all_results();

        // Vendor Health Score (Fulfillment Ratio)
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('status', 10); // Delivered
        $delivered_count = $this->db->count_all_results();

        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('status', 3); // Shipped
        $shipped_count = $this->db->count_all_results();

        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where_in('status', array(5, 17)); // Cancelled
        $cancelled_count = $this->db->count_all_results();

        $total_for_score = $delivered_count + $shipped_count + $cancelled_count;
        if ($total_for_score > 0) {
            $health_score = round((($delivered_count + $shipped_count) / $total_for_score) * 100);
        } else {
            $health_score = 100;
        }

        if ($health_score >= 90) {
            $health_label = 'Excellent Performance';
            $health_color = 'var(--success)';
            $health_desc = 'Your store metrics are in the top 5%';
        } elseif ($health_score >= 70) {
            $health_label = 'Good Performance';
            $health_color = 'var(--primary)';
            $health_desc = 'Keep up the good work to maintain high scores';
        } else {
            $health_label = 'Needs Attention';
            $health_color = 'var(--danger)';
            $health_desc = 'Focus on processing orders faster to reduce cancellations';
        }

        return $this->ok([
            'total_products' => $total_products,
            'total_orders' => $total_orders,
            'total_earnings' => $total_earnings,
            'orders_trend_pct' => $orders_trend_pct,
            'sales_trend_pct' => $sales_trend_pct,
            'next_payout_date' => $next_payout_date,
            'estimated_payout' => $estimated_payout,
            'unpaid_invoices_count' => $unpaid_invoices_count,
            'out_of_stock_count' => $out_of_stock_count,
            'returns_pending_count' => $returns_pending_count,
            'health_score' => $health_score,
            'health_label' => $health_label,
            'health_color' => $health_color,
            'health_desc' => $health_desc,
            'salevalue_past7days' => $salevalue_arr,
        ], 'success');
    }

    public function earnings()
    {
        // 1. JWT Authentication
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        // Get Datatables parameters from POST or raw input stream
        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $length = isset($payload['length']) ? (int)$payload['length'] : 10;
        $start = isset($payload['start']) ? (int)$payload['start'] : 0;
        $draw = isset($payload['draw']) ? $payload['draw'] : '';

        // Get This Vendor's Commission Rate
        $vendor_obj = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
        $global_commission = isset($vendor_obj->commission) ? $vendor_obj->commission : 0;

        // Query Builder
        $this->db->select('eoi.*, eo.order_number, eo.payment_method, eo.payment_status, eo.date_added as order_date_added, eo.last_updated as order_last_updated');
        $this->db->from('ec_order_items as eoi');
        $this->db->join('ec_orders as eo', 'eo.id = eoi.order_id', 'left');
        $this->db->where('eoi.login_id', $vendor_id);
        
        // Pagination & Sorting
        $this->db->limit($length, $start);
        $this->db->order_by('eoi.id', 'DESC');

        $query = $this->db->get();
        $list = $query->result();

        // Count Total Records
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $total_records = $this->db->count_all_results();

        $data = array();

        foreach ($list as $obj) {
            $row = array();

            $commission_pct = $global_commission;
            $total_earning = isset($obj->subtotal) ? $obj->subtotal : 0;

            $admin_share   = ($total_earning * $commission_pct) / 100;
            $vendor_share  = $total_earning - $admin_share;

            $row['order_uid']      = isset($obj->order_number) && $obj->order_number ? $obj->order_number : (isset($obj->order_id) ? $obj->order_id : (isset($obj->id) ? $obj->id : ''));
            
            // Format with $ currency symbol
            $row['total_earning']  = '$' . number_format($total_earning, 2);
            $row['commission_val'] = '$' . number_format($admin_share, 2);
            $row['vendor_earning'] = '$' . number_format($vendor_share, 2);
            $row['commission_pct'] = $commission_pct . '%';

            $row['payment_mode'] = isset($obj->payment_method) && $obj->payment_method ? $obj->payment_method : '-';

            $is_paid = (isset($obj->payment_status) && strtolower((string)$obj->payment_status) === 'paid');

            $db_date = '';
            if(isset($obj->order_last_updated) && $obj->order_last_updated && $obj->order_last_updated != '0000-00-00 00:00:00'){
                $db_date = $obj->order_last_updated;
            }elseif(isset($obj->order_date_added) && $obj->order_date_added && $obj->order_date_added != '0000-00-00 00:00:00'){
                $db_date = $obj->order_date_added;
            }
            $row['paid_date'] = ($is_paid && $db_date) ? date('d-M-Y', strtotime($db_date)) : '-';

            $row['status'] = $is_paid ? 'Paid' : 'Unpaid';
            $row['status_badge'] = $is_paid ? 'badge-success' : 'badge-warning';
            // $row['status_html'] = $row['status'];

            $data[] = $row;
        }

        $output = array(
            "draw"            => $draw,
            "recordsTotal"    => (int)$total_records,
            "recordsFiltered" => (int)$total_records,
            "data"            => $data
        );

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode($output));
    }

    public function returns()
    {
        // 1. JWT Authentication
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->load->model('Return_model');

        // Support Datatables pagination parameters if sent
        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $length = isset($payload['length']) ? (int)$payload['length'] : 0;
        $start = isset($payload['start']) ? (int)$payload['start'] : 0;
        $draw = isset($payload['draw']) ? $payload['draw'] : '';

        // Query Builder using standard Model query
        $this->db->select('r.*, r.id as return_id, COALESCE(oi.product_name, np.name) as product_name, r.image_proof');
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('(oi.login_id = '.(int)$vendor_id.' OR np.vendor_id = '.(int)$vendor_id.')', null, false);
        $this->db->order_by('r.created_at', 'DESC');

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        $list = $query->result();

        // Get total count
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('(oi.login_id = '.(int)$vendor_id.' OR np.vendor_id = '.(int)$vendor_id.')', null, false);
        $total_records = $this->db->count_all_results();

        $data = array();

        foreach ($list as $r) {
            $row = array();

            $row['return_id'] = '#' . $r->return_id;
            $row['order_id'] = '#' . $r->order_id;
            $row['product_name'] = htmlspecialchars($r->product_name ?? '');
            $row['reason'] = htmlspecialchars($r->reason ?? '');

            // Image Proof URL (plain URL, no HTML)
            $image_proof_url = '';
            if (!empty($r->image_proof)) {
                $img = (string)$r->image_proof;
                $image_proof_url = (preg_match('#^https?://#i', $img)) ? $img : base_url(ltrim($img, '/'));
            }
            $row['image_proof_url'] = $image_proof_url;
            // Keep legacy field for backward compatibility (no HTML, just URL or empty)
            $row['image_proof_html'] = $image_proof_url;

            // Status mapping (plain text, no HTML)
            $badge = 'badge-warning';
            $st = strtolower((string)$r->status);
            $st_display = $st;
            if ($st_display == 'approved') $st_display = 'accepted';
            if ($st_display == 'inspection_pending') $st_display = 'initiated';
            if ($st_display == 'pickup_scheduled' || $st_display == 'picked_up') $st_display = 'completed';
            if ($st_display == 'approved_refund' || $st_display == 'refund_completed') $st_display = 'refunded';
            if ($st_display == 'initiated') $badge = 'badge-secondary';
            if ($st_display == 'accepted') $badge = 'badge-success';
            if ($st_display == 'completed') $badge = 'badge-primary';
            if ($st_display == 'refunded') $badge = 'badge-info';
            if ($st_display == 'rejected' || $st_display == 'return_cancelled') $badge = 'badge-danger';

            // Plain status fields (no HTML)
            $row['status'] = $st_display;
            $row['status_badge'] = $badge;
            // Legacy field: plain status text (no HTML)
            // $row['status_html'] = $st_display;
            $row['created_at_date'] = date('Y-m-d', strtotime($r->created_at));

            // Plain action fields (no HTML)
            $row['return_id_raw'] = (int)$r->return_id;
            // Legacy field: plain return id (no HTML)
            $row['action_html'] = (int)$r->return_id;

            $data[] = $row;
        }

        $output = array(
            "draw"            => $draw,
            "recordsTotal"    => (int)$total_records,
            "recordsFiltered" => (int)$total_records,
            "data"            => $data
        );

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode($output));
    }

    public function return_details()
    {
        // 1. JWT Authentication
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->load->model('Return_model');

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $return_id = isset($payload['return_id']) ? (int)$payload['return_id'] : 0;

        if (empty($return_id)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Return ID is required",
                    "data" => null,
                    "errors" => ["Return ID is required"]
                ]));
        }

        $return_data = $this->Return_model->get_return_details($return_id, $vendor_id);

        if (empty($return_data)) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Return details not found or access denied",
                    "data" => null,
                    "errors" => ["Return details not found or access denied"]
                ]));
        }

        // Pre-process and format response fields
        $data = [
            'return_id' => $return_data->return_id,
            'order_id' => $return_data->order_id,
            'order_number' => isset($return_data->order_number) && !empty($return_data->order_number) ? $return_data->order_number : $return_data->order_id,
            'order_date' => date('Y-m-d', strtotime($return_data->order_date)),
            'customer_name' => htmlspecialchars($return_data->first_name . ' ' . $return_data->last_name),
            'product_name' => htmlspecialchars($return_data->product_name ?? ''),
            'reason' => htmlspecialchars($return_data->reason ?? ''),
            'vendor_note' => $return_data->vendor_note ?? '',
            'image_url' => '',
            'status' => $return_data->status
        ];

        // Format image proof URL
        if (!empty($return_data->image_proof)) {
            $img = (string)$return_data->image_proof;
            $data['image_url'] = (preg_match('#^https?://#i', $img)) ? $img : base_url(ltrim($img, '/'));
        }

        // Format status badge
        $badge = 'badge-warning';
        $st = strtolower((string)$return_data->status);
        $st_display = $st;
        if ($st_display == 'approved') $st_display = 'accepted';
        if ($st_display == 'inspection_pending') $st_display = 'initiated';
        if ($st_display == 'pickup_scheduled' || $st_display == 'picked_up') $st_display = 'completed';
        if ($st_display == 'approved_refund' || $st_display == 'refund_completed') $st_display = 'refunded';
        if ($st_display == 'initiated') $badge = 'badge-secondary';
        if ($st_display == 'accepted') $badge = 'badge-success';
        if ($st_display == 'completed') $badge = 'badge-primary';
        if ($st_display == 'refunded') $badge = 'badge-info';
        if ($st_display == 'rejected' || $st_display == 'return_cancelled') $badge = 'badge-danger';

        $data['status_display'] = $st_display;
        $data['status_badge_class'] = $badge;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                "status" => 1,
                "message" => "success",
                "data" => $data,
                "errors" => []
            ]));
    }

    public function order_counts()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->load->model('Order_model');
        $counts = $this->Order_model->item_count_vendor_wise(array('vendor_id' => $vendor_id));
        
        $data = array(
            'total_order' => (int)($counts['total_items'] ?? 0),
            'delivered_order' => (int)($counts['delivered'] ?? 0),
            'total_amount' => (float)($counts['amount'] ?? 0)
        );

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                "status" => 1,
                "message" => "Success",
                "data" => $data
            ]));
    }

    public function order_list()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->load->model('Order_model');
        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $status_val = isset($payload['status']) ? $payload['status'] : 1;
        $status_filter = 1;

        if (is_string($status_val) && !is_numeric($status_val)) {
            $status_clean = strtolower(trim($status_val));
            if ($status_clean === 'pending') {
                $status_filter = 1;
            } elseif ($status_clean === 'in process' || $status_clean === 'in_process' || $status_clean === 'inprocess') {
                $status_filter = 2;
            } elseif ($status_clean === 'shipped') {
                $status_filter = 3;
            } elseif ($status_clean === 'completed' || $status_clean === 'delivered') {
                $status_filter = 10;
            } elseif ($status_clean === 'cancelled' || $status_clean === 'canceled' || $status_clean === 'canclled' || $status_clean === 'cancled') {
                $status_filter = array(5, 17);
            } else {
                $status_filter = (int)$status_val;
            }
        } else {
            $status_filter = (int)$status_val;
        }

        // Merge session so browser session elements are not wiped
        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id'] = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? TRUE;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');

        $post_params = array('status' => $status_filter);
        $order_list = $this->Order_model->order_list(array('post_data' => $post_params));
        
        $order_wise = array();
        if ($order_list) {
            foreach ($order_list as $row_wise) {
                $d_date = isset($row_wise->date_added) ? $row_wise->date_added : date('Y-m-d H:i:s');
                $row_wise->date_created_modify = date('d-M-Y H:i', strtotime($d_date));
                $row_wise->total = isset($row_wise->total_amount) ? $row_wise->total_amount : 0;
                
                if (!isset($row_wise->order_uid) || empty($row_wise->order_uid)) {
                    $row_wise->order_uid = isset($row_wise->order_id) ? $row_wise->order_id : $row_wise->id;
                }
                
                $row_wise->order_item_uid = $row_wise->id;
                
                $product_data = new stdClass();
                if (isset($row_wise->product_name) && $row_wise->product_name) {
                    $product_data->post_title = $row_wise->product_name;
                } else {
                    $product_data->post_title = 'Order #' . $row_wise->order_uid;
                }
                
                $row_wise->product_obj = $product_data;
                $row_wise->orderstatus = $row_wise->status;
                $order_wise[] = $row_wise;
            }
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                "status" => 1,
                "message" => "Success",
                "data" => array('order_wise' => $order_wise)
            ]));
    }

    public function update_order_item_status()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $item_id = isset($payload['item_id']) ? (int)$payload['item_id'] : 0;
        $order_type = isset($payload['order_type']) ? (string)$payload['order_type'] : '';

        if (!$item_id) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Invalid item ID",
                    "data" => null
                ]));
        }

        // Ensure item belongs to this vendor
        $item = $this->db->from('ec_order_items')
            ->where('id', $item_id)
            ->where('login_id', $vendor_id)
            ->limit(1)->get()->row();
        if (!$item) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Item not found or access denied",
                    "data" => null
                ]));
        }

        $status = null;
        $msg = 'Updated';
        if ($order_type === 'in_process') {
            $status = 2;
            $msg = 'Updated and now move in In Process Tab';
        } elseif ($order_type === 'shipped') {
            $status = 3;
            $msg = 'Updated and now move in Shipped Tab';
        } elseif ($order_type === 'delivered') {
            $status = 10;
            $msg = 'Updated and now move in In Completed Tab';
        }

        if ($status === null) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Invalid action type",
                    "data" => null
                ]));
        }

        $updated = $this->Query_model->update_data('ec_order_items', array('status' => (string)$status), array('id' => $item_id, 'login_id' => $vendor_id));
        if ($updated) {
            $this->_sync_parent_order_status_api((int)($item->order_id ?? 0));
            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    "status" => 1,
                    "message" => $msg,
                    "data" => array('item_id' => $item_id)
                ]));
        }

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                "status" => 0,
                "message" => "There is no update",
                "data" => null
            ]));
    }

    private function _sync_parent_order_status_api($order_id)
    {
        $order_id = (int)$order_id;
        if (!$order_id) {
            return;
        }

        $this->db->from('ec_order_items');
        $this->db->where('order_id', $order_id);
        $items = $this->db->get()->result();
        if (!$items) {
            return;
        }

        $all_delivered = true;
        $any_shipped = false;
        $any_in_process = false;
        $any_cancelled = false;
        $any_pending = false;

        foreach ($items as $it) {
            $st = (int)($it->status ?? 0);
            if ($st === 10) {
                // delivered
            } elseif ($st === 3) {
                $any_shipped = true;
                $all_delivered = false;
            } elseif ($st === 2) {
                $any_in_process = true;
                $all_delivered = false;
            } elseif ($st === 5 || $st === 17) {
                $any_cancelled = true;
                $all_delivered = false;
            } else {
                $any_pending = true;
                $all_delivered = false;
            }
        }

        $new_status = 'pending';
        if ($any_cancelled) {
            $new_status = 'cancelled';
        } else if ($all_delivered) {
            $new_status = 'delivered';
        } else if ($any_shipped) {
            $new_status = 'shipped';
        } else if ($any_in_process) {
            $new_status = 'confirmed';
        } else if ($any_pending) {
            $new_status = 'pending';
        }

        $this->Query_model->update_data('ec_orders', array('status' => $new_status), array('id' => $order_id));
    }

    public function cancel_order()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $order_id = isset($payload['order_id']) ? (int)$payload['order_id'] : 0;
        $item_id = isset($payload['item_id']) ? (int)$payload['item_id'] : 0;
        $reason = isset($payload['reason']) ? (string)$payload['reason'] : '';
        $comment = isset($payload['comment']) ? (string)$payload['comment'] : '';

        if (!$order_id) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Invalid order ID",
                    "data" => null
                ]));
        }
        if (empty($reason)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Reason is required",
                    "data" => null
                ]));
        }

        $this->db->trans_begin();
        $updated_items = 0;
        if ($item_id > 0) {
            $item = $this->db->from('ec_order_items')
                ->where('id', $item_id)
                ->where('order_id', $order_id)
                ->where('login_id', $vendor_id)
                ->limit(1)->get()->row();
            if (!$item) {
                $this->db->trans_rollback();
                return $this->output
                    ->set_status_header(404)
                    ->set_output(json_encode([
                        "status" => 0,
                        "message" => "Item not found or access denied",
                        "data" => null
                    ]));
            }
            $updated_items = $this->Query_model->update_data('ec_order_items', array('status' => 17), array('id' => $item_id, 'login_id' => $vendor_id));
        } else {
            $updated_items = $this->Query_model->update_data('ec_order_items', array('status' => 17), array('order_id' => $order_id, 'login_id' => $vendor_id));
        }

        // Persist comment
        $reason = trim($reason);
        $comment = trim($comment);
        if ($reason !== '' || $comment !== '') {
            $existing = $this->db->select('comment')->from('ec_orders')->where('id', $order_id)->get()->row();
            $prev = $existing && isset($existing->comment) ? (string)$existing->comment : '';
            $stamp = date('Y-m-d H:i:s');
            $line = "[Vendor #".$vendor_id." Cancel @ ".$stamp."] Reason: ".$reason;
            if ($comment !== '') {
                $line .= " | Comment: ".$comment;
            }
            $new_comment = $prev ? ($prev."\n".$line) : $line;
            $this->Query_model->update_data('ec_orders', array('comment' => $new_comment), array('id' => $order_id));
        }

        // Sync parent order status after cancellation
        $this->db->from('ec_order_items');
        $this->db->where('order_id', $order_id);
        $items = $this->db->get()->result();
        if ($items) {
            $all_cancelled = true;
            foreach ($items as $it) {
                $st = (int)($it->status ?? 0);
                if (!in_array($st, array(5,17), true)) {
                    $all_cancelled = false;
                    break;
                }
            }
            if ($all_cancelled) {
                $updateOrder = array('status' => 'cancelled');
                if ($this->db->field_exists('cancelled_by', 'ec_orders')) {
                    $updateOrder['cancelled_by'] = 'vendor';
                }
                $this->Query_model->update_data('ec_orders', $updateOrder, array('id' => $order_id));
            } else {
                $this->_sync_parent_order_status_api($order_id);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    "status" => 0,
                    "message" => "Cancel failed",
                    "data" => null
                ]));
        } else {
            $this->db->trans_commit();
            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    "status" => 1,
                    "message" => "Cancelled",
                    "data" => array('updated_items' => $updated_items)
                ]));
        }
    }

    public function reviews()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $limit = (int)$this->input->post('length') ?: 10;
        $start = (int)$this->input->post('start') ?: 0;
        $search = $this->input->post('search');
        $search_val = (is_array($search) && isset($search['value'])) ? trim((string)$search['value']) : '';

        // Query setup
        $this->db->select('r.review_rating_id, r.title, r.rating, r.review, r.status, r.date_created');
        $this->db->select('p.name as product_name, p.id as product_id');
        $this->db->select("CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,'')) AS customer_name", false);
        $this->db->from('ec_review_rating r');
        $this->db->join('products p', 'p.id = r.product_id', 'inner');
        $this->db->join('ec_customer c', 'c.customer_id = r.customer_id', 'left');
        $this->db->where('p.vendor_id', $vendor_id);

        if ($search_val !== '') {
            $this->db->group_start();
            $this->db->like('p.name', $search_val);
            $this->db->or_like('r.title', $search_val);
            $this->db->or_like('r.review', $search_val);
            $this->db->or_like('c.fname', $search_val);
            $this->db->or_like('c.lname', $search_val);
            $this->db->group_end();
        }

        // Get total filtered count
        $tempdb = clone $this->db;
        $recordsFiltered = $tempdb->count_all_results();

        // Fetch records
        $this->db->order_by('r.date_created', 'DESC');
        $this->db->limit($limit, $start);
        $reviews = $this->db->get()->result();

        // Get total overall count
        $this->db->from('ec_review_rating r');
        $this->db->join('products p', 'p.id = r.product_id', 'inner');
        $this->db->where('p.vendor_id', $vendor_id);
        $recordsTotal = $this->db->count_all_results();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                "draw" => (int)$this->input->post('draw'),
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $reviews,
                "status" => 1,
                "message" => "Success"
            ]));
    }
}

