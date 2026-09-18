<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search_model extends MY_Model{

	var $column_search = array('post_title', 'modified', 'user_id','enabled');
    var $column_alias = array(); //set column field database

    function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    } 


		
	
	
}















