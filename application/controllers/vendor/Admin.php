<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Admin_model');
		$this->load->model('User_model');
		$this->load->model('Query_model');
		$this->config->load('custom_config');
		$this->load->helper('api');
        $this->TYPE = $this->session->userdata('type') ? $this->session->userdata('type') : 'vendor';
		$user_session = $this->session->userdata($this->TYPE);
		$this->LOGIN_ID = isset($user_session['login_id']) ? (int)$user_session['login_id'] : (isset($user_session['vendor_id']) ? (int)$user_session['vendor_id'] : 0);
		$this->FNAME = isset($user_session['fname']) ? $user_session['fname'] : (isset($user_session['vendor_name']) ? $user_session['vendor_name'] : 'Vendor');
	}

	private function _base64url_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	private function _sign_token(array $claims, $ttl_seconds)
	{
		$secret = (string)$this->config->item('encryption_key');
		if ($secret === '') {
			$secret = 'default_api_secret';
		}

		$now = time();
		$claims['iat'] = $now;
		$claims['exp'] = $now + (int)$ttl_seconds;

		$payload = json_encode($claims);
		$b64 = $this->_base64url_encode($payload);
		$sig = hash_hmac('sha256', $b64, $secret, true);
		$b64sig = $this->_base64url_encode($sig);
		return $b64 . '.' . $b64sig;
	}

	private function _is_token_expired($token)
	{
		// Parse the payload section of the custom JWT (base64url.sig format)
		$parts = explode('.', (string)$token);
		if (count($parts) !== 2) return true;
		$padding = strlen($parts[0]) % 4;
		if ($padding) $parts[0] .= str_repeat('=', 4 - $padding);
		$claims = @json_decode(@base64_decode(strtr($parts[0], '-_', '+/')), true);
		if (!is_array($claims) || !isset($claims['exp'])) return true;
		return time() >= (int)$claims['exp'];
	}

	private function _get_vendor_access_token()
	{
		$sess = $this->session->userdata($this->TYPE);
		if (!is_array($sess) || empty($sess)) {
			$all_sess = $this->session->all_userdata();
			foreach ($all_sess as $key => $val) {
				if (is_array($val) && !empty($val['logged_in']) && (!empty($val['vendor_id']) || !empty($val['login_id']))) {
					$sess = $val;
					break;
				}
			}
		}

		// Return session token only if it is still valid (not expired)
		if (is_array($sess) && !empty($sess['access_token'])) {
			$existing = (string)$sess['access_token'];
			if (!$this->_is_token_expired($existing)) {
				return $existing;
			}
			// Token expired — clear it and fall through to regeneration
			unset($sess['access_token']);
		}
		if (!empty($_SESSION['token']) && !$this->_is_token_expired($_SESSION['token'])) {
			return (string)$_SESSION['token'];
		}

		// Self-generate a fresh 1-year token from the session vendor_id
		if (is_array($sess) && (!empty($sess['vendor_id']) || !empty($sess['login_id']))) {
			$vendor_id = !empty($sess['login_id']) ? (int)$sess['login_id'] : (int)$sess['vendor_id'];
			$token = $this->_sign_token(['sub' => $vendor_id, 'type' => 'access', 'role' => 'vendor'], 31536000);
			$sess['access_token'] = $token;
			$this->session->set_userdata('vendor', $sess);
			if (!empty($this->TYPE)) {
				$this->session->set_userdata($this->TYPE, $sess);
			}
			return $token;
		}
		return '';
	}

	private function _build_api_headers()
	{
		$token = $this->_get_vendor_access_token();
		if ($token === '') {
			return [];
		}
		return ['Authorization' => 'Bearer ' . $token];
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
		$current_id =$this->LOGIN_ID;
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
		$vendor_id = $this->LOGIN_ID;
		$basic = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
		if($basic){
			$basic->fname = isset($basic->name) ? $basic->name : '';
			$basic->lname = '';
			$basic->logo = isset($basic->vendor_img) ? $basic->vendor_img : '';
		}
		$data['basic'] = $basic;
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
    public function checkall_status_change()
    {

        $id = $this->input->post('id');
        $status = $this->input->post('status');
        $ids = json_decode($id);
        if ($status == 'active') {
            $status_val = 0;
        } elseif ($status == 'inactive') {
            $status_val = 1;
        }
        $data = array();
        if ($ids && $this->LOGIN_ID && $this->TYPE == 'admin') {
            $update = $this->Query_model->update_data('ec_admin', array('status' => $status), array('admin_id' => $ids));
        }
        echo json_encode(array('success' => $update));
    }

    public function export_csv()
    {

        $filename = 'user_report_' . date('Ymd') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        $list = $this->Admin_model->get_export();

        // file creation  
        $file = fopen('php://output', 'w');
        $header = array("admin_id", "admin_uid", "fname", "lname", "email", 'mobile', 'date_added');

        fputcsv($file, $header);

        foreach ($list as $key => $line) {
            fputcsv($file, $line);
        }

        fclose($file);
        exit;
    }

    	//vendor profile setting
	public function profile($id)
	{
		if (!logged_in()) redirect("$this->TYPE/auth/login");
		$shipping_addr = array();
		$billing_addr = array();
		$page_lang = get_page_language_data('admin_page_lang');
		$crumbs = array($page_lang->home => "/$this->TYPE/dashboard", 'vendor' => "/$this->TYPE/admin/",);
		$breadcrumbs = $this->breadcrumbs->show($crumbs);
		$data['breadcrumbs'] = $breadcrumbs;

		$data['csrf'] = csrf_token();
		$data['TYPE'] = $this->TYPE;
		// Always show the logged-in vendor's profile
		$vendor_id = $this->LOGIN_ID;
		$vendor_doc_status = '';
		$vendor_doc_admin_note = '';
		$vendor_doc_front = '';
		$vendor_doc_back = '';
		$vendor_admin_uploaded_doc = '';
		$doc_table = 'ec_vendor_verification';

		$basic = null;
		$headers = $this->_build_api_headers();
		if (!empty($headers)) {
			$api_res = call_api('GET', 'vendor/profile', null, $headers);
			if ($api_res['response'] !== null && (int)($api_res['response']['status'] ?? 0) === 1 && is_array($api_res['response']['data'] ?? null)) {
				$profile_data = $api_res['response']['data'];
				$basic = new stdClass();
				$basic->vendor_id = $profile_data['vendor_id'] ?? 0;
				$basic->name = $profile_data['name'] ?? '';
				$basic->fname = $profile_data['name'] ?? '';
				$basic->email = $profile_data['email'] ?? '';
				$basic->mobile = $profile_data['mobile'] ?? '';
				$basic->store_name = $profile_data['store_name'] ?? '';
				$basic->address = $profile_data['address'] ?? '';
				$basic->city = $profile_data['city'] ?? '';
				$basic->country = $profile_data['country'] ?? '';
				$basic->latitude = $profile_data['latitude'] ?? '';
				$basic->longitude = $profile_data['longitude'] ?? '';
				$basic->zip = $profile_data['zip'] ?? '';
				$basic->location = $profile_data['location'] ?? '';
				$basic->place_id = $profile_data['place_id'] ?? '';
				$basic->vendor_img = $profile_data['vendor_img'] ?? '';
				$basic->logo = $profile_data['logo'] ?? '';
				$acc_status = $profile_data['account_status'] ?? 'pending';
				$basic->status = ($acc_status === 'approved') ? 1 : (($acc_status === 'rejected') ? 2 : 0);
				
				$vendor_doc_status = $profile_data['document_status'] ?? '';
				$vendor_doc_admin_note = $profile_data['document_admin_note'] ?? '';
				$vendor_doc_front = $profile_data['document_front'] !== '' ? basename($profile_data['document_front']) : '';
				$vendor_doc_back = $profile_data['document_back'] !== '' ? basename($profile_data['document_back']) : '';
			}
		}

		if (!$basic) {
			$basic = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
			if ($basic) {
				// status in ec_vendor: 0=pending, 1=approved, 2=rejected
				if (isset($basic->status)) {
					$st = (string)$basic->status;
					if ($st === '1') {
						$vendor_doc_status = 'approved';
					} elseif ($st === '2') {
						$vendor_doc_status = 'rejected';
					} elseif ($st === '0') {
						$vendor_doc_status = 'pending';
					}
				}
				// Provide backward compatible keys expected by the view
				$basic->fname = isset($basic->name) ? $basic->name : '';
				$basic->mobile = isset($basic->mobile) ? $basic->mobile : '';
				// $basic->description = isset($basic->description) ? $basic->description : '';
				// $basic->place_id = isset($basic->place_id) ? $basic->place_id : '';
			}
		}

		$data['vendor_id'] = $vendor_id;

		if ($this->db->table_exists($doc_table) && $this->db->field_exists('status', $doc_table)) {
			$vendor_col = '';
			if ($this->db->field_exists('vendor_id', $doc_table)) {
				$vendor_col = 'vendor_id';
			} elseif ($this->db->field_exists('login_id', $doc_table)) {
				$vendor_col = 'login_id';
			} elseif ($this->db->field_exists('admin_id', $doc_table)) {
				$vendor_col = 'admin_id';
			}
			if ($vendor_col) {
				$row = $this->db
					->select('*')
					->from($doc_table)
					->where($vendor_col, (int)$vendor_id)
					->order_by('id', 'DESC')
					->limit(1)
					->get()->row();
				if ($row && isset($row->status)) {
					if ($vendor_doc_status === '') {
						$vendor_doc_status = (string)$row->status;
					}
					$vendor_doc_admin_note = (isset($row->admin_note) && $row->admin_note !== null) ? (string)$row->admin_note : '';
					$vendor_doc_front = (isset($row->document_front) && $row->document_front !== null) ? (string)$row->document_front : ((isset($row->doc_front) && $row->doc_front !== null) ? (string)$row->doc_front : '');
					$vendor_doc_back = (isset($row->document_back) && $row->document_back !== null) ? (string)$row->document_back : ((isset($row->doc_back) && $row->doc_back !== null) ? (string)$row->doc_back : '');
					$vendor_admin_uploaded_doc = (isset($row->admin_uploaded_doc) && $row->admin_uploaded_doc !== null) ? (string)$row->admin_uploaded_doc : '';
				}
			}
		}
		$data['vendor_document_status'] = $vendor_doc_status;
		$data['vendor_document_admin_note'] = $vendor_doc_admin_note;
		$data['vendor_document_front'] = $vendor_doc_front;
		$data['vendor_document_back'] = $vendor_doc_back;
		$data['vendor_admin_uploaded_doc'] = $vendor_admin_uploaded_doc;
		$data['basic'] = $basic;
		$data['access_token'] = $this->_get_vendor_access_token();
		$data['use_api'] = ($data['access_token'] !== '');
		$update = $this->load->template("$this->TYPE/admin/app-profile", $data);
	}

	public function reupload_documents()
	{
		$vendor_id = $this->LOGIN_ID;

		$headers = $this->_build_api_headers();
		if (!empty($headers)) {
			$payload = [];
			$has_file = false;
			if (isset($_FILES['document_front']['name']) && $_FILES['document_front']['name'] !== '') {
				$payload['document_front'] = new CURLFile($_FILES['document_front']['tmp_name'], $_FILES['document_front']['type'], $_FILES['document_front']['name']);
				$has_file = true;
			}
			if (isset($_FILES['document_back']['name']) && $_FILES['document_back']['name'] !== '') {
				$payload['document_back'] = new CURLFile($_FILES['document_back']['tmp_name'], $_FILES['document_back']['type'], $_FILES['document_back']['name']);
				$has_file = true;
			}
			if ($has_file) {
				$payload['__is_multipart'] = true;
				$api_res = call_api('POST', 'vendor/profile/documents', $payload, $headers);
				if ($api_res['response'] !== null) {
					$resp = $api_res['response'];
					if ((int)($resp['status'] ?? 0) === 1) {
						echo json_encode(array('success' => 1, 'msg' => $resp['message'] ?? 'Documents submitted. Your account is under verification.'));
					} else {
						$err = $resp['errors'][0] ?? ($resp['message'] ?? 'Failed to submit documents.');
						echo json_encode(array('success' => 0, 'msg' => $err));
					}
					return;
				}
			}
		}

		$doc_table = 'ec_vendor_verification';
		if (!$this->db->table_exists($doc_table)) {
			echo json_encode(array('success' => 0, 'msg' => 'Verification table not found.'));
			return;
		}

		$vendor_col = $this->db->field_exists('vendor_id', $doc_table) ? 'vendor_id' : ($this->db->field_exists('login_id', $doc_table) ? 'login_id' : ($this->db->field_exists('admin_id', $doc_table) ? 'admin_id' : ''));
		if (!$vendor_col) {
			echo json_encode(array('success' => 0, 'msg' => 'Invalid verification table schema.'));
			return;
		}

		$ver_row = $this->db
			->select('*')
			->from($doc_table)
			->where($vendor_col, (int)$vendor_id)
			->order_by('id', 'DESC')
			->limit(1)
			->get()->row();

		$cur_status = ($ver_row && isset($ver_row->status)) ? (string)$ver_row->status : '';
		if ($cur_status === '') {
			$cur_status = 'pending';
		}
		if ($cur_status !== 'rejected' && $cur_status !== 'pending') {
			echo json_encode(array('success' => 0, 'msg' => 'Document upload is allowed only when pending or rejected.'));
			return;
		}

		$upload_dir = FCPATH . 'uploads/vendor_documents';
		if (!is_dir($upload_dir)) {
			@mkdir($upload_dir, 0777, true);
		}

		$config = array(
			'upload_path' => $upload_dir,
			'allowed_types' => 'gif|jpg|png|jpeg|pdf',
			'max_size' => '20240000',
		);
		$this->load->library('upload', $config);

		$doc_front_name = '';
		$doc_back_name = '';
		if (isset($_FILES['document_front']['name']) && $_FILES['document_front']['name'] != '') {
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('document_front')) {
				echo json_encode(array('success' => 0, 'msg' => strip_tags($this->upload->display_errors())));
				return;
			}
			$data_file = $this->upload->data();
			$doc_front_name = isset($data_file['file_name']) ? $data_file['file_name'] : '';
		}
		if (isset($_FILES['document_back']['name']) && $_FILES['document_back']['name'] != '') {
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('document_back')) {
				echo json_encode(array('success' => 0, 'msg' => strip_tags($this->upload->display_errors())));
				return;
			}
			$data_file = $this->upload->data();
			$doc_back_name = isset($data_file['file_name']) ? $data_file['file_name'] : '';
		}

		$update = array();
		if ($this->db->field_exists('document_front', $doc_table) && $doc_front_name) {
			$update['document_front'] = $doc_front_name;
		}
		if ($this->db->field_exists('document_back', $doc_table) && $doc_back_name) {
			$update['document_back'] = $doc_back_name;
		}
		if ($this->db->field_exists('status', $doc_table)) {
			$update['status'] = 'pending';
		}
		if ($this->db->field_exists('admin_note', $doc_table)) {
			$update['admin_note'] = '';
		}
		if ($this->db->field_exists('updated_at', $doc_table)) {
			$update['updated_at'] = date('Y-m-d H:i:s');
		}

		if (!$update || !$ver_row || !isset($ver_row->id)) {
			echo json_encode(array('success' => 0, 'msg' => 'No changes detected.'));
			return;
		}

		$this->db->where('id', (int)$ver_row->id);
		$this->db->update($doc_table, $update);
		$this->_reset_vendor_status_local($vendor_id);
		echo json_encode(array('success' => 1, 'msg' => 'Documents submitted. Your account is under verification.'));
	}

    public function ajaxform()
    {
		$headers = $this->_build_api_headers();
		if (!empty($headers)) {
			$payload = $this->input->post(NULL, true) ?: [];
			if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'] !== '') {
				$payload['logo'] = new CURLFile($_FILES['logo']['tmp_name'], $_FILES['logo']['type'], $_FILES['logo']['name']);
				$payload['__is_multipart'] = true;
			}
			if (isset($payload['fname']) && !isset($payload['name'])) {
				$payload['name'] = $payload['fname'];
			}
			$api_res = call_api('POST', 'vendor/profile/update', $payload, $headers);
			if ($api_res['response'] !== null) {
				$resp = $api_res['response'];
				if ((int)($resp['status'] ?? 0) === 1) {
					echo json_encode(array('msg' => $resp['message'] ?? 'Updated Successfully.', 'success' => 1, 'data' => array()));
				} else {
					$err = $resp['errors'][0] ?? ($resp['message'] ?? 'Failed to update profile.');
					echo json_encode(array('msg' => $err, 'success' => 0));
				}
				return;
			}
		}

        $this->check_validation();
        $vendor_id = $this->LOGIN_ID;

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters();

        $post = $this->input->post();
        $post = $this->security->xss_clean($post);

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('msg' => validation_errors(), 'success' => 0));
            return;
        }

        if (isset($_FILES["logo"]["name"]) && $_FILES["logo"]["name"] != '') {
            $this->upload_file($vendor_id, $post);
            return;
        }

        $update_data = array(
            'name' => isset($post['fname']) ? $post['fname'] : (isset($post['name']) ? $post['name'] : ''),
            'email' => isset($post['email']) ? $post['email'] : '',
            'mobile' => isset($post['mobile']) ? $post['mobile'] : ''
        );

        $this->Query_model->update_data('ec_vendor', $update_data, array('vendor_id' => $vendor_id));
        $this->_reset_vendor_status_local($vendor_id);
        echo json_encode(array('msg' => 'Updated Successfully.', 'success' => 1, 'data' => array()));
    }

    public function upload_file($id = NULL, $data)
    {
        if (isset($_FILES["logo"]["name"]) && $_FILES["logo"]["name"] != '') {
            $config = array(
                'upload_path' => "./assets/vendor/images",
                'allowed_types' => "gif|jpg|png|jpeg|pdf",
                'max_size' => "20240000",
            );
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('logo')) {
                echo json_encode(array('msg' => 'File type not match or something Missing.', 'success' => 0, 'data' => array()));
                return;
            }

            $data_file = $this->upload->data();
            $file_name = $data_file["file_name"];
            $thumb_arr = explode(".", $file_name);
            $large = $file_name;
            $thumb = $thumb_arr[0] . "_thumb" . "." . $thumb_arr[1];

            $config = array(
                'image_library' => 'gd2',
                'source_image' => "./assets/vendor/images/" . $data_file['file_name'],
                'maintain_ratio' => true,
                'allowed_types' => "gif|jpg|png|jpeg",
                'width' => 150,
                'height' => 150,
                'new_image' => "./assets/vendor/images/" . $thumb,
            );
            $this->load->library('image_lib', $config);
            $this->upload->initialize($config);
            $this->image_lib->resize();
            $this->image_lib->clear();

            $image = serialize(array(
                'large' => base_url() . '/assets/vendor/images/' . $large,
                'thumb' => base_url() . '/assets/vendor/images/' . $thumb
            ));

            $update_data = array(
                'logo' => $image,
                'name' => isset($data['fname']) ? $data['fname'] : (isset($data['name']) ? $data['name'] : ''),
                'email' => isset($data['email']) ? $data['email'] : '',
                'mobile' => isset($data['mobile']) ? $data['mobile'] : ''
            );
            $this->Query_model->update_data('ec_vendor', $update_data, array('vendor_id' => $id));
            $this->_reset_vendor_status_local($id);
            echo json_encode(array('msg' => 'Updated Successfully.', 'success' => 1, 'data' => array()));
        }
    }

    public function ajax_accountform()
    {
		$headers = $this->_build_api_headers();
		if (!empty($headers)) {
			$payload = $this->input->post(NULL, true) ?: [];
			if (isset($_FILES['vendor_img']['name']) && $_FILES['vendor_img']['name'] !== '') {
				$payload['vendor_img'] = new CURLFile($_FILES['vendor_img']['tmp_name'], $_FILES['vendor_img']['type'], $_FILES['vendor_img']['name']);
				$payload['__is_multipart'] = true;
			}
			$api_res = call_api('POST', 'vendor/profile/update_profile', $payload, $headers);
			if ($api_res['response'] !== null) {
				$resp = $api_res['response'];
				if ((int)($resp['status'] ?? 0) === 1) {
					echo json_encode(array('msg' => $resp['message'] ?? 'Updated Successfully.', 'success' => 1, 'data' => array()));
				} else {
					$err = $resp['errors'][0] ?? ($resp['message'] ?? 'Failed to update store info.');
					echo json_encode(array('msg' => $err, 'success' => 0));
				}
				return;
			}
		}

        $this->check_validation_accountInfo();
        $vendor_id = $this->LOGIN_ID;
        $data = array();
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters();
        $post = $this->input->post();
        $post = $this->security->xss_clean($post);

        // Only allow ec_vendor columns here. The view posts legacy keys (zip/phone/description/place_id)
        // that do not exist in ec_vendor.
        $data = array(
            'store_name' => isset($post['store_name']) ? $post['store_name'] : null,
            'address'    => isset($post['address']) ? $post['address'] : null,
            'city'       => isset($post['city']) ? $post['city'] : null,
            'country'    => isset($post['country']) ? $post['country'] : null,
            'latitude'   => isset($post['latitude']) ? $post['latitude'] : null,
            'longitude'  => isset($post['longitude']) ? $post['longitude'] : null,
            'mobile'      => isset($post['mobile']) ? $post['mobile'] : null,
            // 'description'=> isset($post['description']) ? $post['description'] : null,
            // 'place_id'   => isset($post['place_id']) ? $post['place_id'] : null,
        );

        if (isset($_FILES["vendor_img"]["name"]) && $_FILES["vendor_img"]["name"] != '') {
            $config = array(
                'upload_path' => "./assets/vendor/images",
                'allowed_types' => "gif|jpg|png|jpeg",
                'max_size' => "20240000",
            );

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('vendor_img')) {
                $error = array('error' => $this->upload->display_errors());
                echo json_encode(
                    array(
                        'msg' => 'File type not match or something Missing.',
                        'success' => 0,
                        'data' => array()
                    )
                );
            } else {
                $data_file = $this->upload->data();
                $file_name = $data_file["file_name"];
                $thumb_arr = explode(".", $file_name);
                $large = $file_name;
                $thumb = $thumb_arr[0] . "_thumb" . "." . $thumb_arr[1];

                $config = array(
                    'image_library' => 'gd2', //get original image
                    'source_image' => "./assets/vendor/images/" . $data_file['file_name'],
                    'maintain_ratio' => true,
                    'allowed_types' => "gif|jpg|png|jpeg",
                    'width' => 150,
                    'height' => 150,
                    'new_image' => "./assets/vendor/images/" . $thumb,
                );
                $this->load->library('image_lib', $config);
                $this->upload->initialize($config);
                $this->image_lib->resize();
                $this->image_lib->clear();

                $var = serialize(array('large' => base_url() . '/assets/vendor/images/' . $large, 'thumb' => base_url() . '/assets/vendor/images/' . $thumb));

                $data['vendor_img'] = $var;

                if (isset($vendor_id)) {
                    $this->Query_model->update_data('ec_vendor', $data, array('vendor_id' => $vendor_id));
                    $this->_reset_vendor_status_local($vendor_id);
                    echo json_encode(
                        array(
                            'msg' => 'Updated Successfully.',
                            'success' => 1,
                            'data' => array()
                        )
                    );
                }
            }
        } else {
            if (isset($vendor_id)) {
                $this->Query_model->update_data('ec_vendor', $data, array('vendor_id' => $vendor_id));
                $this->_reset_vendor_status_local($vendor_id);
                echo json_encode(
                    array(
                        'msg' => 'Updated Successfully.',
                        'success' => 1,
                        'data' => array()
                    )
                );
            }
        }
    }

    private function _reset_vendor_status_local($vendor_id)
    {
        // 1. Get current vendor status
        $vendor = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
        if ($vendor && (string)$vendor->status === '2') {
            // Reset vendor status to pending (0)
            $this->Query_model->update_data('ec_vendor', array('status' => 0), array('vendor_id' => $vendor_id));
            // Sync all products of this vendor to inactive (0)
            sync_vendor_product_status($vendor_id, 0);
        }

        // 2. Also reset verification row status to 'pending' if it was rejected
        $doc_table = 'ec_vendor_verification';
        if ($this->db->table_exists($doc_table)) {
            $vendor_col = '';
            if ($this->db->field_exists('vendor_id', $doc_table))      $vendor_col = 'vendor_id';
            elseif ($this->db->field_exists('login_id', $doc_table))   $vendor_col = 'login_id';
            elseif ($this->db->field_exists('admin_id', $doc_table))   $vendor_col = 'admin_id';

            if ($vendor_col) {
                $ver_row = $this->db->select('*')->from($doc_table)
                    ->where($vendor_col, (int)$vendor_id)
                    ->order_by('id', 'DESC')->limit(1)->get()->row();
                if ($ver_row && (string)($ver_row->status ?? '') === 'rejected') {
                    $this->db->where('id', (int)$ver_row->id)->update($doc_table, array(
                        'status' => 'pending',
                        'admin_note' => '',
                        'updated_at' => date('Y-m-d H:i:s')
                    ));
                }
            }
        }
    }

    public function check_validation_accountInfo()
    {
        $this->load->helper(array('form', 'url'));		
        $this->load->library('form_validation');		
        $this->form_validation->set_error_delimiters('<div>', '</div>');          
        $this->form_validation->set_rules('store_name','Store name', 'trim|required'); 
		$this->form_validation->set_rules('address','Address', 'trim|required');
				

    }

	public function check_validation()
	{
		$this->load->helper(array('form', 'url'));		
		$this->load->library('form_validation');		
		$this->form_validation->set_error_delimiters('<div>', '</div>');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|xss_clean');
	}

    /**
     * POST /vendor/profile/change_password
     * AJAX: forwards change-password request to the vendor profile API.
     */
    public function change_password()
    {
        if (!logged_in()) {
            echo json_encode(['success' => false, 'msg' => 'Not authenticated.']);
            return;
        }

        $new_password     = $this->input->post('new_password',     true);
        $confirm_password = $this->input->post('confirm_password', true);

        if (!$new_password || !$confirm_password) {
            echo json_encode(['success' => false, 'msg' => 'New password and confirm password are required.']);
            return;
        }

        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'msg' => 'New password must be at least 6 characters.']);
            return;
        }

        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'msg' => 'New password and confirm password do not match.']);
            return;
        }

        $headers = $this->_build_api_headers();
        if (empty($headers)) {
            echo json_encode(['success' => false, 'msg' => 'Session expired. Please log in again.']);
            return;
        }

        $api_res = call_api('POST', 'vendor/profile/password', [
            'new_password'     => $new_password,
            'confirm_password' => $confirm_password,
        ], $headers);

        $resp = $api_res['response'] ?? null;
        if ($resp && (int)($resp['status'] ?? 0) === 1) {
            echo json_encode(['success' => true, 'msg' => $resp['message'] ?? 'Password updated successfully.']);
        } else {
            $err = '';
            if (!empty($resp['errors']) && is_array($resp['errors'])) $err = (string)$resp['errors'][0];
            if ($err === '') $err = (string)($resp['message'] ?? 'Failed to update password. Please try again.');
            echo json_encode(['success' => false, 'msg' => $err]);
        }
    }
	
	
}
