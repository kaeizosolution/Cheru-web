<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contact extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    //$this->load->model('Query_model');
		$this->load->model('Form_model'); 
		//$this->load->model('User_model'); 
        $this->TYPE = $this->session->userdata('type');
		$this->load->library('csvimport');
		
	
        $this->check_module_permission('enquiries');
    }
	
	public function index()
    {
		if(!logged_in()) redirect("$this->TYPE/auth/login");
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", 'Enquire Listing' => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();		

        $this->load->template("$this->TYPE/contact/contact_index",$data);
	}

    public function index_ajax_post()
    {
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
		
        $contact = $this->Form_model->get_contact();	
		//echo "<pre>"; print_r($contact); echo "</pre>";die;	
        $data = array();  
		$sno = 0;		
        foreach ($contact as $obj) {  
		
			$sno++;
            $row = array();
            $row['sno']         = $sno;
            $lname 				= (($obj->lname) ? " " . $obj->lname : '');
            $row['fname']    = $obj->fname . $lname;
            $row['email']       = $obj->email;
            $row['message']     = $obj->message;
            $row['ip_address']  = $obj->ip_address;
            $row['created']     = date("d-m-Y", strtotime($obj->created)); //date('Y-m-d',$obj->created);
            $data[] = $row;
        }

        $output = array(
            "draw" => isset($_POST['draw'])?$_POST['draw']:'',
            "recordsTotal" => $this->Form_model->count_all('ec_enquiry'),
            "recordsFiltered" => $this->Form_model->count_filtered('ec_enquiry'),
            "data" => $data,
        );
		//echo "<pre>"; print_r($output); echo "</pre>";
		
        echo json_encode($output);
    }
		
	public function export_csv(){ 
	  
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
		
		//$login_id   = $this->session->userdata('user_login_id');
		//$parent_id  = $this->session->userdata('parent_id');
		//$company_id = $parent_id ? $parent_id : $login_id;	

		///$campaignData = $this->campaign_model->csv_format($company_id);
		// file name
 
		$filename = 'contact_report_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$filename"); 
		header("Content-Type: application/csv; ");

	   // get data 
	  // $campaignData = $this->campaign_model->csv_format($company_id);
	   $contact = $this->Form_model->get_contact_list();
		//echo "<pre>"; print_r($contact); echo "</pre>";die; 
		
	   // file creation  
	   $file = fopen('php://output', 'w');
	 
	   //$header = array("e_name","c_name","r_id","camp_name", "camp_panel","panel_msg_id", "mailer_send", "total_mailer_send","rate", "c_type", "last_updated"); 
	  
		$header = array("enquiry_id","name","email","message","ip_address","created"); 
	  	  
	  fputcsv($file, $header);
	      
	   foreach ($contact as $key=>$line){ 
	   	   
		 fputcsv($file,$line); 
	   }
	   fclose($file); 

	   exit;
 
	  }	
}
