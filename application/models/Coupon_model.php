<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Coupon_model extends MY_Model{

    //var $column_search = array('name','discount','start_date','end_date','status','date_added');
	var $column_search = array('name','start_date','end_date','status');
    var $column_alias = array(); //set column field database

    function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

    public function get_datatable($login_id = NULL)
    {
        $this->slave->select('*');
        $this->slave->from('ec_coupon');
        if($login_id){
            $this->slave->where('login_id', $login_id);
        }
        #$this->slave->where('status', '1');
        $this->get_datatables_query();
        $this->slave->order_by('status', 'desc');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $user = $this->slave->get()->result();
        //echo $this->slave->last_query(); exit;
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
        $this->slave->from('ec_coupon');
        #$this->slave->where('status', '1');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_coupon');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

	public function coupon_data_for_home()
	{
		$current_time = date('Y-m-d');
		$this->slave->select('*');
		$this->slave->from('ec_coupon');
		$this->slave->where('date(end_date) >=' , "$current_time");
		$this->slave->where('status', '1');
		$this->slave->limit(10, 0);
		$obj = $this->slave->get();
		return $obj->result();
	}
    public function sarch_coupan($s=null)
    {
        $this->slave->select('*');
        $this->slave->from('products');
        $this->slave->where('status','1');
        if ($s !== null && $s !== '') {
            $this->slave->like('name',$s);
        }
        $user = $this->slave->get()->result();		
        return $user;
    }
     public function get_product_filterby_id($s=null)
    {
        $this->slave->select('*');
        $this->slave->from('ec_coupon');
        $this->slave->where('coupon_id',$s);
        $user = $this->slave->get()->result();
        return $user;
    }

    public function coupan_status_change($coupon_id,$status_val)
    {
        $this->master->where('coupon_id',$coupon_id);
        $sql = $this->master->update('ec_coupon', array('status' => "$status_val"));
        return $sql;
    }



	
}
