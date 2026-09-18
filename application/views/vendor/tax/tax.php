
        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
				<div class="row page-titles">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
						<li class="breadcrumb-item"><a href="tax.html">Tax</a></li>
						<li class="breadcrumb-item active"><a href="javascript:void(0)">Add New Tax</a></li>
					</ol>
                </div>
                <!-- row -->
                <div class="row">
					<?php if (($this->session->flashdata('error')) || validation_errors()!='') { ?>
                    <div class="alert alert-danger">
                        <?= validation_errors();?>
                        <?= $this->session->flashdata('error')?>
                    </div>
                    <?php } ?>

                    <?php if($this->session->flashdata('success')){?>
                       <div class="alert alert-success">
                            <?= $this->session->flashdata('success')?>
                       </div>
                    <?php } ?>

					<?php echo form_open("/$TYPE/tax/$action/$tax_id", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'card')) ?>

					<div class="col-md-8 col-sm-12">
						<div class="col-md-12 col-sm-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title">Tax</h4>
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
											<div class="form-group col-md-12 mt-3">
												<label class="form-label">Tax Type</label>
												<select class="dropdown-groups" name="type" data-parsley-trigger="change" data-parsley-errors-container="#type_error" required>
														<option value=""></option>
														<option value="1" <?= isset($type) && $type == '1'  ? 'selected' : '' ?>>Fixed</option>
                                        <option value="2" <?= isset($type) && $type == '2'  ? 'selected' : '' ?>>Percentage</option>
												</select>
												<div id="type_error"></div>
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
										<a type="submit" class="btn btn-outline-primary btn-block" href="tax.html">Back</a>
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
<script>
$(document).ready(function() {
    $('select').select2({allowClear: true, placeholder: "Tax Type"});
});
</script>
