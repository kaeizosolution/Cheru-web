<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Thanks extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
		$this->load->model('Product_model');
        $this->load->model('Order_model');
        $this->load->helper('text');		
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

	## Thank you page		
	public function index($order_uid=null) 
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
       
        $orderlist =   $this->Order_model->get_data_by_order_uid($order_uid);
		if($this->LOGIN_ID){	
		
		}
		  
        foreach ($orderlist as $orderdata) {

            $row = array();

            $shipping_address = $this->Query_model->get_data_obj('ec_shipping_address', array('shipping_address_id' => $orderdata->shipping_address_id));

            $row['name']           = $shipping_address->fullname;
            $row['mobile']         = $shipping_address->mobile;
            $row['order_uid']      = $orderdata->order_uid;
            $row['address']        = $shipping_address->address_1.', '.$shipping_address->city.', '.$shipping_address->postcode;
            $row['order_total']    = $orderdata->order_total;
            $row['payment_mode']   = $orderdata->payment_mode;
            $row['discount_amount'] = (float)($orderdata->discount_amount ?? 0);
            $row['coupon_code']     = (string)($orderdata->coupon_code ?? '');
            $row['total_amount']    = (float)($orderdata->total_amount ?? $orderdata->order_total);

            $data = $row;
        }

        $symbol = '$';
        $cur_rate = 1.0;
        $cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
        if ($cc_id > 0) {
            $cur = $this->db
                ->select('currency_id, symbol, rate')
                ->from('ec_currency')
                ->where('currency_id', $cc_id)
                ->where('status', '1')
                ->get()->row();
            if ($cur) {
                $symbol = (string)$cur->symbol;
                if ((float)$cur->rate > 0) {
                    $cur_rate = (float)$cur->rate;
                }
            }
        }
        $data['symbol'] = $symbol;
        $data['cur_rate'] = $cur_rate;
        $data['TYPE'] = $this->TYPE;

        $data['homepage'] = 1;
		$data['csrf']           = csrf_token(); 
        
        $this->load->template("$this->TYPE/thankyou", $data);
    }
	
}





















