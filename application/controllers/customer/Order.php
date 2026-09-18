<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Cart_model');
		$this->config->load('custom_config');
		$this->load->helper('api');
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
			'cart_total' => (float)$final_total,
			'cart_qty' => (int)$total_qty,
			'cart_count' => (int)count($items),
		];
	}

	private function _resolve_customer_id()
	{
		$customer = $this->session->userdata('customer');
		if (!$customer) {
			return 0;
		}
		return (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
	}

	private function _product_image_url($product_id, $variation_id = 0)
	{
		$product_id = (int)$product_id;
		$variation_id = (int)$variation_id;
		if ($product_id <= 0) {
			return base_url('assets/customer/images/shop/s1.png');
		}

		// Prefer new variation images when variation_id is known
		if ($variation_id > 0) {
			$img = $this->db->get_where('product_variation_images', ['variation_id' => $variation_id], 1)->row();
			if ($img && !empty($img->image_path)) {
				return base_url('uploads/products/' . (string)$img->image_path);
			}
		}

		// If product exists in new products table, try to resolve a default variation image
		$newProduct = $this->db->get_where('products', ['id' => $product_id], 1)->row();
		if ($newProduct) {
			$imgRow = $this->db
				->select('pvi.image_path')
				->from('product_variations pv')
				->join('product_variation_images pvi', 'pvi.variation_id = pv.id', 'inner')
				->where('pv.product_id', $product_id)
				->order_by('pvi.id', 'asc')
				->limit(1)
				->get()->row();
			if ($imgRow && !empty($imgRow->image_path)) {
				return base_url('uploads/products/' . (string)$imgRow->image_path);
			}
			return base_url('assets/customer/images/shop/s1.png');
		}

		$product = $this->db->get_where('products', ['id' => $product_id])->row();
		if (!$product) {
			return base_url('assets/customer/images/shop/s1.png');
		}

		$image_name = 'default.png';
		if (!empty($product->images)) {
			$clean_image = str_replace(['[', ']', '"'], '', (string)$product->images);
			$clean_image = trim($clean_image);
			if ($clean_image !== '') {
				$image_name = $clean_image;
			}
		} else {
			$image_row = $this->db->get_where('ec_gallery', ['product_id' => $product_id], 1)->row();
			if ($image_row && !empty($image_row->file_name)) {
				$image_name = $image_row->file_name;
			}
		}

		return base_url('uploads/products/' . $image_name);
	}

	private function _item_status_to_order_status_label($item_status)
	{
		$st = (int)$item_status;
		if ($st === 10) {
			return 'delivered';
		}
		if ($st === 3) {
			return 'shipped';
		}
		if ($st === 2) {
			return 'confirmed';
		}
		if ($st === 1) {
			return 'pending';
		}
		if ($st === 17) {
			return 'cancelled';
		}
		if ($st === 5) {
			return 'cancelled';
		}
		return 'pending';
	}

	private function _compute_order_status_from_items($order_row)
	{
		if (!is_array($order_row)) {
			return $order_row;
		}
		$items = $order_row['items'] ?? [];
		if (!is_array($items) || empty($items)) {
			return $order_row;
		}
		$min = null;
		foreach ($items as $it) {
			if (!is_array($it)) {
				continue;
			}
			$st = isset($it['status']) ? (int)$it['status'] : null;
			if ($st === null) {
				continue;
			}
			if ($min === null || $st < $min) {
				$min = $st;
			}
		}
		if ($min !== null) {
			$order_row['status'] = $this->_item_status_to_order_status_label($min);
		}
		return $order_row;
	}

	private function _get_orders_with_items($user_id, $order_id = null, $for_return = false)
	{
		$this->db->from('ec_orders');
		$this->db->where('user_id', (int)$user_id);
		if ($order_id) {
			$this->db->where('id', (int)$order_id);
		}
		$this->db->order_by('date_added', 'DESC');
		$orders = $this->db->get()->result_array();
		if (!$orders) {
			return [];
		}

		foreach ($orders as &$o0) {
			if (empty($o0['status']) && isset($o0['order_status'])) {
				$os = (int)$o0['order_status'];
				$map = [
					1 => 'pending',
					2 => 'confirmed',
					3 => 'shipped',
					4 => 'delivered',
					5 => 'cancelled'
				];
				$o0['status'] = $map[$os] ?? 'pending';
			}
			if (empty($o0['status'])) {
				$o0['status'] = 'pending';
			}
		}
		unset($o0);

		$orderIds = array_map(function($o){ return (int)$o['id']; }, $orders);
		$this->db->from('ec_order_items');
		$this->db->where_in('order_id', $orderIds);
		if ($for_return) {
			// Only delivered items are returnable
			$this->db->where('status', 10);
		}
		$this->db->order_by('id', 'ASC');
		$items = $this->db->get()->result_array();

		$itemsByOrder = [];
		foreach ($items as $it) {
			$oid = (int)$it['order_id'];
			if (!isset($itemsByOrder[$oid])) {
				$itemsByOrder[$oid] = [];
			}
			$itemsByOrder[$oid][] = $it;
		}

		foreach ($orders as &$o) {
			$oid = (int)$o['id'];
			$o['items'] = $itemsByOrder[$oid] ?? [];
			foreach ($o['items'] as &$it) {
				$pid = (int)($it['product_id'] ?? 0);
				$variation_id = 0;
				if (!empty($it['options'])) {
					$opt = json_decode((string)$it['options'], true);
					if (is_array($opt) && isset($opt['variation_id'])) {
						$variation_id = (int)$opt['variation_id'];
					}
				}
				$it['image_url'] = $this->_product_image_url($pid, $variation_id);
			}
			unset($it);
			$o['items_count'] = count($o['items']);
			$o = $this->_compute_order_status_from_items($o);

			$addrId = (int)($o['address_id'] ?? 0);
			if ($addrId > 0) {
				$addr = $this->Query_model->get_data_obj('ec_shipping_address', [
					'shipping_address_id' => $addrId,
					'customer_id' => (int)$user_id,
					'status' => '1'
				]);
				$o['address'] = $addr ? (array)$addr : null;
			} else {
				$o['address'] = null;
			}
		}
		unset($o);

		return $orders;
	}

	private function _map_api_orders_list_to_legacy($api_payload)
	{
		$orders = [];
		$rows = is_array($api_payload['orders'] ?? null) ? $api_payload['orders'] : [];
		foreach ($rows as $r) {
			if (!is_array($r)) {
				continue;
			}
			$orders[] = [
				'id' => (int)($r['order_id'] ?? 0),
				'order_number' => $r['order_number'] ?? null,
				'date_added' => $r['order_date'] ?? null,
				'final_amount' => (float)($r['final_amount'] ?? $r['total_amount'] ?? 0),
				'total_amount' => (float)($r['total_amount'] ?? 0),
				'payment_status' => $r['payment_status'] ?? null,
				'payment_method' => $r['payment_method'] ?? null,
				'status' => $r['order_status'] ?? 'pending',
				'items' => [],
				'items_count' => (int)($r['items_count'] ?? 0),
				'address' => null,
			];
		}
		return $orders;
	}

	private function _map_api_order_detail_to_legacy($api_payload)
	{
		$order = is_array($api_payload['order'] ?? null) ? $api_payload['order'] : [];
		$items = is_array($api_payload['items'] ?? null) ? $api_payload['items'] : [];
		$address = $api_payload['shipping_address'] ?? null;
		if (!is_array($address)) {
			$address = null;
		}

		$out = [
			'id' => (int)($order['order_id'] ?? 0),
			'order_number' => $order['order_number'] ?? null,
			'date_added' => $order['order_date'] ?? null,
			'total_amount' => (float)($order['total_amount'] ?? 0),
			'discount_amount' => (float)($order['discount_amount'] ?? 0),
			'tax_amount' => (float)($order['tax_amount'] ?? 0),
			'shipping_amount' => (float)($order['shipping_amount'] ?? 0),
			'final_amount' => (float)($order['final_amount'] ?? 0),
			'payment_method' => $order['payment_method'] ?? null,
			'payment_status' => $order['payment_status'] ?? null,
			'status' => $order['order_status'] ?? 'pending',
			'address' => $address,
			'items' => [],
			'items_count' => 0,
		];

		foreach ($items as $it) {
			if (!is_array($it)) {
				continue;
			}
			$pid = (int)($it['product_id'] ?? 0);
			$variation_id = (int)($it['variation_id'] ?? 0);
			$out['items'][] = [
				'product_id' => $pid,
				'product_name' => (string)($it['name'] ?? ''),
				'price' => (float)($it['price'] ?? 0),
				'qty' => (int)($it['quantity'] ?? 0),
				'subtotal' => (float)($it['subtotal'] ?? 0),
				'image_url' => $this->_product_image_url($pid, $variation_id),
			];
		}
		$out['items_count'] = count($out['items']);
		return $out;
	}

    public function place_order() {
        $customer = $this->session->userdata('customer');
        if (empty($customer['login_id']) && empty($customer['customer_id'])) {
            return api_response(['status' => 0, 'msg' => 'Please login to place an order.', 'data'=>[]]);
        }

        $user_id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
        $address_id = (int)($this->input->post('address_id') ?: $this->session->userdata('selected_address'));
        $payment_method = strtoupper(trim((string)$this->input->post('payment_method')));
        $terms_accepted = (int)$this->input->post('terms_accepted');
        $comment = (string)$this->input->post('comment');

        if (!$address_id) {
            return api_response(['status' => 0, 'msg' => 'Please select a delivery address before placing the order.', 'data' => []]);
        }

        if (!$terms_accepted) {
            return api_response(['status' => 0, 'msg' => 'Please accept Terms & Conditions to continue.', 'data' => []]);
        }

        if (!$payment_method) {
            return api_response(['status' => 0, 'msg' => 'Payment method is required.', 'data' => []]);
        }

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$address = $this->Query_model->get_data_obj('ec_shipping_address', [
				'shipping_address_id' => $address_id,
				'customer_id' => $user_id,
				'status' => '1'
			]);
			if (!$address) {
				return api_response(['status' => 0, 'msg' => 'Selected address is invalid.', 'data' => []]);
			}

			// Snapshot cart for confirm view before placing order (API may clear cart on COD).
			$cart_payload = null;
			$api_cart = call_api('GET', 'cart', null, $headers);
			if ($api_cart['response'] !== null && (int)($api_cart['response']['status'] ?? 0) === 1 && is_array($api_cart['response']['data'] ?? null)) {
				$cart_payload = $this->_map_api_cart_to_legacy_payload($api_cart['response']['data']);
			}
			if ($cart_payload === null) {
				$cart_info = $this->Cart_model->checkout_items();
				$summary = $cart_info['cart_summary'] ?? (object)[];
				$cart_payload = [
					'cart_items' => $cart_info['cart_items'] ?? [],
					'cart_summary' => $summary,
					'cart_count' => count($cart_info['cart_items'] ?? []),
					'cart_qty' => (int)($summary->total_qty ?? 0),
					'cart_total' => (float)($summary->grand_total ?? 0),
				];
			}
			if (empty($cart_payload['cart_items'])) {
				return api_response(['status' => 0, 'msg' => 'Cart is empty.', 'data' => []]);
			}

			$api_place = call_api('POST', 'checkout/place-order', [
				'address_id' => $address_id,
				'payment_method' => $payment_method,
			], $headers);
			if ($api_place['response'] !== null && (int)($api_place['response']['status'] ?? 0) === 1) {
				$api_data = $api_place['response']['data'] ?? [];
				$order_id = (int)($api_data['order_id'] ?? 0);
				$order_number = (string)($api_data['order_number'] ?? '');
				$order_date = date('Y-m-d H:i:s');
				if ($order_id > 0) {
					$order_obj = $this->Query_model->get_data_obj('ec_orders', ['id' => $order_id], [], ['length' => 1]);
					if ($order_obj && !empty($order_obj->date_added)) {
						$order_date = (string)$order_obj->date_added;
					}
				}

				$final_amount = (float)($api_data['final_amount'] ?? 0);
				if ($final_amount > 0 && isset($cart_payload['cart_summary']) && is_object($cart_payload['cart_summary'])) {
					$cart_payload['cart_summary']->grand_total = $final_amount;
					$cart_payload['cart_total'] = $final_amount;
				}

				return api_response([
					'status' => 1,
					'msg' => 'Order placed successfully',
					'data' => [
						'order_id' => $order_id,
						'order_number' => $order_number !== '' ? $order_number : $order_id,
						'order_date' => $order_date,
						'payment_method' => $payment_method,
						'address' => $address,
						'cart_summary' => $cart_payload['cart_summary'] ?? (object)[],
						'cart_items' => $cart_payload['cart_items'] ?? [],
					]
				]);
			}
			// If API fails, continue to legacy checkout below.
		}

		if ($payment_method !== 'COD') {
			return api_response(['status' => 0, 'msg' => 'Only Cash on Delivery is available right now.', 'data' => []]);
		}

        $address = $this->Query_model->get_data_obj('ec_shipping_address', [
            'shipping_address_id' => $address_id,
            'customer_id' => $user_id,
            'status' => '1'
        ]);
        if (!$address) {
            return api_response(['status' => 0, 'msg' => 'Selected address is invalid.', 'data' => []]);
        }

        $buy_now = $this->session->userdata('buy_now_product');
        $is_buy_now = ($buy_now && is_array($buy_now) && !empty($buy_now['product_id']));
        $cart_info = $this->Cart_model->checkout_items();
        $items = $cart_info['cart_items'] ?? [];
        $summary = $cart_info['cart_summary'] ?? (object)[];
        if (empty($items)) {
            return api_response(['status' => 0, 'msg' => 'Cart is empty.', 'data' => []]);
        }

        $subtotal = (float)($summary->subtotal ?? 0);
        $shipping = (float)($summary->shipping ?? 0);
        $tax = 0;
        $discount = 0;
        $final_amount = (float)($summary->grand_total ?? ($subtotal + $shipping));

        $this->db->trans_begin();
        try {
            $order_data = [
                'order_number' => 'ORD' . date('Ymd') . strtoupper(substr(md5(uniqid((string)$user_id, true)), 0, 6)),
                'user_id' => $user_id,
                'address_id' => $address_id,
                'payment_method' => 'COD',
                'payment_status' => 'pending',
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'shipping_amount' => $shipping,
                'final_amount' => $final_amount,
                'comment' => $comment,
                'status' => 'pending',
                'order_status' => 1
            ];

            $this->db->insert('ec_orders', $order_data);
            $order_id = (int)$this->db->insert_id();
            if (!$order_id) {
                throw new Exception('Failed to create order.');
            }

            foreach ($items as $item) {
                $product_id = (int)$item->product_id;
                $qty = (int)$item->quantity;
                $price = (float)$item->sale_price;
                $row_subtotal = (float)$item->total;

                $vendor_login_id = 0;
                $product_row = $this->db->get_where('products', ['id' => $product_id])->row();
                if ($product_row && isset($product_row->vendor_id)) {
                    $vendor_login_id = (int)$product_row->vendor_id;
                }

                $this->db->insert('ec_order_items', [
                    'login_id' => $vendor_login_id,
                    'status' => 1,
                    'order_id' => $order_id,
                    'product_id' => $product_id,
                    'product_name' => (string)$item->post_title,
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $row_subtotal,
                    'options' => null
                ]);
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('DB transaction failed.');
            }

            $this->db->trans_commit();

            // Clear checkout state
            $this->session->unset_userdata('selected_address');
            if ($is_buy_now) {
                $this->Cart_model->clear_buy_now();
            } else {
                $this->session->unset_userdata('cart');
                $this->db->where('user_id', $user_id)->delete('ec_cart');
            }

            $order_obj = $this->Query_model->get_data_obj('ec_orders', ['id' => $order_id]);
            $order_date = $order_obj->date_added ?? date('Y-m-d H:i:s');

            return api_response([
                'status' => 1,
                'msg' => 'Order placed successfully',
				'data' => [
					'order_id' => $order_id,
					'order_number' => $order_data['order_number'],
					'order_date' => $order_date,
					'payment_method' => 'COD',
					'address' => $address,
					'cart_summary' => $summary,
					'cart_items' => $items
				]
            ]);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return api_response(['status' => 0, 'msg' => $e->getMessage(), 'data' => []]);
        }
    }

    public function list() {
		$user_id = $this->_resolve_customer_id();
		if (!$user_id) {
			return api_response(['status' => 0, 'msg' => 'Not logged in', 'data'=>[]]);
		}

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$api_list = call_api('GET', 'orders', null, $headers);
			if ($api_list['response'] !== null && (int)($api_list['response']['status'] ?? 0) === 1 && is_array($api_list['response']['data'] ?? null)) {
				$orders = $this->_map_api_orders_list_to_legacy($api_list['response']['data']);
				if (!empty($orders)) {
					return api_response(['status' => 1, 'msg' => 'order list', 'data' => $orders]);
				}
			}
		}

		$orders = $this->_get_orders_with_items($user_id);
		if (!empty($orders)) {
			api_response(['status' => 1, 'msg' => 'order list', 'data' => $orders]);
		} else {
			api_response(["status" => 0, "msg" => "No orders found.",'data' => []]);
		}
    }

	public function get_order_by_orderid()
	{
		$user_id = $this->_resolve_customer_id();
		$order_id = (int)$this->input->post('order_id');
		$for_return = (int)$this->input->post('for_return') === 1;
		if (!$user_id) {
			return api_response(['status' => 0, 'msg' => 'Not logged in', 'data'=>[]]);
		}
		if (!$order_id) {
			return api_response(['status' => 0, 'msg' => 'order_id is required', 'data'=>[]]);
		}

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$api_show = call_api('GET', 'orders/' . $order_id, null, $headers);
			if ($api_show['response'] !== null && (int)($api_show['response']['status'] ?? 0) === 1 && is_array($api_show['response']['data'] ?? null)) {
				$payload = $this->_map_api_order_detail_to_legacy($api_show['response']['data']);
				if (!empty($payload['id'])) {
					return api_response(['status' => 1, 'msg' => 'Order Dtl', 'data' => $payload]);
				}
			}
		}

		$orders = $this->_get_orders_with_items($user_id, $order_id, $for_return);
		if (!empty($orders)) {
			api_response(['status' => 1, 'msg' => 'Order Dtl', 'data' => $orders[0]]);
		} else {
			api_response(["status" => 0, "msg" => "No orders found.",'data'=>[]]);
		}
		
	}

	public function payment_verify()
	{
		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if (!$use_api || empty($headers)) {
			return api_response(['status' => 0, 'msg' => 'Payment verification is not available.', 'data' => []]);
		}

		$payload = $this->input->post(NULL, true) ?: [];
		$order_id = (int)($payload['order_id'] ?? 0);
		if ($order_id <= 0) {
			return api_response(['status' => 0, 'msg' => 'order_id is required', 'data' => []]);
		}

		$api_verify = call_api('POST', 'checkout/payment-verify', $payload, $headers);
		if ($api_verify['response'] !== null && (int)($api_verify['response']['status'] ?? 0) === 1) {
			return api_response(['status' => 1, 'msg' => 'Payment verified', 'data' => $api_verify['response']['data'] ?? []]);
		}

		$msg = 'Payment verification failed.';
		if ($api_verify['response'] !== null) {
			$msg = (string)($api_verify['response']['msg'] ?? $api_verify['response']['message'] ?? $msg);
		}
		return api_response(['status' => 0, 'msg' => $msg, 'data' => []]);
	}

	public function download_invoice()
	{
		$user_id = $this->_resolve_customer_id();
		$order_id = (int)$this->input->get('order_id');
		if (!$user_id) {
			show_error('Not logged in', 401);
			return;
		}
		if (!$order_id) {
			show_error('order_id is required', 400);
			return;
		}

		$orders = $this->_get_orders_with_items($user_id, $order_id);
		if (empty($orders)) {
			show_error('Order not found', 404);
			return;
		}
		$order = $orders[0];

		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator('Cheru');
		$pdf->SetAuthor('Cheru');
		$pdf->SetTitle('Invoice ' . ($order['order_number'] ?? $order_id));
		$pdf->SetMargins(12, 12, 12);
		$pdf->SetAutoPageBreak(true, 12);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 10);

		$orderNo = htmlspecialchars((string)($order['order_number'] ?? $order_id));
		$orderDate = htmlspecialchars((string)($order['date_added'] ?? ''));
		$status = htmlspecialchars((string)($order['status'] ?? ''));
		$payMethod = htmlspecialchars((string)($order['payment_method'] ?? 'COD'));
		$payStatus = htmlspecialchars((string)($order['payment_status'] ?? 'pending'));
		$addr = $order['address'] ?? null;

		$addrHtml = 'N/A';
		if (is_array($addr)) {
			$addrHtml = htmlspecialchars(trim((string)($addr['fullname'] ?? ''))) . '<br>'
				. htmlspecialchars(trim((string)($addr['address_1'] ?? '')))
				. (!empty($addr['address_2']) ? '<br>' . htmlspecialchars(trim((string)$addr['address_2'])) : '')
				. '<br>' . htmlspecialchars(trim((string)($addr['city'] ?? ''))) . ', ' . htmlspecialchars(trim((string)($addr['state'] ?? '')))
				. ' - ' . htmlspecialchars(trim((string)($addr['postcode'] ?? '')))
				. '<br>Phone: ' . htmlspecialchars(trim((string)($addr['mobile'] ?? '')));
		}

		$symbol = '$';
		$cur_rate = 1.0;
		$cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
		if ($cc_id > 0) {
			$cur = $this->db
				->select('currency_id, symbol, rate')
				->from('ec_currency')
				->where('currency_id', $cc_id)
				->where('status', '1')
				->get()->row();
			if ($cur) {
				$symbol = (string)$cur->symbol;
				if ((float)$cur->rate > 0) {
					$cur_rate = (float)$cur->rate;
				}
			}
		}

		$items = $order['items'] ?? [];
		$itemsRows = '';
		$subtotal = 0;
		foreach ($items as $it) {
			$name = htmlspecialchars((string)($it['product_name'] ?? ''));
			$qty = (int)($it['qty'] ?? 0);
			$price = (float)($it['price'] ?? 0);
			$rowSub = (float)($it['subtotal'] ?? ($qty * $price));
			$subtotal += $rowSub;
			$itemsRows .= '<tr>'
				. '<td style="padding:6px;border:1px solid #ddd;">' . $name . '</td>'
				. '<td style="padding:6px;border:1px solid #ddd;text-align:right;">' . $qty . '</td>'
				. '<td style="padding:6px;border:1px solid #ddd;text-align:right;">' . $symbol . ' ' . number_format($price * $cur_rate, 2) . '</td>'
				. '<td style="padding:6px;border:1px solid #ddd;text-align:right;">' . $symbol . ' ' . number_format($rowSub * $cur_rate, 2) . '</td>'
				. '</tr>';
		}
		$shipping = (float)($order['shipping_amount'] ?? 0);
		$discount = (float)($order['discount_amount'] ?? 0);
		$final = (float)($order['final_amount'] ?? ($subtotal + $shipping - $discount));

		$html = '
			<h2 style="margin:0 0 6px 0;">Invoice</h2>
			<table cellpadding="0" cellspacing="0" style="width:100%;">
				<tr>
					<td style="width:60%;vertical-align:top;">
						<b>Order:</b> ' . $orderNo . '<br>
						<b>Date:</b> ' . $orderDate . '<br>
						<b>Status:</b> ' . $status . '<br>
						<b>Payment:</b> ' . $payMethod . ' (' . $payStatus . ')
					</td>
					<td style="width:40%;vertical-align:top;">
						<b>Shipping Address</b><br>' . $addrHtml . '
					</td>
				</tr>
			</table>
			<br>
			<h3 style="margin:10px 0 6px 0;">Items</h3>
			<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">
				<thead>
					<tr>
						<th style="padding:6px;border:1px solid #ddd;text-align:left;">Item</th>
						<th style="padding:6px;border:1px solid #ddd;text-align:right;">Qty</th>
						<th style="padding:6px;border:1px solid #ddd;text-align:right;">Price</th>
						<th style="padding:6px;border:1px solid #ddd;text-align:right;">Subtotal</th>
					</tr>
				</thead>
				<tbody>
					' . $itemsRows . '
				</tbody>
			</table>
			<br>
			<table cellpadding="0" cellspacing="0" style="width:100%;">
				<tr>
					<td style="width:60%;"></td>
					<td style="width:40%;">
						<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">
							<tr><td style="padding:6px;border:1px solid #ddd;">Subtotal</td><td style="padding:6px;border:1px solid #ddd;text-align:right;">' . $symbol . ' ' . number_format($subtotal * $cur_rate, 2) . '</td></tr>
							<tr><td style="padding:6px;border:1px solid #ddd;">Shipping</td><td style="padding:6px;border:1px solid #ddd;text-align:right;">' . $symbol . ' ' . number_format($shipping * $cur_rate, 2) . '</td></tr>
							<tr><td style="padding:6px;border:1px solid #ddd;">Discount</td><td style="padding:6px;border:1px solid #ddd;text-align:right;">' . $symbol . ' ' . number_format($discount * $cur_rate, 2) . '</td></tr>
							<tr><td style="padding:6px;border:1px solid #ddd;"><b>Total</b></td><td style="padding:6px;border:1px solid #ddd;text-align:right;"><b>' . $symbol . ' ' . number_format($final * $cur_rate, 2) . '</b></td></tr>
						</table>
					</td>
				</tr>
			</table>
		';

		$pdf->writeHTML($html, true, false, true, false, '');
		$fileName = 'invoice_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $orderNo) . '.pdf';
		$pdf->Output($fileName, 'D');
		exit;
	}

	public function cancel_order()
	{
		$user_id = $this->_resolve_customer_id();
		if (!$user_id) {
			return api_response(['status' => 0, 'msg' => 'Not logged in', 'data'=>[]]);
		}
		$order_id = (int)$this->input->post('order_id');
		$reason = trim((string)$this->input->post('reason'));
		if (!$order_id) {
			return api_response(['status' => 0, 'msg' => 'order_id is required', 'data'=>[]]);
		}
		if ($reason === '') {
			return api_response(['status' => 0, 'msg' => 'Cancellation reason is required', 'data'=>[]]);
		}

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$api_cancel = call_api('POST', 'orders/cancel', [
				'order_id' => $order_id,
			], $headers);
			if ($api_cancel['response'] !== null && (int)($api_cancel['response']['status'] ?? 0) === 1) {
				return api_response(['status' => 1, 'msg' => 'Order cancelled successfully', 'data' => $api_cancel['response']['data'] ?? []]);
			}
		}

		$order = $this->Query_model->get_data_obj('ec_orders', ['id' => $order_id, 'user_id' => $user_id]);
		if (!$order) {
			return api_response(['status' => 0, 'msg' => 'Order not found', 'data'=>[]]);
		}

		$allowed = ['pending', 'confirmed'];
		if (!in_array((string)$order->status, $allowed, true)) {
			return api_response(['status' => 0, 'msg' => 'Order cannot be cancelled at this stage', 'data'=>[]]);
		}

		$comment = (string)$order->comment;
		$prefix = '[CANCELLED BY CUSTOMER] ';
		$newComment = $prefix . $reason;
		if ($comment !== '') {
			$newComment = $comment . "\n" . $newComment;
		}

		$updateOrder = [
			'status' => 'cancelled',
			'comment' => $newComment
		];
		if ($this->db->field_exists('cancelled_by', 'ec_orders')) {
			$updateOrder['cancelled_by'] = 'customer';
		}

		$this->db->trans_begin();
		$updated = $this->Query_model->update_data('ec_orders', $updateOrder, ['id' => $order_id, 'user_id' => $user_id]);
		// Keep order items consistent for vendor/admin side lists
		$this->Query_model->update_data('ec_order_items', [
			'status' => 17
		], ['order_id' => $order_id]);
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$updated = 0;
		} else {
			$this->db->trans_commit();
		}

		if ($updated) {
			api_response(['status' => 1, 'msg' => 'Order cancelled successfully', 'data' => []]);
		} else {
			api_response(['status' => 0, 'msg' => 'Failed to cancel order', 'data' => []]);
		}
	}
	

}
