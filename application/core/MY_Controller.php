<?php
class MY_Controller extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('breadcrumbs');
        $this->load->library('session');
        $this->load->helper('url');
        $this->controller = $this->router->fetch_class();
        $this->model = $this->router->fetch_method();
        // Prioritize 'vendor' before 'admin' in boundary fallback checks
        $user_type_array = array('vendor', 'admin', 'customer', 'driver');

        $PATH_INFO = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');
        if ($PATH_INFO === '') {
            // Fallback to REQUEST_URI when PATH_INFO is not set (common with mod_rewrite)
            $PATH_INFO = !empty($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '';
        }

        // Use the first URL segment to determine user type.
        $_mc_segs  = explode('/', trim($PATH_INFO, '/'));

        // Strip index.php if it's the first segment (common when mod_rewrite is partially active)
        if (isset($_mc_segs[0]) && strtolower($_mc_segs[0]) === 'index.php') {
            array_shift($_mc_segs);
        }

        $_mc_first = strtolower($_mc_segs[0] ?? '');

        if ($_mc_first === 'api') {
            // API route: /api/v1/{type}/... — scan deeper segments
            foreach ($_mc_segs as $_mc_seg) {
                $_mc_seg_lower = strtolower((string)$_mc_seg);
                if (in_array($_mc_seg_lower, $user_type_array)) {
                    $this->session->set_userdata('type', $_mc_seg_lower);
                    break;
                }
            }
        } elseif (in_array($_mc_first, $user_type_array)) {
            $this->session->set_userdata('type', $_mc_first);
        } else {
            // Fallback prioritized boundary scan
            foreach ($user_type_array as $_fb_val) {
                if (preg_match('/\b' . preg_quote($_fb_val, '/') . '\b/i', $PATH_INFO)) {
                    $this->session->set_userdata('type', $_fb_val);
                    break;
                }
            }
        }
        $dir = $this->router->fetch_directory();
        if ($dir === 'vendor/') {
            $this->session->set_userdata('type', 'vendor');
            $_SESSION['type'] = 'vendor';
        } elseif ($dir === 'admin/') {
            $this->session->set_userdata('type', 'admin');
            $_SESSION['type'] = 'admin';
        } elseif ($dir === 'customer/') {
            $this->session->set_userdata('type', 'customer');
            $_SESSION['type'] = 'customer';
        }

        unset($_mc_segs, $_mc_first, $_mc_seg, $_mc_seg_lower);
        #print_r($_SESSION);
        $_SESSION['type'] = isset($_SESSION['type']) ? $_SESSION['type'] : 'customer';
        $_SESSION['cur']  = isset($_SESSION['cur']) ? $_SESSION['cur'] : '1';

        // ── Currency DB-sync ──────────────────────────────────────────────────────
        // Problem: each browser has its own independent PHP session. When a customer
        // changes currency in Browser A, only Browser A's $_SESSION['cur'] is updated.
        // Browser B keeps its stale session value and shows wrong prices.
        //
        // Fix: on every web request, if the user is logged in, read their
        // preferred_currency_id directly from the DB and overwrite $_SESSION['cur'].
        // This ensures ALL browsers for the same account always show the correct currency.
        //
        // Performance: 1 lightweight SELECT by primary key per request (indexed).
        //              The static $__cur_synced flag guarantees it runs exactly once
        //              per PHP process even if __construct() is somehow called twice.
        // Safety: wrapped in try/catch — a DB error must never crash a page load.
        static $__cur_synced = false;
        if (!$__cur_synced && isset($this->db)) {
            $__cur_synced = true;
            try {
                $__sess_type = isset($_SESSION['type']) ? $_SESSION['type'] : '';

                // ── Customer: sync from ec_customer.preferred_currency_id ──────────
                if ($__sess_type === 'customer') {
                    $__cust_data = $this->session->userdata('customer');
                    $__cust_id   = 0;
                    if (is_array($__cust_data)) {
                        $__cust_id = (int)($__cust_data['login_id'] ?? ($__cust_data['customer_id'] ?? 0));
                    }
                    if ($__cust_id > 0) {
                        $__cust_row = $this->db
                            ->select('preferred_currency_id')
                            ->from('ec_customer')
                            ->where('customer_id', $__cust_id)
                            ->limit(1)
                            ->get()->row();
                        $__db_cur = (int)($__cust_row->preferred_currency_id ?? 0);
                        if ($__db_cur > 0 && $__db_cur !== (int)$_SESSION['cur']) {
                            $_SESSION['cur'] = $__db_cur;
                            $this->session->set_userdata('cur', $__db_cur);
                        }
                        unset($__cust_row, $__db_cur);
                    }
                    unset($__cust_data, $__cust_id);

                // ── Vendor: sync from ec_vendor.preferred_currency_id ─────────────
                } elseif ($__sess_type === 'vendor') {
                    $__vend_data = $this->session->userdata('vendor');
                    $__vend_id   = 0;
                    if (is_array($__vend_data) && !empty($__vend_data['logged_in'])) {
                        $__vend_id = (int)($__vend_data['vendor_id'] ?? ($__vend_data['login_id'] ?? 0));
                    }
                    if ($__vend_id > 0) {
                        $__vend_row = $this->db
                            ->select('preferred_currency_id')
                            ->from('ec_vendor')
                            ->where('vendor_id', $__vend_id)
                            ->limit(1)
                            ->get()->row();
                        $__db_cur = (int)($__vend_row->preferred_currency_id ?? 0);
                        if ($__db_cur > 0 && $__db_cur !== (int)$_SESSION['cur']) {
                            $_SESSION['cur'] = $__db_cur;
                            $this->session->set_userdata('cur', $__db_cur);
                        }
                        unset($__vend_row, $__db_cur);
                    }
                    unset($__vend_data, $__vend_id);
                }

                unset($__sess_type);
            } catch (Exception $__cur_ex) {
                // Silently skip — a currency sync failure must never break a page.
                // This also gracefully handles the case where preferred_currency_id
                // column does not yet exist in ec_customer / ec_vendor.
                unset($__cur_ex);
            }
        }
        // ─────────────────────────────────────────────────────────────────────────

        // Restore language from cookie if session is fresh (prevents reset to English on session expiry)
        if (empty($_SESSION['ln']) || $_SESSION['ln'] == '3') {
            $ci_ln = $this->session->userdata('ln');
            if ($ci_ln && $ci_ln != '3') {
                // CI session has a non-English language — trust it
                $_SESSION['ln'] = $ci_ln;
            } elseif (isset($_COOKIE['cheru_ln']) && (int)$_COOKIE['cheru_ln'] > 0) {
                // Cookie has the user's preferred language — restore it
                $_SESSION['ln'] = (int)$_COOKIE['cheru_ln'];
                $this->session->set_userdata('ln', (int)$_COOKIE['cheru_ln']);
            } else {
                $_SESSION['ln'] = isset($_SESSION['ln']) ? $_SESSION['ln'] : '3';
            }
        }
        // Sync CI session with $_SESSION['ln'] so current_language() is always correct
        if ($this->session->userdata('ln') != $_SESSION['ln']) {
            $this->session->set_userdata('ln', $_SESSION['ln']);
        }

        // Auto-initialize missing languages in database (ensures dropdown shows all 8 languages)
        try {
            if (isset($this->db)) {
                $languages = [
                    ['language_id' => 3, 'name' => 'English', 'iso_code' => 'en', 'status' => '1', 'basic' => 0],
                    ['language_id' => 12, 'name' => 'French', 'iso_code' => 'fr', 'status' => '1', 'basic' => 0],
                    ['language_id' => 13, 'name' => 'Spanish', 'iso_code' => 'es', 'status' => '1', 'basic' => 0],
                    ['language_id' => 14, 'name' => 'Canadian', 'iso_code' => 'ca', 'status' => '1', 'basic' => 0],
                    ['language_id' => 15, 'name' => 'Chinese', 'iso_code' => 'zh', 'status' => '1', 'basic' => 0],
                    ['language_id' => 16, 'name' => 'German', 'iso_code' => 'de', 'status' => '1', 'basic' => 0],
                    ['language_id' => 17, 'name' => 'Indonesian', 'iso_code' => 'id', 'status' => '1', 'basic' => 0],
                    ['language_id' => 18, 'name' => 'Japanese', 'iso_code' => 'ja', 'status' => '1', 'basic' => 0],
                    ['language_id' => 19, 'name' => 'Korean', 'iso_code' => 'ko', 'status' => '1', 'basic' => 0],
                ];
                foreach ($languages as $lang) {
                    $check_query = $this->db->get_where('ec_language', ['language_id' => $lang['language_id']]);
                    if (!$check_query) {
                        $err = $this->db->error();
                        @file_put_contents(FCPATH . 'db_error_log.txt', "Get lang " . $lang['language_id'] . " failed: " . ($err['message'] ?? 'Unknown error') . "\n", FILE_APPEND);
                        continue;
                    }
                    $check = $check_query->row();
                    if (!$check) {
                        $ins = $this->db->insert('ec_language', $lang);
                        if (!$ins) {
                            $err = $this->db->error();
                            @file_put_contents(FCPATH . 'db_error_log.txt', "Insert lang " . $lang['language_id'] . " failed: " . ($err['message'] ?? 'Unknown error') . "\n", FILE_APPEND);
                        } else {
                            @file_put_contents(FCPATH . 'db_error_log.txt', "Inserted lang " . $lang['language_id'] . " successfully\n", FILE_APPEND);
                        }
                    } else {
                        $upd = $this->db->where('language_id', $lang['language_id'])->update('ec_language', [
                            'name' => $lang['name'],
                            'iso_code' => $lang['iso_code'],
                            'status' => '1'
                        ]);
                        if (!$upd) {
                            $err = $this->db->error();
                            @file_put_contents(FCPATH . 'db_error_log.txt', "Update lang " . $lang['language_id'] . " failed: " . ($err['message'] ?? 'Unknown error') . "\n", FILE_APPEND);
                        } else {
                            @file_put_contents(FCPATH . 'db_error_log.txt', "Updated lang " . $lang['language_id'] . " successfully\n", FILE_APPEND);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            @file_put_contents(FCPATH . 'db_error_log.txt', "Exception caught: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        if(!preg_match('/login|logout|register|forgot|-ajax-|_ajax_|reset\//', current_url())){
          $current_url = current_url();
          $this->session->set_userdata('previous_url', $current_url);
        }

        $non_logged_in = array("api","auth","Auth","login","forgot_password","welcome","index","product","product_list","page","posts","basket","taxonomy","Taxonomy","cart","checkout","thanks","contact","subscribe","coupon", "item_list", "item", "category_products", "Category_products", "LanguageSwitcher", "CurrencySwitcher", "languageswitcher", "currencyswitcher");
        //echo $this->controller;
        if (in_array($this->controller, $non_logged_in) == false) 
        {
          $this->isUserLoggedIn(); 
        }

        // ── DB: Auto-migrate join table for multiple roles ───────────────────────
        if (isset($this->db)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS ec_admin_to_roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                role_id INT NOT NULL,
                UNIQUE KEY admin_role_idx (admin_id, role_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Migrate legacy single role_id values to the join table
            $this->db->query("INSERT IGNORE INTO ec_admin_to_roles (admin_id, role_id) 
                SELECT admin_id, role_id FROM ec_admin WHERE role_id > 0;");

            // Auto-migrate: rename vendor_img → admin_img in ec_admin if old column still exists
            $fields = $this->db->list_fields('ec_admin');
            if (in_array('vendor_img', $fields) && !in_array('admin_img', $fields)) {
                $this->db->query("ALTER TABLE ec_admin CHANGE vendor_img admin_img VARCHAR(255) NULL DEFAULT NULL;");
            }
            // Add admin_img column if neither vendor_img nor admin_img exists
            if (!in_array('vendor_img', $fields) && !in_array('admin_img', $fields)) {
                $this->db->query("ALTER TABLE ec_admin ADD COLUMN admin_img VARCHAR(255) NULL DEFAULT NULL;");
            }

            // Auto-migrate: ensure ec_vendor has banner_img column
            if ($this->db->table_exists('ec_vendor')) {
                $vendor_fields = $this->db->list_fields('ec_vendor');
                if (!in_array('banner_img', $vendor_fields)) {
                    $this->db->query("ALTER TABLE ec_vendor ADD COLUMN banner_img VARCHAR(500) NULL DEFAULT NULL;");
                }
            }
        }

        // ── Admin: sync permissions dynamically from DB (RBAC with multiple roles)
        // Super admin can change a sub-admin's roles while they are active.
        // Doing this sync on every request ensures the sub-admin's permissions
        // take effect instantly without requiring them to log out and log in again.
        if (isset($this->db) && isset($this->session)) {
            $admin_sess = $this->session->userdata('admin');
            if (is_array($admin_sess) && !empty($admin_sess['logged_in']) && (empty($admin_sess['super_admin']) || $admin_sess['super_admin'] != 1)) {
                $admin_id = (int)($admin_sess['login_id'] ?? 0);
                if ($admin_id > 0) {
                    // Fetch all roles currently assigned to this admin
                    $role_rows = $this->db->select('role_id')->from('ec_admin_to_roles')->where('admin_id', $admin_id)->get()->result();
                    $role_ids = array();
                    foreach ($role_rows as $rr) {
                        $role_ids[] = (int)$rr->role_id;
                    }

                    $permissions = array();
                    if (!empty($role_ids)) {
                        $perm_rows = $this->db
                            ->select('m.module_key, t.class')
                            ->from('ec_admin_role_permissions rp')
                            ->join('ec_admin_modules m', 'm.module_id = rp.module_id', 'left')
                            ->join('ec_admin_permission_type t', 't.perm_type_id = rp.perm_type_id', 'left')
                            ->where_in('rp.role_id', $role_ids)
                            ->where('m.status', 1)
                            ->get()->result();

                        foreach ($perm_rows as $row) {
                            if (!empty($row->module_key)) {
                                if (!isset($permissions[$row->module_key])) {
                                    $permissions[$row->module_key] = $row->class;
                                } elseif ($row->class === 'edit') {
                                    $permissions[$row->module_key] = 'edit';
                                }
                            }
                        }
                    }
                    // For legacy compatibility, save the first role_id in the session
                    $admin_sess['role_id']     = !empty($role_ids) ? $role_ids[0] : 0;
                    $admin_sess['role_ids']    = $role_ids;
                    $admin_sess['permissions'] = $permissions;
                    $this->session->set_userdata('admin', $admin_sess);
                }
            }
        }
    }

    public function isUserLoggedIn()
    {
        $type = isset($_SESSION['type']) ? $_SESSION['type'] : 'customer';

        // Customer routes can be accessed without the `/customer/` prefix (e.g. `/profile`, `/checkout`).
        // If we're executing a customer controller, ensure the session `type` is aligned so we don't
        // accidentally redirect to another area's auth page.
        $dir = $this->router->fetch_directory();
        if ($dir === 'customer/') {
            $type = 'customer';
            $this->session->set_userdata('type', 'customer');
            $_SESSION['type'] = 'customer';
        }

        // Customer sessions in this project may store only login_id/customer_id
        // without an explicit `logged_in` flag.
        if ($type === 'customer') {
            $cust = $this->session->userdata('customer');
            $cid = (int)($cust['login_id'] ?? ($cust['customer_id'] ?? 0));
            if ($cid > 0) {
                return;
            }
        }

        if($this->session->userdata("$type") && isset($this->session->userdata("$type")['logged_in']))
        {
            return;
        }
        else
        {
            // Detect API requests by POST flag OR by URL prefix
            $req_uri = !empty($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '';
            $is_api_url = (strpos($req_uri, '/api/') !== false);
            if(is_api() || $is_api_url)
            {
                echo json_encode(array('status' => 0, 'message' => 'login required.', 'STATUS' => 0, 'MSG' => array('login required.'), 'DATA' => array())); 
                die();          
            }
            else
            {
                if ($type === 'customer') {
                    // Session expired: clear any stale customer data and send to home page
                    $this->session->unset_userdata('customer');
                    redirect(base_url());
                }
                redirect("$type/auth/login");
            }
        }
    }
    
    protected function bootstrap_pagination($paging_config = array()){
//config for bootstrap pagination class integration
    $config['full_tag_open'] = '<ul class="pagination pagination-sm no-margin pull-right">';
    $config['full_tag_close'] = '</ul>';
    $config['first_link'] = false;
    $config['last_link'] = false;
    $config['first_tag_open'] = '<li>';
    $config['first_tag_close'] = '</li>';
    $config['prev_link'] = '&laquo';
    $config['prev_tag_open'] = '<li class="prev">';
    $config['prev_tag_close'] = '</li>';
    $config['next_link'] = '&raquo';
    $config['next_tag_open'] = '<li>';
    $config['next_tag_close'] = '</li>'; // <-- Line 77 is now gone
    $config['last_tag_open'] = '<li>';
    $config['last_tag_close'] = '</li>';
    $config['cur_tag_open'] = '<li class="active"><a href="#">';
    $config['cur_tag_close'] = '</a></li>';
    $config['num_tag_open'] = '<li>';
    $config['num_tag_close'] = '</li>';

    $config = array_merge($paging_config,$config);
        $this->pagination->initialize($config);
       // echo "<pre>"; print_r( $this->pagination->create_links() ); echo "</pre>"; die;
        return $this->pagination->create_links(); 
}
    
    
    /**
     * Template loading function for customer views
     * Automatically loads cart data for all customer pages
     */
    protected function load_template($view, $data = array())
    {
        // Load Cart model if not already loaded
        if (!isset($this->Cart_model)) {
            $this->load->model('Cart_model');
        }
        
        // Get cart data
        $cart_info = $this->Cart_model->cart_items();
        
        $cart_data = array(
            'cart_items'   => $cart_info['cart_items'],
            'cart_summary' => $cart_info['cart_summary'],
            'cart_count'   => count($cart_info['cart_items']),
            'cart_total'   => $cart_info['cart_summary']->total,
            'symbol'       => '$' // Default symbol, can be overridden
        );

        // Merge cart data with existing data
        if (is_array($data)) {
            $data = array_merge($data, $cart_data);
        } else {
            $data = $cart_data;
        }

        // Load the template
        $this->load->view($view, $data);
    }
    
    /**
     * This is the one and only template loading function.
     * It correctly uses the $view variable.
     */
    protected function _render_page($view, $data = array())
{

    // Load Cart model if not already loaded (some admin controllers don't load it explicitly)
    if (!isset($this->Cart_model)) {
        $this->load->model('Cart_model');
    }

    $cart_info = $this->Cart_model->cart_items();
    
    
    $cart_data = array(
        'cart_items'   => $cart_info['cart_items'],
        'cart_summary' => $cart_info['cart_summary'],
        'cart_count'   => count($cart_info['cart_items']),
        'symbol'       => '₹'
    );

    
    if (is_array($data)) {
        $data = array_merge($data, $cart_data);
    } else {
        $data = $cart_data;
    }

    
    $this->load->view('admin/header', $data);
    $this->load->view($view, $data);
    $this->load->view('admin/footer', $data);
}

    /**
     * Check if the currently logged-in admin has permission for a given module.
     * Called in each admin controller's __construct().
     *
     * @param string $module_key   The module_key from ec_admin_modules (e.g. 'vendor', 'order')
     * @param bool   $need_edit    Set TRUE to require Add/Edit/Delete access (perm_type class = 'edit')
     */
    public function check_module_permission($module_key, $need_edit = false)
    {
        // Only apply to admin panel routes
        $dir = $this->router->fetch_directory();
        if ($dir !== 'admin/') {
            return;
        }

        // Load session admin data
        $admin_session = $this->session->userdata('admin');
        if (!$admin_session || empty($admin_session['logged_in'])) {
            redirect('admin/auth/login');
            return;
        }

        // Super admins bypass all permission checks
        if (!empty($admin_session['super_admin']) && $admin_session['super_admin'] == 1) {
            return;
        }

        // Get permissions from session: ['vendor' => 'edit', 'order' => 'view', ...]
        $permissions = isset($admin_session['permissions']) && is_array($admin_session['permissions'])
            ? $admin_session['permissions']
            : [];

        // Check if module is in permitted list
        if (!array_key_exists($module_key, $permissions)) {
            $this->_show_403();
            return;
        }

        // Auto-detect if current action is a write/edit action and strictly enforce edit checks
        $current_method = $this->router->fetch_method();
        if ($this->_is_write_action($current_method)) {
            $need_edit = true;
        }

        // If write/edit access is needed but only view is granted, block it
        if ($need_edit && $permissions[$module_key] !== 'edit') {
            $this->_show_403('You have view-only access to this module.');
            return;
        }
    }

    /**
     * Helper to detect if a controller method is a write/modify action.
     */
    private function _is_write_action($method)
    {
        $method = strtolower($method);
        
        // Exact method names that modify state
        $exact_writes = array(
            'add', 'edit', 'update', 'delete', 'post', 'save', 'remove',
            'role_add', 'role_edit', 'role_delete', 'amount_transfer',
            'checkall_status_change'
        );
        if (in_array($method, $exact_writes)) {
            return true;
        }

        // Prefix patterns for modify actions
        $write_patterns = array(
            'ajax_delete', 'ajax_set', 'ajax_insert', 'ajax_save', 
            'ajax_toggle', 'ajax_update', 'ajax_upload', 'update_ajax'
        );
        foreach ($write_patterns as $pattern) {
            if (strpos($method, $pattern) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show a 403 Access Denied page and stop execution.
     */
    private function _show_403($message = 'You do not have permission to access this page.')
    {
        $this->output->set_status_header(403);
        if ($this->input->is_ajax_request()) {
            echo json_encode(array(
                'status' => 0,
                'message' => $message,
                'MSG' => array($message),
                'DATA' => array(),
                'success' => 0,
                'msg' => $message
            ));
            exit;
        }
        $html  = '<!DOCTYPE html><html><head><title>403 - Access Denied</title>';
        $html .= '<style>body{font-family:sans-serif;background:#f4f7f6;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}';
        $html .= '.box{background:#fff;padding:50px 60px;border-radius:12px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.08);}';
        $html .= 'h1{font-size:72px;color:#6366f1;margin:0;}h2{color:#1e293b;margin:10px 0;}p{color:#64748b;font-size:15px;}';
        $html .= 'a{display:inline-block;margin-top:20px;padding:10px 25px;background:#6366f1;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;}';
        $html .= '</style></head><body><div class="box"><h1>403</h1><h2>Access Denied</h2>';
        $html .= '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<a href="javascript:history.back()">&#8592; Go Back</a></div></body></html>';
        echo $html;
        exit;
    }

}
?>