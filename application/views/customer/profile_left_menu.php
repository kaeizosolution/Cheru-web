<?php $customer_id  = $_SESSION['customer']['login_id']; ?>
<div class="col-lg-3 col-md-3">
<div class="left-side-tabs">
  <div class="user-dt">
    <div class="user-img">
	<?php if(isset($account_detail->user_img) && $account_detail->user_img !=''){ ?> 
		<img id="UserProfile" src="<?= base_url()?>assets/user_profile/<?= $account_detail->user_img; ?>" alt="">
	<?php } else{?>
		<img id="UserProfile" src="/assets/default_images/my-av.jpg" alt="">
	<?php }?>
      <div class="img-add">
        <input type="file" id="file" onchange="uploadimg(event,this);">
        <label for="file"><i class="uil uil-camera-plus"></i></label>
      </div>
    </div>
    <h4 class="mb-0"><?= isset($account_detail->fname) ? $account_detail->fname : 'Hi'; ?></h4>
    <p class=""><?= isset($account_detail->mobile) ? $account_detail->mobile : ''; ?></p>
  </div>
<div class="dashboard-left-links">
  <a href="/<?= $TYPE?>/profile" data-slide="profile" class="user-item "><i class="uil uil-user"></i>My Profile</a>
  <a href="/<?= $TYPE?>/profile/my-orders" data-slide="my-orders" class="user-item "><i class="uil uil-box"></i>My Orders</a> 
  <a href="/<?= $TYPE?>/profile/my-address" data-slide="my-address" class="user-item"><i class="uil uil-location-point"></i>My Address</a> 
  <a href="<?= base_url('wallet'); ?>" data-slide="wallet" class="user-item"><i class="uil uil-wallet"></i>Wallet</a>
  <a href="/<?= $TYPE?>/auth/logout" class="user-item"><i class="uil uil-exit"></i>Logout</a> 
</div>
</div>
<script>
var csrf = <?=json_encode($csrf);?>;
var customer_id = <?= $customer_id;?>;
</script>


