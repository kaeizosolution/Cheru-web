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
            
               
<div class="row">		

<?php echo form_open_multipart('/admin/product/upload_image');?>

<div class="form-group col-sm-3">
	<label>Choose Files</label>
	<input type="file" class="form-control" name="upload_Files[]" multiple/>				
</div>   
<div class="form-group  col-sm-6">		
	<input  type="submit" class="btn btn-default" name="submitForm" id="submitForm" value="Upload" />	
</div>		
</div
<?php echo form_close(); ?>

> 	
<div class="row ">
<p><?php echo $this->session->flashdata('statusMsg'); ?></p>
</div>
<div class="row">
<div class="gallery">
	<ul>
		<?php if(!empty($gallery)): foreach($gallery as $file): ?>
		<li>
			<img width="200" height="200" src="<?php echo base_url('uploads/files/'.$file['file_name']); ?>" alt="" >
			<p>Uploaded On <?php echo date("j M Y",strtotime($file['created'])); ?></p>
		</li>
		<?php endforeach; else: ?>
		<p>No File uploaded.....</p>
		<?php endif; ?>
	</ul>
</div>
</div>
	   
			   
                
            </div>
        </div>
    </div>
</div>

