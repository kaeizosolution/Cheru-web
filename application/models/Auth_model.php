<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('Query_model');
    }

    public function check_customer_login($login_id, $password) {
        // 1. First, find the user by email or mobile
        $this->slave->select('*');
        $this->slave->from('ec_customer');
        $this->slave->group_start();
        $this->slave->where('email', $login_id);
        $this->slave->or_where('mobile', $login_id);
        $this->slave->group_end();
        
        $user = $this->slave->get()->row();

        // Scenario 2: User doesn't exist at all
        if (!$user) {
            return 'no_user'; 
        }

        // Scenario 3: User exists, but check if password matches
        // Note: Using password_verify is essential for security
        if (!password_verify($password, $user->password)) {
            return 'wrong_password';
        }

        // Check if account is inactive
        if ($user->status != '1') {
            return 'inactive';
        }

        // Scenario 1: Everything matches
        return $user;
    }

    public function is_exist_login($login_id) {
        $this->slave->select('customer_id');
        $this->slave->from('ec_customer');
        $this->slave->group_start();
        $this->slave->where('email', $login_id);
        $this->slave->or_where('mobile', $login_id);
        $this->slave->group_end();
        return $this->slave->get()->row();
    }

    public function is_exist_email_or_mobile($email, $mobile)
    {
        $email = trim((string)$email);
        $mobile = trim((string)$mobile);
        $this->slave->select('customer_id');
        $this->slave->from('ec_customer');
        $this->slave->group_start();
        if ($email !== '') {
            $this->slave->where('email', $email);
        }
        if ($mobile !== '') {
            $this->slave->or_where('mobile', $mobile);
        }
        $this->slave->group_end();
        return $this->slave->get()->row();
    }
}