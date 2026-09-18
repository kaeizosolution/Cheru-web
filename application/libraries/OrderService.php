<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OrderService {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Order_model');
        $this->CI->load->library('cart');
    }

    public function create_order($user_id, $address_id, $payment_method, $cart_items, $coupon_code = null) {
        $this->CI->db->trans_begin();

        try {
            $total = 0;
            foreach ($cart_items as $item) {
                $total += $item['price'] * $item['qty'];
            }

            $discount = $this->apply_coupon($coupon_code, $total);
            $tax = $this->calculate_tax($total);
            $shipping = $this->calculate_shipping($total);

            $final_total = max(0, $total - $discount + $tax + $shipping);

            $order_data = [
                'order_number'   => $this->generate_order_number(),
                'user_id'        => $user_id,
                'address_id'     => $address_id,
                'payment_method' => $payment_method,
                'payment_status' => ($payment_method == 'COD') ? 'pending' : 'paid',
                'total_amount'   => $total,
                'discount_amount'=> $discount,
                'tax_amount'     => $tax,
                'shipping_amount'=> $shipping,
                'final_amount'   => $final_total,
                'status'         => 'pending',
            ];

            $order_id = $this->CI->Order_model->insert_order($order_data);
            if (!$order_id) throw new Exception("Failed to create order.");

            $items_to_insert = [];
            foreach ($cart_items as $item) {
                $stock = $this->CI->Order_model->get_product_stock($item['id']);
                if ($stock < $item['qty']) {
                    throw new Exception("Product '{$item['name']}' is out of stock.");
                }

                $this->CI->Order_model->update_stock($item['id'], $stock - $item['qty']);

                $items_to_insert[] = [
                    'order_id'     => $order_id,
                    'product_id'   => $item['id'],
                    'product_name' => $item['name'],
                    'price'        => $item['price'],
                    'qty'          => $item['qty'],
                    'subtotal'     => $item['price'] * $item['qty'],
                    'options'      => json_encode($item['options'])
                ];
            }

            $this->CI->Order_model->insert_order_items($items_to_insert);

            if ($this->CI->db->trans_status() === FALSE) {
                throw new Exception("DB transaction failed.");
            }

            $this->CI->db->trans_commit();
			$this->CI->cart->destroy();

			if (!empty($user_id)) {
    			$this->CI->db->where('user_id', $user_id)->delete('ec_cart');
			}
            return ['success' => true, 'order_id' => $order_id];

        } catch (Exception $e) {
            $this->CI->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function apply_coupon($code, $total) {
        if (!$code) return 0;

        $code_lower = strtolower(trim($code));
        $q = $this->CI->db->query(
            "SELECT * FROM ec_coupon WHERE LOWER(name) = ? AND status = 1 LIMIT 1",
            [$code_lower]
        );
        if (!$q->num_rows()) return 0;
        $coupon = $q->row();
        return ($coupon->discount_type == 'percent')
            ? ($total * $coupon->discount / 100)
            : $coupon->discount;
    }

    private function calculate_tax($total) {
        $tax_percent = 0;
	if($tax_percent)
        return round($total * $tax_percent / 100, 2);
	
	return false;
    }

    private function calculate_shipping($total) {
        return ($total >= 500) ? 0 : 0; #50 remove
    }

    private function generate_order_number() {
        return "ORD" . date("Ymd") . strtoupper(substr(md5(uniqid()), 0, 4));
    }
}

