<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Returns extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Query_model');
        $this->config->load('custom_config');
		$this->load->helper('api');
    }

	private function _get_customer_access_token()
	{
		$customer = $this->session->userdata('customer');
		if (is_array($customer) && !empty($customer['access_token'])) {
			return (string)$customer['access_token'];
		}
		if (!empty($_SESSION['token'])) {
			return (string)$_SESSION['token'];
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

    private function _resolve_customer_id() {
        $customer = $this->session->userdata('customer');
        if (!$customer) {
            return 0;
        }
        return (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
    }

	private function _eligible_for_return_item($item_row) {
		if (!$item_row) {
			return [false, 'Order item not found'];
		}

		$status = (int)($item_row->status ?? 0);
		if ($status !== 10) {
			return [false, 'Return allowed only for delivered items'];
		}

		$delivered_at = null;
		if (!empty($item_row->last_updated)) {
			$delivered_at = (string)$item_row->last_updated;
		} elseif (!empty($item_row->updated_at)) {
			$delivered_at = (string)$item_row->updated_at;
		} elseif (!empty($item_row->date_added)) {
			$delivered_at = (string)$item_row->date_added;
		}

		if (!$delivered_at) {
			return [false, 'Delivery date not available'];
		}

		$delivered_ts = strtotime($delivered_at);
		if (!$delivered_ts) {
			return [false, 'Invalid delivery date'];
		}

		$expiry_ts = $delivered_ts + (7 * 24 * 60 * 60);
		if (time() > $expiry_ts) {
			return [false, 'Return window expired'];
		}

		return [true, 'OK'];
	}

    public function index() {
        $customer_id = $this->_resolve_customer_id();
        if (!$customer_id) {
            redirect('login');
            return;
        }

        $data = [];
        $data['TYPE'] = 'customer';
        $data['csrf'] = function_exists('csrf_token') ? csrf_token() : null;

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$api = call_api('GET', 'returns', null, $headers);
			if ($api['response'] !== null && (int)($api['response']['status'] ?? 0) === 1) {
				$payload = $api['response']['data'] ?? [];
				$data['returns'] = $payload['returns'] ?? [];
				$this->load->template('customer/my_returns', $data);
				return;
			}

			$apiMsg = (string)($api['response']['msg'] ?? $api['response']['message'] ?? '');
			$apiMsgLc = strtolower(trim($apiMsg));
			if ($apiMsgLc !== '' && (strpos($apiMsgLc, 'unauthor') !== false || strpos($apiMsgLc, 'token') !== false || strpos($apiMsgLc, 'forbidden') !== false)) {
				// auth issues: fall back to DB instead of blocking customer returns page
			} else {
				$data['returns'] = [];
				$data['api_error'] = $apiMsg !== '' ? $apiMsg : 'Failed to load returns';
				$this->load->template('customer/my_returns', $data);
				return;
			}
		}

		// Fallback (non-api)
		$rows = $this->db
			->select('r.*', false)
			->select('COALESCE(oi.product_name, np.name) as product_name', false)
			->select('o.order_number', false)
			->from('returns r')
			->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left')
			->join('products np', 'np.id = r.product_id', 'left')
			->join('ec_orders o', 'o.id = r.order_id', 'left')
			->where('r.customer_id', (int)$customer_id)
			->order_by('r.id', 'DESC')
			->get()->result();
		$data['returns'] = $rows ? $rows : [];

        $this->load->template('customer/my_returns', $data);
    }

    public function request() {
        $customer_id = $this->_resolve_customer_id();
        if (!$customer_id) {
            return api_response(['status' => 0, 'msg' => 'Not logged in', 'data' => []]);
        }

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();

        $order_id = (int)$this->input->post('order_id');
        $order_item_id = (int)$this->input->post('order_item_id');
        $product_id = (int)$this->input->post('product_id');
        $vendor_id = (int)$this->input->post('vendor_id');
        $reason = trim((string)$this->input->post('reason'));
        $description = trim((string)$this->input->post('description'));

        if ($order_id <= 0 || $order_item_id <= 0 || $product_id <= 0) {
            return api_response(['status' => 0, 'msg' => 'Invalid order item', 'data' => []]);
        }

        $allowed_reasons = [
            'Damaged product',
            'Wrong product received',
            'Defective product',
            'Not as described',
            'Other',
        ];
        if ($reason === '' || !in_array($reason, $allowed_reasons, true)) {
            return api_response(['status' => 0, 'msg' => 'Invalid return reason', 'data' => []]);
        }

		$fallback_image_path = null;
		// API-first
		if ($use_api && !empty($headers)) {
			$images = [];
			if (!empty($_FILES['image_proof']['name'])) {
				$config['upload_path'] = './uploads/returns/';
				$config['allowed_types'] = 'jpg|jpeg|png|webp';
				$config['max_size'] = 4096;
				$config['encrypt_name'] = true;

				if (!is_dir($config['upload_path'])) {
					@mkdir($config['upload_path'], 0755, true);
				}

				$this->load->library('upload', $config);
				if (!$this->upload->do_upload('image_proof')) {
					return api_response(['status' => 0, 'msg' => $this->upload->display_errors('', ''), 'data' => []]);
				}
				$fileData = $this->upload->data();
				$fallback_image_path = 'uploads/returns/' . $fileData['file_name'];
				$images[] = base_url($fallback_image_path);
			}

			$payload = [
				'order_id' => $order_id,
				'order_item_id' => $order_item_id,
				'reason' => $reason,
				'images' => $images,
			];
			$api = call_api('POST', 'returns/request', $payload, $headers);
			if ($api['response'] !== null && (int)($api['response']['status'] ?? 0) === 1) {
				return api_response(['status' => 1, 'msg' => (string)($api['response']['msg'] ?? 'Return requested successfully'), 'data' => $api['response']['data'] ?? []]);
			}
			$msg = 'Failed to request return.';
			if ($api['response'] !== null) {
				$msg = (string)($api['response']['msg'] ?? $api['response']['message'] ?? $msg);
			}
			$msgLc = strtolower(trim((string)$msg));
			if (!($msgLc !== '' && (strpos($msgLc, 'unauthor') !== false || strpos($msgLc, 'token') !== false || strpos($msgLc, 'forbidden') !== false))) {
				return api_response(['status' => 0, 'msg' => $msg, 'data' => []]);
			}
			// auth failed: fall back to DB insert below
		}

        $order_row = $this->db
            ->from('ec_orders')
            ->where('id', (int)$order_id)
            ->where('user_id', (int)$customer_id)
            ->limit(1)
            ->get()->row();

        $item_row = $this->db
            ->from('ec_order_items')
            ->where('id', (int)$order_item_id)
            ->where('order_id', (int)$order_id)
            ->limit(1)
            ->get()->row();

        if (!$item_row || (int)($item_row->product_id ?? 0) !== $product_id) {
            return api_response(['status' => 0, 'msg' => 'Invalid order item', 'data' => []]);
        }

        [$okItem, $msgItem] = $this->_eligible_for_return_item($item_row);
        if (!$okItem) {
            return api_response(['status' => 0, 'msg' => $msgItem, 'data' => []]);
        }

        $vendor_id_db = 0;
        if ($vendor_id > 0) {
            $vendor_id_db = $vendor_id;
        } elseif (isset($item_row->login_id)) {
            $vendor_id_db = (int)$item_row->login_id;
        }

        $existing = $this->db
            ->from('returns')
            ->where('order_item_id', (int)$order_item_id)
            ->where('customer_id', (int)$customer_id)
            ->limit(1)
            ->get()->row();

        if ($existing) {
            return api_response(['status' => 0, 'msg' => 'Return already requested for this item', 'data' => []]);
        }

        $image_path = $fallback_image_path;
        if ($image_path === null && !empty($_FILES['image_proof']['name'])) {
            $config['upload_path'] = './uploads/returns/';
            $config['allowed_types'] = 'jpg|jpeg|png|webp';
            $config['max_size'] = 4096;
            $config['encrypt_name'] = true;

            if (!is_dir($config['upload_path'])) {
                @mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('image_proof')) {
                return api_response(['status' => 0, 'msg' => $this->upload->display_errors('', ''), 'data' => []]);
            }
            $fileData = $this->upload->data();
            $image_path = 'uploads/returns/' . $fileData['file_name'];
        }

        $now = date('Y-m-d H:i:s');
        $insert = [
            'order_id' => $order_id,
            'order_item_id' => $order_item_id,
            'product_id' => $product_id,
            'customer_id' => $customer_id,
            'vendor_id' => $vendor_id_db,
            'reason' => $reason,
            'description' => $description,
            'image_proof' => $image_path,
            'status' => 'requested',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $ok = $this->db->insert('returns', $insert);
        if (!$ok) {
            return api_response(['status' => 0, 'msg' => 'Failed to save return request', 'data' => []]);
        }

        return api_response(['status' => 1, 'msg' => 'Return requested successfully', 'data' => ['return_id' => $this->db->insert_id()]]);
    }

	public function list_json() {
		$customer_id = $this->_resolve_customer_id();
		if (!$customer_id) {
			return api_response(['status' => 0, 'msg' => 'Not logged in', 'data' => []]);
		}

		$use_api = (bool)$this->config->item('use_api');
		$headers = $this->_build_api_headers();
		if ($use_api && !empty($headers)) {
			$api = call_api('GET', 'returns', null, $headers);
			if ($api['response'] !== null && (int)($api['response']['status'] ?? 0) === 1) {
				$payload = $api['response']['data'] ?? [];
				$returns = $payload['returns'] ?? [];
				$orderIds = [];
				$orderItemIds = [];
				if (is_array($returns)) {
					foreach ($returns as $r) {
						$row = is_array($r) ? $r : (array)$r;
						$oid = (int)($row['order_id'] ?? 0);
						$oiid = (int)($row['order_item_id'] ?? 0);
						if ($oid > 0) $orderIds[$oid] = 1;
						if ($oiid > 0) $orderItemIds[$oiid] = 1;
					}
				}
				return api_response([
					'status' => 1,
					'msg' => 'returns map',
					'data' => [
						'order_ids' => array_keys($orderIds),
						'order_item_ids' => array_keys($orderItemIds),
					]
				]);
			}
		}

		$rows = $this->db
			->select('order_id, order_item_id')
			->from('returns')
			->where('customer_id', (int)$customer_id)
			->get()->result_array();
		$orderIds = [];
		$orderItemIds = [];
		foreach (($rows ?: []) as $r) {
			$oid = (int)($r['order_id'] ?? 0);
			$oiid = (int)($r['order_item_id'] ?? 0);
			if ($oid > 0) $orderIds[$oid] = 1;
			if ($oiid > 0) $orderItemIds[$oiid] = 1;
		}
		return api_response([
			'status' => 1,
			'msg' => 'returns map',
			'data' => [
				'order_ids' => array_keys($orderIds),
				'order_item_ids' => array_keys($orderItemIds),
			]
		]);
	}

	public function order_availability_json() {
		$customer_id = $this->_resolve_customer_id();
		if (!$customer_id) {
			return api_response(['status' => 0, 'msg' => 'Not logged in', 'data' => []]);
		}

		$order_ids = $this->input->post('order_ids');
		if (is_string($order_ids)) {
			$order_ids = array_filter(array_map('trim', explode(',', $order_ids)));
		}
		if (!is_array($order_ids)) {
			$order_ids = [];
		}
		$order_ids = array_values(array_unique(array_map('intval', $order_ids)));
		$order_ids = array_values(array_filter($order_ids, function($v){ return $v > 0; }));
		if (empty($order_ids)) {
			return api_response(['status' => 1, 'msg' => 'ok', 'data' => ['orders' => []]]);
		}

		// total items per order
		$totalsRows = $this->db
			->select('order_id, COUNT(*) as total_items', false)
			->from('ec_order_items')
			->where_in('order_id', $order_ids)
			->group_by('order_id')
			->get()->result_array();
		$totals = [];
		foreach (($totalsRows ?: []) as $r) {
			$oid = (int)($r['order_id'] ?? 0);
			$totals[$oid] = (int)($r['total_items'] ?? 0);
		}

		// returned counts per order
		$returnCountsRows = $this->db
			->select('order_id, COUNT(DISTINCT CASE WHEN order_item_id > 0 THEN order_item_id END) as returned_items', false)
			->from('returns')
			->where('customer_id', (int)$customer_id)
			->where_in('order_id', $order_ids)
			->group_by('order_id')
			->get()->result_array();
		$returnCounts = [];
		foreach (($returnCountsRows ?: []) as $r) {
			$oid = (int)($r['order_id'] ?? 0);
			$returnCounts[$oid] = (int)($r['returned_items'] ?? 0);
		}

		// returned order_item_ids per order
		$returnedItemsRows = $this->db
			->select('order_id, order_item_id')
			->from('returns')
			->where('customer_id', (int)$customer_id)
			->where_in('order_id', $order_ids)
			->where('order_item_id >', 0)
			->get()->result_array();
		$returnedMap = [];
		foreach (($returnedItemsRows ?: []) as $r) {
			$oid = (int)($r['order_id'] ?? 0);
			$oiid = (int)($r['order_item_id'] ?? 0);
			if ($oid <= 0 || $oiid <= 0) continue;
			if (!isset($returnedMap[$oid])) $returnedMap[$oid] = [];
			$returnedMap[$oid][] = $oiid;
		}

		$out = [];
		foreach ($order_ids as $oid) {
			$total = (int)($totals[$oid] ?? 0);
			$ret = (int)($returnCounts[$oid] ?? 0);
			$hasUnreturned = ($total > 0) ? ($ret < $total) : true;
			$out[$oid] = [
				'has_unreturned_item' => $hasUnreturned ? 1 : 0,
				'returned_order_item_ids' => array_values(array_unique($returnedMap[$oid] ?? [])),
			];
		}

		return api_response(['status' => 1, 'msg' => 'ok', 'data' => ['orders' => $out]]);
	}
}
