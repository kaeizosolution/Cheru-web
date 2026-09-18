<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Query_model');
		$this->load->model('Item_model');
		$this->load->library('cart');
		$this->load->library('CartService');
		$this->TYPE = $this->session->userdata('type');
		$this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
	}

		
	public function add_to_cart() {
        $product_id = $this->input->post('product_id');
        $product    = $this->Query_model->get_data_obj('products', ['id' => $product_id]);

        if (!$product) {
            return api_response(['status' => 0, 'msg' => 'Product not found', 'data'=>[]]);
        }


        $item_data = $this->cartservice->prepare_item_data($product, $this->input->post());
        $this->cartservice->add_or_update_cart($product_id, $item_data);
        $this->cartservice->save_cart_if_logged_in();

        $cart_dtl = $this->cartservice->get_cart_response();
        api_response(['status' => 1, 'msg' => 'Item added to cart', 'data' => $cart_dtl]);
    }

    public function remove() {
        $rowid = $this->input->post('rowid');
        $this->cartservice->remove_item($rowid);
        api_response(['status' => 1, 'msg' => 'Item removed', 'data' => $this->cartservice->get_cart_response()]);
    }

    public function update_qty() {
        $rowid = $this->input->post('rowid');
        $qty   = $this->input->post('qty');
        $this->cartservice->update_item_qty($rowid, $qty);
        api_response(['status' => 1, 'msg' => 'Quantity updated', 'data' => $this->cartservice->get_cart_response()]);
    }

    public function apply_coupon() {
        $code = $this->input->post('coupon');
        $result = $this->cartservice->apply_coupon($code);
        api_response($result);
    }

	public function get_cart()
	{
		try {
			$cart_data = $this->cartservice->get_cart_response();
			api_response([
							'status' => 1,
							'msg'    => 'Cart details fetched successfully',
							'data'   => $cart_data
						]);
			} catch (Exception $e) {
			api_response([
							'status' => 0,
							'msg'    => 'Failed to fetch cart: ' . $e->getMessage(),
							'data'   => []
						]);
			}
	}

	public function ajax_search() {
        $query = $this->input->post('q');
        $products = $this->Item_model->search_products($query);

		api_response([
                    	'status' => 1,
                        'msg'    => 'List',
                        'data'   => $products
                        ]);
    }	
	
}
