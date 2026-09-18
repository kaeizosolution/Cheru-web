<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Add new</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
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

                    <?php //echo form_open_multipart("/$TYPE/page/$action/$page_id", array('id' => 'postForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'addpost')) ?>
                    
					<form method="POST" class="addpost" id="postForm" action='<?php echo "/$TYPE/user/$action/$customer_id"; ?>' enctype="multipart/form-data" autocomplete='off'>
					
					<div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label">First name</label>								
                                    <input type="text" name="fname" class="form-control" value="<?= isset($fname) ? $fname : '' ?>" placeholder="First name" onkeypress="return check(event)" required>
                                	<?php if(isset($customer_uid)) { ?><span>Permalink: <a target="_blank" href="<?php echo site_url('/page/').$customer_uid; ?>"><?php echo isset($customer_uid) ? $customer_uid : '' ?></a></span><?php } ?>
								</div>
                            </div>
							<div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label">Last name</label>								
                                    <input type="text" name="lname" class="form-control" value="<?= isset($lname) ? $lname : '' ?>" placeholder="Last name" onkeypress="return check(event)">
                                	
								</div>
                            </div>
							<div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label">Email </label>								
                                    <input type="email" name="email" class="form-control" id="mailer_validate" value="<?= isset($email) ? $email : '' ?>" placeholder="Email Id" required>
                                	
								</div>
                            </div>
							
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label">Description</label>
                                    <textarea name="user_description" rows="5" class="form-control" placeholder="Write Something..." ><?= isset($user_description) ? $user_description : '' ?></textarea>
                                </div>
                            </div>
							<div class="col-sm-6 col-md-4">
                                <div class="form-group"> <br >
								<label class="form-label">Mobile </label>	 							
                                    <input type="text" name="mobile" class="form-control" id="mob" value="<?= isset($mobile) ? $mobile : '' ?>" placeholder="Mobile">
                                <div id="phone-error" style="display:none;"></div>	
								</div>
                            </div>
							
							
							 <div class="col-sm-6 col-md-4">
								<div class="form-group"><br >
								<label>Role Type</label>	
								<?php $role = isset($role) ? $role : ''; ?>	
								<select class="form-control" name="role" required="required">
								      <option value="">Select Status</option>								  
									  <option value="subscribe" <?php if($role=='subscribe') echo "selected=selected"; ?>>Subscribe</option>                          
									  <option value="administrator" <?php if($role=='administrator') echo "selected=selected"; ?>>administrator</option>
									  <option value="author" <?php if($role=='author') echo "selected=selected"; ?>>Author</option>
									  <option value="customer" <?php if($role=='customer') echo "selected=selected"; ?>>Customer</option>	
								</select>
								</div>  
							</div> 
							<div class="col-sm-6 col-md-4"><br >
								<label class="col-form-label pt-0">Password</label>
								<input type="password" class="form-control form-control-lg" placeholder="Password" id="password" name="password" type="password" value="<?= isset($password) ? $password : '' ?>" required >
							</div>
							<!--<div class="form-group">
								<label class="col-form-label pt-0">Confirm Password</label>
								<input type="password" class="form-control form-control-lg" placeholder="Confirm Password" id="confirm_password" name="confirm_password" type="password" required data-parsley-equalto="#password" data-parsley-error-message="Password and Confirm password should be same" >
							</div>-->
							<div class="form-group col-md-3 m-t-20">
								<label>Profile image (Size:515X512 50KB) </label>
								<input type="file" name="featured_image" id="featured_image" class="form-control" value=""> 								
								<label for="image_url1" id="img_error" style="display:none;"  generated="true" class="error">Please enter a value with a valid extension  and image size maximum 50kb.</label>										
								<?php $featured_image = isset($featured_image) ? $featured_image : ''; 
								if($featured_image){
								?>
								<img src="http://klentano.msdev.in/assets/images/<?php echo $featured_image; ?>" width="150px" height="150px" alt="feature image" />
								<?php } ?>
							</div>		
						    <div class="col-sm-6 col-md-4">
								<div class="form-group"><br >
								<label>Status</label>	
								<?php $status = isset($status) ? $status : ''; ?>									
								<select class="form-control" name="status" required="required">
								  <option value="">Select Status</option>								  
								  <option value="1" <?php if($status=='1') echo "selected=selected"; ?>>Active</option>                          
								  <option value="0" <?php if($status=='0') echo "selected=selected"; ?>>Inactive</option>
								</select>
								</div>  
							</div>   
							
                        </div>
                    </div>
                    <div class="card-footer text-right">
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


<script>
//check image validateion
$("#postForm").click(function(){
 $("#img_error").hide();
    var flag=0;
    var files = $("#image_url").get(0).files;
	
	var img_name = $('#image_url').val();
	
    if(img_name!=''){
	var ext = $('#image_url').val().split('.').pop().toLowerCase();

    if($.inArray(ext, ['gif','png','jpg','jpeg','pdf']) == -1){
       flag=1;
    }

    for(var i=0; i<files.length;i++) {

        var file = files[i];
        var name = file.name;
        var size = file.size;
        if(size>600000){
            flag=1;
        }
      }
    if(flag==1){
        $("#img_error").show();
        return false;
        }
    }
})
</script> 
