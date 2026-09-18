<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advertisement_model extends CI_Model {

    private $table = 'ec_advertisement';
    private $primary_key = 'advertisement_id';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_paginated_data($limit, $start, $filter = null)
    {
        // --- UPDATED: Removed 'status' ---
        $select = [
            'advertisement_id AS id',
            'advertisement_name AS title',
            'advertisement_link AS link',
            'advertisement_image AS image',
        ];
        if ($this->db->field_exists('advertisement_tag', $this->table)) {
            $select[] = 'advertisement_tag AS tag';
        }
        if ($this->db->field_exists('is_visible', $this->table)) {
            $select[] = 'is_visible AS is_visible';
        }
        $this->db->select(implode(',', $select));
        
        $this->_apply_filters($filter);
        $this->db->limit($limit, $start);
        $query = $this->db->get($this->table);
        return $query->result();
    }

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    public function count_filtered($filter = null)
    {
        $this->_apply_filters($filter);
        $query = $this->db->get($this->table);
        return $query->num_rows();
    }
    
    public function delete_by_id($id)
    {
        $this->db->where($this->primary_key, $id);
        return $this->db->delete($this->table);
    }
    
    private function _apply_filters($filter)
    {
        // --- UPDATED: Removed 'status' filter ---
        if (isset($filter['title']) && !empty($filter['title'])) {
            $this->db->like('advertisement_name', $filter['title']);
        }
    }

    /**
     * Fetches a single advertisement by its ID.
     */
    public function get_by_id($id)
    {
        $this->db->where($this->primary_key, $id);
        return $this->db->get($this->table)->row(); // .row() gets a single result
    }

    /**
     * Updates a single advertisement by its ID.
     */
    public function update($id, $data)
    {
        $this->db->where($this->primary_key, $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Fetches all advertisements for the main website.
     * Orders by creation date (newest first).
     */
    public function get_all_ads() // <-- This line is now fixed
    {
        // Selects all columns from your advertisement table
        $this->db->from($this->table);
        
        // This is the key part for your requirement:
        // It sorts the results by 'created_at' in 'DESC' (descending) order.
        $this->db->order_by('created_at', 'DESC'); 
        
        // Run the query and get the results
        $query = $this->db->get();
        
        // Return all matching ads as an array of objects
        return $query->result();
    }

    public function set_visible_selected(array $ids)
    {
        if (!$this->db->field_exists('is_visible', $this->table)) {
            return false;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        $this->db->trans_start();
        $this->db->update($this->table, ['is_visible' => 0]);
        if ($ids) {
            $this->db->where_in($this->primary_key, $ids)->update($this->table, ['is_visible' => 1]);
        }
        $this->db->trans_complete();

        return $this->db->trans_status() ? true : false;
    }

    public function get_visible_ads($limit = 3)
    {
        $limit = (int)$limit;
        if ($limit <= 0) {
            $limit = 3;
        }

        if (!$this->db->field_exists('is_visible', $this->table)) {
            return [];
        }

        $this->db->from($this->table);
        $this->db->where('is_visible', 1);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function update_visible($id, $is_visible)
    {
        if (!$this->db->field_exists('is_visible', $this->table)) {
            return false;
        }

        $id = (int)$id;
        $is_visible = (int)$is_visible ? 1 : 0;
        if ($id <= 0) {
            return false;
        }

        $this->db->where($this->primary_key, $id);
        return $this->db->update($this->table, ['is_visible' => $is_visible]);
    }
}