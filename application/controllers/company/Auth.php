<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Auth_model');
        $this->TYPE = $this->session->userdata('type');
        $this->load->helper('cookie');
	}
	
	public function index()
    {
		if(!$this->logged_in()) redirect("$this->TYPE/auth/login");
		 
		// Redirect to your logged in landing page here
		redirect("$this->TYPE/dashboard");
	}
	
	/**
	 * Login page
	*/
	public function login()
    {
		// Redirect to your logged in landing page here
        if(is_api() && $this->logged_in()){ $this->auth_api(array('status' => 1, 'msg' => 'Already Login.', 'data' => array()));}
		if($this->logged_in()) redirect("$this->TYPE/dashboard");
		$this->load->library('form_validation');
		$data['error'] = false;
		$data['message']['error'] = ''; 

		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');
        $email       = $this->input->post('email');
        $password    = $this->input->post('password');
        if($this->form_validation->run()){
            $password = md5($password);
            $company = $this->Auth_model->get_company($email,$password);
            if($company){
                $role_str = '';
                if($company->parent){
                    $role_obj = $this->Auth_model->get_data('li_role', array('type' => $this->TYPE, 'status' => '1'));
                    $role_array = explode(',',$company->role_id);
                    $role_aarray = array();
                    foreach($role_array as $row){
                        $role_aarray[$row] = $row;
                    }
                    if($role_obj){
                        $rhash = array();
                        foreach($role_obj as $ro){
                            if(isset($role_aarray[$ro->role_id])){
                                $rhash[] = $ro->slug;
                            }
                        }
                        if($rhash){
                            $role_str = implode('|',$rhash);
                        }
                    }
                }
                $name = $company->cname;
                $details = array(
                               'email'         => $company->email,
                               'parent'        => $company->parent,
                               'role'          => $role_str,
                               'fname'         => $name,
                               'image'         => $company->image,
                               'login_id'      => $company->company_id,
                               'logged_in'     => TRUE
                        );
                $this->session->set_userdata($this->TYPE, $details);
                if(is_api()){ $this->auth_api(array('status' => 1, 'msg' => 'Success.', 'data' => $details)); }
                set_cookie('type',$this->TYPE,'3600');
                redirect("$this->TYPE/dashboard");
/*
                $previous_url = $this->session->userdata('previous_url');
                $base_url = base_url();
                if($base_url == $previous_url) $previous_url = '';
                if($previous_url){
                    redirect("$previous_url");
                }else{
                    redirect("$this->TYPE/dashboard");
                }
*/
            }else{
                if(is_api()){ $this->auth_api(array('status' => 0, 'msg' => 'Your email address and/or password is incorrect.', 'data' => array())); }
                $data['message']['error']='Your email address and/or password is incorrect.';
            }
		}
        else
        {
            if(is_api()){ $this->auth_api(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array())); }
        }
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/login",$data);
	}

	/**
	 * Logout page
	 */
	public function logout()
	{
        $this->session->unset_userdata($this->TYPE); 
        delete_cookie('type');
        if(is_api()) {$this->auth_api(array('section' => 'signout', 'status' => 1, 'msg' => 'Successully Logout', 'data' => 'undefined'));}
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

        $response = array('status' => 'success', 'message' => 'success');
 
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $email = $this->input->post('email');
        if($this->form_validation->run()){
            $company       = $this->Auth_model->get_company_by_email($email);
            if($company){
                $slug       = md5($this->Auth_model->random_password());
                $base_url   = $this->config->item('base_url');
                $site_url   = $base_url."/".$this->TYPE."/auth/reset/".$slug;
       
                $data =  array(
                        'company_id'    => $company->company_id,
                        'reset_key'     => $slug,
                        'ip_address'    => $_SERVER['REMOTE_ADDR'],
                        );
                $last_inserted_id    =  $this->Auth_model->set_company_password_reset('li_company_password_reset',$data);
                $email = $company->email;
                $this->load->library('mailer');
                $ret_status = $this->mailer->smtp(array('SUBJECT' => 'Reset your Password', 'EMAIL' => $email, 'CONTENT' => $this->forgot_mailer_content($site_url))); 
                if (!$ret_status) {
                    $response = array('status' => 'fail', 'message' => 'Failed to send password, please try again!');    
                    $this->session->set_flashdata('message','Failed to send password, please try again!');
                } else {
                    $response = array('status' => 'success', 'message' => 'Password sent to your email!');
                    $this->session->set_flashdata('message','Password sent to your email!');
                }
            }else{
                $response = array('status' => 'fail', 'message' => 'Your email address is incorrect.');
                $data['message']['error']='Your email address is incorrect.';
            }
        }
        else
        {
            $response = array('status' => 'fail', 'message' => remove_extra_for_api(validation_errors()));
        }

        if(is_api())
        {
            $msg = $response['message'];
            $status = $response['status'] == 'success'?1:0;
            $this->auth_api(array('status' => $status, 'msg' => $msg, 'data' => array()));
        }
        
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/forgot_password",$data);
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
        if(is_api() && $this->logged_in())
        {
            $this->auth_api(array('status' => 1, 'msg' => 'Already Login.', 'data' => array()));
        }
		if($this->logged_in()) redirect("$this->TYPE/dashboard");
        
		$this->load->library('form_validation');
		$data['error'] = false;
		$data['message']['error'] = '';

        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[50]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');

		if($this->form_validation->run()){
            if(isset($sid) && $sid != '' && $this->is_valid_md5($sid)){
                $company_reset = $this->Auth_model->get_company_reset_key($sid);
                if($company_reset){
                    $company = $this->Auth_model->get_company_by_id($company_reset->company_id);
                    if($company){
                        $role_str = '';
                        if($company->parent){
                            $role_obj = $this->Auth_model->get_data('li_role', array('type' => $this->TYPE, 'status' => '1'));
                            $role_array = explode(',',$company->role_id);
                            $role_aarray = array();
                            foreach($role_array as $row){
                                $role_aarray[$row] = $row;
                            }
                            if($role_obj){
                                foreach($role_obj as $ro){
                                    if(isset($role_aarray[$ro])){
                                        $rhash[] = $ro->slug;
                                    }
                                }
                                $role_str = implode('|',$rhash);
                            }
                        }
                        $data =  array(
                            'password'  => md5($confirm_password),
                        );
                        $this->Auth_model->set_company_password('li_company',$data,$company_reset->company_id);

                        $data =  array(
                            'status'      => 'reset',
                        );
                        $this->Auth_model->set_company_password_reset_status('li_company_password_reset',$data,$company_reset->company_id);

                        #$name = ($company->type == 'company') ? $company->cname : $company->fname;
                        $name = $company->cname;
                        $details = array(
                               'email'         => $company->email,
                               'parent'        => $company->parent,
                               'role'          => $role_str,
                               'fname'         => $name,
                               'image'         => $company->image,
                               'login_id'      => $company->company_id,
                               'logged_in'     => TRUE
                        );
                        $this->session->set_userdata($this->TYPE, $details);
                        if(is_api())
                        {
                            $this->auth_api(array('section' => 'reset', 'status' => 1, 'msg' => 'Reset Successfully.', 'data' => array()));
                        }

                        redirect("$this->TYPE/dashboard");
                    }else{
                        if(is_api())
                        {
                            $this->auth_api(array('section' => 'reset', 'status' => 0, 'msg' => 'Oops, that link has expired. Please enter your email below to start again.', 'data' => array()));
                        }
                        $data['message']['error']='Oops, that link has expired. Please enter your email below to start again.';
                    }
                }else{
                    if(is_api())
                    {
                        $this->auth_api(array('section' => 'reset', 'status' => 0, 'msg' => 'This link has been expired.', 'data' => array()));
                    }
                    $data['message']['error']='This link has been expired.';
                }
            }else{
                if(is_api())
                {
                    $this->auth_api(array('section' => 'reset', 'status' => 0, 'msg' => 'Invalid Parameter!', 'data' => array()));
                }
                $this->session->set_flashdata('message','Invalid Parameter!');
            }
        }
        else
        {
            if(is_api())
            {
                $this->auth_api(array('section' => 'reset', 'status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
            }
        }
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
		$this->load->template("$this->TYPE/reset_password", $data);
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

    public function register()
    {
        if($this->logged_in()) redirect("$this->TYPE/dashboard");
        $country_arr        =  get_country();
        $this->load->library('form_validation');

        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('cvr', 'CVR Number', 'trim|required|is_unique[li_company.cvr]');
        $this->form_validation->set_rules('cname', 'Company Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[li_company.email]');
        $this->form_validation->set_rules('mobile', 'Mobile No.', 'required|regex_match[/^[0-9]{8,10}$/]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]');
        #$this->form_validation->set_rules('city', 'City Name', 'trim|required');
        $this->form_validation->set_rules('street', 'Street Name', 'trim|required');
        //$this->form_validation->set_rules('floor', 'Floor Name', 'trim|required');
        #$this->form_validation->set_rules('pincode', 'Pincode Name', 'trim|required');
        $this->form_validation->set_rules('country_id', 'Country Name', 'trim|required');

        if($_POST) {
            $data =  array(
                    'cvr'      => $this->input->post('cvr'),
                    'cname'    => $this->input->post('cname'),
                    'fname'    => $this->input->post('fname'),
                    'lname'    => $this->input->post('lname'),
                    'email'    => $this->input->post('email'),
                    'mobile'   => $this->input->post('mobile'),
                    'street'   => $this->input->post('street'),
                    'pincode'  => $this->input->post('pincode'),
                    'country_id'  => $this->input->post('country_id'),
                );
        }

        if ($this->form_validation->run() == FALSE)
        {
            $data['country_arr']  =  $country_arr;

            $data['navigation'] = 0;
            $data['TYPE'] = $this->TYPE;
            $data['csrf'] = csrf_token();
            if(is_api())
            {
                $this->auth_api(array('section' => 'signup', 'status' => 0, 'data' => array(), 'msg' => remove_extra_for_api(validation_errors())));
            }
            $this->config->load('custom_config');
            $data['AUTOCOMPLETION_URL'] = $this->config->item('AUTOCOMPLETION_URL');
            $data['ROUTE_API_KEY']      = $this->config->item('ROUTE_API_KEY');
            $this->load->template("$this->TYPE/register",$data);
        }else{
            $data['company_uid']= uniq_uid();
            $data['password']   = md5($this->input->post('password'));
            $data['status']     = '0';

            $this->load->model('Route_model');
            $route_array['curr_address'] = (object) array('street' => $data['street']);
            $best_route = $this->Route_model->get_geolocation(array('route_array' => $route_array, 'pickup_hash' => array(), 'type' => 'other'));
            if($best_route){
                if($best_route['curr_address']->position){
                    $position = $best_route['curr_address']->position;
                    $data['latitude'] = $position['lat'];
                    $data['longitude'] = $position['lng'];
                }
            }
            $last_inserted_id   = $this->Auth_model->insert_data('li_company',$data);
            if(isset($last_inserted_id)){
                $useremail = $data['email'];
                if(isset($data['cname'])){
                    $username = $data['cname'];
                }else{
                    $username = $data['fname'];
                }
        
        $Uname = ucfirst($username);
        $msg =<<<HTML
            Hi, $Uname
        
            <div style="display:block;text-align:left;">Thank you, for Register with us</div>
HTML;

                $this->load->library('mailer');
                $this->mailer->smtp(array('SUBJECT' => 'Thank You For Register', 'EMAIL' => $useremail, 'CONTENT' => $msg));
                if(is_api())
                {
                    $this->auth_api(array('section' => 'signup', 'status' => '1', 'msg' => 'Company Registration Successfully.', 'data' => array()));
                }
                $this->session->set_flashdata('message', 'Company Registration Successfully.');
/*
                $details = array(
                               'email'         => $useremail,
                               'parent'        => 0,
                               'fname'         => $username,
                               'login_id'      => $last_inserted_id,
                               'logged_in'     => TRUE
                        );
                $this->session->set_userdata($this->TYPE, $details);
                set_cookie('type',$this->TYPE,'3600');
                redirect("$this->TYPE/dashboard");
*/
            }else{
                if(is_api())
                {
                    $this->auth_api(array('section' => 'signup', 'status' => '0', 'msg' => 'Your Email Address is Incorrect.', 'data' => array()));
                }

                $data['message']['error']='Your Email Address is Incorrect.';
            }
            redirect("$this->TYPE/auth/register");
        }  
    }

    public function auth_api($args)
    {
        $ret_data = array();
        $msg = gettype($args['msg']) == 'array' ? $args['msg'] : array($args['msg']);
        $data = gettype($args['data']) == 'array' ? $args['data'] : array($args['data']);
        if($args['status'])
        {
            $ret_data['STATUS'] = 1;
            $ret_data['MSG'] = $msg;
            $ret_data['DATA'] = array($args['data']);
        }
        else
        {
            $ret_data['STATUS'] = 0;
            $ret_data['MSG'] = $msg;
            $ret_data['DATA'] = $data;
        }


        header('Content-Type: application/json');
        echo json_encode($ret_data);
        die();
    }

    public function index_ajax_address()
    {
        $this->load->model('Here_address_api');
        $searchText = $this->input->post('searchTerm');
        $this->Here_address_api->ajax_address($searchText);
    }    
}
