<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advertisement extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('advertisement_model');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->library('upload');
        $this->load->library('breadcrumbs'); // Ensure Breadcrumbs lib is loaded
    
        $this->check_module_permission('advertisement');
    }

    public function index()
    {
        // 1. Load Language Data
        $page_lang = get_page_language_data('admin_page_lang');
        
        $data['title'] = $page_lang->manage_advertisements; // Dynamic Title
        $data['TYPE'] = 'admin'; 
        $data['csrf'] = [
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash()
        ];
        $data['page_count'] = 25;

        // 2. Dynamic Breadcrumbs
        $crumbs = array(
            $page_lang->home => base_url('admin/dashboard'),
            $page_lang->manage_advertisements => ''
        );
        $data['breadcrumbs'] = $this->breadcrumbs->show($crumbs);

        $this->_render_page('admin/advertisement/advertisement', $data);
    }
    
    public function add()
    {
        if (!$_POST) {
            $this->session->unset_userdata('success');
            $this->session->unset_userdata('error');
        }

        $this->form_validation->set_rules('title', 'Title', 'required|trim');
        $this->form_validation->set_rules('tag', 'Tag', 'trim');
        $this->form_validation->set_rules('link', 'Link URL', 'trim');
        
        // Load Language
        $page_lang = get_page_language_data('admin_page_lang');

        // Dynamic Breadcrumbs
        $crumbs = array(
            $page_lang->home => base_url('admin/dashboard'),
            $page_lang->manage_advertisements => base_url('admin/advertisement'),
            $page_lang->add_new => ''
        );
        $breadcrumbs_html = $this->breadcrumbs->show($crumbs);
        
        if ($this->form_validation->run() == FALSE)
        {
            $data['title'] = $page_lang->add_new_advertisement;
            $data['TYPE'] = 'admin';
            $data['csrf'] = [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash()
            ];
            $data['breadcrumbs'] = $breadcrumbs_html; 
            $data['form_action'] = base_url('admin/advertisement/add');

            $this->_render_page('admin/advertisement/1post_form', $data);
        }
        else
        {
            $uploadDir = rtrim(FCPATH, '/\\') . '/uploads/advertisements/';
            $uploadDir = str_replace('\\', '/', $uploadDir);
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
                @chmod($uploadDir, 0755);
            }
            if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                $data['title'] = $page_lang->add_new_advertisement;
                $data['TYPE'] = 'admin';
                $data['csrf'] = [
                    'name' => $this->security->get_csrf_token_name(),
                    'hash' => $this->security->get_csrf_hash()
                ];
                $data['upload_error'] = 'The upload destination folder does not appear to be writable.';
                $data['breadcrumbs'] = $breadcrumbs_html;
                $data['form_action'] = base_url('admin/advertisement/add');

                $this->_render_page('admin/advertisement/1post_form', $data);
                return;
            }
            $config['upload_path']   = $uploadDir;
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size']      = 2048;
            $config['encrypt_name']  = TRUE; // Better to encrypt name to avoid issues

            $this->upload->initialize($config);

            if ( ! $this->upload->do_upload('userfile'))
            {
                $data['title'] = $page_lang->add_new_advertisement;
                $data['TYPE'] = 'admin';
                $data['csrf'] = [
                    'name' => $this->security->get_csrf_token_name(),
                    'hash' => $this->security->get_csrf_hash()
                ];
                $data['upload_error'] = $this->upload->display_errors();
                $data['breadcrumbs'] = $breadcrumbs_html;
                $data['form_action'] = base_url('admin/advertisement/add');

                $this->_render_page('admin/advertisement/1post_form', $data);
            }
            else
            {
                $upload_data = $this->upload->data();
                $image_file_name = $upload_data['file_name'];

                $save_data = [
                    'advertisement_name'  => $this->input->post('title'),
                    'advertisement_link'  => $this->input->post('link'),
                    'advertisement_image' => $image_file_name,
                ];
                if ($this->db->field_exists('advertisement_tag', 'ec_advertisement')) {
                    $save_data['advertisement_tag'] = $this->input->post('tag');
                }
                if ($this->db->field_exists('created_at', 'ec_advertisement')) {
                    $save_data['created_at'] = date('Y-m-d H:i:s');
                }
                
                if ($this->advertisement_model->insert($save_data)) {
                    $this->session->set_flashdata('error', '');
                    $this->session->set_flashdata('success', 'Advertisement added successfully!');
                } else {
                    $this->session->set_flashdata('success', '');
                    $this->session->set_flashdata('error', 'Failed to add advertisement.');
                }
                
                redirect('admin/advertisement');
            }
        }
    }


    public function update($id)
    {
        if (!$_POST) {
            $this->session->unset_userdata('success');
            $this->session->unset_userdata('error');
        }

        $advertisement = $this->advertisement_model->get_by_id($id);
        if (!$advertisement) {
            $this->session->set_flashdata('error', 'Advertisement not found.');
            redirect('admin/advertisement');
        }

        $this->form_validation->set_rules('title', 'Title', 'required|trim');
        $this->form_validation->set_rules('tag', 'Tag', 'trim');
        $this->form_validation->set_rules('link', 'Link URL', 'trim');
        
        // Load Language
        $page_lang = get_page_language_data('admin_page_lang');
        
        if ($this->form_validation->run() == FALSE)
        {
            $data['title'] = $page_lang->edit; // You need to add 'edit' key later
            $data['TYPE'] = 'admin';
            $data['csrf'] = [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash()
            ];
            
            $data['advertisement'] = $advertisement; 
            $data['form_action'] = base_url('admin/advertisement/update/' . $id);
            
            // Dynamic Breadcrumbs
            $crumbs = array(
                $page_lang->home => base_url('admin/dashboard'),
                $page_lang->manage_advertisements => base_url('admin/advertisement'),
                $page_lang->edit => ''
            );
            $data['breadcrumbs'] = $this->breadcrumbs->show($crumbs);

            $this->_render_page('admin/advertisement/1post_form', $data);
        }
        else
        {
            $image_file_name = $this->input->post('old_image'); 

            if (isset($_FILES['userfile']) && !empty($_FILES['userfile']['name']))
            {
                $uploadDir = rtrim(FCPATH, '/\\') . '/uploads/advertisements/';
                $uploadDir = str_replace('\\', '/', $uploadDir);
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                    @chmod($uploadDir, 0755);
                }
                if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                    $this->session->set_flashdata('success', '');
                    $this->session->set_flashdata('error', 'The upload destination folder does not appear to be writable.');
                    redirect('admin/advertisement/update/' . $id);
                    return;
                }
                $config['upload_path']   = $uploadDir;
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size']      = 2048;
                $config['encrypt_name']  = TRUE;
                $this->upload->initialize($config);

                if ( ! $this->upload->do_upload('userfile'))
                {
                    $this->session->set_flashdata('success', '');
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('admin/advertisement/update/' . $id);
                    return;
                }
                
                $upload_data = $this->upload->data();
                $image_file_name = $upload_data['file_name'];
            }

            $save_data = [
                'advertisement_name'  => $this->input->post('title'),
                'advertisement_link'  => $this->input->post('link'),
                'advertisement_image' => $image_file_name
            ];
            if ($this->db->field_exists('advertisement_tag', 'ec_advertisement')) {
                $save_data['advertisement_tag'] = $this->input->post('tag');
            }
            
            if ($this->advertisement_model->update($id, $save_data)) {
                $this->session->set_flashdata('error', '');
                $this->session->set_flashdata('success', 'Advertisement updated successfully!');
            } else {
                $this->session->set_flashdata('success', '');
                $this->session->set_flashdata('error', 'Failed to update advertisement.');
            }
            
            redirect('admin/advertisement');
        }
    }

    public function index_ajax_post()
    {
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $filter = $this->input->post('filter');

        $advertisements = $this->advertisement_model->get_paginated_data($length, $start, $filter);
        $total_records = $this->advertisement_model->count_all();
        $filtered_records = $this->advertisement_model->count_filtered($filter);

        $data = array();
        foreach ($advertisements as $ad) {
            $row = array();
            $row['advertisement_id'] = $ad->id;
            $row['title'] = $ad->title;
            // Ensure this path matches your upload folder
            $row['image'] = '<img src="'.base_url('uploads/advertisements/'.$ad->image).'" alt="'.$ad->title.'" style="width: 100px; height: auto;" />';
            $row['link'] = '<a href="'.$ad->link.'" target="_blank">View Link</a>';
            $row['is_visible'] = isset($ad->is_visible) ? (int)$ad->is_visible : 0;
            $row['action'] = ''; // JS handles the buttons
            $data[] = $row;
        }

        $output = array(
            "draw"            => intval($this->input->post('draw')),
            "recordsTotal"    => $total_records,
            "recordsFiltered" => $filtered_records,
            "data"            => $data,
        );

        echo json_encode($output);
    }

    public function update_ajax_visible()
    {
        $id = (int)$this->input->post('id');
        $current = (int)$this->input->post('is_visible');

        $next = $current ? 0 : 1;
        $ok = $this->advertisement_model->update_visible($id, $next);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $ok ? true : false, 'is_visible' => $next]));
    }

    public function del()
    {
        $id = $this->input->post('id');
        if ($this->advertisement_model->delete_by_id($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function ajax_set_visible_selected()
    {
        $ids = $this->input->post('advertisement_ids');

        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        $ok = false;
        $message = '';
        try {
            $ok = $this->advertisement_model->set_visible_selected($ids);
            if (!$ok) {
                $message = 'Unable to update visibility.';
            }
        } catch (Exception $e) {
            $ok = false;
            $message = 'Visibility column is missing.';
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $ok ? true : false, 'message' => $message]));
    }
}