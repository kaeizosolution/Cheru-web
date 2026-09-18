<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function page_count()
{
    $page_count = 10;
    return $page_count;
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

function get_country()
{
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->cache_on();
    $CI->slave->select('country_id, name');
    $CI->slave->from('ec_country');
    $CI->slave->order_by("name", "asc");
    $obj = $CI->slave->get()->result();
    $CI->slave->cache_off();
    return $obj;
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
    $CI->slave->order_by("name", "asc");
    $obj = $CI->slave->get()->result();
    $CI->slave->cache_off();
    return $obj;
}

function get_currency($currency_id = NULL)
{
    $CI =& get_instance();
    $CI->slave = $CI->load->database('slave', TRUE);
    $CI->slave->cache_on();
    $CI->slave->select('currency_id, name, iso_code, symbol, rate');
    $CI->slave->from('ec_currency');
    if($currency_id){
        $CI->slave->where('currency_id',$currency_id);
    }
    $CI->slave->where('status','1');
    $CI->slave->order_by("name", "asc");
    $obj = $CI->slave->get()->result();
    $CI->slave->cache_off();
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

function random_string( $length = 8 )
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
            $ret_data = array('status' => 'success', 'message' => 'Already Login.', 'data' => array());
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
    $array = array(1=>"Home Page Banner",2=>"Featured Categories Banner", 3=>"Featured Banner", 4=>"New Arrivals Banner", 5=>"Best Selling Banner");
    if(isset($type) && $array[$type]){
        return $array[$type];
    }
    return $array;
}
function get_permalink($product_slug=null) {       
         
	if ( $product_slug ) {
	   return base_url().'/product/detail/'.$product_slug;
	}else{
	   return null;
	}		
}


?>
