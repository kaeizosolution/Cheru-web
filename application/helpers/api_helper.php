<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('api_call')) {
    function api_call($method, $path, $payload = null, $headers = [], $timeout = 10)
    {
        $CI =& get_instance();

        $base = rtrim((string)base_url(), '/');
        $path = '/' . ltrim((string)$path, '/');
        $url = $base . $path;

        $method = strtoupper(trim((string)$method));

		// Fallback for servers without PHP cURL extension.
		if (!function_exists('curl_init')) {
			$out_headers = [];
			$out_headers[] = 'Accept: application/json';
			foreach ((array)$headers as $k => $v) {
				if (is_int($k)) {
					$out_headers[] = (string)$v;
				} else {
					$out_headers[] = $k . ': ' . $v;
				}
			}

			$body = null;
			$http_code = 0;
			try {
				$context_opts = [
					'http' => [
						'method' => $method,
						'header' => implode("\r\n", $out_headers),
						'timeout' => (int)$timeout,
						'ignore_errors' => true,
					],
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
					],
				];
				if ($method !== 'GET' && $payload !== null) {
					$context_opts['http']['content'] = json_encode($payload);
					$context_opts['http']['header'] .= "\r\nContent-Type: application/json";
				}
				$context = stream_context_create($context_opts);
				$body = @file_get_contents($url, false, $context);
				if (isset($http_response_header) && is_array($http_response_header)) {
					foreach ($http_response_header as $h) {
						if (preg_match('/^HTTP\/(?:1\.0|1\.1|2)\s+(\d{3})/i', $h, $m)) {
							$http_code = (int)$m[1];
							break;
						}
					}
				}
			} catch (Exception $e) {
				return [
					'ok' => false,
					'http_code' => 0,
					'error' => $e->getMessage(),
					'response' => null,
				];
			}

			$decoded = null;
			if (is_string($body) && $body !== '') {
				$decoded = json_decode($body, true);
			}

			if (!is_array($decoded)) {
				return [
					'ok' => false,
					'http_code' => (int)$http_code,
					'error' => 'Invalid API response',
					'response' => null,
				];
			}

			$status = isset($decoded['status']) ? (int)$decoded['status'] : 0;
			$ok = ($http_code >= 200 && $http_code < 300 && $status === 1);
			return [
				'ok' => $ok,
				'http_code' => (int)$http_code,
				'error' => null,
				'response' => $decoded,
			];
		}

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, (int)$timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, (int)$timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $parsed = parse_url($url);
        $host = isset($parsed['host']) ? $parsed['host'] : '';
        if ($host !== '' && $host !== '127.0.0.1' && $host !== 'localhost') {
            curl_setopt($curl, CURLOPT_RESOLVE, [
                $host . ":80:127.0.0.1",
                $host . ":443:127.0.0.1"
            ]);
        }

        $out_headers = [];
        $out_headers[] = 'Accept: application/json';

        foreach ((array)$headers as $k => $v) {
            if (is_int($k)) {
                $out_headers[] = (string)$v;
            } else {
                $out_headers[] = $k . ': ' . $v;
            }
        }

        if ($method === 'GET') {
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            if ($payload !== null) {
                if (is_array($payload) && isset($payload['__is_multipart'])) {
                    unset($payload['__is_multipart']);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
                } else {
                    $json = json_encode($payload);
                    $out_headers[] = 'Content-Type: application/json';
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
                }
            }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $out_headers);

        $body = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $http_code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($errno) {
            log_message('error', "API Loopback cURL Error ($errno): $error for URL: $url");
            return [
                'ok' => false,
                'http_code' => 0,
                'error' => $error,
                'response' => null,
            ];
        }

        $decoded = null;
        if (is_string($body) && $body !== '') {
            $decoded = json_decode($body, true);
        }

        // If API returned non-JSON (e.g. HTML error page), treat as failure.
        if (!is_array($decoded)) {
            log_message('error', "API Loopback HTTP Error ($http_code) - Invalid JSON response: " . substr(strip_tags((string)$body), 0, 300) . " for URL: $url");
            return [
                'ok' => false,
                'http_code' => $http_code,
                'error' => 'Invalid API response',
                'response' => null,
            ];
        }

        $status = isset($decoded['status']) ? (int)$decoded['status'] : 0;
        $ok = ($http_code >= 200 && $http_code < 300 && $status === 1);

        return [
            'ok' => $ok,
            'http_code' => $http_code,
            'error' => null,
            'response' => $decoded,
        ];
    }
}

if (!function_exists('call_api')) {
    function call_api($method, $endpoint, $payload = null, $headers = [], $timeout = 10)
    {
        $endpoint = ltrim((string)$endpoint, '/');
        $path = '/api/v1/' . $endpoint;
        return api_call($method, $path, $payload, $headers, $timeout);
    }
}

if (!function_exists('sync_vendor_product_status')) {
    function sync_vendor_product_status($vendor_id, $vendor_status)
    {
        $CI =& get_instance();
        $prod_status = ((int)$vendor_status === 1) ? '1' : '0';

        // 1. Update new products table if it exists
        if ($CI->db->table_exists('products')) {
            $CI->db->where('vendor_id', (int)$vendor_id);
            $CI->db->update('products', ['status' => $prod_status]);
        }

        // 2. Update legacy ec_product table if it exists
        if ($CI->db->table_exists('ec_product')) {
            $CI->db->where('login_id', (int)$vendor_id);
            $CI->db->update('ec_product', ['status' => $prod_status]);
        }
    }
}
