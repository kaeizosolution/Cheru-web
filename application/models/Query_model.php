<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Query_model extends MY_Model{
    
    var $column_search = array('fname','lname', 'email', 'mobile' ,'status','date_added'); 
    var $column_alias = array(); //set column field database

	
    function __construct() {
        parent::__construct();
    }   

    public function insert_data($table_name,$data)
    {
        $this->master->insert($table_name,$data);
        return  $this->master->insert_id();
    }

	public function insert_batch($table_name, $data)
    {
        $status = $this->master->insert_batch($table_name, $data);
        return $status;
    }
	

    public function update_data($table_name,$data,$condition)
    {
        foreach($condition as $column => $value){
            if(is_array($value)){
                $this->master->where_in($column, $value);
            }else{
                $this->master->where($column, $value);
            }
        }
        $this->master->update($table_name,$data);
	    //echo $this->master->last_query(); exit;
        return ($this->master->affected_rows() > 0) ? TRUE : FALSE;
		
    }

    public function get_data($table_name,$condition = NULL,$order = NULL,$limit = NULL)
	{
		if($table_name === 'ec_product' && is_array($condition) && $condition){
			if(isset($condition['product_uid']) && !isset($condition['id'])){
				$condition['id'] = (int)$condition['product_uid'];
				unset($condition['product_uid']);
			}
			if(isset($condition['product_id']) && !isset($condition['id'])){
				$condition['id'] = (int)$condition['product_id'];
				unset($condition['product_id']);
			}
			if(isset($condition['post_slug']) && !isset($condition['id'])){
				$slug_val = $condition['post_slug'];
				unset($condition['post_slug']);
				if(is_numeric((string)$slug_val) && (string)$slug_val !== ''){
					$condition['id'] = (int)$slug_val;
				}else{
					$condition['id'] = 0;
				}
			}
			if(isset($condition['enabled']) && !isset($condition['status'])){
				$condition['status'] = ((string)$condition['enabled'] === '1') ? '1' : '0';
				unset($condition['enabled']);
			}
		}
		$this->slave->select('*');
		$this->slave->from($table_name);
		$this->get_datatables_query();
		if($condition){
            foreach($condition as $column => $value){
                if(is_array($value)){
                    $this->slave->where_in($column, $value);
                }else{
                    $this->slave->where($column, $value);
                }
            }
        }
        if($order){
            foreach($order as $column => $value){
                $this->slave->order_by($column, $value);
            }
        }
        if($limit){
            if(isset($limit['start'])){
                $this->slave->limit($limit['length'], $limit['start']);
            }else{
                $this->slave->limit($limit['length']);
            }
        }

        $obj = $this->slave->get()->result();
        #echo $this->slave->last_query(); exit;
        return $obj;
    }

    public function get_datatables_query() 
    {
         $i = 0;
        if(isset($_POST['filter'])){
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search) && $col_value != '') {
                    if($i===0){
                        $this->slave->group_start();
                    }
                    if($col_name == 'name'){
                        $this->slave->like("CONCAT_WS(' ',fname, lname)", $col_value, "both");

                    }
                    else{
                        $column_name_alias = $col_name;
                        if(isset($this->column_alias[$col_name])){
                            $column_name_alias =  $this->column_alias[$col_name].".".$col_name;
                        }
                        $this->slave->like($column_name_alias, $col_value);
                    }
                    $i++;
                }
            }
            if($i > 0){
                $this->slave->group_end();
            }
        }
    }
	
    public function get_data_obj($table_name,$condition = NULL,$order = NULL,$limit = NULL)
	{
		if($table_name === 'ec_product' && is_array($condition) && $condition){
			if(isset($condition['product_uid']) && !isset($condition['id'])){
				$condition['id'] = (int)$condition['product_uid'];
				unset($condition['product_uid']);
			}
			if(isset($condition['product_id']) && !isset($condition['id'])){
				$condition['id'] = (int)$condition['product_id'];
				unset($condition['product_id']);
			}
			if(isset($condition['post_slug']) && !isset($condition['id'])){
				$slug_val = $condition['post_slug'];
				unset($condition['post_slug']);
				if(is_numeric((string)$slug_val) && (string)$slug_val !== ''){
					$condition['id'] = (int)$slug_val;
				}else{
					$condition['id'] = 0;
				}
			}
			if(isset($condition['enabled']) && !isset($condition['status'])){
				$condition['status'] = ((string)$condition['enabled'] === '1') ? '1' : '0';
				unset($condition['enabled']);
			}
		}
		$this->slave->select('*');
		$this->slave->from($table_name);
		if($condition){
			foreach($condition as $column => $value){
				if(is_array($value)){
                    $this->slave->where_in($column, $value);
                }else{
                    $this->slave->where($column, $value);
                }
            }
        }
        if($order){
            foreach($order as $column => $value){
                $this->slave->order_by($column, $value);
            }
        }
        if($limit){
            if(isset($limit['start'])){
                $this->slave->limit($limit['length'], $limit['start']);
            }else{
                $this->slave->limit($limit['length']);
            }
        }
	    
        $obj = $this->slave->get()->result();
        //echo $this->slave->last_query(); exit; 
        if($obj && count($obj) == 1){
            $obj = $obj[0];
        }
        return $obj;
    }

    public function get_author($id)
    { 
        $this->slave->select('*');
        $this->slave->from('ec_admin');
        $this->slave->where('admin_id', $id);        
        $user = $this->slave->get()->row();
        return $user;
    }

    public function get_attr_vari_item($product_id=null)
    {	
        $sql="select a.*,b.attribute_id, c.name as attribute_name, b.name from ec_product_variation a,ec_attribute_item b,ec_attribute c 
            where a.attribute_item_id = b.attribute_item_id and c.attribute_id=b.attribute_id and a.product_id=$product_id";	
        $query  =  $this->db->query($sql);
        $data   =  $query->result();
        return $data;
    }

    public function get_product_attr_item($product_id=null)
    {
        $sql="select a.*,b.attribute_id, c.name as attribute_name, b.name from ec_product_attribute a,ec_attribute_item b,ec_attribute c
            where a.attribute_item_id = b.attribute_item_id and c.attribute_id=b.attribute_id and a.product_id=$product_id";
        $query  =  $this->db->query($sql);
        $data   =  $query->result();
        return $data;
    }

    public function category($product_id=null)
    {	
        $sql="select group_concat(category_id) category_id from ec_product_categories where post_id=$product_id group by post_id";	
        $query  =  $this->db->query($sql);
        $data   =  $query->row();
        return $data;
    } 

    public function related_post($product_id=null)
    {
        $category = $this->Query_model->category($product_id);
        $category_id = $category->category_id;
        $data = array();	
        if(isset($category_id)){	
            $sql= "select a.*, b.category_id from ec_product a,ec_product_categories b 
                where b.category_id IN($category_id) and b.post_id !=$product_id and a.product_id = b.post_id group by a.product_id 
                ORDER BY a.product_id DESC limit 10"; 	

                $query  =  $this->db->query($sql);
            $data   =  $query->result();
        }
        return $data;
    }

    public function count_filtered($table_name = NULL, $condition = NULL)
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from($table_name);
        if($condition){
            foreach($condition as $column => $value){
                if(is_array($value)){
                    $this->slave->where_in($column, $value);
                }else{
                    $this->slave->where($column, $value);
                }
            }
        }
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all($table_name = NULL , $condition = NULL)
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from($table_name);
        if($condition){
            foreach($condition as $column => $value){
                if(is_array($value)){
                    $this->slave->where_in($column, $value);
                }else{
                    $this->slave->where($column, $value);
                }
            }
        }
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function find_by_id($table_name, $condition = NULL,$order = NULL,$limit = NULL)
    {
        $this->slave->select('*');
        $this->slave->from($table_name);
        if($condition){
            foreach($condition as $column => $value){
                if(is_array($value)){
                    $this->slave->where_in($column, $value);
                }else{
                    $this->slave->where($column, $value);
                }
            }
        }

        $obj = $this->slave->get()->result();
        if($obj && count($obj) == 1){
            $obj = $obj[0];
        }
        return $obj;
    }


    public function delete_query($table = NULL, $condition = NULL)
    {
        if($condition){
            foreach($condition as $column => $value){
                if(is_array($value)){
                    $this->master->where_in($column, $value);
                }else{
                    $this->master->where($column, $value);
                }
            }
        }
        $status = $this->master->delete($table);
        return ($this->master->affected_rows() > 0) ? TRUE : FALSE;
    }
    
    public function get_contact()
    {
        $this->slave->select('*');
        $this->slave->from('ec_enquiry');
        $this->slave->where('custype','contact');
		//$this->get_cform_datatables_query();
        return  $this->slave->get()->result();
    }

	public function get_locator($args)
   	{
    	$lat = $args['lat']; $long = $args['long']; $distance = $args['distance'];

       $this->slave->select(" * , admin_uid as vendor_uid, fname as name, (6371 * 2 * ASIN(SQRT( POWER(SIN(( '$lat' - latitude) *  pi()/180 / 2), 2) +COS( '$lat' * pi()/180) * COS(latitude * pi()/180) * POWER(SIN(( '$long' - longitude) * pi()/180 / 2), 2) ))) as distance");
        $this->slave->from("ec_admin");
        $this->slave->where("role_id", '2');
        $this->slave->where("status", '1');
        $this->slave->having('distance <=', $distance);
        $this->slave->order_by('distance', 'ASC');
		if(isset($args['limit']) && $args['limit'])
		{
			$this->slave->limit($args['limit'], '0');
		}

        $obj = $this->slave->get()->result();
        return $obj;
	}

    public function get_last_id($table_name, $condition = NULL, $order = NULL,$limit = NULL)
    {
        $this->slave->select('vehicle_id');
        $this->slave->from($table_name);
        $this->slave->insert_id();
        $obj = $this->slave->get()->result();
        return $obj;
    }

    public function get_data_group_by($args)
    {
        $table_name = $args['table_name']; $condition = $args['condition'];
        $group_by   = $args['group_by']; $fields = isset($args['fields']) ? $args['fields'] : '';
        $obj = []; 
        if($condition)
        {
            $fields = $fields ? $fields : '*';
            $this->slave->select($fields);
            $this->slave->from($table_name);

            foreach($condition as $column => $value)
            {
                if(is_array($value))
                $this->slave->where_in($column, $value);
                else
                $this->slave->where($column, $value);
                
            }
        
            $this->slave->group_by($group_by);
            $obj = $this->slave->get()->result();
            return $obj;
        }
	} 
	
	public function sum_data($args)
    {
        $field = $args['field']; $table_name = $args['table_name'];
        $condition = $args['condition'];        

        $this->slave->select_sum($field);
        $this->slave->select($field);
        $this->slave->from($table_name);
    
        if($condition)
        {
            foreach($condition as $column => $value)
            {
                if(is_array($value))
                $this->slave->where_in($column, $value);
                else
                $this->slave->where($column, $value);
         
            }
        }
        
        $obj = $this->slave->get()->row();
        return $obj;
    }	
}
