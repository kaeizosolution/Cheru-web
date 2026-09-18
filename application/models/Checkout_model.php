<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Checkout_model extends MY_Model{
    function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = isset($user_session['vendor_id']) ? $user_session['vendor_id'] : 0;
    }

    public function make_order($args_data){
        $cart_details = [];
        if(isset($args_data['order_now_btn']) && $args_data['order_now_btn'])
        $cart_details = $args_data['order_now_btn'];
        else
        $cart_details = get_cart();

        $this->master->trans_begin();
        $this->master->insert('ec_order',$args_data['ec_order']);
        $order_id = $this->master->insert_id();

        $ec_order_item = $args_data['ec_order_item'];
        $notidata = array();
        if($ec_order_item)
        {
        foreach($ec_order_item as $row){
            $product_type = $row['product_type'];
            $quantity = $row['quantity'];
            $product_id = $row['product_id'];
            $attribute_item_id = $row['attribute_item_id'];
            $row['order_id'] = $order_id;
            $row['order_item_uid'] = uniq_uid();
            $notidata = array('order_id' => $order_id, 'product_id'=> $row['product_id'], 'user_id' => $row['customer_id'], 'vendor_id' => $row['supplier_id'], 'type'=> '1');
            $this->master->insert('ec_order_item',$row);
            $this->master->insert('ec_notification',$notidata);
            $order_item_id = $this->master->insert_id();
            if($order_item_id && $cart_details && isset($cart_details[$product_id][$attribute_item_id])){
                if($product_type == 'simple'){
                    $this->master->set('stock', "stock-$quantity", FALSE);
                    $this->master->where('product_id', $product_id);
                    $this->master->update('ec_product');
                }else{
                    $this->master->set('_stock', "_stock-$quantity", FALSE);
                    $this->master->where('product_id', $product_id);
                    $this->master->where('attribute_item_id', $attribute_item_id);
                    $this->master->update('ec_product_variation');
                }
                unset($cart_details[$product_id][$attribute_item_id]);
            
                if(isset($args_data['order_now_btn']) && $args_data['order_now_btn'])
                {
                    $this->session->unset_userdata('order_now_btn', $cart_details);
                }
                else
                {
                    $this->session->set_userdata('cart', $cart_details);
                    $this->set_cart_db($cart_details);
                }
            }
        }
        }

        if ($this->master->trans_status() === FALSE){
            $this->master->trans_rollback();
            return 0;
        }else{
            $this->master->trans_commit();
            return $order_id;
        }
    }

    public function notification_set_driver($args)
    {
        $this->slave->select('driver_id');
        $this->slave->from('ec_driver');
        $this->slave->where('status','1');
        $this->slave->where('driver_status','1');
        $obj = $this->slave->get()->result();
        if($obj)
        {
            $insert_all = [];
            foreach($obj as $row_wise)
            { 
                $insert_data['order_id']    = $args['order_id'];
                $insert_data['product_id']  = $args['product_id'];
                $insert_data['type']        = '2'; 
                $insert_data['user_id']     = $args['user_id'];
                $insert_data['driver_id']   = $row_wise->driver_id;
                $insert_all[]               = $insert_data;
            }
            $this->master->insert_batch('ec_notification', $insert_all);
        }
    }

    public function cancel_order($args){
       
        $tax = $args['tax'];
        $shipping = $args['shipping'];
        $coupon_amount = $args['coupon_amount'];
        $subtotal = $args['subtotal'];
        $total = $args['total'];
        $quantity = $args['quantity'];
        $attribute_item_id = $args['attribute_item_id'];
        $product_id = $args['product_id'];
        $order_id   = $args['order_id'];
        $order_item_id   = $args['order_item_id'];
        $type = $args['type'];
        $cancel_reason = $args['cancel_order_reason'];
        $comment = $args['comment'];
		$status = isset($args['status']) ? $args['status'] : '5';	

        $this->master->trans_begin();

        if($type == 'simple'){
            $this->master->set('stock', "stock+$quantity", FALSE);
            $this->master->where('product_id', $product_id);
            $this->master->update('ec_product');
        }else{
            $this->master->set('_stock', "_stock+$quantity", FALSE);
            $this->master->where('product_id', $product_id);
            $this->master->where('attribute_item_id', $attribute_item_id);
            $this->master->update('ec_product_variation');
        }

        $this->master->set('subtotal', "subtotal-$subtotal", FALSE);
        $this->master->set('shipping', "shipping-$shipping", FALSE);
        $this->master->set('tax', "tax-$tax", FALSE);
        $this->master->set('coupon_amount', "coupon_amount-$coupon_amount", FALSE);
        $this->master->set('total', "total-$total", FALSE);
		if(isset($args['cancelled_by']) && $args['cancelled_by'] == 'backend')
		{
			$this->master->set('status', '17');
		}
        $this->master->where('order_id', $order_id);
        $this->master->update('ec_order');

        $this->master->set('status', $status);
        $this->master->set('comment', $comment);
        $this->master->set('cancel_order_reason', $cancel_reason);
        $this->master->where('order_item_id', $order_item_id);
        $this->master->update('ec_order_item');

        if ($this->master->trans_status() === FALSE){
            $this->master->trans_rollback();
            return 0;
        }else{
            $this->master->trans_commit();
            return 1;
        }
    }

    public function set_cart_db($cart_details)
    {
        if($this->LOGIN_ID){
            $this->slave->select('*');
            $this->slave->from('ec_cart');
            $this->slave->where('customer_id', $this->LOGIN_ID);
            $obj = $this->slave->get()->row();
            if($obj){
                $data = array('cart' => serialize($cart_details));
                $this->master->where('customer_id', $this->LOGIN_ID);
                $this->master->update('ec_cart',$data);
            }else{
                $data = array('customer_id' => $this->LOGIN_ID, 'cart' => serialize($cart_details));
                $this->master->insert('ec_cart',$data);
            }
        }
    }
}
