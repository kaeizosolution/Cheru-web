<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LanguageSwitcher extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function switchLang($lang_id = "") {
        // Default to English (3) if no ID is sent
        $lang_id = ($lang_id != "") ? $lang_id : "3";
        
        // Save the ID in the session variable 'ln'
        // This works because both Admin and Customer share the same Session library
        $this->session->set_userdata('ln', $lang_id);
        
        // Redirect the user back to the exact page they were on
        redirect($_SERVER['HTTP_REFERER']);
    }
}
?>