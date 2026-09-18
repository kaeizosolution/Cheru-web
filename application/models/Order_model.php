<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends MY_Model {
    
    // Cache whether currency columns exist on ec_order_items (null = not checked yet)
    private static $_items_has_currency = null;

    function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
        // Keep the session logic: it correctly grabs the ID from the new session structure
        $user_session = $this->session->userdata($this->TYPE);
        $this->VENDOR_ID = 0;
        if($user_session){
            if(isset($user_session['vendor_id'])){
                $this->VENDOR_ID = (int)$user_session['vendor_id'];
            }elseif(isset($user_session['login_id'])){
                $this->VENDOR_ID = (int)$user_session['login_id'];
            }
        }
    }

    /**
     * Check once per request whether ec_order_items has currency_rate & currency_id columns.
     * Caches result so DB is only queried once.
     */
    private function _has_currency_columns()
    {
        if (self::$_items_has_currency !== null) {
            return self::$_items_has_currency;
        }
        try {
            $q = $this->slave->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ec_order_items' AND COLUMN_NAME IN ('currency_rate','currency_id')");
            self::$_items_has_currency = ($q && $q->num_rows() >= 2);
        } catch (Exception $e) {
            self::$_items_has_currency = false;
        }
        return self::$_items_has_currency;
    }

    public function get_order_backend_data($user_id, $post_data)
    {
        $filter = isset($post_data['filter']) && is_array($post_data['filter']) ? $post_data['filter'] : array();
        $this->slave->select('eos.id as order_id, eos.order_number, eos.user_id, eos.final_amount as final_amount, eos.date_added, eos.status, eos.address_id, eos.last_updated, eos.cancelled_by');
        $this->slave->select('SUM(eop.qty) as quantity');
        $this->slave->select('SUM(eop.subtotal) as items_subtotal');
        $this->slave->from('ec_orders as eos');
        $this->slave->join('ec_order_items as eop', 'eop.order_id = eos.id');

        // Vendor sees only own orders; admin sees all (optionally filtered by user_id)
        if($this->TYPE === 'vendor'){
            $this->slave->where('eop.login_id', $this->VENDOR_ID);
        }else{
            if(!empty($user_id)){
                $this->slave->where('eos.user_id', $user_id);
            }
        }

        if(isset($post_data['search']['value']) && !empty($post_data['search']['value']))
        {
            $search = $post_data['search']['value'];
            $this->slave->group_start();
            $this->slave->like('eos.order_number', $search); 
            $this->slave->or_like('eop.subtotal', $search);
            $this->slave->group_end();
        }

        if(isset($filter['order_number']) && $filter['order_number'] !== ''){
            $this->slave->like('eos.order_number', $filter['order_number']);
        }
        if(isset($filter['date_added']) && $filter['date_added'] !== ''){
            $this->slave->like('eos.date_added', $filter['date_added'], 'after');
        }

        $statusFilter = '';
        if(isset($post_data['status']) && $post_data['status'] !== ''){
            $statusFilter = $post_data['status'];
        }else if(isset($filter['status']) && $filter['status'] !== ''){
            $statusFilter = $filter['status'];
        }
        if($statusFilter !== ''){
            $this->slave->where('eos.status', $statusFilter);
        }

        $this->slave->group_by('eos.id');

        $this->slave->order_by('eos.date_added', 'DESC');

        if(isset($post_data['length']) && $post_data['length'] != -1)
        {
            $this->slave->limit($post_data['length'], $post_data['start']);
        }

        $query = $this->slave->get();
        return $query->result();
    }

    public function order_list($args = array())
    {
        $post_data = isset($args['post_data']) ? $args['post_data'] : array();
        $status = isset($post_data['status']) ? $post_data['status'] : '';
        // Allow explicit vendor_id to be passed (API context — session not set at model-load time)
        $explicit_vendor_id = isset($args['vendor_id']) ? (int)$args['vendor_id'] : 0;

        // Determine effective vendor_id: explicit param takes priority over session
        $effective_vendor_id = $explicit_vendor_id > 0 ? $explicit_vendor_id : (int)$this->VENDOR_ID;
        $is_vendor = ($this->TYPE === 'vendor') || ($explicit_vendor_id > 0);

        // Vendor should process per item (ec_order_items), not the full order.
        if($is_vendor && $effective_vendor_id > 0){
            $has_currency = $this->_has_currency_columns();
            $this->slave->select('eop.id');
            $this->slave->select('eop.order_id');
            $this->slave->select('eos.order_number as order_uid');
            $this->slave->select('eop.date_added');
            $this->slave->select('eop.subtotal as total_amount');
            $this->slave->select('eop.status as status');
            $this->slave->select('eop.product_name');
            if ($has_currency) {
                $this->slave->select('IFNULL(eop.currency_rate, 1) as currency_rate', false);
                $this->slave->select('IFNULL(eop.currency_id, 1) as currency_id', false);
            } else {
                $this->slave->select('1 as currency_rate', false);
                $this->slave->select('1 as currency_id', false);
            }
            $this->slave->from('ec_order_items as eop');
            $this->slave->join('ec_orders as eos', 'eop.order_id = eos.id', 'inner');
            $this->slave->where('eop.login_id', $effective_vendor_id);
            if($status !== '' && $status !== null){
                if(is_array($status)){
                    $this->slave->where_in('eop.status', $status);
                }else{
                    $this->slave->where('eop.status', $status);
                }
            }
            $this->slave->order_by('eop.date_added', 'DESC');
            $query = $this->slave->get();
            return $query->result();
        }

        // Admin/other: keep existing order-grouped behavior.
        $this->slave->select('eos.id, eos.order_number as order_uid, eos.date_added');
        $this->slave->select('SUM(eop.subtotal) as total_amount');
        $this->slave->select('MIN(eop.status) as status');
        $this->slave->from('ec_orders as eos');
        $this->slave->join('ec_order_items as eop', 'eop.order_id = eos.id');
        if($status !== '' && $status !== null){
            if(is_array($status)){
                $this->slave->where_in('eop.status', $status);
            }else{
                $this->slave->where('eop.status', $status);
            }
        }
        $this->slave->group_by('eos.id');
        $this->slave->order_by('eos.date_added', 'DESC');
        $query = $this->slave->get();
        return $query->result();
    }

    public function count_all($user_id = null)
    {
        if($this->TYPE === 'vendor'){
            $this->slave->select('COUNT(DISTINCT eos.id) as CNT', false);
            $this->slave->from('ec_orders as eos');
            $this->slave->join('ec_order_items as eop', 'eop.order_id = eos.id');
            $this->slave->where('eop.login_id', $this->VENDOR_ID);
            $row = $this->slave->get()->row();
            return (int)($row->CNT ?? 0);
        }

        $this->slave->from('ec_orders as eos');
        if(!empty($user_id)){
            $this->slave->where('eos.user_id', $user_id);
        }
        return $this->slave->count_all_results();
    }

    public function count_filtered($user_id, $post_data)
    {
        $this->slave->select('COUNT(DISTINCT eos.id) as CNT', false);
        $this->slave->from('ec_orders as eos');
        $this->slave->join('ec_order_items as eop', 'eop.order_id = eos.id');
        if($this->TYPE === 'vendor'){
            $this->slave->where('eop.login_id', $this->VENDOR_ID);
        }else{
            if(!empty($user_id)){
                $this->slave->where('eos.user_id', $user_id);
            }
        }

        if(isset($post_data['status']) && $post_data['status'] != '')
        {
             $this->slave->where('eos.status', $post_data['status']);
        }
        $row = $this->slave->get()->row();
        return (int)($row->CNT ?? 0);
    }

    public function item_count_vendor_wise($args = array()) {
        // Allow explicit vendor_id to be passed (API context — session not set at model-load time)
        $explicit_vendor_id = isset($args['vendor_id']) ? (int)$args['vendor_id'] : 0;
        $effective_vendor_id = $explicit_vendor_id > 0 ? $explicit_vendor_id : (int)$this->VENDOR_ID;
        $is_vendor = ($this->TYPE === 'vendor') || ($explicit_vendor_id > 0);

        // Total items for THIS vendor
        $this->slave->select('count(*) as cnt');
        $this->slave->from('ec_order_items');
        if($is_vendor && $effective_vendor_id > 0){
            $this->slave->where('login_id', $effective_vendor_id);
        }
        $total = $this->slave->get()->row();

        // Delivered items for THIS vendor
        $this->slave->select('count(*) as cnt');
        $this->slave->from('ec_order_items');
        if($is_vendor && $effective_vendor_id > 0){
            $this->slave->where('login_id', $effective_vendor_id);
        }
        $this->slave->where('status', '10');
        $delivered = $this->slave->get()->row();

        // Total earned by THIS vendor (normalised to base currency if currency_rate column exists)
        $has_currency = $this->_has_currency_columns();
        if ($has_currency) {
            $this->slave->select('SUM(subtotal / IFNULL(currency_rate, 1)) as total', false);
        } else {
            $this->slave->select('sum(subtotal) as total');
        }
        $this->slave->from('ec_order_items');
        if($is_vendor && $effective_vendor_id > 0){
            $this->slave->where('login_id', $effective_vendor_id);
        }
        $amount = $this->slave->get()->row();

        return [
            'total_items' => $total->cnt ?? 0,
            'delivered'   => $delivered->cnt ?? 0,
            'amount'      => $amount->total ?? 0
        ];
    }

    public function get_finance_order_detail($customer_id, $order_id)
    {
        $this->slave->select('eop.*, eop.qty as quantity');
        $this->slave->from('ec_order_items as eop');
        $this->slave->join('ec_orders as eos', 'eop.order_id = eos.id');
        $this->slave->where('eos.customer_id', $customer_id);
        $this->slave->where('eop.order_id', $order_id);
        if($this->TYPE === 'vendor'){
            $this->slave->where('eop.login_id', $this->VENDOR_ID);
        }
        return $this->slave->get()->result();
    }

    public function get_data_by_order_uid($order_uid)
    {
        $has_currency = $this->_has_currency_columns();
        if ($has_currency) {
            $this->slave->select('eop.*, IFNULL(eop.currency_rate, 1) as currency_rate, IFNULL(eop.currency_id, 1) as currency_id', false);
        } else {
            $this->slave->select('eop.*', false);
            $this->slave->select('1 as currency_rate', false);
            $this->slave->select('1 as currency_id', false);
        }
        $this->slave->select('eos.order_number as order_uid, eos.total_amount as order_total, eos.shipping_amount as order_shipping, eos.tax_amount as order_tax, eos.final_amount as order_total_final, eos.address_id as shipping_address_id, eos.payment_method as payment_mode, eos.discount_amount, eos.coupon_code, eos.date_added as date_created', false);
        $this->slave->select('eop.qty as quantity');
        $this->slave->from('ec_order_items as eop');
        $this->slave->join('ec_orders as eos', 'eop.order_id = eos.id', 'inner');
        $this->slave->where('eos.order_number', $order_uid);
        if ($this->TYPE === 'vendor' && $this->VENDOR_ID > 0) {
            $this->slave->where('eop.login_id', $this->VENDOR_ID);
        }
        $query = $this->slave->get();
        return $query->result();
    }

    /**
     * Same as get_data_by_order_uid() but uses the numeric ec_orders.id primary key.
     * Preferred over get_data_by_order_uid() for new code.
     */
    public function get_data_by_order_id($order_id)
    {
        $order_id = (int)$order_id;
        $has_currency = $this->_has_currency_columns();
        if ($has_currency) {
            $this->slave->select('eop.*, IFNULL(eop.currency_rate, 1) as currency_rate, IFNULL(eop.currency_id, 1) as currency_id', false);
        } else {
            $this->slave->select('eop.*', false);
            $this->slave->select('1 as currency_rate', false);
            $this->slave->select('1 as currency_id', false);
        }
        $this->slave->select('eos.order_number as order_uid, eos.total_amount as order_total, eos.shipping_amount as order_shipping, eos.tax_amount as order_tax, eos.final_amount as order_total_final, eos.address_id as shipping_address_id, eos.payment_method as payment_mode, eos.discount_amount, eos.coupon_code, eos.date_added as date_created', false);
        $this->slave->select('eop.qty as quantity');
        $this->slave->from('ec_order_items as eop');
        $this->slave->join('ec_orders as eos', 'eop.order_id = eos.id', 'inner');
        $this->slave->where('eos.id', $order_id);
        if ($this->TYPE === 'vendor' && $this->VENDOR_ID > 0) {
            $this->slave->where('eop.login_id', $this->VENDOR_ID);
        }
        $query = $this->slave->get();
        return $query->result();
    }
}