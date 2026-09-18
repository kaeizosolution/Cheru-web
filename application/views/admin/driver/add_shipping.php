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
    
    /* Datepicker Input Style */
    .start_date {
        background-color: #fff !important;
        cursor: pointer;
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
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
    }

    /* FIXED BACK BUTTON (Direct Link) */
    .btn-back-custom {
        background-color: #000000ff !important;
        color: #000 !important; /* Black Text */
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        padding: 10px 25px;
        font-weight: 600;
        margin-right: 10px;
        transition: all 0.2s;
        text-decoration: none !important; /* Remove underline */
        display: inline-block;
        line-height: 1.5;
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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->shipping_charges; ?></h3>
                </div>
                <?= $breadcrumbs ?> 
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                
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
                
                <div class="shipping_alert alert alert-danger" role="alert" style="display:none;"></div>

                <?php echo form_open("/$TYPE/driver/$action/$price_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card', 'onsubmit'=>'return check_validate(this)')) ?>
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">
                            <?= ($action == 'add_shipping') ? 'Add Shipping Charge' : 'Edit Shipping Charge'; ?>
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-sm-6 col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->shipping_type; ?> <span class="text-danger">*</span></label>
                                    <select name="vehicle_type" class="form-control" id="vehicle_type" required>
                                        <option value=""></option>
                                        <?php 
                                            $vehicle = vehicle_type(); 
                                            $vehicletype = $vehicle_type;
                                            foreach($vehicle as $key => $val) {
                                                $selected = ($vehicletype == $key) ? "selected" : "";
                                                echo "<option value='$key' $selected>$val</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label"><?= $page_lang->price_per_km; ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control unsigned_float" maxlength="5" placeholder="0.00" name="price_per_km" value="<?= isset($price_per_km) ? $price_per_km : '' ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-group mb-0">
                                    <label class="form-label"><?= $page_lang->apply_date; ?> <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #e2e8f0;"><i class="fa fa-calendar text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control start_date border-left-0" readonly placeholder="YYYY-MM-DD" name="apply_date" value="<?= isset($apply_date) ? $apply_date : '' ?>" required style="border-radius: 0 8px 8px 0;">
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" class="form-control fancy_date" name="status" value='1'>

                        </div>
                    </div>

                    <div class="card-footer text-right bg-white border-top p-4" style="border-radius: 0 0 16px 16px;">
                        <a href="<?php echo base_url($TYPE . '/driver/shipping'); ?>" class="btn btn-back-custom"><?= $page_lang->back; ?></a>
                        <button type="submit" class="btn btn-submit-custom"><?= $page_lang->submit; ?></button>
                    </div>

                <?php echo form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
// Datepicker Initialization
$('.start_date').datepicker({ 
    autoUpdateInput: false,
    minDate: new Date(),
    dateFormat: 'yy-mm-dd',
    startDate: new Date(),    
});

$('.start_date').change(function(){
    $('.end_date').datepicker('option', 'minDate', new Date(this.value));
    $('.end_date').val(this.value);
});

function check_validate(t){
   return true;
}
</script>