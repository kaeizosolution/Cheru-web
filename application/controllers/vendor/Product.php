<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product extends MY_Controller {

    function __construct() 
{ 
    parent::__construct();
    $this->load->model('Query_model');  
    $this->load->model('Product_model'); 
    $this->load->model('Category_prod_model');
    $this->load->model('Tags_prod_model');      
    $this->load->model('files');
    $this->load->model('Attribute_model'); 
    $this->load->library('csvimport');
	$this->config->load('custom_config');
	$this->load->helper('api');

    // Ensure TYPE is set
    $this->TYPE = $this->session->userdata('type') ? $this->session->userdata('type') : 'vendor';
    
    $user_session = $this->session->userdata($this->TYPE);

    // FIX: Updated keys to match the new session structure
    $this->LOGIN_ID   = isset($user_session['login_id']) ? (int)$user_session['login_id'] : (isset($user_session['vendor_id']) ? (int)$user_session['vendor_id'] : 0);
    $this->FNAME      = isset($user_session['fname']) ? $user_session['fname'] : (isset($user_session['vendor_name']) ? $user_session['vendor_name'] : 'Vendor');
    $this->STORE_NAME = isset($user_session['store_name']) ? $user_session['store_name'] : '';
}

	private function _get_vendor_access_token()
	{
		$sess = $this->session->userdata($this->TYPE);
		if (is_array($sess) && !empty($sess['access_token'])) {
			return (string)$sess['access_token'];
		}
		if (!empty($_SESSION['token'])) {
			return (string)$_SESSION['token'];
		}
		return '';
	}

	private function _build_api_headers()
	{
		$token = $this->_get_vendor_access_token();
		if ($token === '') {
			return [];
		}
		return ['Authorization' => 'Bearer ' . $token];
	}

	public function index()
	{
    // Redirect if not logged in
    if(!logged_in()) redirect("$this->TYPE/auth/login");

    $page_lang = get_page_language_data('admin_page_lang');
    $crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", $page_lang->product => "");
    $breadcrumbs = $this->breadcrumbs->show($crumbs);
    
    $data['breadcrumbs'] = $breadcrumbs;
    $data['csrf']        = csrf_token(); 
    $data['TYPE']        = $this->TYPE;
    $data['vendor_id']   = $this->LOGIN_ID;
    $data['page_count']  = page_count();

    // Pass API token to view so DataTable can call API directly from browser
    $access_token         = $this->_get_vendor_access_token();
    $data['access_token'] = $access_token;
    $data['use_api']      = (bool)$this->config->item('use_api') && ($access_token !== '');

    $this->load->template("$this->TYPE/product/index_product", $data);       
}

    public function index_ajax_post()
{
    $length = (isset($_POST['length']))?$_POST['length']: page_count();
    $start  = (isset($_POST['start']))?$_POST['start']: 0;
    $page   = ($length > 0) ? (int)($start / $length) + 1 : 1;

    $type_array = array(1 => 'Fixed', 2 => 'Percentage');
    
    $use_api = (bool)$this->config->item('use_api');
	$headers = $this->_build_api_headers();
    $list = [];
    $recordsTotal = 0;

	if ($use_api && !empty($headers)) {
        // Extract filters
        $name = isset($_POST['filter']['name']) ? $_POST['filter']['name'] : '';
        $date_added = isset($_POST['filter']['date_added']) ? $_POST['filter']['date_added'] : '';
        $status = isset($_POST['filter']['status']) ? $_POST['filter']['status'] : '';

        $url = 'vendor/products?page=' . $page . '&limit=' . $length;
        if ($name !== '') $url .= '&name=' . urlencode($name);
        if ($date_added !== '') $url .= '&date_added=' . urlencode($date_added);
        if ($status !== '') $url .= '&status=' . urlencode($status);

		$api_list = call_api('GET', $url, null, $headers);
		if ($api_list['response'] !== null && (int)($api_list['response']['status'] ?? 0) === 1 && is_array($api_list['response']['data'] ?? null)) {
			$api_data = $api_list['response']['data'];
			$rows = is_array($api_data['products'] ?? null) ? $api_data['products'] : [];
            $recordsTotal = (int)($api_data['pagination']['total'] ?? count($rows));
			foreach ($rows as $r) {
				if (!is_array($r)) {
					continue;
				}
				$obj = new stdClass();
				$obj->id = (int)($r['id'] ?? 0);
				$obj->name = $r['name'] ?? '';
				$obj->date_added = $r['date_added'] ?? date('Y-m-d');
				$obj->status = (int)($r['status'] ?? 1);
				$obj->type = 'simple';
				$obj->stock = (int)($r['stock'] ?? 0);
				$obj->slug = $obj->id;
				$list[] = $obj;
			}
		} else {
			$list = $this->Product_model->get_datatable((int)$this->LOGIN_ID);
            $recordsTotal = $this->Product_model->count_all((int)$this->LOGIN_ID);
		}
	} else {
		$list = $this->Product_model->get_datatable((int)$this->LOGIN_ID);
        $recordsTotal = $this->Product_model->count_all((int)$this->LOGIN_ID);
	}
    
    $data = array();
    foreach ($list as $obj) {

        $row = array();
        $stock_count = $this->stock_count(array('obj'=>$obj));
        
        // --- SAFE SLUG LOGIC ---
        $safe_slug = $obj->id; 
        if(isset($obj->slug) && !empty($obj->slug)) {
            $safe_slug = $obj->slug;
        } elseif(isset($obj->post_slug) && !empty($obj->post_slug)) {
            $safe_slug = $obj->post_slug;
        }

        $obj->post_slug = $safe_slug; 
        $obj->product_uid = isset($obj->product_uid) ? $obj->product_uid : $obj->id;
        
        $url = detail_page_url($obj);
        $autho_name = 'Admin'; 
        
        $row['product_id']   = $obj->id; // Ensure this maps to your DB ID
        
        if($obj->status == 1) {
            $row['post_title'] = '<a href="'.$url.'" target="_blank" onClick="todtl(\''.$safe_slug.'\')">'.$obj->name.'</a>';
        } else {
            $row['post_title'] = $obj->name;
        }

        $row['user_id']      = $autho_name;
        $row['comment']      = '-';
        
        $d_date = isset($obj->date_added) ? $obj->date_added : date('Y-m-d');
        $row['date']         = date("d-m-Y", strtotime($d_date));
        
        $row['status']       = $obj->status;           
        $row['type']         = isset($obj->product_type) ? $obj->product_type : (isset($obj->type) ? $obj->type : 'simple');
        $row['stock_count']  = $stock_count;
        $row['status_str']   = ($obj->status == 1) ? 'Active' : 'Inactive';
        
        // --- ADDED THIS LINE TO PREVENT DATATABLE WARNINGS ---
        $row['action']       = ''; 
        
        $data[] = $row;
    } 
    
    $output = array(
            "draw" => isset($_POST['draw'])?$_POST['draw']:'',
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => $data,
            );  

    echo json_encode($output);
}

    public function stock_count($args)
    {
        $obj = $args['obj'];
        $type = isset($obj->type) ? $obj->type : 'simple';
        $stock = 0;
        
        if($type == 'simple')
        {
            $stock = isset($obj->stock) ? $obj->stock : 0; 
        }
        else if($type == 'variable')
        {
			$cnt = $this->db
				->from('product_variations')
				->where('product_id', (int)$obj->id)
				->count_all_results();
			$stock = $cnt ? (int)$cnt : 0;
        }
        return $stock;
    }


    /************upload image************/
    function files_upload(){
        $data = array();
        if($this->input->post('submitForm') && !empty($_FILES['upload_Files']['name'])){

            $filesCount = count($_FILES['upload_Files']['name']);
            for($i = 0; $i < $filesCount; $i++){
                $_FILES['upload_File']['name'] = $_FILES['upload_Files']['name'][$i];
                $_FILES['upload_File']['type'] = $_FILES['upload_Files']['type'][$i];
                $_FILES['upload_File']['tmp_name'] = $_FILES['upload_Files']['tmp_name'][$i];
                $_FILES['upload_File']['error'] = $_FILES['upload_Files']['error'][$i];
                $_FILES['upload_File']['size'] = $_FILES['upload_Files']['size'][$i];
                
                $uploadPath = './assets/uploads/files/'.$this->input->post('sku').'/';

                if (!mkdir($uploadPath, 0777, true)) {
                    // die('Failed to create folders...'); 
                }

                $config['upload_path'] = $uploadPath;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|webp|avif';                
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if($this->upload->do_upload('upload_File')){
                    $fileData = $this->upload->data();
                    $uploadData[$i]['file_name'] = $fileData['file_name'];
                    $uploadData[$i]['created'] = date("Y-m-d H:i:s");
                    $uploadData[$i]['modified'] = date("Y-m-d H:i:s"); 
                }
            }            
            if(!empty($uploadData)){
                $insert = $this->files->insert($uploadData);
                $statusMsg = $insert?'Files uploaded successfully.':'Some problem occurred, please try again.';
                $this->session->set_flashdata('statusMsg',$statusMsg);
            }
        }
        $data['gallery'] = $this->files->getRows();
        $this->load->template("$this->TYPE/product/upload_view", $data);
    }

    /******************end*********/

    public function get_slug($string){
        $string = trim($string);
        $string = strtolower($string);
        $string = str_replace(' ', '-', $string);
		$slug   = preg_replace('/[^A-Za-z0-9-]+/', '', $string);
		$slug   = preg_replace("/[\-]+/", '-', $slug);
		return $slug;
    }

public function add()
{
    if(!logged_in()) redirect("$this->TYPE/auth/login");

    // --- 1. CHECK FOR FORM SUBMISSION (POST) ---
    if($this->input->post()) {
        $use_api = (bool)$this->config->item('use_api');
        $headers = $this->_build_api_headers();
        
        $this->load->library('form_validation');
        $this->form_validation->set_rules('post_title', 'Product Name', 'trim|required');
        $sku_rules = 'trim|required';
        if ($this->db->field_exists('sku', 'products')) {
            $sku_rules .= '|is_unique[products.sku]';
        }
        $this->form_validation->set_rules('sku', 'SKU', $sku_rules);
        $this->form_validation->set_rules('cat_id', 'Category', 'trim|required');

        if ($this->form_validation->run() === TRUE) {
            if ($use_api && !empty($headers)) {
                $api_create = call_api('POST', 'vendor/products', [
                    'name' => (string)$this->input->post('post_title'),
                    'price' => (float)$this->input->post('sale_price'),
                    'stock' => (int)$this->input->post('stock'),
                    'category_id' => (int)$this->input->post('cat_id'),
                    'description' => (string)$this->input->post('post_content'),
                ], $headers);
                if ($api_create['response'] !== null && (int)($api_create['response']['status'] ?? 0) === 1) {
                    $this->session->set_flashdata('success', 'Product Added Successfully.');
                    redirect("$this->TYPE/product");
                    return;
                }
            }
            
            $this->db->trans_begin();
            try {
                $product_data = array(
                    'name' => (string)$this->input->post('post_title'),
                    'description' => (string)$this->input->post('post_content'),
                    'product_type' => (string)$this->input->post('type'),
                    'category' => (string)$this->input->post('cat_id'),
                    'sub_category' => (string)$this->input->post('sub_cat_id'),
                    'status' => (string)$this->input->post('enabled'),
                    'vendor_id' => (int)$this->LOGIN_ID,
                );
                if ($this->db->field_exists('sku', 'products')) {
                    $product_data['sku'] = (string)$this->input->post('sku');
                }
                if ($this->db->field_exists('stock', 'products')) {
                    $product_data['stock'] = (int)$this->input->post('stock');
                }
                if ($this->db->field_exists('brand_id', 'products')) {
                    $product_data['brand_id'] = $this->input->post('brand_id') !== '' ? (int)$this->input->post('brand_id') : null;
                }

                $this->db->insert('products', $product_data);
                $product_id = (int)$this->db->insert_id();
                if (!$product_id) {
                    throw new Exception('Database Error: Could not save product.');
                }

                // Always create at least one variation row
                $this->db->insert('product_variations', array(
                    'product_id' => $product_id,
                    'attr_json' => null,
                ));
                $variation_id = (int)$this->db->insert_id();
                if (!$variation_id) {
                    throw new Exception('Database Error: Could not save product variation.');
                }

                // Base price tier (min_qty 0) — convert vendor currency → base (USD)
                $sale_price = $this->input->post('sale_price');
                $regular_price = $this->input->post('regular_price');
                $price_val = $sale_price !== '' ? (float)$sale_price : (float)$regular_price;
                // Convert from vendor preferred currency to base currency for storage
                $price_val = $this->_convert_price_to_base($price_val, (int)$this->LOGIN_ID);
                if ($price_val > 0) {
                    $this->db->insert('product_variation_price', array(
                        'variation_id' => $variation_id,
                        'min_qty' => 0,
                        'max_qty' => null,
                        'price' => $price_val,
                    ));
                }

                // Upload images into uploads/products and store in product_variation_images
                if (!empty($_FILES['upload_Files']['name'][0])) {
                    $uploadDir = rtrim(FCPATH, '/\\') . '/uploads/products/';
                    $uploadDir = str_replace('\\', '/', $uploadDir);
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                        @chmod($uploadDir, 0755);
                    }
                    if (is_dir($uploadDir) && is_writable($uploadDir)) {
                        $this->load->library('upload');
                        $filesCount = count($_FILES['upload_Files']['name']);
                        $existing_count = 0;
                        for ($i = 0; $i < $filesCount; $i++) {
                            if ($existing_count >= 5) {
                                break;
                            }
                            if (empty($_FILES['upload_Files']['name'][$i])) {
                                continue;
                            }
                            $_FILES['temp_file'] = array(
                                'name' => $_FILES['upload_Files']['name'][$i],
                                'type' => $_FILES['upload_Files']['type'][$i],
                                'tmp_name' => $_FILES['upload_Files']['tmp_name'][$i],
                                'error' => $_FILES['upload_Files']['error'][$i],
                                'size' => $_FILES['upload_Files']['size'][$i],
                            );
                            $config = array(
                                'upload_path' => $uploadDir,
                                'allowed_types' => 'jpg|jpeg|png|webp|avif',
                                'max_size' => '20240000',
                                'encrypt_name' => TRUE,
                            );
                            $this->upload->initialize($config);
                            if ($this->upload->do_upload('temp_file')) {
                                $ud = $this->upload->data();
                                if (!empty($ud['file_name'])) {
                                    $this->db->insert('product_variation_images', array(
                                        'variation_id' => $variation_id,
                                        'image_path' => (string)$ud['file_name'],
                                    ));
                                    $existing_count++;
                                }
                            }
                        }
                    }
                }

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Transaction Failed');
                }
                $this->db->trans_commit();

                $this->session->set_flashdata('success', 'Product Added Successfully.');
                redirect("$this->TYPE/product");
                return;
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $e->getMessage());
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
        }
    }

    // --- 2. LOAD THE FORM (GET Request) ---
    $data = array();
    
    // Breadcrumbs
    $page_lang = get_page_language_data('admin_page_lang');
    $home_label = isset($page_lang->home) ? $page_lang->home : 'Home';
    $prod_label = isset($page_lang->product) ? $page_lang->product : 'Products';
    
    $crumbs = array(
        $home_label => "/$this->TYPE/dashboard",
        $prod_label => "/$this->TYPE/product",
        "Add New" => ""
    );
    $data['breadcrumbs'] = $this->breadcrumbs->show($crumbs);

    // Categories
    $cat_list = $this->Category_prod_model->find_list_prod_vendor();
    $data['categories_obj'] = isset($cat_list['data']) ? $cat_list['data'] : array();
    
    // --- LOAD ATTRIBUTES FOR VARIABLE PRODUCTS ---
    $this->load->model('Attribute_model'); 
    $data['all_attributes'] = $this->Attribute_model->get_all_attribute(); 
    // --------------------------------------------

    $data['get_product'] = array();
    $data['csrf'] = csrf_token();
    $data['TYPE'] = $this->TYPE;

    $this->load->template("$this->TYPE/product/add", $data);
    return;
}

