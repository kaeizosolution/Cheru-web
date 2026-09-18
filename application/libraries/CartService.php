<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CartService {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('cart');
        $this->CI->load->model('Cart_model');
    }

    public function prepare_item_data($product, $post_data) {
	    $prices = json_decode($product->prices, true) ?: [];
	    $mods   = json_decode($product->modifiers, true) ?: [];
	    $extras = json_decode($product->extras, true) ?: [];

	    $price_name = isset($post_data['prices'][0]['name'])
		    ? $post_data['prices'][0]['name']
		    : (!empty($prices[0]['name']) ? $prices[0]['name'] : null);

	    if (!$price_name) {
		    return ['error' => 'No price option found for this product'];
	    }

	    $mod_ids   = !empty($post_data['modifiers']) ? array_column($post_data['modifiers'], 'id') : [];
	    $extra_ids = !empty($post_data['extras']) ? array_column($post_data['extras'], 'id') : [];

	    $size = $this->get_selected_size($prices, $price_name);
	    if ($size['price'] <= 0) {
		    return ['error' => 'Invalid price option'];
	    }

	    $modifiers = !empty($mods) ? $this->filter_selected($mods, $mod_ids) : [];
	    $extrasArr = !empty($extras) ? $this->filter_selected($extras, $extra_ids) : [];

	    $final_price = $size['price']
		    + array_sum(array_column($modifiers, 'price'))
		    + array_sum(array_column($extrasArr, 'price'));

	    return [
		    'qty'     => 1,
		    'price'   => $final_price,
		    'name'    => $product->name,
		    'options' => [
			    'size'      => $size,
		    'modifiers' => $modifiers,
		    'extras'    => $extrasArr
		    ]
	    ];
    }
	
