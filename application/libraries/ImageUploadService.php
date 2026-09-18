<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ImageUploadService
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('upload');
    }

    public function uploadSingle($inputName, $folder = 'uploads')
    {
        $path = FCPATH . "uploads/{$folder}/";

        if (!is_dir($path)) mkdir($path, 0777, true);

        $config = [
            'upload_path'   => $path,
            'allowed_types' => '*',
            'max_size'      => 2048,
            'file_name'     => uniqid('img_') . time(),
        ];

        $this->CI->upload->initialize($config);

        if (!$this->CI->upload->do_upload($inputName)) {
			$error = $this->CI->upload->display_errors('', '');
    		//echo "Upload error: " . $error; // temporary for debugging
            return false;
        }

        $data = $this->CI->upload->data();
        return $data['file_name'];
    }

    public function uploadMultipleOld($files, $folder = 'uploads')
    {
        $uploaded = [];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $_FILES['multi_file']['name']     = $files['name'][$i];
            $_FILES['multi_file']['type']     = $files['type'][$i];
            $_FILES['multi_file']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['multi_file']['error']    = $files['error'][$i];
            $_FILES['multi_file']['size']     = $files['size'][$i];

            $file = $this->uploadSingle('multi_file', $folder);
            if ($file) {
                $uploaded[] = $file;
            }
        }

        return $uploaded;
    }

	public function uploadMultiple($files, $folder = 'uploads')
	{
			$uploaded = [];
			$fileCount = count($files['name']);

			for ($i = 0; $i < $fileCount; $i++) {
					// Check if this index is a nested array (e.g. attribute images)
					if (is_array($files['name'][$i])) {
							// Nested files: handle each nested file one by one
							$nestedCount = count($files['name'][$i]);

							for ($j = 0; $j < $nestedCount; $j++) {
									$_FILES['multi_file']['name']     = $files['name'][$i][$j];
									$_FILES['multi_file']['type']     = $files['type'][$i][$j];
									$_FILES['multi_file']['tmp_name'] = $files['tmp_name'][$i][$j];
									$_FILES['multi_file']['error']    = $files['error'][$i][$j];
									$_FILES['multi_file']['size']     = $files['size'][$i][$j];

									$file = $this->uploadSingle('multi_file', $folder);
									if ($file) {
											$uploaded[] = $file;
									}
							}
					} else {
							// Flat files: process as usual
							$_FILES['multi_file']['name']     = $files['name'][$i];
							$_FILES['multi_file']['type']     = $files['type'][$i];
							$_FILES['multi_file']['tmp_name'] = $files['tmp_name'][$i];
							$_FILES['multi_file']['error']    = $files['error'][$i];
							$_FILES['multi_file']['size']     = $files['size'][$i];

							$file = $this->uploadSingle('multi_file', $folder);
							if ($file) {
									$uploaded[] = $file;
							}
					}
			}

			return $uploaded;
	}


    public function uploadAttributeImagesByIndex($files, $index, $folder = 'attributes')
    {
        $uploaded = [];
        $fileCount = count($files['name'][$index] ?? []);

        for ($i = 0; $i < $fileCount; $i++) {
            $_FILES['attr_file']['name']     = $files['name'][$index][$i];
            $_FILES['attr_file']['type']     = $files['type'][$index][$i];
            $_FILES['attr_file']['tmp_name'] = $files['tmp_name'][$index][$i];
            $_FILES['attr_file']['error']    = $files['error'][$index][$i];
            $_FILES['attr_file']['size']     = $files['size'][$index][$i];

            $file = $this->uploadSingle('attr_file', $folder);
            if ($file) {
                $uploaded[] = $file;
            }
        }

        return $uploaded;
    }
}


