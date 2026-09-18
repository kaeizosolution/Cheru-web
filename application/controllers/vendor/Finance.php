<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Finance extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Query_model');
		$this->TYPE = $this->session->userdata('type');
		$this->LOGIN_ID = isset($user_session['vendor_id']) ? $user_session['vendor_id'] : 0;
		$this->FNAME = $this->session->userdata($this->TYPE)['fname'];
					
	}	

	public function index()
	{
		$data = array();
		$data['TYPE'] = $this->TYPE;	
		$this->load->template("$this->TYPE/product/index_finance",$data);
	}	
}
