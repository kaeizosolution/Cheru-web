  <section>
  	<img src="/assets/frontend/images/p_banner.jpg" class="img-fluid w-100" alt="">
  </section>
		
	<section>
		  
		  <div class="container">
		
<nav aria-label="breadcrumb">
  <ol class="breadcrumb text-uppercase">
    <li class="breadcrumb-item"><a href="<?= base_url(); ?>"><?= $page_lang->home; ?></a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $page_lang->my_account; ?></li>
  </ol>
</nav>
			 
	<div class="row">
	 
   <?php include('profile_left_menu.php');?>

	<div class="col-md-9">
		<div class=" card border ac-detail">
			<h3><?= $page_lang->change_password; ?> </h3>
      <hr>
			<div class="row">
        <div class="col-md-12">
          <?php if ((isset($message) && $message['error']!='') || validation_errors()!='') { ?>
            <div class="alert alert-danger">
                <?= validation_errors();
                    if($message['error']!=''){
                        echo $message['error'];
                    }
                ?>
            </div>
            <?php } ?>
              <div id="SubmitChamngePassword"></div>
          </div>
				<div class="col-md-6 offset-md-3">
          <form method="POST" id="ChamngePassword" >
					   <div class="ac-box border my-form">
              <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                  <div class="form-group col-md-12">
                    <label for="inputPassword4"><?= $page_lang->new_password; ?> </label>
                    <input type="password" class="form-control" name="password" placeholder="<?= $page_lang->password; ?>" value="" data-parsley-errors-container="#password_error" minlength="3"  maxlength="15" data-parsley-error-message="Please fill the passowrd" />
                  </div>
                  <div class="form-group col-md-12">
                    <label for="inputPassword4"><?= $page_lang->confirm_new_password; ?> </label>
                    <input type="password" class="form-control" name="confirm_password" id="ConfirmPassword" placeholder="<?= $page_lang->password; ?>" value="" data-parsley-errors-container="#password_error" minlength="3" maxlength="15" data-parsley-error-message="Confirm Password must be same as new password" />
                  </div>
                  <div class="form-group col-md-12">	
                <button class="btn btn-primary w-100" type="submit" name="submit"><?= $page_lang->submit; ?></button>
						  </div>
            </div>
          </form>

					</div>
				
				</div>
	
			</div>
		</div>
	</div>
	</div>
</div>
</section>	  


<script type="text/javascript">
  
$(document).on('ready', function() {
$('#SubmitChamngePassword').hide();
$('#ChamngePassword').on('submit', function (e) {
    e.preventDefault();
      $.ajax({
        type: 'POST',
        url: '/customer/profile/change-password',
        data: $('#ChamngePassword').serialize(),
        dataType:"json",
        success: function (response) {          
                if(response.status == '1'){
                    $('#SubmitChamngePassword').html(response.message);
                    $("#SubmitChamngePassword").addClass( "alert alert-success");
                    setTimeout(function() { 
                            $('.alert-success').fadeOut('fast'); 
                    }, 3000); 
                    $("#ChamngePassword").trigger("reset");
                    $('#SubmitChamngePassword').show();
                }else{
                    $('#SubmitChamngePassword').show();
                    $('#SubmitChamngePassword').html(response.message);
                    $("#SubmitChamngePassword").addClass( "alert alert-danger");
                     setTimeout(function() { 
                            $('.alert-success').fadeOut('fast'); 
                    }, 3000);
                }
            }
        });
    });
});


</script>
		  
	
