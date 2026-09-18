<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Product_model');
        $this->load->model('Cart_model');
		$this->config->load('custom_config');
		$this->load->helper('api');
        $this->load->helper('text');		
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        
		$cart_payload = $this->_get_cart_payload();
		$this->cart_data = array(
			'cart_items' => $cart_payload['cart_items'],
			'cart_summary' => $cart_payload['cart_summary'],
			'cart_count' => (int)$cart_payload['cart_count'],
			'cart_qty' => (int)$cart_payload['cart_qty'],
			'cart_total' => (float)$cart_payload['cart_total'],
		);
    }

	private function _get_customer_access_token()
	{
		$customer = $this->session->userdata('customer');
		if (is_array($customer) && !empty($customer['access_token'])) {
			return (string)$customer['access_token'];
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

	private function _build_cart_item_id($product_id, $attribute_items)
	{
		$product_id = (int)$product_id;
		$attr = is_array($attribute_items) ? $attribute_items : [];
		return $product_id . '_' . md5(json_encode($attr));
	}

	private function _map_api_cart_to_legacy_payload($api_data)
	{
		$api_items = is_array($api_data['items'] ?? null) ? $api_data['items'] : [];
		$cart_total = (float)($api_data['cart_total'] ?? 0);
		$final_total = (float)($api_data['final_total'] ?? $cart_total);

		$items = [];
		$subtotal = 0;
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
			$subtotal += $row_total;
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
				$image_query = $this->db->get_where('ec_gallery', array('product_id' => $product_id), 1);
				$image_row = $image_query->row();
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

		return array(
			'cart_items' => $items,
			'cart_summary' => $summary,
			'cart_count' => count($items),
			'cart_qty' => $total_qty,
			'cart_total' => $final_total,
		);
	}

	private function _get_cart_payload($force_local = false)
	{
		$use_api = (bool)$this->config->item('use_api');
		if (!$force_local && $use_api) {
			$headers = $this->_build_api_headers();
			if (!empty($headers)) {
				$api_cart = call_api('GET', 'cart', null, $headers);
				if ($api_cart['response'] !== null && (int)($api_cart['response']['status'] ?? 0) === 1 && is_array($api_cart['response']['data'] ?? null)) {
					return $this->_map_api_cart_to_legacy_payload($api_cart['response']['data']);
				}
			}
		}

		$cart_info = $this->Cart_model->cart_items();
		$summary = $cart_info['cart_summary'] ?? (object)[];
		$unique_count = count($cart_info['cart_items'] ?? []);
		return array(
			'cart_items' => $cart_info['cart_items'],
			'cart_summary' => $cart_info['cart_summary'],
			'cart_count' => (int)$unique_count,
			'cart_qty' => (int)($summary->total_qty ?? 0),
			'cart_total' => (float)($summary->grand_total ?? 0),
		);
	}

    public function index($arg=null) 
    {
        $this->session->unset_userdata('order_now_btn');
		$this->session->unset_userdata('buy_now_product');
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
		$data['homepage'] = 1;

        $cur = current_currency();

        $currency = get_currency($cur);
        $rate = $currency->rate;
        $symbol = $currency->symbol;

		$cart_payload = $this->_get_cart_payload();
		$cart_items   = $cart_payload['cart_items'];
		$cart_summary = $cart_payload['cart_summary'];

	   $shipping_address = $this->Query_model->get_data_obj('ec_shipping_address', array('customer_id'=> $this->LOGIN_ID, 'default_address'=> '1'));

        $data['cart_summary'] = $cart_summary;
        $data['cart_items'] = $cart_items;
		$data['shipping_address'] = $shipping_address;
        $data['symbol'] = $symbol;
        $data['iso_code'] = $currency->iso_code;
        $data['supplier_detail'] = array(); // $supplier_detail from original code
        $data['customer_id'] = $this->LOGIN_ID;
        
		// Add cart count and total for header display
		$data['cart_count'] = (int)$cart_payload['cart_count'];
		$data['cart_qty'] = (int)$cart_payload['cart_qty'];
		$data['cart_total'] = (float)$cart_payload['cart_total'];
       
        if(is_api()){
            unset($data['csrf']);
            unset($data['breadcrumbs']);
            unset($data['TYPE']);

			api_response(array('status' => '1', 'msg' => 'success', 'data' => [$data]));
        }
		else{
			// IMPORTANT: Web cart UI needs variation attribute values (e.g. Gold, 24GB, 1TB).
			// The API cart payload may not include variation/attribute selections, so use local
			// session cart as the source of truth for rendering the cart page.
			$cart_payload = $this->_get_cart_payload(true);
			$data['cart_summary'] = $cart_payload['cart_summary'];
			$data['cart_items'] = $cart_payload['cart_items'];
			$data['cart_count'] = (int)$cart_payload['cart_count'];
			$data['cart_qty'] = (int)$cart_payload['cart_qty'];
			$data['cart_total'] = (float)$cart_payload['cart_total'];

            $this->load->template("$this->TYPE/cart_detail", $data); 
        }
    }

    private function normalize_attribute_items($attribute_item)
    {
        if (empty($attribute_item)) {
            return [];
        }

        if (is_array($attribute_item)) {
            $attribute_items = $attribute_item;
        } else {
            $raw = trim((string)$attribute_item);

            $decoded = null;
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
            }

            if (is_array($decoded)) {
                $attribute_items = $decoded;
            } else {
                // Handle comma-separated strings like "1,2,3" or "1"
                $attribute_items = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            }
        }

        // Normalize to ints/strings and keep stable ordering
        $attribute_items = array_values(array_filter($attribute_items, function ($v) {
            return $v !== null && $v !== '';
        }));

        // Convert numeric strings to ints so JSON encoding produces the same cart_key for add/update/delete
        $attribute_items = array_map(function ($v) {
            if (is_int($v)) {
                return $v;
            }
            if (is_string($v)) {
                $s = trim($v);
                if ($s !== '' && ctype_digit($s)) {
                    return (int)$s;
                }
                return $s;
            }
            if (is_float($v) && (int)$v == $v) {
                return (int)$v;
            }
            return $v;
        }, $attribute_items);

        if (!empty($attribute_items)) {
            sort($attribute_items);
        }

        return $attribute_items;
    }

    public function ajax_add_to_cart()
{
    $wishlist_id        = $this->input->post('wishlist_id');
    $product_id         = $this->input->post('product_id');
	$variation_id       = (int)$this->input->post('variation_id');
    $vendor_id          = $this->input->post('vendor_id');
    $quantity           = ($this->input->post('quantity')) ? $this->input->post('quantity') : 1;
    $attribute_item     = $this->input->post('attribute_item');
    $action             = $this->input->post('action');
    $order_now          = $this->input->post('btn_type');
    $from_order_now     = 0;

    // 1. Manage "Order Now" session state
    if(!$order_now) {
        $this->session->unset_userdata('order_now_btn');
    }
    if($this->session->userdata('order_now_btn') && count($this->session->userdata('order_now_btn'))) {
        $from_order_now = 1;
        $order_now = 'order_now';
    }

    // 2. Default action is 'add'
    if(!$action){
        $action = 'add';
    }

    // 3. Handle Product Attributes (Variations)
    $attribute_items = $this->normalize_attribute_items($attribute_item);

	// Variation stock validation (new flow; legacy remains unchanged)
	if ((int)$variation_id > 0) {
		$variation_row = $this->db->get_where('product_variations', ['id' => (int)$variation_id], 1)->row();
		if (!$variation_row) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 0, 'message' => 'Variation not found']));
			return;
		}

		// IMPORTANT: Some variation add-to-cart flows only submit variation_id.
		// In that case, derive attribute_item IDs from variation attr_json so they are persisted
		// in ec_cart.options and can be displayed later (e.g. Gold, 24GB, 1TB).
		if (empty($attribute_items)) {
			$raw = isset($variation_row->attr_json) ? (string)$variation_row->attr_json : '';
			$decoded = [];
			if (trim($raw) !== '') {
				$tmp = json_decode($raw, true);
				if (is_array($tmp)) {
					$decoded = $tmp;
				}
			}

			$vals = [];
			if (is_array($decoded) && $decoded) {
				$is_assoc = array_keys($decoded) !== range(0, count($decoded) - 1);
				if ($is_assoc) {
					foreach ($decoded as $k => $v) {
						if (is_scalar($v)) {
							$vals[] = trim((string)$v);
						}
					}
				} else {
					foreach ($decoded as $pair) {
						if (is_array($pair) && isset($pair['value']) && is_scalar($pair['value'])) {
							$vals[] = trim((string)$pair['value']);
						}
					}
				}
			}

			$ids = [];
			$names = [];
			foreach ($vals as $v) {
				if ($v === '') continue;
				if (ctype_digit($v)) {
					$iv = (int)$v;
					if ($iv > 0) $ids[] = $iv;
				} else {
					$names[] = $v;
				}
			}
			$ids = array_values(array_unique(array_filter($ids)));
			if (empty($ids) && !empty($names)) {
				$names = array_values(array_unique(array_filter(array_map('trim', $names), function ($x) { return $x !== ''; })));
				if (!empty($names)) {
					$rows = $this->db
						->select('attribute_item_id')
						->from('ec_attribute_item')
						->where_in('name', $names)
						->get()->result();
					foreach ($rows as $r) {
						$rid = (int)($r->attribute_item_id ?? 0);
						if ($rid > 0) $ids[] = $rid;
					}
					$ids = array_values(array_unique(array_filter($ids)));
				}
			}
			if (!empty($ids)) {
				sort($ids);
				$attribute_items = $ids;
			}
		}
		$stock = isset($variation_row->stock) ? (int)$variation_row->stock : 0;
		$qty_i = (int)$quantity;
		if ($qty_i <= 0) {
			$qty_i = 1;
		}
		if ($stock <= 0) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 0, 'message' => 'OUT OF STOCK']));
			return;
		}
		if ($qty_i > $stock) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['status' => 0, 'message' => 'You can not buy more then ' . $stock . ' quantity']));
			return;
		}
	}

    $args = array(
        'product_id' => $product_id, 
		'variation_id' => $variation_id,
        'quantity' => $quantity, 
        'attribute_item' => $attribute_items, 
        'action' => $action, 
        'vendor_id' => $vendor_id, 
        'btn_type' => $order_now, 
        'from_order_now' => $from_order_now
    );

	$use_api = (bool)$this->config->item('use_api');
	$token_headers = $this->_build_api_headers();
	$api_used = false;
	$response = null;

	if ($use_api && !empty($token_headers)) {
		$api_used = true;
		if ($action == 'delete') {
			$api_resp = call_api('POST', 'cart/remove', [
				'product_id' => (int)$product_id,
				'variation_id' => (int)$variation_id,
				'attribute_item' => $attribute_items,
			], $token_headers);
			if ($api_resp['response'] !== null && (int)($api_resp['response']['status'] ?? 0) === 1) {
				$response = ['status' => 1];
			} else {
				$response = ['status' => 0, 'message' => 'Unable to remove item'];
			}
		} else {
			$api_resp = call_api('POST', 'cart/add', [
				'product_id' => (int)$product_id,
				'variation_id' => (int)$variation_id,
				'attribute_item' => $attribute_items,
				'quantity' => (int)$quantity,
			], $token_headers);
			if ($api_resp['response'] !== null && (int)($api_resp['response']['status'] ?? 0) === 1) {
				$response = ['status' => 1];
			} else {
				$response = ['status' => 0, 'message' => 'Unable to add item'];
			}
		}
	}

	if (!$api_used || !isset($response['status']) || (int)$response['status'] !== 1) {
		if ($action == 'delete') {
			$response = $this->Cart_model->delete_to_cart($args);
		} else {
			$response = $this->Cart_model->add_to_cart($args);
			if (isset($response['status']) && $response['status'] == 1 && $wishlist_id) {
				$this->Query_model->update_data('ec_wishlist', array('status' => '2'), array('product_id' => $product_id, 'wishlist_id' => $wishlist_id, 'customer_id' => $this->LOGIN_ID));
			}
		}
	}

	if(isset($response['status']) && (int)$response['status'] == 1) {
		$cart_payload = $this->_get_cart_payload($variation_id > 0);
		$summary = $cart_payload['cart_summary'] ?? (object)[];
		$response['cart_count'] = (int)($cart_payload['cart_count'] ?? 0);
		$response['cart_qty'] = (int)($cart_payload['cart_qty'] ?? 0);
		$response['total_price'] = number_format((float)($cart_payload['cart_total'] ?? 0), 2);
		$response['subtotal'] = number_format((float)($summary->subtotal ?? 0), 2);
		$response['shipping'] = number_format((float)($summary->shipping ?? 0), 2);
		$response['grand_total'] = number_format((float)($cart_payload['cart_total'] ?? 0), 2);
		$response['message'] = ($action == 'delete') ? "Item removed from cart successfully!" : "Item added to cart successfully!";
	}

	// 6. Return the full response as JSON
	$this->output
		->set_content_type('application/json')
		->set_output(json_encode($response));
	return;
	}

    public function order_now_process($args)
    {
        $post_data      = $args['post_data'];
        $wishlist_id    = isset($post_data['wishlist_id']) ? $post_data['wishlist_id'] : '';
        $product_id     = $post_data['product_id'];
        $vendor_id      = $post_data['vendor_id'];
        $quantity       = isset($post_data['quantity']) ? $post_data['quantity'] : 1;
        $attribute_item = $post_data['attribute_item'];
        $action         = $post_data['action'];

        $args = array('product_id' => $product_id, 'quantity' => $quantity, 'attribute_item' => $attribute_items, 'action' => $action, 'vendor_id' => $vendor_id, 'btn_type'=> 'order_now');
        $response = $this->Cart_model->add_to_cart($args);
        if(isset($response['status']) && $response['status'] == 1 && $wishlist_id)
        {
            $this->Query_model->update_data('ec_wishlist',array('status' => '2'), array('product_id' => $product_id, 'wishlist_id' => $wishlist_id, 'customer_id' => $this->LOGIN_ID));
        }   
 
        api_response(array('status'=>1, 'msg'=>'Success', 'data'=>$response));
    }
 
    public function ajax_delete_to_cart()
    {
        $product_id         = $this->input->post('product_id');
        $variation_id       = (int)$this->input->post('variation_id');
        $attribute_item     = $this->input->post('attribute_item');

        $attribute_items = $this->normalize_attribute_items($attribute_item);
        $args = array('product_id' => $product_id, 'variation_id' => $variation_id, 'attribute_item' => $attribute_items);
		$use_api = (bool)$this->config->item('use_api');
		$token_headers = $this->_build_api_headers();
		$api_used = false;
		$response = null;
		if ($use_api && !empty($token_headers)) {
			$api_used = true;
			$api_resp = call_api('POST', 'cart/remove', [
				'product_id' => (int)$product_id,
				'variation_id' => (int)$variation_id,
				'attribute_item' => $attribute_items,
			], $token_headers);
			if ($api_resp['response'] !== null && (int)($api_resp['response']['status'] ?? 0) === 1) {
				$response = ['status' => 1];
			}
		}
		if (!$api_used || !isset($response['status']) || (int)$response['status'] !== 1) {
			$response = $this->Cart_model->delete_to_cart($args);
		}
        
        // ENHANCEMENT: Get updated header data for AJAX
        if(isset($response['status']) && $response['status'] == 1) {
			$cart_payload = $this->_get_cart_payload($variation_id > 0);
			$summary = $cart_payload['cart_summary'] ?? (object)[];
            
			$response['cart_count'] = (int)($cart_payload['cart_count'] ?? 0);
			$response['cart_qty'] = (int)($cart_payload['cart_qty'] ?? 0);
			$response['total_price'] = number_format((float)($cart_payload['cart_total'] ?? 0), 2);
            $response['subtotal'] = number_format((float)($summary->subtotal ?? 0), 2);
            $response['shipping'] = number_format((float)($summary->shipping ?? 0), 2);
			$response['grand_total'] = number_format((float)($cart_payload['cart_total'] ?? 0), 2);
            $response['message'] = "Item removed from cart successfully!";
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    } 


    public function ajax_update_quantity()
    {
        $product_id         = $this->input->post('product_id');
        $variation_id       = (int)$this->input->post('variation_id');
        $quantity           = $this->input->post('quantity');
        $current_quantity   = (int)$this->input->post('current_quantity');
        $attribute_item     = $this->input->post('attribute_item');
        $action             = $this->input->post('action'); // 'increase' or 'decrease'

        $attribute_items = $this->normalize_attribute_items($attribute_item);

		// IMPORTANT: Variation cart updates are stored in the local session cart.
		// When API cart mode is enabled, the remote cart may not reflect variation changes,
		// which would cause qty to "stick" (e.g. always 2) and UI to revert.
		$cart_payload = $this->_get_cart_payload($variation_id > 0);
		$current_qty = 0;
		foreach(($cart_payload['cart_items'] ?? []) as $item) {
			$item_attr = $this->normalize_attribute_items($item->attribute_item_id ?? []);
			$item_vid = isset($item->variation_id) ? (int)$item->variation_id : 0;
			if ($variation_id > 0) {
				if ($item_vid === (int)$variation_id) {
					$current_qty = (int)$item->quantity;
					break;
				}
			} else {
				if($item->product_id == $product_id && json_encode($item_attr) === json_encode($attribute_items)) {
					$current_qty = (int)$item->quantity;
					break;
				}
			}
		}

		// Fallback: if we couldn't match an item (common when variation_id/attrs are inconsistent),
		// trust the UI-provided current_quantity so +/- can still progress correctly.
		if ($current_qty <= 0 && $current_quantity > 0) {
			$current_qty = (int)$current_quantity;
		}

        // Calculate new quantity based on action
        if($action == 'increase') {
            $new_quantity = $current_qty + 1;
        } elseif($action == 'decrease') {
            $new_quantity = max(1, $current_qty - 1); // Minimum 1
        } else {
            $new_quantity = (int)$quantity; // Direct set
        }

        $args = array(
            'product_id' => $product_id, 
			'variation_id' => $variation_id,
            'quantity' => $new_quantity, 
            'attribute_item' => $attribute_items, 
            'action' => 'update'
        );
        
		$use_api = (bool)$this->config->item('use_api');
		$token_headers = $this->_build_api_headers();
		$api_used = false;
		$response = null;
		if ($variation_id <= 0 && $use_api && !empty($token_headers)) {
			$api_used = true;
			$cart_item_id = $this->_build_cart_item_id($product_id, $attribute_items);
			$api_resp = call_api('PATCH', 'cart/update', ['cart_item_id' => $cart_item_id, 'quantity' => (int)$new_quantity], $token_headers);
			if ($api_resp['response'] !== null && (int)($api_resp['response']['status'] ?? 0) === 1) {
				$response = ['status' => 1];
			}
		}
		if (!$api_used || !isset($response['status']) || (int)$response['status'] !== 1) {
			$response = $this->Cart_model->add_to_cart($args);
		}
        
        // ENHANCEMENT: Get updated header data for AJAX
        if(isset($response['status']) && $response['status'] == 1) {
			$cart_payload = $this->_get_cart_payload($variation_id > 0);
			$summary = $cart_payload['cart_summary'] ?? (object)[];
            
			$response['cart_count'] = (int)($cart_payload['cart_count'] ?? 0);
			$response['cart_qty'] = (int)($cart_payload['cart_qty'] ?? 0);
			$response['total_price'] = number_format((float)($cart_payload['cart_total'] ?? 0), 2);
            $response['subtotal'] = number_format((float)($summary->subtotal ?? 0), 2);
            $response['shipping'] = number_format((float)($summary->shipping ?? 0), 2);
			$response['grand_total'] = number_format((float)($cart_payload['cart_total'] ?? 0), 2);
            $response['new_quantity'] = $new_quantity;
            $response['message'] = "Cart updated successfully!";
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

	public function ajax_cart_payload()
	{
		// IMPORTANT: The local session cart is the source of truth for variation items.
		// If API cart is enabled, the remote cart may not include/update variations,
		// which can cause UI refreshes to revert quantities.
		$cart_payload = $this->_get_cart_payload(true);
		$payload = [
			'cart_items' => $cart_payload['cart_items'] ?? [],
			'cart_summary' => $cart_payload['cart_summary'] ?? (object)[],
			'cart_count' => (int)($cart_payload['cart_count'] ?? 0),
			'cart_qty' => (int)($cart_payload['cart_qty'] ?? 0),
			'cart_total' => (float)($cart_payload['cart_total'] ?? 0),
		];

		api_response(['status' => 1, 'msg' => 'Cart payload', 'data' => $payload]);
	}

    public function get_wishlist()
    {
        $customer_id  =  $this->LOGIN_ID;
        $condition = array('customer_id' => $customer_id, 'status' => array('0','1'));
        $productlist = $this->Query_model->get_data('ec_wishlist', $condition);

        $current_currency = current_currency();
        $currency = get_currency($current_currency);
        $rate = $currency->rate;
        $symbol = $currency->symbol;
        $ln = current_language();
        $data = array();
        if($customer_id){
            foreach ($productlist as $orderlistdata) {

                $attribute_item_id = $orderlistdata->attribute_item_id;
                $product_obj = $this->Query_model->get_data_obj('ec_product', array('product_id' => $orderlistdata->product_id));
                if($product_obj){
                    $discount = 0;
                    $sale_price_dates_from = $product_obj->sale_price_dates_from;
                    $sale_price_dates_to   = $product_obj->sale_price_dates_to;
                    if($sale_price_dates_from && $sale_price_dates_from != '0000-00-00 00:00:00' && $sale_price_dates_to && $sale_price_dates_to != '0000-00-00 00:00:00'){
                        $ctime = time();
                        $sale_ftime = strtotime($sale_price_dates_from);
                        $sale_ttime = strtotime($sale_price_dates_to);
                        if($ctime > $sale_ftime && $ctime < $sale_ttime){
                            $discount = 1;
                        }
                    }
                    if($ln == 12){
                        $product_obj->post_title = ($product_obj->post_title_es) ? $product_obj->post_title_es : $product_obj->post_title;
                        $product_obj->post_content = ($product_obj->post_content_es) ? $product_obj->post_content_es : $product_obj->post_content;
                        $product_obj->post_slug = ($product_obj->post_slug) ? $product_obj->post_slug : '';
                    }
                    $product_gallery = $this->Query_model->get_data_obj('ec_gallery',array('product_id' => $orderlistdata->product_id), array(), array('length' => 1));
                    if($product_gallery){
                        $productimg = base_url().'assets/uploads/files/'.$product_gallery->file_name;           
                    }else{
                        $productimg = base_url().'assets/default_images/product.jpg';
                    }

                    if($product_obj->type == 'variable'){
						//print_r($attribute_item_id); exit;
		  	            //$attribute_item_id = explode(",",$attribute_item_id);
                        if($attribute_item_id){
                            $variation_obj = $this->Query_model->get_data('ec_product_variation',array('product_id' => $orderlistdata->product_id, 'attribute_item_id' => $attribute_item_id));

                            if($variation_obj){
                                $product_obj->sale_price = $variation_obj[0]->_sale_price;
                                $product_obj->regular_price = $variation_obj[0]->_regular_price;
                                $productimg = base_url().'assets/uploads/'.$variation_obj[0]->_thumbnail_id;

                            }
                        }
                    }

                    $attribute_items = array(); 
                    if($attribute_item_id){
                        $attribute_item_array = explode(',',$attribute_item_id);
                        if($attribute_item_array){
                            $attribute_obj = $this->Query_model->get_data('ec_attribute_item',array('attribute_item_id' => $attribute_item_array)); 
                            if($attribute_obj){
                                foreach($attribute_obj as $ao){
                                    $attribute_items[] = $ao->name;
                                }
                            }
                        }
                    }

                    $product_obj->sale_price = sprintf('%.02f',$rate*$product_obj->sale_price);
                    $product_obj->regular_price = sprintf('%.02f',$rate*$product_obj->regular_price);
                    $row['wishlist_id']     =  $orderlistdata->wishlist_id;
                    $row['customer_id']     =  $orderlistdata->customer_id;
                    $row['productimg']      =  $productimg;
                    $row['url']             =  detail_page_url($product_obj); //'/product/detail/'.$product_obj->post_slug;
                    $row['discount']        =  $discount;
                    $row['attribute_item_id'] =  ($attribute_item_id) ? explode(',',$attribute_item_id) : array();
                    $row['attribute_items'] =  $attribute_items;
                    $row['productname']     =  $product_obj->post_title;
                    $row['price']           =  $product_obj->sale_price;
                    $row['sale_price']      =  $product_obj->sale_price;
                    $row['regular_price']   =  $product_obj->regular_price;
                    $row['post_content']    =  $product_obj->post_content;
                    $row['symbol']          =  $symbol;
                    $row['iso_code']        =  $currency->iso_code;
                    $row['product_id']      =  $orderlistdata->product_id;
                    $row['date_added']      =  $orderlistdata->date_added;
                    $data[]      = $row;
                }
            }
            
            //return $data;

             api_response(array('status'=>1, 'msg'=>'success', 'data' => $data));
            
        }
            

    }

    public function ajax_check_inventory()
    {   
        $cart_items = array();
        $cart = $this->Cart_model->cart_items();
        #print_r($cart);
        $title = [];
        if(isset($cart['cart_items']))
        {
            foreach($cart['cart_items'] as $row_wise)
            {
                if($row_wise->product_type == 'simple')
                {
                    $stock = $this->simple_prd_qty($row_wise);
                    if($stock['status'] == 1){continue;}
                    
                    $title[$row_wise->product_id] = $stock['msg'];
                    
                }
                else if($row_wise->product_type == 'variable')
                {
                    $stock = $this->variation_prd_qty($row_wise);
                    if($stock['status'] == 1){continue;}

                    $title[$row_wise->product_id] = $stock['msg'];
                } 
            } 
        }
        if(count($title))
        api_response(array('status'=>'1', 'msg'=>'OUT OF STOCK', 'data'=> $title)); 
        else
        api_response(array('status'=>'0', 'msg'=>'There is no data', 'data'=> []));
    }

    public function simple_prd_qty($row_wise)
    {
        $prod_obj = $this->Query_model->get_data_obj('ec_product', array('product_id'=>$row_wise->product_id, 'enabled'=>'1')); 
        if($prod_obj->stock >= $row_wise->quantity)
        {
            return array('status'=>1, 'msg'=>'success', 'data'=>$prod_obj->stock);
        }
        else
        {
            $msg = $prod_obj->stock > 0 ? "You can not buy more then $prod_obj->stock quantity" : "OUT OF STOCK";
            return array('status'=>0, 'msg'=>$msg, 'data'=>$prod_obj->stock);
        }
        
    }

    public function variation_prd_qty($row_wise)
    {
       $prod_obj = $this->Query_model->get_data('ec_product_variation', array('product_id'=>$row_wise->product_id, 'attribute_item_id'=>$row_wise->attribute_item_id));
        
        if($prod_obj[0]->_stock >= $row_wise->quantity)
        {
            return array('status'=>1, 'msg'=>'success', 'data'=>$prod_obj[0]->_stock);
        }
        else
        {
            $msg = $prod_obj[0]->_stock > 0 ? "You can not buy more then $prod_obj[0]->_stock quantity" : "OUT OF STOCK";
            return array('status'=>0, 'msg'=>$msg, 'data'=>$prod_obj[0]->_stock); 
        }
    }



}
