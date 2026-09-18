<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

/**
 * Vendor Profile API Controller
 * Handles: get_profile, update_profile, update_account_info, update_password, reupload_documents
 *
 * Routes (add to config/routes.php):
 *   GET  api/v1/vendor/profile             -> vendor/VendorProfile/get_profile
 *   POST api/v1/vendor/profile/update      -> vendor/VendorProfile/update_profile
 *   POST api/v1/vendor/profile/update_profile     -> vendor/VendorProfile/update_account_info
 *   POST api/v1/vendor/profile/password    -> vendor/VendorProfile/update_password
 *   POST api/v1/vendor/profile/documents   -> vendor/VendorProfile/reupload_documents
 */
class VendorProfile extends API_Controller
{
    private $access_ttl = 31536000;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->library('session');
        $this->load->helper('api');
    }

    // ----------------------------------------------------------------
    // Auth guard
    // ----------------------------------------------------------------

    private function _require_vendor()
    {
        $header = $this->input->get_request_header('Authorization', true);
        if (!$header) {
            $header = $this->input->server('HTTP_AUTHORIZATION');
        }
        $header = trim((string)$header);
        $token  = '';
        if (stripos($header, 'Bearer ') === 0) {
            $token = trim(substr($header, 7));
        }

        if ($token === '') {
            $this->fail('unauthorized', ['Missing Authorization Bearer token'], 401);
            return 0;
        }

        $claims = $this->verify_token($token);
        if (!$claims || !isset($claims['sub']) || ($claims['type'] ?? '') !== 'access' || ($claims['role'] ?? '') !== 'vendor') {
            $this->fail('unauthorized', ['Invalid or expired vendor access token'], 401);
            return 0;
        }

        $vendor_id = (int)$claims['sub'];
        if ($vendor_id <= 0) {
            $this->fail('unauthorized', ['Invalid token subject'], 401);
            return 0;
        }

        // Merge session so browser session elements are not wiped
        $vend = $this->session->userdata('vendor');
        $vend = is_array($vend) ? $vend : [];
        $vend['login_id'] = $vendor_id;
        $vend['vendor_id'] = $vendor_id;
        $vend['logged_in'] = $vend['logged_in'] ?? TRUE;
        $this->session->set_userdata('vendor', $vend);
        $this->session->set_userdata('type', 'vendor');

        return $vendor_id;
    }

    // ----------------------------------------------------------------
    // Endpoints
    // ----------------------------------------------------------------

    /**
     * GET api/v1/vendor/profile
     * Returns the logged-in vendor's full profile data.
     */
    public function get_profile()
    {
        if (strtoupper((string)$this->input->method()) !== 'GET') {
            return $this->fail('method_not_allowed', ['Only GET is allowed'], 405);
        }

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) return;

        $vendor = $this->Query_model->get_data_obj('ec_vendor', ['vendor_id' => $vendor_id]);
        if (!$vendor) {
            return $this->fail('not_found', ['Vendor record not found'], 404);
        }

        // Build document info
        $doc_status     = '';
        $doc_admin_note = '';
        $doc_front      = '';
        $doc_back       = '';
        $doc_table      = 'ec_vendor_verification';

        if ($this->db->table_exists($doc_table)) {
            $vendor_col = '';
            if ($this->db->field_exists('vendor_id', $doc_table))      $vendor_col = 'vendor_id';
            elseif ($this->db->field_exists('login_id', $doc_table))   $vendor_col = 'login_id';
            elseif ($this->db->field_exists('admin_id', $doc_table))   $vendor_col = 'admin_id';

            if ($vendor_col) {
                $doc_row = $this->db->select('*')->from($doc_table)
                    ->where($vendor_col, (int)$vendor_id)
                    ->order_by('id', 'DESC')->limit(1)->get()->row();

                if ($doc_row) {
                    $doc_status     = (string)($doc_row->status ?? '');
                    $doc_admin_note = (string)($doc_row->admin_note ?? '');
                    $doc_front      = (string)($doc_row->document_front ?? $doc_row->doc_front ?? '');
                    $doc_back       = (string)($doc_row->document_back  ?? $doc_row->doc_back  ?? '');
                }
            }
        }

        // Map account status from ec_vendor.status
        $account_status = 'pending';
        $st = (string)($vendor->status ?? '0');
        if ($st === '1') $account_status = 'approved';
        elseif ($st === '2') $account_status = 'rejected';

        // Fallback for doc_status if empty/blank
        if ($doc_status === '') {
            $doc_status = $account_status;
        }

        // Resolve image URL
        $vendor_img_url = '';
        if (!empty($vendor->vendor_img)) {
            $img_raw = $vendor->vendor_img;
            $arr = @unserialize($img_raw);
            if (is_array($arr) && isset($arr['large'])) {
                $vendor_img_url = $arr['large'];
            } elseif (preg_match('#^https?://#i', $img_raw)) {
                $vendor_img_url = $img_raw;
            } else {
                $vendor_img_url = base_url(ltrim($img_raw, '/'));
            }
        }

        // Resolve banner image URL
        $banner_img_url = '';
        if (!empty($vendor->banner_img)) {
            $b_raw = $vendor->banner_img;
            if (preg_match('#^https?://#i', $b_raw)) {
                $banner_img_url = $b_raw;
            } else {
                $banner_img_url = base_url(ltrim($b_raw, '/'));
            }
        }

        return $this->ok([
            'vendor_id'           => $vendor_id,
            'name'                => (string)($vendor->name       ?? ''),
            'email'               => (string)($vendor->email      ?? ''),
            'mobile'              => (string)($vendor->mobile     ?? ''),
            'store_name'          => (string)($vendor->store_name ?? ''),
            'address'             => (string)($vendor->address    ?? ''),
            'city'                => (string)($vendor->city       ?? ''),
            'country'             => (string)($vendor->country    ?? ''),
            'latitude'            => (string)($vendor->latitude   ?? ''),
            'longitude'           => (string)($vendor->longitude  ?? ''),
            'zip'                 => (string)($vendor->zip        ?? ''),
            'house_no'            => (string)($vendor->house_no   ?? ''),
            'location'            => (string)($vendor->location   ?? ''),
            'place_id'            => (string)($vendor->place_id   ?? ''),
            'vendor_img'          => $vendor_img_url,
            'banner_img'          => $banner_img_url,
            'account_status'      => $account_status,
            'document_status'     => $doc_status,
            'document_admin_note' => $doc_admin_note,
            'document_front'      => $doc_front ? base_url('uploads/vendor_documents/' . $doc_front) : '',
            'document_back'       => $doc_back  ? base_url('uploads/vendor_documents/' . $doc_back)  : '',
        ], 'success');
    }

    /**
     * POST api/v1/vendor/profile/update
     * Updates basic info: name, email, mobile (and logo if sent as base64 or filename via multipart)
     */
    public function update_profile()
    {
        if (!$this->require_post()) return;

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) return;

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $name    = isset($payload['name'])   ? trim((string)$payload['name'])   : (isset($payload['fname']) ? trim((string)$payload['fname']) : '');
        $email   = isset($payload['email'])  ? trim((string)$payload['email'])  : '';
        $mobile  = isset($payload['mobile']) ? trim((string)$payload['mobile']) : '';

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('validation_error', ['Please enter a valid email address.'], 422);
        }

        $update = [];
        if ($name   !== '') $update['name']   = $name;
        if ($email  !== '') $update['email']  = $email;
        if ($mobile !== '') $update['mobile'] = $mobile;

        // Handle logo upload (multipart)
        $logo_col = '';
        if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'] !== '') {
            $upload_dir = './assets/vendor/images';
            if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

            $config = [
                'upload_path'   => $upload_dir,
                'allowed_types' => 'gif|jpg|png|jpeg',
                'max_size'      => '20240000',
            ];
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('logo')) {
                return $this->fail('upload_error', [strip_tags($this->upload->display_errors())], 422);
            }
            $data_file = $this->upload->data();
            $file_name = $data_file['file_name'];
            $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
            $thumb     = pathinfo($file_name, PATHINFO_FILENAME) . '_thumb.' . $ext;

            $this->load->library('image_lib', [
                'image_library' => 'gd2',
                'source_image'  => $upload_dir . '/' . $file_name,
                'maintain_ratio'=> true,
                'width'         => 150,
                'height'        => 150,
                'new_image'     => $upload_dir . '/' . $thumb,
            ]);
            $this->image_lib->resize();
            $this->image_lib->clear();

            $update['logo'] = serialize([
                'large' => base_url('/assets/vendor/images/' . $file_name),
                'thumb' => base_url('/assets/vendor/images/' . $thumb),
            ]);
        }

        if (empty($update)) {
            return $this->fail('validation_error', ['No fields to update'], 422);
        }

        $this->Query_model->update_data('ec_vendor', $update, ['vendor_id' => $vendor_id]);
        $this->_reset_rejected_to_pending($vendor_id);
        return $this->ok([], 'Profile updated successfully.');
    }

    /**
     * POST api/v1/vendor/profile/update_profile
     * Updates store/account info: store_name, address, city, country, latitude, longitude, mobile, vendor_img
     */
    public function update_account_info()
    {
        if (!$this->require_post()) return;

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) return;

        $payload    = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $store_name = isset($payload['store_name']) ? trim((string)$payload['store_name']) : '';
        $address    = isset($payload['address'])    ? trim((string)$payload['address'])    : '';
        $city       = isset($payload['city'])       ? trim((string)$payload['city'])       : '';
        $country    = isset($payload['country'])    ? trim((string)$payload['country'])    : '';
        $latitude   = isset($payload['latitude'])   ? (string)$payload['latitude']         : '';
        $longitude  = isset($payload['longitude'])  ? (string)$payload['longitude']        : '';
        $mobile     = isset($payload['mobile'])     ? trim((string)$payload['mobile'])     : '';

        if ($store_name === '' || $address === '') {
            return $this->fail('validation_error', ['store_name and address are required'], 422);
        }

        $update = [
            'store_name' => $store_name,
            'address'    => $address,
            'city'       => $city,
            'country'    => $country,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
        ];
        if ($mobile !== '') $update['mobile'] = $mobile;

        if ($this->db->field_exists('zip', 'ec_vendor') && isset($payload['zip'])) {
            $update['zip'] = trim((string)$payload['zip']);
        }
        if ($this->db->field_exists('house_no', 'ec_vendor') && isset($payload['house_no'])) {
            $update['house_no'] = trim((string)$payload['house_no']);
        }
        if ($this->db->field_exists('location', 'ec_vendor') && isset($payload['location'])) {
            $update['location'] = trim((string)$payload['location']);
        }
        if ($this->db->field_exists('place_id', 'ec_vendor') && isset($payload['place_id'])) {
            $update['place_id'] = trim((string)$payload['place_id']);
        }

        // Handle vendor_img upload (multipart)
        if (isset($_FILES['vendor_img']['name']) && $_FILES['vendor_img']['name'] !== '') {
            $upload_dir = './assets/vendor/images';
            if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

            $config = [
                'upload_path'   => $upload_dir,
                'allowed_types' => 'gif|jpg|png|jpeg',
                'max_size'      => '20240000',
            ];
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('vendor_img')) {
                return $this->fail('upload_error', [strip_tags($this->upload->display_errors())], 422);
            }
            $data_file = $this->upload->data();
            $file_name = $data_file['file_name'];
            $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
            $thumb     = pathinfo($file_name, PATHINFO_FILENAME) . '_thumb.' . $ext;

            $this->load->library('image_lib', [
                'image_library' => 'gd2',
                'source_image'  => $upload_dir . '/' . $file_name,
                'maintain_ratio'=> true,
                'width'         => 150,
                'height'        => 150,
                'new_image'     => $upload_dir . '/' . $thumb,
            ]);
            $this->image_lib->resize();
            $this->image_lib->clear();

            $update['vendor_img'] = serialize([
                'large' => base_url('/assets/vendor/images/' . $file_name),
                'thumb' => base_url('/assets/vendor/images/' . $thumb),
            ]);
        }

        $this->Query_model->update_data('ec_vendor', $update, ['vendor_id' => $vendor_id]);
        $this->_reset_rejected_to_pending($vendor_id);
        return $this->ok([], 'Account info updated successfully.');
    }

    /**
     * POST api/v1/vendor/profile/password
     * Body: { new_password, confirm_password }
     */
    public function update_password()
    {
        if (!$this->require_post()) return;

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) return;

        $payload          = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $new_password     = isset($payload['new_password'])     ? (string)$payload['new_password']     : '';
        $confirm_password = isset($payload['confirm_password']) ? (string)$payload['confirm_password'] : '';

        if ($new_password === '' || $confirm_password === '') {
            return $this->fail('validation_error', ['new_password and confirm_password are required'], 422);
        }

        if (strlen($new_password) < 6) {
            return $this->fail('validation_error', ['New password must be at least 6 characters'], 422);
        }

        if ($new_password !== $confirm_password) {
            return $this->fail('validation_error', ['New password and confirm password do not match'], 422);
        }

        $this->Query_model->update_data('ec_vendor', ['password' => md5($new_password)], ['vendor_id' => $vendor_id]);
        return $this->ok([], 'Password updated successfully.');
    }

    /**
     * POST api/v1/vendor/profile/documents
     * Multipart: document_front (file), document_back (file)
     * Allowed only when doc status is pending or rejected.
     */
    public function reupload_documents()
    {
        if (!$this->require_post()) return;

        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) return;

        $doc_table = 'ec_vendor_verification';
        if (!$this->db->table_exists($doc_table)) {
            return $this->fail('not_found', ['Verification table not found.'], 404);
        }

        $vendor_col = '';
        if ($this->db->field_exists('vendor_id', $doc_table))      $vendor_col = 'vendor_id';
        elseif ($this->db->field_exists('login_id', $doc_table))   $vendor_col = 'login_id';
        elseif ($this->db->field_exists('admin_id', $doc_table))   $vendor_col = 'admin_id';

        if (!$vendor_col) {
            return $this->fail('server_error', ['Invalid verification table schema.'], 500);
        }

        $ver_row = $this->db->select('*')->from($doc_table)
            ->where($vendor_col, (int)$vendor_id)
            ->order_by('id', 'DESC')->limit(1)->get()->row();

        $cur_status = ($ver_row && isset($ver_row->status)) ? (string)$ver_row->status : 'pending';
        if ($cur_status !== 'rejected' && $cur_status !== 'pending') {
            return $this->fail('not_allowed', ['Document upload is only allowed when status is pending or rejected.'], 403);
        }

        $upload_dir = FCPATH . 'uploads/vendor_documents';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

        $config = [
            'upload_path'   => $upload_dir,
            'allowed_types' => 'gif|jpg|png|jpeg|pdf',
            'max_size'      => '20240000',
        ];
        $this->load->library('upload', $config);

        $doc_front_name = '';
        $doc_back_name  = '';

        if (isset($_FILES['document_front']['name']) && $_FILES['document_front']['name'] !== '') {
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('document_front')) {
                return $this->fail('upload_error', [strip_tags($this->upload->display_errors())], 422);
            }
            $doc_front_name = $this->upload->data()['file_name'] ?? '';
        }

        if (isset($_FILES['document_back']['name']) && $_FILES['document_back']['name'] !== '') {
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('document_back')) {
                return $this->fail('upload_error', [strip_tags($this->upload->display_errors())], 422);
            }
            $doc_back_name = $this->upload->data()['file_name'] ?? '';
        }

        $update = [];
        if ($this->db->field_exists('document_front', $doc_table) && $doc_front_name) $update['document_front'] = $doc_front_name;
        if ($this->db->field_exists('document_back',  $doc_table) && $doc_back_name)  $update['document_back']  = $doc_back_name;
        if ($this->db->field_exists('status',         $doc_table)) $update['status']     = 'pending';
        if ($this->db->field_exists('admin_note',     $doc_table)) $update['admin_note'] = '';
        if ($this->db->field_exists('updated_at',     $doc_table)) $update['updated_at'] = date('Y-m-d H:i:s');

        $is_new = (!$ver_row || !isset($ver_row->id));

        if ($is_new) {
            if ($this->db->field_exists('created_at', $doc_table)) $update['created_at'] = date('Y-m-d H:i:s');
            $update[$vendor_col] = (int)$vendor_id;
            
            if (empty($doc_front_name) && empty($doc_back_name)) {
                return $this->fail('validation_error', ['Please upload at least one document.'], 422);
            }
            $this->db->insert($doc_table, $update);
        } else {
            if (empty($doc_front_name) && empty($doc_back_name)) {
                return $this->fail('validation_error', ['No new document files provided.'], 422);
            }
            $this->db->where('id', (int)$ver_row->id)->update($doc_table, $update);
        }

        $this->_reset_rejected_to_pending($vendor_id);
        return $this->ok([], 'Documents submitted. Your account is under verification.');
    }

    /**
     * POST api/v1/vendor/profile/upload_photo
     * Multipart: vendor_img (file) — uploads/replaces vendor profile photo
     */
    public function upload_vendor_photo()
    {
        if (!$this->require_post()) return;
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) return;

        if (!isset($_FILES['vendor_img']['name']) || $_FILES['vendor_img']['name'] === '') {
            return $this->fail('validation_error', ['vendor_img file is required'], 422);
        }

        $upload_dir = './assets/vendor/images';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

        $config = [
            'upload_path'   => $upload_dir,
            'allowed_types' => 'gif|jpg|png|jpeg|webp',
            'max_size'      => '20240000',
        ];
        $this->load->library('Upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('vendor_img')) {
            return $this->fail('upload_error', [strip_tags($this->upload->display_errors())], 422);
        }

        $data_file = $this->upload->data();
        $file_name = $data_file['file_name'];
        $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
        $thumb     = pathinfo($file_name, PATHINFO_FILENAME) . '_thumb.' . $ext;

        $this->load->library('image_lib', [
            'image_library'  => 'gd2',
            'source_image'   => $upload_dir . '/' . $file_name,
            'maintain_ratio' => true,
            'width'          => 300,
            'height'         => 300,
            'new_image'      => $upload_dir . '/' . $thumb,
        ]);
        $this->image_lib->resize();
        $this->image_lib->clear();

        $serialized = serialize([
            'large' => base_url('/assets/vendor/images/' . $file_name),
            'thumb' => base_url('/assets/vendor/images/' . $thumb),
        ]);

        $this->Query_model->update_data('ec_vendor', ['vendor_img' => $serialized], ['vendor_id' => $vendor_id]);

        return $this->ok([
            'vendor_img'       => base_url('/assets/vendor/images/' . $file_name),
            'vendor_img_thumb' => base_url('/assets/vendor/images/' . $thumb),
        ], 'Profile photo updated successfully.');
    }

    /**
     * POST api/v1/vendor/profile/upload_banner
     * Multipart: banner_img (file) — uploads/replaces vendor banner/cover photo
     */
    public function upload_vendor_banner()
    {
        if (!$this->require_post()) return;
        $vendor_id = $this->_require_vendor();
        if (!$vendor_id) return;

        if (!isset($_FILES['banner_img']['name']) || $_FILES['banner_img']['name'] === '') {
            return $this->fail('validation_error', ['banner_img file is required'], 422);
        }

        $upload_dir = './assets/vendor/images';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

        $config = [
            'upload_path'   => $upload_dir,
            'allowed_types' => 'gif|jpg|png|jpeg|webp',
            'max_size'      => '20240000',
        ];
        $this->load->library('Upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('banner_img')) {
            return $this->fail('upload_error', [strip_tags($this->upload->display_errors())], 422);
        }

        $data_file = $this->upload->data();
        $file_name = $data_file['file_name'];
        $banner_url = base_url('/assets/vendor/images/' . $file_name);

        // Also create a wide crop for banner (1200x300)
        $banner_crop = pathinfo($file_name, PATHINFO_FILENAME) . '_banner.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $this->load->library('image_lib', [
            'image_library'  => 'gd2',
            'source_image'   => $upload_dir . '/' . $file_name,
            'maintain_ratio' => true,
            'width'          => 1200,
            'height'         => 300,
            'new_image'      => $upload_dir . '/' . $banner_crop,
        ]);
        $this->image_lib->resize();
        $this->image_lib->clear();

        // Store absolute URL in banner_img column
        // Check if column exists, add if not
        if (!$this->db->field_exists('banner_img', 'ec_vendor')) {
            $this->db->query('ALTER TABLE ec_vendor ADD COLUMN banner_img VARCHAR(500) NULL DEFAULT NULL');
        }

        $this->Query_model->update_data('ec_vendor', ['banner_img' => $banner_url], ['vendor_id' => $vendor_id]);

        return $this->ok([
            'banner_img' => $banner_url,
        ], 'Banner updated successfully.');
    }

    private function _reset_rejected_to_pending($vendor_id)
    {
        // 1. Get current vendor status
        $vendor = $this->Query_model->get_data_obj('ec_vendor', ['vendor_id' => $vendor_id]);
        if ($vendor && (string)$vendor->status === '2') {
            // Reset vendor status to pending (0)
            $this->Query_model->update_data('ec_vendor', ['status' => 0], ['vendor_id' => $vendor_id]);
            // Sync all products of this vendor to inactive (0)
            sync_vendor_product_status($vendor_id, 0);
        }

        // 2. Also reset verification row status to 'pending' if it was rejected
        $doc_table = 'ec_vendor_verification';
        if ($this->db->table_exists($doc_table)) {
            $vendor_col = '';
            if ($this->db->field_exists('vendor_id', $doc_table))      $vendor_col = 'vendor_id';
            elseif ($this->db->field_exists('login_id', $doc_table))   $vendor_col = 'login_id';
            elseif ($this->db->field_exists('admin_id', $doc_table))   $vendor_col = 'admin_id';

            if ($vendor_col) {
                $ver_row = $this->db->select('*')->from($doc_table)
                    ->where($vendor_col, (int)$vendor_id)
                    ->order_by('id', 'DESC')->limit(1)->get()->row();
                if ($ver_row && (string)($ver_row->status ?? '') === 'rejected') {
                    $this->db->where('id', (int)$ver_row->id)->update($doc_table, [
                        'status' => 'pending',
                        'admin_note' => '',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }
}
