<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Driver extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Vendor_model');
        $this->load->model('Order_model');
		$this->load->model('Shipping_model');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        $this->FNAME = $this->session->userdata($this->TYPE)['fname'];
    }

    public function index()
    {
        $crumbs = array("Home" => "/$this->TYPE/dashboard", "Driver Listing" => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
        $this->load->template("$this->TYPE/driver/index_driver",$data);
    }


    public function index_ajax_driver()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['start']))?$_POST['start']: 1;
        $list = $this->Shipping_model->get_data('ec_driver', '', array('driver_id'=> 'DESC'),array('start'=>$page, 'length'=>$length));
        $data = array();
        $i=1; 
        foreach ($list as $obj) {
            
            if($obj->status == 0)
            {
                $status = 'Inactive';
            }elseif($obj->status == 1)
            {
                $status = 'Active / KYC Approved';
            }else{
                $status = 'KYC Rejected';
            }              
  
            $row = array();
            $row['id']    	= $i++;
            $row['driver_uid']        = $obj->driver_uid;
            $row['name']              = isset($obj->name) ? $obj->name : 'N/A';
            $row['email']             = isset($obj->email) ? $obj->email : 'N/A';
            $row['mobile']            = $obj->mobile;
            $row['commission']        = isset($obj->commission) ? $obj->commission : 0;
            $row['status']            = $status; //($obj->status == 1) ? 'Active' : 'Inactive';
            $data[] = $row;
        }
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Shipping_model->count_all_driver('ec_driver'),
                "recordsFiltered" => $this->Shipping_model->count_filtered_driver('ec_driver'),
                "data" => $data,
                );

        echo json_encode($output);
    }

    public function add()
    {
        $this->post();
    }

    public function update($driver_id)
    {
        $this->post($driver_id);
    }

    function post($driver_id = NULL)
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('status', 'Status', 'trim|required');
        $this->form_validation->set_rules('commission', 'Admin commission', 'trim|required|greater_than[0]');

        if(isset($driver_id)){
            $driver_obj = $this->Query_model->get_data_obj('ec_driver',array('driver_id' => $driver_id));
            if(!$driver_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/driver");
            }
        }

        if($_POST) {
            $data =  array(
                    'status' => $this->input->post('status'),
                    'commission'    => $this->input->post('commission'),
                    );
        }else{
            if(isset($driver_id)){
                $data =  array(
                        'status' => $driver_obj->status,
                        'commission'    => $driver_obj->commission,
                        );
            }
        }

        if ($this->form_validation->run() == FALSE){
            $action_bc = isset($driver_id) ? 'Update' : 'Add';
            $crumbs = array("Home" => "/$this->TYPE/dashboard", "Driver" => "/$this->TYPE/driver/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($driver_id) ? 'update' : 'add';
            $data['driver_id'] = isset($driver_id) ? $driver_id : NULL;
            //$this->load->template("$this->TYPE/driver",$data);

            $data['driver_info']    = $this->Query_model->get_data_obj('ec_driver', array('driver_id' => $driver_id));
            $data['driver_info']->user_img = base_url().'assets/images/no-image.png';
            $data['vehicle_info']   = $this->Query_model->get_data_obj('ec_driver_vehicle', array('driver_id' => $driver_id));

            $this->load->template("$this->TYPE/driver/driver_info",$data);
        }else{
            //$data['status'] = ($data['status']) ? $data['status'] : '0';
            $db_data =  array(
                    'status'  => $this->input->post('status'),
                    'commission'     => $this->input->post('commission')
                    );
            if(isset($driver_id)){
                $this->Query_model->update_data('ec_driver', $db_data, array('driver_id' => $driver_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');
            }else{
                $this->Query_model->insert_data('ec_driver',$db_data);
                $this->session->set_flashdata('success', 'Inserted Successfully.');
            }
            redirect("$this->TYPE/driver");
        }
    }
    public function filter_order($vendor_id)
    {
        $this->LOGIN_ID=2; 
        $order_list = $this->Order_model->order_list(array('login_id'=>$this->LOGIN_ID,'post_data' => '' ));
        echo "<pre>"; print_r( $order_list); echo "</pre>";exit;

        $order_wise = array(); $order_count = 0;
        if($order_list)
        {
            foreach($order_list as $row_wise)
            {
                $product_obj        = $this->Query_model->get_data_obj('ec_product',array('product_id' => $row_wise->product_id));
                $row_wise->date_created_modify = date('d-M-Y H:i', strtotime($row_wise->date_created));
                $row_wise->product_obj = $product_obj;

                $order_wise['items'][$row_wise->order_uid][] = $row_wise;
                $order_wise['order_dtl'][$row_wise->order_uid] = $row_wise;
            }
        }

        $api_data['order_wise']= $order_wise;

        if(count($api_data['order_wise']))
            api_response(array('status'=>1, 'msg'=>'Sucess', 'data'=>$api_data));
        else
            api_response(array('status'=>0, 'msg'=>'There is no data', 'data'=>array()));
    }


    public function driver_details($driver_uid = NULL)
    {
        $crumbs             = array("Home" => "/$this->TYPE/dashboard", "Driver Listing" => "/$this->TYPE/driver", 'Driver Detail'=> '');
        $breadcrumbs        = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']= $breadcrumbs;
        $data['csrf']       = csrf_token();
        $driver_id          = get_driver_id($driver_uid);
        $data['TYPE']       = $this->TYPE;
        $data['driver_id']  = $driver_id->driver_id;
        $data['page_count'] = page_count();

        $data['driver_info']        = $this->Query_model->get_data_obj('ec_driver', array('driver_id' => $driver_id->driver_id));
        $data['vehicle_info']       = $this->Query_model->get_data_obj('ec_driver_vehicle', array('driver_id' => $driver_id->driver_id));
       // $vehicle_type               = $vehicle_type[$data['vehicle_info']->vehicle_type]->vehicle_type;
        $data['vehicle_type_info']  = isset($data['vehicle_info']->vehicle_type) ? get_vehicle_type($data['vehicle_info']->vehicle_type)->vehicle_type : 'N/A'; 
        $data['driver_doc_info']    = $this->Query_model->get_data_obj('ec_driver_documents', array('driver_id' => $driver_id->driver_id));
        $data['bank_info']          = $this->Query_model->get_data_obj('ec_bank_details', array('driver_id' => $driver_id->driver_id));
        $data['order_info']         = $this->Vendor_model->count_all_item('ec_order_item', array('driver_id' => $driver_id->driver_id));
        $data['order_info']         = $this->Vendor_model->count_all_item('ec_order_item', array('driver_id' => $driver_id->driver_id));
        $driver_amount              = $this->driver_amount_transfer($driver_id->driver_id);
        $this->load->template("$this->TYPE/driver/driver_info",$data);
    }


    public function shipping()
    {
        $crumbs = array("Home" => "/$this->TYPE/dashboard", "Shipping Cost" => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
        $this->load->template("$this->TYPE/driver/index_shipping",$data);
    }    

    public function index_ajax_shipping()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;       
        $list = $this->Shipping_model->get_data('ec_vehicle_price',null, array('date_added'=>'desc'));
        
		$vehicle = vehicle_type();
        $data = array();
        foreach ($list as $obj) {
            $row = array();
            $row['price_id']        = $obj->price_id;
			$row['apply_date']      = !$obj->apply_date ? '-' : date('Y-m-d', strtotime($obj->apply_date));
		
			$row['end_date']      	= !$obj->end_date ?  '-' : date('Y-m-d', strtotime($obj->end_date));
		
            $row['vehicle_type']    = $vehicle[$obj->vehicle_type];
            $row['price_per_km']    = $obj->price_per_km.' Km';
            $row['status']          = ($obj->status == '1') ? 'Active' : 'Inactive';
            $data[] = $row;
        }
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Shipping_model->count_all_shipping('ec_vehicle_price'),
                "recordsFiltered" => $this->Shipping_model->count_filtered_shipping('ec_vehicle_price'),
                "data" => $data,
                );
		//echo "<pre>"; echo "</pre>";
		//dd($output);
        echo json_encode($output);
    }


    public function add_shipping()
    {
        $this->post_shipping();
    }

    public function update_shipping($price_id)
    {
        $this->post_shipping($price_id);
    }

    function post_shipping($price_id = NULL)
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('vehicle_type', 'Vehicle Type', 'trim|required');
        $this->form_validation->set_rules('price_per_km', 'Price / Km', 'trim|required');
		//$this->form_validation->set_rules('apply_date', 'Apply date', 'trim|required|callback_check_order_no');

        if(isset($price_id)){
            $price_obj = $this->Query_model->get_data_obj('ec_vehicle_price',array('price_id' => $price_id));
            if(!$price_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/driver/shipping");
            }
        }

        if($_POST) {
            $data =  array(
                    'vehicle_type'  => $this->input->post('vehicle_type'),
                    'apply_date'    => $this->input->post('apply_date'),
					//'end_date'      => $this->input->post('end_date'),
                    'price_per_km'  => $this->input->post('price_per_km'),
                    'status'        => $this->input->post('status'),
                    );
        }else{
            if(isset($price_id)){
                $data =  array(
                        'vehicle_type'  => $price_obj->vehicle_type,
                        'apply_date'    => $price_obj->apply_date,
						//'end_date'      => $price_obj->apply_date,
                        'price_per_km'  => $price_obj->price_per_km,
                        'status'        => $price_obj->status,
                        );
            }
        }

        if ($this->form_validation->run() == FALSE){
            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($price_id) ? $page_lang->update : $page_lang->add;
            $crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", 'Delivery Charges' => "/admin/driver/shipping", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf']           = csrf_token();
            $data['TYPE']           = $this->TYPE;
            $data['action']         = isset($price_id) ? 'update_shipping' : 'add_shipping';
            $data['price_id']       = isset($price_id) ? $price_id : NULL;
            $data['vehicle_type']   = $this->Query_model->get_data('ec_vehicle_type', array('status'=>'1')); 
            $this->load->template("$this->TYPE/driver/add_shipping",$data);
        }else{
			 $apply_date = date('Y-m-d',strtotime($data['apply_date']));
			 $today = date('Y-m-d');
			if($apply_date  == $today)
				$data['status'] = '1';
			else
				$data['status'] = '0';
			
           
            $db_data =  array(
                    'vehicle_type'  => $data['vehicle_type'],
                    'apply_date'    => $data['apply_date'],
					//'end_date'      => $data['end_date'],
                    'price_per_km'  => $data['price_per_km'],
                    'status'        => $data['status'],
                    );
					
		$driver_obj = $this->Shipping_model->table('ec_vehicle_price')
		 ->where('vehicle_type',$data['vehicle_type'])
		 ->where('apply_date','LIKE',$apply_date.'%')
		 ->one();
		 
		
        $vehicle_price_old = $this->Query_model->get_data('ec_vehicle_price', array('vehicle_type'=> $data['vehicle_type']), array('apply_date'=>'ASC'));    
        $end_dd = []; 
			if(!$driver_obj || empty($driver_obj)){
				#$this->Query_model->insert_data('ec_vehicle_price',$db_data);
                if(count($vehicle_price_old))
                {
                    $apply_date_str = strtotime($data['apply_date']);
                    foreach($vehicle_price_old as $row_wise)
                    {
                        $apply_date_old_str = strtotime($row_wise->apply_date);
                        if($apply_date_str < $apply_date_old_str)
                        {
                    
                        }            

                        #if($apply_date_str > $apply_date_old_str)
                        if(1)
                        {
                            $end_date = date( "Y-m-d", strtotime( $data['apply_date'] . "-1 day"));
                            if(count($end_dd) < 1 && $apply_date_str < $apply_date_old_str)
                            {
                                $end_dd[] = $row_wise->apply_date;  
                            }
                            if($row_wise->end_date == NULL)   
                            {
                                $end_date = $apply_date_str > $apply_date_old_str ? $end_date : '';
                                if($end_date)
                                $this->Query_model->update_data('ec_vehicle_price', array('end_date'=> $end_date), array('price_id'=> $row_wise->price_id)); 

                                break;  
                            }
                        }
                        else
                        {
                            $end_date = date( "Y-m-d", strtotime( $data['apply_date'] . "-1 day"));
                        
                        }
                        
                    }       
                }
                $db_data['end_date'] = isset($end_dd[0]) ? $end_dd[0] : NULL;
                $this->Query_model->insert_data('ec_vehicle_price',$db_data);
				$this->session->set_flashdata('success', 'Inserted Successfully.');
            }else{
				
				if($apply_date  != $today){
                #$vehicle_obj = $this->Query_model->get_data('ec_vehicle_price', array(''));
                #$db_data['end_date'] = date( "Y-m-d", strtotime( $apply_date . "-1 day"));
                

				$this->Shipping_model->table('ec_vehicle_price')
					->where('vehicle_type',$data['vehicle_type'])
					->where('apply_date','LIKE',$apply_date.'%')
					->update(array('price_per_km' => $db_data['price_per_km'], 'end_date' => $db_data['end_date'] ));
				

                $this->session->set_flashdata('success', 'Updated Successfully.');
				}else 
					 $this->session->set_flashdata('error', 'Driver is running / only future entries allowed');
				
            }
            redirect("$this->TYPE/driver/shipping");
        }
    }
	
	
	function update_shipping_status() {
		
				$today = date('Y-m-d');		
				$this->Shipping_model->table('ec_vehicle_price')
					->where('apply_date','LIKE',$today.'%')
					->update(array('status' => '1'));
					
					$this->Shipping_model->table('ec_vehicle_price')
					->where('apply_date','NOT LIKE',$today.'%')
					->where('end_date','<',$today.'%')
					->where('status','1')
					->update(array('status' => '0'));
				
		
	}

	function check_apply_date($order_no) {        
        if($this->input->post('id'))
            $id = $this->input->post('id');
        else
            $id = '';
        $result = $this->Data_model->check_unique_order_no($id, $order_no);
        if($result == 0)
            $response = true;
        else {
            $this->form_validation->set_message('check_order_no', 'Apply date already exist');
            $response = false;
        }
        return $response;
    }

    public function driver_amount_transfer($driver_id = NULL)
    {
        $data = array();
        $checkdata = $this->Query_model->get_data('ec_driver_earnings', array('driver_id' => $driver_id)); 
        $getdata   = $this->Order_model->get_data_by_month(array('driver_id'=>$driver_id));
        $admin_commission = $this->Query_model->get_data_obj('ec_driver', array('driver_id' => $driver_id));
        $admincom = $admin_commission->commission;
        $getmonth  = get_month();        
        $year      = date("Y"); 

        if(empty($checkdata))
        {
            $insdata    =   array();
            $current_YM = date('Y-m');
            foreach($getdata as $monthdata)
            {
                $admin_amount          = ($admincom / 100) * $monthdata->shipping;
                $month                 = $getmonth[$monthdata->month];
                $days                  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $datayear              = $monthdata->Year;

                if($current_YM == $datayear.'-'.$month){continue;}
                //if('2021-12' == $datayear.'-'.$month){continue;}

                $insdata = array( 
                    'driver_id'         => $monthdata->driver_id,
                    'admin_percentage'  => $admincom, 
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
                        $admin_amount          = ($admincom / 100) * $monthdata->shipping;
                        $month                 = $getmonth[$monthdata->month];
                        $days                  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $datayear              = $monthdata->Year;

                        if($current_YM == $datayear.'-'.$month){continue;}

                        $insdata = array(       
                                'driver_id'         => $monthdata->driver_id,
                                'admin_percentage'  => $admincom, 
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
        $admin_commission = $this->Query_model->get_data_obj('ec_driver', array('driver_id' => $args['driver_id']));
        $admincom = $admin_commission->commission;

        if(count($getdata))
        {
            $admin_amount                = ($admincom / 100) * $getdata[0]->shipping;
            $driveramount                = $getdata[0]->shipping - $admin_amount;
            $getdata[0]->earning_id      = '';
            $getdata[0]->admin_id        = '';
            $getdata[0]->driver_id       = $getdata[0]->driver_id;
            $getdata[0]->total           = $getdata[0]->shipping;
            $getdata[0]->admin_percentage= $admincom;
            $getdata[0]->admin_amount    = number_format((float)$admin_amount, 2, '.', '');
            $getdata[0]->driver_amount   = number_format((float)$driveramount, 2, '.', '');
            $getdata[0]->from_date       = date("Y-m-01 H:i:s");
            $getdata[0]->to_date         = date("Y-m-d H:i:s");
            $getdata[0]->current_month   = '0';

        }
        return $getdata;
    } 
    
    public function get_driver_amount()
    {
        $data = array();
        $driver_id = $this->input->post('driver_id');
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
        $monthyear = date('M Y');

        $list = $this->Query_model->get_data('ec_driver_earnings', array('driver_id'=>$driver_id), array('earning_id'=> 'DESC'));
        $curnt_mnth_data = $this->current_month_earning(array('driver_id'=>$driver_id, 'current_mnth'=>1, 'yrmnth'=>date('Y-m')));
        $newArr = array_merge($curnt_mnth_data, $list);

        //print_r($newArr); exit;

        foreach($newArr as $obj) {

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
             
            $row['earning_id']      = $obj->earning_id;
            $row['driver_id']       = $obj->driver_id;
            $row['total']           = $obj->total;
            $row['admin_amount']    = $obj->admin_amount;
            $row['driver_amount']   = $obj->driver_amount;
            $row['from_date']       = date('M Y', strtotime($obj->from_date));
            $row['to_date']         = date('M d Y', strtotime($obj->to_date));
            $row['paid_type']       = isset($obj->paid_type) ? $obj->paid_type : 'N/A';
            $row['paid_date']       = isset($obj->paid_date) ? date('M d Y', strtotime($obj->paid_date)) : 'N/A';
            $row['paid_status']     = isset($obj->paid_status) ? $obj->paid_status : '0' ;
            $row['current_month']   = $current_month;

            $data[] = $row;
        }

        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Query_model->count_all('ec_driver_earnings', array('driver_id'=>$driver_id)),
                "recordsFiltered" => $this->Query_model->count_filtered('ec_driver_earnings', array('driver_id'=>$driver_id)),
                "data" => $data,
                );
        echo json_encode($output);

    } 

    public function amount_transfer()
    {
        $PaidType       = $this->input->post('PaidType'); 
        $all_info       = $this->input->post('all_info');
        $decode_all     = json_decode($all_info);
        $earning_id     = $decode_all->earning_id;
        $driver_amount  = $decode_all->driver_amount;
        $date           = date('Y-m-d h:i:s a', time());

        $tranferdata = array('paid_type' => $PaidType, 'driver_amount' => $driver_amount, 'paid_date' => $date, 'paid_status' => '1');
        $trandata = $this->Query_model->update_data('ec_driver_earnings', $tranferdata, array('earning_id'=>$earning_id));
        if($trandata)
        {
            $output = array('success'=>'Amount Transferred Successfully');
        }else
        {
            $output = array('Fail'=>'Amount is not Transferred');
        }
        
        echo json_encode($output);
    }  


	public function check_shipping()
    {
        $output = [];
		
		
		$vehicle_type = $this->input->post('vehicle_type');
		$apply_date  = date('Y-m-d', strtotime($this->input->post('apply_date')));
		
		$output['status'] = false;
		$output['vehicle_type'] = $vehicle_type;
		$output['apply_date'] = $apply_date;
		
		$driver_obj = $this->Shipping_model->table('ec_vehicle_price')
		 ->where('vehicle_type',$vehicle_type)
		 ->where('apply_date','LIKE',$apply_date.'%')
		 ->one();
		 
		
       
		if(!$driver_obj || empty($driver_obj))
			$output['status']=true;
		
		$output['obj'] =$driver_obj;
		header("Content-type: application/json; charset=utf-8");
        echo json_encode($output);
    }



}
