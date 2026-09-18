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
        $this->load->model('Cart_model');
        $this->load->library('session');
    }

    private function _require_customer()
    {
        $token = $this->get_bearer_token();
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
            $this->fail('unauthorized', ['Invalid or expired access token'], 401);
            return 0;
        }

        $customer_id = (int)$claims['sub'];
        if ($customer_id <= 0) {
            $this->fail('unauthorized', ['Invalid token subject'], 401);
            return 0;
        }

        // Merge into session instead of overwriting
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

    private function _normalize_order_status($order_row)
    {
        $status = isset($order_row->status) ? trim((string)$order_row->status) : '';
        if ($status !== '') {
            return $status;
        }

        $os = isset($order_row->order_status) ? (int)$order_row->order_status : 0;
        $map = [
            1 => 'pending',
            2 => 'confirmed',
            3 => 'shipped',
            4 => 'delivered',
            5 => 'cancelled',
        ];
        return $map[$os] ?? 'pending';
    }

    /**
     * Applies status filter to the DB query builder for ec_orders.
     *
     * Active uses a SAFE combined condition:
     *   (status IN ('pending','confirmed','shipped'))
     *   OR (order_status IN (1,2,3) AND status NOT IN ('delivered','cancelled'))
     *
     * The NOT IN guard ensures delivered/cancelled orders NEVER leak into Active,
     * while still catching orders where the status enum was never synced from the default.
     *
     * Completed and Cancelled use only the reliable status enum (single value).
     */
    private function _apply_status_filter($filter)
    {
        if ($filter === 'active') {
            // Safe combined condition:
            // Match orders that are clearly active by status enum,
            // OR by order_status integer (1=pending,2=confirmed,3=shipped)
            // but ONLY if they are not delivered/cancelled (prevents any leaking)
            $this->db->group_start()
                ->where_in('status', ['pending', 'confirmed', 'shipped'])
                ->or_group_start()
                    ->where_in('order_status', [1, 2, 3])
                    ->where_not_in('status', ['delivered', 'cancelled'])
                ->group_end()
            ->group_end();
        } elseif ($filter === 'completed') {
            // Completed = delivered only. Never includes returned orders.
            $this->db->where('status', 'delivered');
        } elseif ($filter === 'cancelled') {
            // Cancelled = cancelled only. Returned orders keep status 'delivered'.
            $this->db->where('status', 'cancelled');
        }
        // 'all' or empty → no WHERE added → returns all orders for this customer
    }


    private function _build_orders_response($customer_id, $filter, $page, $limit)
    {
        if ($limit <= 0 || $limit > 100) {
            $limit = 20;
        }
        $offset = ($page - 1) * $limit;

        // --- COUNT query (resets builder by default) ---
        $this->db->from('ec_orders')->where('user_id', $customer_id);
        $this->_apply_status_filter($filter);
        $total = (int)$this->db->count_all_results(); // no args = reset=true, safe

        // --- DATA query (fresh builder, same conditions) ---
        $this->db->select('*')
                 ->from('ec_orders')
                 ->where('user_id', $customer_id);
        $this->_apply_status_filter($filter);
        $this->db->order_by('date_added', 'DESC')->limit($limit, $offset);
        $rows = $this->db->get()->result();

        $order_ids = [];
        foreach ($rows as $r) {
            if (isset($r->id)) {
                $order_ids[] = $r->id;
            }
        }
        
        $returned_orders = [];
        if (!empty($order_ids)) {
            $returns = $this->db->select('order_id, status')->from('returns')->where_in('order_id', $order_ids)->get()->result();
            foreach($returns as $ret) {
                $returned_orders[$ret->order_id] = $ret->status; 
            }
        }

        $orders = [];
        foreach ($rows as $r) {
            $base_status = $this->_normalize_order_status($r);
            if (isset($returned_orders[$r->id])) {
                $ret_status = $returned_orders[$r->id];
                if ($ret_status === 'requested' || $ret_status === 'pending') {
                    $base_status = 'return_requested';
                } elseif ($ret_status === 'rejected') {
                    $base_status = 'return_rejected';
                } else {
                    $base_status = 'returned';
                }
            }

            $orders[] = [
                'order_id'       => (int)($r->id ?? 0),
                'order_number'   => $r->order_number ?? null,
                'order_date'     => $r->date_added ?? null,
                'total_amount'   => (float)($r->final_amount ?? $r->total_amount ?? 0),
                'payment_status' => (string)($r->payment_status ?? ''),
                'order_status'   => $base_status,
            ];
        }

        return [
            'filter'     => $filter ?: 'all',
            'orders'     => $orders,
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => $limit ? (int)ceil($total / $limit) : 1,
            ],
        ];
    }


    /**
     * GET /api/v1/Orders
     * Backward-compatible endpoint. Accepts ?filter=active|completed|cancelled|all
     * Also still works without filter for all orders.
     */
    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $filter = strtolower(trim((string)$this->input->get('filter')));
        $page   = max(1, (int)$this->input->get('page'));
        $limit  = (int)$this->input->get('limit');
        if ($limit <= 0) {
            $limit = 20;
        }

        $data = $this->_build_orders_response($customer_id, $filter, $page, $limit);
        return $this->ok($data, 'success');
    }

    /**
     * POST /api/v1/Orders/list
     * Recommended endpoint for mobile apps.
     * Send filter in JSON body — no query string needed.
     *
     * Request body (JSON):
     *   {
     *     "filter": "all" | "active" | "completed" | "cancelled",
     *     "page": 1,
     *     "limit": 20
     *   }
     *
     * Filter mapping:
     *   all       → all orders for the customer
     *   active    → pending, confirmed, shipped
     *   completed → delivered only
     *   cancelled → cancelled only
     *
     * Response includes filter name used so the app can confirm what was applied.
     */
    public function list_orders()
    {
        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        // Accept filter from JSON body or POST form data
        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $filter  = strtolower(trim((string)($payload['filter'] ?? 'all')));
        $page    = max(1, (int)($payload['page'] ?? 1));
        $limit   = (int)($payload['limit'] ?? 20);

        // Validate filter value
        $allowed_filters = ['all', 'active', 'completed', 'cancelled'];
        if (!in_array($filter, $allowed_filters, true)) {
            return $this->fail('validation_error', [
                'Invalid filter. Allowed values: ' . implode(', ', $allowed_filters)
            ], 422);
        }

        $data = $this->_build_orders_response($customer_id, $filter, $page, $limit);
        return $this->ok($data, 'success');
    }

    public function detail()
    {
        if (!$this->require_post()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $order_id = (int)($payload['order_id'] ?? 0);
        if ($order_id <= 0) {
            return $this->fail('validation_error', ['order_id is required'], 422);
        }

        $order = $this->db
            ->from('ec_orders')
            ->where('id', $order_id)
            ->get()->row();

        if (!$order) {
            return $this->fail('not_found', ['Order not found'], 404);
        }

        if ((int)($order->user_id ?? 0) !== $customer_id) {
            return $this->fail('unauthorized', ['You are not allowed to view this order'], 403);
        }

        $address_id = (int)($order->address_id ?? 0);
        $address = null;
        if ($address_id > 0) {
            $addr = $this->Query_model->get_data_obj('ec_shipping_address', [
                'shipping_address_id' => $address_id,
                'customer_id'         => $customer_id
            ]);
            $address = $addr ? (array)$addr : null;
        }

        $items_rows = $this->db
            ->from('ec_order_items')
            ->where('order_id', $order_id)
            ->order_by('id', 'ASC')
            ->get()->result();

        $items = [];
        foreach ($items_rows as $it) {
            $image_url  = '';
            $product_id = (int)($it->product_id ?? 0);
            if ($product_id > 0) {
                $variation = $this->Query_model->get_data_obj('product_variations', ['product_id' => $product_id], ['id' => 'ASC'], ['length' => 1]);
                if ($variation && isset($variation->id)) {
                    $img = $this->Query_model->get_data_obj('product_variation_images', ['variation_id' => (int)$variation->id], ['id' => 'ASC'], ['length' => 1]);
                    if ($img && !empty($img->image_path)) {
                        $image_url = base_url('uploads/products/' . $img->image_path);
                    }
                }
            }

            $items[] = [
                'order_item_id' => (int)($it->id ?? 0),
                'product_id'    => $product_id,
                'name'          => (string)($it->product_name ?? ''),
                'price'         => (float)($it->price ?? 0),
                'quantity'      => (int)($it->qty ?? 0),
                'subtotal'      => (float)($it->subtotal ?? 0),
                'image_url'     => $image_url,
                'status'        => (string)($it->status ?? ''),
                'last_updated'  => $it->last_updated ?? null,
                'date_added'    => $it->date_added ?? null,
                'login_id'      => (int)($it->login_id ?? 0)
            ];
        }

        $customer     = $this->Query_model->get_data_obj('ec_customer', ['customer_id' => $customer_id]);
        $customer_out = $customer ? [
            'customer_id' => $customer_id,
            'fname'       => $customer->fname ?? null,
            'lname'       => $customer->lname ?? null,
            'email'       => $customer->email ?? null,
            'mobile'      => $customer->mobile ?? null,
        ] : ['customer_id' => $customer_id];

        $return_req = $this->db->select('status')->from('returns')->where('order_id', $order_id)->limit(1)->get()->row();
        $base_status = $this->_normalize_order_status($order);
        if ($return_req) {
            $ret_status = $return_req->status;
            if ($ret_status === 'requested' || $ret_status === 'pending') {
                $base_status = 'return_requested';
            } elseif ($ret_status === 'rejected') {
                $base_status = 'return_rejected';
            } else {
                $base_status = 'returned';
            }
        }

        return $this->ok([
            'order' => [
                'order_id'        => (int)($order->id ?? 0),
                'order_number'    => $order->order_number ?? null,
                'order_date'      => $order->date_added ?? null,
                'total_amount'    => (float)($order->total_amount ?? 0),
                'discount_amount' => (float)($order->discount_amount ?? 0),
                'tax_amount'      => (float)($order->tax_amount ?? 0),
                'shipping_amount' => (float)($order->shipping_amount ?? 0),
                'final_amount'    => (float)($order->final_amount ?? 0),
                'payment_method'  => $order->payment_method ?? null,
                'payment_status'  => $order->payment_status ?? null,
                'order_status'    => $base_status,
            ],
            'customer'         => $customer_out,
            'shipping_address' => $address,
            'items'            => $items,
        ], 'success');
    }

    public function cancel()
    {
        if (!$this->require_post()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload  = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $order_id = (int)($payload['order_id'] ?? 0);
        if ($order_id <= 0) {
            return $this->fail('validation_error', ['order_id is required'], 422);
        }

        $order = $this->db
            ->from('ec_orders')
            ->where('id', $order_id)
            ->get()->row();

        if (!$order) {
            return $this->fail('not_found', ['Order not found'], 404);
        }

        if ((int)($order->user_id ?? 0) !== $customer_id) {
            return $this->fail('unauthorized', ['You are not allowed to cancel this order'], 403);
        }

        $status = $this->_normalize_order_status($order);
        if (in_array($status, ['shipped', 'delivered'], true)) {
            return $this->fail('cannot_cancel', ['Order cannot be cancelled after shipping/delivery'], 409);
        }
        if ($status === 'cancelled') {
            return $this->ok(['order_id' => $order_id, 'order_status' => 'cancelled'], 'success');
        }

        $this->db->trans_begin();
        try {
            $items = $this->db->from('ec_order_items')->where('order_id', $order_id)->get()->result();

            // Restore stock (simple products only)
            foreach ($items as $it) {
                $pid = (int)($it->product_id ?? 0);
                $qty = (int)($it->qty ?? 0);
                if ($pid > 0 && $qty > 0) {
                    $this->db->set('stock', 'stock+' . $qty, false)
                        ->where('id', $pid)
                        ->update('ec_product');
                }
            }

            $orderUpdate = [
                'status'       => 'cancelled',
                'order_status' => 5,
            ];
            if ($this->db->field_exists('cancelled_by', 'ec_orders')) {
                $orderUpdate['cancelled_by'] = 'customer';
            }
            $this->db->where('id', $order_id)->update('ec_orders', $orderUpdate);

            // Mark all order items as cancelled (status 5 = Cancelled By Customer)
            $itemUpdate = [
                'status'       => '5',
                'last_updated' => date('Y-m-d H:i:s'),
            ];
            if ($this->db->field_exists('cancelled_by', 'ec_order_items')) {
                $itemUpdate['cancelled_by'] = 'customer';
            }
            $this->db->where('order_id', $order_id)->update('ec_order_items', $itemUpdate);

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('DB transaction failed');
            }

            $this->db->trans_commit();

            return $this->ok([
                'order_id'     => $order_id,
                'order_status' => 'cancelled',
            ], 'success');

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return $this->fail('server_error', [$e->getMessage()], 500);
        }
    }
}