public function update($product_id)
{
    if(!logged_in()) redirect("$this->TYPE/auth/login");
    $this->post((int)$product_id);
}

function post($product_id = NULL)
{
    if(!logged_in()) redirect("$this->TYPE/auth/login");

    $data = array();

    $cat_list_prod = $this->Category_prod_model->find_list_prod_vendor();
    $categories_obj = isset($cat_list_prod['data']) ? $cat_list_prod['data'] : array();

    $this->load->library('form_validation');
    $this->form_validation->set_rules('sku', 'SKU', 'trim|required|callback_unique_sku['.$product_id.']');
    $this->form_validation->set_rules('post_title', 'Post Title', 'trim|required');
    $this->form_validation->set_rules('cat_id', 'Category', 'trim|required');

    if($product_id){
        $product_obj = $this->Query_model->get_data_obj('products', array('id' => (int)$product_id, 'vendor_id' => $this->LOGIN_ID));
        if(!$product_obj){
            $this->session->set_flashdata('error', 'Invalid Product.');
            redirect("$this->TYPE/product");
            return;
        }
    }

    if ($this->input->post() && $product_id) {
        if ($this->form_validation->run() === TRUE) {
            $use_api = (bool)$this->config->item('use_api');
            $headers = $this->_build_api_headers();
            if ($use_api && !empty($headers)) {
                $payload = array(
                    'name' => (string)$this->input->post('post_title'),
                    'description' => (string)$this->input->post('post_content'),
                    'product_type' => (string)$this->input->post('type'),
                    'category' => (string)$this->input->post('cat_id'),
                    'category_id' => (int)$this->input->post('cat_id'),
                    'sub_category' => (string)$this->input->post('sub_cat_id'),
                    'status' => (string)$this->input->post('enabled'),
                    'sku' => (string)$this->input->post('sku'),
                    'stock' => (int)$this->input->post('stock'),
                    'brand_id' => $this->input->post('brand_id') !== '' ? (int)$this->input->post('brand_id') : null,
                    'price' => $this->input->post('sale_price') !== '' ? (float)$this->input->post('sale_price') : (float)$this->input->post('regular_price'),
                );
                
                $has_files = false;
                if (!empty($_FILES['upload_Files']['name'][0])) {
                    $filesCount = count($_FILES['upload_Files']['name']);
                    for ($i = 0; $i < $filesCount; $i++) {
                        if (empty($_FILES['upload_Files']['name'][$i])) {
                            continue;
                        }
                        $payload['upload_Files[' . $i . ']'] = new CURLFile(
                            $_FILES['upload_Files']['tmp_name'][$i],
                            $_FILES['upload_Files']['type'][$i],
                            $_FILES['upload_Files']['name'][$i]
                        );
                        $has_files = true;
                    }
                }
                if ($has_files) {
                    $payload['__is_multipart'] = true;
                }

                $api_res = call_api('POST', 'vendor/products/' . $product_id, $payload, $headers);
                if ($api_res['response'] !== null) {
                    $resp = $api_res['response'];
                    if ((int)($resp['status'] ?? 0) === 1) {
                        $this->session->set_flashdata('success', $resp['message'] ?? 'Product Updated Successfully.');
                        redirect("$this->TYPE/product");
                        return;
                    } else {
                        $err = $resp['errors'][0] ?? ($resp['message'] ?? 'Failed to update product.');
                        $this->session->set_flashdata('error', $err);
                        redirect("$this->TYPE/product");
                        return;
                    }
                }
            }

            $this->db->trans_begin();
            try {
                $product_data = array(
                    'name' => (string)$this->input->post('post_title'),
                    'description' => (string)$this->input->post('post_content'),
                    'product_type' => (string)$this->input->post('type'),
                    'category' => (string)$this->input->post('cat_id'),
                    'sub_category' => (string)$this->input->post('sub_cat_id'),
                    'status' => (string)$this->input->post('enabled'),
                );
                if ($this->db->field_exists('sku', 'products')) {
                    $product_data['sku'] = (string)$this->input->post('sku');
                }
                if ($this->db->field_exists('stock', 'products')) {
                    $product_data['stock'] = (int)$this->input->post('stock');
                }
                if ($this->db->field_exists('brand_id', 'products')) {
                    $product_data['brand_id'] = $this->input->post('brand_id') !== '' ? (int)$this->input->post('brand_id') : null;
                }

                $this->db->where('id', (int)$product_id);
                $this->db->where('vendor_id', (int)$this->LOGIN_ID);
                $this->db->update('products', $product_data);

                $variation_row = $this->db
                    ->select('id')
                    ->from('product_variations')
                    ->where('product_id', (int)$product_id)
                    ->order_by('id', 'ASC')
                    ->limit(1)
                    ->get()->row();
                $variation_id = $variation_row && isset($variation_row->id) ? (int)$variation_row->id : 0;
                if (!$variation_id) {
                    $this->db->insert('product_variations', array(
                        'product_id' => (int)$product_id,
                        'attr_json' => null,
                    ));
                    $variation_id = (int)$this->db->insert_id();
                }
                if (!$variation_id) {
                    throw new Exception('Database Error: Could not save product variation.');
                }

                $sale_price = $this->input->post('sale_price');
                $regular_price = $this->input->post('regular_price');
                $price_val = $sale_price !== '' ? (float)$sale_price : (float)$regular_price;
                // Convert from vendor preferred currency to base currency for storage
                $price_val = $this->_convert_price_to_base($price_val, (int)$this->LOGIN_ID);
                if ($price_val > 0) {
                    $price_row = $this->db
                        ->select('id')
                        ->from('product_variation_price')
                        ->where('variation_id', (int)$variation_id)
                        ->where('min_qty', 0)
                        ->limit(1)
                        ->get()->row();
                    if ($price_row && isset($price_row->id)) {
                        $this->db->where('id', (int)$price_row->id)->update('product_variation_price', array('price' => $price_val));
                    } else {
                        $this->db->insert('product_variation_price', array(
                            'variation_id' => (int)$variation_id,
                            'min_qty' => 0,
                            'max_qty' => null,
                            'price' => $price_val,
                        ));
                    }
                }

                if (!empty($_FILES['upload_Files']['name'][0])) {
                    $uploadDir = rtrim(FCPATH, '/\\') . '/uploads/products/';
                    $uploadDir = str_replace('\\', '/', $uploadDir);
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                        @chmod($uploadDir, 0755);
                    }
                    if (is_dir($uploadDir) && is_writable($uploadDir)) {
                        $this->load->library('upload');
                        $filesCount = count($_FILES['upload_Files']['name']);
                        for ($i = 0; $i < $filesCount; $i++) {
                            if (empty($_FILES['upload_Files']['name'][$i])) {
                                continue;
                            }
                            $_FILES['temp_file'] = array(
                                'name' => $_FILES['upload_Files']['name'][$i],
                                'type' => $_FILES['upload_Files']['type'][$i],
                                'tmp_name' => $_FILES['upload_Files']['tmp_name'][$i],
                                'error' => $_FILES['upload_Files']['error'][$i],
                                'size' => $_FILES['upload_Files']['size'][$i],
                            );
                            $config = array(
                                'upload_path' => $uploadDir,
                                'allowed_types' => 'jpg|jpeg|png|webp|avif',
                                'max_size' => '20240000',
                                'encrypt_name' => TRUE,
                            );
                            $this->upload->initialize($config);
                            if ($this->upload->do_upload('temp_file')) {
                                $ud = $this->upload->data();
                                if (!empty($ud['file_name'])) {
                                    $this->db->insert('product_variation_images', array(
                                        'variation_id' => (int)$variation_id,
                                        'image_path' => (string)$ud['file_name'],
                                    ));
                                }
                            }
                        }
                    }
                }

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Transaction Failed');
                }
                $this->db->trans_commit();

                $this->session->set_flashdata('success', 'Product Updated Successfully.');
                redirect("$this->TYPE/product");
                return;
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', $e->getMessage());
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
        }
    }

    $data['brand_obj'] = $this->Query_model->get_data('ec_brand', array('status' => '1'));
    $data['supplier_obj'] = (object)array('supplier_id' => $this->LOGIN_ID, 'cname' => $this->STORE_NAME);
    $data['categories_obj'] = $categories_obj;
    $data['get_product'] = array();
    if($product_id){
        $data['get_product'] = $this->Query_model->get_data_obj('products', array('id' => (int)$product_id, 'vendor_id' => $this->LOGIN_ID));
    }
    $data['csrf'] = csrf_token();
    $data['TYPE'] = $this->TYPE;
    $this->load->template("$this->TYPE/product/add", $data);
    return;
}

