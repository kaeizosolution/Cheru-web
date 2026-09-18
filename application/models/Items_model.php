<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Items_model extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $type         = $this->session->userdata('type') ?: 'vendor';
        $session_data = $this->session->userdata($type);
        $this->vendor_id = isset($session_data['vendor_id'])
            ? (int)$session_data['vendor_id']
            : (isset($session_data['login_id']) ? (int)$session_data['login_id'] : 0);
    }

    public function get_product($product_id)
    {
        return $this->slave
            ->where('id', $product_id)
            ->get('products')
            ->row();
    }

    public function get_variations($product_id)
    {
        $variations = $this->slave
            ->where('product_id', $product_id)
            ->get('product_variations')
            ->result();

        foreach ($variations as &$v) {
            $rawAttr = isset($v->attr_json) ? $v->attr_json : null;
            $rawAttr = is_string($rawAttr) ? trim($rawAttr) : '';
            if ($rawAttr === '') {
                $v->attr = [];
            } else {
                $decoded = json_decode($rawAttr, true);
                $v->attr = is_array($decoded) ? $decoded : [];
            }

            $v->prices = $this->slave
                ->where('variation_id', $v->id)
                ->order_by('min_qty', 'ASC')
                ->get('product_variation_price')
                ->result();
            $v->images = $this->slave
                ->where('variation_id', $v->id)
                ->get('product_variation_images')
                ->result();
        }

        return $variations;
    }

    private function _get_slug($string)
    {
        $string = trim((string)$string);
        $string = strtolower($string);
        $string = str_replace(' ', '-', $string);
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '', $string);
        $slug = preg_replace("/[\-]+/", '-', $slug);
        return $slug;
    }

    public function save_product()
    {
        $post = $this->input->post();
        $product_id = $post['product_id'] ?? 0;

        $this->db->trans_begin();

        try {
            // Check vendor status: 0=pending, 1=approved, 2=rejected
            $vendor_status = 0; // Default to pending (0) if not found
            if ($this->vendor_id) {
                $vendor = $this->db->select('status')->from('ec_vendor')->where('vendor_id', $this->vendor_id)->limit(1)->get()->row();
                if ($vendor) {
                    $vendor_status = (int)$vendor->status;
                }
            }
            // If approved (1) then active (1), else inactive (0)
            $prod_status = ($vendor_status === 1) ? '1' : '0';

            $product_data = [
                'name'         => $post['product_name'] ?? null,
                'slug'         => $this->_get_slug($post['product_name'] ?? ''),
                'product_type' => $post['product_type'] ?? 'simple',
                'category'     => $post['cat'] ?? null,
                'sub_category' => $post['sub_cat'] ?? null,
                'description'  => $post['description'] ?? null,
                'status'       => $prod_status
            ];

            if ($this->db->field_exists('sub_sub_category', 'products')) {
                $sub_sub = $post['sub_sub_cat'] ?? null;
                if ($sub_sub === null || $sub_sub === '') {
                    $sub_sub = $post['s_sub_cat_id'] ?? null;
                }
                if ($sub_sub === null || $sub_sub === '') {
                    $sub_sub = $post['sub_sub_cat_id'] ?? null;
                }
                $product_data['sub_sub_category'] = $sub_sub !== null ? (string)$sub_sub : null;
            }

            if ($this->db->field_exists('brand_id', 'products')) {
                $product_data['brand_id'] = isset($post['brand_id']) && $post['brand_id'] !== '' ? (int)$post['brand_id'] : null;
            }

            if ($this->db->field_exists('currency_id', 'products')) {
                $product_data['currency_id'] = isset($post['currency_id']) && $post['currency_id'] !== '' ? (int)$post['currency_id'] : 1;
            }

            // SKU
            if ($this->db->field_exists('sku', 'products')) {
                $sku_val = trim((string)($post['sku'] ?? ''));
                if ($sku_val !== '') {
                    $product_data['sku'] = $sku_val;
                }
            }

            // Stock
            if ($this->db->field_exists('stock', 'products')) {
                if (isset($post['stock']) && $post['stock'] !== '') {
                    $product_data['stock'] = (int)$post['stock'];
                }
            }

            if ($product_id && $product_id > 0) {
                $this->db->where('id', $product_id)
                         ->where('vendor_id', $this->vendor_id)
                         ->update('products', $product_data);
            } else {
                $product_data['vendor_id'] = $this->vendor_id;
                $this->db->insert('products', $product_data);
                $product_id = $this->db->insert_id();
            }

            $this->process_variations($product_id, $post);

            // Handle video upload (optional)
            if (!empty($_FILES['product_video']['name'])) {
                $video_result = $this->save_video($product_id);
                if ($video_result['status'] === false) {
                    throw new Exception($video_result['msg']);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction Failed");
            }

            $this->db->trans_commit();
            return ["status" => true, "product_id" => $product_id];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ["status" => false, "msg" => $e->getMessage()];
        }
    }

    private function process_variations($product_id, $post)
    {
        $keep_variation_ids = [];

        if ($post['product_type'] === 'simple') {
            $existing = $this->db->get_where('product_variations', ['product_id' => $product_id])->row();

            $v_id = $this->sync_row('product_variations', ($existing->id ?? 0), [
                'product_id' => $product_id,
                'attr_json'  => null,
                'stock'      => isset($post['stock']) ? (int)$post['stock'] : 0
            ]);
            $keep_variation_ids[] = $v_id;

            $this->save_price_tiers($v_id, $post, 'simple_');
            $this->save_images($v_id, 0);

        } else if (!empty($post['vars']) && is_array($post['vars'])) {
            $total_stock = 0;
            foreach ($post['vars'] as $index => $v) {
                $v_id_from_post = $v['variation_id'] ?? 0;
                $v_stock = isset($v['stock']) ? (int)$v['stock'] : 0;
                $total_stock += $v_stock;

                $v_id = $this->sync_row('product_variations', $v_id_from_post, [
                    'product_id' => $product_id,
                    'attr_json'  => isset($v['attr']) ? json_encode($v['attr']) : null,
                    'stock'      => $v_stock
                ]);
                $keep_variation_ids[] = $v_id;

                $this->save_price_tiers($v_id, $v);
                $this->save_images($v_id, $index);
            }

            // Sync total stock to main products table
            if ($this->db->field_exists('stock', 'products')) {
                $this->db->where('id', $product_id)->update('products', ['stock' => $total_stock]);
            }
        }

        if ($product_id && !empty($keep_variation_ids)) {
            $this->db->where('product_id', $product_id)
                ->where_not_in('id', $keep_variation_ids)
                ->delete('product_variations');
        }
    }

    private function sync_row($table, $id, $data)
    {
        if ($id > 0) {
            $this->db->where('id', $id)->update($table, $data);
            return $id;
        } else {
            $this->db->insert($table, $data);
            return $this->db->insert_id();
        }
    }

    private function save_price_tiers($variation_id, $data, $prefix = '')
    {
        $this->db->where('variation_id', $variation_id)->delete('product_variation_price');

        $min_qtys = $data[$prefix . 'min_qty'] ?? [];
        $max_qtys = $data[$prefix . 'max_qty'] ?? [];
        $prices   = $data[$prefix . 'price']   ?? [];

        if (!is_array($min_qtys)) { $min_qtys = [$min_qtys]; }
        if (!is_array($max_qtys)) { $max_qtys = [$max_qtys]; }
        if (!is_array($prices))   { $prices   = [$prices]; }

        foreach ($min_qtys as $i => $min_val) {
            if (empty($prices[$i])) continue;

            $this->db->insert('product_variation_price', [
                'variation_id' => $variation_id,
                'min_qty'      => $min_val,
                'max_qty'      => !empty($max_qtys[$i]) ? $max_qtys[$i] : null,
                'price'        => $prices[$i]
            ]);
        }
    }

    private function save_images($variation_id, $variation_index)
    {
        if (!isset($_FILES['variation_images'])) {
            return;
        }

        $files     = $_FILES['variation_images'];
        $names_raw = $files['name'] ?? null;

        $names     = [];
        $types     = [];
        $tmp_names = [];
        $errors    = [];
        $sizes     = [];

        if (is_string($names_raw) && $names_raw !== '') {
            // ── Case A: flat single file ──────────────────────────────────
            // Postman sends key "variation_images" (no brackets at all).
            // PHP gives: $files['name'] = 'filename.png', $files['error'] = 0 (int)
            // Only applies to the first variation (index 0).
            if ($variation_index !== 0) return;
            $names     = [$names_raw];
            $types     = [(string)($files['type']     ?? '')];
            $tmp_names = [(string)($files['tmp_name'] ?? '')];
            $errors    = [(int)   ($files['error']    ?? UPLOAD_ERR_NO_FILE)];
            $sizes     = [(int)   ($files['size']     ?? 0)];

        } elseif (is_array($names_raw)) {
            if (!isset($names_raw[$variation_index])) return;
            $slot = $names_raw[$variation_index];

            if (is_array($slot)) {
                // ── Case B: variation_images[varIdx][] ────────────────────
                // Web form and recommended Postman key sends nested arrays.
                $names     = $slot;
                $err_raw   = $files['error'][$variation_index]    ?? [];
                $sz_raw    = $files['size'][$variation_index]     ?? [];
                $types     = is_array($files['type'][$variation_index])     ? $files['type'][$variation_index]     : (array)($files['type'][$variation_index]     ?? '');
                $tmp_names = is_array($files['tmp_name'][$variation_index]) ? $files['tmp_name'][$variation_index] : (array)($files['tmp_name'][$variation_index] ?? '');
                $errors    = is_array($err_raw) ? $err_raw : [$err_raw];
                $sizes     = is_array($sz_raw)  ? $sz_raw  : [$sz_raw];

            } elseif (is_string($slot) && $slot !== '') {
                // ── Case C: variation_images[] (array without sub-index) ──
                // Postman sends key "variation_images[]".
                // PHP gives: $files['name'] = ['file.png'], $files['error'] = [0]
                $names     = [$slot];
                $types     = [(string)(is_array($files['type'])     ? ($files['type'][$variation_index]     ?? '') : $files['type'])];
                $tmp_names = [(string)(is_array($files['tmp_name']) ? ($files['tmp_name'][$variation_index] ?? '') : $files['tmp_name'])];
                $err_val   = is_array($files['error']) ? ($files['error'][$variation_index] ?? UPLOAD_ERR_NO_FILE) : (int)$files['error'];
                $errors    = [(int)$err_val];
                $sz_val    = is_array($files['size'])  ? ($files['size'][$variation_index]  ?? 0) : (int)$files['size'];
                $sizes     = [(int)$sz_val];

            } else {
                return; // slot is empty or unrecognised
            }
        } else {
            return; // no usable file data
        }

        $this->load->library('upload');

        $existing_count = $this->db->where('variation_id', $variation_id)
            ->count_all_results('product_variation_images');

        foreach ($names as $i => $name) {
            if (empty($name) || $existing_count >= 5) continue;

            $_FILES['temp_file'] = [
                'name'     => $names[$i]     ?? '',
                'type'     => $types[$i]     ?? '',
                'tmp_name' => $tmp_names[$i] ?? '',
                'error'    => $errors[$i]    ?? UPLOAD_ERR_NO_FILE,
                'size'     => $sizes[$i]     ?? 0,
            ];

            $rootDir   = str_replace('\\', '/', rtrim(FCPATH, '/\\'));
            $uploadDir = $rootDir . '/uploads/products/';

            if (!is_dir($rootDir . '/uploads/')) { @mkdir($rootDir . '/uploads/', 0777, true); }
            if (!is_dir($uploadDir))             { @mkdir($uploadDir, 0777, true); @chmod($uploadDir, 0777); }

            if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                throw new Exception('Upload folder is not writable: ' . $uploadDir);
            }

            $config = [
                'upload_path'   => $uploadDir,
                'allowed_types' => 'jpg|jpeg|png|webp|avif',
                'encrypt_name'  => TRUE,
                'max_size'      => '20240000',
            ];
            $this->upload->initialize($config);

            if ($this->upload->do_upload('temp_file')) {
                $ud = $this->upload->data();
                $this->db->insert('product_variation_images', [
                    'variation_id' => $variation_id,
                    'image_path'   => $ud['file_name'],
                ]);
                $existing_count++;
            } else {
                throw new Exception($this->upload->display_errors('', ''));
            }
        }
    }

    /**
     * Upload a product video and store filename in products.video_path
     */
    public function save_video($product_id)
    {
        $product_id = (int)$product_id;
        if (!$product_id) {
            return ['status' => false, 'msg' => 'Invalid product ID'];
        }

        $prod = $this->db->get_where('products', ['id' => $product_id, 'vendor_id' => $this->vendor_id])->row();
        if (!$prod) {
            return ['status' => false, 'msg' => 'Product not found or access denied'];
        }

        $rootDir  = str_replace('\\', '/', rtrim(FCPATH, '/\\'));
        $videoDir = $rootDir . '/uploads/product_videos/';

        if (!is_dir($videoDir)) { @mkdir($videoDir, 0755, true); @chmod($videoDir, 0755); }
        if (!is_dir($videoDir) || !is_writable($videoDir)) {
            return ['status' => false, 'msg' => 'Video folder not writable: ' . $videoDir];
        }

        $this->load->library('upload');
        $config = [
            'upload_path'   => $videoDir,
            'allowed_types' => 'mp4|mov|avi|wmv|webm|mkv',
            'max_size'      => '204800', // 200 MB in KB
            'encrypt_name'  => TRUE,
        ];
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('product_video')) {
            return ['status' => false, 'msg' => $this->upload->display_errors('', '')];
        }

        $ud           = $this->upload->data();
        $new_filename = $ud['file_name'];

        // Remove old video file
        if (!empty($prod->video_path)) {
            $old = $videoDir . ltrim($prod->video_path, '/\\');
            if (@file_exists($old)) { @unlink($old); }
        }

        // Save to DB (guarded by column existence check)
        if ($this->db->field_exists('video_path', 'products')) {
            $this->db->where('id', $product_id)
                     ->where('vendor_id', $this->vendor_id)
                     ->update('products', ['video_path' => $new_filename]);
        }

        return ['status' => true, 'video_path' => $new_filename];
    }

    /**
     * Delete a product video — vendor ownership enforced
     */
    public function delete_video($product_id)
    {
        $product_id = (int)$product_id;
        if (!$product_id || !$this->vendor_id) return false;

        $prod = $this->db->get_where('products', ['id' => $product_id, 'vendor_id' => $this->vendor_id])->row();
        if (!$prod) return false;

        if (!empty($prod->video_path)) {
            $rootDir  = str_replace('\\', '/', rtrim(FCPATH, '/\\'));
            $filePath = $rootDir . '/uploads/product_videos/' . ltrim($prod->video_path, '/\\');
            if (@file_exists($filePath)) { @unlink($filePath); }
        }

        if ($this->db->field_exists('video_path', 'products')) {
            $this->db->where('id', $product_id)
                     ->where('vendor_id', $this->vendor_id)
                     ->update('products', ['video_path' => null]);
        }

        return true;
    }

    /**
     * Delete a variation image — vendor ownership enforced
     */
    public function delete_image($image_id)
    {
        $image_id = (int)$image_id;
        if (!$image_id) return false;

        $row = $this->db
            ->select('pvi.id, pvi.variation_id, pvi.image_path, p.vendor_id')
            ->from('product_variation_images pvi')
            ->join('product_variations pv', 'pv.id = pvi.variation_id', 'inner')
            ->join('products p', 'p.id = pv.product_id', 'inner')
            ->where('pvi.id', $image_id)
            ->limit(1)
            ->get()->row();

        if (!$row || (int)$row->vendor_id !== (int)$this->vendor_id) return false;

        $deleted = $this->db->where('id', $image_id)->delete('product_variation_images');
        if (!$deleted) return false;

        $fn = trim((string)($row->image_path ?? ''));
        if ($fn !== '') {
            $path = str_replace('\\', '/', rtrim(FCPATH, '/\\')) . '/uploads/products/' . ltrim($fn, '/\\');
            if (@file_exists($path)) { @unlink($path); }
        }

        return true;
    }

}
