<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="content-body">
<div class="container-fluid">
<div class="row page-titles"><?= $breadcrumbs ?></div>

<div class="row">
	<div class="col-md-8 col-sm-12">
	<div class="col-md-12 col-sm-12">
		<?php if (($this->session->flashdata('error')) || validation_errors()!='') { ?>
		<div class="alert alert-danger"><?= validation_errors();?><?= $this->session->flashdata('error')?></div><?php } ?>
		<?php if($this->session->flashdata('success')){?><div class="alert alert-success"><?= $this->session->flashdata('success')?></div><?php } ?>

	<div class="card">
	<div class="card-header"><h4 class="card-title"><?= $page_lang->coupon; ?></h4>	</div>
	<?php echo form_open("$TYPE/coupon/$action/$coupon_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card', 'onsubmit' => 'return validateDiscount()')) ?>
	<div class="card-body">
	<div class="row">
	<div class="form-group col-md-6">                                
		<label class="form-label"><?= $page_lang->name; ?></label>
		<input type="text" class="form-control" name="name" value="<?= isset($name) ? $name : '' ?>" placeholder="<?= $page_lang->name; ?>" required>

	</div>
	<div class="form-group col-md-6">                               
		<label class="form-label"><?= $page_lang->start_date; ?></label>
		<input type="text" class="form-control fancy_date" placeholder="<?= $page_lang->start_date; ?>" name="start_date" id="start_date" value="<?= isset($start_date) ? $start_date : '' ?>" data-parsley-myvalidator="" data-parsley-error-message="Start Date can't be greater than End Date" readonly required>
	</div>
	<div class="form-group col-md-6 mt-3">
		<label class="form-label"><?= $page_lang->end_date; ?></label>
		<input type="text" class="form-control fancy_date" placeholder="<?= $page_lang->end_date; ?>" name="end_date" id="end_date" value="<?= isset($end_date) ? $end_date : '' ?>" readonly required>
	</div>
	<div class="form-group col-md-6 mt-3">                               
		<label class="form-label"><?= $page_lang->discount; ?></label>
		<input type="text" class="form-control unsigned_float" placeholder="<?= $page_lang->discount; ?>" name="discount" id="discount" value="<?= isset($discount) ? $discount : '' ?>" required>
	</div>
	<div class="form-group col-md-6" style="display:none">                                
		<label class="form-label"><?= $page_lang->minimum_order_value; ?></label>
		<input type="text" class="form-control unsigned_float" placeholder="<?= $page_lang->minimum_order_value; ?>" name="minimum_order_value" id="minimum_order_value" value="<?= isset($minimum_order_value) ? $minimum_order_value : '' ?>">

	</div>
	<div class="form-group col-md-6 mt-3">                               
		<label class="form-label"><?= $page_lang->discount_type; ?></label>
		<select class="form-control btn-square" placeholder="<?= $page_lang->discount_type; ?>" name="type" id="type" data-parsley-trigger="change" data-parsley-errors-container="#type_error" required>
		<option value=""></option>
		<option value="1" <?= isset($type) && $type == '1'  ? 'selected' : '' ?>><?= $page_lang->fixed; ?></option>
		<option value="2" <?= isset($type) && $type == '2'  ? 'selected' : '' ?>><?= $page_lang->percentage; ?></option>
		</select>
		<div id="type_error"></div>                               
	</div>

	<div class="form-group col-md-12 mt-3">
		<input type="radio" name="ctype" value="1" class="btn-check" id="btn-ctype-1" <?= (isset($ctype) && $ctype == 1) ? 'checked' : '' ?>>
		<label class="btn btn-outline-secondary btn-ctype-1" data-id="cat_btn_div" for="btn-ctype-1">Category</label>

		<input type="radio" name="ctype" value="2" class="btn-check" id="btn-ctype-2" <?= (isset($ctype) && $ctype == 2) ? 'checked' : '' ?>>
		<label class="btn btn-outline-secondary btn-ctype-2" data-id="brand_btn_div" for="btn-ctype-2">Brand</label>

		<input type="radio" name="ctype" value="3" class="btn-check" id="btn-ctype-3" <?= (isset($ctype) && $ctype == 3) ? 'checked' : '' ?>>
		<label class="btn btn-outline-secondary btn-ctype-3" data-id="product_btn_div" for="btn-ctype-3">Product</label>
	</div>


	<div class="form-group col-md-12 mt-3">


	<hr>
		<div class="coupons_div cat_btn_div" style="display:none;">
			<label for="post_status"><?= $page_lang->category; ?></label>
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

		<div class="coupons_div product_btn_div" style="display:none;">
			<label for="post_status"><?= $page_lang->product; ?></label>
			<select id='product_search' name="product_id[]"  style='width: 200px;' multiple>
				<option></option>
				
				<?php 
				//echo "<pre>"; print_r($product_id); echo "</pre>";
				
				if($product_id){
					foreach($product_id as $row){
							#echo "<option value=\"$row->product_id\" $product_seleted>$row->post_title</option>";
                        echo "<option value=\"$row->product_id\" selected>$row->post_title</option>";
						}
					}
				?>
			</select>
		</div>

		<div class="coupons_div brand_btn_div" style="display:none;">
			<label for="post_status"><?= $page_lang->brand; ?></label>
			<select id="brand_id" name="brand_id" class="form-control brand_combo" data-placeholder="Product" data-parsley-errors-container="#brand_id_error">
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

	</div>

	<div class="form-group col-sm-6 mt-3">
	<label for="status"><?= $page_lang->status; ?></label>
	<div class="checkbox">
	<?php $checked = ($action == 'add') ? 'checked' : '' ;
	$checked = isset($status) && $status == '1' ? 'checked' : '' ; ?>
	<input id="checkbox1" type="checkbox" name="status" id="status" <?= $checked?> value="1">                                   
	</div>
	</div>
	<div class="form-group col-md-12 mt-3">                                
	<label class="form-label"><?= $page_lang->description; ?></label>
	<textarea rows="4" id="comment" class="form-control" placeholder="<?= $page_lang->description; ?>" name="description"><?= isset($description) ? $description : '' ?></textarea>
	</div>

	</div>
	</div><!------end card body------>

</div> </div></div><!------end col-md-8------>

<div class="col-md-4 col-sm-12"><div class="basic-form"><div class="mb-3 row">
<div class="col-sm-12"> <button type="button" class="btn btn-outline-primary btn-block"><?= $page_lang->back; ?></button></div>
<div class="col-sm-12 mt-2"> <button type="submit" class="btn btn-primary btn-block"><?= $page_lang->submit; ?></button></div>
</div></div></div>
<?php echo form_close() ?>
</div><!------end row------>
</div>
</div>

<script>

$(document).ready(function(){
<?php if(isset($ctype)) {?>
    setTimeout(function(){ 
        $('.btn-ctype-<?= $ctype ?>').click();
    }, 1000);

<?php } ?>

$('.btn-outline-secondary').click(function(){
	var id = $(this).attr("data-id");
	$('.coupons_div').hide();
	$('.'+id).show();	
});
var csrf_name = "<?= $csrf->name; ?>";
var csrf_value = "<?= $csrf->hash; ?>";
	
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
			return {
				results: response
			};
		},
		cache: true
		},
		placeholder: "Start typing",
    		minimumInputLength: 3,
	});
});


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
            }else{                
            }
        }else{          
        }
        
        if(error == 1){
            return false;
        }else{
            return true;
        }
    }, 32)

    .addMessage('en', 'myvalidator', 'Start Date can\'t be greater than End Date');
});

function validateDiscounte()
{
    var discount = $('#discount').val();
    if($('#type').val() == 2 && parseInt(discount) > 100){
        $('#discount').addClass("parsley-err");
        return false;
    }
}






</script>
