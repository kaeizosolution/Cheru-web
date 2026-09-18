<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order extends MY_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Query_model');	
        $this->load->model('Dashboard_model');	
        $this->load->model('Checkout_model');	
		$this->config->load('custom_config');
		$this->load->helper('api');
        $this->TYPE = 'vendor';
		$user_session = $this->session->userdata($this->TYPE);
		$this->LOGIN_ID = 0;
		if($user_session){
			if(isset($user_session['vendor_id'])){
				$this->LOGIN_ID = (int)$user_session['vendor_id'];
			}elseif(isset($user_session['login_id'])){
				$this->LOGIN_ID = (int)$user_session['login_id'];
			}
		}
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

	private function _get_vendor_access_token()
	{
		$sess = $this->session->userdata('vendor');
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
			return (string)$sess['access_token'];
		}
		if (!empty($_SESSION['token'])) {
			return (string)$_SESSION['token'];
		}

		if (is_array($sess) && (!empty($sess['vendor_id']) || !empty($sess['login_id']))) {
			$vendor_id = !empty($sess['login_id']) ? (int)$sess['login_id'] : (int)$sess['vendor_id'];
			$token = $this->_sign_token(['sub' => $vendor_id, 'type' => 'access', 'role' => 'vendor'], 86400);
			$sess['access_token'] = $token;
			$this->session->set_userdata('vendor', $sess);
			if (!empty($this->TYPE)) {
				$this->session->set_userdata($this->TYPE, $sess);
			}
			return $token;
		}
		return '';
	}

	private function _build_api_headers()
	{
		$token = $this->_get_vendor_access_token();
		if ($token === '') {
			return [];
		}
		return ['Authorization' => 'Bearer ' . $token];
	}

	private function _vendor_status_label_to_numeric($label)
	{
		$label = strtolower(trim((string)$label));
		$map = [
			'pending' => 1,
			'processing' => 2,
			'confirmed' => 2,
			'shipped' => 3,
			'delivered' => 10,
			'cancelled' => 17,
			'canceled' => 17,
		];
		return $map[$label] ?? 1;
	}
	 
	public function index()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
        
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", $page_lang->product => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs'] = $breadcrumbs;

        $data['csrf'] = csrf_token(); 
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count(); 

        $use_api = true; // force api-driven loading
        $access_token = $this->_get_vendor_access_token();
        $data['use_api'] = $use_api;
        $data['access_token'] = $access_token;

		// Top stats for vendor order page
		$counts = $this->order_counts();
		$data['total_order'] = $counts['total_order'] ?? 0;
		$data['delivered_order'] = $counts['delivered_order'] ?? 0;
		$data['total_amount'] = $counts['total_amount'] ?? 0;
        $this->load->template("$this->TYPE/order/index_order",$data);       
    }



	public function order_counts()
	{
		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$api_list = call_api('GET', 'vendor/orders', null, $headers);
			if ($api_list['response'] !== null && (int)($api_list['response']['status'] ?? 0) === 1 && is_array($api_list['response']['data'] ?? null)) {
				$api_data = $api_list['response']['data'];
				$rows = is_array($api_data['orders'] ?? null) ? $api_data['orders'] : [];
				$total = 0;
				$delivered = 0;
				$amount = 0;
				foreach ($rows as $r) {
					if (!is_array($r)) {
						continue;
					}
					$total++;
					$amount += (float)($r['vendor_total'] ?? 0);
					$label = strtolower(trim((string)($r['vendor_status'] ?? 'pending')));
					if ($label === 'delivered') {
						$delivered++;
					}
				}
				return array(
					'total_order' => $total,
					'delivered_order' => $delivered,
					'total_amount' => $amount,
				);
			}
		}

		$counts = $this->Order_model->item_count_vendor_wise(array('vendor_id'=>$this->LOGIN_ID));
		$ret_data = array();
		$ret_data['total_order'] 		= $counts['total_items'] ?? 0;
		$ret_data['delivered_order'] 	= $counts['delivered'] ?? 0;
		$ret_data['total_amount']		= $counts['amount'] ?? 0;
		return $ret_data;	
	}

	public function ajax_order_counts()
	{
		if (!$this->input->is_ajax_request()) {
			exit('No direct script access allowed');
		}
		$counts = $this->order_counts();
		api_response(array('status'=>1, 'msg'=>'Success', 'data'=>$counts));
	}

	public function ajax_order_list()
    {
        $use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$status_filter = (int)$this->input->post('status');
			$api_list = call_api('GET', 'vendor/orders', null, $headers);
			if ($api_list['response'] !== null && (int)($api_list['response']['status'] ?? 0) === 1 && is_array($api_list['response']['data'] ?? null)) {
				$api_data = $api_list['response']['data'];
				$rows = is_array($api_data['orders'] ?? null) ? $api_data['orders'] : [];
				$order_wise = [];
				foreach ($rows as $r) {
					if (!is_array($r)) {
						continue;
					}
					$numeric_status = $this->_vendor_status_label_to_numeric($r['vendor_status'] ?? 'pending');
					if ($status_filter > 0 && $numeric_status !== $status_filter) {
						continue;
					}

					$o = new stdClass();
					$o->id = (int)($r['order_id'] ?? 0);
					$o->order_uid = (string)($r['order_number'] ?? $o->id);
					$o->date_created_modify = isset($r['order_date']) ? date('d-M-Y H:i', strtotime((string)$r['order_date'])) : date('d-M-Y H:i');
					$o->total_amount = (float)($r['vendor_total'] ?? 0);
					$o->status = $numeric_status;
					$o->product_obj = (object)['post_title' => 'Order #' . $o->order_uid];
					$o->orderstatus = $o->status;
					$order_wise[] = $o;
				}

				if (count($order_wise)) {
					return api_response(['status' => 1, 'msg' => 'Sucess', 'data' => ['order_wise' => $order_wise]]);
				}
				return api_response(['status' => 0, 'msg' => 'There is no data', 'data' => []]);
			}
		}

        // 1. Get list from Model (filtered by status if clicked on tabs)
        $order_list = $this->Order_model->order_list(array('post_data'=>$this->input->post()));
        $order_wise = array(); 
        
        if($order_list)
        {
            foreach($order_list as $row_wise)
            {
                // --- FIX 1: Safe Date Handling ---
                $d_date = isset($row_wise->date_added) ? $row_wise->date_added : date('Y-m-d H:i:s');
                $row_wise->date_created_modify = date('d-M-Y H:i', strtotime($d_date));
                
                // --- FIX 2: Map 'total' for JS ---
                // If total_amount is missing, default to 0
                $row_wise->total = isset($row_wise->total_amount) ? $row_wise->total_amount : 0;
                
                // --- FIX 3: Safe Order UID ---
                if(!isset($row_wise->order_uid) || empty($row_wise->order_uid)){
                    $row_wise->order_uid = isset($row_wise->order_id) ? $row_wise->order_id : $row_wise->id;
                }
                
                // --- FIX 4: Item UID for buttons ---
                // For vendor, row id is ec_order_items.id
                $row_wise->order_item_uid = $row_wise->id;

                // --- FIX 5: Product Name ---
                $product_data = new stdClass();
                if(isset($row_wise->product_name) && $row_wise->product_name){
                    $product_data->post_title = $row_wise->product_name;
                } else {
                    $product_data->post_title = 'Order #' . $row_wise->order_uid;
                }
                
                $row_wise->product_obj = $product_data;
                $row_wise->orderstatus = $row_wise->status; 

                $order_wise[] = $row_wise;                  
            }
        }
        
        $api_data['order_wise']= $order_wise;

        if(count($api_data['order_wise']))
        api_response(array('status'=>1, 'msg'=>'Sucess', 'data'=>$api_data));
        else
        		api_response(array('status'=>0, 'msg'=>'There is no data', 'data'=>array()));
    }

	public function ajax_order_item_status_update()
	{
		$item_id = (int)$this->input->post('item_id');
		$order_type = (string)$this->input->post('order_type');

		if(!$item_id){
			return api_response(array('status'=>0, 'msg'=>'Invalid item', 'data'=>array()));
		}

		// Ensure item belongs to this vendor
		$item = $this->db->from('ec_order_items')
			->where('id', $item_id)
			->where('login_id', (int)$this->LOGIN_ID)
			->limit(1)->get()->row();
		if(!$item){
			return api_response(array('status'=>0, 'msg'=>'Item not found', 'data'=>array()));
		}

		$status = null;
		$msg = 'Updated';
		if($order_type === 'in_process'){
			$status = 2;
			$msg = 'Updated and now move in In Process Tab';
		}elseif($order_type === 'shipped'){
			$status = 3;
			$msg = 'Updated and now move in Shipped Tab';
		}elseif($order_type === 'delivered'){
			$status = 10;
			$msg = 'Updated and now move in In Completed Tab';
		}
		if($status === null){
			return api_response(array('status'=>0, 'msg'=>'Invalid action', 'data'=>array()));
		}

		$updated = $this->Query_model->update_data('ec_order_items', array('status'=>(string)$status), array('id'=>$item_id, 'login_id'=>(int)$this->LOGIN_ID));
		if($updated){
			$this->_sync_parent_order_status((int)($item->order_id ?? 0));
			return api_response(array('status'=>1, 'msg'=>$msg, 'data'=>array('item_id'=>$item_id)));
		}
		return api_response(array('status'=>0, 'msg'=>'There is no update', 'data'=>array()));
	}

	public function ajax_order_status_update()
	{
		$orderid = $this->input->post('orderid');
		$order_type = $this->input->post('order_type');

		$msg = '';
		$orderid = (int)$orderid;
		if(!$orderid){
			api_response(array('status'=>0, 'msg'=>'Invalid order', 'data'=>array()));
		}

		// Backward compatible: treat orderid as item_id if it matches a vendor item
		$item = $this->db->from('ec_order_items')
			->where('id', $orderid)
			->where('login_id', (int)$this->LOGIN_ID)
			->limit(1)->get()->row();
		if($item){
			// Delegate to item status update behavior
			$this->input->post('item_id', true);
			$_POST['item_id'] = $orderid;
			return $this->ajax_order_item_status_update();
		}

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$label = '';
			$msg = 'Updated';
			if ($order_type === 'in_process') {
				$label = 'processing';
				$msg = 'Updated and now move in In Process Tab';
			} elseif ($order_type === 'shipped') {
				$label = 'shipped';
				$msg = 'Updated and now move in Shipped Tab';
			} elseif ($order_type === 'delivered') {
				$label = 'delivered';
				$msg = 'Updated and now move in In Completed Tab';
			}
			if ($label !== '') {
				$api_upd = call_api('POST', 'vendor/orders/update-status', [
					'order_id' => $orderid,
					'status' => $label,
				], $headers);
				if ($api_upd['response'] !== null && (int)($api_upd['response']['status'] ?? 0) === 1) {
					return api_response(['status' => 1, 'msg' => $msg, 'data' => $api_upd['response']['data'] ?? []]);
				}
			}
		}
		if($order_type == 'in_process')
		{
			// Use 2 as a generic "in process/confirmed" code for item
			$msg = 'Updated and now move in In Process Tab';
			$updated = $this->Query_model->update_data('ec_order_items', array('status'=>'2'), array('order_id'=>(int)$orderid, 'login_id'=>(int)$this->LOGIN_ID));
	        	if($updated)
	            {
				$this->_sync_parent_order_status($orderid);
				api_response(array('status'=>1, 'msg'=>$msg, 'data'=>$updated));
	            }
	            else
	            {
				api_response(array('status'=>0, 'msg'=>'There is no update', 'data'=>array()));     
	            }
			
		}
        elseif($order_type == 'shipped')
		{
			$msg = 'Updated and now move in Shipped Tab';
			$updated = $this->Query_model->update_data('ec_order_items', array('status'=>'3'), array('order_id'=>(int)$orderid, 'login_id'=>(int)$this->LOGIN_ID));
	        	if($updated){
				$this->_sync_parent_order_status($orderid);
	           	api_response(array('status'=>1, 'msg'=>$msg, 'data'=>$updated));
			}
	           	else
	           	api_response(array('status'=>0, 'msg'=>'There is no update', 'data'=>array()));
			
		}
		else if($order_type == 'delivered')
		{
			// Delivered
			$msg = 'Updated and now move in In Completed Tab';
		
		$updated = $this->Query_model->update_data('ec_order_items', array('status'=>'10'), array('order_id'=>(int)$orderid, 'login_id'=>(int)$this->LOGIN_ID));
		if($updated)
		{
			$this->_sync_parent_order_status($orderid);
			api_response(array('status'=>1, 'msg'=>$msg, 'data'=>$updated));
		}
		else
		api_response(array('status'=>0, 'msg'=>'There is no update', 'data'=>array()));
    
		}
	}

	private function _sync_parent_order_status($order_id)
	{
		$order_id = (int)$order_id;
		if(!$order_id){
			return;
		}

		$this->db->from('ec_order_items');
		$this->db->where('order_id', $order_id);
		$items = $this->db->get()->result();
		if(!$items){
			return;
		}

		$all_delivered = true;
		$any_shipped = false;
		$any_in_process = false;
		$any_cancelled = false;
		$any_pending = false;

		foreach($items as $it){
			$st = (int)($it->status ?? 0);
			if($st === 10){
				// delivered
			} elseif($st === 3){
				$any_shipped = true;
				$all_delivered = false;
			} elseif($st === 2){
				$any_in_process = true;
				$all_delivered = false;
			} elseif($st === 5 || $st === 17){
				$any_cancelled = true;
				$all_delivered = false;
			} else {
				$any_pending = true;
				$all_delivered = false;
			}
		}

		// ec_orders.status is enum: pending/confirmed/shipped/delivered/cancelled
		$new_status = 'pending';
		if($any_cancelled){
			$new_status = 'cancelled';
		}else if($all_delivered){
			$new_status = 'delivered';
		}else if($any_shipped){
			$new_status = 'shipped';
		}else if($any_in_process){
			$new_status = 'confirmed';
		}else if($any_pending){
			$new_status = 'pending';
		}

		$this->Query_model->update_data('ec_orders', array('status' => $new_status), array('id' => $order_id));
	}

	public function ajax_cancel_order()
	{
		$this->validation_cancel();
		if($this->form_validation->run() == false) 
		{
			api_response(array('status'=>0, 'msg'=>remove_extra_for_api(validation_errors()), 'data'=>array()));
		}
		$order_id = (int)$this->input->post('order_id');
		$item_id = (int)$this->input->post('item_id');
		#$get_order_id = get_order_id($order_id);
		#$order_id = $get_order_id->order_id;
		$reason = (string)$this->input->post('reason');
		$comment = (string)$this->input->post('comment');
			
		if(!$order_id){
			api_response(array('status'=>0, 'msg'=>'Invalid order', 'data'=>array()));
		}

		// Cancel only this vendor's items for the order
		$this->db->trans_begin();
		$updated_items = 0;
		if ($item_id > 0) {
			// Item-based cancellation (preferred)
			$item = $this->db->from('ec_order_items')
				->where('id', $item_id)
				->where('order_id', $order_id)
				->where('login_id', (int)$this->LOGIN_ID)
				->limit(1)->get()->row();
			if (!$item) {
				$this->db->trans_rollback();
				api_response(array('status'=>0, 'msg'=>'Item not found', 'data'=>array()));
			}
			$updated_items = $this->Query_model->update_data('ec_order_items', array('status' => 17), array('id' => $item_id, 'login_id' => (int)$this->LOGIN_ID));
		} else {
			// Backward compatible: cancel all vendor items in this order
			$updated_items = $this->Query_model->update_data('ec_order_items', array(
				'status' => 17
			), array('order_id' => $order_id, 'login_id' => (int)$this->LOGIN_ID));
		}

		// Persist cancellation reason/comment into ec_orders.comment (ec_order_items has no reason/comment columns)
		$reason = trim($reason);
		$comment = trim($comment);
		if($reason !== '' || $comment !== ''){
			$existing = $this->db->select('comment')->from('ec_orders')->where('id', $order_id)->get()->row();
			$prev = $existing && isset($existing->comment) ? (string)$existing->comment : '';
			$stamp = date('Y-m-d H:i:s');
			$line = "[Vendor #".(int)$this->LOGIN_ID." Cancel @ ".$stamp."] Reason: ".$reason;
			if($comment !== ''){
				$line .= " | Comment: ".$comment;
			}
			$new_comment = $prev ? ($prev."\n".$line) : $line;
			$this->Query_model->update_data('ec_orders', array('comment' => $new_comment), array('id' => $order_id));
		}

		// Sync parent order status after item/vendor cancellation
		$this->db->from('ec_order_items');
		$this->db->where('order_id', $order_id);
		$items = $this->db->get()->result();
		if($items){
			$all_cancelled = true;
			foreach($items as $it){
				$st = (int)($it->status ?? 0);
				if(!in_array($st, array(5,17), true)){
					$all_cancelled = false;
					break;
				}
			}
			if($all_cancelled){
				$updateOrder = array('status' => 'cancelled');
				if ($this->db->field_exists('cancelled_by', 'ec_orders')) {
					$updateOrder['cancelled_by'] = 'vendor';
				}
				$this->Query_model->update_data('ec_orders', $updateOrder, array('id' => $order_id));
			} else {
				$this->_sync_parent_order_status($order_id);
			}
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			api_response(array('status'=>0, 'msg'=>'Cancel failed', 'data'=>array()));
		} else {
			$this->db->trans_commit();
			api_response(array('status'=>1, 'msg'=>'Cancelled', 'data'=>array('updated_items'=>$updated_items)));
		}
		
	}

	public function validation_cancel()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('order_id', 'Order', 'trim|required');
		$this->form_validation->set_rules('reason', 'Reason', 'trim|required');
	}

	public function finance($vendor_id = NULL)
    {
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$data = array();
		$page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->order => "finance");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
		
		$data['csrf'] = csrf_token(); 
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();	
		
		$data['order_history'] = $this->Dashboard_model->get_order_qty($this->LOGIN_ID);		
		//$data['delivered_item_val'] = $this->Dashboard_model->get_delivered_order($this->LOGIN_ID);
		$data['total_delivered'] = $this->Dashboard_model->get_finance_orderDeliverCount($this->LOGIN_ID);	
        $order_counts = $this->Order_model->item_count_vendor_wise(array('vendor_id'=>$this->LOGIN_ID));
	
		$data['vendor_id']      = $this->LOGIN_ID;
		$data['order_counts']   = $order_counts;
		
		$this->load->template("$this->TYPE/order/finance",$data);		
	}
	
	public function ajax_get_finance_order()
	{
		$data = array();
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
		
		$vendor_id = $this->input->post('vendor_id');
		$finance_order_record = $this->Query_model->get_data('ec_order_item', array('supplier_id'=> $vendor_id),'',array('start'=>$page, 'length'=>$length));
		//echo "<pre>"; print_r($finance_order_record); echo "</pre>";die;
       

        foreach ($finance_order_record as $orderlistdata) {
            
				$row = array();
                $currency = current_currency($orderlistdata->currency_id);
                $order_status = order_status();
                $order_uid    = get_order_uid($orderlistdata->order_id);
                $iso_code = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol = isset($currency->symbol) ? $currency->symbol : '';

				$row['order_id']        = $orderlistdata->order_id;
				$row['order_date']      = date("Y-m-d h:i a", strtotime($orderlistdata->date_created));
				$row['order_uid_view']  = "<a href='/$this->TYPE/order/finance_detail/$order_uid->order_uid'>".$order_uid->order_uid.'</a>'; 
                $row['order_uid']       = $order_uid->order_uid;                
                $row['quantity']        = $orderlistdata->quantity;
                $row['status']          = $order_status[$orderlistdata->status];
                $row['total']           = $symbol.' '.$orderlistdata->total;
				$row['payment_mode']    = $order_uid->payment_mode;
             
                $data[] = $row;
         }

         $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Query_model->count_all('ec_order_item', array('supplier_id'=>$vendor_id)),
                "recordsFiltered" => $this->Query_model->count_filtered('ec_order_item', array('supplier_id'=>$vendor_id)),
                "data" => $data,
                );
        echo json_encode($output);

        }
		
		public function finance_detail($order_uid = NULL)
        {
            if(!logged_in()) redirect("$this->TYPE/auth/login");
            $page_lang = get_page_language_data('admin_page_lang');
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", 'Order detail' => "");
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;
            $data['csrf'] = csrf_token(); 
            $data['TYPE'] = $this->TYPE;

            $data['order_uid'] = $order_uid;
            $data['page_count'] = page_count();
            $condition = array('order_uid' => $order_uid);
            $order_detail    = $this->Query_model->get_data_obj('ec_order', $condition);
        
            $data['order_detail'] = $order_detail;

            $customer_id = array('customer_id' =>$order_detail->customer_id);

            $data['customer_detail'] = $this->Query_model->get_data_obj('ec_customer', $customer_id);
			
            $data['country_name'] = isset($data['customer_detail']->country) ?  get_country($data['customer_detail']->country)->name : '';
			
			$curr_id = array('admin_id' => $this->LOGIN_ID);
			$data['vendor_info'] = $this->Query_model->get_data_obj('ec_admin', $curr_id);

			//echo "<pre>"; print_r($this->login_id) ; echo "</pre>";die;
			
            $shipping_id = array('shipping_address_id' => $data['order_detail']->shipping_address_id);

            $data['shipping_address'] = $this->Query_model->get_data_obj('ec_shipping_address', $shipping_id);
            $data['shipping_country_name'] = isset($data['shipping_address']->country) ? get_country($data['shipping_address']->country)->name :'' ;
			
			$orderlist = $this->Order_model->get_finance_order_detail( $data['order_detail']->customer_id, $data['order_detail']->order_id );

			$order_info = array();
			$ln = current_language();
			foreach($orderlist as $row) 
			{
				
				$category_id        = get_categories_id($row->product_id);
				$category_name      = get_categories($category_id->category_id);
				
				$currency           = current_currency($row->currency_id);
				$iso_code           = isset($currency->iso_code) ? $currency->iso_code : '';
				$symbol             = isset($currency->symbol) ? $currency->symbol : '';
				
				$unit_price = ($row->subtotal / $row->quantity);
				$unit_price = sprintf('%.02f',$unit_price);

				$unit_gross_price = $row->subtotal;
				if(is_numeric($row->tax)){
					$unit_gross_price+= $row->tax;
				}
				if(is_numeric($row->shipping)){
					$unit_gross_price+=$row->shipping;
				}
				
				$product_id         = isset($row->product_id) ? $row->product_id :'';
				$product_obj        = $this->Query_model->get_data_obj('ec_product',array('product_id' => $row->product_id));
				if($ln == 12){
					$product_obj->post_title = ($product_obj->post_title_es) ? $product_obj->post_title_es : $product_obj->post_title;
					$product_obj->post_content = ($product_obj->post_content_es) ? $product_obj->post_content_es : $product_obj->post_content;
					$product_obj->post_slug = ($product_obj->post_slug) ? $product_obj->post_slug : '';
				}

				if($product_obj->type == 'simple'){
					$product_gallery = $this->Query_model->get_data_obj('ec_gallery',array('product_id' => $row->product_id), array(), array('length' => 1));
					if($product_gallery){
						$productimg = base_url().'assets/uploads/files/'.$product_gallery->file_name;
					}else{
						$productimg = base_url().'assets/default_images/product.jpg';
					}
				}else{
					$product_gallery = $this->Query_model->get_data_obj('ec_product_variation',array('product_id' => $row->product_id, 'attribute_item_id' => $row->attribute_item_id));
                    
					if($product_gallery){
						$productimg = base_url().'assets/uploads/'.$product_gallery[0]->_thumbnail_id;
					}else{
						$productimg = base_url().'assets/default_images/product.jpg';
					}
				}

				$attribute_item = array();
					if($row->attribute_item_id){
						$att_array = explode(',',$row->attribute_item_id);
						if($att_array){
							$attribute_item_obj = $this->Query_model->get_data('ec_attribute_item',array('attribute_item_id' => $att_array));
							if($attribute_item_obj){
								foreach($attribute_item_obj as $at){
									$attribute_item[] = $at->name;
								}
							}
						}
					}
				/*************dispay item************/	
				$row->category_name = $category_name->name;
				$row->product_name   = $product_obj->post_title;
				$row->quantity      = $row->quantity;
				$row->subtotal      = $row->subtotal;
				$row->tax           = $row->tax;
				$row->currency_symbol = $symbol;
				$row->unit_price    = $unit_price;
				$row->iso_code       = $iso_code;
				$row->image         = $productimg;
				$row->attribute_item = $attribute_item;
				$row->gtotal    = $unit_price*$row->quantity;
				
				$order_info[] = $row;
			}	
			$data['order_info'] = $order_info;
		    $this->load->template("$this->TYPE/order/index_finance_detail", $data);
        }	


	



