<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
    }

    public function index() 
    {
        $data                       = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/index", $data);
    }
}
