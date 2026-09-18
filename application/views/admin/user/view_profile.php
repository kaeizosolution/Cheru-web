<?php $page_lang = get_page_language_data('admin_page_lang');?>
<div class="page-body">
<div class="page-wrapper">
<?php 

if( isset($basic) && $basic!='' ){
//echo "Result Found!"; ?>
<div class="page-header"><div class="row">
<div class="col-lg-6 col-xlg-6 col-md-6"><h3 class="text-themecolor"><i class="fa fa-user-secret" style="color:#1976d2"></i> 
<?php echo $name = (isset($basic->fname) && $basic->fname!='') ? ($basic->fname .' '.$basic->lname) : ''; ?></h3></div>
<?= $breadcrumbs ?>
</div>
</div>

<style>
    .admin-customer-stats{ margin-bottom: 20px; }
    .admin-customer-stats .stat-card{ background:#fff; border-radius:14px; padding:16px 18px; box-shadow:0 6px 18px rgba(0,0,0,0.06); border:1px solid rgba(0,0,0,0.04); }
    .admin-customer-stats .stat-label{ font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; margin:0 0 6px 0; }
    .admin-customer-stats .stat-value{ font-size:22px; font-weight:700; margin:0; color:#1e293b; }

    .admin-readonly-input{ background:#f8fafc !important; border:1px solid #e2e8f0 !important; border-radius:10px !important; padding:10px 12px !important; color:#0f172a; box-shadow:none !important; }
    .admin-readonly-input:focus{ background:#f8fafc !important; border-color:#cbd5e1 !important; box-shadow:none !important; }
    .admin-readonly-label{ font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#64748b; margin-bottom:6px; }

    .admin-profile-card{ border-radius:16px; box-shadow:0 6px 18px rgba(0,0,0,0.06); border:1px solid rgba(0,0,0,0.04); overflow:hidden; }
    .admin-profile-card-header{ display:flex; align-items:center; justify-content:space-between; padding:14px 18px; background:#fff; border-bottom:1px solid rgba(226,232,240,1); }
    .admin-profile-card-title{ margin:0; font-size:16px; font-weight:700; color:#0f172a; }
    .admin-profile-chip{ font-size:12px; color:#334155; background:#f1f5f9; border:1px solid #e2e8f0; padding:6px 10px; border-radius:999px; }
    .admin-profile-card-body{ padding:16px 18px; }
    .admin-profile-avatar{ display:flex; align-items:center; justify-content:center; padding:8px 0 14px; }
    .admin-profile-avatar .avatar-wrap{ width:110px; height:110px; border-radius:999px; background:#fff; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; position:relative; }
    .admin-profile-avatar img{ width:96px; height:96px; border-radius:999px; object-fit:cover; }
    .admin-profile-avatar .cam-badge{ width:34px; height:34px; border-radius:999px; background:#fbbf24; display:flex; align-items:center; justify-content:center; position:absolute; right:-2px; bottom:-2px; border:3px solid #fff; }
    .admin-profile-avatar .cam-badge i{ color:#0f172a; }
    .admin-profile-grid{ margin-top:4px; }
</style>

<div class="container-fluid">
<div class="row">
<div class="col-lg-12 col-xlg-12 col-md-12">

<div class="row admin-customer-stats">
    <div class="col-sm-6 col-md-3 mb-3">
        <div class="stat-card">
            <p class="stat-label">Total Orders</p>
            <p class="stat-value"><?php echo (int)($total_orders ?? 0); ?></p>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <div class="stat-card">
            <p class="stat-label">In Progress</p>
            <p class="stat-value"><?php echo (int)($orders_in_progress ?? 0); ?></p>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <div class="stat-card">
            <p class="stat-label">Delivered</p>
            <p class="stat-value"><?php echo (int)($orders_delivered ?? 0); ?></p>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <div class="stat-card">
            <p class="stat-label">Wishlist Items</p>
            <p class="stat-value"><?php echo (int)($total_wishlist ?? 0); ?></p>
        </div>
    </div>
</div>

<div class="card">
<!-- Nav tabs -->
<ul class="nav nav-tabs profile-tab" role="tablist">
<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab" style="font-size: 14px;"><?= $page_lang->personal_info; ?> </a> </li>
<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab" style="font-size: 14px;"> <?= $page_lang->shipping_address; ?></a> </li>                              

<?php /*?><li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#password1" role="tab" style="font-size: 14px;"><?= $page_lang->billing_address; ?></a> </li> <?php */ ?>                               

</ul>
<!-- Tab panes -->

<div class="tab-content">
<div class="tab-pane active" id="home" role="tabpanel">
<?php
    $full_name = trim((string)($basic->fname ?? '').' '.(string)($basic->lname ?? ''));
?>
<div class="card admin-profile-card">
    <div class="admin-profile-card-header">
        <h5 class="admin-profile-card-title">Profile Information</h5>
        <span class="admin-profile-chip">Primary info for orders &amp; invoices</span>
    </div>
    <div class="admin-profile-card-body">
        <div class="admin-profile-avatar">
            <div class="avatar-wrap">
                <img src="<?php echo base_url(); ?>assets/images/MIF1041.jpg" alt="Profile" />
                <span class="cam-badge"><i class="fa fa-camera"></i></span>
            </div>
        </div>

        <form class="row admin-profile-grid" action="Update" method="post" enctype="multipart/form-data">
            <div class="form-group col-md-6">
                <label class="admin-readonly-label">Full Name</label>
                <input type="text" class="form-control admin-readonly-input" value="<?php echo $full_name; ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label class="admin-readonly-label"><?= $page_lang->email; ?></label>
                <input type="text" class="form-control admin-readonly-input" value="<?php echo isset($basic->email) ? $basic->email : ''; ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label class="admin-readonly-label"><?= $page_lang->mobile; ?></label>
                <input type="text" class="form-control admin-readonly-input" value="<?php echo isset($basic->mobile) ? $basic->mobile : ''; ?>" readonly>
            </div>

            <input type="hidden" name="fname" value="<?php echo isset($basic->fname) ? $basic->fname :''; ?>" />
            <input type="hidden" name="lname" value="<?php echo isset($basic->lname) ? $basic->lname :''; ?>" />
            <input type="hidden" name="contact" value="<?php echo isset($basic->mobile) ? $basic->mobile :''; ?>" />
        </form>
    </div>
</div>
</div>
<!--second tab-->
<div class="tab-pane" id="profile" role="tabpanel">
<div class="card">
<?php if( isset($shipping_addr) && !empty($shipping_addr)){ ?>
<div class="card-body">
<h3 class="card-title"><?= $page_lang->shipping_info; ?></h3>
<div class="row" id="ShippingAddress">
<?php $i=1; foreach($shipping_addr as $shipping){ ?>
	<div class="col-md-6" id="shippind_add-<?php echo $shipping['shipping_address_id']; ?>">
		<div class="ac-box border">
			<h2><?php echo $i.'.'; ?>shipping address</h2><hr>
			<p><label><strong><?= $page_lang->name; ?>: </strong> 	</label><?php echo $shipping['shipping_first_name'];  ?></p>
			<p><label><strong><?= $page_lang->mobile; ?>: </strong>	</label><?php echo $shipping['shipping_mobile']; ?></p>
			<p><label><strong><?= $page_lang->street; ?>: </strong> 	</label><?php echo $shipping['shipping_street']; ?></p>
			<p><label><strong><?= $page_lang->city; ?>: </strong> 	</label><?php echo $shipping['shipping_city']; ?></p>
			<p><label><strong><?= $page_lang->zip_code; ?>: </strong>		</label><?php echo $shipping['shipping_postcode']; ?></p>
			<p><label><strong><?= $page_lang->shipping_address; ?>: </strong> </label><?php echo $shipping['shipping_address_1']; ?></p>			
		</div>
	</div>
	<?php $i++; } ?>
	
	</div>
</div>	
<?php }else{
	echo '<div class="row"><p class="p-4" style="color:red;">No Result Found!</p></div>';
} ?> 
</div></div>
		   
<div class="tab-pane" id="password1" role="tabpanel">
<?php if( isset($billing_addr) && !empty($billing_addr)){?>
<div class="card-body">
<h3 class="card-title"><?= $page_lang->billing_info; ?></h3>
<div class="row" id="billingAddress">
<?php foreach($billing_addr as $shipping){ ?>
	<div class="col-md-6" id="billing_add-<?php echo $shipping['customer_id']; ?>">
		<div class="ac-box">
			
			<p><label><strong><?= $page_lang->name; ?>: </strong> </label><?php echo $shipping['fname'] . $shipping['lname']; ?></p>
			<p><label><strong><?= $page_lang->mobile; ?>: </strong> </label><?php echo $shipping['mobile']; ?></p>
			<p><label><strong><?= $page_lang->street; ?>: </strong> </label><?php echo $shipping['street']; ?></p>
			<p><label><strong><?= $page_lang->city; ?>: </strong> </label><?php echo $shipping['city']; ?></p>
			<p><label><strong><?= $page_lang->zip_code; ?>: </strong> </label><?php echo $shipping['zip_code']; ?></p>
			<p><label><strong><?= $page_lang->billing_address; ?>: </strong> </label><?php echo $shipping['address']; ?></p>			
		</div>
	</div>
	<?php } ?>
</div>	
</div>
<?php }else{
	echo '<div class="row"><p class="p-4" style="color:red;">No Result Found!</p></div>';
} ?> 

</div>
   
</div>
</div>
</div>

<?php }else{ ?>
	<div class="row">
	<p class="p-4" style="color:red;">No Result Found!</p> </div>
<?php }
//die; 
?>
<!-- Column -->
</div></div>
