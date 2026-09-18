<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?php echo $action.'&nbsp;'.$page_lang->supplier; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>.
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
                    <?php if (($this->session->flashdata('error')) || validation_errors()!='') { ?>
                    <div class="alert alert-danger"><?= validation_errors();?>  <?= $this->session->flashdata('error')?>
                    </div>
                    <?php } ?>

                    <?php if($this->session->flashdata('success')){?>
                       <div class="alert alert-success"> <?= $this->session->flashdata('success')?> </div>
                    <?php } ?>
					
					<ul class="nav nav-tabs profile-tab" role="tablist">
						<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab" style="font-size: 14px;"><?= $page_lang->personal_info; ?></a></li>
						<?php if($supplier_id){ ?>
						<li class="nav-item" style="display:none;"> <a class="nav-link" data-toggle="tab" href="#password1" role="tab" style="font-size: 14px;"><?= $page_lang->change_password; ?></a> </li><?php } ?>
						
					</ul>
					
					<div class="tab-content">
					<div class="tab-pane active" id="home" role="tabpanel">
                        <div class="card"><div class="card-body">
										
					<form method="POST" class="addpost" id="postForm" action='<?php echo "/$TYPE/supplier/$action/$supplier_id"; ?>' enctype="multipart/form-data" autocomplete='off'>
					
					<div class="card-body">
                        <div class="row">							
							<div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label"><?= $page_lang->company_name; ?></label>								
                                    <input type="text" name="cname" class="form-control" value="<?php echo isset($cname) ? $cname : '' ?>" placeholder="<?= $page_lang->company_name; ?>" required>
                                	
								</div>
                            </div>
							 <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label"><?= $page_lang->address; ?></label>
                                    <textarea name="address" rows="5" class="form-control" placeholder="<?= $page_lang->address; ?>..." ><?php echo isset($address) ? $address : '' ?></textarea>
                                </div>
                            </div>
							<div class="col-sm-6 col-md-4 p-4">
                                <div class="form-group">
								<label class="form-label"><?= $page_lang->registration_no; ?></label>	 							
                                    <input type="text" name="company_registration_no" class="form-control" id="registration" value="<?php echo isset($company_registration_no) ? $company_registration_no : '' ?>" placeholder="<?= $page_lang->registration_no; ?>">
                                <div id="registratione-error" style="display:none;"></div>	
								</div>
                            </div>
							<div class="col-sm-6 col-md-4 p-4">
                                <div class="form-group"> 
								<label class="form-label"><?= $page_lang->city; ?></label>	 							
                                    <input type="text" name="city" class="form-control onlystring" id="city" value="<?= isset($city) ? $city : '' ?>" placeholder="<?= $page_lang->city; ?>">
                                <div id="city-error" style="display:none;"></div>	
								</div>
                            </div>
							<div class="col-sm-6 col-md-4 p-4" style="display:none;">
                                <div class="form-group"> 
								<label class="form-label"><?= $page_lang->state; ?></label>	 							
                                    <input type="text" name="state" class="form-control" id="state" value="<?= isset($state) ? $state : '' ?>" placeholder="<?= $page_lang->state; ?>">
                                <div id="state-error" style="display:none;"></div>	
								</div>
                            </div>
							<div class="col-sm-6 col-md-4 p-4">
                                <div class="form-group">
								<label class="form-label"><?= $page_lang->zip_code; ?></label>	 							
                                    <input type="text" name="zip_code" class="form-control" id="pincode" value="<?= isset($zip_code) ? $zip_code : '' ?>" placeholder="<?= $page_lang->zip_code; ?>">
                                <div id="zip-error" style="display:none;"></div>	
								</div>
                            </div>
							
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label"><?= $page_lang->first_name; ?></label>								
                                    <input type="text" name="fname" class="form-control onlystring" value="<?= isset($fname) ? $fname : '' ?>" placeholder="<?= $page_lang->first_name; ?>" required>
                                	<div id="fname-error" style="display:none;"></div>
								</div>
                            </div>
							<div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label"><?= $page_lang->last_name; ?></label>								
                                    <input type="text" name="lname" class="form-control onlystring" value="<?= isset($lname) ? $lname : '' ?>" placeholder="<?= $page_lang->last_name; ?>">
                                	
								</div>
                            </div>
							<div class="col-sm-6 col-md-4">
                                <div class="form-group">
								<label class="form-label"><?= $page_lang->mobile; ?></label>	 							
                                    <input type="text" name="mobile" class="form-control" id="mobile_no" value="<?= isset($mobile) ? $mobile : '' ?>" placeholder="<?= $page_lang->mobile; ?>">
                                <div id="mobile_no-erro" style="color:red;"></div>
								</div>
                            </div>
							
							<div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label"><?= $page_lang->email; ?></label>								
                                    <input type="email" name="email" class="form-control" id="mailer_validate" value="<?= isset($email) ? $email : '' ?>" placeholder="<?= $page_lang->email; ?>" required>
                                	
								</div>
                            </div>
							
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label"><?= $page_lang->description; ?></label>
                                    <textarea name="description" rows="5" class="form-control" placeholder="<?= $page_lang->write_something; ?>..." ><?= isset($description) ? $description : '' ?></textarea>
                                </div>
                            </div>
							<div class="form-group col-md-3 m-t-10">
								<label><?= $page_lang->profile_image; ?></label>
								<input type="file" name="logo" id="image_url" class="form-control" value=""> 								
								<label for="image_url" id="img_error" style="display:none;"  generated="true" class="error">Please enter a value with a valid extension  and image size maximum 50kb.</label>										
								<?php $logo = isset($logo) ? $logo : ''; 
								
								//echo "<pre>"; print_r($logo); echo "</pre>";
								//die;
								
								if($logo){
								?>
								<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
										style=" background:none; color:red;" type="button" data-id="<?php echo $supplier_id; ?>"><i class="fa fa-trash"></i></button>	

								<img src="<?php echo base_url();?>assets/images/<?php echo $logo; ?>" width="150px" height="150px" alt="feature image" />
								<?php } ?>
							</div>	
								
						    <div class="col-sm-6 col-md-4">
								<div class="form-group"><br >
								<label><?= $page_lang->status; ?></label>	
								<?php $status = isset($status) ? $status : ''; ?>									
								<select class="form-control" name="status" required="required">
								  <option value=""><?= $page_lang->select_status; ?></option>								  
								  <option value="1" <?php if($status=='1') echo "selected=selected"; ?>><?= $page_lang->active; ?></option>                          
								  <option value="0" <?php if($status=='0') echo "selected=selected"; ?>><?= $page_lang->inactive; ?></option>
								</select>
								</div>  
							</div>   
							
                        </div>
                    </div>
                    <div class="card-footer text-right">
						<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                        <button type="button" class="btn btn-light btn_back"><?= $page_lang->back; ?></button>
                        <button type="submit" id="btn-submit"class="btn btn-primary"><?= $page_lang->submit; ?></button>
                    </div>
                    </form>
					</div></div></div>
					
						<div class="tab-pane" id="password1" role="tabpanel">
						  <div class="card">				                     
							<div class="card-body">  
								<form class="row" action="<?php echo "/$TYPE/supplier/update_password/$supplier_id"; ?>" method="post" enctype="multipart/form-data">
									<div class="form-group col-md-6 m-t-20">
										<label><?= $page_lang->password; ?></label>
										<input type="password" class="form-control" id="pass1" name="new1" value="" required minlength="6" placeholder="<?= $page_lang->password; ?>"> 
									</div>
									<div class="form-group col-md-6 m-t-20">
										<label><?= $page_lang->confirm_password; ?></label>
										<input type="password" name="new2" id="pass2" class="form-control" value="" required minlength="6"<?= $page_lang->confirm_password; ?> > <span id="error" style="color:red;"></span>
									</div>
									<div class="form-actions col-md-12">
										<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
										<button id="confirm2" type="submit" class="btn btn-info"> <i class="fa fa-check"></i><?= $page_lang->save; ?></button>
								
									</div>
								</form></div>
							</div>
						</div>   
						
								
					   </div><!--table end-->
					   
					
                </div>
            </div>
        </div>
    </div>
