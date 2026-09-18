<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles">
                <?= $breadcrumbs ?>         
        </div>  
		
		<div class="row">
		<div class="col-md-8 col-sm-12">
			<div class="col-md-12 col-sm-12">	
			
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
					
					
			<div class="card">                    
				<div class="card-header">
					<h4 class="card-title"><?= $page_lang->brand; ?></h4>
				</div>
			<?php echo form_open_multipart("$TYPE/brand/$action/$brand_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label class="form-label">Name</label>
							<input type="text" class="form-control" name="name" value="<?= isset($name) ? $name : '' ?>" placeholder="Name" required>
						</div>
					</div> 
					
					<div class="mb-3 mt-3">                               
							<label class="form-label"><?= $page_lang->description; ?></label>
							<textarea rows="5" class="form-control" placeholder="<?= $page_lang->description; ?>" name="description"><?= isset($description) ? $description : '' ?></textarea>
					</div>
					
					<div class="form-group col-md-6 mt-2">                               
						<label for="image"><?= $page_lang->image; ?></label>
						<div class="input-group new-formfile mb-3"><div class="form-file"><input type="file" name="image" class="form-control" id="image" placeholder="<?= $page_lang->image; ?>">
						</div></div>
						<?php if(isset($image) && $image !='' ){ ?><img src="<?php echo base_url();?>assets/brand/<?= $image ?>"  width="100" height="100"> 
						<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
						style=" background:none; color:red;" type="button" data-id="<?php echo $brand_id; ?>"><i class="fa fa-trash"></i></button>
						<?php } ?>                               
					</div>
					<div class="col-sm-6 mt-2">					
						<label for="checkbox1"><?= $page_lang->status; ?></label>
						<div class="checkbox">						
							<?php $checked = ($action == 'add') ? 'checked' : '' ;
								$checked = isset($status) && $status == '1' ? 'checked' : '' ;
							?>
							<input id="checkbox1" type="checkbox" name="status" id="status" <?= $checked?> value="1">
							
						</div>
					</div>
				</div>
				</div><!------end card-body----->
					
				</div><!------end card----->
			</div>
		</div><!------end row----->
					
		<div class="col-md-4 col-sm-12"><div class="basic-form">
			<div class="mb-3 row">
				<div class="col-sm-12"> <button type="button" class="btn btn-outline-primary btn-block"><?= $page_lang->back; ?></button></div>
				<div class="col-sm-12 mt-2"><button type="submit" class="btn btn-primary btn-block"><?= $page_lang->submit; ?></button></div>
			</div> </div> 
		</div><!------end col-md-4----->
		<?php echo form_close() ?>
	</div><!------end row----->
	
</div></div><!------end content-body----->


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

$(document).on('click','.btn-delete', function(e){
	//alert('dd');
	var obj = $(this);
	var id=$(this).attr('data-id');  
	var postData = {};
	var csrf_name = "<?= $csrf->name; ?>";
	var csrf_value = "<?= $csrf->hash; ?>";
	postData[csrf_name] =  csrf_value;
	postData.id = id;
	postData.brand_id = <?= isset($brand_id) ? $brand_id : 0 ?>;
	$.ajax({
			url         :   '<?php echo base_url();?>admin/brand/delimg',
			type        :   'post',
			enctype	    :   'multipart/form-data',
			dataType    :   "json",  
			data        :   postData,
			success     :   function(data){
				if(data.success)
				{
					if(data.MSG)  {
							swal('Session expired.');
						}		 
					swal('Image Delete'); 
					location.reload(true);
				} 
			}
	});
} );
</script>