public function ajax_update_product_status()
{
    $product_id = (int)$this->input->post('product_id');
    $status = (int)$this->input->post('status');
    $csrf_hash = $this->security->get_csrf_hash();

    $enabled = '1';
    if($status == 1){
        $enabled = '0';
    }

    $use_api = (bool)$this->config->item('use_api');
    $headers = $this->_build_api_headers();
    if ($use_api && !empty($headers)) {
        $payload = array(
            'status' => (string)$enabled
        );
        $api_res = call_api('POST', 'vendor/products/' . $product_id, $payload, $headers);
        if ($api_res['response'] !== null) {
            $resp = $api_res['response'];
            $success = ((int)($resp['status'] ?? 0) === 1) ? 1 : 0;
            echo json_encode(array('success' => $success, 'csrf_token' => $csrf_hash, 'status' => (string)$enabled));
            return;
        }
    }

    $updated = $this->Query_model->update_data(
        'products',
        array('status' => (string)$enabled),
        array('id' => $product_id, 'vendor_id' => $this->LOGIN_ID)
    );
    echo json_encode(array('success' => $updated ? 1 : 0, 'csrf_token' => $csrf_hash, 'status' => (string)$enabled));
    return;
}

public function checkall_status_change()
{
    $id = $this->input->post('id');
    $status = (string)$this->input->post('status');
    $csrf_hash = $this->security->get_csrf_hash();

    $ids = json_decode((string)$id, true);
    if (!is_array($ids)) {
        $ids = array();
    }

    $enabled = ($status === '1' || $status === 'active' || $status === 'Active') ? '1' : '0';
    $success = 0;

    $clean_ids = array();
    foreach ($ids as $pid) {
        $pid = (int)$pid;
        if ($pid > 0) {
            $clean_ids[] = $pid;
        }
    }
    $clean_ids = array_values(array_unique($clean_ids));

    if ($this->LOGIN_ID && count($clean_ids)) {
        $use_api = (bool)$this->config->item('use_api');
        $headers = $this->_build_api_headers();
        if ($use_api && !empty($headers)) {
            $success_cnt = 0;
            foreach ($clean_ids as $pid) {
                $payload = array(
                    'status' => (string)$enabled
                );
                $api_res = call_api('POST', 'vendor/products/' . $pid, $payload, $headers);
                if ($api_res['response'] !== null && (int)($api_res['response']['status'] ?? 0) === 1) {
                    $success_cnt++;
                }
            }
            $success = ($success_cnt === count($clean_ids)) ? 1 : 0;
            echo json_encode(array('success' => $success, 'csrf_token' => $csrf_hash, 'status' => (string)$enabled));
            return;
        }

        $this->db->where('vendor_id', (int)$this->LOGIN_ID);
        $this->db->where_in('id', $clean_ids);
        $this->db->set('status', (string)$enabled);
        $this->db->update('products');
        $success = ($this->db->affected_rows() >= 0) ? 1 : 0;
    }

    echo json_encode(array('success' => $success, 'csrf_token' => $csrf_hash, 'status' => (string)$enabled));
}

