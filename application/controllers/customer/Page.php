<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Product_model');
        $this->load->helper('text');		
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

    public function index($arg=null) 
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
	$data['homepage'] = 1;
        $data['csrf'] = csrf_token();
	
		if ($this->uri->segment(2) === FALSE)
		{
			$slug = 0;
		}
		else
		{
			$slug = $this->uri->segment(2);
		}
		
		if(is_api()){
			$slug=$this->input->post('slug');
		}
		
		$args = array('post_slug' => $slug,'enabled' => '1');		
		$detail = $this->Query_model->get_data_obj('ec_page',$args);
	 
               if($detail){
			//$detail->post_content = strip_tags($detail->post_content);
			$detail->post_content = $detail->post_content;
               }	
	$crumbs = array("Home" => "/", "$slug" => '');
        $breadcrumbs = $this->breadcrumbs->show_new($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
	$data['detail'] = $detail;	
	if(is_api()){
			 
            unset($data['csrf']);
            unset($data['breadcrumbs']);
            unset($data['TYPE']);
            $response = array(
                'status' => '1',
                'message' => 'success',
             );
            if($detail){
                $response['data'] = [$detail];
            }else{
                $response['data'] = array();
            }
			$this->output
			->set_content_type('application/json')
			->set_output(json_encode($response));
		}else{
			$this->load->template("$this->TYPE/page_detail", $data);
		}
		
		
		
    }

    
}
