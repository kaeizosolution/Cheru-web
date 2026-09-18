<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vendor extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');
	    $this->load->model('Vendor_model');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        $this->FNAME = $this->session->userdata($this->TYPE)['fname'];
	}
	
	public function index()
    {
        $crumbs = array("Home" => "/$this->TYPE/dashboard", "Vendor" => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
        $this->load->template("$this->TYPE/vendor/index_vendor",$data);
	}

    public function index_ajax_vendor()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $type_array = array(1 => 'Fixed', 2 => 'Percentage');
        $list = $this->Vendor_model->get_datatable();
        $data = array();
        foreach ($list as $obj) {
            $row = array();
            $row['admin_id']    = $obj->admin_id;
            $row['name']        = $obj->fname.' '.$obj->lname;
            $row['email']       = $obj->email;
            $row['mobile']      = $obj->mobile;
            $row['status']      = $obj->status;

            $data[] = $row;
        }
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Vendor_model->count_all(),
                "recordsFiltered" => $this->Vendor_model->count_filtered(),
                "data" => $data,
                );
    
        echo json_encode($output);
    }

	public function add()
    {
        $this->post();
    }

	public function update($admin_id)
    {
        $this->post($admin_id);
    }

	function post($admin_id = NULL)
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('fname', 'First Name', 'trim|required');
        $this->form_validation->set_rules('lname', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');

        if(isset($admin_id)){
            $vendor_obj = $this->Query_model->get_data_obj('ec_admin',array('admin_id' => $admin_id));
            if(!$vendor_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/vendor");
            }
        }

        if($_POST) {
            $data =  array(
                    'fname'     => $this->input->post('fname'),
                    'lname'     => $this->input->post('lname'),
                    'email'     => $this->input->post('email'),
                    'mobile'    => $this->input->post('mobile'),
                    'status'    => $this->input->post('status'),
                );
        }else{
            if(isset($admin_id)){
                $data =  array(
                    'fname'     => $vendor_obj->fname,
                    'lname'     => $vendor_obj->lname,
                    'email'     => $vendor_obj->email,
                    'mobile'    => $vendor_obj->mobile,
                    'status'    => $vendor_obj->status,
                );
            }
        }

        if ($this->form_validation->run() == FALSE){
            $action_bc = isset($admin_id) ? 'Update' : 'Add';
            $crumbs = array("Home" => "/$this->TYPE/dashboard", "Vendor" => "/$this->TYPE/vendor/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($admin_id) ? 'update' : 'add';
            $data['admin_id'] = isset($admin_id) ? $admin_id : NULL;
            $this->load->template("$this->TYPE/vendor/vendor",$data);
        }else{
            $data['status'] = ($data['status']) ? $data['status'] : '0';
            $db_data =  array(
                'fname'          => $data['fname'],
                'lname'          => $data['lname'],
                'email'          => $data['email'],
                'mobile'         => $data['mobile'],
                'status'         => $data['status'],
            );
            if(isset($admin_id)){
                $this->Query_model->update_data('ec_admin',$db_data,array('admin_id' => $admin_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');
            }else{
                $this->Query_model->insert_data('ec_admin',$db_data);
                $this->session->set_flashdata('success', 'Inserted Successfully.');
            }
            redirect("$this->TYPE/vendor");
        }
	}
}
