<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment_model extends MY_Model{

    var $column_search = array('title', 'description', 'status');
    var $column_alias = array(); //set column field database
	
    function __construct() {
        parent::__construct();
    }
	public function get_payment()
    {
        $this->slave->select('*');
        $this->slave->from('ec_payment');       
        $this->get_datatables_query();
        $this->slave->order_by('payment_id', 'DESC');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $payment_method = $this->slave->get()->result();
        return $payment_method; 
    }
    public function get_datatable()
    {
        $this->slave->select('*');
        $this->slave->from('ec_payment');
        //$this->slave->where('status', '1');       
        $this->get_datatables_query();
        $this->slave->order_by('payment_id', 'DESC');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $payment = $this->slave->get()->result();
        return $payment; 
    }
	 public function get_order_obj($payment_id)
    {
        $this->slave->select('*');
        $this->slave->from('ec_order');
        $this->slave->where('payment_id', "$payment_id");             
        $this->slave->order_by('order_id', 'DESC');       
        $order_records = $this->slave->get()->row();
        return $order_records; 
    }
	 public function get_order($payment_id)
    {
        $this->slave->select('*');
        $this->slave->from('ec_order');
        $this->slave->where('payment_id', "$payment_id");             
        $this->slave->order_by('order_id', 'DESC');       
        $order_records = $this->slave->get()->result();
        return $order_records; 
    }
	public function get_profile($id)
    {
        $this->slave->select('*');
        $this->slave->from('ec_payment');
        $this->slave->where('status', '1');
		$this->slave->where('payment_id', $id);  
        $user = $this->slave->get()->row();
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
        $this->slave->from('ec_payment');
        $this->slave->where('status', '1');
       // $this->slave->where('super_admin', 0);
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
		
        return $query->CNT;
    }
	

    public function count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_payment');
        $this->slave->where('status', '1');
        //$this->slave->where('super_admin', 0);
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
	  public function payment_count_filtered()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_payment');
        $this->slave->where('status', '1');
       // $this->slave->where('super_admin', 0);
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
		
        return $query->CNT;
    }
	  public function payment_count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_payment');
        $this->slave->where('status', '1');
        //$this->slave->where('super_admin', 0);
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
	
	public function payment_status_change($user_id,$status_val)
    {	
		$this->master->where('payment_id',$user_id);		
		$sql = $this->master->update('ec_payment', array('status' => "$status_val"));		
		return $sql;
    }
	public function status_change($user_id,$status_val)
    {	
		$this->master->where('payment_id',$user_id);		
		$sql = $this->master->update('ec_payment', array('status' => "$status_val"));		
		return $sql;
    }
	
	 public function delete_post($product_id)
    {
        $this->master->where('payment_id',$product_id);
        $status = $this->master->delete('ec_payment');
        return ($this->master->affected_rows() > 0) ? TRUE : FALSE;
    }
	
	public function get_profile_payment($id) 
    {
        $this->slave->select('*');
        $this->slave->from('ec_payment');       
		$this->slave->where('payment_id', $id);  
        $user = $this->slave->get()->row();
        return $user; 
    }
	public function delete_image($id)
    {
      	
		$sql ="UPDATE ec_payment set logo=NULL WHERE payment_id=$id"; 
		$query  =  $this->slave->query($sql);
		return ($query > 0) ? TRUE : FALSE;		
		
    }
	
}
