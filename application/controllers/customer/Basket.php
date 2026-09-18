<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Basket extends MY_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Product_model');
        $this->load->helper('text');		
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

## add to cart 		
    public function ajaxaddtocart()
    {
        $this->validation();		
        $data         = array();
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters();

        if ($this->form_validation->run() == FALSE)
        {
            echo json_encode(array('msg' => validation_errors(), 'success' => 0));
        }
        else{
            $data = $this->input->post();	
            $data = $this->security->xss_clean($data);
            $this->addtocart(array('DATA' => $data));		
        }   
    }  

    public function validation()
    {
        $this->load->helper(array('form', 'url'));		
        $this->load->library('form_validation');		
        $this->form_validation->set_error_delimiters('<div>', '</div>');          
        $this->form_validation->set_rules('quantity',' quantity', 'numeric|xss_clean');      
    }

    public function addtocart($args)
    {	
        $data['error'] = false;
        $newdata = array();
        if(isset($args['DATA']) && $args['DATA'])
        {
            $session_id = $_SESSION['__ci_last_regenerate'];
            $date = date("Y-m-d h:i:s"); //2021-01-05 10:06:16 
            $product_id = $this->input->post('product_id');
            $quantity = $this->input->post('quantity');
            $var_id_jason = $this->input->post('variation_id');	 
            //[{"title":"Redmi+Note+9+Pro+(Glacier+White,+4GB+RAM,+64GB+Storage)","Colour":"Champagne+Gold","Size":"Medium"}]
            if($var_id_jason == '0'){

                $product_obj = $this->Query_model->get_data_obj('ec_product',array('product_id' => $product_id));
                if($product_obj){
                    $product_name = trim($product_obj->post_title);
                    $total = ($product_obj->sale_price) ? $product_obj->sale_price : $product_obj->regular_price;
                }
                $var_name_json = '[{"title":"'.$product_name.'"}]';
                $product_name = $var_name_json;
            }else{

                $product_name = trim($this->input->post('product_name'));
                $var_name_json = $this->input->post('product_name');
                $total = $this->input->post('total');
            }

            $order_items['DATA']['order_item_name'] 	= $var_name_json;			
            $order_items['DATA']['order_item_type']	  	= "line_item";
            $order_items['DATA']['customer_id'] 		= $this->LOGIN_ID;
            $order_items['DATA']['session_id'] 			= $session_id;			

## if user loging
            if($this->LOGIN_ID){
                $item_obj = $this->Query_model->get_data_obj('ec_order_items',array('customer_id' => $this->LOGIN_ID, 'order_item_name' => $product_name,'order_id' => NULL));

                if(!empty($item_obj) && $item_obj->order_item_name==$product_name){

#ec_order_item tables update

                    $item_arr = $this->Query_model->get_data_obj('ec_order_item',array('customer_id' => $this->LOGIN_ID, 'order_item_id' => $item_obj->order_item_id,'order_id' => NULL));
                    $item_arr_quantity = isset($item_arr->quantity) ? $item_arr->quantity : 0;

                    $qty = $item_arr_quantity + $quantity;
                    $db_data1 = array(
                            'quantity'       		=> $qty,
                            'total'       => $total,
                            );

                    $get_cart = $this->session->userdata('cart');
                    $get_cart[$item_obj->order_item_id]['quantity'] = $qty;
                    $this->session->set_userdata('cart', $get_cart);

                    $this->Query_model->update_data('ec_order_item',$db_data1,array('order_item_id' => $item_obj->order_item_id));

                    $db_data2 = array(
                            '_qty'       => $qty,
                            );
                    $update = $this->Query_model->update_data('ec_order_itemmeta',$db_data2,array('order_item_id' => $item_obj->order_item_id,'order_id' => NULL));

                    if($update){
                        $cart_item = cart_item_count();
                        echo json_encode(array('msg' => 'Product update successfully', 'success' => 1, 'data' => array('cart_count' => $cart_item)));
                    }

                }else{
                    $last_inserted_id  =  $this->Query_model->insert_data('ec_order_items',$order_items['DATA']);
                    $order_product['DATA']['order_item_id'] 		= $last_inserted_id;
                    $order_product['DATA']['order_item_name']       = $var_name_json;
                    $order_product['DATA']['product_id'] 			= $product_id;
                    $order_product['DATA']['quantity'] 			= $quantity;
                    $order_product['DATA']['total'] 	= $total;
                    $order_product['DATA']['variation_id'] 			= $var_id_jason;
                    $order_product['DATA']['customer_id'] 			= $this->LOGIN_ID;
                    $order_product['DATA']['date_created'] 			= $date;

                    $cart_details = $this->session->userdata('cart');
                    $cart_details[$order_product['DATA']['order_item_id']] = $order_product['DATA'];
                    $this->session->set_userdata('cart', $cart_details);	
                    unset($order_product['DATA']['order_item_name']);
                    $order_product  =  $this->Query_model->insert_data('ec_order_item',$order_product['DATA']);

                    $order_itemmeta['DATA']['order_item_id'] 	= $last_inserted_id;
                    $order_itemmeta['DATA']['product_id'] 		= $product_id;
                    $order_itemmeta['DATA']['_qty'] 			= $quantity;			
                    $order_itemmeta['DATA']['_variation_id'] 	= $var_id_jason;	
                    $order_item_meta  =  $this->Query_model->insert_data('ec_order_itemmeta',$order_itemmeta['DATA']);
                }
            }else{
                $item_obj = $this->Query_model->get_data_obj('ec_order_items',array('session_id' => $session_id, 'order_item_name' => $product_name,'order_id' => NULL));
                if(!empty($item_obj) && $item_obj->order_item_name==$product_name){
                    $get_cart = get_cart();

                    $item_arr = $this->Query_model->get_data_obj('ec_order_item',array('session_id' => $session_id, 'order_item_id' => $item_obj->order_item_id,'order_id' => NULL));
                    $item_arr_quantity = isset($item_arr->quantity) ? $item_arr->quantity : 0;
                    $qty = $item_arr_quantity + $quantity;
                    $db_data1 = array(
                            'quantity'       => $qty,
                            );

                    $get_cart[$item_obj->order_item_id]['quantity'] = $qty;
                    $this->session->set_userdata('cart', $get_cart);
                    $this->Query_model->update_data('ec_order_item',$db_data1,array('order_item_id' => $item_obj->order_item_id));

                    $db_data2 = array(
                            '_qty'       => $qty,
                            );

                    $update = $this->Query_model->update_data('ec_order_itemmeta',$db_data2,array('order_item_id' => $item_obj->order_item_id));
                    if(1){
                        $cart_item = cart_item_count();
                        echo json_encode(array('msg' => 'updated!', 'success' => 1, 'data' => array('cart_count' => $cart_item)));
                    }
                }else{
                    $last_inserted_id  =  $this->Query_model->insert_data('ec_order_items',$order_items['DATA']);
                    $order_product['DATA']['order_item_id'] 		= $last_inserted_id;
                    $order_product['DATA']['order_item_name']   	= $var_name_json;
                    $order_product['DATA']['session_id'] 			= $session_id;
                    $order_product['DATA']['product_id'] 			= $product_id;
                    $order_product['DATA']['quantity'] 			= $quantity;
                    $order_product['DATA']['total'] 	= $total;				
                    $order_product['DATA']['date_created'] 			= $date;	
                    $order_product['DATA']['variation_id'] 			= $var_id_jason;
                    $order_product['DATA']['subtotal'] = 0;
                    $order_product['DATA']['coupon_amount'] 		= 0;				
                    $order_product['DATA']['tax'] 			= 0;
                    $order_product['DATA']['shipping']		= 0;

                    $cart_details = $this->session->userdata('cart');	

                    $cart_details[$order_product['DATA']['order_item_id']] = $order_product['DATA'];															
                    $this->session->set_userdata('cart', $cart_details);

                    unset($order_product['DATA']['order_item_name']);
                    unset($order_product['DATA']['order_id']);
                    unset($order_product['DATA']['subtotal']);
                    unset($order_product['DATA']['coupon_amount']);
                    unset($order_product['DATA']['tax']);
                    unset($order_product['DATA']['shipping']);

                    $order_product  =  $this->Query_model->insert_data('ec_order_item',$order_product['DATA']);
                    $order_itemmeta['DATA']['order_item_id'] 	= $last_inserted_id;
                    $order_itemmeta['DATA']['product_id'] 		= $product_id;
                    $order_itemmeta['DATA']['_qty'] 			= $quantity;	
                    $order_itemmeta['DATA']['_variation_id'] 	= $var_id_jason;	

                    $order_item_meta  =  $this->Query_model->insert_data('ec_order_itemmeta',$order_itemmeta['DATA']);
                }
            } //end logoout

            $this->load->library('mailer');
            //$useremail = $email;
            $email = 'jpforever1999@gmail.com';
            $useremail = 'jpforever1999@gmail.com';
            $admin_email = 'jay@move2inbox.in';//admin@viralbake.com

            $msg = '<p style="text-align:center;">Thanks for purchase this product! We will be in touch with you shortly.</p>';

            $admin_msg = '<div style="text-align:center;"><p>'.$useremail.' has purchase product on klentano. <br ><br >Here is the submitted form data</p><table style="width:100%" border="1px"cellspacing="0" cellpadding="10"><tr><td>Email</td><td>'.$email.'</td> </tr> </table></div>';

            $this->mailer->smtp(array('SUBJECT' => 'Thank You purchase', 'EMAIL' => $useremail, 'CONTENT' => $msg));
            $this->mailer->smtp(array('SUBJECT' => 'Klentano purchase', 'EMAIL' => $admin_email, 'CONTENT' => $admin_msg));

            if(isset($last_inserted_id) && $last_inserted_id!='' )
            {
                $cart_item = cart_item_count();
                echo json_encode(array('msg' => 'added!', 'success' => 1, 'data' => array('cart_count' => $cart_item)));
            }
        }
    }

    public function update_cart_qty()
    {
        $cart_details = $this->session->userdata('cart');
        $data['csrf'] = csrf_token();
        $order_itemid = $this->input->post('item_id');	
        $order_quantity = $this->input->post('quantity');

        if(isset($cart_details) && isset($cart_details[$order_itemid])){
            $cart_details[$order_itemid]['quantity'] = $order_quantity;
            $this->session->set_userdata('cart', $cart_details);
        }
        //exit;
        $data['csrf'] = csrf_token();
        $data = array();
        $data = array(
                'quantity' => $order_quantity,
                );		
        $update_cartqty = $this->Query_model->update_data('ec_order_item',$data,array('order_item_id' => $order_itemid));

        $data_itemmeta = array(
                '_qty' => $order_quantity,
                );

        $update_order_itemmeta = $this->Query_model->update_data('ec_order_itemmeta',$data_itemmeta,array('order_item_id' => $order_itemid));

        if($update_order_itemmeta){
            $cart_item = cart_item_count();
            echo json_encode(array('msg' => 'Item updated', 'success' => 1, 'data' => array('cart_count' => $cart_item)));
        }
    }

    public function del()
    {
        $data['csrf'] = csrf_token();
        $order_item_id = $this->input->post('id');		
        $data['csrf'] = csrf_token();
        $data = array();
        $delete = $this->Product_model->delete_cart($order_item_id);

        $cart_iterm = $this->session->userdata('cart');
        unset($cart_iterm[$order_item_id]);
        $this->session->set_userdata('cart',$cart_iterm);			
        echo json_encode(array('success' => $delete));
    }

    public function proceed_checkout()
    {
        $this->session->set_userdata('proceed_checkout',$this->input->post('cart_item'));
    }
}





















