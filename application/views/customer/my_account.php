<style>
#CloseEdit{display:none}
</style>
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
                    <h4><i class="uil uil-user"></i>My Profile</h4>
                  </div>
                </div>
                <div class="col-lg-12 col-md-12">
                  <div class="pdpt-bg">
                    <div id="CloseEdit">
                    <div class="pdpt-title">
                      <h4>Update Personal Information <a href="javascript:void(0)" id="CloseProfile" title="Close" class="text-orange float-right"><i class="uil uil-multiply"></i>Close</a></h4>
                    </div>
                    <div class="ddsh-body">
                      <div id="UpdateProfile"></div>
                      <form class="write-reviewform" method="post" id="UserProfile" onsubmit="return update_profile(this, event);">
                        <div class="row">
                          <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">First Name</label>
                               <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                              <input class="form-control" type="text" name="fname" value="<?= isset($account_detail->fname) ? $account_detail->fname : ''; ?>" id="Fname" required="" maxlength="20" placeholder="Your First Name" />
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">Last Name</label>
                              <input class="form-control" type="text" name="lname" value="<?= isset($account_detail->lname) ? $account_detail->lname : ''; ?>" id="lname" required="" placeholder="Your Last Name" maxlength="20"  />
                            </div>
                          </div>
                         <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">Email Address</label>
                              <input class="form-control" type="email" name="email" value="<?= isset($account_detail->email) ? $account_detail->email : ''; ?>" id="email" required="" placeholder="Your Email Address" maxlength="40"  />
                            </div>                          
                         </div>
                          <div class="col-lg-6">
                            <div class="form-group mt-1">
                              <label class="control-label">Phone Number</label>
                              <input class="form-control" type="text" name="phonenumber" value="<?=$account_detail->mobile; ?>" id="phone" required="" placeholder="Your Phone Number" readonly maxlength="10"  />
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
                            <button class="save-btn14 hover-btn mb-0" id="CancelForm" onclick="Cancel()" type="button">Cancel</button>
                          </div>
                        </div>
                      </form>
                    </div>  
                    </div>
                    
                <!--this form start for show personal information-->
                    <div id="Personal_Information">
                      <div class="pdpt-title">
                      <h4>Personal Information <a href="javascript:void(0)" id="EditProfile" title="Edit" class="text-orange float-right"><i class="uil-edit-alt pr-1"></i>Edit</a></h4>
                    </div>
                    <div class="ddsh-body">
                      <form class="write-reviewform">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="form-group mt-1">
                              <label class="control-label">Full Name</label>
                              <input class="form-control" type="text" name="fname" id="Full_Name" required="" maxlength="64" placeholder="Your First Name" readonly />
                            </div>
                          </div>
			  <div class="col-lg-4">
                            <div class="form-group mt-1">
                              <label class="control-label">Email Address</label>
                              <input class="form-control" type="email" name="emailaddress" id="Email_Address" required="" placeholder="Your Email Address" readonly />
                            </div>                          
			                    </div>
                          <div class="col-lg-4">
                            <div class="form-group mt-1">
                              <label class="control-label">Phone Number</label>
                              <input class="form-control" type="text" name="phonenumber" id="Mobile_No" required="" placeholder="Your Phone Number" readonly />
                            </div>
                          </div>
                          <div class="col-lg-12">
                            <div class="field">
                              <div class="ui radio checkbox chck-rdio mr-4">
                                <input type="radio"  name="gender" value="1" tabindex="0" class="hidden" readonly="readonly" />
                                <label>Male</label>
                              </div>
                              <div class="ui radio checkbox chck-rdio">
                                <input type="radio" name="gender" value="2" tabindex="1" class="hidden" readonly="readonly" />
                                <label>Female</label>
                              </div>
                            </div>
                          </div>
                        </div>
                      </form>
                    </div>
                    </div>
                    <!--this form End for show personal information-->
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
<script src="/assets/js/pages/my_account.js" ></script>


