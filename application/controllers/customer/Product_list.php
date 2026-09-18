<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Product_list extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
		$this->load->model('Product_model');
		$this->load->model('Cart_model');
		$this->load->model('Attribute_model');
		$this->load->model('Search_model');
        $this->load->helper('text');		
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
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

	public function index($arg=null) 
    {
		$data         = array();
		$data['page_count'] = page_count();		
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
		
		$data['page_lang'] = get_page_language_data('product_list_lang');
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $crumbs = array($data['page_lang']->home => "/$this->TYPE/product/", $data['page_lang']->products => "");
        $breadcrumbs = $this->breadcrumbs->show_new($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
		$data['fts'] = $arg;					
		$cat_slug = trim((string)$this->input->get('cat'));
		$data['cat_slug'] = $cat_slug;
		$cat_row = null;
		if($cat_slug !== '' && $cat_slug !== 'AllCategory'){
			$cat_slug_norm = get_slug($cat_slug);
			$cat_name_norm = strtolower(str_replace('-', ' ', $cat_slug_norm));
			$cat_row = $this->db
				->select('id, name, slug')
				->from('ec_categories_prod')
				->group_start()
					->where('slug', $cat_slug)
					->or_where('slug', $cat_slug_norm)
					->or_where('name', $cat_slug)
					->or_where('LOWER(name) = '.$this->db->escape($cat_name_norm), null, false)
				->group_end()
				->order_by('id', 'DESC')
				->limit(1)
				->get()->row();
		}
		$data['cat'] = $cat_row ? (int)$cat_row->id : 0;
		$data['cat_image'] = 0;
		$data['homepage'] = 1;
        $this->load->template("$this->TYPE/product_list", $data);
    }

	private function _resolve_header_product_image_url($product)
	{
		$placeholder = $this->_normalize_url_scheme(base_url('assets/default_images/product.jpg'));
		$candidates_raw = array();

		if(isset($product->images) && $product->images){
			$imgs = json_decode($product->images, true);
			if(is_array($imgs)){
				foreach($imgs as $img){
					if(is_string($img) && $img !== ''){
						$candidates_raw[] = $img;
					}
				}
			}
		}

		if(!$candidates_raw && isset($product->prices) && $product->prices){
			$prices_obj = json_decode($product->prices);
			if(is_array($prices_obj) && isset($prices_obj[0]) && is_object($prices_obj[0]) && isset($prices_obj[0]->images) && is_array($prices_obj[0]->images)){
				foreach($prices_obj[0]->images as $img){
					if(is_string($img) && $img !== ''){
						$candidates_raw[] = $img;
					}
				}
			}
		}

		foreach($candidates_raw as $raw){
			$raw = (string)$raw;
			if(preg_match('#^https?://#i', $raw)){
				return $this->_normalize_url_scheme($raw);
			}

			$raw_rel = ltrim($raw, '/\\');
			if(@file_exists(FCPATH.$raw_rel)){
				return $this->_normalize_url_scheme(base_url($raw_rel));
			}

			$fn = $raw_rel;
			$try_fns = array($fn);
			if(strpos($fn, '_thumb.') !== false){
				$try_fns[] = preg_replace('/_thumb(\.[a-z0-9]+)$/i', '$1', $fn);
			}
			foreach($try_fns as $tfn){
				$paths = array(
					array('url' => $this->_normalize_url_scheme(base_url().'uploads/products/'.$tfn), 'path' => FCPATH.'uploads/products/'.$tfn),
					array('url' => $this->_normalize_url_scheme(base_url().'assets/uploads/files/'.$tfn), 'path' => FCPATH.'assets/uploads/files/'.$tfn),
					array('url' => $this->_normalize_url_scheme(base_url().'assets/uploads/'.$tfn), 'path' => FCPATH.'assets/uploads/'.$tfn),
				);
				foreach($paths as $p){
					if(isset($p['path']) && @file_exists($p['path'])){
						return $p['url'];
					}
				}
			}

			if(strpos($fn, 'uploads/') === 0 || strpos($fn, 'assets/') === 0){
				return $this->_normalize_url_scheme(base_url($fn));
			}
		}

		return $placeholder;
	}

    public function category($arg=null) 
    {
		$data         = array();
		$data['page_count'] = page_count();		
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
		
		$data['page_lang'] = get_page_language_data('product_list_lang');
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();

        $category_slug = get_slug($arg);
        $category_obj = $this->Query_model->get_data_obj('ec_categories_prod',array('slug' => $category_slug),array(),array('length' => 1));
        
		if($category_obj){
		    $data['cat'] = $category_obj->id;
			$data['cat_image'] = base_url().'assets/categories/'.$category_obj->banner_image;
            $data['fts'] = '';
            $cat_parent_obj  = $this->Product_model->get_all_parent($category_obj->id);
            $crumbs = array($data['page_lang']->home => "/$this->TYPE/product/");
            if($cat_parent_obj){
                foreach($cat_parent_obj as $cpo){
                    $crumbs[$cpo->name] = get_slug($cpo->name);
                }
            }
        }else{
            $data['fts'] = $arg;
            $data['cat'] = 0;
			$data['cat_image'] = 0;
            $crumbs = array($data['page_lang']->home => "/$this->TYPE/product/", $data['page_lang']->products => "");
        }		
        $breadcrumbs = $this->breadcrumbs->show_new($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['slug']    		= $category_slug;
		$data['cate_all_strip'] = $this->Product_model->categories_all_strip();
		$data['homepage'] = 1;
		$data['login_id'] = $this->LOGIN_ID;
        $this->load->template("$this->TYPE/product_list", $data);
    }


    public function ajax_search() 
    {
	//wfile($_POST);
		$show_categories = (int)$this->input->post('show_categories');
		$length 		= (int)$this->input->post('length');
		if ($length <= 0) {
			$length = page_count();
		}
		if ($length > 100) {
			$length = 100;
		}
		$_POST['length'] = $length;
        $page  			= (isset( $_POST['page']))?$_POST['page']: 1;

		#$_POST['category_id'] = isset($_POST['category_id']) ? $_POST['category_id'] : 4;
		$cat_image 		= $this->input->post('cat_image');
        $fts 			= $this->input->post('fts');
        $category_id 	= $this->input->post('category_id');
		$category_slug  = trim((string)$this->input->post('category_slug'));
		$slug_param		= $this->input->post('slug_param');
	    $sorting 		= (int)$this->input->post('sorting');
		$filter 		= $this->input->post('filter');
		$vendor_obj		= $this->input->post('vendor_obj');
		$vendor_categories = [];

		$filter_arr = $filter;
		if (is_string($filter_arr) && $filter_arr !== '') {
			$tmp = json_decode($filter_arr, true);
			if (is_array($tmp)) {
				$filter_arr = $tmp;
			}
		}
		if (!is_array($filter_arr)) {
			$filter_arr = [];
		}
		$filter = $filter_arr;
		// Normalize brand filter: JS posts filter.brand as JSON string of selected brand_ids
		if (isset($filter['brand']) && is_string($filter['brand']) && trim($filter['brand']) !== '') {
			$tmp_brand = json_decode($filter['brand'], true);
			if (is_array($tmp_brand)) {
				$filter['brand'] = $tmp_brand;
			}
		}
		$brand_ids = [];
		if (isset($filter['brand']) && is_array($filter['brand'])) {
			$brand_ids = array_values(array_unique(array_filter(array_map(function($v){ return (int)$v; }, $filter['brand']), function($v){ return $v > 0; })));
		}
		$price_from = null;
		$price_to = null;
		if (isset($filter['price_range']) && $filter['price_range'] !== '') {
			$pr = $filter['price_range'];
			if (is_string($pr)) {
				$tmp_pr = json_decode($pr, true);
				if (is_array($tmp_pr)) {
					$pr = $tmp_pr;
				}
			}
			if (is_array($pr)) {
				if (isset($pr['from']) && $pr['from'] !== '' && $pr['from'] !== null) {
					$price_from = (float)$pr['from'];
				}
				if (isset($pr['to']) && $pr['to'] !== '' && $pr['to'] !== null) {
					$price_to = (float)$pr['to'];
				}
			}
		}
		if (!(int)$category_id && isset($filter_arr['category']) && is_array($filter_arr['category']) && isset($filter_arr['category'][0])) {
			$filter_cat_id = (int)$filter_arr['category'][0];
			if ($filter_cat_id > 0) {
				$category_id = $filter_cat_id;
			}
		}
		if (!(int)$category_id && $category_slug !== '' && $category_slug !== 'AllCategory') {
			$cat_slug_norm = get_slug($category_slug);
			$cat_name_norm = strtolower(str_replace('-', ' ', $cat_slug_norm));
			$category_slug_valid = false;
			$cat_row = $this->db
				->select('id')
				->from('ec_categories_prod')
				->group_start()
					->where('slug', $category_slug)
					->or_where('slug', $cat_slug_norm)
					->or_where('name', $category_slug)
					->or_where('LOWER(name) = '.$this->db->escape($cat_name_norm), null, false)
				->group_end()
				->order_by('id', 'DESC')
				->limit(1)
				->get()->row();
			if ($cat_row && isset($cat_row->id)) {
				$category_id = (int)$cat_row->id;
				$category_slug_valid = true;
			}
			if (!$category_slug_valid) {
				$category_slug = '';
			}
		}

		// Prefer new products table for general listing/search (no category/vendor specific filters)
		$fts_trim = trim((string)$fts);
		$category_id_int = (int)$category_id;
		$has_vendor_ctx = false;
		if (is_array($vendor_obj) && (isset($vendor_obj['admin_id']) || isset($vendor_obj['admin_uid']))) {
			$has_vendor_ctx = true;
		} elseif (is_object($vendor_obj) && (isset($vendor_obj->admin_id) || isset($vendor_obj->admin_uid))) {
			$has_vendor_ctx = true;
		}

		$has_category_filter = isset($filter_arr['category']) && is_array($filter_arr['category']) && count($filter_arr['category']) > 0;
		$has_category_slug_ctx = ($category_slug !== '' && $category_slug !== 'AllCategory');
		$has_brand_filter = is_array($brand_ids) && count($brand_ids) > 0;
		if ($show_categories === 1 && $has_brand_filter) {
			// On All Category page, selecting a brand should switch to product results
			$show_categories = 0;
		}

		if ($show_categories === 1 && !$has_vendor_ctx && $category_id_int <= 0 && !$has_category_filter && !$has_category_slug_ctx) {
			$cats = $this->db
				->select('id, name, slug, thumbnail')
				->from('ec_categories_prod')
				->where('status', '1')
				->where('parent_id', '0')
				->order_by('id', 'DESC')
				->get()->result();
			$categories_list = [];
			if ($cats) {
				foreach ($cats as $c) {
					$image_url = base_url('assets/default_images/product.jpg');
					if (isset($c->thumbnail) && $c->thumbnail) {
						$image_url = base_url('assets/categories/' . $c->thumbnail);
					}
					$categories_list[] = [
						'id' => (int)$c->id,
						'name' => (string)$c->name,
						'slug' => (string)$c->slug,
						'url' => base_url('products/category/' . (string)$c->slug),
						'image_url' => $image_url,
					];
				}
			}

			$all_childs = getcategories();
			$buildtree_data = $this->buildtree($all_childs, array(), $vendor_obj, $parent_id = 0);
			$make_final_tree = $this->make_final_tree($buildtree_data, $vendor_obj, $root_parent = array());
			$breadcrumb = '<li class="breadcrumb-item"><a href="/">Home</a></li>';
			$breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">All Category</li>';
			$heading_name = 'All Category';
			$brand_data = $this->db
				->from('ec_brand')
				->where('status', '1')
				->order_by('name', 'ASC')
				->get()->result();
			$ln = current_language();
			if ($brand_data) {
				foreach ($brand_data as $br) {
					if ($ln == 12) {
						$br->name = ($br->name_es) ? $br->name_es : $br->name;
					}
					$br_id = isset($br->brand_id) ? (int)$br->brand_id : 0;
					$br->checked = ($br_id > 0 && in_array($br_id, $brand_ids)) ? 1 : 0;
				}
			}

			$output = array(
				"draw"              => isset($_POST['draw'])?$_POST['draw']:'',
				"recordsSummary"    => '',
				"recordsTotal"      => count($categories_list),
				"recordsFiltered"   => count($categories_list),
				"pagination"        => '',
				"result"            => array(),
				"categories_list"   => $categories_list,
				"brand"             => $brand_data,
				"cat_data"          => $make_final_tree,
				"cat_image"         => $cat_image,
				"vendor_obj"        => $vendor_obj,
				"vendor_categories" => $vendor_categories,
				"breadcrumb"        => $breadcrumb,
				"heading_name"      => $heading_name,
				"root_parent"       => array(),
			);
			api_response(array('status' => 1, 'msg' => 'success', 'data' => [$output]));
			return;
		}

		// Products-table category listing/search path
		if ($category_id_int > 0 && !$has_vendor_ctx) {
			$root_parent = [];
			$cat_row = $this->db
				->select('id, name, slug')
				->from('ec_categories_prod')
				->where('id', $category_id_int)
				->limit(1)
				->get()->row();
			if ($this->Product_model && method_exists($this->Product_model, 'get_root_parent')) {
				$root_parent = $this->Product_model->get_root_parent((int)$category_id_int);
				if (!is_array($root_parent)) {
					$root_parent = [];
				}
			}
			$cat_name = $cat_row && isset($cat_row->name) ? (string)$cat_row->name : '';
			$cat_slug_db = $cat_row && isset($cat_row->slug) ? (string)$cat_row->slug : '';
			$cat_ids = [$category_id_int];
			$sub = $this->Product_model->get_all_subcat($category_id_int);
			if ($sub) {
				foreach ($sub as $s) {
					if (isset($s->category_id) && (int)$s->category_id > 0) {
						$cat_ids[] = (int)$s->category_id;
					}
				}
			}
			$cat_ids = array_values(array_unique(array_filter($cat_ids, function($v){ return (int)$v > 0; })));
			$cat_ctx = array_map(function($v){ return (string)$v; }, $cat_ids);

			$res = $this->Product_model->get_new_products_page((int)$page, (int)$length, $fts_trim, $cat_ctx, $sorting, $brand_ids, $price_from, $price_to);
			$rows = $res['items'] ?? [];
			$total = (int)($res['total'] ?? 0);

			$symbol = '$';
			$rate = 1.0;
			$cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
			if ($cc_id > 0) {
				$cur = $this->db
					->select('currency_id, symbol, rate')
					->from('ec_currency')
					->where('currency_id', $cc_id)
					->where('status', '1')
					->get()->row();
				if ($cur) {
					if (isset($cur->symbol) && $cur->symbol) {
						$symbol = (string)$cur->symbol;
					}
					if (isset($cur->rate) && is_numeric($cur->rate)) {
						$rate = (float)$cur->rate;
					}
				}
			}

			$items = [];
			if ($rows) {
				foreach ($rows as $r) {
					if (!is_object($r)) continue;
					$img_path = isset($r->first_image_path) ? trim((string)$r->first_image_path) : '';
					$img_url = $this->_normalize_url_scheme(base_url('assets/default_images/product.jpg'));
					if ($img_path !== '') {
						$img_url = $this->_normalize_url_scheme(base_url('uploads/products/' . ltrim($img_path, '/\\')));
					}
					$price = isset($r->min_price) && $r->min_price !== null ? (float)$r->min_price * $rate : 0;
					$title = isset($r->name) ? (string)$r->name : '';
					$title_trim = function_exists('name_trim') ? name_trim(['title' => $title]) : $title;

					$items[] = (object)[
						'id' => isset($r->id) ? (int)$r->id : 0,
						'post_title' => $title,
						'post_title_trim' => $title_trim,
						'url' => base_url('product/details/' . (int)($r->id ?? 0)),
						'featured_image' => $img_url,
						'store_name' => '',
						'symbol' => $symbol,
						'regular_price' => $price,
						'sale_price' => $price,
						'discount' => 0,
						'percentage_off' => 0,
						'avg_rating' => isset($r->avg_rating) ? (float)$r->avg_rating : 0,
						'review_count' => isset($r->review_count) ? (int)$r->review_count : 0,
						'product_type' => isset($r->product_type) ? (string)$r->product_type : 'simple',
					];
				}
			}

			$ln = current_language();
			$brand_data = array();
			$brand_cat_id = 0;
			if (!empty($root_parent) && isset($root_parent[0]->category_id)) {
				$brand_cat_id = (int)$root_parent[0]->category_id;
			} else {
				$brand_cat_id = (int)$category_id_int;
			}
			if ($brand_cat_id > 0) {
				$brand_data = $this->db
					->from('ec_brand')
					->where('status', '1')
					->where('cat_id', $brand_cat_id)
					->order_by('name', 'ASC')
					->get()->result();
				if ($brand_data) {
					foreach ($brand_data as $br) {
						if ($ln == 12) {
							$br->name = ($br->name_es) ? $br->name_es : $br->name;
						}
						$br_id = isset($br->brand_id) ? (int)$br->brand_id : 0;
						if ($br_id > 0 && in_array($br_id, $brand_ids)) {
							$br->checked = 1;
						} else {
							$br->checked = 0;
						}
					}
				}
			}
			$breadcrumb = '';
			$breadcrumb .= '<li class="breadcrumb-item"><a href="/">Home</a></li>';
			$heading_name = '';
			$all_parents = [];
			if ($root_parent) {
				if (isset($root_parent[0]) && isset($root_parent[0]->name) && $root_parent[0]->name !== '') {
					$heading_name = $root_parent[0]->name;
				}
				$cnt = 0;
				foreach($root_parent as $rp){
					$cnt++;
					$all_parents[] = $rp->category_id;
					$active_class = 'class="breadcrumb-item"';
					$crumb_url = '/products/category/'.$rp->slug;
					if($cnt == count($root_parent)){
						$crumb_url = 'javascript:void(0);';
						$active_class = 'class="breadcrumb-item active" aria-current="page"';
					}
					$breadcrumb .= '<li '.$active_class.'><a href="'.$crumb_url.'">'.$rp->name.'</a></li>';
				}
			}
			if ($fts_trim !== '') {
				$breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">'.$fts_trim.'</li>';
			}

			$all_childs = getcategories();
			$category_array = ($all_parents && count($all_parents) > 1) ? $all_parents : [];
			$buildtree_data = $this->buildtree($all_childs, $category_array, $vendor_obj, $parent_id = 0);
			$make_final_tree = $this->make_final_tree($buildtree_data, $vendor_obj, $root_parent);

			$this->load->library('pager');
			$pagination = $this->pager->showLinks('', $page, $total, $length);
			$output = array(
				"draw" => isset($_POST['draw'])?$_POST['draw']:'',
				"recordsSummary" => '',
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"pagination" => $pagination,
				"result" => $items,
				"brand" => $brand_data,
				"cat_data" => $make_final_tree,
				"cat_image" => $cat_image,
				"vendor_obj" => $vendor_obj,
				"vendor_categories" => $vendor_categories,
				"breadcrumb" => $breadcrumb,
				"heading_name" => $heading_name,
				"root_parent" => $root_parent,
			);
			api_response(array('status' => 1, 'msg' => 'success', 'data' => [$output]));
			return;
		}

		// Products-table search with category_slug but no resolved category_id: still return sidebar categories
		if ($category_id_int <= 0 && $category_slug !== '' && $category_slug !== 'AllCategory' && !$has_vendor_ctx) {
			$ln = current_language();
			$slug_norm = get_slug($category_slug);
			$slug_name_norm = strtolower(str_replace('-', ' ', $slug_norm));
			$detected_cat = $this->db
				->select('id, name, slug')
				->from('ec_categories_prod')
				->group_start()
					->where('slug', $category_slug)
					->or_where('slug', $slug_norm)
					->or_where('name', $category_slug)
					->or_where('LOWER(name) = '.$this->db->escape($slug_name_norm), null, false)
				->group_end()
				->order_by('id', 'DESC')
				->limit(1)
				->get()->row();
			$root_parent = array();
			$brand_data = array();
			if ($detected_cat && isset($detected_cat->id) && (int)$detected_cat->id > 0) {
				$root_parent = $this->Product_model->get_root_parent((int)$detected_cat->id);
				$brand_cat_id = 0;
				if (!empty($root_parent) && isset($root_parent[0]->category_id)) {
					$brand_cat_id = (int)$root_parent[0]->category_id;
				} else {
					$brand_cat_id = (int)$detected_cat->id;
				}
				if ($brand_cat_id > 0) {
					$brand_data = $this->db
						->from('ec_brand')
						->where('status', '1')
						->where('cat_id', $brand_cat_id)
						->order_by('name', 'ASC')
						->get()->result();
					if (!$brand_data) {
						$brand_data = $this->db
							->from('ec_brand')
							->where('status', '1')
							->order_by('name', 'ASC')
							->get()->result();
					}
				} else {
					$brand_data = $this->db
						->from('ec_brand')
						->where('status', '1')
						->order_by('name', 'ASC')
						->get()->result();
				}
				if ($brand_data) {
					foreach ($brand_data as $br) {
						if ($ln == 12) {
							$br->name = ($br->name_es) ? $br->name_es : $br->name;
						}
						$br_id = isset($br->brand_id) ? (int)$br->brand_id : 0;
						$br->checked = ($br_id > 0 && in_array($br_id, $brand_ids)) ? 1 : 0;
					}
				}
			} else {
				$brand_data = $this->db
					->from('ec_brand')
					->where('status', '1')
					->order_by('name', 'ASC')
					->get()->result();
				if ($brand_data) {
					foreach ($brand_data as $br) {
						if ($ln == 12) {
							$br->name = ($br->name_es) ? $br->name_es : $br->name;
						}
						$br_id = isset($br->brand_id) ? (int)$br->brand_id : 0;
						$br->checked = ($br_id > 0 && in_array($br_id, $brand_ids)) ? 1 : 0;
					}
				}
			}
			if ($detected_cat && isset($detected_cat->id) && (int)$detected_cat->id > 0) {
				$cat_ids = [(int)$detected_cat->id];
				$sub = $this->Product_model->get_all_subcat((int)$detected_cat->id);
				if ($sub) {
					foreach ($sub as $s) {
						if (isset($s->category_id) && (int)$s->category_id > 0) {
							$cat_ids[] = (int)$s->category_id;
						}
					}
				}
				$cat_ids = array_values(array_unique(array_filter($cat_ids, function($v){ return (int)$v > 0; })));
				$cat_ctx = array_map(function($v){ return (string)$v; }, $cat_ids);
			} else {
				$cat_ctx = array_filter(array_unique([
					(string)$category_slug,
					(string)get_slug($category_slug),
					strtolower(str_replace('-', ' ', (string)get_slug($category_slug))),
				]), function($v){ return (string)$v !== ''; });
			}
			$res = $this->Product_model->get_new_products_page((int)$page, (int)$length, $fts_trim, $cat_ctx, $sorting, $brand_ids, $price_from, $price_to);
			$rows = $res['items'] ?? [];
			$total = (int)($res['total'] ?? 0);
			$symbol = '$';
			$rate = 1.0;
			$cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
			if ($cc_id > 0) {
				$cur = $this->db
					->select('currency_id, symbol, rate')
					->from('ec_currency')
					->where('currency_id', $cc_id)
					->where('status', '1')
					->get()->row();
				if ($cur) {
					if (isset($cur->symbol) && $cur->symbol) {
						$symbol = (string)$cur->symbol;
					}
					if (isset($cur->rate) && is_numeric($cur->rate)) {
						$rate = (float)$cur->rate;
					}
				}
			}
			$items = [];
			if ($rows) {
				foreach ($rows as $r) {
					if (!is_object($r)) continue;
					$img_path = isset($r->first_image_path) ? trim((string)$r->first_image_path) : '';
					$img_url = $this->_normalize_url_scheme(base_url('assets/default_images/product.jpg'));
					if ($img_path !== '') {
						$img_url = $this->_normalize_url_scheme(base_url('uploads/products/' . ltrim($img_path, '/\\')));
					}
					$price = isset($r->min_price) && $r->min_price !== null ? (float)$r->min_price * $rate : 0;
					$title = isset($r->name) ? (string)$r->name : '';
					$title_trim = function_exists('name_trim') ? name_trim(['title' => $title]) : $title;
					$items[] = (object)[
						'id' => isset($r->id) ? (int)$r->id : 0,
						'post_title' => $title,
						'post_title_trim' => $title_trim,
						'url' => base_url('product/details/' . (int)($r->id ?? 0)),
						'featured_image' => $img_url,
						'store_name' => '',
						'symbol' => $symbol,
						'regular_price' => $price,
						'sale_price' => $price,
						'discount' => 0,
						'percentage_off' => 0,
						'avg_rating' => isset($r->avg_rating) ? (float)$r->avg_rating : 0,
						'review_count' => isset($r->review_count) ? (int)$r->review_count : 0,
						'product_type' => isset($r->product_type) ? (string)$r->product_type : 'simple',
					];
				}
			}

			$category_array = array();
			if (!empty($root_parent)) {
				foreach ($root_parent as $rp) {
					if (is_object($rp) && isset($rp->category_id)) {
						$category_array[] = $rp->category_id;
					}
				}
			}
			$all_childs = getcategories();
			$buildtree_data = $this->buildtree($all_childs, $category_array, $vendor_obj, $parent_id = 0);
			$make_final_tree = $this->make_final_tree($buildtree_data, $vendor_obj, $root_parent);
			$breadcrumb = '<li class="breadcrumb-item"><a href="/">Home</a></li>';
			$breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">'.$category_slug.'</li>';
			if ($fts_trim !== '') {
				$breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">'.$fts_trim.'</li>';
			}
			$heading_name = $category_slug;

			$this->load->library('pager');
			$pagination = $this->pager->showLinks('', $page, $total, $length);
			$output = array(
				"draw" => isset($_POST['draw'])?$_POST['draw']:'',
				"recordsSummary" => '',
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"pagination" => $pagination,
				"result" => $items,
				"brand" => $brand_data,
				"cat_data" => $make_final_tree,
				"cat_image" => $cat_image,
				"vendor_obj" => $vendor_obj,
				"vendor_categories" => $vendor_categories,
				"breadcrumb" => $breadcrumb,
				"heading_name" => $heading_name,
				"root_parent" => $root_parent,
			);
			api_response(array('status' => 1, 'msg' => 'success', 'data' => [$output]));
			return;
		}
		if ($category_id_int <= 0 && !$has_vendor_ctx && !$has_category_filter && !$has_category_slug_ctx) {
			$res = $this->Product_model->get_new_products_page((int)$page, (int)$length, $fts_trim, null, $sorting, $brand_ids, $price_from, $price_to);
			$rows = $res['items'] ?? [];
			$total = (int)($res['total'] ?? 0);

			$symbol = '$';
			$rate = 1.0;
			$cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
			if ($cc_id > 0) {
				$cur = $this->db
					->select('currency_id, symbol, rate')
					->from('ec_currency')
					->where('currency_id', $cc_id)
					->where('status', '1')
					->get()->row();
				if ($cur) {
					if (isset($cur->symbol) && $cur->symbol) {
						$symbol = (string)$cur->symbol;
					}
					if (isset($cur->rate) && is_numeric($cur->rate)) {
						$rate = (float)$cur->rate;
					}
				}
			}

			$items = [];
			if ($rows) {
				foreach ($rows as $r) {
					if (!is_object($r)) {
						continue;
					}

					$img_path = isset($r->first_image_path) ? trim((string)$r->first_image_path) : '';
					$img_url = $this->_normalize_url_scheme(base_url('assets/default_images/product.jpg'));
					if ($img_path !== '') {
						$img_url = $this->_normalize_url_scheme(base_url('uploads/products/' . ltrim($img_path, '/\\')));
					}

					$price = isset($r->min_price) && $r->min_price !== null ? (float)$r->min_price * $rate : 0;
					$title = isset($r->name) ? (string)$r->name : '';
					$title_trim = function_exists('name_trim') ? name_trim(['title' => $title]) : $title;

					$items[] = (object)[
						'id' => isset($r->id) ? (int)$r->id : 0,
						'post_title' => $title,
						'post_title_trim' => $title_trim,
						'url' => base_url('product/details/' . (int)($r->id ?? 0)),
						'featured_image' => $img_url,
						'store_name' => '',
						'symbol' => $symbol,
						'regular_price' => $price,
						'sale_price' => $price,
						'discount' => 0,
						'percentage_off' => 0,
						'avg_rating' => isset($r->avg_rating) ? (float)$r->avg_rating : 0,
						'review_count' => isset($r->review_count) ? (int)$r->review_count : 0,
						'product_type' => isset($r->product_type) ? (string)$r->product_type : 'simple',
					];
				}
			}

			$root_parent = array();
			$brand_data = array();
			$category_array = array();
			$breadcrumb = '<li class="breadcrumb-item"><a href="/">Home</a></li>';
			$heading_name = $fts_trim !== '' ? $fts_trim : 'Products';

			if ($fts_trim !== '' && $rows && isset($rows[0]) && is_object($rows[0])) {
				$first = $rows[0];
				$candidates = array();
				if (isset($first->sub_sub_category)) $candidates[] = $first->sub_sub_category;
				if (isset($first->sub_category)) $candidates[] = $first->sub_category;
				if (isset($first->category)) $candidates[] = $first->category;

				$detected_cat = null;
				foreach ($candidates as $cand) {
					$cand_str = is_scalar($cand) ? trim((string)$cand) : '';
					if ($cand_str === '') continue;
					if (is_numeric($cand_str)) {
						$detected_cat = $this->db
							->select('id, name, slug')
							->from('ec_categories_prod')
							->where('id', (int)$cand_str)
							->limit(1)
							->get()->row();
					} else {
						$cand_slug_norm = get_slug($cand_str);
						$cand_name_norm = strtolower(str_replace('-', ' ', $cand_slug_norm));
						$detected_cat = $this->db
							->select('id, name, slug')
							->from('ec_categories_prod')
							->group_start()
								->where('slug', $cand_str)
								->or_where('slug', $cand_slug_norm)
								->or_where('name', $cand_str)
								->or_where('LOWER(name) = '.$this->db->escape($cand_name_norm), null, false)
							->group_end()
							->order_by('id', 'DESC')
							->limit(1)
							->get()->row();
					}

					if ($detected_cat && isset($detected_cat->id) && (int)$detected_cat->id > 0) {
						break;
					}
					$detected_cat = null;
				}

				if ($detected_cat && isset($detected_cat->id) && (int)$detected_cat->id > 0) {
					$root_parent = $this->Product_model->get_root_parent((int)$detected_cat->id);
					$breadcrumb = '<li class="breadcrumb-item"><a href="/">Home</a></li>';
					$heading_name = (string)$detected_cat->name;
					if ($root_parent) {
						if (isset($root_parent[0]) && isset($root_parent[0]->name) && $root_parent[0]->name !== '') {
							$heading_name = $root_parent[0]->name;
						}
						$cnt = 0;
						$category_array = array();
						foreach ($root_parent as $rp) {
							$cnt++;
							$category_array[] = $rp->category_id;
							$active_class = 'class="breadcrumb-item"';
							$crumb_url = '/products/category/'.$rp->slug;
							if ($cnt == count($root_parent)) {
								$crumb_url = 'javascript:void(0);';
								$active_class = 'class="breadcrumb-item active" aria-current="page"';
							}
							$breadcrumb .= '<li '.$active_class.'><a href="'.$crumb_url.'">'.$rp->name.'</a></li>';
						}
					}
					$breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">'.$fts_trim.'</li>';
				} else {
					$breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">'.$fts_trim.'</li>';
				}
			}

			$all_childs = getcategories();
			$buildtree_data = $this->buildtree($all_childs, $category_array, $vendor_obj, $parent_id = 0);
			$make_final_tree = $this->make_final_tree($buildtree_data, $vendor_obj, $root_parent);

			$brand_cat_id = 0;
			if (!empty($root_parent) && isset($root_parent[0]->category_id)) {
				$brand_cat_id = (int)$root_parent[0]->category_id;
			}
			if ($brand_cat_id > 0) {
				$brand_data = $this->db
					->from('ec_brand')
					->where('status', '1')
					->where('cat_id', $brand_cat_id)
					->order_by('name', 'ASC')
					->get()->result();
				if (!$brand_data) {
					$brand_data = $this->db
						->from('ec_brand')
						->where('status', '1')
						->order_by('name', 'ASC')
						->get()->result();
				}
			} else {
				$brand_data = $this->db
					->from('ec_brand')
					->where('status', '1')
					->order_by('name', 'ASC')
					->get()->result();
			}
			$ln = current_language();
			if ($brand_data) {
				foreach ($brand_data as $br) {
					if ($ln == 12) {
						$br->name = ($br->name_es) ? $br->name_es : $br->name;
					}
					$br_id = isset($br->brand_id) ? (int)$br->brand_id : 0;
					$br->checked = ($br_id > 0 && in_array($br_id, $brand_ids)) ? 1 : 0;
				}
			}

			$this->load->library('pager');
			$pagination = $this->pager->showLinks('', (int)$page, $total, (int)$length);
			$output = array(
				"draw"              => isset($_POST['draw'])?$_POST['draw']:'',
				"recordsSummary"    => '',
				"recordsTotal"      => $total,
				"recordsFiltered"   => $total,
				"pagination"        => $pagination,
				"result"            => $items,
				"brand"             => $brand_data,
				"cat_data"          => $make_final_tree,
				"cat_image"         => $cat_image,
				"vendor_obj"        => $vendor_obj,
				"vendor_categories" => $vendor_categories,
				"breadcrumb"        => $breadcrumb,
				"heading_name"      => $heading_name,
				"root_parent"       => array(),
			);

			api_response(array('status' => 1, 'msg' => 'success', 'data' => [$output]));
			return;
		}
		if(isset($_POST['api']) && isset($_POST['vendor_id']) && $_POST['vendor_id'])
		{
			$vendor_obj     = $this->vendor_dtl(array('admin_id'=>$_POST['vendor_id']));
			$vendor_obj     = (array)$vendor_obj[0];
			$vendor_categories = $this->api_vendor_categories(array('vendor_id'=>$_POST['vendor_id']));
			$slug_param_db	= isset($vendor_obj['store_name']) ? strtolower(strtok($vendor_obj['store_name'], " ")) : '';

        	$slug_param = $slug_param == $slug_param_db ? 'all' : $slug_param;
		}


		$vendor_cat_array = [];
		if(isset($vendor_obj['admin_id']) || (isset($_POST['api']) && isset($_POST['vendor_id']) && $_POST['vendor_id']))
		{
			$vendor_id = isset($vendor_obj['admin_id']) ? $vendor_obj['admin_id'] : $_POST['vendor_id'];
			$vendor_cat_arr = $this->Query_model->get_data('ec_vendor_categories', array('vendor_id'=>$vendor_id, 'status'=>'1'), array('id'=>'ASC'));
			if($vendor_cat_arr)
			{
				foreach($vendor_cat_arr as $cat_arr_row)
				{
					$vendor_cat_array[] = $cat_arr_row->id;
				}
			}
		}
        $sub_cat_array 	= array();
        $ln 			= current_language();

		if(count($vendor_cat_array) && $slug_param == 'all')
		{
			$product_ids = '';
			$result_obj   = $this->Product_model->get_product_by_category($category_id, $product_ids, $vendor_cat_array);
		}
		else if((int)$category_id)
		{
			$product_ids = '';
			if(trim((string)$fts) !== ''){
				$sr = $this->Product_model->get_search_result(array('fts' => $fts));
				$ids = array();
				if($sr){
					foreach($sr as $srow){
						if(isset($srow->product_id)){
							$ids[] = (int)$srow->product_id;
						}
					}
				}
				$ids = array_values(array_unique(array_filter($ids)));
				if($ids){
					$product_ids = implode(',', $ids);
					$result_obj   = $this->Product_model->get_product_by_category($category_id, $product_ids, array());
				}else{
					$result_obj = array();
				}
			}else{
				$result_obj   = $this->Product_model->get_product_by_category($category_id, $product_ids, array());
			}
			
			$sub_cat_obj  = $this->Product_model->get_all_subcat($category_id);
			$sub_cat_array[$category_id] = 1;
			if($sub_cat_obj)
			{
            	foreach($sub_cat_obj as $r)
				{
                	$sub_cat_array[$r->category_id] = 1;
                }
            }
        }
		else{
            /*$result_obj   = $this->Product_model->get_search_result(array('fts' => $fts));
            if($result_obj){

            }else{
                $result_obj   = $this->Product_model->get_product_by_category($fts);	
            }*/

            $result_obj   = $this->Product_model->get_product_by_category($fts);
            if($result_obj)
            {}else{
            $result_obj   = $this->Product_model->get_search_result(array('fts' => $fts));
            }
            
        }

        $product_array = array();
        $search_data = array(); 
		$brand_array = array();
        $category_array = array();
		$attribute_array = array();
        if($result_obj){
            foreach($result_obj as $row){
                $product_id = $row->product_id;
				$product_condi = array();
				$product_condi['id'] = $product_id;
				$product_condi['status'] = '1';
				if(isset($vendor_obj['admin_id']))
				{
					$product_condi['login_id'] = $vendor_obj['admin_id'];
				}
                $product_obj = $this->Query_model->get_data_obj('ec_product', $product_condi);

		    if($product_obj){
				$legacy_obj = $this->Query_model->get_data_obj('ec_product_old', array('product_id' => $product_id));
				if ($legacy_obj) {
					if (!isset($product_obj->brand_id) || !(int)$product_obj->brand_id) {
						$product_obj->brand_id = (int)$legacy_obj->brand_id;
					}
					if ((!isset($product_obj->regular_price) || $product_obj->regular_price === null || $product_obj->regular_price === '') && isset($legacy_obj->regular_price)) {
						$product_obj->regular_price = $legacy_obj->regular_price;
					}
					if ((!isset($product_obj->sale_price) || $product_obj->sale_price === null || $product_obj->sale_price === '') && isset($legacy_obj->sale_price)) {
						$product_obj->sale_price = $legacy_obj->sale_price;
					}
				}
                    $categories_obj = $this->Query_model->get_data('ec_product_categories',array('post_id' => $product_id));
                    $tmp_cat_array = array();
                    if($categories_obj){
                        foreach($categories_obj as $cat){
                            if($this->input->post('category_id') && $slug_param != 'all'){
                                if(isset($sub_cat_array[$cat->category_id])){
                                    $category_array[] = $cat->category_id;
                                }
                            }else{
                                $category_array[] = $cat->category_id;
                            }
                            $tmp_cat_array[]  = $cat->category_id;
                        }
                    }
                    $product_obj->category_id = $tmp_cat_array;
                    $product_obj = $this->Product_model->get_product_info(array('product_obj' => $product_obj));
				if ($legacy_obj && (!isset($product_obj->brand_id) || !(int)$product_obj->brand_id)) {
					$product_obj->brand_id = (int)$legacy_obj->brand_id;
				}
				$search_data[] = $product_obj;
				$brand_array[] = $product_obj->brand_id;
                }
                $product_array[] = $row->product_id;
            }
        }
        $category_array = array_unique($category_array);
        $brand_array = array_unique($brand_array);
		if($search_data && isset($sorting) && $sorting==2){	
			// Asc sort		
			$column = 'effective_price';
			usort($search_data, function($first, $second){
				$a_sale = (float)($first->sale_price ?? 0);
				$a_reg = (float)($first->regular_price ?? 0);
				$a = ($a_sale > 0) ? $a_sale : $a_reg;
				$b_sale = (float)($second->sale_price ?? 0);
				$b_reg = (float)($second->regular_price ?? 0);
				$b = ($b_sale > 0) ? $b_sale : $b_reg;
				if ($a === $b) { return 0; }
				return ($a < $b) ? -1 : 1;
			});
		}		
		if($search_data && isset($sorting) && $sorting==3){
			// Desc sort			
			$column = 'effective_price';
			usort($search_data, function($first, $second){
				$a_sale = (float)($first->sale_price ?? 0);
				$a_reg = (float)($first->regular_price ?? 0);
				$a = ($a_sale > 0) ? $a_sale : $a_reg;
				$b_sale = (float)($second->sale_price ?? 0);
				$b_reg = (float)($second->regular_price ?? 0);
				$b = ($b_sale > 0) ? $b_sale : $b_reg;
				if ($a === $b) { return 0; }
				return ($a > $b) ? -1 : 1;
			});
		}

        $filter_search_data = array();
        $filter_params = array('brand' => 0, 'category' => 0);;
        if($search_data){
            if(isset($filter['brand']) && $filter['brand']){
                $filter['brand'] = json_decode($filter['brand']);
            }
            if(isset($filter['category']) && $filter['category']){
                $filter['category'] = json_decode($filter['category']);
            }
            if(isset($filter['price_range'])){
                $filter_price = $filter['price_range'];
            //    $filter = 1;
            }
            foreach($search_data as $r){
                $filter_success = array('brand' => 0, 'category' => 0);;
                if($filter['brand'] && count($filter['brand']) > 0){
                    if (is_array($filter['brand']) && in_array($r->brand_id, $filter['brand'])){
                        $filter_success['brand'] = 1;
                    }
                }

                if(isset($filter['category']) && count($filter['category']) > 0){
                    foreach($r->category_id as $index => $val){
                        if (in_array($val, $filter['category'])){
                            $filter_success['category'] = 1;
                            break;
                        }
                    }
                }

                $error = 0;
                if($filter){
                foreach($filter as $key => $val){
                    if(($key == 'brand' || $key == 'category') && (count($val) > 0 && $filter_success[$key] == 0)){
                        $error = 1;
                        break;
                    }
                }
                }
                if($error == 0){
					$r->post_title_trim = name_trim(array('title'=>$r->post_title));
					$r->percentage_off = percentage_off(array('sale_price'=>$r->sale_price, 'org_price'=>$r->regular_price));
                    if(isset($r->supplier_id) && $r->supplier_id)
                    {
                        $vendor_store = $this->vendor_dtl(array('admin_id'=>$r->supplier_id));
                        
                        $r->store_name = isset($vendor_store[0]->store_name) ? $vendor_store[0]->store_name : '';
                    }
                    $filter_search_data[] = $r;
                }
            } 
        }
         
        $recordsTotal = count($search_data); 
        $recordsFiltered = count($filter_search_data);
        $product_array = array_chunk($filter_search_data, $length);
        if($product_array){
            $filter_search_data = $product_array[$page -1];
        }
		
		$brand_data = array();	
		$brand_array = array_values(array_filter($brand_array, function($v){
			return (string)$v !== '' && (int)$v > 0;
		}));
		if($brand_array){
			$brand_data = $this->db
				->from('ec_brand')
				->where_in('brand_id', $brand_array)
				->where('status', '1')
				->get()->result();
            if($brand_data){
                foreach($brand_data as $r){
                    if($ln == 12){
                       $r->name = ($r->name_es) ? $r->name_es : $r->name;
                    }
                    if (is_array($filter['brand']) && in_array($r->brand_id, $filter['brand'])){
                        $r->checked = 1;
                    }else{
                        $r->checked = 0;
                    }
                }
            }
		}else{
			$brand_cat_id = (int)$category_id;
			if ($brand_cat_id > 0) {
				$brand_data = $this->db
					->from('ec_brand')
					->where('status', '1')
					->where('cat_id', $brand_cat_id)
					->get()->result();
				if($brand_data){
					foreach($brand_data as $r){
						if($ln == 12){
							$r->name = ($r->name_es) ? $r->name_es : $r->name;
						}
						if (is_array($filter['brand']) && in_array($r->brand_id, $filter['brand'])){
							$r->checked = 1;
						}else{
							$r->checked = 0;
						}
					}
				}
			}
		}
		
		$cat_data= array();	$make_final_tree = $root_cat_id = $root_sub_cat_id = $heading_name = '';
        $root_parent = array(); $all_parents = [];
        
		#if($category_array){
        if(1){
            $root_parent = [];
            if($category_id)
            {
			    $root_parent = $this->Product_model->get_root_parent($category_id);
            }
           # print_r($root_parent);
            $breadcrumb = '';
            $breadcrumb .= '<li class="breadcrumb-item"><a href="/">Home</a></li>';
			if(isset($root_parent[0]->category_id))
			{
                $root_cat_id = $root_parent[0]->category_id;
                $root_sub_cat_id = isset($root_parent[1]->category_id) ? $root_parent[1]->category_id : '';
            
                $cnt=0;
                foreach($root_parent as $root_parent_row)
                { 
                    $cnt++;
                    $all_parents[] = $root_parent_row->category_id;
                    $active_class = 'class="breadcrumb-item"';
                    $crumb_url = '/products/category/'.$root_parent_row->slug; 
                    if($cnt == count($root_parent))
                    {
                        $crumb_url = 'javascript:void(0);';
                        $active_class = 'class="breadcrumb-item active" aria-current="page"';
                    } 
                    $breadcrumb .= '<li '.$active_class.'><a href="'.$crumb_url.'">'.$root_parent_row->name.'</a></li>';
                    $heading_name = $root_parent_row->name; 
                }
                /*
				$category_ids = count($vendor_cat_array) ? implode(',', $vendor_cat_array) : $root_parent[0]->category_id;
				$all_childs = $this->Product_model->get_all_childs($category_ids);
				if(count($all_childs) < 1)
				{
					$category_ids = count($vendor_cat_array) ? $vendor_cat_array : $root_parent[0]->category_id;
					$all_childs = $this->Query_model->get_data('ec_categories_prod', array('id'=>$category_ids));
				}*/
            }
            if($fts)
            {
				$breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">?'.$fts.'</li>';
				if(!$heading_name){
					$heading_name = $fts;
				}
			}
                $all_childs = getcategories();
                
				$category_array = count($vendor_cat_array) ? $vendor_cat_array : $category_array;
                if($all_parents && count($all_parents) > 1)
                {
                    $category_array = count($vendor_cat_array) ? $vendor_cat_array : $all_parents;
                }
                 
				$buildtree_data = $this->buildtree($all_childs, $category_array, $vendor_obj, $parent_id = 0);
				$make_final_tree = $this->make_final_tree($buildtree_data, $vendor_obj, $root_parent);
								
			
			/*$cat_data = $this->Query_model->get_data('ec_categories_prod', array('id' => $category_array));
            if($cat_data){
                foreach($cat_data as $r){
                    if($ln == 12){
                        $r->name = ($r->name_es) ? $r->name_es : $r->name;
                    }
                    if (is_array($filter['category']) && in_array($r->id, $filter['category'])){
                        $r->checked = 1;
                    }else{
                        $r->checked = 0;
                    }
                }
            }*/
		}	
	   
        $this->load->library('pager');
        $pagination = $this->pager->showLinks('', $page, $recordsFiltered, $length);
        $recordsSummary = ''; #$recordsTotal = $recordsFiltered = '';


        $output = array(
                "draw"              => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsSummary"    => $recordsSummary,
                "recordsTotal"      => $recordsFiltered,
                "recordsFiltered"   => $recordsFiltered,
                "pagination"        => $pagination,
                "result"            => $filter_search_data,
				"brand"             => $brand_data,
				"cat_data"          => $make_final_tree,
				"cat_image"         => $cat_image,
				"vendor_obj"        => $vendor_obj,
				"vendor_categories" => $vendor_categories,
                "breadcrumb"        => $breadcrumb,
                "heading_name"      => $heading_name,
                "root_parent"       => $root_parent
                );		
				
        /*$response = array('status' => 1, 'message' => 'success', 'data' => [$output]);
        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));*/

		api_response(array('status' => 1, 'msg' => 'success', 'data' => [$output]));
    }

    public function buildtree($src_arr, $cat_arr, $vendor_obj, $parent_id = 0, $tree = array())
	{
     	foreach($src_arr as $idx => $row)
     	{
            
			$vendor_countable = !empty($vendor_obj) && (is_array($vendor_obj) || $vendor_obj instanceof Countable);
			if($vendor_countable && count($vendor_obj) && !in_array($row->id, $cat_arr)){continue;}
         	if($row->parent_id == $parent_id)
         	{
             	foreach($row as $k => $v)
                $tree[$row->id][$k] = $v;
             	unset($src_arr[$idx]);
             	$tree[$row->id]['children'] = $this->buildtree($src_arr, $cat_arr, $vendor_obj, $row->id);
         	}
        }
    ksort($tree);
    return $tree;
	}

	function display_list($nested_categories, $type='parent', $vendor_obj=null, $root_parent=null)
	{
		$current_ids_arr = [];
		if (!is_array($root_parent)) {
			$root_parent = [];
		}
		if(count($root_parent))
		{
			foreach($root_parent as $current_ids)
			{
				$current_ids_arr[] = $current_ids->category_id;
			}
		}

		$list = '';
		if($type == 'child')
		{
			$list .= '<ul class="list-unstyled pb-2">';
		}
		foreach($nested_categories as $nested)
		{
			$url = isset($vendor_obj['admin_uid']) ? "/store/".$vendor_obj['admin_uid']."/".$nested['slug'] : "/products/category/".$nested['slug'];
			if($type == 'parent')
			{
				$class_plus = (!empty($nested['children'])) ? '<span class="accordion-plusicon"></span>' : '';
				$class_open = in_array($nested['id'], $current_ids_arr) ? ' active' : '';
				$class_link2 = ' link2_'.$nested['id'];
				$list .= '<li class="mb-1"><div class="link2 '.$class_open.$class_link2.'" style="position:relative;"><a href="'.$url.'" class="text-black parent_list">'.$nested['name'].'</a>'.$class_plus.'</div>';
			}
			else
			{
				$class_active = in_array($nested['id'], $current_ids_arr) ? ' class="active"' : '';
				$list .= '<li'.$class_active.'><a href="'.$url.'" class="child_list">'.$nested['name'].'</a></li>';
			}
			if(!empty($nested['children']))
			{
				$list .= $this->display_list($nested['children'], 'child', $vendor_obj, $root_parent);
				$list .= '</ul>';
			}
			if($type == 'parent')
			{
				$list .= '</li>';
			}
		}
		$list .= '';

		return $list;
	}

	public function make_final_tree($tree_arr, $vendor_obj, $root_parent)
	{
		$allHt = '';
		foreach($tree_arr as $cat)
		{
			$url = isset($vendor_obj['admin_uid']) ? "/store/".$vendor_obj['admin_uid']."/".$cat['slug'] : "/products/category/".$cat['slug'];
			$child_link2 = 'link';
			$plus_icon = empty($cat['children']) ? '' : '<span class="accordion-plusicon"></span>';
			$class_active = $class_open = $class_submenu = '';
			if($root_parent && isset($root_parent[0]) && $cat['id'] == $root_parent[0]->category_id)
			{
				$class_active = ' active';
				$class_open = 'class="cat_'.$cat['id'].'"';
				$class_submenu = 'submenu_cat_'.$cat['id'];
			}

			$allHt .= '<li '.$class_open.'><div class="'.$child_link2.$class_active.'"><a href="'.$url.'" class="main_cat_css parent_list">'.$cat['name'].'</a>'.$plus_icon.'</div>';
			$allHt .= '<ul id="accordion-subcategory-three" class="list-unstyled pb-2 accordion-subcategory-three accordion3 side_categories submenu '.$class_submenu.'">';
			if(!empty($cat['children']))
			{
				$allHt .= $this->display_list($cat['children'], 'parent', $vendor_obj, $root_parent);
			}
			$allHt .= '</ul></li>';
		}
		return $allHt;
	}
	

	public function get_name_by_id($ids)
	{
		$name_by_ids = $this->Product_model->get_name_by_id($ids);
		$by_id = [];
		if($name_by_ids)
		{
			foreach($name_by_ids as $row_wise)
			{
				$by_id[$row_wise->id] = $row_wise;
			}
		}
		return $by_id;
	}
	
    public function detail($slug=null) 
    {
        $data = array(); 
		$data['page_lang'] = get_page_language_data('product_detail_lang');
		$categories = '';
        $data['csrf'] = csrf_token(); 
        $data['TYPE'] = $this->TYPE;  
		if(!is_api() && is_numeric((string)$slug) && (string)$slug !== ''){
			redirect(base_url('product/details/'.(int)$slug));
			return;
		}
		
		if(is_api())
		{
			$product_id = $this->input->post('product_id');
            $args = array('product_id' => $product_id);
		}else{
            $args = array('post_slug' => $slug);
        }

        $product_obj = $this->Query_model->get_data_obj('ec_product',$args);	
        if($product_obj){
            if(is_array($product_obj)){
                $product_obj = $product_obj[0];
            }
            $product_id = $product_obj->product_id;
        }else{
            if(isset($_POST['api']) && $_POST['api'] == 1){
                $ret_data = array('status' => '0', 'message' => 'Invalid Product', 'data' => array());
                header('Content-Type: application/json');
                echo json_encode($ret_data);
                die();
            }else{
                redirect("/");
            }
        }

        $discount = 0;
        $sale_price_dates_from = $product_obj->sale_price_dates_from;
        $sale_price_dates_to   = $product_obj->sale_price_dates_to;
        if($sale_price_dates_from && $sale_price_dates_from != '0000-00-00 00:00:00' && $sale_price_dates_to && $sale_price_dates_to != '0000-00-00 00:00:00'){
            $ctime = time();
            $sale_ftime = strtotime($sale_price_dates_from);
            $sale_ttime = strtotime($sale_price_dates_to);
            if($ctime > $sale_ftime && $ctime < $sale_ttime){
                $discount = 1;
            }
        }
        $current_language = current_language();
        $current_currency = current_currency();
        $currency = get_currency($current_currency);
        $rate = $currency->rate;
        $symbol = $currency->symbol;

        if($current_language == 12){
            $product_obj->post_title = ($product_obj->post_title_es) ? $product_obj->post_title_es : $product_obj->post_title;
            $product_obj->post_content = ($product_obj->post_content_es) ? $product_obj->post_content_es : $product_obj->post_content;
        }
        
        $prod_currency_id = isset($product_obj->currency_id) && $product_obj->currency_id !== '' ? (int)$product_obj->currency_id : 1;
        $qty_price = array();
        if($product_obj->type == 'simple'){
            $quantity_range = isset($product_obj->quantity_range) ? $product_obj->quantity_range : '';
            $price_range = isset($product_obj->price_range) ? $product_obj->price_range : '';
            $qty_range = unserialize($quantity_range);
            $pricerange = unserialize($price_range);

            if($qty_range && $pricerange){
                foreach($qty_range as $key => $val){
                    if((isset($key) && $val) && (isset($pricerange[$key]) && $pricerange[$key])){
                        $qty_price[$qty_range[$key]] = sprintf('%.02f', convert_price($pricerange[$key], $prod_currency_id, $current_currency));
                    }
                }
            }
            if(is_api()){
                $quantity_price_range = array();
                foreach($qty_price as $quantity => $price){
                    $quantity_price_range[] = (object) array('quantity' => $quantity, 'price' => $price);
                }
                $product_obj->price_range = $quantity_price_range;
            }else{
                $product_obj->price_range = $qty_price;
            }
        }else{
            $product_obj->price_range = array();
        }

		$data['rate'] = $rate;
		$data['symbol'] = $symbol;
		$data['iso_code'] = $currency->iso_code;
		$data['discount'] = $discount;
		
		$title= substr($product_obj->post_title,0,40);	
		
        $category_arr = $this->Product_model->get_category($product_id);
		$cat_name = isset($category_arr[0]->name)? $category_arr[0]->name:'';
		$cat_slug = isset($category_arr[0]->slug)? $category_arr[0]->slug:'';
		
		$crumbs = array($data['page_lang']->home => "/",$cat_name => '/products/category/'.$cat_slug, "$title" => '');
		$breadcrumbs = $this->breadcrumbs->show_new($crumbs);
		$data['breadcrumbs']    = $breadcrumbs;       
              
		$args_image = array('product_id' => $product_id );	
        $product_gallery = $this->Query_model->get_data('ec_gallery',$args_image);
        if($product_gallery){
            foreach($product_gallery as $key=>$val){			
                $val->url = base_url().'assets/uploads/files/'.$val->file_name; 			
            }
        }else{
            $url = base_url().'assets/default_images/product.jpg';
            $product_gallery = array((object) array ('file_name' => 'product.jpg', 'url' => $url));
        }
        $data['product_gallery'] = $product_gallery;

        $brand_obj = $this->Query_model->get_data_obj('ec_brand',array('brand_id' => $product_obj->brand_id));
        $supplier_obj = $this->Query_model->get_data_obj('ec_supplier',array('supplier_id' => $product_obj->supplier_id,'status'=>'1'));
        $comment_obj = $this->Product_model->get_product_comment($product_obj->product_id);

        if($supplier_obj){
            unset($supplier_obj->status);unset($supplier_obj->password);unset($supplier_obj->date_added);unset($supplier_obj->last_updated);unset($supplier_obj->supplier_uid);
            if($supplier_obj->country){
                $country = get_country($supplier_obj->country);
                $supplier_obj->country = $country->name;
            }
        }else{
            $supplier_obj = (object) array();
        }
        
        $variations      = array();
        $attributes      = array();
        $attribute_items = array();
        $variation_mapping = array();
        $attribute_item_array = array();      
        if($product_obj->type == 'variable'){
            $img_array = array();
            $variation = $this->Query_model->get_attr_vari_item($product_id);
            if($variation){
                foreach($variation as $v){
                    $arr = explode(',',$v->attribute_item_id);
                    if($arr){
                        foreach($arr as $a){
                            $attribute_item_array[$a] = base_url().'assets/uploads/'.$v->_thumbnail_id;
                        }
                    }

                    $tmp_array = array(
                            'sale_price' => sprintf('%.02f', convert_price($v->_sale_price, $prod_currency_id, $current_currency)), 
                            'regular_price' => sprintf('%.02f', convert_price($v->_regular_price, $prod_currency_id, $current_currency))                           
                            );
                    sort($arr);
                    $att_item_id = implode(',',$arr);
                    $variations[$att_item_id] = $tmp_array;
                    $img_array[] = (object) array(
                            'file_name' => base_url().'assets/uploads/'.$v->_thumbnail_id,
                            'url' => base_url().'assets/uploads/'.$v->_thumbnail_id,
                    );
                }
            }
            if($attribute_item_array){
                $ata = array_keys($attribute_item_array);
                $variations_keys_array = array_keys($variations);
                $first_variation = $variations_keys_array[0];
                $first_variation_array = explode(',',$first_variation);
                $first_variation_hash  = array();
                foreach($first_variation_array as $i){
                    $first_variation_hash[$i] = 1;
                }

                if($variations[$first_variation]){
                    $product_obj->sale_price = sprintf('%.02f',$variations[$first_variation]['sale_price']);
                    $product_obj->regular_price = sprintf('%.02f',$variations[$first_variation]['regular_price']);
                }
                $attribute_item_obj = $this->Query_model->get_data('ec_attribute_item', array('attribute_item_id' => $ata));
                $it_at_mapping = array();
                if($attribute_item_obj){
                    foreach($attribute_item_obj as $ai){
                        if(isset($attribute_item_array[$ai->attribute_item_id])){
                            $ai->image = $attribute_item_array[$ai->attribute_item_id];
                            if($ai->attribute_id != 1){
                                $ai->image = '';
                            }
                        }else{
                            $ai->image = '';
                        }
                        if(isset($first_variation_hash[$ai->attribute_item_id])){
                            $ai->selected = 1;
                        }else{
                            $ai->selected = 0;
                        }
                        $attribute_items[$ai->attribute_id][] = $ai;
                        $it_at_mapping[$ai->attribute_item_id] = $ai->attribute_id;
                    }

                    $attribute_array = array_keys($attribute_items);
                    $attribute_obj = $this->Query_model->get_data('ec_attribute', array('attribute_id' => $attribute_array));
                    if($attribute_obj){
                        foreach($attribute_obj as $a){
                            if($current_language == 12){
                                $a->name = ($a->name_es) ? $a->name_es : $a->name;
                            }
                            $attributes[] = $a;
                        }
                    }
                }
                $data['product_gallery'] = $img_array;

                foreach($attribute_item_array as $ak => $av){
                    foreach($variations as $vk => $vv){
                        $vk_array = explode(',',$vk);
                        if(in_array($ak,$vk_array)){
                            $tmp_array = [];
                            foreach($vk_array as $i){
                                $tmp_array[$i] = 1;
                            }
                            if(isset($variation_mapping[$ak])){
                                $tarray = $variation_mapping[$ak];
                                foreach($tmp_array as $tk => $tv){
                                    $tarray[$tk] = 1;
                                }
                                $variation_mapping[$ak] = $tarray;
                            }else{
                                $variation_mapping[$ak] = $tmp_array;
                            }
                        }
                    }
                }
                foreach($variation_mapping as $key => $val){
                    $taken = array();
                    foreach($val as $k => $v){
                        if(isset($taken[$it_at_mapping[$k]])){
                            $variation_mapping[$key][$k]+=1; 
                        }
                        $taken[$it_at_mapping[$k]] = 1;   
                    }
                    foreach($it_at_mapping as $k1 => $v1){
                        if(!isset($variation_mapping[$key][$k1])){
                            $variation_mapping[$key][$k1] = 0;
                        }
                    }
                }
            }else{
                $product_obj->enabled = 0;
            }
        }else{
            $product_obj->sale_price = sprintf('%.02f', convert_price($product_obj->sale_price, $prod_currency_id, $current_currency));
            $product_obj->regular_price = sprintf('%.02f', convert_price($product_obj->regular_price, $prod_currency_id, $current_currency));
            $attribute_item_obj = $this->Query_model->get_product_attr_item($product_id);
            if($attribute_item_obj){
                $tmp_attr = array();
                foreach($attribute_item_obj as $ai){
                    $ai->image = '';
                    if(isset($tmp_attr[$ai->attribute_id])){
                        $ai->selected = '';
                    }
                    if(isset($tmp_attr[$ai->attribute_id])){
                        $ai->selected = 0;
                    }else{
                        $tmp_attr[$ai->attribute_id] = 1;
                        $ai->selected = 1;
                    }
                    $attribute_items[$ai->attribute_id][] = $ai;
                }

                $attribute_array = array_keys($attribute_items);
                $attribute_obj = $this->Query_model->get_data('ec_attribute', array('attribute_id' => $attribute_array));
                if($attribute_obj){
                    foreach($attribute_obj as $a){
                        if($current_language == 12){
                            $a->name = ($a->name_es) ? $a->name_es : $a->name;
                        }
                        $attributes[] = $a;
                    }
                }
            }
        }

        $product_obj->subtotal =  ($discount) ? $product_obj->sale_price : $product_obj->regular_price;

        $tax_obj = $this->Cart_model->get_tax($product_obj->tax_id,$product_obj->subtotal);
        $shipping_obj = $this->Cart_model->get_shipping($product_obj->shipping_id);
        if($shipping_obj){
            if(is_numeric($shipping_obj->shipping)){
                $shipping_obj->shipping = sprintf('%.02f',$rate*$shipping_obj->shipping);
            }
            $product_obj->shipping = $shipping_obj->shipping; 
        }
        if($tax_obj){
            if(is_numeric($tax_obj->tax)){
                $product_obj->tax = sprintf('%.02f',$tax_obj->tax);
            }else{
                $product_obj->tax = $tax_obj->tax;
            }
            $product_obj->tax_type = $tax_obj->tax_type;
            $product_obj->tax_rate = $tax_obj->tax_rate;
        }
        $product_obj->total = $product_obj->subtotal;
        if(is_numeric($product_obj->shipping)){
            $product_obj->total+= $product_obj->shipping;
        }
        if(is_numeric($product_obj->tax)){
            $product_obj->total+= $product_obj->tax;
        }
		
        $tmp_attribute_items;
        if($attributes){
            foreach($attributes as $at => $av){
                if($attribute_items && isset($attribute_items[$av->attribute_id])){
                    $av->attribute_items = $attribute_items[$av->attribute_id];

                    foreach($attribute_items[$av->attribute_id] as $k => $v){
                        $tmp_attribute_items[$v->attribute_item_id] = $v;
                    }
                }
            }
        }

        $tmp_variations = array();
        if(is_api() && $variations){
            foreach($variations as $k => $v){
                if($k){
                    $tmp_array = array();
                    $k_array = explode(',',$k);
                    if($k_array){
                        foreach($k_array as $i => $j){
                            if($tmp_attribute_items[$j]){
                                $tmp_array[$tmp_attribute_items[$j]->name] = $j;
                            }
                        }
                    }
                    $tmp_array['sale_price'] = $v['sale_price'];
                    $tmp_array['regular_price'] = $v['regular_price'];
                    $tmp_variations[] = $tmp_array;
                }
            }
            $data['variations'] = $tmp_variations;
        }else{
            $data['variations'] = $variations;
        }

		$data['brand_obj'] = $brand_obj;
		$data['supplier_obj'] = $supplier_obj;
		$data['comment_obj'] = $comment_obj;
        $data['attributes'] = $attributes;
        $data['attribute_items'] = $attribute_items;
        $data['variation_mapping'] = $variation_mapping;
        $data['lead_time'] = lead_time();
        $data['shipping_time'] = shipping_time();

        $wishlist = 0;
        if($this->LOGIN_ID){
            $wishlist_obj = $this->Query_model->get_data_obj('ec_wishlist',array('customer_id' => $this->LOGIN_ID, 'product_id' => $product_obj->product_id, 'status' => '1'));
            if($wishlist_obj){
                $wishlist = 1;
            }
        }
        $data['wishlist'] = $wishlist;        
        $cart         = $this->Cart_model->cart_items();
        $data['cart_summary'] = $cart['cart_summary'];
        $data['related_product'] = $this->Product_model->related_product($product_id);
		$data['detail'] = $product_obj;
		
    
        $data['delivery_information'] = delivery_information();
        $data['return_policy'] = return_policy();
		$this->load->template("$this->TYPE/product_detail", $data);
        
        if(is_api()){
            unset($data['csrf']);
            unset($data['breadcrumbs']);
            unset($data['TYPE']);
            $response = array(
                'status' => '1',
                'message' => 'success',
                'data' => [$data],
             );               
                $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
		
    }
    
    function get_page_id_by_slug($slug=null) {       
        $args = array('post_slug' => $slug, 'enabled' => '1');	
        $page= $this->Query_model->get_data_obj('ec_product',$args);      
        if ( $page ) {
           return (int) $page->product_id;
        }else{
           return null;
        }		
    }

    public function ajax_product_variation()
    {
        $current_language = current_language();
        $current_currency = current_currency();
        $currency = get_currency($current_currency);
        $rate = $currency->rate;
        $symbol = $currency->symbol;

        $product_id         = $this->input->post('product_id');
        $quantity           = $this->input->post('quantity');
        $attribute_item     = $this->input->post('attribute_item');
        $selected_item_id   = $this->input->post('attribute_item_id');
        $attribute_items = array();
        if($attribute_item){
            $attribute_items = json_decode($attribute_item);
            if($attribute_items){
                sort($attribute_items);
            }
        }
      
        $response = array(
                        'status'    => '0',
                        'message'   => 'Error',
                    );
        $data = array(); 
        $product_obj = $this->Query_model->get_data_obj('ec_product',array('product_id' => $product_id));
        $prod_currency_id = isset($product_obj->currency_id) && $product_obj->currency_id !== '' ? (int)$product_obj->currency_id : 1;
        $variations      = array();
        $attribute_item_array = array();
        $image_array = array();
        if($product_obj->type == 'variable'){
            $attribute_item_id = implode(',',$attribute_items);
            $variation = $this->Query_model->get_attr_vari_item($product_id);
            if($variation){
                foreach($variation as $v){
                    $arr = explode(',',$v->attribute_item_id);
                    if($arr){
                        foreach($arr as $a){
                            $image_array[$a] = base_url().'assets/uploads/'.$v->_thumbnail_id;
                            $attribute_item_array[$a] = 0;
                        }
                    }

                    $tmp_array = array(
                            'sale_price' => sprintf('%.02f', convert_price($v->_sale_price, $prod_currency_id, $current_currency)),
                            'regular_price' => sprintf('%.02f', convert_price($v->_regular_price, $prod_currency_id, $current_currency)),
                            );
                    sort($arr);
                    $att_item_id = implode(',',$arr);
                    $variations[$att_item_id] = $tmp_array;
                }
            }

            if(!isset($variations[$attribute_item_id])){
                $compare = array();
                if($variations){
                    foreach($variations as $vk => $vv){
                        $vk_array = explode(',',$vk);
                        if(in_array($selected_item_id,$vk_array)){
                            $result   = array_diff($vk_array,$attribute_items);
                            $compare[$vk] = count($result);
                        }else{
                            $compare[$vk] = 999999999;
                        }
                    }
                }
                asort($compare);
                if($compare){
                    foreach($compare as $vk => $vv){
                        if(isset($variations[$vk])){
                            $attribute_item_id = $vk;
                            $attribute_items   = explode(',',$attribute_item_id);
                            break;
                        }
                    }
                }
            }

            if($variations){
                foreach($variations as $vk => $vv){
                    $vk_array = explode(',',$vk);
                    if(in_array($selected_item_id,$vk_array)){
                        foreach($vk_array as $i){
                            $attribute_item_array[$i] = 2;
                        }
                    }
                }
            }
            if($attribute_items){  
                foreach($attribute_items as $ak => $av){
                    if(isset($attribute_item_array[$av])){
                        $attribute_item_array[$av] = 1;
                    }
                }
            }
            $data['variations'] = $attribute_item_array;
            $ata = array_keys($attribute_item_array);
            $attribute_item_obj = $this->Query_model->get_data('ec_attribute_item', array('attribute_item_id' => $ata));
            $attribute_items = array();
            $it_at_mapping = array();
            if($attribute_item_obj){
                foreach($attribute_item_obj as $ai){
                    if($ai->attribute_id == 1 && isset($image_array[$ai->attribute_item_id])){
                        $ai->image = $image_array[$ai->attribute_item_id];
                        $img_array[] = (object) array(
									'file_name' => $ai->image,
									'url' => $ai->image,
								);
                    }else{
                        $ai->image = '';
                    }
                    $ai->selected = $attribute_item_array[$ai->attribute_item_id];
                    $attribute_items[$ai->attribute_id][] = $ai;
                    $it_at_mapping[$ai->attribute_item_id] = $ai->attribute_id;
                }
                $attribute_array = array_keys($attribute_items);
                $attribute_obj = $this->Query_model->get_data('ec_attribute', array('attribute_id' => $attribute_array));
                if($attribute_obj){
                    foreach($attribute_obj as $a){
                        if($current_language == 12){
                            $a->name = ($a->name_es) ? $a->name_es : $a->name;
                        }
                        if($attribute_items && isset($attribute_items[$a->attribute_id])){
                            $a->attribute_items = $attribute_items[$a->attribute_id];
                        }
                        $attributes[] = $a;
                    }
                }
            }

            $data['attributes'] = $attributes;

            $product_obj->sale_price = $variations[$attribute_item_id]['sale_price'];
            $product_obj->regular_price = $variations[$attribute_item_id]['regular_price'];
            $discount = 0;
            $sale_price_dates_from = $product_obj->sale_price_dates_from;
            $sale_price_dates_to   = $product_obj->sale_price_dates_to;
            if($sale_price_dates_from && $sale_price_dates_from != '0000-00-00 00:00:00' && $sale_price_dates_to && $sale_price_dates_to != '0000-00-00 00:00:00'){
                $ctime = time();
                $sale_ftime = strtotime($sale_price_dates_from);
                $sale_ttime = strtotime($sale_price_dates_to);
                if($ctime > $sale_ftime && $ctime < $sale_ttime){
                    $discount = 1;
                }
            }

            $product_obj->subtotal = ($discount) ? $product_obj->sale_price : $product_obj->regular_price;
            $tax_obj = $this->Cart_model->get_tax($product_obj->tax_id,$product_obj->subtotal);
            $shipping_obj = $this->Cart_model->get_shipping($product_obj->shipping_id);
            if($shipping_obj){
                if(is_numeric($shipping_obj->shipping)){
                    $product_obj->shipping  = sprintf('%.02f',$rate*$shipping_obj->shipping);
                }else{
                    $product_obj->shipping  = $shipping_obj->shipping;
                }
            }
            if($tax_obj){
                    $product_obj->tax = $tax_obj->tax;
                $product_obj->tax_type  = $tax_obj->tax_type;
            }

            $data['sale_price']         = $product_obj->sale_price;
            $data['regular_price']      = $product_obj->regular_price;
            $data['subtotal']           = $product_obj->subtotal;
            $data['shipping']           = $product_obj->shipping;
            $data['tax']                = $product_obj->tax;
            $data['tax_type']           = $product_obj->tax_type;
            $data['discount']           = $discount;
            $data['is_price_update']    = 1;
            $data['symbol']             = $symbol;
            $data['iso_code']           = $currency->iso_code;

            $response = array(
                        'status'    => '1',
                        'message'   => 'Success',
                        'data'      => [$data]
                    );

        }
        if($product_obj->type == 'simple'){
            $data['is_price_update'] = 0;
            $response = array(
                        'status'    => '1',
                        'message'   => 'Success',
                        'data'      => [$data]
                    );

        }
        if(!isset($response['data'])){
            $response['data'] = array();
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

	public function vendor_cat($vendor_uid=null, $slug=null)
	{
		$data	= array();	
		$data['page_count'] = page_count();
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
		$page  = (isset( $_POST['page']))?$_POST['page']: 1;

		$data['page_lang'] 	= get_page_language_data('product_list_lang');	
		$data['TYPE'] 		= $this->TYPE;
		$data['csrf'] 		= csrf_token();

		$slug = strtolower($slug);

		$vendor_obj = $this->Query_model->get_data_obj('ec_admin', array('admin_uid'=>$vendor_uid, 'role_id'=>'2'));
        $vendor_img = $vendor_obj->vendor_img ? unserialize($vendor_obj->vendor_img) : [];
        $vendor_obj->vendor_img = isset($vendor_img['thumb']) ? $vendor_img['thumb'] : base_url().'/assets/images/store/s-1.png';

		$vendor_cat_obj = $this->Query_model->get_data_obj('ec_vendor_categories', array('vendor_id'=>$vendor_obj->admin_id, 'status'=>'1'), array('vendor_cat_id'=> 'DESC'), array('length' => 1));

		$slug_param        = isset($vendor_obj->store_name) ? strtolower(strtok($vendor_obj->store_name, " ")) : '';
			
		$slug = $slug == $slug_param ? 'all' : $slug;
		$category_slug = get_slug($slug);	

		$cat_prod_condi = [];
		if($slug == 'all')
		{
			$cat_prod_condi['id'] = isset($vendor_cat_obj->id) ? $vendor_cat_obj->id : '';	
		}
		else
		{
			$cat_prod_condi['slug'] = $category_slug;		
		}

		$category_obj = $this->Query_model->get_data_obj('ec_categories_prod', $cat_prod_condi, array(),array('length' => 1));

		if($category_obj){
		    $data['cat'] = $category_obj->id;
			$data['cat_image'] = base_url().'assets/categories/'.$category_obj->banner_image;
            $data['fts'] = '';
            $cat_parent_obj  = $this->Product_model->get_all_parent($category_obj->id);
            $crumbs = array($data['page_lang']->home => "/$this->TYPE/product/");
            if($cat_parent_obj){
                foreach($cat_parent_obj as $cpo){
                    $crumbs[$cpo->name] = get_slug($cpo->name);
                }
            }
        }else{
            $data['fts'] = ''; //$arg;
            $data['cat'] = 0;
			$data['cat_image'] = 0;
            $crumbs = array($data['page_lang']->home => "/$this->TYPE/product/", $data['page_lang']->products => "");
        }		
        $breadcrumbs = $this->breadcrumbs->show_new($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['slug']    		= $category_slug;
		$data['cate_all_strip']	= $this->Product_model->categories_all_strip();
		$data['homepage'] 		= 1;
		$data['login_id'] 		= $this->LOGIN_ID;
		$data['vendor_obj'] 	= $vendor_obj;
		$data['slug_param'] 	= $slug == 'all' ? 'all' : $category_slug;
        $this->load->template("$this->TYPE/product_list", $data);
			
	}

	public function vendor_dtl($args)
	{
		$admin_obj = $this->Query_model->get_data('ec_admin', array('admin_id'=>$args['admin_id']));	
		return $admin_obj;
	} 

	public function api_vendor_categories($args)
	{
		$vendor_cat = $this->Query_model->get_data('ec_vendor_categories', array('vendor_id'=>$args['vendor_id']));
		if($vendor_cat)
		{
			$all_vendor_cat= [];
			foreach($vendor_cat as $vendor_cat_row)
			{
				$all_vendor_cat[] = $vendor_cat_row->id;
			}
			$vendor_cat_dtl = $this->Query_model->get_data('ec_categories_prod', array('id'=>$all_vendor_cat));
			if($vendor_cat_dtl)
			{
				foreach($vendor_cat_dtl as $row_wise)
				{
					$row_wise->thumbnail_path = base_url()."assets/categories/$row_wise->thumbnail";
				}
				return $vendor_cat_dtl;
			}
		}
	}
}
