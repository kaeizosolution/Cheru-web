<?php       
defined('BASEPATH') OR exit('No direct script access allowed');
            
class Welcome extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Cart_model');
        $this->load->model('Query_model');
        $this->load->model('Advertisement_model');
        $this->load->model('Product_model');
        $this->load->model('Item_model');
        $this->load->model('Currency_model');
        $this->load->model('Coupon_model');
        $this->load->helper('api');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        $this->config->load('custom_config');
        $this->G_KEY = $this->config->item('G_KEY');
    }

	private function _normalize_url_scheme($url)
	{
		$url = (string)$url;
		$is_https = false;
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
			$is_https = true;
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
			$is_https = true;
		}
		if ($is_https) {
			$url = preg_replace('#^http://#i', 'https://', $url);
		}
		return $url;
	}


///////////NEW CODE///////////////

	public function index()
    {
        $data = [];
        
        // --- FIXED: Load 'home_page_lang' instead of 'site_lang' ---
        $data['site_lang'] = get_page_language_data('home_page_lang'); 
        // ----------------------------------------------------------

        $data['csrf']     = csrf_token();
        $data['homepage'] = '1';

        		$this->load->model('Query_model'); 

		// Homepage: Advertisements (below Shop by Category)
		try {
			$ads = [];
			if (method_exists($this->Advertisement_model, 'get_visible_ads')) {
				$ads = $this->Advertisement_model->get_visible_ads(3);
			}
			if (!is_array($ads) || !$ads) {
				$ads = $this->Advertisement_model->get_all_ads();
				if (is_array($ads)) {
					$ads = array_slice($ads, 0, 3);
				}
			}
			$data['homepage_ads'] = is_array($ads) ? $ads : [];
		} catch (Exception $e) {
			$data['homepage_ads'] = [];
		}

		$use_api = (bool)$this->config->item('use_api');
		$api_ok = false;
		$products = null;
		$shop_categories = null;
		$shop_by_category_products = null;

		if ($use_api) {
			$api_products = call_api('GET', 'products?page=1');
			if ($api_products['response'] !== null && (int)($api_products['response']['status'] ?? 0) === 1) {
				$items = $api_products['response']['data']['items'] ?? [];
				if (is_array($items)) {
					$mapped = [];
					foreach ($items as $it) {
						if (!is_array($it)) {
							continue;
						}

						$img_url = (string)($it['image_url'] ?? '');
						$img_fn = $img_url !== '' ? basename(parse_url($img_url, PHP_URL_PATH)) : '';
						$imgs = $img_fn !== '' ? json_encode([$img_fn]) : '';
						$prices = $it['prices'] ?? [];
						if (!is_array($prices)) {
							$prices = [];
						}

						$mapped[] = (object)[
							'id' => (int)($it['id'] ?? 0),
							'name' => (string)($it['name'] ?? ''),
							'images' => $imgs,
							'prices' => json_encode($prices),
							'status' => '1',
							'vendor_id' => isset($it['vendor_id']) ? (int)$it['vendor_id'] : 0,
						];
					}
					$products = $mapped;
					$api_ok = true;
				}
			}

			$api_categories = call_api('GET', 'categories');
			if ($api_categories['response'] !== null && (int)($api_categories['response']['status'] ?? 0) === 1) {
				$cats = $api_categories['response']['data']['categories'] ?? [];
				if (is_array($cats)) {
					$mapped_cats = [];
					foreach ($cats as $c) {
						if (!is_array($c)) {
							continue;
						}
						if ((int)($c['parent_id'] ?? 0) !== 0) {
							continue;
						}
						$mapped_cats[] = (object)[
							'id' => (int)($c['id'] ?? 0),
							'parent_id' => (int)($c['parent_id'] ?? 0),
							'name' => (string)($c['name'] ?? ''),
							'slug' => (string)($c['slug'] ?? ''),
							'thumbnail' => (string)($c['thumbnail'] ?? ''),
							'banner_image' => (string)($c['banner_image'] ?? ''),
							'icon' => (string)($c['icon'] ?? ''),
							'status' => (string)($c['status'] ?? '1'),
						];
					}
					$shop_categories = $mapped_cats;
				}
			}

			if (is_array($shop_categories) && $shop_categories) {
				$shop_by_category_products = [];
				$cat_seen = 0;
				foreach ($shop_categories as $cat) {
					$cat_seen++;
					$cat_id = isset($cat->id) ? (int)$cat->id : 0;
					if (!$cat_id) {
						continue;
					}

					$cat_api = call_api('GET', 'products?category_id=' . $cat_id . '&page=1&per_page=8');
					$cat_products = [];
					if ($cat_api['response'] !== null && (int)($cat_api['response']['status'] ?? 0) === 1) {
						$cat_items = $cat_api['response']['data']['items'] ?? [];
						if (is_array($cat_items)) {
							foreach ($cat_items as $it) {
								if (!is_array($it)) {
									continue;
								}
								$img_url = (string)($it['image_url'] ?? '');
								$img_fn = $img_url !== '' ? basename(parse_url($img_url, PHP_URL_PATH)) : '';
								$imgs = $img_fn !== '' ? json_encode([$img_fn]) : '';
								$prices = $it['prices'] ?? [];
								if (!is_array($prices)) {
									$prices = [];
								}
								$p = (object)[
									'id' => (int)($it['id'] ?? 0),
									'name' => (string)($it['name'] ?? ''),
									'images' => $imgs,
									'prices' => json_encode($prices),
									'status' => '1',
									'vendor_id' => isset($it['vendor_id']) ? (int)$it['vendor_id'] : 0,
								];
								$p->url = base_url('product/details/' . $p->id);
								$p->image_url = base_url('assets/default_images/product.jpg');
								$price_data = json_decode($p->prices, true) ?: [];
								$p->display_price = 0;
								if (is_array($price_data) && isset($price_data[0]) && is_array($price_data[0])) {
									$first = $price_data[0];
									if (isset($first['sales_price'])) {
										$p->display_price = (float)$first['sales_price'];
									} elseif (isset($first['sale_price'])) {
										$p->display_price = (float)$first['sale_price'];
									} elseif (isset($first['regular_price'])) {
										$p->display_price = (float)$first['regular_price'];
									}
								}
								$cat_products[] = $p;
							}
						}
					}

					$cat->url = isset($cat->slug) && $cat->slug ? base_url('products/category/' . $cat->slug) : '#';
					$shop_by_category_products[] = (object)[
						'category' => $cat,
						'products' => $cat_products,
					];

					if ($cat_seen >= 4) {
						break;
					}
				}
			}
		}

		if (!$api_ok) {
			// Homepage: Deal of the Day + generic products should come from new `products` table
			$res = $this->Product_model->get_new_products_page(1, 12, '');
			$rows = $res['items'] ?? [];
			$mapped = [];
			if ($rows) {
				foreach ($rows as $r) {
					if (!is_object($r)) {
						continue;
					}
					$img_path = isset($r->first_image_path) ? trim((string)$r->first_image_path) : '';
					$imgs = $img_path !== '' ? json_encode([basename($img_path)]) : '';
					$min_price = isset($r->min_price) && $r->min_price !== null ? (float)$r->min_price : 0;
					$prices = json_encode([
						['regular_price' => $min_price, 'sale_price' => $min_price, 'sales_price' => $min_price]
					]);
					$mapped[] = (object)[
						'id' => (int)($r->id ?? 0),
						'name' => (string)($r->name ?? ''),
						'images' => $imgs,
						'prices' => $prices,
						'status' => '1',
						'vendor_id' => isset($r->vendor_id) ? (int)$r->vendor_id : 0,
					];
				}
			}
			$data['products'] = $mapped;

			// Homepage: Shop by Category -> category-wise products from `products` table (best-effort mapping)
			$shop_categories = $this->Query_model->get_data('ec_categories_prod', array('status' => '1', 'parent_id' => '0'), array('orderby' => 'ASC', 'id' => 'ASC'));
			$data['shop_categories'] = $shop_categories ? $shop_categories : array();
			$shop_by_category_products = array();
			if ($shop_categories) {
				$cat_seen = 0;
				foreach ($shop_categories as $cat) {
					$cat_seen++;
					$cat_id = isset($cat->id) ? (int)$cat->id : 0;
					if (!$cat_id) {
						continue;
					}

					$cat_products = $this->db
						->select('p.id, p.name, p.product_type, p.status, p.category, p.vendor_id')
						->from('products p')
						->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left')
						->where('p.status', '1')
						->group_start()
							->where('p.vendor_id', 0)
							->or_where('p.vendor_id IS NULL', null, false)
							->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
						->group_end()
						->group_start()
							->where('p.category', (string)($cat->name ?? ''))
							->or_where('p.category', (string)($cat->slug ?? ''))
							->or_where('p.category', (string)$cat_id)
						->group_end()
						->order_by('p.id', 'DESC')
						->limit(8)
						->get()->result();

					if ($cat_products) {
						foreach ($cat_products as $p) {
							$p->url = base_url('product/details/' . $p->id);
							$p->image_url = base_url('assets/default_images/product.jpg');
							$img = $this->db
								->select('product_variation_images.image_path')
								->from('product_variations')
								->join('product_variation_images', 'product_variation_images.variation_id = product_variations.id')
								->where('product_variations.product_id', (int)$p->id)
								->order_by('product_variation_images.id', 'ASC')
								->limit(1)
								->get()->row();
							if ($img && !empty($img->image_path)) {
								$p->image_url = $this->_normalize_url_scheme(base_url('uploads/products/' . (string)$img->image_path));
							}

							$p->display_price = 0;
							$unit_price_row = $this->db
								->select('pvp.price')
								->from('product_variation_price pvp')
								->join('product_variations pv', 'pv.id = pvp.variation_id')
								->where('pv.product_id', (int)$p->id)
								->order_by('CASE WHEN pvp.min_qty IS NULL THEN 2 WHEN pvp.min_qty <= 1 THEN 0 ELSE 1 END, pvp.min_qty ASC', '', false)
								->limit(1)
								->get()->row();
							if ($unit_price_row && isset($unit_price_row->price)) {
								$p->display_price = (float)$unit_price_row->price;
							}
						}
					}

					$cat->url = isset($cat->slug) && $cat->slug ? base_url('products/category/' . $cat->slug) : '#';
					$shop_by_category_products[] = (object)array(
						'category' => $cat,
						'products' => $cat_products ? $cat_products : array(),
					);

					if ($cat_seen >= 4) {
						break;
					}
				}
			}
			$data['shop_by_category_products'] = $shop_by_category_products;
		} else {
			$data['products'] = is_array($products) ? $products : [];
			$data['shop_categories'] = is_array($shop_categories) ? $shop_categories : [];
			$data['shop_by_category_products'] = is_array($shop_by_category_products) ? $shop_by_category_products : [];
		}

		// Homepage: Best seller in the last month (last 30 days)
		$data['best_sellers_last_month'] = [];
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
				->limit(20)
				->get()->result();

			$product_ids = [];
			$qty_map = [];
			if (is_array($top)) {
				foreach ($top as $t) {
					$pid = (int)($t->product_id ?? 0);
					if ($pid <= 0) continue;
					$product_ids[] = $pid;
					$qty_map[$pid] = (int)($t->total_qty ?? 0);
				}
			}

			$product_ids = array_values(array_unique($product_ids));
			if ($product_ids) {
				$rows = $this->db
					->select('p.id, p.name, p.status, p.product_type, p.vendor_id')
					->select('COALESCE(rt.avg_rating, 0) AS avg_rating, COALESCE(rt.review_count, 0) AS review_count', false)
					->from('products p')
					->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left')
					->join('(SELECT product_id, AVG(rating) AS avg_rating, COUNT(review_rating_id) AS review_count FROM ec_review_rating WHERE status = \'1\' GROUP BY product_id) rt', 'rt.product_id = p.id', 'left', false)
					->where_in('p.id', $product_ids)
					->where('p.status', '1')
					->group_start()
						->where('p.vendor_id', 0)
						->or_where('p.vendor_id IS NULL', null, false)
						->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
					->group_end()
					->get()->result();

				$by_id = [];
				if (is_array($rows)) {
					foreach ($rows as $r) {
						if (!is_object($r)) continue;
						$by_id[(int)$r->id] = $r;
					}
				}

				$best = [];
				foreach ($product_ids as $pid) {
					if (!isset($by_id[$pid])) continue;
					$p = $by_id[$pid];
					$p->image_url = base_url('assets/default_images/product.jpg');
					$img = $this->db
						->select('product_variation_images.image_path')
						->from('product_variations')
						->join('product_variation_images', 'product_variation_images.variation_id = product_variations.id')
						->where('product_variations.product_id', (int)$pid)
						->order_by('product_variation_images.id', 'ASC')
						->limit(1)
						->get()->row();
					if ($img && !empty($img->image_path)) {
						$p->image_url = $this->_normalize_url_scheme(base_url('uploads/products/' . (string)$img->image_path));
					}

					$min_price = 0;
					$unit_price_row = $this->db
						->select('pvp.price')
						->from('product_variation_price pvp')
						->join('product_variations pv', 'pv.id = pvp.variation_id')
						->where('pv.product_id', (int)$pid)
						->order_by('CASE WHEN pvp.min_qty IS NULL THEN 2 WHEN pvp.min_qty <= 1 THEN 0 ELSE 1 END, pvp.min_qty ASC', '', false)
						->limit(1)
						->get()->row();
					if ($unit_price_row && isset($unit_price_row->price)) {
						$min_price = (float)$unit_price_row->price;
					}
					$p->prices = json_encode([
						['regular_price' => $min_price, 'sale_price' => $min_price, 'sales_price' => $min_price]
					]);
					$p->total_qty = $qty_map[$pid] ?? 0;
					$best[] = $p;
				}
				$data['best_sellers_last_month'] = $best;
			}
		} catch (Exception $e) {
			$data['best_sellers_last_month'] = [];
		}

		// Homepage: Hot New Arrivals (products added in last 30 days, status=1)
		$data['hot_new_arrivals_last_30_days'] = [];
		try {
			$since_new = date('Y-m-d H:i:s', strtotime('-30 days'));
			$new_rows = $this->db
				->select('p.id, p.name, p.date_added, p.product_type, p.vendor_id')
				->select('COALESCE(rt.avg_rating, 0) AS avg_rating, COALESCE(rt.review_count, 0) AS review_count', false)
				->from('products p')
				->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left')
				->join('(SELECT product_id, AVG(rating) AS avg_rating, COUNT(review_rating_id) AS review_count FROM ec_review_rating WHERE status = \'1\' GROUP BY product_id) rt', 'rt.product_id = p.id', 'left', false)
				->where('p.status', '1')
				->where('p.date_added >=', $since_new)
				->group_start()
					->where('p.vendor_id', 0)
					->or_where('p.vendor_id IS NULL', null, false)
					->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
				->group_end()
				->order_by('p.date_added', 'DESC')
				->limit(12)
				->get()->result();

			if (is_array($new_rows) && $new_rows) {
				foreach ($new_rows as $p) {
					if (!is_object($p) || empty($p->id)) continue;
					$pid = (int)$p->id;
					$p->image_url = base_url('assets/default_images/product.jpg');
					$img = $this->db
						->select('product_variation_images.image_path')
						->from('product_variations')
						->join('product_variation_images', 'product_variation_images.variation_id = product_variations.id')
						->where('product_variations.product_id', $pid)
						->order_by('product_variation_images.id', 'ASC')
						->limit(1)
						->get()->row();
					if ($img && !empty($img->image_path)) {
						$p->image_url = $this->_normalize_url_scheme(base_url('uploads/products/' . (string)$img->image_path));
					}

					$p->display_price = 0;
					$unit_price_row = $this->db
						->select('pvp.price')
						->from('product_variation_price pvp')
						->join('product_variations pv', 'pv.id = pvp.variation_id')
						->where('pv.product_id', $pid)
						->order_by('CASE WHEN pvp.min_qty IS NULL THEN 2 WHEN pvp.min_qty <= 1 THEN 0 ELSE 1 END, pvp.min_qty ASC', '', false)
						->limit(1)
						->get()->row();
					if ($unit_price_row && isset($unit_price_row->price)) {
						$p->display_price = (float)$unit_price_row->price;
					}
				}
				$data['hot_new_arrivals_last_30_days'] = $new_rows;
			}
		} catch (Exception $e) {
			$data['hot_new_arrivals_last_30_days'] = [];
		}

		// Homepage: Random products (active only)
		$data['homepage_random_products'] = [];
		try {
			$trending_limit = 0;
			$w = $this->db
				->select('active_limit')
				->from('ec_homepage_widget_settings')
				->where('widget_id', 2)
				->limit(1)
				->get()->row();
			if ($w && isset($w->active_limit)) {
				$trending_limit = (int)$w->active_limit;
			}
			if ($trending_limit <= 0) {
				$trending_limit = (int)$this->config->item('HOMEPAGE_TRENDING_PRODUCTS_LIMIT');
			}
			if ($trending_limit <= 0) {
				$trending_limit = 12;
			}
			if ($trending_limit > 50) {
				$trending_limit = 50;
			}
			$data['homepage_trending_limit'] = $trending_limit;

			$rand_rows = $this->db
				->select('p.id, p.name, p.product_type, p.vendor_id')
				->select('COALESCE(rt.avg_rating, 0) AS avg_rating, COALESCE(rt.review_count, 0) AS review_count', false)
				->from('products p')
				->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left')
				->join('(SELECT product_id, AVG(rating) AS avg_rating, COUNT(review_rating_id) AS review_count FROM ec_review_rating WHERE status = \'1\' GROUP BY product_id) rt', 'rt.product_id = p.id', 'left', false)
				->where('p.status', '1')
				->group_start()
					->where('p.vendor_id', 0)
					->or_where('p.vendor_id IS NULL', null, false)
					->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
				->group_end()
				->order_by('RAND()', '', false)
				->limit($trending_limit)
				->get()->result();

			if (is_array($rand_rows) && $rand_rows) {
				foreach ($rand_rows as $p) {
					if (!is_object($p) || empty($p->id)) continue;
					$pid = (int)$p->id;
					$p->image_url = base_url('assets/default_images/product.jpg');
					$img = $this->db
						->select('product_variation_images.image_path')
						->from('product_variations')
						->join('product_variation_images', 'product_variation_images.variation_id = product_variations.id')
						->where('product_variations.product_id', $pid)
						->order_by('product_variation_images.id', 'ASC')
						->limit(1)
						->get()->row();
					if ($img && !empty($img->image_path)) {
						$p->image_url = $this->_normalize_url_scheme(base_url('uploads/products/' . (string)$img->image_path));
					}

					$p->display_price = 0;
					$unit_price_row = $this->db
						->select('pvp.price')
						->from('product_variation_price pvp')
						->join('product_variations pv', 'pv.id = pvp.variation_id')
						->where('pv.product_id', $pid)
						->order_by('CASE WHEN pvp.min_qty IS NULL THEN 2 WHEN pvp.min_qty <= 1 THEN 0 ELSE 1 END, pvp.min_qty ASC', '', false)
						->limit(1)
						->get()->row();
					if ($unit_price_row && isset($unit_price_row->price)) {
						$p->display_price = (float)$unit_price_row->price;
					}
				}
				$data['homepage_random_products'] = $rand_rows;
			}
		} catch (Exception $e) {
			$data['homepage_random_products'] = [];
		}
        

        $modifiers           = $this->Query_model->get_data('ec_modifiers', ['status'=>'1']);        
        $data['modifiers']   = array_column($modifiers, null, 'id');

        $extras              = $this->Query_model->get_data('ec_extras', ['status'=>'1']);
        $data['extras']      = array_column($extras, null, 'id');
        $cart_info = $this->Cart_model->cart_items();
        $data['symbol'] = '$';
        $data['cart_items'] = $cart_info['cart_items'];
        $data['cart_summary'] = $cart_info['cart_summary'];
        
        // Add cart count and total for header display
        $data['cart_count'] = count($cart_info['cart_items']);
        $data['cart_total'] = $cart_info['cart_summary']->total;
        // Load advertisements
        $this->load->model('advertisement_model');
        $data['advertisements'] = $this->advertisement_model->get_all_ads();
        // ===========================================================
        // RENDER-TIME VENDOR APPROVAL VERIFICATION (runs on EVERY request)
        // Final safety net: strip any products whose vendor is NOT approved
        // before the view renders, regardless of how they arrived here.
        // - vendor_id = 0 or NULL => admin product, always show.
        // - vendor_id set => vendor must have status = 1 / 'approved' / 'Approved'.
        // ===========================================================
        $vendor_status_cache = [];
        $approved_vendor_filter = function($product_list) use (&$vendor_status_cache) {
            if (!is_array($product_list)) return [];
            $filtered = [];
            foreach ($product_list as $p) {
                if (!is_object($p)) { $filtered[] = $p; continue; }
                $vid = isset($p->vendor_id) ? (int)$p->vendor_id : 0;
                if ($vid === 0) { $filtered[] = $p; continue; }   // admin product: always show
                if (!array_key_exists($vid, $vendor_status_cache)) {
                    $v = $this->db->select('status')->from('ec_vendor')->where('vendor_id', $vid)->limit(1)->get()->row();
                    if (!$v) {
                        $vendor_status_cache[$vid] = false;  // vendor not found → hide
                    } else {
                        $s = (string)$v->status;
                        $vendor_status_cache[$vid] = ($s === '1' || strtolower($s) === 'approved');
                    }
                }
                if ($vendor_status_cache[$vid]) { $filtered[] = $p; }
            }
            return $filtered;
        };

        // Apply filter to each product list
        if (isset($data['products']) && is_array($data['products'])) {
            $data['products'] = $approved_vendor_filter($data['products']);
        }
        if (isset($data['best_sellers_last_month']) && is_array($data['best_sellers_last_month'])) {
            $data['best_sellers_last_month'] = $approved_vendor_filter($data['best_sellers_last_month']);
        }
        if (isset($data['hot_new_arrivals_last_30_days']) && is_array($data['hot_new_arrivals_last_30_days'])) {
            $data['hot_new_arrivals_last_30_days'] = $approved_vendor_filter($data['hot_new_arrivals_last_30_days']);
        }
        if (isset($data['homepage_random_products']) && is_array($data['homepage_random_products'])) {
            $data['homepage_random_products'] = $approved_vendor_filter($data['homepage_random_products']);
        }
        // Filter products within each shop-by-category bucket
        if (isset($data['shop_by_category_products']) && is_array($data['shop_by_category_products'])) {
            foreach ($data['shop_by_category_products'] as &$bucket) {
                if (is_object($bucket) && isset($bucket->products) && is_array($bucket->products)) {
                    $bucket->products = $approved_vendor_filter($bucket->products);
                } elseif (is_array($bucket) && isset($bucket['products']) && is_array($bucket['products'])) {
                    $bucket['products'] = $approved_vendor_filter($bucket['products']);
                }
            }
            unset($bucket);
        }
        // ===========================================================

        $view_path = (!empty($this->TYPE)) ? "$this->TYPE/index" : 'customer/index'; 

        $this->load->template($view_path, $data);
    }

	public function home_page()
	{
		$api_data = [];
		$api_data['categories'] = $this->category();
		$api_data['home_page_product'] = $this->Item_model->get_home_products(product_category());
		$api_data['new_product'] = $this->Item_model->get_new_added_products();
		$api_data['randum_product'] = $this->Item_model->get_random_products();
		api_response(['status'=>1, 'msg'=>'Detail', 'data'=>$api_data]);
	}

	public function category()
	{
		$parent_cat = $this->Product_model->category_item_exist();
		$data = array_map(function($parent_cat) {
        	$parent_cat->full_path_image = !empty($parent_cat->thumbnail) 
            	? base_url("/assets/categories/".$parent_cat->thumbnail) 
            	: null;
        	return $parent_cat;
    	}, $parent_cat);

    	return $data;
	}


	public function category_old()
	{
		$parent_cat = $this->Query_model->get_data('ec_categories_prod', ['parent_id'=>'0', 'status'=>'1']);	
		
		$data = array_map(function($parent_cat) {
    		$parent_cat->full_path_image = !empty($parent_cat->thumbnail) ? base_url("/assets/categories/".$parent_cat->thumbnail) : null;
    		return $parent_cat;
		}, $parent_cat);
		return $data;
	}

	public function subscription()
	{
		$email = $this->input->post('subscribe_email');
		$email = is_string($email) ? trim($email) : '';
		if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)){
			api_response(['status'=>0, 'msg'=>'Invalid email address.', 'data'=>[]]);
		}

		$ip_address = $this->_client_ip_address();

		$is_exist = $this->Query_model->get_data_obj('ec_subscribe', ['email'=>$email]);
		if($is_exist){
			api_response(['status'=>1, 'msg'=>'Already Subscribed.', 'data'=>[]]);
		}else{
			$insert_id = $this->Query_model->insert_data('ec_subscribe', [
				'email' => $email,
				'ip_address' => $ip_address,
				'status' => '1',
				'date_added' => date('Y-m-d H:i:s')
			]);
			if($insert_id){
				api_response(['status'=>1, 'msg'=>'Thank you for subscribe.', 'data'=>[]]);
			}else{
				api_response(['status'=>0, 'msg'=>'Subscription failed. Please try again.', 'data'=>[]]);
			}
		}
		
	}

	private function _client_ip_address()
	{
		$xff = $this->input->server('HTTP_X_FORWARDED_FOR');
		if (is_string($xff) && trim($xff) !== '') {
			$parts = explode(',', $xff);
			if (is_array($parts) && isset($parts[0])) {
				$first = trim($parts[0]);
				if ($first !== '' && filter_var($first, FILTER_VALIDATE_IP)) {
					return $first;
				}
			}
		}

		$client_ip = $this->input->server('HTTP_CLIENT_IP');
		if (is_string($client_ip) && trim($client_ip) !== '' && filter_var(trim($client_ip), FILTER_VALIDATE_IP)) {
			return trim($client_ip);
		}

		$remote_addr = $this->input->server('REMOTE_ADDR');
		if (is_string($remote_addr) && trim($remote_addr) !== '' && filter_var(trim($remote_addr), FILTER_VALIDATE_IP)) {
			return trim($remote_addr);
		}

		$ip = $this->input->ip_address();
		return is_string($ip) ? $ip : '';
	}

