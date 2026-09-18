<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('Query_model');
		$this->load->model('Category');
		$this->TYPE = $this->session->userdata('type');		
	}

	public function index(){
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$data = array();
		$crumbs = array("Home" => "/$this->TYPE/dashboard", "category" => ""); 
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		$data['page_count'] = page_count();		
		$this->load->template("$this->TYPE/categories/index",$data); 	
                
	}
	
	public function index_ajax_cat()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

		$type_status = array(1 => 'active', 0 => 'inactive');	
        $list = $this->Category->get_datatable();
		
		//echo "<pre>"; print_r($list); echo "</pre>";
		//die;
        
		$data = array();
        foreach ($list as $obj) {
            $row = array();
			if (array_key_exists($obj->status, $type_status)) {
			  $status = $type_status[$obj->status];
			}	
			$row['id']   	= $obj->id;
			$row['name']   	= $obj->name;
            $row['slug']   	= $obj->slug;
			$row['status']  = $status;			
            $data[] = $row;
        }
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Category->count_all(),
                "recordsFiltered" => $this->Category->count_filtered(),
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
		
		
		$this->form_validation->set_rules('name', 'name', 'required|is_unique[categories.name]');
		$this->form_validation->set_rules('status', 'status', 'required');

		if($this->form_validation->run() == true){
			$category = array(
				'name' => $this->input->post('name'),
				'status' => $this->input->post('status')
			);
			$this->Category->create($category);
			$this->session->set_flashdata('message',message_box('Category has been saved','success'));
			redirect('admin/categories/index');
		}
                
		$this->load->template("$this->TYPE/categories/add",$data); 	
		//$this->load->template('admin/categories/add', $data);
	}

	public function update($id = null){
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		$data = array();
		$crumbs = array("Home" => "/$this->TYPE/dashboard", "category" => ""); 
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
		
		$data['admin'] = 1;                
		if($id == null){
			$id = $this->input->post('id');
		}
		$this->form_validation->set_rules('name', 'name', 'required');
		$this->form_validation->set_rules('status', 'status', 'required');

		if($this->form_validation->run() == true){
			$category = array(
				'name' => $this->input->post('name'),
				'status' => $this->input->post('status')
			);
		$this->Category->update($category, $id);
		$this->session->set_flashdata('message',message_box('Category has been saved','success'));
		redirect('admin/categories/index');
		}

		$data['category'] = $this->Category->find_by_id($id);

		//$this->load_admin('categories/edit');
		$this->load->template('admin/categories/edit', $data);
		
	}

	public function delete($id = null){
		if(!logged_in()) redirect("$this->TYPE/auth/login");
		if(!empty($id)){
			$this->Category->delete($id);
			$this->session->set_flashdata('message',message_box('Category has been deleted','success'));
			redirect('admin/categories/index');
		}else{
			$this->session->set_flashdata('message',message_box('Invalid id','danger'));
			redirect('admin/categories/index');
		}
	}
}
