<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reviews extends MY_Controller {

	function __construct() 
	{
        parent::__construct();
        $this->load->model('Query_model');	
		$this->config->load('custom_config');
		$this->load->helper('api');
        $this->TYPE = 'vendor';
		$user_session = $this->session->userdata($this->TYPE);
		$this->LOGIN_ID = 0;
		if($user_session){
			if(isset($user_session['vendor_id'])){
				$this->LOGIN_ID = (int)$user_session['vendor_id'];
			}elseif(isset($user_session['login_id'])){
				$this->LOGIN_ID = (int)$user_session['login_id'];
			}
		}
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
		if (!is_array($sess) || empty($sess)) {
			$all_sess = $this->session->all_userdata();
			foreach ($all_sess as $key => $val) {
				if (is_array($val) && !empty($val['logged_in']) && (!empty($val['vendor_id']) || !empty($val['login_id']))) {
					$sess = $val;
					break;
				}
			}
		}

		if (is_array($sess) && !empty($sess['access_token'])) {
			return (string)$sess['access_token'];
		}
		if (!empty($_SESSION['token'])) {
			return (string)$_SESSION['token'];
		}

		if (is_array($sess) && (!empty($sess['vendor_id']) || !empty($sess['login_id']))) {
			$vendor_id = !empty($sess['login_id']) ? (int)$sess['login_id'] : (int)$sess['vendor_id'];
			$token = $this->_sign_token(['sub' => $vendor_id, 'type' => 'access', 'role' => 'vendor'], 86400);
			$sess['access_token'] = $token;
			$this->session->set_userdata('vendor', $sess);
			if (!empty($this->TYPE)) {
				$this->session->set_userdata($this->TYPE, $sess);
			}
			return $token;
		}
		return '';
	}

	public function index()
	{
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		
		$page_lang = get_page_language_data('admin_page_lang');
		$crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", "Product Reviews" => "");
		$breadcrumbs = $this->breadcrumbs->show($crumbs);
		$data['breadcrumbs'] = $breadcrumbs;

		$data['csrf'] = csrf_token(); 
		$data['TYPE'] = $this->TYPE;
		$data['page_count'] = page_count(); 

		$use_api = true; // force api-driven loading
		$access_token = $this->_get_vendor_access_token();
		$data['use_api'] = $use_api;
		$data['access_token'] = $access_token;

		$this->load->template("$this->TYPE/reviews/index",$data);       
	}
}
