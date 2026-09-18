<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Categories extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Category_prod_model');
    }

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $parent_id_raw = $this->input->get('parent_id');
        $has_parent_filter = $parent_id_raw !== null && $parent_id_raw !== '';
        $parent_id = $has_parent_filter ? (int)$parent_id_raw : null;

        $this->db->select('c.id, c.parent_id, c.name, c.slug, c.thumbnail, c.banner_image, c.icon, c.status');
        $this->db->from('ec_categories_prod c');
        $this->db->where('c.status', '1');
        if ($has_parent_filter) {
            $this->db->where('c.parent_id', $parent_id);
        } else {
            $this->db->group_start();
            $this->db->where('c.parent_id', 0);
            $this->db->or_where('c.parent_id IS NULL', null, false);
            $this->db->group_end();
        }
        $this->db->order_by('c.name', 'ASC');
        $categories = $this->db->get()->result();

        $has_children_map = [];
        $cat_ids = [];
        if ($categories) {
            foreach ($categories as $c) {
                if (isset($c->id)) {
                    $cat_ids[] = (int)$c->id;
                }
            }
        }
        $cat_ids = array_values(array_unique(array_filter($cat_ids)));

        if ($cat_ids) {
            $rows = $this->db
                ->select('parent_id AS pid, COUNT(*) AS cnt', false)
                ->from('ec_categories_prod')
                ->where('status', '1')
                ->where_in('parent_id', $cat_ids)
                ->group_by('parent_id')
                ->get()->result();
            if ($rows) {
                foreach ($rows as $r) {
                    $pid = isset($r->pid) ? (int)$r->pid : 0;
                    $has_children_map[$pid] = isset($r->cnt) && (int)$r->cnt > 0;
                }
            }
        }

        $out = [];
        if ($categories) {
            foreach ($categories as $c) {
                $cid = isset($c->id) ? (int)$c->id : 0;
                $has_children = $cid > 0 ? (bool)($has_children_map[$cid] ?? false) : false;

                $out[] = [
                    'id' => $cid,
                    'parent_id' => isset($c->parent_id) ? (int)$c->parent_id : 0,
                    'name' => isset($c->name) ? (string)$c->name : '',
                    'slug' => isset($c->slug) ? (string)$c->slug : '',
                    'thumbnail' => isset($c->thumbnail) ? (string)$c->thumbnail : null,
                    'banner_image' => isset($c->banner_image) ? (string)$c->banner_image : null,
                    'icon' => isset($c->icon) ? (string)$c->icon : null,
                    'status' => isset($c->status) ? (string)$c->status : null,
                    'has_children' => $has_children,
                ];
            }
        }

        return $this->ok(['categories' => $out], 'success');
    }
}
