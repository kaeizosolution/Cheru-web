<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reviews extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    
        $this->check_module_permission('reviews');
    }

    public function index()
    {
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", "Product Reviews" => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;

        // Fetch all reviews, join products and customer tables
        $data['reviews'] = $this->db
            ->select('r.review_rating_id, r.title, r.rating, r.review, r.status, r.date_created, p.name as product_name, p.id as product_id')
            ->select("CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,'')) AS customer_name", false)
            ->from('ec_review_rating r')
            ->join('products p', 'p.id = r.product_id', 'left')
            ->join('ec_customer c', 'c.customer_id = r.customer_id', 'left')
            ->order_by('r.date_created', 'DESC')
            ->get()->result();

        $this->load->template("$this->TYPE/reviews/index", $data);
    }

    public function approve($id = 0)
    {
        $id = (int)$id;
        if ($id) {
            $this->db->where('review_rating_id', $id)->update('ec_review_rating', ['status' => '1']);
            $this->session->set_flashdata('success', 'Review approved successfully.');
        } else {
            $this->session->set_flashdata('error', 'Invalid Review ID.');
        }
        redirect("$this->TYPE/reviews");
    }

    public function reject($id = 0)
    {
        $id = (int)$id;
        if ($id) {
            $this->db->where('review_rating_id', $id)->update('ec_review_rating', ['status' => '0']);
            $this->session->set_flashdata('success', 'Review rejected (set to pending).');
        } else {
            $this->session->set_flashdata('error', 'Invalid Review ID.');
        }
        redirect("$this->TYPE/reviews");
    }


    public function bulk_approve()
    {
        $ids = $this->input->post('review_ids');
        if (is_array($ids) && !empty($ids)) {
            $sanitized_ids = array_map('intval', $ids);
            $this->db->where_in('review_rating_id', $sanitized_ids)->update('ec_review_rating', ['status' => '1']);
            $this->session->set_flashdata('success', count($sanitized_ids) . ' reviews approved successfully.');
        } else {
            $this->session->set_flashdata('error', 'No reviews selected.');
        }
        redirect("$this->TYPE/reviews");
    }

    public function bulk_reject()
    {
        $ids = $this->input->post('review_ids');
        if (is_array($ids) && !empty($ids)) {
            $sanitized_ids = array_map('intval', $ids);
            $this->db->where_in('review_rating_id', $sanitized_ids)->update('ec_review_rating', ['status' => '0']);
            $this->session->set_flashdata('success', count($sanitized_ids) . ' reviews rejected successfully.');
        } else {
            $this->session->set_flashdata('error', 'No reviews selected.');
        }
        redirect("$this->TYPE/reviews");
    }


}
