<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Influencer_video_model extends MI_Model {

    protected $table = 'influencer_videos';

    public function __construct() {
        parent::__construct();
    }

    public function insert($data) {
        $this->slave->insert($this->table, $data);
        return $this->slave->insert_id();
    }

    public function get_all($limit = 50, $offset = 0) {
        return $this->slave->order_by('created_at', 'DESC')
                        ->get($this->table, $limit, $offset)
                        ->result();
    }

    public function get($id) {
        return $this->slave->where('id', intval($id))->get($this->table)->row();
    }

    public function delete($id) {
        return $this->slave->where('id', intval($id))->delete($this->table);
    }
}

