<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Item_model extends MY_Model{

	function __construct() {
		parent::__construct();	
	}

	function list($args)
	{
		$cat_id = $args['cat_id'];
		$product_cat_id = $args['product_cat_id'];

		$this->slave->select('p.*, cp.name as cat_name, AVG(rr.rating) as avg_rating, COUNT(rr.review_rating_id) as rating_count');
		$this->slave->from('products as p');
		$this->slave->join('ec_categories_prod as cp', 'cp.id = p.category_id');
		$this->slave->join('ec_review_rating as rr', 'rr.product_id = p.id', 'left');
		$this->slave->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
		$this->slave->where('p.status', '1');
		$this->slave->group_start()
			->where('p.vendor_id', 0)
			->or_where('p.vendor_id IS NULL', null, false)
			->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
		->group_end();
		if($cat_id){
		$this->slave->group_start();
		$this->slave->where('p.category_id', $cat_id);
		$this->slave->or_where('p.sub_cat_id', $cat_id);
		$this->slave->group_end();
		#}else if($product_cat_id){
		}else{
			$this->slave->where('p.product_category', $product_cat_id);
		}
		$this->slave->group_by('p.id');
		$query = $this->slave->get();
		return $query->result();		
		
	}
	
	public function search_products($query) {
        $this->slave->select('p.id, p.name, p.images, p.prices, p.modifiers, p.extras,
                              c.name AS category_name, c.slug AS category_slug,
                              sc.name AS subcategory_name, sc.slug AS subcategory_slug');
        $this->slave->from('products AS p');
        $this->slave->join('ec_categories_prod AS c', 'c.id = p.category_id', 'left');
        $this->slave->join('ec_categories_prod AS sc', 'sc.id = p.sub_cat_id', 'left');
		$this->slave->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
		$this->slave->where('p.status', '1');
		$this->slave->group_start()
			->where('p.vendor_id', 0)
			->or_where('p.vendor_id IS NULL', null, false)
			->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
		->group_end();

        $this->slave->group_start();
        $this->slave->like('p.name', $query);
        $this->slave->or_like('p.short_description', $query);
        $this->slave->or_like('c.name', $query);
        $this->slave->group_end();

        $this->slave->limit(10);
        $result = $this->slave->get()->result_array();

        foreach ($result as &$r) {
            $r['price'] = null;
            if (!empty($r['prices'])) {
                $prices = json_decode($r['prices'], true);
                if (is_array($prices) && count($prices) > 0) {
                    $first = $prices[0];
                    $r['price'] = $first['sales_price'];
                }
            }

            $r['image_url'] = '/uploads/products/default.png';
            if (!empty($r['images'])) {
                $imgs = json_decode($r['images'], true);
                if (is_array($imgs) && count($imgs) > 0) {
                    $r['image_url'] = '/uploads/products/' . $imgs[0];
                }
            }

            $slug_name = strtolower(str_replace(' ', '-', $r['name']));
            //$r['category_url'] = "/category/{$r['category_slug']}/{$r['id']}";
            $r['category_url'] = "/category/{$r['category_slug']}";
            $r['product_url']  = "/product/{$slug_name}";
        }

        return $result;
    }

	public function get_random_products($limit = 8, $exclude_latest = 4) {
    $this->slave->select('p.id');
    $this->slave->from('products p');
    $this->slave->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
	$this->slave->where('p.status','1');
    $this->slave->group_start()
        ->where('p.vendor_id', 0)
        ->or_where('p.vendor_id IS NULL', null, false)
        ->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
    ->group_end();
    $this->slave->order_by('p.id', 'DESC');
    $this->slave->limit($exclude_latest);
    $latest_products = $this->slave->get()->result_array();

    $exclude_ids = array_column($latest_products, 'id');

    $this->slave->select('p.id AS product_id, p.product_category, p.name, p.prices, p.images, p.product_type, p.short_description, p.modifiers, p.extras');
    $this->slave->from('products p');
    $this->slave->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
	$this->slave->where('p.status','1');
    $this->slave->group_start()
        ->where('p.vendor_id', 0)
        ->or_where('p.vendor_id IS NULL', null, false)
        ->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
    ->group_end();
    if (!empty($exclude_ids)) {
        $this->slave->where_not_in('id', $exclude_ids);
    }

    $this->slave->order_by('RAND()');
    $this->slave->limit($limit);

    $query = $this->slave->get()->result_array();

    $products = [];
    foreach ($query as $row) {

		$images = json_decode($row['images']);
            $first_image = count($images) ? $images[0] : '';
            $prices = json_decode($row['prices']);
            $first_image = count($prices) && isset($prices[0]->images[0]) ? $prices[0]->images[0] : $first_image;

        $image_path = $first_image
            ? ($row['product_type'] == 'attribute'
                ? '/uploads/attributes/'.$first_image
                : '/uploads/products/'.$first_image)
            : '';

        $short_description = $row['short_description'];
        $short_description = (strlen($short_description) > 30)
            ? substr($short_description, 0, 30) . '...'
            : $short_description;

        $products[] = [
            'id'    => $row['product_id'],
            'name'  => $row['name'],
            'product_type'  => $row['product_type'],
            'prices' => $row['prices'],
            'image' => $row['images'],
            'image_path' => $image_path,
            'short_description' => $short_description,
			'modifiers'	=> $row['modifiers'],
			'extras'=>$row['extras']
        ];
    }

    return $products;
}



	public function get_new_added_products($limit = 4) {
    $this->slave->select('p.id AS product_id, p.product_category, p.name, p.prices, p.images, p.product_type, p.short_description, p.modifiers, p.extras');
    $this->slave->from('products p');
    $this->slave->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
	$this->slave->where('p.status','1');
    $this->slave->group_start()
        ->where('p.vendor_id', 0)
        ->or_where('p.vendor_id IS NULL', null, false)
        ->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
    ->group_end();
    $this->slave->order_by('id', 'DESC'); // Or use created_at if available
    $this->slave->limit($limit);

    $query = $this->slave->get()->result_array();

    $products = [];
    foreach ($query as $row) {
		$images = json_decode($row['images']);
            $first_image = count($images) ? $images[0] : '';
            $prices = json_decode($row['prices']);
            $first_image = count($prices) && isset($prices[0]->images[0]) ? $prices[0]->images[0] : $first_image;

        $image_path = $first_image 
            ? ($row['product_type'] == 'attribute' 
                ? '/uploads/attributes/'.$first_image 
                : '/uploads/products/'.$first_image) 
            : '';

        $short_description = $row['short_description'];
        $short_description = (strlen($short_description) > 30) 
            ? substr($short_description, 0, 30) . '...' 
            : $short_description;

        $products[] = [
			'id'    => $row['product_id'],
            'name'  => $row['name'],
            'product_type'  => $row['product_type'],
            'prices' => $row['prices'],
            'image' => $row['images'],
            'image_path' => $image_path,
            'short_description' => $short_description,
            'modifiers' => $row['modifiers'],
            'extras'=>$row['extras']
        ];
    }

    return $products;
}


	public function get_home_products($product_category) {
		$category_ids = array_keys($product_category);
        if (empty($category_ids)) return [];

        $this->slave->select('p.id AS product_id, p.product_category, p.name, p.prices, p.images, p.product_type, p.short_description, p.modifiers, p.extras');
        $this->slave->from('products p');
        $this->slave->join('ec_vendor v_check', 'v_check.vendor_id = p.vendor_id', 'left');
		$this->slave->where('p.status','1');
        $this->slave->group_start()
            ->where('p.vendor_id', 0)
            ->or_where('p.vendor_id IS NULL', null, false)
            ->or_where("CAST(v_check.status AS CHAR) IN ('1', 'approved', 'Approved')", null, false)
        ->group_end();

        $subquery = "(SELECT COUNT(*) 
                      FROM products p2 
                      LEFT JOIN ec_vendor v_check2 ON v_check2.vendor_id = p2.vendor_id
                      WHERE p2.status='1' and p2.product_category = p.product_category 
                      AND (p2.vendor_id = 0 OR p2.vendor_id IS NULL OR CAST(v_check2.status AS CHAR) IN ('1', 'approved', 'Approved'))
                      AND p2.id <= p.id) <= 4";

        $this->slave->where($subquery, null, false);
        $this->slave->where_in('p.product_category', $category_ids);
        $this->slave->order_by('p.product_category, p.id');

        $query = $this->slave->get()->result_array();

        $categories = [];
        foreach ($query as $row) {
			$images = json_decode($row['images']);
			$first_image = count($images) ? $images[0] : '';
			$prices = json_decode($row['prices']);
			$first_image = count($prices) && isset($prices[0]->images[0]) ? $prices[0]->images[0] : $first_image;
			if($first_image)
			$image_path = $row['product_type'] == 'attribute' ? '/uploads/attributes/'.$first_image : '/uploads/products/'.$first_image;
			else
			$image_path = '';


			$short_description = $row['short_description'];
			$short_description = (strlen($short_description) > 30) ? substr($short_description, 0, 30) . '...' : $short_description;
            $catId = $row['product_category'];
			$categories[$catId]['product_category_name'] = $product_category[$catId]; 
            $categories[$catId]['products'][] = [
            'id'    => $row['product_id'],
            'name'  => $row['name'],
            'product_type'  => $row['product_type'],
            'prices' => $row['prices'],
            'image' => $row['images'],
            'image_path' => $image_path,
            'short_description' => $short_description,
            'modifiers' => $row['modifiers'],
            'extras'=>$row['extras'] 
            ];
        }

        return $categories;
    }
	
}
