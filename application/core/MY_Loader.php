<?php
class MY_Loader extends CI_Loader {

    public function template($template_name, $args = array(), $return = FALSE)
    {
        if($return){
    #        $content  = $this->view('common/header', $args, $return);
    #        $content .= $this->view($template_name, $args, $return);
    #        $content .= $this->view('common/footer', $args, $return);
    #        return $content;
            $this->view($template_name, $args);
        }else{
            $type = isset($_SESSION['type']) ? $_SESSION['type'] : 'customer';
            if($type == 'customer'){
                // Ensure cart data is available to header/sidebar on every customer page
                $CI =& get_instance();
                if (!isset($CI->Cart_model)) {
                    $CI->load->model('Cart_model');
                }

				$CI->config->load('custom_config');
				$CI->load->helper('api');

				$use_api = (bool)$CI->config->item('use_api');
				$api_payload = null;
				if ($use_api) {
					$customer = $CI->session->userdata('customer');
					$token = '';
					if (is_array($customer) && !empty($customer['access_token'])) {
						$token = (string)$customer['access_token'];
					}
					if ($token !== '') {
						$api_cart = call_api('GET', 'cart', null, ['Authorization' => 'Bearer ' . $token]);
						if ($api_cart['response'] !== null && (int)($api_cart['response']['status'] ?? 0) === 1 && is_array($api_cart['response']['data'] ?? null)) {
							$api_payload = $api_cart['response']['data'];
						}
					}
				}

				if ($api_payload !== null) {
					$api_items = is_array($api_payload['items'] ?? null) ? $api_payload['items'] : [];
					$cart_total = (float)($api_payload['cart_total'] ?? 0);
					$final_total = (float)($api_payload['final_total'] ?? $cart_total);
					$total_qty = 0;
					$items = [];
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

						$product = $CI->db->get_where('ec_product', array('id' => $product_id))->row();
						$image_name = 'default.png';
						$product_type = 'simple';
						$post_title = (string)($it['product_name'] ?? '');
						$regular_price = $price;

						if ($product) {
							$post_title = $post_title !== '' ? $post_title : (string)($product->name ?? '');
							$product_type = isset($product->product_type) ? (string)$product->product_type : 'simple';
							if (!empty($product->images)) {
								$clean_image = str_replace(['[', ']', '"', '"'], '', (string)$product->images);
								$clean_image = trim($clean_image);
								if ($clean_image !== '') {
									$image_name = $clean_image;
								}
							}

							if (isset($product->prices) && $product->prices) {
								$price_data = json_decode($product->prices, true);
								if (is_array($price_data) && isset($price_data[0]) && is_array($price_data[0]) && isset($price_data[0]['regular_price'])) {
									$regular_price = (float)$price_data[0]['regular_price'];
								}
							}
						} else {
							$image_query = $CI->db->get_where('ec_gallery', array('product_id' => $product_id), 1);
							$image_row = $image_query->row();
							if ($image_row && !empty($image_row->file_name)) {
								$image_name = (string)$image_row->file_name;
							}
						}

						$image_url = base_url('uploads/products/' . $image_name);
						$items[] = (object)array(
							'id' => $product_id,
							'product_id' => $product_id,
							'post_title' => $post_title,
							'image' => $image_name,
							'image_url' => $image_url,
							'quantity' => $qty,
							'sale_price' => $price,
							'regular_price' => $regular_price,
							'total' => $row_total,
							'attribute_item_id' => '',
							'product_type' => $product_type,
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

					$cart_payload = array(
						'cart_items'   => $items,
						'cart_summary' => $summary,
						'cart_count'   => (int)count($items),
						'cart_qty'     => (int)$total_qty,
						'cart_total'   => (float)$final_total,
					);
				} else {
					$cart_info = $CI->Cart_model->cart_items();
					$summary = $cart_info['cart_summary'] ?? (object)[];

					$cart_payload = array(
						'cart_items'   => $cart_info['cart_items'] ?? array(),
						'cart_summary' => $summary,
						'cart_count'   => (int)count($cart_info['cart_items'] ?? array()),
						'cart_qty'     => (int)($summary->total_qty ?? 0),
						'cart_total'   => (float)($summary->grand_total ?? 0),
					);
				}

                if (is_array($args)) {
                    $args = array_merge($args, $cart_payload);
                } else {
                    $args = $cart_payload;
                }

                $this->view("$type/header", $args);
                $this->view($template_name, $args);
                $this->view("$type/footer", $args);
            }elseif($type == 'vendor'){

                $this->view("$type/header", $args);
                $this->view($template_name, $args);
                $this->view("$type/footer", $args);

            }if($type == 'admin'){
                $this->view("$type/header", $args);
                $this->view($template_name, $args);
                $this->view("$type/footer", $args);
            }
        }
    }
}

?>
