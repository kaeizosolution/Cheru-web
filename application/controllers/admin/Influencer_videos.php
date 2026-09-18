<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Influencer_videos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Influencer_video_model');
        $this->load->helper(['url', 'form', 'file']);
        $this->load->library(['form_validation', 'upload', 'session']);
    }

    public function index() {
        $data['videos'] = $this->Influencer_video_model->get_all();
        $this->load->view('/admin/influencer/list', $data);
    }

    public function upload() {
        // form validation
        $this->form_validation->set_rules('title','Title','trim|required|max_length[255]');
        $this->form_validation->set_rules('description','Description','trim');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('influencer_videos/upload');
            return;
        }

        // upload config
        $upload_path = FCPATH . 'uploads/videos/';
        if (!is_dir($upload_path)) mkdir($upload_path, 0755, true);

        $config = [
            'upload_path'   => $upload_path,
            'allowed_types' => 'mp4|mov|webm|mkv|avi',
            'max_size'      => 102400, // in KB (100MB)
            'file_ext_tolower' => TRUE,
            'encrypt_name'  => TRUE,
        ];

        $this->upload->initialize($config);

        if ( ! $this->upload->do_upload('video_file')) {
            $data['error'] = $this->upload->display_errors('', '');
            $this->load->view('influencer_videos/upload', $data);
            return;
        }

        $file = $this->upload->data();
        $filename = $file['file_name'];

        // optional: create thumbnail using ffmpeg if available
        $thumbnail_name = null;
        $thumbs_path = FCPATH . 'uploads/thumbnails/';
        if (!is_dir($thumbs_path)) mkdir($thumbs_path, 0755, true);

        $ffmpeg = trim(shell_exec('command -v ffmpeg')); // path to ffmpeg or empty
        if ($ffmpeg) {
            // create single thumbnail at 2 seconds
            $thumb_file = uniqid('thumb_') . '.jpg';
            $video_path = escapeshellarg($upload_path . $filename);
            $thumb_path = escapeshellarg($thumbs_path . $thumb_file);
            $cmd = "$ffmpeg -ss 00:00:02 -i $video_path -frames:v 1 -q:v 2 $thumb_path 2>&1";
            @shell_exec($cmd);
            if (file_exists($thumbs_path . $thumb_file)) $thumbnail_name = $thumb_file;
        }

        $insert = [
            'title' => $this->input->post('title', true),
            'description' => $this->input->post('description', true),
            'filename' => $filename,
            'thumbnail' => $thumbnail_name,
            'uploader_id' => $this->session->userdata('user_id') ?: null,
        ];

        $id = $this->Influencer_video_model->insert($insert);

        $this->session->set_flashdata('success', 'Video uploaded successfully.');
        redirect('influencer/videos/view/' . $id);
    }

    public function view($id) {
        $video = $this->Influencer_video_model->get($id);
        if (!$video) show_404();
        $data['video'] = $video;
        $this->load->view('influencer_videos/view', $data);
    }

    public function delete($id) {
        // basic delete with no auth checks (add permission checks in production)
        $video = $this->Influencer_video_model->get($id);
        if (!$video) {
            $this->session->set_flashdata('error', 'Video not found.');
            redirect('influencer/videos');
        }

        // unlink files
        @unlink(FCPATH . 'uploads/videos/' . $video->filename);
        if ($video->thumbnail) @unlink(FCPATH . 'uploads/thumbnails/' . $video->thumbnail);

        $this->Influencer_video_model->delete($id);
        $this->session->set_flashdata('success', 'Video deleted.');
        redirect('influencer/videos');
    }
}

