<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Productt extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->library('image_upload');
        $this->load->model('Query_model');
    }

    function index()
    {
        $this->load->view('/vendor/product/save_product5');
    }


    public function save()
    {
        if(!$this->input->is_ajax_request()){
            exit('No direct script access allowed');
        }

        $post = $this->input->post();

        $this->db->trans_begin();

        try{

            $product = [
                'name' => $post['product_name'],
                'description'  => $post['description'],
                'product_type' => $post['product_type']
            ];

            $product_id = $this->Query_model->insert_data('products',$product);

            if(!$product_id){
                throw new Exception("Product insert failed");
            }

            if($post['product_type'] == 'simple'){
                $this->save_simple($product_id);
            }else{
                $this->save_variable($product_id);
            }

            if ($this->db->trans_status() === FALSE)
            {
                throw new Exception("Transaction failed");
            }

            $this->db->trans_commit();

            echo json_encode([
                'status'=>true,
                'message'=>'Product saved successfully'
            ]);

        }catch(Exception $e){

            $this->db->trans_rollback();

            echo json_encode([
                'status'=>false,
                'message'=>$e->getMessage()
            ]);
        }

    }


    private function save_simple($product_id)
    {
        $variation = [
            'product_id' => $product_id,
            'attr_json'  => json_encode([])
        ];

        $variation_id = $this->Query_model->insert_data('product_variations',$variation);

        $min   = $this->input->post('simple_min_qty');
        $max   = $this->input->post('simple_max_qty');
        $price = $this->input->post('simple_price');

        if(!empty($min))
        {
            foreach($min as $k=>$v)
            {
                $tier = [
                    'variation_id'=>$variation_id,
                    'min_qty'=>$min[$k],
                    'max_qty'=>$max[$k],
                    'price'=>$price[$k]
                ];

                $this->Query_model->insert_data('product_variation_price',$tier);
            }
        }

        // images
        $imgs = $this->image_upload->upload_multiple('simple_images');

        if(!empty($imgs))
        {
            foreach($imgs as $img)
            {
                $imgData = [
                    'variation_id'=>$variation_id,
                    'image_path'=>$img
                ];

                $this->Query_model->insert_data('product_variation_images',$imgData);
            }
        }
    }



    private function save_variable($product_id)
    {
        $vars = $this->input->post('vars');

        if(empty($vars)) return;

        foreach($vars as $key=>$var)
        {
            $variation = [
                'product_id'=>$product_id,
                'attr_json'=>json_encode($var['attr'])
            ];

            $variation_id = $this->Query_model->insert_data('product_variations',$variation);

            if(!empty($var['min_qty']))
            {
                foreach($var['min_qty'] as $i=>$min)
                {
                    $tier = [
                        'variation_id'=>$variation_id,
                        'min_qty'=>$var['min_qty'][$i],
                        'max_qty'=>$var['max_qty'][$i],
                        'price'=>$var['price'][$i]
                    ];

                    $this->Query_model->insert_data('product_variation_price',$tier);
                }
            }

            // upload variation images
            $fileKey = "v_img_".$key;

            if(isset($_FILES[$fileKey]))
            {
                $imgs = $this->image_upload->upload_multiple($fileKey);

                if(!empty($imgs))
                {
                    foreach($imgs as $img)
                    {
                        $imgData = [
                            'variation_id'=>$variation_id,
                            'image_path'=>$img
                        ];

                        $this->Query_model->insert_data('product_variation_images',$imgData);
                    }
                }
            }
        }
    }

}
