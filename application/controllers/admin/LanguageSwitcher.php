<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LanguageSwitcher extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function switchLang($lang_id = "") {
        // ID 3 = English
        // ID 12 = Spanish
        // If no ID is provided, default to 3 (English)
        $lang_id = ($lang_id != "") ? $lang_id : "3";
        
        // Save the ID in the session variable 'ln' (This is what your system uses)
        $this->session->set_userdata('ln', $lang_id);
        
        // Go back to the previous page
        redirect($_SERVER['HTTP_REFERER']);
    }
}
?>