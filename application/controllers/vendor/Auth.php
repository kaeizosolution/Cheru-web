<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {

	function __construct()
	{
		parent::__construct();
	    $this->load->model('Query_model');
	    $this->load->helper('api');
        $this->TYPE = 'vendor';
	}

	private function _vendor_documents_table()
	{
		$table = 'ec_vendor_verification';
		return $this->db->table_exists($table) ? $table : '';
	}

	private function _vendor_documents_payload($table, $vendor_id, $front_file, $back_file)
	{
		$now = date('Y-m-d H:i:s');
		$payload = array();
		if ($this->db->field_exists('vendor_id', $table)) {
			$payload['vendor_id'] = $vendor_id;
		} elseif ($this->db->field_exists('login_id', $table)) {
			$payload['login_id'] = $vendor_id;
		} elseif ($this->db->field_exists('admin_id', $table)) {
			$payload['admin_id'] = $vendor_id;
		} else {
			return array();
		}

		if ($this->db->field_exists('document_front', $table)) {
			$payload['document_front'] = $front_file;
		} elseif ($this->db->field_exists('doc_front', $table)) {
			$payload['doc_front'] = $front_file;
		}

		if ($this->db->field_exists('document_back', $table)) {
			$payload['document_back'] = $back_file;
		} elseif ($this->db->field_exists('doc_back', $table)) {
			$payload['doc_back'] = $back_file;
		}

		if ($this->db->field_exists('status', $table)) {
			$payload['status'] = 'pending';
		}
		if ($this->db->field_exists('created_at', $table)) {
			$payload['created_at'] = $now;
		}
		if ($this->db->field_exists('updated_at', $table)) {
			$payload['updated_at'] = $now;
		}
		return $payload;
	}
	
	public function index()
	{
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $this->load->template("$this->TYPE/index",$data);

		#if(!$this->logged_in()) redirect("$this->TYPE/auth/login");
		// Redirect to your logged in landing page here
		#redirect("$this->TYPE/dashboard");
	}
	
	/**
	 * Login page
	*/
	/**
     * Login page
    */
   public function login()
{
    is_auth(); 
    $data = array();
    $data['TYPE'] = $this->TYPE;
    $data['csrf'] = csrf_token();

    if ($this->input->is_ajax_request()) {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        if (!$email || !$password) {
            echo json_encode([
                'status' => '0',
                'message' => 'Email and Password are required.'
            ]);
            return;
        }

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'status' => '0',
                'message' => 'email format is wrong'
            ]);
            return;
        }

        // Call the API driven login
        $api = call_api('POST', 'vendor/login', [
            'email' => $email,
            'password' => $password
        ]);

        if ($api['response'] !== null) {
            $resp = $api['response'];
            if ((int)($resp['status'] ?? 0) === 1) {
                $resp_data = $resp['data'] ?? [];
                $vendor_info = $resp_data['vendor'] ?? [];
                $vendor_id = (int)($vendor_info['vendor_id'] ?? 0);

                // Set session data
                $session_data = array(
                    'vendor_id'   => $vendor_id,
                    'login_id'    => $vendor_id,
                    'fname'       => $vendor_info['name'] ?? '',
                    'email'       => $vendor_info['email'] ?? '',
                    'logged_in'   => TRUE,
                    'access_token' => $resp_data['access_token'] ?? '',
                    'refresh_token' => $resp_data['refresh_token'] ?? '',
                );
                
                $this->session->set_userdata($this->TYPE, $session_data);
                $this->session->set_userdata('type', $this->TYPE);
                $this->session->set_userdata('vendor_approved_seen', '1');

                echo json_encode([
                    'status' => '1',
                    'message' => 'Login successful! Redirecting...',
                    'redirect_url' => '/' . $this->TYPE . '/dashboard'
                ]);
            } else {
                // Read exact error message from API
                $err = '';
                if (!empty($resp['errors']) && is_array($resp['errors'])) {
                    $err = (string)$resp['errors'][0];
                }
                if ($err === '') {
                    $err = (string)($resp['message'] ?? 'Invalid Email or Password.');
                }
                echo json_encode([
                    'status' => '0',
                    'message' => $err
                ]);
            }
            return;
        } else {
            // Local DB fallback if API is unreachable
            $condition = array(
                'email'    => $email,
                'password' => md5($password)
            );
            $user = $this->Query_model->get_data_obj('ec_vendor', $condition);
            if ($user) {
                if ($this->db->field_exists('is_enabled', 'ec_vendor') && isset($user->is_enabled) && (string)$user->is_enabled === '0') {
                    echo json_encode(['status' => '0', 'message' => 'Your account is disabled. Please contact admin.']);
                    return;
                }
                $session_data = array(
                    'vendor_id'   => $user->vendor_id,
                    'login_id'    => $user->vendor_id,
                    'fname'       => $user->name,
                    'email'       => $user->email,
                    'logged_in'   => TRUE
                );
                $this->session->set_userdata($this->TYPE, $session_data);
                $this->session->set_userdata('type', $this->TYPE);
                $this->session->set_userdata('vendor_approved_seen', '1');

                echo json_encode([
                    'status' => '1',
                    'message' => 'Login successful! Redirecting...',
                    'redirect_url' => '/' . $this->TYPE . '/dashboard'
                ]);
            } else {
                echo json_encode(['status' => '0', 'message' => 'email password does not match']);
            }
            return;
        }
    }

    // Double assurance: if they visited fresh or clicked logout, clear any stale "Invalid Email or Password" message
    if ($this->session->flashdata('message') === 'Invalid Email or Password.') {
        $this->session->set_flashdata('message', '');
    }

    $this->load->template("$this->TYPE/auth/login", $data);
}
  /**
	 * Registration page
	*/
	public function signup()
{
    is_auth();  
    $data = array();
    $data['TYPE'] = $this->TYPE;
    $data['csrf'] = csrf_token();   
    $data['error'] = false;
    $data['message']['error'] = ''; 

    $this->load->library('form_validation');       
    $this->form_validation->set_rules('name', 'Full Name', 'required|min_length[2]|max_length[50]');
    $this->form_validation->set_rules('mobile','Mobile No', 'trim|required|max_length[10]|min_length[10]'); 
    $this->form_validation->set_rules('email','Email', 'trim|required|valid_email'); 
    $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[3]|max_length[15]');
    $this->form_validation->set_rules('address', 'Address', 'required|min_length[5]');
    $this->form_validation->set_rules('store_name', 'Store Name', 'required|min_length[2]');
    $this->form_validation->set_rules('country', 'Country', 'trim|required');

    if ($this->form_validation->run()) {
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $mobile = $this->input->post('mobile');
        $password = $this->input->post('password');
        $store_name = $this->input->post('store_name');
        $address = trim($this->input->post('location') . ' ' . $this->input->post('address'));
        $city = $this->input->post('city');
        $country = $this->input->post('country');
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');

        $doc_front_name = '';
        $doc_back_name = '';

        $upload_dir = FCPATH . 'uploads/vendor_documents';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0777, true);
        }

        $config = array(
            'upload_path' => $upload_dir,
            'allowed_types' => 'gif|jpg|png|jpeg|pdf',
            'max_size' => '20240000',
        );
        $this->load->library('upload', $config);

        if (isset($_FILES['document_front']['name']) && $_FILES['document_front']['name'] != '') {
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('document_front')) {
                echo json_encode([
                    'status' => '0',
                    'message' => strip_tags($this->upload->display_errors())
                ]);
                return;
            }
            $data_file = $this->upload->data();
            $doc_front_name = isset($data_file['file_name']) ? $data_file['file_name'] : '';
        }

        if (isset($_FILES['document_back']['name']) && $_FILES['document_back']['name'] != '') {
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('document_back')) {
                echo json_encode([
                    'status' => '0',
                    'message' => strip_tags($this->upload->display_errors())
                ]);
                return;
            }
            $data_file = $this->upload->data();
            $doc_back_name = isset($data_file['file_name']) ? $data_file['file_name'] : '';
        }

        // Call the API driven register
        $api = call_api('POST', 'vendor/register', [
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'password' => $password,
            'store_name' => $store_name,
            'address' => $address,
            'city' => $city,
            'country' => $country,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'document_front' => $doc_front_name,
            'document_back' => $doc_back_name
        ]);

        if ($api['response'] !== null) {
            $resp = $api['response'];
            if ((int)($resp['status'] ?? 0) === 1) {
                $resp_data = $resp['data'] ?? [];
                $vendor_info = $resp_data['vendor'] ?? [];
                $vendor_id = (int)($vendor_info['vendor_id'] ?? 0);

                // Set session data
                $session_data = array(
                    'vendor_id'   => $vendor_id,
                    'login_id'    => $vendor_id,
                    'fname'       => $vendor_info['name'] ?? '',
                    'email'       => $vendor_info['email'] ?? '',
                    'logged_in'   => TRUE,
                    'access_token' => $resp_data['access_token'] ?? '',
                    'refresh_token' => $resp_data['refresh_token'] ?? '',
                );
                $this->session->set_userdata($this->TYPE, $session_data);
                $this->session->set_userdata('type', $this->TYPE);

                echo json_encode([
                    'status' => '1', 
                    'message' => 'Registration successful! Your account is under verification.',
                    'redirect_url' => '/' . $this->TYPE . '/profile'
                ]);
            } else {
                $err = '';
                if (!empty($resp['errors']) && is_array($resp['errors'])) {
                    $err = (string)$resp['errors'][0];
                }
                if ($err === '') {
                    $err = (string)($resp['message'] ?? 'Registration failed.');
                }
                echo json_encode([
                    'status' => '0', 
                    'message' => $err
                ]);
            }
            return;
        }

        // Local DB fallback if API is unreachable
        $vendor_uid = substr(uniqid(), 0, 13); 
        $insert_data = array(
            'vendor_uid'    => $vendor_uid,
            'name'          => $name,
            'email'         => $email,
            'password'      => md5($password),
            'mobile'        => $mobile,
            'store_name'    => $store_name,
            'address'       => $address,
            'city'          => $city,
            'country'       => $country,
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'status'        => '0'
        );          
     
        $customer_id = $this->Query_model->insert_data('ec_vendor', $insert_data);

        if ($customer_id) { 
            $docs_table = $this->_vendor_documents_table();
            if ($docs_table) {
                $docs_payload = $this->_vendor_documents_payload($docs_table, $customer_id, $doc_front_name, $doc_back_name);
                if ($docs_payload) {
                    $this->Query_model->insert_data($docs_table, $docs_payload);
                }
            }

            $session_data = array(
                'vendor_id'   => $customer_id,
                'login_id'    => $customer_id,
                'fname'       => $name,
                'email'       => $email,
                'logged_in'   => TRUE
            );
            $this->session->set_userdata($this->TYPE, $session_data);
            $this->session->set_userdata('type', $this->TYPE);

            echo json_encode([
                'status' => '1', 
                'message' => 'Registration successful! Your account is under verification.',
                'redirect_url' => '/' . $this->TYPE . '/profile'
            ]);
        } else {
            echo json_encode([
                'status' => '0', 
                'message' => 'Database error. Please try again.'
            ]);
        }
        return;
    } else {
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'status' => '0', 
                'message' => strip_tags(validation_errors())
            ]);
            return;
        }
        $this->load->template("$this->TYPE/auth/signup", $data);     
    }
}
	
	
	/**
	 * Logout page
	 */
	public function logout()
	{
		// Get session data BEFORE clearing it
		$vendor_data = $this->session->userdata($this->TYPE);

		// Revoke the refresh token directly in DB (avoids session-lock deadlock from loopback cURL)
		if (!empty($vendor_data['refresh_token'])) {
			$token     = $vendor_data['refresh_token'];
			$token_hash = hash('sha256', $token);
			if ($this->db->table_exists('ec_api_tokens')) {
				$this->db->where('token_hash', $token_hash)
				         ->where('user_type', 'vendor')
				         ->update('ec_api_tokens', ['revoked' => 1]);
			}
		}

		// Clear the web session
		$this->session->unset_userdata($this->TYPE);
		$this->session->unset_userdata('type');
		$this->session->set_flashdata('message', '');
		redirect("$this->TYPE/auth/login");
	}
	
	/**
	 * Forgot password page
	 */
	public function forgot()
    {
        if($this->logged_in()) redirect("$this->TYPE/dashboard");

        $data['error'] = false;
        $data['message']['error'] = '';
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();

        if ($this->input->is_ajax_request()) {
            $email = $this->input->post('email');
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => '0', 'message' => 'Please enter a valid email address.']);
                return;
            }

            $api = call_api('POST', 'vendor/auth/forgot-password', ['email' => $email]);
            if ($api['response'] !== null) {
                $resp = $api['response'];
                if ((int)($resp['status'] ?? 0) === 1) {
                    echo json_encode(['status' => '1', 'message' => $resp['message'] ?? 'Reset link sent to your email.']);
                } else {
                    $err = '';
                    if (!empty($resp['errors']) && is_array($resp['errors'])) $err = (string)$resp['errors'][0];
                    if ($err === '') $err = (string)($resp['message'] ?? 'Failed to send reset email. Please try again.');
                    echo json_encode(['status' => '0', 'message' => $err]);
                }
                return;
            }

            // Local fallback
            $this->load->library('form_validation');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $args = array('email' => $email);
            $vendor = $this->Query_model->get_data_obj('ec_vendor', $args);
            if ($vendor) {
                $slug     = md5(uniqid($email, true));
                $base_url = rtrim((string)$this->config->item('base_url'), '/');
                $site_url = $base_url . '/' . $this->TYPE . '/auth/reset/' . $slug;
                $this->db->where('admin_id', (int)$vendor->vendor_id)->delete('ec_admin_password_reset');
                $this->Query_model->insert_data('ec_admin_password_reset', [
                    'admin_id'   => (int)$vendor->vendor_id,
                    'reset_key'  => $slug,
                    'ip_address' => $this->input->ip_address(),
                    'status'     => 'active',
                ]);
                $this->load->library('mailer');
                $this->mailer->smtp([
                    'SUBJECT' => 'Reset your Password',
                    'EMAIL'   => $vendor->email,
                    'CONTENT' => $this->forgot_mailer_content($site_url, $vendor->name)
                ]);
            }
            echo json_encode(['status' => '1', 'message' => 'If that email is registered, a reset link has been sent.']);
            return;
        }

        $this->load->template("$this->TYPE/auth/forgot_password", $data);
    }
	
	  public function register_mailer_content($fanme)
    {
        $date = date('j M Y');
        $html =<<<HTML
            <tr>
              <td class="h5-center" style="color:#000; font-family:Arial; font-size:16px; line-height:22px; text-align:left; padding-bottom:5px; font-weight:bold; text-transform: capitalize;"><multiline>Hi, $fname</multiline></td>
            </tr>
            <tr>
              <td class="h2-center"style="color:#000000; font-family:'Playfair Display', Times, 'Times New Roman', serif; font-size:32px; line-height:36px; text-align:left; padding-bottom:20px;"><multiline>Welcome to Imosys</multiline></td>
            </tr>
            <tr>
              <td class="text-center"style="color:#5d5c5c; font-family:'Raleway', Arial,sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:40px;"><multiline>Thank you, for Register with us</multiline></td>
            </tr>
HTML;
        return $html;
    }

    public function forgot_mailer_content($site_url, $fname)
    {
        $date = date('j M Y');
        $html =<<<HTML
            <tr>
              <td class="h5-center" style="color:#000; font-family:Arial; font-size:16px; line-height:22px; text-align:left; padding-bottom:5px; font-weight:bold; text-transform: capitalize;"><multiline>Hi, $fname</multiline></td>
            </tr>
            <tr>
              <td class="h2-center"style="color:#000000; font-family:'Playfair Display', Times, 'Times New Roman', serif; font-size:32px; line-height:36px; text-align:left; padding-bottom:20px;"><multiline>Welcome to Imosys</multiline></td>
            </tr>
            <tr>
              <td class="text-center"style="color:#5d5c5c; font-family:'Raleway', Arial,sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:40px;"><multiline>To reset your password please click the link below and follow the instructions:</multiline></td>
            </tr>
            <tr>
              <td align="center"><table border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 30px;">
                  <tr>
                    <td class="text-button-orange"style="background: #e85711;color: #ffffff;font-family: Arial;font-size: 14px;line-height: 18px;text-align: center;padding: 13px 30px;border-radius: 20px;font-weight: 700;"><multiline><a href="$site_url" target="_blank" class="link-white"style="color:#ffffff; text-decoration:none;"><span class="link-white"style="color:#ffffff; text-decoration:none; text-transform: uppercase;">Reset Password URL</span></a></multiline></td>
                  </tr>
                </table></td>
            </tr>
            <tr>
              <td class="text-center"style="color:#5d5c5c; font-family:'Raleway', Arial,sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:40px;"><multiline>If you did not request to reset your password then please just ignore this email and no changes will occur.</multiline></td>
            </tr>
            <tr>
              <td class="text-center"style="color:#5d5c5c; font-family:'Raleway', Arial,sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:40px;"><multiline>Note: This reset code will expire after $date .</multiline></td>
            </tr>
HTML;
        return $html;
    }
	
	/**
	 * Reset password page
	 */
	public function reset($sid = NULL)
	{
        $this->session->set_flashdata('message','');
		// Redirect to your logged in landing page here
		if($this->logged_in()) redirect("$this->TYPE/dashboard");
       
		$this->load->library('form_validation');
		$data['error'] = false;
		$data['message']['error'] = '';

        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[50]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');
		if($this->form_validation->run()){
            $sid = $this->input->post('sid');
            if(isset($sid) && $sid != '' && $this->is_valid_md5($sid)){
				$vendor_reset = $this->Query_model->get_data_obj('ec_admin_password_reset',array('reset_key' => $sid, 'status' => 'active'));
				if($vendor_reset){
					$vendor = $this->Query_model->get_data_obj('ec_vendor',array('vendor_id' => $vendor_reset->admin_id, 'status' => '1'));
					if($vendor){
						$data =  array(
							'password'      => md5($confirm_password),
						);
						$this->Query_model->update_data('ec_vendor',$data,array('vendor_id' => $vendor_reset->admin_id));

						$data =  array(
							'status'      => 'reset',
						);
						$this->Query_model->update_data('ec_admin_password_reset',$data,array('admin_id' => $vendor_reset->admin_id));

						$details = array(
								'email'         => $vendor->email,
								'fname'         => $vendor->name,
								'vendor_id'     => $vendor->vendor_id,
								'login_id'      => $vendor->vendor_id,
								'logged_in'     => TRUE
								);
						$this->session->set_userdata($this->TYPE, $details);

						redirect("$this->TYPE/dashboard");
					}else{
						$data['message']['error']='Oops, that link has expired. Please enter your email below to start again.';
					}
				}else{
					$data['message']['error']='Invalid Parameter!!.';
				}
            }else{
                $this->session->set_flashdata('message','Invalid Parameter!');
            }
        }
	
        $data['navigation'] = 0;
        $data['TYPE'] = $this->TYPE;
        $data['csrf'] = csrf_token();
        $data['sid']  = $sid;
		$this->load->template("$this->TYPE/auth/reset_password", $data);
	}

    public function logged_in()
    {
        if($this->session->userdata($this->TYPE) && isset($this->session->userdata($this->TYPE)['logged_in'])){
            return true;
        }else{
            return false;
        }
    }

    public function set_session($details) 
    {
        $this->session->set_userdata($details);
    }

    public function is_valid_md5($md5 ='')
    {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }

    /**
     * POST /api/vendor/forgot
     *
     * API-style endpoint for vendor forgot-password.
     * Returns JSON directly — no view loaded.
     * Routed via: $route['api/vendor/forgot'] = 'vendor/Auth/forgot_json'
     */
    public function forgot_json()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (strtoupper($this->input->method()) !== 'POST') {
            echo json_encode(['status' => 0, 'message' => 'Method not allowed', 'errors' => ['Only POST is accepted']]);
            return;
        }

        $email = trim(strtolower((string)$this->input->post('email', true)));

        if ($email === '') {
            echo json_encode(['status' => 0, 'message' => 'validation_error', 'errors' => ['email is required']]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 0, 'message' => 'validation_error', 'errors' => ['Please enter a valid email address']]);
            return;
        }

        // Look up vendor — return success regardless to prevent email enumeration
        $vendor = $this->Query_model->get_data_obj('ec_vendor', ['email' => $email]);
        if (!$vendor) {
            echo json_encode(['status' => 1, 'message' => 'If that email is registered, a reset link has been sent.', 'data' => (object)[], 'errors' => []]);
            return;
        }

        $slug     = md5(uniqid($email, true));
        $base_url = rtrim((string)base_url(), '/');
        $site_url = $base_url . '/vendor/auth/reset/' . $slug;

        // Clean old keys then insert new one
        if ($this->db->table_exists('ec_admin_password_reset')) {
            $this->db->where('admin_id', (int)$vendor->vendor_id)->delete('ec_admin_password_reset');
        }
        $this->Query_model->insert_data('ec_admin_password_reset', [
            'admin_id'   => (int)$vendor->vendor_id,
            'reset_key'  => $slug,
            'ip_address' => $this->input->ip_address(),
            'status'     => 'active',
        ]);

        // Send email — SMTP warnings suppressed, failures logged not echoed
        $this->load->library('mailer');
        $vendor_name  = htmlspecialchars($vendor->name ?? 'Vendor', ENT_QUOTES);
        $html_content = '
            <tr><td style="font-family:Arial;font-size:16px;padding-bottom:5px;font-weight:bold;">Hi, ' . $vendor_name . '</td></tr>
            <tr><td style="font-family:Arial;font-size:14px;padding-bottom:20px;color:#555;">
                You requested a password reset. Click the button below to reset your password:
            </td></tr>
            <tr><td align="center" style="padding-bottom:20px;">
                <a href="' . $site_url . '" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;display:inline-block;">Reset Password</a>
            </td></tr>
            <tr><td style="font-family:Arial;font-size:13px;color:#888;">
                If you did not request this, please ignore this email.
            </td></tr>';

        $this->mailer->smtp([
            'SUBJECT' => 'Reset your Password',
            'EMAIL'   => $vendor->email,
            'CONTENT' => $html_content,
        ]);

        echo json_encode(['status' => 1, 'message' => 'If that email is registered, a reset link has been sent.', 'data' => (object)[], 'errors' => []]);
    }
}
