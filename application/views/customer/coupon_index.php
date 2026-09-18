

<?php
$coupon_lang = get_page_language_data('coupon_lang');
$symbol = '$';
$cur_rate = 1.0;
$cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
if ($cc_id > 0) {
	$cur = $this->db
		->select('currency_id, symbol, rate')
		->from('ec_currency')
		->where('currency_id', $cc_id)
		->where('status', '1')
		->get()->row();
	if ($cur) {
		$symbol = (string)$cur->symbol;
		if ((float)$cur->rate > 0) {
			$cur_rate = (float)$cur->rate;
		}
	}
}
?>
<div class="wrapper">
	<div class="imosysnew-Breadcrumb">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="/"><?= $coupon_lang->home ?? 'Home' ?></a></li>
							<li class="breadcrumb-item active" aria-current="page"><?= $coupon_lang->coupon ?? 'Coupon' ?> </li>
						</ol>
					</nav>
				</div>
			</div>
		</div>
	</div>

<?php if(count($top_offers)) { ?>
<div class="section-category">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="main-title-tt pt-4">
					<h2><?= $coupon_lang->offer_in_categories ?? 'Offer in your categories' ?> </h2>				  
				</div>
		  </div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="owl-carousel offerbuy-slider owl-theme owl-loaded owl-drag">
		           	<div class="owl-stage-outer">
						<div class="owl-stage">
							<?php
								$offer_html = '';
								$getbrand     = get_brand();
								$getcategories = categories_name();
								$offerctype    = offer_ctype(); 
								foreach($top_offers as $row)
								{
									$coupon_name = $row->name;
									$dis_type = $row->type == 1 ? ($coupon_lang->flat ?? 'Flat') : '%';
									$symbol_add = $row->type == 1 ? $symbol : '';
									$discount_val = $row->type == 1 ? number_format($row->discount * $cur_rate, 2) : $row->discount;

									$discount	= $symbol_add.' '.$discount_val.' '.$dis_type;
									$prod_image = '/assets/images/no-image.png';
									$getcategories = categories_name($row->category_id);
									$getbrand      = getbrand($row->brand_id);
									//echo "<pre>"; print_r($getbrand); echo "</pre>";
									$brand_html = '';
									if($row->ctype == 1)
									{
										
									 $tanomomy_link= $url = '/category/'.$getcategories->slug;
									 $img = isset($getcategories->banner_image) ? '/assets/categories/'.$getcategories->banner_image : '/assets/images/offer/os.png';	
									
							?>
							
							<div class="owl-item">
								<div class="best-offer-seconditem" id="coupan_id-<?php echo $row->coupon_id ?>">
									<h1 class="banneroffer-title mb-0"><?php echo $discount ?></h1>
									<div class="flat-offer-wrap">
										<div class="best-offer-seconditem-text">
											<h3 class="products-count"><?= $coupon_lang->code_label ?? 'Code :' ?> <span class="text-green"><?php echo $coupon_name ?> </span></h3>
											<h3 class="products-count"><?= $coupon_lang->category_label ?? 'Category:' ?> <span class="text-orange"><a href="<?php echo $tanomomy_link; ?>"><?php echo $getcategories->name ?></a></span></h3>
										</div>
										<div class="best-offer-seconditem-img"> <img src="<?php echo $img; ?>" class="img-fluid"> </div>
									</div>
								</div>
							</div>
							<?php }} ?>	
																				
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
		
	</div>
</div><!--section-category-->
<?php } ?>

<?php if(count($top_offers)) { ?>
<div class="section-brand">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="main-title-tt pt-4">
					<h2><?= $coupon_lang->offer_in_brand ?? 'Offer in your brand' ?> </h2>				  
				</div>
		  </div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="owl-carousel offerbuy-slider owl-theme owl-loaded owl-drag">
		           	<div class="owl-stage-outer">
						<div class="owl-stage">
							<?php
								$offer_html = '';
								$getbrand     = get_brand();
								$getcategories = categories_name();
								$offerctype    = offer_ctype(); 
								foreach($top_offers as $row)
								{
									$coupon_name = $row->name;
									$dis_type = $row->type == 1 ? ($coupon_lang->flat ?? 'Flat') : '%';
									$symbol_add = $row->type == 1 ? $symbol : '';
									$discount_val = $row->type == 1 ? number_format($row->discount * $cur_rate, 2) : $row->discount;

									$discount	= $symbol_add.' '.$discount_val.' '.$dis_type;
									$prod_image = '/assets/images/no-image.png';
									$getcategories = categories_name($row->category_id);
									$getbrand      = getbrand($row->brand_id);
									$brand_html = '';
									if($row->ctype == 2)
									{
										
								   
							?>
							
							<div class="owl-item">
								<div class="best-offer-seconditem" id="coupan_id-<?php echo $row->coupon_id ?>">
									<h1 class="banneroffer-title mb-0"><?php echo $discount ?></h1>
									<div class="flat-offer-wrap">
										<div class="best-offer-seconditem-text">
											<h3 class="products-count"><?= $coupon_lang->code_label ?? 'Code :' ?> <span class="text-green"><?php echo $coupon_name ?> </span></h3>
											<h3 class="products-count"><?= $coupon_lang->brand_label ?? 'Brand:' ?> <span class="text-orange"><?php echo $getbrand->name ?></span></h3>
										</div>
										<div class="best-offer-seconditem-img"> <img src="/assets/images/offer/os.png" class="img-fluid"> </div>
									</div>
								</div>
							</div>
							<?php }} ?>	
																				
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
		
	</div>
</div><!--section-brand-->
<?php } ?>

