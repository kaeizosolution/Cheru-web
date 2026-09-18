<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Shop_by_category extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
    }

    public function index()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'GET' && $method !== 'POST') {
            return $this->fail('method_not_allowed', ['Only GET and POST are allowed'], 405);
        }

        $limit_raw = null;
        if ($method === 'POST') {
            $payload = [];
            $raw = (string)$this->input->raw_input_stream;
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }
            if (isset($payload['limit'])) {
                $limit_raw = $payload['limit'];
            } else {
                $limit_raw = $this->input->post('limit');
            }
        } else {
            $limit_raw = $this->input->get('limit');
        }

        $limit = ($limit_raw !== null && $limit_raw !== '') ? (int)$limit_raw : 12;
        if ($limit <= 0) {
            $limit = 12;
        }
        if ($limit > 50) {
            $limit = 50;
        }

        $this->db->select('c.id, c.parent_id, c.name, c.slug, c.thumbnail, c.banner_image, c.icon, c.status');
        $this->db->from('ec_categories_prod c');
        $this->db->where('c.status', '1');
        $this->db->group_start();
        $this->db->where('c.parent_id', 0);
        $this->db->or_where('c.parent_id IS NULL', null, false);
        $this->db->group_end();
        $this->db->order_by('c.name', 'ASC');
        $this->db->limit($limit);
        $categories = $this->db->get()->result();

        $out = [];
        if ($categories) {
            foreach ($categories as $c) {
                $thumb = isset($c->thumbnail) ? (string)$c->thumbnail : '';
                $thumb_url = $thumb !== '' ? base_url('assets/categories/' . $thumb) : base_url('assets/default_images/product.jpg');

                $slug = isset($c->slug) ? (string)$c->slug : '';
                $url = $slug !== '' ? base_url('products/category/' . $slug) : '#';

                $out[] = [
                    'id' => isset($c->id) ? (int)$c->id : 0,
                    'parent_id' => isset($c->parent_id) ? (int)$c->parent_id : 0,
                    'name' => isset($c->name) ? (string)$c->name : '',
                    'slug' => $slug,
                    'thumbnail' => $thumb !== '' ? $thumb : null,
                    'thumbnail_url' => $thumb_url,
                    'url' => $url,
                ];
            }
        }

        return $this->ok(['categories' => $out], 'success');
    }
}
