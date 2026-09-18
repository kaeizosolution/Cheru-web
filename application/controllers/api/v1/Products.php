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
        $this->load->model('Query_model');
        $this->load->model('Product_model');
        $this->load->library('session');
    }

	private function _require_customer()
	{
		$token = $this->get_bearer_token();
		if ($token === '') {
			$customer = $this->session->userdata('customer');
			$customer_id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
			if ($customer_id > 0) {
				return $customer_id;
			}
			$this->fail('unauthorized', ['Missing Authorization Bearer token'], 401);
			return 0;
		}

		$claims = $this->verify_token($token);
		if (!$claims || !isset($claims['sub']) || ($claims['type'] ?? '') !== 'access' || ($claims['role'] ?? '') !== 'customer') {
			$this->fail('unauthorized', ['Invalid or expired access token'], 401);
			return 0;
		}

		$customer_id = (int)$claims['sub'];
		if ($customer_id <= 0) {
			$this->fail('unauthorized', ['Invalid token subject'], 401);
			return 0;
		}

		// Merge into session instead of overwriting
		$cust = $this->session->userdata('customer');
		$cust = is_array($cust) ? $cust : [];
		$cust['login_id'] = $customer_id;
		$cust['customer_id'] = $cust['customer_id'] ?? $customer_id;
		$cust['logged_in'] = $cust['logged_in'] ?? 1;
		$this->session->set_userdata('customer', $cust);
		$this->session->set_userdata('type', 'customer');
		return $customer_id;
	}

	public function enquiry_add()
	{
		if (!$this->require_post()) {
			return;
		}

		$customer_id = $this->_require_customer();
		if ($customer_id <= 0) {
			return;
		}

		$payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

		$product_id = (int)($payload['product_id'] ?? 0);
		$product_name = trim((string)($payload['product_name'] ?? ''));
		$full_name = trim((string)($payload['full_name'] ?? ($payload['fname'] ?? '')));
		$email = trim((string)($payload['email'] ?? ''));
		$mobile = trim((string)($payload['mobile'] ?? ''));
		$message = trim((string)($payload['message'] ?? ''));

		$errors = [];
		if ($product_id <= 0) {
			$errors[] = 'product_id is required';
		}
		if ($full_name === '') {
			$errors[] = 'full_name is required';
		}
		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Valid email is required';
		}
		if ($message === '') {
			$errors[] = 'message is required';
		}

		if ($errors) {
			return $this->fail('validation_error', $errors, 422);
		}

		$row = [
			'customer_id' => $customer_id,
			'product_id' => $product_id,
			'product_name' => $product_name,
			'full_name' => $full_name,
			'email' => $email,
			'mobile' => $mobile,
			'message' => $message,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string)$this->input->user_agent(), 0, 255),
			'created_at' => date('Y-m-d H:i:s'),
			'status' => 1,
		];

		$insert_id = $this->Query_model->insert_data('ec_product_enquiry', $row);
		if (!$insert_id) {
			return $this->fail('server_error', ['Unable to submit'], 500);
		}

		return $this->ok(['id' => (int)$insert_id], 'Enquiry sent successfully');
	}

	private function _list_products($page, $per_page, $category_id, $vendor_id, $q)
	{
		$page = (int)$page;
		$per_page = (int)$per_page;
		$category_id = (int)$category_id;
		$vendor_id = (int)$vendor_id;
		$q = trim((string)$q);

		if ($page < 1) {
			$page = 1;
		}
		if ($per_page < 1) {
			$per_page = 12;
		}
		if ($per_page > 100) {
			$per_page = 100;
		}

		// Always list from `products` table.
		$category_ctx = null;
		if ($category_id > 0) {
			$cat_ids = [$category_id];
			$sub = $this->Product_model->get_all_subcat($category_id);
			if ($sub) {
				foreach ($sub as $s) {
					if (isset($s->category_id) && (int)$s->category_id > 0) {
						$cat_ids[] = (int)$s->category_id;
					}
				}
			}
			$cat_ids = array_values(array_unique(array_filter($cat_ids, function ($v) { return (int)$v > 0; })));
			$category_ctx = array_map(function ($v) { return (string)$v; }, $cat_ids);
		}

		$res = $this->Product_model->get_new_products_page($page, $per_page, $q, $category_ctx, null, null, null, null, $vendor_id);
		$rows = $res['items'] ?? [];
		$total = (int)($res['total'] ?? 0);
		$items = [];

		if ($rows) {
			// Fetch average ratings for this page of products efficiently
			$product_ids = [];
			foreach ($rows as $r) {
				if (is_object($r) && isset($r->id) && (int)$r->id > 0) {
					$product_ids[] = (int)$r->id;
				}
			}
			
			$ratings_map = [];
			if ($product_ids) {
				$rv_stats = $this->db
					->select('product_id, COUNT(*) as review_count, AVG(rating) as avg_rating')
					->from('ec_review_rating')
					->where_in('product_id', $product_ids)
					->where('status', '1')
					->group_by('product_id')
					->get()->result_array();
				foreach ($rv_stats as $stat) {
					$ratings_map[$stat['product_id']] = [
						'avg'   => round((float)$stat['avg_rating'], 1),
						'count' => (int)$stat['review_count']
					];
				}
			}

			foreach ($rows as $r) {
				if (!is_object($r)) {
					continue;
				}

				$p_id = isset($r->id) ? (int)$r->id : 0;
				$img_path = isset($r->first_image_path) ? (string)$r->first_image_path : '';
				$image_url = $img_path !== '' ? $this->_resolve_image_url($img_path) : base_url('assets/default_images/product.jpg');
				$min_price = isset($r->min_price) && $r->min_price !== null ? (float)$r->min_price : null;
				$max_price = isset($r->max_price) && $r->max_price !== null ? (float)$r->max_price : null;

				$prices = [];
				if ($min_price !== null) {
					$prices[] = [
						'regular_price' => $min_price,
						'sale_price' => $min_price,
					];
				}

				$var_data = [
					'available_attributes' => [],
					'default_selection' => (object)[],
					'variations' => [],
				];
				if ($p_id > 0 && isset($r->product_type) && strtolower((string)$r->product_type) === 'variable') {
					$var_data = $this->_get_product_variations_and_attributes($p_id, 1);
				}

				$items[] = [
					'id' => $p_id,
					'name' => isset($r->name) ? (string)$r->name : '',
					'short_description' => null,
					'status' => '1',
					'vendor_id' => isset($r->vendor_id) ? (int)$r->vendor_id : null,
					'product_type' => isset($r->product_type) ? (string)$r->product_type : null,
					'category' => isset($r->category) ? (string)$r->category : null,
					'prices' => $prices,
					'price_range' => [
						'min' => $min_price !== null ? number_format($min_price, 2, '.', '') : null,
						'max' => $max_price !== null ? number_format($max_price, 2, '.', '') : null,
					],
					'image_url' => $image_url,
					'rating' => $ratings_map[$p_id]['avg'] ?? 0,
					'review' => $ratings_map[$p_id]['count'] ?? 0,
					'video_path' => isset($r->video_path) && $r->video_path ? (string)$r->video_path : '',
					'video_url' => isset($r->video_path) && $r->video_path ? base_url('uploads/product_videos/' . $r->video_path) : '',
					'available_attributes' => $var_data['available_attributes'],
					'default_selection' => $var_data['default_selection'],
					'variations' => $var_data['variations'],
				];
			}
		}

		$last_page = $per_page > 0 ? (int)ceil($total / $per_page) : 1;
		return $this->ok([
			'items' => $items,
			'meta' => [
				'source' => 'products',
			],
			'pagination' => [
				'page' => $page,
				'per_page' => $per_page,
				'total' => $total,
				'last_page' => $last_page,
			],
			'filters' => [
				'category_id' => $category_id > 0 ? $category_id : null,
				'vendor_id' => $vendor_id > 0 ? $vendor_id : null,
				'q' => $q !== '' ? $q : null,
			],
		]);
	}

	public function by_category()
	{
		if (!$this->require_post()) {
			return;
		}
		$payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);

		// 3-level category support (Main -> Sub -> Sub-Sub)
		// Accept the most specific id provided, for backward compatibility.
		$category_id = (int)($payload['category_id'] ?? 0);
		$sub_category_id = (int)($payload['sub_category_id'] ?? ($payload['sub_cat_id'] ?? 0));
		$sub_sub_category_id = (int)($payload['sub_sub_category_id'] ?? ($payload['sub_sub_cat_id'] ?? 0));

		$effective_category_id = $sub_sub_category_id > 0
			? $sub_sub_category_id
			: ($sub_category_id > 0 ? $sub_category_id : $category_id);

		if ($effective_category_id <= 0) {
			return $this->fail('validation_error', ['category_id is required'], 422);
		}
		$page = (int)($payload['page'] ?? 1);
		$per_page = (int)($payload['per_page'] ?? 12);
		$vendor_id = (int)($payload['vendor_id'] ?? 0);
		$q = isset($payload['q']) ? (string)$payload['q'] : '';
		return $this->_list_products($page, $per_page, $effective_category_id, $vendor_id, $q);
	}

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

		$page = (int)$this->input->get('page');
		$per_page = (int)$this->input->get('per_page');
		$category_id = (int)$this->input->get('category_id');
		$vendor_id = (int)$this->input->get('vendor_id');
		$q = (string)$this->input->get('q');
		return $this->_list_products($page, $per_page, $category_id, $vendor_id, $q);
    }

    /**
     * GET/POST /api/v1/products/related_products
     * Params: product_id (required), per_page (optional, default 10, max 10)
     * Returns up to per_page active products from the same category, excluding the given product (randomized).
     */
    public function related_products()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST') {
            return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
        }

        $payload = array_merge($this->get_json_input(), $_GET ?: [], $_POST ?: []);
        $product_id = isset($payload['product_id']) && is_numeric((string)$payload['product_id']) ? (int)$payload['product_id'] : 0;

        if ($product_id <= 0) {
            return $this->fail('validation_error', ['product_id is required'], 422);
        }

        // Hard-cap at 10 random products; allow caller to request fewer
        $per_page = isset($payload['per_page']) && is_numeric((string)$payload['per_page']) ? (int)$payload['per_page'] : 10;
        if ($per_page < 1)  $per_page = 10;
        if ($per_page > 10) $per_page = 10;

        // Fetch the product to get its category
        $prod = $this->db
            ->select('id, category, sub_category, sub_sub_category')
            ->from('products')
            ->where('id', $product_id)
            ->limit(1)
            ->get()
            ->row();

        if (!$prod) {
            return $this->ok(['items' => [], 'total' => 0], 'Product not found');
        }

        // Determine effective category ID: prefer most-specific category
        $cat_id = 0;
        foreach (['sub_sub_category', 'sub_category', 'category'] as $cf) {
            $cv = isset($prod->$cf) ? $prod->$cf : '';
            if ($cv !== '' && $cv !== null && is_numeric((string)$cv) && (int)$cv > 0) {
                $cat_id = (int)$cv;
                break;
            }
        }

        if ($cat_id <= 0) {
            // No category — return empty result gracefully
            return $this->ok(['items' => [], 'total' => 0]);
        }

        // Build subquery for cheapest price per variation
        $price_sql = "SELECT pp.variation_id, pp.price
            FROM product_variation_price pp
            JOIN (
                SELECT variation_id, MIN(COALESCE(min_qty,0)) AS min_qty
                FROM product_variation_price
                GROUP BY variation_id
            ) x ON x.variation_id = pp.variation_id AND COALESCE(pp.min_qty,0) = x.min_qty";

        $this->db->select('p.id, p.name, p.slug, p.vendor_id, p.product_type, p.category, p.sub_category, p.sub_sub_category');
        $this->db->select('SUBSTRING_INDEX(GROUP_CONCAT(img.image_path ORDER BY img.id ASC SEPARATOR ","), ",", 1) AS first_image_path', false);
        $this->db->select('MIN(pr.price) AS min_price', false);
        $this->db->from('products p');
        $this->db->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
        $this->db->join('product_variations v', 'v.product_id = p.id', 'left');
        $this->db->join('product_variation_images img', 'img.variation_id = v.id', 'left');
        $this->db->join("($price_sql) pr", 'pr.variation_id = v.id', 'left', false);
        $this->db->where('p.id !=', $product_id);
        $this->db->group_start()
            ->where('p.status', '1')
            ->or_where('p.status', 'active')
            ->or_where('p.status', 'Active')
        ->group_end();
        // Only show products from approved vendors (or admin-owned products)
        $this->db->group_start()
            ->where('p.vendor_id', 0)
            ->or_where('p.vendor_id IS NULL', null, false)
            ->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
        ->group_end();
        // Match by same effective category level
        $this->db->where('(p.sub_sub_category = ' . $cat_id
            . ' OR (p.sub_sub_category IS NULL AND p.sub_category = ' . $cat_id . ')'
            . ' OR (p.sub_sub_category IS NULL AND p.sub_category IS NULL AND p.category = ' . $cat_id . ')'
            . ' OR p.category = ' . $cat_id
            . ')', null, false);
        $this->db->group_by('p.id');
        $this->db->order_by('RAND()', '', false); // randomize on every call
        $this->db->limit($per_page);

        $rows = $this->db->get()->result();

        $items = [];
        if ($rows) {
            // Fetch ratings for these products
            $p_ids = array_map(function($r) { return (int)$r->id; }, $rows);
            $ratings_map = [];
            if ($p_ids) {
                $rv_stats = $this->db
                    ->select('product_id, COUNT(*) as review_count, AVG(rating) as avg_rating')
                    ->from('ec_review_rating')
                    ->where_in('product_id', $p_ids)
                    ->where('status', '1')
                    ->group_by('product_id')
                    ->get()->result_array();
                foreach ($rv_stats as $stat) {
                    $ratings_map[$stat['product_id']] = [
                        'avg'   => round((float)$stat['avg_rating'], 1),
                        'count' => (int)$stat['review_count'],
                    ];
                }
            }

            foreach ($rows as $r) {
                $img_path = isset($r->first_image_path) ? trim((string)$r->first_image_path) : '';
                $image_url = $img_path !== '' ? $this->_resolve_image_url($img_path) : base_url('assets/default_images/product.jpg');
                $min_price = isset($r->min_price) && $r->min_price !== null ? (float)$r->min_price : null;
                $pid = (int)$r->id;

                $items[] = [
                    'id'          => $pid,
                    'name'        => (string)($r->name ?? ''),
                    'slug'        => (string)($r->slug ?? ''),
                    'vendor_id'   => isset($r->vendor_id) ? (int)$r->vendor_id : null,
                    'product_type'=> isset($r->product_type) ? (string)$r->product_type : null,
                    'category'    => isset($r->category) ? $r->category : null,
                    'image_url'   => $image_url,
                    'price'       => $min_price !== null ? number_format($min_price, 2, '.', '') : null,
                    'price_range' => [
                        'min' => $min_price !== null ? number_format($min_price, 2, '.', '') : null,
                    ],
                    'rating'      => $ratings_map[$pid]['avg'] ?? 0,
                    'review'      => $ratings_map[$pid]['count'] ?? 0,
                    'url'         => base_url('product/detail/' . (isset($r->slug) && $r->slug ? $r->slug . '-' . $pid : (string)$pid)),
                ];
            }
        }

        return $this->ok([
            'items' => $items,
            'total' => count($items),
            'product_id' => $product_id,
            'category_id' => $cat_id,
        ]);
    }

    public function add_variable()
    {
        if (strtoupper((string)$this->input->method()) !== 'POST') {
            return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $id = is_numeric((string)($payload['product_id'] ?? null)) ? (int)$payload['product_id'] : 0;

        return $this->show($id);
    }

    public function detail()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET' && strtoupper((string)$this->input->method()) !== 'POST') {
            return $this->fail('method_not_allowed', ['Only GET and POST are allowed'], 405);
        }

        $payload = array_merge($this->get_json_input(), $_GET ?: [], $_POST ?: []);
        $id = 0;
        if (isset($payload['product_id']) && is_numeric((string)$payload['product_id'])) {
            $id = (int)$payload['product_id'];
        } elseif (isset($payload['id']) && is_numeric((string)$payload['id'])) {
            $id = (int)$payload['id'];
        }

        if (!$id) {
            return $this->fail('validation_error', ['Invalid product id'], 422);
        }

        return $this->show($id);
    }

    public function show($id = null)
    {
        if (strtoupper((string)$this->input->method()) !== 'GET' && strtoupper((string)$this->input->method()) !== 'POST') {
            return $this->fail('method_not_allowed', ['Only GET and POST are allowed'], 405);
        }

        if (strtoupper((string)$this->input->method()) === 'POST') {
            $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
            $id = is_numeric((string)($payload['product_id'] ?? null)) ? (int)$payload['product_id'] : (is_numeric((string)$id) ? (int)$id : 0);
        } else {
            $id = is_numeric((string)$id) ? (int)$id : 0;
        }

        if (!$id) {
            return $this->fail('validation_error', ['Invalid product id'], 422);
        }

        $qty = (int)$this->input->get('qty');
        if ($qty <= 0) {
            $qty = 1;
        }

        // Option B (new tables): prefer new `products` table, but keep legacy fallback.
        $new_product = $this->Product_model->get_new_product($id);

        $detail = [];
        $gallery_out = [];
        $cat_out = [];
        $source = 'ec_product';

        if ($new_product) {
            $source = 'products';
            $detail = [
                'id' => isset($new_product->id) ? (int)$new_product->id : 0,
                'name' => isset($new_product->name) ? (string)$new_product->name : '',
                'description' => isset($new_product->description) ? (string)$new_product->description : null,
                'short_description' => null,
                'status' => isset($new_product->status) ? (string)$new_product->status : null,
                'vendor_id' => isset($new_product->vendor_id) ? (int)$new_product->vendor_id : null,
                'brand_id' => isset($new_product->brand_id) ? (int)$new_product->brand_id : null,
                'prices' => [],
                'stock' => isset($new_product->stock) ? (int)$new_product->stock : null,
                // Keep category-ish info (your new table stores these as text fields)
                'category' => isset($new_product->category) ? (string)$new_product->category : null,
                'sub_category' => isset($new_product->sub_category) ? (string)$new_product->sub_category : null,
                'sub_sub_category' => isset($new_product->sub_sub_category) ? (string)$new_product->sub_sub_category : null,
                'product_type' => isset($new_product->product_type) ? (string)$new_product->product_type : null,
                'video_path' => isset($new_product->video_path) && $new_product->video_path ? (string)$new_product->video_path : '',
                'video_url' => isset($new_product->video_path) && $new_product->video_path ? base_url('uploads/product_videos/' . $new_product->video_path) : '',
            ];

            // For Option B we derive images/gallery from variation images.
            $bundle = $this->Product_model->get_variation_bundle($id);
            $all_img_files = [];
            if ($bundle) {
                foreach ($bundle as $b) {
                    if (!isset($b->images) || !$b->images) {
                        continue;
                    }
                    $parts = explode(',', (string)$b->images);
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $all_img_files[] = $p;
                        }
                    }
                }
            }
            $all_img_files = array_values(array_unique(array_filter($all_img_files)));

            $detail['images'] = [];
            foreach ($all_img_files as $fn) {
                $detail['images'][] = $this->_resolve_image_url($fn);
                $gallery_out[] = [
                    'file_name' => (string)$fn,
                    'url' => $this->_resolve_image_url($fn),
                ];
            }
            if (!$detail['images']) {
                $detail['images'] = [base_url('assets/default_images/product.jpg')];
            }
        } else {
            $this->db->select('*');
            $this->db->from('products p');
            $this->db->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
            $this->db->where('p.id', $id);
            $this->db->group_start()
                ->where('p.status', '1')
                ->or_where('p.status', 'active')
                ->or_where('p.status', 'Active')
            ->group_end();
            // Only show if vendor is approved (or admin-owned)
            $this->db->group_start()
                ->where('p.vendor_id', 0)
                ->or_where('p.vendor_id IS NULL', null, false)
                ->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
            ->group_end();
            $this->db->limit(1);
            $p = $this->db->get()->row();
            if (!$p) {
                return $this->fail('not_found', ['Product not found or vendor not approved'], 404);
            }
            $source = 'products';
            $detail = [
                'id' => isset($p->id) ? (int)$p->id : 0,
                'name' => isset($p->name) ? (string)$p->name : '',
                'description' => isset($p->description) ? (string)$p->description : null,
                'short_description' => null,
                'status' => isset($p->status) ? (string)$p->status : null,
                'vendor_id' => isset($p->vendor_id) ? (int)$p->vendor_id : (isset($p->login_id) ? (int)$p->login_id : null),
                'brand_id' => isset($p->brand_id) ? (int)$p->brand_id : null,
                'prices' => [],
                'stock' => isset($p->stock) ? (int)$p->stock : null,
                'category' => isset($p->category) ? (string)$p->category : null,
                'sub_category' => isset($p->sub_category) ? (string)$p->sub_category : null,
                'sub_sub_category' => isset($p->sub_sub_category) ? (string)$p->sub_sub_category : null,
                'product_type' => isset($p->product_type) ? (string)$p->product_type : null,
                'video_path' => isset($p->video_path) && $p->video_path ? (string)$p->video_path : '',
                'video_url' => isset($p->video_path) && $p->video_path ? base_url('uploads/product_videos/' . $p->video_path) : '',
            ];
            $gallery_out = [];
        }

        // Variation-ready format (backward compatible: do not remove existing keys)
        $available_attributes = [];
        $default_selection = (object)[];
        $variations_out = [];

        $bundle = $this->Product_model->get_variation_bundle($id);
        if ($bundle) {
            $variation_ids = [];
            foreach ($bundle as $tmp) {
                if (is_object($tmp) && isset($tmp->variation_id)) {
                    $variation_ids[] = (int)$tmp->variation_id;
                }
            }
            $tiers_map = $this->Product_model->get_variation_price_tiers($variation_ids);

            foreach ($bundle as $v) {
                $attrs = [];
                if (isset($v->attr_json) && $v->attr_json) {
                    $decoded = json_decode((string)$v->attr_json, true);
                    if (is_array($decoded)) {
                        // Support both {"Color":"Green"} and [{"name":"Color","value":"Green"}] forms
                        $is_assoc = array_keys($decoded) !== range(0, count($decoded) - 1);
                        if ($is_assoc) {
                            foreach ($decoded as $k => $val) {
                                $k = trim((string)$k);
                                $val = is_scalar($val) ? trim((string)$val) : '';
                                if ($k !== '' && $val !== '') {
                                    $attrs[$k] = $val;
                                }
                            }
                        } else {
                            foreach ($decoded as $pair) {
                                if (!is_array($pair)) {
                                    continue;
                                }
                                $k = isset($pair['name']) ? trim((string)$pair['name']) : '';
                                $val = isset($pair['value']) ? trim((string)$pair['value']) : '';
                                if ($k !== '' && $val !== '') {
                                    $attrs[$k] = $val;
                                }
                            }
                        }
                    }
                }

				// If attr_json stores attribute_item_id values, map them to labels for UI
				if ($attrs) {
					// Map numeric attribute keys (attribute_id) to attribute names
					$key_ids = [];
					foreach ($attrs as $ak => $av) {
						if (is_numeric((string)$ak)) {
							$key_ids[] = (int)$ak;
						}
					}
					$key_ids = array_values(array_unique(array_filter($key_ids)));
					if ($key_ids) {
						$krows = $this->db
							->select('attribute_id, name')
							->from('ec_attribute')
							->where_in('attribute_id', $key_ids)
							->get()->result();
						$kmap = [];
						foreach ($krows as $r) {
							$kmap[(string)$r->attribute_id] = (string)$r->name;
						}
						$next = [];
						foreach ($attrs as $ak => $av) {
							$sak = (string)$ak;
							$label = isset($kmap[$sak]) && $kmap[$sak] !== '' ? $kmap[$sak] : $sak;
							$next[$label] = $av;
						}
						$attrs = $next;
					}

					$val_ids = [];
					foreach ($attrs as $ak => $av) {
						$av = is_scalar($av) ? trim((string)$av) : '';
						if ($av !== '' && is_numeric($av)) {
							$val_ids[] = (int)$av;
						}
					}
					$val_ids = array_values(array_unique(array_filter($val_ids)));
					if ($val_ids) {
						$rows = $this->db
							->select('attribute_item_id, name')
							->from('ec_attribute_item')
							->where_in('attribute_item_id', $val_ids)
							->get()->result();
						$map = [];
						foreach ($rows as $r) {
							$map[(string)$r->attribute_item_id] = (string)$r->name;
						}
						foreach ($attrs as $ak => $av) {
							$sav = trim((string)$av);
							if ($sav !== '' && isset($map[$sav]) && $map[$sav] !== '') {
								$attrs[$ak] = $map[$sav];
							}
						}
					}
				}

                foreach ($attrs as $k => $val) {
                    if (!isset($available_attributes[$k])) {
                        $available_attributes[$k] = [];
                    }
                    if (!in_array($val, $available_attributes[$k], true)) {
                        $available_attributes[$k][] = $val;
                    }
                }

                if (!$default_selection && $attrs) {
                    $default_selection = (object)$attrs;
                }

                $img_files = [];
                if (isset($v->images) && $v->images) {
                    $parts = explode(',', (string)$v->images);
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $img_files[] = $p;
                        }
                    }
                }
                $img_files = array_values(array_unique($img_files));

                $img_urls = [];
                foreach ($img_files as $fn) {
                    $img_urls[] = $this->_resolve_image_url($fn);
                }
                $img_urls = array_values(array_unique(array_filter($img_urls)));

                $vid = isset($v->variation_id) ? (int)$v->variation_id : 0;
                $price_tiers = ($vid > 0 && isset($tiers_map[$vid]) && is_array($tiers_map[$vid])) ? $tiers_map[$vid] : [];

                $main_price = null;
                if (isset($v->price) && $v->price !== null) {
                    $main_price = number_format((float)$v->price, 2, '.', '');
                } elseif (isset($price_tiers[0]) && is_array($price_tiers[0]) && isset($price_tiers[0]['price'])) {
                    $main_price = (string)$price_tiers[0]['price'];
                }

                $effective_price = $this->_resolve_effective_price_for_qty($price_tiers, $qty, $main_price);

                $variations_out[] = [
                    'variation_id' => isset($v->variation_id) ? (string)$v->variation_id : '',
                    'attributes' => $attrs,
                    'price' => $main_price,
                    'effective_price' => $effective_price,
                    'mrp' => null,
                    'stock' => isset($v->stock) ? (string)((int)$v->stock) : null,
                    'images' => $img_files,
                    'image_urls' => $img_urls,
                    'price_tiers' => $price_tiers,
                ];
            }

            foreach ($available_attributes as $k => $vals) {
                sort($available_attributes[$k]);
            }

            // Derive minimum price from variations for backward compatibility with template price arrays
            $min_var_price = null;
            if ($variations_out) {
                foreach ($variations_out as $vo) {
                    if (isset($vo['price']) && $vo['price'] !== null) {
                        $p_val = (float)$vo['price'];
                        if ($min_var_price === null || $p_val < $min_var_price) {
                            $min_var_price = $p_val;
                        }
                    }
                }
            }
            if ($min_var_price !== null) {
                $detail['prices'] = [
                    [
                        'regular_price' => $min_var_price,
                        'sale_price' => $min_var_price,
                    ]
                ];
            }
        }

        // Fetch ratings and review count directly via DB (safe regardless of model slave state)
        $avg_rating = 0.0;
        $review_count = 0;
        try {
            $rating_row = $this->db
                ->select('AVG(rating) AS avg_rating, COUNT(review) AS review_count')
                ->from('ec_review_rating')
                ->where('product_id', $id)
                ->get()->row();
            if ($rating_row) {
                $avg_rating = ($rating_row->avg_rating !== null) ? round((float)$rating_row->avg_rating, 1) : 0.0;
                $review_count = ($rating_row->review_count !== null) ? (int)$rating_row->review_count : 0;
            }
        } catch (Exception $e) {
            // silently fallback to defaults
        }

        $detail['rating'] = $avg_rating;
        $detail['review'] = $review_count;

        // Fetch category names and attach as simple keys inside product
        $detail['category_name'] = null;
        $detail['sub_category_name'] = null;
        $detail['sub_sub_category_name'] = null;

        $cat_id = isset($detail['category']) && is_numeric((string)$detail['category']) ? (int)$detail['category'] : 0;
        $sub_cat_id = isset($detail['sub_category']) && is_numeric((string)$detail['sub_category']) ? (int)$detail['sub_category'] : 0;
        $sub_sub_cat_id = isset($detail['sub_sub_category']) && is_numeric((string)$detail['sub_sub_category']) ? (int)$detail['sub_sub_category'] : 0;

        $cat_ids_to_fetch = array_values(array_unique(array_filter([$cat_id, $sub_cat_id, $sub_sub_cat_id])));

        if ($cat_ids_to_fetch) {
            $cat_rows = $this->db
                ->select('id, name')
                ->from('ec_categories_prod')
                ->where_in('id', $cat_ids_to_fetch)
                ->get()->result();
            $cat_map = [];
            if ($cat_rows) {
                foreach ($cat_rows as $cr) {
                    $cat_map[(int)$cr->id] = (string)$cr->name;
                }
            }
            if ($cat_id > 0 && isset($cat_map[$cat_id])) {
                $detail['category_name'] = $cat_map[$cat_id];
            }
            if ($sub_cat_id > 0 && isset($cat_map[$sub_cat_id])) {
                $detail['sub_category_name'] = $cat_map[$sub_cat_id];
            }
            if ($sub_sub_cat_id > 0 && isset($cat_map[$sub_sub_cat_id])) {
                $detail['sub_sub_category_name'] = $cat_map[$sub_sub_cat_id];
            }
        }

        // ── Reviews list ────────────────────────────────────────────────────────
        $reviews_list = [];
        $reviews_breakdown = [];
        $reviews_avg  = $avg_rating;
        $reviews_total = $review_count;
        try {
            $reviews_raw = $this->db
                ->select('r.review_rating_id, r.customer_id, r.title, r.rating, r.review, r.status, r.date_created')
                ->select("CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,'')) AS reviewer_name", false)
                ->from('ec_review_rating r')
                ->join('ec_customer c', 'c.customer_id = r.customer_id', 'left')
                ->where('r.product_id', $id)
                ->where('r.status', '1')
                ->order_by('r.date_created', 'DESC')
                ->get()->result_array();

            $rv_total = 0;
            $rv_sum   = 0;
            $star_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

            foreach ($reviews_raw as &$rv) {
                $s = (int)$rv['rating'];
                $rv_total++;
                if (isset($star_counts[$s])) {
                    $star_counts[$s]++;
                }
                $rv_sum += $s;

                $name = trim($rv['reviewer_name']);
                $rv['reviewer_name']    = $name ?: 'Anonymous';
                $rv['reviewer_initial'] = strtoupper(substr($rv['reviewer_name'], 0, 1));
                $rv['date_formatted']   = date('M d, Y', strtotime($rv['date_created']));
                $rv['rating']           = $s;
                $reviews_list[] = $rv;
            }
            unset($rv);

            $reviews_avg   = $rv_total > 0 ? round($rv_sum / $rv_total, 1) : 0;
            $reviews_total = $rv_total;

            for ($i = 5; $i >= 1; $i--) {
                $cnt = $star_counts[$i];
                $reviews_breakdown[$i] = [
                    'count'   => $cnt,
                    'percent' => $rv_total > 0 ? round(($cnt / $rv_total) * 100) : 0,
                ];
            }
        } catch (Exception $e) {
            // silently fallback
        }
        // ────────────────────────────────────────────────────────────────────────

        // Clean structured response — all product fields nested under 'product' key, no top-level duplicates.
        $out = [
            'product'              => $detail,
            'available_attributes' => $available_attributes,
            'default_selection'    => $default_selection,
            'variations'           => $variations_out,
            'gallery'              => $gallery_out,
            'reviews'              => [
                'total'     => $reviews_total,
                'avg'       => $reviews_avg,
                'breakdown' => $reviews_breakdown,
                'list'      => $reviews_list,
            ],
            'meta'                 => [
                'source'       => $source,
                'selected_qty' => $qty,
            ],
        ];

        return $this->ok($out);
    }

    private function _resolve_effective_price_for_qty($price_tiers, $qty, $fallback_price)
    {
        $qty = (int)$qty;
        if ($qty <= 0) {
            $qty = 1;
        }

        if (is_array($price_tiers) && $price_tiers) {
            foreach ($price_tiers as $t) {
                if (!is_array($t)) {
                    continue;
                }
                $min = isset($t['min_qty']) ? (int)$t['min_qty'] : 0;
                $max = isset($t['max_qty']) ? (int)$t['max_qty'] : 0;
                $price = isset($t['price']) ? (string)$t['price'] : null;
                if ($price === null || $price === '') {
                    continue;
                }

                if ($min <= 0) {
                    $min = 1;
                }
                $max_ok = ($max <= 0) ? true : ($qty <= $max);
                if ($qty >= $min && $max_ok) {
                    return $price;
                }
            }

            // If no tier matched, use the last tier's price (common expectation for qty > max)
            $last = end($price_tiers);
            if (is_array($last) && isset($last['price']) && $last['price'] !== null && $last['price'] !== '') {
                return (string)$last['price'];
            }
        }

        return $fallback_price !== null && $fallback_price !== '' ? (string)$fallback_price : null;
    }

	private function _get_product_variations_and_attributes($id, $qty = 1)
	{
		$available_attributes = [];
		$default_selection = (object)[];
		$variations_out = [];

		$bundle = $this->Product_model->get_variation_bundle($id);
		if ($bundle) {
			$variation_ids = [];
			foreach ($bundle as $tmp) {
				if (is_object($tmp) && isset($tmp->variation_id)) {
					$variation_ids[] = (int)$tmp->variation_id;
				}
			}
			$tiers_map = $this->Product_model->get_variation_price_tiers($variation_ids);

			foreach ($bundle as $v) {
				$attrs = [];
				if (isset($v->attr_json) && $v->attr_json) {
					$decoded = json_decode((string)$v->attr_json, true);
					if (is_array($decoded)) {
						$is_assoc = array_keys($decoded) !== range(0, count($decoded) - 1);
						if ($is_assoc) {
							foreach ($decoded as $k => $val) {
								$k = trim((string)$k);
								$val = is_scalar($val) ? trim((string)$val) : '';
								if ($k !== '' && $val !== '') {
									$attrs[$k] = $val;
								}
							}
						} else {
							foreach ($decoded as $pair) {
								if (!is_array($pair)) {
									continue;
								}
								$k = isset($pair['name']) ? trim((string)$pair['name']) : '';
								$val = isset($pair['value']) ? trim((string)$pair['value']) : '';
								if ($k !== '' && $val !== '') {
									$attrs[$k] = $val;
								}
							}
						}
					}
				}

				// If attr_json stores attribute_item_id values, map them to labels for UI
				if ($attrs) {
					// Map numeric attribute keys (attribute_id) to attribute names
					$key_ids = [];
					foreach ($attrs as $ak => $av) {
						if (is_numeric((string)$ak)) {
							$key_ids[] = (int)$ak;
						}
					}
					$key_ids = array_values(array_unique(array_filter($key_ids)));
					if ($key_ids) {
						$krows = $this->db
							->select('attribute_id, name')
							->from('ec_attribute')
							->where_in('attribute_id', $key_ids)
							->get()->result();
						$kmap = [];
						foreach ($krows as $r) {
							$kmap[(string)$r->attribute_id] = (string)$r->name;
						}
						$next = [];
						foreach ($attrs as $ak => $av) {
							$sak = (string)$ak;
							$label = isset($kmap[$sak]) && $kmap[$sak] !== '' ? $kmap[$sak] : $sak;
							$next[$label] = $av;
						}
						$attrs = $next;
					}

					$val_ids = [];
					foreach ($attrs as $ak => $av) {
						$av = is_scalar($av) ? trim((string)$av) : '';
						if ($av !== '' && is_numeric($av)) {
							$val_ids[] = (int)$av;
						}
					}
					$val_ids = array_values(array_unique(array_filter($val_ids)));
					if ($val_ids) {
						$rows = $this->db
							->select('attribute_item_id, name')
							->from('ec_attribute_item')
							->where_in('attribute_item_id', $val_ids)
							->get()->result();
						$map = [];
						foreach ($rows as $r) {
							$map[(string)$r->attribute_item_id] = (string)$r->name;
						}
						foreach ($attrs as $ak => $av) {
							$sav = trim((string)$av);
							if ($sav !== '' && isset($map[$sav]) && $map[$sav] !== '') {
								$attrs[$ak] = $map[$sav];
							}
						}
					}
				}

				foreach ($attrs as $k => $val) {
					if (!isset($available_attributes[$k])) {
						$available_attributes[$k] = [];
					}
					if (!in_array($val, $available_attributes[$k], true)) {
						$available_attributes[$k][] = $val;
					}
				}

				if (!$default_selection && $attrs) {
					$default_selection = (object)$attrs;
				}

				$img_files = [];
				if (isset($v->images) && $v->images) {
					$parts = explode(',', (string)$v->images);
					foreach ($parts as $p) {
						$p = trim($p);
						if ($p !== '') {
							$img_files[] = $p;
						}
					}
				}
				$img_files = array_values(array_unique($img_files));

				$img_urls = [];
				foreach ($img_files as $fn) {
					$img_urls[] = $this->_resolve_image_url($fn);
				}
				$img_urls = array_values(array_unique(array_filter($img_urls)));

				$vid = isset($v->variation_id) ? (int)$v->variation_id : 0;
				$price_tiers = ($vid > 0 && isset($tiers_map[$vid]) && is_array($tiers_map[$vid])) ? $tiers_map[$vid] : [];

				$main_price = null;
				if (isset($v->price) && $v->price !== null) {
					$main_price = number_format((float)$v->price, 2, '.', '');
				} elseif (isset($price_tiers[0]) && is_array($price_tiers[0]) && isset($price_tiers[0]['price'])) {
					$main_price = (string)$price_tiers[0]['price'];
				}

				$effective_price = $this->_resolve_effective_price_for_qty($price_tiers, $qty, $main_price);

				$variations_out[] = [
					'variation_id' => isset($v->variation_id) ? (string)$v->variation_id : '',
					'attributes' => $attrs,
					'price' => $main_price,
					'effective_price' => $effective_price,
					'mrp' => null,
					'stock' => isset($v->stock) ? (string)((int)$v->stock) : null,
					'images' => $img_files,
					'image_urls' => $img_urls,
					'price_tiers' => $price_tiers,
				];
			}

			foreach ($available_attributes as $k => $vals) {
				sort($available_attributes[$k]);
			}
		}

		return [
			'available_attributes' => $available_attributes,
			'default_selection' => $default_selection,
			'variations' => $variations_out,
		];
	}

    private function _format_product_detail($p)
    {
        $prices = $this->_parse_prices($p);

        return [
            'id' => isset($p->id) ? (int)$p->id : 0,
            'name' => isset($p->name) ? (string)$p->name : '',
            'description' => isset($p->description) ? (string)$p->description : null,
            'short_description' => isset($p->short_description) ? (string)$p->short_description : null,
            'status' => isset($p->status) ? (string)$p->status : null,
            'vendor_id' => isset($p->login_id) ? (int)$p->login_id : null,
            'brand_id' => isset($p->brand_id) ? (int)$p->brand_id : null,
            'prices' => $prices,
            'stock' => isset($p->stock) ? (int)$p->stock : null,
            'video_path' => isset($p->video_path) && $p->video_path ? (string)$p->video_path : '',
            'video_url' => isset($p->video_path) && $p->video_path ? base_url('uploads/product_videos/' . $p->video_path) : '',
        ];
    }

	private function _format_product_summary($p)
	{
		$prices = $this->_parse_prices($p);
		$imgs = $this->_resolve_product_image_urls($p);
		$image_url = (is_array($imgs) && isset($imgs[0])) ? (string)$imgs[0] : base_url('assets/default_images/product.jpg');

		$min_price = null;
		$max_price = null;
		if (is_array($prices) && $prices) {
			foreach ($prices as $pr) {
				if (!is_array($pr)) {
					continue;
				}
				$val = null;
				if (isset($pr['sale_price']) && $pr['sale_price'] !== '' && $pr['sale_price'] !== null) {
					$val = (float)$pr['sale_price'];
				} elseif (isset($pr['sales_price']) && $pr['sales_price'] !== '' && $pr['sales_price'] !== null) {
					$val = (float)$pr['sales_price'];
				} elseif (isset($pr['regular_price']) && $pr['regular_price'] !== '' && $pr['regular_price'] !== null) {
					$val = (float)$pr['regular_price'];
				}
				if ($val === null) {
					continue;
				}
				if ($min_price === null || $val < $min_price) {
					$min_price = $val;
				}
				if ($max_price === null || $val > $max_price) {
					$max_price = $val;
				}
			}
		}

		return [
			'id' => isset($p->id) ? (int)$p->id : 0,
			'name' => isset($p->name) ? (string)$p->name : '',
			'short_description' => isset($p->short_description) ? (string)$p->short_description : null,
			'status' => isset($p->status) ? (string)$p->status : null,
			'vendor_id' => isset($p->login_id) ? (int)$p->login_id : null,
			'prices' => $prices,
			'price_range' => [
				'min' => $min_price !== null ? number_format($min_price, 2, '.', '') : null,
				'max' => $max_price !== null ? number_format($max_price, 2, '.', '') : null,
			],
			'image_url' => $image_url,
		];
	}

    private function _parse_prices($p)
    {
        $out = [];
        if (isset($p->prices) && $p->prices) {
            $decoded = json_decode($p->prices, true);
            if (is_array($decoded)) {
                $out = $decoded;
            }
        }
        return $out;
    }

    private function _resolve_product_image_urls($product)
    {
        $placeholder = base_url('assets/default_images/product.jpg');
        $candidates = [];

        if (isset($product->images) && $product->images) {
            $imgs = json_decode($product->images, true);
            if (is_array($imgs)) {
                foreach ($imgs as $img) {
                    if (is_string($img) && $img !== '') {
                        $candidates[] = $img;
                    }
                }
            }
        }

        if (!$candidates && isset($product->prices) && $product->prices) {
            $prices_obj = json_decode($product->prices);
            if (is_array($prices_obj) && isset($prices_obj[0]) && is_object($prices_obj[0]) && isset($prices_obj[0]->images) && is_array($prices_obj[0]->images)) {
                foreach ($prices_obj[0]->images as $img) {
                    if (is_string($img) && $img !== '') {
                        $candidates[] = $img;
                    }
                }
            }
        }

        $urls = [];
        foreach ($candidates as $raw) {
            $urls[] = $this->_resolve_image_url($raw);
        }

        $urls = array_values(array_unique(array_filter($urls)));
        return $urls ? $urls : [$placeholder];
    }

    private function _resolve_image_url($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        $rel = ltrim($raw, '/\\');
        if ($rel !== '' && @file_exists(FCPATH . $rel)) {
            return base_url($rel);
        }

        return base_url('uploads/products/' . $rel);
    }

	public function add_review()
	{
		if (!$this->require_post()) {
			return;
		}

		$customer_id = $this->_require_customer();
		if ($customer_id <= 0) {
			return;
		}

		$input = $this->get_json_input();
		if (empty($input)) {
			$input = $this->input->post();
		}

		$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
		$title      = isset($input['title']) ? trim((string)$input['title']) : '';
		$review     = isset($input['review']) ? trim((string)$input['review']) : '';
		$rating     = isset($input['rating']) ? (int)$input['rating'] : 0;

		if (!$product_id) {
			$this->fail('validation_error', ['Product ID is required']);
			return;
		}
		if ($review === '') {
			$this->fail('validation_error', ['Please write a review.']);
			return;
		}
		if ($rating < 1 || $rating > 5) {
			$this->fail('validation_error', ['Please select a star rating (1–5).']);
			return;
		}

		$post_data = [
			'customer_id' => $customer_id,
			'product_id'  => $product_id,
			'title'       => $title,
			'review'      => $review,
			'rating'      => $rating,
			'status'      => '0',
		];

		$existing = $this->db
			->get_where('ec_review_rating', [
				'product_id'  => $product_id,
				'customer_id' => $customer_id,
			])->row();

		if ($existing) {
			if (!$this->db
				->where('product_id', $product_id)
				->where('customer_id', $customer_id)
				->update('ec_review_rating', $post_data)) {
                $this->fail('db_error', ['Failed to update: ' . json_encode($this->db->error())]);
                return;
            }
			$this->ok(['message' => 'Your review has been updated and is pending admin approval.']);
		} else {
			if (!$this->db->insert('ec_review_rating', $post_data)) {
                $this->fail('db_error', ['Failed to insert: ' . json_encode($this->db->error())]);
                return;
            }
			$this->ok(['message' => 'Thank you! Your review has been submitted and is pending admin approval.']);
		}
	}

	public function product_reviews()
	{
		if (strtoupper((string)$this->input->method()) !== 'POST') {
			return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
		}

		$raw = (string)$this->input->raw_input_stream;
		$input = [];
		if ($raw !== '') {
			$decoded = json_decode($raw, true);
			if (is_array($decoded)) {
				$input = $decoded;
			}
		}
		if (empty($input)) {
			$input = $this->input->post();
		}

		$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
		if (!$product_id) {
			return $this->fail('validation_error', ['Product ID is required']);
		}

		// Check if we have customer credentials (optional, to see own pending reviews)
		$customer_id = 0;
		$token = $this->get_bearer_token();
		if ($token !== '') {
			$claims = $this->verify_token($token);
			if ($claims && isset($claims['sub']) && ($claims['type'] ?? '') === 'access' && ($claims['role'] ?? '') === 'customer') {
				$customer_id = (int)$claims['sub'];
			}
		} else {
			$customer = $this->session->userdata('customer');
			$customer_id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
		}

		$this->db
			->select('r.review_rating_id, r.customer_id, r.title, r.rating, r.review, r.status, r.date_created')
			->select("CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,'')) AS reviewer_name", false)
			->from('ec_review_rating r')
			->join('ec_customer c', 'c.customer_id = r.customer_id', 'left')
			->where('r.product_id', $product_id);

		if ($customer_id > 0) {
			$this->db->where("(r.status = '1' OR r.customer_id = $customer_id)");
		} else {
			$this->db->where('r.status', '1');
		}

		$reviews_raw = $this->db
			->order_by('r.date_created', 'DESC')
			->get()->result_array();

		$total = 0;
		$sum = 0;
		$star_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
		$my_review = null;

		foreach ($reviews_raw as &$r) {
			$s = (int)$r['rating'];
			if ($customer_id > 0 && (int)$r['customer_id'] === $customer_id) {
				$my_review = $r;
			}
			if ($r['status'] === '1') {
				$total++;
				if (isset($star_counts[$s])) {
					$star_counts[$s]++;
				}
				$sum += $s;
			}

			$name = trim($r['reviewer_name']);
			$r['reviewer_name']   = $name ?: 'Anonymous';
			$r['reviewer_initial'] = strtoupper(substr($r['reviewer_name'], 0, 1));
			$r['date_formatted']  = date('F j, Y', strtotime($r['date_created']));
			$r['rating']          = (int)$r['rating'];
		}
		unset($r);

		$avg = $total > 0 ? round($sum / $total, 1) : 0;

		$breakdown = [];
		for ($i = 5; $i >= 1; $i--) {
			$cnt = $star_counts[$i];
			$breakdown[$i] = [
				'count'   => $cnt,
				'percent' => $total > 0 ? round(($cnt / $total) * 100) : 0,
			];
		}

		return $this->ok([
			'reviews'   => $reviews_raw,
			'summary'   => [
				'total'     => $total,
				'avg'       => $avg,
				'breakdown' => $breakdown,
			],
			'my_review' => $my_review,
		], 'success');
	}
}