####################NEW CHANGES#########################

	public function ajax_get_customer_order()
	{
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
	
		$customer_uid = $this->input->post('customer_uid');
		$orderlist = $this->Order_model->get_order_backend_data($customer_uid);
        $data = array();

        foreach ($orderlist as $orderlistdata) {
            
				$row = array();
                $currency               = current_currency($orderlistdata->currency_id);

                $iso_code = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol = isset($currency->symbol) ? $currency->symbol : '';

                $row['order_id']        = $orderlistdata->order_id;
				$row['order_uid_view']       = "<a href='/admin/order/order_detail/$orderlistdata->order_uid'>".$orderlistdata->order_uid.'</a>';
                $row['order_uid']       = $orderlistdata->order_uid;
                $row['customer_name']   = get_customer($orderlistdata->customer_id)->fname.' '.get_customer($orderlistdata->customer_id)->lname;
                $row['quantity']        = $orderlistdata->quantity;
                $row['total']           = $iso_code.' '.$symbol.' '.$orderlistdata->ordertotal;
                $row['order_date']      = date("Y-m-d", strtotime($orderlistdata->date_created));
                $row['delivery_date']   = date("Y-m-d", strtotime($orderlistdata->date_created."+7 day"));
                $row['status']          = order_status($orderlistdata->status);
                $data[] = $row;
         }

         $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Order_model->count_all(),
                "recordsFiltered" => $this->Order_model->count_filtered($customer_uid),
                "data" => $data,
                );
        echo json_encode($output);

        }

        public function update_ajax_status()
        {
            $order_id = $this->input->post('orderid');
            $statusval = $this->input->post('statusval');
            $post_data = array('status' => $statusval);
            $condition = array('order_id' => $order_id);
            $this->Query_model->update_data('ec_order', $post_data, $condition);
            $data['message']  = 'Status Changed Successfully';
            echo json_encode($data);
            die();
        }


        public function order_detail($order_uid = NULL)
        {
            if(!logged_in()) redirect("$this->TYPE/auth/login");
            $page_lang = get_page_language_data('admin_page_lang');
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->order => "");
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;
            $data['csrf'] = csrf_token(); 
            $data['TYPE'] = $this->TYPE;

            $data['order_uid'] = $order_uid;
            $data['page_count'] = page_count();
            $condition = array('order_uid' => $order_uid);
            $data['order_detail']    = $this->Query_model->get_data_obj('ec_order', $condition);

            $customer_id = array('customer_id' =>$data['order_detail']->customer_id);

            $data['customer_detail'] = $this->Query_model->get_data_obj('ec_customer', $customer_id);

            $data['country_name'] = isset($data['customer_detail']->country) ?  get_country($data['customer_detail']->country)->name : '';

            $shipping_id = array('shipping_address_id' => $data['order_detail']->shipping_address_id);

            $data['shipping_address'] = $this->Query_model->get_data_obj('ec_shipping_address', $shipping_id);
            $data['shipping_country_name'] = isset($data['shipping_address']->country) ? get_country($data['shipping_address']->country)->name :'' ;

            $this->load->template("$this->TYPE/order/index_order_detail", $data);
        }


        public function ajax_order_detail()
        {
            $length = (isset($_POST['length']))?$_POST['length']: page_count();
            $page  = (isset( $_POST['page']))?$_POST['page']: 1;
            $order_uid = $this->input->post('order_uid');
            $orderlist = $this->Order_model->get_data_by_order_uid($order_uid);
            $condition = array('order_uid' => $order_uid);
            $currency = $this->Query_model->get_data_obj('ec_order', $condition);

            $order_status    = order_status();
            $currencysymbol  = get_currency($currency->currency_id);
            $currency_symbol = isset($currencysymbol->symbol) ? $currencysymbol->symbol : '';
            $iso_code        = isset($currencysymbol->iso_code) ? $currencysymbol->iso_code : '';

            $order_item_status = order_status();

            $data = array();

            $ln = current_language();
            $unit_gross_price =  $shipping = $tax = 0;
            $order_id_arr = $order_id_arr1 = array();
            foreach ($orderlist as $row) {
                
                //print_r($row); exit;
                
                $product_id         = isset($row->product_id) ? $row->product_id :'';
                $product_obj        = $this->Query_model->get_data_obj('ec_product',array('product_id' => $row->product_id));
                if($ln == 12){
                    $product_obj->post_title = ($product_obj->post_title_es) ? $product_obj->post_title_es : $product_obj->post_title;
                    $product_obj->post_content = ($product_obj->post_content_es) ? $product_obj->post_content_es : $product_obj->post_content;
                    $product_obj->post_slug = ($product_obj->post_slug) ? $product_obj->post_slug : '';
                }

                if($product_obj->type == 'simple'){
                    $product_gallery = $this->Query_model->get_data_obj('ec_gallery',array('product_id' => $row->product_id), array(), array('length' => 1));
                    if($product_gallery){
                        $productimg = base_url().'assets/uploads/files/'.$product_gallery->file_name;
                    }else{
                        $productimg = base_url().'assets/default_images/product.jpg';
                    }
                }else{
                    $product_gallery = $this->Query_model->get_data_obj('ec_product_variation',array('product_id' => $row->product_id, 'attribute_item_id' => $row->attribute_item_id));
                    if($product_gallery){
                        $productimg = base_url().'assets/uploads/'.$product_gallery->_thumbnail_id;
                    }else{
                        $productimg = base_url().'assets/default_images/product.jpg';
                    }
                }


                $attribute_item = array();
                    if($row->attribute_item_id){
                        $att_array = explode(',',$row->attribute_item_id);
                        if($att_array){
                            $attribute_item_obj = $this->Query_model->get_data('ec_attribute_item',array('attribute_item_id' => $att_array));
                            if($attribute_item_obj){
                                foreach($attribute_item_obj as $at){
                                    $attribute_item[] = $at->name;
                                }
                            }
                        }
                    }


                $category_id        = get_categories_id($row->product_id);
                $category_name      = get_categories($category_id->category_id);
                $currency           = current_currency($row->currency_id);
                $iso_code           = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol             = isset($currency->symbol) ? $currency->symbol : '';

                $unit_price = ($row->subtotal / $row->quantity);
                $unit_price = sprintf('%.02f',$unit_price);

                $unit_gross_price = $row->subtotal;
                if(is_numeric($row->tax)){
                    $unit_gross_price+= $row->tax;
                }
                if(is_numeric($row->shipping)){
                    $unit_gross_price+=$row->shipping;
                }

                $row->url           = get_permalink($product_obj->post_slug);
                $row->order_id      = $row->order_id;
                $row->order_uid     = $row->order_uid;
                $row->product_id    = $row->product_id;
                $row->category_name = $category_name->name;
                $row->order_item_uid= $row->order_item_uid;
                $row->num_items     = $row->num_items_sold;
                $row->productname   = $product_obj->post_title;
                $row->quantity      = $row->quantity;
                $row->subtotal      = $row->subtotal;
                $row->tax           = $row->tax;
                $row->currency_symbol = $symbol;
                $row->unit_price    = $unit_price;
                $row->iso_code       = $iso_code;
                $row->image         = $productimg;
                $row->attribute_item = $attribute_item;
                $row->orderstatus   = $order_status[$row->status];
                $row->order_detail_status = $row->status;
                $row->delivery_date = date("d-M-Y", strtotime($row->date_created."+7 day"));
                $row->order_date    = date("d-M-Y", strtotime($row->date_created));
                $row->product_link  = '/product/detail/'.$product_obj->post_slug;
                $order_id_arr[$row->order_item_uid][] = $row;
                $tax               = $row->order_tax;
                $shipping          = $row->order_shipping;
                $unit_gross_price  = $row->order_total;
            }

            $response = array(
                    "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                    "recordsTotal"      => $this->Order_model->count_all('ec_order'),
                    "recordsFiltered"   => $this->Order_model->count_filtered('ec_order'),
                    "data"              => $order_id_arr,
                    "tax"               => $tax,
                    "currency_symbol"   => $currency_symbol,
                    "iso_code"          => $iso_code,
                    "shipping"          => $shipping,
                    "unit_gross_price"  => $unit_gross_price,
                    "order_item_status" => $order_item_status,
                    );

            echo json_encode($response);

        }


         public function update_ajax_item_status()
        {
            $status        = $this->input->post('status');
            $order_item_id = $this->input->post('order_item_id');
            $post_data     = array('status' => $status);
            $condition     = array('order_item_id' => $order_item_id);
            $this->Query_model->update_data('ec_order_item', $post_data, $condition);
            $data['message'] = 'Item status changed successfully';
            echo json_encode($data);
            die();
        }
		
		public function checkall_status_change(){

	    $id = $this->input->post('id');
		$status = $this->input->post('status');		
		$ids = json_decode($id);
		//echo "<pre>"; print_r($_POST);exit;
		if($status == 'active'){
			$status_val = 0;
		}elseif($status == 'inactive'){
			$status_val = 1;
		}		
        $data = array();
        //$update = $this->User_model->checkall_supplier_status_change($id,$status);
		if($ids && $this->LOGIN_ID && $this->TYPE == 'admin'){
			$update = $this->Query_model->update_data('ec_order',array('status' => $status),array('order_id' => $ids));
		}
		
        echo json_encode(array('success' => $update));
		
		}


