<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class API_Controller extends CI_Controller
{
    private const API_BUILD_MARKER = 'Cheru-2026-04-21-02';

    public function __construct()
    {
        // Suppress PHP warnings/notices from appearing in JSON responses.
        // Errors are still logged to server log but won't corrupt API output.
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
        @ini_set('display_errors', '0');

        parent::__construct();
        $this->output->set_content_type('application/json');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('X-API-Build: ' . self::API_BUILD_MARKER);
    }

    protected function get_authorization_header()
    {
        // 1) CodeIgniter header helper
        $header = $this->input->get_request_header('Authorization', true);
        if ($header) {
            return trim((string)$header);
        }

        // Some servers/proxies strip `Authorization` but allow custom headers
        $header = $this->input->get_request_header('X-Authorization', true);
        if ($header) {
            return trim((string)$header);
        }

        // 2) Common server variables
        $candidates = [
            'HTTP_AUTHORIZATION',
            'REDIRECT_HTTP_AUTHORIZATION',
            'Authorization',
            'X_AUTHORIZATION',
            'HTTP_X_AUTHORIZATION',
            'HTTP_X_AUTH_TOKEN',
        ];
        foreach ($candidates as $key) {
            $val = $this->input->server($key);
            if ($val) {
                return trim((string)$val);
            }
        }

        // 3) getallheaders()/apache_request_headers() fallback (some PHP SAPIs)
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = @getallheaders();
        } elseif (function_exists('apache_request_headers')) {
            $headers = @apache_request_headers();
        }
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                if (strcasecmp((string)$k, 'Authorization') === 0) {
                    return trim((string)$v);
                }
            }
        }

        // 4) Fallback to ?token= or body['token'] for easier testing
        $token = $this->input->get('token', true) ?: $this->input->post('token', true);
        if ($token) {
            return 'Bearer ' . trim((string)$token);
        }

        return '';
    }

    protected function get_bearer_token()
    {
        $header = $this->get_authorization_header();
        if ($header === '') {
            return '';
        }
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return '';
    }

    protected function respond($status = 1, $message = 'success', $data = null, $errors = [], $http_code = 200)
    {
        $payload = [
            'status' => (int)$status,
            'message' => (string)$message,
            'data' => $data === null ? (object)[] : $data,
            'errors' => is_array($errors) ? $errors : [$errors],
        ];

        if ((int)$status === 1) {
            $currency_obj = null;
            if (function_exists('current_currency') && function_exists('get_currency')) {
                $cc_id = current_currency();
                if ($cc_id) {
                    $currency_obj = get_currency($cc_id);
                }
            }
            if (!$currency_obj) {
                $currency_obj = (object)[
                    'currency_id' => '1',
                    'name' => 'USD',
                    'iso_code' => 'USD',
                    'symbol' => '$',
                    'rate' => '1.00000000',
                    'basic' => '1'
                ];
            }
            $payload['currency'] = $currency_obj;
        }

        if (isset($this->security)) {
            try {
                $payload['csrf_token_name'] = (string)$this->security->get_csrf_token_name();
                $hash = (string)$this->security->get_csrf_hash();
                if ($hash === '' && method_exists($this->security, 'get_csrf_cookie_name')) {
                    $cookie_name = (string)$this->security->get_csrf_cookie_name();
                    $cookie_val = (string)$this->input->cookie($cookie_name, true);
                    if ($cookie_val !== '') {
                        $hash = $cookie_val;
                    }
                }
                $payload['csrf_hash'] = $hash;
            } catch (Throwable $e) {
            } catch (Exception $e) {
            }
        }

        return $this->output
            ->set_status_header((int)$http_code)
            ->set_output(json_encode($payload));
    }

    protected function ok($data = null, $message = 'success')
    {
        return $this->respond(1, $message, $data, [], 200);
    }

    protected function fail($message = 'error', $errors = [], $http_code = 400)
    {
        return $this->respond(0, $message, (object)[], $errors, $http_code);
    }

    protected function get_json_input()
    {
        $raw = $this->input->raw_input_stream;
        if (!$raw) {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function require_post()
    {
        if (strtoupper((string)$this->input->method()) !== 'POST') {
            $this->fail('method_not_allowed', ['Only POST is allowed'], 405);
            return false;
        }
        return true;
    }

    protected function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    protected function sign_token(array $claims, $ttl_seconds)
    {
        $secret = (string)$this->config->item('encryption_key');
        if ($secret === '') {
            $secret = 'default_api_secret';
        }

        $now = time();
        $claims['iat'] = $now;
        $claims['exp'] = $now + (int)$ttl_seconds;

        $payload = json_encode($claims);
        $b64 = $this->base64url_encode($payload);
        $sig = hash_hmac('sha256', $b64, $secret, true);
        $b64sig = $this->base64url_encode($sig);
        return $b64 . '.' . $b64sig;
    }

    protected function verify_token($token)
    {
        $secret = (string)$this->config->item('encryption_key');
        if ($secret === '') {
            $secret = 'default_api_secret';
        }

        $parts = explode('.', (string)$token);
        if (count($parts) !== 2) {
            return null;
        }
        [$b64, $b64sig] = $parts;
        $expected = $this->base64url_encode(hash_hmac('sha256', $b64, $secret, true));
        if (!hash_equals($expected, $b64sig)) {
            return null;
        }
        $json = $this->base64url_decode($b64);
        $claims = json_decode($json, true);
        if (!is_array($claims)) {
            return null;
        }
        if (isset($claims['exp']) && time() > (int)$claims['exp']) {
            return null;
        }
        return $claims;
    }
}
