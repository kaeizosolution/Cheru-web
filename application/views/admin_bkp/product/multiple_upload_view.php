<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>image upload</h3>
                </div>
               
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
			   <?php echo form_open_multipart('/admin/product/upload');?>
                <legend>Select Files to Upload:</legend>
                <div class="form-group">
                    <input name="usr_files[]" type="file" multiple="" />
                    <span class="text-danger"><?php if (isset($error)) { echo $error; } ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" value="Upload" class="btn btn-primary btn-block"/>
                </div>
            <?php echo form_close(); ?>
            <?php if (isset($success_msg)) { echo $success_msg; } ?>
			
                </div>
            </div>
        </div>
    </div>
</div>

