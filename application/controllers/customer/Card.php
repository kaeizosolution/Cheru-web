<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
                
class Card extends MY_Controller {

	function __construct()
    {           
        parent::__construct();  
        $this->load->model('Query_model');
		$this->load->library('Cc_validation.php'); 
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }



	public function save_card()
	{
		$data 		 = array();
		$customer_id = $this->LOGIN_ID;
		$name 	 	 = $this->input->post('name');
		$card_no 	 = $this->input->post('card_no');
		$month 	  	 = $this->input->post('month');
		$year 	  	 = $this->input->post('year');
		$cvv 	   	 = $this->input->post('cvv');
	
		$this->card_validation_form();
        if($this->form_validation->run() == FALSE)
        {
            api_response(array('status' => 0, 'msg' => remove_extra_for_api(validation_errors()), 'data' => array()));
        }
		
		if($_POST)
		{
				#this use for the card no validation
				$validateCrednumber = $this->cc_validation->validateCreditcard_number($card_no); 
				$card_type  =  $validateCrednumber['card_type'];
				if($validateCrednumber['card_type'] == 'Invalid') 
				{   
						api_response(array('status' => 0, 'msg' => 'Invalid Card', 'data' => array())); 
				}
	
				#this use for the card month and year validation
				$year        = '20'.$year;
				$Expiration = $this->cc_validation->validateCreditCardExpirationDate($month, $year);
				if($Expiration == 'false') 
				{   
						api_response(array('status' => 0, 'msg' => 'Card is not valid', 'data' => array())); 
				}
			
				#this use for the card cvv validation
				$validcvv = $this->cc_validation->validateCVV($card_no, $cvv);
				if($validcvv == 'false') 
				{   
						api_response(array('status' => 0, 'msg' => 'Cvv no is not valid', 'data' => array())); 
				}					
	
				#this is use for the check card no alreday in db
				$condition      = array('card_no' => $card_no, 'customer_id'=> $customer_id); 
				$card_detail    = $this->Query_model->get_data_obj('ec_card_detail', $condition);
				if($card_detail)
				{
						api_response(array('status' => 1, 'msg' => 'Card details already added', 'data' => array()));
				}
				else
				{
						$valid = $year.'-'.$month.'-'.'00';
						$data  = array('name'    => $name,
										'card_no'  => $card_no,
										'valid'    => $valid,
										'cvv'      => $cvv,
										'card_type'=> $card_type,
										'customer_id' => $customer_id);

						$card_detail_id = $this->Query_model->insert_data('ec_card_detail', $data); 
						if($card_detail_id)
						{
								api_response(array('status' => 1, 'msg' => 'Add a card detail successfully', 'data' => $card_detail_id));   
						}           
				}				

		}	

	}


	public function card_validation_form()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		$this->form_validation->set_rules('name', 'Customer Name', 'trim|required|regex_match[/^[a-zA-Z\s+]+$/]|min_length[2]|max_length[30]');
		$this->form_validation->set_rules('card_no', 'Card No', 'trim|required|is_unique[ec_card_detail.card_no]', array('is_unique' => 'This card is already added'));
		$this->form_validation->set_rules('month', 'Card validated month', 'trim|required');
		$this->form_validation->set_rules('year', 'Card validated year', 'trim|required');
		$this->form_validation->set_rules('cvv', 'CVV No', 'trim|required');
	}
	
}


