<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends MY_Model{

    var $column_search = array('fname', 'status', 'email', 'mobile');
    var $column_alias = array(); //set column field database
	
	var $column_search_supplier = array('cname', 'city', 'status', 'email', 'mobile');
    var $column_alias_supplier = array(); //set column field database
	
	var $column_search_user = array('fname','date_created','status','email','order_uid', 'mobile');
   // var $column_alias_user = array('date_created','u'); //set column field database
	var $column_alias_user = array('date_created' => 'c', 'status' => 'c','email' => 'c','order_uid'=>'os' ); //set column field database
	
    function __construct() {
        parent::__construct();
    }

	public function get_exportcsv()
    {

        $this->slave->select('c.fname, c.lname, c.email, c.mobile, c.status, c.customer_id as customer_id, os.date_created, os.order_id, c.user_description,os.order_uid');
        $this->slave->from('ec_customer as c');
        $this->slave->join('ec_order as os','`os`.`customer_id`=`c`.`customer_id`'); 
		$this->slave->group_by('`c`.`customer_id`'); 
        $this->slave->order_by('`c`.`customer_id`', 'DESC');
        $result = $this->slave->get()->result();
		$i=0;
	    $data_arr=   array();
        foreach($result as $data)
        {
			
			$data_arr[$i]['fname']=$data->fname;
			$data_arr[$i]['lname']=$data->lname;
			$data_arr[$i]['email']=$data->email;
			$data_arr[$i]['email']=$data->email;
			$data_arr[$i]['mobile']=$data->mobile;
			$data_arr[$i]['date_created']=$data->date_created;		
			$i++;
		
		}
		 return $data_arr;
       // echo 'cc'.$this->slave->last_query();exit;
       
    }
	
	
    public function get_datatable()
    {

        $this->slave->select('c.fname, c.lname, c.email, c.mobile, c.status, c.customer_id as customer_id, os.date_created, os.order_id, c.user_description,os.order_uid');
        $this->slave->from('ec_customer as c');
        $this->slave->join('ec_order as os','`os`.`customer_id`=`c`.`customer_id`'); 
		$this->slave->group_by('`c`.`customer_id`'); 
        $this->get_datatables_user_query();
        $this->slave->order_by('`c`.`customer_id`', 'DESC');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $user = $this->slave->get()->result();
       // echo 'cc'.$this->slave->last_query();exit;
        return $user; 
    }
	
	public function get_datatables_user_query() 
    {
        if(isset($_POST['filter']) && $_POST['filter']){
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search_user) && (is_array($col_value) || $col_value != '')) {
                    $this->slave->group_start();
                    $column_alias_user = $col_name;
                    if(isset($this->column_alias[$col_name])){
                        $column_alias_user =  $this->column_alias[$col_name].".".$col_name;
                    }
                    if(is_array($col_value)){
                        $this->slave->where_in($column_alias_user, $col_value);
                    }else{
                        $this->slave->like($column_alias_user, $col_value); 
                    }
                    $this->slave->group_end();
                }
            }
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
	
	 public function get_order_obj($customer_id)
    {
        $this->slave->select('*');
        $this->slave->from('ec_orders');
        $this->slave->where('user_id', (int)$customer_id);
        $this->slave->order_by('id', 'DESC');
        $order_records = $this->slave->get()->row();
        return $order_records; 
    }
	 public function get_order($customer_id)
    {
        $this->slave->select('*');
        // Aliases for backward compatibility with view_profile.php
        $this->slave->select('id as order_id', false);
        $this->slave->select('date_added as date_created', false);
        $this->slave->from('ec_orders');
        $this->slave->where('user_id', (int)$customer_id);
        $this->slave->order_by('id', 'DESC');
        $order_records = $this->slave->get()->result();
        return $order_records; 
    }
	/*
	public function get_profile($id)
    {
        $this->slave->select('*');
        $this->slave->from('ec_customer');
        //$this->slave->where('status', '1');
		$this->slave->where('customer_id', $id);  
        $user = $this->slave->get()->row();
        return $user; 
    }
	*/
    

    public function count_filtered()
    {
        $this->slave->from('ec_customer as c');
        //$this->slave->join('ec_order as os','`os`.`customer_id`=`c`.`customer_id`');		
        $this->get_datatables_user_query();
		$this->slave->group_by('`c`.`customer_id`'); 
		$this->slave->order_by('`c`.`customer_id`', 'DESC'); 		
        return $this->slave->get()->num_rows();
    }

    public function count_all()
    {
        $this->slave->from('ec_customer as c');
        //$this->slave->join('ec_order as os','`os`.`customer_id`=`c`.`customer_id`');
        $this->slave->group_by('`c`.`customer_id`');
        $this->slave->order_by('`c`.`customer_id`', 'DESC');
        return $this->slave->get()->num_rows();
    }
	
	public function status_change($user_id,$status_val)
    	{ 	
		$this->master->where('customer_id',$user_id);		
		$sql = $this->master->update('ec_customer', array('status' => $status_val));		
		return $sql;
    }
	
	 public function delete_post($product_id)
    {
        $this->master->where('customer_id',$product_id);
        $status = $this->master->delete('ec_customer');
        return ($this->master->affected_rows() > 0) ? TRUE : FALSE;
    }
	//supplier section
	
	 public function get_supplier()
    {
        $this->slave->select('*');
        $this->slave->from('ec_supplier');       
        $this->get_datatables_supplier_query();
        $this->slave->order_by('supplier_id', 'DESC');
        $this->slave->limit($_POST['length'], $_POST['start']);
        $user = $this->slave->get()->result();
        return $user; 
    }
	
	public function supplier_count_all()
    {        
        $this->slave->from('ec_supplier'); 
		return $this->slave->get()->num_rows();       
    }
	public function supplier_count_filtered()
    {       
        $this->slave->from('ec_supplier');      
        $this->get_datatables_supplier_query();       
		return $this->slave->get()->num_rows();      
    }
	public function supplier_status_change($user_id,$status_val)
    {	
		$this->master->where('supplier_id',$user_id);		
		$sql = $this->master->update('ec_supplier', array('status' => "$status_val"));		
		return $sql;
    }
	public function get_datatables_supplier_query() 
    {
        if(isset($_POST['filter'])){
            $this->slave->group_start();
            foreach($_POST['filter'] as $col_name => $col_value){
                if (in_array($col_name, $this->column_search_supplier) && (is_array($col_value) || $col_value != '')) {
                    $column_name_alias = $col_name;
                    if(isset($this->column_alias_supplier[$col_name])){
                        $column_name_alias =  $this->column_alias_supplier[$col_name].".".$col_name;
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
	public function get_profile_supplier($id) 
    {
        $this->slave->select('*');
        $this->slave->from('ec_supplier');       
		$this->slave->where('supplier_id', $id);  
        $user = $this->slave->get()->row();
        return $user; 
    }
	public function get_profile_admin($id) 
    {
        $this->slave->select('*');
        $this->slave->from('ec_admin');
        //$this->slave->where('status', '1');
		$this->slave->where('admin_id', $id);  
        $user = $this->slave->get()->row();
        return $user; 
    }
	
	public function update_Password($id,$data){ 			
		$this->db->where('supplier_id', $id);
		$sql = $this->db->update('ec_supplier',$data);  
		return $sql; 		
    }
	
	public function delete_image($id)
    {
      	
		$sql ="UPDATE ec_supplier set logo=NULL WHERE supplier_id=$id"; 
		$query  =  $this->slave->query($sql);
		return ($query > 0) ? TRUE : FALSE;		
		
    }
	
	 public function get_export_supplier()
    {
        $this->slave->select('*');
        $this->slave->from('ec_supplier');  
        $this->slave->order_by('supplier_id', 'DESC');      
        $result = $this->slave->get()->result();
		//echo "<pre>"; print_r($result); echo "</pre>";die;
		$i=0;
	    $data_arr=   array();
        foreach($result as $data)
        {			
			$data_arr[$i]['supplier_id']=$data->supplier_id;
			$data_arr[$i]['fname']=$data->fname;
			$data_arr[$i]['lname']=$data->lname;
			$data_arr[$i]['email']=$data->email;
			$data_arr[$i]['description']=$data->description;
			$data_arr[$i]['address']=$data->address;
			$data_arr[$i]['city']=$data->city;
			$data_arr[$i]['state']=$data->state;
			$data_arr[$i]['zip_code']=$data->zip_code;
			$data_arr[$i]['country']=$data->country;
			$data_arr[$i]['company_registration_no']=$data->company_registration_no;
			$data_arr[$i]['mobile']=$data->mobile;
			$data_arr[$i]['date_added']=$data->date_added;		
			$i++;		
		}
		 return $data_arr;
    }
	
	/*public function get_export_supplier()
    {

        $this->slave->select('c.fname, c.lname, c.email, c.mobile, c.status, c.customer_id as customer_id, os.date_created, os.order_id, c.user_description,os.order_uid');
        $this->slave->from('ec_customer as c');
        $this->slave->join('ec_order as os','`os`.`customer_id`=`c`.`customer_id`'); 
		$this->slave->group_by('`c`.`customer_id`'); 
        $this->slave->order_by('`c`.`customer_id`', 'DESC');
        $result = $this->slave->get()->result();
		$i=0;
	    $data_arr=   array();
        foreach($result as $data)
        {
			
			$data_arr[$i]['fname']=$data->fname;
			$data_arr[$i]['lname']=$data->lname;
			$data_arr[$i]['email']=$data->email;
			$data_arr[$i]['email']=$data->email;
			$data_arr[$i]['mobile']=$data->mobile;
			$data_arr[$i]['date_created']=$data->date_created;		
			$i++;
		
		}
		 return $data_arr;
       // echo 'cc'.$this->slave->last_query();exit;
       
    }*/

    	public function get_order_datatable()
    {

		$wallet_user_col = 'user_id';
		if ($this->master->field_exists('customer_id', 'ec_wallets')) {
			$wallet_user_col = 'customer_id';
		} elseif ($this->master->field_exists('login_id', 'ec_wallets')) {
			$wallet_user_col = 'login_id';
		} elseif ($this->master->field_exists('user_id', 'ec_wallets')) {
			$wallet_user_col = 'user_id';
		}

		$this->master->select('c.fname, c.lname, c.email, c.mobile, c.status, c.customer_id');
		$this->master->select('IFNULL(w.balance, 0) as wallet_balance', false);
		$this->master->from('ec_customer as c');
		$this->master->join('ec_wallets as w', 'w.' . $wallet_user_col . ' = c.customer_id', 'left');
        //$this->slave->join('ec_order_item as oi','`oi`.`customer_id`=`c`.`customer_id`');
        //$this->slave->group_by('`c`.`customer_id`');
		$this->get_datatables_user_query();
		$this->master->order_by('c.customer_id', 'DESC');
		$this->master->limit($_POST['length'], $_POST['start']);
		$user = $this->master->get()->result();
        //echo $this->slave->last_query();
        return $user;
    }
	

}
