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
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        $this->FNAME = $this->session->userdata($this->TYPE)['fname'];
    }

    public function index()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", $page_lang->product => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token(); 
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();	
        $this->load->template("$this->TYPE/product/index_product",$data);       
    }

    public function index_ajax_post()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $type_array = array(1 => 'Fixed', 2 => 'Percentage');
        $list = $this->Product_model->get_datatable();			
        $data = array();
        foreach ($list as $obj) {

            $row = array();

            $author = $this->Query_model->get_author($obj->user_id);
            $autho_name = !empty($author->fname) ? $author->fname : $author->admin_uid;
            $row['product_id']   = $obj->product_id;
            $row['post_title']   = '<a href="/product/detail/'.$obj->post_slug.'">'.$obj->post_title.'</a>';
            $row['user_id']      = $autho_name;
            $row['comment']      = '-';
            $row['date']     	 = $obj->modified;
            $row['status']       = $obj->enabled;			
            $row['type']         = $obj->type;
            $row['status_str']   = ($obj->enabled) ? 'Active' : 'Inactive';
            $data[] = $row;
        } 
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Product_model->count_all(),
                "recordsFiltered" => $this->Product_model->count_filtered(),
                "data" => $data,
                );	

        echo json_encode($output);
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
                // $uploadPath = './assets/uploads/files/';
                // Desired folder structure
                $uploadPath = './assets/uploads/files/'.$this->input->post('sku').'/';

                // To create the nested structure, the $recursive parameter 
                // to mkdir() must be specified.

                if (!mkdir($uploadPath, 0777, true)) {
                    die('Failed to create folders...');
                }

                $config['upload_path'] = $uploadPath;
                $config['allowed_types'] = 'gif|jpg|png';                
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
                //Insert file information into the database
                $insert = $this->files->insert($uploadData);
                $statusMsg = $insert?'Files uploaded successfully.':'Some problem occurred, please try again.';
                $this->session->set_flashdata('statusMsg',$statusMsg);
            }
        }
        //Get files data from database
        $data['gallery'] = $this->files->getRows();
        //Pass the files data to view
        //$this->load->view('files_upload/index', $data);
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
        $this->post();
    }

    public function update($product_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");       
        $this->post($product_id);
    }

    function post($product_id = NULL)
    {
        $data =array();	
        $data = $this->input->post();	
        //echo "<pre>"; print_r($data ); echo "</pre>";	die;
        $qtyrange = $this->input->post('quantity_range');
        if($qtyrange){
            $qtyrange = serialize($qtyrange);	
        }else{
            $qtyrange = '';
        }

        $pricerange = $this->input->post('price_range');
        if($pricerange){
            $pricerange = serialize($pricerange);	
        }else{
            $pricerange = '';
        }					
        $user = $this->session->userdata();		
        $current_id =$user['admin']['login_id'];		
        $categories_obj = $this->Category_prod_model->find_list_prod();	
        $data['tags'] = $this->Tags_prod_model->find_list_prod();

		###
        $selected_variation_obj = array();
        $all_attribute_array = array();
        $all_attribute_item_array = array();
        $selected_attribute_item = array();
        $all_attribute_obj = $this->Attribute_model->get_all_attribute();
        if($all_attribute_obj){
            foreach($all_attribute_obj as $row){
               $all_attribute_array[$row->attribute_id] = $row->name; 
            }
        }
        $all_attribute_item_obj = $this->Attribute_model->get_all_attribute_item();
        if($all_attribute_item_obj){
            foreach($all_attribute_item_obj as $row){
               $all_attribute_item_array[$row->attribute_item_id] = $row->name;
            }
        }

        if($_POST){
            if(isset($_POST['attribute_item'])){
                $attribute_item = $_POST['attribute_item'];
                if($attribute_item && is_array($attribute_item) && count($attribute_item) > 0){
                    $selected_attribute_obj = $this->Attribute_model->get_attribute_item_attribute_item_id($attribute_item);
                    if($selected_attribute_obj){
                        foreach($selected_attribute_obj as $row){
                            $selected_attribute_item[$row->attribute_id][] = $row;
                        }
                    }
                }
            }
            if(isset($_POST['attr_itemid'])){
                foreach($_POST['attr_itemid'] as $index => $val){
                    $variation_id = time();
                    if(isset($_POST['variations_image'][$index])){
                        $selected_variation_obj[] = (object) array(
                            'variation_id'      => $variation_id,
                            'attribute_item_id' => $_POST['attr_itemid'][$index],
                            '_regular_price'    => $_POST['_regular_price'][$index],
                            '_sale_price'       => $_POST['_sale_price'][$index],
                            '_stock'            => $_POST['_stock'][$index],
                            '_thumbnail_id'     => $_POST['variations_image'][$index],
                        );
                    }else{
                        $selected_variation_obj[] = (object) array(
                            'variation_id'      => $variation_id,
                            'attribute_item_id' => $_POST['attr_itemid'][$index],
                            '_regular_price'    => $_POST['_regular_price'][$index],
                            '_sale_price'       => $_POST['_sale_price'][$index],
                            '_stock'            => $_POST['_stock'][$index],
                        );
                    }
                }
            }
        }else{
            if($product_id){
                $selected_attribute_obj = $this->Attribute_model->get_attribute_item_product_id($product_id);
                if($selected_attribute_obj){
                    foreach($selected_attribute_obj as $row){
                        $selected_attribute_item[$row->attribute_id][] = $row;
                    }
                }
                $selected_variation_obj = $this->Attribute_model->get_product_variation($product_id);
            }
        }

        $brand_array = array();
        $brand_obj = $this->Query_model->get_data('ec_brand', array('status' => '1'));
        $supplier_obj = $this->Query_model->get_data('ec_supplier', array('status' => '1'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules('sku', 'SKU', 'trim|required|callback_unique_sku['.$product_id.']');
        $this->form_validation->set_rules('post_title', 'Post Title', 'trim|required|callback_unique_slug['.$product_id.']');
        if(isset($product_id)){			
            $product_obj = $this->Query_model->get_data_obj('ec_product',array('product_id' => $product_id, 'enabled' => '1')); 
            if(!$product_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/product");
            }			 
            $data['gallery'] = $this->Query_model->get_data('ec_gallery',array('product_id' => $product_id));		
        }
        if($_POST) {		
            $data =  array(
                    'type'                  => $data['type'],
                    'post_title'          	=> $data['post_title'],
                    'post_title_es'         => $data['post_title_es'],
                    'post_content'        	=> $data['post_content'],
                    'post_content_es'       => $data['post_content_es'],
                    'tax_id'                => $data['tax_id'],
                    'shipping_id'           => $data['shipping_id'],
                    'brand_id'              => $data['brand_id'],
                    'supplier_id'           => $data['supplier_id'],
                    'post_slug'           	=> $this->get_slug( $data['post_title'] ),
                    'regular_price'       	=> $data['regular_price'],
                    'sale_price'       	  	=> $data['sale_price'],
                    'sale_price_dates_from' => $data['sale_price_dates_from'],
                    'sale_price_dates_to'   => $data['sale_price_dates_to'],				
                    'sku'        		  	=> $data['sku'],
                    'stock'               	=> $data['stock'],
                    'weight'               	=> $data['weight'],
                    'length'               	=> $data['length'],
                    'width'               	=> $data['width'],
                    'height'               	=> $data['height'],			
                    'quantity_range'      	=> $qtyrange,
                    'price_range'         	=> $pricerange,
                    //'price_range'       	=> $data['price_range'],
                    'enabled'             	=> $data['enabled'],
                    'featured_product'      => $data['featured_product'],
                    'category_ids'          => $data['category'],
                    );		
            
        }else{
            if(isset($product_id)){
                $current_category = $this->db->select('category_id')->where(array('post_id' => $product_id ))->get('ec_product_categories')->result_array();
                $current_tag = $this->db->select('tag_id')->where(array('post_id' => $product_id ))->get('ec_product_tags')->result_array();
                $current_attributes = $this->Attribute_model->get_data_attr($product_id);

                $category_ids = array();
                if(!empty($current_category)){
                    foreach($current_category as $current){
                        $category_ids[] = $current['category_id'];
                    }
                }

                $tag_ids = array();
                if(!empty($current_tag)){
                    foreach($current_tag as $cur_tag){
                        $tag_ids[] = $cur_tag['tag_id'];
                    }
                }
                $data['category_ids'] = $category_ids;
                $data['tag_ids'] = $tag_ids;	

                $get_data = (array)$this->Product_model->edit($product_id);
                $data1['product_id'] = $get_data;			
                $data1 = array_merge($data,$get_data);			
                $data =  array(
                        'type'        	        => $product_obj->type,
                        'post_title'        	=> $product_obj->post_title,
                        'post_title_es'        	=> $product_obj->post_title_es,
                        'post_content'      	=> $product_obj->post_content,				
                        'post_content_es'      	=> $product_obj->post_content_es,				
                        'post_slug'         	=> $product_obj->post_slug,
                        'tax_id'                => $product_obj->tax_id,
                        'shipping_id'           => $product_obj->shipping_id,
                        'brand_id'              => $product_obj->brand_id,
                        'supplier_id'           => $product_obj->supplier_id,
                        //'featured_image'  	=> $product_obj->featured_image,
                        'regular_price'    		=> $product_obj->regular_price,
                        'sale_price'     		=> $product_obj->sale_price,
                        'sale_price_dates_from' => $product_obj->sale_price_dates_from,
                        'sale_price_dates_to'   => $product_obj->sale_price_dates_to,
                        'sku'       			=> $product_obj->sku,
                        'stock'      			=> $product_obj->stock,
                        'weight'            	=> $product_obj->weight,
                        'length'            	=> $product_obj->length,
                        'width'             	=> $product_obj->width,
                        'height'            	=> $product_obj->height,
                        'quantity_range'  		=> $product_obj->quantity_range,
                        'price_range'     		=> $product_obj->price_range,
                        'enabled'         		=> $product_obj->enabled,						   
                        'featured_product'      => $product_obj->featured_product,						   
                        );

                $data = array_merge($data1,$get_data);
            }			
        }

        if ($this->form_validation->run() == FALSE){

            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($product_id) ? $page_lang->update : $page_lang->add;	
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->product => "/$this->TYPE/product/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($product_id) ? 'update' : 'add';
            $data['product_id'] = isset($product_id) ? $product_id : NULL;

            $tax_obj = $this->Query_model->get_data('ec_tax', array('status' => '1'));
            $shipping_obj = $this->Query_model->get_data('ec_shipping', array('status' => '1'));

            if(isset($data['sale_price_dates_from']) && $data['sale_price_dates_from'] == '0000-00-00 00:00:00'){
                $data['sale_price_dates_from'] = '';
            }
            if(isset($data['sale_price_dates_to']) && $data['sale_price_dates_to'] == '0000-00-00 00:00:00'){
                $data['sale_price_dates_to'] = '';
            }
            $data['tax_obj'] = $tax_obj;
            $data['shipping_obj'] = $shipping_obj;
            $data['categories'] = $categories_obj;
            $data['brand_obj'] = $brand_obj;
            $data['supplier_obj'] = $supplier_obj;

            $data['all_attribute_array'] = $all_attribute_array;
            $data['all_attribute_item_array'] = $all_attribute_item_array;
            $data['selected_attribute_item'] = $selected_attribute_item; 
            $data['selected_variation_obj'] = $selected_variation_obj; 

            $this->load->template("$this->TYPE/product/post_form",$data);
        }else{
//https://stackoverflow.com/questions/44918503/uploading-multiple-images-with-crop-images-in-codeigniter-but-getting-too-much
            if(!empty($_FILES['upload_Files']['name'])){
                $uploadData = array();
                $filesCount = count($_FILES['upload_Files']['name']);
                for($i = 0; $i < $filesCount; $i++){
					
                    $img2_name = preg_replace('/\s*/', '', $_FILES['upload_Files']['name'][$i]);
                    // convert the string to all lowercase
                    $img2 = strtolower($img2_name); 
					
                    $_FILES['upload_File']['name'] 		= $img2;
                    $_FILES['upload_File']['type'] 		= $_FILES['upload_Files']['type'][$i];
                    $_FILES['upload_File']['tmp_name'] 	= $_FILES['upload_Files']['tmp_name'][$i];
                    $_FILES['upload_File']['error'] 	= $_FILES['upload_Files']['error'][$i];
                    $_FILES['upload_File']['size'] 		= $_FILES['upload_Files']['size'][$i];
					
                    $uploadPath = './assets/uploads/files/'; 					
                    $config['upload_path'] = $uploadPath;
                    $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';  
					
                    $this->load->library('upload', $config);				
                    $this->upload->initialize($config);
					
					
                    if($this->upload->do_upload('upload_File')){
												
						$ppimagedata = $this ->upload->data();
						$ppnewimagename = $ppimagedata["file_name"];
						$this ->load ->library("image_lib");
						
						$config['image_library']   = 'gd2';
						$config['source_image']    = $ppimagedata["full_path"];
						$config['create_thumb']    = TRUE;
						$config['maintain_ratio']  = TRUE;
						$config['new_image']       = './assets/uploads/files/';
						$config['width']           = 800;
						$config['height']          = 800;
						
						$this ->image_lib->initialize($config);
						$this ->image_lib->resize();						
						 
						$config['new_image']    = './assets/uploads/files/300x300/';
						$config['width']        = 300;
						$config['height']       = 300;
						$this->image_lib->initialize($config);
						$this->image_lib->resize();        
								 
						#echo "<pre>"; print_r($config);echo "</pre>";	
												
                        #$fileData = $this->upload->data();
                        $img_name = preg_replace('/\s*/', '', $ppnewimagename);
                        $img = strtolower($img_name);
                        $uploadData[$i]['file_name'] = $img;
                        $uploadData[$i]['created'] = date("Y-m-d H:i:s");
                        $uploadData[$i]['modified'] = date("Y-m-d H:i:s");
						
                    }else{
						$ppnewimagename = "";
						$ppthumb = "";
                        $display_errors = $this->upload->display_errors();
                    }
                } 
				#echo "<pre>"; print_r( $uploadData); echo "</pre>";die;
            }
            $db_data =  array(
                    'type'       		    => $data['type'],
                    'post_title'       		=> $data['post_title'],
                    'post_title_es'       	=> $data['post_title_es'],
                    'post_content'     		=> $data['post_content'],
                    'post_content_es'     	=> $data['post_content_es'],
                    'tax_id'                => $data['tax_id'],
                    'shipping_id'           => $data['shipping_id'],
                    'brand_id'              => $data['brand_id'],
                    'supplier_id'           => $data['supplier_id'],
                    'post_slug'        		=> $data['post_slug'],
                    'regular_price'    		=> $data['regular_price'],
                    'sale_price'       		=> $data['sale_price'],
                    'sale_price_dates_from' => $data['sale_price_dates_from'],
                    'sale_price_dates_to'   => $data['sale_price_dates_to'],
                    'sku'     		   		=> $data['sku'],
                    'stock'     	   		=> $data['stock'],
                    'weight'           		=> $data['weight'],
                    'length'           		=> $data['length'],
                    'width'            		=> $data['width'],
                    'height'           		=> $data['height'],					
                    'quantity_range'   		=> $qtyrange,
                    'price_range'      		=> $pricerange,					
                    'post_type'        		=> 'product',
                    'enabled'          		=> $data['enabled'],
                    'featured_product'      => $data['featured_product'],
                    'user_id'          		=> $current_id
                        );	

            if(isset($product_id)){
				//echo "<pre>"; print_r($db_data); echo "</pre>"; die;
                $this->Query_model->update_data('ec_product',$db_data,array('product_id' => $product_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');				
                $post_id = $product_id;
                if(!empty($_POST['category'])){
                    $this->db->where('post_id',$post_id);
                    $this->db->where_not_in('category_id',$_POST['category']);
                    $this->db->delete('ec_product_categories');
                    foreach($_POST['category'] as $key => $cat_id){
                        if($this->db->where(array('post_id' => $post_id, 'category_id' => $cat_id))->get('ec_product_categories',1)->num_rows() < 1){
                            $post_category = array(
                                    'post_id' => $post_id,
                                    'category_id' => $cat_id
                                    );
                            $this->db->insert('ec_product_categories',$post_category);
                        }
                    }
                }

                $post_id = $product_id;
                if(!empty($_POST['attribute_item'])){
                    $this->db->where('product_id',$post_id);
                    $this->db->where_not_in('attribute_item_id',$_POST['attribute_item']);
                    $this->db->delete('ec_product_attribute');

                    foreach($_POST['attribute_item'] as $key => $cat_id){
                        if($this->db->where(array('product_id' => $post_id, 'attribute_item_id' => $cat_id))->get('ec_product_attribute',1)->num_rows() < 1){
                            $post_attribue_iterm = array(
                                    'product_id' => $post_id,
                                    'attribute_item_id' => $cat_id
                                    );
                            $this->db->insert('ec_product_attribute',$post_attribue_iterm);
                        }
                    }
                }else{
                    $this->db->where('product_id',$post_id);
                    $this->db->delete('ec_product_attribute');
                }
                $post_id = $product_id;				
                //variation section							
                if(!empty($_FILES['_thumbnail_id']) && $_FILES['_thumbnail_id']!='' ){
                    $this->upload_color_image(array('product_id' =>$post_id ));				
                }elseif(!empty($_POST['_regular_price'])){
                    $this->db->where('product_id',$post_id);
                    $this->db->where_not_in('attribute_item_id',$_POST['attribute_item']);
                    $this->db->delete('ec_product_variation');

                    foreach($_POST['_regular_price'] as $index => $value){					
                        $attribute_item_id 	= $_POST['attr_itemid'][$index];
                        $sale_price 		= $_POST['_sale_price'][$index];
                        $regular_price 		= $_POST['_regular_price'][$index];
                        //$sku 				= $_POST['_sku'][$index];
                        $stock 				= $_POST['_stock'][$index];
                        $arr = explode(',',$attribute_item_id);
                        sort($arr);
                        $attribute_item_id = implode(',',$arr);
                        $attribute_iname_array = array();
                        $attribute_item_name = '';
                        if($arr){
                            foreach($arr as $i => $j){
                                $attribute_iname_array[] = $all_attribute_item_array[$j]; 
                            }
                            $attribute_item_name = implode(',',$attribute_iname_array);
                        }
                        $post_variation = array(
							'product_id' => $post_id,
							'attribute_item_id' => $attribute_item_id,
							'attribute_item_name'   => $attribute_item_name,
							'_sale_price' => $sale_price,
							'_regular_price' => $regular_price,
							//'_sku' => $sku,
							'_stock' => $sale_price,
							);				
                        $this->db->insert('ec_product_variation',$post_variation);						
                    }
                }					
                $uploadData2 = array();						
                for($j = 0; $j < sizeof($uploadData); $j++){	
					$ppthumb = explode(".", $uploadData[$j]['file_name']);
					$ppthumb = $ppthumb[0] . "_thumb". "." . $ppthumb[1];
					
                    $uploadData2[$j]['file_name'] = $ppthumb;
                    $uploadData2[$j]['created'] =$uploadData[$j]['created'];
                    $uploadData2[$j]['modified'] =$uploadData[$j]['modified'];
                    $uploadData2[$j]['product_id'] = $product_id;
                }				
                if(!empty($uploadData2)){
                    //Insert file information into the database
                    $insert = $this->files->insert($uploadData2);
                    $statusMsg = $insert?'Files uploaded successfully.':'Some problem occurred, please try again.';
                    $this->session->set_flashdata('statusMsg',$statusMsg);
                }
            }else{		
					
                $db_data['product_uid'] = uniq_uid();
                $last_inserted_id  = $this->Query_model->insert_data('ec_product',$db_data);
                $this->session->set_flashdata('success', 'Inserted Successfully.');

                $data = $_POST;
                unset($data['category']);
                unset($data['tag']);

                $_POST['category'] = $this->input->post('category');                  
                if(!empty($_POST['category'])){
                    foreach($_POST['category'] as $key => $cat_id){
                        $post_category = array(
                                'post_id' => $last_inserted_id,
                                'category_id' => $cat_id
                                );
                        $this->db->insert('ec_product_categories',$post_category);
                    }
                }

                if(!empty($_POST['attribute_item'])){
                    foreach($_POST['attribute_item'] as $key => $attribute_item){                                        
                        $attribute_item = array(
                                'product_id' => $last_inserted_id,
                                'attribute_item_id' => $attribute_item
                                );
                        $this->db->insert('ec_product_attribute',$attribute_item);

                    }
                }
				
                $uploadData1 = array();						
                for($j = 0; $j < sizeof($uploadData); $j++){	

					$ppthumb = explode(".", $uploadData[$j]['file_name']);
					$ppthumb = $ppthumb[0] . "_thumb". "." . $ppthumb[1];										
                    $uploadData1[$j]['file_name'] = $ppthumb;
                    $uploadData1[$j]['created'] =$uploadData[$j]['created'];
                    $uploadData1[$j]['modified'] =$uploadData[$j]['modified'];
                    $uploadData1[$j]['product_id'] = $last_inserted_id;
                }
                if(!empty($uploadData1)){
					//echo "xxx<pre>"; print_r($uploadData1); echo "</pre>"; die;
                    //Insert file information into the database
                    $insert = $this->files->insert($uploadData1);
                    $statusMsg = $insert?'Files uploaded successfully.':'Some problem occurred, please try again.';
                    $this->session->set_flashdata('statusMsg',$statusMsg);
                }				
            }
            redirect("$this->TYPE/product");
        }
    }

    function unique_sku($value, $product_id)
    {
        $sku = $this->input->post('sku');
        $args = array('sku' => $sku);
        if($product_id){
            $args['product_id!='] = $product_id;
        }
        $admin_obj = $this->Query_model->get_data_obj('ec_product',$args);
        $status   = TRUE;
        $message  = '';
        if($admin_obj) {
            $status   = FALSE;
            $message  = 'SKU must be unique <br/>';
            $this->form_validation->set_message('unique_sku', $message);
        }

        return $status;
    }

    function unique_slug($value, $product_id)
    {
        $post_title = $this->input->post('post_title');
        $args = array('post_title' => $post_title);
        if($product_id){
            $args['product_id!='] = $product_id;
        }
        $admin_obj = $this->Query_model->get_data_obj('ec_product',$args);
        $status   = TRUE;
        $message  = '';
        if($admin_obj) {
            $status   = FALSE;
            $message  = 'Product Title be unique <br/>';
            $this->form_validation->set_message('unique_slug', $message);
        }

        return $status;
    }

    	
    public function ajax_update_product_status()
    {
        $product_id = $this->input->post('product_id');
        $status = $this->input->post('status');
        $enabled = '1';
        if($status == 1){
            $enabled = '0';
        }
        $data = array();
        if($this->LOGIN_ID){
            $delete = $this->Query_model->update_data('ec_product',array('enabled' => $enabled), array('product_id' => $product_id));
            echo json_encode(array('success' => $delete));
        }else{
            echo json_encode(array('success' => 0));
        }
    }	

    public function delimg()
    {
        $data['csrf'] = csrf_token();
        $id = $this->input->post('id');
        $product_id = $this->input->post('product_id');
        $data['csrf'] = csrf_token();
        $data = array();
        $delete = 0;
        if($product_id && $id){
            $delete = $this->Product_model->delete_image($id,$product_id);
        }
        echo json_encode(array('success' => $delete));
    }

    public function delete_variation()
    {
        $data['csrf'] = csrf_token();
        $variation_id = $this->input->post('variation_id');
        $product_id = $this->input->post('product_id');
        $data['csrf'] = csrf_token();
        $data = array();
        $delete = $this->Product_model->delete_variation_post($variation_id, $product_id);
        echo json_encode(array('success' => $delete));
    }

    public function get_attr(){
        $data = array();
        $data['csrf'] = csrf_token();
        $id = $this->input->post('id');
        $data['csrf'] = csrf_token();
        $data = array();
        $attribute_item = array();
        $attribute_item_obj = $this->Query_model->get_data('ec_attribute_item',array('attribute_id' => $id));
        if($attribute_item_obj){
            foreach($attribute_item_obj as $row){
                $attribute_item[$row->attribute_item_id] = $row->name;
            }
        }

        $response = array(
                'status' => 'success',
                'message' => 'success',
                'data' => $attribute_item
                );
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function get_vitem_ajax(){ 
        $data = array();
        $data['csrf'] = csrf_token();
        $id = $this->input->post('id');
        $data['csrf'] = csrf_token();
        $data = array();

        $attribute_item = array();
        $attribute_item_obj = $this->Query_model->get_data('ec_attribute_item',array('attribute_id' => $id));
        if($attribute_item_obj){
            foreach($attribute_item_obj as $row){
                $attribute_item[$row->attribute_item_id] = $row->name;
            }
        }

        $response = array(
                'status' => 'success',
                'message' => 'success',
                'data' => $attribute_item
                );
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function upload_color_image($args)
    { 
        $uploadData = array(); $error = ''; $errorUploadType = '';
        if(!empty($_FILES['_thumbnail_id']['name']))
        {
            $filesCount = count((array)$_FILES['_thumbnail_id']['name']);
            $fileNum = 0;
            for($i = 0; $i < $filesCount; $i++)
            {
                $fileNum++;
                $_FILES['file']['name']     = $_FILES['_thumbnail_id']['name'][$i];
                $_FILES['file']['type']     = $_FILES['_thumbnail_id']['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES['_thumbnail_id']['tmp_name'][$i];
                $_FILES['file']['error']    = $_FILES['_thumbnail_id']['error'][$i];
                $_FILES['file']['size']     = $_FILES['_thumbnail_id']['size'][$i];

                $img = preg_replace('/\s*/', '', $_FILES['file']['name']);
                $full_img = strtolower($img);
                if($_FILES['file']['name'])
                {
                    $num = 1;
					
					$config = array(				
						'upload_path' => "./assets/uploads/",  //"./assets/images"	
						'allowed_types' => "gif|jpg|png|jpeg|pdf|webp",
						'max_size' => "20240000",
					);
				                   
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
					
                    if($this->upload->do_upload('file'))
                    {
                        $fileData = $this->upload->data();
                        $uploadData[$i]['file_name'] = $fileData['file_name'];
                        $uploadData[$i]['uploaded_on'] = date("Y-m-d H:i:s");						
						$uploadData[$i]['image_library'] = 'gd2';
						$uploadData[$i]['source_image'] = "./assets/uploads/".$fileData['file_name'];
						$uploadData[$i]['maintain_ratio'] = TRUE;						
						$uploadData[$i]['width'] = 800; 
						$uploadData[$i]['height'] = 800;						
						$uploadData[$i]['new_image'] = "./assets/uploads/".'thumb_'.$fileData['file_name'];
											
						$this->load->library('image_lib',$uploadData[$i]); 
						$this->upload->initialize($uploadData[$i]);  
						$this->image_lib->resize();
						$this->image_lib->clear();					
					
                    }
                    else
                    {
                        if($this->upload->display_errors())
                        {
                            $error .= $this->upload->display_errors() . 'Picture'.$fileNum;
                        }
                        $errorUploadType .= $_FILES['file']['name'].' | ';
                    }
                }else{					
                    foreach($_POST['_regular_price'] as $index => $value){
                        $post_variation = array(
                                'product_id' => $args['product_id'],
                                'attribute_item_id' => $_POST['attr_itemid'][$index],
                                '_sale_price' => $_POST['_sale_price'][$index],
                                '_regular_price' => $_POST['_regular_price'][$index],
                                //'_sku' => $_POST['_sku'][$index],
                                '_stock' => $_POST['_stock'][$index],									
                                );
                        $this->db->where('product_id',$args['product_id']);
                        $this->db->where('attribute_item_id',$_POST['attr_itemid'][$index]);
                        $this->db->update('ec_product_variation',$post_variation);
                    }
                }
            }
			
			#echo "<pre>"; print_r($uploadData); echo "<pre>";die;
			
            foreach($uploadData as $index => $value){
                $post_variation = array(
                        'product_id' => $args['product_id'],
                        'attribute_item_id' => $_POST['attr_itemid'][$index],
                        '_sale_price' => $_POST['_sale_price'][$index],
                        '_regular_price' => $_POST['_regular_price'][$index],
                        //'_sku' => $_POST['_sku'][$index],
                        '_stock' => $_POST['_stock'][$index],
                        '_thumbnail_id' => 'thumb_'.$value['file_name'],
                        );
						
				#echo "<pre>"; print_r($post_variation);echo "</pre>";die;		 
                $this->db->where('product_id',$args['product_id']);
                $this->db->where('attribute_item_id',$_POST['attr_itemid'][$index]);
                $this->db->update('ec_product_variation',$post_variation);
            }
            $attribute_item = '';		
            $attribute_item_obj = $this->Query_model->get_data('ec_product_variation',array('product_id'=>$args['product_id'],'attribute_item_id' => $_POST['attr_itemid'][$index]));

            if(empty($attribute_item_obj)){	
                foreach($uploadData as $index => $value){
                    $post_variation = array(
                            'product_id' => $args['product_id'],
                            'attribute_item_id' => $_POST['attr_itemid'][$index],
                            '_sale_price' => $_POST['_sale_price'][$index],
                            '_regular_price' => $_POST['_regular_price'][$index],
                            //'_sku' => $_POST['_sku'][$index],
                            '_stock' => $_POST['_stock'][$index],
                            '_thumbnail_id' => 'thumb_'.$value['file_name'],
                            );
                    $this->db->insert('ec_product_variation',$post_variation);
                }
            }		
        }
    }

	public function checkall_status_change(){

	    $id = $this->input->post('id');
		$status = $this->input->post('status');		
		$ids = json_decode($id);
		//echo "<pre>"; print_r($_POST);exit;
		if($status == 'active'){
			$status_val = 0;
		}elseif($status == 'inactive'){
			$status_val = 1;
		}		
        $data = array();
        //$update = $this->User_model->checkall_supplier_status_change($id,$status);
		if($ids && $this->LOGIN_ID && $this->TYPE == 'admin'){
			$update = $this->Query_model->update_data('ec_product',array('enabled' => $status),array('product_id' => $ids));
		}
		
        echo json_encode(array('success' => $update));
		
    }

}
