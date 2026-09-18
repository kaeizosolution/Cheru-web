<?php
$category_name = isset($category->name) ? (string)$category->name : '';
?>

<div class="wrapper body-bg">
  <section class="deliver-divider pt30 pb30">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="d-flex db-500 justify-content-between">
            <div class="main-title mb0-500 d-block d-lg-flex">
              <h2 class=""><?php echo htmlspecialchars($category_name); ?></h2>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <?php if(!empty($products)){ ?>
              <?php foreach($products as $row){ ?>
                <div class="col-6 col-md-4 col-lg-3 mb20">
                  <div class="item ovh">
                    <div class="shop_item bdrtrb1 px-2 px-sm-3 wow fadeIn" data-wow-duration="1.2s">
                      <div class="thumb pb30">
                        <img src="<?php echo $row->image_url; ?>" alt="<?php echo htmlspecialchars((string)$row->name); ?>" style="height: 200px; width: 100%; object-fit: contain;" onerror="this.src='<?php echo base_url('assets/default_images/product.jpg'); ?>'">

                        <div class="thumb_info">
                          <ul class="mb0">
                            <li><a href="#"><span class="flaticon-show"></span></a></li>
                            <li><a href="#"><span class="flaticon-graph"></span></a></li>
                          </ul>
                        </div>

                        <div class="shop_item_cart_btn d-grid">
						  <button type="button" class="btn btn-thm add-to-cart-btn" data-product_id="<?php echo (int)$row->id; ?>" data-vendor_id="0" data-quantity="1">Add to Cart</button>
                        </div>
                      </div>

                      <div class="details">
                        <div class="title">
                          <a href="<?php echo base_url('product/details/'.(int)$row->id); ?>">
                            <?php echo htmlspecialchars((string)$row->name); ?>
                          </a>
                        </div>

                        <div class="review d-flex db-500">
                          <ul class="mb0 me-2">
                            <li class="list-inline-item"><i class="fas fa-star"></i></li>
                            <li class="list-inline-item"><i class="fas fa-star"></i></li>
                            <li class="list-inline-item"><i class="fas fa-star"></i></li>
                            <li class="list-inline-item"><i class="fas fa-star"></i></li>
                            <li class="list-inline-item"><i class="fas fa-star"></i></li>
                          </ul>
                          <div class="review_count"><a href="#">&nbsp;</a></div>
                        </div>

                        <div class="si_footer">
                          <div class="price">
                            <?php echo '$'.number_format((float)($row->display_price ?? 0), 2); ?>
                          </div>
                          <div class="line mt20"></div>
                          <div class="sell_stock mt10">
                            <div class="sell">Status: In Stock</div>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              <?php } ?>
            <?php } else { ?>
              <div class="col-12">
                <div class="bx-shadow" style="background:#fff; padding:20px; border-radius:8px;">
                  No products found in this category.
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
