<!--Header Area End Here-->
<div class="wrapper body-bg">
  <div class="shop-by-categories bx-shadow">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="owl-carousel cate-slider owl-theme">
          <?php
              $cat_html_first = '';
              if(isset($cate_all_strip['all_cat']) && $cate_all_strip['all_cat'])
              {
                foreach($cate_all_strip['all_cat'] as $row)
                {
                  
                  $img = $row->banner_image;
                  $name = $row->name; $url = '/category/'.$row->slug;
                  $cat_html_first .=<<<HTML
                  <div class="item"> 
                    <a href="$url" class="category-item">
                            <!--<div class="cate-img"><img src="$img" alt=""></div>-->
                              <h4>$name</h4>
                            </a> 
                  </div>
HTML;
                }
                echo $cat_html_first;
              }
          ?> 
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!--Product-Listing Details Area Start Here-->
  <div class="all-product-imosys">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <div class="product-dt-view product_id-<?php echo $detail->product_id; ?>" >
            <div class="row">
              <div class="col-lg-4 col-md-4">
                <div class="left-sidebar">
					<div class="sync1_js">
                  <div id="sync1" class="owl-carousel owl-theme">
                    <?php 
			$product_gallery = isset($detail->variations_img) ? $detail->variations_img : $product_gallery;
			
			foreach($product_gallery as $k=>$v) { ?> 
                      <div class="item <?php if($k==0) { echo "active"; } ?>" data-slide-number="<?php echo $k ?>" > <div class="big-product-wrap"><img src="<?php echo $v->url; ?>" alt="<?php echo $v->file_name; ?>" class="big-img-product"> </div></div>
                    <?php }  ?>
                  </div></div>
					<div class="sync2_js">
                  <div id="sync2" class="owl-carousel owl-theme">
                    <?php 
			$product_gallery = isset($detail->variations_img) ? $detail->variations_img : $product_gallery; 
			foreach($product_gallery as $k=>$thumb){?>
                      <div class="item item-thumb <?=$k==0?'active':''?>" onclick="setActiveThumb(this)" id="carousel-selector-<?php echo $k; ?>">
                       <img src="<?php echo $thumb->url; ?>"  alt="<?php echo $thumb->file_name; ?>" class="mini-img-product"> 

                     </div>
                    <?php }  ?>

                  </div></div>
                  <form class="contact-form-box cart" id="addtocart" method="post">
                  <div class="product-group-dt details-price-div">
                    <div class="ordr-crt-share" id="product_id-<?php echo $detail->product_id; ?>">
                       <?php
                        $product_price = '';
                        $sale_price = (isset($detail->sale_price)) ? $detail->sale_price : '';
                        $regular_price = (isset($detail->regular_price)) ? $detail->regular_price : '';
                        if($sale_price){
                          $product_price = $sale_price;
                        }else{
                          $product_price = $regular_price;
                        }
                        ?> 
                    <?php 
                        $title  = $detail->post_title;
                        $myJSON = json_encode($title);
                        ?>
                      <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                      <input type="hidden" name="product_id" value="<?php echo (isset($detail->product_id)) ? $detail->product_id : ''; ?>" />
                      <input type="hidden" name="vendor_id" value="<?php echo (isset($detail->vendor_id)) ? $detail->vendor_id : ''; ?>" />
                      <span class="isstockclass" style="width:100%;">
                      <?php if($detail->enabled && $detail->is_stock){ ?>
                      <button type="submit" name="add-to-cart" class="add-cart-btn hover-btn mr-3 text-uppercase is_stock_class_add"><i class="uil uil-shopping-cart-alt"></i>Add to Cart</button>
                      <button class="order-btn hover-btn is_stock_class_order" name="order_now" id="order_now" value="order_now">Order Now</button>
                        <?php } ?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-8 col-md-8 main-content">
                <div class="product-dt-right mt-0 product_id-<?php echo $detail->product_id; ?>" > 
                  <!--breadcrumb Area Start Here-->
                  <div class="imosys-Breadcrumb">
                    <div class="row">
                      <div class="col-md-12">
                        <?= $breadcrumbs ?>
                        <!--<nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                            <li class="breadcrumb-item"><a href="index.html">Mobile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mobile Details</li>
                          </ol>
                        </nav>-->
                      </div>
                    </div>
                  </div>
                  <!--breadcrumb Area Start Here-->
                  <?php if($detail){ ?>
                    <h2><?php echo (isset($detail->post_title)) ? $detail->post_title : ''; ?></h2>
                  <?php } ?>
                  
                  <?php if (validation_errors()!='') { ?>
                    <div class="alert alert-danger"> <?php echo validation_errors();?> </div>
                  <?php } ?>
                  <!--<div class="no-stock">
                    <p class="mb-2"><span class="ratings-badge-1">4.0 <i class="fa fa-star pl-1"></i></span><span class="product-view-ratings">37,974 Ratings&nbsp;</span> & <span class="product-view-ratings">&nbsp;1,450 Reviews </span></p>
                  </div>-->
                  <?php  
                   if($discount){ ?>
                    <span id="discount_price_div"><div class="product-price chk" id="regular_price"><?= ($detail->sale_price) ? $symbol.'&nbsp;'.(int)$detail->sale_price : '' ?><span class="line-through-price"><?= ($detail->regular_price) ? $symbol.(int)$detail->regular_price : '' ?></span> <span class="product-off"><?=$detail->percentage_off?>% OFF</span></div></span>
                  <?php }else{ ?>
                    <div class="product-price" id="regular_price"><?= ($detail->regular_price) ? $symbol.'&nbsp;'.(int)$detail->regular_price : '' ?></div>
                  <?php } ?>
               
                                      
                        <p class="sellername-text text-orange is_stock_msg">
                        <?php if(!$detail->is_stock) { ?>
                        <b>
                        OUT OF STOCK
                        </b>
                        <?php } ?>
                        </p>
                
                  <?php 
                  if($detail->price_range){ ?>
                  <div class="product-quin">
                      <?php foreach($detail->price_range as $qty=>$price){ ?>
                      <p><span><?= $qty; ?> <?= $page_lang->pieces ?></span><br><?= $symbol.$price ?></p>
                      <?php } ?>
                  </div>
                  <?php } ?>
                  <hr>
                  <div class="products-attribute">
                    <div class="row">
                        <?php 
                         if($attribute_items){
                          $img_count = 0;
                          foreach($attributes as $key=>$val){ 
                        ?>
                        <div class="product-radio col-md-4">
                          <div class="product-specific-text <?= $val->name; ?>" for="<?= $val->name; ?>" id="variation-<?= $val->attribute_id; ?>" ><?= $val->name; ?></div>
                          <ul class="product-now">
                          <?php foreach($val->attribute_items as $k=>$v){
                              if(isset($v->selected) && $v->selected){
                                  $label_class = 'category_selected ';
                                  $radio_selected = 'checked';
                              }else{
                                  $label_class = 'category';
                                  $radio_selected = '';
                              }
                           ?> 
                            <li title="<?= $v->name ?>">
                                <input type="radio" <?= $radio_selected ?> name="attribute_<?= $val->attribute_id ?>" class="attr_item term_item term_item_<?= $val->attribute_id ?>" data-id="<?php echo $v->attribute_item_id ?>" data-name="<?php echo $v->name ?>"  id="attr_itemid_<?php echo $v->attribute_item_id ?>" data-attrlvl="<?= $val->attribute_id ?>" value="<?php echo $v->attribute_item_id ?>" required />
								<label for="attr_itemid_<?php echo $v->attribute_item_id ?>">
                                 <?php if($v->image){ 
                                  echo "<img src=\"$v->image\" alt=\"$v->name\" class=''>";
                                  $img_count++;
                                   }else{ 
                                     echo $v->name;
                                   }?>
								</label>
                            </li>
                          <?php } ?>
                          </ul>
                        </div>
                        <?php } } ?>
                      <!--<div class="col-md-12">
                        <div class="product-radio">
                          <div class="product-specific-text">Storage</div>
                          <ul class="product-now">
                            <li>
                              <input type="radio" id="p3" name="product2" checked>
                              <label for="p3">128 GB</label>
                            </li>
                            <li>
                              <input type="radio" id="p4" name="product2">
                              <label for="p4">256 GB</label>
                            </li>
                          </ul>
                        </div>
                      </div>-->
                      <!--<div class="col-md-4">
                        <div class="product-radio">
                          <div class="product-specific-text">RAM</div>
                          <ul class="product-now">
                            <li>
                              <input type="radio" id="p5" name="product3" checked>
                              <label for="p5">6 GB</label>
                            </li>
                            <li>
                              <input type="radio" id="p6" name="product3">
                              <label for="p6">8 GB</label>
                            </li>
                          </ul>
                        </div>
                      </div>-->
                    </div>
                  </div>
                  <!--<div class="products-attribute">
                    <div class="product-radio">
                      <div class="product-specific-text">Size</div>
                      <ul class="product-now">
                        <li>
                          <input type="radio" id="xs" name="size" checked>
                          <label for="xs">XS</label>
                        </li>
                        <li>
                          <input type="radio" id="S" name="size">
                          <label for="S">S</label>
                        </li>
                        <li>
                          <input type="radio" id="M" name="size">
                          <label for="M">M</label>
                        </li>
                        <li>
                          <input type="radio" id="L" name="size">
                          <label for="L">L</label>
                        </li>
                        <li>
                          <input type="radio" id="XL" name="size">
                          <label for="XL">XL</label>
                        </li>
                        <li>
                          <input type="radio" id="XXL" name="size">
                          <label for="XXL">XXL</label>
                        </li>
                        <li>
                          <input type="radio" id="3XL" name="size">
                          <label for="3XL">3XL</label>
                        </li>
                      </ul>
                    </div>
                  </div>-->
                <div class="two-option-wrap">
                    <?php if(isset($detail->highlight) && $detail->highlight) { ?>
                    <div class="product-delivery-date">
                        <div class="product-specific-text">Highlights</div>
                      	<ul class="highlight-list">
                   	    <?php 
				        $content = array();
				        $content= preg_split('/\n/', $detail->highlight); 
                  		foreach($content as $content_data){ ?> 
                  		<li><?= $content_data; ?></li>
                  	     <?php } ?>
                        </ul>
                    </div>
                    <?php } if(isset($detail->easy_pymnt_option) && $detail->easy_pymnt_option) { ?>
                    <div class="product-delivery-date pl-4">
                      <div class="product-specific-text">Easy Payment Options</div>
                      <ul class="highlight-list">
						<?php
                            $content = preg_split('/\n/', $detail->easy_pymnt_option);
                            foreach($content as $content_data){ ?>
                                <li><?= $content_data; ?></li>
                        <?php } ?>

                      </ul>
                    </div>
                    <?php } ?>
                  </div>
                  <div class="product-delivery-date">
                    <div class="product-specific-text">Seller</div>
                    <div class="delivery-dateby">
                       <div class="dropdown">
                        <?php if(count($vendor_list['vendor_detail']) > 1){?>
                        <h5 class="sellername-text text-orange dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;"><?=isset($admin_obj->store_name) ? $admin_obj->store_name : ''?><span class="caret"></span></h5>
                        <?php }else{?>
                        <h5 class="sellername-text text-orange"><?=isset($admin_obj->store_name) ? $admin_obj->store_name : ''?><span class="caret"></span></h5>
                        <?php }?>
                          <ul class="dropdown-menu">
                          <?php foreach($vendor_list['vendor_detail'] as $vendorlist){
                                $product_info = (object)$vendor_list['supplier_wise_product'][$vendorlist->admin_id];
                                $url = detail_page_url($product_info); 
                            ?>
                            <li><a href="<?=$url?>"><?= $vendorlist->store_name; ?></a></li>
                          <?php }?>
                        </ul>
                      </div> 
			<!--<span class="seller-offer-badge">4.0 <i class="fa fa-star pl-1"></i></span>--></h5>
                      <!--<ul class="highlight-list">
                        <li>7 Days Replacement Policy</li>
                        <li>GST invoice available</li>
                      </ul>-->
                    </div>
                  </div>
                  <div class="product-group-dt details-price-div">
                    <ul class="gty-wish-share">
                      <li>
                        <?php if(($detail->type == 'simple') || ($detail->type == 'variable' && $attribute_items)){ ?>
                        <div class="qty-product">
                          <div class="quantity buttons_added input-group numbering">
                              <span class="input-group-btn"><a href="javascript:void(0);" class="minus minus-btn btn-default quantity_update" data-id="minus" data-dir="dwn"><i class="fa fa-minus-circle"></i></a></span>
                              <input type="text" name="quantity" id="quantity" class="input-text qty text numeric qty_update" value="1">
                              <span class="input-group-btn"><a href="javascript:void(0);" class="btn btn-default quantity_update plus plus-btn" data-id="plus" data-dir="up"><i class="fa fa-plus-circle"></i></a></span>
                          </div>
                        </div>
                       <?php } ?>
                      </li>
                      <li>
                        <?php $heart = ($wishlist) ?  'liked' : ''  ?>
                        <span class="like-icon save-icon <?= $heart; ?>" data-data="<?=$detail->product_id; ?>" id="WishlistProduct" title="wishlist"></span></li>
                    </ul>
                  </div>
                </form>
                  <!--<div class="product-delivery-date">
                    <div class="product-specific-text">Description</div>
                    <div class="delivery-dateby"> <?php //if(isset($detail->post_content)){
                              //echo (isset($detail->post_content)) ? $detail->post_content : '';
                            //}else{        
                              //echo '<div class="alert alert-warning">No data available!</div>';
                    //} ?></div>
                  </div>-->
                  <div class="product-delivery-date">
                    <div class="product-specific-text mb-0">Share</div>
                    <div class="delivery-dateby"> <a href=""><i class="fab fa-facebook-square fb-color mr-1 fa-2x"></i></a> <a href=""><i class="fab fa-twitter-square twit-color mr-1 fa-2x"></i></a> <a href=""><i class="fa fa-envelope text-danger fa-2x"></i></a> </div>
                  </div>
                  <div class="pdpt-bg br-whole">
                    <div class="pdpt-title">
                      <h4>Product Details</h4>
                    </div>
                    <div class="">
                      <div class="pdct-dts-1">
                        <div class="pdct-dt-step">
                          <h4>Description</h4>
                           <div class="delivery-dateby"> <?php if(isset($detail->post_content)){
                            $post_content = preg_replace('/\n/', '<br>', $detail->post_content);
                              echo $post_content;
                            }else{        
                              echo '<div class="alert alert-warning">No data available!</div>';
                            } ?></div>
                        </div>
                        <!--<div class="pdct-dt-step">
                          <h4>Benefits</h4>
                          <div class="product_attr"> Aliquam nec nulla accumsan, accumsan nisl in, rhoncus sapien.<br>
                            In mollis lorem a porta congue.<br>
                            Sed quis neque sit amet nulla maximus dignissim id mollis urna.<br>
                            Cras non libero at lorem laoreet finibus vel et turpis.<br>
                            Mauris maximus ligula at sem lobortis congue.<br>
                          </div>
                        </div>
                        <div class="pdct-dt-step">
                          <h4>How to Use</h4>
                          <div class="product_attr"> The peeled, orange segments can be added to the daily fruit bowl, and its juice is a refreshing drink. </div>
                        </div>
                        <div class="pdct-dt-step">
                          <h4>Seller</h4>
                          <div class="product_attr"> Gambolthemes Pvt Ltd, Sks Nagar, Near Mbd Mall, Ludhana, 141001 </div>
                        </div>
                        <div class="pdct-dt-step">
                          <h4>Disclaimer</h4>
                          <p>Phasellus efficitur eu ligula consequat ornare. Nam et nisl eget magna aliquam consectetur. Aliquam quis tristique lacus. Donec eget nibh et quam maximus rutrum eget ut ipsum. Nam fringilla metus id dui sollicitudin, sit amet maximus sapien malesuada.</p>
                        </div>-->
                      </div>
                    </div>
                  </div>
		<?php if($avgrating != 0.0){ ?>
                  <div class="review-ratings-client">
                    <div class="pdpt-bg br-whole">
                      <div class="pdpt-title">
                        <h4>Ratings & Reviews</h4>
                      </div>
                      <div class="">
                        <div class="pdct-dts-1">
                          <div class="row">
                            <div class="col-md-3">
                              <div class="ratings-count-number"> <span class="count-number"><?= $avgrating; ?> <span class="fs-20"><i class="fa fa-star"></i></span></span> <span class="d-block text-grey"><?= $sumrating; ?> Ratings &</span> <span class="d-block text-grey"><?= $cntreview; ?> Reviews</span> </div>
                            </div>
                            <div class="col-md-5">

			    <?php foreach($starcnt as $star_count){?>
                              <div class="ratings-progressbar-wrap">
                                <div class="star-number"><?= $star_count->rating; ?><i class="fa fa-star"></i></div>
                                <div class="progressbar-status">
                                  <div class="cssProgress">
                                    <div class="progress4">
                                      <div class="cssProgress-bar cssProgress-success" data-percent="45" style="width:100%;"></div>
                                    </div>
                                  </div>
                                </div>
                                <div class="progress-number-customers"><?= $star_count->starcount; ?></div>
                              </div>
			  <?php }?>
			  </div>
                            <div class="col-md-4 text-right">
                              <!--<div class="write-review">
                                <button class="order-btn hover-btn" data-toggle="modal" data-target="#write-review">Write Review</button>
                              </div>-->
                            </div>
                          </div>
                          <hr>
                          <div class="customers-reviews-wrap">
                            <?php if($comment_obj){ foreach($comment_obj as $cmt){?>
                            <div class="customers-reviews-wrap">
                                 <div class="customers-row-div">
                                 <div class="customers-ratings"> <span class="ratings-badge-1"><?= $cmt->rating; ?>.0 <i class="fa fa-star pl-1"></i></span> <span class="customers-words"><?= $cmt->title; ?></span> </div>
                                <div class="customers-desc"><?= $cmt->comment ?> </div>
                                <div class="customers-postby-wrap">
                                <div class="customers-postby"> <span class="cc-name"><?= $cmt->fname?> <?= $cmt->lname?></span> <span class="post-dateby"><?= $cmt->last_updated ?></span> </div>
                                </div>
                            </div>
                        </div>
                        <?php } } ?>
                        <hr>
                          </div>
                          </div>
		            	<?php }?>
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
    </div>
  </div>
  <!--Product-Listing Details Area End Here-->
 <?php //echo "<pre>"; print_r($related_product); echo "</pre>"; 
    if(isset($related_product) && !empty($related_product)){ ?> 
  <div class="section145">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="main-title-tt">
            <div class="main-title-left">
              <h2>Related Products</h2>
            </div>
            <!--<a href="#" class="see-more-btn">See All</a> </div>-->
        </div>
        </div>
        <div class="col-md-12">
          <div class="owl-carousel featured-slider owl-theme mb-5">
          	<?php foreach($related_product as $v){?>
	            <div class="item" id="product_id-<?php echo $v->product_id; ?>">
	              <div class="product-item"><div class="product-absolute-options">
                  <?php $heart = isset($v->wishlist) && $v->wishlist ?  ' liked' : ''  ?> 
                  <span class="like-icon w_<?=$v->product_id; ?><?= $heart; ?>" onclick="wishlistProduct(this);" data-data="<?=$v->product_id; ?>" id="RelateProduct" title="wishlist"></span>
                 <!--<span class="offer-badge-1">4.0 <i class="fa fa-star pl-1"></i></span> --><!--<span class="like-icon" title="wishlist"></span>--></div>
                <a href="<?= $v->url; ?>" class="product-img" title="post title"> <img src="<?= $v->featured_image ?>" alt="">
	                </a>
	                <div class="product-text-dt">
	                   <h4><?= $v->post_title; ?></h4>
	                   <?php if($v->discount){ ?>
	                  <div class="product-price"><?= $v->symbol.'&nbsp;'.(int)$v->sale_price; ?><del><?= $v->symbol.(int)$v->regular_price; ?> <span class="line-through-price">$500</span> <span class="product-off"><?=$detail->percentage_off?>% OFF</span></div>
	                  <?php } else {?>
	                  	<div class="product-price"><?= $v->symbol.'&nbsp;'.(int)$v->regular_price; ?></div>
	                  <?php } ?>
	                  <!--<div class="qty-cart">
	                    <div class="quantity buttons_added"> <span class=""><i class="fa fa-minus-circle minus minus-btn"></i></span>
	                      <input type="number" step="1" name="quantity" value="1" class="input-text qty text">
	                      <span class=""><i class="fa fa-plus-circle plus plus-btn"></i></span> </div>
	                  </div>
	                  <div class="product-buynow">
	                    <button type="button" class="btn btn-buynow">Buy Now</button>
	                    <span class="cart-icon"><i class="uil uil-shopping-cart-alt"></i></span> </div>-->
	                </div>
	              </div>
	            </div>
        	<?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
</div>
<!--Wrapper Area End Here-->
<script>
    var variations = <?= ($variations) ? json_encode($variations) : "''"; ?>;
    var variation_mapping = <?= ($variation_mapping) ? json_encode($variation_mapping) : "''"; ?>;
    $(document).on('ready', function() {
        $(".hp_slick").slick({
            dots: true,
            infinite: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000,
            margin:5,
            pauseOnHover: true,
            responsive: [
            {
                breakpoint: 1040,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
         }); 
       }); 
             
        $(document).on('hover', '.dropdown-menu', function (e) {
        e.stopPropagation();
    });
        
    setTimeout(() => { $('#myCarousel').carousel({
       interval: 4000
    }) }, 2500); 
   
    
    // handles the carousel thumbnails
    $('[id^=carousel-selector-]').click( function(){
         var id_selector = $(this).attr("id");
         var id = id_selector.substr(id_selector.length -1);
         id = parseInt(id);
         $('#bannerCarousel').carousel(id);
         $('[id^=carousel-selector-]').removeClass('selected');
         $(this).addClass('selected');
    });
    
    // when the carousel slides, auto update
    $('#myCarousel').on('slid', function (e) {
         var id = $('.item.active').data('slide-number');
         id = parseInt(id);
         $('[id^=carousel-selector-]').removeClass('selected');
         $('[id=carousel-selector-'+id+']').addClass('selected');
    }); 
</script>
<script>
    $(document).ready(function(){
      $('#addtocart').on('submit', function (e) { 
            e.preventDefault();
            var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
            postData.product_id = <?= isset($detail->product_id) ? $detail->product_id : 0 ?>;
            postData.vendor_id = <?= isset($detail->vendor_id) ? $detail->vendor_id : 0 ?>;
            if($('#quantity').val().trim())
            {
                postData.quantity = $('#quantity').val();
            }
            postData.action = 'add';
            var btn_click = $(document.activeElement).val();
            postData.btn_type = btn_click;

            var attribute_item_array = [];
            $(".attr_item:checked").each(function() {
                attribute_item_array.push($(this).val())
            });
            postData.attribute_item = JSON.stringify(attribute_item_array);
          $.ajax({
            type: 'POST',
            url:  "/<?= $TYPE ?>/cart/ajax_add_to_cart",
            data: postData,
            dataType:"json",
                beforeSend: function() {
                  //  $("#loading-image").show();
                },
            success: function (response) {
            if(response.status){
                        if(btn_click == 'order_now')
                        {
                            window.location.href='/checkout';
                        }
                        else
                        {
                            window.location.reload();
                            showToast(response.message, 'success');
                        }
            }
                   // $("#loading-image").hide();
            },
                error: function (error) {
                  //  $("#loading-image").hide();
                }
          });
      });
    });

    $(".btn-checkout").on('click', function(e) {
        window.location.href = "/checkout";
    });

    $(()=>{
    // THIS FUNCTION USE FOR THE ADD WISHLIST PRODUCT
        $("#WishlistProduct").on('click', function(e) {

            var product_id = $(this).data('data');
            var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
            postData.product_id = product_id;
            var attribute_item_array = [];
            $(".attr_item:checked").each(function() {
                attribute_item_array.push($(this).val())
            });
            postData.attribute_item = JSON.stringify(attribute_item_array);
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: '/welcome/ajax_add_to_wishlist',
                data: postData,
                dataType:"json",
                success: function (response) {
                    showToast(response.message, 'success');      
                    //swal({text: response.message, timer: 2000, buttons: false});
                    if(response.status == '1'){
                        $('.gty-wish-share .like-icon').addClass('liked');
                    }else{
                        $('.gty-wish-share .like-icon').removeClass('liked');
                    }
/*
                    if(response.success == 0){
                        $('#msg').append(response.msg);
                    }else{
                        $('#msg').append(response.msg);
                    }
*/
                }
            });
        });
    });



// THIS FUNCTION USE FOR THE ADD WISHLIST PRODUCT
    function wishlistProduct(t){
      var product_id = $(t).data('data');
      var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
      postData.product_id = product_id;
      var attribute_item_array = [];
      $(".attr_item:checked").each(function() {
          attribute_item_array.push($(t).val())
      });
      postData.attribute_item = JSON.stringify(attribute_item_array);
      $.ajax({
          type: 'POST',
          url: '/welcome/ajax_add_to_wishlist',
          data: postData,
          dataType:"json",
          success: function (response) {
              showToast(response.message, 'success');      
              //swal({text: response.message, timer: 2000, buttons: false});
              if(response.status == '1'){
                  $('.w_'+product_id).addClass('liked');
              }else{
                  $('.w_'+product_id).removeClass('liked');
              }
          }
      });
  }


</script>
<script>
    $("input[type=radio]").click(function() {
        var attribute_item_array = [];
        $(".attr_item:checked").each(function() {
            attribute_item_array.push($(this).val());
        });
        var current=$(this); 
        var attrlvl=$(this).attr('data-attrlvl'); 
        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.product_id = <?=$detail->product_id; ?>;
        postData.attribute_item_id = this.value;
        postData.attribute_item = JSON.stringify(attribute_item_array);
        $.ajax({
           type: 'POST',
           url:  "/<?= $TYPE ?>/product/ajax_product_variation",
           data: postData,
           dataType:"json",
           beforeSend: function() {
             //  $("#loading-image").show();
           },
           success: function (response) {
			if(response.status == 0)
			{
				return false;
			}
          if(response.status ){
				if(response.data[0].is_price_update && response.data[0].is_price_update == 0)
				{
					return false;
				}
                var symbol = '<?= $symbol ?>';
                var iso_code = '<?= $iso_code ?>';
                var row = response.data[0];
                var variations = row.variations;
                var is_price_update = row.is_price_update;
				attributes_images(row);
                if(is_price_update){
                    $(".attr_item").each(function() {
                        $(this).parent().parent().removeClass('category_selected');
                        $(this).parent().parent().removeClass('category_disabled');
                        $(this).attr("checked",false);
                    });
                    for(var i in variations){
                        var attr_itemid = 'attr_itemid_'+i;
                        var cls_name = 'category_enabled';
                        if(variations[i] == 0){
                            cls_name = 'category_disabled';
                        }else if(variations[i] == 1){
                            cls_name = 'category_selected';
                            $('#'+attr_itemid).prop("checked", true);
                        }else if(variations[i] == 2){
                            cls_name = 'category_enabled';
                        }
                        $('#'+attr_itemid).parent().parent().addClass(cls_name);
                    }
                    var discount = row.discount;
                    var sale_price = row.sale_price;
                    var regular_price = row.regular_price;
                    var subtotal = row.subtotal;
                    var shipping = row.shipping;
                    var is_stock = row.is_stock;
                    var tax = row.tax;
                    if(discount){
                        //$('#regular_price').html(symbol+regular_price);
                        //$('#sale_price').html(symbol+sale_price);
                        //$('#subtotal').html(symbol+sale_price);
						
						var discount_html = '<div class="product-price" id="regular_price">'+symbol+' '+sale_price+'<span class="line-through-price">'+symbol+' '+regular_price+'</span> <span class="product-off">'+row.percentage_off+'</span></div>';
						$('#discount_price_div').html(discount_html);
                    }else{
                        $('#regular_price').html(symbol+regular_price);
                        $('#subtotal').html(symbol+regular_price);
                    }
            
                    if(is_stock !=0)
                    {
                       var button_html = '<button type="submit" name="add-to-cart" class="add-cart-btn hover-btn mr-3 text-uppercase is_stock_class_add"><i class="uil uil-shopping-cart-alt"></i>Add to Cart</button><button class="order-btn hover-btn is_stock_class_order" name="order_now" id="order_now" value="order_now">Order Now</button>'; 
                       $('.isstockclass').html(button_html);
                       $('.isstockclass').show(); 
                    }else{
                       $('.isstockclass').hide(); 
                    }

                    //$('.is_stock_class').show();
                    $('.is_stock_msg').html('');
                    if(!is_stock)
                    {
                        $('.is_stock_msg').html('<b>OUT OF STOCK</b>'); 
                       // $('.is_stock_class').hide('');
                    }
                     
                    
                    var price = 0;
                    if(discount){
                        price = parseFloat(sale_price);
                    }else{
                        price = parseFloat(regular_price);
                    }
                    var total = price;
                    if(!isNaN(shipping)){
                        total+= parseFloat(shipping);
                    }
                    if(!isNaN(tax)){
                        total+= parseFloat(tax);
                    } 
                    total = total.toFixed(2);
                    if(!isNaN(tax)){
                        $('#tax').html(symbol+tax);
                    }else{
                        $('#tax').html(tax);
                    }
                    $('#total').html(symbol+total);
                }else{
                    $(".term_item_"+attrlvl).parent().removeClass('category_selected').addClass('category');
                    $(current).parent().addClass("category_selected");
                }
          }
			
              // $("#loading-image").hide();
           },
           error: function (error) {
             //  $("#loading-image").hide();
           }
        });
    });

function attributes_images(args)
{


	var varimgs = args.variations_img;
	//console.log("varimgs== "+ JSON.stringify(varimgs));
	var sync1_js_html = '<div id="sync1" class="owl-carousel owl-theme">'; 
	var sync2_js_html='<div id="sync2" class="owl-carousel owl-theme">';
	$.each(varimgs, function (key, val) {
		var active_class = key == 0 ? 'active' : '';
		sync1_js_html += '<div class="item '+active_class+'" data-slide-number="'+key+'" > <div class="big-product-wrap"><img src="'+val.url+'" alt="'+val.file_name+'" class="big-img-product"> </div></div>';
		sync2_js_html += '<div class="item item item-thumb '+active_class+'" onclick="setActiveThumb(this)" id="carousel-selector-'+key+'"> <img src="'+val.url+'" alt="'+val.file_name+'" class="mini-img-product"> </div>';
    });
	sync1_js_html += '</div>';
	sync2_js_html += '</div>';
	$('.sync1_js').html(sync1_js_html);
	$('.sync2_js').html(sync2_js_html);
	reinitialsie_owl();		
}

function reinitialsie_owl()
{
	$("#sync2").owlCarousel();
	$("#sync1").owlCarousel({
		navigation : true,
		items: 1,
		loop: true,
      	slideSpeed : 300,
      	paginationSpeed : 400,
		nav: true,
  		dots: false,
  		autoplay: true,
      	singleItem:true,
        navText: ["<i class='uil uil-angle-left'></i>", "<i class='uil uil-angle-right'></i>"]
	});
}

$('.quantity_update').on('click', function (e) {
    var id = $(this).attr('data-id');
    var txtBox = $(this).closest('.numbering').find('input[type=text]');
    var quantity = txtBox.val().trim();

    if(id == 'plus'){
        quantity++;
    }else if(id == 'minus'){
        if(quantity > 1){
            quantity--;
        }
    }
    txtBox.val(quantity);
});

$('.qty_update').on('keyup', function (e) {
    if($(this).val() == '' || $(this).val() == 0){
        $(this).val(1);
    }
});

$(".nav a").on("click", function(){
   $(".nav").find(".active").removeClass("active");
   $(this).parent().addClass("active");
});


      $(document).on('hover', '.dropdown-menu', function (e) {
    e.stopPropagation();
  });
       
       $(function() {
    $('.mob-menu').click(function() {
      $('#sidebar').toggleClass('visible');
    });
  });
       
       $(document).ready(function () {
  $("#accordion li > h4").click(function () {

    if ($(this).next().is(':visible')) {
      $(this).next().slideUp(300);
      $(this).children(".plusminus").text('+');
    } else {
      $(this).next("#accordion ul").slideDown(300);
      $(this).children(".plusminus").text('-');
    }
  });
});


       function setActiveThumb(t){
        $('.item-thumb').removeClass('active');
        $(t).addClass('active');
       }

</script>
