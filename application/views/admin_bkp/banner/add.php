<?php 
if ($this->uri->segment(3) === FALSE){
	$segment = 0;
}else{
	$segment = $this->uri->segment(3);
}
?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header"> <div class="row">
                <div class="col-lg-6"> <h3><?php echo $segment.' ';
					echo isset($banner_type) ? $banner_type:''; ?></h3>
                </div> <?= $breadcrumbs ?>
            </div> </div>
    </div>
    <div class="container-fluid"> 
            <div class="row ">
                <div class="col-sm-12">
                    <div class="card">
						
                        <?php if ((isset($message) && $message['error']!='') || validation_errors()!='') { ?>
                          <div class="alert alert-danger">
                              <?= validation_errors();
                                  if($message['error']!=''){
                                      echo $message['error'];
                                  }							
                              ?>
                          </div>
                          <?php }
						 $id = isset($banner_id) ? $banner_id:'';
						 $image = isset($banner_image) ? $banner_image:'';	
						 
						 ?>
                          <?php if($this->session->flashdata('message')){ ?>   <div class="alert alert-success"> <?= $this->session->flashdata('message')?>
                          </div> <?php } ?>  
                          <?php if($segment=='add'){  ?>        
    				    <form role="form" action="<?php echo site_url('admin/banner/add/').$widget_id; ?>" method="post" enctype="multipart/form-data" > 
                        <?php }else{ ?>
                        <form role="form" action="<?php echo site_url('admin/banner/update/'). $id ?>" method="post" enctype="multipart/form-data" > 
                        <?php } ?>
        					<div class="card-body">
                                <input type="hidden" name="type" value="<?= isset($type)?$type:''?>">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                        <label for="category_name">Banner Heading</label>
                                        <input type="text" name="title" class="form-control" id="Bannertitle" placeholder="Banner Heading" value="<?= isset($title)?$title:''?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="category_name">URL</label>
                                            <input type="text" placeholder="URL" name="url" class="form-control" id="url" value="<?= isset($url)? $url:'' ?>" />
                                        </div>
                                    </div>
        							 <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="category_name">Banner Image</label>
                                            <input type="file" name="banner_image" class="form-control" id="Image" value="<?= $image ?>" />

										<?php if(isset($type) && $type!='' && $banner_image!=''){?>
										<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
										style=" background:none; color:red;" type="button" data-id="<?php echo $type; ?>"><i class="fa fa-trash"></i></button>	

                                         <img src="<?php echo base_url().'assets/home_page_banner/'.$banner_image; ?>" width="100"  height="100" />
										 <?php }?>

                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="category_name">Banner Height</label>
                                            <input type="text" placeholder="Height in px" name="height" class="form-control" id="BannerHeight" value="<?= isset($height)?$height:''?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="category_name">Banner Width</label>
                                            <input type="text" placeholder="Width in px" name="width" class="form-control" id="Bannerwidth" value="<?= isset($width)?$width:''?>" />
                                        </div>
                                    </div>
                                   
                                    <div class="col-sm-6 col-md-6">
                                       <div class="form-group">
                                        <label for="category_status">Status</label>
                                            <select name="enabled" id="enabled" class="form-control"> 
                                                <option value="1" <?php if(isset($enabled) && $enabled == 1){ echo 'selected=selected'; }?>>Active</option>
                                                <option value="0" <?php if(isset($enabled) && $enabled == 0){ echo 'selected=selected'; }?>>Inactive</option>
                                            </select>
            							</div>
                                    </div>
        							
                                </div>
                            </div>
                            <div class="card-footer text-right">
        						<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                                <button type="button" class="btn btn-light btn_back">Back</button>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary"><?php if( isset($type) && $type==''){ echo 'Submit'; }else{ echo 'Update';}?></button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
    </div>
</div>

<script>
$(document).on('click','.btn-delete', function(e){
	//alert('dd');
	var obj = $(this);
	var id=$(this).attr('data-id');  
	var postData = {};
	var csrf_name = "<?= $csrf->name; ?>";
	var csrf_value = "<?= $csrf->hash; ?>";
	postData[csrf_name] =  csrf_value;
	postData.id = id;
	postData.product_id = <?= isset($banner_id) ? $banner_id : 0 ?>;
	$.ajax({
			url         :   '<?php echo base_url();?>admin/banner/delimg',
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