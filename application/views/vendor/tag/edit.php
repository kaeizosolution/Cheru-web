<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Edit Tag</h3>
                </div>
                <?php $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
                   
				 <form role="form" action="<?php echo site_url('admin/tags/update')?>" method="post">
					<div class="card-body">
						<?php echo $this->session->flashdata('message');?>
						<?php echo validation_errors(); ?>
							
                        <div class="row">
						  <?php echo message_box(validation_errors(),'danger'); ?>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                <label for="tag_name">Tag Name</label>
                                <input type="text" name="name" class="form-control" id="tag_name" placeholder="Name" 
                                       value="<?php echo set_value('name', isset($tag['name']) ? $tag['name'] : '') ?>">
								</div>
                            </div>
							<div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="tag_status">Status</label>
                                <?php 
                                $tag_status = array("0" => "Inactive", "1" => "Active");
                                echo form_dropdown('status',$tag_status, isset($tag['status']) ? $tag['status'] : '',array('class' => 'form-control'));
                                ?>
                            </div> </div>
														
                        </div>
                    </div>
                    <div class="card-footer text-right">
						<input type="hidden" name="id" value="<?php echo $tag['id']?>">
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
