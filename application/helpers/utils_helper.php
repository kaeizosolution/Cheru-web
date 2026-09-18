<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function page_count()
{
    $page_count = 10;
    return $page_count;
}

function g_key()
{
    $g_key = 'AIzaSyDZDczEIV69u0OyAG0B7FtJwQiTc5TzGj0';
    return $g_key;
}

function csrf_token()
{
    $CI =& get_instance();
    $csrf = (object) array(
    'name' => $CI->security->get_csrf_token_name(),
    'hash' => $CI->security->get_csrf_hash()
    );
    return $csrf;
}

function insert_data_utils($table_name,$data)
{
    $CI =& get_instance();
    $CI->master = $CI->load->database('master', TRUE);
    $CI->master->insert($table_name,$data);
    return $CI->master->insert_id();
}

function update_data_utils($table_name,$data,$condition)
{
    $CI =& get_instance();
    $CI->master = $CI->load->database('master', TRUE);
    foreach($condition as $column => $value){
        $CI->master->where($column, $value);
    }
    $CI->master->update($table_name,$data);
    return ($CI->master->affected_rows() > 0) ? TRUE : FALSE;
}

function get_data_utils($table_name,$condition)
{
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('*');
    $CI->slave->from($table_name);
    foreach($condition as $column => $value){
        $CI->slave->where($column, $value);
    }
    $obj = $CI->slave->get()->result();
    if($obj && count($obj) == 1){
        $obj = $obj[0];
    }
    return $obj;
}

function get_country($country_id = NULL)
{
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->cache_on();
    $CI->slave->select('country_id, name');
    $CI->slave->from('ec_country');
    if($country_id){
        $CI->slave->where('country_id', $country_id);
    }
    $CI->slave->order_by("name", "asc");
    if($country_id){
        $obj = $CI->slave->get()->row();
    }else{
        $obj = $CI->slave->get()->result();
    }
    $CI->slave->cache_off();
    return $obj;
}

function get_page_language_data($page = NULL)
{
    $CI =& get_instance();

    // Hardcoded language_id → folder name map (used as fallback when DB lookup fails)
    $hardcoded_lang_names = [
        3  => 'english',
        12 => 'french',
        13 => 'spanish',
        14 => 'canadian',
        15 => 'chinese',
        16 => 'german',
        17 => 'indonesian',
        18 => 'japanese',
        19 => 'korean',
    ];

    $ln = current_language();

    // Also check $_SESSION and cookie as extra fallbacks
    if (!$ln || $ln == '3') {
        $sess_ln = isset($_SESSION['ln']) ? (int)$_SESSION['ln'] : 0;
        if ($sess_ln > 0) $ln = $sess_ln;
        elseif (isset($_COOKIE['cheru_ln']) && (int)$_COOKIE['cheru_ln'] > 0) {
            $ln = (int)$_COOKIE['cheru_ln'];
        }
    }

    $language = get_language($ln);

    // SAFETY CHECK: If language is not found in DB, try hardcoded name
    if (empty($language) || !is_object($language)) {
        $ln_int = (int)$ln;
        if (isset($hardcoded_lang_names[$ln_int])) {
            $language = (object)['language_id' => $ln_int, 'name' => $hardcoded_lang_names[$ln_int]];
        } else {
            // Fallback to English
            $language = get_language(3);
        }
    }

    // Determine folder name
    if (isset($language->name)) {
        $name = strtolower(trim($language->name));
        // Ensure it's a known language folder name
        if (!in_array($name, array_values($hardcoded_lang_names))) {
            $name = 'english';
        }
    } else {
        $name = 'english';
    }

    // Load the language file; fall back to English if the file doesn't exist.
    // CI's lang->load() strips a trailing _lang suffix then re-adds _lang.php,
    // so "home_lang" → "home_lang.php". We mirror that logic in our file_exists check.
    $ci_lang_base  = preg_replace('/_lang$/', '', $page) . '_lang';
    $lang_file     = APPPATH . 'language/' . $name . '/' . $ci_lang_base . '.php';
    $en_lang_file  = APPPATH . 'language/english/' . $ci_lang_base . '.php';

    if (!file_exists($lang_file)) {
        // Try English fallback
        if (file_exists($en_lang_file)) {
            $name = 'english';
        } else {
            // File doesn't exist in any language folder — return empty object
            // so the view doesn't crash with a 500 error.
            return (object)[];
        }
    }

    // Confirm the file exists in BASEPATH or any package path (mirrors CI Lang::load logic)
    // CI's show_error() calls exit(), so we must check before calling load().
    $ci_found = false;
    if (file_exists(BASEPATH . 'language/' . $name . '/' . $ci_lang_base . '.php')) {
        $ci_found = true;
    } else {
        foreach ($CI->load->get_package_paths(TRUE) as $pkg_path) {
            if (file_exists($pkg_path . 'language/' . $name . '/' . $ci_lang_base . '.php')) {
                $ci_found = true;
                break;
            }
        }
    }

    if (!$ci_found) {
        return (object)[];
    }

    $CI->lang->load($page, $name);

    $page_ln_obj = (object) $CI->lang->language;
    return $page_ln_obj;
}

function get_language($language_id = NULL)
{
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->cache_on();
    $CI->slave->select('language_id, name');
    $CI->slave->from('ec_language');
    if($language_id){
        $CI->slave->where('language_id',$language_id);
    }
    $CI->slave->where('status','1');
    $CI->slave->order_by("basic", "desc");
    $CI->slave->order_by("name", "asc");
    if($language_id){
        $obj = $CI->slave->get()->row();
    }else{
        $obj = $CI->slave->get()->result();
    }
    $CI->slave->cache_off();
    return $obj;
}

function current_language($language_id = NULL)
{
    $CI =& get_instance();
    $ln = $CI->session->userdata('ln');
    if($language_id){
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->cache_on();
        $CI->slave->select('language_id, name');
        $CI->slave->from('ec_language');
        $CI->slave->where('language_id',$ln);
        $obj = $CI->slave->get()->row();
        $CI->slave->cache_off();
        return $obj;
    }else{
        return $ln;
    }
}

