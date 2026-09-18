<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

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
        overflow: visible !important;
    }
    
    .card-header-custom {
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        border-radius: 16px 16px 0 0;
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
    
    textarea.form-control { min-height: 120px; }

    /* --- TOGGLE SWITCH --- */
    .switch-container {
        display: flex;
        align-items: center;
        margin-top: 30px;
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
        margin-right: 15px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px; width: 18px;
        left: 4px; bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->tax; ?></h3>
                </div>
                <?= $breadcrumbs ?> 
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-sm-12 col-md-12 col-lg-12">
                
                <?php if (($this->session->flashdata('error')) || validation_errors()!='') { ?>
                    <div class="alert alert-danger">
                        <?= validation_errors();?>
                        <?= $this->session->flashdata('error')?>
                    </div>
                <?php } ?>

                <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('success')?>
                    </div>
                <?php } ?>

                <?php echo form_open("/$TYPE/tax/$action/$tax_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">
                            <?= ($action == 'add') ? 'Add New Tax' : 'Edit Tax'; ?>
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->name; ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= isset($name) ? $name : '' ?>" placeholder="<?= $page_lang->name; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->rate; ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control unsigned_float" placeholder="<?= $page_lang->rate; ?>" name="rate" value="<?= isset($rate) ? $rate : '' ?>" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->tax_type; ?> <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="type" data-parsley-trigger="change" data-parsley-errors-container="#type_error" required>
                                        <option value=""></option>
                                        <option value="1" <?= isset($type) && $type == '1'  ? 'selected' : '' ?>><?= $page_lang->fixed; ?></option>
                                        <option value="2" <?= isset($type) && $type == '2'  ? 'selected' : '' ?>><?= $page_lang->percentage; ?></option>
                                    </select>
                                    <div id="type_error"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->description; ?></label>
                                    <textarea rows="4" class="form-control" placeholder="<?= $page_lang->description; ?>" name="description"><?= isset($description) ? $description : '' ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="switch-container">
                                    <?php 
                                        $checked = ($action == 'add') ? 'checked' : '' ;
                                        $checked = isset($status) && $status == '1' ? 'checked' : '' ;
                                    ?>
                                    <label class="switch">
                                        <input type="checkbox" name="status" id="status" <?= $checked?> value="1">
                                        <span class="slider"></span>
                                    </label>
                                    <label for="status" class="switch-label"><?= $page_lang->status; ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right bg-white border-top p-4" style="border-radius: 0 0 16px 16px;">
                        <a href="/<?= $TYPE?>/tax" class="btn btn-back-custom"><?= $page_lang->back; ?></a>
                        <button type="submit" class="btn btn-submit-custom"><?= $page_lang->submit; ?></button>
                    </div>

                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('select').select2({allowClear: true, placeholder: "Select Tax Type", width: '100%'});
});
</script>