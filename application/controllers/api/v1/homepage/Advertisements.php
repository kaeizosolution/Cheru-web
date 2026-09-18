<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Advertisements extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Advertisement_model');
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

        $limit = ($limit_raw !== null && $limit_raw !== '') ? (int)$limit_raw : 3;
        if ($limit <= 0) {
            $limit = 3;
        }
        if ($limit > 20) {
            $limit = 20;
        }

        $ads = [];
        try {
            if (method_exists($this->Advertisement_model, 'get_visible_ads')) {
                $ads = $this->Advertisement_model->get_visible_ads($limit);
            }
            if (!is_array($ads) || !$ads) {
                $all = $this->Advertisement_model->get_all_ads();
                $ads = is_array($all) ? array_slice($all, 0, $limit) : [];
            }
        } catch (Exception $e) {
            $ads = [];
        }

        $out = [];
        if (is_array($ads) && $ads) {
            foreach ($ads as $ad) {
                if (!is_object($ad)) {
                    continue;
                }

                $id = isset($ad->advertisement_id) ? (int)$ad->advertisement_id : (int)($ad->id ?? 0);
                $title = isset($ad->advertisement_name) ? (string)$ad->advertisement_name : (string)($ad->title ?? '');
                $tag = isset($ad->advertisement_tag) ? (string)$ad->advertisement_tag : (string)($ad->tag ?? '');
                $link = isset($ad->advertisement_link) ? (string)$ad->advertisement_link : (string)($ad->link ?? '');
                $img = isset($ad->advertisement_image) ? (string)$ad->advertisement_image : (string)($ad->image ?? '');

                $img_url = $img !== '' ? base_url('uploads/advertisements/' . ltrim($img, '/')) : base_url('assets/default_images/product.jpg');

                $out[] = [
                    'id' => $id,
                    'title' => $title,
                    'tag' => $tag,
                    'link' => $link,
                    'image' => $img !== '' ? $img : null,
                    'image_url' => $img_url,
                ];
            }
        }

        return $this->ok(['advertisements' => $out], 'success');
    }
}