function current_currency($currency_id = NULL)
{
    $CI =& get_instance();
    $cur = $CI->session->userdata('cur');
    if($currency_id){
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->cache_on();
        $CI->slave->select('currency_id, name, iso_code, symbol, rate, basic');
        $CI->slave->from('ec_currency');
        $CI->slave->where('currency_id',$currency_id);
        $obj = $CI->slave->get()->row();
        $CI->slave->cache_off();
        return $obj;
    }else{
        return $cur;
    }
}

function get_currency($currency_id = NULL)
{
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->cache_on();
    $CI->slave->select('currency_id, name, iso_code, symbol, rate, basic');
    $CI->slave->from('ec_currency');
    if($currency_id){
        $CI->slave->where('currency_id',$currency_id);
    }
    $CI->slave->where('status','1');
    $CI->slave->order_by("basic", "desc");
    $CI->slave->order_by("name", "asc");
    if($currency_id){
        $obj = $CI->slave->get()->row();
        if(is_api() && $obj){
            $obj->symbol = html_entity_decode($obj->symbol);
        }
    }else{
        $obj = $CI->slave->get()->result();
    }
    $CI->slave->cache_off();
    return $obj;
}

function default_currency()
{
    $CI =& get_instance();
    if($CI->session->userdata('cur')){
        $cur = $CI->session->userdata('cur');
    }else{
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->cache_on();
        $CI->slave->select('currency_id');
        $CI->slave->from('ec_currency');
        $CI->slave->where('status','1');
        $CI->slave->where('basic',1);
        $obj = $CI->slave->get()->row();
        $CI->slave->cache_off();
        $cur = $obj->currency_id;
    }
    return $cur;
}

function convert_price($amount, $from_cur_id, $to_cur_id = null)
{
    $CI =& get_instance();
    if ($to_cur_id === null) {
        $to_cur_id = default_currency();
    }
    
    $from_cur = current_currency($from_cur_id);
    $to_cur   = current_currency($to_cur_id);
    
    if (!$from_cur || !$to_cur) {
        return $amount;
    }
    
    $from_rate = (float)$from_cur->rate;
    $to_rate   = (float)$to_cur->rate;
    
    if ($from_rate <= 0) $from_rate = 1.0;
    
    // Convert: amount / from_rate = Price in base currency; Converted = Price in base * to_rate
    $converted = ($amount / $from_rate) * $to_rate;
    return $converted;
}

function convert_and_format_price($amount, $from_cur_id, $to_cur_id = null)
{
    $CI =& get_instance();
    if ($to_cur_id === null) {
        $to_cur_id = default_currency();
    }
    
    $to_cur = current_currency($to_cur_id);
    $converted = convert_price($amount, $from_cur_id, $to_cur_id);
    
    $symbol = $to_cur ? $to_cur->symbol : '$';
    return $symbol . number_format($converted, 2, '.', ',');
}

if (!function_exists('get_base_amount')) {
    function get_base_amount($amount, $currency_id, $currency_rate = null)
    {
        $CI =& get_instance();
        $amount = (float)$amount;
        if ($currency_rate !== null) {
            $rate = (float)$currency_rate;
        } else {
            $CI->slave = $CI->load->database('slave', TRUE);
            $CI->slave->cache_on();
            $CI->slave->select('rate');
            $CI->slave->from('ec_currency');
            $CI->slave->where('currency_id', $currency_id);
            $row = $CI->slave->get()->row();
            $CI->slave->cache_off();
            $rate = $row ? (float)$row->rate : 1.0;
        }
        if ($rate <= 0) {
            $rate = 1.0;
        }
        return $amount / $rate;
    }
}

if (!function_exists('format_currency_for_vendor')) {
    function format_currency_for_vendor($amount_in_base, $vendor_display_currency_id = null)
    {
        $CI =& get_instance();
        $amount_in_base = (float)$amount_in_base;
        if ($vendor_display_currency_id === null) {
            $vendor_display_currency_id = $CI->session->userdata('cur');
        }
        if (!$vendor_display_currency_id) {
            $vendor_display_currency_id = default_currency();
        }
        
        $target_cur = current_currency($vendor_display_currency_id);
        if (!$target_cur) {
            // Absolute fallback
            $target_cur = new stdClass();
            $target_cur->currency_id = 1;
            $target_cur->symbol = '$';
            $target_cur->rate = 1.0;
            $target_cur->iso_code = 'USD';
        }
        
        $rate = (float)$target_cur->rate;
        if ($rate <= 0) {
            $rate = 1.0;
        }
        
        $converted_amount = $amount_in_base * $rate;
        $symbol = html_entity_decode($target_cur->symbol);
        
        return [
            'amount' => $converted_amount,
            'symbol' => $symbol,
            'formatted' => $symbol . number_format($converted_amount, 2, '.', ',')
        ];
    }
}

function default_language()
{
    $CI =& get_instance();
    if($CI->session->userdata('ln')){
        $ln = $CI->session->userdata('ln');
    }else{
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->cache_on();
        $CI->slave->select('language_id');
        $CI->slave->from('ec_language');
        $CI->slave->where('status','1');
        $CI->slave->where('basic',1);
        $obj = $CI->slave->get()->row();
        $CI->slave->cache_off();
        $ln = $obj->language_id;
    }
    return $ln;
}

