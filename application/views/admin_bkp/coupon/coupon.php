<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Coupon</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
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

                    <?php echo form_open("/$TYPE/coupon/$action/$coupon_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card', 'onsubmit' => 'return validateDiscount()')) ?>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="<?= isset($name) ? $name : '' ?>" placeholder="Name" required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Start Date</label>
                                    <input type="text" class="form-control fancy_date" placeholder="Start Date" name="start_date" id="start_date" value="<?= isset($start_date) ? $start_date : '' ?>" data-parsley-myvalidator="" data-parsley-error-message="Start Date can't be greater than End Date" readonly required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">End Date</label>
                                    <input type="text" class="form-control fancy_date" placeholder="End Date" name="end_date" id="end_date" value="<?= isset($end_date) ? $end_date : '' ?>" readonly required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Discount</label>
                                    <input type="text" class="form-control unsigned_float" placeholder="Discount" name="discount" id="discount" value="<?= isset($discount) ? $discount : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4" style="display:none">
                                <div class="form-group">
                                    <label class="form-label">Minimum Order Value</label>
                                    <input type="text" class="form-control unsigned_float" placeholder="Minimum Order Value" name="minimum_order_value" id="minimum_order_value" value="<?= isset($minimum_order_value) ? $minimum_order_value : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Discount Type</label>
                                    <select class="form-control btn-square" placeholder="Discount Type" name="type" id="type" data-parsley-trigger="change" data-parsley-errors-container="#type_error" required>
                                        <option value=""></option>
                                        <option value="1" <?= isset($type) && $type == '1'  ? 'selected' : '' ?>>Fixed</option>
                                        <option value="2" <?= isset($type) && $type == '2'  ? 'selected' : '' ?>>Percentage</option>
                                    </select>
                                    <div id="type_error"></div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label for="post_status">Category</label>
                                <select id="category_id" name="category_id" class="form-control category_combo" data-parsley-errors-container="#category_id_error">
                                    <option></option>
                                    <?php
                                        foreach($category_obj as $row){
                                            $cat_seleted = (isset($category_id) && $category_id == $row->id) ? 'Selected' : '';
                                            echo "<option value=\"$row->id\" $cat_seleted>$row->name</option>";
                                        }
                                        ?>
                                </select>
                                <div id="category_id_error"></div>
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label for="post_status">Brand</label>
                                <select id="brand_id" name="brand_id" class="form-control brand_combo" data-parsley-errors-container="#brand_id_error">
                                    <option></option>
                                    <?php
                                        foreach($brand_obj as $row){
                                            $brand_seleted = (isset($brand_id) && $brand_id == $row->brand_id) ? 'Selected' : '';
                                            echo "<option value=\"$row->brand_id\" $brand_seleted>$row->name</option>";
                                        }
                                        ?>
                                </select>
                                <div id="brand_id_error"></div>
                            </div>
                            <div class="form-group col-md-12 m-t-20">
                                <label for="post_status">Product</label>
                                <select id="product_id" name="product_id[]" class="form-control product_combo" data-parsley-errors-container="#product_id_error" multiple>
                                    <option></option>
                                    <?php
                                        foreach($product_obj as $row){
                                            $product_seleted = isset($product_id) && in_array($row->product_id, $product_id) ? 'Selected' : '';
                                            echo "<option value=\"$row->product_id\" $product_seleted>$row->post_title</option>";
                                        }
                                        ?>
                                </select>
                                <div id="product_id_error"></div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label">Description</label>
                                    <textarea rows="5" class="form-control" placeholder="Description" name="description"><?= isset($description) ? $description : '' ?></textarea>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="checkbox p-0">
                                    <?php $checked = ($action == 'add') ? 'checked' : '' ;
                                        $checked = isset($status) && $status == '1' ? 'checked' : '' ;
                                    ?>
                                    <input id="checkbox1" type="checkbox" name="status" id="status" <?= $checked?> value="1">
                                    <label for="checkbox1">Status</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="button" class="btn btn-light btn_back">Back</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#type').select2({allowClear: true, placeholder: "Discount Type"});
    $('.product_combo').select2({placeholder: "Product"});
    $('.category_combo').select2({allowClear: true, placeholder: "Category"});
    $('.brand_combo').select2({allowClear: true, placeholder: "Brand"});
});
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

$(document).ready(function() {
    window.ParsleyValidator.addValidator('myvalidator',
    function (value) {
        var sdate = $('#start_date').val();
        var edate = $('#end_date').val();
        var error = 0;
        if(sdate && edate){
            var sfepoch = Date.parse(sdate)/1000;
            var stepoch = Date.parse(edate)/1000;

            if(sfepoch > stepoch){
                error = 1;
                   // return false;
            }else{
                   // return true;
            }
        }else{
            //    return false;
        }
        
        if(error == 1){
            return false;
        }else{
            return true;
        }
    }, 32)

    .addMessage('en', 'myvalidator', 'Start Date can\'t be greater than End Date');
});

function validateDiscount()
{
    var discount = $('#discount').val();
    if($('#type').val() == 2 && parseInt(discount) > 100){
        $('#discount').addClass("parsley-err");
        return false;
    }
}
//$('#loginForm')
</script>
