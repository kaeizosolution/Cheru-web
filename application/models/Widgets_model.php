<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Widgets_model extends MY_Model{

    function __construct() {
        parent::__construct();
    }   

    public function insert_data($table_name,$data)
    {
        $this->master->insert($table_name,$data);
        return  $this->master->insert_id();
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
        return ($this->master->affected_rows() > 0) ? TRUE : FALSE;
    }

    public function get_data($table_name,$condition = NULL,$order = NULL,$limit = NULL)
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
        return $obj;
    }

    public function get_data_obj($table_name,$condition = NULL,$order = NULL,$limit = NULL)
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
        if($obj && count($obj) == 1){
            $obj = $obj[0];
        }
        return $obj;
    }

    public function count_all($table_name,$condition)
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from($table_name);
        foreach($condition as $column => $value){
            if(is_array($value)){
                $this->slave->where_in($column, $value);
            }else{
                $this->slave->where($column, $value);
            }
        }
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
	public function get_author($id)
    { 
        $this->slave->select('*');
        $this->slave->from('ec_admin');
        $this->slave->where('admin_id', $id);        
        $user = $this->slave->get()->row();
        return $user;
    }
}
