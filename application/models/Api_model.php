<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api_model extends MY_Model {
	
	function __construct()
	{
		parent::__construct();
	}

    public function get_customer($APP_ID,$SECRET_KEY)
    {
        return 1;
    }

    public function find_data_by_args($table,$args)
    {
/*
        $args = array('requirement_id' => $requirement_id, 'customer_id' => $customer_id);
        $data_obj = $this->Api_model->find_requirement_by_args('li_delivery_address',$args);
*/
        $this->slave->select('*');
        $this->slave->from($table);
        foreach($args as $column => $value){
            $this->slave->where($column, $value);
        }
        $obj = $this->slave->get()->row();
        return $obj;
    }

}
