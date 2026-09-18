<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

/**
 * Product_enquiries (Vendor API)
 *
 * GET/POST /api/v1/vendor/product_enquiries          → index()
 * POST     /api/v1/vendor/product_enquiries/response  → response()
 */
class Product_enquiries extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');

        if ($this->db->table_exists('ec_product_enquiry') && !$this->db->field_exists('is_contacted', 'ec_product_enquiry')) {
            $this->load->dbforge();
            $fields = array(
                'is_contacted' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
                'contacted_at' => array('type' => 'DATETIME', 'null' => TRUE)
            );
            $this->dbforge->add_column('ec_product_enquiry', $fields);
        }
    }

    // ─── Auth Helpers ────────────────────────────────────────────────────────

    protected function _get_bearer_token()
    {
        $header = $this->input->get_request_header('Authorization', true);
        if (!$header) {
            $header = $this->input->server('HTTP_AUTHORIZATION');
        }
        $header = trim((string)$header);
        if ($header === '') {
            return '';
        }
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return '';
    }

    protected function _require_vendor()
    {
        $token = $this->_get_bearer_token();
        if ($token === '') {
            $this->fail('unauthorized', ['Missing Authorization Bearer token'], 401);
            return 0;
        }

        $claims = $this->verify_token($token);
        if (!$claims || !isset($claims['sub']) || ($claims['type'] ?? '') !== 'access' || ($claims['role'] ?? '') !== 'vendor') {
            $this->fail('unauthorized', ['Invalid or expired vendor access token'], 401);
            return 0;
        }

        $vendor_id = (int)$claims['sub'];
        if ($vendor_id <= 0) {
            $this->fail('unauthorized', ['Invalid token subject'], 401);
            return 0;
        }

        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id']  = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? TRUE;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');

        return $vendor_id;
    }

    // ─── Enquiries List ──────────────────────────────────────────────────────

    public function index()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $length  = isset($payload['length']) ? (int)$payload['length'] : 10;
        $start   = isset($payload['start'])  ? (int)$payload['start']  : 0;
        $draw    = isset($payload['draw'])   ? $payload['draw']         : '';
        $search  = isset($payload['search']) ? $payload['search']       : null;
        $search_val = (is_array($search) && isset($search['value'])) ? trim((string)$search['value']) : '';

        // Query builder
        $this->db->select('e.*, p.name as db_product_name, p.id as db_product_id');
        $this->db->select("CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,'')) AS db_customer_name", false);
        $this->db->from('ec_product_enquiry e');
        $this->db->join('products p', 'p.id = e.product_id', 'left');
        $this->db->join('ec_customer c', 'c.customer_id = e.customer_id', 'left');
        $this->db->where('p.vendor_id', $vendor_id);

        if ($search_val !== '') {
            $this->db->group_start();
            $this->db->like('p.name', $search_val);
            $this->db->or_like('e.product_name', $search_val);
            $this->db->or_like('c.fname', $search_val);
            $this->db->or_like('c.lname', $search_val);
            $this->db->or_like('e.full_name', $search_val);
            $this->db->or_like('e.message', $search_val);
            $this->db->group_end();
        }

        $this->db->order_by('e.created_at', 'DESC');

        // Count filtered
        $temp_db = clone $this->db;
        $recordsFiltered = $temp_db->count_all_results();

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $list = $this->db->get()->result();

        // Count total
        $this->db->from('ec_product_enquiry e');
        $this->db->join('products p', 'p.id = e.product_id', 'left');
        $this->db->where('p.vendor_id', $vendor_id);
        $recordsTotal = $this->db->count_all_results();

        $data = [];
        foreach ($list as $r) {
            $is_contacted = isset($r->is_contacted) ? (int)$r->is_contacted : 0;
            $prod_name    = htmlspecialchars($r->db_product_name ?: $r->product_name);
            $cust_name    = htmlspecialchars($r->full_name ?: ($r->db_customer_name ?: 'Anonymous'));

            $row = [];
            $row['id']            = $r->id;
            $row['created_at']    = date('d-M-Y H:i', strtotime($r->created_at));
            $row['product_id']    = $r->product_id;
            $row['product_name']  = $prod_name;
            $row['customer_name'] = $cust_name;
            $row['message']       = htmlspecialchars($r->message ?? '');
            $row['is_contacted']  = $is_contacted;
            $row['contacted_at']  = (!empty($r->contacted_at)) ? date('d-M-Y H:i', strtotime($r->contacted_at)) : '';

            $data[] = $row;
        }

        $output = [
            'draw'            => $draw,
            'recordsTotal'    => (int)$recordsTotal,
            'recordsFiltered' => (int)$recordsFiltered,
            'data'            => $data,
        ];

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode($output));
    }

    // ─── Response Action ─────────────────────────────────────────────────────

    public function response()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $payload   = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $id        = isset($payload['id']) ? (int)$payload['id'] : 0;
        $status    = isset($payload['status']) ? (int)$payload['status'] : 1;

        if (!$id) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 0, 'message' => 'Invalid Enquiry ID']));
        }

        // Check ownership
        $this->db->select('e.id');
        $this->db->from('ec_product_enquiry e');
        $this->db->join('products p', 'p.id = e.product_id', 'left');
        $this->db->where('e.id', $id);
        $this->db->where('p.vendor_id', $vendor_id);
        $check = $this->db->get()->row();

        if (!$check) {
            return $this->output
                ->set_status_header(403)
                ->set_output(json_encode(['status' => 0, 'message' => 'Access denied']));
        }

        $now = date('Y-m-d H:i:s');
        $update_data = array(
            'is_contacted' => $status,
            'contacted_at' => ($status == 1) ? $now : NULL
        );

        $this->Query_model->update_data('ec_product_enquiry', $update_data, array('id' => $id));

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'       => 1,
                'message'      => ($status == 1) ? 'Response recorded successfully' : 'Marked as Not Contacted',
                'is_contacted' => $status,
                'contacted_at' => ($status == 1) ? date('d-M-Y H:i', strtotime($now)) : ''
            ]));
    }
}
