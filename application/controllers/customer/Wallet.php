<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Wallet_model');
		$this->load->model('Product_model');
		$this->load->model('Query_model');
		$this->TYPE = $this->session->userdata('type');
		$this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? (int)$this->session->userdata($this->TYPE)['login_id'] : 0;
	}

	private function _customer_id()
	{
		$customer = $this->session->userdata('customer');
		if (is_array($customer) && isset($customer['customer_id'])) {
			return (int)$customer['customer_id'];
		}
		if (is_array($customer) && isset($customer['login_id'])) {
			return (int)$customer['login_id'];
		}
		return (int)$this->LOGIN_ID;
	}

	public function index()
	{
		redirect('customer/profile?tab=wallet');
		return;

		$customer_id = $this->_customer_id();
		if ($customer_id <= 0) {
			redirect('login');
			return;
		}

		$this->Wallet_model->ensure_wallet($customer_id);

		$data = array();
		$data['TYPE'] = $this->TYPE;
		$data['csrf'] = csrf_token();
		$data['cate_all_strip'] = $this->Product_model->categories_all_strip();
		$data['account_detail'] = $this->Query_model->get_data_obj('ec_customer', array('customer_id' => $customer_id));

		$data['wallet_balance'] = $this->Wallet_model->get_balance($customer_id);
		$data['transactions'] = $this->Wallet_model->get_transactions($customer_id, 10);
		$data['has_bank'] = $this->Wallet_model->has_bank_details($customer_id) ? 1 : 0;

		$this->load->template('customer/wallet', $data);
	}

	public function transactions()
	{
		redirect('customer/profile?tab=wallet_transactions');
		return;

		$customer_id = $this->_customer_id();
		if ($customer_id <= 0) {
			redirect('login');
			return;
		}

		$this->Wallet_model->ensure_wallet($customer_id);

		$data = array();
		$data['TYPE'] = $this->TYPE;
		$data['csrf'] = csrf_token();
		$data['cate_all_strip'] = $this->Product_model->categories_all_strip();
		$data['account_detail'] = $this->Query_model->get_data_obj('ec_customer', array('customer_id' => $customer_id));

		$data['wallet_balance'] = $this->Wallet_model->get_balance($customer_id);
		$data['transactions'] = $this->Wallet_model->get_transactions($customer_id, null);

		$this->load->template('customer/wallet_transactions', $data);
	}

	public function ajax_save_bank()
	{
		$customer_id = $this->_customer_id();
		if ($customer_id <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Please login first'));
			return;
		}

		if ($this->Wallet_model->count_bank_accounts($customer_id) >= 3) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'You can add maximum 3 bank accounts.'));
			return;
		}

		$payload = array(
			'account_holder_name' => (string)$this->input->post('account_holder_name'),
			'bank_name' => (string)$this->input->post('bank_name'),
			'account_number' => (string)$this->input->post('account_number'),
			'ifsc_code' => (string)$this->input->post('ifsc_code'),
		);

		if ($payload['account_holder_name'] === '' || $payload['bank_name'] === '' || $payload['account_number'] === '' || $payload['ifsc_code'] === '') {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'All bank fields are required.'));
			return;
		}

		$ok = $this->Wallet_model->save_bank_details($customer_id, $payload);
		echo json_encode(array('STATUS' => $ok ? 1 : 0, 'MSG' => $ok ? 'Bank details saved.' : 'Failed to save bank details.'));
	}

	public function ajax_list_banks()
	{
		$customer_id = $this->_customer_id();
		if ($customer_id <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Please login first'));
			return;
		}
		$banks = $this->Wallet_model->get_bank_accounts($customer_id);
		echo json_encode(array('STATUS' => 1, 'banks' => $banks));
	}

	public function ajax_delete_bank()
	{
		$customer_id = $this->_customer_id();
		if ($customer_id <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Please login first'));
			return;
		}
		$bank_id = (int)$this->input->post('bank_id');
		if ($bank_id <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Invalid bank account.'));
			return;
		}
		$ok = $this->Wallet_model->delete_bank_account($customer_id, $bank_id);
		echo json_encode(array('STATUS' => $ok ? 1 : 0, 'MSG' => $ok ? 'Bank deleted.' : 'Failed to delete bank.'));
	}

	public function ajax_add_money()
	{
		$customer_id = $this->_customer_id();
		if ($customer_id <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Please login first'));
			return;
		}

		if (!$this->Wallet_model->has_bank_details($customer_id)) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Please add bank details first.', 'need_bank' => 1));
			return;
		}

		$amount = (float)$this->input->post('amount');
		if ($amount <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Amount must be greater than 0.'));
			return;
		}

		$ok = $this->Wallet_model->add_money($customer_id, $amount);
		if ($ok) {
			echo json_encode(array('STATUS' => 1, 'MSG' => 'Money added.', 'balance' => $this->Wallet_model->get_balance($customer_id)));
			return;
		}
		echo json_encode(array('STATUS' => 0, 'MSG' => 'Failed to add money.'));
	}

	public function ajax_withdraw()
	{
		$customer_id = $this->_customer_id();
		if ($customer_id <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Please login first'));
			return;
		}

		$amount = (float)$this->input->post('amount');
		if ($amount <= 0) {
			echo json_encode(array('STATUS' => 0, 'MSG' => 'Amount must be greater than 0.'));
			return;
		}

		$res = $this->Wallet_model->request_withdraw($customer_id, $amount);
		echo json_encode(array('STATUS' => $res['ok'] ? 1 : 0, 'MSG' => $res['msg'], 'balance' => $this->Wallet_model->get_balance($customer_id)));
	}
}
