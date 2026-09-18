<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Best_sellers extends API_Controller
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

        $limit = ($limit_raw !== null && $limit_raw !== '') ? (int)$limit_raw : 20;
        if ($limit <= 0) {
            $limit = 20;
        }
        if ($limit > 50) {
            $limit = 50;
        }

        $out = [];
        try {
            $since = date('Y-m-d H:i:s', strtotime('-30 days'));
            $top = $this->db
                ->select('oi.product_id, SUM(oi.qty) AS total_qty', false)
                ->from('ec_order_items oi')
                ->join('ec_orders o', 'o.id = oi.order_id', 'inner')
                ->where('o.date_added >=', $since)
                ->where('o.status !=', 'cancelled')
                ->group_by('oi.product_id')
                ->order_by('total_qty', 'DESC')
                ->limit($limit)
                ->get()->result();

            $product_ids = [];
            $qty_map = [];
            if (is_array($top)) {
                foreach ($top as $t) {
                    $pid = (int)($t->product_id ?? 0);
                    if ($pid <= 0) {
                        continue;
                    }
                    $product_ids[] = $pid;
                    $qty_map[$pid] = (int)($t->total_qty ?? 0);
                }
            }

            $product_ids = array_values(array_unique($product_ids));
            if ($product_ids) {
                $rows = $this->db
                    ->select('p.id, p.name, p.status, p.vendor_id, p.product_type')
                    ->select('COALESCE(rt.avg_rating, 0) AS avg_rating, COALESCE(rt.review_count, 0) AS review_count', false)
                    ->from('products p')
                    ->join('(SELECT product_id, AVG(rating) AS avg_rating, COUNT(review_rating_id) AS review_count FROM ec_review_rating WHERE status = \'1\' GROUP BY product_id) rt', 'rt.product_id = p.id', 'left', false)
                    ->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left')
                    ->where_in('p.id', $product_ids)
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
                    ->get()->result();

                $by_id = [];
                if (is_array($rows)) {
                    foreach ($rows as $r) {
                        if (!is_object($r)) {
                            continue;
                        }
                        $by_id[(int)$r->id] = $r;
                    }
                }

                foreach ($product_ids as $pid) {
                    if (!isset($by_id[$pid])) {
                        continue;
                    }

                    $p = $by_id[$pid];

                    $image_url = base_url('assets/default_images/product.jpg');
                    $img = $this->db
                        ->select('product_variation_images.image_path')
                        ->from('product_variations')
                        ->join('product_variation_images', 'product_variation_images.variation_id = product_variations.id')
                        ->where('product_variations.product_id', (int)$pid)
                        ->order_by('product_variation_images.id', 'ASC')
                        ->limit(1)
                        ->get()->row();
                    if ($img && !empty($img->image_path)) {
                        $image_url = base_url('uploads/products/' . (string)$img->image_path);
                    }

                    $min_price = 0;
                    $cur_rate_bs = 1.0;
                    if (function_exists('current_currency') && function_exists('get_currency')) {
                        $cc_bs = current_currency();
                        $cur_bs = $cc_bs ? get_currency($cc_bs) : null;
                        if ($cur_bs && isset($cur_bs->rate) && is_numeric($cur_bs->rate)) {
                            $cur_rate_bs = (float)$cur_bs->rate;
                        }
                    }
                    // Get the 1-unit retail price:
                    // Priority: min_qty=1 > lowest positive min_qty > NULL (no tier set)
                    $unit_price_row = $this->db
                        ->select('pvp.price')
                        ->from('product_variation_price pvp')
                        ->join('product_variations pv', 'pv.id = pvp.variation_id')
                        ->where('pv.product_id', (int)$pid)
                        ->order_by('CASE WHEN pvp.min_qty IS NULL THEN 2 WHEN pvp.min_qty <= 1 THEN 0 ELSE 1 END, pvp.min_qty ASC', '', false)
                        ->limit(1)
                        ->get()->row();
                    if ($unit_price_row && isset($unit_price_row->price)) {
                        $min_price = (float)$unit_price_row->price * $cur_rate_bs;
                    }

                    $out[] = [
                        'id' => (int)$pid,
                        'name' => isset($p->name) ? (string)$p->name : '',
                        'vendor_id' => isset($p->vendor_id) ? (int)$p->vendor_id : null,
                        'product_type' => isset($p->product_type) ? (string)$p->product_type : 'simple',
                        'image_url' => $image_url,
                        'prices' => [
                            [
                                'regular_price' => $min_price,
                                'sale_price' => $min_price,
                            ]
                        ],
                        'total_qty' => (int)($qty_map[$pid] ?? 0),
                        'url' => base_url('product/details/' . (int)$pid),
                        'rating' => isset($p->avg_rating) ? (float)$p->avg_rating : 0,
                        'review' => isset($p->review_count) ? (int)$p->review_count : 0,
                    ];
                }
            }
        } catch (Exception $e) {
            $out = [];
        }

        return $this->ok(['best_sellers' => $out], 'success');
    }
}
