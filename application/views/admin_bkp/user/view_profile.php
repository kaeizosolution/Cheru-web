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

<div class="container-fluid">
<div class="row">
<div class="col-lg-12 col-xlg-12 col-md-12">
<div class="card">
<!-- Nav tabs -->
<ul class="nav nav-tabs profile-tab" role="tablist">
<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab" style="font-size: 14px;">  Personal Info </a> </li>
<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab" style="font-size: 14px;"> Shippping Address </a> </li>                              

<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#password1" role="tab" style="font-size: 14px;"> Billing address</a> </li>                                

</ul>
<!-- Tab panes -->

<div class="tab-content">
<div class="tab-pane active" id="home" role="tabpanel">
<div class="card">
<div class="card-body">
<div class="row">
<div class="col-md-4">
<div class="card">
	<div class="card-body">
		<center class="m-t-10">
		<img src="<?php echo base_url(); ?>assets/images/MIF1041.jpg" class='img-circle' width="150" />
		<?php if( isset($basic->fname) && !empty($basic->fname) ) { ?><h4 class="card-title m-t-10"><?php echo $basic->fname .' '.$basic->lname; ?></h4><?php } ?>		
		</center>
	</div>
	<div>
	<hr> </div>
	<div class="card-body">
	
	<?php if( isset($basic->email) && !empty($basic->email) ) { ?><small class="text-muted">Email address </small><h6><?php echo $basic->email; ?></h6><?php } ?>
	
	<?php if( isset($basic->mobile) && !empty($basic->mobile) ) { ?><small class="text-muted p-t-30 db">Phone</small><h6><?php echo $basic->mobile; ?></h6><?php } ?> 														
	</div>
</div>                                                    
</div>
<div class="col-md-8">
<form class="row" action="Update" method="post" enctype="multipart/form-data">   
	<div class="form-group col-md-4 m-t-10">
		<label>First Name</label>
		<input type="text" class="form-control form-control-line" placeholder="Your first name" name="fname" value="<?php
		echo isset($basic->fname) ? $basic->fname :''; ?>" readonly> 
	</div>
	<div class="form-group col-md-4 m-t-10">
		<label>Last Name </label>
		<input type="text" id="" name="lname" class="form-control form-control-line" value="<?php 
		echo isset($basic->lname) ? $basic->lname : ''; ?>" placeholder="Your last name" readonly> 
	</div>  
	
	<div class="form-group col-md-4 m-t-10">
		<label>Contact Number </label>
		<input type="text" class="form-control" name="contact" placeholder="Your phone" readonly value="<?php echo isset($basic->mobile) ?$basic->mobile : ''; ?>" minlength="10" maxlength="15" placeholder="" required> 
	</div>
	<?php if($order_history){ ?>
	<div class="form-group col-md-4 m-t-10">		
		<label>Order Date </label>
		<ul>
		<?php if ( isset($order_history) && !empty($order_history) ){
				foreach($order_history as $key=>$val){ ?>
				<li class="form-control" id="<?php echo isset($val->date_created) ?$val->date_created : ''; ?>"><?php echo isset($val->date_created) ?$val->date_created : ''; ?></li> 
		<?php }
		} ?>
		</ul>
	</div>	
	<div class="form-group col-md-4 m-t-10">		
		<label>Order Number </label>
		<ul>
		<?php if ( isset($order_history) && !empty($order_history) ){
				foreach($order_history as $key=>$val){ ?>
				<li class="form-control" id="<?php echo isset($val->order_id) ?$val->order_id : ''; ?>"><?php echo isset($val->order_id) ?$val->order_id : ''; ?></li> 
		<?php }
		} ?>
		</ul>
	</div>	
	<?php }else{		
		echo '<div class="row"><p class="p-4" style="color:red">Order not found!</p></div>';
	} ?>
	
</form>
</div>
</div>
</div>
</div>
</div>
<!--second tab-->
<div class="tab-pane" id="profile" role="tabpanel">
<div class="card">
<?php if( isset($shipping_addr) && !empty($shipping_addr)){ ?>
<div class="card-body">
<h3 class="card-title">Shipping Info</h3>
<div class="row" id="ShippingAddress">
<?php $i=1; foreach($shipping_addr as $shipping){ ?>
	<div class="col-md-6" id="shippind_add-<?php echo $shipping['shipping_address_id']; ?>">
		<div class="ac-box border">
			<h2><?php echo $i.'.'; ?>shipping address</h2><hr>
			<p><label><strong>Name: </strong> 	</label><?php echo $shipping['shipping_first_name'] . $shipping['shipping_last_name']; ?></p>
			<p><label><strong>Mobile: </strong>	</label><?php echo $shipping['shipping_mobile']; ?></p>
			<p><label><strong>Street: </strong> 	</label><?php echo $shipping['shipping_street']; ?></p>
			<p><label><strong>City: </strong> 	</label><?php echo $shipping['shipping_city']; ?></p>
			<p><label><strong>Pin: </strong>		</label><?php echo $shipping['shipping_postcode']; ?></p>
			<p><label><strong>Shipping address 1: </strong> </label><?php echo $shipping['shipping_address_1']; ?></p>			
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
<h3 class="card-title">Billing Info</h3>
<div class="row" id="billingAddress">
<?php foreach($billing_addr as $shipping){ ?>
	<div class="col-md-6" id="billing_add-<?php echo $shipping['customer_id']; ?>">
		<div class="ac-box">
			
			<p><label><strong>Name: </strong> </label><?php echo $shipping['fname'] . $shipping['lname']; ?></p>
			<p><label><strong>Mobile: </strong> </label><?php echo $shipping['mobile']; ?></p>
			<p><label><strong>Street: </strong> </label><?php echo $shipping['street']; ?></p>
			<p><label><strong>City: </strong> </label><?php echo $shipping['city']; ?></p>
			<p><label><strong>Pin: </strong> </label><?php echo $shipping['zip_code']; ?></p>
			<p><label><strong>Billing Address: </strong> </label><?php echo $shipping['address']; ?></p>			
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
