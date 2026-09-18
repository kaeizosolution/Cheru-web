<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_model extends MY_Model{

	function __construct() {
		parent::__construct();
		$this->load->model('Query_model');
	}

	public function check_customer_login($login_id, $password) {
		$login_id = trim((string)$login_id);

		// 1. Find user by email OR mobile (allow login with either)
		$this->db->group_start();
		$this->db->where('email', $login_id);
		$this->db->or_where('mobile', $login_id);
		$this->db->group_end();
		$query = $this->db->get('ec_customer');

		if ($query->num_rows() != 1) {
			return 'no_user';
		}

		$user = $query->row();

		// 2. Verify password (bcrypt preferred; support legacy md5)
		$stored = (string)($user->password ?? '');
		if ($stored !== '' && password_verify($password, $stored)) {
			return $user;
		}

		// Legacy md5 support (some older modules still use md5 passwords)
		if ($stored !== '' && strlen($stored) === 32 && ctype_xdigit($stored)) {
			if (hash_equals($stored, md5((string)$password))) {
				// Upgrade hash to bcrypt on successful legacy login
				$new_hash = password_hash((string)$password, PASSWORD_BCRYPT);
				$this->db->where('customer_id', (int)($user->customer_id ?? 0))->update('ec_customer', ['password' => $new_hash]);
				$user->password = $new_hash;
				return $user;
			}
		}

		return 'wrong_password';
	}


	public function is_exist_login($login_id)
    {
        $this->slave->select('*');
        $this->slave->from('ec_customer');
        $this->slave->group_start();
        $this->slave->where('email', $login_id);
        $this->slave->or_where('mobile', $login_id);
        $this->slave->group_end();
        $this->slave->where('status', '1');
        $user = $this->slave->get()->row();
        return $user;
    }

	public function set_reset_token($email, $token, $expiry) {
        return $this->slave->where('email', $email)
            ->update('ec_customer', ['reset_token' => $token, 'reset_expiry' => $expiry]);
    }

	function transactional_mail_send($args)
    {
        $template_uid = $to = $name = $ver_link = $otp = '';
        $template_uid = $args['template_uid'];
        $to           = $args['to'];
        $name         = isset($args['name']) ? $args['name'] : '';

        $params       = []; $ret_params = [];

	if (!empty($args['func_name'])) {
    		$functionName = $args['func_name'];
		if (method_exists($this, $functionName)) {
        	$ret_params = $this->$functionName($args);
    		} 
	}

        $params = array_merge($params, $ret_params);
        $post_data =  array(
            'APP-ID'        => 'eLaW7kmT2yrK8CixHIJAjbuUdQpZq6PX',
            'SECRET-KEY'    => 'xJZpbW6FfSuiTvIK43zgOcds7Dm9PRew',
            'TEMPLATE_UID'  => $template_uid,
            'SENDER_UID'    => '6798a89b8b575',
            'TO'            => $to,
            //'BCC'           => 'dheerusingh59@gmail.com',
            'PARAMS'        => json_encode($params),
            );
        $ret_data = $this->curl_request($post_data);
        return $ret_data;
    }

    function curl_request($post_data)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://transact.theupgrade.in/api/data/');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);

        $data = json_decode($result);
        return $data;
    }

    function forgot_func($args)
    {
        $params = [];
        $params['LINK']   = $args['LINK'];

        return $params;
    }

    public function register_customer($data) {
    // 1. Hash the password before saving
    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['status'] = 1; // Set active by default or 0 for email verification

    // 2. Insert into the Master database (not slave)
    // Note: Use $this->db or your master connection here
    if ($this->db->insert('ec_customer', $data)) {
        return $this->db->insert_id();
    }
    return false;
}
	
	
}
