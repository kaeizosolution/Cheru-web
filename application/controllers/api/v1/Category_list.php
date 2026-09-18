<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Category_list extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
    }

    public function index()
    {
        if (!$this->require_post()) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $category_id = isset($payload['category_id']) ? (int)$payload['category_id'] : 0;

        $this->db->select('id, parent_id, name, slug, thumbnail, banner_image, icon, status');
        $this->db->from('ec_categories_prod');
        $this->db->where('status', '1');
        if ($category_id === 0) {
            $this->db->group_start();
                $this->db->where('parent_id', 0);
                $this->db->or_where('parent_id IS NULL', null, false);
            $this->db->group_end();
        } else {
            $this->db->where('parent_id', $category_id);
        }
        $this->db->order_by('name', 'ASC');
        $categories = $this->db->get()->result_array();

        if ($categories) {
            foreach ($categories as &$c) {
                if ($c['thumbnail']) {
                    $c['thumbnail_url'] = base_url('assets/categories/' . $c['thumbnail']);
                } else {
                    $c['thumbnail_url'] = base_url('assets/default_images/product.jpg');
                }
                if ($c['banner_image']) {
                    $c['banner_image_url'] = base_url('assets/categories/' . $c['banner_image']);
                } else {
                    $c['banner_image_url'] = null;
                }
            }
        } else {
            $categories = [];
        }

        return $this->ok(['categories' => $categories], 'success');
    }
}
