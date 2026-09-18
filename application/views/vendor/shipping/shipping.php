
        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
				<div class="row page-titles">
					<?= $breadcrumbs ?>	
                </div>
                <!-- row -->
                <div class="row">
					<?php if ($this->session->flashdata('error')) { ?>
                     <div class="alert alert-danger">
                         <?= $this->session->flashdata('error')?>
                     </div>
                     <?php } ?>

                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('success')?>
                    </div>
                    <?php } ?>

					<?php echo form_open("/$TYPE/shipping/$action/$shipping_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>

					<div class="col-md-8 col-sm-12">
						<div class="col-md-12 col-sm-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title">Shipping</h4>
								</div>
								<div class="card-body">
									<div class="basic-form">
										<div class="row">
											
											<div class="form-group col-md-6">
												<label class="form-label">Name</label>
												<input type="text" class="form-control input-default " placeholder="Name" name="name" value="<?= isset($name) ? $name : '' ?>" required>
											</div>
											<div class="form-group col-md-6">
												<label class="form-label">Rate</label>
												<input type="text" class="form-control input-default " placeholder="Enter Rate" name="rate" value="<?= isset($rate) ? $rate : '' ?>" required>
											</div>
											
										</div>
										
										<hr>
											
											<div class="mb-3">
												<label class="form-label">Description</label>
												<textarea rows="4" class="form-control" placeholder="Description" name="description"><?= isset($description) ? $description : '' ?></textarea>
											</div>
										
										
									</div>
								</div>
							</div>
						</div>
						
					</div>
					
					<div class="col-md-4 col-sm-12">
						<div class="basic-form">
								<div class="mb-3 row">
									<div class="col-sm-12">
										<a type="submit" class="btn btn-outline-primary btn-block" href="">Back</a>
									</div>
									<div class="col-sm-12 mt-2">
										<button type="submit" class="btn btn-primary btn-block">Submit</button>
									</div>
								</div>
						</div>	
					</div>
					<?php echo form_close() ?>
				</div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

