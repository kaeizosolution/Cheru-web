<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class ProductService
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Product_model');
        $this->CI->load->model('Query_model');
        $this->CI->load->library('ImageUploadService');
    }

    public function save_product($post_data, $files=null, $id=null)
    {
        $this->CI->load->library('upload');
        $post = $this->CI->input->post();

        $productData = $this->prepareBasicData($post, $id);
        $existing = $id ? $this->CI->Query_model->get_data_obj('ec_product', ['id'=>$id]) : (object) [];

        $productData['images'] = $this->handleImages($existing, $_FILES);
        $productData['modifiers'] = $this->handleModifiers($existing, $post);
        $productData['extras'] = $this->handleExtras($existing, $post);
        $productData['prices'] = $this->handlePrices($existing, $post, $_FILES);
		
		#print "id=== $id\n";
		#print_r($productData); 
		#exit;
        if ($id) {
            $this->CI->Product_model->updateProduct($id, $productData);
        } else {
            $id = $this->CI->Product_model->insertProduct($productData);
        }

        return ['success' => true, 'id' => $id];
    }

    private function prepareBasicData($post, $id)
    {
        $data = [
            'name' 				=> $post['name'],
            'product_category' 	=> $post['product_category'],
            'category_id' 		=> $post['category_id'],
            'sub_cat_id' 		=> $post['sub_cat_id'],
            'food_type' 		=> $post['food_type'],
            'short_description' => $post['short_description'],
            'product_type' 		=> $post['product_type'],
            'tax' 				=> $post['tax'] ?? null,
            'sales_duration' 	=> $post['sales_duration'],
        ];

        return $data;
    }

    private function handleImages($existing, $files)
    {
        $existingImages = json_decode($existing->images ?? '[]');
		$inputKey = $this->getImageInputKey($_POST['product_type']);
        $newImages = $this->CI->imageuploadservice->uploadMultiple($files[$inputKey], 'products');
        return json_encode(array_merge($existingImages, $newImages));
    }

	private function getImageInputKey($product_type)
	{
    	if ($product_type === 'attribute') {
        	return 'attribute_image';
    	}
    	return 'image';
	}	

    private function handleModifiers($existing, $post)
    {
        $existingModifiers = json_decode($existing->modifiers ?? '[]');
        $incoming = [];
        foreach ($post['modifier_price'] ?? [] as $id => $price) {
            $incoming[] = ['id' => $id, 'price' => $price];
        }

        $map = [];
        foreach (array_merge($existingModifiers, $incoming) as $m) {
            $modId = is_array($m) ? $m['id'] : $m->id;
            $modPrice = is_array($m) ? $m['price'] : $m->price;
            $map[$modId] = ['id' => $modId, 'price' => $modPrice];
        }

        return json_encode(array_values($map));
    }

    private function handleExtras($existing, $post)
    {
        $existingExtras = json_decode($existing->extras ?? '[]');
        $incoming = [];
        foreach ($post['extra_price'] ?? [] as $id => $price) {
            $incoming[] = ['id' => $id, 'price' => $price];
        }

        $map = [];
        foreach (array_merge($existingExtras, $incoming) as $e) {
            $extraId = is_array($e) ? $e['id'] : $e->id;
            $extraPrice = is_array($e) ? $e['price'] : $e->price;
            $map[$extraId] = ['id' => $extraId, 'price' => $extraPrice];
        }

        return json_encode(array_values($map));
    }

    private function handlePrices($existing, $post, $files)
    {
        $existingPrices = json_decode($existing->prices ?? '[]');
        $newPrices = [];

        if ($post['product_type'] === 'attribute') {
            foreach ($post['attribute_name'] as $i => $name) {
                if (!$name) continue;
				$uploadedImages = $this->CI->imageuploadservice->uploadAttributeImagesByIndex(
            		$files['attribute_image'], 
            		$i, 
            		'attributes'
        			);

				$images = $uploadedImages;
        		if (empty($uploadedImages) && !empty($existingPrices[$i]->images)) {
            		$images = $existingPrices[$i]->images;
        		} 

        		$newPrices[] = [
            		'name' => $name,
            		'regular_price' => $post['attribute_regular_price'][$i],
            		'sales_price' => $post['attribute_sales_price'][$i],
            		'images' => $images
        		];
            }
        } else {
			$inputKey = $this->getImageInputKey($post['product_type']);
    		$uploadedImages = $this->CI->imageuploadservice->uploadMultiple($files[$inputKey], 'products');

    		if (empty($uploadedImages) && !empty($existingPrices[0]->images)) {
        		$images = $existingPrices[0]->images;
    		} else {
        		$images = $uploadedImages;
    		}

    		if (!empty($post['regular_price']) || !empty($post['sales_price']) || !empty($images)) {
        		$newPrices[] = [
            		'name' 			=> 'default',
            		'regular_price' => $post['regular_price'],
            		'sales_price' 	=> $post['sales_price'],
            		'images' 		=> $images
        		];
    		}
        }

        #return json_encode(array_merge($existingPrices, $newPrices));
		return json_encode($newPrices);
    }
}

