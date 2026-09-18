<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vendor extends MY_Controller {

	function __construct()
	{
	    parent::__construct();
	    $this->load->model('Query_model');
	    $this->load->model('Vendor_model');
        $this->load->model('Order_model');
        $this->load->helper('api');
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
        $this->FNAME = $this->session->userdata($this->TYPE)['fname'];
        $this->check_module_permission('vendor');
	}

	/**
	 * Check if the current admin can perform write/edit operations on vendor.
	 * Returns TRUE for super admins and sub-admins with 'edit' permission on vendor module.
	 */
	private function _can_edit_vendor()
	{
		$admin_session = $this->session->userdata('admin');
		if (!$admin_session) return false;
		// Super admin bypass
		if (!empty($admin_session['super_admin']) && $admin_session['super_admin'] == 1) return true;
		// Sub-admin with vendor edit permission
		$permissions = isset($admin_session['permissions']) && is_array($admin_session['permissions'])
			? $admin_session['permissions'] : [];
		return (isset($permissions['vendor']) && $permissions['vendor'] === 'edit');
	}
	
	public function index()
    {
        // 1. Load Language Data
        $page_lang = get_page_language_data('admin_page_lang');

        // 2. Use variables ($page_lang->home) instead of "Home"
        $crumbs = array($page_lang->home => "/$this->TYPE/dashboard", $page_lang->vendor => "");
        
        $breadcrumbs = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
        $data['csrf'] = csrf_token();
        $admin_id    = $this->LOGIN_ID;
        $data['TYPE'] = $this->TYPE;
        $data['page_count'] = page_count();
        $this->load->template("$this->TYPE/vendor/index_vendor",$data);
    }

   public function index_ajax_vendor()
{
    // 1. Setup Pagination and Basics
    $length = (isset($_POST['length'])) ? $_POST['length'] : page_count();
    $page   = (isset($_POST['page'])) ? $_POST['page'] : 1;
    
    // 2. Fetch the list from the Model (Includes the JOIN for category_name)
    $list = $this->Vendor_model->get_datatable();
    
    $data = array();
    
    $has_enabled = $this->db->field_exists('is_enabled', 'ec_vendor');
    foreach ($list as $obj) {
        $row = array();
        
        // 3. Fetch specific counts for this vendor
        // Products are stored in new table `products` with vendor_id
        $total_products   = $this->Query_model->count_all('products', array('vendor_id' => $obj->vendor_id));
        // Orders are stored per vendor in ec_order_items.login_id
        $total_order      = $this->Query_model->count_all('ec_order_items', array('login_id' => $obj->vendor_id));
        
        // Fetch status-specific counts
        $pending_count   = $this->Query_model->count_all('ec_order_items', array('login_id' => $obj->vendor_id, 'status' => '1'));
        $delivered_count = $this->Query_model->count_all('ec_order_items', array('login_id' => $obj->vendor_id, 'status' => '10'));

        // 4. Commission Display Logic
        $commission_val = isset($obj->commission) ? $obj->commission : '';
        $row['commission'] = '<span class="badge badge-outline-secondary" style="border: 1px solid #ccc; padding: 4px 8px;">' . $commission_val . '</span>';

        // 5. Build the "Contact Info" Column
        $row['contact_info'] = '<strong>' . $obj->name . '</strong><br>' . 
                               '<small class="text-muted">' . $obj->email . '</small><br>' . 
                               '<small class="text-muted">' . $obj->mobile . '</small>';

        // 6. Map data points (Fixed the variable undefined errors here)
        $row['total_products']   = $total_products;
        $row['total_order']      = $total_order;
        $row['pending_order']    = $pending_count;
        $row['delivered_order']  = $delivered_count;
        
        $st = isset($obj->status) ? (string)$obj->status : '0';
        $status_text = ($st === '1') ? 'Approved' : (($st === '2') ? 'Rejected' : 'Pending');
        $status_cls = ($st === '1') ? 'badge-success' : (($st === '2') ? 'badge-danger' : 'badge-warning');
        $row['status_html'] = '<span class="badge ' . $status_cls . '">' . $status_text . '</span>';

        $row['enabled_html'] = '';
        if($has_enabled){
            $en = isset($obj->is_enabled) ? (string)$obj->is_enabled : '1';
            $enabled_text = ($en === '1') ? 'Enabled' : 'Disabled';
            $enabled_cls = ($en === '1') ? 'badge-success' : 'badge-secondary';
            $row['enabled_html'] = '<span class="badge ' . $enabled_cls . '">' . $enabled_text . '</span>';
        }

        $row['admin_id']  = $obj->vendor_id;
        $row['admin_uid'] = $obj->vendor_uid;
        $row['action']    = ''; 

        $data[] = $row;
    }

    // 8. Prepare Final Output for DataTables
    $output = array(
        "draw"            => isset($_POST['draw']) ? $_POST['draw'] : '',
        "recordsTotal"    => $this->Vendor_model->count_all(),
        "recordsFiltered" => $this->Vendor_model->count_filtered(),
        "data"            => $data,
        "csrf_token"      => $this->security->get_csrf_hash() 
    );

    echo json_encode($output);
}
	public function add()
    {
        $this->post();
    }

	public function update($admin_id)
    {
        $this->post($admin_id);
    }

function post($admin_id = NULL)
{
    $this->load->library('form_validation');

    // 1. Validation Rules
    $this->form_validation->set_rules('fname', 'Name', 'trim|required');
    $this->form_validation->set_rules('email', 'Email', 'trim|required');
    $is_add = (!isset($admin_id) || !$admin_id);
    if($is_add){
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
    }

    $can_edit_commission = true;

    // Fetch existing vendor data if editing
    if(isset($admin_id)){
        $vendor_obj = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $admin_id));
        
        if(!$vendor_obj){
            $this->session->set_flashdata('error', 'Vendor not found.');
            redirect("$this->TYPE/vendor");
        }

		$can_edit_commission = ((string)$vendor_obj->status === '0');
    }

	// Commission required only when adding, or when editing a pending vendor
	if(!isset($admin_id) || $can_edit_commission){
		$this->form_validation->set_rules('commission', 'Commission', 'trim|required|numeric');
	}

    if($this->input->post()) {
        // Prepare data from form submission
        $data = array(
            'fname'        => $this->input->post('fname'),
            'lname'        => '', 
            'email'        => $this->input->post('email'),
            'mobile'       => $this->input->post('mobile'),
            'store_name'   => $this->input->post('store_name'),
            'city'         => $this->input->post('city'),
            'zip'          => $this->input->post('zip'),
            'address'      => $this->input->post('address'),
            'country'      => $this->input->post('country'),
			'status'       => $is_add ? $this->input->post('status') : (isset($vendor_obj->status) ? $vendor_obj->status : '0'),
			'commission'   => $this->input->post('commission'),
        );

		// If vendor is not pending, commission cannot be updated even if posted
		if(isset($admin_id) && !$can_edit_commission){
			$data['commission'] = isset($vendor_obj->commission) ? $vendor_obj->commission : '';
		}
    } else {
        if(isset($admin_id)){
			$commission_val = isset($vendor_obj->commission) ? $vendor_obj->commission : '';
            // Populate form with existing database data
            $data = array(
                'fname'        => $vendor_obj->name,
                'lname'        => '',
                'email'        => $vendor_obj->email,
                'mobile'       => $vendor_obj->mobile,
                'store_name'   => $vendor_obj->store_name,
                'city'         => $vendor_obj->city,
                'zip'          => '',
                'address'      => $vendor_obj->address,
                'country'      => $vendor_obj->country,
                'status'       => $vendor_obj->status,
				'is_enabled'   => isset($vendor_obj->is_enabled) ? $vendor_obj->is_enabled : '1',
				'commission'   => $commission_val,
            );
        }
    }

    if ($this->form_validation->run() == FALSE){
        $action_bc = isset($admin_id) ? 'Update' : 'Add';
        $page_lang = get_page_language_data('admin_page_lang');

        $crumbs = array(
            (isset($page_lang->home) ? $page_lang->home : 'Home') => "/$this->TYPE/dashboard", 
            (isset($page_lang->vendor) ? $page_lang->vendor : 'Vendor') => "/$this->TYPE/vendor/", 
            $action_bc => 'action'
        );
        
        $data['breadcrumbs'] = $this->breadcrumbs->show($crumbs);
        $data['csrf']        = csrf_token();
        $data['TYPE']        = $this->TYPE;
        $data['action']      = isset($admin_id) ? 'update' : 'add';
        $data['admin_id']    = isset($admin_id) ? $admin_id : NULL;
		$data['can_edit_commission'] = $can_edit_commission;
        
        $this->load->template("$this->TYPE/vendor/vendor", $data);
    } else {
        // Data to be saved in ec_vendor
        $db_data = array(
            'name'       => $data['fname'],
            'email'       => $data['email'],
            'mobile'      => $data['mobile'],
            'store_name'  => $data['store_name'],
            'city'        => $data['city'],
            'address'     => $data['address'],
            'country'     => $data['country'],
            'status'      => $data['status'],
            'commission'  => $data['commission'],
        );
                    
        if(isset($admin_id)){
            // Status is handled via the vendor list dropdown; do not update via edit form
            unset($db_data['status']);

            // If vendor is not pending, do not allow commission change
            if(!$can_edit_commission){
                if(isset($vendor_obj) && isset($vendor_obj->commission)){
                    $db_data['commission'] = $vendor_obj->commission;
                }else{
                    unset($db_data['commission']);
                }
            }

            // Simply update vendor details
            $this->Query_model->update_data('ec_vendor', $db_data, array('vendor_id' => $admin_id));

			// Optional: admin uploaded document (stored separately from vendor documents)
			if (isset($_FILES['admin_uploaded_doc']['name']) && $_FILES['admin_uploaded_doc']['name'] != '') {
				$doc_table = '';
				if ($this->db->table_exists('ec_vendor_verification')) {
					$doc_table = 'ec_vendor_verification';
				} elseif ($this->db->table_exists('ec_vendor_varification')) {
					$doc_table = 'ec_vendor_varification';
				}
				if ($doc_table && $this->db->field_exists('admin_uploaded_doc', $doc_table)) {
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
					$this->upload->initialize($config);
					if ($this->upload->do_upload('admin_uploaded_doc')) {
						$data_file = $this->upload->data();
						$admin_doc_name = isset($data_file['file_name']) ? (string)$data_file['file_name'] : '';
						if ($admin_doc_name !== '') {
							$vendor_col = $this->db->field_exists('vendor_id', $doc_table) ? 'vendor_id' : ($this->db->field_exists('login_id', $doc_table) ? 'login_id' : ($this->db->field_exists('admin_id', $doc_table) ? 'admin_id' : ''));
							if ($vendor_col) {
								$row = $this->db
									->select('id')
									->from($doc_table)
									->where($vendor_col, (int)$admin_id)
									->order_by('id', 'DESC')
									->limit(1)
									->get()->row();
								$payload = array('admin_uploaded_doc' => $admin_doc_name);
								if ($this->db->field_exists('updated_at', $doc_table)) {
									$payload['updated_at'] = date('Y-m-d H:i:s');
								}
								if ($row && isset($row->id)) {
									$this->db->where('id', (int)$row->id);
									$this->db->update($doc_table, $payload);
								} else {
									$payload[$vendor_col] = (int)$admin_id;
									if ($this->db->field_exists('created_at', $doc_table)) {
										$payload['created_at'] = date('Y-m-d H:i:s');
									}
									$this->db->insert($doc_table, $payload);
								}
							}
						}
					}
				}
			}

            $this->session->set_flashdata('success', 'Vendor Updated Successfully.');
        } else {
            // Insert new vendor
			$newId = $this->Query_model->insert_data('ec_vendor', $db_data);
            $this->session->set_flashdata('success', 'Vendor Inserted Successfully.');
        }
        redirect("$this->TYPE/vendor");
    }
}
    public function filter_order($vendor_id)
    {
        $this->LOGIN_ID=2; 
        $order_list = $this->Order_model->order_list(array('login_id'=>$this->LOGIN_ID,'post_data' => '' ));
       echo "<pre>"; print_r( $order_list); echo "</pre>";exit;
    
         $order_wise = array(); $order_count = 0;
        if($order_list)
        {
            foreach($order_list as $row_wise)
            {
                $product_obj        = $this->Query_model->get_data_obj('ec_product',array('product_id' => $row_wise->product_id));
                $row_wise->date_created_modify = date('d-M-Y H:i', strtotime($row_wise->date_created));
                $row_wise->product_obj = $product_obj;

                $order_wise['items'][$row_wise->order_uid][] = $row_wise;
                $order_wise['order_dtl'][$row_wise->order_uid] = $row_wise;
            }
        }

        $api_data['order_wise']= $order_wise;

        if(count($api_data['order_wise']))
        api_response(array('status'=>1, 'msg'=>'Sucess', 'data'=>$api_data));
        else
        api_response(array('status'=>0, 'msg'=>'There is no data', 'data'=>array()));
    }

    
    public function vendor_details($vendor_uid = NULL)
    {
        // 1. Load Language Data
        $page_lang = get_page_language_data('admin_page_lang');

        // 2. Use variables
        $crumbs = array(
            $page_lang->home => "/$this->TYPE/dashboard", 
            $page_lang->vendor_list => "/$this->TYPE/vendor", 
            $page_lang->vendor_details => ''
        );

        $breadcrumbs                     = $this->breadcrumbs->show($crumbs);
        $data['breadcrumbs']     = $breadcrumbs;
        $data['csrf']            = csrf_token();
        $data['TYPE']            = $this->TYPE;
        $data['page_count']      = page_count();
        $data['vendor_uid']      = $vendor_uid;
        $vendor_row              = get_vendor_id($vendor_uid);
        $vendor_id               = $vendor_row ? (int)$vendor_row->vendor_id : 0;
        $data['vendor_info']     = $this->Query_model->get_data_obj('ec_vendor', array('vendor_id' => $vendor_id));
		$doc_table = '';
		if ($this->db->table_exists('ec_vendor_verification')) {
			$doc_table = 'ec_vendor_verification';
		} elseif ($this->db->table_exists('ec_vendor_varification')) {
			$doc_table = 'ec_vendor_varification';
		}
		$vendor_doc_front = '';
		$vendor_doc_back = '';
		$vendor_doc_status = '';
		$vendor_doc_admin_note = '';
		$vendor_admin_uploaded_doc = '';
		if ($doc_table) {
			$vendor_col = $this->db->field_exists('vendor_id', $doc_table) ? 'vendor_id' : ($this->db->field_exists('login_id', $doc_table) ? 'login_id' : ($this->db->field_exists('admin_id', $doc_table) ? 'admin_id' : ''));
			if ($vendor_col) {
				$row = $this->db
					->select('*')
					->from($doc_table)
					->where($vendor_col, (int)$vendor_id)
					->order_by('id', 'DESC')
					->limit(1)
					->get()->row();
				if ($row) {
					$vendor_doc_status = isset($row->status) ? (string)$row->status : '';
					$vendor_doc_admin_note = (isset($row->admin_note) && $row->admin_note !== null) ? (string)$row->admin_note : '';
					$vendor_doc_front = (isset($row->document_front) && $row->document_front !== null) ? (string)$row->document_front : ((isset($row->doc_front) && $row->doc_front !== null) ? (string)$row->doc_front : '');
					$vendor_doc_back = (isset($row->document_back) && $row->document_back !== null) ? (string)$row->document_back : ((isset($row->doc_back) && $row->doc_back !== null) ? (string)$row->doc_back : '');
					$vendor_admin_uploaded_doc = (isset($row->admin_uploaded_doc) && $row->admin_uploaded_doc !== null) ? (string)$row->admin_uploaded_doc : '';
				}
			}
		}
		$data['vendor_document_status'] = $vendor_doc_status;
		$data['vendor_document_admin_note'] = $vendor_doc_admin_note;
		$data['vendor_document_front'] = $vendor_doc_front;
		$data['vendor_document_back'] = $vendor_doc_back;
		$data['vendor_admin_uploaded_doc'] = $vendor_admin_uploaded_doc;
        $data['item_count']      = $this->Vendor_model->count_all_item('ec_order_items', array('login_id'=> $vendor_id));
        $order_item_id           = $this->Vendor_model->get_last_orderId('ec_order_items', array('login_id'=> $vendor_id));
        $data['order_item_id']   = isset($order_item_id->id) ? $order_item_id->id : 0;
        if(!empty($order_item_id)){
        $data['last_order_date'] = $order_item_id->date_added;
        $data['amount']          = $this->Vendor_model->count_earning('ec_order_items', array('login_id'=> $vendor_id));
        
        $paid_status = null;
        if (isset($order_item_id->date_added)) {
            $last_date = date('Y-m-d', strtotime($order_item_id->date_added));
            $paid_status = $this->db
                ->select('*')
                ->from('ec_earnings')
                ->where('vendor_id', $vendor_id)
                ->where('from_date <=', $last_date)
                ->where('to_date >=', $last_date)
                ->where('paid_status', '1')
                ->limit(1)
                ->get()->row();
        }
        if($paid_status){ $statusdata = 'Paid'; }else{$statusdata = 'Unpaid'; }
        $data['vendor_id']      = $vendor_id;
        $amount                 = $this->vendor_amount_transfer($vendor_id);   
        $data['paid_status']    = $statusdata;
        }
        $data['admincom']       = '';
        $this->load->template("$this->TYPE/vendor/vendor_info",$data);
    }

    


