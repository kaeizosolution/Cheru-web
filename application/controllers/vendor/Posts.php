<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Posts extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');
	    //$this->load->model('Vendor_model');
		$this->load->model('Posts_model'); 
        $this->TYPE = $this->session->userdata('type');
		$this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
		$this->load->model('Category');
        $this->load->model('Tags_model');
		
	}
	
	public function index()
    {
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$crumbs = array("Home" => "/$this->TYPE/dashboard", "Posts" => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
		//echo "<pre>"; print_r($data); echo "</pre>";die;
        $this->load->template("$this->TYPE/posts/index_post",$data);
	}
	
	public function get_slug($string){
        $string = trim($string);
        $string = strtolower($string);
        $string = str_replace(' ', '-', $string);
        $slug=preg_replace('/[^A-Za-z0-9-]+/', '', $string);
        return $slug;
    }  
	
	public function index_ajax_post()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $type_array = array(1 => 'Fixed', 2 => 'Percentage');
        $type_status = array( 1 => 'Active', 0 => 'Inactive');

        $list = $this->Posts_model->get_post_datatable();
		//echo "<pre>"; print_r($list); echo "</pre>";
        $data = array();
        foreach ($list as $obj) {
            $row = array();
			$author = $this->Query_model->get_author($obj->user_id);
			$autho_name = !empty($author->fname) ? $author->fname : $author->admin_uid;
		
            if (array_key_exists($obj->enabled, $type_status)) {
                $enabled = $type_status[$obj->enabled];
            }

            $row['post_id']   = $obj->post_id;			
			$row['post_title']   = '<a href="/post/'.$obj->post_slug.'">'.$obj->post_title.'</a>';
            $row['user_id']      = $autho_name;
			$row['comment']      = '-';
			$row['date']     	 = $obj->modified;
			$row['enabled']       = $enabled;			
            $data[] = $row;
        }
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Posts_model->post_count_all(),
                "recordsFiltered" => $this->Posts_model->post_count_filtered(),
                "data" => $data,
                );
			//echo "<pre>"; print_r( $output); echo "</pre>";die;
        echo json_encode($output);
    }
		
	public function add()
    {	
		if(!logged_in()) redirect("$this->TYPE/auth/login");
        $this->post();
    }

	public function update($post_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");
		$this->post($post_id);
    }

	function post($post_id = NULL)
    {
		$data =array();	
		$data = $this->input->post();
		$user = $this->session->userdata();		
		$current_id =$user['admin']['login_id'];
		
		$data['categories'] = $this->Category->find_list();
        $data['tags'] = $this->Tags_model->find_list();
		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('post_title', 'Post Title', 'trim|required');        
		
        if(isset($post_id)){
			
            $post_obj = $this->Query_model->get_data_obj('ec_posts',array('post_id' => $post_id)); 
			
            if(!$post_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/posts");
            }
        }

        if($_POST) {
			
            $data =  array(
				   'post_title'          => $data['post_title'],
				   'post_slug'         	 => $this->get_slug( $data['post_title'] ),
				   'post_content'        => $data['post_content'],
				   //'featured_image' 	=> $this->input->post('featured_image'),
				   'enabled'             => $data['enabled']				   
                );
        }else{
            if(isset($post_id)){
				
				
			$current_category = $this->db->select('category_id')->where(array('post_id' => $post_id ))->get('ec_posts_categories')->result_array();
            $current_tag = $this->db->select('tag_id')->where(array('post_id' => $post_id ))->get('ec_posts_tags')->result_array();
            
			
			
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
			
			//echo "<pre>"; print_r($data); echo "</pre>";die;
				
            /***********end ***********/
            $get_data = (array)$this->Posts_model->edit($post_id);
            $data1['post_id'] = $get_data;
			
            $data1 = array_merge($data,$get_data);
				
			$data =  array(
			   'post_title'         => $post_obj->post_title,
			   'post_slug'          => $this->get_slug($post_obj->post_slug),
			   'post_content'       => $post_obj->post_content,
			   'featured_image'   	=>$post_obj->featured_image,
			   'enabled'         	=> $post_obj->enabled						   
			);	
				
			$data = array_merge($data1,$get_data);	
					
            }
        }
		
        if ($this->form_validation->run() == FALSE){

            $action_bc = isset($post_id) ? 'Update' : 'Add';	
            $crumbs = array("Home" => "/$this->TYPE/dashboard", "Posts" => "/$this->TYPE/posts/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;
            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($post_id) ? 'update' : 'add';
            $data['post_id'] = isset($post_id) ? $post_id : NULL;
            $this->load->template("$this->TYPE/posts/post_form",$data);
        }else{
			
            if($_FILES['featured_image']['name']){
			
			
            $file_name = $_FILES['featured_image']['name'];
			$fileSize = $_FILES["featured_image"]["size"]/1024;
			$fileType = $_FILES["featured_image"]["type"];
			$new_file_name='';
            $new_file_name .= substr($data['post_title'],0,3).time(); 

            $config = array(
                'file_name' => $new_file_name,
                'upload_path' => "./assets/images",
                'allowed_types' => "gif|jpg|png|jpeg|pdf",
                'overwrite' => False,
                'max_size' => "20240000", // Can be set to particular file size , here it is 2 MB(2048 Kb)
                'max_height' => "800",
                'max_width' => "800"
            );
    
            $this->load->library('Upload', $config);
            $this->upload->initialize($config);  
            if (!$this->upload->do_upload('featured_image')) {
               // echo $this->upload->display_errors();
				$this->session->set_flashdata('formdata','Filetype is not allowed or uplaod file size 2MB');
				//redirect('payment/add_payment','refresh');
				 redirect("$this->TYPE/page",'refresh');
				
			}
			
			$path = $this->upload->data();
			$img_url = $path['file_name'];
			
			$db_data =  array(
				'post_title'       => $data['post_title'],
				'post_slug'        => $this->get_slug( $data['post_title'] ),
				'post_content'     => $data['post_content'],
				'featured_image'   => $img_url,
				'post_type'        => 'page',
				'enabled'          => $data['enabled'],
				'user_id'          => $current_id
            );		
		
		}else{ 
			$db_data =  array(
				'post_title'       => $data['post_title'],
				'post_slug'        => $this->get_slug( $data['post_title'] ),
				'post_content'     => $data['post_content'],
				'post_type'        => 'posts',
				'enabled'          => $data['enabled'],
				'user_id'          => $current_id
            );
		}	
		//echo "<pre>"; print_r($db_data); echo "</pre>";die;
            if(isset($post_id)){
               $this->Query_model->update_data('ec_posts',$db_data,array('post_id' => $post_id));
               $this->session->set_flashdata('success', 'Updated Successfully.');
			   
			   
				if(!empty($_POST['category'])){
					
					$this->db->where('post_id',$post_id);
					$this->db->where_not_in('category_id',$_POST['category']);
					$this->db->delete('ec_posts_categories');
					
					foreach($_POST['category'] as $key => $cat_id){
					
						if($this->db->where(array('post_id' => $post_id, 'category_id' => $cat_id))->get('ec_posts_categories',1)->num_rows() < 1){
							$post_category = array(
								'post_id' => $post_id,
								'category_id' => $cat_id
							);
							$this->db->insert('ec_posts_categories',$post_category);
						}
					}
				}

				if(!empty($_POST['tag'])){
					$this->db->where('post_id',$post_id);
					$this->db->where_not_in('tag_id',$_POST['tag']);
					$this->db->delete('ec_posts_tags');

					foreach($_POST['tag'] as $key => $tag){
						$existTag = $this->Tags_model->find_by_id($tag);
						if(!empty($existTag)){
							if($this->db->where(array('post_id' => $post_id, 'tag_id' => $tag))->get('ec_posts_tags',1)->num_rows() < 1){
								$post_tag = array(
									'post_id' => $post_id,
									'tag_id' => $tag
								);
								$this->db->insert('ec_posts_tags',$post_tag);
							}
						}else{

							$newTag = array(
								'name' => $tag,
								'slug' => url_title($tag,'-',true),
								'status' => 1
							);

							$this->db->insert('tags',$newTag);
							$tag_id = $this->db->insert_id();
							$post_tag = array(
								'post_id' => $post_id,
								'tag_id' => $tag_id
							);
							$this->db->insert('ec_posts_tags',$post_tag);
						}
					}
				}
				
            }else{			
				$last_inserted_id  =  $this->Query_model->insert_data('ec_posts',$db_data);
				$this->session->set_flashdata('success', 'Inserted Successfully.');
			   
				$data = $_POST;
				unset($data['category']);
				unset($data['tag']);
		
				$_POST['category'] = $this->input->post('category');                  
				$_POST['tag'] = $this->input->post('tag');         
				
				if(!empty($_POST['category'])){
					foreach($_POST['category'] as $key => $cat_id){
						$post_category = array(
							'post_id' => $last_inserted_id,
							'category_id' => $cat_id
						);
						$this->db->insert('ec_posts_categories',$post_category);
					}
				}
				
				if(!empty($_POST['tag'])){
				foreach($_POST['tag'] as $key => $tag){                                        
					$post_tag = array(
							'post_id' => $last_inserted_id,
							'tag_id' => $tag
						);
					$this->db->insert('ec_posts_tags',$post_tag);
				  
					}
				} 
            }
            redirect("$this->TYPE/posts");
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
			$update = $this->Query_model->update_data('ec_posts',array('enabled' => $status),array('post_id' => $ids));
		}
		
        echo json_encode(array('success' => $update));
		
    }
	public function del()
    {

		$data['csrf'] = csrf_token();
	    $id = $this->input->post('id');
		$data['csrf'] = csrf_token();
        $data = array();
        $delete = $this->Posts_model->delete_post($id);
        echo json_encode(array('success' => $delete));
		
    }

}
