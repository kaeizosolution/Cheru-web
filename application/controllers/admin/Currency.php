<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Currency extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');
	    $this->load->model('Currency_model');
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

        $data['csrf'] = csrf_token();
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
        $this->load->template("$this->TYPE/currency/index_currency",$data);
	}

    public function index_ajax_currency()
    {
        $length = (isset($_POST['length']))?$_POST['length']: page_count();
        $page  = (isset( $_POST['page']))?$_POST['page']: 1;

        $type_status = array(1 => 'active', 0 => 'inactive', 2 => 'deleted');
        $list = $this->Currency_model->get_datatable();
        $country_obj = get_country();
        if($country_obj){
            foreach($country_obj as $c){
                $country[$c->country_id] = $c->name;
            }
        }
        $data = array();
        foreach ($list as $obj) {
            $row = array();
            // Cast to int to guarantee 0 or 1 regardless of MySQL driver string/int return
            $status_int = (int)$obj->status;

            $row['currency_id'] = $obj->currency_id;
            $row['name']        = $obj->name;
            $row['iso_code']    = $obj->iso_code;
            $row['symbol']      = $obj->symbol;
            $row['rate']        = $obj->rate;
            $row['last_updated']= $obj->last_updated;
            $row['country_name']= isset($country[$obj->country_id]) ? $country[$obj->country_id] : 'NA';
            // Return numeric status directly: 1=active, 0=inactive
            $row['status']      = $status_int;

            $data[] = $row;
        }
        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $this->Currency_model->count_all(),
                "recordsFiltered" => $this->Currency_model->count_filtered(),
                "data" => $data,
                );

        echo json_encode($output);
    }

	public function add()
    {
        $this->post();
    }

	public function update($currency_id)
    {
        $this->post($currency_id);
    }

	function post($currency_id = NULL)
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('symbol', 'Symbol', 'trim|required');
        $this->form_validation->set_rules('rate', 'Rate', 'trim|required');
        $this->form_validation->set_rules('iso_code', 'iso_code', "trim|required");
/*
        if(isset($currency_id)){
            $this->form_validation->set_rules('iso_code', 'iso_code', "trim|required|callback_check_unique_currency[$currency_id]");
        }else{
            $this->form_validation->set_rules('iso_code', 'iso_code', "trim|required|callback_check_unique_currency[0]");
        }
*/
        if(isset($currency_id)){
            $currency_obj = $this->Query_model->get_data_obj('ec_currency',array('currency_id' => $currency_id));
            if(!$currency_obj){
                $this->session->set_flashdata('error', 'Invalid Error.');
                redirect("$this->TYPE/currency");
            }
        }

        if($_POST) {
            $name = $this->input->post('name');
            $iso_code  = $this->input->post('iso_code');
            $symbol = $this->input->post('symbol');
            $rate = $this->input->post('rate');
            $country_id = $this->input->post('country_id');

            $data =  array(
                    'name'      => $name,
                    'iso_code'  => $iso_code,
                    'symbol'    => $symbol,
                    'rate'      => $rate,
                    'country_id'=> $country_id,
                    );
        }else{
            if(isset($currency_id)){
                $data =  array(
                        'name'      => $currency_obj->name,
                        'iso_code'  => $currency_obj->iso_code,
                        'symbol'    => $currency_obj->symbol,
                        'rate'      => $currency_obj->rate,
                        'country_id'=> $currency_obj->country_id,
                        );
            }
        }

        if ($this->form_validation->run() == FALSE){
            $page_lang = get_page_language_data('admin_page_lang');
            $action_bc = isset($currency_id) ? $page_lang->update : $page_lang->add;
            $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->currency => "/$this->TYPE/currency/", "$action_bc" => 'action');
            $breadcrumbs = $this->breadcrumbs->show($crumbs);
            $data['breadcrumbs']    = $breadcrumbs;

            $data['csrf'] = csrf_token();
            $data['TYPE'] = $this->TYPE;
            $data['action'] = isset($currency_id) ? $page_lang->update : $page_lang->add;
            $data['currency_id'] = isset($currency_id) ? $currency_id : NULL;
            #$data['country_obj'] = get_country();
            $country_obj = $this->Query_model->get_data('ec_country');
            $data['country_obj'] = $country_obj;
            $this->load->template("$this->TYPE/currency/currency",$data);
        }else{
            $db_data =  array(
                    'name'      => $data['name'],
                    'iso_code'  => $data['iso_code'],
                    'symbol'    => $data['symbol'],
                    'rate'      => $data['rate'],
                    'country_id'=> $data['country_id'],
                    );
            // Set status=1 (active) for new currencies, preserve existing on update
            if (!isset($currency_id)) {
                $db_data['status'] = 1;
            }
            if(isset($currency_id)){
                #echo "<pre>"; print_r($db_data); echo "</pre>";die; 
                $this->Query_model->update_data('ec_currency',$db_data,array('currency_id' => $currency_id));
                $this->session->set_flashdata('success', 'Updated Successfully.');
            }else{
                $this->Query_model->insert_data('ec_currency',$db_data);
                $this->session->set_flashdata('success', 'Inserted Successfully.');
            }

            // Update currency fields in the ec_country table for the selected country
            if (!empty($data['country_id'])) {
                $country_db_data = array(
                    'currency_name'   => $data['name'],
                    'currency_symbol' => $data['symbol'],
                    'currency'        => $data['iso_code'],
                );
                $this->Query_model->update_data('ec_country', $country_db_data, array('country_id' => $data['country_id']));
            }
/*
            $ec_currency = array();
            $currency_db = $this->Query_model->get_data('ec_currency');
            if($currency_db){
                foreach($currency_db as $c){
                    $ec_currency[$c->country_id] = $c;
                }
                $this->load->driver('cache', array('adapter' => 'file'));
                $this->cache->delete('ec_currency');
                $this->cache->save('ec_currency', $ec_currency, 0);
            }
*/
            redirect("$this->TYPE/currency");
        }
    }

    public function status_change()
    {
        $id     = $this->input->post('id');
        $status = $this->input->post('status');
        // Toggle: 'active' (1 in DB) → set to 0; 'inactive' (0 in DB) → set to 1
        if ($status == 'active') {
            $status_val = 0;
        } elseif ($status == 'inactive') {
            $status_val = 1;
        } else {
            // Fallback: treat numeric value as direct toggle
            $status_val = ($status == '1') ? 0 : 1;
        }
        $update = $this->Currency_model->coupan_status_change($id, $status_val);
        echo json_encode(array('success' => $update, 'csrf' => csrf_token()));
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

    function check_unique_currency($iso_code, $currency_id)
    {
        $response = $this->Currency_model->check_unique_currency($iso_code, $currency_id);
        $status   = TRUE;
        $message  = '';
        $cnt = $response->cnt;

        if($cnt > 0) {
            $status   = FALSE;
            $message  = 'Currency already exists <br/>';
        }

        if(!$status) {

            $this->form_validation->set_message('check_unique_currency', $message);
        }
        return $status;
    }
}
