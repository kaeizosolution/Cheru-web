<div class="page-body">
<div class="page-wrapper">
<?php if( isset($basic) && $basic!='' ){
	//echo "Result Found!";

?>
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
		<?php if( isset($basic->cname) && !empty($basic->cname) ) { ?><h4 class="card-title m-t-10"><?php echo $basic->cname; ?></h4><?php } ?>		
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
	<form class="row" action='<?php echo "/$TYPE/supplier/$action/$supplier_id"; ?>' method="post" enctype="multipart/form-data">   
		<div class="form-group col-md-4 m-t-10">
			<label>First Name</label>
			<input type="text" class="form-control form-control-line" placeholder="Your first name" name="fname" value="<?php
			echo isset($basic->fname) ? $basic->fname :''; ?>" > 
		</div>
		<div class="form-group col-md-4 m-t-10">
			<label>Last Name </label>
			<input type="text" id="" name="lname" class="form-control form-control-line" value="<?php 
			echo isset($basic->lname) ? $basic->lname : ''; ?>" placeholder="Your last name" > 
		</div>  
		
		<div class="form-group col-md-4 m-t-10">
			<label>Address </label>
			<input type="text" class="form-control" name="Address" placeholder="Your Address"  value="<?php echo isset($basic->address) ?$basic->address : ''; ?>" minlength="10" placeholder="" required> 
		</div>
		<div class="form-group col-md-4 m-t-10">
			<label>City </label>
			<input type="text" class="form-control" name="City" placeholder="Your city"  value="<?php echo isset($basic->city) ?$basic->city : ''; ?>" minlength="10" placeholder="" > 
		</div>
		
		<div class="form-actions col-md-12">
			<button type="submit" class="btn btn-info"> <i class="fa fa-check"></i> Save</button>		
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

<?php }else{ ?>
	<div class="row">
	<p class="p-4" style="color:red;">No Result Found!</p> </div>
<?php }
//die; 
?>
<!-- Column -->
</div></div>
