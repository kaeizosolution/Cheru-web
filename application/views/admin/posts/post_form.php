<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Add new</h3>
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

                    <?php //echo form_open("/$TYPE/posts/$action/$post_id", array('id' => 'postForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'addpost')) ?>
                    
				<form method="POST" action="<?php echo "/$TYPE/posts/$action/$post_id"; ?>" class="addpost" id="postForm" enctype="multipart/form-data" autocomplete='off'>	
					<div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                   
                                    <input type="text" name="post_title"  class="form-control" value="<?= isset($post_title) ? $post_title : '' ?>" placeholder="Enter Title" onkeypress="return check(event)" required>
									<?php if(isset($post_title)) { ?><span>Permalink: <a target="_blank" href="<?php echo site_url('/post/').$post_slug; ?>"><?php echo isset($post_slug) ? $post_slug : '' ?></a></span><?php } ?>
								</div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label">Body</label>
                                    <textarea name="post_content" rows="5" class="form-control" placeholder="Write Something..." ><?= isset($post_content) ? $post_content : '' ?></textarea>
                                </div>
                            </div>
							
						<?php /* ?>	
						 <div class="col-sm-6 col-md-4">
                            <label for="post_status">Categories</label>
                            <?php  
                            //echo "<pre>"; print_r($category_ids); echo "</pre>";
                            if(isset($category_ids)){
                              echo form_dropdown('category[]',$categories,$category_ids,array('class' => 'select2 form-control','multiple' => true));   
                            }else{
                                 echo form_dropdown('category[]',$categories,null,array('class' => 'select2 form-control','multiple' => true));
                            }
                            ?>
                        </div>

                        <div class="col-sm-6 col-md-4">
                            <label for="post_status">Tags</label>
                            <?php                                                    
                            if(isset($tag_ids)){
                                echo form_dropdown('tag[]',$tags,$tag_ids,array('class' => 'select2-tags form-control','multiple' => true));
                            }else{
                                echo form_dropdown('tag[]',$tags,null,array('class' => 'select2-tags form-control','multiple' => true));
                            }

                            ?>
                        </div>
						<?php */ ?>
						
							<div class="col-sm-6 col-md-4"><br >
								<label>Image (Size:515X512 50KB) </label>
								<input type="file" name="featured_image" id="featured_image" class="form-control" value=""> 								
								<label for="image_url1" id="img_error" style="display:none;" generated="true" class="error">Please enter a value with a valid extension  and image size maximum 50kb.</label>										
								<?php $featured_image = isset($featured_image) ? $featured_image : ''; 
								if($featured_image){
								?>
								<img src="<?php echo base_url(); ?>assets/images/<?php echo $featured_image; ?>" width="150px" height="150px" alt="feature image" />
								<?php } ?>
							</div>
							
							
						 <div class="form-group col-md-3 m-t-20">
                            <label for="post_status">Categories</label>
                            <?php 
							// echo "xx<pre>"; print_r($category_ids); echo "</pre>";die;	
							
                            if(isset($category_ids)){
                              echo form_dropdown('category[]',$categories,$category_ids,array('class' => 'select2 form-control','multiple' => true));   
                            }else{
                                 echo form_dropdown('category[]',$categories,null,array('class' => 'select2 form-control','multiple' => true));
                            }
                            ?>
						 </div>
						 
						<?php  ?>
                        <div class="form-group col-md-3 m-t-20">
                            <label for="post_status">Tags</label>
                            <?php                                                    
                            if(isset($tag_ids)){
                                echo form_dropdown('tag[]',$tags,$tag_ids,array('class' => 'select2-tags form-control','multiple' => true));
                            }else{
                                echo form_dropdown('tag[]',$tags,null,array('class' => 'select2-tags form-control','multiple' => true));
                            }

                            ?>
                        </div><?php  ?>
						
						    <div class="col-md-6">
							 <div class="form-group"><br >
								<label>Status</label>									
								<select class="form-control" name="enabled" required="required">
								  <option value="">Select Status</option>								  
								  <option value="1" selected>Active</option>                          
								  <option value="0">Inactive</option>                           
								                         
								</select>
								</div>  
							</div>                            
                        </div>
                    </div>
                    <div class="card-footer text-right">
						<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
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