<?php if(count($top_offers)) { ?>
<div class="section-product">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="main-title-tt pt-4">
					<h2><?= $coupon_lang->offer_in_product ?? 'Offer in your product' ?> </h2>				  
				</div>
		  </div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="owl-carousel offerbuy-slider owl-theme owl-loaded owl-drag">
		           	<div class="owl-stage-outer">
						<div class="owl-stage">
							<?php
								$offer_html = '';
								$getbrand     = get_brand();
								$getcategories = categories_name();
								$offerctype    = offer_ctype(); 
								foreach($top_offers as $row)
								{
									$coupon_name = $row->name;
									$dis_type = $row->type == 1 ? ($coupon_lang->flat ?? 'Flat') : '%';
									$symbol_add = $row->type == 1 ? $symbol : '';
									$discount_val = $row->type == 1 ? number_format($row->discount * $cur_rate, 2) : $row->discount;

									$discount	= $symbol_add.' '.$discount_val.' '.$dis_type;
									$prod_image = '/assets/images/no-image.png';
									$getcategories = categories_name($row->category_id);
									$getbrand      = getbrand($row->brand_id);
									$brand_html = '';
									if($row->ctype == 3)
									{
										$db_product_arr=array();		 
										$db_product_id = json_decode(json_decode($row->product_id));
										$db_product_arr = $this->Query_model->get_data('products', array('product_id'=>$db_product_id));
										//echo "<pre>"; print_r($db_product_arr); echo "<pre>";

										for($i=0; $i<sizeof($db_product_arr); $i++){

										$permalink = '/product/detail/'. $db_product_arr[$i]->post_slug.'-'. $db_product_arr[$i]->product_uid;

										if( $db_product_arr[$i]->type =='simple' ){

											$img_url= get_thumb_link($db_product_arr[$i]->product_id);

										}else{
											
											$variation = $this->Query_model->get_attr_vari_item($db_product_arr[$i]->product_id);
											
											if($variation){
												foreach($variation as $v){
													$arr = explode(',',$v->attribute_item_id);
													if($arr){
														foreach($arr as $a){
															$attribute_item_array[$a] = base_url().'assets/uploads/'.$v->_thumbnail_id;
														}
													}

													$img_array_vrns = (object) array(
															'file_name' => base_url().'assets/uploads/'.$v->_thumbnail_id,
															'url' => base_url().'assets/uploads/'.$v->_thumbnail_id,
													);

												}}
											$img_url = $img_array_vrns->url;
											
										}

									

									?>
							
										<div class="owl-item">
											<div class="best-offer-seconditem post_id-<?php echo $db_product_arr[$i]->product_id ?>" id="coupan_id-<?php echo $row->coupon_id ?>">
												<h1 class="banneroffer-title mb-0"><?php echo $discount ?></h1>
												<div class="flat-offer-wrap">
													<div class="best-offer-seconditem-text">
														<h3 class="products-count"><?= $coupon_lang->code_label ?? 'Code :' ?> <span class="text-green"><?php echo $coupon_name ?> </span></h3>
														<h3 class="products-count" id="id-<?php echo $db_product_arr[$i]->product_id ?>"><?= $coupon_lang->product_label ?? 'product:' ?> <span class="text-orange">
														<a href="<?php echo $permalink ?>" title="<?php echo trim($db_product_arr[$i]->post_title) ?>" ><?php echo $db_product_arr[$i]->post_title ?></span></h3>
													</div>
													<div class="best-offer-seconditem-img"> <img src="<?php echo $img_url; ?>" class="img-fluid" alt="<?= $coupon_lang->product_image_alt ?? 'product image' ?>"> </div>
												</div>
											</div>
										</div>
									<?php }
									}
								} ?>	
																				
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
		
	</div>
</div><!--section-product-->
<?php } ?>


</div><!--Wrapper Area End Here-->








