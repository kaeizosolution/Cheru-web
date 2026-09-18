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

            // Fetch assigned role names for this sub-admin
            $role_names = array();
            $roles = $this->db->select('r.name')
                ->from('ec_admin_to_roles ar')
                ->join('ec_admin_roles r', 'r.role_id = ar.role_id')
                ->where('ar.admin_id', $obj->admin_id)
                ->where('r.status', 1)
                ->get()->result();
            foreach ($roles as $r) {
                $role_names[] = $r->name;
            }
            $role_str = !empty($role_names) ? implode(', ', $role_names) : 'No Roles';

            $row['superadmin']      = $role_str;
            $row['status']          = $st;
            $row['date_added']      = $obj->date_added;
            $row['last_updated']    = $obj->last_updated;
        
            $data[] = $row;
        }
		
        $output = array(
            "draw" => isset($_POST['draw'])?$_POST['draw']:'',
            "recordsTotal" => $this->Admin_model->admin_count_all('ec_admin', array('super_admin !=' => 1)),
            "recordsFiltered" => $this->Admin_model->admin_count_filtered('ec_admin', array('super_admin !=' => 1)),
            "data" => $data,
        );			
        echo json_encode($output);
    }
	
	public function add()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
        $this->check_module_permission('admin', true);
		$this->post();		
	}

	public function update($admin_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
        $this->check_module_permission('admin', true);
		$this->post($admin_id);
    }

    // -------------------------------------------------------
    // My Profile: view and save for the currently logged-in admin
    // -------------------------------------------------------
    public function profile()
    {
        if (!logged_in()) redirect("$this->TYPE/auth/login");

        $page_lang   = get_page_language_data('admin_page_lang');
        $sess        = $this->session->userdata($this->TYPE);
        $admin_id    = (int)$sess['login_id'];
        $admin_obj   = $this->Query_model->get_data_obj('ec_admin', array('admin_id' => $admin_id));

        $crumbs      = array(
            isset($page_lang->home) ? $page_lang->home : 'Home' => "/$this->TYPE/dashboard",
            'My Profile' => ''
        );
        $data['breadcrumbs'] = $this->breadcrumbs->show($crumbs);
        $data['TYPE']        = $this->TYPE;
        $data['csrf']        = csrf_token();
        $data['page_lang']   = $page_lang;
        $data['admin_obj']   = $admin_obj;

        $this->load->template("$this->TYPE/admin/profile", $data);
    }

    public function profile_save()
    {
        if (!logged_in()) redirect("$this->TYPE/auth/login");

        $sess     = $this->session->userdata($this->TYPE);
        $admin_id = (int)$sess['login_id'];

        $this->load->library('form_validation');
        $this->form_validation->set_rules('fname', 'First Name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('email',  'Email',      'required|trim|valid_email|xss_clean');

        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('profile_error', validation_errors());
            redirect("$this->TYPE/admin/profile");
        }

        $admin_obj = $this->Query_model->get_data_obj('ec_admin', array('admin_id' => $admin_id));
        // Read existing image from admin_img column (with fallback to legacy logo column)
        $admin_img = '';
        if ($admin_obj) {
            $admin_img = !empty($admin_obj->admin_img) ? $admin_obj->admin_img
                       : (!empty($admin_obj->logo)     ? $admin_obj->logo : '');
        }

        // Handle profile picture upload — same pattern as rest of codebase
        if (!empty($_FILES['logo']['name'])) {
            $upload_config = array(
                'upload_path'   => './assets/images',
                'allowed_types' => 'gif|jpg|png|jpeg|webp',
                'max_size'      => '20240000',
            );
            $this->load->library('Upload', $upload_config);
            $this->upload->initialize($upload_config);

            if ($this->upload->do_upload('logo')) {
                $udata    = $this->upload->data();
                $filename = $udata['file_name'];

                // Create resized thumbnail (same as sub-admin upload)
                $config2 = array(
                    'image_library'  => 'gd2',
                    'source_image'   => './assets/images/' . $filename,
                    'maintain_ratio' => TRUE,
                    'width'          => 800,
                    'height'         => 800,
                    'new_image'      => './assets/images/thumb_' . $filename,
                );
                $this->load->library('image_lib', $config2);
                $this->image_lib->resize();
                $this->image_lib->clear();

                $admin_img = 'thumb_' . $filename;
            } else {
                // Flash upload error so it's visible
                $this->session->set_flashdata('profile_error', 'Image upload failed: ' . $this->upload->display_errors('', ''));
                redirect("$this->TYPE/admin/profile");
            }
        }

        $update = array(
            'fname'     => $this->input->post('fname'),
            'lname'     => $this->input->post('lname'),
            'mobile'    => $this->input->post('mobile'),
            'email'     => $this->input->post('email'),
            'admin_img' => $admin_img,
        );
        $this->db->where('admin_id', $admin_id)->update('ec_admin', $update);

        // Refresh session with new values so header avatar updates immediately
        $sess['fname']  = $update['fname'];
        $sess['email']  = $update['email'];
        $sess['mobile'] = $update['mobile'];
        $sess['image']  = $admin_img;
        $this->session->set_userdata($this->TYPE, $sess);

        $this->session->set_flashdata('profile_success', 'Profile updated successfully.');
        redirect("$this->TYPE/admin/profile");
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

            // RBAC: Load all available roles for the role selector dropdown
            $data['all_roles'] = $this->db->select('role_id, name')
                ->from('ec_admin_roles')->where('status', 1)->order_by('name', 'ASC')->get()->result();

            // Load all roles assigned to this admin as an array
            $data['assigned_roles'] = array();
            if ($admin_id) {
                $role_rows = $this->db->select('role_id')->from('ec_admin_to_roles')->where('admin_id', (int)$admin_id)->get()->result();
                foreach ($role_rows as $rr) {
                    $data['assigned_roles'][] = (int)$rr->role_id;
                }
            }

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
				
				$posted_role_ids = $this->input->post('role_ids');
				$first_role_id = (!empty($posted_role_ids) && is_array($posted_role_ids)) ? (int)$posted_role_ids[0] : 2;

				if($admin_id){
					$data =  array(
						'logo'      => $img_url,					
						'fname'     => $this->input->post('fname'),
						'lname'     => $this->input->post('lname'), 
						'email'     => $this->input->post('email'), 
						'mobile'    => $this->input->post('mobile'),
						'role_id'   => $first_role_id,
					);	
						
				}else{
					$password = isset($data['password']) ? md5($data['password']):'';					
					$data =  array(
						'logo'      => $img_url,					
						'fname'     => $this->input->post('fname'),
						'lname'     => $this->input->post('lname'), 
						'email'     => $this->input->post('email'), 
						'mobile'    => $this->input->post('mobile'),		
						'role_id'   => $first_role_id,
						'password'  => $password,
						'admin_uid'	=> uniq_uid(),
					);					
				}				
					

			}else{ 
				$posted_role_ids = $this->input->post('role_ids');
				$first_role_id = (!empty($posted_role_ids) && is_array($posted_role_ids)) ? (int)$posted_role_ids[0] : 2;

				if($admin_id){
					
					$data =  array(					
						'fname'     => $data['fname'],
						'lname'     => $data['lname'],
						'email'     => $data['email'],
						'mobile'    => $data['mobile'],
						'role_id'   => $first_role_id,
					);
					
				}else{
					
					$password = isset($data['password']) ? md5($data['password']):'';
					$data =  array(					
						'fname'     => $data['fname'],
						'lname'     => $data['lname'],
						'email'     => $data['email'],
						'mobile'    => $data['mobile'],				
						'role_id'   => $first_role_id,
						'password'  => $password,
						'admin_uid'	=> uniq_uid()
					);

				}
			
			}	
			
            if(isset($admin_id)){
                $this->Query_model->update_data('ec_admin',$data,array('admin_id' => $admin_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');
                $saved_admin_id = $admin_id;
            }else{
                $insert_db = $this->Query_model->insert_data('ec_admin',$data);
				if($insert_db){
					$this->session->set_flashdata('success', 'Inserted Successfully.');
				}
                $saved_admin_id = $insert_db;
            }

            // Save multiple roles to join table
            if ($saved_admin_id) {
                $this->db->where('admin_id', (int)$saved_admin_id)->delete('ec_admin_to_roles');
                $posted_role_ids = $this->input->post('role_ids');
                if (!empty($posted_role_ids) && is_array($posted_role_ids)) {
                    foreach ($posted_role_ids as $r_id) {
                        $r_id = (int)$r_id;
                        if ($r_id > 0) {
                            $this->db->insert('ec_admin_to_roles', array(
                                'admin_id' => (int)$saved_admin_id,
                                'role_id'  => $r_id
                            ));
                        }
                    }
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
			
			$sess = $this->session->userdata($this->TYPE);
			$logged_in_id = (int)$sess['login_id'];
			
			if($onep == $twop){
				$data = array(
					'password'=> md5($onep)
				);
				$success = $this->Admin_model->update_Password($admin_id,$data);
				
				if ((int)$admin_id === $logged_in_id) {
					$this->session->set_flashdata('profile_success', 'Password updated successfully.');
					redirect("$this->TYPE/admin/profile");
				} else {
					$this->session->set_flashdata('feedback','Successfully Updated');
					redirect("$this->TYPE/admin");
				}
			}else{
				if ((int)$admin_id === $logged_in_id) {
					$this->session->set_flashdata('profile_error', 'Passwords do not match.');
					redirect("$this->TYPE/admin/profile");
				} else {
					$this->session->set_flashdata('feedback','Please enter valid password');			
					redirect("$this->TYPE/admin");
				}
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

    // ================================================
    // ROLES MANAGEMENT (RBAC)
    // ================================================

    public function roles()
    {
        if (!logged_in()) redirect("$this->TYPE/auth/login");
        $this->check_module_permission('admin', true);
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array(
            isset($page_lang->home) ? $page_lang->home : 'Home' => "/$this->TYPE/dashboard",
            'Admin' => "/$this->TYPE/admin",
            'Roles' => ''
        );
        $data['breadcrumbs'] = $this->breadcrumbs->show($crumbs);
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['roles'] = $this->db->select('r.role_id, r.name, r.status, r.date_added, COUNT(a.admin_id) AS admin_count')
            ->from('ec_admin_roles r')
            ->join('ec_admin a', 'a.role_id = r.role_id', 'left')
            ->group_by('r.role_id')
            ->order_by('r.role_id', 'ASC')
            ->get()->result();
        $this->load->template("$this->TYPE/admin/roles_list", $data);
    }

    public function role_add()
    {
        if (!logged_in()) redirect("$this->TYPE/auth/login");
        $this->check_module_permission('admin', true);
        $this->role_post();
    }

    public function role_edit($role_id)
    {
        if (!logged_in()) redirect("$this->TYPE/auth/login");
        $this->check_module_permission('admin', true);
        $this->role_post((int)$role_id);
    }

    private function role_post($role_id = null)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('role_name', 'Role Name', 'required|trim|xss_clean');

        // Ensure permission types exist
        $count_types = $this->db->count_all('ec_admin_permission_type');
        if ($count_types < 4) {
            $this->db->truncate('ec_admin_permission_type');
            $this->db->insert_batch('ec_admin_permission_type', array(
                array('perm_type_id' => 1, 'name' => 'View',    'class' => 'view'),
                array('perm_type_id' => 2, 'name' => 'Add',     'class' => 'edit'),
                array('perm_type_id' => 3, 'name' => 'Edit',    'class' => 'edit'),
                array('perm_type_id' => 4, 'name' => 'Approve', 'class' => 'edit'),
            ));
        }

        $page_lang  = get_page_language_data('admin_page_lang');
        $action_bc  = $role_id ? 'Edit Role' : 'Add Role';
        $crumbs     = array(
            isset($page_lang->home) ? $page_lang->home : 'Home' => "/$this->TYPE/dashboard",
            'Admin' => "/$this->TYPE/admin",
            'Roles' => "/$this->TYPE/admin/roles",
            $action_bc => ''
        );

        $data['breadcrumbs']   = $this->breadcrumbs->show($crumbs);
        $data['TYPE']          = $this->TYPE;
        $data['csrf']          = csrf_token();
        $data['role_id']       = $role_id;
        $data['all_modules']   = $this->db->select('module_id, title, module_key, parent_id')
            ->from('ec_admin_modules')->where('status', 1)->order_by('sort_order', 'ASC')->get()->result();
        $data['all_perm_types'] = $this->db->select('perm_type_id, name, class')
            ->from('ec_admin_permission_type')->order_by('perm_type_id', 'ASC')->get()->result();

        // Load existing role permissions for this role (if editing)
        $data['assigned_permissions'] = array();
        if ($role_id) {
            $existing = $this->db->select('module_id, perm_type_id')
                ->from('ec_admin_role_permissions')->where('role_id', $role_id)->get()->result();
            foreach ($existing as $ep) {
                $data['assigned_permissions'][$ep->module_id][$ep->perm_type_id] = true;
            }
        }

        $data['role_name']   = '';
        $data['role_status'] = 1;
        if ($role_id) {
            $role_obj = $this->db->select('name, status')->from('ec_admin_roles')->where('role_id', $role_id)->limit(1)->get()->row();
            if ($role_obj) {
                $data['role_name']   = $role_obj->name;
                $data['role_status'] = $role_obj->status;
            }
        }

        if ($this->form_validation->run() == FALSE) {
            $this->load->template("$this->TYPE/admin/role_form", $data);
        } else {
            $posted_name   = trim($this->input->post('role_name'));
            $posted_status = (int)$this->input->post('role_status') ? 1 : 0;

            if ($role_id) {
                $this->db->where('role_id', $role_id)->update('ec_admin_roles', array('name' => $posted_name, 'status' => $posted_status));
                $this->db->where('role_id', $role_id)->delete('ec_admin_role_permissions');
            } else {
                $this->db->insert('ec_admin_roles', array('name' => $posted_name, 'status' => $posted_status, 'date_added' => date('Y-m-d H:i:s')));
                $role_id = $this->db->insert_id();
            }

            // Save new permission checkboxes
            $posted_modules    = $this->input->post('modules');    // array of module_ids
            $posted_perm_types = $this->input->post('perm_type');  // [module_id => array of perm_type_ids]
            if (is_array($posted_modules) && is_array($posted_perm_types)) {
                foreach ($posted_modules as $mid) {
                    $mid = (int)$mid;
                    if ($mid > 0 && isset($posted_perm_types[$mid]) && is_array($posted_perm_types[$mid])) {
                        $ptids = array_unique(array_filter(array_map('intval', $posted_perm_types[$mid])));
                        foreach ($ptids as $ptid) {
                            if ($ptid > 0) {
                                $this->db->insert('ec_admin_role_permissions', array(
                                    'role_id'      => (int)$role_id,
                                    'module_id'    => $mid,
                                    'perm_type_id' => $ptid,
                                ));
                            }
                        }
                    }
                }
            }

            $this->session->set_flashdata('success', $role_id ? 'Role updated successfully.' : 'Role created successfully.');
            redirect("$this->TYPE/admin/roles");
        }
    }

    public function role_delete($role_id)
    {
        if (!logged_in()) redirect("$this->TYPE/auth/login");
        $this->check_module_permission('admin', true);
        $role_id = (int)$role_id;
        $count = (int)$this->db->where('role_id', $role_id)->count_all_results('ec_admin');
        if ($count > 0) {
            $this->session->set_flashdata('error', 'Cannot delete role: ' . $count . ' admin(s) are still assigned to it.');
        } else {
            $this->db->where('role_id', $role_id)->delete('ec_admin_role_permissions');
            $this->db->where('role_id', $role_id)->delete('ec_admin_roles');
            $this->session->set_flashdata('success', 'Role deleted successfully.');
        }
        redirect("$this->TYPE/admin/roles");
    }

}
