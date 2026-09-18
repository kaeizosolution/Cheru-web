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

    /* --- MODERN RADIO SELECTION (For Category/Brand/Product) --- */
    .radio-group-wrapper {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .radio-option {
        flex: 1;
        position: relative;
    }
    
    .radio-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    
    .radio-option label {
        display: block;
        background: #fff;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        color: var(--secondary);
        font-weight: 600;
        margin: 0;
    }
    
    .radio-option input[type="radio"]:checked + label {
        border-color: var(--primary);
        background-color: rgba(99, 102, 241, 0.05);
        color: var(--primary);
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.2);
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
    }

    .btn-back-custom {
        background-color: #000000ff !important;
        color: #000 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        padding: 12px 25px;
        font-weight: 600;
        margin-right: 10px;
        transition: all 0.2s;
        text-decoration: none !important;
        display: inline-block;
    }
    .btn-back-custom:hover { background-color: #4f46e5; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3);

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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->coupon; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row justify-content-center">
                <div class="col-lg-12"> <?php if (($this->session->flashdata('error')) || validation_errors()!=''){ ?>
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

                    <?php echo form_open("/$TYPE/coupon/$action/$coupon_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card', 'onsubmit' => 'return validateDiscount()')) ?>
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">Coupon Details</h5>
                        <a href="/<?= $TYPE; ?>/coupon" class="btn-close-card" title="Close"><i class="fa fa-times"></i></a>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label><?= $page_lang->name; ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= isset($name) ? $name : '' ?>" placeholder="<?= $page_lang->name; ?>" maxlength="30" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label><?= $page_lang->discount_type; ?> <span class="text-danger">*</span></label>
                                <select class="form-control btn-square" name="type" id="type" required data-parsley-errors-container="#type_error">
                                    <option value="">Select Type</option>
                                    <option value="1" <?= isset($type) && $type == '1'  ? 'selected' : '' ?>><?= $page_lang->fixed; ?></option>
                                    <option value="2" <?= isset($type) && $type == '2'  ? 'selected' : '' ?>><?= $page_lang->percentage; ?></option>
                                </select>
                                <div id="type_error"></div>
                            </div>

                            <div class="form-group col-md-6 mt-3">
                                <label><?= $page_lang->discount; ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control unsigned_float" placeholder="0.00" name="discount" id="discount" value="<?= isset($discount) ? $discount : '' ?>" required>
                            </div>
                            
                            <div class="form-group col-md-6 mt-3">
                                <label>Image</label>
                                <input class="form-control" type="file" name="image" id="image" style="padding: 7px;">    
                            </div>

                            <div class="form-group col-md-6 mt-3">
                                <label><?= $page_lang->start_date; ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                     <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #e2e8f0;"><i class="fa fa-calendar text-muted"></i></span>
                                    </div>
                                    <input type="text" class="form-control fancy_date border-left-0" name="start_date" id="start_date" value="<?= isset($start_date) ? $start_date : '' ?>" readonly required style="border-radius: 0 8px 8px 0;">
                                </div>
                            </div>

                            <div class="form-group col-md-6 mt-3">
                                <label><?= $page_lang->end_date; ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                     <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #e2e8f0;"><i class="fa fa-calendar text-muted"></i></span>
                                    </div>
                                    <input type="text" class="form-control fancy_date border-left-0" name="end_date" id="end_date" value="<?= isset($end_date) ? $end_date : '' ?>" readonly required style="border-radius: 0 8px 8px 0;">
                                </div>
                            </div>

                            <div class="form-group col-md-12 mt-4">
                                <label class="d-block mb-3">Apply Coupon On <span class="text-danger">*</span></label>
                                <div class="radio-group-wrapper">
                                    <div class="radio-option">
                                        <input type="radio" name="ctype" value="1" id="btn-ctype-1" <?= (isset($ctype) && $ctype == 1) ? 'checked' : '' ?> required>
                                        <label for="btn-ctype-1" class="btn-outline-secondary" data-id="cat_btn_div">Category</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="ctype" value="2" id="btn-ctype-2" <?= (isset($ctype) && $ctype == 2) ? 'checked' : '' ?> required>
                                        <label for="btn-ctype-2" class="btn-outline-secondary" data-id="brand_btn_div">Brand</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" name="ctype" value="3" id="btn-ctype-3" <?= (isset($ctype) && $ctype == 3) ? 'checked' : '' ?> required>
                                        <label for="btn-ctype-3" class="btn-outline-secondary" data-id="product_btn_div">Product</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 mt-2">
                                
                                <div class="coupons_div cat_btn_div" style="display:none;">
                                    <label><?= $page_lang->category; ?> <small class="text-muted font-weight-normal">(leave empty or select &ldquo;All&rdquo; to apply to all categories)</small></label>
                                    <select id="category_id" name="category_id[]" class="form-control category_combo" style="width: 100%;" multiple>
                                    <?php
                                        // Determine if nothing is selected (= All)
                                        $cat_none_selected = !(isset($category_id) && is_array($category_id) && !empty($category_id));
                                        $cat_all_selected  = $cat_none_selected ? 'selected' : '';
                                        echo "<option value=\"all\" $cat_all_selected>&#x2714; All Categories</option>";
                                        foreach($category_obj as $row){
                                            $cat_selected = (isset($category_id) && is_array($category_id) && in_array((string)$row->id, array_map('strval', $category_id))) ? 'selected' : '';
                                            echo "<option value=\"$row->id\" $cat_selected>$row->name</option>";
                                        }
                                    ?>
                                    </select>
                                </div>

                                <div class="coupons_div product_btn_div" style="display:none;">
                                    <label><?= $page_lang->product; ?> <small class="text-muted font-weight-normal">(leave empty or select &ldquo;All&rdquo; to apply to all products)</small></label>
                                    <select id='product_search' name="product_id[]" class="form-control" style="width:100%;" multiple>
                                        <?php
                                        $prod_none_selected = !(isset($product_id) && is_array($product_id) && !empty($product_id));
                                        $prod_all_selected  = $prod_none_selected ? 'selected' : '';
                                        echo "<option value=\"all\" $prod_all_selected>&#x2714; All Products</option>";
                                        if($product_id){
                                            foreach($product_id as $row){
                                                echo "<option value=\"$row->id\" selected>$row->name</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="coupons_div brand_btn_div" style="display:none;">
                                    <label><?= $page_lang->brand; ?> <small class="text-muted font-weight-normal">(leave empty or select &ldquo;All&rdquo; to apply to all brands)</small></label>
                                    <select id="brand_id" name="brand_id[]" class="form-control brand_combo" style="width: 100%;" multiple>
                                    <?php
                                        // Determine if nothing is selected (= All)
                                        $brand_none_selected = !(isset($brand_id) && is_array($brand_id) && !empty($brand_id));
                                        $brand_all_selected  = $brand_none_selected ? 'selected' : '';
                                        echo "<option value=\"all\" $brand_all_selected>&#x2714; All Brands</option>";
                                        foreach($brand_obj as $row){
                                            $brand_selected = (isset($brand_id) && is_array($brand_id) && in_array((string)$row->brand_id, array_map('strval', $brand_id))) ? 'selected' : '';
                                            echo "<option value=\"$row->brand_id\" $brand_selected>$row->name</option>";
                                        }
                                    ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-12 mt-3">                                
                                <label><?= $page_lang->description; ?></label>
                                <textarea rows="4" id="comment" class="form-control" placeholder="<?= $page_lang->description; ?>" name="description"><?= isset($description) ? $description : '' ?></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer text-right bg-white border-top p-4">
                        <a href="/<?= $TYPE; ?>/coupon" class="btn btn-back-custom"><?= $page_lang->back; ?></a>
                        <button type="submit" class="btn btn-submit-custom"><?= $page_lang->submit; ?></button>
                    </div>
                    
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Initialize logic on page load if edit mode
    <?php if(isset($ctype)) {?>
        setTimeout(function(){ 
            // Trigger click on label associated with radio
            $('label[for="btn-ctype-<?= $ctype ?>"]').click();
        }, 500);
    <?php } ?>

    // Handle Radio Button Logic via Label Click
    $('.btn-outline-secondary').click(function(){
        var id = $(this).attr("data-id");
        
        // Update UI: Remove active class from all labels, add to clicked
        $('.btn-outline-secondary').css({'border-color': '#e2e8f0', 'color': '#64748b', 'background': '#fff'});
        $(this).css({'border-color': '#6366f1', 'color': '#6366f1', 'background': 'rgba(99, 102, 241, 0.05)'});

        // Show/Hide Dropdowns
        $('.coupons_div').hide();
        $('.coupons_div select').removeAttr('required');
        $('.'+id).show();   

        if('product_btn_div' == id){
            // Specific logic for product select2 if needed
        }else{
            $('select','.'+id).attr('required',true);
        }
    });

    var csrf_name = "<?= $csrf->name; ?>";
    var csrf_value = "<?= $csrf->hash; ?>";
    
    // Select2 for Products (AJAX)
    $("#product_search").select2({
        ajax: {
            url: "<?= base_url().$TYPE; ?>/coupon/search_ajax_product",
            type: "post",
            dataType: 'json',   
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                    '<?= $csrf->name; ?>': csrf_value,               
                };
            },      
            processResults: function (response) {
                return { results: response };
            },
            cache: true
        },
        placeholder: "Start typing to search products...",
        minimumInputLength: 3,
    });

    // Init other Select2s
    $('#type').select2({allowClear: true, placeholder: "Select Type", width: '100%'});

    // Category combo with "All" toggle logic
    $('.category_combo').select2({allowClear: true, placeholder: "Select categories (or leave empty for All)", width: '100%'});
    $('#category_id').on('select2:select', function (e) {
        var selectedId = e.params.data.id;
        var currentVals = $(this).val() || [];
        if (selectedId === 'all') {
            $(this).val(['all']).trigger('change');
        } else {
            var index = currentVals.indexOf('all');
            if (index !== -1) {
                currentVals.splice(index, 1);
                $(this).val(currentVals).trigger('change');
            }
        }
    });
    $('#category_id').on('select2:unselect', function (e) {
        setTimeout(function() {
            var currentVals = $('#category_id').val() || [];
            if (currentVals.length === 0) {
                $('#category_id').val(['all']).trigger('change');
            }
        }, 1);
    });

    // Brand combo with "All" toggle logic
    $('.brand_combo').select2({allowClear: true, placeholder: "Select brands (or leave empty for All)", width: '100%'});
    $('#brand_id').on('select2:select', function (e) {
        var selectedId = e.params.data.id;
        var currentVals = $(this).val() || [];
        if (selectedId === 'all') {
            $(this).val(['all']).trigger('change');
        } else {
            var index = currentVals.indexOf('all');
            if (index !== -1) {
                currentVals.splice(index, 1);
                $(this).val(currentVals).trigger('change');
            }
        }
    });
    $('#brand_id').on('select2:unselect', function (e) {
        setTimeout(function() {
            var currentVals = $('#brand_id').val() || [];
            if (currentVals.length === 0) {
                $('#brand_id').val(['all']).trigger('change');
            }
        }, 1);
    });

    // Product search with "All" toggle logic
    $('#product_search').on('select2:select', function (e) {
        var selectedId = e.params.data.id;
        var currentVals = $(this).val() || [];
        if (selectedId === 'all') {
            $(this).val(['all']).trigger('change');
        } else {
            var index = currentVals.indexOf('all');
            if (index !== -1) {
                currentVals.splice(index, 1);
                $(this).val(currentVals).trigger('change');
            }
        }
    });
    $('#product_search').on('select2:unselect', function (e) {
        setTimeout(function() {
            var currentVals = $('#product_search').val() || [];
            if (currentVals.length === 0) {
                $('#product_search').val(['all']).trigger('change');
            }
        }, 1);
    });
});

// Datepicker Logic
$('.fancy_date').daterangepicker({
    autoUpdateInput: false,
    minDate: moment().format('YYYY-MM-DD H:mm'),
    startDate: nearestMinutes(0),
    timePickerIncrement: 15,
    singleDatePicker: true,
    showDropdowns: true,
    timePicker: true,
    timePicker24Hour: true,
    drops: 'up',
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

// Validation
$(document).ready(function() {
    window.ParsleyValidator.addValidator('myvalidator',
    function (value) {
        var sdate = $('#start_date').val();
        var edate = $('#end_date').val();
        if(sdate && edate){
            var sfepoch = Date.parse(sdate)/1000;
            var stepoch = Date.parse(edate)/1000;
            if(sfepoch > stepoch){ return false; }
        }
        return true;
    }, 32)
    .addMessage('en', 'myvalidator', 'Start Date can\'t be greater than End Date');
});

function validateDiscount() {
    var discount = $('#discount').val();
    if($('#type').val() == 2 && parseInt(discount) > 100){
        $('#discount').addClass("parsley-err");
        alert("Percentage discount cannot exceed 100%");
        return false;
    }
    return true;
}
</script>