#THIS FUNCTION USE FOR THE CANCEL ORDER START
    public function cancel_order($customer_uid = NULL)
    {               
        if(!logged_in()) redirect("$this->TYPE/auth/login");
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->cancel_order => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token(); 
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count(); 
        $data['customer_uid'] = $customer_uid;
        
        $this->load->template("$this->TYPE/order/cancel_order",$data);
       //$this->load->view('imageUploadForm'); 
    }



    public function ajax_get_cancel_order()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
    
        $customer_uid = $this->input->post('customer_uid');
        $orderlist = $this->Order_model->get_cancel_order_data($customer_uid);
	
        $data = array();

        foreach ($orderlist as $orderlistdata) {
            
                $row = array();
                $currency               = current_currency($orderlistdata->currency_id);

                $iso_code = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol = isset($currency->symbol) ? $currency->symbol : '';
                

                $row['order_id']        = $orderlistdata->order_id;
                $row['order_uid']       = $orderlistdata->order_uid;
                $row['customer_name']   = get_customer($orderlistdata->customer_id)->fname.' '.get_customer($orderlistdata->customer_id)->lname;
                $row['order_uid_view']       = "<a href='/admin/order/order_detail/$orderlistdata->order_uid'>".$orderlistdata->order_uid.'</a>';

                $row['quantity']        = $orderlistdata->quantity;
                $row['total']           = $iso_code.' '.$symbol.' '.$orderlistdata->total;
                $row['order_date']      = date("Y-m-d", strtotime($orderlistdata->date_created));
                //$row['order_date']      = $order_date;
                $row['delivery_date']   = date("Y-m-d", strtotime($orderlistdata->date_created."+7 day"));
                $row['status']          = order_status($orderlistdata->status);
                $data[] = $row;
         }

         $output = array(
                "draw" => isset($_POST['draw'])? $_POST['draw']:'',
                "recordsTotal" => $this->Order_model->count_all_cancel('ec_order'),
                "recordsFiltered" => $this->Order_model->count_filtered_cancel('ec_order'),
                "data" => $data,
                );
        
        //echo "<pre>";print_r( $output); echo "</pre>";
        
        
        echo json_encode($output);

        }

    #THIS FUNCTION USE FOR THE CANCEL ORDER END


