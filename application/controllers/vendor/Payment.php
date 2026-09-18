<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');	
		$this->load->model('User_model'); 
		$this->load->model('Payment_model'); 
        $this->TYPE = $this->session->userdata('type');		
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
	}
	
	public function index()
    {
        $page_lang = get_page_language_data('admin_page_lang');
		if(!logged_in()) redirect("$this->TYPE/auth/login");
	    $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->payment => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		$data['page_count'] = page_count();			
		$this->load->template("$this->TYPE/payment/index_post",$data);
		
	}
	
	public function index_ajax_post()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $type_array = array(1 => 'Fixed', 2 => 'Percentage');
		$type_status = array(1 => 'active', 0 => 'inactive');		
        $list = $this->Payment_model->get_payment();		
		
		$last_order_date ='-';
        $data = array();		
        foreach ($list as $obj) {					
			$payment_id = $obj->payment_id;	
			$title = isset($obj->title) ? $obj->title :'-';	
			$description = isset($obj->description) ? $obj->description :'-';	
			$date_added = isset($obj->date_added) ? $obj->date_added :'-';			
			if (array_key_exists($obj->status, $type_status)) {
			  $st = $type_status[$obj->status];
			}						
            $row = array();
			$row['payment_id']   		= $obj->payment_id;
			$row['title']   			= $title;
			$row['description']   		= $description;       		
			$row['status']       		= $st;	
			$row['date_added']       	= $date_added;			
            $data[] 					= $row;
        }
		
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Payment_model->payment_count_all(),
                "recordsFiltered" => $this->Payment_model->payment_count_filtered(),
                "data" => $data,
                );	
		echo json_encode($output);
    }
		
	public function add()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post();		
	}

	public function update($payment_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post($payment_id);
    }

	function post($payment_id = NULL)
    {
		$data =array();	
		$db_data =array();	
		$data = $this->input->post();	
		$user = $this->session->userdata();				
		$current_id =$user['admin']['login_id'];
        $page_lang = get_page_language_data('admin_page_lang');
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('title', 'title name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('description', 'description', 'required|trim|xss_clean');
		//update
        if(isset($payment_id)){
			
			$payment_obj = $this->Query_model->get_data_obj('ec_payment',array('payment_id' => $payment_id)); 			
			if(!$payment_obj){
				$this->session->set_flashdata('error', 'Invalid Error.');
				redirect("$this->TYPE/payment");
			}else{
				
				if($_POST) {
					 
					$data =  array(	
						'title'     	  			=> $data['title'],
						'description'      	  		=> $data['description'],
						'instructions'      	 	=> $data['instructions'],
						'status'  			 		=> $data['status'],	
					);
					
				 }else{
					 
					$data =  array(	
						'title'         	 		=> $payment_obj->title,
						'description'          	 	=> $payment_obj->description,
						'instructions'      	 	=> $payment_obj->instructions,
						'logo'          	 		=> $payment_obj->logo,						
						'status'  			 		=> $payment_obj->status,
					);
				}
			}	
		}
		
		
		if ($this->form_validation->run() == FALSE){
            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($payment_id) ? $page_lang->update : $page_lang->add;	
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->payment => "/$this->TYPE/payment/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($payment_id) ? $page_lang->update : $page_lang->add;
            $data['payment_id'] = isset($payment_id) ? $payment_id : NULL;	
            $this->load->template("$this->TYPE/payment/post_form",$data);
			
        }else{		
		
			if($_FILES['logo']['name']){		
			
				$config = array(				
					'upload_path' => "./assets/images",  //"./assets/images"	
					'allowed_types' => "gif|jpg|png|jpeg|pdf",
					'max_size' => "20240000",
				);				
						
		
				$this->load->library('Upload', $config);
				$this->upload->initialize($config);
				
				if ($this->upload->do_upload('logo')) {               
					
					$data_file = $this->upload->data();	
					//code for resize image	
					$config2 = array(
						'image_library' =>	'gd2', //get original image
						'source_image' 	=> "./assets/images/".$data_file['file_name'],
						'maintain_ratio'=> TRUE,
						'width' =>800,
						'height' =>800,
						'new_image' 	=> "./assets/images/".'thumb_'.$data_file['file_name'],						
					);	
					$this->load->library('image_lib', $config2); 
					$this->upload->initialize($config2);  
					$this->image_lib->resize();
					$this->image_lib->clear();
					
				}			
				$logo = 'thumb_'.$data_file['file_name'];					
				
				if(isset($payment_id)){
	 
					$data =  array(
						'logo'     	 		 	=> $logo,
						'title'     	  		=> $data['title'],
						'description'      	  	=> $data['description'],
						'instructions'      	=> $data['instructions'],
						'status'      	 		=> $data['status'],		
					);
				}else{
					
				$data =  array(
					'logo'     	 		 	=> $logo,
					'title'     	  		=> $data['title'],
					'description'      	  	=> $data['description'],
					'instructions'      	=> $data['instructions'],
					'status'      	  		=> $data['status'],									
					'payment_uid'			=> uniq_uid(),
				);		
				}	

			}else{ 
				if(isset($payment_id)){
					
					$data =  array(
						'title'     	  					=> $data['title'],
						'description'      	  				=> $data['description'],
						'instructions'      	 			=> $data['instructions'],
						'status'      	 					=> $data['status'],						
					);

			}else{ 
				 
				$data =  array(
					'title'     	  					=> $data['title'],
					'description'      	  				=> $data['description'],
					'instructions'      	 			=> $data['instructions'],
					'status'      	  					=> $data['status'],					
					'payment_uid'						=>uniq_uid(),
				);
				}
			}	
			
            if(isset($payment_id)){		
			#echo "<pre>"; print_r($data); echo "</pre>";die;
							
                $this->Query_model->update_data('ec_payment',$data,array('payment_id' => $payment_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');			
				
            }else{			
				
			
				
                $insert_db = $this->Query_model->insert_data('ec_payment',$data);
				if($insert_db){
					$this->session->set_flashdata('success', 'Inserted Successfully.');	
				}
                
            }
            redirect("$this->TYPE/payment");
        }  
	}
 	public function view()
    {
		$data = array();
		if ($this->uri->segment(4) === FALSE){
			$id = 0;
		}else{
			$id = $this->uri->segment(4);
		}		
		if(!logged_in()) redirect("$this->TYPE/auth/login");		
		$shipping_addr = array();$billing_addr = array();
        $page_lang = get_page_language_data('admin_page_lang');
		$crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->payment => "/$this->TYPE/payment/");
		$breadcrumbs = $this->breadcrumbs->show($crumbs);
		$data['breadcrumbs']    = $breadcrumbs;

		$data['csrf'] = csrf_token();
		$data['TYPE'] = $this->TYPE;			
		$data['basic'] = $this->Payment_model->get_profile_payment($id); 	
		$update = $this->load->template("$this->TYPE/payment/view_profile",$data); 	
    }
	
	public function status_change(){

		$data['csrf'] = csrf_token();
	    $id = $this->input->post('id');
		$status = $this->input->post('status');
		if($status == 'active'){
			$status_val = 0;
		}elseif($status == 'inactive'){
			$status_val = 1;
		}		
		$data['csrf'] = csrf_token();
        $data = array();
        $update = $this->Payment_model->payment_status_change($id,$status_val);
        echo json_encode(array('success' => $update));
		
    }
	
	public function checkall_status_change(){

	    $id = $this->input->post('id');
		$status = $this->input->post('status');		
		$ids = json_decode($id);	
		if($status == 'active'){
			$status_val = 0;
		}elseif($status == 'inactive'){
			$status_val = 1;
		}		
        $data = array();       
		if($ids && $this->LOGIN_ID && $this->TYPE == 'admin'){
			$update = $this->Query_model->update_data('ec_payment',array('status' => $status),array('payment_id' => $ids));
		}
		
        echo json_encode(array('success' => $update));
		
    }
	
	public function del(){
		$data['csrf'] = csrf_token();
	    $id = $this->input->post('id');
		$data['csrf'] = csrf_token();
        $data = array();
        $delete = $this->Payment_model->delete_post($id);
        echo json_encode(array('success' => $delete));		
    }
	
	public function delimg()
    {
        $data['csrf'] = csrf_token();
        $payment_id = $this->input->post('id');	
		#$product_id = $this->input->post('product_id');			
        $data['csrf'] = csrf_token();
        $data = array();
        $delete = 0;
        if($payment_id){
            $delete = $this->Payment_model->delete_image($payment_id);
        }
		//echo '==='.$delete;
		//die;
        echo json_encode(array('success' => $delete));
    }
	
}
