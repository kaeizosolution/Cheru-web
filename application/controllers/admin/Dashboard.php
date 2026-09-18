<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Dashboard extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Dashboard_model');
        $this->TYPE = $this->session->userdata('type');
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
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->dashboard => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();

        $filter_id = ($this->uri->segment(4) === FALSE) ? 0 : $this->uri->segment(4);
        $range = ($this->uri->segment(5) === FALSE) ? null : $this->uri->segment(5);
        $range_end = ($this->uri->segment(6) === FALSE) ? null : $this->uri->segment(6);
        $startDate = null;
        $endDate = null;
        if ($filter_id === 'customdate') {
            $startDate = $range;
            $endDate = $range_end ?: $range;
        } elseif ($filter_id === 'today') {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        } elseif ($filter_id === 'week') {
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
        } elseif ($filter_id === 'month') {
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d');
        } elseif ($filter_id === 'annual') {
            $startDate = date('Y-m-d', strtotime('-364 days'));
            $endDate = date('Y-m-d');
        }
            
        $args = array('enabled' => '1');		
        $data['count_all_post'] = $this->Query_model->count_all('ec_posts',$args);

        $args1 = array('custype' => 'subscribe');
        $data['count_all_subscribe'] = $this->Query_model->count_all('ec_enquiry',$args1);

        $args2 = array('status' => '1');
        $data['count_all_visiter'] = $this->Query_model->count_all('ec_customer',$args2);

        // Summary cards (order/income/products/users) based on selected range
        $data['summary'] = $this->Dashboard_model->dashboard_summary($startDate, $endDate);

       // $data['subtotal'] = $this->Dashboard_model->total_income($filter_id);
       // $data['users_report'] = $this->Dashboard_model->users_report($filter_id);

       // $data['top_selling_products'] = $this->Dashboard_model->top_selling_products();

        // Dynamic graph data based on selected range
        $data['sale_graph'] = $sale_graph = $this->Dashboard_model->sale_graph_range($startDate, $endDate);	
        $qty = array();
        $labels = array();
        $amounts = array();
        foreach($sale_graph as $k=>$val){
            $qty[] = $val->product_qty;			
            $labels[] = $val->label;
            $amounts[] = $val->sale_amount;
        }
        $data['graph_labels'] = json_encode($labels);
        $data['product_qty'] = json_encode($qty);
        $data['graph_sale_amount'] = json_encode($amounts);

        $total_product_qty = 0;
        foreach($sale_graph as $k=>$val){
            $total_product_qty += $val->product_qty;			
        }
        $data['total_product_qty'] = $total_product_qty;

        $sale_amount = 0;
        foreach($sale_graph as $k=>$val){
            $sale_amount += $val->sale_amount;			
        }
        $data['sale_amount'] = $sale_amount;

        // Top cards: delivered/pending orders (global counts)
        $data['pending_orders_count'] = $this->Dashboard_model->count_orders_by_status(1, null, null);
        // Delivered is reliably tracked on ec_order_items in this codebase (10 => Delivered)
        $data['delivered_orders_count'] = $this->Dashboard_model->count_delivered_orders(null, null);

        $data['approved_order'] 	    = $this->Dashboard_model->get_approv_order();
        $data['cancelled_order'] 	    = $this->Dashboard_model->get_cancelled_order();
        $data['shipped_order'] 		    = $this->Dashboard_model->get_shipped_order();
        $data['delivered_order'] 	    = $this->Dashboard_model->get_delivered_order();
        $data['total_order_qty']        = $this->Dashboard_model->get_dash_orderCount();
        $data['delivered_order_qty']    = $this->Dashboard_model->get_dash_delivered_order_qty();
        $data['pending_order_qty']    	= $this->Dashboard_model->get_dash_pending_order_qty();
        $data['vendor_total']    	    = $this->Dashboard_model->get_vendor_total();

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




}















