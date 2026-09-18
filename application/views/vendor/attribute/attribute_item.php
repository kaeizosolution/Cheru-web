
  <div class="content-body">
	<div class="container-fluid">	
	  
		<div class="row page-titles"> <?= $breadcrumbs ?> </div> 
    
   
            <div class="row">
                <div class="col-md-8 col-sm-12">
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

                    <?php echo form_open("$TYPE/attributes/$action/$attribute_id/$attribute_item_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>
                    
					<div class="card-body"><div class="basic-form">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" id="name" value="<?= isset($name) ? $name : '' ?>" placeholder="Name" required>
                                </div>
                            </div>                           
                            <div class="col-md-12">
                                <div class="form-group mt-3">
                                    <label class="form-label mb-3">Description</label>
                                    <textarea rows="6" class="form-control" placeholder="Description" name="description"><?= isset($description) ? $description : '' ?></textarea>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-6 mt-3">
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select class="col-sm-12" id="status" name="status" data-parsley-trigger="change" data-parsley-errors-container="#status_error" placeholder="Status" required>
                                        <option></option>
                                        <option value="0" <?= isset($status) && $status == 0 ? 'selected' : '' ?>></option>
                                        <option value="1" <?= isset($status) && $status == 1 ? 'selected' : '' ?>></option>
                                    </select>
                                    <div id="status_error"></div>
                                </div>
                            </div>
                        </div> </div>
                    </div>
                   
                </div>
				
				<div class="col-md-4 col-sm-12">
					<div class="basic-form">						
						<div class="mb-3 row">
							<div class="col-sm-12"><button type="button" class="btn btn-outline-primary btn-block">Back</button></div>
							<div class="col-sm-12 mt-2">
							<button type="submit" class="btn btn-primary btn-block">Submit</button></div>
						</div>
						<?php echo form_close() ?>
					</div>
        		</div>	
					
            </div><!---------end row----->
			
        </div>
    </div> <!---------end content-body----->


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
    $('select').select2({allowClear: true, placeholder: "Status"});
});

function nearestMinutes(mnt){
    time = moment();
    round_interval = 15;
    intervals = Math.ceil(time.minutes() / round_interval);
    minutes = intervals * round_interval;
    time.minutes(minutes);
    return time.add(mnt,'minutes').format('YYYY-MM-DD H:mm');
}

$("#slug").focus(function() {
    var str = $("#name").val().trim();
    var res = str.toLowerCase().replace(/\s+/g, '-');
    $("#slug").val(res);
});
</script>