#THIS FUNCTION USE FOR THE COMPLETE ORDER START
    public function complete_order($customer_uid = NULL)
    {               
        if(!logged_in()) redirect("$this->TYPE/auth/login");
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->complete_order => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token(); 
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count(); 
        $data['customer_uid'] = $customer_uid;
        
        $this->load->template("$this->TYPE/order/complete_order",$data);
       //$this->load->view('imageUploadForm'); 
    }



    public function ajax_get_complete_order()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
    
        $customer_uid = $this->input->post('customer_uid');
        $orderlist = $this->Order_model->get_complete_order_data($customer_uid);
        $data = array();

        foreach ($orderlist as $orderlistdata) {
            
                $row = array();
                $currency               = current_currency($orderlistdata->currency_id);

                $iso_code = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol = isset($currency->symbol) ? $currency->symbol : '';

                $row['order_id']        = $orderlistdata->order_id;
                $row['order_uid']       = $orderlistdata->order_uid;
                $row['order_uid_view']       = "<a href='/admin/order/order_detail/$orderlistdata->order_uid'>".$orderlistdata->order_uid.'</a>';

                $row['customer_name']   = get_customer($orderlistdata->customer_id)->fname.' '.get_customer($orderlistdata->customer_id)->lname;
                $row['quantity']        = $orderlistdata->quantity;
                $row['total']           = $iso_code.' '.$symbol.' '.$orderlistdata->total;
                $row['order_date']      = date("Y-m-d", strtotime($orderlistdata->date_created));
                //$row['order_date']      = $order_date;
                $row['delivery_date']   = date("Y-m-d", strtotime($orderlistdata->date_created."+7 day"));
                $row['status']          = order_status($orderlistdata->status);
                $data[] = $row;
         }

         $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Order_model->count_all_completed('ec_order'),
                "recordsFiltered" => $this->Order_model->count_filtered_completed('ec_order'),
                "data" => $data,
                );
        
        //echo "<pre>";print_r( $output); echo "</pre>";
        
        
        echo json_encode($output);

        }