public function delimg()
{
    $data['csrf'] = csrf_token();
    $id = (int)$this->input->post('id');
    $product_id = (int)$this->input->post('product_id');
    $imgdeltype = (string)$this->input->post('imgdeltype');
    $delete = 0;

    if ($this->LOGIN_ID && $id && $product_id) {
        $prod = $this->Query_model->get_data_obj('products', array('id' => $product_id, 'vendor_id' => $this->LOGIN_ID));
        if ($prod) {
            if ($imgdeltype === 'variton') {
                $delete = $this->Product_model->delete_image_variation($id, $product_id);
            } else {
                $delete = $this->Product_model->delete_image($id, $product_id);
            }
        }
    }

    echo json_encode(array('success' => $delete ? 1 : 0));
}

public function delete_variation()
{
    $data['csrf'] = csrf_token();
    $variation_id = (int)$this->input->post('variation_id');
    $product_id = (int)$this->input->post('product_id');
    $delete = 0;

	if ($this->LOGIN_ID && $variation_id && $product_id) {
		$prod = $this->Query_model->get_data_obj('products', array('id' => $product_id, 'vendor_id' => $this->LOGIN_ID));
		if ($prod) {
			$delete = $this->Product_model->delete_variation_post($variation_id, $product_id);
		}
	}

	echo json_encode(array('success' => $delete ? 1 : 0));
}