</div>


<script>

$('#btn-submit').click(function(){
	
	//alert('ddd');
	if(document.getElementById('mobile_no').value != ""){

       var y = document.getElementById('mobile_no').value;
       if(isNaN(y)||y.indexOf(" ")!=-1)
       {
			$("#mobile_no").addClass("parsley-error");
			$("#mobile_no-erro").html('Invalid Mobile No.');
			document.getElementById('mobile_no').focus();
          return false;
       }

       if (y.length>10 || y.length<10)
       {
			$("#mobile_no").addClass("parsley-error");
			document.getElementById("mobile_no-erro").innerHTML = 'Mobile No. should be 10 digit';
			//document.getElementById('mobile_no').focus();
			
            return false;
       }
       if (!(y.charAt(0)=="9" || y.charAt(0)=="8" || y.charAt(0)=="7" || y.charAt(0)=="6"))
       {
          
			$("#mobile_no-erro").html('Mobile No. should start with 6,7,8 or 9');
			$("#mobile_no").addClass("parsley-error");
            document.getElementById('mobile_no').focus();
            return false
       }
 }
	

});


//check image validateion
$("#btn-submit").click(function(){
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
        if(size>1000000){
            flag=1;
        }
      }
    if(flag==1){
        $("#img_error").show();
        return false;
        }
    }
}) 