function get_product($product_id = NULL)
{
     if(!$product_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('id, name, slug, description, product_type');
    $CI->slave->from('products');
    $CI->slave->where('id', $product_id);
    $CI->slave->limit('1');
    $row = $CI->slave->get()->row();
    if ($row) {
        $row->product_id = $row->id;
        $row->product_uid = $row->id;
        $row->post_title = $row->name;
        $row->post_slug = $row->slug;
        $row->post_content = $row->description;
        $row->type = $row->product_type;
        $row->post_type = $row->product_type;
        $row->sale_price = 0;
    }
    return $row;  
}

function get_product_image($product_id = NULL)
{
     if(!$product_id){return false;}
     $CI =& get_instance();
     $CI->slave = $CI->load->database('slave', TRUE);
     $CI->slave->select('file_name');
     $CI->slave->from('ec_gallery');
     $CI->slave->where('product_id', $product_id);
     $CI->slave->order_by("id", "DESC");
     $CI->slave->limit('1');
     $obj = $CI->slave->get()->row();
     //echo $CI->slave->last_query(); exit;
    return $obj;  
}

function ec_product_variation($product_id = NULL)
{
     if(!$product_id){return false;}
     $CI =& get_instance();
     $CI->slave = $CI->load->database('slave', TRUE);
     $CI->slave->select('_thumbnail_id');
     $CI->slave->from('ec_product_variation');
     $CI->slave->where('product_id', $product_id);
     $CI->slave->order_by("variation_id", "DESC");
     $CI->slave->limit('1');
     $obj = $CI->slave->get()->row();
     //echo $CI->slave->last_query(); exit;
    return $obj; 
}

function format_date_limadi_type($date)
{
    $date = date_create($date);
    $date = date_format($date,"d-m-Y H:i");
    return $date;
}

function format_date_mysql_type($date,$date_only = NULL)
{
    $date = date_create($date);
    if($date_only){
        $date = date_format($date,"Y-m-d");
    }else{
        $date = date_format($date,"Y-m-d H:i:s");
    }
    return $date;
}

function random_string_data( $length = 8 )
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}

function valid_email($email){
    $success = 0;
    if (preg_match("/^[a-zA-Z0-9\'\.\-_\+]+@[a-zA-Z0-9\-]+\.([a-zA-Z0-9\-]+\.)*?[a-zA-Z]+$/i",$email)) {
        $success = 1;
    }
    return $success;
}

