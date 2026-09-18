<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->TYPE = $this->session->userdata('type');
    }



    public function register()
    {
        $data   = array();
        $mobile = $this->input->post('mobile');
        $otp    = get_otp();

        $this->form_validation();
        if($this->form_validation->run() == FALSE)
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

        if($_POST)
        {
            $condition      =   array('mobile' => $mobile);
            $getuserdata    =   $this->Query_model->get_data_obj('ec_driver', $condition);
            if($getuserdata){

                $data       =   array('otp'=>$otp);
                $condition1 =   array('driver_id' => $getuserdata->driver_id);      
                $userdata   =   $this->Query_model->update_data('ec_driver', $data, $condition1);

                if($userdata)
                {
                    $detail =   array('mobile' => $getuserdata->mobile, 'otp'=> $otp);
                    api_response(array('status' => 1, 'msg' => 'OTP Sent Successfully.', 'data' => $detail));
                }                       
        
            }else{
                $driver_uid = uniq_uid();
                $password   = md5($otp);
                $screen_id  = 0;
                $data =  array(
                                'mobile'        => $mobile,
                                'otp'           => $otp,
                                'password'      => $password,
                                'screen_id'     => $screen_id,
                                'driver_uid'    => $driver_uid,
                                'ip_address'    => $_SERVER['REMOTE_ADDR'],
                              );    
                $customer_id = $this->Query_model->insert_data('ec_driver',$data);
                $detail =  array('mobile' => $mobile,'otp'=> $otp);
                if($customer_id)
                {
                     api_response(array('status' => 1, 'msg' => 'OTP Sent Successfully.', 'data' => $detail));
                }

            }   
        }
    
    } 

    public function form_validation(){
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|regex_match[/^[0-9]{10}$/]');
    }


    public function check_otp()
    {
        $this->chect_otp_validation();

        $otp       = $this->input->post('otp');
        $mobile    = $this->input->post('mobile');

        $condition = array('otp' => $otp, 'mobile' => $mobile);
        $user_obj  = $this->Query_model->get_data_obj('ec_driver', $condition);

        if($this->form_validation->run() == FALSE)
        {   
                api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

        if(!empty($user_obj))
        {
            $screenid  = $this->Query_model->get_data_obj('ec_driver_vehicle', array('driver_id' => $user_obj->driver_id));
            if(isset($screenid->screen_id) && $screenid->screen_id == 6 ) $action = '1'; else $action = '0';

            if(is_api() && $this->logged_in())
            {               
                apiresponse(array('section' => 'login', 'status' => 1, 'msg' => 'Already Login.',  'action'=> $action, 'data' => [$user_obj]));  
            }
        
            $details = array(
                            'mobile'        => $user_obj->mobile,   
                            'type'          => 'driver',
                            'name'          => $user_obj->name,
                            'login_id'      => $user_obj->driver_id,
                            'driver_img'    => $user_obj->user_img,
                            'logged_in'     => TRUE   
                        );
            $this->session->set_userdata('driver', $details);
            $_POST['driver_id'] = $user_obj->driver_id;
            $user_obj->user_img = ($user_obj->user_img !='') ? base_url().'assets/user_profile/'.$user_obj->user_img : base_url().'assets/default_images/user-circle.svg';
            $user_obj->name = ($user_obj->name !='') ? $user_obj->name : 'empty';
            $user_obj->email = ($user_obj->email !='') ? $user_obj->email : 'empty';
            if(isset($screenid->screen_id) && $screenid->screen_id == 6 ) $action = '1'; else $action = '0';
            apiresponse(array('status' => '1', 'msg' => 'Login Successfully', 'action'=> $action,'data' => [$user_obj])); 
        }
        else
        {
            $condition = array('otp' => $otp, 'mobile' => $mobile);
            $user_obj = $this->Query_model->get_data_obj('ec_driver', $condition);
            if($user_obj)
            {
                $user_obj->user_img = ($user_obj->user_img !='') ? base_url().'assets/user_profile/'.$user_obj->user_img : base_url().'assets/default_images/user-circle.svg';
                $user_obj->name = ($user_obj->name !='') ? $user_obj->name : 'empty';
                $user_obj->email = ($user_obj->email !='') ? $user_obj->email : 'empty';
                if(isset($user_obj->screen_id) && $user_obj->screen_id < 6 ) $action = '0'; else $action = '1';
                        apiresponse(array('status' => '1', 'msg' => 'success', 'action'=> $action,'data' => [$user_obj]));      
            }else
            {
            if(is_api())
                {
                api_response(array('status' => '0', 'msg' => 'OTP is incorrect.' , 'data' => array()));
                }
            }
        }       
    }       

	public function chect_otp_validation()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|regex_match[/^[0-9]{10}$/]');
        $this->form_validation->set_rules('otp', 'OTP', 'required');
	}




    public function logged_in()
    {
        if($this->session->userdata($this->TYPE) && isset($this->session->userdata($this->TYPE)['logged_in'])){
            return true;
        }else{
            return false;
        }
    }

    
    public function logout()
    {
        $this->session->unset_userdata($this->TYPE);
        if(is_api())
        {
            api_response(array('status' => 1, 'msg' => 'Successfully logout', 'data' => array()));
        }
        redirect(base_url());
    }


    public function distance_by_place_id()
    {
        $place_id_source    = $this->input->post('place_id_s');
        $place_id_desti     = $this->input->post('place_id_d');
        
        $distance = GetDrivingDistance(array('place_id_s'=>$place_id_source, 'place_id_d'=>$place_id_desti));
        api_response(array('status'=>'1', 'msg'=>'success', 'data'=>$distance));
        
    }

    
    public function profile()
    {
        $_POST['checkpage'] = 'profile';
        wfile($_POST);
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['error'] = false;
        $data['type'] = '';
        $data['message']['error'] = '';
        //$amount     =  $this->driver_amount();
        $this->LOGIN_ID  = $this->input->post('driver_id'); 
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

            if(isset($data_obj->driver_id)){
            $details = array(
                    'mobile'        => $data_obj->mobile,
                    'type'          => 'driver',
                    'name'          => $this->input->post('name'),
                    'login_id'      => $data_obj->driver_id,
                    'driver_img'    => $data_obj->user_img,
                    'logged_in'     => TRUE
                    );

            $this->session->set_userdata('driver', $details);
            }
            $status     = $this->Query_model->update_data('ec_driver',$data, $condition);
            $condition1 = array('driver_id' => $data_obj->driver_id);
            $data       = $this->Query_model->get_data_obj('ec_driver', $condition1);
            $msg        = 'Register Successfully.';
            $data       = array($data);

            api_response(array('status' => '1', 'msg' => $msg, 'data' => $data));
        }
        else
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }

    }

    public function profile_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('name', 'Full Name', 'trim|required|min_length[2]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|valid_email|is_unique[ec_driver.email]');
        $this->form_validation->set_rules('flat_no', 'Flat No', 'trim|required');
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        $this->form_validation->set_rules('zip_code', 'Pin Code', 'trim|required');
    }

    public function go_to_page_name()
    {
        #1=>LOGIN, 2=>KYC, 3=>HOME, 4=>START SCREEN
        $arr = array('1'=>'LOGIN', '2'=>'KYC', '3'=>'HOME', '4'=>'START SCREEN');
        $go_to = 1;
        if($this->logged_in())
        {
            $sesson_obj = $this->session->userdata('driver');
            $screenid  = $this->Query_model->get_data_obj('ec_driver_vehicle', array('driver_id' => $sesson_obj['login_id']));

            $is_reject = $this->Query_model->get_data_obj('ec_driver', array('driver_id'=> $sesson_obj['login_id']));
            
            if((isset($screenid->screen_id) && $screenid->screen_id == 6) && ($is_reject->status != '2')) $go_to = 3; else $go_to = 2;

            $order_item_obj = $this->Query_model->get_data_obj('ec_order_item', array('driver_id'=> $sesson_obj['login_id'], 'status'=>'19')); 
            if($order_item_obj && $order_item_obj->driver_id) $go_to = 4;

            
            
            api_response(array('status'=>1, 'msg'=>'Already Login', 'data'=> array('go_to'=>$go_to, 'screen'=> $arr[$go_to])));
        }
        else
        {
            api_response(array('status'=>1, 'msg'=>'Already logout', 'data'=>array('go_to'=>$go_to)));
        }
        
    } 


}
