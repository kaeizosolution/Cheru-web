<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Smart_search extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function _as_str($v)
    {
        return trim((string)$v);
    }

    private function _get_input($key)
    {
        $method = strtoupper((string)$this->input->method());
        if ($method === 'POST') {
            $raw = (string)$this->input->raw_input_stream;
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && array_key_exists($key, $decoded)) {
                    return $decoded[$key];
                }
            }
            return $this->input->post($key);
        }
        return $this->input->get($key);
    }

    private function _resolve_header_product_image_url($product)
    {
        $placeholder = base_url('assets/default_images/product.jpg');
        $candidates_raw = [];

        if (is_object($product) && isset($product->images) && $product->images) {
            $imgs = json_decode((string)$product->images, true);
            if (is_array($imgs)) {
                foreach ($imgs as $img) {
                    if (is_string($img) && $img !== '') {
                        $candidates_raw[] = $img;
                    }
                }
            }
        }

        if (!$candidates_raw && is_object($product) && isset($product->prices) && $product->prices) {
            $prices_obj = json_decode((string)$product->prices);
            if (is_array($prices_obj) && isset($prices_obj[0]) && is_object($prices_obj[0]) && isset($prices_obj[0]->images) && is_array($prices_obj[0]->images)) {
                foreach ($prices_obj[0]->images as $img) {
                    if (is_string($img) && $img !== '') {
                        $candidates_raw[] = $img;
                    }
                }
            }
        }

        foreach ($candidates_raw as $raw) {
            $raw = (string)$raw;
            if (preg_match('#^https?://#i', $raw)) {
                return $raw;
            }

            $raw_rel = ltrim($raw, '/\\');
            if (@file_exists(FCPATH . $raw_rel)) {
                return base_url($raw_rel);
            }

            $fn = $raw_rel;
            $try_fns = [$fn];
            if (strpos($fn, '_thumb.') !== false) {
                $try_fns[] = preg_replace('/_thumb(\.[a-z0-9]+)$/i', '$1', $fn);
            }
            foreach ($try_fns as $tfn) {
                $paths = [
                    ['url' => base_url() . 'uploads/products/' . $tfn, 'path' => FCPATH . 'uploads/products/' . $tfn],
                    ['url' => base_url() . 'assets/uploads/files/' . $tfn, 'path' => FCPATH . 'assets/uploads/files/' . $tfn],
                    ['url' => base_url() . 'assets/uploads/' . $tfn, 'path' => FCPATH . 'assets/uploads/' . $tfn],
                ];
                foreach ($paths as $p) {
                    if (isset($p['path']) && @file_exists($p['path'])) {
                        return $p['url'];
                    }
                }
            }

            if (strpos($fn, 'uploads/') === 0 || strpos($fn, 'assets/') === 0) {
                return base_url($fn);
            }
        }

        return $placeholder;
    }

    private function _get_descendant_category_ids($root_id, $max_depth = 3)
    {
        $root_id = (int)$root_id;
        if ($root_id <= 0) {
            return [];
        }

        $all = [$root_id];
        $current = [$root_id];

        for ($d = 0; $d < $max_depth; $d++) {
            if (empty($current)) {
                break;
            }

            $children = $this->db
                ->select('id')
                ->from('ec_categories_prod')
                ->where('status', '1')
                ->where_in('parent_id', $current)
                ->get()->result_array();

            $next = [];
            foreach ($children as $r) {
                $cid = (int)($r['id'] ?? 0);
                if ($cid > 0 && !in_array($cid, $all, true)) {
                    $all[] = $cid;
                    $next[] = $cid;
                }
            }

            $current = $next;
        }

        return $all;
    }

    public function index()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'GET' && $method !== 'POST') {
            return $this->fail('method_not_allowed', ['Only GET and POST are allowed'], 405);
        }

        $term = $this->_as_str($this->_get_input('term'));
        $category_slug = $this->_as_str($this->_get_input('category_slug'));

        if ($term === '' || mb_strlen($term) < 2) {
            return $this->ok(['categories' => [], 'products' => []], 'success');
        }

        $category_row = null;
        if ($category_slug !== '' && $category_slug !== 'AllCategory') {
            $category_id_input = is_numeric($category_slug) ? (int)$category_slug : 0;
            $category_row = $this->db
                ->select('id, name, slug')
                ->from('ec_categories_prod')
                ->group_start()
                    ->where('slug', $category_slug)
                    ->or_where('name', $category_slug)
                    ->or_where('id', $category_id_input)
                ->group_end()
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()->row();

            if (!$category_row || !isset($category_row->id)) {
                return $this->ok(['categories' => [], 'products' => [], 'message' => 'No product found in this category.'], 'success');
            }
        }

        $category_id = $category_row ? (int)$category_row->id : 0;
        $category_name = $category_row ? (string)$category_row->name : '';
        $category_ids = $category_id > 0 ? $this->_get_descendant_category_ids($category_id, 5) : [];

        $categories = [];
        if ($category_id <= 0) {
            $cat_q = $this->db
                ->select('id, name, slug, thumbnail')
                ->from('ec_categories_prod')
                ->group_start()
                    ->like('name', $term)
                    ->or_like('slug', $term)
                ->group_end()
                ->order_by('id', 'DESC')
                ->limit(5);

            $cat_rows = $cat_q->get()->result();
            if ($cat_rows) {
                foreach ($cat_rows as $c) {
                    $image_url = '';
                    if (isset($c->thumbnail) && $c->thumbnail) {
                        $image_url = base_url('assets/categories/' . (string)$c->thumbnail);
                    }
                    $categories[] = [
                        'id' => (int)$c->id,
                        'name' => (string)$c->name,
                        'slug' => (string)$c->slug,
                        'url' => base_url('products/category/' . (string)$c->slug),
                        'image_url' => $image_url,
                    ];
                }
            }
        }

        $products = [];
        $allowed = [];

        if (!empty($category_ids)) {
            $desc_rows = $this->db
                ->select('id, name, slug')
                ->from('ec_categories_prod')
                ->where_in('id', $category_ids)
                ->get()->result();

            if ($desc_rows) {
                foreach ($desc_rows as $r) {
                    if (isset($r->id)) {
                        $allowed[] = (string)((int)$r->id);
                    }
                    if (isset($r->slug) && (string)$r->slug !== '') {
                        $allowed[] = (string)$r->slug;
                    }
                    if (isset($r->name) && (string)$r->name !== '') {
                        $allowed[] = (string)$r->name;
                    }
                }
            }
            $allowed = array_values(array_unique(array_filter($allowed, function ($v) {
                return $v !== null && $v !== '';
            })));
        }

        // Products table only — active products from approved vendors only.
        $this->db
            ->select('products.id, products.name, products.product_type, products.status, products.category, products.sub_category, products.sub_sub_category, products.vendor_id')
            ->from('products')
            ->join('ec_vendor v_check', 'v_check.vendor_id = products.vendor_id', 'left')
            // Only active products
            ->group_start()
                ->where('products.status', 1)
                ->or_where('products.status', '1')
                ->or_where('products.status', 'active')
                ->or_where('products.status', 'Active')
            ->group_end()
            // Only approved vendor (or admin-owned products with vendor_id = 0 / NULL)
            ->group_start()
                ->where('products.vendor_id', 0)
                ->or_where('products.vendor_id IS NULL', null, false)
                ->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
            ->group_end()
            ->like('products.name', $term);

        if (!empty($allowed)) {
            $this->db
                ->group_start()
                    ->where_in('products.category', $allowed)
                    ->or_where_in('products.sub_category', $allowed)
                    ->or_where_in('products.sub_sub_category', $allowed)
                ->group_end();
        }

        $prod_rows = $this->db
            ->order_by('products.id', 'DESC')
            ->limit(8)
            ->get()->result();

        if ($prod_rows) {
            foreach ($prod_rows as $p) {
                $image_url = base_url('assets/default_images/product.jpg');
                $imgRow = $this->db
                    ->select('pvi.image_path')
                    ->from('product_variations pv')
                    ->join('product_variation_images pvi', 'pvi.variation_id = pv.id', 'inner')
                    ->where('pv.product_id', (int)$p->id)
                    ->order_by('pvi.id', 'asc')
                    ->limit(1)
                    ->get()->row();
                if ($imgRow && !empty($imgRow->image_path)) {
                    $image_url = base_url('uploads/products/' . (string)$imgRow->image_path);
                }

                $products[] = [
                    'id' => (int)$p->id,
                    'name' => (string)$p->name,
                    'vendor_id' => isset($p->vendor_id) ? (int)$p->vendor_id : null,
                    'url' => base_url('product/details/' . (int)$p->id),
                    'image_url' => $image_url,
                    'price' => '',
                ];
            }
        }

        return $this->ok([
            'categories' => $categories,
            'products'   => $products,
            'message'    => empty($products)
                ? ($category_id > 0 ? 'No product found in this category.' : 'No product found.')
                : '',
        ], 'success');
    }
}