public function get_order_list_by_vendor()
{
    $vendor_id = $this->input->post('vendor_id');
    $length = (isset($_POST['length'])) ? $_POST['length'] : page_count();
    $page = (isset($_POST['start'])) ? $_POST['start'] : 0;

    // New schema: vendor is stored on ec_order_items.login_id (sort by latest order first)
    $list = $this->Query_model->get_data('ec_order_items', array('login_id' => $vendor_id), array('id' => 'DESC'), array('start' => $page, 'length' => $length));
    $data = array();

    foreach ($list as $obj) {
        $row = array();
        $order_obj = $this->Query_model->get_data_obj('ec_orders', array('id' => $obj->order_id));

        $row['order_id']   = isset($order_obj->order_number) ? $order_obj->order_number : $obj->order_id;
        $row['order_info'] = '<div><strong>'.$obj->product_name.'</strong><br><small>Qty: '.$obj->qty.'</small></div>';
        $row['date']       = date("d-M-Y H:i", strtotime($obj->date_added));

        // Get currency symbol from order
        $currency_symbol = '$';
        if ($order_obj && isset($order_obj->currency_id)) {
            $currency = current_currency($order_obj->currency_id);
            if ($currency && isset($currency->symbol)) {
                $currency_symbol = html_entity_decode($currency->symbol);
            }
        }
        $row['amount']     = $currency_symbol . ' ' . number_format($obj->subtotal, 2);

        $status_int = (int)$obj->status;
        $status_text = 'Unknown';
        $status_badge_cls = 'badge-secondary';

        if (in_array($status_int, [1, 16, 19])) {
            $status_text = 'Pending';
            $status_badge_cls = 'badge-warning'; // Yellow for pending
        } elseif ($status_int === 15) {
            $status_text = 'Confirmed';
            $status_badge_cls = 'badge-info'; // Blue/Cyan for confirmed
        } elseif (in_array($status_int, [2, 3, 8, 18])) {
            $status_text = 'Shipped';
            $status_badge_cls = 'badge-primary'; // Primary Blue for shipped
        } elseif ($status_int === 10) {
            $status_text = 'Delivered';
            $status_badge_cls = 'badge-success'; // Green for delivered
        } elseif (in_array($status_int, [5, 17])) {
            $status_text = 'Cancelled';
            $status_badge_cls = 'badge-danger'; // Red for cancelled
        }

        $row['status'] = '<span class="badge '.$status_badge_cls.'">'.$status_text.'</span>';
        $data[] = $row;
    }

    echo json_encode(array(
        "draw" => isset($_POST['draw']) ? $_POST['draw'] : '',
        "recordsTotal" => $this->Query_model->count_all('ec_order_items', array('login_id' => $vendor_id)),
        "recordsFiltered" => $this->Query_model->count_filtered('ec_order_items', array('login_id' => $vendor_id)),
        "data" => $data,
    ));
}

