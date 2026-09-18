 <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Edit Tag</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content event">
                    <form role="form" action="<?php echo site_url('admin/tags/edit')?>" method="post">
                        <input type="hidden" name="id" value="<?php echo $tag['id']?>">
                        <div class="box-body">
                            <?php echo $this->session->flashdata('message');?>
                            <?php echo validation_errors(); ?>
                            <div class="form-group">
                                <label for="tag_name">Tag Name</label>
                                <input type="text" name="name" class="form-control" id="tag_name" placeholder="Name" 
                                       value="<?php echo set_value('name', isset($tag['name']) ? $tag['name'] : '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="tag_status">Status</label>
                                <?php 
                                $tag_status = array("0" => "Inactive", "1" => "Active");
                                echo form_dropdown('status',$tag_status, isset($tag['status']) ? $tag['status'] : '',array('class' => 'form-control'));
                                ?>
                            </div>
                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Submit</button> 
                            <button type="button" class="btn btn-default" onclick="javascript:history.back()">Back</button>
                        </div>
                    </form>

                 
                        
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

