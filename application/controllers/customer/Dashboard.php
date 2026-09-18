<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->helper('text');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

    public function index() 
    {
        redirect("$this->TYPE/product");
        $data                = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/index", $data);
    }
}