///////////END NEW CODE//////////

    public function categories_list()
    {
        $catdata	= $this->categories();
        if($catdata)
        {
            api_response(array('status' => '1', 'msg' => 'Success', 'data' => $catdata));
        }
        else 
        {
            api_response(array('status' => '0', 'msg' => 'Failed', 'data' => array()));
        }

    }

    public function categories()
    {
        $data	=	array();
        $catdata =   $this->Query_model->get_data('ec_categories_prod', array('status'=>'1'));
        foreach($catdata as $catdata_list)
        {
            $catdata_list->thumbnail = base_url().'assets/categories/'.$catdata_list->thumbnail;
            $catdata_list->banner_image = base_url().'assets/categories/'.$catdata_list->banner_image;
            $data[] =   $catdata_list;
        }

        return $data;

    }

    public function categories_all_strip_old() // ITS MOVE TO PRODUCT MODEL SECTION
    {
        $data    =   array();
        $catdata =   $this->Query_model->get_data('ec_categories_prod');
        $cnt=0;
        foreach($catdata as $catdata_list)
        {
            $cnt++;
            $data['all_cat'][] =   $catdata_list;
            if($cnt <=5)
            {
                $data['five_cat'][] = $catdata_list;
            }
            else if($cnt > 5 || $cnt <= 13)
            {
                $data['five_greater'][] = $catdata_list;	
            }

        }
        return $data;
    }

    public function banner_list()
    {
        $data    = $this->banner();
        if($data)
        {   
            api_response(array('status' => '1', 'msg' => 'Success', 'data' => $data));
        }
        else
        {   
            api_response(array('status' => '0', 'msg' => 'Failed', 'data' => array()));
        }

    }

    public function banner()
    {
        $data	=	array();
        $bannerdata = $this->Query_model->get_data('ec_banner', array('enabled'=>'1'));
        
        foreach($bannerdata as $banner_list)
        {   
                $banner_arr =$banner_list->banner_image ? unserialize($banner_list->banner_image) : [];
                if(isset($banner_arr['large'])){
                    $banner_list->banner_image = base_url().'assets/home_page_banner/'.$banner_arr['thumb'];
                    $banner_list->banner_large_image = base_url().'assets/home_page_banner/'.$banner_arr['large'];
                    $data[] =   $banner_list;
                }
        }
        return $data;
    }

    public function index_old()
    {
        $data = array();
        $data				= $this->index_data_for_both();
        $data['csrf'] 		= csrf_token();
        $data['homepage'] 	= '1';

        $this->load->template("$this->TYPE/index", $data);					
    }

    public function index_data_for_both()
    {
        $data['banner']     		= $this->banner();
#$data['categories']     	= $this->categories();
        $data['cate_all_strip']		= $this->Product_model->categories_all_strip();
        $session_lat_long 			= $this->session_lat_long();
        $data['get_geo_loc'] 		= isset($session_lat_long['get_geo_loc']) ? $session_lat_long['get_geo_loc'] : '';
        $data['session_lat_long'] 	= isset($session_lat_long['set_lat_long_sess']) ? $session_lat_long['set_lat_long_sess'] : '';
        $data['store_near_by'] 		= $this->store_near_by($data);
        $data['electric_category'] 	= $this->product_electric_category();
        $data['top_offers'] 		= $this->top_offers();
        $data['all_products'] 		= $this->all_products();

        return $data;
    }

    public function session_lat_long()
    {
        $data = array();
        if($this->session->userdata('set_lat_long'))
        {
            $set_lat_long_sess = $this->session->userdata('set_lat_long');
            $get_geo_loc = get_geo_loc(array('lat'=>$set_lat_long_sess['lat'], 'long'=>$set_lat_long_sess['long']));
            $data['get_geo_loc']     	= $get_geo_loc;
            $data['set_lat_long_sess']	= $set_lat_long_sess;
        }
        return $data;
    }

    public function index_api()
    {
        if(empty($_POST['api'])){return false;}
        wfile($_POST);
        $data 				= array();
        $this->location_validation();
        if($this->form_validation->run() == FALSE)
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

        $_POST['latitude']  = $_POST['lat'];
        $_POST['longitude'] = $_POST['long'];
        $_POST['place_id']  = $_POST['place_id'];

        if(is_api())
            $this->getLocation();

        $data 				= $this->index_data_for_both();
        $data['categories'] = $this->categories();

        api_response(array('status'=>1, 'msg'=>'Index Data.', 'data'=>$data));
    }

    public function location_validation(){
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('lat', 'Location is required', 'trim|required');
        $this->form_validation->set_rules('long', 'Location is required', 'trim|required');
    }


    public function getLocation()
    {
        #$lat = '28.653228'; //$this->input->post('latitude');
        #$long = '77.4442163'; //$this->input->post('longitude');
        $lat = $this->input->post('latitude');
        $long = $this->input->post('longitude');
        $place_id = $this->input->post('place_id');

        $ret_data = [];
        $ret_data['lat']        = $lat;
        $ret_data['long']       = $long;
        $ret_data['place_id']   = $place_id;

        $this->session->set_userdata('set_lat_long', $ret_data);
        if($place_id)
            $address_by_place_id = address_by_place_id(array('lat'=>$lat, 'long'=>$long, 'place_id'=>$place_id));
        else
            $address_by_place_id = get_geo_loc(array('lat'=>$lat, 'long'=>$long));
       
        $country = isset($address_by_place_id['country']) ? $address_by_place_id['country'] : NULL; 
        $currency_detail = $this->Currency_model->get_currency_detail($country);
        $address_by_place_id['country_id'] = $currency_detail->country_id;
        $this->session->set_userdata('geo_loc', $address_by_place_id);
        $this->session->set_userdata('cur', $currency_detail->currency_id); 
        if($address_by_place_id)
        {
            $api_data = $address_by_place_id;
            if(is_api())
                return $api_data;
            else
                api_response(array('status'=>1, 'msg'=>'location address', 'data'=>$api_data));
        }
    }

    public function search_loc_bkp()
    {
        $query = isset($_GET['query']) ? urlencode($_GET['query']) : '';

        $set_lat_long_sess = $this->session->userdata('set_lat_long') ? $this->session->userdata('set_lat_long') : '';
        $lat = isset($set_lat_long_sess['lat']) ? $set_lat_long_sess['lat'] : '';
        $long = isset($set_lat_long_sess['long']) ? $set_lat_long_sess['long'] : '';


#$url ="https://maps.googleapis.com/maps/api/place/textsearch/json?query=$query&location=$lat,$long&radius=50000&key=AIzaSyDkYJmQ8bD4bQBd16J3eNnkXZAaipg5a6c";
#$url ="https://maps.googleapis.com/maps/api/place/textsearch/json?query=$query&key=AIzaSyDkYJmQ8bD4bQBd16J3eNnkXZAaipg5a6c";
        $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=$query&location=$lat,$long&key=$this->G_KEY";
#echo "$url===";	
        $json = @file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if($status == "OK")
        {
            $results  = $data->results;
            $all_locs = array();
            if($results)
            {
                foreach($results as $row_wise)
                {
                    $lat	= $row_wise->geometry->location->lat;
                    $long 	= $row_wise->geometry->location->lng;
                    $all_locs[] = array('name'=>$row_wise->formatted_address, 'lat'=>$lat, 'long'=>$long);
                }
            }
        }

        echo json_encode($all_locs);

    }

    public function store_near_by($args)
    {
        $lat = isset($args['session_lat_long']['lat']) ? $args['session_lat_long']['lat'] : ''; 
        $long = isset($args['session_lat_long']['long']) ? $args['session_lat_long']['long'] : ''; 
        $distance = '50';
        //$args_locator = array('lat'=>$lat, 'long'=>$long, 'distance'=>$distance, 'limit'=>5);
        $args_locator = array('lat'=>$lat, 'long'=>$long, 'distance'=>$distance);
        $vendor_data = $this->Query_model->get_locator($args_locator);
        //echo "<pre>"; print_r($vendor_data); echo "</pre>";
        if($vendor_data)
        {
            //if(is_api())
            if(1)	
            {
                $vendor_id_arr = array();
                foreach($vendor_data as $row)
                {
                    $row->vendor_img = $row->vendor_img ?? '';
                    $vendor_img = $row->vendor_img ? unserialize($row->vendor_img) : [];

                    $address_modify 		= $row->address;
                    $address_modify 		= strlen($address_modify) > 30 ? substr($address_modify,0,30)."..." : $address_modify;
                    $row->vendor_id 		= $row->admin_id;
                    $row->address_modify 	= $address_modify;
                    $row->distance_modify 	= round($row->distance).' Km';
                    $row->vendor_img_modify = isset($vendor_img['thumb']) ? $vendor_img['thumb'] : base_url().'assets/images/store/s-1.png';
                    $row->slug_param    	= strtolower(strtok($row->store_name, " "));
                    $row->url 				= "/store/$row->vendor_uid/$row->slug_param";
                    $cat_dtl 				= $this->latest_cat_by_vendor_wise(array('vendor_id'=>$row->vendor_id));
                    $row->category_id 		= isset($cat_dtl->id) ? $cat_dtl->id : '';
                }
                return $vendor_data;
            }
            return $vendor_data;	
        }
        else
        {
            $vendor_data = $this->all_stores();
            return $vendor_data;
        }
    }

    public function all_stores($args=null)
    {
        $data         		= array();
        $data['page_count'] = page_count();		
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $vendor_data = $this->Query_model->get_data('ec_admin', array('role_id'=>'2', 'status'=>'1'));


        if($vendor_data)
        {
            foreach($vendor_data as $row)
            {
                $row->vendor_img = $row->vendor_img ?? '';
                $vendor_img = $row->vendor_img ? unserialize($row->vendor_img) : [];

                $address_modify         = $row->address;
                $address_modify         = strlen($address_modify) > 30 ? substr($address_modify,0,30)."..." : $address_modify;
                $row->vendor_id         = $row->admin_id;
                $row->address_modify    = $address_modify;
                $row->distance_modify   = ''; 
                $row->vendor_img_modify = isset($vendor_img['thumb']) ? $vendor_img['thumb'] : base_url().'assets/images/store/s-1.png';
                $row->slug_param        = strtolower(strtok($row->store_name, " "));
                $row->url               = "/store/$row->admin_uid/$row->slug_param";
                $cat_dtl                = $this->latest_cat_by_vendor_wise(array('vendor_id'=>$row->vendor_id));
                $row->category_id       = isset($cat_dtl->id) ? $cat_dtl->id : '';   
            } 
        }
        return $vendor_data;
    }


    public function get_ajax_stores()
    {
        $data         		= array();
        $data['page_count'] = page_count();		

        $length         = 9; $_POST['length'] = page_count();
        $page           = (isset( $_POST['page']))?$_POST['page']: 1;
        $start          = ($page-1) * $length; 

        $vendor_data = $this->Query_model->get_data('ec_admin', array('role_id'=>'2', 'status'=>'1'), '', array('start'=>$start, 'length'=>$length));


        if($vendor_data)
        {
            foreach($vendor_data as $row)
            {
                $vendor_img = $row->vendor_img ? unserialize($row->vendor_img) : [];

                $address_modify         = $row->address;
                $address_modify         = strlen($address_modify) > 30 ? substr($address_modify,0,30)."..." : $address_modify;
                $row->vendor_id         = $row->admin_id;
                $row->address_modify    = $address_modify;
                $row->distance_modify   = ''; 
                $row->vendor_img_modify = isset($vendor_img['thumb']) ? $vendor_img['thumb'] : base_url().'assets/images/store/s-1.png';
                $row->slug_param        = strtolower(strtok($row->store_name, " "));
                $row->url               = "/store/$row->admin_uid/$row->slug_param";
                $cat_dtl                = $this->latest_cat_by_vendor_wise(array('vendor_id'=>$row->vendor_id));
                $row->category_id       = isset($cat_dtl->id) ? $cat_dtl->id : '';   
            } 
        }

        $this->load->library('pager');
        $recordsTotal = $this->Query_model->count_all('ec_admin', array('role_id'=>'2', 'status'=>'1'));
        $pagination = $this->pager->showLinks('', $page, $recordsTotal, $length);

        $output = array(
                "draw"              => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal"      => $recordsTotal,
                "pagination"        => $pagination,
                "result"            => $vendor_data
                );

        $response = array('status' => 1, 'message' => 'success', 'data' => $output);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));

        // api_response(array('status'=>1, 'msg'=>'Success', 'data'=>$vendor_data));
        //return $vendor_data;
    }

    public function product_electric_category()
    {
        $category_id = 5;
        $result_obj   = $this->Product_model->product_by_single_category($category_id);

        if($result_obj)
        {
            $prod_id_arr = array();
            foreach($result_obj as $prod_id)
            {
                $prod_id_arr[] = $prod_id->product_id;
            }
#$product_id = $result_obj[0]->product_id;
            $prod_image = $this->prod_image(array('product_id'=>$prod_id_arr));
            $product_obj = $this->Query_model->get_data('products',array('product_id' => $prod_id_arr, 'enabled' => '1'));
            if($product_obj)
            {
                foreach($product_obj as $row_wise)
                {
                    $row_wise->image = isset($prod_image[$row_wise->product_id]) ? base_url().$prod_image[$row_wise->product_id] : base_url().'assets/images/no-image.png';
                }
                return [$product_obj,$prod_image];
            }
            return [];
        }
        return [];
    }

    public function top_offers()
    {
        $coupon_obj = $this->Coupon_model->coupon_data_for_home();
        //echo "<pre>"; print_r($coupon_obj); echo "</pre>";die;
        if(is_api())
        {   
            $get_currency = get_currency();
            $getbrand     = get_brand();
            //$getcategories = categories_name();
            $offerctype    = offer_ctype();

            if($coupon_obj){
                $data = array(); $row_data   = array(); $row = array();
                foreach($coupon_obj as $coupondata)
                {
                    if($coupondata->category_id)
                    {
                        $getcategories = categories_name($coupondata->category_id);

                        $row['top_offer_tag'] = isset($coupondata->category_id) ? $getcategories->name : '';
                        $row['top_offer_img'] = isset($coupondata->category_id) ? base_url().'assets/categories/'.$getcategories->thumbnail : base_url().'assets/images/no-image.png'; 
                    }
                    elseif($coupondata->product_id)
                    {
                        
                        $product_id = json_decode(json_decode($coupondata->product_id));
                       if (is_array($product_id) || is_object($product_id))
                        {
                        foreach($product_id as $productid)
                        {
                            $product_id = $productid;
                            $post_title = get_product($product_id);
                            $file_name  = get_product_image($product_id);
                            $title = isset($coupondata->product_id) && strlen($post_title->post_title) > 10 ? substr($post_title->post_title,0,10)."..." : $post_title->post_title;
                            $row['top_offer_tag'] = $title;
                            if($post_title->type == 'variable'){
                                $variable_img   =   ec_product_variation($product_id);
                                $row['top_offer_img'] = isset($coupondata->product_id) ? base_url().'assets/uploads/'.$variable_img->_thumbnail_id : base_url().'assets/images/no-image.png';
                            }else{
                                $row['top_offer_img'] = isset($coupondata->product_id) ? base_url().'assets/uploads/'.$file_name->file_name : base_url().'assets/images/no-image.png';

                            }   
                            $row_data[] = $row;
                        }
                        }
                        //$row['top_offer_tag'] = isset($coupondata->product_id) ? $post_title->post_title : '';
                        // $row['top_offer_img'] = isset($coupondata->product_id) ? base_url().'assets/uploads/'.get_product_image[$coupondata->product_id]->file_name : base_url().'assets/images/no-image.png';
                    }
                    elseif($coupondata->brand_id)
                    {
                        $row['top_offer_tag'] = isset($coupondata->brand_id) ? getbrand($coupondata->brand_id)->name : '';
                        $row['top_offer_img'] = isset($coupondata->brand_id) ? base_url().'assets/brand/'.getbrand($coupondata->brand_id)->image : base_url().'assets/images/no-image.png';
                    }

                    $row['coupon_name']  = $coupondata->name;  
                    $row['dis_type']     = $coupondata->type == 1 ? 'Flat' : '%';  
                    $row['symbol_add']   = $coupondata->type == 1 ? $get_currency[0]->symbol : '';  
                    $row['discount']     = $row['symbol_add'].' '.$coupondata->discount.' '.$row['dis_type'];  
                    // $row['prod_image']   = base_url().'/assets/images/no-image.png';  
                    // $row['getbrand']     = isset($coupondata->brand_id) ? getbrand($coupondata->brand_id)->name : '';    
                    //$data[] = $row_data; 
                    $data[] = $row;
                }
                $all_data = array_merge($data, $row_data); 
                return $all_data;
            }

            return []; 

        }else{
            //print_r($coupon_obj); exit;
            if($coupon_obj)
                return $coupon_obj;
            else
                return [];
        }
    }

    public function prod_image($args)
    {
        $product_id = $args['product_id'];
        $gallery_arr = $this->Query_model->get_data('ec_gallery', array('product_id'=>$product_id));
        $gallery_arr_hash = [];
        if($gallery_arr)
        {
            foreach($gallery_arr as $gall_row)
            {
                $gallery_arr_hash[$gall_row->product_id] = '/assets/uploads/files/'.$gall_row->file_name;		
            }
        }
        return $gallery_arr_hash;
    }		

    public function all_products()
    {
        $product_obj = $this->Query_model->get_data('products', array('enabled' => '1'), array('product_id','DESC'), array('length'=>10));
        $prod_id_arr = []; $all_prod=[]; $prod_images=''; $prod_variation=''; $api_all_data=[];
        if($product_obj)
        {
            foreach($product_obj as $row)
            {
                $prod_id_arr[] = $row->product_id;
                $all_prod[] = $row;
            }
            $prod_images = $this->prod_image(array('product_id'=>$prod_id_arr));
            $prod_variation = $this->get_product_variation(array('product_id'=>$prod_id_arr));

            $api_data = []; $api_all_data = [];
            foreach($product_obj as $row_wise)
            {
                $api_data['type']           = $row_wise->type;
                $api_data['post_title']     = $row_wise->post_title;
                $api_data['product_id']     = $row_wise->product_id;
                $api_data['sale_price']     = $row_wise->sale_price;
                $api_data['regular_price']  = $row_wise->regular_price;
                $api_data['discount']       = check_discount(array('sale_price_dates_from'=>$row_wise->sale_price_dates_from, 'sale_price_dates_to'=>$row_wise->sale_price_dates_to));
                $api_data['image']          = '';

                if($row_wise->type == 'variable')
                {
                    $api_data['regular_price']  = isset($prod_variation[$row_wise->product_id]) ? $prod_variation[$row_wise->product_id]['regular_price'] : 0;
                    $api_data['sale_price']     = isset($prod_variation[$row_wise->product_id]) ? $prod_variation[$row_wise->product_id]['sale_price'] : 0;

                    $api_data['image'] = isset($prod_variation[$row_wise->product_id]) ? base_url().$prod_variation[$row_wise->product_id]['img_path'] : '';
                }
                else if($row_wise->type == 'simple')
                {
                    $api_data['image'] = isset($prod_images[$row_wise->product_id]) ? base_url().$prod_images[$row_wise->product_id] : base_url().'assets/images/no-image.png';
                } 

                $api_all_data[] = $api_data;
            }

        }
        $ret_data = array();
        $ret_data['all_prod']	    = $all_prod;
        $ret_data['prod_images']    = $prod_images;
        $ret_data['prod_variation'] = $prod_variation;
        $ret_data['api_data']       = $api_all_data;

        return $ret_data;	
    }


    public function get_product_variation($product_id = NULL)
    {
        $gallery_arr = $this->Query_model->get_data_group_by(array('table_name'=>'ec_product_variation', 'fields'=> 'product_id, _thumbnail_id, _regular_price, _sale_price', 'condition'=> $product_id, 'group_by'=> 'product_id'));
        $gallery_arr_hash = [];
        if($gallery_arr)
        {
            foreach($gallery_arr as $gall_row)
            {
                $gallery_arr_hash[$gall_row->product_id] = array('img_path'=>'/assets/uploads/'.$gall_row->_thumbnail_id, 'regular_price'=> intval($gall_row->_regular_price), 'sale_price'=> intval($gall_row->_sale_price));
            }
        }
        return $gallery_arr_hash;

    }


    public function ajax_add_to_wishlist()
    {
        if(logged_in()){
            $product_id   = $this->input->post('product_id');
            $attribute_item     = $this->input->post('attribute_item');
            $customer_id  = $this->LOGIN_ID;
            $wishlist_obj = $this->Query_model->get_data_obj('ec_wishlist', array('product_id' => $product_id, 'customer_id' => $customer_id));
            if($wishlist_obj)
            {
                $delete_data = $this->Query_model->delete_query('ec_wishlist', array('product_id' => $product_id, 'customer_id' => $customer_id));

                if($delete_data){
                    $response = array(
                            'status'    => '0',
                            'message'   => 'Product removed successfully in wishlist',
                            );
                }	
            }else{
                $attribute_items = array();
                $attribute_item_id = NULL;
                if($attribute_item){
                    $attribute_items = json_decode($attribute_item);
                    if($attribute_items){
                        sort($attribute_items);
                    }
                    $attribute_item_id = implode(',',$attribute_items);
                }
                $post_data = array(
                        'customer_id'       => $customer_id,
                        'product_id'        => $product_id,
                        'attribute_item_id' => $attribute_item_id,
                        );

                //echo "<pre>"; print_r($post_data); echo "</pre>";die;

                $last_inserted_id = $this->Query_model->insert_data('ec_wishlist',$post_data);
                if($last_inserted_id)
                {
                    $response = array(
                            'status'    => '1',
                            'message'   => 'Product add successfully in wishlist',
                            );
                }else{
                    $response = array(
                            'status'    => '0',
                            'message'   => 'Product not add successfully in wishlist',
                            );
                }
            }
        }else{
            $response = array(
                    'status'    => '0',
                    'message'   => 'Please Login',
                    );
        }
        $response['data'] = array();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function stores()
    {
        $data   = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['homepage'] = 1;
        $session_lat_long 			= $this->session_lat_long();
        $data['get_geo_loc'] 		= isset($session_lat_long['get_geo_loc']) ? $session_lat_long['get_geo_loc'] : '';
        $data['session_lat_long'] 	= isset($session_lat_long['set_lat_long_sess']) ? $session_lat_long['set_lat_long_sess'] : '';
        $data['store_list'] 		= $this->all_stores();
        //$data['store_list'] 		= $this->store_near_by($data);
        //echo "<pre>"; print_r($data['store_list']); echo "<pre>";die;
        $data['cate_all_strip']  = $this->Product_model->categories_all_strip();
        $categories = '';	

        //api_response(array('status'=>1, 'msg'=>'Index Data.', 'data'=>$data));

        $this->load->template("$this->TYPE/store_list", $data);

    }

    public function latest_cat_by_vendor_wise($args)
    {
        $vendor_cats = $this->Query_model->get_data_obj('ec_vendor_categories', array('vendor_id'=>$args['vendor_id'], 'is_parent'=>'1'), array('date_added'=>'DESC'), array('length'=>'1'));
        $cat_name = (object)[];
        $cat_name->slug = ''; $cat_name->id = ''; 
        if(isset($vendor_cats->id))
        {
            $cat_name = $this->Query_model->get_data_obj('ec_categories_prod', array('id'=>$vendor_cats->id, 'parent_id'=>'0', 'status'=>'1'));
            return $cat_name;
        }
        return $cat_name;
    }	


    public function ajax_coupon_list()
    {
        $data = array();
        $condition   = array('status' => '1'); 
        $coupon_data = $this->Query_model->get_data('ec_coupon', $condition);
        //echo "<pre>"; print_r($coupon_data); echo "</pre>"; die;

        $brand 		 = get_brand();
        $categories  = getcategories();

        foreach($coupon_data as $couponlist)
        {
            $row = array();

            $row['name'] 			= $couponlist->name;		
            $row['discount'] 		= $couponlist->discount;		
            $row['brand_name'] 		= $brand[$couponlist->brand_id]->name;		
            $row['brand_image'] 	= isset($brand[$couponlist->brand_id]->image) ? base_url().'assets/brand/'.$brand[$couponlist->brand_id]->image : base_url().'assets/images/no-image.png';		
            $row['category_name'] 	= $categories[$couponlist->category_id]->name;	
            $row['category_image'] 	= isset($categories[$couponlist->category_id]->thumbnail) ? base_url().'assets/categories/'.$categories[$couponlist->category_id]->thumbnail : base_url().'assets/images/no-image.png';	
            $data[] = $row;

        }


        if(is_api())
        {
            api_response(array('status'=>1, 'msg'=>'Success', 'data'=>$data));
        }else
        {
            api_response(array('status'  => 0, 'msg' => 'Fail', 'data'    => array()));
        }

    }



}

?>
