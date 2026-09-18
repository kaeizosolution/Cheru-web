<!--this is for the header category -->
<?php include('header_cat.php'); ?>
<!-- this is for the header category -->
  
  <!--Product-Listing Area Start Here-->
  <div class="all-product-imosys">
    <div class="container-fluid">
      <div class="myaccount-body">
        <div class="row">
          <?php include('profile_left_menu.php');?>
          </div>
          <div class="col-lg-8 col-md-8">
            <div class="dashboard-right">
              <div class="row">
                <div class="col-md-12">
                  <div class="main-title-tab">
                    <h4><i class="uil uil-user"></i>My Profile </h4>
                  </div>
                </div>
                <div class="col-lg-12 col-md-12">
                  <div class="pdpt-bg">
                    <div class="pdpt-title">
                      <h4>Edit Personal Information</h4>
                    </div>
                    <div class="ddsh-body">
                      <div id="UpdateProfile"></div>
                      <form class="write-reviewform" method="post" id="UserProfile" onsubmit="return update_profile(this, event);">
                        <div class="row">
                          <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">First Name</label>
                               <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                              <input class="form-control" type="text" name="fname" value="<?= isset($account_detail->fname) ? $account_detail->fname : ''; ?>" id="Fname" required="" maxlength="64" placeholder="Your First Name" />
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">Last Name</label>
                              <input class="form-control" type="text" name="lname" value="<?= isset($account_detail->lname) ? $account_detail->lname : ''; ?>" id="lname" required="" placeholder="Your Last Name" />
                            </div>
                          </div>
                         <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">Email Address</label>
                              <input class="form-control" type="email" name="email" value="<?= isset($account_detail->email) ? $account_detail->email : ''; ?>" id="email" required="" placeholder="Your Email Address"  />
                            </div>                          
                         </div>
                          <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">Phone Number</label>
                              <input class="form-control" type="text" name="phonenumber" value="<?=$account_detail->mobile; ?>" id="phone" required="" placeholder="Your Phone Number" readonly />
                            </div>
                          </div>
                          <div class="col-lg-12">
                            <div class="field">
                              <div class="ui radio checkbox chck-rdio mr-4">
                                <input type="radio" name="gender" <?php if ($account_detail->gender != '2') echo "checked"; ?>  value="1" tabindex="0" class="hidden">
                                <label>Male</label>
                              </div>
                              <div class="ui radio checkbox chck-rdio">
                                <input type="radio" name="gender" <?php if ($account_detail->gender == '2') echo "checked"; ?> value="2" tabindex="1" class="hidden">
                                <label>Female</label>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-12 save-button">
                            <button class="post-btn hover-btn mb-0 mr-2" type="submit">Save</button>
                            <button class="save-btn14 hover-btn mb-0" onclick="Cancel()">Cancel</button>
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
      </div>
    </div>
  </div>
  <!--Product-Listing Area End Here--> 
  
</div>
<!--Wrapper Area End Here-->

<script type="text/javascript">
function update_profile(t, e)
{
  var formdata = new FormData(t); 
  $.ajax({
      type: 'POST',
      url: '/<?= $TYPE; ?>/profile/ajax_get_profile',
      //data: UserProfile,
      data:formdata,
      contentType: false,
      processData: false,
     // dataType:"json",
      success: function (response) {          
              if(response.STATUS == '1'){
                  $('#UpdateProfile').html(response.MSG);
                  $("#UpdateProfile").addClass("alert alert-success");
                  $('#UpdateProfile').show();
                   setTimeout(function() { 
                      $('.alert-success').fadeOut('slow'); 
                  }, 5000);
                  location.href = '<?=base_url();?>customer/profile';
              }else{
                  $('#UpdateProfile').html(response.MSG);
                  $("#UpdateProfile").addClass("alert alert-danger");
                  $('#UpdateProfile').show();
                   setTimeout(function() { 
                      $('.alert-success').fadeOut('slow'); 
                  }, 5000);
                  location.href = '<?=base_url();?>customer/profile/';
              }
          }
      });  
  return false;
}


function Cancel()
{
  location.href = '<?=base_url();?>customer/profile';
}


function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
    {
        return false;
    }
    return true;
}
</script>
