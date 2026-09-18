<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	private function _bank_user_col()
	{
		return $this->_id_col('ec_user_bank_details');
	}

	private function _id_col($table)
	{
		$table = (string)$table;
		if ($table === '') {
			return 'user_id';
		}
		if ($this->db->field_exists('user_id', $table)) {
			return 'user_id';
		}
		if ($this->db->field_exists('customer_id', $table)) {
			return 'customer_id';
		}
		if ($this->db->field_exists('login_id', $table)) {
			return 'login_id';
		}
		return 'user_id';
	}

	public function ensure_wallet($customer_id)
	{
		$customer_id = (int)$customer_id;
		if ($customer_id <= 0) {
			return null;
		}

		$id_col = $this->_id_col('ec_wallets');

		$row = $this->db
			->select('id,' . $id_col . ' as user_id,balance,created_at,updated_at')
			->from('ec_wallets')
			->where($id_col, $customer_id)
			->limit(1)
			->get()->row();

		if ($row) {
			return $row;
		}

		$insert = array(
			$id_col => $customer_id,
			'balance' => '0.00',
		);
		$this->db->insert('ec_wallets', $insert);

		return $this->db
			->select('id,' . $id_col . ' as user_id,balance,created_at,updated_at')
			->from('ec_wallets')
			->where($id_col, $customer_id)
			->limit(1)
			->get()->row();
	}

	public function get_balance($customer_id)
	{
		$wallet = $this->ensure_wallet($customer_id);
		if (!$wallet) {
			return 0.0;
		}
		return (float)$wallet->balance;
	}

	public function has_bank_details($customer_id)
	{
		$customer_id = (int)$customer_id;
		if ($customer_id <= 0) {
			return false;
		}

		$id_col = $this->_bank_user_col();

		$row = $this->db
			->select('id')
			->from('ec_user_bank_details')
			->where($id_col, $customer_id)
			->limit(1)
			->get()->row();

		return $row ? true : false;
	}

	public function count_bank_accounts($customer_id)
	{
		$customer_id = (int)$customer_id;
		if ($customer_id <= 0) {
			return 0;
		}
		$id_col = $this->_bank_user_col();
		return (int)$this->db
			->from('ec_user_bank_details')
			->where($id_col, $customer_id)
			->count_all_results();
	}

	public function get_bank_accounts($customer_id)
	{
		$customer_id = (int)$customer_id;
		if ($customer_id <= 0) {
			return array();
		}
		$id_col = $this->_bank_user_col();
		$res = $this->db
			->select('id,bank_name,account_holder_name,account_number,ifsc_code,bank_location,status,created_at,updated_at')
			->from('ec_user_bank_details')
			->where($id_col, $customer_id)
			->order_by('id', 'DESC')
			->get()->result();
		return $res ? $res : array();
	}

	public function delete_bank_account($customer_id, $bank_id)
	{
		$customer_id = (int)$customer_id;
		$bank_id = (int)$bank_id;
		if ($customer_id <= 0 || $bank_id <= 0) {
			return false;
		}
		$id_col = $this->_bank_user_col();
		$this->db->where('id', $bank_id);
		$this->db->where($id_col, $customer_id);
		return (bool)$this->db->delete('ec_user_bank_details');
	}

	public function save_bank_details($customer_id, $data)
	{
		$customer_id = (int)$customer_id;
		if ($customer_id <= 0) {
			return false;
		}

		$id_col = $this->_bank_user_col();

		$payload = array(
			$id_col => $customer_id,
			'account_holder_name' => (string)($data['account_holder_name'] ?? ''),
			'bank_name' => (string)($data['bank_name'] ?? ''),
			'account_number' => (string)($data['account_number'] ?? ''),
			'ifsc_code' => (string)($data['ifsc_code'] ?? ''),
		);

		return (bool)$this->db->insert('ec_user_bank_details', $payload);
	}

	public function add_money($customer_id, $amount)
	{
		$customer_id = (int)$customer_id;
		$amount = (float)$amount;
		if ($customer_id <= 0 || $amount <= 0) {
			return false;
		}

		$wallet_id_col = $this->_id_col('ec_wallets');
		$txn_id_col = $this->_id_col('ec_wallet_transactions');

		$this->db->trans_start();

		$this->ensure_wallet($customer_id);

		$txn = array(
			$txn_id_col => $customer_id,
			'type' => 'credit',
			'amount' => number_format($amount, 2, '.', ''),
			'transaction_type' => 'add_money',
			'status' => 'success',
		);
		$this->db->insert('ec_wallet_transactions', $txn);

		$this->db
			->set('balance', 'balance + ' . (float)$amount, false)
			->where($wallet_id_col, $customer_id)
			->update('ec_wallets');

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function request_withdraw($customer_id, $amount)
	{
		$customer_id = (int)$customer_id;
		$amount = (float)$amount;
		if ($customer_id <= 0 || $amount <= 0) {
			return array('ok' => false, 'msg' => 'Invalid amount.');
		}

		$txn_id_col = $this->_id_col('ec_wallet_transactions');

		$balance = $this->get_balance($customer_id);
		if ($amount > $balance) {
			return array('ok' => false, 'msg' => 'Withdraw amount must be less than or equal to wallet balance.');
		}

		$txn = array(
			$txn_id_col => $customer_id,
			'type' => 'debit',
			'amount' => number_format($amount, 2, '.', ''),
			'transaction_type' => 'withdraw',
			'status' => 'pending',
		);

		$this->db->trans_start();

		$ok = (bool)$this->db->insert('ec_wallet_transactions', $txn);

		if ($ok) {
			$wallet_id_col = $this->_id_col('ec_wallets');
			$this->db
				->set('balance', 'balance - ' . (float)$amount, false)
				->where($wallet_id_col, $customer_id)
				->update('ec_wallets');
		}

		$this->db->trans_complete();
		$ok = $this->db->trans_status() && $ok;

		return array('ok' => $ok, 'msg' => $ok ? 'Withdraw request submitted.' : 'Failed to submit withdraw request.');
	}

	public function get_transactions($customer_id, $limit = null)
	{
		$customer_id = (int)$customer_id;
		if ($customer_id <= 0) {
			return array();
		}

		$id_col = $this->_id_col('ec_wallet_transactions');

		$this->db
			->select('id,type,amount,transaction_type,status,created_at')
			->from('ec_wallet_transactions')
			->where($id_col, $customer_id)
			->order_by('id', 'DESC');

		if ($limit !== null) {
			$this->db->limit((int)$limit);
		}

		$res = $this->db->get()->result();
		return $res ? $res : array();
	}
}
