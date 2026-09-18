<?php $page_lang = get_page_language_data('admin_page_lang'); ?>


<div class="content-body"> <div class="container-fluid">
	
	<div class="row page-titles">
		<?= $breadcrumbs ?>
	</div>
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

                    <?php echo form_open("$TYPE/attributes/$action/$attribute_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>
                    
					<div class="col-md-12 col-sm-12"><div class="card">
					<div class="card-header">
						<h4 class="card-title">Attributes</h4>
					</div>
								
				   <div class="card-body">
					   <div class="basic-form">					   
							<div class="row">										
								<div class="col-md-6 form-group">  
									<label class="form-label">Name</label>	
									<input type="text" class="form-control" name="name" id="name" value="<?= isset($name) ? $name : '' ?>" placeholder="Name" required>
								</div> 
							</div>
							<hr>
							
                            <div class="mb-3">                               
                                    <label class="form-label"><?= $page_lang->description; ?></label>
                                    <textarea rows="4" class="form-control" name="description" placeholder="<?= $page_lang->description; ?>" ><?= isset($description) ? $description : '' ?></textarea>
                             
                            </div>
							
                            <div class="form-group col-md-6">                               
                                    <label class="form-label"><?= $page_lang->status; ?></label>
                                    <select name="status" class="dropdown-groups" id="status" data-parsley-trigger="change" data-parsley-errors-container="#status_error" placeholder="<?= $page_lang->status; ?>" required>
                                        <option></option>
                                        <option value="0" <?= isset($status) && $status == 0 ? 'selected' : '' ?>> <?= $page_lang->inactive; ?></option>
                                        <option value="1" <?= isset($status) && $status == 1 ? 'selected' : '' ?>><?= $page_lang->active; ?></option>
                                    </select>
                                    <div id="status_error"></div>                               
                            </div>							
                        </div>
                    </div><!-------------Card Body end----->
					
					
					 </div></div><!-------------col-md-12 col-sm-12 end ----->
					 
					 </div><!-------------col-8 end----->
					
                    <div class="col-md-4 col-sm-12">
						<div class="basic-form">
							<div class="col-sm-12">
								<button type="button" class="btn btn-outline-primary btn-block"><?= $page_lang->back; ?></button></div>
								<div class="col-sm-12 mt-2"><button type="submit" class="btn btn-primary btn-block"><?= $page_lang->submit; ?></button>
							</div>                   
						</div> 
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
