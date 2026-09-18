<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?= $page_lang->edit_category; ?></h3>
                </div>
                <?php $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
                   
				 <form role="form" action="<?php echo site_url('admin/categories_prod/update')?>" method="post" enctype="multipart/form-data" >
					<div class="card-body">
						<?php echo $this->session->flashdata('message');?>							
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label class="form-label">Name(English)</label>
                                <input type="text" name="name" class="form-control" id="name" placeholder="Name" 
                                value="<?php echo set_value('name', isset($category['name']) ? $category['name'] : '') ?>" required>
								<?php echo message_box(validation_errors(),'danger'); ?>
								</div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label class="form-label">Nombre(Spanish)</label>
                                <input type="text" name="name_es" class="form-control" id="name_es" placeholder="Name" 
                                value="<?php echo set_value('name_es', isset($category['name_es']) ? $category['name_es'] : '') ?>" required>
								<?php echo message_box(validation_errors(),'danger'); ?>
								</div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                 <div class="form-group">
                                 <label for="parent_id"><?= $page_lang->parent; ?></label>
                                 <select name="parent_id" id="parent_id" class="form-control">
                                 <option></option>
                                 <?php $this->Category_prod_model->categoryTree(0,'',$category['parent_id']); ?>
                                 </select>
                                 </div>
							</div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label for="Icon_name"><?= $page_lang->icon; ?></label>
                                <input type="file" name="icon" class="form-control" id="icon" placeholder="icon"> 
								<?php if(isset($category['icon']) && $category['icon']!=''){ ?>
								<img src="<?php echo base_url();?>assets/categories/<?php echo $category['icon']; ?>" 
								width="100" height="100"> 
								<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
								style=" background:none; color:red;" type="button" data-id="icon"><i class="fa fa-trash"></i></button>	

								<?php } ?>     
								</div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label for="Thumbnail image"><?= $page_lang->image; ?> (600X600)</label>
                                <input type="file" name="thumbnail_image" class="form-control" id="thumbnail_image" placeholder="Image"> 
								<?php if(isset($category['thumbnail']) && $category['thumbnail'] !='' ){ ?>
								<img src="<?php echo base_url();?>assets/categories/<?php echo $category['thumbnail']; ?>"  
								width="100" height="100"> 
								<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
								style=" background:none; color:red;" type="button" data-id="thumbnail"><i class="fa fa-trash"></i></button>	
								<?php } ?>     
								</div>
                            </div>
							<div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label for="banner image"><?= $page_lang->image; ?> (1800X400)</label>
                                <input type="file" name="banner_image" class="form-control" id="banner_image" placeholder="Banner image "> 
								<?php if(isset($category['banner_image']) && $category['banner_image'] !='' ){ ?>
								<img src="<?php echo base_url();?>assets/categories/<?php echo $category['banner_image']; ?>"  width="220" height="80"> 
								<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
								style=" background:none; color:red;" type="button" data-id="banner_image"><i class="fa fa-trash"></i></button>	
								<?php } ?>     
								</div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label for="category_status"><?= $page_lang->status; ?></label>
                                <?php 
                                $category_status = array("0" => $page_lang->inactive, "1" => $page_lang->active);
                                echo form_dropdown('status',$category_status, isset($category['status']) ? $category['status'] : '',array('id' => 'status', 'class' => 'form-control'));
                                ?>
                                </div> 
                            </div>
														
                        </div>
                    </div>
                    <div class="card-footer text-right">
						<input type="hidden" name="id" value="<?php echo $category['id']?>">
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
$(document).ready(function() {
    $('#parent_id').select2({allowClear: true, placeholder: "Parent"});
    $('#status').select2({allowClear: true, placeholder: "Status"});
});


$(document).on('click','.btn-delete', function(e){
	//alert('dd');
	var obj = $(this);
	var id=$(this).attr('data-id');  
	var postData = {};
	var csrf_name = "<?= $csrf->name; ?>";
	var csrf_value = "<?= $csrf->hash; ?>";
	postData[csrf_name] =  csrf_value;
	postData.id = id;
	postData.cat_id = <?= isset($category['id']) ? $category['id'] : 0 ?>;
	$.ajax({
			url         :   '<?php echo base_url();?>admin/Categories_prod/delimg',
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
