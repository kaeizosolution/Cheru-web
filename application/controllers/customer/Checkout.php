<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Checkout extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
		$this->load->model('Product_model');
		$this->load->model('Cart_model');
		$this->load->model('Checkout_model');
		$this->config->load('custom_config');
		$this->load->helper('api');
        $this->load->helper('text');		
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

	private function _get_customer_access_token()
	{
		$customer = $this->session->userdata('customer');
		if (is_array($customer) && !empty($customer['access_token'])) {
			return (string)$customer['access_token'];
		}
		if (!empty($_SESSION['token'])) {
			return (string)$_SESSION['token'];
		}
		return '';
	}

	private function _build_api_headers()
	{
		$token = $this->_get_customer_access_token();
		if ($token === '') {
			return [];
		}
		return ['Authorization' => 'Bearer ' . $token];
	}

	private function _map_api_cart_to_legacy_payload($api_data)
	{
		$api_items = is_array($api_data['items'] ?? null) ? $api_data['items'] : [];
		$cart_total = (float)($api_data['cart_total'] ?? 0);
		$final_total = (float)($api_data['final_total'] ?? $cart_total);

		$items = [];
		$total_qty = 0;
		foreach ($api_items as $it) {
			if (!is_array($it)) {
				continue;
			}
			$product_id = (int)($it['product_id'] ?? 0);
			if ($product_id <= 0) {
				continue;
			}
			$qty = (int)($it['quantity'] ?? 0);
			$price = (float)($it['price'] ?? 0);
			$row_total = (float)($it['subtotal'] ?? ($price * $qty));
			$total_qty += $qty;

			$product = $this->Query_model->get_data_obj('ec_product', ['id' => $product_id], [], ['length' => 1]);
			$image_name = 'default.png';
			if ($product && !empty($product->images)) {
				$clean_image = str_replace(['[', ']', '"', '"'], '', (string)$product->images);
				$clean_image = trim($clean_image);
				if ($clean_image !== '') {
					$image_name = $clean_image;
				}
			} else {
				$image_row = $this->db->get_where('ec_gallery', ['product_id' => $product_id], 1)->row();
				if ($image_row && !empty($image_row->file_name)) {
					$image_name = (string)$image_row->file_name;
				}
			}
			$image_url = base_url('uploads/products/' . $image_name);

			$regular_price = $price;
			if ($product && isset($product->prices) && $product->prices) {
				$price_data = json_decode($product->prices, true);
				if (is_array($price_data) && isset($price_data[0]) && is_array($price_data[0]) && isset($price_data[0]['regular_price'])) {
					$regular_price = (float)$price_data[0]['regular_price'];
				}
			}

			$items[] = (object)array(
				'id' => $product_id,
				'product_id' => $product_id,
				'post_title' => (string)($it['product_name'] ?? ($product && isset($product->name) ? $product->name : '')),
				'image' => $image_name,
				'image_url' => $image_url,
				'quantity' => $qty,
				'sale_price' => $price,
				'regular_price' => $regular_price,
				'total' => $row_total,
				'attribute_item_id' => '',
				'product_type' => $product && isset($product->product_type) ? $product->product_type : 'simple',
			);
		}

		$summary = (object)array(
			'subtotal' => $cart_total,
			'shipping' => 0,
			'additional_charges' => 0,
			'tax' => 0,
			'total' => $cart_total,
			'grand_total' => $final_total,
			'total_qty' => $total_qty,
		);

		return [
			'cart_items' => $items,
			'cart_summary' => $summary,
			'cart_count' => (int)count($items),
			'cart_qty' => (int)$total_qty,
			'cart_total' => (float)$final_total,
		];
	}

	

	
	private function set_checkout_step($step) {
        $this->session->set_userdata('checkout_step', $step);
    }

    private function get_checkout_step() {
        return $this->session->userdata('checkout_step') ?? 'address';
    }

	public function set_step()
	{
		$step = $this->input->post('step');
		$allowed = array('address', 'payment', 'confirm');
		if (!in_array($step, $allowed, true)) {
			return api_response(['status' => 0, 'msg' => 'Invalid step', 'data' => []]);
		}
		$this->set_checkout_step($step);
		return api_response(['status' => 1, 'msg' => 'Step updated', 'data' => ['step' => $step]]);
	}

	function index(){
		$data = [];
		$data['login_id'] = $this->LOGIN_ID;
		$existing_step = $this->get_checkout_step();
		// Ensure checkout always starts from address (fresh entry) and doesn't get stuck on payment/confirm
		if(!$this->LOGIN_ID || $existing_step === 'payment' || $existing_step === 'confirm'){
			$this->set_checkout_step('address');
			$this->session->unset_userdata('selected_address');
			$existing_step = 'address';
            
            // Set redirect back to checkout after login
            if (!$this->LOGIN_ID) {
                $this->session->set_userdata('auth_redirect_url', base_url('checkout'));
            }
		}
		if($this->LOGIN_ID){$this->after_login();}
		$data['checkout_step'] = $existing_step;
		$this->load->template('/customer/checkout', $data);
	}

	public function buy_now($id)
	{
		$id = (int)$id;
		if ($id <= 0) {
			redirect('checkout');
			return;
		}
		$variation_id = (int)$this->input->get('variation_id');
		$attribute_item_id = trim((string)$this->input->get('attribute_item_id'));
		$this->session->set_userdata('buy_now_product', [
			'product_id' => $id,
			'quantity' => 1,
			'variation_id' => $variation_id,
			'attribute_item_id' => $attribute_item_id,
		]);
		$this->set_checkout_step('address');
		redirect('checkout');
	}

	public function ajax_checkout_payload()
	{
		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$api_cart = call_api('GET', 'cart', null, $headers);
			if ($api_cart['response'] !== null && (int)($api_cart['response']['status'] ?? 0) === 1 && is_array($api_cart['response']['data'] ?? null)) {
				$payload = $this->_map_api_cart_to_legacy_payload($api_cart['response']['data']);
			}
		}

		if (!isset($payload)) {
			$cart_info = $this->Cart_model->checkout_items();
			$summary = $cart_info['cart_summary'] ?? (object)[];
			$payload = [
				'cart_items' => $cart_info['cart_items'] ?? [],
				'cart_summary' => $summary,
				'cart_count' => count($cart_info['cart_items'] ?? []),
				'cart_qty' => (int)($summary->total_qty ?? 0),
				'cart_total' => (float)($summary->grand_total ?? 0)
			];
		}

		$cur_rate = 1.0;
		$cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
		if ($cc_id > 0) {
			$cur = $this->db
				->select('currency_id, symbol, rate')
				->from('ec_currency')
				->where('currency_id', $cc_id)
				->where('status', '1')
				->get()->row();
			if ($cur && (float)$cur->rate > 0) {
				$cur_rate = (float)$cur->rate;
			}
		}

		if (isset($payload['cart_items']) && is_array($payload['cart_items'])) {
			foreach ($payload['cart_items'] as &$it) {
				if (is_object($it)) {
					$it->sale_price = (float)$it->sale_price * $cur_rate;
					$it->regular_price = (float)$it->regular_price * $cur_rate;
					$it->total = (float)$it->total * $cur_rate;
				} elseif (is_array($it)) {
					$it['sale_price'] = (float)$it['sale_price'] * $cur_rate;
					$it['regular_price'] = (float)$it['regular_price'] * $cur_rate;
					$it['total'] = (float)$it['total'] * $cur_rate;
				}
			}
			unset($it);
		}

		if (isset($payload['cart_summary']) && is_object($payload['cart_summary'])) {
			$payload['cart_summary']->subtotal = (float)$payload['cart_summary']->subtotal * $cur_rate;
			$payload['cart_summary']->shipping = (float)$payload['cart_summary']->shipping * $cur_rate;
			$payload['cart_summary']->additional_charges = (float)$payload['cart_summary']->additional_charges * $cur_rate;
			$payload['cart_summary']->tax = (float)$payload['cart_summary']->tax * $cur_rate;
			$payload['cart_summary']->total = (float)$payload['cart_summary']->total * $cur_rate;
			$payload['cart_summary']->grand_total = (float)$payload['cart_summary']->grand_total * $cur_rate;
		}

		if (isset($payload['cart_total'])) {
			$payload['cart_total'] = (float)$payload['cart_total'] * $cur_rate;
		}

		return api_response(['status' => 1, 'msg' => 'Checkout payload', 'data' => $payload]);
	}

	public function after_login() {
        $this->set_checkout_step('address');
    }

	public function select_address() {
		$customer = $this->session->userdata('customer');
		$user_id = $customer['login_id'] ?? 0;
		if (!$user_id) {
			return api_response(['status' => 0, 'msg' => 'Please login to continue shopping.', 'data' => []]);
		}

		$address_id = (int)$this->input->post('address_id');
		if (!$address_id) {
			return api_response(['status' => 0, 'msg' => 'Address is required.', 'data' => []]);
		}

		$address = $this->Query_model->get_data_obj('ec_shipping_address', [
			'shipping_address_id' => $address_id,
			'customer_id' => $user_id,
			'status' => '1'
		]);
		if (!$address) {
			return api_response(['status' => 0, 'msg' => 'Invalid address selected.', 'data' => []]);
		}

		$this->session->set_userdata('selected_address', $address_id);
		return api_response(['status' => 1, 'msg' => 'Address selected', 'data' => ['address_id' => $address_id]]);
	}
	
	public function payment_success() {
        echo json_encode(['status' => 1, 'msg' => 'Payment successful']);
    }

	public function get_addresses()
{
    $user_id = (int)$this->LOGIN_ID;

    if (!$user_id) {
		return api_response(['status' => 0, 'msg' => 'Please login to continue shopping.', 'data' => []]);
    }

    // Fetch the data
    $address_obj = $this->Query_model->get_data('ec_shipping_address', [
        'customer_id' => $user_id, 
        'status' => '1'
    ]);

    if ($address_obj) {
        $indexed = [];
        foreach ($address_obj as $row) {
            // FIX: Convert object to array if necessary to prevent Line 101 error
            $temp = (array)$row; 
            $indexed[$temp['shipping_address_id']] = $temp;
        }
        return api_response(['status' => 1, 'msg' => 'Addresses.', 'data' => $indexed]);
    }

    return api_response(['status' => 0, 'msg' => 'No addresses found.', 'data' => []]);
}
	public function add_address()
{
    // Use the ID from constructor to ensure we are saving to the right user
    $user_id = (int)$this->LOGIN_ID; 

    if (!$user_id) {
		return api_response(['status' => 0, 'msg' => 'Please login to continue shopping.', 'data' => []]);
    }

    $this->load->library('form_validation');
    $this->form_validation->set_rules('fullname', 'Full Name', 'required|trim');
    $this->form_validation->set_rules('address_1', 'Street Address', 'required|trim');
    $this->form_validation->set_rules('city', 'City', 'required|trim');
    $this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric');

    if ($this->form_validation->run() == FALSE) {
        return api_response(['status' => 0, 'msg' => strip_tags(validation_errors()), 'data' => []]);
    }

    $data = [
        'customer_id'  => $user_id,
        'fullname'     => $this->input->post('fullname'),
        'address_1'    => $this->input->post('address_1'),
        'address_2'    => $this->input->post('address_2'),
        'city'         => $this->input->post('city'),
        'state'        => $this->input->post('state'),
        'mobile'       => $this->input->post('mobile'),
        'postcode'     => $this->input->post('postcode'),
        // 'address_type' => $this->input->post('address_type'),
        'status'       => '1',
    ];

    $insert_id = $this->Query_model->insert_data('ec_shipping_address', $data);
    
    if ($insert_id) {
        $data['shipping_address_id'] = $insert_id;
        return api_response(['status' => 1, 'msg' => 'Address Saved!', 'data' => $data]);
    } else {
        return api_response(['status' => 0, 'msg' => 'Database error. Check your table fields.', 'data' => []]);
    }
}
	

}
