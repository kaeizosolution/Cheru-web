<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="content-body">
<div class="container-fluid">
<?php if( isset($basic) && $basic!='' ){ ?>
	<div class="row page-titles"><?= $breadcrumbs ?></div>
	<div class="row">
		<div class="card">
		<!-- Nav tabs -->
		<ul class="nav 11nav-tabs profile-tab" role="tablist">
			<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab" style="font-size: 14px;">  Personal Info </a> </li>
		</ul>
			<!-- Tab panes -->
			<div class="tab-content">
				<div class="tab-pane active" id="home" role="tabpanel">					
					<div class="row">
							<div class="col-md-4">
								<div class="card">
									<div class="card-body">
										<center class="m-t-10">
										<?php  if(isset($basic->logo)){			
											$img_url = base_url().'assets/images/'.$basic->logo;
										}else{
											$img_url = base_url().'assets/images/MIF1041.jpg';}
										
										?>
										<img src="<?php echo $img_url ?>" class='img-circle' width="150" />
										
										<?php if( isset($basic->fname) && !empty($basic->fname) ) { ?><h4 class="card-title m-t-10"><?php echo $basic->fname; ?></h4><?php } ?>		
										</center> 
									</div>
									<div>
									<hr> </div>
									<div class="card-body">
									
									<?php if( isset($basic->email) && !empty($basic->email) ) { ?><small class="text-muted"><?= $page_lang->email; ?></small><h6><?php echo $basic->email; ?></h6><?php } ?>
									
									<?php if( isset($basic->mobile) && !empty($basic->mobile) ) { ?><small class="text-muted p-t-30 db"><?= $page_lang->mobile; ?></small><h6><?php echo $basic->mobile; ?></h6><?php } ?> 														
									</div>
								</div>                                                    
							</div>
							<div class="col-md-8">
								<form class="row" action='<?php echo "/$TYPE/supplier/$action/$supplier_id"; ?>' method="post" enctype="multipart/form-data">   
									<div class="form-group col-md-4 m-t-10">
										<label><?= $page_lang->first_name; ?></label>
										<input type="text" class="form-control form-control-line" placeholder="<?= $page_lang->first_name; ?>" name="fname" value="<?php
										echo isset($basic->fname) ? $basic->fname :''; ?>" readonly> 
									</div>
									<div class="form-group col-md-4 m-t-10">
										<label><?= $page_lang->last_name; ?></label>
										<input type="text" id="" name="lname" class="form-control form-control-line" value="<?php 
										echo isset($basic->lname) ? $basic->lname : ''; ?>" placeholder="<?= $page_lang->last_name; ?>" readonly> 
									</div>  
								
								</form>
							</div>
					</div>			
				</div>


			   
			</div>
		</div>
	</div>

<?php }else{ ?>
	<div class="row"><p class="p-4" style="color:red;">No Result Found!</p> </div>
<?php }
//die; 
?>
<!-- Column -->
</div></div>
