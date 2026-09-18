<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<style>
    :root {
        --primary: #6366f1;
        --secondary: #64748b;
        --dark: #1e293b;
        --light-gray: #f8fafc;
        --border-color: #e2e8f0;
        --danger: #ef4444;
    }

    /* --- CARD --- */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: visible !important;
        background: #fff;
    }
    
    .card-header-custom {
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        border-radius: 16px 16px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title-custom {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
    }

    /* --- CLOSE BUTTON --- */
    .btn-close-card {
        color: #94a3b8;
        font-size: 20px;
        text-decoration: none;
        width: 30px; height: 30px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%;
        transition: all 0.3s;
    }
    .btn-close-card:hover { background: #f1f5f9; color: #ef4444; transform: rotate(90deg); }

    /* --- FORM ELEMENTS --- */
    .form-group label {
        font-weight: 600;
        color: #475569;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 10px 15px;
        font-size: 14px;
        color: var(--dark);
        transition: all 0.2s;
        height: auto;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    textarea.form-control { min-height: 150px; }

    /* --- FILE UPLOAD (Modern) --- */
    .file-upload-wrapper {
        position: relative;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        padding: 20px;
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
        top: 0; left: 0;
        opacity: 0;
        cursor: pointer;
    }

    .file-upload-text { font-size: 13px; color: #64748b; font-weight: 500; }
    
    /* --- TOGGLE SWITCH --- */
    .switch-container { display: flex; align-items: center; margin-top: 10px; }
    .switch { position: relative; display: inline-block; width: 50px; height: 26px; margin-right: 15px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--primary); }
    input:checked + .slider:before { transform: translateX(24px); }
    .switch-label { font-weight: 600; color: var(--dark); margin: 0; cursor: pointer; }

    /* --- BUTTONS --- */
    .btn-submit-custom {
        background-color: var(--primary);
        color: #fff !important;
        border: none;
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        transition: all 0.2s;
    }
    .btn-submit-custom:hover {
        background-color: #4f46e5;
        transform: translateY(-2px);
    }

    /* FIXED BACK BUTTON */
    .btn-back-custom {
        background-color: #000000ff !important;
        color: #000 !important; /* Forced Black */
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 600;
        margin-right: 10px;
        transition: all 0.2s;
        text-decoration: none !important;
        display: inline-block;
    }
    .btn-back-custom:hover {background-color: #4f46e5; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3);
        
    }
    
    /* --- ALERTS --- */
    .alert { border-radius: 8px; border: none; font-size: 14px; margin-bottom: 20px; }
    .alert-danger { background-color: #fee2e2; color: #b91c1c; }
    .alert-success { background-color: #dcfce7; color: #15803d; }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->page; ?></h3>
                </div>
                <?= $breadcrumbs ?> 
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row justify-content-center">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    
                    <?php if (($this->session->flashdata('error')) || validation_errors()!='') { ?>
                    <div class="alert alert-danger m-3 rounded">
                        <?= validation_errors();?>
                        <?= $this->session->flashdata('error')?>
                    </div>
                    <?php } ?>

                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success m-3 rounded">
                        <?= $this->session->flashdata('success')?>
                    </div>
                    <?php } ?>

                    <form method="post" class="card addpost" id="postForm" action="<?php echo "/$TYPE/page/$action/$page_id"; ?>" enctype="multipart/form-data" autocomplete='off'>
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">
                            <?= ($action == 'add') ? 'Add New Page' : 'Edit Page'; ?>
                        </h5>
                        <a href="/<?= $TYPE; ?>/page" class="btn-close-card" title="Close"><i class="fa fa-times"></i></a>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label><?= $page_lang->name; ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="post_title" class="form-control" value="<?= isset($post_title) ? $post_title : '' ?>" placeholder="Enter Title" required>
                                    <?php if(isset($post_title)) { ?>
                                        <small class="text-muted mt-1 d-block">Permalink: <a target="_blank" href="<?php echo site_url('/page/').$post_slug; ?>"><?php echo isset($post_slug) ? $post_slug : '' ?></a></small>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label>Image (Size: 515X512 50KB)</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="featured_image" id="featured_image">
                                    <div class="file-upload-text">
                                        <i class="fa fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 5px;"></i><br>
                                        Upload Image
                                    </div>
                                </div>
                                <label for="featured_image1" id="img_error" style="display:none; color:var(--danger); font-size:12px; margin-top:5px;" generated="true" class="error">Please enter a valid image (max 50kb).</label>
                            </div>

                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <label>Body</label>
                                    <textarea name="post_content" id="post_content" rows="8" class="form-control" placeholder="Write Something..."><?= isset($post_content) ? $post_content : '' ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label>Footer Section</label>
                                    <select class="form-control" name="footer_section">
                                        <?php $fs = isset($footer_section) ? (string)$footer_section : ''; ?>
                                        <option value="" <?= ($fs === '') ? 'selected' : '' ?>>None</option>
                                        <option value="about" <?= ($fs === 'about') ? 'selected' : '' ?>>About Cheru</option>
                                        <option value="support" <?= ($fs === 'support') ? 'selected' : '' ?>>Customer Support</option>
                                        <option value="services" <?= ($fs === 'services') ? 'selected' : '' ?>>Services</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-4">
                                <label><?= $page_lang->status; ?></label>
                                <div class="switch-container">
                                    <?php 
                                        $checked = (isset($enabled) && $enabled == '1') ? 'checked' : '' ;
                                        // Default checked for add
                                        if($action == 'add') $checked = 'checked';
                                    ?>
                                    <label class="switch">
                                        <input type="checkbox" name="enabled" value="1" <?= $checked ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <label class="switch-label">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right bg-white border-top p-4" style="border-radius: 0 0 16px 16px;">
                        <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                        <a href="/<?= $TYPE?>/page" class="btn btn-back-custom"><?= $page_lang->back; ?></a>
                        <button type="submit" class="btn btn-submit-custom"><?= $page_lang->submit; ?></button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image Validation Logic Preserved
$("#postForm").submit(function(e){
    $("#img_error").hide();
    var flag=0;
    // Check if file input exists and has files
    if($("#featured_image").length > 0 && $("#featured_image").get(0).files.length > 0) {
        var files = $("#featured_image").get(0).files;
        var img_name = $('#featured_image').val();
        
        if(img_name!=''){
            var ext = $('#featured_image').val().split('.').pop().toLowerCase();
            if($.inArray(ext, ['gif','png','jpg','jpeg','pdf']) == -1){
                flag=1;
            }
            for(var i=0; i<files.length;i++) {
                var file = files[i];
                var size = file.size;
                if(size>600000){ // 600KB limit from original code (comment said 50kb but code said 600000 bytes which is ~600kb)
                    flag=1;
                }
            }
            if(flag==1){
                $("#img_error").show();
                return false; // Prevent submit
            }
        }
    }
});
</script>

<script src="<?= base_url('assets/plugins/vendor/ckeditor/ckeditor.js'); ?>"></script>
<script>
if (window.CKEDITOR && document.getElementById('post_content')) {
    if (CKEDITOR.instances && CKEDITOR.instances.post_content) {
        CKEDITOR.instances.post_content.destroy(true);
    }
    CKEDITOR.replace('post_content', {
        allowedContent: true,
        removePlugins: 'elementspath',
        resize_enabled: true,
        autoParagraph: false,
        enterMode: CKEDITOR.ENTER_BR,
        shiftEnterMode: CKEDITOR.ENTER_BR,
        toolbar: [
            { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
            { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ] },
            { name: 'insert', items: [ 'Table' ] },
            { name: 'links', items: [ 'Link', 'Unlink' ] },
            { name: 'tools', items: [ 'RemoveFormat', 'Source' ] }
        ]
    });
}
</script>