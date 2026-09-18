<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Supplier extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');	
		$this->load->model('User_model'); 
		$this->load->library('csvimport');
        $this->TYPE = $this->session->userdata('type');		
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
	}
	
	public function index()
    {
        $page_lang = get_page_language_data('admin_page_lang');
		if(!logged_in()) redirect("$this->TYPE/auth/login");
	    $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->supplier => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		$data['page_count'] = page_count();			

		$this->load->template("$this->TYPE/supplier/index_post",$data);
	}
	
	public function index_ajax_post()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $type_array = array(1 => 'Fixed', 2 => 'Percentage');
		$type_status = array(1 => 'active', 0 => 'inactive');		
        $list = $this->User_model->get_supplier();		
		
		$last_order_date ='-';
        $data = array();		
        foreach ($list as $obj) {					
			$supplier_id = $obj->supplier_id;							
			$address = isset($obj->address) ? $obj->address :'-';	
			$mobile = isset($obj->mobile) ? $obj->mobile :'-';
			$email = isset($obj->email) ? $obj->email :'-';	
			$city = isset($obj->city) ? $obj->city :'-';
			$cname = isset($obj->cname) ? $obj->cname :'-';	
			$date_added = isset($obj->date_added) ? $obj->date_added :'-';			
			if (array_key_exists($obj->status, $type_status)) {
			  $st = $type_status[$obj->status];
			}						
            $row = array();
			$row['supplier_id']   		= $obj->supplier_id;
			$row['cname']   			= $cname;
			$row['city']   				= $city;
            $row['address']      		= $address;
			$row['email']    			= $email;
			$row['mobile']     			= $mobile;			
			$row['status']       		= $st;	
			$row['date_added']       	= $date_added;			
            $data[] 					= $row;
        }
		
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->User_model->supplier_count_all(),
                "recordsFiltered" => $this->User_model->supplier_count_filtered(),
                "data" => $data,
                );	
		echo json_encode($output);
    }
		
	public function add()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post();		
	}

	public function update($supplier_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post($supplier_id);
    }

	function post($supplier_id = NULL)
    {
		$data =array();	
		$db_data =array();	
		$data = $this->input->post();			
		$user = $this->session->userdata();				
		$current_id =$user['admin']['login_id'];
        $page_lang = get_page_language_data('admin_page_lang');
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('cname', 'Company name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|callback_unique_field['.$supplier_id.']');
		//update
        if(isset($supplier_id)){
			
			$user_obj = $this->Query_model->get_data_obj('ec_supplier',array('supplier_id' => $supplier_id)); 			
			if(!$user_obj){
				$this->session->set_flashdata('error', 'Invalid Error.');
				redirect("$this->TYPE/supplier");
			}else{
				
				if($_POST) {
					 
					$data =  array(	
						'cname'     	  			=> $data['cname'],
						'description'      	  		=> $data['description'],
						'address'    		  		=> $data['address'],
						'city'     					=> $data['city'],
						'state'						=> $data['state'],				
						'zip_code'          		=> $data['zip_code'],
						'company_registration_no' 	=> $data['company_registration_no'],
						'fname'      				=> $data['fname'],
						'lname'     		  		=> $data['lname'],
						'email'     		  		=> $data['email'],
						'mobile'     		  		=> $data['mobile'],	
						#'password'  			 	=> $data['password'],	
						'status'  			 		=> $data['status'],	
					);
					
				 }else{
					 
					$data =  array(	
						'cname'         	 		=> $user_obj->cname,
						'description'          	 	=> $user_obj->description,
						'address'  	 				=> $user_obj->address,
						'city'   					=> $user_obj->city,
						'state'   					=> $user_obj->state,
						'zip_code'         	 		=>$user_obj->zip_code,
						'company_registration_no'   => $user_obj->company_registration_no,
						'fname'   		 			=> $user_obj->fname,
						'lname'   			 		=> $user_obj->lname,
						'mobile'   			 		=> $user_obj->mobile,
						'email'   			 		=> $user_obj->email,
						'logo'   			 		=> $user_obj->logo,
						#'password'  			 	=> $user_obj->password,
						'status'  			 		=> $user_obj->status,
					);
				}
			}	
		}
		
		
		if ($this->form_validation->run() == FALSE){
            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($supplier_id) ? $page_lang->update : $page_lang->add;	
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->supplier => "/$this->TYPE/supplier/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($supplier_id) ? $page_lang->update : $page_lang->add;
            $data['supplier_id'] = isset($supplier_id) ? $supplier_id : NULL;	
            $this->load->template("$this->TYPE/supplier/post_form",$data);
			
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
				$img_url = 'thumb_'.$data_file['file_name'];					
				
				if(isset($supplier_id)){
	 
					$data =  array(
						'logo'     	 		 	=> $img_url,
						'cname'     	  		=> $data['cname'],
						'description'      	  	=> $data['description'],
						'address'    		  	=> $data['address'],
						'city'     				=> $data['city'],
						'state'					=> $data['state'],				
						'zip_code'          	=> $data['zip_code'],
						'company_registration_no' => $data['company_registration_no'],
						'fname'      			=> $data['fname'],
						'lname'     		  	=> $data['lname'],
						'email'     		  	=> $data['email'],
						'mobile'     		  	=> $data['mobile'],				
						'status'      	 		=> $data['status'],								
						#'password'     	  		=> md5($data['password']),
						#'supplier_uid'			=> uniq_uid(),
					);
				}else{
					
				$data =  array(
					'logo'     	 		 	=> $img_url,
					'cname'     	  		=> $data['cname'],
					'description'      	  	=> $data['description'],
					'address'    		  	=> $data['address'],
					'city'     				=> $data['city'],
					'state'					=> $data['state'],				
					'zip_code'          	=> $data['zip_code'],
					'company_registration_no' => $data['company_registration_no'],
					'fname'      			=> $data['fname'],
					'lname'     		  	=> $data['lname'],
					'email'     		  	=> $data['email'],
					'mobile'     		  	=> $data['mobile'],				
						#'password'     	  		=> md5($data['password']),
					'supplier_uid'			=> uniq_uid(),
				);		
				}	

			}else{ 
				if(isset($supplier_id)){
					
					$data =  array(
						'cname'     	  					=> $data['cname'],
						'description'      	  				=> $data['description'],
						'address'    		  				=> $data['address'],
						'city'     							=> $data['city'],
						'state'								=> $data['state'],				
						'zip_code'         				 	=> $data['zip_code'],
						'company_registration_no'          	=> $data['company_registration_no'],
						'fname'      						=> $data['fname'],
						'lname'     		 				=> $data['lname'],
						'email'     		 				 => $data['email'],
						'mobile'     					  	=> $data['mobile'],				
						'status'      	 					=> $data['status'],							
						#'password'     	  					=> md5($data['password']),
						#'supplier_uid'						=>uniq_uid(),
					);

			}else{ 
				 
				$data =  array(
					'cname'     	  					=> $data['cname'],
					'description'      	  				=> $data['description'],
					'address'    		  				=> $data['address'],
					'city'     							=> $data['city'],
					'state'								=> $data['state'],				
					'zip_code'         				 	=> $data['zip_code'],
					'company_registration_no'          	=> $data['company_registration_no'],
					'fname'      						=> $data['fname'],
					'lname'     		 				=> $data['lname'],
					'email'     		 				 => $data['email'],
					'mobile'     					  	=> $data['mobile'],				
						#'password'     	  					=> md5($data['password']),
					'supplier_uid'						=>uniq_uid(),
				);
				}
			}	
			
            if(isset($supplier_id)){	
				//echo "<pre>"; print_r($data); echo "<pre>";die;			
                $this->Query_model->update_data('ec_supplier',$data,array('supplier_id' => $supplier_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');			
				
            }else{			
				
                $insert_db = $this->Query_model->insert_data('ec_supplier',$data);
				if($insert_db){
					$this->session->set_flashdata('success', 'Inserted Successfully.');	
				}
                
            }
            redirect("$this->TYPE/supplier");
        }  
	}

    function unique_field($value, $supplier_id)
    {
        $email = $this->input->post('email');
        $args = array('email' => $email);
        if($supplier_id){
            $args['supplier_id!='] = $supplier_id;
        }
        $supplier_obj = $this->Query_model->get_data_obj('ec_supplier',$args);

        $status   = TRUE;
        $message  = '';
        if($supplier_obj) {
            $status   = FALSE;
            $message  = 'Email must be unique <br/>';
            $this->form_validation->set_message('unique_field', $message);
        }

        return $status;
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
		$crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->supplier => "/$this->TYPE/supplier/");
		$breadcrumbs = $this->breadcrumbs->show($crumbs);
		$data['breadcrumbs']    = $breadcrumbs;

		$data['csrf'] = csrf_token();
		$data['TYPE'] = $this->TYPE;			
		$data['basic'] = $this->User_model->get_profile_supplier($id); 	
		$update = $this->load->template("$this->TYPE/supplier/view_profile",$data); 	
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
        $update = $this->User_model->supplier_status_change($id,$status_val);
        echo json_encode(array('success' => $update));
		
    }
	
	public function checkall_status_change(){

	    $id = $this->input->post('id');
		$status = $this->input->post('status');		
		$ids = json_decode($id);
		//echo "<pre>"; print_r($_POST);exit;
		if($status == 'active'){
			$status_val = 0;
		}elseif($status == 'inactive'){
			$status_val = 1;
		}		
        $data = array();
        //$update = $this->User_model->checkall_supplier_status_change($id,$status);
		if($ids && $this->LOGIN_ID && $this->TYPE == 'admin'){
			$update = $this->Query_model->update_data('ec_supplier',array('status' => $status),array('supplier_id' => $ids));
		}
		
        echo json_encode(array('success' => $update));
		
    }
	
	public function del(){
		$data['csrf'] = csrf_token();
	    $id = $this->input->post('id');
		$data['csrf'] = csrf_token();
        $data = array();
        $delete = $this->User_model->delete_post($id);
        echo json_encode(array('success' => $delete));		
    }
	
	public function update_password($supplier_id = NULL){
			if(!logged_in()) redirect("$this->TYPE/auth/login");
			$data = array();
			$crumbs = array("Home" => "/$this->TYPE/dashboard", "supplier" => "");
			$breadcrumbs = $this->breadcrumbs->show($crumbs);
			$data['breadcrumbs']    = $breadcrumbs;
			$data['csrf'] = csrf_token();
			$data['TYPE'] = $this->TYPE;			
					
			$onep = $this->input->post('new1');
			$twop = $this->input->post('new2');
			if($onep == $twop){
				
				$data = array(
					'password'=> md5($onep)
				);
				$update = $this->User_model->update_Password($supplier_id,$data);
				$this->session->set_flashdata('feedback','Successfully Updated');
				echo json_encode(array('success' => $update));
				$this->load->template("$this->TYPE/supplier/post_form");
				redirect("admin/supplier");
				
			}else{
				$this->session->set_flashdata('feedback','Please enter valid password');			
				echo "Please enter valid password";
				
			}

          
    }
	
	public function delimg()
    {
        $data['csrf'] = csrf_token();
        $supplier_id = $this->input->post('id');	
		#$product_id = $this->input->post('product_id');			
        $data['csrf'] = csrf_token();
        $data = array();
        $delete = 0;
        if($supplier_id){
            $delete = $this->User_model->delete_image($supplier_id);
        }
		//echo '==='.$delete;
		//die;
        echo json_encode(array('success' => $delete));
    }
	
	public function export_csv(){ 
	  		
		$filename = 'supplier_report_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$filename"); 
		header("Content-Type: application/csv; ");	
		$list = $this->User_model->get_export_supplier();		

	    //file creation  
	    $file = fopen('php://output', 'w'); 
		$header = array("supplier_id","fname","lname","email",'description',"address",'city','state','zip_code','country','company_registration_no','mobile','date_added');   
	  
	    fputcsv($file, $header);
	      
	    foreach ($list as $key=>$line){ 
	   	   fputcsv($file,$line); 
	   }
	   fclose($file); 
	   exit; 
	}	
	
}
