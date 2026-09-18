<?php
$default_currency = default_currency();
$currency =get_currency($default_currency);
$rate = $currency->rate;
$symbol = $currency->symbol; ?>

<section><img src="<?php echo base_url(); ?>assets/frontend/images/p_banner.jpg" class="img-fluid w-100" alt=""></section>
<section>
    <div class="container">	    
		  <?= $breadcrumbs ?>		
        <hr>
        <div class="row">
            <aside class="filter col-md-3">
                <h2>Filters:<span><a href="#">Clear all</a></span></h2>
                <div class="card mt-3">
                    <article class="filter-group">
                        <div class="card-header">
                            <a href="#" data-toggle="collapse" data-target="#collapse_1" aria-expanded="true" class="">
                                <i class="icon-control fa fa-chevron-down"></i>
                                <h6 class="title">Product type</h6>
                            </a>
                        </div>
                        <div class="filter-content collapse show" id="collapse_1" style="">
                            <div class="card-body">
                                <ul class="list-menu">
                                    <li><a href="#">People  </a></li>
                                    <li><a href="#">Watches </a></li>
                                    <li><a href="#">Cinema  </a></li>
                                    <li><a href="#">Clothes  </a></li>
                                    <li><a href="#">Home items </a></li>
                                    <li><a href="#">Animals</a></li>
                                    <li><a href="#">People </a></li>
                                </ul>
                            </div>
                            <!-- card-body.// -->
                        </div>
                        <hr>
                    </article>
                    <!-- filter-group  .// -->
                    <article class="filter-group">
                        <div class="card-header">
                            <a href="#" data-toggle="collapse" data-target="#collapse_2" aria-expanded="true" class="">
                                <i class="icon-control fa fa-chevron-down"></i>
                                <h6 class="title">Brands </h6>
                            </a>
                        </div>
                        <div class="filter-content collapse show" id="collapse_2" style="">
                            <div class="card-body">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" checked="" class="custom-control-input">
                                    <div class="custom-control-label">Mercedes  
                                        <b class="badge badge-pill badge-light float-right">120</b>  
                                    </div>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" checked="" class="custom-control-input">
                                    <div class="custom-control-label">Toyota 
                                        <b class="badge badge-pill badge-light float-right">15</b>  
                                    </div>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" checked="" class="custom-control-input">
                                    <div class="custom-control-label">Mitsubishi 
                                        <b class="badge badge-pill badge-light float-right">35</b> 
                                    </div>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" checked="" class="custom-control-input">
                                    <div class="custom-control-label">Nissan 
                                        <b class="badge badge-pill badge-light float-right">89</b> 
                                    </div>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input">
                                    <div class="custom-control-label">Honda 
                                        <b class="badge badge-pill badge-light float-right">30</b>  
                                    </div>
                                </label>
                            </div>
                            <!-- card-body.// -->
                        </div>
                        <hr>
                    </article>
                    <!-- filter-group .// -->
                    <article class="filter-group">
                        <div class="card-header">
                            <a href="#" data-toggle="collapse" data-target="#collapse_3" aria-expanded="true" class="">
                                <i class="icon-control fa fa-chevron-down"></i>
                                <h6 class="title">Price range </h6>
                            </a>
                        </div>
                        <div class="filter-content collapse show" id="collapse_3" style="">
                            <div class="card-body">
                                <input type="range" class="custom-range" min="0" max="100" name="">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Min</label>
                                        <input class="form-control" placeholder="$0" type="number">
                                    </div>
                                    <div class="form-group text-right col-md-6">
                                        <label>Max</label>
                                        <input class="form-control" placeholder="$1,0000" type="number">
                                    </div>
                                </div>
                                <!-- form-row.// -->
                            </div>
                            <!-- card-body.// -->
                        </div>
                        <hr>
                    </article>
                    <!-- filter-group .// -->
                    <article class="filter-group">
                        <div class="card-header">
                            <a href="#" data-toggle="collapse" data-target="#collapse_4" aria-expanded="true" class="">
                                <i class="icon-control fa fa-chevron-down"></i>
                                <h6 class="title">Sizes </h6>
                            </a>
                        </div>
                        <div class="filter-content collapse show" id="collapse_4" style="">
                            <div class="card-body">
                                <label class="checkbox-btn">
                                <input type="checkbox">
                                <span class="btn btn-light border-0"> XS </span>
                                </label>
                                <label class="checkbox-btn">
                                <input type="checkbox">
                                <span class="btn btn-light border-0"> SM </span>
                                </label>
                                <label class="checkbox-btn">
                                <input type="checkbox">
                                <span class="btn btn-light border-0"> LG </span>
                                </label>
                                <label class="checkbox-btn">
                                <input type="checkbox">
                                <span class="btn btn-light border-0"> XXL </span>
                                </label>
                            </div>
                            <!-- card-body.// -->
                        </div>
                        <hr>
                    </article>
                    <!-- filter-group .// -->
                    <article class="filter-group">
                        <div class="card-header">
                            <a href="#" data-toggle="collapse" data-target="#collapse_5" aria-expanded="false" class="">
                                <i class="icon-control fa fa-chevron-down"></i>
                                <h6 class="title">More filter </h6>
                            </a>
                        </div>
                        <div class="filter-content collapse show" id="collapse_5" style="">
                            <div class="card-body">
                                <label class="custom-control custom-radio">
                                    <input type="radio" name="myfilter_radio" checked="" class="custom-control-input">
                                    <div class="custom-control-label">Any condition</div>
                                </label>
                                <label class="custom-control custom-radio">
                                    <input type="radio" name="myfilter_radio" class="custom-control-input">
                                    <div class="custom-control-label">Brand new </div>
                                </label>
                                <label class="custom-control custom-radio">
                                    <input type="radio" name="myfilter_radio" class="custom-control-input">
                                    <div class="custom-control-label">Used items</div>
                                </label>
                                <label class="custom-control custom-radio">
                                    <input type="radio" name="myfilter_radio" class="custom-control-input">
                                    <div class="custom-control-label">Very old</div>
                                </label>
                            </div>
                            <!-- card-body.// -->
                        </div>
                    </article>
                    <!-- filter-group .// -->
                </div>
                <!-- card.// -->
            </aside>
            <?php  ?>
			<main class="col-md-9">			
                <div class="pro-fil">
                    <div class="form-inline">
                        <span class="mr-md-auto">Showing <?php echo $count_all; ?> Products </span>
                        <span class="pr-2">Short By:</span>
                        <select class=" form-control ghh ">
                            <option>Latest items</option>
                            <option>Trending</option>
                            <option>Most Popular</option>
                            <option>Cheapest</option>
                        </select>
                    </div>
                </div>
                <!-- sect-heading -->
                <div class="row">
					<?php 
					
					//echo "<pre>"; print_r($products); echo "</pre>";die;
					if($products){
						foreach($products as $key=>$val){						
						
						?>
                    <div class="col-md-4 pdd2">
                        <figure class="card card-product-grid" id="post-<?php echo (isset($val->product_id)) ? $val->product_id : ''; ?>">
                            <div class="img-wrap"> <a href="<?php echo base_url().'product/detail/'.$val->post_slug; ?>" class="title">
							<?php $img = isset($val->image_path) ? $val->image_path:''; ?>	
							<?php if( isset($img) && !empty($img) ){ ?><img src="<?= $img ?>" alt="<?php echo $val->post_title; ?>" class="img-fluid"><?php }else{ ?>
							<img src="<?php echo base_url(); ?>assets/uploads/dummy.jpg" alt="<?php echo $val->post_title; ?>" class="img-fluid">	
							<?php } ?>								
                            
                            </a></div>
                            <!-- img-wrap.// -->
                            <figcaption class="info-wrap">
                                <div class="fix-height">
                                    <a href="<?php echo base_url().'product/detail/'.$val->post_slug; ?>" class="title"><?php echo $val->post_title; ?></a>
                                    <div class="price-wrap mt-2">																		
											<span class="price"><?php echo $sale_price = (isset($val->sale_price) && !empty($val->sale_price)) ? $symbol.$rate*$val->sale_price : ''; ?> </span>
											<?php if($sale_price) {?><del class="price-old"><?php } ?>	
											<?php echo $regular_price = (isset($val->regular_price) && !empty($val->regular_price) ) ? $symbol.$rate*$val->regular_price : ''; ?><?php if($sale_price) {?></del><?php } ?>											
                                    </div>
                                    <!-- price-wrap.// -->
                                </div>		         
							   
                            </figcaption>
                        </figure>
                    </div>
                    <!-- col.// -->
		
<script>
$(document).ready(function(){
	var id = '<?php echo $val->product_id; ?>';
	$('#message'+id).hide();
/*
	$('#addtocart-'+id).on('submit', function (e) {		
	 e.preventDefault();
	  $.ajax({
		type: 'POST',
		url: '/customer/basket/ajaxaddtocart',
		data: $('#addtocart-'+id).serialize(),
		dataType:"json",
		success: function (response) {			
				if(response.success){
					console.log(response.data.cart_count);
					$('#cart_count-'+id).html(response.data.cart_count);
					$('#message'+id).html('<div class="addtocart message">'+response.msg+'</div>');
					//$('#message').html(response.msg);
					setTimeout(function(){
						document.getElementById("message").innerHTML = '';
					}, 3000);					
					//$('#subform').hide();
					$("#addtocart-"+id).trigger("reset");
					$('#message'+id).show(); 
				}
			}
		});
	});
*/
  });
</script>			
					
					<?php } ?>
                </div>
                <!-- row end.// -->
                <div class="float-right">
                    <nav class="mt-4 mb-5" aria-label="Page navigation sample">
                        <ul class="pagination">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>
				<?php }else{
						echo '<div class="alert alert-warning">No product available!</div>';
					} ?>
				
            </main>
        </div>
    </div>
</section>


 
