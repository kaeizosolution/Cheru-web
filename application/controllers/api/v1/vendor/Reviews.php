<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Reviews extends API_Controller
{
    private $refresh_ttl = 2592000; // 30 days
    private $access_ttl = 900; // 15 minutes

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');
    }

    private function _get_bearer_token()
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

    private function _require_vendor()
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

        // Merge session so browser session elements are not wiped
        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id'] = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? TRUE;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');

        return $vendor_id;
    }

    private function _token_store_enabled()
    {
        return isset($this->db) && $this->db->table_exists('ec_api_tokens');
    }

    private function _store_refresh_token($vendor_id, $refresh_token)
    {
        if (!$this->_token_store_enabled()) {
            return;
        }

        $hash = hash('sha256', $refresh_token);
        $data = [
            'user_type' => 'vendor',
            'user_id' => (int)$vendor_id,
            'token_hash' => $hash,
            'expires_at' => date('Y-m-d H:i:s', time() + $this->refresh_ttl),
            'revoked' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('ec_api_tokens', $data);
    }

    private function _is_refresh_token_valid($vendor_id, $refresh_token)
    {
        if (!$this->_token_store_enabled()) {
            return true;
        }

        $hash = hash('sha256', $refresh_token);
        $row = $this->db
            ->from('ec_api_tokens')
            ->where('user_type', 'vendor')
            ->where('user_id', (int)$vendor_id)
            ->where('token_hash', $hash)
            ->where('revoked', 0)
            ->get()->row();
        return (bool)$row;
    }

    private function _revoke_refresh_token($vendor_id, $refresh_token)
    {
        if (!$this->_token_store_enabled()) {
            return;
        }

        $hash = hash('sha256', $refresh_token);
        $this->db
            ->where('user_type', 'vendor')
            ->where('user_id', (int)$vendor_id)
            ->where('token_hash', $hash)
            ->update('ec_api_tokens', ['revoked' => 1]);
    }

    // Login, Register, Refresh Token, and Logout endpoints have been moved to VendorAuth.php


    public function index()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $limit = (int)$this->input->post('length') ?: 10;
        $start = (int)$this->input->post('start') ?: 0;
        $search = $this->input->post('search');
        $search_val = (is_array($search) && isset($search['value'])) ? trim((string)$search['value']) : '';

        // Query setup
        $this->db->select('r.review_rating_id, r.title, r.rating, r.review, r.status, r.date_created');
        $this->db->select('p.name as product_name, p.id as product_id');
        $this->db->select("CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,'')) AS customer_name", false);
        $this->db->from('ec_review_rating r');
        $this->db->join('products p', 'p.id = r.product_id', 'inner');
        $this->db->join('ec_customer c', 'c.customer_id = r.customer_id', 'left');
        $this->db->where('p.vendor_id', $vendor_id);

        if ($search_val !== '') {
            $this->db->group_start();
            $this->db->like('p.name', $search_val);
            $this->db->or_like('r.title', $search_val);
            $this->db->or_like('r.review', $search_val);
            $this->db->or_like('c.fname', $search_val);
            $this->db->or_like('c.lname', $search_val);
            $this->db->group_end();
        }

        // Get total filtered count
        $tempdb = clone $this->db;
        $recordsFiltered = $tempdb->count_all_results();

        // Fetch records
        $this->db->order_by('r.date_created', 'DESC');
        $this->db->limit($limit, $start);
        $reviews = $this->db->get()->result();

        // Get total overall count
        $this->db->from('ec_review_rating r');
        $this->db->join('products p', 'p.id = r.product_id', 'inner');
        $this->db->where('p.vendor_id', $vendor_id);
        $recordsTotal = $this->db->count_all_results();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                "draw" => (int)$this->input->post('draw'),
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $reviews,
                "status" => 1,
                "message" => "Success"
            ]));
    }
}

