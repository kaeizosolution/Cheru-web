<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shipping_model extends MY_Model{
	
	private $_table = null;
    private $_select = '*';
    private $_join = [];
    private $_where = [];
	
    var $column_search = array('name','status','apply_date','email','mobile');
	// var $column_search = array('status','apply_date');
    var $column_alias = array(); //set column field database

    function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

    public function get_datatable()
    {
        $this->slave->select('*');
        $this->slave->from('ec_shipping');
        $this->slave->where('login_id', $this->LOGIN_ID);
        #$this->slave->where('status', '1');
        $this->get_datatables_query();
        $this->slave->order_by('date_added', 'desc');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $user = $this->slave->get()->result();
        return $user;
    }

    public function get_datatables_query()
    {
        if(isset($_POST['filter'])){
            $this->slave->group_start();
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search) && (is_array($col_value) || $col_value != '')) {
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
        $this->slave->from('ec_shipping');
        #$this->slave->where('login_id', $this->LOGIN_ID);
        #$this->slave->where('status', '1');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_shipping');
        #$this->slave->where('login_id', $this->LOGIN_ID);
        #$this->slave->where('status', '1');
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
	/***************Custom function**********/
	public function table($tbl)
    {
        $this->_table = $tbl;
        $this->_select = '*';
        $this->_join = [];
        $this->_where = [];
       return $this;
    }

    public function select($cols)
    {
        $this->_select = $cols;
       return $this;
    }

    public function join($tbl,$col)
    {
       $this->_join[] = ['table'=>$tbl,'col'=>$col];
       return $this;
    }

     public function where($col, $operator, $val = null)
    {
        if(!$val)
            $this->_where[] = ['col'=>$col,'operator'=>'=','value'=>$operator];
        else
            $this->_where[] = ['col'=>$col,'operator'=>$operator,'value'=>$val]; 

       return $this;
    }

    public function get(){
        $this->slave->select($this->_select);
        $this->slave->from($this->_table);
        
        if(count($this->_join)){
            foreach ($this->_join as $key => $join) {
                $this->slave->join($join['table'],$join['col']);
            }
        }

        if(count($this->_where)){
            foreach ($this->_where as $key => $whr) {
               $this->slave->where($whr['col'].$whr['operator'],$whr['value']);
            }
        }


        return $this->slave->get()->result();
    }


    public function one(){
        $this->slave->select($this->_select);
        $this->slave->from($this->_table);
        
        if(count($this->_join)){
            foreach ($this->_join as $key => $join) {
                $this->slave->join($join['table'],$join['col']);
            }
        }

        if(count($this->_where)){
            foreach ($this->_where as $key => $whr) {
               $this->slave->where($whr['col'].' '.$whr['operator'],$whr['value']);
            }
        }

		
        $obj = $this->slave->get()->result();
		//return $this->slave->last_query();
		//echo "<pre>"; print_r($obj); echo "</pre>";
        if($obj && count($obj)){
            $obj = $obj[0];
        }
        return $obj;
    }
	
	 public function update($data)
    {
        if(count($this->_where)){
            foreach ($this->_where as $key => $whr) {
               $this->slave->where($whr['col'].' '.$whr['operator'],$whr['value']);
            }
        }
        $this->slave->update($this->_table,$data);
		//echo $this->master->last_query(); exit;
        return ($this->slave->affected_rows() > 0) ? TRUE : FALSE;
		
    }
	
	public function get_data($table_name,$condition = NULL,$order = NULL,$limit = NULL)
    {
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
       //echo $this->slave->last_query(); exit;
        return $obj;
    }
		
	 public function count_filtered_shipping($table_name = NULL, $condition = NULL)
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
		$this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
    public function count_all_shipping($table_name = NULL , $condition = NULL)
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
	
	//driver 	
	 public function count_filtered_driver($table_name = NULL, $condition = NULL)
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
		$this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
    public function count_all_driver($table_name = NULL , $condition = NULL)
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
	
	
	
}
