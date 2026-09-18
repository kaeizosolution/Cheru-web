<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Wishlist extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
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

    public function index()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        // Use standard join to get wishlist and product data (new products table)
        $this->db->select('w.wishlist_id, w.product_id, p.name');
        $this->db->select('cp.name AS category_name');
        $this->db->select('AVG(rr.rating) AS avg_rating, COUNT(rr.review_rating_id) AS rating_count', false);
        $this->db->from('ec_wishlist w');
        $this->db->join('products p', 'p.id = w.product_id', 'left');
        $this->db->join('ec_categories_prod cp', 'cp.id = p.category', 'left');
        $this->db->join('ec_review_rating rr', 'rr.product_id = w.product_id', 'left');
        $this->db->where('w.customer_id', $customer_id);
        $this->db->where('w.status', '1');
        $this->db->group_by('w.wishlist_id');
        $this->db->order_by('w.date_added', 'DESC');
        $rows = $this->db->get()->result();

        $items = [];
        if ($rows) {
            foreach ($rows as $r) {
                $price = 0;

                // Image URL from new schema (first variation image)
                $image_url = '';
                $product_id = (int)($r->product_id ?? 0);
                $variation = null;
                if ($product_id > 0) {
                    $variation = $this->Query_model->get_data_obj('product_variations', ['product_id' => $product_id], ['id' => 'ASC'], ['length' => 1]);
                    if ($variation && isset($variation->id)) {
                        $img = $this->Query_model->get_data_obj('product_variation_images', ['variation_id' => (int)$variation->id], ['id' => 'ASC'], ['length' => 1]);
                        if ($img && !empty($img->image_path)) {
                            $image_url = base_url('uploads/products/' . $img->image_path);
                        }

                        $price_row = $this->db
                            ->select('price')
                            ->from('product_variation_price')
                            ->where('variation_id', (int)$variation->id)
                            ->order_by('COALESCE(min_qty,0)', 'ASC', false)
                            ->limit(1)
                            ->get()->row();
                        if ($price_row && isset($price_row->price) && $price_row->price !== null) {
                            $price = (float)$price_row->price;
                        }
                    }
                }

                $items[] = [
                    'wishlist_id' => (int)($r->wishlist_id ?? 0),
                    'product_id' => (int)($r->product_id ?? 0),
                    'product_name' => (string)($r->name ?? ''),
                    'category_name' => (string)($r->category_name ?? ''),
                    'avg_rating' => isset($r->avg_rating) && $r->avg_rating !== null ? (float)$r->avg_rating : 0,
                    'rating_count' => isset($r->rating_count) && $r->rating_count !== null ? (int)$r->rating_count : 0,
                    'price' => $price,
                    'image_url' => $image_url,
                ];
            }
        }

        return $this->ok(['wishlist' => $items], 'success');
    }

    public function add()
    {
        if (!$this->require_post()) {
            return;
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $product_id = (int)($payload['product_id'] ?? 0);

        if ($product_id <= 0) {
            return $this->fail('validation_error', ['product_id is required'], 422);
        }

        // Check if already in wishlist
        $existing = $this->Query_model->get_data_obj('ec_wishlist', [
            'customer_id' => $customer_id, 
            'product_id' => $product_id
        ], [], ['length' => 1]);

        if ($existing) {
            if ((string)$existing->status === '1') {
                 return $this->ok(['wishlist_id' => (int)$existing->wishlist_id, 'in_wishlist' => true], 'Already in wishlist');
            }
            // Reactivate it
            $this->Query_model->update_data('ec_wishlist', [
                'status' => '1', 
                'last_updated' => date('Y-m-d H:i:s')
            ], ['wishlist_id' => $existing->wishlist_id]);
            
            return $this->ok(['wishlist_id' => (int)$existing->wishlist_id, 'in_wishlist' => true], 'Added to wishlist');
        }

        $insert = [
            'customer_id' => $customer_id,
            'product_id' => $product_id,
            'status' => '1',
            'date_added' => date('Y-m-d H:i:s'),
            'last_updated' => date('Y-m-d H:i:s')
        ];
        
        $new_id = $this->Query_model->insert_data('ec_wishlist', $insert);

        if (!$new_id) {
            return $this->fail('server_error', ['Failed to add to wishlist'], 500);
        }

        return $this->ok(['wishlist_id' => (int)$new_id, 'in_wishlist' => true], 'Added to wishlist');
    }

    public function remove()
    {
        $method = strtoupper((string)$this->input->method());
        if (!in_array($method, ['DELETE', 'POST'], true)) {
            return $this->fail('method_not_allowed', ['Only DELETE or POST is allowed'], 405);
        }

        $customer_id = $this->_require_customer();
        if (!$customer_id) {
            return;
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $product_id = (int)($payload['product_id'] ?? 0);
        $wishlist_id = (int)($payload['wishlist_id'] ?? 0);

        if ($product_id <= 0 && $wishlist_id <= 0) {
            return $this->fail('validation_error', ['product_id or wishlist_id is required'], 422);
        }

        $condition = ['customer_id' => $customer_id];
        if ($wishlist_id > 0) {
             $condition['wishlist_id'] = $wishlist_id;
        } else {
             $condition['product_id'] = $product_id;
        }

        $ok = $this->Query_model->update_data('ec_wishlist', [
            'status' => '2', 
            'last_updated' => date('Y-m-d H:i:s')
        ], $condition);

        return $this->ok(['removed' => true], 'Removed from wishlist');
    }
}
