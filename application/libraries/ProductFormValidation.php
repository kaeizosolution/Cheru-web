<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ProductFormValidation
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('form_validation');
    }
	
	public function run()
	{
    $this->CI->form_validation->set_rules('name', 'Product Name', 'required|trim|min_length[2]|xss_clean');
    $this->CI->form_validation->set_rules('category_id', 'Category', 'required|integer');
    #$this->CI->form_validation->set_rules('sub_cat_id', 'Sub Category', 'required|integer');
    $this->CI->form_validation->set_rules('short_description', 'Short Description', 'trim|xss_clean');

    $product_type = $this->CI->input->post('product_type');
    if ($product_type === 'attribute') {
        $attributes = $this->CI->input->post('attribute_name');
        $reg_prices = $this->CI->input->post('attribute_regular_price');
        $sale_prices = $this->CI->input->post('attribute_sales_price');

        if (!empty($attributes)) {
            foreach ($attributes as $index => $attr) {
                $this->CI->form_validation->set_rules("attribute_name[{$index}]", "Attribute Name #".($index+1), 'required|trim');
                $this->CI->form_validation->set_rules("attribute_regular_price[{$index}]", "Regular Price #".($index+1), 'required|numeric|greater_than_equal_to[0]');
                $this->CI->form_validation->set_rules("attribute_sales_price[{$index}]", "Sales Price #".($index+1), 'required|numeric|greater_than_equal_to[0]');
            }
        }
    } else {
        $this->CI->form_validation->set_rules('regular_price', 'Regular Price', 'required|numeric|greater_than_equal_to[0]');
        $this->CI->form_validation->set_rules('sales_price', 'Sales Price', 'required|numeric|greater_than_equal_to[0]');
    }
	return $this->CI->form_validation->run();
	
	}


}

