<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notify_model extends MY_Model{

    //var $column_search = array('name','discount','start_date','end_date','status','date_added');
	var $column_search_finance = array('date_created_range', 'last_updated');
    var $column_alias_finance = array();

    function __construct() {
    parent::__construct();
    $type = $this->session->userdata('type');
    
    $user_session = $this->session->userdata($type); 

    $this->login_id = isset($user_session['vendor_id']) ? $user_session['vendor_id'] : 0;
}

    public function get_datatable($login_id = NULL)
    {
        $this->slave->select('*');
        $this->slave->from('ec_notification');
        if($login_id){
            $this->slave->where('vendor_id', $login_id);
        }
        //$this->slave->where('show_noti', '1');  
		$this->slave->where('type', '1');
        $this->get_datatables_query_notify();
        $this->slave->order_by('date_added', 'desc');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $user = $this->slave->get()->result();
		//echo $this->slave->last_query();
        return $user;
    }
	
	public function get_datatables_query_notify()
    {
        if(isset($_POST['filter'])){
            $this->slave->group_start();
			#print_r($_POST['filter']);
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search_finance) && (is_array($col_value) || $col_value != '')) {
                    $column_name_alias = $col_name;
                    if(isset($this->column_alias_finance[$col_name])){
                        $column_name_alias =  $this->column_alias_finance[$col_name].".".$col_name;
                    }
					else if($col_name == 'date_created_range')
                    {
                        $this->date_filter($col_value);
                    }
                    else if(is_array($col_value))
					{
                        $this->slave->where_in($column_name_alias, $col_value);
                    }
					else
					{
                        $this->slave->like($column_name_alias, $col_value); 
                    }
                }
            }
            $this->slave->group_end();
        }
    }
	
	public function date_filter($date_range)
	{
		$date_range_arr = preg_split('/::/', $date_range);
		if(isset($date_range_arr[0]) && isset($date_range_arr[1]))
		{
			$this->slave->where("DATE(date_added) >= '".$date_range_arr[0]."' AND DATE(date_added) <= '".$date_range_arr[1]."'");	
		}
		else if($date_range_arr[0])
		{
			$this->slave->where("DATE(date_added) >= '".$date_range_arr[0]."' AND DATE(date_added) <= '".$date_range_arr[0]."'");
		}
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

    public function count_filtered($login_id = NULL)
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_notification');
        if($login_id){
            $this->slave->where('vendor_id', $this->LOGIN_ID);
        }
        $this->get_datatables_query_notify();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all($login_id = NULL)
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_notification');
        if($login_id){
            $this->slave->where('vendor_id', $login_id);
        }
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

	
  
   



	
}
