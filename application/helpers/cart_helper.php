<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('merge_cart')) {
    function merge_cart($user_id) {
        $CI =& get_instance();
        $CI->load->library('cart');
        $CI->load->model('Cart_model');

        $session_cart = $CI->cart->contents();

        $user_cart = $CI->Cart_model->get_user_cart($user_id);

        foreach ($user_cart as $item) {
            $found = false;

            foreach ($session_cart as $rowid => $session_item) {
                if ($session_item['id'] == $item['id'] && $session_item['options'] == $item['options']) {
                    $CI->cart->update([
                        'rowid' => $rowid,
                        'qty'   => $session_item['qty'] + $item['qty']
                    ]);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $CI->cart->insert($item);
            }
        }

        $CI->Cart_model->save_cart($user_id, $CI->cart->contents());
    }
}

