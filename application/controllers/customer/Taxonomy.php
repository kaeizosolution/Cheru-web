<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Taxonomy extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
		$this->load->model('Product_model');
        $this->load->helper('text');		
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

    public function index($search = NULL) 
    { 	
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        if ($this->uri->segment(2) === FALSE){
            $term_slug = 0;
        }else{
            $term_slug = $this->uri->segment(2);
        }
        if(is_api()){
            $category_id = $this->input->post('category_id');

        }
        $crumbs = array("Home" => "/", "$term_slug" =>'');
        $breadcrumbs = $this->breadcrumbs->show_new($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;		
        $args = array('enabled' => '1');
        $data['count_all'] = $this->Query_model->count_all('ec_product',$args);		
        $args = array('enabled' => '1');	

        if(is_api()){	
            $data['products'] = $this->Product_model->get_data_by_termid($category_id);	
            foreach($data['products'] as $k =>$val){				
                $args_image = array('product_id' => $val->product_id );
                $thumb_img = $this->Query_model->get_data('ec_gallery',$args_image);			
                if(isset($thumb_img) && !empty($thumb_img)){
                    $val->image_path = base_url().'assets/uploads/files/'.$thumb_img[0]->file_name;
                } 				
            }			
        }else{
            $term_id = $this->Product_model->get_term_id_by_slug('ec_categories_prod',$term_slug);
            #print '<pre>';
            #print $term_slug;
            #print_r($term_id);#exit;
            $data['products'] = $this->Product_model->get_data_by_termid($term_id->id);	

            foreach($data['products'] as $k =>$val){
                $args_image = array('product_id' => $val->product_id );
                $thumb_img = $this->Query_model->get_data('ec_gallery',$args_image);			
                if(isset($thumb_img) && !empty($thumb_img)){
                    $val->image_path = base_url().'assets/uploads/files/'.$thumb_img[0]->file_name;
                }		
            }			
        }	

        if(is_api()){
            unset($data['csrf']);
            unset($data['breadcrumbs']);
            unset($data['TYPE']);			
            $response = array(
                    'status' => '1',
                    'message' => 'success',
                    'data' => $data['products'],
                    );             
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }else{			
            $this->load->template("$this->TYPE/product_list", $data);
        }	
    }	    
}
