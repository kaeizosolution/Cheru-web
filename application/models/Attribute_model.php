<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attribute_model extends MY_Model{

    var $column_search = array('name','status');
    var $column_alias = array(); //set column field database

    var $table = 'ec_attribute';
    function __construct() {
        parent::__construct();
        $this->TYPE = $this->session->userdata('type');
		$vendor = $this->session->userdata('vendor');
		$this->login_id = (is_array($vendor) && isset($vendor['vendor_id'])) ? (int)$vendor['vendor_id'] : 0;
    }

    public function get_datatable()
    {
        $this->slave->select('*');
        $this->slave->from('ec_attribute');    
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
        $this->slave->from('ec_attribute');
#$this->slave->where('login_id', $this->LOGIN_ID);
#$this->slave->where('status', '1');
        $this->get_datatables_query();
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function count_all()
    {
        $this->slave->select('COUNT(*) CNT');
        $this->slave->from('ec_attribute');
#$this->slave->where('login_id', $this->LOGIN_ID);
#$this->slave->where('status', '1');
        $query = $this->slave->get()->row();
        return $query->CNT;
    }

    public function get_attr($attribute_id=null)
    {
        $sql="select distinct a.attribute_id,a.name from ec_attribute a,ec_attribute_item b where a.status = '1' and b.status = '1' and a.attribute_id = b.attribute_id";
        if($attribute_id){
            $sql.=" and a.attribute_id = $attribute_id";
        }
        $query  =  $this->slave->query($sql);
        $data 	=   $query->result();
        return $data;
    }

    public function get_attr_already($product_id=null)
    {
        $sql="select a.attribute_id,a.name from ec_attribute a where status = '1' and a.attribute_id not in ( select distinct(b.attribute_id) from ec_product_attribute a, ec_attribute_item b where a.attribute_item_id = b.attribute_item_id and b.status = '1' and a.product_id=$product_id)";	
        $query  =  $this->slave->query($sql);
        $data 	=   $query->result();
        return $data;
    }

    public function get_attr_vari($product_id=null)
    {	
        $sql="select a.*,b.attribute_id,b.name from ec_product_attribute a, ec_attribute_item b where a.attribute_item_id = b.attribute_item_id and a.product_id=$product_id"; 
        $query  =   $this->slave->query($sql);
        $data 	=   $query->result();
        return $data;

    }

    public function get_attr_vari_item($product_id=null)
    {	
        $sql="select a.*,b.attribute_id,b.name from ec_product_variation a,ec_attribute_item b  where a.attribute_item_id = b.attribute_item_id and a.product_id=$product_id";	
        $query  = $this->slave->query($sql);
        $data 	= $query->result();
        return $data;
    }

    public function get_data_attr($product_id = null)
    {
        if($product_id){
            $sql 	=	"select a.*,b.attribute_id,b.name from ec_product_attribute a,ec_attribute_item b  where a.attribute_item_id = b.attribute_item_id and a.product_id=$product_id";	
        }else{
            $sql 	=	"select a.*,b.attribute_id,b.name from ec_product_attribute a,ec_attribute_item b where a.attribute_item_id = b.attribute_item_id";	
        }		

        $query  =   $this->slave->query($sql);
        $data 	=   $query->result();
        return $data;
    }

    public function get_all_item()
    {
        $sql 	=	"select * from ec_attribute_item";	
        $query  =   $this->slave->query($sql);
        $data 	=   $query->result();
        return $data;
    }

    function find_list_prod(){
        $this->slave->order_by('name','asc');
        $query = $this->slave->get('ec_attribute_item');
        $data = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[$row['attribute_item_id']] = $row['name'];
            }
        }
        return $data; 
    }

    function get_attr_val($attr_id = null){
        $this->slave->order_by('name','ASC');
        $this->slave->where('attribute_id', $attr_id);    
        $query = $this->slave->get('ec_attribute_item');		
        $data = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $data[$row['attribute_item_id']] = $row['name'];
            }
        }
        return $data; 
    }
/* neeraj */
    public function get_all_attribute(){
        $this->slave->select('a.attribute_id,a.name');
        $this->slave->from('ec_attribute a');
   //     $this->slave->where('a.status', '1');
        $this->slave->order_by('a.name', 'desc');
        $obj = $this->slave->get()->result();
        return $obj;
    }

    public function get_all_attribute_item(){
        $this->slave->select('a.attribute_item_id,a.name');
        $this->slave->from('ec_attribute_item a');
 //       $this->slave->where('a.status', '1');
        $this->slave->order_by('a.name', 'desc');
        $obj = $this->slave->get()->result();
        return $obj;
    }

    public function get_attribute_item_product_id($product_id = NULL)
    {
        $this->slave->select('b.attribute_id,b.attribute_item_id,b.name');
        $this->slave->from('ec_product_attribute as a');
        $this->slave->join('ec_attribute_item as b', 'a.attribute_item_id = b.attribute_item_id');
        $this->slave->where("a.product_id", $product_id);
        $obj = $this->slave->get()->result();
        return $obj;
    }

    public function get_attribute_item_attribute_item_id($attribute_item_id = NULL)
    {
        $this->slave->select('b.attribute_id,b.attribute_item_id,b.name');
        $this->slave->from('ec_attribute as a');
        $this->slave->join('ec_attribute_item as b', 'a.attribute_id = b.attribute_id');
        $this->slave->where_in("b.attribute_item_id", $attribute_item_id);
        $obj = $this->slave->get()->result();
        return $obj;
    }

    public function get_product_variation($product_id = NULL)
    {
        $this->slave->select('*');
        $this->slave->from('ec_product_variation');
        $this->slave->where("product_id", $product_id);
        $obj = $this->slave->get()->result();
        return $obj;
    }
	
	 public function get_size_list($arg=null) 
    {				
		$sql='';		
		$string = implode(",",$arg);
		
		$sql .="SELECT t1.id, t1.product_id, t1.attribute_item_id,t2.name as size
				FROM klentano_db.ec_product_attribute as t1, ec_attribute_item as t2 
				where product_id in ($string) and t2.attribute_item_id=t1.attribute_item_id and t2.attribute_id=2 
				GROUP BY t1.attribute_item_id;"; 
		$query  =  $this->slave->query($sql);		
		$data   =  $query->result();
		return $data;
    }
	 public function get_color_list($arg=null) 
    {				
		$sql='';
		$string = implode(",",$arg);
		$sql .="SELECT t1.id, t1.product_id, t1.attribute_item_id,t2.name as size
				FROM klentano_db.ec_product_attribute as t1, ec_attribute_item as t2 
				where product_id in ($string) and t2.attribute_item_id=t1.attribute_item_id and t2.attribute_id=1 
				GROUP BY t1.attribute_item_id"; 
		$query  =  $this->slave->query($sql);		
		$data   =  $query->result();
		return $data;
    }

	
	public function delete_image($id,$banner_id)
    {
      	
		$sql ="UPDATE ec_banner set banner_image =NULL WHERE banner_id =$id"; 
		$query  =  $this->slave->query($sql);
		return ($query > 0) ? TRUE : FALSE;		
		
    }

	public function find_widget_id($id){
		$this->slave->where('id',$id);
		return $this->slave->get($this->table,1)->row_array();
	}

}
