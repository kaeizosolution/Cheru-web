<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Dashboard extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Vendor_model');
        $this->load->model('Dashboard_model');
        $this->load->model('Order_model');
		$this->config->load('custom_config');
		$this->load->helper('api');
        $this->TYPE = 'vendor';
        
        $session_data = $this->session->userdata($this->TYPE);
		$this->VENDOR_ID = isset($session_data['login_id']) ? (int)$session_data['login_id'] : (isset($session_data['vendor_id']) ? (int)$session_data['vendor_id'] : 0);
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

	private function _is_token_expired($token)
	{
		$parts = explode('.', (string)$token);
		if (count($parts) !== 2) return true;
		$padding = strlen($parts[0]) % 4;
		if ($padding) $parts[0] .= str_repeat('=', 4 - $padding);
		$claims = @json_decode(@base64_decode(strtr($parts[0], '-_', '+/')), true);
		if (!is_array($claims) || !isset($claims['exp'])) return true;
		return time() >= (int)$claims['exp'];
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

		// Return session token only if it is still valid (not expired)
		if (is_array($sess) && !empty($sess['access_token'])) {
			$existing = (string)$sess['access_token'];
			if (!$this->_is_token_expired($existing)) {
				return $existing;
			}
			// Token expired — clear it and fall through to regeneration
			unset($sess['access_token']);
		}
		if (!empty($_SESSION['token']) && !$this->_is_token_expired($_SESSION['token'])) {
			return (string)$_SESSION['token'];
		}

		// Self-generate a fresh 1-year token from the session vendor_id
		if (is_array($sess) && (!empty($sess['vendor_id']) || !empty($sess['login_id']))) {
			$vendor_id = !empty($sess['login_id']) ? (int)$sess['login_id'] : (int)$sess['vendor_id'];
			$token = $this->_sign_token(['sub' => $vendor_id, 'type' => 'access', 'role' => 'vendor'], 31536000);
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
		
	function time_elapsed_string($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year','m' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour','i' => 'minute', 's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}

		if (!$full) $string = array_slice($string, 0, 1);
		$string ? implode(', ', $string) . ' ago' : 'just now';
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	 
	}
public function index()  
    {
        $use_api = true; // Force api-driven mode for vendor dashboard to ensure client-side API requests run
        $headers = $this->_build_api_headers();
        // Do not hard-block the dashboard if token is missing; fall back to legacy DB widgets.

        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->dashboard => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        
        if ($this->uri->segment(4) === FALSE){
            $filter_id = 0;
        }else{
            $filter_id = $this->uri->segment(4);
        }           
        
        // Filter by THIS vendor for products using dynamic table and column detection
        $product_table = $this->Dashboard_model->_product_table();
        $vendor_col = $this->Dashboard_model->_product_vendor_column($product_table);
        $data['count_all_product'] = 0;
        if ($vendor_col) {
            $data['count_all_product'] = $this->Query_model->count_all($product_table, array($vendor_col => $this->VENDOR_ID));
        }
        $data['count_all_visiter'] = $this->Query_model->count_all('ec_customer', array('status' => '1'));
        
        $vendor_id = $this->VENDOR_ID; // Use the class variable

        // Determine display currency & rate
        $display_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
        $all_currencies = $this->db
            ->select('currency_id, symbol, iso_code, rate, basic')
            ->from('ec_currency')
            ->where('status', '1')
            ->get()->result();

        $display_rate   = 1.0;
        $display_symbol = '$';
        $display_code   = 'USD';

        foreach ($all_currencies as $c) {
            if ((int)$c->basic === 1) {
                $display_rate   = (float)$c->rate ?: 1.0;
                $display_symbol = html_entity_decode($c->symbol);
                $display_code   = $c->iso_code;
                break;
            }
        }
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

        // Dashboard Stats filtered by Vendor & Display Currency
        $data['salevalue_m']            = $this->Dashboard_model->orders_salevalue_m($vendor_id, $display_rate);
        $data['salevalue_monthly']       = $this->Dashboard_model->salevalue_monthly_graph($vendor_id, $display_rate);
        $data['today_sale_data']        = $this->Dashboard_model->today_sale($vendor_id, $display_rate);
        
        // This triggers the calculation logic below
        $this->vendor_amount_transfer($vendor_id);
        
        $data['subtotal'] = $this->Dashboard_model->total_income($filter_id, $display_rate);
        $subtotal_value = 0;
        if (is_object($data['subtotal']) && isset($data['subtotal']->total)) {
            $subtotal_value = (float)$data['subtotal']->total;
        } elseif (is_numeric($data['subtotal'])) {
            $subtotal_value = (float)$data['subtotal'];
        }
        $data['sale_amount'] = number_format($subtotal_value, 2, '.', '');
        $data['top_selling_products'] = $this->Dashboard_model->top_selling_products();      
        $data['new_product'] = $this->Dashboard_model->new_product($filter_id);
        
        // Image logic for top selling products
        foreach($data['top_selling_products'] as $k =>$val){
            $args_image = array('product_id' => $val->product_id );
            $thumb_img = $this->Query_model->get_data('ec_gallery',$args_image);
            if(isset($thumb_img) && !empty($thumb_img)){
                $val->image_path = base_url().'assets/uploads/files/'.$thumb_img[0]->file_name;
            }   
        }
        
        $data['sold_product'] = $this->Dashboard_model->sold_product(); 
        $data['recent_product'] = $this->Dashboard_model->recent_product(); 
        
        // Status Counts
        // Vendor scoped counts
        $vendor_id = (int)$this->VENDOR_ID;
        $data['pending_order']      = $this->Dashboard_model->get_pending_order($vendor_id);
        $data['approved_order']     = $this->Dashboard_model->get_approv_order($vendor_id);
        $data['cancelled_order']    = $this->Dashboard_model->get_cancelled_order($vendor_id);
        $data['shipped_order']      = $this->Dashboard_model->get_shipped_order($vendor_id);
        $data['delivered_order']    = $this->Dashboard_model->get_delivered_order($vendor_id);

        // Total orders card should reflect vendor's items
        $orderCounts = $this->Order_model->item_count_vendor_wise(array('vendor_id'=>$vendor_id));
        $data['orders'] = $orderCounts['total_items'] ?? 0;

        // Fetch 7-day sales graph data
        $data['salevalue_past7days'] = $this->Dashboard_model->salevalue_past7days_graph($vendor_id, $display_rate);

        // Orders Trend Calculation (vs Last Week)
        // Orders count past 7 days
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        $orders_this_week = $this->db->count_all_results();

        // Orders count prior 7 days (7-14 days ago)
        $this->db->from('ec_order_items');
        $this->db->where('login_id', $vendor_id);
        $this->db->where('date_added >=', date('Y-m-d H:i:s', strtotime('-14 days')));
        $this->db->where('date_added <', date('Y-m-d H:i:s', strtotime('-7 days')));
        $orders_last_week = $this->db->count_all_results();

        if ($orders_last_week > 0) {
            $data['orders_trend_pct'] = round((($orders_this_week - $orders_last_week) / $orders_last_week) * 100);
        } else {
            $data['orders_trend_pct'] = $orders_this_week > 0 ? 100 : 0;
        }

        // Sales Value Trend Calculation (vs Last Month) - Currency aware
        // Sales past 30 days
        $this_month_row = $this->db
            ->select("ROUND(SUM(subtotal / IFNULL(currency_rate, 1)) * {$display_rate}, 2) as total", false)
            ->from('ec_order_items')
            ->where('login_id', $vendor_id)
            ->where('date_added >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->get()->row();
        $sales_this_month = (float)($this_month_row->total ?? 0);

        // Sales prior 30 days (30-60 days ago) - Currency aware
        $last_month_row = $this->db
            ->select("ROUND(SUM(subtotal / IFNULL(currency_rate, 1)) * {$display_rate}, 2) as total", false)
            ->from('ec_order_items')
            ->where('login_id', $vendor_id)
            ->where('date_added >=', date('Y-m-d H:i:s', strtotime('-60 days')))
            ->where('date_added <', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->get()->row();
        $sales_last_month = (float)($last_month_row->total ?? 0);

        if ($sales_last_month > 0) {
            $data['sales_trend_pct'] = round((($sales_this_month - $sales_last_month) / $sales_last_month) * 100);
        } else {
            $data['sales_trend_pct'] = $sales_this_month > 0 ? 100 : 0;
        }

        // Next Payout Date & Estimation
        $today_day = (int)date('j');
        if ($today_day <= 15) {
            $data['next_payout_date'] = date('M 15');
        } else {
            $data['next_payout_date'] = date('M 15', strtotime('next month'));
        }

        $vendor_obj = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
        $commission_pct = isset($vendor_obj->commission) ? (float)$vendor_obj->commission : 10.0;

        // Current month sales - Currency aware
        $cur_month_row = $this->db
            ->select("ROUND(SUM(subtotal / IFNULL(currency_rate, 1)) * {$display_rate}, 2) as total", false)
            ->from('ec_order_items')
            ->where('login_id', $vendor_id)
            ->where('MONTH(date_added)', date('n'))
            ->where('YEAR(date_added)', date('Y'))
            ->get()->row();
        $current_month_sales = (float)($cur_month_row->total ?? 0);

        $estimated_payout_amount = $current_month_sales * (100 - $commission_pct) / 100;
        $data['estimated_payout'] = number_format($estimated_payout_amount, 2);

        // Action Required Widget Counts
        // 1. Unpaid Invoices
        $this->db->from('ec_order_items as eoi');
        $this->db->join('ec_orders as eo', 'eo.id = eoi.order_id', 'left');
        $this->db->where('eoi.login_id', $vendor_id);
        $this->db->group_start()
            ->where('eo.payment_status IS NULL')
            ->or_where('LOWER(eo.payment_status) !=', 'paid')
        ->group_end();
        $data['unpaid_invoices_count'] = $this->db->count_all_results();

        // 2. Out of Stock
        $product_table = $this->Dashboard_model->_product_table();
        $vendor_col = $this->Dashboard_model->_product_vendor_column($product_table);
        $data['out_of_stock_count'] = 0;
        if ($vendor_col) {
            $this->db->from($product_table);
            $this->db->where($vendor_col, $vendor_id);
            $this->db->where('stock <=', 0);
            $data['out_of_stock_count'] = $this->db->count_all_results();
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
        $data['returns_pending_count'] = $this->db->count_all_results();

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

        $data['health_score'] = $health_score;
        if ($health_score >= 90) {
            $data['health_label'] = 'Excellent Performance';
            $data['health_color'] = 'var(--success)';
            $data['health_desc'] = 'Your store metrics are in the top 5%';
        } elseif ($health_score >= 70) {
            $data['health_label'] = 'Good Performance';
            $data['health_color'] = 'var(--primary)';
            $data['health_desc'] = 'Keep up the good work to maintain high scores';
        } else {
            $data['health_label'] = 'Needs Attention';
            $data['health_color'] = 'var(--danger)';
            $data['health_desc'] = 'Focus on processing orders faster to reduce cancellations';
        }

		// API override (completely maps all view metrics from the API to be fully API-driven)
		if ($use_api && !empty($headers)) {
			$api_dash = call_api('GET', 'vendor/dashboard', null, $headers);
			if ($api_dash['response'] !== null && (int)($api_dash['response']['status'] ?? 0) === 1 && is_array($api_dash['response']['data'] ?? null)) {
				$api_data = $api_dash['response']['data'];
				
				$data['count_all_product'] = (int)($api_data['total_products'] ?? 0);
				$data['orders'] = (int)($api_data['total_orders'] ?? 0);
				$data['sale_amount'] = number_format((float)($api_data['total_earnings'] ?? 0.00), 2, '.', '');
				
				if (isset($api_data['orders_trend_pct'])) {
					$data['orders_trend_pct'] = (int)$api_data['orders_trend_pct'];
				}
				if (isset($api_data['sales_trend_pct'])) {
					$data['sales_trend_pct'] = (int)$api_data['sales_trend_pct'];
				}
				if (isset($api_data['next_payout_date'])) {
					$data['next_payout_date'] = $api_data['next_payout_date'];
				}
				if (isset($api_data['estimated_payout'])) {
					$data['estimated_payout'] = $api_data['estimated_payout'];
				}
				if (isset($api_data['unpaid_invoices_count'])) {
					$data['unpaid_invoices_count'] = (int)$api_data['unpaid_invoices_count'];
				}
				if (isset($api_data['out_of_stock_count'])) {
					$data['out_of_stock_count'] = (int)$api_data['out_of_stock_count'];
				}
				if (isset($api_data['returns_pending_count'])) {
					$data['returns_pending_count'] = (int)$api_data['returns_pending_count'];
				}
				if (isset($api_data['health_score'])) {
					$data['health_score'] = (int)$api_data['health_score'];
				}
				if (isset($api_data['health_label'])) {
					$data['health_label'] = $api_data['health_label'];
				}
				if (isset($api_data['health_color'])) {
					$data['health_color'] = $api_data['health_color'];
				}
				if (isset($api_data['health_desc'])) {
					$data['health_desc'] = $api_data['health_desc'];
				}
				if (isset($api_data['salevalue_past7days'])) {
					$data['salevalue_past7days'] = array_map(function($v) {
						return (object)$v;
					}, $api_data['salevalue_past7days']);
				}
				// Currency context for the view
				if (isset($api_data['currency_symbol'])) {
					$data['dashboard_currency_symbol'] = $api_data['currency_symbol'];
				}
				if (isset($api_data['currency_code'])) {
					$data['dashboard_currency_code'] = $api_data['currency_code'];
				}
			}
		}

        $data['access_token'] = $this->_get_vendor_access_token();
        $data['use_api'] = $use_api;
        
        $this->load->template("$this->TYPE/dashboard",$data);
    }
    public function ajax_set_default()
    {
        $type = $this->input->post('type');
        $id = $this->input->post('id');
        $response = array('status'=>0, 'message'=>'Invalid request');
        if($id && $type == 'cur'){
            $obj = get_currency($id);
            if($obj){
                $this->session->set_userdata('cur', $obj->currency_id);
                $response = array('status'=>1, 'message'=>'Success');
            }
        }else if($id && $type == 'ln'){
            $obj = get_language($id);
            if($obj){
                $this->session->set_userdata('ln', $obj->language_id);
                $response = array('status'=>1, 'message'=>'Success');
            }
        }
        if(!isset($response['data'])){
            $response['data'] = array();
        }
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
    }

	public function ajax_notify()
    {
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		$show_noti = $this->input->post();
		$this->Query_model->update_data('ec_notification',$show_noti,array('vendor_id' => $this->login_id));

	}


   public function vendor_amount_transfer($vendor_id = NULL)
{
    if (!$vendor_id) {
        return false;
    }

    $data = array();
    $current_YM = date('Y-m'); // Moved to the top to fix Undefined Variable notice
    $getmonth   = get_month();        
    $year       = date("Y"); 

    $checkdata = $this->Query_model->get_data('ec_earnings', array('vendor_id' => $vendor_id)); 
    $getdata   = $this->Vendor_model->get_data_by_month(array('vendor_id' => $vendor_id));

    if(empty($checkdata))
    {
        $insdata = array();
        foreach($getdata as $monthdata)
        {
            $admin_amount = (10 / 100) * $monthdata->total;
            $month        = $getmonth[$monthdata->month];
            $days         = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $datayear     = $monthdata->Year;

            // Skip current month and specific legacy date
            if($current_YM == $datayear.'-'.$month){ continue; }
            if('2021-12' == $datayear.'-'.$month){ continue; }

            $insdata = array( 
                'vendor_id'         => $monthdata->supplier_id,
                'admin_percentage'  => '10', 
                'from_date'         => $datayear.'-'.$month.'-01',
                'to_date'           => $datayear.'-'.$month.'-'.$days,
                'total'             => $monthdata->total,
                'admin_amount'      => $admin_amount,
                'vendor_amount'     => $monthdata->total - $admin_amount
            );

            $this->Query_model->insert_data('ec_earnings', $insdata);
        }  
    }
    else
    {
        if(count($checkdata))
        {
            $last_array = end($checkdata);
            $to_date = $last_array->to_date;
            if(!$to_date){ return false; }
            
            $yrmnth = date('Y-m', strtotime($to_date));
            $getdata = $this->Vendor_model->get_data_by_month(array(
                'vendor_id' => $vendor_id, 
                'yrmnth' => $yrmnth, 
                'next_month' => 1
            ));

            if(count($getdata))
            {
                foreach($getdata as $monthdata)
                {
                    // Skip if processing the current month name
                    if(date('F') == $monthdata->month){ continue; }
                    
                    $admin_amount = (10 / 100) * $monthdata->total;
                    $month        = $getmonth[$monthdata->month];
                    $days         = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                    $datayear     = $monthdata->Year;

                    // This now has access to $current_YM from line 7
                    if($current_YM == $datayear.'-'.$month){ continue; }

                    $insdata = array(       
                        'vendor_id'         => $monthdata->supplier_id,
                        'admin_percentage'  => '10', 
                        'from_date'         => $datayear.'-'.$month.'-01',
                        'to_date'           => $datayear.'-'.$month.'-'.$days,
                        'total'             => $monthdata->total,
                        'admin_amount'      => $admin_amount,
                        'vendor_amount'     => $monthdata->total - $admin_amount
                    );

                    $this->Query_model->insert_data('ec_earnings', $insdata);
                }
            }
        }  
    } 
}

    public function current_month_earning($args)
    {
       $monthyear = date('M Y');
       $getmonth  = get_month();
       $year      = date("Y");
       $nulldate  = '';
       $getdata   = $this->Vendor_model->get_data_by_month(array('vendor_id'=>$args['vendor_id'], 'current_month'=> $args['current_mnth'], 'yrmnth'=>$args['yrmnth']));

        // Resolve display currency from session
        $display_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
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

        if(count($getdata))
        {
            // get_data_by_month already returns base-currency totals (SUM / currency_rate)
            $base_total          = (float)$getdata[0]->total;
            $display_total       = $base_total * $display_rate;

            $admin_amount        = (10 / 100) * $display_total;
            $vendoramount        = $display_total - $admin_amount;

            $getdata[0]->earning_id      = '';
            $getdata[0]->admin_id        = '';
            $getdata[0]->vendor_id       = $getdata[0]->supplier_id;
            $getdata[0]->total           = number_format((float)$display_total, 2, '.', '');
            $getdata[0]->admin_percentage= '10';
            $getdata[0]->admin_amount    = number_format((float)$admin_amount, 2, '.', '');
            $getdata[0]->vendor_amount   = number_format((float)$vendoramount, 2, '.', '');
            $getdata[0]->from_date       = date("Y-m-01 H:i:s");
            $getdata[0]->to_date         = date("Y-m-d H:i:s");
            $getdata[0]->current_month   = '0';
            $getdata[0]->currency_symbol = $display_symbol;
        }
        return $getdata;
    }  






	
}















