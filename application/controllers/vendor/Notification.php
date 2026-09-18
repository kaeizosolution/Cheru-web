<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notification extends MY_Controller {

	function __construct()
    {
	parent::__construct();
		$this->load->model('Notify_model');		
        $this->TYPE = $this->session->userdata('type');
		$user_session = $this->session->userdata($this->TYPE);
		$this->LOGIN_ID = isset($user_session['login_id']) ? (int)$user_session['login_id'] : (isset($user_session['vendor_id']) ? (int)$user_session['vendor_id'] : 0);
		$this->FNAME    = isset($user_session['fname']) ? $user_session['fname'] : 'Vendor';
	}
	
	public function index()
    {
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", 'Notification' => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
		$data['vendor_id'] =  $this->LOGIN_ID;
        $this->load->template("$this->TYPE/notification/index_notification",$data);
	}

    	public function ajax_notify()
    {
		$data = array();
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
        $page_lang = get_page_language_data('admin_page_lang');
		$list = $this->Notify_model->get_datatable($this->LOGIN_ID);
		//echo "<pre>"; print_r($list);echo "</pre>";die;
        foreach ($list as $obj) {
        $row = array();
		$pro = get_product($obj->product_id);
		$cust = get_customer($obj->user_id);
		$image_info = get_product_image($obj->product_id);
		$slug = get_slug($pro->post_title);
		//echo "<pre>"; print_r($slug); echo "</pre>";die;
		$image = isset($image_info->file_name) ? '<img width="80" height="80" alt="product image" src="'.base_url().'assets/uploads/files/'.$image_info->file_name.'" />' : '';
		
		$product_title = '<h6 class="mb-1"><span class="text-orange">Order ID #'.$obj->order_id.'</span></h6> <h6 class="mb-1"><a href="'.detail_page_url($pro).'">'.$pro->post_title.'</a></h4><small class="d-block">'.date("d  M  Y - g:ia", strtotime($obj->date_added)).'</small>';

		//$row['image']       = $image;		
		$row['order_id']	  = '#'.$obj->order_id;
		$row['name']          =  $pro->post_title;
		$row['notify_date']   = date("d  M  Y - g:ia", strtotime($obj->date_added));
		$data[] = $row;
		
		}
		
		$output = array(
				"draw" => isset($_POST['draw'])?$_POST['draw']:'',
				"recordsTotal" => $this->Notify_model->count_all($this->LOGIN_ID),
				"recordsFiltered" => $this->Notify_model->count_filtered($this->LOGIN_ID),
				"data" => $data,
				   );

		echo json_encode($output);
    }

	public function add()
    {
        $this->post();
    }

	public function update($coupon_id)
    {
        $this->post($coupon_id);
    }

	function post($coupon_id = NULL)
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean|callback_unique_field['.$coupon_id.']');
        $this->form_validation->set_rules('discount', 'Discount', 'trim|required');
        $this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
        $this->form_validation->set_rules('end_date', 'End Date', 'trim|required');
        $this->form_validation->set_rules('type', 'Discount Type', 'trim|required');

        #$product_obj = $this->Query_model->get_data('ec_product', array('enabled' => '1'));
        
        $brand_obj = $this->Query_model->get_data('ec_brand', array('status' => '1'));
        $category_obj = $this->Query_model->get_data('ec_categories_prod', array('status' => '1'));
        $db_product_arr = [];
        if(isset($coupon_id)){
            $coupon_obj = $this->Query_model->get_data_obj('ec_coupon',array('coupon_id' => $coupon_id, 'login_id' => $this->LOGIN_ID));
            if(!$coupon_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/coupon");
            }
            $db_product_id = json_decode(json_decode($coupon_obj->product_id));
            $db_product_arr = $this->Query_model->get_data('ec_product', array('product_id'=>$db_product_id));
        }

        if($_POST) {
            $ctype = $this->input->post('ctype');
            $category_id  = $this->input->post('category_id');
            $product_id = $this->input->post('product_id');
            $brand_id = $this->input->post('brand_id');
            if($ctype == 1){
                $product_id = $brand_id = NULL;
            }else if($ctype == 2){
                $product_id = $category_id = NULL;
            }else{
                $category_id = $brand_id = NULL;
                $product_id =json_encode($product_id);

            }
            $data =  array(
                    'name'          => $this->input->post('name'),
                    'description'   => $this->input->post('description'),
                    'discount'      => $this->input->post('discount'),
                    'start_date'    => $this->input->post('start_date'),
                    'end_date'      => $this->input->post('end_date'),
                    'type'          => $this->input->post('type'),
                    'ctype'         => $this->input->post('ctype'),
                    'status'        => $this->input->post('status'),
                    'brand_id'      => $brand_id,
                    'product_id'    => $product_id,
                    'category_id'   => $category_id,
                    'minimum_order_value'   => $this->input->post('minimum_order_value'),
                    );
        }else{
            if(isset($coupon_id)){
                $data =  array(
                        'name'          => $coupon_obj->name,
                        'description'   => $coupon_obj->description,
                        'discount'      => $coupon_obj->discount,
                        'start_date'    => $coupon_obj->start_date,
                        'end_date'      => $coupon_obj->end_date,
                        'type'          => $coupon_obj->type,
                        'ctype'         => $coupon_obj->ctype,
                        'status'        => $coupon_obj->status,
                        'brand_id'      => $coupon_obj->brand_id,
                        'product_id'    => $db_product_arr,
                        'category_id'   => $coupon_obj->category_id,
                        'minimum_order_value'   => $coupon_obj->minimum_order_value,
                        );
            }
        }

        if ($this->form_validation->run() == FALSE){
            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($coupon_id) ? $page_lang->update : $page_lang->add;
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->coupon => "/$this->TYPE/coupon/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($coupon_id) ? $page_lang->update : $page_lang->add;
            $data['coupon_id'] = isset($coupon_id) ? $coupon_id : NULL;
            $data['product_obj'] = []; //$product_obj;
            $data['brand_obj'] = $brand_obj;
            $data['category_obj'] = $category_obj;
            
            $this->load->template("$this->TYPE/coupon/coupon",$data);
        }else{
            $data['status'] = ($data['status']) ? $data['status'] : '0';
            $prodid = $this->input->post('product_id');
            if($data['product_id']){
                $data['product_id'] = json_encode($data['product_id']);
            }
            $db_data =  array(
                    'name'          => $data['name'],
                    'description'   => $data['description'],
                    'discount'      => $data['discount'],
                    'start_date'    => $data['start_date'],
                    'end_date'      => $data['end_date'],
                    'type'          => $data['type'],
                    'ctype'         => $data['ctype'],
                    'status'        => $data['status'],
                    'login_id'      => $this->LOGIN_ID,
                    'brand_id'      => $data['brand_id'],
                    'product_id'    => $data['product_id'],
                    'category_id'   => $data['category_id'],
                    'minimum_order_value'   => $data['minimum_order_value'],
                    );
            if(isset($coupon_id)){
             #echo "<pre>"; print_r($db_data); echo "</pre>";die; 
               $this->Query_model->update_data('ec_coupon',$db_data,array('coupon_id' => $coupon_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');
            }else{
                $this->Query_model->insert_data('ec_coupon',$db_data);
                $this->session->set_flashdata('success', 'Inserted Successfully.');
            }
            redirect("$this->TYPE/coupon");
        }
    }
	
	function unique_field($value, $coupon_id)
    {
        $name = $this->input->post('name');
        $args = array('name' => $name);
        if($coupon_id){
            $args['coupon_id!='] = $coupon_id;
        }	
		
        $coupon_obj = $this->Query_model->get_data_obj('ec_coupon',$args);					
        $status   = TRUE;
        $message  = '';
        if($coupon_obj) {
            $status   = FALSE;
            $message  = 'Name must be unique <br/>';
            $this->form_validation->set_message('unique_field', $message);
        }
        return $status;
    }
	
	function search_ajax_product()
    {
	$sarch = $this->input->post('searchTerm');		
        $product_obj = $this->Coupon_model->sarch_coupan($sarch);		
        $data = array();
        foreach ($product_obj as $obj) {
            $row = array();
            $row['id']   = $obj->product_id;
            $row['text'] 	 = $obj->post_title;			
            $data[] = $row;
        }
        echo json_encode($data);
    }
	function get_product()
    {		
        $product_obj = $this->Coupon_model->sarch_coupan();		
	$data = array();
        foreach ($product_obj as $obj) {
            $row = array();			
            //$row['product_id']   = $obj->product_id;
            $row['post_title'] 	 = $obj->post_title;			
            $data[] = $row;
        }
	return $data;
        //echo json_encode($data);
    }	
	
}
