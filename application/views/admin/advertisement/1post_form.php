<?php 
// Load Language Data
$page_lang = get_page_language_data('admin_page_lang'); 
?>

<style>
    :root {
        --primary: #6366f1;
        --secondary: #64748b;
        --dark: #1e293b;
        --light-gray: #f8fafc;
        --border-color: #e2e8f0;
    }

    /* --- CARD --- */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .card-header-custom {
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
    }

    .card-title-custom {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
    }

    /* --- FORM ELEMENTS --- */
    .form-group label {
        font-weight: 600;
        color: #475569;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 12px 15px;
        font-size: 14px;
        color: var(--dark);
        transition: all 0.2s;
        height: auto;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* --- FILE UPLOAD (Modern) --- */
    .file-upload-wrapper {
        position: relative;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        transition: all 0.2s;
        background: #fafafa;
        cursor: pointer;
    }
    
    .file-upload-wrapper:hover {
        border-color: var(--primary);
        background: #f0f4ff;
    }

    .file-upload-wrapper input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }

    .file-upload-icon {
        font-size: 40px;
        color: #94a3b8;
        margin-bottom: 10px;
    }
    
    .file-upload-text {
        font-size: 14px;
        color: #64748b;
        font-weight: 500;
    }

    /* --- PREVIEW IMAGE --- */
    .current-image-preview {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 5px;
        margin-bottom: 15px;
        max-width: 100%;
        width: 200px;
        height: auto;
    }

    /* --- BUTTONS --- */
    .btn-submit-custom {
        background-color: var(--primary);
        color: #fff !important;
        border: none;
        border-radius: 8px;
        padding: 12px 30px;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        transition: all 0.2s;
    }
    .btn-submit-custom:hover {
        background-color: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
    }

    /* FIX: Force Dark Text for Cancel Button (Overrides global white text rule) */
    .btn-cancel-custom {
        background-color: #000000ff !important; /* Slightly darker gray background */
        color: #1e293b !important; /* Dark text color force */
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        padding: 12px 25px;
        font-weight: 600;
        margin-left: 10px;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .btn-cancel-custom:hover { background-color: #4f46e5; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3); 
    }
    .btn-cancel-custom i {
        color: #ef4444 !important; /* Red Icon */
        margin-right: 5px;
    }
    
    /* --- ALERTS --- */
    .alert { border-radius: 8px; border: none; font-size: 14px; }
    .alert-danger { background-color: #fee2e2; color: #b91c1c; }
    .alert-success { background-color: #dcfce7; color: #15803d; }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $title ?></h3>
                </div>
                <?= $breadcrumbs ?> 
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-sm-12 col-md-12"> 
                <div class="card">
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom"><?= $page_lang->advertisement_details ?? 'Advertisement Details'; ?></h5>
                    </div>

                    <div class="card-body p-4">
                        
                        <?php if (isset($upload_error)): ?>
                            <div class="alert alert-danger mb-4">
                                <i class="fa fa-exclamation-circle mr-2"></i>
                                <strong><?= $page_lang->upload_failed; ?>:</strong> <?php echo $upload_error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (validation_errors()): ?>
                            <div class="alert alert-danger mb-4">
                                <?php echo validation_errors(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger mb-4"><?= $this->session->flashdata('error') ?></div>
                        <?php endif; ?>

                        <form action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
                            
                            <input type="hidden" name="<?= $csrf['name']; ?>" value="<?= $csrf['hash']; ?>">

                            <div class="form-group mb-4">
                                <label for="title"><?= $page_lang->title; ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #e2e8f0;"><i class="fa fa-heading text-muted"></i></span>
                                    </div>
                                    <input type="text" class="form-control border-left-0" id="title" name="title" 
                                           style="border-radius: 0 8px 8px 0;"
                                           value="<?php echo set_value('title', isset($advertisement) ? $advertisement->advertisement_name : ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="tag"><?= $page_lang->tag ?? 'Tag'; ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #e2e8f0;"><i class="fa fa-tag text-muted"></i></span>
                                    </div>
                                    <input type="text" class="form-control border-left-0" id="tag" name="tag" 
                                           style="border-radius: 0 8px 8px 0;"
                                           value="<?php echo set_value('tag', isset($advertisement) ? ($advertisement->advertisement_tag ?? '') : ''); ?>">
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="link"><?= $page_lang->link_url; ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #e2e8f0;"><i class="fa fa-link text-muted"></i></span>
                                    </div>
                                    <input type="text" class="form-control border-left-0" id="link" name="link" placeholder="e.g., https://www.example.com" 
                                           style="border-radius: 0 8px 8px 0;"
                                           value="<?php echo set_value('link', isset($advertisement) ? $advertisement->advertisement_link : ''); ?>">
                                </div>
                            </div>

                            <div class="form-group mb-5">
                                <label for="image"><?= $page_lang->advertisement_image; ?></label>
                                
                                <?php if (isset($advertisement) && $advertisement->advertisement_image): ?>
                                    <div class="mb-3 p-3 bg-light rounded text-center">
                                        <p class="text-muted small mb-2"><?= $page_lang->current_image; ?></p>
                                        <img src="<?php echo base_url('uploads/advertisements/' . $advertisement->advertisement_image); ?>" 
                                             class="current-image-preview shadow-sm">
                                        <input type="hidden" name="old_image" value="<?php echo $advertisement->advertisement_image; ?>">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="file-upload-wrapper">
                                    <input type="file" id="image" name="userfile" onchange="updateFileName(this)">
                                    <div class="file-upload-icon"><i class="fa fa-cloud-upload-alt"></i></div>
                                    <div class="file-upload-text" id="file-name-display">
                                        Drag & drop image here or <strong>browse</strong>
                                    </div>
                                    <small class="d-block text-muted mt-2">Supported formats: JPG, PNG, JPEG</small>
                                </div>
                            </div>

                            <div class="form-group text-right mt-4" style="border-top: 1px solid #f1f5f9; padding-top: 20px;">
                                <button type="submit" class="btn btn-submit-custom">
                                    <i class="fa fa-check mr-2"></i> <?= $page_lang->submit; ?>
                                </button>
                                
                                <a href="<?php echo base_url($TYPE . '/advertisement'); ?>" class="btn btn-cancel-custom">
                                    <i class="fa fa-times"></i> <?= $page_lang->cancel; ?>
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple script to show selected file name in custom upload box
function updateFileName(input) {
    if (input.files && input.files[0]) {
        var fileName = input.files[0].name;
        document.getElementById('file-name-display').innerHTML = 'Selected: <strong>' + fileName + '</strong>';
    } else {
        document.getElementById('file-name-display').innerHTML = 'Drag & drop image here or <strong>browse</strong>';
    }
}
</script>