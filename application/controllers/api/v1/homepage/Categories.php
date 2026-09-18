<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Categories extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function _as_int($v, $default = 0)
    {
        if ($v === null) {
            return (int)$default;
        }
        $v = trim((string)$v);
        if ($v === '') {
            return (int)$default;
        }
        return (int)$v;
    }

    private function _as_bool($v)
    {
        if ($v === null) {
            return false;
        }
        $v = strtolower(trim((string)$v));
        return ($v === '1' || $v === 'true' || $v === 'yes' || $v === 'y');
    }

    private function _fetch_level($parent_id)
    {
        return $this->db
            ->select('id, name, slug, parent_id')
            ->from('ec_categories_prod')
            ->where('status', '1')
            ->where('parent_id', (int)$parent_id)
            ->order_by('id', 'DESC')
            ->get()->result_array();
    }

    private function _child_counts_map($parent_ids)
    {
        $out = [];
        $ids = is_array($parent_ids) ? $parent_ids : [];
        $ids = array_values(array_filter(array_map('intval', $ids), function ($v) {
            return $v > 0;
        }));

        if (empty($ids)) {
            return $out;
        }

        $counts = $this->db
            ->select('parent_id, COUNT(1) as cnt')
            ->from('ec_categories_prod')
            ->where('status', '1')
            ->where_in('parent_id', $ids)
            ->group_by('parent_id')
            ->get()->result_array();

        foreach ($counts as $c) {
            $pid = (int)($c['parent_id'] ?? 0);
            $out[$pid] = (int)($c['cnt'] ?? 0);
        }

        return $out;
    }

    private function _count_for($map, $id)
    {
        $id = (int)$id;
        return isset($map[$id]) ? (int)$map[$id] : 0;
    }

    public function index()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'GET' && $method !== 'POST') {
            return $this->fail('method_not_allowed', ['Only GET and POST are allowed'], 405);
        }

        $in = [];
        if ($method === 'POST') {
            $raw = (string)$this->input->raw_input_stream;
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $in = $decoded;
                }
            }
        }

        $parent_id = 0;
        $tree_raw = null;
        $nested_raw = null;
        $depth_raw = null;

        if ($method === 'POST') {
            $parent_id = $this->_as_int($in['parent_id'] ?? $this->input->post('parent_id'), 0);
            $tree_raw = $in['tree'] ?? $this->input->post('tree');
            $nested_raw = $in['nested'] ?? $this->input->post('nested');
            $depth_raw = $in['depth'] ?? $this->input->post('depth');
        } else {
            $parent_id = $this->_as_int($this->input->get('parent_id'), 0);
            $tree_raw = $this->input->get('tree');
            $nested_raw = $this->input->get('nested');
            $depth_raw = $this->input->get('depth');
        }

        $tree = $this->_as_bool($tree_raw) || $this->_as_bool($nested_raw);
        $depth = $this->_as_int($depth_raw, 3);
        if ($depth < 1) {
            $depth = 1;
        }
        if ($depth > 3) {
            $depth = 3;
        }

        // Default mode (non-breaking): return one level by parent_id.
        if (!$tree) {
            $rows = $this->_fetch_level($parent_id);
            $out = [];
            if (!empty($rows)) {
                $ids = array_map(function ($r) {
                    return (int)($r['id'] ?? 0);
                }, $rows);
                $childCountsByParent = $this->_child_counts_map($ids);

                foreach ($rows as $r) {
                    $id = (int)($r['id'] ?? 0);
                    $child_cnt = $this->_count_for($childCountsByParent, $id);
                    $out[] = [
                        'id' => $id,
                        'name' => (string)($r['name'] ?? ''),
                        'slug' => (string)($r['slug'] ?? ''),
                        'has_children' => ($child_cnt > 0),
                        'children_count' => $child_cnt,
                    ];
                }
            }
            return $this->ok(['categories' => $out], 'success');
        }

        // Tree mode: return nested categories up to 3 levels.
        // Level 1
        $lvl1 = $this->_fetch_level($parent_id);
        $lvl1_ids = array_values(array_filter(array_map(function ($r) {
            return (int)($r['id'] ?? 0);
        }, $lvl1), function ($v) {
            return $v > 0;
        }));

        // Level 2 (bulk)
        $lvl2 = [];
        $lvl2_by_parent = [];
        if ($depth >= 2 && !empty($lvl1_ids)) {
            $lvl2 = $this->db
                ->select('id, name, slug, parent_id')
                ->from('ec_categories_prod')
                ->where('status', '1')
                ->where_in('parent_id', $lvl1_ids)
                ->order_by('id', 'DESC')
                ->get()->result_array();

            foreach ($lvl2 as $r) {
                $pid = (int)($r['parent_id'] ?? 0);
                if (!isset($lvl2_by_parent[$pid])) {
                    $lvl2_by_parent[$pid] = [];
                }
                $lvl2_by_parent[$pid][] = $r;
            }
        }

        $lvl2_ids = [];
        if (!empty($lvl2)) {
            $lvl2_ids = array_values(array_filter(array_map(function ($r) {
                return (int)($r['id'] ?? 0);
            }, $lvl2), function ($v) {
                return $v > 0;
            }));
        }

        // Level 3 (bulk)
        $lvl3 = [];
        $lvl3_by_parent = [];
        if ($depth >= 3 && !empty($lvl2_ids)) {
            $lvl3 = $this->db
                ->select('id, name, slug, parent_id')
                ->from('ec_categories_prod')
                ->where('status', '1')
                ->where_in('parent_id', $lvl2_ids)
                ->order_by('id', 'DESC')
                ->get()->result_array();

            foreach ($lvl3 as $r) {
                $pid = (int)($r['parent_id'] ?? 0);
                if (!isset($lvl3_by_parent[$pid])) {
                    $lvl3_by_parent[$pid] = [];
                }
                $lvl3_by_parent[$pid][] = $r;
            }
        }

        $hasChildrenMapLvl1 = $this->_child_counts_map($lvl1_ids);
        $hasChildrenMapLvl2 = $this->_child_counts_map($lvl2_ids);

        $tree_out = [];
        foreach ($lvl1 as $r1) {
            $id1 = (int)($r1['id'] ?? 0);
            $child_cnt_1 = $this->_count_for($hasChildrenMapLvl1, $id1);
            $node1 = [
                'id' => $id1,
                'name' => (string)($r1['name'] ?? ''),
                'slug' => (string)($r1['slug'] ?? ''),
                'has_children' => ($child_cnt_1 > 0),
                'children_count' => $child_cnt_1,
                'children' => [],
            ];

            if ($depth >= 2 && isset($lvl2_by_parent[$id1])) {
                foreach ($lvl2_by_parent[$id1] as $r2) {
                    $id2 = (int)($r2['id'] ?? 0);
                    $child_cnt_2 = $this->_count_for($hasChildrenMapLvl2, $id2);
                    $node2 = [
                        'id' => $id2,
                        'name' => (string)($r2['name'] ?? ''),
                        'slug' => (string)($r2['slug'] ?? ''),
                        'has_children' => ($child_cnt_2 > 0),
                        'children_count' => $child_cnt_2,
                        'children' => [],
                    ];

                    if ($depth >= 3 && isset($lvl3_by_parent[$id2])) {
                        foreach ($lvl3_by_parent[$id2] as $r3) {
                            $id3 = (int)($r3['id'] ?? 0);
                            $node2['children'][] = [
                                'id' => $id3,
                                'name' => (string)($r3['name'] ?? ''),
                                'slug' => (string)($r3['slug'] ?? ''),
                                'has_children' => false,
                                'children_count' => 0,
                            ];
                        }
                    }

                    $node1['children'][] = $node2;
                }
            }

            $tree_out[] = $node1;
        }

        return $this->ok(['categories' => $tree_out], 'success');
    }
}
