<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Orders extends API_Controller
{
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

    private function _item_status_to_label($status)
    {
        $status = (int)$status;
        $map = [
            1 => 'pending',
            2 => 'processing',
            3 => 'shipped',
            10 => 'delivered',
            5 => 'cancelled',
            17 => 'cancelled',
        ];
        return $map[$status] ?? 'pending';
    }

    private function _label_to_item_status($label)
    {
        $label = strtolower(trim((string)$label));
        $map = [
            'processing' => 2,
            'shipped' => 3,
            'delivered' => 10,
        ];
        return $map[$label] ?? 0;
    }

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $page = (int)$this->input->get('page');
        $limit = (int)$this->input->get('limit');
        if ($page <= 0) {
            $page = 1;
        }
        if ($limit <= 0 || $limit > 100) {
            $limit = 20;
        }
        $offset = ($page - 1) * $limit;

        $total_row = $this->db->select('COUNT(DISTINCT eos.id) as cnt', false)
            ->from('ec_orders eos')
            ->join('ec_order_items eop', 'eop.order_id = eos.id')
            ->where('eop.login_id', $vendor_id)
            ->get()->row();
        $total = (int)($total_row->cnt ?? 0);

        $rows = $this->db
            ->select('eos.id as order_id, eos.order_number, eos.user_id, eos.date_added, eos.status as order_status')
            ->select('SUM(eop.subtotal) as vendor_total', false)
            ->select('MIN(eop.status) as vendor_status', false)
            ->from('ec_orders eos')
            ->join('ec_order_items eop', 'eop.order_id = eos.id')
            ->where('eop.login_id', $vendor_id)
            ->group_by('eos.id')
            ->order_by('eos.date_added', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();

        $orders = [];
        foreach ($rows as $r) {
            $orders[] = [
                'order_id' => (int)($r->order_id ?? 0),
                'order_number' => $r->order_number ?? null,
                'order_date' => $r->date_added ?? null,
                'vendor_total' => (float)($r->vendor_total ?? 0),
                'vendor_status' => $this->_item_status_to_label($r->vendor_status ?? 1),
            ];
        }

        return $this->ok([
            'orders' => $orders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit ? (int)ceil($total / $limit) : 1,
            ]
        ], 'success');
    }

    public function show($id = null)
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $order_id = (int)$id;
        if ($order_id <= 0) {
            return $this->fail('validation_error', ['Invalid order id'], 422);
        }

        $has_item = $this->db->from('ec_order_items')
            ->where('order_id', $order_id)
            ->where('login_id', $vendor_id)
            ->count_all_results();
        if (!$has_item) {
            return $this->fail('not_found', ['Order not found'], 404);
        }

        $order = $this->db->from('ec_orders')->where('id', $order_id)->get()->row();
        if (!$order) {
            return $this->fail('not_found', ['Order not found'], 404);
        }

        $items_rows = $this->db
            ->from('ec_order_items')
            ->where('order_id', $order_id)
            ->where('login_id', $vendor_id)
            ->order_by('id', 'ASC')
            ->get()->result();

        $items = [];
        $vendor_total = 0;
        foreach ($items_rows as $it) {
            $sub = (float)($it->subtotal ?? 0);
            $vendor_total += $sub;
            $items[] = [
                'order_item_id' => (int)($it->id ?? 0),
                'product_id' => (int)($it->product_id ?? 0),
                'product_name' => (string)($it->product_name ?? ''),
                'price' => (float)($it->price ?? 0),
                'quantity' => (int)($it->qty ?? 0),
                'subtotal' => $sub,
                'status' => $this->_item_status_to_label($it->status ?? 1),
            ];
        }

        return $this->ok([
            'order' => [
                'order_id' => (int)($order->id ?? 0),
                'order_number' => $order->order_number ?? null,
                'order_date' => $order->date_added ?? null,
                'vendor_total' => $vendor_total,
                // important: do not expose payment_status modifications; we only show current values if exist
                'payment_status' => $order->payment_status ?? null,
            ],
            'items' => $items,
        ], 'success');
    }

    public function update_status()
    {
        if (!$this->require_post()) {
            return;
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $order_id = (int)($payload['order_id'] ?? 0);
        $status_label = isset($payload['status']) ? (string)$payload['status'] : '';

        if ($order_id <= 0 || trim($status_label) === '') {
            return $this->fail('validation_error', ['order_id and status are required'], 422);
        }

        $new_status = $this->_label_to_item_status($status_label);
        if (!$new_status) {
            return $this->fail('validation_error', ['Allowed status: processing, shipped, delivered'], 422);
        }

        $items = $this->db
            ->from('ec_order_items')
            ->where('order_id', $order_id)
            ->where('login_id', $vendor_id)
            ->get()->result();

        if (!$items) {
            return $this->fail('not_found', ['Order not found'], 404);
        }

        // Validate forward-only flow
        foreach ($items as $it) {
            $cur = (int)($it->status ?? 0);
            if (in_array($cur, [5, 17], true)) {
                return $this->fail('cannot_update', ['Cannot update a cancelled item'], 409);
            }
            if ($cur === 10) {
                return $this->fail('cannot_update', ['Cannot update an already delivered item'], 409);
            }
            if ($new_status < $cur) {
                return $this->fail('cannot_update', ['Status cannot move backwards'], 409);
            }
        }

        $this->db->trans_begin();
        try {
            $this->db->where('order_id', $order_id)->where('login_id', $vendor_id)->update('ec_order_items', [
                'status' => (string)$new_status,
            ]);

            // Do NOT change payment status. Do NOT change other vendors' items.
            // Parent order status may be synchronized based on all items (existing vendor panel logic does that).
            // We'll keep it minimal and only sync ec_orders.status to a string status for UX if possible.
            // Compute aggregate status across all items.
            $all_items = $this->db->from('ec_order_items')->where('order_id', $order_id)->get()->result();
            if ($all_items) {
                $all_delivered = true;
                $any_shipped = false;
                $any_processing = false;
                $any_cancelled = false;
                $any_pending = false;

                foreach ($all_items as $ai) {
                    $st = (int)($ai->status ?? 0);
                    if ($st === 10) {
                        // ok
                    } elseif ($st === 3) {
                        $any_shipped = true;
                        $all_delivered = false;
                    } elseif ($st === 2) {
                        $any_processing = true;
                        $all_delivered = false;
                    } elseif ($st === 5 || $st === 17) {
                        $any_cancelled = true;
                        $all_delivered = false;
                    } else {
                        $any_pending = true;
                        $all_delivered = false;
                    }
                }

                $new_parent = 'pending';
                if ($any_cancelled) {
                    $new_parent = 'cancelled';
                } elseif ($all_delivered) {
                    $new_parent = 'delivered';
                } elseif ($any_shipped) {
                    $new_parent = 'shipped';
                } elseif ($any_processing) {
                    $new_parent = 'confirmed';
                } elseif ($any_pending) {
                    $new_parent = 'pending';
                }

                $this->db->where('id', $order_id)->update('ec_orders', ['status' => $new_parent]);
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('DB transaction failed');
            }

            $this->db->trans_commit();

            return $this->ok([
                'order_id' => $order_id,
                'status' => strtolower(trim($status_label)),
            ], 'success');

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $this->fail('server_error', [$e->getMessage()], 500);
        }
    }
}
