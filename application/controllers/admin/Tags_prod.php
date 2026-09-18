<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tags_prod extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('Tags_prod_model');
		$this->TYPE = $this->session->userdata('type');
	}

	public function index(){
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$data = array();
		$crumbs = array("Home" => "/$this->TYPE/dashboard", "Tags" => ""); 
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		$data['page_count'] = page_count();		
		$this->load->template("$this->TYPE/tag_prod/index",$data); 
		
		//$this->load->template('admin/tag/index', $data);                
	}
	public function index_ajax_tag()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;
		$type_status = array(1 => 'active', 0 => 'inactive');
        $list = $this->Tags_prod_model->get_datatable();
		$data = array();
        foreach ($list as $obj) {
			if (array_key_exists($obj->status, $type_status)) {
			  $status = $type_status[$obj->status];
			}	
            $row = array();
			$row['id']   	= $obj->id;
			$row['name']   	= $obj->name;
            $row['slug']   	= $obj->slug;
			$row['status']  = $status;			
            $data[] = $row;
        }
		//echo "<pre>"; print_r($data); echo "</pre>";die;
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Tags_prod_model->count_all(),
                "recordsFiltered" => $this->Tags_prod_model->count_filtered(),
                "data" => $data,
                );		
        echo json_encode($output);
    }
	

	public function add(){
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$data = array();
		$data['admin'] = 1;
		$crumbs = array("Home" => "/$this->TYPE/dashboard", "Category" => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		
		$this->form_validation->set_rules('name', 'name', 'required|is_unique[ec_tags_prod.name]');
		$this->form_validation->set_rules('status', 'status', 'required');

		if($this->form_validation->run() == true){
			$tag = array(
				'name' => $this->input->post('name'),
				'status' => $this->input->post('status')
			);
                                              
			$this->Tags_prod_model->create($tag);
                        
			$this->session->set_flashdata('message',message_box('Tag has been saved','success'));
			redirect('admin/tags_prod/index');
		}
                
		//$this->load_admin('tag/add');
		$this->load->template('admin/tag_prod/add', $data);
	}

	public function update($id = null){
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$data = array();
		$data['admin'] = 1;	
		
		$crumbs = array("Home" => "/$this->TYPE/dashboard", "category" => ""); 
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		

			
		if($id == null){
		$id = $this->input->post('id');
		}

		$this->form_validation->set_rules('name', 'name', 'required');
		$this->form_validation->set_rules('status', 'status', 'required');

		if($this->form_validation->run() == true){
			$tag = array(
				'name' => $this->input->post('name'),
				'status' => $this->input->post('status') 
			);
			$this->Tags_prod_model->update($tag, $id);
			$this->session->set_flashdata('message',message_box('Tag has been saved','success'));
			redirect('admin/tags_prod/index');
		}

		$data['tag'] = $this->Tags_prod_model->find_by_id($id);
		//$this->load_admin('tag/edit');
		$this->load->template('admin/tag_prod/edit', $data); 
	}

	public function delete($id = null){
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		if(!empty($id)){
			$this->Tags_prod_model->delete($id);
			$this->session->set_flashdata('message',message_box('Tag has been deleted','success'));
			redirect('admin/tags_prod/index');
		}else{
			$this->session->set_flashdata('message',message_box('Invalid id','danger'));
			redirect('admin/tags_prod/index');
		}
	}
}
