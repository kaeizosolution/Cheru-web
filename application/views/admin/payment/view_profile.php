<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
<div class="page-wrapper">
<?php if( isset($basic) && $basic!='' ){
	//echo "Result Found!";

?>
<div class="page-header"><div class="row">
<div class="col-lg-6 col-xlg-6 col-md-6"><h3 class="text-themecolor"><i class="fa fa-user-secret" style="color:#1976d2"></i> 
<?php echo $name = (isset($basic->title) && $basic->title!='') ? ($basic->title .' '.$basic->title) : ''; ?></h3></div>
<?= $breadcrumbs ?>
</div>
</div>

<div class="container-fluid">
<div class="row">
<div class="col-lg-12 col-xlg-12 col-md-12">
<div class="card">
<!-- Nav tabs -->
<ul class="nav nav-tabs profile-tab" role="tablist">
<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab" style="font-size: 14px;"><?= $page_lang->payment_info; ?></a> </li>
                              

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
		<?php if( isset($basic->title) && !empty($basic->title) ) { ?><h4 class="card-title m-t-10"><?php echo $basic->title; ?></h4><?php } ?>		
		</center> 
	</div>
	<div>
	<hr> </div>
	<div class="card-body">
	
	<?php if( isset($basic->description) && !empty($basic->description) ) { ?><small class="text-muted"><?= $page_lang->description; ?> </small><h6><?php echo $basic->description; ?></h6><?php } ?>
															
	</div>
</div>                                                    
</div>
<div class="col-md-8">
	<form class="row" action='<?php echo "/$TYPE/payment/$action/$payment_id"; ?>' method="post" enctype="multipart/form-data">   
		<div class="form-group col-md-6 m-t-10">
			<label><?= $page_lang->title; ?></label>
			<input type="text" class="form-control form-control-line" placeholder="<?= $page_lang->title; ?>" name="title" value="<?php
			echo isset($basic->title) ? $basic->title :''; ?>" > 
		</div>
		<div class="form-group col-md-6 m-t-10">
			<label> <?= $page_lang->description; ?></label>
					
			 <textarea name="description" rows="12" class="form-control" placeholder="<?= $page_lang->description; ?>..." ><?php echo isset($basic->description) ? $basic->description : ''; ?></textarea>
			 
		</div>  		
		<?php /* ?><div class="form-actions col-md-12">
			<button type="submit" class="btn btn-info"> <i class="fa fa-check"></i><?= $page_lang->save; ?></button>		
		</div><?php */ ?>
		
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
