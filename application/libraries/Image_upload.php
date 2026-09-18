<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Image_upload {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('upload');
    }

    public function upload_multiple($field, $folder='products')
    {
        $upload_path = FCPATH.'uploads/'.$folder.'/';

        if(!is_dir($upload_path)){
            mkdir($upload_path,0777,true);
        }

        $files = $_FILES[$field];
        $uploaded = [];

        $count = count($files['name']);

        for($i=0;$i<$count;$i++)
        {
            $_FILES['temp']['name']     = $files['name'][$i];
            $_FILES['temp']['type']     = $files['type'][$i];
            $_FILES['temp']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['temp']['error']    = $files['error'][$i];
            $_FILES['temp']['size']     = $files['size'][$i];

            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp|avif',
                'encrypt_name'  => TRUE,
                'max_size'      => 20240000
            ];

            $this->CI->upload->initialize($config);

            if($this->CI->upload->do_upload('temp'))
            {
                $data = $this->CI->upload->data();
                $uploaded[] = $data['file_name'];
            }
        }

        return $uploaded;
    }
}
