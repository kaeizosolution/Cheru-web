<!-- Agent Table Start -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
            <div class="clearfix"></div>

            <div class="row">
           
              <div class="col-md-12 col-sm-12 ">
                <div class="x_panel">
                    <?php if(!empty($this->session->flashdata('message'))) {?>
                            <div align="center" role="alert">
                                <?php echo $this->session->flashdata('message'); ?>
                            </div>
                   <?php  } ?>

                  <div class="x_title">
                    <h2>Tag</h2>&nbsp;&nbsp;&nbsp; <a class="cat-button" href="/admin/tags/add"> <span class="btn btn-light btn-sm">Add New</span></a>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="row">
                          <div class="col-sm-12">
                            <div class="card-box table-responsive">
                    <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                            <th>Sl. No.</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th style="width: 100px">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $i=1;
                        $url = base_url();                  
                        if(!empty($tag)):
                        foreach($tag as $val) : ?>
                            <tr>
                                <td><?php echo $i ?></td>                             
                                <td><?php echo $val['name']; ?></td>
                                <td><?php if( $val['status'] == 1 ) echo 'active'; else echo 'Inactive'; ?></td>
                                <td>
                                    <a href="<?php echo site_url('admin/tags/edit/'.$val['id'])?>"><span class="badge bg-green">edit</span></a>
                                    <a href="<?php echo site_url('admin/tags/delete/'.$val['id'])?>" onclick="return confirm('Are you sure?')"><span class="badge bg-red">delete</span></a>
                                </td>
                            </tr>                            
                        <?php  $i++; endforeach;?>
                	<?php else:?>
                            <tr><td colspan="5">No record found</td></tr>
                	<?php endif;?>
                          
                       </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Agent Table End -->

<style>
    span{
    white-space: pre-wrap;
    word-break: break-word;
    width: 200px;
    display: block;
    line-height: 18px;
    }
</style>

    