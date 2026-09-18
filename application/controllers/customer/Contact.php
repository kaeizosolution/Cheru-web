<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends MY_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model('Contact_model');
        $this->load->model('Shipping_model');
        $this->load->library('session');
        $this->config->load('custom_config');
        $this->load->helper('api');
        $this->master = $this->load->database('master', TRUE);
        $this->TYPE = $this->session->userdata('type');
    }

	private function _get_customer_access_token()
	{
		$customer = $this->session->userdata('customer');
		if (is_array($customer) && !empty($customer['access_token'])) {
			return (string)$customer['access_token'];
		}
		return '';
	}

	private function _build_api_headers()
	{
		$token = $this->_get_customer_access_token();
		if ($token === '') {
			return [];
		}
		return ['Authorization' => 'Bearer ' . $token];
	}

	public function ajax_product_enquiry()
	{
		$customer = $this->session->userdata('customer');
		$customer_id = (is_array($customer) && isset($customer['login_id'])) ? (int)$customer['login_id'] : 0;
		if ($customer_id <= 0) {
			echo json_encode(['success' => 0, 'msg' => 'Please login for your query']);
			return;
		}

		$this->load->helper(['form', 'url']);
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		$this->form_validation->set_rules('fname', 'Full name', 'trim|required');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim');
		$this->form_validation->set_rules('product_id', 'Product', 'trim|required|integer');
		$this->form_validation->set_rules('message', 'Message', 'trim|required');

		if ($this->form_validation->run() === FALSE) {
			echo json_encode(['success' => 0, 'msg' => validation_errors() ?: 'Invalid input']);
			return;
		}

		$data = $this->security->xss_clean($this->input->post());
		$csrfName = $this->security->get_csrf_token_name();
		if (isset($data[$csrfName])) {
			unset($data[$csrfName]);
		}
		if (isset($data['ci_csrf_token'])) {
			unset($data['ci_csrf_token']);
		}

		$product_id = (int)($data['product_id'] ?? 0);
		$product_name = isset($data['product_name']) ? (string)$data['product_name'] : '';
		$payload = [
			'customer_id' => $customer_id,
			'product_id' => $product_id,
			'product_name' => $product_name,
			'full_name' => (string)($data['fname'] ?? ''),
			'email' => (string)($data['email'] ?? ''),
			'mobile' => (string)($data['mobile'] ?? ''),
			'message' => (string)($data['message'] ?? ''),
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'created_at' => date('Y-m-d H:i:s'),
			'status' => 1,
		];

		$use_api = (bool)$this->config->item('use_api');
		$token_headers = $this->_build_api_headers();
		$api_ok = false;
		if ($use_api && !empty($token_headers) && function_exists('call_api')) {
			try {
				$api_resp = call_api('POST', 'product-enquiry/add', $payload, $token_headers);
				if ($api_resp && isset($api_resp['response']) && is_array($api_resp['response']) && (int)($api_resp['response']['status'] ?? 0) === 1) {
					$api_ok = true;
				}
			} catch (Exception $e) {
				$api_ok = false;
			}
		}

		if (!$api_ok) {
			$insert_id = $this->Contact_model->insert_data('ec_product_enquiry', $payload);
			if (!$insert_id) {
				echo json_encode(['success' => 0, 'msg' => 'Unable to submit']);
				return;
			}
		}

		$submitted_mobile = (string)($payload['mobile'] ?? '');
		if ($submitted_mobile !== '' && is_array($customer)) {
			$hasMobile = (!empty($customer['mobile']) || !empty($customer['phone']));
			if (!$hasMobile) {
				$customer['mobile'] = $submitted_mobile;
				$this->session->set_userdata('customer', $customer);
			}
		}
		echo json_encode(['success' => 1, 'msg' => 'Enquiry sent successfully']);
	}


    public function index($data=null)
    {
        $data=array();
        $data['msg'] = $data;
        $data['csrf'] = csrf_token();	
        $data['homepage'] = 1;
        $this->load->template("$this->TYPE/contact_us",$data);
    }    

    function ajax_cform()
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

			$csrfName = $this->security->get_csrf_token_name();
			if(isset($data[$csrfName])){
				unset($data[$csrfName]);
			}
			if(isset($data['ci_csrf_token'])){
				unset($data['ci_csrf_token']);
			}

            $this->insert_formdata(array('DATA' => $data));
        }

    }  
    public function insert_formdata($args)
    {	
        $data['error'] = false;
        if(isset($args['DATA']) && $args['DATA'])
        {
            $url = base_url();
            $args['DATA']['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $args['DATA']['custype'] = 'cform';
            $email = $this->input->post('email');			
            $last_inserted_id  =  $this->Contact_model->insert_data('ec_enquiry',$args['DATA']);

            $this->load->library('mailer');
            $useremail = $email;
            $admin_email = 'tarzan@admin.com';
            $msg = '<p style="text-align:center;">Thank you contacting with us, will reach out you soon.</p>';
            $admin_msg = '<div style="text-align:center;"><p>'.$useremail.' has subscription on tarzan. <br ><br >Here is the submitted form data</p><table style="width:100%" border="1px"cellspacing="0" cellpadding="10"><tr><td>Email</td><td>'.$email.'</td> </tr> </table></div>';

            $this->mailer->smtp(array('SUBJECT' => 'Thank You for Enquiry', 'EMAIL' => $useremail, 'CONTENT' => $msg));
            $this->mailer->smtp(array('SUBJECT' => 'Contact form Enquiry', 'EMAIL' => $admin_email, 'CONTENT' => $admin_msg));

            if($last_inserted_id)
            {
                echo json_encode(array('msg' => 'Thank you contacting with us, will reach out you soon.', 'success' => 1));
            }
            else{
                echo json_encode(array('msg' => 'Form is not submitted', 'success' => 0));
            }
        }

    }    

    public function check_validation()
    {
        $this->load->helper(array('form', 'url'));		
        $this->load->library('form_validation');		
        $this->form_validation->set_error_delimiters('<div>', '</div>');          
        $this->form_validation->set_rules('email',' Email', 'trim|required|valid_email');      
        $this->form_validation->set_rules('fname','Full name', 'trim|required');
        $this->form_validation->set_rules('subject','Subject', 'trim|required'); 
        $this->form_validation->set_rules('message','Message', 'trim|required');
    }

    //for cron job
    function update_shipping_status($pwd) {

        if($pwd !='1234')
            return 0;


        $today = date('Y-m-d');		
        $this->Shipping_model->table('ec_vehicle_price')
            ->where('apply_date','LIKE',$today.'%')
            ->update(array('status' => '1'));

        $this->Shipping_model->table('ec_vehicle_price')
            ->where('apply_date','NOT LIKE',$today.'%')
            ->where('end_date','<',$today.'%')
            ->where('status','1')
            ->update(array('status' => '0'));

        return 1;
    }

}

