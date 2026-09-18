<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tax_model extends MY_Model{

    var $column_search = array('name','status');
    var $column_alias = array(); //set column field database

    function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

    public function get_datatable()
    {
        $this->slave->select('*');
        $this->slave->from('ec_tax');
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
        $this->slave->from('ec_tax');
        $this->slave->where('login_id', $this->LOGIN_ID);
        #$this->slave->where('status', '1');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_tax');
        $this->slave->where('login_id', $this->LOGIN_ID);
        #$this->slave->where('status', '1');
        $query = $this->slave->get()->row();
        return $query->CNT;
    }
}
