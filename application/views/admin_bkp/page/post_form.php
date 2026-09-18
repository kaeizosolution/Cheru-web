<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?= $page_lang->add_new; ?></h3>
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

                    <?php //echo form_open_multipart("/$TYPE/page/$action/$page_id", array('id' => 'postForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'addpost')) ?>
                    
					<form method="post" class="addpost" id="postForm" action='<?php echo "/$TYPE/page/$action/$page_id"; ?>' enctype="multipart/form-data" autocomplete='off'>
					
					<div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-6">
                                <div class="form-group">                                  
                                    <input type="text" name="post_title"  class="form-control" value="<?= isset($post_title) ? $post_title : '' ?>" placeholder="<?= $page_lang->enter_title; ?>" onkeypress="return check(event)" required>
                                	<?php if(isset($post_title)) { ?><span>Permalink: <a target="_blank" href="<?php echo site_url('/page/').$post_slug; ?>"><?php echo isset($post_slug) ? $post_slug : '' ?></a></span><?php } ?>
								</div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label"><?= $page_lang->body; ?></label>
                                    <textarea name="post_content" rows="5" class="form-control" placeholder="Write Something..." ><?= isset($post_content) ? $post_content : '' ?></textarea>
                                </div>
                            </div>
							<div class="form-group col-md-4 m-t-20">
								<label><?= $page_lang->image_size_515_512_50; ?></label>
								<input type="file" name="featured_image" id="featured_image" class="form-control" value=""> 								
								<label for="featured_image" id="img_error" style="display:none;"  generated="true" class="error">Please enter a value with a valid extension  and image size maximum 50kb.</label>	<?php $featured_image = isset($featured_image) ? $featured_image : ''; 
								if($featured_image){
								?>
								<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
										style=" background:none; color:red;" type="button" data-id="<?php echo $page_id; ?>"><i class="fa fa-trash"></i></button>	

								<img src="<?php echo base_url(); ?>assets/images/<?php echo $featured_image; ?>" width="150px" height="150px" alt="feature image" />
								<?php } ?>									
							</div>
									
						    <div class="col-md-4">
							 <div class="form-group"><br >
								<label><?= $page_lang->status; ?></label>
								<?php $enabled = isset($enabled) ? $enabled : ''; ?>									
								<select class="form-control" name="enabled" required="required">
								  <option value=""><?= $page_lang->select_status; ?></option>								  
								  <option value="1" <?php if($enabled=='1') echo "selected=selected"; ?>><?= $page_lang->active; ?></option>                          
								  <option value="0" <?php if($enabled=='0') echo "selected=selected"; ?>><?= $page_lang->inactive; ?></option>
								</select>
								
								</div>  
							</div>                            
                        </div>
                    </div>
                    <div class="card-footer text-right">
						<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                        <button type="button" class="btn btn-light btn_back"><?= $page_lang->back; ?></button>
                        <button type="submit" class="btn btn-primary"><?= $page_lang->submit; ?></button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
//check image validateion
$("#postForm").click(function(){
 $("#img_error").hide();
    var flag=0;
    var files = $("#featured_image").get(0).files;
	
	var img_name = $('#featured_image').val(); 
	
    if(img_name!=''){
	var ext = $('#featured_image').val().split('.').pop().toLowerCase();

    if($.inArray(ext, ['gif','png','jpg','jpeg','pdf']) == -1){
       flag=1;
    }

    for(var i=0; i<files.length;i++) {

        var file = files[i];
        var name = file.name;
        var size = file.size;
        if(size>600000){
            flag=1;
        }
      }
    if(flag==1){
        $("#img_error").show();
        return false;
        }
    }
})

$(document).on('click','.btn-delete', function(e){
	//alert('dd');
	var obj = $(this);
	var id=$(this).attr('data-id');  
	var postData = {};
	var csrf_name = "<?= $csrf->name; ?>";
	var csrf_value = "<?= $csrf->hash; ?>";
	postData[csrf_name] =  csrf_value;
	postData.id = id;
	postData.page_id = <?= isset($page_id) ? $page_id : 0 ?>;
	$.ajax({
			url         :   '<?php echo base_url();?>admin/page/delimg',
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
