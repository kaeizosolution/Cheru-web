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
        background: #fff;
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
        display: block;
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
    
    /* Readonly Input Style */
    .form-control[readonly] {
        background-color: #f1f5f9;
        opacity: 1;
        cursor: not-allowed;
    }

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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->currency; ?></h3>
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

                <?php echo form_open("/$TYPE/currency/$action/$currency_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">
                            <?= ($action == 'add') ? 'Add Currency' : 'Edit Currency'; ?>
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label for="country_id"><?= $page_lang->country; ?> <span class="text-danger">*</span></label>
                                    <select id="country_id" name="country_id" class="form-control country_combo select2" required data-parsley-errors-container="#country_id_error">
                                        <option value=""></option>
                                        <?php
                                            foreach($country_obj as $row){
                                                $seleted = (isset($country_id) && $country_id == $row->country_id) ? 'selected' : '';
                                                echo "<option value=\"$row->country_id\" data-name='$row->currency_name' data-iso_code='$row->currency' data-symbol='$row->currency_symbol' $seleted>$row->name</option>";
                                            }
                                        ?>
                                    </select>
                                    <div id="country_id_error"></div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->name; ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" value="<?= isset($name) ? $name : '' ?>" placeholder="<?= $page_lang->name; ?>" maxlength="30" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->symbol; ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="symbol" id="symbol" value="<?= isset($symbol) ? $symbol : '' ?>" placeholder="<?= $page_lang->symbol; ?>" maxlength="30" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->iso_code; ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="iso_code" id="iso_code" value="<?= isset($iso_code) ? $iso_code : '' ?>" placeholder="<?= $page_lang->iso_code; ?>" maxlength="3" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->rate; ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control unsigned_float" name="rate" value="<?= isset($rate) ? $rate : '' ?>" placeholder="<?= $page_lang->rate; ?>" maxlength="30" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right bg-white border-top p-4" style="border-radius: 0 0 16px 16px;">
                        <a href="/<?= $TYPE?>/currency" class="btn btn-back-custom"><?= $page_lang->back; ?></a>
                        <button type="submit" class="btn btn-submit-custom"><?= $page_lang->submit; ?></button>
                    </div>

                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // Initialize Select2 with minimum 2 characters required to search
    $('.country_combo').select2({
        allowClear: true,
        placeholder: "Type country name (min 2 chars)...",
        width: '100%',
        minimumInputLength: 2,
        language: {
            inputTooShort: function() {
                return "Please enter 2 or more characters to search";
            }
        }
    });

    // Auto-fill currency fields when a country is selected
    $('#country_id').on('select2:select', function(e) {
        // Read data-attributes from the original <option> element
        var el = e.params.data.element;
        var countryName = $(el).attr('data-name')   || '';
        var isoCode     = $(el).attr('data-iso_code') || '';
        var symbol      = $(el).attr('data-symbol')  || '';

        $('#name').val(countryName);
        $('#iso_code').val(isoCode);
        $('#symbol').val(symbol);
    });

    // Clear auto-filled fields when selection is cleared
    $('#country_id').on('select2:unselect', function() {
        $('#name').val('');
        $('#iso_code').val('');
        $('#symbol').val('');
    });
});

$(document).on("input", ".unsigned_float", function(evt) {
    var self = $(this);
    self.val(self.val().replace(/[^0-9\.]/g, '').replace(/(\..*?)\..*/g, '$1'));
});
</script>