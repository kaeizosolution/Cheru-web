<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');
        $this->TYPE = $this->session->userdata('type');
	}
	
	public function index()
    {
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/index",$data);

		#if(!$this->logged_in()) redirect("$this->TYPE/auth/login");
		// Redirect to your logged in landing page here
		#redirect("$this->TYPE/dashboard");
	}
	
	/**
	 * Login page
	*/
	public function login()
    {
		// Redirect to your logged in landing page here
		if($this->logged_in()) redirect("$this->TYPE/dashboard");
		 
		$data['error'] = false;
		$data['message']['error'] = ''; 

        $this->load->library('form_validation');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
        
        $email       = $this->input->post('email');
        $password    = $this->input->post('password');
        if($this->form_validation->run()){
            $password = md5($password);
            // Allow both super admins and sub-admins to log in (no role_id restriction)
            $args = array('email' => $email, 'password' => $password, 'status' => '1');
            $admin = $this->Query_model->get_data_obj('ec_admin', $args);
            if($admin){
                // Build permissions map from DB for sub-admins
                $permissions = array();
                $role_ids = array();
                if (empty($admin->super_admin) || $admin->super_admin != 1) {
                    // RBAC: Load permissions from all roles assigned to this admin
                    $role_rows = $this->db->select('role_id')->from('ec_admin_to_roles')->where('admin_id', (int)$admin->admin_id)->get()->result();
                    foreach ($role_rows as $rr) {
                        $role_ids[] = (int)$rr->role_id;
                    }
                    
                    // Fallback to legacy single role_id if no rows in join table yet
                    if (empty($role_ids) && !empty($admin->role_id)) {
                        $role_ids[] = (int)$admin->role_id;
                    }

                    if (!empty($role_ids)) {
                        $perm_rows = $this->db
                            ->select('m.module_key, t.class')
                            ->from('ec_admin_role_permissions rp')
                            ->join('ec_admin_modules m', 'm.module_id = rp.module_id', 'left')
                            ->join('ec_admin_permission_type t', 't.perm_type_id = rp.perm_type_id', 'left')
                            ->where_in('rp.role_id', $role_ids)
                            ->where('m.status', 1)
                            ->get()->result();
                        foreach ($perm_rows as $row) {
                            if (!empty($row->module_key)) {
                                // Highest privilege wins: 'edit' always beats 'view'
                                if (!isset($permissions[$row->module_key])) {
                                    $permissions[$row->module_key] = $row->class;
                                } elseif ($row->class === 'edit') {
                                    $permissions[$row->module_key] = 'edit';
                                }
                            }
                        }
                    }
                }

                 $details = array(
                                'email'         => $admin->email,
                                'fname'         => $admin->fname,
                                'mobile'        => isset($admin->mobile) ? $admin->mobile : '',
                                'image'         => !empty($admin->admin_img) ? $admin->admin_img : (isset($admin->logo) ? $admin->logo : ''),
                                'login_id'      => $admin->admin_id,
                                'super_admin'   => $admin->super_admin,
                                'role_id'       => !empty($role_ids) ? $role_ids[0] : 0,
                                'role_ids'      => $role_ids,
                                'permissions'   => $permissions,
                                'logged_in'     => TRUE
                         );
                 $this->session->set_userdata($this->TYPE, $details);
                 redirect("$this->TYPE/dashboard");
            }else{
                $data['message']['error']='Your email address and/or password is incorrect.';
            }
		}

        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/auth/login",$data);
	}

    public function impersonate($admin_uid)
    {
/*
        $admin_data = $this->Auth_model->get_customer_data($admin_uid);
        $email = $admin_data->email;
        $password = $admin_data->password;
        $admin = $this->Auth_model->get_customer_login($email,$password);
            if($admin){
                 $details = array(
                                'email'         => $admin->email,
                                'fname'         => $admin->fname,
                                'login_id'      => $admin->customer_id,
                                'logged_in'     => TRUE
                         );
                 $this->session->set_userdata($this->TYPE, $details);
                 redirect("$this->TYPE/dashboard");
            }else{
                $data['message']['error']='Your email address and/or password is incorrect.';
            }
*/
    }
	
	/**
	 * Logout page
	 */
	public function logout()
	{
        $this->session->unset_userdata($this->TYPE); 
        redirect("$this->TYPE/auth/login");
	}
	
	/**
	 * Forgot password page
	 */
	public function forgot()
    {
        // Redirect to your logged in landing page here
		if($this->logged_in()) redirect("$this->TYPE/dashboard");

        $this->load->library('form_validation');
        $data['error'] = false;
        $data['message']['error'] = '';
 
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $email = $this->input->post('email');
        if($this->form_validation->run()){
            $args = array('email' => $email, 'status' => '1');
            $admin = $this->Query_model->get_data_obj('ec_admin',$args);
            if($admin){
                $slug       = md5(random_string_data());
                $base_url   = $this->config->item('base_url');
                $site_url   = $base_url."/".$this->TYPE."/auth/reset/".$slug;
       
                $data =  array(
                        'admin_id'      => $admin->admin_id,
                        'reset_key'     => $slug,
                        'ip_address'    => $_SERVER['REMOTE_ADDR'],
                        );
                $last_inserted_id    =  $this->Query_model->insert_data('ec_admin_password_reset',$data);
                $email = $admin->email;
                $this->load->library('mailer');
                $ret_status = $this->mailer->smtp(array('SUBJECT' => 'Reset your Password', 'EMAIL' => $email, 'CONTENT' => $this->forgot_mailer_content($site_url))); 
                if (!$ret_status) {
                    $this->session->set_flashdata('message','Failed to send password, please try again!');
                } else {
                    $this->session->set_flashdata('message','Password sent to your email!');
                }
            }else{
                $data['message']['error']='Your email address is incorrect.';
            }
        }
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/auth/forgot_password",$data);
    }

    public function forgot_mailer_content($site_url)
    {
        $date = date('j M Y');
        $html =<<<HTML
            <p>To reset your password please click the link below and follow the instructions:</p>

            <p>$site_url</p>
            <p>If you did not request to reset your password then please just ignore this email and no changes will occur. </p>

            <p>Note: This reset code will expire after $date .</p>
HTML;
        return $html;
    }
	
	/**
	 * Reset password page
	 */
	public function reset($sid = NULL)
	{
        $this->session->set_flashdata('message','');
		// Redirect to your logged in landing page here
		if($this->logged_in()) redirect("$this->TYPE/dashboard");
       
		$this->load->library('form_validation');
		$data['error'] = false;
		$data['message']['error'] = '';

        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[50]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
		if($this->form_validation->run()){
            $sid = $this->input->post('sid');
            if(isset($sid) && $sid != '' && $this->is_valid_md5($sid)){
                $admin_reset = $this->Query_model->get_data_obj('ec_admin_password_reset',array('reset_key' => $sid, 'status' => 'active'));
                if($admin_reset){
                    $admin = $this->Query_model->get_data_obj('ec_admin',array('admin_id' => $admin_reset->admin_id, 'status' => '1'));
                    if($admin){
                        $data =  array(
                            'password'      => md5($confirm_password),
                        );
                        $this->Query_model->update_data('ec_admin',$data,array('admin_id' => $admin_reset->admin_id));

                        $data =  array(
                            'status'      => 'reset',
                        );
                        $this->Query_model->update_data('ec_admin_password_reset',$data,array('admin_id' => $admin_reset->admin_id));

                        $details = array(
                                'email'         => $admin->email,
                                'fname'         => $admin->fname,
                                'login_id'      => $admin->admin_id,
                                'logged_in'     => TRUE
                                );
                        $this->session->set_userdata($this->TYPE, $details);

                        redirect("$this->TYPE/dashboard");
                    }else{
                        $data['message']['error']='Oops, that link has expired. Please enter your email below to start again.';
                    }
                }else{
                    $data['message']['error']='Invalid Parameter!!.';
                }
            }else{
                $this->session->set_flashdata('message','Invalid Parameter!');
            }
        }
	
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['sid']  = $sid;
		$this->load->template("$this->TYPE/auth/reset_password", $data);
	}

    public function logged_in()
    {
        if($this->session->userdata($this->TYPE) && isset($this->session->userdata($this->TYPE)['logged_in'])){
            return true;
        }else{
            return false;
        }
    }

    public function set_session($details) 
    {
        $this->session->set_userdata($details);
    }

    public function is_valid_md5($md5 ='')
    {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }
}
