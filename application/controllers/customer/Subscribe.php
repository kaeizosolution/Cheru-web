<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscribe extends MY_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model('Contact_model');
        $this->load->library('session');
        $this->master = $this->load->database('master', TRUE);
	$this->TYPE = $this->session->userdata('type');
    }
	
    public function ajax_subsform()
    {
	$this->check_validation();
        $data = array();
	$this->load->library('form_validation');
        $this->form_validation->set_error_delimiters();
		
        if ($this->form_validation->run() == FALSE)
        {
	    echo json_encode(array('msg' => validation_errors(), 'success' => 0));
        }
        else{
            $data = $this->input->post();
            $data = $this->security->xss_clean($data);
   	   // echo "<pre>"; print_r( $this->input->post() ); echo "</pre>";die;
	    $this->insert_subscribe(array('DATA' => $data));
        }
   
    }  
    
    public function check_validation()
    {
        $this->load->helper(array('form', 'url'));		
        $this->load->library('form_validation');		
        $this->form_validation->set_error_delimiters('<div>', '</div>');          
        $this->form_validation->set_rules('email',' Email', 'trim|required|valid_email');      
      
    }
 
    public function insert_subscribe($args)
    {	
	//echo "<pre>"; print_r( $args ); echo "</pre>";die;
        $data['error'] = false;  
        if(isset($args['DATA']) && $args['DATA'])
        {
	$url = base_url();
	$args['DATA']['ip_address'] = $_SERVER['REMOTE_ADDR'];
	$args['DATA']['custype'] = 'subscribe';
	$email = $this->input->post('email');			
	$last_inserted_id  =  $this->Contact_model->insert_data('ec_enquiry',$args['DATA']);
	
	$this->load->library('mailer');
	$useremail = $email;
	$admin_email = 'admin@tarzan.com';
	$msg = '<p style="text-align:center;">Thanks for subscription us! We will be in touch with you shortly.</p>';
	$admin_msg = '<div style="text-align:center;"><p>'.$useremail.' has subscription on tarzan. <br ><br >Here is the submitted form data</p><table style="width:100%" border="1px"cellspacing="0" cellpadding="10"><tr><td>Email</td><td>'.$email.'</td> </tr> </table></div>';
	$this->mailer->smtp(array('SUBJECT' => 'Thank You subscription', 'EMAIL' => $useremail, 'CONTENT' => $msg));
	$this->mailer->smtp(array('SUBJECT' => 'tarzan Subscription', 'EMAIL' => $admin_email, 'CONTENT' => $admin_msg));
        
        if($last_inserted_id)
            {
                echo json_encode(array('msg' => 'Thank You for Subscribe', 'success' => 1));
            }
            else{
                echo json_encode(array('msg' => 'Form is not submitted', 'success' => 0));
            }
        }

    }
 


}



