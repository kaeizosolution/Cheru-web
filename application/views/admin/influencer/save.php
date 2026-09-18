<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?= $page_lang->new_category; ?></h3>
                </div>
                <?=$breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">               
				 <form role="form" class="card" action="<?php echo site_url('admin/categories_prod/add')?>" method="post" enctype="multipart/form-data" > 
					<div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label class="form-label">Name</label>
								<input type="text" name="name" class="form-control" id="name" placeholder="Name" required>
								 <?php echo message_box(validation_errors(),'danger'); ?>
								</div>
                            </div>
                           
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label for="parent_id"><?= $page_lang->parent; ?></label>
                                <select name="parent_id" id="parent_id" class="form-control">
                                <option></option>
                                <?php
                                    $this->Category_prod_model->categoryTree();
                                    #echo form_dropdown('parent_id',$cat_array,isset($category['parent_id']) ? $category['parent_id'] : '',array('id' => 'parent_id', 'class' => 'form-control'));
                                ?>
                                </select>
							    </div>
							</div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group" style="display: none;">
                                <label for="category_name"><?= $page_lang->icon; ?></label>
                                <input type="file" name="icon" class="form-control" id="icon"> 
								</div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label for="category_name"><?= $page_lang->image; ?> (600X600)</label>
                                <input type="file" name="thumbnail_image" class="form-control" id="thumbnail_image" required=""> 
								</div>
                            </div>
							<div class="col-sm-6 col-md-4" style="display: none;">
                                <div class="form-group" >
                                <label for="category_name">Banner <?= $page_lang->image; ?> (1800X400)</label>
                                <input type="file" name="banner_image" class="form-control" id="banner_image"> 
								</div>
                            </div>
							 <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="form-label mt-2">Status</label>
                                    <select class="col-sm-12" id="status" name="status" data-parsley-trigger="change" data-parsley-errors-container="#status_error" placeholder="Status" required>
                                        <option></option>
                                        <option value="0" <?= isset($status) && $status == 0 ? 'selected' : '' ?>>InActive</option>
                                        <option value="1" <?= isset($status) && $status == 1 ? 'selected' : '' ?>>Active</option>
                                    </select>
                                    <div id="status_error"></div>
                                </div>
                            </div>
							
                           <?php /* ?> <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label for="category_status"><?= $page_lang->status; ?></label>
                                    <?php $category_status = array("1" => $page_lang->active, "0" => $page_lang->inactive);
                                        echo form_dropdown('status',$category_status,null,array('id' => 'status', 'class' => 'form-control'));
                                    ?>
							    </div>
							</div><?php */ ?>
							
                        </div>
                    </div>
                    <div class="card-footer">
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
</script>
