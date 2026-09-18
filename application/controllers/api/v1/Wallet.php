<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

/**
 * Wallet API Controller
 *
 * GET  /api/v1/Wallet              → index()         balance + last 10 txns
 * GET  /api/v1/Wallet/transactions → transactions()  all transactions
 * GET  /api/v1/Wallet/list_banks   → list_banks()    list bank accounts
 * POST /api/v1/Wallet/save_bank    → save_bank()     add bank account
 * POST /api/v1/Wallet/delete_bank  → delete_bank()   remove bank account
 * POST /api/v1/Wallet/add_money    → add_money()     credit wallet
 * POST /api/v1/Wallet/withdraw     → withdraw()      request withdrawal
 */
class Wallet extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Wallet_model');
    }

    // ─── Auth helper ─────────────────────────────────────────────────────────

    private function _auth()
    {
        $token = $this->get_bearer_token();

        // Fallback: session-based auth (same-origin page requests)
        if ($token === '') {
            $customer = $this->session->userdata('customer');
            $id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
            if ($id > 0) return $id;
            $this->fail('unauthorized', ['Missing Authorization Bearer token'], 401);
            return 0;
        }

        $claims = $this->verify_token($token);
        if (!$claims
            || empty($claims['sub'])
            || ($claims['type'] ?? '') !== 'access'
            || ($claims['role'] ?? '') !== 'customer') {
            $this->fail('unauthorized', ['Invalid or expired token'], 401);
            return 0;
        }

        return (int)$claims['sub'];
    }

    private function _body()
    {
        return array_merge(
            $this->get_json_input(),
            $this->input->post(null, true) ?: []
        );
    }

    private function _fmt($txn)
    {
        $t = (array)$txn;
        return [
            'id'               => (int)   ($t['id']               ?? 0),
            'type'             => (string)($t['type']             ?? ''),
            'amount'           => (float) ($t['amount']           ?? 0),
            'transaction_type' => (string)($t['transaction_type'] ?? ''),
            'status'           => (string)($t['status']           ?? ''),
            'created_at'       => (string)($t['created_at']       ?? ''),
        ];
    }

    // ─── GET /api/v1/Wallet ──────────────────────────────────────────────────

    public function index()
    {
        $cid = $this->_auth();
        if (!$cid) return;

        $this->Wallet_model->ensure_wallet($cid);

        $this->ok([
            'balance'      => (float)$this->Wallet_model->get_balance($cid),
            'has_bank'     => (bool) $this->Wallet_model->has_bank_details($cid),
            'transactions' => array_map([$this, '_fmt'],
                              (array)$this->Wallet_model->get_transactions($cid, 10)),
        ]);
    }

    // ─── GET /api/v1/Wallet/transactions ─────────────────────────────────────

    public function transactions()
    {
        $cid = $this->_auth();
        if (!$cid) return;

        $limit = (int)($this->input->get('limit') ?? 0);
        $this->Wallet_model->ensure_wallet($cid);

        $this->ok([
            'balance'      => (float)$this->Wallet_model->get_balance($cid),
            'transactions' => array_map([$this, '_fmt'],
                              (array)$this->Wallet_model->get_transactions($cid, $limit > 0 ? $limit : null)),
        ]);
    }

    // ─── GET /api/v1/Wallet/list_banks ───────────────────────────────────────

    public function list_banks()
    {
        $cid = $this->_auth();
        if (!$cid) return;

        $banks = $this->Wallet_model->get_bank_accounts($cid);
        $this->ok(['banks' => array_values((array)$banks)]);
    }

    // ─── POST /api/v1/Wallet/save_bank ───────────────────────────────────────

    public function save_bank()
    {
        $cid = $this->_auth();
        if (!$cid) return;

        $b = $this->_body();
        $holder = trim((string)($b['account_holder_name'] ?? ''));
        $bank   = trim((string)($b['bank_name']           ?? ''));
        $acc    = trim((string)($b['account_number']      ?? ''));
        $ifsc   = trim((string)($b['ifsc_code']           ?? ''));

        if (!$holder || !$bank || !$acc || !$ifsc) {
            return $this->fail('validation_error', ['All bank fields are required.'], 422);
        }
        if ($this->Wallet_model->count_bank_accounts($cid) >= 3) {
            return $this->fail('limit_exceeded', ['Maximum 3 bank accounts allowed.'], 409);
        }

        $ok = $this->Wallet_model->save_bank_details($cid, [
            'account_holder_name' => $holder,
            'bank_name'           => $bank,
            'account_number'      => $acc,
            'ifsc_code'           => $ifsc,
        ]);

        $ok ? $this->ok(['message' => 'Bank details saved.'])
            : $this->fail('server_error', ['Failed to save bank details.'], 500);
    }

    // ─── POST /api/v1/Wallet/delete_bank ─────────────────────────────────────

    public function delete_bank()
    {
        $cid = $this->_auth();
        if (!$cid) return;

        $bank_id = (int)(($this->_body())['bank_id'] ?? 0);
        if ($bank_id <= 0) {
            return $this->fail('validation_error', ['bank_id is required.'], 422);
        }

        $ok = $this->Wallet_model->delete_bank_account($cid, $bank_id);
        $ok ? $this->ok(['message' => 'Bank account deleted.'])
            : $this->fail('server_error', ['Failed to delete bank account.'], 500);
    }

    // ─── POST /api/v1/Wallet/add_money ───────────────────────────────────────

    public function add_money()
    {
        $cid = $this->_auth();
        if (!$cid) return;

        $amount = (float)(($this->_body())['amount'] ?? 0);
        if ($amount <= 0) {
            return $this->fail('validation_error', ['Amount must be greater than 0.'], 422);
        }
        if (!$this->Wallet_model->has_bank_details($cid)) {
            return $this->fail('no_bank', ['Please add bank details first.'], 400);
        }

        $ok = $this->Wallet_model->add_money($cid, $amount);
        if (!$ok) {
            return $this->fail('server_error', ['Failed to add money.'], 500);
        }

        $this->ok([
            'balance' => (float)$this->Wallet_model->get_balance($cid),
            'message' => 'Money added successfully.',
        ]);
    }

    // ─── POST /api/v1/Wallet/withdraw ────────────────────────────────────────

    public function withdraw()
    {
        $cid = $this->_auth();
        if (!$cid) return;

        $amount = (float)(($this->_body())['amount'] ?? 0);
        if ($amount <= 0) {
            return $this->fail('validation_error', ['Amount must be greater than 0.'], 422);
        }

        $result = $this->Wallet_model->request_withdraw($cid, $amount);
        if (!$result['ok']) {
            return $this->fail('withdraw_error', [$result['msg']], 400);
        }

        $this->ok([
            'balance' => (float)$this->Wallet_model->get_balance($cid),
            'message' => $result['msg'],
        ]);
    }
}
