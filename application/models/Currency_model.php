<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Currency_model extends MY_Model{

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
        $this->slave->from('ec_currency');
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
        $this->slave->from('ec_currency');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_currency');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

	public function currency_data_for_home()
	{
		$current_time = date('Y-m-d');
		$this->slave->select('*');
		$this->slave->from('ec_currency');
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
		$this->slave->like('name',$s);       
        $user = $this->slave->get()->result();		
        if ($user) {
            foreach ($user as $row) {
                $row->product_id = $row->id;
                $row->product_uid = $row->id;
                $row->post_title = $row->name;
                $row->post_slug = $row->slug;
                $row->type = $row->product_type;
                $row->login_id = $row->vendor_id;
                $row->enabled = $row->status;
            }
        }
        return $user;
    }
     public function get_product_filterby_id($s=null)
    {
        $this->slave->select('*');
        $this->slave->from('ec_currency');
        $this->slave->where('currency_id',$s);
        $user = $this->slave->get()->result();
        return $user;
    }

    public function coupan_status_change($currency_id,$status_val)
    {
        $this->master->where('currency_id',$currency_id);
        $sql = $this->master->update('ec_currency', array('status' => "$status_val"));
        return $sql;
    }

    public function check_unique_currency($iso_code, $currency_id)
    {
        $this->slave->select('count(*) AS cnt');
        $this->slave->from('ec_currency');
        $this->slave->where('iso_code', $iso_code);
        if($currency_id){
            $this->slave->where('currency_id!=', $currency_id);
        }
        $obj = $this->slave->get()->row();
        $cnt = $obj->cnt;
        $data  = array(
                        'cnt' => $cnt
                      );

        return (object) $data;
    }

    public function get_currency_detail($country=null)
    {
        $data = (object) array('country_id' => 233, 'currency_id' => 1, 'name' => 'American Dollar', 'iso_code' => 'USD', 'symbol' => '$', 'rate' => 1);
        if($country){
            $sql="SELECT a.country_id,a.currency_id,a.name,a.iso_code,a.symbol,a.rate FROM ec_currency a, ec_country b WHERE a.country_id = b.country_id and LOWER(b.name) like LOWER('%$country%')";
            $query  =  $this->slave->query($sql);
            $data   =  $query->row();
            if($data){

            }else{
                $sql="SELECT a.country_id,a.currency_id,a.name,a.iso_code,a.symbol,a.rate FROM ec_currency a, ec_country b WHERE a.country_id = b.country_id and a.basic = '1'";
                $query  =  $this->slave->query($sql);
                $data   =  $query->row();
            }
        }else{
            $sql="SELECT a.country_id,a.currency_id,a.name,a.iso_code,a.symbol,a.rate FROM ec_currency a, ec_country b WHERE a.country_id = b.country_id and a.basic = '1'";
            $query  =  $this->slave->query($sql);
            $data   =  $query->row();
        }
        return $data;
    }
}
