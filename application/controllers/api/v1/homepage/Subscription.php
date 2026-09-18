<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('API_Controller')) {
    require_once(APPPATH . 'core/API_Controller.php');
}

class Subscription extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
    }

    public function index()
    {
        $method = strtoupper((string)$this->input->method());
        if ($method !== 'POST') {
            return $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
        }

        $payload = array_merge($this->get_json_input(), $this->input->post(NULL, true) ?: []);
        $email = $payload['email'] ?? ($payload['subscribe_email'] ?? '');
        $email = is_string($email) ? trim($email) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('validation_error', ['Invalid email address.'], 422);
        }

        $ip_address = (string)$this->input->ip_address();

        try {
            $is_exist = $this->Query_model->get_data_obj('ec_subscribe', ['email' => $email], [], ['length' => 1]);
            if ($is_exist) {
                return $this->ok(['subscribed' => true], 'Already Subscribed.');
            }

            $insert_id = $this->Query_model->insert_data('ec_subscribe', [
                'email' => $email,
                'ip_address' => $ip_address,
                'status' => '1',
                'date_added' => date('Y-m-d H:i:s')
            ]);

            if ($insert_id) {
                return $this->ok(['subscribed' => true], 'Thank you for subscribe.');
            }

            return $this->fail('server_error', ['Subscription failed. Please try again.'], 500);
        } catch (Exception $e) {
            return $this->fail('server_error', ['Subscription failed. Please try again.'], 500);
        }
    }
}
