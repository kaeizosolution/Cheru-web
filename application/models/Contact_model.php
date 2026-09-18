<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_model extends CI_Model{

	//administrator section
	var $column_search = array('email', 'created');
    var $column_alias = array(); //set column field database
	
	 function __construct()
    {
        parent::__construct();
        $this->load->library('session');
		$this->master = $this->load->database('master', TRUE);
    }   
    public function insert_data($table_name,$data)
    {
        unset($data['save']);
        $this->master->insert($table_name,$data);
        return  $this->master->insert_id();
    }
	
	 public function get_contact()
    {
        $this->master->select('*');
        $this->master->from('ec_enquiry');
        $this->master->where('custype','contact');
		//$this->get_cform_datatables_query();
        return  $this->master->get()->result();
    } 
	public function count_filtered($table_name = NULL, $condition = NULL)
    {
        $this->master->select('COUNT(*) CNT');
        $this->master->from($table_name);
        if($condition){
            foreach($condition as $column => $value){
                if(is_array($value)){
                    $this->master->where_in($column, $value);
                }else{
                    $this->master->where($column, $value);
                }
            }
        }
		$this->get_cform_datatables_query();
        $query = $this->master->get()->row();
        return $query->CNT;
    }

    public function count_all($table_name = NULL , $condition = NULL)
    {
        $this->master->select('COUNT(*) CNT');
        $this->master->from($table_name);
        if($condition){
            foreach($condition as $column => $value){
                if(is_array($value)){
                    $this->master->where_in($column, $value);
                }else{
                    $this->master->where($column, $value);
                }
            }
        }
        $query = $this->master->get()->row();
        return $query->CNT;
    }
	
	 public function get_cform_datatables_query()
    {
        if(isset($_POST['filter'])){
            $this->master->group_start();
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search) && (is_array($col_value) || $col_value != '')) {
                    $column_name_alias = $col_name;
                    if(isset($this->column_alias[$col_name])){
                        $column_name_alias =  $this->column_alias[$col_name].".".$col_name;
                    }
                    if(is_array($col_value)){
                        $this->master->where_in($column_name_alias, $col_value);
                    }else{
                        $this->master->like($column_name_alias, $col_value); 
                    }
                }
            }
            $this->master->group_end();
        }
    }
	
}