<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Banners extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
    }

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $type_raw = $this->input->get('type');
        $type = ($type_raw !== null && $type_raw !== '') ? (int)$type_raw : 1;

        $this->db->select('banner_id, title, type, banner_image, url, tag, description, enabled, created, modified');
        $this->db->from('ec_banner');
        $this->db->where('enabled', '1');
        $this->db->where('type', $type);
        $this->db->order_by('banner_id', 'DESC');
        $rows = $this->db->get()->result();

        $out = [];
        if ($rows) {
            foreach ($rows as $r) {
                $img = null;
                $thumb = null;
                $large = null;

                if (isset($r->banner_image) && $r->banner_image !== null && $r->banner_image !== '') {
                    $val = (string)$r->banner_image;
                    $un = @unserialize($val);
                    if (is_array($un)) {
                        $thumb = isset($un['thumb']) ? (string)$un['thumb'] : null;
                        $large = isset($un['large']) ? (string)$un['large'] : null;
                    } else {
                        // Fallback: plain filename
                        $thumb = $val;
                        $large = $val;
                    }
                }

                if ($large) {
                    $img = base_url('assets/home_page_banner/' . $large);
                } elseif ($thumb) {
                    $img = base_url('assets/home_page_banner/' . $thumb);
                }

                $out[] = [
                    'id' => isset($r->banner_id) ? (int)$r->banner_id : 0,
                    'type' => isset($r->type) ? (int)$r->type : $type,
                    'title' => isset($r->title) ? (string)$r->title : '',
                    'tag' => isset($r->tag) ? (string)$r->tag : '',
                    'description' => isset($r->description) ? (string)$r->description : '',
                    'url' => isset($r->url) ? (string)$r->url : '',
                    'image_url' => $img,
                    'image_thumb_url' => $thumb ? base_url('assets/home_page_banner/' . $thumb) : null,
                    'image_large_url' => $large ? base_url('assets/home_page_banner/' . $large) : null,
                ];
            }
        }

        return $this->ok(['banners' => $out], 'success');
    }
}
