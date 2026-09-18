<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');

class Return_model extends CI_Model {
    
    public function get_vendor_returns($vendor_id)
    {
        $this->db->select('r.*, r.id as return_id, COALESCE(oi.product_name, np.name) as product_name, r.image_proof');
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('(oi.login_id = '.(int)$vendor_id.' OR np.vendor_id = '.(int)$vendor_id.')', null, false);
        $this->db->order_by('r.created_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function insert_return($data)
    {
        return $this->db->insert('returns', $data);
    }

    public function get_return_details($return_id, $vendor_id)
    {
        $this->db->select('r.*, r.id as return_id, COALESCE(oi.product_name, np.name) as product_name, o.order_number, o.date_added as order_date, c.fname as first_name, c.lname as last_name, c.email, r.image_proof');
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->join('ec_orders o', 'o.id = r.order_id', 'left');
        $this->db->join('ec_customer c', 'c.customer_id = o.user_id', 'left');
        $this->db->where('r.id', (int)$return_id);
        $this->db->where('(oi.login_id = '.(int)$vendor_id.' OR np.vendor_id = '.(int)$vendor_id.')', null, false);
        $query = $this->db->get();
        return $query->row();
    }

    public function update_return($return_id, $vendor_id, $data)
    {
        // verify ownership first
        $this->db->select('r.id');
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->where('r.id', (int)$return_id);
        $this->db->where('(oi.login_id = '.(int)$vendor_id.' OR np.vendor_id = '.(int)$vendor_id.')', null, false);
        $query = $this->db->get();
        
        if($query->num_rows() > 0) {
            $this->db->where('id', (int)$return_id);
            return $this->db->update('returns', $data);
        }
        return false;
    }

    private function _admin_returns_base_query($filters = array())
    {
        $this->db->from('returns r');
        $this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
        $this->db->join('products np', 'np.id = r.product_id', 'left');
        $this->db->join('ec_vendor v', 'v.vendor_id = COALESCE(np.vendor_id, oi.login_id)', 'left', false);
        $this->db->join('ec_orders o', 'o.id = r.order_id', 'left');
        $this->db->join('ec_customer c', 'c.customer_id = o.user_id', 'left');

        if (is_array($filters)) {
            $status = isset($filters['status']) ? trim((string)$filters['status']) : '';
            if ($status !== '') {
                $this->db->where('r.status', $status);
            }

            $vendor_id = isset($filters['vendor_id']) ? (int)$filters['vendor_id'] : 0;
            if ($vendor_id > 0) {
                $this->db->where('(oi.login_id = '.$vendor_id.' OR np.vendor_id = '.$vendor_id.')', null, false);
            }

            $product = isset($filters['product']) ? trim((string)$filters['product']) : '';
            if ($product !== '') {
                $this->db->group_start()
                    ->like('np.name', $product)
                ->group_end();
            }

            $date_from = isset($filters['date_from']) ? trim((string)$filters['date_from']) : '';
            $date_to = isset($filters['date_to']) ? trim((string)$filters['date_to']) : '';
            $date_col = $this->db->field_exists('created_at', 'returns') ? 'r.created_at' : ($this->db->field_exists('date_added', 'returns') ? 'r.date_added' : '');
            if ($date_col !== '') {
                if ($date_from !== '') {
                    $this->db->where($date_col.' >=', $date_from.' 00:00:00');
                }
                if ($date_to !== '') {
                    $this->db->where($date_col.' <=', $date_to.' 23:59:59');
                }
            }
        }
    }

    public function get_admin_returns($post_data = array(), $filters = array())
    {
        $this->db->select('r.*, r.id as return_id');
        $this->db->select('COALESCE(oi.product_name, np.name) as product_name');
        $this->db->select('v.store_name as vendor_store_name, v.name as vendor_name');
        $this->db->select('o.order_number');
        $this->db->select('c.fname as customer_fname, c.lname as customer_lname, c.email as customer_email');

        $this->_admin_returns_base_query($filters);

        $order_col = $this->db->field_exists('created_at', 'returns') ? 'r.created_at' : ($this->db->field_exists('date_added', 'returns') ? 'r.date_added' : 'r.id');
        $this->db->order_by($order_col, 'DESC');

        $length = isset($post_data['length']) ? (int)$post_data['length'] : 10;
        $start = isset($post_data['start']) ? (int)$post_data['start'] : 0;
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        return $this->db->get()->result();
    }

    public function count_admin_returns_all()
    {
        $this->db->select('COUNT(*) as cnt', false);
        $this->db->from('returns r');
        $row = $this->db->get()->row();
        return $row ? (int)$row->cnt : 0;
    }

	public function count_admin_returns_filtered($post_data = array(), $filters = array())
	{
		$this->db->select('COUNT(*) as cnt', false);
		$this->_admin_returns_base_query($filters);
		$row = $this->db->get()->row();
		return $row ? (int)$row->cnt : 0;
	}

	public function get_admin_return_details($return_id)
	{
		$this->db->select('r.*, r.id as return_id');
		$this->db->select('COALESCE(oi.product_name, np.name) as product_name');
		$this->db->select('v.store_name as vendor_store_name, v.name as vendor_name, v.vendor_id as vendor_id');
		$this->db->select('o.order_number, o.date_added as order_date');
		$this->db->select('c.fname as customer_fname, c.lname as customer_lname, c.email as customer_email, c.customer_id as customer_id');
		$this->db->from('returns r');
		$this->db->join('ec_order_items oi', 'oi.id = r.order_item_id', 'left');
		$this->db->join('products np', 'np.id = r.product_id', 'left');
		$this->db->join('ec_vendor v', 'v.vendor_id = COALESCE(np.vendor_id, oi.login_id)', 'left', false);
		$this->db->join('ec_orders o', 'o.id = r.order_id', 'left');
		$this->db->join('ec_customer c', 'c.customer_id = o.user_id', 'left');
		$this->db->where('r.id', (int)$return_id);
		return $this->db->get()->row();
	}

	public function admin_update_return($return_id, $data)
	{
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				if (!$this->db->field_exists($k, 'returns')) {
					unset($data[$k]);
				}
			}
		}
		$this->db->where('id', (int)$return_id);
		return $this->db->update('returns', $data);
	}
}
