<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Returns extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Return_model');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? (int)$this->session->userdata($this->TYPE)['login_id'] : 0;
    
        $this->check_module_permission('returns');
    }

    public function index()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");

        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", 'Returns' => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);

        $data = array();
        $data['breadcrumbs'] = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
		$data['vendors'] = $this->db
			->select('vendor_id, store_name, name')
			->from('ec_vendor')
			->order_by('store_name', 'ASC')
			->get()->result();

        $this->load->template("$this->TYPE/returns/index_returns", $data);
    }

    public function index_ajax_returns()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $filters = array();
        $filter = $this->input->post('filter');
        if (is_array($filter)) {
            $filters = $filter;
        }

        $rows = $this->Return_model->get_admin_returns($_POST, $filters);

        $data = array();
        foreach ($rows as $obj) {
            $row = array();

            $row['return_id'] = isset($obj->return_id) ? $obj->return_id : (isset($obj->id) ? $obj->id : 0);
            $row['customer'] = trim(((string)($obj->customer_fname ?? '') . ' ' . (string)($obj->customer_lname ?? '')));
            if ($row['customer'] === '') {
                $row['customer'] = isset($obj->customer_email) ? (string)$obj->customer_email : 'N/A';
            }

            $vendorLabel = (string)($obj->vendor_store_name ?? '');
            if ($vendorLabel === '') {
                $vendorLabel = (string)($obj->vendor_name ?? '');
            }
            $row['vendor'] = $vendorLabel !== '' ? $vendorLabel : 'N/A';

            $row['product'] = isset($obj->product_name) ? (string)$obj->product_name : 'N/A';

			$st = isset($obj->status) ? strtolower((string)$obj->status) : 'requested';
			// Map legacy statuses to the 5 canonical ones for display
			$st_display = $st;
			if ($st_display === 'approved') $st_display = 'accepted';
			if ($st_display === 'inspection_pending') $st_display = 'initiated';
			if ($st_display === 'pickup_scheduled' || $st_display === 'picked_up') $st_display = 'completed';
			if ($st_display === 'approved_refund' || $st_display === 'refund_completed') $st_display = 'refunded';

			$badgeClass = 'badge badge-secondary';
			if ($st_display === 'requested' || $st_display === 'pending') $badgeClass = 'badge badge-warning';
			if ($st_display === 'initiated') $badgeClass = 'badge badge-secondary';
			if ($st_display === 'accepted') $badgeClass = 'badge badge-success';
			if ($st_display === 'completed') $badgeClass = 'badge badge-primary';
			if ($st_display === 'refunded') $badgeClass = 'badge badge-info';
			if ($st_display === 'rejected' || $st_display === 'return_cancelled') $badgeClass = 'badge badge-danger';
			$row['status'] = '<span class="'.$badgeClass.'">'.ucfirst($st_display).'</span>';

            $dt = '';
            if (isset($obj->created_at) && $obj->created_at) {
                $dt = (string)$obj->created_at;
            } elseif (isset($obj->date_added) && $obj->date_added) {
                $dt = (string)$obj->date_added;
            }
            $row['date'] = $dt !== '' ? date('Y-m-d', strtotime($dt)) : '-';

            $rid = (int)$row['return_id'];
            $row['action'] = '<a href="/'. $this->TYPE .'/returns/details/'.$rid.'" class="btn btn-sm btn-primary">View</a>';

            $data[] = $row;
        }

        $output = array(
            'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            'recordsTotal' => $this->Return_model->count_admin_returns_all(),
            'recordsFiltered' => $this->Return_model->count_admin_returns_filtered($_POST, $filters),
            'data' => $data,
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    public function details($return_id)
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");

        $return_id = (int)$return_id;
        $return_data = $this->Return_model->get_admin_return_details($return_id);
        if(!$return_data){
            $this->session->set_flashdata('error', 'Return not found.');
            redirect("$this->TYPE/returns");
        }

        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", 'Returns' => "/$this->TYPE/returns", 'Return Details' => '');
        $breadcrumbs = $this->breadcrumbs->show($crumbs);

        $data = array();
        $data['breadcrumbs'] = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['return_data'] = $return_data;

        $this->load->template("$this->TYPE/returns/details", $data);
    }

    public function action()
    {
        if(!logged_in()) redirect("$this->TYPE/auth/login");

        $return_id = (int)$this->input->post('return_id');
        $action = (string)$this->input->post('action');
        $rejection_reason = (string)$this->input->post('rejection_reason');

        $is_ajax = $this->input->is_ajax_request();

        if ($return_id <= 0) {
            if ($is_ajax) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 0, 'msg' => 'Invalid return']));
            }
            $this->session->set_flashdata('error', 'Invalid return.');
            redirect("$this->TYPE/returns");
        }

        $status = '';
		// Persist the exact 5 canonical statuses
		if ($action === 'initiate') $status = 'initiated';
		if ($action === 'accept') $status = 'accepted';
		if ($action === 'complete') $status = 'completed';
		if ($action === 'refund') $status = 'refunded';
		if ($action === 'reject') $status = 'rejected';
		// backwards compatibility
		if ($action === 'approve') $status = 'accepted';
		if ($action === 'force_refund') $status = 'refunded';

        if ($action === 'reject' && trim($rejection_reason) === '') {
            if ($is_ajax) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 0, 'msg' => 'Rejection reason is required']));
            }
            $this->session->set_flashdata('error', 'Rejection reason is required.');
            redirect("$this->TYPE/returns/details/$return_id");
        }

        if ($status === '') {
            if ($is_ajax) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 0, 'msg' => 'Invalid action']));
            }
            $this->session->set_flashdata('error', 'Invalid action.');
            redirect("$this->TYPE/returns/details/$return_id");
        }

        $update_data = array(
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($action === 'reject') {
			// schema-safe: only set if this column exists in DB
			if ($this->db->field_exists('rejection_reason', 'returns')) {
				$update_data['rejection_reason'] = $rejection_reason;
			}
        }

        $ok = $this->Return_model->admin_update_return($return_id, $update_data);
        if ($is_ajax) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => $ok ? 1 : 0, 'msg' => $ok ? 'Updated' : 'Failed']));
        }

        if ($ok) {
            $this->session->set_flashdata('success', 'Return updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update return.');
        }

        redirect("$this->TYPE/returns/details/$return_id");
    }
}