function is_api()
{
    if(isset($_POST['api']) && $_POST['api'] == 1)
    {
        return 1;
    }
    // Also detect API requests by URL prefix (handles routes like api/vendor/forgot)
    $req_uri = !empty($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '';
    if (strpos($req_uri, '/api/') !== false) {
        return 1;
    }
    return 0;
}

function logged_in()
{
    $CI =& get_instance();
    $TYPE = $CI->session->userdata('type');
    if($CI->session->userdata($TYPE) && isset($CI->session->userdata($TYPE)['logged_in'])){
        return true;
    }else{
        return false;
    }
}

function is_auth()
{
    $CI =& get_instance();
    $TYPE = $CI->session->userdata('type');
    if($CI->session->userdata($TYPE) && isset($CI->session->userdata($TYPE)['logged_in'])){
        if(isset($_POST['api']) && $_POST['api'] == 1){
            $ret_data = array('status' => '1', 'message' => 'Already Login.', 'data' => array());
            header('Content-Type: application/json');
            echo json_encode($ret_data);
            die();
        }else{
            redirect("$TYPE/dashboard");
        }
    }
}

function remove_extra_for_api($msg)
{
    $errors = preg_replace('/<p>|<\/p>|<div>|<\/div>/', '', $msg);
    #$errors = preg_replace('/<div>|<\/div>/', '', $msg);
    $errors = preg_replace('/<div class="error">|<\/div>/', '', $msg);
    $errors = preg_replace('/\n/', ',', $errors);
    $errors = preg_replace('/,$/', '', $errors);
    $errors = preg_replace('/<br\/>/', '', $errors);
    return explode(',', $errors);
}

function uniq_uid ()
{
    $uniqid = uniqid();
    return $uniqid;
}

function message_box($msg, $status = 'success'){
	$response = '';
	$class = 'danger';
	if($status == 'success'){
		$class = 'success';
	}
	if(!empty($msg)){
		$response = '<div class="alert alert-'.$class.' no-margin" style="margin-bottom:15px!important;">'.$msg.'</div>';
	}
	return $response;
}

function banner_type($type = NULL)
{
    $current_language = current_language();
    $array = array(
            '3' => array(1=>"Home Page Banner",2=>"Featured Categories Banner", 3=>"Featured Banner", 4=>"Top Banner", 5=>"Best Selling Banner",6=>"Home page banner middle section"),
            '12' => array(1=>"Banner de la página de inicio",2=>"Banner de categorías destacadas", 3=>"Banner destacado", 4=>"Banner superior", 5=>"Banner más vendido",6=>"Sección intermedia del banner de la página de inicio"),
            );

    if(isset($type)){
        $s_array = $array[$current_language];
        if (array_key_exists($type,$s_array))
        {
            return $s_array[$type];
        }
    }   
}



function get_thumb_link($product_id=null) 
{         
	if ( $product_id ) {
        $img = get_product_image($product_id);
	    return base_url().'assets/uploads/files/'.$img->file_name;
	}else{
	   return null;
	}	
}

function get_cart()
{
    $CI =& get_instance();
    $TYPE = $CI->session->userdata('type');
    if($CI->session->userdata($TYPE) && isset($CI->session->userdata($TYPE)['logged_in'])){
        $customer_id = $CI->session->userdata($TYPE)['login_id'];
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('*');
        $CI->slave->from('ec_cart');
        $CI->slave->where('customer_id', $customer_id);
        $obj = $CI->slave->get()->row();
        if($obj){
            $cart_details = unserialize($obj->cart);
        }else{
            $cart_details = array();            
        }
    }else{
    $cart_details = $CI->session->userdata('cart');
    }
    return $cart_details;
}

function cart_item_count()
{
	$cart_details = get_cart();
    $quantity = 0;
    if($cart_details){
        foreach($cart_details as $product_id => $val){
            foreach($cart_details[$product_id] as $attribute_item_id => $v){
                $item_array = $cart_details[$product_id][$attribute_item_id];
                if($item_array['quantity']){
                    $quantity+= 1; #$item_array['quantity'];
                }
            }
        }
    }
	return $quantity;
}

function get_slug($string){
    $string = trim($string);
    $string = strtolower($string);
    $string = str_replace(' ', '-', $string);
    $slug=preg_replace('/[^A-Za-z0-9-]+/', '', $string);
    return $slug;
} 

function generate_url_slug($string,$table,$field,$key=NULL,$value=NULL)
{
    $CI =& get_instance();
    $slug = url_title($string);
    $slug = strtolower($slug);
    $i = 0;
    $params = array ();
    $params[$field] = $slug;
    if($key)$params["$key !="] = $value; 
    while ($CI->slave->where($params)->get($table)->num_rows())
    {   
        if (!preg_match ('/-{1}[0-9]+$/', $slug ))
            $slug .= '-' . ++$i;
        else
            $slug = preg_replace ('/[0-9]+$/', ++$i, $slug );

        $params [$field] = $slug;
    }
    return $slug;   
}

#This function add by maharaj

function order_status($type = NULL)
{
    $array = array(
        1  => "Ordered", 
        15 => "Approved", 
        5  => "Cancelled By Customer", 
        2  => "Shipped", 
        3  => "Partially Shipped", 
        10 => "Delivered", 
        16 => "In Process", 
        17 => "Cancelled By Vendor", 
        18 => "Out For Delivery", 
        19 => "In Process", 
        8  => "Awaiting Pickup"
    );

    // If $type is null, return the whole array
    if ($type === NULL) {
        return $array;
    }

    // Check if the key exists in the array to avoid "Undefined Index"
    if (isset($array[$type])) {
        return $array[$type];
    }
    
    // Optional: Handle common string mappings if your controller sends strings
    if ($type === 'pending') {
        return $array[1]; // Return "Ordered"
    }

    // Return a default string or the array if the specific key wasn't found
    return "Unknown Status"; 
}


function order_item_status($type = NULL) // for API Display status used in api_order_track function
{
   $array = array(1=> 'Ordered', 16=> 'Ordered', 19=>'In Process', 2=>'Shipped', 18=>'Out For Delivery', 10=>'Delivered');

    if(isset($type) && $array[$type]){
      return $array[$type];
   	}
	return $array;
    
}


function get_customer($customer_id = NULL)
{
    if(!$customer_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('customer_uid, fname, lname, user_img, email, mobile, preferred_currency_id');
    $CI->slave->from('ec_customer');
    $CI->slave->where('customer_id', $customer_id);
    $obj = $CI->slave->get()->row();
    return $obj;  
}

function get_driver_id($driver_uid = NULL)
{
    if(!$driver_uid){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('driver_id');
    $CI->slave->from('ec_driver');
    $CI->slave->where('driver_uid', $driver_uid);
    $obj = $CI->slave->get()->row();
    return $obj;  
}



function get_shipping_info($shipping_address_id = NULL)
{
    if(!$shipping_address_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('fullname, mobile, address_1, latitude, longitude, shipping_address_id');
    $CI->slave->from('ec_shipping_address');
    $CI->slave->where('shipping_address_id', $shipping_address_id);
    $obj = $CI->slave->get()->row();
    return $obj;  
}

function delivery_information()
{
    $current_language = current_language();
    $delivery_information = array(
        '3' => "Normally delivered in 3 -5 days,Please check exact dates in the Checkout page.",
        '12' => "Normalmente se entrega en 3-5 días, verifique las fechas exactas en la página de pago.",
    );
    return $delivery_information[$current_language];
}

function return_policy()
{
    $current_language = current_language();
    $return_policy = array(
        '3' => "Free return within 15 days for Jumia Mall items and 7 days for other eligible items.",
        '12' => "Devolución gratuita en 15 días para los artículos de Jumia Mall y en 7 días para otros artículos elegibles.",
    );
    return $return_policy[$current_language];
}

function get_order_id($order_uid = NULL)
{
    if(!$order_uid){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('order_id, total');
    $CI->slave->from('ec_order');
    $CI->slave->where('order_uid', $order_uid);
    $obj = $CI->slave->get()->row();
    //echo $CI->slave->last_query(); exit;
    return $obj;
}


function get_order_uid($order_id = NULL)
{
    if(!$order_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('order_uid, payment_mode, shipping_address_id');
    $CI->slave->from('ec_order');
    $CI->slave->where('order_id', $order_id);
    $obj = $CI->slave->get()->row();
    return $obj;
}

function get_paymentstatus($order_id = NULL)
{
    if(!$order_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('payment_mode');
    $CI->slave->from('ec_order');
    $CI->slave->where('order_id', $order_id);
    $obj = $CI->slave->get()->row();
    return $obj;
}



function get_shipping_address($cmeta_id = NULL)
{
    if(!$cmeta_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('shipping_first_name, shipping_last_name, shipping_mobile, shipping_address_1, shipping_city, shipping_street, shipping_postcode, shipping_country');
    $CI->slave->from('ec_customermeta');
    $CI->slave->where('cmeta_id', $cmeta_id);
    $obj = $CI->slave->get()->row();
    return $obj;
}

function wfile($args)
{
    //$file = fopen("/home1/kaeizner/tsatsa/public_html/logs.txt","w");
    //fwrite($file, print_r($args, true));
    //fwrite($file, $args);
    //fclose($file);
}


function get_supplier($supplier_id = NULL)
{
    if(!$supplier_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('cname');
    $CI->slave->from('ec_supplier');
    $CI->slave->where('supplier_id', $supplier_id);
    $obj = $CI->slave->get()->row();
    return $obj;
}

function lead_time()
{
    return 'Lead Time 15 Days';
}

function shipping_time()
{
    return 'Shipping Time 4-7 Days';
}


function get_categories_id($product_id = NULL)
{
     if(!$product_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    //$CI->slave->select('category_id, name, thumbnail');
	$CI->slave->select('category_id'); //modify by mify
    $CI->slave->from('ec_product_categories');
    $CI->slave->where('post_id', $product_id);
    $CI->slave->limit('1');
    $obj = $CI->slave->get()->row();
    //echo $CI->slave->last_Query(); exit;
    return $obj;  
}

function get_categories($category_id = NULL)
{
     if(!$category_id){return false;}
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->select('id, name');
    $CI->slave->from('ec_categories_prod');
    $CI->slave->where('id', $category_id);
    $CI->slave->limit('1');
    $obj = $CI->slave->get()->row();
    //echo $CI->slave->last_Query(); exit;
    return $obj;  
}

function split_words($string, $nb_caracs, $separator){
		
    $string = strip_tags(html_entity_decode($string));
	
    if( strlen($string) < $nb_caracs ){
        $final_string = $string;
    } else {
        $final_string = "";
        $words = explode(" ", $string);
        foreach( $words as $value ){
            if( strlen($final_string . " " . $value) < $nb_caracs ){
                if( !empty($final_string) ) $final_string .= " ";
                $final_string .= $value;
            } else {
                break;
            }
        }
        $final_string .= $separator;
    }
	
    return $final_string;
}

function get_categories_new($parent_id=0){
    $CI =& get_instance();
        $CI->slave->select('*');
        $CI->slave->from('ec_categories_prod');
		$CI->slave->where('parent_id', $parent_id);
        //$this->slave->where('featured_cat', 1);
        $CI->slave->limit(8, 0);
        $parent = $CI->slave->get();
		$categories = $parent->result();
        $i=0;
        foreach($categories as $p_cat){
                $categories[$i]->sub = get_categories_new($p_cat->id);
                $i++;
        }
        return $categories;
    }

function get_categories_new_more($parent_id=0){
        $CI =& get_instance();
        $CI->slave->select('*');
        $CI->slave->from('ec_categories_prod');
        $CI->slave->where('parent_id', $parent_id);
        $CI->slave->where('featured_cat = 0');
        //$this->slave->limit(9, 0);
        $parent = $CI->slave->get();
        $categories = $parent->result();
        $i=0;
        foreach($categories as $p_cat){
                $categories[$i]->sub = get_categories_new($p_cat->id);
                $i++;
        }
        return $categories;
    }

 
	function loadThis($data = array()) 
    {
		$CI =& get_instance();  
		$data = $CI->load->view("admin/header", $data);
		$data = $CI->load->view("admin/access/access", $data);
		$data = $CI->load->view("admin/footer", $data);
		return $data;
    }
	
	function in_multiarray($elem, $array,$field)
	{
		$top = sizeof($array) - 1;
		$bottom = 0;
		while($bottom <= $top)
		{
			if($array[$bottom][$field] == $elem)
				return true;
			else 
				if(is_array($array[$bottom][$field]))
					if(in_multiarray($elem, ($array[$bottom][$field])))
						return true;

			$bottom++;
		}        
		return false;
	}

	function objectToArray($d) {
			if (is_object($d)) {
				// Gets the properties of the given object
				// with get_object_vars function
				$d = get_object_vars($d);
			}
			
			if (is_array($d)) {
				/*
				* Return array converted to object
				* Using __FUNCTION__ (Magic constant)
				* for recursive call
				*/
				return array_map(__FUNCTION__, $d);
			}
			else {
				// Return array
				return $d;
			}
		}

	function api_response($args)
	{
		$ret_data = array();
		$status = $args['status'] ?? 0;
		$msg = gettype($args['msg']) == 'array' ? $args['msg'] : array($args['msg']);
		$data = gettype($args['data']) == 'array' ? $args['data'] : array($args['data']);

		$ret_data['STATUS'] = $status;
		$ret_data['MSG'] = $msg[0];
		$ret_data['DATA'] = $data;
		$ret_data['SESSION'] = isset($_SESSION['customer']['logged_in']) ? '1' : '0';

		header('Content-Type: application/json');
		echo json_encode($ret_data);
		die();
	}	


    function apiresponse($args)
	{
		$ret_data = array();
		$status = $args['status'] ? 1 : 0;
		$msg = gettype($args['msg']) == 'array' ? $args['msg'] : array($args['msg']);
		$data = gettype($args['data']) == 'array' ? $args['data'] : array($args['data']);
		$action = gettype($args['action']) == 'array' ? $args['action'] : array($args['action']);


		$ret_data['STATUS'] = $status;
		$ret_data['MSG'] = $msg[0];
		$ret_data['DATA'] = $data;
		$ret_data['ACTION'] = $action[0];
		$ret_data['SESSION'] = isset($_SESSION['user']['logged_in']) ? '1' : '0';

		header('Content-Type: application/json');
		echo json_encode($ret_data);
		die();
	}	
    
    

	function get_otp()
	{
		$otp = mt_rand(1000,9999);
	    return $otp; 		
	}	


	function get_favorite_store($customer_id=NULL)
	{
		if(!$customer_id){return false;}
		$CI =& get_instance();
		$CI->slave = $CI->load->database('slave', TRUE);
		$CI->slave->select('vendor_id');
		$CI->slave->from('ec_favourite_store');
		$CI->slave->where('customer_id', $customer_id);
		$obj = $CI->slave->get()->result();
		$id_wise = array();
        if($obj)
        {
            foreach($obj as $row)
            {
                $id_wise[] = $row->vendor_id;
            }
        }
        $CI->slave->cache_off();
        return $id_wise;
	}

	function location_type()
	{
		$arr = array(1=>'Office',2=>'home',3=>'other');
		return $arr;
	}

	function getLocation($args)
	{
		$lat = $args['latitude']; //$this->input->post('latitude');
        $long = $args['longitude']; //$this->input->post('longitude');

        $ret_data = [];
        $ret_data['lat']    = $lat;
        $ret_data['long']   = $long;

        //$this->session->set_userdata('set_lat_long', $ret_data);
		$get_geo_loc = get_geo_loc(array('lat'=>$lat, 'long'=>$long));
		if($get_geo_loc)
		{
			$api_data = [];
			$api_data['address']	        = $get_geo_loc['address'];
			$api_data['sublocality_level_2']= $get_geo_loc['sublocality_level_2'];
			$api_data['sublocality_level_1']= $get_geo_loc['sublocality_level_1'];
			$api_data['locality']	        = $get_geo_loc['locality'];

			$api_data['city']	            = $get_geo_loc['locality'];
            $api_data['area']               = $get_geo_loc['sublocality_level_2'];
            $api_data['country']            = $get_geo_loc['country'];            
			$api_data['display_address']	= $get_geo_loc['display_address'];

			return $api_data;
			//api_response(array('status'=>1, 'msg'=>'location address', 'data'=>$api_data));
		}
	}

	function get_geo_loc($args)
	{
		$latitude = trim($args['lat']); 
		$longitude = trim($args['long']);
        $formatted_address = $sublocality_level_2 = $sublocality_level_1 = '';
        $locality = '';
		if($latitude && $longitude)
		{
            #$g_key = 'AIzaSyDkYJmQ8bD4bQBd16J3eNnkXZAaipg5a6c';
            $g_key = 'AIzaSyDZDczEIV69u0OyAG0B7FtJwQiTc5TzGj0';//IMOSYS
			#$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$g_key";
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$g_key&sensor=false"; 
    		$json = @file_get_contents($url);
    		$data = json_decode($json);
    		$status = $data->status;
    
    		if($status == "OK")
			{
        		$formatted_address = $data->results[0]->formatted_address;

                #print_r($data->results[0]->address_components);

				if($data->results[0]->address_components)
				{
					foreach($data->results[0]->address_components as $row_wise)
					{
				        if(in_array('sublocality_level_2', $row_wise->types))
                        {
                            $sublocality_level_2 = $row_wise->short_name;
                        }
                        if(in_array('sublocality_level_1', $row_wise->types))
                        {
                            $sublocality_level_1 = $row_wise->short_name;
                        }
                        if(in_array('locality', $row_wise->types))
                        {
                            $locality = $row_wise->short_name;
                        } 
                        if(in_array('country', $row_wise->types))
                        {
                            $country = $row_wise->long_name;
                        }		
					}
				}
    		}
		}

        $ret_data['address']  = $formatted_address;
        $ret_data['sublocality_level_2']= $sublocality_level_2;
        $ret_data['sublocality_level_1']= $sublocality_level_1;
        $ret_data['locality']           = $locality;
        $ret_data['country']            = $country;
        $display_address                = $sublocality_level_2.', '.$sublocality_level_1.', '.$locality;

        $display_address    = preg_replace('/^,/','', $display_address);
        $display_address    = preg_replace('/, ,/',',', $display_address);
        $display_address    = ltrim($display_address," , ");
        $display_address    = rtrim($display_address," , ");

        $ret_data['display_address']    = $display_address;
 
		return $ret_data;
	}

    function address_by_place_id($args=null)
    {
        $place_id = $args['place_id'];
        $formatted_address = $sublocality_level_2 = $sublocality_level_1 = '';
        $locality = '';

        if($place_id)
        {
            #$g_key = 'AIzaSyDkYJmQ8bD4bQBd16J3eNnkXZAaipg5a6c';
            $g_key = 'AIzaSyDZDczEIV69u0OyAG0B7FtJwQiTc5TzGj0';
            $url =  "https://maps.googleapis.com/maps/api/place/details/json?place_id=$place_id&key=$g_key";
            $json = @file_get_contents($url);
            $data = json_decode($json);
            $status = isset($data->status) ? $data->status : '';
            if($status == "OK")
            {
                $formatted_address = $data->result->formatted_address;
                foreach($data->result->address_components as $row_wise)
                {
                    if(in_array('sublocality_level_2', $row_wise->types))
                    {
                        $sublocality_level_2 = $row_wise->short_name;
                    }
                    if(in_array('sublocality_level_1', $row_wise->types))
                    {
                        $sublocality_level_1 = $row_wise->short_name;
                    }
                    if(in_array('locality', $row_wise->types))
                    {
                        $locality = $row_wise->short_name;
                    }
                }
            }
        }
        $ret_data['address']            = $formatted_address;
        $ret_data['sublocality_level_2']= $sublocality_level_2;
        $ret_data['sublocality_level_1']= $sublocality_level_1;
        $ret_data['locality']           = $locality; 
        $display_address                = $sublocality_level_2.', '.$sublocality_level_1.', '.$locality;
        $display_address    = preg_replace('/^,/','', $display_address);
        $display_address    = preg_replace('/, ,/',',', $display_address);
        $display_address    = ltrim($display_address," , ");
        $display_address    = rtrim($display_address," , ");
        
        $ret_data['display_address']    = $display_address;
        
        
        return $ret_data; 
    }

	function percentage_off($args)
	{
		$sale_price = $args['sale_price']; $org_price = $args['org_price'];
		$discount = $org_price - $sale_price;
		$disPercent = $org_price!='0.00' ? ($discount /$org_price) * 100 : 0;
		return round($disPercent);

	}
		
	function name_trim($args)
	{
		$title = $args['title'];
		$title = strlen($title) > 45 ? substr($title,0,45)."..." : $title;
		return $title;
	}

    function ec_admin($supplier_id = NULL)
    {
        $CI =& get_instance();
        if($supplier_id){
            $CI->slave = $CI->load->database('slave', TRUE);
            $CI->slave->cache_on();
            $CI->slave->select('*');
            $CI->slave->from('ec_admin');
            $CI->slave->where('admin_id',$supplier_id);
            $obj = $CI->slave->get()->row();
            $CI->slave->cache_off();
            return $obj;
        }else{
            return $cur;
        }
    }

	function get_attribute()
	{
		$CI =& get_instance();
		$CI->slave = $CI->load->database('slave', TRUE);
		$CI->slave->select('attribute_id, login_id, name');
		$CI->slave->from('ec_attribute');
		$obj = $CI->slave->get()->result();
		$id_wise = array();
        if($obj)
        {
            foreach($obj as $row)
            {
                $id_wise[$row->attribute_id] = $row->name;
            }
        }
        $CI->slave->cache_off();
        return $id_wise;
	}

	function get_wishlist_productid($customer_id = NULL)
    {   
        $CI =& get_instance();
        if($customer_id){
            $CI->slave = $CI->load->database('slave', TRUE);
            $CI->slave->cache_on();
            $CI->slave->select('product_id, status');
            $CI->slave->from('ec_wishlist');
            $CI->slave->where('customer_id',$customer_id);
            $CI->slave->where('status', '1');
            $obj = $CI->slave->get()->result();
			$id_wise = array();
        	if($obj)
        	{
            	foreach($obj as $row)
            	{
                	$id_wise[$row->product_id] = $row->status;
            	}
        	}
            $CI->slave->cache_off();
            return $id_wise;
        }else{
            return $cur;
        }

   }


	
	
	function synonym()
	{
		$arr['shoe'] = 'shoe shoes';
		$arr['shoes'] = 'shoe shoes';

		return $arr;
	}

    function cat_synonym()
    {
        $arr['mobile']  = 'Mobile';
        $arr['home']    = 'Home';

        return $arr;
    }

	function get_wishlist_by_customer()
	{
		$CI =& get_instance();
		$CI->slave = $CI->load->database('slave', TRUE);
		$CI->slave->select('wishlist_id,customer_id,product_id');
		$CI->slave->from('ec_wishlist');
		$obj = $CI->slave->get()->result();
		$id_wise = array();
        if($obj)
        {
            foreach($obj as $row)
            {
                $id_wise[$row->attribute_id] = $row->name;
            }
        }
        $CI->slave->cache_off();
        return $id_wise;
	}


	function get_brand($brand_id = NULL)
	{
		$CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('brand_id, name, image');
        $CI->slave->from('ec_brand');
        $brand = $CI->slave->get()->result();
		return $brand;
	}

    
	function getbrand($brand_id = NULL)
	{
		$CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('brand_id, name, image');
        $CI->slave->from('ec_brand');
        $CI->slave->where('brand_id', $brand_id);
        $brand = $CI->slave->get()->row();
		return $brand;
	}

	function getcategories()
	{
		$CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('id, parent_id, slug, name, thumbnail');
        $CI->slave->from('ec_categories_prod');
        $CI->slave->where('status', '1');
        $brand = $CI->slave->get()->result();
		return $brand;
	}

    function categories_name($cat_id = NULL)
	{
		$CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('name, slug, thumbnail, banner_image');
        $CI->slave->from('ec_categories_prod');
        $CI->slave->where('parent_id', '0');
        $CI->slave->where('id', $cat_id);
        $brand = $CI->slave->get()->row();
		return $brand;
	}
    function categories()
    {
        $CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('*');
        $CI->slave->from('ec_categories_prod');
        $CI->slave->where('parent_id', '0');
        $categories = $CI->slave->get()->result();
        return $categories;
    }

    function check_discount($args)
    {
                
        $discount = 0;
        $sale_price_dates_from = $args['sale_price_dates_from'];
        $sale_price_dates_to   = $args['sale_price_dates_to'];
        if($sale_price_dates_from && $sale_price_dates_from != '0000-00-00 00:00:00' && $sale_price_dates_to && $sale_price_dates_to != '0000-00-00 00:00:00')
        {
            $ctime = time();
            $sale_ftime = strtotime($sale_price_dates_from);
            $sale_ttime = strtotime($sale_price_dates_to);
            if($ctime > $sale_ftime && $ctime < $sale_ttime)
            {
                $discount = 1;
            }
        }
        return $discount;

    }

   
    function vehicle_type_price()
    {
        $price = array(1=> '5', 2=>'7', 3=>'10');
        return $price;
    }
 
    function codexworldGetDistanceOpt($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
{
    $rad = M_PI / 180;
    //Calculate distance from latitude and longitude
    $theta = $longitudeFrom - $longitudeTo;
    $dist = sin($latitudeFrom * $rad) 
        * sin($latitudeTo * $rad) +  cos($latitudeFrom * $rad)
        * cos($latitudeTo * $rad) * cos($theta * $rad);

    return acos($dist) / $rad * 60 *  1.853;
} 
    
function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers') {
    $theta = $longitude1 - $longitude2;
  //  (6371 * 2 * ASIN(SQRT( POWER(SIN(( '$lat' - latitude) *  pi()/180 / 2), 2) +COS( '$lat' * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( '$long' - longitude) * pi()/180 / 2), 2) ))) as distance
 
    $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
    $distance = acos($distance); 
    $distance = rad2deg($distance); 
    $distance = $distance * 60 * 1.1515; 
    switch($unit) { 
        case 'miles': 
            break; 
        case 'kilometers' : 
            $distance = $distance * 1.609344; 
    } 
    return (round($distance,2)); 
}

function GetDrivingDistance($args)
{
    if(!$args['place_id_s'] || !$args['place_id_d'])
    {
        return false;
    }
    $place_id_s = $args['place_id_s'];
    $place_id_d = $args['place_id_d'];
    $g_key = g_key();

    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=place_id:$place_id_s&destinations=place_id:$place_id_d&key=$g_key";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response, true);


    #$dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
    #$time = $response_a['rows'][0]['elements'][0]['duration']['text'];
    if($response_a['status'] == 'OK')
    return $response_a['rows'][0]['elements'][0];
    else
    return array('status'=>'INVALID');
}


    function review_mode()
    {
        $review_mode = array(1=>'Very Poor', 2=>'Poor', 3=>'Average', 4=>'Good', 5=>'Excellent');
        return $review_mode;
    }

    function vehicle_type()
{
    // Get the current language ID (3 = English, 12 = French)
    $current_language = current_language();

    // Define English Array
    $arr_en = array('1'=>'Two Wheeler', '2'=>'Three Wheeler', '3'=>'Four Wheeler');

    // Define French Array
    $arr_fr = array('1'=>'Deux roues', '2'=>'Trois roues', '3'=>'Quatre roues');

    // Return based on ID (12 is French)
    if($current_language == '12'){
        return $arr_fr;
    } else {
        return $arr_en;
    }
}
         
    function offer_ctype()
    {
        $offer  = array(1 => 'Category', 2 => 'Brand', 3 => 'Product');
        return $offer;
    }

    function detail_page_url($args)
    {
        if(is_object($args)){
            $id = isset($args->id) && $args->id ? $args->id : (isset($args->product_id) && $args->product_id ? $args->product_id : 0);
            $slug = isset($args->slug) && trim((string)$args->slug) !== '' ? trim((string)$args->slug) : (isset($args->post_slug) && trim((string)$args->post_slug) !== '' ? trim((string)$args->post_slug) : '');
            if($id){
                if($slug !== ''){
                    return '/product/detail/'.$slug.'-'.$id;
                }
                return '/product/details/'.$id;
            }
        } elseif(is_array($args)){
            $id = isset($args['id']) && $args['id'] ? $args['id'] : (isset($args['product_id']) && $args['product_id'] ? $args['product_id'] : 0);
            $slug = isset($args['slug']) && trim((string)$args['slug']) !== '' ? trim((string)$args['slug']) : (isset($args['post_slug']) && trim((string)$args['post_slug']) !== '' ? trim((string)$args['post_slug']) : '');
            if($id){
                if($slug !== ''){
                    return '/product/detail/'.$slug.'-'.$id;
                }
                return '/product/details/'.$id;
            }
        }
        return '/';
    }

	function get_permalink($product_slug=null) 
	{         
		if ( $product_slug ) {
		   return base_url().'product/detail/'.$product_slug;
		}else{
		   return null;
		}		
	}

    function get_slug_puid($str)
    {
        $str = explode('-',$str);
        $str_split = end($str);
        array_pop($str);
        $str = implode('-', $str);

        $ret_data = [];
        $ret_data['slug'] = $str;
        $ret_data['puid'] = $str_split;
        return $ret_data;
    }

    function is_discount($product_obj)
    {
        $discount = 0;
        $sale_price_dates_from = $product_obj->sale_price_dates_from;
        $sale_price_dates_to   = $product_obj->sale_price_dates_to;
        $product_obj->vendor_id = $product_obj->user_id;

        if($sale_price_dates_from && $sale_price_dates_from != '0000-00-00 00:00:00' && $sale_price_dates_to && $sale_price_dates_to != '0000-00-00 00:00:00'){
            $ctime = time();
            $sale_ftime = strtotime($sale_price_dates_from);
            $sale_ttime = strtotime($sale_price_dates_to);
            if($ctime > $sale_ftime && $ctime < $sale_ttime){
                $discount = 1;
            }
        }

        return $discount;
    }

    function get_screen_no()
    {
        $arr = array('0'=>1,'1'=>2, '2'=>3, '3'=>4, '4'=>5, '5'=>6, '6'=>7);
        return $arr;
    }

    function distance($lat1, $lon1, $lat2, $lon2, $unit) 
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));  $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
  
        if ($unit == "K") 
        {
            $km = ($miles * 1.609344);
            $km = sprintf('%.02f', $km);
            return $km;
        } 
        else if ($unit == "N") 
        {
            return ($miles * 0.8684);
        } 
        else 
        {
            return $miles;
        }   
    }


    function get_vendor_id($admin_uid = NULL)
    {
        $CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->cache_on();
        $CI->slave->select('vendor_id');
        $CI->slave->from('ec_vendor');
        if($admin_uid){
            $CI->slave->where('vendor_uid', $admin_uid);
        }
        if($admin_uid){
            $obj = $CI->slave->get()->row();
        }else{
            $obj = $CI->slave->get()->result();
        }
        $CI->slave->cache_off();
        return $obj; 
    }


    function get_month()
    {
        $month = $months = array('January'=> '01', 'February'=>'02', 'March'=>'03', 'April'=>'04', 'May'=>'05', 'June'=>'06', 'July'=>'07',     'August'=>'08', 'September'=>'09', 'October'=>10, 'November'=>11, 'December'=>12);

        return $month;

    }

    function get_delivered_address($shipping_address_id = NULL)
    {
        if(!$shipping_address_id){return false;}
        $CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('place_id, fullname, mobile, address_1, latitude, longitude');
        $CI->slave->from('ec_shipping_address');
        $CI->slave->where('shipping_address_id', $shipping_address_id);
        //$CI->slave->where('default_address','1');
        $obj = $CI->slave->get()->row();
        //echo $CI->slave->last_query(); exit;
        return $obj; 
    }
    
    function get_pickup_address($admin_id = NULL)
    {
        if(!$admin_id){return false;}
        $CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('place_id, store_name, mobile, address, latitude, longitude');
        $CI->slave->from('ec_admin');
        $CI->slave->where('admin_id', $admin_id);
        $obj = $CI->slave->get()->row();
        //echo $CI->slave->last_query(); exit;
        return $obj; 
    }

    function get_driver_rating($driver_id = NULL)
    {
       if(!$driver_id){return false;}
        $CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('*');
        $CI->slave->from('ec_driver_review');
        $CI->slave->where('driver_id', $driver_id);
        $obj = $CI->slave->get()->row();
        //echo $CI->slave->last_query(); exit;
        return $obj; 
    }


    function dd($data){
        echo '<pre style="color:red">';
        print_r($data);
        echo '</pre>';
        exit;
    }

    function d($data){
        echo '<pre style="color:seagreen">';
        print_r($data);
        echo '</pre>';
    
    }

    function get_vehicle_type($vehicle_type_id = NULL)
    {
       if(!$vehicle_type_id){return false;}
        $CI =& get_instance();
        $CI->slave = $CI->load->database('slave', TRUE);
        $CI->slave->select('*');
        $CI->slave->from('ec_vehicle_type');
        $CI->slave->where('vehicle_type_id', $vehicle_type_id);
        $obj = $CI->slave->get()->row();
        //echo $CI->slave->last_query(); exit;
        return $obj; 
    }

	function food_type()
	{
		return $hash = [1=>'Veg', 2=>'Non Veg'];
	}

	function product_category()
	{
		return $hash = [1=>'Food', 2=>'Fast Food', 3=>'Icecream', 4=>'Pastry', 5=>'Grocery', 6=>'Butcher by Kilo'];
	}

	function product_category_by_slug($slug=null)
	{
		$hash = ['food'=>1, 'fast-food'=>2, 'icecream'=>3, 'pastry'=>4, 'grocery'=>5, 'butcher-by-kilo'=>6];
		return isset($hash[$slug]) ? $hash[$slug] : '';
	}


	function get_cart_counts() {
		$CI =& get_instance(); 
		$cart = $CI->session->userdata('cart_contents');

		if (!is_array($cart) || empty($cart)) {
				return [
						'total_items' => 0,
						'unique_items' => 0
				];
		}

		$total_items = isset($cart['total_items']) && is_numeric($cart['total_items']) 
				? (int)$cart['total_items'] 
				: 0;

		$unique_items = 0;
		foreach ($cart as $key => $item) {
				if (is_array($item) && isset($item['qty'])) {
						$unique_items++;
				}
		}

		return [
				'total_items' => $total_items,
				'unique_items' => $unique_items
		];
}

?>
