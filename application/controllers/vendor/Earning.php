<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Earning extends MY_Controller {

	function __construct()
{
    parent::__construct();
    $this->load->model('Query_model');
    $this->load->model('Vendor_model');

    $this->TYPE = 'vendor';
    $user_session = $this->session->userdata($this->TYPE);
    $this->LOGIN_ID = isset($user_session['login_id']) ? (int)$user_session['login_id'] : (isset($user_session['vendor_id']) ? (int)$user_session['vendor_id'] : 0);
    $this->FNAME    = isset($user_session['fname']) ? $user_session['fname'] : 'Vendor';
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

public function index()
{
    $use_api = true; // Force api-driven mode
    $access_token = $this->_get_vendor_access_token();

    // Resolve display currency for the badge in the view
    $display_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
    $all_currencies = $this->db
        ->select('currency_id, symbol, iso_code, rate, basic')
        ->from('ec_currency')
        ->where('status', '1')
        ->get()->result();

    $display_symbol      = '$';
    $display_currency_code = 'USD';
    // Default to base currency
    foreach ($all_currencies as $c) {
        if ((int)$c->basic === 1) {
            $display_symbol        = html_entity_decode($c->symbol);
            $display_currency_code = $c->iso_code;
            break;
        }
    }
    // Override with vendor's session currency
    if ($display_cur_id > 0) {
        foreach ($all_currencies as $c) {
            if ((int)$c->currency_id === $display_cur_id) {
                $display_symbol        = html_entity_decode($c->symbol);
                $display_currency_code = $c->iso_code;
                break;
            }
        }
    }

    $data = array();
    $data['csrf'] = csrf_token();
    $data['vendor_id'] = $this->LOGIN_ID; // This passes the correct ID to the View's JS
    $data['page_count'] = page_count();
    $data['TYPE'] = $this->TYPE;    
    $data['use_api'] = $use_api;
    $data['access_token'] = $access_token;
    $data['display_symbol']        = $display_symbol;
    $data['display_currency_code'] = $display_currency_code;
    $this->load->template("$this->TYPE/earning/earning", $data);
}



	public function current_month_earning($args)
    {
       $vendor_id = $this->LOGIN_ID;
       $monthyear = date('M Y');
       $getmonth  = get_month();
       $year      = date("Y");
       $nulldate  = '';
       $getdata   = $this->Vendor_model->get_data_by_month(array('vendor_id'=>$vendor_id, 'current_month'=> $args['current_mnth'], 'yrmnth'=>$args['yrmnth']));

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
            // $getdata[0]->total is already in base currency (from get_data_by_month SUM / currency_rate)
            $base_total          = (float)$getdata[0]->total;
            // Convert base total to display currency
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
    
   public function get_vendor_amount()
{
    // 1. Identify the logged-in Vendor
    $vendor_id = $this->LOGIN_ID; 
    
    $length = (isset($_POST['length'])) ? $_POST['length'] : 10;
    $page   = (isset($_POST['start'])) ? $_POST['start'] : 0;

    // Resolve display currency & rate
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

    // 2. Get This Vendor's Commission Rate
    $vendor_obj = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
    $global_commission = isset($vendor_obj->commission) ? $vendor_obj->commission : 0;

    // 3. Query Builder
    $this->db->select('eoi.*, eo.order_number, eo.payment_method, eo.payment_status, eo.date_added as order_date_added, eo.last_updated as order_last_updated');
    $this->db->from('ec_order_items as eoi');
    $this->db->join('ec_orders as eo', 'eo.id = eoi.order_id', 'left');
    $this->db->where('eoi.login_id', $vendor_id);
    
    // Pagination & Sorting
    $this->db->limit($length, $page);
    $this->db->order_by('eoi.id', 'DESC');

    $query = $this->db->get();
    $list = $query->result();

    // 4. Count Total Records
    $this->db->from('ec_order_items');
    $this->db->where('login_id', $vendor_id);
    $total_records = $this->db->count_all_results();

    $data = array();

    foreach ($list as $obj) {
        $row = array();

        // --- CALCULATIONS ---
        $commission_pct = $global_commission;

        $raw_subtotal = isset($obj->subtotal) ? (float)$obj->subtotal : 0;

        // Convert to base amount first
        $item_rate = (float)($obj->currency_rate ?? 1.0) ?: 1.0;
        $base_subtotal = $raw_subtotal / $item_rate;

        // Convert to display currency
        $total_earning = $base_subtotal * $display_rate;

        $admin_share   = ($total_earning * $commission_pct) / 100;
        $vendor_share  = $total_earning - $admin_share;

        // --- MAPPING ---
        
        // 1. Order Item UID
        $row['order_uid']      = isset($obj->order_number) && $obj->order_number ? $obj->order_number : (isset($obj->order_id) ? $obj->order_id : (isset($obj->id) ? $obj->id : ''));

        // 2. Money Values
        $row['total_earning']  = $display_symbol . number_format($total_earning, 2);
        $row['commission_val'] = $display_symbol . number_format($admin_share, 2);
        $row['vendor_earning'] = $display_symbol . number_format($vendor_share, 2);
        $row['commission_pct'] = $commission_pct . '%';

        // 3. Payment Mode
        $row['payment_mode'] = isset($obj->payment_method) && $obj->payment_method ? $obj->payment_method : '-';

        // 4. Paid Status
        $is_paid = (isset($obj->payment_status) && strtolower((string)$obj->payment_status) === 'paid');

        // 5. Paid Date
        $db_date = '';
        if(isset($obj->order_last_updated) && $obj->order_last_updated && $obj->order_last_updated != '0000-00-00 00:00:00'){
            $db_date = $obj->order_last_updated;
        }elseif(isset($obj->order_date_added) && $obj->order_date_added && $obj->order_date_added != '0000-00-00 00:00:00'){
            $db_date = $obj->order_date_added;
        }
        $row['paid_date'] = ($is_paid && $db_date) ? date('d-M-Y', strtotime($db_date)) : '-';

        // 6. Status Badge
        if ($is_paid) {
            $row['status_html'] = '<span class="badge badge-success">Paid</span>';
        } else {
            $row['status_html'] = '<span class="badge badge-warning">Unpaid</span>';
        }

        $data[] = $row;
    }

    $output = array(
        "draw"            => isset($_POST['draw']) ? $_POST['draw'] : '',
        "recordsTotal"    => $total_records,
        "recordsFiltered" => $total_records,
        "data"            => $data
    );

    echo json_encode($output);
}






}
