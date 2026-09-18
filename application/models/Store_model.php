<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store_model extends MY_Model{

   // var $column_search = array('name','discount','start_date','end_date','status','date_added');
    var $column_search = array('cname', 'city', 'status');
    var $column_alias = array(); //set column field database
	
    function __construct() {
        parent::__construct();
    }

    function get_store_count($st = NULL)
    {   
		$sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=2 ";
            
        $query = $this->db->query($sql);
      //  print $this->db->last_query();exit; 
        return $query->num_rows();
    }
	
	 //fetch books
    function get_stores($limit, $start, $search = NULL)
    {   
        if($search){
            
            $str = explode('sep',$search);            
           // echo "<pre>"; print_r($str); echo "</pre>";
            
            $fname = $str[0];
            $foot = $str[1];
            $position = $str[2];   
            $age = $str[3];
            if($age){
            $ageRange = explode('-',$age); 
            $startAge = $ageRange[0];
            $endAge =  $ageRange[1];
                
            }
            
            if($foot || $position){
            
            $sql = "select registration.*, professional.foot,professional.position "
                    . " FROM registration "
                    . " RIGHT JOIN professional ON registration.registration_id = professional.registration_id ";                
            }else{
                $sql = "select registration.*, `professional`.`position`, `professional`.`foot` "
                    . " FROM registration "  
                    . " left JOIN professional ON registration.registration_id = professional.registration_id ";  
            }
                    
            if($foot){                
                $sql.= " and professional.foot ='$foot'";
            }    

            if($position){
              
                $sql.= " and professional.position ='$position'";
            } 
            
            $sql.=" WHERE enabled=1 and registration_type=2 and fname like '%$fname%' ";
            
            if($age){
                $sql.= " and TIMESTAMPDIFF(YEAR,`player_dob`,NOW()) BETWEEN  $startAge AND $endAge ";
            } 
            
            $sql .= " limit " . $start . ", " . $limit;
            
            
            
            }else{
            $fname =''; 
             $sql = "select registration.*, professional.foot,professional.position "
                    . " FROM registration "
                    . " LEFT JOIN professional ON registration.registration_id = professional.registration_id "
                    . " WHERE fname like '%$fname%' and enabled=1 and registration_type=2 "
                    . " limit " . $start . ", " . $limit; 
            
           
        }   
       // echo $sql;die;
        
        $query = $this->db->query($sql);
        return $query->result();
    }
	
}
