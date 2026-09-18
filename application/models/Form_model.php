<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Form_model extends MY_Model{

	//administrator section
	var $column_search = array('fname','email', 'created');
    var $column_alias = array(); //set column field database
	
	function __construct() {
        parent::__construct();
    }
    
	public function get_contact_list()
    {
		$this->slave->select('*');
		$this->slave->from('ec_enquiry');
		$this->slave->where('custype','cform');
		$result = $this->slave->get()->result();
		//echo $this->slave->last_query();exit;
			//return  $contact;
		$i=0;
		$data_arr=   array();
		foreach($result as $data){				
		   $data_arr[$i]['enquiry_id']=$data->enquiry_id;
		   $data_arr[$i]['fname']=$data->fname;		  
		   $data_arr[$i]['email']=$data->email;
		   $data_arr[$i]['message']=$data->message;		  
		   $data_arr[$i]['ip_address']=$data->ip_address;
		   $data_arr[$i]['created']=$data->created;
		  $i++;		
		}
	 return $data_arr;
		
    } 
	
	
	public function get_contact(){
		
        $this->slave->select('*');
        $this->slave->from('ec_enquiry');
        $this->slave->where('custype','cform');
        $this->slave->order_by('enquiry_id','DESC');
	    $this->get_datatables_query();
	    $this->slave->limit($_POST['length'], $_POST['start']);
	    $contact = $this->slave->get()->result();
	   // echo $this->slave->last_query();exit;
        return  $contact;		
    } 
	
	public function get_datatables_query(){
		
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
	public function count_filtered(){
		
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_enquiry');        
        $this->slave->where('custype', 'cform');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }	
    public function count_all($table_name = NULL , $condition = NULL){
		
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_enquiry');
        $this->slave->where('custype', 'cform');
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
	
}
