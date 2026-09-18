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

                    <?php if($this->session->flashdata('success')){ ?>
                       <div class="alert alert-success">
                            <?= $this->session->flashdata('success') ?>
                       </div>
                    <?php } ?>

                    <?php //echo form_open_multipart("/$TYPE/page/$action/$page_id", array('id' => 'postForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'addpost')) ?>
                    
					<form class="addpost" id="postForm" method="post" action="<?php echo '/$TYPE/page/$action/$page_id'; ?>" enctype="multipart/form-data">
					
					<div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">                                  
                                    <input type="text" name="post_title"  class="form-control" value="<?= isset($post_title) ? $post_title : '' ?>" placeholder="Enter Title" required>
                                	<?php if(isset($post_title)) { ?><span>Permalink: <a target="_blank" href="<?php echo site_url('/page/').$post_slug; ?>"><?php echo isset($post_slug) ? $post_slug : '' ?></a></span><?php } ?>
								</div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label">Body</label>
                                    <textarea name="post_content" rows="5" class="form-control" placeholder="Write Something..." ><?= isset($post_content) ? $post_content : '' ?></textarea>
                                </div>
                            </div>
							<div class="form-group col-md-3 m-t-20">
								<label>Image (Size:515X512 50KB) </label>
								<input type="file" name="image_url" id="image_url" class="form-control" value=""> 								
								<label for="image_url1" id="img_error" style="display:none;"  generated="true" class="error">Please enter a value with a valid extension  and image size maximum 50kb.</label>										
							</div>
									
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
    var files = $("#image_url").get(0).files;
	
	var img_name = $('#image_url').val();
	
    if(img_name!=''){
	var ext = $('#image_url').val().split('.').pop().toLowerCase();

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
</script> 