public function delete($product_id = null)
{
	if(!logged_in()) redirect("$this->TYPE/auth/login");
	$product_id = (int)$product_id;
	if (!$product_id) {
		$this->session->set_flashdata('error', 'Invalid Product.');
		redirect("$this->TYPE/product");
		return;
	}

	$prod = $this->Query_model->get_data_obj('products', array('id' => $product_id, 'vendor_id' => $this->LOGIN_ID));
	if (!$prod) {
		$this->session->set_flashdata('error', 'Invalid Product.');
		redirect("$this->TYPE/product");
		return;
	}

	$use_api = (bool)$this->config->item('use_api');
	$headers = $this->_build_api_headers();
	if ($use_api && !empty($headers)) {
		$api_res = call_api('POST', 'vendor/products/delete/' . $product_id, null, $headers);
		if ($api_res['response'] !== null) {
			$resp = $api_res['response'];
			if ((int)($resp['status'] ?? 0) === 1) {
				$this->session->set_flashdata('success', $resp['message'] ?? 'Deleted Successfully.');
			} else {
				$err = $resp['errors'][0] ?? ($resp['message'] ?? 'Delete failed.');
				$this->session->set_flashdata('error', $err);
			}
			redirect("$this->TYPE/product");
			return;
		}
	}

	$deleted = $this->Product_model->delete_post($product_id);
	if ($deleted) {
		$this->session->set_flashdata('success', 'Deleted Successfully.');
	} else {
		$this->session->set_flashdata('error', 'Delete failed.');
	}
	redirect("$this->TYPE/product");
}