function validateNumber(event) {
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode === 8 || event.keyCode === 46) {
        return true;
    } else if ( key < 48 || key > 57 ) {
        return false;
    } else {
        return true;
    }
};


$(document).ready(function(){
    $('[id^=pincode]').keypress(validateNumber);
});

$(document).ready(function(){
    $(".onlystring").keypress(function(event){
        var inputValue = event.charCode;
        if(!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0)){
            event.preventDefault();
        }
    });
});

//update query
$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/supplier/update/"+uid+"";
    url_new_tab(e,url);
} );

function url_new_tab(e,url)
{
    if(e.ctrlKey){
        window.open(url);
    }else{
        $(location).attr('href', url);
    }
}

$('#confirm').click(function(){
	//alert('ddd');
	var password = $('#new1').val();
	var confirm = $('#new2').val();
	if(password!=confirm){
		//	alert('error');
	  $("#error").html('password not match');
		return false;
	}
	
})
$('#confirm2').click(function(){
	var pass1 = $('#pass1').val();
	var confirmpass2 = $('#pass2').val();
	if(pass1!=confirmpass2){
	
	  $("#error").html('password not match');
		return false;
	}
	
})

$(document).on('click','.btn-delete', function(e){
	//alert('dd');
	var obj = $(this);
	var id=$(this).attr('data-id');  
	var postData = {};
	var csrf_name = "<?= $csrf->name; ?>";
	var csrf_value = "<?= $csrf->hash; ?>";
	postData[csrf_name] =  csrf_value;
	postData.id = id;
	postData.supplier_id = <?= isset($supplier_id) ? $supplier_id : 0 ?>;
	$.ajax({
			url         :   '<?php echo base_url();?>admin/supplier/delimg',
			type        :   'post',
			enctype	    :   'multipart/form-data',
			dataType    :   "json",  
			data        :   postData,
			success     :   function(data){
				if(data.success)
				{
					if(data.MSG)  {
							swal('Session expired.');
						}		 
					swal('Image Delete'); 
					location.reload(true);
				} 
			}
	});
} );
</script> 
