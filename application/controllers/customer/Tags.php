<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Tags extends MY_Controller {

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
        
		
		//echo "KKK!".$term_slug; die;
		$data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
		if ($this->uri->segment(2) === FALSE){
			$term_slug = 0;
		}else{
			$term_slug = $this->uri->segment(2);
		}
        $crumbs = array("Home" => "/$this->TYPE/product/", "product" => "");
        $breadcrumbs = $this->breadcrumbs->show_new($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
		
		$args = array('enabled' => '1');
		$data['count_all'] = $this->Query_model->count_all('ec_product',$args);
		
		$args = array('enabled' => '1');
		$tag_id = $this->Product_model->get_term_id_by_slug('ec_tags_prod', $term_slug);
		$data['products'] = $this->Product_model->get_data_by_tagid($tag_id->id);		
		//echo "<pre>"; print_r($data['products']); echo "</pre>";die;		
        $this->load->template("$this->TYPE/product_list", $data);
    }
	    
}





















