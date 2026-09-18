<!--this is for the header category -->
<?php include('header_cat.php'); ?>
<?php $addr_lang = get_page_language_data('address_lang'); ?>
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
                    <h4><i class="uil uil-location-point"></i><?= $addr_lang->manage_addresses ?? 'Manage Addresses' ?></h4>
                  </div>
                </div>
                <div class="col-lg-12 col-md-12">
                  <div class="pdpt-bg">
                    <div class="pdpt-title">
              			<h4 class="close-address"><?= $addr_lang->my_address ?? 'My Address' ?> <a href="javascript:void(0)" onclick="EditAddress()" id="Add_Address" class="text-orange float-right" data-toggle="collapse">
              			<span class="without-close"><i class="fa fa-plus pr-1"></i><?= $addr_lang->add_new_address ?? 'Add New Address' ?></span>
              			<span class="collapsed-close"><i class="uil uil-multiply"></i></span>
              </a></h4>
		            <div id="msgaddress"></div>
                    </div>
                    <div class="pdpt-bg collapse addNewAddress" id="add-address">
                      <div class="ddsh-body pt-0">
                        <div class="checout-address-step">
                          <div class="row">
                            <div class="col-lg-12">
                              <form class="" id="Address">
                                <div class="form-group">
                                  <div class="product-radio">
                                    <ul class="product-now">
                                      <li>
                                        <input type="radio" id="ad1" name="loc_type" value="2" checked />
                                        <label for="ad1"><?= $addr_lang->address_type_home ?? 'Home' ?></label>
                                      </li>
                                      <li>
                                        <input type="radio" id="ad2" name="loc_type" value="1" />
                                        <label for="ad2"><?= $addr_lang->address_type_office ?? 'Office' ?></label>
                                      </li>
                                      <li>
                                        <input type="radio" id="ad3" name="loc_type" value="3" />
                                        <label for="ad3"><?= $addr_lang->address_type_other ?? 'Other' ?></label>
                                      </li>
                                    </ul>
                                  </div>
                                </div>
                                <div class="address-fieldset">
                                  <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                      <div class="form-group">
                                        <label class="control-label"><?= $addr_lang->name ?? 'Name' ?><span class="mandatory">*</span></label>
                              					<input type="hidden" name="csrf_im_token" value="<?= $csrf->hash; ?>" />
                              					<input type="hidden" name="shipping_address_id" value="" id="ShippingAddressId" />
                              					<input type="hidden" name="latitude" value="" id="latitude_check" />
                              					<input type="hidden" name="longitude" value="" id="longitude_check" />
                              					<input type="hidden" name="place_id" value="" id="place_id" />
                                        <input id="Fullname" name="name" type="text" data-parsley-pattern="^[a-zA-Z\s+]+$" data-parsley-error-message="<?= $addr_lang->please_enter_valid_name ?? 'Please enter valid name.' ?>"  placeholder="<?= $addr_lang->name ?? 'Name' ?>" class="form-control input-md" maxlength="25" required="" />
                                      </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                      <div class="form-group">
                                        <label class="control-label"><?= $addr_lang->mobile ?? 'Mobile' ?><span class="mandatory">*</span></label>
                                        <input id="Mobile" onkeypress="return isNumberKey(event)" name="mobile" type="text" placeholder="<?= $addr_lang->mobile ?? 'Mobile no' ?>" class="form-control input-md"  data-parsley-pattern="^((6|7|8|9)[0-9]{9})$" data-parsley-error-message="<?= $addr_lang->please_enter_valid_mobile ?? 'Please enter valid mobile number.' ?>" maxlength="10" required="" />
                                      </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                      <div class="form-group">
                                        <label class="control-label"><?= $addr_lang->address_detail ?? 'Address' ?><span class="mandatory">*</span></label>
                                        <input id="Address1" name="address_1" type="text" placeholder="<?= $addr_lang->address_detail ?? 'Address' ?>" class="form-control input-md" maxlength="150" required="" onfocus="check_initializeAutocomplete()"  autocomplete="off" />
                                      </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                      <div class="form-group">
                                        <label class="control-label"><?= $addr_lang->city ?? 'City' ?><span class="mandatory">*</span></label>
                                        <input id="City" name="city" type="text" placeholder="<?= $addr_lang->city ?? 'City' ?>" class="form-control input-md" maxlength="50" required="" />
                                      </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                      <div class="form-group">
                                        <label class="control-label"><?= $addr_lang->pincode ?? 'Pincode' ?><span class="mandatory">*</span></label>
                                        <input id="PostCode" name="postcode" type="text" placeholder="<?= $addr_lang->pincode ?? 'Pincode' ?>" class="form-control input-md" maxlength="6" required="" />
                                      </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                      <div class="form-group">
                                        <label class="control-label"><?= $addr_lang->flat_house_office_no ?? 'Flat / House / Office No.' ?><span class="mandatory">*</span></label>
                                        <input id="HowToReach" name="how_to_reach" type="text" placeholder="<?= $addr_lang->enter_locality ?? 'Enter Locality' ?>" class="form-control input-md" maxlength="50" required="" />
                                      </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12">
                                      <div class="form-group mb-0">
                                        <div class="address-btns">
                                          <button class="next-btn16 mr-2"><?= $addr_lang->save ?? 'Save' ?></button>
                                          <button type="button" class="save-btn14 hover-btn" onclick="cancel()"><?= $addr_lang->cancel ?? 'Cancel' ?></button>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="ddsh-body" id="ShippingAddress"> 
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

<script src="/assets/js/pages/my_account.js" ></script>
<script src="/assets/js/pages/my_address.js" ></script>
<script>
 var csrf = <?=json_encode($csrf);?>;
</script>