public function update_item_qty($rowid, $qty) {
    if ($qty < 0) $qty = 0;
    if ($qty > 10) $qty = 10;

    $exists = false;
    foreach ($this->CI->cart->contents() as $item) {
        if ($item['rowid'] == $rowid) {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        return ['success' => false, 'message' => 'Item not found in cart'];
    }

    if ($qty == 0) {
        $this->CI->cart->update([
            'rowid' => $rowid,
            'qty'   => 0
        ]);
    } else {
        $this->CI->cart->update([
            'rowid' => $rowid,
            'qty'   => $qty
        ]);
    }

    $this->save_cart_if_logged_in();

    return [
        'success' => true,
        'cart'    => $this->get_cart_response()
    ];
}
	
public function remove_item($rowid) {
    $this->CI->cart->update([
        'rowid' => $rowid,
        'qty'   => 0
    ]);

    $this->save_cart_if_logged_in();

    return [
        'success' => true,
        'cart'    => $this->get_cart_response()
    ];
}
 

    private function get_selected_size($prices, $selected_name) {
        foreach ($prices as $p) {
            if ($p['name'] == $selected_name) {
                $price = ($p['sales_price'] ?? 0) > 0 ? $p['sales_price'] : $p['regular_price'];
                return ['name' => $p['name'], 'price' => (float)$price];
            }
        }
        return ['name' => '', 'price' => 0];
    }

    private function filter_selected($all_options, $selected_ids) {
        $result = [];
        foreach ($all_options as $opt) {
            if (in_array($opt['id'], $selected_ids)) {
                $result[] = [
                    'id'    => $opt['id'],
                    'price' => isset($opt['price']) ? (float)$opt['price'] : 0
                ];
            }
        }
        return $result;
    }

    public function add_or_update_cart($product_id, $item_data) {
        if (isset($item_data['error'])) {
            return ['error' => $item_data['error']];
        }

        $found = false;
        foreach ($this->CI->cart->contents() as $rowid => $item) {
            if (
                $item['id'] == $product_id &&
                $item['options']['size']['name'] == $item_data['options']['size']['name'] &&
                json_encode($item['options']['modifiers']) == json_encode($item_data['options']['modifiers']) &&
                json_encode($item['options']['extras']) == json_encode($item_data['options']['extras'])
            ) {
                $this->CI->cart->update([
                    'rowid' => $rowid,
                    'qty'   => $item['qty'] + 1
                ]);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->CI->cart->insert(array_merge(['id' => $product_id], $item_data));
        }

        $this->save_cart_if_logged_in();
        return ['success' => true];
    }

    public function save_cart_if_logged_in() {
        $customer = $this->CI->session->userdata('customer');
        if (!empty($customer['login_id'])) {
            $this->CI->Cart_model->save_cart($customer['login_id'], $this->CI->cart->contents());
        }
    }

    public function merge_session_and_db_cart() {
        $customer = $this->CI->session->userdata('customer');
        if (empty($customer['login_id'])) return false;

        $user_id = $customer['login_id'];
        $db_cart = $this->CI->Cart_model->load_cart($user_id);
        $session_cart = $this->CI->cart->contents();

        $this->CI->cart->destroy();

        foreach ($db_cart as $row) {
            $this->CI->cart->insert([
                'id'      => $row['product_id'],
                'qty'     => $row['qty'],
                'price'   => $row['price'],
                'name'    => $this->get_product_name($row['product_id']),
                'options' => json_decode($row['options'], true)
            ]);
        }

        foreach ($session_cart as $session_item) {
            $this->add_or_update_cart($session_item['id'], $session_item);
        }

        $this->save_cart_if_logged_in();
        return true;
    }

    public function get_cart_response_old() {
        $cart_items = [];
        foreach ($this->CI->cart->contents() as $item) {
            $cart_items[] = [
                'rowid'    => $item['rowid'],
                'id'       => $item['id'],
                'name'     => $item['name'],
                'qty'      => $item['qty'],
                'price'    => $item['price'],
                'subtotal' => $item['subtotal'],
                'options'  => $this->get_options_with_names($item['options'])
            ];
        }

        return [
            'cart_count' => $this->CI->cart->total_items(),
            'cart_total' => $this->CI->cart->total(),
            'cart_items' => $cart_items
        ];
    }

public function get_cart_response() {
    $this->load_user_cart_if_logged_in();

    return [
        'cart_count' => $this->CI->cart->total_items(),
        'cart_total' => $this->CI->cart->total(),
        'cart_items' => $this->format_cart_items($this->CI->cart->contents())
    ];
}

private function load_user_cart_if_logged_in() {
    $customer = $this->CI->session->userdata('customer');

    if (empty($customer['login_id'])) {
        return;
    }

    $db_cart = $this->CI->Cart_model->load_cart($customer['login_id']);

    $this->CI->cart->destroy();
    foreach ($db_cart as $row) {
        $this->CI->cart->insert([
            'id'      => $row['product_id'],
            'qty'     => $row['qty'],
            'price'   => $row['price'],
            'name'    => $this->get_product_name($row['product_id']),
            'options' => json_decode($row['options'], true)
        ]);
    }
}

private function format_cart_items($cart_contents) {
    $cart_items = [];
    foreach ($cart_contents as $item) {
        $cart_items[] = [
            'rowid'    => $item['rowid'],
            'id'       => $item['id'],
            'name'     => $item['name'],
            'qty'      => $item['qty'],
            'price'    => $item['price'],
            'subtotal' => $item['subtotal'],
            'options'  => $this->get_options_with_names($item['options'])
        ];
    }
    return $cart_items;
}
	

	


    private function get_options_with_names($options) {
        if (!isset($options['modifiers'])) $options['modifiers'] = [];
        if (!isset($options['extras'])) $options['extras'] = [];

        if (!empty($options['modifiers'])) {
            $ids = array_column($options['modifiers'], 'id');
            $names = $this->get_names_from_table('ec_modifiers', $ids);
            foreach ($options['modifiers'] as &$m) {
                $m['name'] = $names[$m['id']] ?? '';
            }
        }

        if (!empty($options['extras'])) {
            $ids = array_column($options['extras'], 'id');
            $names = $this->get_names_from_table('ec_extras', $ids);
            foreach ($options['extras'] as &$e) {
                $e['name'] = $names[$e['id']] ?? '';
            }
        }

        return $options;
    }

    private function get_names_from_table($table, $ids) {
        if (empty($ids)) return [];
        $rows = $this->CI->db->select('id,name')->where_in('id', $ids)->get($table)->result_array();
        $map = [];
        foreach ($rows as $r) $map[$r['id']] = $r['name'];
        return $map;
    }

    private function get_product_name($product_id) {
        $q = $this->CI->db->select('name')->from('ec_product')->where('id', $product_id)->get();
        if ($q->num_rows()) {
			return (string)$q->row()->name;
		}
		$q2 = $this->CI->db->select('name')->from('products')->where('id', (int)$product_id)->get();
		return $q2->num_rows() ? (string)$q2->row()->name : '';
    } 
 
    public function apply_coupon($code) {
        $q = $this->CI->db->where('code', $code)->where('status', 1)->get('coupons');
        if (!$q->num_rows()) {
            return ['success' => false, 'message' => 'Invalid coupon'];
        }

        $coupon = $q->row();
        $discount = ($coupon->discount_type == 'percent')
            ? ($this->CI->cart->total() * $coupon->discount / 100)
            : $coupon->discount;

        return [
            'success'  => true,
            'discount' => $discount,
            'final_total' => max(0, $this->CI->cart->total() - $discount)
        ];
    }
}

