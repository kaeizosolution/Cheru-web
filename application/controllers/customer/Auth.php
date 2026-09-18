<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Auth_model'); // Ensure this is uncommented
        $this->TYPE = 'customer';
    }

    /**
     * Unified Handler for Scenario 1, 2, and 3 (Login)
     * and Scenario 4 (Registration)
     */
    public function signin_signup() {
        $post_data = $this->input->post();
        $login_id  = $this->input->post('email_mobile');
        $password  = $this->input->post('password');

        // Check if this is a Registration attempt (if full_name is present)
        if (!empty($post_data['full_name'])) {
            $this->handle_registration($post_data);
        } else {
            $this->handle_login($login_id, $password);
        }
    }

    private function handle_login($login_id, $password) {
        // Call the updated Auth_model we discussed
        $auth_result = $this->Auth_model->check_customer_login($login_id, $password);

        if ($auth_result === 'no_user') {
            // SCENARIO 2: User not found
            api_response(['status' => 0, 'msg' => 'No user found. Please create an account.']);
        } 
        elseif ($auth_result === 'wrong_password') {
            // SCENARIO 3: Incorrect password
            api_response(['status' => 0, 'msg' => 'Invalid password. Please try again.']);
        } 
        elseif (is_object($auth_result)) {
            // SCENARIO 1: Success
            $this->session->unset_userdata('cart');
            $this->session->unset_userdata('buy_now_product');
            $this->session->sess_regenerate(TRUE);
            $session_data = [
                'login_id'   => $auth_result->customer_id,
                'email'      => $auth_result->email,
                'full_name'  => $auth_result->full_name,
                'logged_in'  => TRUE
            ];
            $this->session->set_userdata('customer', $session_data);
            
            api_response(['status' => 3, 'msg' => 'Login successful! Redirecting...']);
        } else {
            api_response(['status' => 0, 'msg' => 'Account is inactive. Please contact support.']);
        }
    }

    private function handle_registration($post_data) {
        // Check if user already exists
        $exists = $this->Auth_model->is_exist_login($post_data['email_mobile']);
        if ($exists) {
            api_response(['status' => 0, 'msg' => 'Account already exists with this Email/Mobile.']);
            return;
        }

        // SCENARIO 4: Create account and redirect to login
        $customer_uid = uniq_uid();
        $full_name = isset($post_data['full_name']) ? trim((string)$post_data['full_name']) : '';
        $insert_data = [
            'customer_uid' => $customer_uid,
            'fname'        => $full_name,
            'lname'        => '',
            'password'     => password_hash((string)($post_data['password'] ?? ''), PASSWORD_BCRYPT),
            'status'       => '1',
            'role'         => 'customer',
            'date_added'   => date('Y-m-d H:i:s'),
            'ip_address'   => $this->input->ip_address(),
        ];

        // Assign email or mobile based on input format
        if (filter_var($post_data['email_mobile'], FILTER_VALIDATE_EMAIL)) {
            $insert_data['email'] = $post_data['email_mobile'];
        } else {
            $insert_data['mobile'] = $post_data['email_mobile'];
        }

        $insert_id = $this->Query_model->insert_data('ec_customer', $insert_data);

        if ($insert_id) {
            api_response([
                'status' => 2, 
                'msg'    => 'Account created successfully! Please sign in to your new account.'
            ]);
        } else {
            api_response(['status' => 0, 'msg' => 'Registration failed. Please try again.']);
        }
    }

    public function logout() {
        $this->load->library('cart');
        $this->cart->destroy();
        $this->session->unset_userdata('cart');
        $this->session->unset_userdata('buy_now_product');
        $this->session->unset_userdata('customer');
        $this->session->sess_regenerate(TRUE);
        redirect(base_url());
    }
}