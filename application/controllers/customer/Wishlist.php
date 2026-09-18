<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wishlist extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Query_model');
    }

    private function _resolve_customer_id()
    {
        $customer = $this->session->userdata('customer');
        if (!$customer) {
            return 0;
        }
        return (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
    }

    public function ids()
    {
        $user_id = $this->_resolve_customer_id();
        if (!$user_id) {
            return api_response(['status' => 0, 'msg' => 'Not logged in', 'data' => []]);
        }

        $rows = $this->Query_model->get_data('ec_wishlist', ['customer_id' => $user_id, 'status' => '1']);
        $ids = [];
        if ($rows) {
            foreach ($rows as $r) {
                if (!empty($r->product_id)) {
                    $ids[] = (int)$r->product_id;
                }
            }
        }

        return api_response(['status' => 1, 'msg' => 'Wishlist ids', 'data' => $ids]);
    }

    public function toggle()
    {
        $user_id = $this->_resolve_customer_id();
        if (!$user_id) {
            return api_response(['status' => 0, 'msg' => 'Not logged in', 'data' => []]);
        }

        $product_id = (int)$this->input->post('product_id');
        if (!$product_id) {
            return api_response(['status' => 0, 'msg' => 'product_id is required', 'data' => []]);
        }

        $existing = $this->Query_model->get_data_obj('ec_wishlist', ['customer_id' => $user_id, 'product_id' => $product_id], [], ['length' => 1]);
        if ($existing && (string)$existing->status === '1') {
            $updated = $this->Query_model->update_data('ec_wishlist', ['status' => '2', 'last_updated' => date('Y-m-d H:i:s')], ['wishlist_id' => $existing->wishlist_id]);
            if ($updated) {
                return api_response(['status' => 1, 'msg' => 'Removed from wishlist', 'data' => ['in_wishlist' => 0]]);
            }
            return api_response(['status' => 0, 'msg' => 'Failed to update wishlist', 'data' => []]);
        }

        if ($existing) {
            $updated = $this->Query_model->update_data('ec_wishlist', ['status' => '1', 'last_updated' => date('Y-m-d H:i:s')], ['wishlist_id' => $existing->wishlist_id]);
            if ($updated) {
                return api_response(['status' => 1, 'msg' => 'Added to wishlist', 'data' => ['in_wishlist' => 1]]);
            }
            return api_response(['status' => 0, 'msg' => 'Failed to update wishlist', 'data' => []]);
        }

        $insert_id = $this->Query_model->insert_data('ec_wishlist', [
            'customer_id' => $user_id,
            'product_id' => $product_id,
            'status' => '1',
            'date_added' => date('Y-m-d H:i:s'),
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        if ($insert_id) {
            return api_response(['status' => 1, 'msg' => 'Added to wishlist', 'data' => ['in_wishlist' => 1]]);
        }

        return api_response(['status' => 0, 'msg' => 'Failed to add to wishlist', 'data' => []]);
    }

    public function list()
    {
        $user_id = $this->_resolve_customer_id();
        if (!$user_id) {
            return api_response(['status' => 0, 'msg' => 'Not logged in', 'data' => []]);
        }

        $this->db->select('w.wishlist_id, w.product_id, p.name');
        $this->db->from('ec_wishlist w');
        $this->db->join('products p', 'p.id = w.product_id', 'left');
        $this->db->where('w.customer_id', (int)$user_id);
        $this->db->where('w.status', '1');
        $this->db->order_by('w.date_added', 'DESC');
        $rows = $this->db->get()->result_array();

        $out = [];
        foreach ($rows as $r) {
            $image_url = '';
            $product_id = (int)($r['product_id'] ?? 0);
            $variation = null;
            if ($product_id > 0) {
                $variation = $this->Query_model->get_data_obj('product_variations', ['product_id' => $product_id], ['id' => 'ASC'], ['length' => 1]);
                if ($variation && isset($variation->id)) {
                    $img = $this->Query_model->get_data_obj('product_variation_images', ['variation_id' => (int)$variation->id], ['id' => 'ASC'], ['length' => 1]);
                    if ($img && !empty($img->image_path)) {
                        $image_url = base_url('uploads/products/' . $img->image_path);
                    }
                }
            }

            $price = 0;
            if (!empty($variation) && isset($variation->id)) {
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

            $out[] = [
                'wishlist_id' => (int)$r['wishlist_id'],
                'product_id' => (int)$r['product_id'],
                'name' => (string)($r['name'] ?? ''),
                'image_url' => $image_url,
                'price' => $price,
                'product_url' => base_url('product/details/' . (int)$r['product_id'])
            ];
        }

        return api_response(['status' => 1, 'msg' => 'Wishlist', 'data' => $out]);
    }
}
