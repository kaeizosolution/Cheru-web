<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends MY_Model {
    
    function __construct() {
        parent::__construct();
        try {
            if ($this->db && $this->db->conn_id) {
                $query = $this->db->query("SHOW COLUMNS FROM ec_coupon LIKE 'category_id'");
                $row = $query ? $query->row() : null;
                if ($row && stripos($row->Type, 'int') !== false) {
                    $this->db->query("ALTER TABLE ec_coupon MODIFY COLUMN category_id VARCHAR(255) DEFAULT NULL");
                    $this->db->query("ALTER TABLE ec_coupon MODIFY COLUMN brand_id VARCHAR(255) DEFAULT NULL");
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Auto-migration error: ' . $e->getMessage());
        }
    }

    private function get_logged_in_user_id()
    {
        $customer = $this->session->userdata('customer');
        return !empty($customer['login_id']) ? (int)$customer['login_id'] : 0;
    }

	private function _get_active_deal_discount_map()
	{
		static $cache = null;
		if (is_array($cache)) {
			return $cache;
		}
		$cache = [];

		if (!$this->db->table_exists('ec_homepage_deals') || !$this->db->table_exists('ec_homepage_deal_items')) {
			return $cache;
		}

		$nowUtc = new DateTime('now', new DateTimeZone('UTC'));
		$nowStr = $nowUtc->format('Y-m-d H:i:s');

		$deal = $this->db
			->from('ec_homepage_deals')
			->where('is_active', 1)
			->where('start_at_utc <=', $nowStr)
			->where('end_at_utc >', $nowStr)
			->order_by('id', 'DESC')
			->get()->row();

		if (!$deal || !isset($deal->id)) {
			return $cache;
		}

		$rows = $this->db
			->select('product_id, discount_percent')
			->from('ec_homepage_deal_items')
			->where('deal_id', (int)$deal->id)
			->get()->result();
		if ($rows) {
			foreach ($rows as $r) {
				$pid = isset($r->product_id) ? (int)$r->product_id : 0;
				$pct = isset($r->discount_percent) ? (float)$r->discount_percent : 0;
				if ($pid > 0 && $pct > 0) {
					if ($pct > 95) {
						$pct = 95;
					}
					$cache[$pid] = $pct;
				}
			}
		}

		return $cache;
	}

	private function _apply_deal_discount_to_unit_price($product_id, $unit_price)
	{
		$product_id = (int)$product_id;
		$unit_price = (float)$unit_price;
		if ($product_id <= 0 || $unit_price <= 0) {
			return [$unit_price, 0];
		}
		$map = $this->_get_active_deal_discount_map();
		$pct = isset($map[$product_id]) ? (float)$map[$product_id] : 0;
		if ($pct <= 0) {
			return [$unit_price, 0];
		}
		$discounted = $unit_price - ($unit_price * $pct / 100);
		if ($discounted < 0) {
			$discounted = 0;
		}
		return [$discounted, $pct];
	}

    private function get_unit_price_by_product_id($product_id)
    {
        return 0;
    }

	private function _resolve_price_for_qty_tiers($tiers, $qty)
	{
		$qty = (int)$qty;
		if ($qty <= 0) {
			$qty = 1;
		}
		if (!is_array($tiers) || !$tiers) {
			return null;
		}
		foreach ($tiers as $t) {
			if (!is_array($t)) {
				continue;
			}
			$min = isset($t['min_qty']) ? (int)$t['min_qty'] : 1;
			$max = isset($t['max_qty']) ? (int)$t['max_qty'] : 0;
			$price = isset($t['price']) ? (float)$t['price'] : null;
			if ($price === null) {
				continue;
			}
			if ($min <= 0) {
				$min = 1;
			}
			$max_ok = ($max <= 0) ? true : ($qty <= $max);
			if ($qty >= $min && $max_ok) {
				return $price;
			}
		}
		$last = end($tiers);
		if (is_array($last) && isset($last['price'])) {
			return (float)$last['price'];
		}
		return null;
	}

	private function _normalize_attr_values_from_json($attr_json)
	{
		if (!$attr_json) {
			return [];
		}
		$decoded = json_decode((string)$attr_json, true);
		if (!is_array($decoded)) {
			return [];
		}

		$vals = [];
		$is_assoc = array_keys($decoded) !== range(0, count($decoded) - 1);
		if ($is_assoc) {
			foreach ($decoded as $k => $v) {
				if (is_scalar($v) && ctype_digit((string)$v)) {
					$vals[] = (int)$v;
				}
			}
		} else {
			foreach ($decoded as $pair) {
				if (!is_array($pair)) {
					continue;
				}
				$v = $pair['value'] ?? null;
				if (is_scalar($v) && ctype_digit((string)$v)) {
					$vals[] = (int)$v;
				}
			}
		}
		sort($vals);
		return array_values(array_unique($vals));
	}

	private function _find_matching_variation_id($product_id, $attribute_item_ids)
	{
		$product_id = (int)$product_id;
		if ($product_id <= 0) {
			return 0;
		}
		$attr = is_array($attribute_item_ids) ? $attribute_item_ids : [];
		$attr = array_values(array_filter(array_map(function ($v) {
			if (is_int($v)) return $v;
			if (is_string($v) && ctype_digit(trim($v))) return (int)trim($v);
			return null;
		}, $attr), function ($v) {
			return $v !== null;
		}));
		sort($attr);

		$rows = $this->db->select('id, attr_json')->from('product_variations')->where('product_id', $product_id)->get()->result_array();
		foreach ($rows as $r) {
			$vals = $this->_normalize_attr_values_from_json($r['attr_json'] ?? '');
			// If no attributes were selected and variation has no attributes, treat as match
			if (!$vals && !$attr) {
				return (int)($r['id'] ?? 0);
			}
			if ($vals && $attr && json_encode($vals) === json_encode($attr)) {
				return (int)($r['id'] ?? 0);
			}
		}
		return 0;
	}

	private function _is_new_products_table_product($product_id)
	{
		$product_id = (int)$product_id;
		if ($product_id <= 0) {
			return false;
		}
		$row = $this->_get_new_product_row($product_id);
		return (bool)$row;
	}

	private function _get_new_product_row($product_id)
	{
		return $this->db->get_where('products', ['id' => (int)$product_id])->row();
	}

	private function _get_variation_row($variation_id)
	{
		$variation_id = (int)$variation_id;
		if ($variation_id <= 0) {
			return null;
		}
		return $this->db->get_where('product_variations', ['id' => $variation_id])->row();
	}

	private function _resolve_variation_unit_price($variation_id, $qty)
	{
		$variation_id = (int)$variation_id;
		if ($variation_id <= 0) {
			return 0;
		}
		$tiers = $this->db
			->select('min_qty, max_qty, price')
			->from('product_variation_price')
			->where('variation_id', $variation_id)
			->order_by('min_qty', 'asc')
			->get()->result_array();
		$price = $this->_resolve_price_for_qty_tiers($tiers, $qty);
		return $price !== null ? (float)$price : 0;
	}

	public function resolve_variation_id_for_attrs($product_id, $attribute_item_ids)
	{
		return (int)$this->_find_matching_variation_id((int)$product_id, $attribute_item_ids);
	}

	public function resolve_variation_unit_price($variation_id, $qty)
	{
		return (float)$this->_resolve_variation_unit_price((int)$variation_id, (int)$qty);
	}

	private function _resolve_new_product_unit_price($product_id, $attribute_item_ids, $qty)
	{
		$variation_id = $this->_find_matching_variation_id($product_id, $attribute_item_ids);
		if ($variation_id <= 0) {
			return 0;
		}
		$tiers = $this->db
			->select('min_qty, max_qty, price')
			->from('product_variation_price')
			->where('variation_id', $variation_id)
			->order_by('min_qty', 'asc')
			->get()->result_array();
		$price = $this->_resolve_price_for_qty_tiers($tiers, $qty);
		return $price !== null ? (float)$price : 0;
	}

	private function _resolve_new_product_image_url($product_id, $attribute_item_ids)
	{
		$variation_id = $this->_find_matching_variation_id($product_id, $attribute_item_ids);
		if ($variation_id <= 0) {
			return base_url('assets/default_images/product.jpg');
		}
		$row = $this->db
			->select('image_path')
			->from('product_variation_images')
			->where('variation_id', $variation_id)
			->order_by('id', 'asc')
			->get()->row();
		$raw = $row && isset($row->image_path) ? trim((string)$row->image_path) : '';
		if ($raw === '') {
			return base_url('assets/default_images/product.jpg');
		}
		if (preg_match('#^https?://#i', $raw)) {
			return $raw;
		}
		if (strpos($raw, '/') === 0) {
			return base_url(ltrim($raw, '/'));
		}
		return base_url('uploads/products/' . $raw);
	}

	private function _resolve_variation_image_url($variation_id)
	{
		$variation_id = (int)$variation_id;
		if ($variation_id <= 0) {
			return base_url('assets/default_images/product.jpg');
		}
		$row = $this->db
			->select('image_path')
			->from('product_variation_images')
			->where('variation_id', $variation_id)
			->order_by('id', 'asc')
			->get()->row();
		$raw = $row && isset($row->image_path) ? trim((string)$row->image_path) : '';
		if ($raw === '') {
			return base_url('assets/default_images/product.jpg');
		}
		if (preg_match('#^https?://#i', $raw)) {
			return $raw;
		}
		if (strpos($raw, '/') === 0) {
			return base_url(ltrim($raw, '/'));
		}
		return base_url('uploads/products/' . $raw);
	}

    private function persist_session_cart_to_db($user_id, $cart)
    {
        if (empty($user_id)) {
            return;
        }

        $this->db->where('user_id', $user_id)->delete('ec_cart');

        foreach ($cart as $cart_key => $item) {
            $product_id = (int)($item['product_id'] ?? 0);
            $qty = (int)($item['quantity'] ?? 0);
            $variation_id = (int)($item['variation_id'] ?? 0);
            if ($product_id <= 0 || $qty <= 0) {
                continue;
            }

			$attr_ids = $item['attribute_item_id'] ?? [];
			if (!is_array($attr_ids)) {
				$attr_ids = [$attr_ids];
			}

			$price = 0;
			if ($variation_id > 0) {
				$price = $this->_resolve_variation_unit_price($variation_id, $qty);
			}
			if ($price <= 0) {
				$price = $this->_resolve_new_product_unit_price($product_id, $attr_ids, $qty);
			}

            $options = [
                'attribute_item_id' => $attr_ids,
                'variation_id' => $variation_id,
            ];

            $data = [
                'user_id'    => $user_id,
                'product_id' => $product_id,
                'qty'        => $qty,
                'price'      => $price,
                'rowid'      => (string)$cart_key,
                'options'    => json_encode($options)
            ];

            $this->db->insert('ec_cart', $data);
        }
    }

    private function load_db_cart_into_session_if_needed($force = false)
    {
        $user_id = $this->get_logged_in_user_id();
        if (empty($user_id)) {
            return;
        }

        $cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : array();
        if (!$force && !empty($cart)) {
            return;
        }

        $rows = $this->db->where('user_id', $user_id)->get('ec_cart')->result_array();
        if (empty($rows)) {
            if ($force) {
                $this->session->set_userdata('cart', []);
            }
            return;
        }

        $loaded = [];
        foreach ($rows as $row) {
            $product_id = (int)($row['product_id'] ?? 0);
            $qty = (int)($row['qty'] ?? 0);
            if ($product_id <= 0 || $qty <= 0) {
                continue;
            }

            $opts = [];
            if (!empty($row['options'])) {
                $decoded = json_decode($row['options'], true);
                if (is_array($decoded)) {
                    $opts = $decoded;
                }
            }

            $attr = $opts['attribute_item_id'] ?? [];
            if (!is_array($attr)) {
                $attr = [$attr];
            }
            $attr = array_values(array_filter(array_map(function ($v) {
                if (is_int($v)) {
                    return $v > 0 ? $v : null;
                }
                if (is_float($v) && (int)$v == $v) {
                    $iv = (int)$v;
                    return $iv > 0 ? $iv : null;
                }
                if (is_string($v)) {
                    $s = trim($v);
                    if ($s !== '' && ctype_digit($s)) {
                        $iv = (int)$s;
                        return $iv > 0 ? $iv : null;
                    }
                }
                return null;
            }, $attr), function ($v) {
                return $v !== null;
            }));
            sort($attr);

            $variation_id = isset($opts['variation_id']) ? (int)$opts['variation_id'] : 0;

            // IMPORTANT: keep cart_key stable across web + API + new sessions.
            // Prefer the stored DB rowid only if it matches our canonical key formats.
            $stored_key = isset($row['rowid']) ? trim((string)$row['rowid']) : '';
            $stored_ok = false;
            if ($stored_key !== '') {
                if (preg_match('/^variation_\d+$/', $stored_key)) {
                    $stored_ok = true;
                } elseif (preg_match('/^\d+_[a-f0-9]{32}$/i', $stored_key)) {
                    $stored_ok = true;
                }
            }
            $cart_key = $stored_ok
                ? $stored_key
                : (($variation_id > 0)
                    ? ('variation_' . $variation_id)
                    : ($product_id . '_' . md5(json_encode($attr))));
            $loaded[$cart_key] = [
                'product_id' => $product_id,
                'quantity' => $qty,
                'attribute_item_id' => $attr,
                'variation_id' => $variation_id,
            ];
        }

        $this->session->set_userdata('cart', $loaded);
    }

public function add_to_cart($args) {
    $this->load_db_cart_into_session_if_needed();
    $cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : array();
    $product_id = $args['product_id'];
	$variation_id = isset($args['variation_id']) ? (int)$args['variation_id'] : 0;
    $action = isset($args['action']) ? $args['action'] : 'add';
    
	// We must generate the same key used when the item was first added
	$attr = !empty($args['attribute_item']) ? $args['attribute_item'] : [];
	if (!is_array($attr)) {
		$attr = [$attr];
	}
	$attr = array_values(array_filter($attr, function($v){ return $v !== null && $v !== ''; }));
	sort($attr);

	// If this is a new-table product but variation_id was not provided, try to resolve it
	if ($variation_id <= 0 && $this->_is_new_products_table_product($product_id)) {
		$variation_id = (int)$this->_find_matching_variation_id($product_id, $attr);
		if ($variation_id > 0) {
			$args['variation_id'] = $variation_id;
		}
	}
	$cart_key = ($variation_id > 0)
		? ('variation_' . $variation_id)
		: ($product_id . '_' . md5(json_encode($attr)));

    if ($action == 'delete') {
        if (isset($cart[$cart_key])) {
            unset($cart[$cart_key]);
        }
    } else if ($action == 'update') {
        if (isset($cart[$cart_key])) {
            $cart[$cart_key]['quantity'] = (int)$args['quantity'];
        } else if ($variation_id > 0) {
			foreach ($cart as $k => $v) {
				$vid = isset($v['variation_id']) ? (int)$v['variation_id'] : 0;
				if ($vid === (int)$variation_id) {
					$cart[$k]['quantity'] = (int)$args['quantity'];
					break;
				}
			}
		}
    } else {
        // Normal add logic
        if (isset($cart[$cart_key])) {
            $cart[$cart_key]['quantity'] += (int)$args['quantity'];
        } else {
            $cart[$cart_key] = [
                'product_id' => $product_id,
                'quantity' => (int)$args['quantity'],
				'attribute_item_id' => $attr,
				'variation_id' => $variation_id,
            ];
        }
    }

    $this->session->set_userdata('cart', $cart);

    $user_id = $this->get_logged_in_user_id();
    $this->persist_session_cart_to_db($user_id, $cart);
    return array('status' => 1, 'msg' => 'Success');
}
public function delete_to_cart($args) {
    $this->load_db_cart_into_session_if_needed();
    $cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : array();
    $product_id = $args['product_id'];
	$variation_id = isset($args['variation_id']) ? (int)$args['variation_id'] : 0;
    $attr = !empty($args['attribute_item']) ? $args['attribute_item'] : [];

	$cart_key = ($variation_id > 0)
		? ('variation_' . $variation_id)
		: ($product_id . '_' . md5(json_encode($attr)));

    if (isset($cart[$cart_key])) {
        unset($cart[$cart_key]);
        $this->session->set_userdata('cart', $cart);

        $user_id = $this->get_logged_in_user_id();
        $this->persist_session_cart_to_db($user_id, $cart);
        return array('status' => 1, 'message' => 'Item removed');
    }
    return array('status' => 0, 'message' => 'Item not found');
}
    

    public function cart_items() {
	$this->load_db_cart_into_session_if_needed(true);
	$cart = $this->session->userdata('cart') ? $this->session->userdata('cart') : array();
	$items = array();
	$subtotal = 0;
	$total_qty = 0;

	$normalize_attr_ids = function ($raw) {
		if (empty($raw)) {
			return [];
		}
		if (is_array($raw)) {
			$vals = $raw;
		} else {
			$s = trim((string)$raw);
			if ($s === '') {
				return [];
			}
			$decoded = json_decode($s, true);
			if (is_array($decoded)) {
				$vals = $decoded;
			} else {
				$vals = preg_split('/\s*,\s*/', $s, -1, PREG_SPLIT_NO_EMPTY);
			}
		}
		$out = [];
		foreach ($vals as $v) {
			if (is_int($v)) {
				if ($v > 0) $out[] = $v;
				continue;
			}
			if (is_string($v)) {
				$v = trim($v);
				if ($v !== '' && ctype_digit($v)) {
					$iv = (int)$v;
					if ($iv > 0) $out[] = $iv;
				}
			}
		}
		$out = array_values(array_unique($out));
		sort($out);
		return $out;
	};

	$attr_name_map = [];
	$all_attr_item_ids = [];
	if (!empty($cart)) {
		foreach ($cart as $v) {
			$attr_ids = $normalize_attr_ids($v['attribute_item_id'] ?? []);
			foreach ($attr_ids as $aid) {
				$all_attr_item_ids[] = (int)$aid;
			}
		}
	}
	$all_attr_item_ids = array_values(array_unique(array_filter($all_attr_item_ids, function ($v) { return (int)$v > 0; })));
	if (!empty($all_attr_item_ids)) {
		$rows = $this->db
			->select('attribute_item_id, name')
			->from('ec_attribute_item')
			->where_in('attribute_item_id', $all_attr_item_ids)
			->get()->result();
		if ($rows) {
			foreach ($rows as $r) {
				$attr_name_map[(int)$r->attribute_item_id] = (string)$r->name;
			}
		}
	}

    if (!empty($cart)) {
        foreach ($cart as $key => $val) {
			$variation_id = isset($val['variation_id']) ? (int)$val['variation_id'] : 0;
			$new_product = $this->_get_new_product_row((int)$val['product_id']);
			if (!$new_product && $variation_id > 0) {
				$vrow = $this->_get_variation_row($variation_id);
				if ($vrow && isset($vrow->product_id)) {
					$new_product = $this->_get_new_product_row((int)$vrow->product_id);
				}
			}

			if ($new_product) {
                $qty = (int)$val['quantity'];
				$attr_ids = $normalize_attr_ids($val['attribute_item_id'] ?? []);

				// If this is a new-table product and variation_id is missing, resolve it now for stable cart ops
				if ($variation_id <= 0) {
					$resolved_vid = (int)$this->_find_matching_variation_id((int)$new_product->id, $attr_ids);
					if ($resolved_vid > 0) {
						$variation_id = $resolved_vid;
						$cart[$key]['variation_id'] = $variation_id;
						$target_key = 'variation_' . $variation_id;
						if ($key !== $target_key && !isset($cart[$target_key])) {
							$cart[$target_key] = $cart[$key];
							unset($cart[$key]);
							$key = $target_key;
						}
						$this->session->set_userdata('cart', $cart);
						$user_id = $this->get_logged_in_user_id();
						$this->persist_session_cart_to_db($user_id, $cart);
					}
				}

				$unit_price = 0;
				$regular_price = 0;
				if ($variation_id > 0) {
					$unit_price = $this->_resolve_variation_unit_price($variation_id, $qty);
					$regular_price = $unit_price;
				}
				if ($unit_price <= 0) {
					$unit_price = $this->_resolve_new_product_unit_price((int)$val['product_id'], $attr_ids, $qty);
					$regular_price = $unit_price;
				}

				$deal_product_id = (int)$val['product_id'];
				$orig_unit_price = (float)$unit_price;
				$applied_pct = 0;
				$applied = $this->_apply_deal_discount_to_unit_price($deal_product_id, $unit_price);
				$unit_price = (float)$applied[0];
				$applied_pct = (float)$applied[1];
				if ($applied_pct > 0) {
					$regular_price = (float)$orig_unit_price;
				}

				$item_total = $unit_price * $qty;
				$subtotal += $item_total;
				$total_qty += $qty;

				$image_url = '';
				if ($variation_id > 0) {
					$image_url = $this->_resolve_variation_image_url($variation_id);
				} else {
					$image_url = $this->_resolve_new_product_image_url((int)$val['product_id'], $attr_ids);
				}
				$image_name = $image_url ? basename(parse_url($image_url, PHP_URL_PATH)) : 'default.png';
				if (!$image_url) {
					$image_url = base_url('assets/default_images/product.jpg');
				}

				$items[] = (object) array(
					'id'            => (int)$new_product->id,
					'product_id'    => (int)$val['product_id'],
					'variation_id'   => $variation_id,
					'post_title'    => (string)$new_product->name,
					'image'         => $image_name,
					'image_url'     => $image_url,
					'quantity'      => $qty,
					'sale_price'    => (float)$unit_price,
					'regular_price' => (float)$regular_price,
					'total'         => (float)$item_total,
					'deal_discount_percent' => (float)$applied_pct,
					'attributes'    => implode(', ', array_values(array_filter(array_map(function ($aid) use ($attr_name_map) {
						$k = (int)$aid;
						return isset($attr_name_map[$k]) ? $attr_name_map[$k] : null;
					}, $attr_ids)))),
					'attribute_item_id' => !empty($attr_ids) ? implode(',', $attr_ids) : '',
					'product_type'  => (string)($new_product->product_type ?? 'variable'),
					'category'      => (string)($new_product->category ?? ''),
					'sub_category'  => (string)($new_product->sub_category ?? ''),
					'sub_sub_category' => (string)($new_product->sub_sub_category ?? ''),
					'brand_id'      => (int)($new_product->brand_id ?? 0)
				);
            }
        }
    }

    $shipping = ($subtotal <= 0) ? 0 : (($subtotal >= 9800) ? 0 : 50);
    $additional_charges = 0;
    $grand_total = $subtotal + $shipping + $additional_charges;

    return array(
        'cart_items'   => $items,
        'cart_summary' => (object) array(
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'additional_charges' => $additional_charges,
            'tax'      => 0,
            'total'    => $subtotal,
            'grand_total' => $grand_total,
            'total_qty' => $total_qty
        )
    );
}

	public function clear_buy_now()
	{
		$this->session->unset_userdata('buy_now_product');
	}

	public function checkout_items()
	{
		$buy = $this->session->userdata('buy_now_product');
		if (!$buy || !is_array($buy) || empty($buy['product_id'])) {
			return $this->cart_items();
		}

		$product_id = (int)$buy['product_id'];
		$variation_id = (int)($buy['variation_id'] ?? 0);
		$attribute_item_id = $buy['attribute_item_id'] ?? '';
		$qty = (int)($buy['quantity'] ?? 1);
		if ($qty <= 0) {
			$qty = 1;
		}

		$attr_ids = [];
		if (is_array($attribute_item_id)) {
			$attr_ids = $attribute_item_id;
		} elseif (is_string($attribute_item_id) && trim($attribute_item_id) !== '') {
			$attr_ids = array_map('trim', explode(',', $attribute_item_id));
		}
		$attr_ids = array_values(array_filter(array_map(function ($v) {
			if (is_int($v)) return $v;
			if (is_string($v) && ctype_digit(trim($v))) return (int)trim($v);
			return null;
		}, $attr_ids), function ($v) {
			return $v !== null;
		}));

		// Prefer new products table
		$new_product = $this->_get_new_product_row((int)$product_id);
		if ($new_product) {
			if ($variation_id <= 0) {
				$variation_id = (int)$this->_find_matching_variation_id((int)$product_id, $attr_ids);
			}

			$unit_price = 0;
			$regular_price = 0;
			if ($variation_id > 0) {
				$unit_price = (float)$this->_resolve_variation_unit_price((int)$variation_id, (int)$qty);
				$regular_price = (float)$unit_price;
			}
			$deal_product_id = (int)$product_id;
			$orig_unit_price = (float)$unit_price;
			$applied_pct = 0;
			$applied = $this->_apply_deal_discount_to_unit_price($deal_product_id, $unit_price);
			$unit_price = (float)$applied[0];
			$applied_pct = (float)$applied[1];
			if ($applied_pct > 0) {
				$regular_price = (float)$orig_unit_price;
			}
			$item_total = $unit_price * $qty;

			$image_url = $this->_resolve_new_product_image_url((int)$product_id, $attr_ids);
			$image_name = $image_url ? basename(parse_url($image_url, PHP_URL_PATH)) : 'default.png';
			if (!$image_url) {
				$image_url = base_url('assets/default_images/product.jpg');
			}

			$items = array();
			$items[] = (object) array(
				'id'            => (int)$new_product->id,
				'product_id'    => (int)$product_id,
				'variation_id'   => (int)$variation_id,
				'post_title'    => (string)$new_product->name,
				'image'         => $image_name,
				'image_url'     => $image_url,
				'quantity'      => (int)$qty,
				'sale_price'    => (float)$unit_price,
				'regular_price' => (float)$regular_price,
				'total'         => (float)$item_total,
				'deal_discount_percent' => (float)$applied_pct,
				'attribute_item_id' => !empty($attr_ids) ? implode(',', $attr_ids) : '',
				'product_type'  => (string)($new_product->product_type ?? 'variable'),
				'category'      => (string)($new_product->category ?? ''),
				'sub_category'  => (string)($new_product->sub_category ?? ''),
				'sub_sub_category' => (string)($new_product->sub_sub_category ?? ''),
				'brand_id'      => (int)($new_product->brand_id ?? 0)
			);

			$subtotal = (float)$item_total;
			$shipping = ($subtotal <= 0) ? 0 : (($subtotal >= 9800) ? 0 : 50);
			$additional_charges = 0;
			$grand_total = $subtotal + $shipping + $additional_charges;

			return array(
				'cart_items' => $items,
				'cart_summary' => (object) array(
					'subtotal' => $subtotal,
					'shipping' => $shipping,
					'additional_charges' => $additional_charges,
					'tax' => 0,
					'total' => $subtotal,
					'grand_total' => $grand_total,
					'total_qty' => (int)$qty
				)
			);
		}

		return array('cart_items' => array(), 'cart_summary' => (object) array(
			'subtotal' => 0,
			'shipping' => 0,
			'additional_charges' => 0,
			'tax' => 0,
			'total' => 0,
			'grand_total' => 0,
			'total_qty' => 0
		));
	}
    public function save_cart($user_id, $cart_contents) {
        $this->db->where('user_id', $user_id)->delete('ec_cart');
        foreach ($cart_contents as $item) {
            $data = [
                'user_id'    => $user_id,
                'product_id' => $item['id'],
                'qty'        => $item['qty'],
                'price'      => $item['price'],
                'rowid'      => $item['rowid'],
                'options'    => json_encode($item['options'])
            ];
            $this->db->insert('ec_cart', $data);
        }
    }

    public function load_cart($user_id) {
        return $this->db->where('user_id', $user_id)->get('ec_cart')->result_array();
    }
}