public function get_vendor_products()
{
    $vendor_id = $this->input->post('vendor_id');
    $length = (isset($_POST['length'])) ? (int)$_POST['length'] : page_count();
    $page = (isset($_POST['start'])) ? (int)$_POST['start'] : 0;

    // Fetch products from `products` table where `vendor_id = $vendor_id`
    $this->db->select('*');
    $this->db->from('products');
    $this->db->where('vendor_id', $vendor_id);
    $this->db->order_by('id', 'DESC');
    $this->db->limit($length, $page);
    $list = $this->db->get()->result();

    $this->db->from('products');
    $this->db->where('vendor_id', $vendor_id);
    $total_records = $this->db->count_all_results();

    // Get the basic currency symbol
    $all_currencies = $this->db
        ->select('currency_id, symbol, iso_code, rate, basic')
        ->from('ec_currency')
        ->where('status', '1')
        ->get()->result();
    $basic_symbol = '$';
    foreach ($all_currencies as $c) {
        if ((int)$c->basic === 1) {
            $basic_symbol = html_entity_decode($c->symbol);
            break;
        }
    }

    $data = array();
    foreach ($list as $obj) {
        $row = array();
        $row['product_id']   = $obj->id;
        
        // Find variations for this product
        $variations = $this->db
            ->select('id, stock')
            ->from('product_variations')
            ->where('product_id', $obj->id)
            ->get()->result();

        $variation_ids = array();
        $total_stock = 0;
        foreach ($variations as $v) {
            $variation_ids[] = $v->id;
            $total_stock += (int)$v->stock;
        }

        // Get the first image from product_variation_images
        $productimg = base_url().'assets/default_images/product.jpg';
        if (!empty($variation_ids)) {
            $img_row = $this->db
                ->select('image_path')
                ->from('product_variation_images')
                ->where_in('variation_id', $variation_ids)
                ->order_by('id', 'ASC')
                ->limit(1)
                ->get()->row();
            if ($img_row && $img_row->image_path) {
                $raw = trim($img_row->image_path);
                if (preg_match('#^https?://#i', $raw)) {
                    $productimg = $raw;
                } else {
                    $rel = ltrim($raw, '/\\');
                    if ($rel !== '' && @file_exists(FCPATH . $rel)) {
                        $productimg = base_url($rel);
                    } else {
                        $productimg = base_url('uploads/products/' . $rel);
                    }
                }
            }
        }

        // Get prices for these variations
        $min_price = null;
        $max_price = null;
        if (!empty($variation_ids)) {
            $price_rows = $this->db
                ->select('price')
                ->from('product_variation_price')
                ->where_in('variation_id', $variation_ids)
                ->get()->result();
            foreach ($price_rows as $pr) {
                $p_val = (float)$pr->price;
                if ($min_price === null || $p_val < $min_price) {
                    $min_price = $p_val;
                }
                if ($max_price === null || $p_val > $max_price) {
                    $max_price = $p_val;
                }
            }
        }

        $price_display = '-';
        if ($min_price !== null) {
            if ($min_price == $max_price) {
                $price_display = $basic_symbol . ' ' . number_format($min_price, 2);
            } else {
                $price_display = $basic_symbol . ' ' . number_format($min_price, 2) . ' - ' . $basic_symbol . ' ' . number_format($max_price, 2);
            }
        }

        $row['image']        = '<img src="'.$productimg.'" width="60" style="max-height:60px; object-fit: cover; border-radius: 4px;">';
        $row['name']         = htmlspecialchars($obj->name);
        $row['mrp']          = '-'; // MRP is not stored in the new schema
        $row['price']        = $price_display;
        $row['quantity']     = $total_stock;
        $row['created_date'] = date("d-M-Y", strtotime($obj->date_added));
        $row['status']       = ($obj->status == 1) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
        $data[] = $row;
    }

    echo json_encode(array(
        "draw" => isset($_POST['draw']) ? $_POST['draw'] : '',
        "recordsTotal" => $total_records,
        "recordsFiltered" => $total_records,
        "data" => $data,
    ));
}


    public function vendor_amount_transfer($vendor_id = NULL)
    {
        if(!$vendor_id){return false;}
        $data = array();
        $admincom = 10;
        $getmonth  = get_month();
        $year      = date("Y");
        $current_YM = date('Y-m');

        $checkdata = $this->Query_model->get_data('ec_earnings', array('vendor_id'=>$vendor_id), array('earning_id'=> 'DESC'));
        $getdata   = $this->Vendor_model->get_data_by_month(array('vendor_id'=>$vendor_id));

        if(empty($checkdata))
        {
            $insdata    =   array();
            foreach($getdata as $monthdata)
            {
                $admin_amount          = ($admincom / 100) * $monthdata->total;
                $month                 = $getmonth[$monthdata->month];
                $days                  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $datayear              = $monthdata->Year;

                if($current_YM == $datayear.'-'.$month){continue;}
                if('2021-12' == $datayear.'-'.$month){continue;}

                $insdata = array( 
                    'vendor_id'         => $monthdata->supplier_id,
                    'admin_percentage'  => '10', 
                    'from_date'         => $datayear.'-'.$month.'-'.'01',
                    'to_date'           => $datayear.'-'.$month.'-'.$days,
                    'total'             => $monthdata->total,
                    'admin_amount'      => $admin_amount,
                    'vendor_amount'     => $monthdata->total - $admin_amount
                );

            $earningsdata = $this->Query_model->insert_data('ec_earnings', $insdata);

            }  
        }
        else
        {
            if(count($checkdata))
            {
                $last_array = end($checkdata);
                $to_date = $last_array->to_date;
                if(!$to_date){return false;}
                $yrmnth = date('Y-m', strtotime($to_date));
                $getdata = [];
                $getdata   = $this->Vendor_model->get_data_by_month(array('vendor_id'=>$vendor_id, 'yrmnth'=>$yrmnth, 'next_month'=>1));
                if(count($getdata))
                {
                    foreach($getdata as $monthdata)
                    {
                        if(date('F') == $monthdata->month){continue;}
                        $admin_amount          = ($admincom / 100) * $monthdata->total;
                        $month                 = $getmonth[$monthdata->month];
                        $days                  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $datayear              = $monthdata->Year;

                        if($current_YM == $datayear.'-'.$month){continue;}

                        $insdata = array(       
                                'vendor_id'         => $monthdata->supplier_id,
                                'admin_percentage'  => '10', 
                                'from_date'         => $datayear.'-'.$month.'-'.'01',
                                'to_date'           => $datayear.'-'.$month.'-'.$days,
                                'total'             => $monthdata->total,
                                'admin_amount'      => $admin_amount,
                                'vendor_amount'     => $monthdata->total - $admin_amount
                                );

                        $earningsdata = $this->Query_model->insert_data('ec_earnings', $insdata);

                        #insert in ec_earnings
                    }
                }
                                     
            
            }  
        }   
     }

    public function current_month_earning($args)
    {
       $monthyear = date('M Y');
       $getmonth  = get_month();
       $year      = date("Y");
       $nulldate  = '';
       $admin_commission = $this->Query_model->get_data_obj('ec_admin', array('admin_id' => $args['vendor_id']));
       $admincom = $admin_commission->commission;
       $getdata   = $this->Vendor_model->get_data_by_month(array('vendor_id'=>$args['vendor_id'], 'current_month'=> $args['current_mnth'], 'yrmnth'=>$args['yrmnth']));
        if(count($getdata))
        {
            $admin_amount                = ($admincom / 100) * $getdata[0]->total;
            $vendoramount                = $getdata[0]->total - $admin_amount;
            $getdata[0]->earning_id      = '';
            $getdata[0]->admin_id        = '';
            $getdata[0]->vendor_id       = $getdata[0]->supplier_id;
            $getdata[0]->total           = $getdata[0]->total;
            $getdata[0]->admin_percentage= $admincom;
            $getdata[0]->admin_amount    = number_format((float)$admin_amount, 2, '.', '');
            $getdata[0]->vendor_amount   = number_format((float)$vendoramount, 2, '.', '');
            $getdata[0]->from_date       = date("Y-m-01 H:i:s");
            $getdata[0]->to_date         = date("Y-m-d H:i:s");
            $getdata[0]->current_month   = '0';

        }
        return $getdata;
    } 
    
    public function get_vendor_amount()
    {
        $data = array();
        $vendor_id = $this->input->post('vendor_id');
        $length = (isset($_POST['length'])) ? (int)$_POST['length'] : page_count();
        $start  = (isset($_POST['start'])) ? (int)$_POST['start'] : 0;
        $monthyear = date('M Y');

        $total_db = $this->Query_model->count_all('ec_earnings', array('vendor_id'=>$vendor_id));
        $recordsTotal = $total_db + 1; // Add 1 for the current month generated on-the-fly

        if ($start == 0) {
            $curnt_mnth_data = $this->current_month_earning(array('vendor_id'=>$vendor_id, 'current_mnth'=>1, 'yrmnth'=>date('Y-m')));
            $db_length = $length - count($curnt_mnth_data);
            if ($db_length < 0) { $db_length = 0; }
            $list = $this->Query_model->get_data('ec_earnings', array('vendor_id'=>$vendor_id), array('earning_id'=> 'DESC'), array('start' => 0, 'length' => $db_length));
            $newArr = array_merge($curnt_mnth_data, $list);
        } else {
            $db_start = $start - 1;
            $list = $this->Query_model->get_data('ec_earnings', array('vendor_id'=>$vendor_id), array('earning_id'=> 'DESC'), array('start' => $db_start, 'length' => $length));
            $newArr = $list;
        }

        // Get the basic currency symbol
        $all_currencies = $this->db
            ->select('currency_id, symbol, iso_code, rate, basic')
            ->from('ec_currency')
            ->where('status', '1')
            ->get()->result();
        $basic_symbol = '$';
        foreach ($all_currencies as $c) {
            if ((int)$c->basic === 1) {
                $basic_symbol = html_entity_decode($c->symbol);
                break;
            }
        }

        foreach($newArr as $obj) {

            $row = array();
            $from_date_raw = isset($obj->from_date) ? $obj->from_date : null;
            $to_date_raw   = isset($obj->to_date) ? $obj->to_date : null;

            $from_date_fmt = $from_date_raw ? date('M Y', strtotime($from_date_raw)) : '';
            $to_date_fmt   = $to_date_raw ? date('M d Y', strtotime($to_date_raw)) : '';

            if($from_date_fmt && $monthyear == $from_date_fmt)
            {
                $current_month = '1';
            }
            else
            {
                $current_month = '0';
            }
             
            $row['earning_id']      = isset($obj->earning_id) ? $obj->earning_id : '';
            $row['vendor_id']       = isset($obj->vendor_id) ? $obj->vendor_id : $vendor_id;

            // Fetch the latest order for this vendor in this earning date range
            $order_uid = '';
            if ($from_date_raw && $to_date_raw) {
                $start_date = date('Y-m-d 00:00:00', strtotime($from_date_raw));
                $end_date   = date('Y-m-d 23:59:59', strtotime($to_date_raw));

                $order_item = $this->db
                    ->select('eo.order_number')
                    ->from('ec_order_items as eoi')
                    ->join('ec_orders as eo', 'eo.id = eoi.order_id', 'left')
                    ->where('eoi.login_id', $vendor_id)
                    ->where('eoi.date_added >=', $start_date)
                    ->where('eoi.date_added <=', $end_date)
                    ->order_by('eo.id', 'DESC')
                    ->limit(1)
                    ->get()->row();

                if ($order_item && $order_item->order_number) {
                    $order_uid = $order_item->order_number;
                }
            }

            $row['order_id']        = $order_uid ? $order_uid : (isset($obj->earning_id) ? 'Earn #' . $obj->earning_id : 'N/A');
            $row['total']           = $basic_symbol . ' ' . number_format((float)(isset($obj->total) ? $obj->total : 0), 2);
            $row['admin_amount']    = $basic_symbol . ' ' . number_format((float)(isset($obj->admin_amount) ? $obj->admin_amount : 0), 2);
            $row['vendor_amount']   = $basic_symbol . ' ' . number_format((float)(isset($obj->vendor_amount) ? $obj->vendor_amount : 0), 2);
            $row['vendor_amount_raw'] = isset($obj->vendor_amount) ? $obj->vendor_amount : '0.00';
            $row['from_date']       = $from_date_fmt;
            $row['to_date']         = $to_date_fmt;
            $row['paid_type']       = isset($obj->paid_type) ? $obj->paid_type : 'N/A';
            $row['paid_date']       = isset($obj->paid_date) ? date('M d Y', strtotime($obj->paid_date)) : 'N/A';
            $row['paid_status']     = isset($obj->paid_status) ? $obj->paid_status : '0' ;
            $row['current_month']   = $current_month;

            $data[] = $row;
        }

        $output = array(
                "draw" => isset($_POST['draw'])?$_POST['draw']:'',
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsTotal,
                "data" => $data,
                );
        echo json_encode($output);

    } 

    public function amount_transfer()
    {
        $PaidType       = $this->input->post('PaidType'); 
        $all_info       = $this->input->post('all_info');
        $decode_all     = json_decode($all_info);
        $earning_id     = $decode_all->earning_id;
        $vendor_amount  = $decode_all->vendor_amount;
        $date           = date('Y-m-d h:i:s a', time());

        $tranferdata = array('paid_type' => $PaidType, 'vendor_amount' => $vendor_amount, 'paid_date' => $date, 'paid_status' => '1');
        
        $trandata = $this->Query_model->update_data('ec_earnings', $tranferdata, array('earning_id'=>$earning_id));
        if($trandata)
        {
            $output = array('success'=>'Amount Transferred Successfully');
        }else
        {
            $output = array('Fail'=>'Amount is not Transferred');
        }
        
        echo json_encode($output);
    }

    public function ajax_toggle_enable()
    {
        // Security Check: Super admin or sub-admin with vendor edit permission.
        if (!$this->_can_edit_vendor()) {
            echo json_encode(['success' => 0, 'msg' => 'You do not have permission for this action.', 'csrf_token' => $this->security->get_csrf_hash()]);
            return;
        }

        $admin_id = $this->input->post('vendor_id');
        $is_enabled = $this->input->post('is_enabled');

        if (!is_numeric($admin_id) || !in_array($is_enabled, ['0', '1'])) {
            echo json_encode(['success' => 0, 'msg' => 'Invalid data provided.', 'csrf_token' => $this->security->get_csrf_hash()]);
            return;
        }

        $this->Query_model->update_data('ec_vendor', ['is_enabled' => $is_enabled], ['vendor_id' => $admin_id]);

        echo json_encode([
            'success' => 1,
            'msg' => 'Vendor ' . ($is_enabled == '1' ? 'enabled' : 'disabled') . ' successfully.',
            'csrf_token' => $this->security->get_csrf_hash()
        ]);
    }

    public function ajax_update_status()
    {
        // Security Check: Super admin or sub-admin with vendor edit permission.
        if (!$this->_can_edit_vendor()) {
            echo json_encode(['success' => 0, 'msg' => 'You do not have permission for this action.', 'csrf_token' => $this->security->get_csrf_hash()]);
            return;
        }

        $admin_id = $this->input->post('admin_id');
        $new_status = $this->input->post('status');
		$admin_note = $this->input->post('admin_note');
		$admin_note = is_string($admin_note) ? trim($admin_note) : '';

        // Validation
        if (!is_numeric($admin_id) || !in_array($new_status, ['0', '1', '2'])) {
            echo json_encode(['success' => 0, 'msg' => 'Invalid data provided.', 'csrf_token' => $this->security->get_csrf_hash()]);
            return;
        }
		if ((string)$new_status === '2' && $admin_note === '') {
			echo json_encode(['success' => 0, 'msg' => 'Rejection note is required.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}

        $vendor_row = $this->db
            ->select('vendor_id, status')
            ->from('ec_vendor')
            ->where('vendor_id', (int)$admin_id)
            ->limit(1)
            ->get()->row();
        if(!$vendor_row || !isset($vendor_row->vendor_id)){
            echo json_encode(['success' => 0, 'msg' => 'Vendor not found.', 'csrf_token' => $this->security->get_csrf_hash()]);
            return;
        }

        $data_to_update = array('status' => $new_status);
        $condition = array('vendor_id' => $admin_id);

        $updated = $this->Query_model->update_data('ec_vendor', $data_to_update, $condition);
		// Treat as success if value already persisted, or if update_data returned false due to affected_rows=0
		$vendor_row_after = $this->db
			->select('vendor_id, status')
			->from('ec_vendor')
			->where('vendor_id', (int)$admin_id)
			->limit(1)
			->get()->row();
		if ($vendor_row_after && isset($vendor_row_after->status) && (string)$vendor_row_after->status === (string)$new_status) {
			$updated = true;
		}
		if(!$updated && isset($vendor_row->status) && (string)$vendor_row->status === (string)$new_status){
			$updated = true;
		}

		if ($updated) {
			sync_vendor_product_status($admin_id, $new_status);
		}

		$doc_table = '';
		if ($this->db->table_exists('ec_vendor_verification')) {
			$doc_table = 'ec_vendor_verification';
		} elseif ($this->db->table_exists('ec_vendor_varification')) {
			$doc_table = 'ec_vendor_varification';
		}
		if ($doc_table && $this->db->field_exists('status', $doc_table)) {
			$vendor_col = $this->db->field_exists('vendor_id', $doc_table) ? 'vendor_id' : ($this->db->field_exists('login_id', $doc_table) ? 'login_id' : ($this->db->field_exists('admin_id', $doc_table) ? 'admin_id' : ''));
			$status_str = ($new_status === '1') ? 'approved' : (($new_status === '2') ? 'rejected' : 'pending');
			if ($vendor_col) {
				$row = $this->db
					->select('id')
					->from($doc_table)
					->where($vendor_col, (int)$admin_id)
					->order_by('id', 'DESC')
					->limit(1)
					->get()->row();
				$payload = array('status' => $status_str);
				if ($this->db->field_exists('admin_note', $doc_table)) {
					$payload['admin_note'] = ($status_str === 'rejected') ? $admin_note : '';
				}
				if ($this->db->field_exists('updated_at', $doc_table)) {
					$payload['updated_at'] = date('Y-m-d H:i:s');
				}
				if ($row && isset($row->id)) {
					$this->db->where('id', (int)$row->id);
					$this->db->update($doc_table, $payload);
				} else {
					$payload[$vendor_col] = (int)$admin_id;
					if ($this->db->field_exists('created_at', $doc_table)) {
						$payload['created_at'] = date('Y-m-d H:i:s');
					}
					$this->db->insert($doc_table, $payload);
				}
			}
		}

        $csrf_hash = $this->security->get_csrf_hash();
        if ($updated) {
            echo json_encode(['success' => 1, 'msg' => 'Vendor status updated successfully.', 'csrf_token' => $csrf_hash]);
        } else {
            echo json_encode(['success' => 0, 'msg' => 'Failed to update status.', 'csrf_token' => $csrf_hash]);
        }
    }

	public function ajax_upload_vendor_documents()
	{
		// Security Check: Super admin or sub-admin with vendor edit permission.
		if (!$this->_can_edit_vendor()) {
			echo json_encode(['success' => 0, 'msg' => 'You do not have permission for this action.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}

		$vendor_id = $this->input->post('vendor_id');
		if (!is_numeric($vendor_id) || (int)$vendor_id <= 0) {
			echo json_encode(['success' => 0, 'msg' => 'Invalid vendor.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}
		$vendor_id = (int)$vendor_id;

		$doc_table = '';
		if ($this->db->table_exists('ec_vendor_verification')) {
			$doc_table = 'ec_vendor_verification';
		} elseif ($this->db->table_exists('ec_vendor_varification')) {
			$doc_table = 'ec_vendor_varification';
		}
		if (!$doc_table) {
			echo json_encode(['success' => 0, 'msg' => 'Verification table not found.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}

		$vendor_col = $this->db->field_exists('vendor_id', $doc_table) ? 'vendor_id' : ($this->db->field_exists('login_id', $doc_table) ? 'login_id' : ($this->db->field_exists('admin_id', $doc_table) ? 'admin_id' : ''));
		if (!$vendor_col) {
			echo json_encode(['success' => 0, 'msg' => 'Invalid verification table schema.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}

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

		$doc_front_name = '';
		$doc_back_name = '';
		if (isset($_FILES['document_front']['name']) && $_FILES['document_front']['name'] != '') {
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('document_front')) {
				echo json_encode(['success' => 0, 'msg' => strip_tags($this->upload->display_errors()), 'csrf_token' => $this->security->get_csrf_hash()]);
				return;
			}
			$data_file = $this->upload->data();
			$doc_front_name = isset($data_file['file_name']) ? $data_file['file_name'] : '';
		}
		if (isset($_FILES['document_back']['name']) && $_FILES['document_back']['name'] != '') {
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('document_back')) {
				echo json_encode(['success' => 0, 'msg' => strip_tags($this->upload->display_errors()), 'csrf_token' => $this->security->get_csrf_hash()]);
				return;
			}
			$data_file = $this->upload->data();
			$doc_back_name = isset($data_file['file_name']) ? $data_file['file_name'] : '';
		}
		if ($doc_front_name === '' && $doc_back_name === '') {
			echo json_encode(['success' => 0, 'msg' => 'Please select at least one document to upload.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}

		$row = $this->db
			->select('id')
			->from($doc_table)
			->where($vendor_col, (int)$vendor_id)
			->order_by('id', 'DESC')
			->limit(1)
			->get()->row();

		$payload = array();
		if ($doc_front_name) {
			if ($this->db->field_exists('document_front', $doc_table)) {
				$payload['document_front'] = $doc_front_name;
			} elseif ($this->db->field_exists('doc_front', $doc_table)) {
				$payload['doc_front'] = $doc_front_name;
			}
		}
		if ($doc_back_name) {
			if ($this->db->field_exists('document_back', $doc_table)) {
				$payload['document_back'] = $doc_back_name;
			} elseif ($this->db->field_exists('doc_back', $doc_table)) {
				$payload['doc_back'] = $doc_back_name;
			}
		}
		if ($this->db->field_exists('status', $doc_table)) {
			$payload['status'] = 'pending';
		}
		if ($this->db->field_exists('admin_note', $doc_table)) {
			$payload['admin_note'] = '';
		}
		if ($this->db->field_exists('updated_at', $doc_table)) {
			$payload['updated_at'] = date('Y-m-d H:i:s');
		}

		if ($row && isset($row->id)) {
			$this->db->where('id', (int)$row->id);
			$this->db->update($doc_table, $payload);
		} else {
			$payload[$vendor_col] = (int)$vendor_id;
			if ($this->db->field_exists('created_at', $doc_table)) {
				$payload['created_at'] = date('Y-m-d H:i:s');
			}
			$this->db->insert($doc_table, $payload);
		}

		echo json_encode(['success' => 1, 'msg' => 'Documents uploaded.', 'csrf_token' => $this->security->get_csrf_hash()]);
	}

	public function ajax_toggle_vendor_enabled()
	{
		// Security Check: Super admin or sub-admin with vendor edit permission.
		if (!$this->_can_edit_vendor()) {
			echo json_encode(['success' => 0, 'msg' => 'You do not have permission for this action.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}
		if (!$this->db->field_exists('is_enabled', 'ec_vendor')) {
			echo json_encode(['success' => 0, 'msg' => 'Vendor enable/disable is not configured (missing ec_vendor.is_enabled).', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}
		$vendor_id = $this->input->post('vendor_id');
		$enabled = $this->input->post('enabled');
		if (!is_numeric($vendor_id) || ((string)$enabled !== '0' && (string)$enabled !== '1')) {
			echo json_encode(['success' => 0, 'msg' => 'Invalid data provided.', 'csrf_token' => $this->security->get_csrf_hash()]);
			return;
		}
		$vendor_id = (int)$vendor_id;
		$ok = $this->Query_model->update_data('ec_vendor', array('is_enabled' => (string)$enabled), array('vendor_id' => $vendor_id));
		if(!$ok){
			$row = $this->db->select('vendor_id, is_enabled')->from('ec_vendor')->where('vendor_id', $vendor_id)->limit(1)->get()->row();
			if($row && isset($row->is_enabled) && (string)$row->is_enabled === (string)$enabled){
				$ok = true;
			}
		}
		echo json_encode(['success' => $ok ? 1 : 0, 'msg' => $ok ? 'Updated.' : 'Failed to update.', 'csrf_token' => $this->security->get_csrf_hash()]);
	}


}