public function upload_color_image($FILES)
{
	$img = preg_replace('/\s*/', '', $FILES['file']['name']);
	$full_img = strtolower($img);
	$ret_data = array(); $ret_data['status'] = 0; $ret_data['msg'] = '';
	$ret_data['data'] = '';

	if($FILES['file']['name'])
	{
		$config = array(
			'upload_path' => "./uploads/products/",
			'allowed_types' => 'gif|jpg|jpeg|png|webp|avif',
			'max_size' => "20240000",
			'encrypt_name'  => TRUE
		);

		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		if($this->upload->do_upload('file'))
		{
			$fileData = $this->upload->data();
			$this->load->library("image_lib");

			$config['image_library']    = 'gd2';
			$config['source_image']    = $fileData["full_path"];
			$config['new_image']       = "./uploads/products/".$fileData['file_name'];
			$config['create_thumb']    = TRUE;
			$config['maintain_ratio']  = TRUE;
			$config['width']           = 800;
			$config['height']          = 800;

			$this->image_lib->initialize($config);
			$this->image_lib->resize();

			$thumbnail = $fileData['raw_name'].'_thumb'.$fileData['file_ext'];
			$ret_data['status'] = 1;
			$ret_data['msg'] = '';
			$ret_data['data'] = $thumbnail;
		}
		else
		{
			$ret_data['status'] = 0; $ret_data['data'] = '';
			$ret_data['msg'] = $this->upload->display_errors();
		}
	}
	return $ret_data;
}

