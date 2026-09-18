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
    
    /* --- IMAGE PREVIEW --- */
    .img-preview-box {
        position: relative;
        display: inline-block;
        margin-top: 10px;
        border: 1px solid #e2e8f0;
        padding: 5px;
        border-radius: 8px;
    }
    .img-preview-box img { border-radius: 4px; }
    .btn-delete-img {
        position: absolute;
        top: -10px; right: -10px;
        background: #fff;
        border: 1px solid #fee2e2;
        color: var(--danger);
        border-radius: 50%;
        width: 25px; height: 25px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .btn-delete-img:hover { background: var(--danger); color: #fff; }

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
        border: none; border-radius: 8px;
        padding: 10px 25px; font-weight: 600;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        transition: all 0.2s;
    }
    .btn-submit-custom:hover { background-color: #4f46e5; transform: translateY(-2px); }

    .btn-back-custom {
        background-color: #000000ff !important;
        color: #000 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 600;
        margin-right: 10px;
        transition: all 0.2s;
        text-decoration: none !important;
        display: inline-block;
    }
    .btn-back-custom:hover {background-color: #4f46e5; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3); }
    
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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->brand; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row justify-content-center">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    
                    <?php if (($this->session->flashdata('error')) || validation_errors()!=''){ ?>
                    <div class="alert alert-danger m-3 rounded">
                        <?= validation_errors();?>
                        <?= $this->session->flashdata('error') ?>
                    </div>
                    <?php } ?>

                    <?php if($this->session->flashdata('success')){ ?>
                    <div class="alert alert-success m-3 rounded">
                        <?= $this->session->flashdata('success') ?>
                    </div>
                    <?php } ?>

                    <?php echo form_open_multipart("/$TYPE/brand/".strtolower($action)."/$brand_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">
                            <?= ($action == 'add') ? 'Add New Brand' : 'Edit Brand'; ?>
                        </h5>
                        <a href="/<?= $TYPE; ?>/brand" class="btn-close-card" title="Close"><i class="fa fa-times"></i></a>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label>Category <span class="text-danger">*</span></label>
                                    <select name="cat_id" id="parent_category_combo" class="select2 form-control main_category_combo" required data-parsley-errors-container="#main_category_error">
                                        <option value="">Select Category</option>
                                        <?php
                                            if($parent_categories) {
                                                foreach($parent_categories as $row_wise) {
                                                    $id = $row_wise['id'];
                                                    $cat_name = $row_wise['name'];
                                                    $selected = $id == $cat_id ? 'selected' : '';
                                                    echo "<option value='$id' $selected>$cat_name</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                    <div id="main_category_error"></div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= isset($name) ? $name : '' ?>" placeholder="Brand Name" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label><?= $page_lang->status; ?></label>
                                <div class="switch-container">
                                    <?php 
                                        $checked = ($action == 'add') ? 'checked' : '' ;
                                        $checked = isset($status) && $status == '1' ? 'checked' : '' ;
                                    ?>
                                    <label class="switch">
                                        <input type="checkbox" name="status" id="status" <?= $checked?> value="1">
                                        <span class="slider"></span>
                                    </label>
                                    <label for="status" class="switch-label">Active</label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer text-right bg-white border-top p-4">
                        <a href="/<?= $TYPE; ?>/brand" class="btn btn-back-custom"><?= $page_lang->back; ?></a>
                        <button type="submit" class="btn btn-submit-custom"><?= $page_lang->submit; ?></button>
                    </div>

                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Datepicker logic preserved just in case
$('.fancy_date').daterangepicker({
    autoUpdateInput: false,
    minDate: moment().format('YYYY-MM-DD H:mm'),
    startDate: nearestMinutes(0),
    timePickerIncrement: 15,
    singleDatePicker: true,
    showDropdowns: true,
    timePicker: true,
    timePicker24Hour: true,
    drops: 'down',
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD H:mm' }
});

$('.fancy_date').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD H:mm'));
    $(this).parsley().reset();
});

$('.fancy_date').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    $(this).parsley().validate()
});

function nearestMinutes(mnt){
    time = moment();
    round_interval = 15;
    intervals = Math.ceil(time.minutes() / round_interval);
    minutes = intervals * round_interval;
    time.minutes(minutes);
    return time.add(mnt,'minutes').format('YYYY-MM-DD H:mm');
}

function delImg(th){
    var id= th.getAttribute('data-id');  
    var postData = {};
    var csrf_name = "<?= $csrf->name; ?>";
    var csrf_value = "<?= $csrf->hash; ?>";
    postData[csrf_name] =  csrf_value;
    postData.id = id;
 
    $.ajax({
            url         :   '<?php echo base_url();?>admin/brand/delimg',
            type        :   'post',
            enctype     :   'multipart/form-data',
            dataType    :   "json",  
            data        :   postData,
            success     :   function(data){
                if(data.success) {
                    if(data.MSG) { swal('Session expired.'); }        
                    swal('Image Delete'); 
                    location.reload(true);
                } 
            }
    });
}
</script>