#THIS FUNCTION USE FOR THE COMPLETE ORDER END

	public function export_pending_csv(){ 
	  
		$filename = 'pending_order_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$filename"); 
		header("Content-Type: application/csv; ");	
		
		
		$customer_uid = $this->input->post('customer_uid');
		$orderlist = $this->Order_model->get_order_backend_data_pending_csv($customer_uid);		
        $data = array();
        foreach ($orderlist as $orderlistdata) {
            
				$row = array();
                $currency               = current_currency($orderlistdata->currency_id);

                $iso_code = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol = isset($currency->symbol) ? $currency->symbol : '';

                $row['order_id']        = $orderlistdata->order_id;				
                $row['order_uid']       = $orderlistdata->order_uid;
                $row['customer_name']   = get_customer($orderlistdata->customer_id)->fname.' '.get_customer($orderlistdata->customer_id)->lname;
                $row['quantity']        = $orderlistdata->quantity;
                $row['total']           = $iso_code.' '.$symbol.' '.$orderlistdata->ordertotal;
                $row['order_date']      = date("Y-m-d", strtotime($orderlistdata->date_created));				
                $row['delivery_date']   = date("Y-m-d", strtotime($orderlistdata->date_created."+7 day"));
                $row['status']          = order_status($orderlistdata->status);
                $data[] = $row;
         }		
		
		//echo "<pre>"; print_r($list); echo "</pre>";	die;
		
		
	   // file creation  
	    $file = fopen('php://output', 'w'); 
		$header = array("order_id","order_uid","customer_name","quantity",'total','order_date','delivery_date','status');   
	  
	    fputcsv($file, $header);
	      
	    foreach ($data as $key=>$line){ 
	   	   fputcsv($file,$line); 
	    }
	   fclose($file); 
	   exit; 
	}
	
	public function export_cancel_csv(){ 
	  
		$filename = 'cancel_order_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$filename"); 
		header("Content-Type: application/csv; ");	
		
		
		$customer_uid = $this->input->post('customer_uid');
        $orderlist = $this->Order_model->get_cancel_order_data_csv($customer_uid);
	
        $data = array();

        foreach ($orderlist as $orderlistdata) {
            
                $row = array();
                $currency               = current_currency($orderlistdata->currency_id);

                $iso_code = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol = isset($currency->symbol) ? $currency->symbol : '';
                

                $row['order_id']        = $orderlistdata->order_id;
                $row['order_uid']       = $orderlistdata->order_uid;              
                $row['quantity']        = $orderlistdata->quantity;
                $row['total']           = $iso_code.' '.$symbol.' '.$orderlistdata->total;
                $row['order_date']      = date("Y-m-d", strtotime($orderlistdata->date_created));
                //$row['order_date']      = $order_date;
                $row['delivery_date']   = date("Y-m-d", strtotime($orderlistdata->date_created."+7 day"));
                $row['status']          = order_status($orderlistdata->status);
                $data[] = $row;
         }
	
		
		//echo "<pre>"; print_r($list); echo "</pre>";	die;
		
		
	   // file creation  
	    $file = fopen('php://output', 'w'); 
		$header = array("order_id","order_uid","customer_name","quantity",'total','order_date','delivery_date','status');   
	  
	    fputcsv($file, $header);
	      
	    foreach ($data as $key=>$line){ 
	   	   fputcsv($file,$line); 
	    }
	   fclose($file); 
	   exit; 
	}
	
	public function export_comp_csv(){ 
	  
		$filename = 'comp_order_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$filename"); 
		header("Content-Type: application/csv; ");	
		
		
		$customer_uid = $this->input->post('customer_uid');
        $orderlist = $this->Order_model->get_complete_order_data_csv($customer_uid);
        $data = array();

        foreach ($orderlist as $orderlistdata) {
            
                $row = array();
                $currency               = current_currency($orderlistdata->currency_id);

                $iso_code = isset($currency->iso_code) ? $currency->iso_code : '';
                $symbol = isset($currency->symbol) ? $currency->symbol : '';

                $row['order_id']        = $orderlistdata->order_id;
                $row['order_uid']       = $orderlistdata->order_uid;
                
                $row['customer_name']   = get_customer($orderlistdata->customer_id)->fname.' '.get_customer($orderlistdata->customer_id)->lname;
                $row['quantity']        = $orderlistdata->quantity;
                $row['total']           = $iso_code.' '.$symbol.' '.$orderlistdata->total;
                $row['order_date']      = date("Y-m-d", strtotime($orderlistdata->date_created));
                //$row['order_date']      = $order_date;
                $row['delivery_date']   = date("Y-m-d", strtotime($orderlistdata->date_created."+7 day"));
                $row['status']          = order_status($orderlistdata->status);
                $data[] = $row;
         }	
	   // file creation  
	    $file = fopen('php://output', 'w'); 
		$header = array("order_id","order_uid","customer_name","quantity",'total','order_date','delivery_date','status');   
	  
	    fputcsv($file, $header);
	      
	    foreach ($data as $key=>$line){ 
	   	   fputcsv($file,$line); 
	    }
	   fclose($file); 
	   exit; 
	}

	
	
}
