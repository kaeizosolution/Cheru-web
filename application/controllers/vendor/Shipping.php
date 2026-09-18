<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shipping extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');
	    $this->load->model('Shipping_model');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        $this->FNAME = $this->session->userdata($this->TYPE)['fname'];
	}
	
	public function index()
    {
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", $page_lang->shipping => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
        $this->load->template("$this->TYPE/shipping/index_shipping",$data);
	}

    public function index_ajax_shipping()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $type_array = array(1 => 'Fixed', 2 => 'Percentage');
		$type_status = array(1 => 'Active', 0 => 'Inactive');
        $list = $this->Shipping_model->get_datatable();
        $data = array();
        foreach ($list as $obj) {
            $row = array();
			if (array_key_exists($obj->status, $type_status)) {
			  $status = $type_status[$obj->status];
			}	
            $row['shipping_id'] = $obj->shipping_id;
            $row['name']        = $obj->name;
            $row['description'] = $obj->description;
            $row['rate']        = $obj->rate;
            $row['status']      = $status;
			$data[] = $row;
        }
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Shipping_model->count_all(),
                "recordsFiltered" => $this->Shipping_model->count_filtered(),
                "data" => $data,
                );
    
        echo json_encode($output);
    }

	public function add()
    {
        $this->post();
    }

	public function update($shipping_id)
    {
        $this->post($shipping_id);
    }

	function post($shipping_id = NULL)
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('rate', 'Rate', 'trim|required');

        if(isset($shipping_id)){
            $shipping_obj = $this->Query_model->get_data_obj('ec_shipping',array('shipping_id' => $shipping_id));
            if(!$shipping_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/shipping");
            }
        }

        if($_POST) {
            $data =  array(
                    'name'          => $this->input->post('name'),
                    'description'   => $this->input->post('description'),
                    'rate'          => $this->input->post('rate'),
                    'login_id'      => $this->LOGIN_ID,
                );
        }else{
            if(isset($shipping_id)){
                $data =  array(
                    'name'          => $shipping_obj->name,
                    'description'   => $shipping_obj->description,
                    'rate'          => $shipping_obj->rate,
                    'status'        => $shipping_obj->status,
                );
            }
        }

        if ($this->form_validation->run() == FALSE){
            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($shipping_id) ? $page_lang->update : $page_lang->add;
            $crumbs = array( $page_lang->home => "/$this->TYPE/dashboard", $page_lang->shipping => "/$this->TYPE/shipping/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($shipping_id) ? 'update' : 'add';
            $data['shipping_id'] = isset($shipping_id) ? $shipping_id : NULL;
            $this->load->template("$this->TYPE/shipping/shipping",$data);
        }else{
            $data['status'] = ($data['status']) ? $data['status'] : '0';
            $db_data =  array(
                'name'          => $data['name'],
                'description'   => $data['description'],
                'rate'          => $data['rate'],
                'status'        => $data['status'],
                'login_id'      => $this->LOGIN_ID,
            );
            if(isset($shipping_id)){
                $this->Query_model->update_data('ec_shipping',$db_data,array('shipping_id' => $shipping_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');
            }else{
                $this->Query_model->insert_data('ec_shipping',$db_data);
                $this->session->set_flashdata('success', 'Inserted Successfully.');
            }
            redirect("$this->TYPE/shipping");
        }
	}
}
