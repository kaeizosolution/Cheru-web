<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Banner extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('Query_model');
		$this->load->model('Attribute_model');
		$this->TYPE = $this->session->userdata('type');		
	}

	public function index(){

		if(!logged_in()) redirect("$this->TYPE/auth/login");

		$data = array();
        $page_lang = get_page_language_data('admin_page_lang');
		$crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->add_all_banner => ""); 
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		$data['page_count'] = page_count();		
		$this->load->template("$this->TYPE/banner/index",$data); 	
                
	}
	

	public function add($widget_id = NULL)
	{
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$data = array();
		$data['admin'] = 1;
		$banner_type = banner_type($widget_id);
        $page_lang = get_page_language_data('admin_page_lang');
		$crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->banner => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		$data['error'] = false;
        $data['type'] = '';
        $data['banner_type'] = $banner_type;
		$data['widget_id'] = $widget_id;
        $data['message']['error'] = '';

        $this->banner_validation();

		if($_POST) {
            $data['type']          		= $widget_id;
            $data['url']             	= $this->input->post('url');
            $data['title']             	= $this->input->post('title');
            $data['banner_image']       = $this->input->post('banner_image');
            $data['height']            	= $this->input->post('height');
            $data['width']          	= $this->input->post('width');
            $data['enabled']          	= $this->input->post('enabled');
        }else{
            if(isset($banner_id)){
				
				redirect("$this->TYPE/banner/add", $data);				
            }
        }
        if ($this->form_validation->run())
        {
            $post_data = array(
                    'type'     		=> $widget_id,
                    'title'         => $data['title'],
                    'url'           => $data['url'],
                    'banner_image'  => $data['banner_image'],
                    'height'        => $data['height'],
                    'width'         => $data['width'],
                    'enabled'       => $data['enabled'],
            );
            if(isset($banner_id)){
                $condition = array('banner_id' => $banner_id);
                $this->Query_model->update_data('ec_banner',$post_data, $condition);
                $this->session->set_flashdata('message', 'Data updated sucessfully.');
                $this->banner_upload($banner_id, $widget_id);
                redirect("$this->TYPE/banner/add/$widget_id");
            }else{
                $banner_id = $this->Query_model->insert_data('ec_banner',$post_data);
                $this->session->set_flashdata('message', 'Data inserted sucessfully.');
                $this->banner_upload($banner_id,$widget_id);
                redirect("$this->TYPE/banner/get_ajax_banner/$widget_id");
            }
        }else{
            $data['TYPE']          = $this->TYPE;
            $data['csrf']          = csrf_token();
            $this->load->template("$this->TYPE/banner/add",$data); 	
        }  
               
	}


	public function banner_validation()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('title', 'Banner Heading', 'trim|required');
		#$this->form_validation->set_rules('url', 'URL', 'trim|required');
		//$this->form_validation->set_rules('image', 'Image', 'trim|required');
		#$this->form_validation->set_rules('height', 'Banner Height in px', 'trim|required');
		#$this->form_validation->set_rules('width', 'Banner width in px', 'trim|required');
	}


	
    public function update($banner_id = NULL)
    {

        if(!logged_in()) redirect("$this->TYPE/auth/login");
        $data = array();
        $data['admin'] = 1;
        $page_lang = get_page_language_data('admin_page_lang'); 
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->banner => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['error'] = false;
        $data['message']['error'] = '';

        $this->banner_validation();

        $condition = array("banner_id" => $banner_id);
        $requirement_obj = $this->Query_model->get_data_obj('ec_banner', $condition);
        if( isset($requirement_obj) && !empty($requirement_obj) ){
			
            $data['banner_id']              = $requirement_obj->banner_id;
            $data['title']                  = $requirement_obj->title;
            $data['type']                   = $requirement_obj->type;
            $data['url']                    = $requirement_obj->url;
            $data['banner_image']           = $requirement_obj->banner_image;
            $data['height']                 = $requirement_obj->height;
            $data['width']                  = $requirement_obj->width;
            $data['enabled']                = $requirement_obj->enabled;
        }
        if ($this->form_validation->run())
        {   
            if($_POST){
                $post_data = array(
                        'type'          => $this->input->post('type'),
                        'title'         => $this->input->post('title'),
                        'url'           => $this->input->post('url'),
                        //'banner_image'  => $this->input->post('banner_image'),
                        'height'        => $this->input->post('height'),
                        'width'         => $this->input->post('width'),
                        'enabled'       => $this->input->post('enabled')
                );
            }

            if(isset($banner_id)){
                $condition = array('banner_id' => $banner_id);
				$widget_id = $post_data['type'];
                $this->Query_model->update_data('ec_banner',$post_data, $condition);
                $this->session->set_flashdata('message', 'Data updated sucessfully.');
                $this->banner_upload($banner_id,$widget_id);
                
                redirect("$this->TYPE/banner/get_ajax_banner/$widget_id");
            }
        }
            
		if( isset($data['type']) ){
         $banner_type = banner_type($data['type']);
         $data['banner_type'] = $banner_type;
		}	 
        $this->load->template("$this->TYPE/banner/add",$data);
    }

	public function banner_upload($banner_id = NULL, $widget_id = NULL)
    {	
		if(isset($_FILES["banner_image"]["name"]) && $_FILES["banner_image"]["name"] != '' ){
		   	$config = array(				
				'upload_path' => "./assets/home_page_banner",  //"./assets/images"	
				'allowed_types' => "gif|jpg|png|jpeg|pdf",
				'max_size' => "20240000",
			);				
											
			$this->load->library('Upload', $config);
			$this->upload->initialize($config); 	
            if ($this->upload->do_upload('banner_image'))
            {
				$data_file = $this->upload->data();	
				$ppnewimagename = $data_file["file_name"];
				$thumb_arr = explode(".", $ppnewimagename);
				$ppthumb = $thumb_arr[0] . "_thumb". "." . $thumb_arr[1];
				#echo $widget_id;die;
				
					if($widget_id==1){
					//code for resize image	
					$config2 = array(
						'image_library' =>	'gd2', //get original image
						'source_image' 	=> "./assets/home_page_banner/".$data_file['file_name'],
						'maintain_ratio'=> true,
						'width' =>800,
						'height' =>800, 
						'new_image' 	=> "./assets/home_page_banner/".$ppthumb,				
					);	
					
				}elseif($widget_id==3){
					//code for resize image	
					$config2 = array(
						'image_library' =>	'gd2', //get original image
						'source_image' 	=> "./assets/home_page_banner/".$data_file['file_name'],
						'maintain_ratio'=> true,
						'width' =>400,
						'height' =>400, 
						'new_image' 	=> "./assets/home_page_banner/".$ppthumb,				
					);	
					
				}elseif($widget_id==2){
					//code for resize image	
					$config2 = array(
						'image_library' 	=>	'gd2', //get original image
						'source_image' 		=> "./assets/home_page_banner/".$data_file['file_name'],
						'maintain_ratio'	=> true,
						'width' 			=>150,
						'height' 			=>150, 
						'new_image' 		=> "./assets/home_page_banner/".$ppthumb,				
					);	
					
				}elseif($widget_id==4){
					//code for resize image	
					$config2 = array(
						'image_library' 	=>	'gd2', //get original image
						'source_image' 		=> "./assets/home_page_banner/".$data_file['file_name'],
						'maintain_ratio'	=> true,
						'width' 			=>600,
						'height' 			=>600, 
						'new_image' 		=> "./assets/home_page_banner/".$ppthumb,				
					);	
					
				}else{
					$config2 = array(
						'image_library' 	=>	'gd2', //get original image
						'source_image' 		=> "./assets/home_page_banner/".$data_file['file_name'],
						'maintain_ratio'	=> true,
						'width' 			=>800,
						'height' 			=>800, 
						'new_image' 		=> "./assets/home_page_banner/".$ppthumb,				
					);	
					
				}
				#echo "<pre>"; print_r($config2);echo "</pre>";die;
				
				
				$this->load->library('image_lib', $config2); 
				$this->upload->initialize($config2); 
				
				$this->image_lib->resize();
				$this->image_lib->clear();
				
				$img_url = $ppthumb;	

				$post_data = array('banner_image' => $img_url);
                $condition = array('banner_id' => $banner_id);
				
                $this->Query_model->update_data('ec_banner',$post_data, $condition);
				
				
            }
        }
    }
	
	public function ajax_get_widget_type()
	{
		$length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

		$arg = array();
		$widgetlist = $this->Query_model->get_data('ec_widget',array('status' => array('1')));
		$i =1;
        $button_widget_id = '';
        foreach ($widgetlist as $widgetdata) {
        		$row = array();

        		$row ['button_widget_id'] = '';
				$row['sl_id'] 	= $i;
        		$row['widget_id'] 	= $widgetdata->widget_id;
        		$row['widget_name'] = "<a href='/admin/banner/get_ajax_banner/$widgetdata->widget_id'>".$widgetdata->widget_name.'</a>';        		
        		$row['button_widget_id'] 	.="<br><a href=\"/admin/banner/add/$widgetdata->widget_id\" target=\"_blank\"  class=\"table_btn_add\">Add</a>";
        		$data[] = $row;
			$i++;	
         }


         $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Query_model->count_all('ec_widget'),
                "recordsFiltered" => $this->Query_model->count_filtered('ec_widget'),
                "data" => $data,
                );
    
        echo json_encode($output);
	}


    public function get_ajax_banner($widget_id = NULL)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");

        $data = array();
        $banner_type = banner_type($widget_id);
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->add_all_banner => ""); 
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['banner_type'] = $banner_type;
        $data['page_count'] = page_count();     
        $data['widget_id'] = $widget_id;
        $this->load->template("$this->TYPE/banner/index_banner",$data); 
    }

	
    public function index_ajax_get_banner()
    {   
        $widget_id = $this->input->post('widget_id'); 
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
        $condition = array('type' => $widget_id);
        $bannerlist = $this->Query_model->get_data('ec_banner', $condition);
        $data = array();
        foreach ($bannerlist as $bannerlistdata) {

                $row = array();
                $row['banner_id']       = $bannerlistdata->banner_id;
                $row['title']           = $bannerlistdata->title;
                $row['url']             = $bannerlistdata->url;
                $row['banner_image']    = base_url().'assets/home_page_banner/'.$bannerlistdata->banner_image;
                $row['height']          = $bannerlistdata->height;
                $row['width']           = $bannerlistdata->width;
                $row['status']          = ($bannerlistdata->enabled == 1) ? "Active" : "Inactive";
                $data[] = $row;
         }

         $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Query_model->count_all('ec_banner', $condition),
                "recordsFiltered" => $this->Query_model->count_filtered('ec_banner', $condition),
                "data" => $data,
                );
    
        echo json_encode($output);
    }


    public function update_ajax_status()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        if($status == 'Active'){
            $status = '0';
        }
        else if($status == 'Inactive'){
            $status = '1';
        }
        $post_data = array('enabled' => $status);
        $condition = array('banner_id' => $id);
        $this->Query_model->update_data('ec_banner',$post_data, $condition);
        $data['message']  = 'Status Changed Successfully';
        echo json_encode($data);
        die();
    }


    public function ajax_delete_banner()
    {
        $id = $this->input->post('id');
        $condition = array('banner_id' => $id);
        $this->Query_model->delete('ec_banner', $condition);
        $data['message']  = 'Status Changed Successfully';
        echo json_encode($data);
        die();
    }

	public function delimg()
    {
        $data['csrf'] = csrf_token();
        $banner_id = $this->input->post('id');	
		$product_id = $this->input->post('product_id');			
        $data['csrf'] = csrf_token();
        $data = array();
        $delete = 0;
        if($banner_id){
            $delete = $this->Attribute_model->delete_image($product_id, $banner_id);
        }
		//echo '==='.$delete;
		//die;
        echo json_encode(array('success' => $delete));
    }

}
