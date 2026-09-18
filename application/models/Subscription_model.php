<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscription_model extends MY_Model{
	
	//administrator section
	var $subscribe_column_search = array('email', 'ip_address', 'date_added');
    var $subscribe_column_alias = array(); //set column field database

    function __construct() {
        parent::__construct();
    }   

	public function get_subscription_list()
    {
        $this->slave->select('*');
		$this->slave->from('ec_subscribe');
		$result = $this->slave->get()->result();
	    $i=0;
	    $data_arr=   array();
        foreach($result as $data)
        {
			$data_arr[$i]['id']=$data->id;
			$data_arr[$i]['email']=$data->email;
			$data_arr[$i]['ip_address']=isset($data->ip_address) ? $data->ip_address : '';
			$data_arr[$i]['status']=$data->status;
			$data_arr[$i]['date_added']=$data->date_added;
			$i++;
		
		}
		 return $data_arr;
		
    } 
     public function get_subscription()
    {
        $this->slave->select('*');
		$this->slave->from('ec_subscribe');
		$this->slave->order_by('id','DESC');
		$this->get_datatables_query();
		$this->slave->limit($_POST['length'], $_POST['start']);    
		$subscription = $this->slave->get()->result();
		//echo $this->slave->last_query();exit;
        return  $subscription;
    } 
	public function count_filtered($table_name = NULL, $condition = NULL)
    {
        $this->slave->select('COUNT(*) CNT');
		$this->slave->from('ec_subscribe');
		$this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all($table_name = NULL , $condition = NULL)
    {
        $this->slave->select('COUNT(*) CNT');
		$this->slave->from('ec_subscribe');
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
	 public function get_datatables_query()
    {
        if(isset($_POST['filter'])){
            $this->slave->group_start();
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->subscribe_column_search) && (is_array($col_value) || $col_value != '')) {
                    $column_name_alias = $col_name;
                    if(isset($this->subscribe_column_alias[$col_name])){
                        $column_name_alias =  $this->subscribe_column_alias[$col_name].".".$col_name;
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
}
