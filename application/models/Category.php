<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends CI_Model{


    function __construct() {
        parent::__construct();
    }
	
	var $table = 'categories';
	//var $column_search = array('name','discount','start_date','end_date','enabled','date_added');
	var $column_search = array('name', 'status');  
    var $column_alias = array(); //set column field database
	
	
	/*
	function find($limit = null, $offset = 0, $conditions = array()){
		$categories = $this->db->where($conditions)->order_by('name','asc')->get($this->table, $limit, $offset)->result_array();
		return $categories;
	}

	function find_active(){
		$this->db->select('c.*');
		$this->db->join('categories c','pc.category_id=c.id');
		$this->db->join('posts p','pc.post_id=p.id');
		$this->db->where('c.status',1);
		$this->db->where('p.status',1);
		$this->db->group_by('pc.category_id');
		$this->db->order_by('c.name','asc');
		$categories = $this->db->get('posts_categories pc')->result_array();
		return $categories;
	}
	*/
	function create($category){
		$category['slug'] = url_title($category['name'],'-',true);
		$this->db->insert($this->table, $category);
	}

	function update($category,$id){
		$category['slug'] = url_title($category['name'],'-',true);
		$this->db->where('id',$id);
		$this->db->update($this->table,$category);
	}

	function delete($id){
		$this->db->where('id',$id);
		$this->db->delete($this->table);
	}
	
	function find_by_id($id){
		$this->db->where('id',$id);
		return $this->db->get($this->table,1)->row_array();
	}
	


	function find_by_slug($id){
		$this->db->where('slug',$id);
		return $this->db->get($this->table,1)->row_array();
	}

	function find_list(){
		$this->db->order_by('name','asc');
		$query = $this->db->get($this->table);
        $data = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[$row['id']] = $row['name'];
            }
        }
        return $data; 
	}
	function find_list_prod(){
		$this->db->order_by('name','asc');
		$query = $this->db->get($this->table);
        $data = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[$row['id']] = $row['name'];
            }
        }
        return $data; 
	}

	
	
	//filter data
	
	
	public function get_datatable()
    {
        $this->db->select('*');
        $this->db->from($this->table);
        //$this->db->where('status', '1');
        //$this->slave->where('super_admin', 0);
        $this->get_datatables_query();
        $this->db->order_by('id', 'DESC');
        $this->db->limit($_POST['length'], $_POST['start']);
        $user = $this->db->get()->result();
        return $user;
    }
	
	
	
    public function get_datatables_query()
    {
        if(isset($_POST['filter'])){
            $this->db->group_start();
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search) && (is_array($col_value) || $col_value != '')) {
                    $column_name_alias = $col_name;
                    if(isset($this->column_alias[$col_name])){
                        $column_name_alias =  $this->column_alias[$col_name].".".$col_name;
                    }
                    if(is_array($col_value)){
                        $this->db->where_in($column_name_alias, $col_value);
                    }else{
                        $this->db->like($column_name_alias, $col_value); 
                    }
                }
            }
            $this->db->group_end();
        }
    }

    public function count_filtered()
    {
        $this->db->select('COUNT(*) CNT');
        $this->db->from($this->table);
        //$this->db->where('status', '1');
       // $this->db->where('super_admin', 0);
        $this->get_datatables_query();
        $query = $this->db->get()->row();
        return $query->CNT;
    }

    public function count_all()
    {
        $this->db->select('COUNT(*) CNT');
        $this->db->from($this->table);
        //$this->db->where('status', '1');
        //$this->slave->where('super_admin', 0);
        $query = $this->db->get()->row();
        return $query->CNT; 
    }
	
	
}
