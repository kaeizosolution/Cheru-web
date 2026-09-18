<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Admin_model');
		$this->load->model('User_model');
		$this->load->model('Query_model');
        $this->TYPE = $this->session->userdata('type');
		//$this->TYPE = $this->session->userdata('type');		
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0; 
		
	}
	
	public function index()
    {
        $page_lang = get_page_language_data('admin_page_lang');
		if(!logged_in()) redirect("$this->TYPE/auth/login");
	    $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", "$page_lang->admin" => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();		

        $this->load->template("$this->TYPE/admin/index",$data);
	}

    public function index_ajax_post()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;			
		$admin = $this->Admin_model->get_admin();		
		$type_status = array(1 => 'active', 0 => 'inactive');		
        $data = array();
        $sno = 0;
        foreach ($admin as $obj) {
			$mobile = isset($obj->mobile) ? $obj->mobile :'-';
			$email = isset($obj->email) ? $obj->email :'-';
			$fname = isset($obj->fname) ? $obj->fname :'-';
			
			if (array_key_exists($obj->status, $type_status)) {
			  $st = $type_status[$obj->status];
			}	
            $sno++;
            $row = array();           
			$row['admin_id']        = $obj->admin_id;
            $row['fname']        = $fname;
            $row['email']           = $email;
            $row['mobile']          = $mobile;
            $row['superadmin']      = 'Administrator';
            $row['status']          = $st;
            $row['date_added']      = $obj->date_added;
            $row['last_updated']    = $obj->last_updated;
        
            $data[] = $row;
        }
		
        $output = array(
            "draw" => isset($_POST['draw'])?$_POST['draw']:'',
            "recordsTotal" => $this->Admin_model->admin_count_all('ec_admin'),
            "recordsFiltered" => $this->Admin_model->admin_count_filtered('ec_admin'),
            "data" => $data,
        );				
        echo json_encode($output);
    }
	
	public function add()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post();		
	}

	public function update($admin_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post($admin_id);
    }

	function post($admin_id = NULL)
    {
		$data =array();	
		$db_data =array();	
		$data = $this->input->post();
        $page_lang = get_page_language_data('admin_page_lang');
		$user = $this->session->userdata();				
		$current_id =$user['admin']['login_id'];
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('fname', 'First name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|callback_unique_field['.$admin_id.']');
		//update
        if(isset($admin_id)){
			$user_obj = $this->Query_model->get_data_obj('ec_admin',array('admin_id' => $admin_id)); 			
			
			if(!$user_obj){
				$this->session->set_flashdata('error', 'Invalid Error.');
				redirect("$this->TYPE/admin");
			}else{
				
				if($_POST) {
					
					$data =  array(								
						'fname'      				=> $data['fname'],
						'lname'     		  		=> $data['lname'],
						'email'     		  		=> $data['email'],
						'mobile'     		  		=> $data['mobile'],							
						'status'  			 		=> $data['status'],	
					);
					
				 }else{
					 
					$data =  array(						
						'fname'   		 			=> $user_obj->fname,
						'lname'   			 		=> $user_obj->lname,
						'mobile'   			 		=> $user_obj->mobile,
						'email'   			 		=> $user_obj->email,
						'logo'   			 		=> $user_obj->logo,
						'password'  			 	=> $user_obj->password,
						'status'  			 		=> $user_obj->status,
					);
				}
			}	
		}		
		
		if ($this->form_validation->run() == FALSE){

            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($admin_id) ? $page_lang->update : $page_lang->add;	
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->admin => "/$this->TYPE/admin/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($admin_id) ? $page_lang->update : $page_lang->add;
            $data['admin_id'] = isset($admin_id) ? $admin_id : NULL;	 
            $this->load->template("$this->TYPE/admin/post_form",$data);
			
        }else{		
		
			if($_FILES['logo']['name']){		
			
				$config = array(				
					'upload_path' => "./assets/images",  //"./assets/images"	
					'allowed_types' => "gif|jpg|png|jpeg|pdf",
					'max_size' => "20240000",
				);				
												
				$this->load->library('Upload', $config);
				$this->upload->initialize($config);  
				
				if ( $this->upload->do_upload('logo') ) {   
					
					$data = $this->upload->data();	
					//code for resize image	
					$config2 = array(
						'image_library' =>	'gd2', //get original image
						'source_image' 	=> "./assets/images/".$data['file_name'],
						'maintain_ratio'=> TRUE,
						'width' =>800,
						'height' =>800,
						'new_image' 	=> "./assets/images/".'thumb_'.$data['file_name'],						
					);	
					$this->load->library('image_lib', $config2); 
					$this->upload->initialize($config2);  
					$this->image_lib->resize();
					$this->image_lib->clear();
					#$this->session->set_flashdata('formdata','Filetype is not allowed or uplaod file size 2MB');				
					#redirect("$this->TYPE/admin",'refresh');				
				}
				
				
				$img_url = 'thumb_'.$data['file_name'];		
				
				if($admin_id){
					$data =  array(
						'logo'     	=> $img_url,					
						'fname'     => $this->input->post('fname'),
						'lname'     => $this->input->post('lname'), 
						'email'     => $this->input->post('email'), 
						'mobile'    => $this->input->post('mobile')						
					);	
						
				}else{
					$password = isset($data['password']) ? md5($data['password']):'';					
					$data =  array(
						'logo'     	=> $img_url,					
						'fname'     => $this->input->post('fname'),
						'lname'     => $this->input->post('lname'), 
						'email'     => $this->input->post('email'), 
						'mobile'    => $this->input->post('mobile'),		
						'role'   	=> 1,
						
						'password'  => $password,
						'admin_uid'	=> uniq_uid(),
					);					
				}				
					

			}else{ 
				if($admin_id){
					
					$data =  array(					
						'fname'     => $data['fname'],
						'lname'     => $data['lname'],
						'email'     => $data['email'],
						'mobile'    => $data['mobile']	
					);
					
				}else{
					
					$password = isset($data['password']) ? md5($data['password']):'';
					$data =  array(					
						'fname'     => $data['fname'],
						'lname'     => $data['lname'],
						'email'     => $data['email'],
						'mobile'    => $data['mobile'],				
						'role'    	=> 1,									
						'password'  => $password,
						'admin_uid'	=>uniq_uid()
					);

				}
			
			}	
			
            if(isset($admin_id)){					
				//echo "<pre>"; print_r($data); echo "</pre>";die;
                $this->Query_model->update_data('ec_admin',$data,array('admin_id' => $admin_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');			
				
            }else{	
                $insert_db = $this->Query_model->insert_data('ec_admin',$data);
				if($insert_db){
					$this->session->set_flashdata('success', 'Inserted Successfully.');	
				}
                
            }
            redirect("$this->TYPE/admin");
        }  
	}

    function unique_field($value, $admin_id)
    {
        $email = $this->input->post('email');
        $args = array('email' => $email);
        if($admin_id){
            $args['admin_id!='] = $admin_id;
        }
        $admin_obj = $this->Query_model->get_data_obj('ec_admin',$args);		
        $status   = TRUE;
        $message  = '';
        if($admin_obj) {
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
		$crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->admin => "/$this->TYPE/admin/");
		$breadcrumbs = $this->breadcrumbs->show($crumbs);
		$data['breadcrumbs']    = $breadcrumbs;

		$data['csrf'] = csrf_token();
		$data['TYPE'] = $this->TYPE;			
		$data['basic'] = $this->User_model->get_profile_admin($id); 		
		$update = $this->load->template("$this->TYPE/admin/view_profile",$data); 	
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
        $update = $this->Admin_model->admin_status_change($id,$status_val);
        echo json_encode(array('success' => $update));
		
    }
	public function delimg(){ 
		$data['csrf'] = csrf_token();
	    $id = $this->input->post('id');
		$data['csrf'] = csrf_token();
        $data = array();
        $deleteimg = $this->Admin_model->deleteimg($id);
        echo json_encode(array('success' => $deleteimg));		
    }
	public function del(){ 
		$data['csrf'] = csrf_token();
	    $id = $this->input->post('id');
		$data['csrf'] = csrf_token();
        $data = array();
        $delete = $this->Admin_model->delete_user($id);
        echo json_encode(array('success' => $delete));		
    }
	
	public function update_password($admin_id = NULL){
			if(!logged_in()) redirect("$this->TYPE/auth/login");
			$data = array();
			$crumbs = array("Home" => "/$this->TYPE/dashboard", "Admin" => "");
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
				$success = $this->Admin_model->update_Password($admin_id,$data);
				$this->session->set_flashdata('feedback','Successfully Updated');
				echo json_encode(array('success' => $update));
				$this->load->template("$this->TYPE/admin/index");
				redirect("admin/admin");
				
			}else{
				$this->session->set_flashdata('feedback','Please enter valid password');			
				echo "Please enter valid password";
				
			}

          
    }
	//Change status by select all 
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
			$update = $this->Query_model->update_data('ec_admin',array('status' => $status),array('admin_id' => $ids));
		}
		
        echo json_encode(array('success' => $update));
		
    }
	
	public function export_csv(){ 
	  
		$filename = 'user_report_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$filename"); 
		header("Content-Type: application/csv; ");	
		$list = $this->Admin_model->get_export();		
		
		//echo "<pre>"; print_r($list); echo "</pre>";	die;
		
		
	   // file creation  
	    $file = fopen('php://output', 'w'); 
		$header = array("admin_id","admin_uid","fname","lname","email",'mobile','date_added');   
	  
	    fputcsv($file, $header);
	      
	    foreach ($list as $key=>$line){ 
	   	   fputcsv($file,$line); 
	    }
	   fclose($file); 
	   exit; 
	}		 
	
}
