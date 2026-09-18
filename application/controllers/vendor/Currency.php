<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Currency extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        $this->FNAME = $this->session->userdata($this->TYPE)['fname'];
	}
	
	public function index()
    {
        $page_lang = get_page_language_data('admin_page_lang');
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->currency => "");
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;

        $currency = $this->Query_model->get_data_obj('ec_currency',array('iso_code' => 'MXN'));
        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
        $data['currency']   = $currency;
        $this->load->template("$this->TYPE/currency/index_currency",$data);
	}

    public function index_ajax_refresh()
    {
        $response = array(
                'status'  => '0',
                'message' => 'fail',
                'data'    => '',
        );

        $url = 'https://api.exchangeratesapi.io/latest?base=USD&symbols=MXN';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        if($result){
            $exchange_obj = json_decode($result);
            if($exchange_obj && $exchange_obj->rates->MXN){
                $MXN = sprintf('%.02f',$exchange_obj->rates->MXN);
                $data =  array(
                    'rate'      => $MXN,
                );
                $this->Query_model->update_data('ec_currency',$data,array('iso_code' => 'MXN'));
                $response['status'] = 1;
                $response['message'] = 'Success';
                $response['data'] = $MXN;
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

}