public function export(){ 
    
    $filename = 'product_report_'.date('Ymd').'.csv'; 
    header("Content-Description: File Transfer"); 
    header("Content-Disposition: attachment; filename=$filename"); 
    header("Content-Type: application/csv; ");  
    $list = $this->Product_model->get_export($this->LOGIN_ID);      
    
    // file creation  
    $file = fopen('php://output', 'w'); 
    $header = array("product_id","product_uid","post_title","post_title_es","post_content","post_content_es","sale_price","regular_price",
    "sku","stock","weight","length","width","height","quantity_range","price_range","post_type","type","created");   

    fputcsv($file, $header);
      
    foreach ($list as $key=>$line){ 
       fputcsv($file,$line); 
    }
   fclose($file); 
   exit; 
}

    public function insert_vendor_cat($args)
    {
        $vendor_cat_ids = $args['vendor_cat_ids'];
        $insert_vendor_data['id'] = $args['cat_id'];
        $insert_vendor_data['vendor_id'] = $this->LOGIN_ID;
        $insert_vendor_data['is_parent'] = $args['is_parent'];
        $insert_vendor_data['product_id'] = $args['product_id'];

        if(!in_array($args['cat_id'], $vendor_cat_ids))
        {
            $this->db->insert('ec_vendor_categories', $insert_vendor_data);
        }
    }

    
        

    public function parent_categories()
    {
        $parent_cat = $this->Query_model->get_data('ec_categories_prod', array('parent_id'=>'0'));  
        $parent_ids = [];
        if($parent_cat)
        {
            foreach($parent_cat as $row)
            {
                $parent_ids[] = $row->id; 
            }
        }
        return $parent_ids;
    }

    public function delete_gallery($args)
    {
        return false;
    }

    public function insert_gallery($args)
    {
        return false;
    }

    public function ajax_sub_cat()
    {
        $cat_id = $this->input->post('category_id');
        $sub_cat_arr = $this->Query_model->get_data('ec_categories_prod', array('status'=>'1', 'parent_id'=>$cat_id));
        if($sub_cat_arr)
        api_response(array('status'=>1, 'msg'=>'success', 'data'=>$sub_cat_arr));
        else
        api_response(array('status'=>0, 'msg'=>'fail', 'data'=>array()));
                
    }

    public function ajax_brand_combo()
    {
        $cat_id = $this->input->post('category_id');
        $brand_arr = $this->Query_model->get_data('ec_brand', array('status'=>'1', 'cat_id'=>$cat_id));
        if($brand_arr)
        api_response(array('status'=>1, 'msg'=>'success', 'data'=>$brand_arr));
        else
        api_response(array('status'=>0, 'msg'=>'fail', 'data'=>array()));
    }   
    
    public function validation_img($id = NULL)
    {       
        echo "xx<pre>"; print_r($_FILES); echo "</pre>";die;
        
        if(isset($_FILES["upload_Files"]["name"]) && $_FILES["upload_Files"]["name"] != '' ){
            $config = array(                
                'upload_path' => "./assets/vendor/images",  //"./assets/images" 
                'allowed_types' => '*', //"gif|jpg|png|jpeg|pdf",
                'max_size' => "20240000",
            );                      
            $this->load->library('Upload', $config);
            if ( ! $this->upload->do_upload('upload_Files')) {
                 $error = array('error' => $this->upload->display_errors()); 
                 echo json_encode(
                        array(
                        'msg' => 'File type not match or something Missing.', 
                        'success' => 0, 
                        'data' => array()
                        )
                    );                  
            }else{ 
            
                
                $data_file = $this->upload->data(); 
                $file_name = $data_file["file_name"];
                $thumb_arr = explode(".", $file_name);
                $large = $file_name;
                $thumb = $thumb_arr[0] . "_thumb". "." . $thumb_arr[1];            
                $config = array(
                    'image_library'     =>  'gd2', //get original image
                    'source_image'      => "./assets/vendor/images/".$data_file['file_name'],
                    'maintain_ratio'    => true,
                    'allowed_types'     => '*', //"gif|jpg|png|jpeg|pdf",
                    'width'             =>150,
                    'height'            =>150, 
                    'new_image'         => "./assets/vendor/images/".$thumb,                
                );                          
                $this->load->library('image_lib', $config); 
                $this->upload->initialize($config);              
                $this->image_lib->resize();
                $this->image_lib->clear();      
                
                $image = serialize(array('large'=>base_url().'/assets/vendor/images/'.$large,'thumb'=>base_url().'/assets/vendor/images/'.$thumb));
                $data['upload_Files'] = $image;             
                $condition = array('admin_id' => $id);              
                $this->Query_model->update_data('ec_admin',$data, $condition);  
                echo json_encode(
                    array(
                    'msg' => 'Updated Successfully.', 
                    'success' => 1, 
                    'data' => array()
                    )
                );  
            }
      }  
    }   


    // ─── Currency Conversion Helpers ────────────────────────────────────────

    /**
     * Convert a price from the vendor's preferred currency to base (USD) for storage.
     */
    private function _convert_price_to_base($price, $vendor_id)
    {
        $info = $this->_get_vendor_currency_info($vendor_id);
        if (!$info['needs_conversion'] || $info['vendor_rate'] <= 0) {
            return round((float)$price, 4);
        }
        return round((float)$price / $info['vendor_rate'] * $info['base_rate'], 4);
    }

    private function _get_vendor_currency_info($vendor_id)
    {
        $base = $this->db
            ->select('currency_id, rate')
            ->from('ec_currency')
            ->where('basic', 1)
            ->where('status', '1')
            ->limit(1)
            ->get()
            ->row();

        $base_id   = $base ? (int)$base->currency_id   : 1;
        $base_rate = $base ? (float)$base->rate          : 1.0;
        if ($base_rate <= 0) $base_rate = 1.0;

        $preferred_id = 0;
        if ($this->db->field_exists('preferred_currency_id', 'ec_vendor')) {
            $vrow = $this->db
                ->select('preferred_currency_id')
                ->from('ec_vendor')
                ->where('vendor_id', $vendor_id)
                ->limit(1)
                ->get()
                ->row();
            $preferred_id = (int)($vrow->preferred_currency_id ?? 0);
        }

        if ($preferred_id <= 0) {
            $preferred_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
        }

        if ($preferred_id <= 0 || $preferred_id === $base_id) {
            return [
                'vendor_currency_id' => $base_id,
                'vendor_rate'        => $base_rate,
                'base_currency_id'   => $base_id,
                'base_rate'          => $base_rate,
                'needs_conversion'   => false,
            ];
        }

        $vcurr = $this->db
            ->select('currency_id, rate')
            ->from('ec_currency')
            ->where('currency_id', $preferred_id)
            ->where('status', '1')
            ->limit(1)
            ->get()
            ->row();

        if (!$vcurr || (float)$vcurr->rate <= 0) {
            return [
                'vendor_currency_id' => $base_id,
                'vendor_rate'        => $base_rate,
                'base_currency_id'   => $base_id,
                'base_rate'          => $base_rate,
                'needs_conversion'   => false,
            ];
        }

        return [
            'vendor_currency_id' => (int)$vcurr->currency_id,
            'vendor_rate'        => (float)$vcurr->rate,
            'base_currency_id'   => $base_id,
            'base_rate'          => $base_rate,
            'needs_conversion'   => true,
        ];
    }

}