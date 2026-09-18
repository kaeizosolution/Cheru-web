<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
				
		<div class="content-body">
            <div class="container-fluid">
				
				<div class="row page-titles">
					<ol class="breadcrumb">
						<?= $page_lang->new_category; ?><?php $breadcrumbs ?>
					</ol>
                </div>
				    
            <div class="row">
                <div class="col-md-8 col-sm-12">               
				 <form role="form" action="<?php echo site_url('vendor/categories_prod/add')?>" method="post" enctype="multipart/form-data" > 
					
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Category</h4>
						</div>
						
						<div class="card-body"><div class="basic-form">
						
                        <div class="row">
                            <div class="col-sm-6 col-md-6">
                                <div class="form-group">
                                <label class="form-label">Name</label>
								<input type="text" name="name" class="form-control" id="name" placeholder="Name" required>
								 <?php echo message_box(validation_errors(),'danger'); ?>
								</div>
                            </div>
                           
                            <div class="col-sm-6 col-md-6">
                                <div class="form-group">
                                <label for="parent_id"><?= $page_lang->parent; ?></label>
                                <select name="parent_id" id="parent_id" class="form-control">
                                <option></option>
                                <?php $this->Category_prod_model->categoryTree(); ?>
                                </select>
							    </div>
							</div>
                            <div class="col-sm-12 col-md-12">
                                <div class="form-group">
                                <label for="category_name"><?= $page_lang->icon; ?></label>
                               <div class="input-group new-formfile mb-3"><div class="form-file"> <input type="file" name="icon" class="form-control" id="icon"> 
								</div></div></div>
                            </div>
                            <div class="col-sm-12 col-md-12">
                                <div class="form-group">
                                <label for="category_name"><?= $page_lang->image; ?> (600X600)</label>
                               <div class="input-group new-formfile mb-3"><div class="form-file"> <input type="file" name="thumbnail_image" class="form-control" id="thumbnail_image"> 
								</div></div></div>
                            </div>
							<div class="col-sm-12 col-md-12">
                                <div class="form-group">
                                <label for="category_name">Banner <?= $page_lang->image; ?> (1800X400)</label>
                               <div class="input-group new-formfile mb-3"><div class="form-file"> <input type="file" name="banner_image" class="form-control" id="banner_image"> 
								</div></div></div>
                            </div>
                            <div class="col-sm-6 col-md-6">
                                <div class="form-group">
                                    <label for="category_status"><?= $page_lang->status; ?></label>
                                    <?php $category_status = array("1" => $page_lang->active, "0" => $page_lang->inactive);
                                        echo form_dropdown('status',$category_status,null,array('id' => 'status', 'class' => 'form-control'));
                                    ?>
							    </div>
							</div>
                        </div> 
						</div> </div>
                    </div>
                    
                </div><!-------end col-md-8 col-sm-12------>
				
				<div class="col-md-4 col-sm-12"><div class="basic-form">
				
				<div class="mb-3 row">
				
				
						<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                      <div class="col-sm-12">  <button type="button" class="btn btn-outline-primary btn-block"><?= $page_lang->back; ?></button></div>
                       <div class="col-sm-12 mt-2"> <button type="submit" class="btn btn-primary btn-block"><?= $page_lang->submit; ?></button></div>
				</div>
				
				</div></div>
			</form>
					
            </div><!-------end row------>
       </div> </div> <!-------end content-body------>

<script>
$(document).ready(function() {
    $('#parent_id').select2({allowClear: true, placeholder: "Parent"});
    $('#status').select2({allowClear: true, placeholder: "Status"});
});
</script>
