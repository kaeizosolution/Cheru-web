<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Brand</h3>
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

                    <?php echo form_open_multipart("/$TYPE/brand/$action/$brand_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">name</label>
                                    <input type="text" class="form-control" name="name" value="<?= isset($name) ? $name : '' ?>" placeholder="name" required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Name(Spanish)</label>
                                    <input type="text" class="form-control" name="name_es" id="name_es" value="<?= isset($name_es) ? $name_es : '' ?>" placeholder="Name" required>
                                </div>
                            </div>
                            <!--<div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Start Date</label>
                                    <input type="text" class="form-control fancy_date" placeholder="Start Date" name="start_date" value="<?= isset($start_date) ? $start_date : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">End Date</label>
                                    <input type="text" class="form-control fancy_date" placeholder="End Date" name="end_date" value="<?= isset($end_date) ? $end_date : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Discount</label>
                                    <input type="text" class="form-control numeric" placeholder="Discount" name="discount" value="<?= isset($discount) ? $discount : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Discount Type</label>
                                    <select class="form-control btn-square" placeholder="Discount Type" name="type" data-parsley-trigger="change" data-parsley-errors-container="#type_error" required>
                                        <option value=""></option>
                                        <option value="1" <?= isset($type) && $type == '1'  ? 'selected' : '' ?>>Fixed</option>
                                        <option value="2" <?= isset($type) && $type == '2'  ? 'selected' : '' ?>>Percentage</option>
                                    </select>
                                    <div id="type_error"></div>
                                </div>
                            </div>-->
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label">Description</label>
                                    <textarea rows="5" class="form-control" placeholder="Description" name="description"><?= isset($description) ? $description : '' ?></textarea>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6">
                                <div class="form-group">
                                <label for="image">Image</label>
                                <input type="file" name="image" class="form-control" id="image" placeholder="Image">
                                <?php if(isset($image) && $image !='' ){ ?><img src="<?php echo base_url();?>assets/brand/<?= $image ?>"  width="100" height="100"> <?php } ?>
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

$(document).ready(function() {
    $('select').select2({allowClear: true, placeholder: "Discount Type"});
});

function nearestMinutes(mnt){
    time = moment();
    round_interval = 15;
    intervals = Math.ceil(time.minutes() / round_interval);
    minutes = intervals * round_interval;
    time.minutes(minutes);
    return time.add(mnt,'minutes').format('YYYY-MM-DD H:mm');
}
</script>
