<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_model extends MY_Model{
    
    // Cache whether currency columns exist on ec_order_items
    private static $_items_has_currency = null;

 function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
        $sess = $this->session->userdata($this->TYPE);
        $this->VENDOR_ID = (is_array($sess) && isset($sess['login_id']))
            ? (int)$sess['login_id']
            : ((is_array($sess) && isset($sess['vendor_id'])) ? (int)$sess['vendor_id'] : 0);
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
            $q = $this->slave->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ec_order_items' AND COLUMN_NAME = 'currency_rate'");
            self::$_items_has_currency = ($q && $q->num_rows() > 0);
        } catch (Exception $e) {
            self::$_items_has_currency = false;
        }
        return self::$_items_has_currency;
    }

	public function _product_table()
	{
		// Always use new products table
		return 'products';
	}

	public function _product_vendor_column($table)
	{
		// Determine which column links a product row to a vendor on this install
		// Return null when no vendor ownership column exists (avoid SQL errors)
		if (!method_exists($this->slave, 'field_exists')) {
			return null;
		}
		$candidates = array('login_id', 'vendor_id', 'supplier_id', 'created_by', 'user_id');
		foreach ($candidates as $col) {
			if ($this->slave->field_exists($col, $table)) {
				return $col;
			}
		}
		return null;
	}

	private function _vendor_table()
	{
		if (method_exists($this->slave, 'table_exists') && $this->slave->table_exists('ec_vendor')) {
			return 'ec_vendor';
		}
		return 'ec_admin';
	}

	private function _orders_table()
	{
		if (method_exists($this->master, 'table_exists') && $this->master->table_exists('ec_orders')) {
			return 'ec_orders';
		}
		return 'ec_order';
	}

	private function _status_candidates($status)
	{
		// Support systems where order status is stored as either numeric codes or strings
		if (is_array($status)) {
			return $status;
		}

		$st = is_string($status) ? strtolower(trim($status)) : $status;
		$candidates = array($status);

		if ($st === 10 || $st === '10' || $st === 'delivered') {
			$candidates = array(10, '10', 'delivered', 'Delivered');
		} elseif ($st === 1 || $st === '1' || $st === 'ordered' || $st === 'pending') {
			$candidates = array(1, '1', 'ordered', 'Ordered', 'pending', 'Pending');
		}

		return $candidates;
	}

	private function _apply_order_date_filter($qb, $startDate, $endDate)
	{
		if ($startDate && $endDate) {
			$qb->where('DATE(o.date_added) >=', $startDate);
			$qb->where('DATE(o.date_added) <=', $endDate);
		} elseif ($startDate) {
			$qb->where('DATE(o.date_added) =', $startDate);
		}
		return $qb;
	}

	public function count_orders_by_status($status, $startDate = null, $endDate = null)
	{
		// Use master for fresh/authoritative counts and reset query builder state
		$table = $this->_orders_table();
		$status_candidates = $this->_status_candidates($status);
		$this->master->reset_query();
		$this->master->from($table . ' o');
		$this->_apply_order_date_filter($this->master, $startDate, $endDate);
		$this->master->where_in('o.status', $status_candidates);
		return (int)$this->master->count_all_results();
	}

	public function sale_graph_range($startDate = null, $endDate = null)
	{
		// Returns rows: label, product_qty, sale_amount
		$this->master->reset_query();
		$this->master->from('ec_order_items oi');

		if ($startDate && $endDate) {
			$this->master->where('DATE(oi.date_added) >=', $startDate);
			$this->master->where('DATE(oi.date_added) <=', $endDate);
		} elseif ($startDate) {
			$this->master->where('DATE(oi.date_added) =', $startDate);
		} else {
			// Default: current year monthly (existing behavior)
			$this->master->where('YEAR(oi.date_added) = YEAR(NOW())', null, false);
		}

		$groupByDay = false;
		if ($startDate && $endDate) {
			$days = (int)floor((strtotime($endDate) - strtotime($startDate)) / 86400);
			$groupByDay = ($days <= 31);
		}

		if ($groupByDay) {
			$this->master->select('DATE(oi.date_added) as label, COUNT(oi.id) as product_qty, ROUND(COALESCE(SUM(oi.subtotal),0),2) as sale_amount', false);
			$this->master->group_by('DATE(oi.date_added)');
			$this->master->order_by('DATE(oi.date_added)', 'ASC');
		} else {
			$this->master->select('DATE_FORMAT(oi.date_added, "%b %Y") as label, COUNT(oi.id) as product_qty, ROUND(COALESCE(SUM(oi.subtotal),0),2) as sale_amount', false);
			$this->master->group_by('YEAR(oi.date_added), MONTH(oi.date_added)');
			$this->master->order_by('YEAR(oi.date_added), MONTH(oi.date_added)', 'ASC');
		}

		return $this->master->get()->result();
	}

	public function count_delivered_orders($startDate = null, $endDate = null)
	{
		// Some installs store delivered status on order items reliably even if ec_orders.status differs
		$status_candidates = $this->_status_candidates(10);
		$this->master->reset_query();
		$this->master->select('COUNT(DISTINCT oi.order_id) as total', false);
		$this->master->from('ec_order_items oi');
		$this->master->where_in('oi.status', $status_candidates);
		if ($startDate && $endDate) {
			$this->master->where('DATE(oi.date_added) >=', $startDate);
			$this->master->where('DATE(oi.date_added) <=', $endDate);
		} elseif ($startDate) {
			$this->master->where('DATE(oi.date_added) =', $startDate);
		}
		$row = $this->master->get()->row();
		return (int)($row->total ?? 0);
	}

	public function get_vendor_total()
	{
		$table = $this->_vendor_table();
		// default vendor role fallback for old structure
		if ($table === 'ec_admin') {
			$sql = 'SELECT count(admin_id) as total FROM ec_admin WHERE role_id=2';
			return $this->slave->query($sql)->row();
		}
		$sql = 'SELECT count(*) as total FROM ec_vendor';
		return $this->slave->query($sql)->row();
	}

	public function dashboard_summary($startDate = null, $endDate = null)
	{
		$table = $this->_orders_table();

		// Orders count + income from ec_orders
		$this->master->reset_query();
		$this->master->select('COUNT(o.id) as total_orders, COALESCE(SUM(o.total_amount),0) as total_income', false);
		$this->master->from($table . ' o');
		$this->_apply_order_date_filter($this->master, $startDate, $endDate);
		$orders = $this->master->get()->row();

		// Products count from ec_order_items (distinct product_id) in date range (join orders)
		$this->master->reset_query();
		$this->master->select('COUNT(DISTINCT oi.product_id) as total_products', false);
		$this->master->from($table . ' o');
		$this->master->join('ec_order_items oi', 'oi.order_id = o.id', 'left');
		$this->_apply_order_date_filter($this->master, $startDate, $endDate);
		$products = $this->master->get()->row();

		// Users count from ec_orders distinct user_id in date range
		$this->master->reset_query();
		$this->master->select('COUNT(DISTINCT o.user_id) as total_users', false);
		$this->master->from($table . ' o');
		$this->_apply_order_date_filter($this->master, $startDate, $endDate);
		$users = $this->master->get()->row();

		return (object)array(
			'total_orders' => (int)($orders->total_orders ?? 0),
			'total_income' => (float)($orders->total_income ?? 0),
			'total_products' => (int)($products->total_products ?? 0),
			'total_users' => (int)($users->total_users ?? 0),
		);
	}

    // ==============================================================
    // SECTION 1: INCOME & ORDERS (Using table: ec_orders)
    // Columns: total_amount, date_added
    // ==============================================================

    public function total_income($arg=NULL, $display_rate = 1.0)
{ 
    $display_rate_sql = (float)$display_rate;
    $has_currency = $this->_has_currency_columns();
    $rate_expr = $has_currency ? 'IFNULL(oi.currency_rate, 1)' : '1';
    $base_sql = "SELECT ROUND(SUM(oi.price / {$rate_expr}) * {$display_rate_sql}, 2) as total 
                 FROM ec_orders o
                 JOIN ec_order_items oi ON o.id = oi.order_id
                 WHERE oi.login_id = " . $this->VENDOR_ID;

    if($arg =='today') {
        $sql = $base_sql . " AND o.date_added >= CURRENT_DATE()";
    } elseif($arg =='week') {
        $sql = $base_sql . " AND o.date_added > DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    } elseif($arg =='month') {
        $sql = $base_sql . " AND o.date_added BETWEEN (CURDATE() - INTERVAL 1 MONTH) AND CURDATE()";
    } elseif($arg =='annual') {
        $sql = $base_sql . " AND YEAR(o.date_added) = YEAR(NOW())";
    } else {
        $sql = $base_sql;
    }

    $query = $this->slave->query($sql);       
    return $query->row();
}
    // ==============================================================
    // SECTION 2: PRODUCTS (Using table: products)
    // Columns: date_added, id
    // ==============================================================
    
   public function new_product($arg=NULL)
{
	$product_table = $this->_product_table();
	$vendor_col = $this->_product_vendor_column($product_table);

	$base_sql = "FROM {$product_table}";
	if ($vendor_col && $this->VENDOR_ID) {
		$base_sql .= " WHERE {$vendor_col} = " . (int)$this->VENDOR_ID;
	}

    if($arg =='today') {
        $sql = "SELECT COUNT(id) as total " . $base_sql . ($vendor_col && $this->VENDOR_ID ? " AND" : " WHERE") . " date_added >= CURRENT_DATE()";
    } elseif($arg =='week') {
        $sql = "SELECT COUNT(id) as total " . $base_sql . ($vendor_col && $this->VENDOR_ID ? " AND" : " WHERE") . " date_added > DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    } else {
        $sql = "SELECT COUNT(id) as total " . $base_sql;
    }
        
    $query = $this->slave->query($sql);       
    return $query->row();
}
    
    public function recent_product()
    {
		$product_table = $this->_product_table();
		$sql = "SELECT * FROM {$product_table}
				WHERE date_added between (CURDATE() - INTERVAL 2 MONTH ) and CURDATE()
				ORDER BY id DESC limit 5";
        $query  =  $this->slave->query($sql);       
        $data   =  $query->result();
        return $data;
    }
    
    // ==============================================================
    // SECTION 3: REPORTS & GRAPHS (Using table: ec_order_items)
    // Columns: subtotal, date_added
    // ==============================================================

    public function orders_report($arg=NULL, $vendor_id=null)
    {
        if($arg == 'today'){
            $date = date("Y-m-d");
        }elseif($arg == 'week'){
            $current_date = date("Y-m-d h:i:s");
            $end_date     = date('Y-m-d 00:00:00', strtotime($current_date. ' - 7 days'));
        }elseif($arg == 'month'){
            $current_date = date("Y-m-d h:i:s");
            $end_date     = date('Y-m-d 00:00:00', strtotime($current_date. ' - 30 days'));
        }elseif($arg == 'annual'){
            $current_date = date("Y-m-d h:i:s");
            $end_date     = date('Y-m-d 00:00:00', strtotime($current_date. ' - 364 days'));
        }elseif($arg == 'customdate'){         
            if ($this->uri->segment(5) === FALSE){ $filter_id = 0; }else{ $filter_id = $this->uri->segment(5); }  
            $date = $filter_id;
        }
        
        if($arg)
        {
            $this->slave->select('count(*) as total, sum(subtotal) as total_price, count(id) as total_product');
            $this->slave->from('ec_order_items');
            
            if($arg == 'today' || $arg == 'customdate'){
                $this->slave->like('date_added', $date, 'after'); 
            }else{
                $this->slave->where('date_added >=', $end_date);
                $this->slave->where('date_added <=', $current_date);
            }
            
            $obj = $this->slave->get()->row();
            return $obj;
        }
    }
    
    public function users_report($arg=NULL)
    {
        if($arg =='customdate')
        {
            if ($this->uri->segment(5) === FALSE){ $filter_id = 0; }else{ $filter_id = $this->uri->segment(5); }           
            $sql = "SELECT COUNT(customer_id) as total 
                    FROM ec_customer 
                    WHERE date_added like '%$filter_id%' 
                    ORDER BY date_added DESC";
        }
        elseif($arg =='today')
        {    
            $sql = 'SELECT COUNT(customer_id) as total 
                    FROM ec_customer 
                    WHERE date_added >= CURRENT_DATE() 
                    ORDER BY date_added DESC';
        }
        elseif($arg =='week')
        {
            $sql = 'SELECT COUNT(customer_id) as total 
                    FROM ec_customer 
                    WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 WEEK) 
                    ORDER BY date_added DESC';          
        }
        elseif($arg =='month')
        {            
            $sql = 'SELECT COUNT(customer_id) as total  
                    FROM ec_customer
                    WHERE date_added between (CURDATE() - INTERVAL 1 MONTH ) and CURDATE()';
        }
        elseif($arg =='annual')
        {
            $sql = 'SELECT COUNT(customer_id) as total 
                    FROM ec_customer 
                    WHERE YEAR( date_added ) = YEAR(NOW())';
        }
        else
        {
            $sql = 'SELECT COUNT(customer_id) as total  
                    FROM ec_customer 
                    WHERE date_added between (CURDATE() - INTERVAL 1 MONTH ) and CURDATE()';
        }   
                
        $query  =  $this->slave->query($sql);       
        $data   =  $query->row();
        return $data;
    }

    public function top_selling_products()
    {
        // FIX: Getting Name/Price from 'ec_order_items' (eo) instead of 'ec_product' (ep)
        // FIX: Using 'ep.id' for join
		$product_table = $this->_product_table();
		$sql = "SELECT eo.product_id, SUM(eo.qty) AS TotalQuantity, eo.product_name as post_title, eo.price as sale_price, ep.status
				FROM ec_order_items eo
				JOIN {$product_table} ep ON ep.id = eo.product_id
				GROUP BY eo.product_id
				ORDER BY SUM(eo.qty) DESC limit 4";
        
        $query  =  $this->slave->query($sql);       
        $data   =  $query->result();
        return $data;
    }

    public function sold_product()
    {
        // FIX: Getting Name/Price from 'ec_order_items' (eo) instead of 'ec_product' (ep)
		$product_table = $this->_product_table();
		$sql = "SELECT eo.product_id, SUM(eo.qty) AS TotalQuantity, SUM(eo.subtotal) AS Totalsale, eo.product_name as post_title, eo.price as sale_price, ep.status
				FROM ec_order_items eo       
				JOIN {$product_table} ep ON ep.id = eo.product_id       
				GROUP BY eo.product_id
				ORDER BY SUM(eo.qty) DESC limit 5";
        
        $query  =  $this->slave->query($sql);       
        $data   =  $query->result();
        return $data;
    }
    
    public function sale_graph($arg=NULL)
    {
        $sql = 'SELECT year(date_added), month(date_added), count(id) as product_qty, round(sum(subtotal),2) as sale_amount
                FROM ec_order_items
                WHERE YEAR(date_added) = YEAR(NOW())
                GROUP BY year(date_added), month(date_added)
                ORDER BY year(date_added), month(date_added)';
                
        $query  =  $this->slave->query($sql);   
        $data   =  $query->result();
        return $data;
    }
        
    // ==============================================================
    // SECTION 4: STATUS COUNTS (Joins Orders and Items)
    // ==============================================================

    public function get_pending_order($vendor_id = NULL)
    {
        $this->slave->from('ec_order_items as eop');
        if($vendor_id){
            $this->slave->where('eop.login_id', (int)$vendor_id);
        }
        $this->slave->where('eop.status', 1);
        return (int)$this->slave->count_all_results();
    }

    public function get_cancelled_order($vendor_id = NULL)
    {
        $this->slave->from('ec_order_items as eop');
        if($vendor_id){
            $this->slave->where('eop.login_id', (int)$vendor_id);
        }
        $this->slave->where_in('eop.status', array(5,17));
        return (int)$this->slave->count_all_results();
    }

    public function get_shipped_order($vendor_id = NULL)
    {
        $this->slave->from('ec_order_items as eop');
        if($vendor_id){
            $this->slave->where('eop.login_id', (int)$vendor_id);
        }
        $this->slave->where('eop.status', 3);
        return (int)$this->slave->count_all_results();
    }

    public function get_delivered_order($vendor_id = NULL)
    {
        // Keep existing return shape (row object) but vendor-aware
        $this->slave->select('sum(eop.subtotal) as grand_total');
        $this->slave->from('ec_order_items as eop');
        if($vendor_id){
            $this->slave->where('eop.login_id', (int)$vendor_id);
        }
        $this->slave->where('eop.status', 10);
        $obj = $this->slave->get()->row();
        return $obj;
    }

    public function get_dash_delivered_order_qty()
    {
        $this->slave->select('count(eop.qty) as delivery_total_qty');
        $this->slave->from('ec_order_items as eop');
        $this->slave->join('ec_orders as eos', 'eop.order_id = eos.id');
        $this->slave->where('eos.status = 10'); 
        $obj = $this->slave->get()->row();
        return $obj;
    }

    public function get_dash_pending_order_qty()
    {
        $this->slave->select('count(eop.qty) as pending_qty');
        $this->slave->from('ec_orders as eos');
        $this->slave->join('ec_order_items as eop', 'eop.order_id = eos.id');
        $this->slave->where('`eos`.`status` = 1'); 
        $obj = $this->slave->get()->row();
        return $obj;
    }
    
    public function get_approv_order($vendor_id = NULL)
    {
        // Approved/In process (status=2)
        $this->slave->from('ec_order_items as eop');
        if($vendor_id){
            $this->slave->where('eop.login_id', (int)$vendor_id);
        }
        $this->slave->where('eop.status', 2);
        return (int)$this->slave->count_all_results();
    }
    
    // ==============================================================
    // SECTION 5: DASHBOARD GRAPHS (Store-Wide)
    // ==============================================================

    public function orders_quantity_m($vendor_id=NULL)
    {
        $sql = 'SELECT count(id) as total_quantity 
                FROM ec_order_items 
                WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH) ';
        
        $query  =  $this->slave->query($sql);       
        $data   =  $query->row();       
        
        if ($query->num_rows() > 0) {
            return $data;
        } else {
            return false;
        }
    }
    
    public function orders_salevalue_m($vendor_id=NULL, $display_rate = 1.0)
    { 
        $display_rate_sql = (float)$display_rate;
        $has_currency = $this->_has_currency_columns();
        $rate_expr = $has_currency ? 'IFNULL(currency_rate, 1)' : '1';
        $vendor_cond = "";
        if ($vendor_id !== NULL) {
            $vendor_cond = " AND login_id = " . (int)$vendor_id;
        } elseif ($this->VENDOR_ID) {
            $vendor_cond = " AND login_id = " . (int)$this->VENDOR_ID;
        }
        
        $sql = "SELECT ROUND(SUM(subtotal / {$rate_expr}) * {$display_rate_sql}, 2) as total_sale 
                FROM ec_order_items 
                WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH) " . $vendor_cond;
        
        $query  =  $this->slave->query($sql);               
        $data   =  $query->row();       
        
        if ($query->num_rows() > 0) {
            return $data;
        } else {
            return false;
        }
    } 
    
    public function salevalue_past7days_graph($vendor_id=NULL, $display_rate = 1.0)
    {
        $display_rate_sql = (float)$display_rate;
        $has_currency = $this->_has_currency_columns();
        $rate_expr = $has_currency ? 'IFNULL(currency_rate, 1)' : '1';
        $vendor_cond = "";
        if ($vendor_id !== NULL) {
            $vendor_cond = " AND login_id = " . (int)$vendor_id;
        } elseif ($this->VENDOR_ID) {
            $vendor_cond = " AND login_id = " . (int)$this->VENDOR_ID;
        }
        
        $sql = 'SELECT DATE_FORMAT(date_added, "%a") as days, ROUND(SUM(subtotal / ' . $rate_expr . ') * ' . $display_rate_sql . ', 2) as sale_value
                FROM ec_order_items 
                WHERE date_added > now() - interval 1 week ' . $vendor_cond . '
                GROUP BY DATE_FORMAT(date_added, "%a");';
                
        $query  =  $this->slave->query($sql);       
        $data   =  $query->result();
        return $data;
    }
    
    public function salevalue_monthly_graph($vendor_id=NULL, $display_rate = 1.0)
    {
        $display_rate_sql = (float)$display_rate;
        $has_currency = $this->_has_currency_columns();
        $rate_expr = $has_currency ? 'IFNULL(currency_rate, 1)' : '1';
        $vendor_cond = "";
        if ($vendor_id !== NULL) {
            $vendor_cond = " AND login_id = " . (int)$vendor_id;
        } elseif ($this->VENDOR_ID) {
            $vendor_cond = " AND login_id = " . (int)$this->VENDOR_ID;
        }

        $sql = 'SELECT DATE_FORMAT(date_added, "%b,%y") as month, ROUND(SUM(subtotal / ' . $rate_expr . ') * ' . $display_rate_sql . ', 2) as sale_value
                FROM ec_order_items 
                WHERE YEAR(date_added) = YEAR(NOW()) ' . $vendor_cond . '
                GROUP BY DATE_FORMAT(date_added, "%Y, %M")
                ORDER BY DATE_FORMAT(date_added, "%Y, %M") ASC;';
                
        $query  =  $this->slave->query($sql);       
        $data   =  $query->result();
        return $data;
    }
    
    public function today_sale($vendor_id=NULL, $display_rate = 1.0)
    {
        $display_rate_sql = (float)$display_rate;
        $has_currency = $this->_has_currency_columns();
        $rate_expr = $has_currency ? 'IFNULL(currency_rate, 1)' : '1';
        $vendor_cond = "";
        if ($vendor_id !== NULL) {
            $vendor_cond = " AND login_id = " . (int)$vendor_id;
        } elseif ($this->VENDOR_ID) {
            $vendor_cond = " AND login_id = " . (int)$this->VENDOR_ID;
        }

        $sql = 'SELECT date_added, SUM(qty) as total_item, ROUND(SUM(subtotal / ' . $rate_expr . ') * ' . $display_rate_sql . ', 2) as total_value 
                FROM ec_order_items 
                WHERE DATE(date_added) = CURDATE() ' . $vendor_cond . ';';
                
        $query  =  $this->slave->query($sql);       
        $data   =  $query->row();
        return $data;
    }
    
    public function salevalue_week($vendor_id=NULL)
    {
        $sql = 'SELECT sum(subtotal) as sale_value
                FROM ec_order_items 
                WHERE date_added > now() - interval 1 week';
                
        $query  =  $this->slave->query($sql);       
        $data   =  $query->row();
        return $data;
    }
    
    public function get_order_qty($vendor_id=NULL)
    {
        $sql = 'SELECT sum(qty) as total_item, sum(subtotal) as total_value 
                FROM ec_order_items';
        
        $query  =  $this->slave->query($sql);       
        $data   =  $query->row();
        return $data;
    }
    
    public function get_finance_orderDeliverCount($vendor_id=NULL)
    {     
        $sql = 'SELECT DISTINCT eop.order_id 
                FROM ec_order_items as eop
                JOIN ec_orders as eos ON eop.order_id = eos.id
                WHERE eos.status = 10';               
        
        $query  =  $this->slave->query($sql);
        $data   =  $query->num_rows();
        return $data;
    }
    
    public function get_finance_orderCount($vendor_id=NULL)
    {        
        $sql = 'SELECT DISTINCT order_id FROM ec_order_items';               
        $query  =  $this->slave->query($sql);       
        $data   =  $query->num_rows();
        return $data;
    }

    public function get_dash_orderCount()
    {
		$product_table = $this->_product_table();
		$sql = "SELECT count(*) as total_item FROM {$product_table}";
        $query  =  $this->slave->query($sql);
        $data   =  $query->row();
        return $data;
    }
    
     public function get_dash_vendor()
     {
        $sql = 'SELECT count(admin_id) as total FROM ec_admin WHERE role_id=2';
        $query  =  $this->slave->query($sql);
        $data   =  $query->row();
        return $data;
    }


    public function get_notification($vendor_id=null)
    {
        // FIX: Join is pro.id = oi.product_id
		$product_table = $this->_product_table();
		$sql = "SELECT nt.noti_id, nt.order_id, nt.product_id, nt.user_id, nt.date_added as added_date, oi.product_id, pro.id as product_id_ref
				FROM ec_notification nt 
				LEFT JOIN ec_order_items oi ON oi.order_id = nt.order_id 
				LEFT JOIN {$product_table} pro ON pro.id = oi.product_id
				WHERE nt.show_noti = '1' AND nt.type = '1'
				GROUP BY nt.noti_id
				LIMIT 10";
        
        $query  =  $this->slave->query($sql);
        $data   =  $query->result();
        return $data;
    }

}