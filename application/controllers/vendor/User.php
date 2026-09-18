<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');	
		$this->load->model('User_model'); 
        $this->TYPE = $this->session->userdata('type');	
		$this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;		
	}
	
	public function index($customer_id=null)
    {
		if(!logged_in()) redirect("$this->TYPE/auth/login");
        $page_lang = get_page_language_data('admin_page_lang');
	    $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->users => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();					
        $this->load->template("$this->TYPE/user/index_post",$data);
	}
	
	public function index_ajax_post()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
		$start  = (isset( $_POST['start']))?$_POST['start']: 1;
	
        $type_array = array(1 => 'Fixed', 2 => 'Percentage');
		$type_status = array(1 => 'active', 0 => 'inactive');
			
        $list = $this->User_model->get_datatable();		
		$last_order_date ='-';
        $data = array();
		$st = '';
        
		foreach ($list as $obj) {					
			$customer_id = $obj->customer_id;				
			$order_view = isset($obj->customer_id)? '<a href="/admin/order/index/'.$obj->customer_id.'">view order</a>' :'-';
			$last_order_date = isset($obj->date_created) ? $obj->date_created :'--';		
		
            $st = '';	
			if (array_key_exists($obj->status, $type_status)) {
			  $st = $type_status[$obj->status];
			}						
            $row = array();
			$row['customer_id']   		= $obj->customer_id;
			$row['fname']   			= $obj->fname;
            $row['email']      			= $obj->email;
			$row['user_description']    = $obj->user_description;
			$row['mobile']     			= $obj->mobile;
			$row['order_date']     	 	= $last_order_date;		
			$row['order_id']       		= $obj->order_id;
			$row['order_view']       	= $order_view;			
			$row['status']       		= $st;			
            $data[] = $row;
        }
		
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->User_model->count_all(),
                "recordsFiltered" => $this->User_model->count_filtered(),
                "data" => $data,
                );	
		//echo "<pre>"; print_r($output); echo "</pre>";		
        echo json_encode($output);
    }
		
	public function add()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post();
    }

	public function update($customer_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post($customer_id);
    }

	function post($customer_id = NULL)
    {
		$data =array();	
		$data = $this->input->post();
		$user = $this->session->userdata();				
		$current_id =$user['admin']['login_id'];
		$this->load->library('form_validation');
		$this->form_validation->set_rules('fname', 'First name', 'trim|required');        
		
        if(isset($customer_id)){			
            $user_obj = $this->Query_model->get_data_obj('ec_customer',array('customer_id' => $customer_id, 'status' => '1')); 			
            if(!$user_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/page");
            }else{		
				$data =  array(
					'fname'         	 => $user_obj->fname,
					'lname'          	 => $user_obj->lname,
					'email'  	 		 => $user_obj->email,
					'user_description'   => $user_obj->user_description,
					'mobile'         	 =>$user_obj->mobile,
					'featured_image'   	 => $user_obj->featured_image,
					'password'   		 => $user_obj->password,
					'role'   			 => $user_obj->role,
					'status'  			 => $user_obj->status
				);				
			}
        }
		
		
        if ($this->form_validation->run() == FALSE){
            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($customer_id) ? $page_lang->update : $page_lang->add;	
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->user => "/$this->TYPE/user/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($customer_id) ? 'update' : 'add';
            $data['customer_id'] = isset($customer_id) ? $customer_id : NULL;	
            $this->load->template("$this->TYPE/user/post_form",$data);
			
        }else{
			 
		if($_FILES['featured_image']['name']){
			
			
            $file_name = $_FILES['featured_image']['name'];
			$fileSize = $_FILES["featured_image"]["size"]/1024;
			$fileType = $_FILES["featured_image"]["type"];
			$new_file_name='';
            $new_file_name .= substr($data['fname'],0,3).time(); 

            $config = array(
                'file_name' => $new_file_name,
                'upload_path' => "./assets/images",
                'allowed_types' => "gif|jpg|png|jpeg|pdf",
                'overwrite' => False,
                'max_size' => "20240000", // Can be set to particular file size , here it is 2 MB(2048 Kb)
                'max_height' => "800",
                'max_width' => "800"
            );
    
            $this->load->library('Upload', $config);
            $this->upload->initialize($config);  
            if (!$this->upload->do_upload('featured_image')) {               
				$this->session->set_flashdata('formdata','Filetype is not allowed or uplaod file size 2MB');				
				 redirect("$this->TYPE/user",'refresh');				
			}
			
			$path = $this->upload->data();
			$img_url = $path['file_name'];
			
			$db_data =  array(
			'fname'          	=> $data['fname'],
			'lname'        		=> $data['lname'],
			'email'    		 	=> $data['email'],	
			'customer_uid'      => uniq_uid(),	
			'user_description'	=> $data['user_description'],					
			'featured_image' 	=> $img_url,
			'mobile'         	=> $data['mobile'],
			'status'        	=> $data['status'],
			'ip_address'     	=> $_SERVER['REMOTE_ADDR'],
			'role'     		 	=> $data['role'],
			'password'     		  =>md5($data['password']),
			'user_id'    		=> $current_id
			);		

		}else{ 
			
            $db_data =  array(
				'fname'     	  => $data['fname'],
				'lname'      	  => $data['lname'],
				'email'    		  => $data['email'],
				'customer_uid'     => uniq_uid(),
				'user_description'=> $data['user_description'],				
				'mobile'          => $data['mobile'],
				'status'          => $data['status'],
				'ip_address'      => $_SERVER['REMOTE_ADDR'],
				'role'     		  => $data['role'],
				'password'     	  => md5($data['password']),
				'user_id'         => $current_id
            );
			
		}				
            if(isset($customer_id)){						
                $this->Query_model->update_data('ec_customer',$db_data,array('customer_id' => $customer_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');			
				
            }else{				
                $this->Query_model->insert_data('ec_customer',$db_data);
                $this->session->set_flashdata('success', 'Inserted Successfully.');
            }
            redirect("$this->TYPE/user");
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
		$crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->user => "/$this->TYPE/user/");
		$breadcrumbs = $this->breadcrumbs->show($crumbs);
		$data['breadcrumbs']    = $breadcrumbs;

		$data['csrf'] = csrf_token();
		$data['TYPE'] = $this->TYPE;			
		//$data['basic'] = $this->User_model->get_profile($id); 
		$data['basic'] = $this->Query_model->get_data_obj('ec_customer',array('customer_id' => $id));
		//echo "<pre>"; print_r($data['basic']); echo "</pre>";die;	
        $data['order_history'] = $this->User_model->get_order($id);				
		$condition = array('customer_id' => $id );
        $shipping_address = $this->Query_model->get_data('ec_shipping_address', $condition);
		
		foreach ($shipping_address as $val) 
        {
            $row = array();
			$shipping_first_name =isset($val->fname) ? $val->fname:'';			
			$row['shipping_address_id'] = $val->shipping_address_id;
            $row['customer_id']         = $val->customer_id;
            $row['shipping_first_name'] = $shipping_first_name;
            $row['shipping_last_name']  = $val->lname;
            $row['shipping_mobile']     = $val->mobile;
            $row['shipping_address_1']  = $val->address_1;
            $row['shipping_city']       = $val->city;
            $row['shipping_street']     = $val->street;
            $row['shipping_postcode']   = $val->postcode;
            //$row['status']              = $status;
            $row['shipping_country']    = get_country($val->country)->name;
            $shipping_addr[] = $row;
        }
		$data['shipping_addr']=$shipping_addr;
		$condition = array('customer_id' => $id );
		$billing_address = $this->Query_model->get_data('ec_customer', $condition);	
		
        foreach ($billing_address as $val) 
        {
            $row = array();            
            $row['customer_id']         = $val->customer_id;
            $row['fname'] 				= $val->fname;
            $row['lname']  				= $val->lname;
            $row['email']     			= $val->email;
            $row['mobile'] 	 			= $val->mobile;
            $row['street']       		= $val->street;
            $row['city']     			= $val->city;
            $row['state']   			= $val->state;
            $row['status']              = $val->status;
			$row['zip_code']            = $val->zip_code;
			$row['address']           	= $val->address;         
            $billing_addr[] = $row;
        }
		$data['billing_addr']=$billing_addr;	
			
		$update = $this->load->template("$this->TYPE/user/view_profile",$data); 	
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
        $update = $this->User_model->status_change($id,$status_val);
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
			$update = $this->Query_model->update_data('ec_customer',array('status' => $status),array('customer_id' => $ids));
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
	

}
