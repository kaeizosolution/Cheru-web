<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Returns extends API_Controller
{
    private $default_return_window_days = 7;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');
    }

    private function _table_ready()
    {
        if (!isset($this->db) || !$this->db->table_exists('returns')) {
            $this->fail('server_error', ['Returns table is missing. Please create `returns` table first.'], 500);
            return false;
        }
        return true;
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

    private function _require_customer()
    {
        $token = $this->_get_bearer_token();
        if ($token === '') {
            $customer = $this->session->userdata('customer');
            $customer_id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
            if ($customer_id > 0) {
                return $customer_id;
            }
            $this->fail('unauthorized', ['Missing Authorization Bearer token'], 401);
            return 0;
        }

        $claims = $this->verify_token($token);
        if (!$claims || !isset($claims['sub']) || ($claims['type'] ?? '') !== 'access' || ($claims['role'] ?? '') !== 'customer') {
            $this->fail('unauthorized', ['Invalid or expired customer access token'], 401);
            return 0;
        }

        $customer_id = (int)$claims['sub'];
        if ($customer_id <= 0) {
            $this->fail('unauthorized', ['Invalid token subject'], 401);
            return 0;
        }

        // Merge session
        $cust = $this->session->userdata('customer');
        $cust = is_array($cust) ? $cust : [];
        $cust['login_id'] = $customer_id;
        $cust['customer_id'] = $cust['customer_id'] ?? $customer_id;
        $cust['logged_in'] = $cust['logged_in'] ?? 1;
        $this->session->set_userdata('customer', $cust);
        $this->session->set_userdata('type', 'customer');
        $_SESSION['type'] = 'customer';

        return $customer_id;
    }

    private function _require_vendor()
    {
        $token = $this->_get_bearer_token();
        if ($token === '') {
            $vendor = $this->session->userdata('vendor');
            $vendor_id = (int)($vendor['login_id'] ?? ($vendor['vendor_id'] ?? 0));
            if ($vendor_id > 0) {
                return $vendor_id;
            }
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

        // Merge session
        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id'] = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? 1;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');
        $_SESSION['type'] = 'vendor';

        return $vendor_id;
    }

    private function _return_window_days()
    {
        $days = (int)$this->config->item('RETURN_WINDOW_DAYS');
        if ($days <= 0) {
            $days = (int)$this->default_return_window_days;
        }
        return $days;
    }

    private function _parse_datetime($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        if (!$ts) {
            return null;
        }
        return $ts;
    }

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }
        if (!$this->_table_ready()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $rows = $this->db
            ->select('r.*')
            ->select('oi.product_id, oi.product_name, oi.price, oi.qty, oi.subtotal')
            ->from('returns r')
            ->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left')
            ->where('r.customer_id', $customer_id)
            ->order_by('r.id', 'DESC')
            ->get()->result();

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'return_id' => (int)($r->id ?? 0),
                'order_id' => (int)($r->order_id ?? 0),
                'order_item_id' => (int)($r->order_item_id ?? 0),
                'reason' => $r->reason ?? null,
                'description' => $r->description ?? null,
                'image_proof' => !empty($r->image_proof) ? ((preg_match('#^https?://#i', $r->image_proof)) ? $r->image_proof : base_url(ltrim($r->image_proof, '/'))) : null,
                'status' => $r->status ?? null,
                'refund_status' => $r->refund_status ?? null,
                'refund_mode' => $r->refund_mode ?? null,
                'created_at' => $r->created_at ?? null,
                'product' => [
                    'product_id' => isset($r->product_id) ? (int)$r->product_id : null,
                    'name' => $r->product_name ?? null,
                    'price' => isset($r->price) ? (float)$r->price : null,
                    'quantity' => isset($r->qty) ? (int)$r->qty : null,
                    'subtotal' => isset($r->subtotal) ? (float)$r->subtotal : null,
                ],
            ];
        }

        return $this->ok(['returns' => $data], 'success');
    }

    public function request()
    {
        if (!$this->require_post()) {
            return;
        }
        if (!$this->_table_ready()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $order_id = (int)($payload['order_id'] ?? 0);
        $order_item_id = (int)($payload['order_item_id'] ?? 0);
        $reason = isset($payload['reason']) ? trim((string)$payload['reason']) : '';
        $description = isset($payload['description']) ? trim((string)$payload['description']) : '';
        $images = $payload['images'] ?? null;

        $errors = [];
        if ($order_id <= 0) $errors[] = 'order_id is required';
        if ($order_item_id <= 0) $errors[] = 'order_item_id is required';
        if ($reason === '') $errors[] = 'reason is required';
        if ($errors) {
            return $this->fail('validation_error', $errors, 422);
        }

        $order = $this->db->from('ec_orders')->where('id', $order_id)->get()->row();
        if (!$order) {
            return $this->fail('not_found', ['Order not found'], 404);
        }
        if ((int)($order->user_id ?? 0) !== $customer_id) {
            return $this->fail('unauthorized', ['You are not allowed to request return for this order'], 403);
        }

        $item = $this->db
            ->from('ec_order_items')
            ->where('id', $order_item_id)
            ->where('order_id', $order_id)
            ->get()->row();

        if (!$item) {
            return $this->fail('not_found', ['Order item not found'], 404);
        }

        // Check item is delivered — DB may store 'delivered' string or numeric status 10
        $item_status_raw = trim((string)($item->status ?? ''));
        $item_status_int = (int)$item_status_raw;
        $item_is_delivered = ($item_status_int === 10)
            || (strtolower($item_status_raw) === 'delivered')
            || (strtolower($item_status_raw) === '')  // empty status — check order-level status
            && in_array(strtolower((string)($order->status ?? '')), ['delivered', '10', '4']);

        if (!$item_is_delivered) {
            return $this->fail('not_eligible', ['Item is not delivered yet'], 409);
        }

        $days = $this->_return_window_days();
        // Use item's last_updated (delivery date) if available, fallback to order date
        $base_raw = !empty($item->last_updated) ? $item->last_updated : ($order->date_added ?? '');
        $base_ts = $this->_parse_datetime($base_raw) ?: time();
        $deadline = $base_ts + ($days * 86400);
        if (time() > $deadline) {
            return $this->fail('not_eligible', ['Return window of ' . $days . ' days has expired'], 409);
        }

        $existing = $this->db
            ->from('returns')
            ->where('order_item_id', $order_item_id)
            ->get()->row();
        if ($existing) {
            return $this->fail('already_requested', ['Return already requested for this item'], 409);
        }

        $vendor_id = (int)($item->login_id ?? 0);
        if ($vendor_id <= 0) {
            return $this->fail('server_error', ['Invalid vendor for this item'], 500);
        }

        // Handle direct multipart file upload if present
        $image_path = null;
        if (!empty($_FILES['image_proof']['name'])) {
            $config['upload_path']   = './uploads/returns/';
            $config['allowed_types'] = 'jpg|jpeg|png|webp';
            $config['max_size']      = 4096;
            $config['encrypt_name']  = true;

            if (!is_dir($config['upload_path'])) {
                @mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload');
            $this->upload->initialize($config);
            if ($this->upload->do_upload('image_proof')) {
                $fileData = $this->upload->data();
                $image_path = 'uploads/returns/' . $fileData['file_name'];
            } else {
                return $this->fail('upload_error', [strip_tags($this->upload->display_errors('', ''))], 422);
            }
        }

        // Fallback: parse images parameter if passed and no file was uploaded
        if ($image_path === null && $images !== null) {
            $parsed_images = null;
            if (is_array($images)) {
                $parsed_images = $images;
            } elseif (is_string($images)) {
                $decoded = json_decode($images, true);
                if (is_array($decoded)) {
                    $parsed_images = $decoded;
                } else {
                    $parsed_images = [$images];
                }
            }
            if (is_array($parsed_images) && !empty($parsed_images)) {
                $first_img = trim((string)reset($parsed_images));
                if ($first_img !== '') {
                    $base = base_url();
                    if (strpos($first_img, $base) === 0) {
                        $image_path = substr($first_img, strlen($base));
                    } else {
                        $image_path = $first_img;
                    }
                }
            }
        }

        $insert = [
            'order_id'         => $order_id,
            'order_item_id'    => $order_item_id,
            'product_id'       => (int)($item->product_id ?? 0),
            'customer_id'      => $customer_id,
            'vendor_id'        => $vendor_id,
            'reason'           => $reason,
            'description'      => $description,
            'status'           => 'requested',
            'refund_status'    => 'pending',
            'image_proof'      => $image_path,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        // Remove any keys whose columns don't exist in the returns table (graceful schema compatibility)
        $existing_cols = array_flip($this->db->list_fields('returns'));
        $insert = array_intersect_key($insert, $existing_cols);

        $new_id = $this->Query_model->insert_data('returns', $insert);
        if (!$new_id) {
            return $this->fail('server_error', ['Failed to create return request'], 500);
        }

        return $this->ok([
            'return_id' => (int)$new_id,
            'status'    => 'requested',
        ], 'success');
    }

    public function approve()
    {
        if (!$this->require_post()) {
            return;
        }
        if (!$this->_table_ready()) {
            return;
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $return_id = (int)($payload['return_id'] ?? 0);
        if ($return_id <= 0) {
            return $this->fail('validation_error', ['return_id is required'], 422);
        }

        $ret = $this->db->from('returns')->where('id', $return_id)->get()->row();
        if (!$ret) {
            return $this->fail('not_found', ['Return request not found'], 404);
        }

        if ((int)($ret->vendor_id ?? 0) !== $vendor_id) {
            return $this->fail('unauthorized', ['You are not allowed to approve this return'], 403);
        }

        if (($ret->status ?? '') !== 'requested' && ($ret->status ?? '') !== 'pending') {
            return $this->fail('cannot_update', ['Only requested/pending returns can be approved'], 409);
        }

        $order = $this->db->from('ec_orders')->where('id', (int)$ret->order_id)->get()->row();
        if (!$order) {
            return $this->fail('server_error', ['Order not found for return'], 500);
        }

        $payment_method = (string)($order->payment_method ?? ($order->payment_mode ?? ''));
        $refund_mode = (strtoupper($payment_method) === 'COD' || strtolower($payment_method) === 'cod') ? 'manual' : 'pending';

        $update = [
            'status'        => 'approved',
            'refund_status' => 'initiated',
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        // Only set refund_mode if the column exists (schema compatibility)
        if ($this->db->field_exists('refund_mode', 'returns')) {
            $update['refund_mode'] = $refund_mode;
        }

        $this->Query_model->update_data('returns', $update, ['id' => $return_id]);

        return $this->ok([
            'return_id'     => $return_id,
            'status'        => 'approved',
            'refund_status' => 'initiated',
            'refund_mode'   => $refund_mode,
        ], 'success');
    }

    public function reject()
    {
        if (!$this->require_post()) {
            return;
        }
        if (!$this->_table_ready()) {
            return;
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $return_id = (int)($payload['return_id'] ?? 0);
        $reason = isset($payload['reason']) ? trim((string)$payload['reason']) : '';

        if ($return_id <= 0 || $reason === '') {
            return $this->fail('validation_error', ['return_id and reason are required'], 422);
        }

        $ret = $this->db->from('returns')->where('id', $return_id)->get()->row();
        if (!$ret) {
            return $this->fail('not_found', ['Return request not found'], 404);
        }

        if ((int)($ret->vendor_id ?? 0) !== $vendor_id) {
            return $this->fail('unauthorized', ['You are not allowed to reject this return'], 403);
        }

        if (($ret->status ?? '') !== 'requested' && ($ret->status ?? '') !== 'pending') {
            return $this->fail('cannot_update', ['Only requested/pending returns can be rejected'], 409);
        }

        $update_data = [
            'status' => 'rejected',
            'vendor_note' => $reason,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        $existing_cols = array_flip($this->db->list_fields('returns'));
        if (isset($existing_cols['rejection_reason'])) {
            $update_data['rejection_reason'] = $reason;
        }

        $this->Query_model->update_data('returns', $update_data, ['id' => $return_id]);

        return $this->ok([
            'return_id' => $return_id,
            'status' => 'rejected',
        ], 'success');
    }

    public function order_availability()
    {
        if (!$this->require_post()) {
            return;
        }
        if (!$this->_table_ready()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $order_ids = $payload['order_ids'] ?? [];
        if (is_string($order_ids)) {
            $order_ids = array_filter(array_map('trim', explode(',', $order_ids)));
        }
        if (!is_array($order_ids)) {
            $order_ids = [];
        }
        $order_ids = array_values(array_unique(array_map('intval', $order_ids)));
        $order_ids = array_values(array_filter($order_ids, function($v){ return $v > 0; }));
        if (empty($order_ids)) {
            return $this->ok(['orders' => (object)[]]);
        }

        // total items per order
        $totalsRows = $this->db
            ->select('order_id, COUNT(*) as total_items', false)
            ->from('ec_order_items')
            ->where_in('order_id', $order_ids)
            ->group_by('order_id')
            ->get()->result_array();
        $totals = [];
        foreach (($totalsRows ?: []) as $r) {
            $oid = (int)($r['order_id'] ?? 0);
            $totals[$oid] = (int)($r['total_items'] ?? 0);
        }

        // returned counts per order
        $returnCountsRows = $this->db
            ->select('order_id, COUNT(DISTINCT CASE WHEN order_item_id > 0 THEN order_item_id END) as returned_items', false)
            ->from('returns')
            ->where('customer_id', $customer_id)
            ->where_in('order_id', $order_ids)
            ->group_by('order_id')
            ->get()->result_array();
        $returnCounts = [];
        foreach (($returnCountsRows ?: []) as $r) {
            $oid = (int)($r['order_id'] ?? 0);
            $returnCounts[$oid] = (int)($r['returned_items'] ?? 0);
        }

        // returned order_item_ids per order
        $returnedItemsRows = $this->db
            ->select('order_id, order_item_id')
            ->from('returns')
            ->where('customer_id', $customer_id)
            ->where_in('order_id', $order_ids)
            ->where('order_item_id >', 0)
            ->get()->result_array();
        $returnedMap = [];
        foreach (($returnedItemsRows ?: []) as $r) {
            $oid = (int)($r['order_id'] ?? 0);
            $oiid = (int)($r['order_item_id'] ?? 0);
            if ($oid <= 0 || $oiid <= 0) continue;
            if (!isset($returnedMap[$oid])) $returnedMap[$oid] = [];
            $returnedMap[$oid][] = $oiid;
        }

        $out = [];
        foreach ($order_ids as $oid) {
            $total = (int)($totals[$oid] ?? 0);
            $ret = (int)($returnCounts[$oid] ?? 0);
            $hasUnreturned = ($total > 0) ? ($ret < $total) : true;
            $out[$oid] = [
                'has_unreturned_item' => $hasUnreturned ? 1 : 0,
                'returned_order_item_ids' => array_values(array_unique($returnedMap[$oid] ?? [])),
            ];
        }

        return $this->ok(['orders' => $out]);
    }
}
