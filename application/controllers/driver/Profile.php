<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Order_model');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }


    public function profile()
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['error'] = false;
        $data['type'] = '';
        $data['message']['error'] = '';
        $amount     =  $this->driver_amount(); 
        $condition  = array('driver_id' => $this->LOGIN_ID);
        $data_obj   = $this->Query_model->get_data_obj('ec_driver', $condition);
 
        if(is_api() && $this->input->post('request_type') == 'get')
        {
            $data_obj->user_img = base_url()."assets/driver_profile/".$data_obj->user_img;
            $data_obj->noti_count = $this->Query_model->count_all('ec_notification', array('driver_id'=>$this->LOGIN_ID, 'show_noti'=>'1', 'type'=>'2'));
            api_response(array('status' => 1, 'msg' => 'success', 'data' => $data_obj));
        } 

        $this->profile_validation();
        
        if($this->form_validation->run())
        {
            if($_POST) {

                $data =  array(
                        'driver_id'       => $this->LOGIN_ID,
                        'screen_id'       => 1,
                        'name'            => $this->input->post('name'),
                        'email'           => $this->input->post('email'),
                        'gender'          => $this->input->post('gender'),
                        'flat_no'         => $this->input->post('flat_no'),
                        'street'          => $this->input->post('street'),
                        'city'            => $this->input->post('city'),
                        'state'           => $this->input->post('state'),
                        'zip_code'        => $this->input->post('zip_code'),
                        'address'         => $this->input->post('address'),
                        );
            }else{
                $data['screen_id']      = 1;
                $data['driver_id']      = $userdetail->driver_id;
                $data['name']           = $userdetail->name;
                $data['email']          = $userdetail->email;
                $data['gender']         = $userdetail->gender;
                $data['flat_no']        = $userdetail->flat_no;
                $data['street']         = $userdetail->street;
                $data['city']           = $userdetail->city;
                $data['state']          = $userdetail->state;
                $data['zip_code']       = $userdetail->zip_code;
                $data['address']        = $userdetail->address;
            }

            $details = array(
                    'mobile'        => $userdetail->mobile,
                    'type'          => 'driver',
                    'name'          => $userdetail->name,
                    'login_id'      => $userdetail->driver_id,
                    'driver_img'    => $userdetail->user_img,
                    'logged_in'     => TRUE
                    );

            $this->session->set_userdata('driver', $details);

            $status    = $this->Query_model->update_data('ec_driver',$data, $condition);
            $msg  = 'Successfully Update.';
            $data = array($data);

            api_response(array('status' => '1', 'msg' => $msg, 'data' => $data));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

    }
 
    public function noti_count($args)
    {
        $this->Query_model->count_all('ec_notification', array('driver_id'=>$args['driver_id'], 'show_noti'=>'1'));
    } 

    public function profile_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('name', 'Full Name', 'trim|required|min_length[2]|max_length[50]');       
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|valid_email|is_unique[ec_driver.email]'); 
        $this->form_validation->set_rules('flat_no', 'Flat No', 'trim|required'); 
        $this->form_validation->set_rules('state', 'State Name', 'trim|required');
        $this->form_validation->set_rules('zip_code', 'Pin Code', 'trim|required'); 
    }

    
   
    public function upload_file()
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $driver_id = $this->LOGIN_ID; 

        $this->file_upload_validation();
        #if($this->form_validation->run())
        if(isset($_FILES["user_img"]["name"]) && $_FILES["user_img"]["name"])
        {       
            $this->config->load('custom_config');
           	$DRIVER_IMG = $this->config->item('DRIVER_IMG');
            if(isset($_FILES["user_img"]["name"]) && $_FILES["user_img"]["name"] != '' ){
                $path_parts     = pathinfo($_FILES["user_img"]["name"]);
                $user_img  =rand(1,1000).'u_id_'.$driver_id.'_profile.'.$path_parts['extension'];
                $user_img  = trim($user_img);
                $config['upload_path']      = $DRIVER_IMG;
                $config['allowed_types']    = '*';
                //$config['max_size']       = 5048;
                //$config['max_width']      = 940;
                //$config['max_height']     = 336;
                $config['overwrite']        = TRUE;
                $config['file_name']        = $user_img;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('user_img'))
                {
                    $post_data = array('user_img' => $user_img, 'screen_id' => 2);
                    $condition = array('driver_id'  => $driver_id);
                    $data =  $this->Query_model->update_data('ec_driver',$post_data, $condition);
                    
                    if($data)
                    {
                        $userimg = base_url().'assets/driver_profile/'.$user_img;
                        api_response(array('status' => 1, 'msg' => 'Profile uploaded Successfully', 'data' => $userimg));
                    }
                    else
                    {
                        api_response(array('status' => 0, 'msg' => 'Fail', 'data' => array()));
                    }
                }
                else
                {
                    $err = $this->upload->display_errors();
                    api_response(array('status' => 0, 'msg' => $err, 'data' => array()));
                }
            }  
        }
        else
        {
        	 #api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
            api_response(array('status' => 0, 'msg' => "Image Required", 'data' => array()));
        }
      
    }
 

    public function file_upload_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        if(empty($_FILES['user_img']['name'])){
            $this->form_validation->set_rules('user_img', 'Driver image', 'trim|required');
        }
    }


    
    public function add_bank_details()
    {
        wfile($_POST);
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['error'] = false;
        $data['type'] = '';
        $data['message']['error'] = '';
       
        $condition = array('driver_id' => $this->LOGIN_ID);
        $data_obj = $this->Query_model->get_data_obj('ec_bank_details', $condition);
 
        if(is_api() && $this->input->post('request_type') == 'get')
        {
            api_response(array('status' => 1, 'msg' => 'success', 'data' => $data_obj));
        } 

        $this->bank_details_validation();
        
        if($this->form_validation->run())
        {
            if($_POST) {

                $data =  array(
                        'driver_id'            => $this->LOGIN_ID,
                        'screen_id'            => 3,
                        'bank_name'            => $this->input->post('bank_name'),
                        'account_holder_name'  => $this->input->post('account_holder_name'),
                        'account_number'       => $this->input->post('account_number'),
                        'ifsc'                 => $this->input->post('ifsc'),
                        'bank_location'        => $this->input->post('bank_location'),
                        );
            }else{
                $data['driver_id']              = $data_obj->driver_id;
                $data['screen_id']              = 3;
                $data['bank_name']              = $data_obj->bank_name;
                $data['account_holder_name']    = $data_obj->account_holder_name;
                $data['account_number']         = $data_obj->account_number;
                $data['ifsc']                   = $data_obj->ifsc;
                $data['bank_location']          = $data_obj->bank_location;
            }

            if(empty($data_obj))
            { 
                $screen  = array('screen_id' => 3);
                $profile = $this->Query_model->update_data('ec_driver', $screen, $condition);
                $data    = $this->Query_model->insert_data('ec_bank_details', $data);
                $msg     = 'Bank details added successfully';
            }else
            {
                $screen  = array('screen_id' => 3);
                $profile = $this->Query_model->update_data('ec_driver', $screen, $condition);
                $status  = $this->Query_model->update_data('ec_bank_details',$data, $condition);
                $msg     = 'Successfully Update.';
            }
            $data = array($data);
            api_response(array('status' => '1', 'msg' => $msg, 'data' => $data));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

    }
    

    public function bank_details_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required');
        $this->form_validation->set_rules('account_holder_name', 'Account Holder Name', 'trim|required');
        $this->form_validation->set_rules('account_number', 'Account No', 'trim|required');
        $this->form_validation->set_rules('ifsc', 'IFSC Code', 'trim|required');
        $this->form_validation->set_rules('bank_location', 'Bank Location', 'trim|required');
    }
 


    public function add_documents()
    {
        wfile($_FILES);
        $data           = array();
        $data['TYPE']   = $this->TYPE;
        $driver_id = $this->LOGIN_ID;
        $this->documents_validation();

        //if($this->form_validation->run())
        if(isset($_FILES["insurance_certificate"]["name"]) && $_FILES["insurance_certificate"]["name"])
        {
           $data = $this->upload_documents(array('FILES'=>$_FILES)); 
           api_response(array('status' => 1, 'msg' => 'Documents added successfully', 'data' => $data));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }
 
    }



    public function upload_documents($args)
    {
        $this->config->load('custom_config');
        $user_id = $this->LOGIN_ID;

        $DRIVER_DOC = $this->config->item('DRIVER_DOC');
        $DRIVER_DOC .= '/'.$user_id;
        if (!file_exists($DRIVER_DOC))
        {
            mkdir($DRIVER_DOC, 0777, true);
        }
        $doc_data = [];
        foreach($args['FILES'] as $key =>$value)
        {
           
        if(isset($_FILES[$key]["name"]) && $_FILES[$key]["name"] != '' ){
            $path_parts     = pathinfo($_FILES[$key]["name"]);
            $user_img  = $user_id.'_'.$key.'.'.$path_parts['extension'];
            $user_img  = trim($user_img);
            
            $config['upload_path']      = $DRIVER_DOC;
            $config['allowed_types']    = '*';
            //$config['max_size']       = 5048;
            //$config['max_width']      = 940;
            //$config['max_height']     = 336;
            $config['overwrite']        = TRUE;
            $config['file_name']        = $user_img;
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            if ($this->upload->do_upload($key))
            {
                $doc_data[$key]= $user_img;
            }
        }
        }

        $doc_data['screen_id'] = 4;
        $doc_data['driver_id'] = $this->LOGIN_ID;
        $data =  $this->Query_model->update_data('ec_driver', array('screen_id'=> '4'), array('driver_id' => $this->LOGIN_ID));
     
        $driver_doc_obj = $this->Query_model->get_data_obj('ec_driver_documents', array('driver_id'=> $this->LOGIN_ID));
        $data = '';
        if($driver_doc_obj)
        {   
            $data =  $this->Query_model->update_data('ec_driver_documents', $doc_data, array('driver_id'=> $this->LOGIN_ID)); 
        }
        else
        {
            $data =  $this->Query_model->insert_data('ec_driver_documents', $doc_data);
        }
        return $data;
    }


    public function documents_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        if(empty($_FILES['insurance_certificate']['name'])){
            $this->form_validation->set_rules('insurance_certificate', 'Insurance Certificate', 'trim|required');
        //}else if(empty($_FILES['pan_certificate']['name'])){
          //  $this->form_validation->set_rules('pan_certificate', 'Pan Certificate', 'trim|required');
        }else if(empty($_FILES['driving_license']['name'])){
            $this->form_validation->set_rules('driving_license', 'Driving License', 'trim|required');
        }
    }


    public function add_vehicle_details()
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['error'] = false;
        $data['type'] = '';
        $data['message']['error'] = '';
       
        $condition = array('driver_id' => $this->LOGIN_ID);
        $data_obj = $this->Query_model->get_data_obj('ec_driver_vehicle', $condition);
 
        if(is_api() && $this->input->post('request_type') == 'get')
        {
            api_response(array('status' => 1, 'msg' => 'success', 'data' => $data_obj));
        } 

        $this->vehicle_details_validation();
        
        if($this->form_validation->run())
        {
            if($_POST) {

                $data =  array(
                        'driver_id'     => $this->LOGIN_ID,
                        'screen_id'     => 5,
                        'brand_name'    => $this->input->post('brand_name'),
                        'model_name'    => $this->input->post('model_name'),
                        'vehicle_no'    => $this->input->post('vehicle_no'),
                        'vehicle_type'  => $this->input->post('vehicle_type'),
                        'color'         => $this->input->post('color'),
                        );
            }else{
                $data['driver_id']     = $data_obj->driver_id;
                $data['screen_id']     = 5;
                $data['brand_name']    = $data_obj->brand_name;
                $data['model_name']    = $data_obj->model_name;
                $data['vehicle_type']  = $data_obj->vehicle_type;
                $data['vehicle_no']    = $data_obj->vehicle_no;
                $data['color']         = $data_obj->color;
            }

            if(empty($data_obj))
            {
                $screen = array('screen_id'=>'5');
                $condition1 = array('driver_id' => $this->LOGIN_ID);
                $data1 = $this->Query_model->update_data('ec_driver', $screen, $condition1);
                $data = $this->Query_model->insert_data('ec_driver_vehicle', $data);
                $msg  = 'Vehicle details added successfully';
            }else
            {
                $screen = array('screen_id'=>'5');
                $condition1 = array('driver_id' => $this->LOGIN_ID);
                $data1 = $this->Query_model->update_data('ec_driver', $screen, $condition1);
                $status    = $this->Query_model->update_data('ec_driver_vehicle',$data, $condition);
                $msg  = 'Successfully Update.';
            }
            $data = array($data);
            api_response(array('status' => '1', 'msg' => $msg, 'data' => $data));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

    }
    

    public function vehicle_details_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('brand_name', 'Brand Name', 'trim|required');
        $this->form_validation->set_rules('model_name', 'Model Name', 'trim|required');
        $this->form_validation->set_rules('vehicle_no', 'Vehicle No', 'trim|required');
        $this->form_validation->set_rules('color', 'Vehicle Color', 'trim|required');
    }
 
    
    public function add_vehicle_info()
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['error'] = false;
        $data['type'] = '';
        $data['message']['error'] = '';

        $condition = array('driver_id' => $this->LOGIN_ID);
        $data_obj = $this->Query_model->get_data_obj('ec_driver_vehicle', $condition);

        if(is_api() && $this->input->post('request_type') == 'get')
        {   
            api_response(array('status' => 1, 'msg' => 'success', 'data' => $data_obj));
        }

        $this->vehicle_info_validation(); 

        //if($this->form_validation->run())
        if(isset($_FILES["rc_certificate"]["name"]) && $_FILES["rc_certificate"]["name"])
        {
            $last_id = $this->upload_vehicle_info(array('FILES'=>$_FILES));
            $msg  = 'Registration successfully';
            api_response(array('status' => '1', 'msg' => $msg, 'data' => $last_id));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }  

    }

    
    public function vehicle_info_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
         if(empty($_FILES['rc_certificate']['name'])){
            $this->form_validation->set_rules('rc_certificate', 'RC Certificate', 'trim|required');
        }else if(empty($_FILES['insurance_policy']['name'])){
            $this->form_validation->set_rules('insurance_policy', 'Insurance Policy', 'trim|required');
        }
    }



    public function upload_vehicle_info($args)
    {

        $this->config->load('custom_config');
        $user_id = $this->LOGIN_ID;

        $DRIVER_DOC = $this->config->item('DRIVER_DOC');
        $DRIVER_DOC .= '/'.$user_id;
        if (!file_exists($DRIVER_DOC))
        {
            mkdir($DRIVER_DOC, 0777, true);
        }
        $doc_data = [];
        foreach($args['FILES'] as $key =>$value)
        {

            if(isset($_FILES[$key]["name"]) && $_FILES[$key]["name"] != '' ){
                $path_parts     = pathinfo($_FILES[$key]["name"]);
                $user_img  = $user_id.'_'.$key.'.'.$path_parts['extension'];
                $user_img  = trim($user_img);

                $config['upload_path']      = $DRIVER_DOC;
                $config['allowed_types']    = '*';
                //$config['max_size']       = 5048;
                //$config['max_width']      = 940;
                //$config['max_height']     = 336;
                $config['overwrite']        = TRUE;
                $config['file_name']        = $user_img;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload($key))
                {
                    $doc_data[$key]= $user_img;
                }
            }
        }

        $doc_data['screen_id'] = '6';
        $driver_vehicle_obj = $this->Query_model->get_data_obj('ec_driver_vehicle', array('driver_id'=> $this->LOGIN_ID));
        if($driver_vehicle_obj->vehicle_id)
        { 
            $condition = array('vehicle_id' => $driver_vehicle_obj->vehicle_id, 'driver_id' => $this->LOGIN_ID);
            $prodata = $this->Query_model->update_data('ec_driver', array('screen_id'=>6), array('driver_id'=>$this->LOGIN_ID));
            $data =  $this->Query_model->update_data('ec_driver_vehicle', $doc_data, $condition);
            return $data;
        }
        else
        {
            return false;
        }

    }


    public function get_driver_location()
    {
        $data           = array();
        $data['TYPE']   = $this->TYPE;
        $data['csrf']   = csrf_token();
        $data['error']  = false;
        $data['type']   = '';
        $data['message']['error'] = '';
        

        $condition        = array('driver_id' => $this->LOGIN_ID);
        $userdetail       = $this->Query_model->get_data_obj('ec_driver', $condition);
        $driver_vehicle   = $this->Query_model->get_data_obj('ec_driver_vehicle', $condition);
        $rejected_orderid = $this->driver_order_status();
        $price            = vehicle_type_price();
        $vehicle_price    = isset($driver_vehicle->vehicle_type) ? $price[$driver_vehicle->vehicle_type] : '0.00';
            
        $this->location_validation();

         if($this->form_validation->run())
        {
            if($_POST)
            {   
                $data   = array('latitude' => $this->input->post('latitude'),  'longitude' => $this->input->post('longitude'));
            }
            else
            {
                $data['latitude']   = $userdetail->latitude;    
                $data['longitude']  = $userdetail->latitude;    
            }
           
            $distance = '50';
            $speed    = 30;
            $args_locator = array('lat'=>$data['latitude'], 'long'=>$data['longitude'], 'distance'=>$distance, 'limit'=>5);
            $diliver_order = $this->Order_model->get_driver_order($args_locator);

            if(1){
  
               $vendor_ids = array();
               $vendor_ids = array_column($diliver_order, 'admin_id');
               $condition1 = array('status'=> '16', 'driver_id'=> NULL);
                
               $order_dataarr = $this->Query_model->get_data('ec_order_item', $condition1);
               $order_data = array();
               if(count($order_dataarr)){
                    $is_order=0;
                   foreach($order_dataarr as $orderdata)
                   {
                       $row = array();
                       if(in_array($orderdata->order_item_id, $rejected_orderid)){ continue; }
                       $index = array_search($orderdata->supplier_id, $vendor_ids);
                       $is_order = 1;
                       $shipping_address_id        = get_order_uid($orderdata->order_id);
                       $delivered_from             = get_delivered_address($shipping_address_id->shipping_address_id);
                       $pickup_from                = get_pickup_address($orderdata->supplier_id);
                       $args                       = array('place_id_s'=> $orderdata->vendor_place_id, 'place_id_d'=> $orderdata->customer_place_id);
                       $get_time_distance          = GetDrivingDistance($args);

                       $row['order_item_id']       = $orderdata->order_item_id;
                       $row['order_item_uid']      = $orderdata->order_item_uid;
                       $row['order_id']            = $orderdata->order_id;
                       $row['customer_id']         = $orderdata->customer_id;
                       $row['supplier_id']         = $orderdata->supplier_id;
                       $row['driver_id']           = $orderdata->driver_id;
                       $row['vendor_id']           = $orderdata->vendor_id;
                       $row['product_id']          = $orderdata->product_id;
                       $row['attribute_item_id']   = $orderdata->attribute_item_id;
                       $row['product_type']        = $orderdata->product_type;
                       $row['quantity']            = $orderdata->quantity;
                       $row['subtotal']            = $orderdata->subtotal;
                       $row['shipping']            = $orderdata->shipping;
                       $row['tax']                 = $orderdata->tax;
                       $row['total']               = $orderdata->total;
                       $row['coupon_amount']       = $orderdata->coupon_amount;
                       $row['currency_id']         = $orderdata->currency_id;
                       $row['status']              = $orderdata->status;
                       $row['cancel_order_reason'] = $orderdata->cancel_order_reason;
                       $row['comment']             = $orderdata->comment;
                       $row['delivered_from']      = $delivered_from;
                       $row['pickup_from']         = $pickup_from;
                       $row['date_created']        = $orderdata->date_created;
                       $row['last_updated']        = $orderdata->last_updated;
                       $row['distance_value']      = $get_time_distance['distance']['text'];
                       $row['distance_unit']       = 'KM';
                       $row['duration_value']      = $get_time_distance['duration']['text'];
                       $row['duration_unit']       = 'Minute';
                       $distance                   = number_format($diliver_order[$index]->distance, 2);
                       $price                      = $distance * $vehicle_price;
                       $row['earning_value']       = $orderdata->shipping;
                       $row['earning_unit']        = 'MKW';      
                       $order_data[] = $row;
                
                   }

                    $status     = $this->Query_model->update_data('ec_driver',$data, $condition);
                    $this->Query_model->update_data('ec_notification', array('show_noti'=>'0'), array('driver_id'=>$this->LOGIN_ID, 'type'=>'2'));

                   api_response(array('status' => '1', 'msg' => 'Success', 'data' => $order_data));
               }
               else
                {
                    api_response(array('status' => '1', 'msg' => "You don't have any orders yet", 'data' => array()));
                }

            }else{

                api_response(array('status' => '0', 'msg' => "You don't have any orders yet", 'data' => $data));
            }
 
        }
        else
        {
           api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array())); 
        }

    }
        


    public function location_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('latitude', 'Please Select location', 'trim|required'); 
        $this->form_validation->set_rules('longitude', 'Please Select location', 'trim|required'); 
    }
    
    
    public function driver_order_status()
    {
        $orderid_data =  $this->Order_model->driver_order_status(array('driver_id' => $this->LOGIN_ID));
        $order_ids = array();
        foreach($orderid_data as $orderiddata)
        {
            $order_ids[] = $orderiddata->order_item_id;
        }

        return $order_ids;

    }



    public function order_accept()
    {
        $data = array();
        $data['TYPE']   = $this->TYPE;
        $data['csrf']   = csrf_token();
        $data['error']  = false;
        $data['type']   = '';
        $data['message']['error'] = '';

        $this->order_accept_validation();
 
        if($this->form_validation->run())
        {
            $order_item_obj = $this->Query_model->get_data_obj('ec_order_item', array('driver_id'=> $this->LOGIN_ID, 'status'=>19));
            if($order_item_obj)
            {
                api_response(array('status'=>0, 'msg'=>'Last delivery pending.', 'data'=>[]));
            }

            if($_POST)
            {
                $data['order_item_id'] = $this->input->post('order_item_id');
                $data['status']        = $this->input->post('status');
            }

            //$condition = array('order_item_id' => $data['order_item_id'], 'driver_id'=> NULL, 'status' => '16');    
            //$get_orderstatus = $this->Query_model->get_data_obj('ec_order_item', $condition);

           // if(empty($get_orderstatus))
            //{
              //  api_response(array('status' => 0, 'msg' => 'Request is not accepected', 'data' => array()));
            //}
            //else
            //{
                $condition1  = array('order_item_id' => $data['order_item_id']);                   
                $update_data = array('driver_id' => $this->LOGIN_ID, 'status' => $data['status']);
                $data = $this->Query_model->update_data('ec_order_item', $update_data, $condition1);
                api_response(array('status' => 1, 'msg' => 'Request is accepected', 'data' => $data));
            //}
        } 
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        } 
    }

    public function order_accept_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('order_item_id', 'Please Select Order', 'trim|required'); 
        $this->form_validation->set_rules('status', 'Please Select Order', 'trim|required'); 
    }


    public function order_out_for_delivery()
    {
        $data = array();
        $data['TYPE']   = $this->TYPE;
        $data['csrf']   = csrf_token();
        $data['error']  = false;
        $data['type']   = '';
        $data['message']['error'] = '';
        $delivery_info = (object) array();

        $condition        = array('driver_id' => $this->LOGIN_ID);
        $driver_vehicle   = $this->Query_model->get_data_obj('ec_driver_vehicle', $condition);
 
        $price            = vehicle_type_price();
        $vehicle_price    = $price[$driver_vehicle->vehicle_type];

        $delivered      = $this->input->post('action');
        $status         = $this->input->post('status');
        $order_item_id  = $this->input->post('order_item_id');

        #this function use for the order delivered only  --- start here---

        if(is_api() && $delivered == 'delivered')
        {
            $args = array('status' => $status, 'order_item_id'=> $order_item_id);
            $data = $this->order_delivered($args);
            if($data)
                api_response(array('status'=>1, 'msg'=>'Order delivered successfully', 'data' => $data));
            else
                api_response(array('status'=>1, 'msg'=>'This Order is already delivered', 'data' => array()));
                
        }

        #function is --  end here  --

        $this->out_for_delivery_validation();
        
        if($this->form_validation->run())
        {
           $order_item_id  = $this->input->post('order_item_id');

           #this function use for get order data detail
           $orderdata       = $this->get_order_data($order_item_id);
           $orderdata->date_created = date('M d, Y', strtotime($orderdata->date_created));
           $orderdata->time = date('h:i A', strtotime($orderdata->date_created));
           $orderdata->order_id = get_order_uid($orderdata->order_id)->order_uid;
           $delivery_info   = $orderdata;

           #this function use for the supplier detail
           $supplier_inof = $this->get_supplier($orderdata->supplier_id);
           $delivery_info->pickup = $supplier_inof;

           #this function use for the customer detail
           $customer_inof = $this->get_user_detail($orderdata->customer_id);
           $delivery_info->drop_off = $customer_inof;  

           #this function use for the distance
           $distance = getDistanceBetweenPointsNew($supplier_inof->latitude, $supplier_inof->longitude, $customer_inof->latitude, $customer_inof->longitude);
           $distance1 = $distance;
           $distance = array('distance'=> number_format($distance, 2), 'unit'=> 'KM');
           $delivery_info->distance = $distance;      
           $delivery_info->earning  = ['unit'=>'MKW', 'value' => number_format($distance1 * $vehicle_price, 2)]; 

           #this function use for the get product detail
           $product_info = $this->get_product_detail($orderdata->product_id);
           $product_info->image = $this->get_product_img($orderdata->product_id);
           $product_info->image = base_url().'assets/uploads/files/'.$product_info->image->file_name;
           $product_info->quantity = $orderdata->quantity;
           $delivery_info->product_info = $product_info;

           api_response(array('status' => 1, 'msg' =>'Success' ,'data' => $delivery_info));             
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }
        
 
    }


    public function out_for_delivery_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('order_item_id', 'Please Select Order', 'trim|required');
    }

    public function order_delivered($args = NULL)
    {
        $condition = array('order_item_id'=> $args['order_item_id']);
        $data      = array('status'=> $args['status'], 'driver_id'=> $this->LOGIN_ID);
        $delivereddata = $this->Query_model->update_data('ec_order_item', $data, $condition);
        return $delivereddata;
    }


    #this function use for the get Supplier detail
 
    public function get_supplier($supplier_id = NULL)
    {
        $condition = array('admin_id' => $supplier_id);
        $supplier = $this->Order_model->get_data_select_obj('store_name, address, latitude, longitude','ec_admin',$condition);
        return $supplier; 
    }

    #this function use for the get order detail

    public function get_order_data($order_item_id = NULL)
    {
        $condition = array('order_item_id' => $order_item_id);
        $order_data = $this->Order_model->get_data_select_obj('order_id, customer_id, supplier_id, product_id, total, quantity, date_created', 'ec_order_item', $condition);
        
        return $order_data;

    }

    #this function use for the customer info

    public function get_user_detail($customer_id = NULL)
    {
        $condition = array('customer_id' => $customer_id, 'default_address' => '1');
        $customer_data = $this->Order_model->get_data_select_obj('fullname, mobile, city, address_1, latitude, longitude', 'ec_shipping_address', $condition);
        return $customer_data; 
    }

    #this function use for the product detail 
    public function get_product_detail($product_id = NULL)
    {
        $condition = array('product_id' => $product_id);
        $product_data = $this->Order_model->get_data_select_obj('post_title', 'ec_product', $condition);
        return $product_data;
        
    }

    public function get_product_img($product_id = NULL)
    {
        $condition   = array('product_id' => $product_id);
        $product_img = $this->Order_model->get_data_select_obj('file_name','ec_gallery', $condition, '', array('length'=>1));
        return $product_img;
    }  
 
    
    public function driver_review()
    {
        $data = array();
        $driver_id = $this->LOGIN_ID;
        $review_mode = review_mode();
        $review_data = $this->Query_model->get_data('ec_driver_review', array('driver_id'=> $driver_id));

        foreach($review_data as $reviewdata)
        {
           $row = array();
           $row['customer']     = get_customer($reviewdata->customer_id)->fname.' '.get_customer($reviewdata->customer_id)->lname; 
           $row['customer_img'] = base_url().'assets/user_profile/'.get_customer($reviewdata->customer_id)->user_img; 
           $row['rating']       = $reviewdata->rating; 
           $row['review']       = $reviewdata->review; 
           $row['review_mode']  = $review_mode[$reviewdata->rating];
           $row['date']         = date('M d, Y', strtotime($reviewdata->date_created));
           $row['time']         = date('h:i A', strtotime($reviewdata->date_created));
           $data[] = $row; 
        }


        if($review_data)
        {
            api_response(array('status' => 1, 'msg' => "Success", 'data' => $data));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => "You don't have any feedback data yet", 'data' => array()));
        }
 

    }



    public function profile_status()
    {
        $data       = array();
        $driver_id  = $this->LOGIN_ID;
        $pro_data   = $this->Query_model->get_data_obj('ec_driver', array('driver_id'=> $driver_id));
        $data['vehicle_type']   =   $this->Query_model->get_data('ec_vehicle_type', array('status'=>'1'));
        //$screenno   = get_screen_no();    
        $data['driver_id']      = $driver_id;
        $data['is_rejected']    = $pro_data->status == 2 ? true : false;
        if($pro_data->status == 2)
        {
            $data['screen_id']  = 3;
        }
        else if($pro_data->screen_id == 0)
        {
            $data['screen_id']  = 1;
        }else
        {
            $data['screen_id']  = $pro_data->screen_id < 6 ? $pro_data->screen_id + 1 : $pro_data->screen_id;
        }
         
        if($pro_data)
        {
            api_response(array('status' => 1, 'msg' => "Success", 'data' => [$data]));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => "You don't have any feedback data yet", 'data' => array()));
        }


    }
    


    public function online_status()
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
       
        $condition = array('driver_id' => $this->LOGIN_ID);
        $data_obj = $this->Query_model->get_data_obj('ec_driver', $condition);
 
        if(is_api() && $this->input->post('request_type') == 'get')
        {
            #$data_obj = $this->Query_model->get_data_obj('ec_driver', $condition);
            $driver_status['driver_status'] = $data_obj->driver_status;
            $driver_status['is_approve']    = $data_obj->status == 1 ? true : false;
            $driver_status['is_rejected']   = $data_obj->status == 2 ? true : false;

            api_response(array('status' => 1, 'msg' => 'success', 'data' => [$driver_status]));
        } 

        $this->online_validation();
        
        if($this->form_validation->run())
        {
            if($_POST) {

                $data =  array(
                        'driver_id'       => $this->LOGIN_ID,
                        'driver_status'   => $this->input->post('driver_status'),
                        'latitude'        => $this->input->post('latitude'),
                        'longitude'       => $this->input->post('longitude'),
                        );
            }else{
                $data['driver_id']      = $userdetail->driver_id;
                $data['driver_status']  = $userdetail->driver_status;
                $data['latitude']       = $userdetail->latitude;
                $data['longitude']      = $userdetail->longitude;
            }
            
            $msg  = 'KYC not yet approved.';
            if($data_obj->status == 1)
            {
                $status    = $this->Query_model->update_data('ec_driver',$data, $condition);
                $msg  = 'Driver online Successfully';
            }

            api_response(array('status' => '1', 'msg' => $msg, 'data' => [$data]));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

    }

    
    public function online_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('driver_status', 'Please Select Driver Status', 'trim|required');  
    }


    public function vehicle_type_price()
    {
        $data = array();
        $vehicledata   = $this->Query_model->get_data('ec_vehicle_price');
        if($vehicledata)
        {   
            api_response(array('status' => 1, 'msg' => "Success", 'data' => $vehicledata));
        }
        else
        {   
            api_response(array('status' => 0, 'msg' => "Fail", 'data' => array()));
        }
 
 
    }


    public function all_orders()
    {
        $data = array();
        $driver_id = $this->LOGIN_ID;
        $orderdata = $this->Query_model->get_data('ec_order_item', array('driver_id'=>$driver_id, 'status !=' => 5));
        $current_currency = current_currency();
        $currency     = get_currency($current_currency);
        $rate         = $currency->rate;
        $symbol       = $currency->symbol;
        $order_status = order_status();

        foreach($orderdata as $order)
        {
            $row = array();
            $product_obj   = get_product($order->product_id);
            $orderuid     = get_order_uid($order->order_id);
            $shipping     = get_shipping_info($orderuid->shipping_address_id);
            $store_info   = get_pickup_address($order->supplier_id);
            $product_image= get_product_image($order->product_id);
            $rating       = get_driver_rating($order->driver_id);
            $user_info    = get_delivered_address($order->customer_id);

            $order->product_id      = $order->product_id;
            $order->product_name    = $product_obj->post_title;
            $order->symbol          = $symbol;
            $order->payment_mode    = $orderuid->payment_mode;
            $order->product_image   = isset($product_image->file_name) ? base_url().'assets/uploads/files/'.$product_image->file_name : base_url().'assets/images/no-image.png';
            $order->delivery_address = $shipping;
            $order->store_name      = $store_info;
            $order->user_name       = $user_info;
            $order->order_status    = $order_status[$order->status];
            $order->rating          = isset($rating) ? $rating : 'N/A';

        }
       
        if(is_api())
        {   
            api_response(array('status' => 1, 'msg' => "Success", 'data' => $orderdata));
        }
        else
        {   
            api_response(array('status' => 0, 'msg' => "Fail", 'data' => array()));
        }
 
 
    }


    public function driver_amount()
    {
        $data = array();
        $driver_id = $this->LOGIN_ID;
        $checkdata = $this->Query_model->get_data('ec_driver_earnings', array('driver_id' => $driver_id)); 
        $getdata   = $this->Order_model->get_data_by_month(array('driver_id'=>$driver_id));
        $getmonth  = get_month();        
        $year      = date("Y"); 

        if(empty($checkdata))
        {
            $insdata    =   array();
            $current_YM = date('Y-m');
            foreach($getdata as $monthdata)
            {
                $admin_amount          = (10 / 100) * $monthdata->shipping;
                $month                 = $getmonth[$monthdata->month];
                $days                  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $datayear              = $monthdata->Year;

                if($current_YM == $datayear.'-'.$month){continue;}
                //if('2021-12' == $datayear.'-'.$month){continue;}

                $insdata = array( 
                    'driver_id'         => $monthdata->driver_id,
                    'admin_percentage'  => '10', 
                    'from_date'         => $datayear.'-'.$month.'-'.'01',
                    'to_date'           => $datayear.'-'.$month.'-'.$days,
                    'total'             => $monthdata->shipping,
                    'admin_amount'      => $admin_amount,
                    'driver_amount'     => $monthdata->shipping - $admin_amount
                );

            $earningsdata = $this->Query_model->insert_data('ec_driver_earnings', $insdata);

            }  
        }
        else
        {
            if(count($checkdata))
            {
                $last_array = end($checkdata);
                $to_date = $last_array->to_date;
                if(!$to_date){return false;}
                $yrmnth = date('Y-m', strtotime($to_date));
                $getdata = [];
                $getdata   = $this->Order_model->get_data_by_month(array('driver_id'=>$driver_id, 'yrmnth'=>$yrmnth, 'next_month'=>1));
                if(count($getdata))
                {
                    foreach($getdata as $monthdata)
                    {
                        if(date('F') == $monthdata->month){continue;}
                        $admin_amount          = (10 / 100) * $monthdata->shipping;
                        $month                 = $getmonth[$monthdata->month];
                        $days                  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $datayear              = $monthdata->Year;

                        if($current_YM == $datayear.'-'.$month){continue;}

                        $insdata = array(       
                                'vendor_id'         => $monthdata->supplier_id,
                                'admin_percentage'  => '10', 
                                'from_date'         => $datayear.'-'.$month.'-'.'01',
                                'to_date'           => $datayear.'-'.$month.'-'.$days,
                                'total'             => $monthdata->shipping,
                                'admin_amount'      => $admin_amount,
                                'vendor_amount'     => $monthdata->shipping - $admin_amount
                                );

                        $earningsdata = $this->Query_model->insert_data('ec_driver_earnings', $insdata);

                        #insert in ec_earnings
                    }
                }
                                     
            }  
        }   
    }



    public function current_month_earning($args)
    {
       $monthyear = date('M Y');
       $getmonth  = get_month();
       $year      = date("Y");
       $nulldate  = '';
       $getdata   = $this->Order_model->get_data_by_month(array('driver_id'=>$args['driver_id'], 'current_month'=> $args['current_mnth'], 'yrmnth'=>$args['yrmnth']));
        if(count($getdata))
        {
            $admin_amount                = (10 / 100) * $getdata[0]->shipping;
            $driveramount                = $getdata[0]->shipping - $admin_amount;
            $getdata[0]->earning_id      = '';
            $getdata[0]->admin_id        = '';
            $getdata[0]->driver_id       = $getdata[0]->driver_id;
            $getdata[0]->total           = $getdata[0]->shipping;
            $getdata[0]->admin_percentage= '10';
            $getdata[0]->admin_amount    = number_format((float)$admin_amount, 2, '.', '');
            $getdata[0]->driver_amount   = number_format((float)$driveramount, 2, '.', '');
            $getdata[0]->from_date       = date("Y-m-01 H:i:s");
            $getdata[0]->to_date         = date("Y-m-d H:i:s");
            $getdata[0]->current_month   = '0'; 

        }
        return $getdata;
    } 



    public function earnings()
    {
        $data = array();
        $driver_id = $this->LOGIN_ID;
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
        $monthyear = date('M Y');

        $list = $this->Query_model->get_data('ec_driver_earnings', array('driver_id'=>$driver_id), array('earning_id'=> 'DESC'));
        $curnt_mnth_data = $this->current_month_earning(array('driver_id'=>$driver_id, 'current_mnth'=>1, 'yrmnth'=>date('Y-m')));
        $newArr = array_merge($curnt_mnth_data, $list);

        foreach($newArr as $obj) {
            //print_r($obj);

            $row = array();
            if($monthyear == date('M Y', strtotime($obj->from_date)))
            {
                $current_month = '1';
                #$this->current_month_earning(array('vendor_id'=>$vendor_id, 'current_mnth'=>1));
                #$obj->total = 100;
            }
            else
            {
                $current_month = '0';
            }
             
            $row['earning_id']          = $obj->earning_id;
            $row['driver_id']           = $obj->driver_id;
            $row['total']               = $obj->total;
            $row['admin_percentage']    = $obj->admin_amount;
            $row['driver_earning']      = $obj->driver_amount;
            $row['last_earning_date']   = date('M Y', strtotime($obj->from_date));
            $row['to_date']             = date('M d Y', strtotime($obj->to_date));
            $row['paid_type']           = isset($obj->paid_type) ? $obj->paid_type : 'N/A';
            $row['paid_date']           = isset($obj->paid_date) ? date('M d Y', strtotime($obj->paid_date)) : 'N/A';
            $row['paid_status']         = isset($obj->paid_status) ? 'Paid' : 'Unpaid' ;
            $row['current_month']       = $current_month;

            $data[] = $row;
        }
        
            api_response(array('status'=>1, 'msg'=>'success', 'data'=> $data));

    }

    public function notification_count()
    {
        $api_data = [];
        $api_data['noti_count'] = 2;

        api_response(array('status'=>1, 'msg'=>'success', 'data'=> $api_data)); 
    }


    public function delivered_order()
    {
        $data = array();
        $data['TYPE']   = $this->TYPE;
        $data['csrf']   = csrf_token();
        $data['error']  = false;
        $data['type']   = '';
        $data['message']['error'] = '';

        $this->order_accept_validation();

        if($this->form_validation->run())
        {
            if($_POST)
            {
                $data['order_item_id'] = $this->input->post('order_item_id');
                $data['status']        = $this->input->post('status');
            }

            $condition1  = array('order_item_id' => $data['order_item_id']);                   
            $update_data = array('driver_id' => $this->LOGIN_ID, 'status' => $data['status']);
            $data = $this->Query_model->update_data('ec_order_item', $update_data, $condition1);
            api_response(array('status' => 1, 'msg' => 'Order successfully delivered', 'data' => $data));
        } 
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        } 
    }
    

    public function order_process()
    {
        #status =2-> shipped, status=18 -> out for delivery, status=10-> delivered

        $data = array();
        $data['TYPE']   = $this->TYPE;
        $data['csrf']   = csrf_token();
        $data['error']  = false;
        $data['type']   = '';
        $data['message']['error'] = '';

        $this->order_accept_validation();

        if($this->form_validation->run())
        {
            if($_POST)
            {
                $data['order_item_id'] = $this->input->post('order_item_id');
                $data['status']        = $this->input->post('status');
            }

            $condition1  = array('order_item_id' => $data['order_item_id']);                   
            $update_data = array('driver_id' => $this->LOGIN_ID, 'status' => $data['status']);
            $data = $this->Query_model->update_data('ec_order_item', $update_data, $condition1);
            api_response(array('status' => 1, 'msg' => 'Order pickup successfully', 'data' => $data));
        } 
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        } 

    }

    public function order_details()
    {
        wfile($_POST);
        $order_item_id = $this->input->post('order_item_id');
        $from = $this->input->post('from');

        if($from && $order_item_id)
        {
            $orderdata = $this->Query_model->get_data_obj('ec_order_item', array('driver_id'=>$this->LOGIN_ID, 'order_item_id'=>$order_item_id)); 
        }
        else
        {
            $orderdata  = $this->Query_model->get_data_obj('ec_order_item', array('driver_id'=>$this->LOGIN_ID, 'status'=>'19'));
        }


        if($orderdata)
        {
            $shipping_address_id        = get_order_uid($orderdata->order_id);
            $delivered_from             = get_delivered_address($shipping_address_id->shipping_address_id);
            $pickup_from                = get_pickup_address($orderdata->supplier_id);
            $args                       = array('place_id_s'=> $orderdata->vendor_place_id, 'place_id_d'=> $orderdata->customer_place_id);
            $get_time_distance          = GetDrivingDistance($args);
            
            $row['order_item_id']       = $orderdata->order_item_id;
            $row['order_item_uid']      = $orderdata->order_item_uid;
            $row['order_id']            = $orderdata->order_id;
            $row['customer_id']         = $orderdata->customer_id;
            $row['supplier_id']         = $orderdata->supplier_id;
            $row['driver_id']           = $orderdata->driver_id;
            $row['vendor_id']           = $orderdata->vendor_id;
            $row['product_id']          = $orderdata->product_id;
            $row['attribute_item_id']   = $orderdata->attribute_item_id;
            $row['product_type']        = $orderdata->product_type;
            $row['quantity']            = $orderdata->quantity;
            $row['subtotal']            = $orderdata->subtotal;
            $row['shipping']            = $orderdata->shipping;
            $row['tax']                 = $orderdata->tax;
            $row['total']               = $orderdata->total;
            $row['coupon_amount']       = $orderdata->coupon_amount;
            $row['currency_id']         = $orderdata->currency_id;
            $row['status']              = $orderdata->status;
            $row['cancel_order_reason'] = $orderdata->cancel_order_reason;
            $row['comment']             = $orderdata->comment;
            #$row['delivered_from']      = $delivered_from;
            $row['delivered_from_fullname']  = $delivered_from->fullname;
            $row['delivered_from_latitude'] = $delivered_from->latitude;
            $row['delivered_from_longitude'] = $delivered_from->longitude;
            #$row['pickup_from']         = $pickup_from;
            $row['pickup_from_store_name'] = $pickup_from->store_name;
            $row['pickup_from_latitude']  = $pickup_from->latitude;
            $row['pickup_from_longitude'] = $pickup_from->longitude;
            $row['date_created']        = $orderdata->date_created;
            $row['last_updated']        = $orderdata->last_updated;
            $row['distance_value']      = $get_time_distance['distance']['text'];
            $row['duration_value']      = $get_time_distance['duration']['text'];
            $row['earning_value']       = $orderdata->shipping;

            api_response(array('status'=>1, 'msg'=>'success', 'data'=>[$row]));    
        }
        else
        {
            api_response(array('status'=>0, 'msg'=>'There is no data.', 'data'=>[]));
        }
        
    }


    public function delete_bank_info()
    {
        $data       = array();
        $driver_id  = $this->LOGIN_ID;    
            
        $details_id = $this->input->post('details_id');   
     
        $this->delete_bank_info_validation();

        if($this->form_validation->run())
        {
            if($_POST)
            {
                $condition  = array('details_id' => $details_id);
            }

            $delete_data = $this->Query_model->delete_query('ec_bank_details', $condition);

            if($delete_data) 
            {
                api_response(array('status'=>1, 'msg'=>'Bank details deleted', 'data'=>$delete_data));
            }
            else
            {
                api_response(array('status'=>0, 'msg'=>'Bank details has not been deleted', 'data'=>array()));
            }
        }else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }       
         
    }

    public function delete_bank_info_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('details_id', 'Bank Detail', 'trim|required');       
    }


    public function vehicle_type()
    {
        $data    = array();
        $vehicle = vehicle_type();
                
        if($vehicle){
            api_response(array('status' => 1, 'msg' => 'Success', 'data' => [$vehicle]));
        }else{
             api_response(array('status' => 0, 'msg' => 'No Data found', 'data' => array()));
        }
    }

}


?>
