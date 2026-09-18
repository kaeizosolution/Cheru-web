<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store extends MY_Controller {

	function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Product_model');
		$this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }  


	public function index()
	{
		$data   = array();
		$data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
		$data['homepage'] = 1;
		$data['store_list'] = $this->store();
		$data['cate_all_strip']  = $this->Product_model->categories_all_strip();
        $categories = '';	
        	
		$this->load->template("$this->TYPE/store_list", $data);

	}

    
    public function store_list()
    {
       $data  = array();
       $lat   = $this->input->post('latitude');
       $long  = $this->input->post('longitude');
       $distance = '50'; 

       $args_locator = array('lat'=>$lat, 'long'=>$long, 'distance'=>$distance);
       $vendor_data = $this->Query_model->get_locator($args_locator);

       if($vendor_data)
       {
           $vendor_id_arr = array();
           foreach($vendor_data as $row)
           {
               $vendor_img = unserialize($row->vendor_img);

               $address_modify         = $row->address;
               $address_modify         = strlen($address_modify) > 30 ? substr($address_modify,0,30)."..." : $address_modify;
               $row->vendor_id         = $row->admin_id;
               $row->address_modify    = $address_modify;
               $row->distance_modify   = round($row->distance).' Km';
               $row->vendor_img_modify = isset($vendor_img['thumb']) ? $vendor_img['thumb'] : base_url().'assets/images/store/s-1.png';
               $row->slug_param        = strtolower(strtok($row->store_name, " "));
               $row->url               = "/store/$row->vendor_uid/$row->slug_param";
           }
       }

        if($vendor_data)
        {
            api_response(array('status'=>1, 'msg'=>'Success', 'data'=>$vendor_data));
        }else{
            api_response(array('status'=>1, 'msg'=>'No data found', 'data'=>array()));
        } 
    }
	
	public function store()
	{
		
		$stores	=	$this->Query_model->get_data('ec_admin', array('status'=>'1', 'role_id'=>'2'));

		#this is use for the input value	
		$latitude	=	$this->input->post('latitude');
		$longitude	=	$this->input->post('longitude');
		$distance	=	$this->input->post('distance');

		$store_loc  =   array('lat' => $latitude, 'long' => $longitude, 'distance'=> $distance);	
		$data   =   array();
		foreach($stores as $store_list)
		{
			if(empty($latitude) && empty($longitude) && empty($distance))
			{
				$store_list->vendor_img = $store_list->vendor_img;
				$store_list->vendor_img = isset($store_list->vendor_img) ? base_url().'assets/default_images/'.$store_list->vendor_img :  base_url().'assets/default_images/store.png';
				$data[] =   $store_list;
			}
			else
			{
					$locator = $this->Query_model->get_locator($store_loc);
					$store_list->vendor_img = isset($locator->vendor_img) ? base_url().'assets/default_images/'.$locator->vendor_img : base_url().'assets/default_images/store.png';
					$store_list->distance =	sprintf('%.2f', $locator->distance);
					$data[] =   $store_list;
			}	
		}
		
		return $data;
	}



	

}


?>
