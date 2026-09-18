<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Returns extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Return_model');
        $this->TYPE = 'vendor';
        
        $session_data = $this->session->userdata($this->TYPE);
		$this->VENDOR_ID = isset($session_data['login_id']) ? (int)$session_data['login_id'] : (isset($session_data['vendor_id']) ? (int)$session_data['vendor_id'] : 0);
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

	private function _get_vendor_access_token()
	{
		$sess = $this->session->userdata('vendor');
		if (is_array($sess) && !empty($sess['access_token'])) {
			return (string)$sess['access_token'];
		}
		if (!empty($_SESSION['token'])) {
			return (string)$_SESSION['token'];
		}
		
		// If no token is set but the vendor is logged in, mint a valid JWT token on the fly!
		if (is_array($sess) && (!empty($sess['vendor_id']) || !empty($sess['login_id']))) {
			$vendor_id = !empty($sess['login_id']) ? (int)$sess['login_id'] : (int)$sess['vendor_id'];
			$token = $this->_sign_token(['sub' => $vendor_id, 'type' => 'access', 'role' => 'vendor'], 86400);
			$sess['access_token'] = $token;
			$this->session->set_userdata('vendor', $sess);
			return $token;
		}
		return '';
	}

    public function index()
    {
        $use_api = true; // Force api-driven mode
        $access_token = $this->_get_vendor_access_token();

        $crumbs = array('Home' => "/$this->TYPE/dashboard", 'Returns' => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs'] = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['use_api'] = $use_api;
        $data['access_token'] = $access_token;
        
        $data['returns'] = $use_api ? array() : $this->Return_model->get_vendor_returns($this->VENDOR_ID);
        
        $this->load->template("$this->TYPE/returns/index", $data);
    }

    public function details($return_id = null)
    {
        $use_api = true; // force api-driven details

        if ($return_id === null) {
            $return_id = $this->input->post('return_id');
        }
        if (empty($return_id)) {
            $return_id = $this->session->userdata('last_viewed_return_id');
        }

        if (empty($return_id)) {
            redirect("$this->TYPE/returns");
        }

        // Store the active return_id in session so page refresh works perfectly
        $this->session->set_userdata('last_viewed_return_id', $return_id);

        $crumbs = array('Home' => "/$this->TYPE/dashboard", 'Returns' => "/$this->TYPE/returns", 'Return Details' => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs'] = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['use_api'] = $use_api;
        $data['access_token'] = $this->_get_vendor_access_token();
        $data['return_id'] = $return_id;

        // Fallback for static rendering when use_api is false
        $data['return_data'] = $use_api ? null : $this->Return_model->get_return_details($return_id, $this->VENDOR_ID);

        if (!$use_api && empty($data['return_data'])){
            redirect("$this->TYPE/returns");
        }

        $this->load->template("$this->TYPE/returns/details", $data);
    }

    public function action()
    {
        $this->session->set_flashdata('error', 'Vendors cannot approve/reject return requests. You can only add an inspection note.');
        redirect("$this->TYPE/returns/details");
    }

    public function add_note()
    {
        $return_id = $this->input->post('return_id');
        $note = $this->input->post('vendor_note');

        if(!empty($note)){
            $update_data = [
                'vendor_note' => $note,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->Return_model->update_return($return_id, $this->VENDOR_ID, $update_data);
            $this->session->set_flashdata('success', 'Note added successfully.');
        } else {
            $this->session->set_flashdata('error', 'Note cannot be empty.');
        }

        redirect("$this->TYPE/returns/details");
    }
}
