<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Products extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
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

        $default_limit = 0;
        try {
            $w = $this->db
                ->select('active_limit')
                ->from('ec_homepage_widget_settings')
                ->where('widget_id', 2)
                ->limit(1)
                ->get()->row();
            if ($w && isset($w->active_limit)) {
                $default_limit = (int)$w->active_limit;
            }
        } catch (Exception $e) {
        }
        if ($default_limit <= 0) {
            try {
                $cfg_limit = (int)$this->config->item('HOMEPAGE_TRENDING_PRODUCTS_LIMIT');
                if ($cfg_limit > 0) {
                    $default_limit = $cfg_limit;
                }
            } catch (Exception $e) {
            }
        }
        if ($default_limit <= 0) {
            $default_limit = 20;
        }
        if ($default_limit > 50) {
            $default_limit = 50;
        }

        $limit = ($limit_raw !== null && $limit_raw !== '') ? (int)$limit_raw : $default_limit;
        if ($limit <= 0) {
            $limit = $default_limit;
        }
        if ($limit > 50) {
            $limit = 50;
        }

        $out = [];
        $cur_rate_hp = 1.0;
        if (function_exists('current_currency') && function_exists('get_currency')) {
            $cc_hp = current_currency();
            $cur_hp = $cc_hp ? get_currency($cc_hp) : null;
            if ($cur_hp && isset($cur_hp->rate) && is_numeric($cur_hp->rate)) {
                $cur_rate_hp = (float)$cur_hp->rate;
            }
        }
        try {
            $rows = $this->db
                ->select('p.id, p.name, p.vendor_id, p.product_type')
                ->from('products p')
                ->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left')
                ->where('p.status', '1')
                ->group_start()
                    ->where('p.vendor_id', 0)
                    ->or_where('p.vendor_id IS NULL', null, false)
                    ->or_group_start()
                        ->where('v_check.status', 'approved')
                        ->or_where('v_check.status', 'Approved')
                        ->or_where('v_check.status', '1')
                        ->or_where('v_check.status', 1)
                    ->group_end()
                ->group_end()
                ->order_by('RAND()', '', false)
                ->limit($limit)
                ->get()->result();

            if (is_array($rows) && $rows) {
                foreach ($rows as $p) {
                    if (!is_object($p) || empty($p->id)) {
                        continue;
                    }

                    $pid = (int)$p->id;

                    $image_url = base_url('assets/default_images/product.jpg');
                    $img = $this->db
                        ->select('product_variation_images.image_path')
                        ->from('product_variations')
                        ->join('product_variation_images', 'product_variation_images.variation_id = product_variations.id')
                        ->where('product_variations.product_id', $pid)
                        ->order_by('product_variation_images.id', 'ASC')
                        ->limit(1)
                        ->get()->row();
                    if ($img && !empty($img->image_path)) {
                        $image_url = base_url('uploads/products/' . (string)$img->image_path);
                    }

                    $min_price = 0;
                    $minp = $this->db
                        ->select('pvp.price')
                        ->from('product_variation_price pvp')
                        ->join('product_variations pv', 'pv.id = pvp.variation_id')
                        ->where('pv.product_id', $pid)
                        ->order_by('COALESCE(pvp.min_qty, 0)', 'ASC', false)
                        ->limit(1)
                        ->get()->row();
                    if ($minp && isset($minp->price)) {
                        $min_price = (float)$minp->price * $cur_rate_hp;
                    }

                    $avg_rating = 0;
                    $review_count = 0;
                    $rev = $this->db
                        ->select('AVG(rating) as avg_rating, COUNT(review_rating_id) as review_count')
                        ->from('ec_review_rating')
                        ->where('product_id', $pid)
                        ->where('status', '1')
                        ->get()->row();
                    if ($rev) {
                        $avg_rating = isset($rev->avg_rating) ? (float)$rev->avg_rating : 0;
                        $review_count = isset($rev->review_count) ? (int)$rev->review_count : 0;
                    }

                    $out[] = [
                        'id' => $pid,
                        'name' => isset($p->name) ? (string)$p->name : '',
                        'vendor_id' => isset($p->vendor_id) ? (int)$p->vendor_id : null,
                        'product_type' => isset($p->product_type) ? (string)$p->product_type : 'simple',
                        'image_url' => $image_url,
                        'display_price' => $min_price,
                        'prices' => [
                            [
                                'regular_price' => $min_price,
                                'sale_price' => $min_price,
                            ]
                        ],
                        'rating' => $avg_rating,
                        'review' => $review_count,
                        'url' => base_url('product/detail/' . $pid),
                    ];
                }
            }
        } catch (Exception $e) {
            $out = [];
        }

        return $this->ok(['products' => $out], 'success');
    }
}
