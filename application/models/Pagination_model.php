  <?php
class pagination_model extends CI_Model{

    function __construct()
    {
        parent::__construct();
    }

    //fetch books
    function get_agents($limit, $start, $st = NULL)
    {   
        if($st){
            $str = explode('sep',$st);
            $fname = $str[0];
            $country = $str[1];  
            #$sql = "select * from registration where fname like '%$fname%' and country LIKE '%$country%' limit " . $start . ", " . $limit;
            $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=1";
            if($country)
            {
              $sql .= " and country ='$country'";  
            }
            $sql .= " limit " . $start . ", " . $limit;
            
        }else{
            $fname =''; 
            $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=1 limit " . $start . ", " . $limit;       
        }   
        
      //  echo $sql;
        //$sql = "select * from registration where fname like '%$fname%' and country ='$country' limit " . $start . ", " . $limit;
        $query = $this->db->query($sql);
        return $query->result();
    }

    function get_agents_count($st = NULL)
    {   
        if($st){
            $str = explode('sep',$st);
            $fname = $str[0];
            $country = $str[1];  
            //$sql = "select * from registration where fname like '%$fname%' and country LIKE '%$country%' ";
             $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=1";
            if($country)
            {
                $sql .= " and country ='$country'";  
            }
              
            
        }else{
            $fname = "";
            $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=1 ";
        }
        //$sql = "select * from registration where fname like '%$fname%' and country='.$country.' ";
        $query = $this->db->query($sql);
      //  print $this->db->last_query();exit; 
        return $query->num_rows();
    }
    
    /****************Club page***********/
     //fetch books
    function get_club($limit, $start, $st = NULL)
    {   
        if($st){
            $str = explode('sep',$st);
            $fname = $str[0];
            $country = $str[1];  
            #$sql = "select * from registration where fname like '%$fname%' and country LIKE '%$country%' limit " . $start . ", " . $limit;
            $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=3";
            if($country)
            {
              $sql .= " and country ='$country'";  
            }
            $sql .= " limit " . $start . ", " . $limit;
            
        }else{
            $fname =''; 
            $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=3 limit " . $start . ", " . $limit;       
        }   
        
      //  echo $sql;
        //$sql = "select * from registration where fname like '%$fname%' and country ='$country' limit " . $start . ", " . $limit;
        $query = $this->db->query($sql);
        return $query->result();
    }

    function get_club_count($st = NULL)
    {   
        if($st){
            $str = explode('sep',$st);
            $fname = $str[0];
            $country = $str[1];  
            //$sql = "select * from registration where fname like '%$fname%' and country LIKE '%$country%' ";
             $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=3 ";
            if($country)
            {
                $sql .= " and country ='$country'";  
            }
              
            
        }else{
            $fname = "";
            $sql = "select * from registration where fname like '%$fname%' and enabled=1 and registration_type=3 ";
        }
        //$sql = "select * from registration where fname like '%$fname%' and country='.$country.' ";
        $query = $this->db->query($sql);
      //  print $this->db->last_query();exit; 
        return $query->num_rows();
    }
}
?>