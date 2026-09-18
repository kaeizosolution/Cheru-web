<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Posts_model extends MY_Model{

    var $post_column_search = array('post_title','modified','enabled');
    var $post_column_alias = array(); //set column field database

    function __construct() {
        parent::__construct();
    }

    public function get_post_datatable() 
    {
        $this->slave->select('*');
        $this->slave->from('ec_posts');      
        $this->get_post_datatables_query();
        $this->slave->order_by('post_id', 'desc');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $user = $this->slave->get()->result();
		//echo $this->slave->last_query();exit;
        return $user;
    }

    public function get_post_datatables_query()
    {
        if(isset($_POST['filter'])){
			$this->slave->group_start();
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->post_column_search) && (is_array($col_value) || $col_value != '')) {
					
                    $column_name_alias = $col_name;
                    if(isset($this->post_column_alias[$col_name])){
                        $column_name_alias =  $this->post_column_alias[$col_name].".".$col_name;
                    }
                    if(is_array($col_value)){
                        $this->slave->where_in($column_name_alias, $col_value);
                    }else{
                        $this->slave->like($column_name_alias, $col_value); 
                    }
                }
            }
            $this->slave->group_end();
        }
    }
	public function get_datatables_query()
    {
        if(isset($_POST['filter'])){
          
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search) && (is_array($col_value) || $col_value != '')) {
					  $this->slave->group_start();
                    $column_name_alias = $col_name;
                    if(isset($this->column_alias[$col_name])){
                        $column_name_alias =  $this->column_alias[$col_name].".".$col_name;
                    }
                    if(is_array($col_value)){
                        $this->slave->where_in($column_name_alias, $col_value);
                    }else{
                        $this->slave->like($column_name_alias, $col_value); 
                    }
                }
            }
            $this->slave->group_end();
        }
    }

    public function count_filtered()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_admin');
        //$this->slave->where('status', '1');
        $this->slave->where('super_admin', 0);
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
	public function post_count_filtered()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_posts');
        $this->get_post_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	public function post_count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_posts');        
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
    public function count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_admin');
        //$this->slave->where('status', '1');
        $this->slave->where('super_admin', 0);
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	 public function edit($post_id)
    {
        $this->slave->select('*');
        $this->slave->from('ec_posts');
        $this->slave->where("post_id='".$post_id."'");
        $postArray = $this->slave->get()->row();
        return $postArray;
    }
	 public function delete_post($product_id)
    {
        $this->master->where('post_id',$product_id);
        $status = $this->master->delete('ec_posts');
        return ($this->master->affected_rows() > 0) ? TRUE : FALSE;
    }
}
