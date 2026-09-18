<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Db_info extends CI_Controller {
    public function index() {
        $fields = $this->db->list_fields('ec_shipping_address');
        echo json_encode($fields);
    }
}
