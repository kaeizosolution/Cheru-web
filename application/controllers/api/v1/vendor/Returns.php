<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

/**
 * Returns
 *
 * GET/POST /api/v1/vendor/returns          → index()   — returns list
 * POST     /api/v1/vendor/returns/details  → details() — single return details
 */
class Returns extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');
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

        // Merge session so browser session elements are not wiped
        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id']  = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? TRUE;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');

        return $vendor_id;
    }


    // ─── Returns List ────────────────────────────────────────────────────────

    public function index()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->load->model('Return_model');

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $length  = isset($payload['length']) ? (int)$payload['length'] : 0;
        $start   = isset($payload['start'])  ? (int)$payload['start']  : 0;
        $draw    = isset($payload['draw'])   ? $payload['draw']         : '';

        $this->db->select('r.*, r.id as return_id, COALESCE(oi.product_name, np.name) as product_name, r.image_proof');
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('(oi.login_id = ' . (int)$vendor_id . ' OR np.vendor_id = ' . (int)$vendor_id . ')', null, false);
        $this->db->order_by('r.created_at', 'DESC');

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $list = $this->db->get()->result();

        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('(oi.login_id = ' . (int)$vendor_id . ' OR np.vendor_id = ' . (int)$vendor_id . ')', null, false);
        $total_records = $this->db->count_all_results();

        $data = [];
        foreach ($list as $r) {
            $row = [];
            $row['return_id']   = '#' . $r->return_id;
            $row['order_id']    = '#' . $r->order_id;
            $row['product_name'] = htmlspecialchars($r->product_name ?? '');
            $row['reason']      = htmlspecialchars($r->reason ?? '');

            $image_proof_url = '';
            if (!empty($r->image_proof)) {
                $img = (string)$r->image_proof;
                $image_proof_url = preg_match('#^https?://#i', $img) ? $img : base_url(ltrim($img, '/'));
            }
            $row['image_proof_url']  = $image_proof_url;
            if (!empty($image_proof_url)) {
                $row['image_proof_html'] = '<a href="' . $image_proof_url . '" target="_blank">' .
                                           '<img src="' . $image_proof_url . '" width="50" alt="Return Image" style="height:50px; object-fit:cover; border-radius:6px;">' .
                                           '</a>';
            } else {
                $row['image_proof_html'] = 'No Image';
            }

            $badge      = 'badge-warning';
            $st         = strtolower((string)$r->status);
            $st_display = $st;
            if ($st_display == 'approved')                                         $st_display = 'accepted';
            if ($st_display == 'inspection_pending')                               $st_display = 'initiated';
            if ($st_display == 'pickup_scheduled' || $st_display == 'picked_up')  $st_display = 'completed';
            if ($st_display == 'approved_refund' || $st_display == 'refund_completed') $st_display = 'refunded';
            if ($st_display == 'initiated')  $badge = 'badge-secondary';
            if ($st_display == 'accepted')   $badge = 'badge-success';
            if ($st_display == 'completed')  $badge = 'badge-primary';
            if ($st_display == 'refunded')   $badge = 'badge-info';
            if ($st_display == 'rejected' || $st_display == 'return_cancelled') $badge = 'badge-danger';

            $row['status']         = $st_display;
            $row['status_badge']   = $badge;
            $row['status_html']    = '<span class="badge ' . $badge . ' text-capitalize">' . $st_display . '</span>';
            $row['created_at_date'] = date('Y-m-d', strtotime($r->created_at));
            $row['return_id_raw']  = (int)$r->return_id;
            $row['action_html']    = '<button type="button" onclick="viewReturnDetails(' . (int)$r->return_id . ')" class="btn btn-primary btn-sm">Review</button>';


            $data[] = $row;
        }

        $output = [
            'draw'            => $draw,
            'recordsTotal'    => (int)$total_records,
            'recordsFiltered' => (int)$total_records,
            'data'            => $data,
        ];

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode($output));
    }

    // ─── Single Return Details ────────────────────────────────────────────────

    public function details()
    {
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $this->load->model('Return_model');

        $payload   = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $return_id = isset($payload['return_id']) ? (int)$payload['return_id'] : 0;

        if (empty($return_id)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status'  => 0,
                    'message' => 'Return ID is required',
                    'data'    => null,
                    'errors'  => ['Return ID is required'],
                ]));
        }

        $return_data = $this->Return_model->get_return_details($return_id, $vendor_id);

        if (empty($return_data)) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status'  => 0,
                    'message' => 'Return details not found or access denied',
                    'data'    => null,
                    'errors'  => ['Return details not found or access denied'],
                ]));
        }

        $data = [
            'return_id'    => $return_data->return_id,
            'order_id'     => $return_data->order_id,
            'order_number' => isset($return_data->order_number) && !empty($return_data->order_number)
                ? $return_data->order_number
                : $return_data->order_id,
            'order_date'     => date('Y-m-d', strtotime($return_data->order_date)),
            'customer_name'  => htmlspecialchars($return_data->first_name . ' ' . $return_data->last_name),
            'product_name'   => htmlspecialchars($return_data->product_name ?? ''),
            'reason'         => htmlspecialchars($return_data->reason ?? ''),
            'vendor_note'    => $return_data->vendor_note ?? '',
            'image_url'      => '',
            'status'         => $return_data->status,
        ];

        if (!empty($return_data->image_proof)) {
            $img = (string)$return_data->image_proof;
            $data['image_url'] = preg_match('#^https?://#i', $img) ? $img : base_url(ltrim($img, '/'));
        }

        $badge      = 'badge-warning';
        $st         = strtolower((string)$return_data->status);
        $st_display = $st;
        if ($st_display == 'approved')                                        $st_display = 'accepted';
        if ($st_display == 'inspection_pending')                              $st_display = 'initiated';
        if ($st_display == 'pickup_scheduled' || $st_display == 'picked_up') $st_display = 'completed';
        if ($st_display == 'approved_refund' || $st_display == 'refund_completed') $st_display = 'refunded';
        if ($st_display == 'initiated') $badge = 'badge-secondary';
        if ($st_display == 'accepted')  $badge = 'badge-success';
        if ($st_display == 'completed') $badge = 'badge-primary';
        if ($st_display == 'refunded')  $badge = 'badge-info';
        if ($st_display == 'rejected' || $st_display == 'return_cancelled') $badge = 'badge-danger';

        $data['status_display']     = $st_display;
        $data['status_badge_class'] = $badge;

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => 1,
                'message' => 'success',
                'data'    => $data,
                'errors'  => [],
            ]));
    }
}
