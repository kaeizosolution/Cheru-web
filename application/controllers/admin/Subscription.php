<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscription extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    //$this->load->model('Query_model'); 
		$this->load->model('Subscription_model'); 
		//$this->load->model('User_model'); 
        $this->TYPE = $this->session->userdata('type');
		
	
        $this->check_module_permission('subscription');
    }
	
	public function index()
    {
		if(!logged_in()) redirect("$this->TYPE/auth/login");
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->subscription => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();		

        $this->load->template("$this->TYPE/subscription/index",$data);
	}

    public function index_ajax_post()
    {	
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

		$type_status = array(1 => 'active', 0 => 'inactive');		
		
        $subscription = $this->Subscription_model->get_subscription();

        $data = array();
        $sno = 0;
        foreach ($subscription as $obj) {
            $sno++;
            $row = array();
            $row['sno']       = $sno;
            $row['email']       = $obj->email;
            $row['ip_address']  = isset($obj->ip_address) ? $obj->ip_address : '';
            $row['created']     = $obj->date_added;
        
            $data[] = $row;
        }

        $output = array(
            "draw" => isset($_POST['draw'])?$_POST['draw']:'',
            "recordsTotal" => $this->Subscription_model->count_all('ec_subscribe'),
            "recordsFiltered" => $this->Subscription_model->count_filtered('ec_subscribe'),
            "data" => $data,
        );
        echo json_encode($output);
    }
	
	public function export_csv(){ 
	  
		$filename = 'subscription_report_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$filename"); 
		header("Content-Type: application/csv; ");
	    $contact = $this->Subscription_model->get_subscription_list();		
	  
	   // file creation  
	    $file = fopen('php://output', 'w'); 
		$header = array("ID","Email","Status","Date");   
	  
	    fputcsv($file, $header);
	      
	    foreach ($contact as $key=>$line){ 
	   	   fputcsv($file,$line); 
	    }
	   fclose($file); 
	   exit; 
	}	
}
