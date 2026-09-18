<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Language extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
    }

    public function set_language()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST' && $method !== 'GET') {
            return $this->fail('method_not_allowed', ['Only POST and GET are allowed'], 405);
        }

        $payload = array_merge($this->get_json_input(), $_GET ?: [], $_POST ?: []);
        $language_id = isset($payload['language_id']) ? (int)$payload['language_id'] : 0;
        if ($language_id <= 0) {
            return $this->fail('validation_error', ['language_id is required and must be positive'], 422);
        }

        // Hardcoded list of all 9 supported languages (fallback when DB lookup fails)
        $hardcoded_languages = [
            3  => (object)['language_id' => 3,  'name' => 'English',    'iso_code' => 'en'],
            12 => (object)['language_id' => 12, 'name' => 'French',     'iso_code' => 'fr'],
            13 => (object)['language_id' => 13, 'name' => 'Spanish',    'iso_code' => 'es'],
            14 => (object)['language_id' => 14, 'name' => 'Canadian',   'iso_code' => 'ca'],
            15 => (object)['language_id' => 15, 'name' => 'Chinese',    'iso_code' => 'zh'],
            16 => (object)['language_id' => 16, 'name' => 'German',     'iso_code' => 'de'],
            17 => (object)['language_id' => 17, 'name' => 'Indonesian', 'iso_code' => 'id'],
            18 => (object)['language_id' => 18, 'name' => 'Japanese',   'iso_code' => 'ja'],
            19 => (object)['language_id' => 19, 'name' => 'Korean',     'iso_code' => 'ko'],
        ];

        // Try DB lookup first
        $row = null;
        try {
            $row = $this->db
                ->select('language_id, name, iso_code')
                ->from('ec_language')
                ->where('language_id', $language_id)
                ->where('status', '1')
                ->get()->row();
        } catch (Exception $e) {
            $row = null;
        }

        // If not in DB, use hardcoded fallback and auto-insert into DB
        if (!$row && isset($hardcoded_languages[$language_id])) {
            $row = $hardcoded_languages[$language_id];
            // Try to insert/update in DB so future requests hit DB
            try {
                $exists = $this->db->get_where('ec_language', ['language_id' => $language_id])->row();
                if (!$exists) {
                    $this->db->insert('ec_language', [
                        'language_id' => $language_id,
                        'name'        => $row->name,
                        'iso_code'    => $row->iso_code,
                        'status'      => '1',
                        'basic'       => 0,
                    ]);
                } else {
                    $this->db->where('language_id', $language_id)
                             ->update('ec_language', ['status' => '1']);
                }
            } catch (Exception $e) {
                // silently ignore DB errors — session will still be set
            }
        }

        // If still not found (unknown language_id), reject it
        if (!$row) {
            return $this->fail('not_found', ['Language not found or inactive'], 404);
        }

        // Set CI session language (persists to DB session table)
        $this->session->set_userdata('ln', $language_id);
        // Also set raw $_SESSION for any code that reads it directly
        $_SESSION['ln'] = $language_id;

        // Write session immediately (prevent race condition with page reload)
        try {
            if (method_exists($this->session, 'sess_write')) {
                $this->session->sess_write();
            } elseif (function_exists('session_write_close')) {
                session_write_close();
            }
        } catch (Exception $e) {}

        // Set a plain browser cookie as a reliable fallback (survives session expiry)
        $cookie_path   = '/';
        $cookie_expire = time() + 31536000; // 1 year
        setcookie('cheru_ln', $language_id, $cookie_expire, $cookie_path, '', false, false);
        $_COOKIE['cheru_ln'] = $language_id;

        // If customer is logged in, save preference to their profile
        $token = $this->get_bearer_token();
        $customer_id = 0;
        if ($token !== '') {
            $claims = $this->verify_token($token);
            if ($claims && isset($claims['sub']) && ($claims['role'] ?? '') === 'customer') {
                $customer_id = (int)$claims['sub'];
            }
        } else {
            $customer = $this->session->userdata('customer');
            $customer_id = (int)($customer['login_id'] ?? ($customer['customer_id'] ?? 0));
        }

        if ($customer_id > 0 && $this->db->field_exists('preferred_language_id', 'ec_customer')) {
            $this->db->where('customer_id', $customer_id)->update('ec_customer', ['preferred_language_id' => $language_id]);
        }

        return $this->ok(['language' => $row, 'language_id' => $language_id], 'Language switched successfully');
    }
}
