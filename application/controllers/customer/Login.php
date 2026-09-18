<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('cart');
        $this->load->model('Query_model');
        $this->load->model('Login_model');
        $this->config->load('custom_config');
        $this->load->helper('api');
        $this->TYPE = $this->session->userdata('type');
    }

    /**
     * Display the Login View
     */
    function index() {
        if($this->logged_in()){ redirect(base_url()); }   
        
        // Handle redirection after login (e.g. from checkout)
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'checkout') !== false) {
            $this->session->set_userdata('auth_redirect_url', base_url('checkout'));
        }

        $data = [];
        $this->load->template("/customer/login", $data); 
    }

    /**
     * Display the Register View
     */
    function register() {
        if($this->logged_in()){ redirect(base_url()); }   
        $data = [];
        $this->load->template("/customer/register", $data); 
    }

    /**
     * Main AJAX Entry Point
     * Decides whether to run Signup or Signin logic
     */
   public function signin_signup() {
    $post_data = $this->input->post();
    $action = $post_data['action'] ?? ''; // Get the hidden action value

    if ($action === 'signup') {
        // This is now 100% guaranteed to run for the signup form
        $this->signup($post_data);
    } else {
        // This runs for the login form
        $this->signin($post_data);
    }
}

private function signup($post_data) {
    $full_name = isset($post_data['fname']) ? trim($post_data['fname']) : '';
    $mobile  = isset($post_data['mobile']) ? trim($post_data['mobile']) : '';
    $email  = isset($post_data['email']) ? trim($post_data['email']) : '';
    $password  = isset($post_data['password']) ? $post_data['password'] : '';

	if ($full_name === '' || $mobile === '' || $email === '' || $password === '') {
		api_response([
			'status' => 0,
			'msg' => 'Name, mobile, email and password are required.',
			'data' => []
		]);
		return;
	}

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		api_response([
			'status' => 0,
			'msg' => 'Please enter a valid email address.',
			'data' => []
		]);
		return;
	}

	if (!preg_match('/^[0-9+]{8,15}$/', $mobile)) {
		api_response([
			'status' => 0,
			'msg' => 'Please enter a valid mobile number.',
			'data' => []
		]);
		return;
	}

    $api = api_call('POST', '/api/v1/auth/register', [
        'fname' => $full_name,
        'mobile' => $mobile,
        'email' => $email,
        'password' => $password,
    ]);

    // Fallback only when API is unreachable or returned non-JSON
    if ($api['response'] !== null) {
        $resp = $api['response'];
        if ((int)($resp['status'] ?? 0) === 1) {
            api_response([
                'status' => 2,
                'msg' => 'Account created! Please login',
                'data' => []
            ]);
            return;
        }

        $err = '';
        if (!empty($resp['errors']) && is_array($resp['errors'])) {
            $err = (string)$resp['errors'][0];
        }
        if ($err === '') {
            $err = (string)($resp['message'] ?? 'Registration failed');
        }

        api_response([
            'status' => 0,
            'msg' => $err,
            'data' => []
        ]);
        return;
    }
    
    // 2. Duplicate Check
    $exists = 0;
    if ($email !== '') {
        $exists += (int)$this->db->where('email', $email)->get('ec_customer')->num_rows();
    }
    if ($mobile !== '') {
        $exists += (int)$this->db->where('mobile', $mobile)->get('ec_customer')->num_rows();
    }

    if ($exists > 0) {
        api_response([
            'status' => 0, 
            'msg'    => 'This email or mobile is already registered.', 
            'data'   => []
        ]);
        return; 
    }

    // 3. Prepare Data for Database
    $insert_data = array(
        'customer_uid' => uniqid(),
        'fname'        => $full_name, 
        'password'     => password_hash($password, PASSWORD_BCRYPT),
        'status'       => '1',
        'role'         => 'customer',
        'date_added'   => date('Y-m-d H:i:s'),
        'ip_address'   => $this->input->ip_address()
    );

    // 4. Store both Email and Mobile
    $insert_data['email'] = $email !== '' ? $email : null;
    $insert_data['mobile'] = $mobile !== '' ? $mobile : null;

    $result = $this->Query_model->insert_data('ec_customer', $insert_data);
    
    if ($result) {
        api_response([
            'status' => 2, 
            'msg'    => 'Account created! Please login',
            'data'   => []
        ]);
    } else {
        api_response(['status' => 0, 'msg' => 'Error: Database rejected the data.', 'data' => []]);
    }
}

    private function signin($post_data) {
        $login_id = $post_data['email_mobile'] ?? '';
        $password = $post_data['password'] ?? '';

        $api = api_call('POST', '/api/v1/auth/login', [
            'email_mobile' => $login_id,
            'password' => $password,
        ]);

        // Fallback only when API is unreachable or returned non-JSON
        if ($api['response'] !== null) {
            $resp = $api['response'];
            if ((int)($resp['status'] ?? 0) === 1) {
                $data = $resp['data'] ?? [];
                $user = isset($data['user']) && is_array($data['user']) ? $data['user'] : [];

                $cust_id = (int)($user['customer_id'] ?? 0);
                // Cart and Buy Now products should be preserved during login
                $this->session->sess_regenerate(TRUE);
                $user_img = (string)($user['user_img'] ?? '');
                $profile_img = base_url('assets/images/default-user.png');
                if ($user_img !== '' && $user_img !== 'NULL') {
                    if (filter_var($user_img, FILTER_VALIDATE_URL) !== false || strpos($user_img, 'http://') === 0 || strpos($user_img, 'https://') === 0) {
                        $profile_img = $user_img;
                    } else {
                        if (file_exists('./assets/uploads/' . $user_img)) {
                            $profile_img = base_url('assets/uploads/' . $user_img);
                        } else {
                            $profile_img = base_url('uploads/profile_image/' . $user_img);
                        }
                    }
                }

                $session_data = array(
                    'name' => (string)($user['fname'] ?? ''),
                    'fname' => (string)($user['fname'] ?? ''),
                    'mobile' => (string)($user['mobile'] ?? ''),
                    'email' => (string)($user['email'] ?? ''),
                    'login_id' => $cust_id,
                    'logged_in' => TRUE,
                    'access_token' => (string)($data['access_token'] ?? ''),
                    'refresh_token' => (string)($data['refresh_token'] ?? ''),
                    'profile_img' => $profile_img,
                    'user_img' => $profile_img,
                );
                $this->session->set_userdata('customer', $session_data);

                // Restore the customer's saved preferred currency
                if ($cust_id > 0) {
                    $pref = $this->db->select('preferred_currency_id')->from('ec_customer')->where('customer_id', $cust_id)->get()->row();
                    if ($pref && !empty($pref->preferred_currency_id)) {
                        $this->session->set_userdata('cur', (int)$pref->preferred_currency_id);
                        $_SESSION['cur'] = (int)$pref->preferred_currency_id;
                    }
                }

                $redirect_url = $this->session->userdata('auth_redirect_url') ?: '/customer/profile';
                $this->session->unset_userdata('auth_redirect_url');

                api_response([
                    'status' => 3,
                    'msg' => 'Login successful! Redirecting...',
                    'data' => ['redirect_url' => $redirect_url]
                ]);
                return;
            }

            $err = '';
            if (!empty($resp['errors']) && is_array($resp['errors'])) {
                $err = (string)$resp['errors'][0];
            }
            if ($err === '') {
                $err = (string)($resp['message'] ?? 'Login failed');
            }

            api_response([
                'status' => 0,
                'msg' => $err,
                'data' => []
            ]);
            return;
        }

        // Local DB login (fast) + validation for email/mobile login method
        $login_info = $this->Login_model->check_customer_login($login_id, $password);

        if ($login_info === 'no_user') {
            $msg = 'No user found. Please create an account.';
            if (filter_var($login_id, FILTER_VALIDATE_EMAIL)) {
                $msg = 'No account found with this email. Try login with your mobile number or register.';
            } elseif (preg_match('/^[0-9+]{8,15}$/', $login_id)) {
                $msg = 'No account found for this mobile number. Try login with your email or register.';
            }

            api_response([
                'status' => 0,
                'msg'    => $msg,
                'data'   => []
            ]);
            return;
        }

        // Users can login using either email or mobile as long as it matches their account.

        if ($login_info === 'wrong_password') {
            api_response([
                'status' => 0,
                'msg'    => 'The password you entered is incorrect.',
                'data'   => []
            ]);
            return;
        }

        if (is_object($login_info)) {
            $this->session_create_exist_user($login_info);
            $redirect_url = $this->session->userdata('auth_redirect_url') ?: '/customer/profile';
            $this->session->unset_userdata('auth_redirect_url');

            api_response([
                'status' => 3,
                'msg'    => 'Login successful! Redirecting...',
                'data'   => ['redirect_url' => $redirect_url]
            ]);
            return;
        }

        api_response([
            'status' => 0,
            'msg'    => 'Login failed',
            'data'   => []
        ]);
    }


    /**
     * Session management for logged-in user
     */
    private function session_create_exist_user($args) {
    $cust_id = $args->customer_id ?? ($args->id ?? ($args->customer_uid ?? ''));
    
    $user_img = (string)($args->user_img ?? '');
    $profile_img = base_url('assets/images/default-user.png');
    if ($user_img !== '' && $user_img !== 'NULL') {
        if (filter_var($user_img, FILTER_VALIDATE_URL) !== false || strpos($user_img, 'http://') === 0 || strpos($user_img, 'https://') === 0) {
            $profile_img = $user_img;
        } else {
            if (file_exists('./assets/uploads/' . $user_img)) {
                $profile_img = base_url('assets/uploads/' . $user_img);
            } else {
                $profile_img = base_url('uploads/profile_image/' . $user_img);
            }
        }
    }
    // Cart and Buy Now products should be preserved during login
    $this->session->sess_regenerate(TRUE);
    $session_data = array(
        'name'        => $args->fname ?? '',
        'fname'       => $args->fname ?? '',
        'mobile'      => $args->mobile ?? '',
        'email'       => $args->email ?? '',
        'login_id'    => $cust_id,
        'profile_img' => $profile_img,
        'user_img'    => $profile_img,
        'logged_in'   => TRUE
    );
    $this->session->set_userdata('customer', $session_data);

    // Restore the customer's saved preferred currency
    $cust_id_int = (int)$cust_id;
    if ($cust_id_int > 0) {
        $pref = $this->db->select('preferred_currency_id')->from('ec_customer')->where('customer_id', $cust_id_int)->get()->row();
        if ($pref && !empty($pref->preferred_currency_id)) {
            $this->session->set_userdata('cur', (int)$pref->preferred_currency_id);
            $_SESSION['cur'] = (int)$pref->preferred_currency_id;
        }
    }
}

    /**
     * Check if user is logged in
     */
    public function logged_in() {
        return ($this->session->userdata('customer') && isset($this->session->userdata('customer')['logged_in']));
    }


    /**
     * Logout logic
     */
    function logout() {
		$customer = $this->session->userdata('customer');
		$refresh = (is_array($customer) && !empty($customer['refresh_token'])) ? (string)$customer['refresh_token'] : '';
		if ($refresh !== '') {
			api_call('POST', '/api/v1/auth/logout', [
				'refresh_token' => $refresh
			]);
		}

        $this->cart->destroy();
        $this->session->unset_userdata('cart');
        $this->session->unset_userdata('buy_now_product');
        $this->session->unset_userdata('customer');
        // Clear currency so the next customer never inherits a previous user's preference
        $this->session->unset_userdata('cur');
        unset($_SESSION['cur']);
        $this->session->sess_regenerate(TRUE);
        if(is_api()){
            api_response(array('status' => 1, 'msg' => 'Successfully logout', 'data' => array()));
        } else {
            redirect(base_url());         
        }
    }

    /**
     * Session-status ping used by the frontend auto-logout heartbeat.
     * Returns JSON: {"status":1} if logged in, {"status":0} if not.
     */
    public function check_session() {
        $customer = $this->session->userdata('customer');
        $cid = 0;
        if (is_array($customer)) {
            $cid = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
        }
        header('Content-Type: application/json');
        if ($cid > 0) {
            echo json_encode(['status' => 1, 'msg' => 'active']);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'session expired']);
        }
        exit;
    }
}