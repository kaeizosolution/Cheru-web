<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Forgot_password extends MY_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model(['Query_model', 'Login_model']);	
		$this->load->helper('string');
	}

	function index()
	{
		if($this->logged_in()){redirect(base_url());}
		$data = [];
		$this->load->template("/customer/forgot_password",$data);
	
	}

	public function logged_in()
    {
        if($this->session->userdata('customer') && isset($this->session->userdata('customer')['logged_in'])){
            return true;
        }else{
            return false;
        }
    }

	public function send_link() {
        $post_data = $this->input->post();

        if (empty($post_data['email'])) {
            return api_response(['status' => 0, 'msg' => 'Email is required', 'data' => []]);
        }

        $use_api = (bool)$this->config->item('use_api');
        if ($use_api) {
            $api = api_call('POST', '/api/v1/auth/forgot-password', ['email' => $post_data['email']]);
            if ($api['response'] !== null) {
                $resp = $api['response'];
                if ((int)($resp['status'] ?? 0) === 1) {
                    return api_response(['status' => 1, 'msg' => 'Password reset link sent to your email', 'data' => []]);
                }

                $err = '';
                if (!empty($resp['errors']) && is_array($resp['errors'])) {
                    $err = (string)$resp['errors'][0];
                }
                if ($err === '') {
                    $err = (string)($resp['message'] ?? 'Email not found');
                }
                return api_response(['status' => 0, 'msg' => $err, 'data' => []]);
            }
        }

        $user = $this->Query_model->get_data_obj('ec_customer', ['email'=>$post_data['email']]);
        if (!$user) {
            return api_response(['status' => 0, 'msg' => 'Email not found', 'data'=>[]]);
        }

        $token = random_string('alnum', 50);
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $this->Login_model->set_reset_token($post_data['email'], $token, $expiry);

        $reset_link = base_url("customer/forgot-password/reset-password-form?token=" . $token);

       	$mail_status = $this->Login_model->transactional_mail_send(['template_uid'=>'679b255869791', 'to'=>$user->email, 'LINK'=>$reset_link, 'func_name'=>'forgot_func']);

        if($mail_status->status == 1) 
		return api_response(['status' => 1, 'msg' => 'Password reset link sent to your email', 'data'=>[]]);
		else
		return api_response(['status' => 0, 'msg' => 'Something went wrong', 'data'=>[]]);

    }

	function reset_password_form(){
		if($this->logged_in()){redirect(base_url());}
        $data = [];
		$data['token'] = $_GET['token'];
        $this->load->template("/customer/reset_password",$data);
	
	}

    public function reset_password() {
        
            $post_data = $this->input->post();

        if (empty($post_data['token']) || empty($post_data['new_password'])) {
            return api_response(['status' => 0, 'msg' => 'Token and new password are required', 'data'=>[]]);
        }

		$user = $this->Query_model->get_data_obj('ec_customer', ['reset_token'=>$post_data['token']]);
        if (!$user || strtotime($user->reset_expiry) < time()) {
            return api_response(['status' => 0, 'msg' => 'Invalid or expired token', 'data'=>[]]);
        }

		
        // Use password_hash (not md5) to keep compatibility with API login which uses password_verify()
        $this->Query_model->update_data('ec_customer', [
            'password'     => password_hash($post_data['new_password'], PASSWORD_BCRYPT),
            'reset_token'  => null,
            'reset_expiry' => null,
        ], ['customer_id' => $user->customer_id]);

        return api_response(['status' => 1, 'msg' => 'Password updated successfully', 'data'=>[]]);
    }		

}
