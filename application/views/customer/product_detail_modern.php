<?php
$placeholder = base_url('assets/default_images/product.jpg');
$title = isset($product->name) ? (string)$product->name : '';
$desc = isset($product->short_description) ? (string)$product->short_description : '';
$price = isset($product->display_price) ? (float)$product->display_price : 0;
$images = (isset($product->image_urls) && is_array($product->image_urls) && $product->image_urls) ? $product->image_urls : array($placeholder);
$main_img = isset($images[0]) ? $images[0] : $placeholder;
$product_id = isset($product->id) ? (int)$product->id : 0;
$vendor_id = isset($product->vendor_id) ? (int)$product->vendor_id : 0;
$available_attributes = (isset($available_attributes) && is_array($available_attributes)) ? $available_attributes : array();
$default_selection = (isset($default_selection) && is_array($default_selection)) ? $default_selection : array();
$variations = (isset($variations) && is_array($variations)) ? $variations : array();
$attr_item_labels = (isset($attr_item_labels) && is_array($attr_item_labels)) ? $attr_item_labels : array();
$attribute_labels = (isset($attribute_labels) && is_array($attribute_labels)) ? $attribute_labels : array();
$product_video = isset($product->video_path) && $product->video_path ? (string)$product->video_path : '';

$csrf = function_exists('csrf_token') ? csrf_token() : null;

if (!$desc && isset($product->description) && $product->description) {
  $desc = (string)$product->description;
}
// Render as plain-text with preserved line breaks (no HTML editor used)
$desc_html = $desc ? nl2br(htmlspecialchars($desc, ENT_QUOTES, 'UTF-8')) : '';
?>

<style>
.zoomimg_wrapper {
  overflow: hidden !important;
  position: relative;
  cursor: zoom-in;
  border-radius: 8px;
  background: #fff;
}
.zoomimg_wrapper img.zoom-img {
  transition: transform 0.25s cubic-bezier(0.25, 1, 0.5, 1);
  transform-origin: center center;
  will-change: transform;
}

/* Related Products Cards */
.rp-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #fff;
  border: 1px solid #f0f0f0;
  border-radius: 12px;
  overflow: hidden;
  transition: box-shadow 0.22s ease, transform 0.22s ease;
  text-decoration: none !important;
  color: inherit;
}
.rp-card:hover {
  box-shadow: 0 8px 28px rgba(0,0,0,0.12);
  transform: translateY(-3px);
}
.rp-card__img-wrap {
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #f8f8f8;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 12px;
}
.rp-card__img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.rp-card__body {
  padding: 10px 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}
.rp-card__title {
  font-size: 13px;
  font-weight: 600;
  color: #1a1a2e;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 2.8em;
  margin: 0;
}
.rp-card__price {
  font-size: 15px;
  font-weight: 700;
  color: #111;
  margin: 0;
}
#pd-related-products-section {
  padding: 0 12px;
}
#pd-related-products-list {
  display: flex;
  flex-wrap: wrap;
  margin: 0 -8px;
}
#pd-related-products-list > .rp-col {
  padding: 0 8px;
  margin-bottom: 16px;
  width: 16.6667%;
}
@media (max-width: 1200px) {
  #pd-related-products-list > .rp-col { width: 20%; }
}
@media (max-width: 992px) {
  #pd-related-products-list > .rp-col { width: 25%; }
}
@media (max-width: 768px) {
  #pd-related-products-list > .rp-col { width: 33.333%; }
}
@media (max-width: 576px) {
  #pd-related-products-list > .rp-col { width: 50%; }
}
</style>


<section class="shop-single-content pb80 pt0 ovh">
  <div class="container">
    <div class="row">
      <div class="col-xl-5">
        <div class="shop_single_natabmenu">
          <div class="d-block">
            <div class="tab-content" id="pd-gallery-tab-content">
              <div class="tab-pane fade show active" id="pd-gallery-main" role="tabpanel">
                <div class="shop_single_navmenu_content mb-3 text-center">
                  <div class="zoomimg_wrapper" style="position: relative; width: 100%; min-height: 350px; display: flex; align-items: center; justify-content: center;">
                    <img class="zoom-img" id="pd-main-img" src="<?php echo $main_img; ?>" alt="" style="width:350px; max-width:100%; height:auto; object-fit:contain;" />
                    <?php if ($product_video) { ?>
                      <video id="pd-main-video" controls preload="metadata" style="display: none; width:100%; max-height:350px; background:#000; border-radius:8px;">
                        <source src="<?php echo base_url('uploads/product_videos/' . htmlspecialchars($product_video)); ?>" type="video/mp4">
                        <source src="<?php echo base_url('uploads/product_videos/' . htmlspecialchars($product_video)); ?>" type="video/webm">
                        Your browser does not support HTML5 video.
                      </video>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>
            <?php if(count($images) > 1 || $product_video){ ?>
              <div class="nav d-flex nav-pills me-3 mb-3" id="pd-gallery-thumbs" role="tablist" aria-orientation="vertical">
                <?php foreach($images as $k => $url){ ?>
                  <button type="button" class="nav-link mb-0 me-3 <?php echo $k===0 ? 'active' : ''; ?>" onclick="pdSelectThumb(this, 'image', '<?php echo $url; ?>')">
                    <img src="<?php echo $url; ?>" alt="" style="width:54px; height:54px; object-fit:cover; border-radius:6px;" />
                  </button>
                <?php } ?>
                <?php if ($product_video) { ?>
                  <button type="button" class="nav-link mb-0 me-3 position-relative" onclick="pdSelectThumb(this, 'video', '<?php echo base_url('uploads/product_videos/' . htmlspecialchars($product_video)); ?>')">
                    <img src="<?php echo $main_img; ?>" alt="" style="width:54px; height:54px; object-fit:cover; border-radius:6px; filter: brightness(0.8);" />
                    <div class="position-absolute top-50 start-50 translate-middle" style="color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.6); pointer-events: none;">
                      <i class="fa fa-play-circle" style="font-size: 24px;"></i>
                    </div>
                  </button>
                <?php } ?>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <div class="col-xl-7 alibaba-box">

        <div class="imosys-Breadcrumb mb-2">
          <div class="row">
            <div class="col-md-12">
              <?php echo isset($breadcrumbs) ? $breadcrumbs : ''; ?>
            </div>
          </div>
        </div>

        <div class="shop_single_product_details shop_single3_style ps-0 mt-4 mt-xl-0">
          <h4 class="title mb15"><?php echo htmlspecialchars($title); ?></h4>
        </div>

        <div class="price-tier" id="pd-tier-strip">
          <div class="tier-row" id="pd-tier-grid"></div>
        </div>

        <hr>

        <?php if($available_attributes){ ?>
          <div class="variation-section" id="pd-attrs">
            <div class="variation-head">
              <h4>Variations</h4>
              <a href="javascript:void(0)">Select now</a>
            </div>

            <?php foreach($available_attributes as $attr_key => $vals){
              if(!is_array($vals) || !$vals){
                continue;
              }
              $attr_name = (string)$attr_key;
              if (is_numeric((string)$attr_key) && isset($attribute_labels[(string)$attr_key]) && $attribute_labels[(string)$attr_key] !== '') {
                $attr_name = (string)$attribute_labels[(string)$attr_key];
              }
              $selected = isset($default_selection[$attr_key]) ? (string)$default_selection[$attr_key] : (string)$vals[0];
            ?>
              <div class="variation-group">
                <label id="variation-<?php echo htmlspecialchars((string)$attr_key); ?>"><?php echo htmlspecialchars(strtolower($attr_name)); ?>: <b><span id="pd-selected-<?php echo htmlspecialchars((string)$attr_key); ?>" style="display:none;"></span></b></label>
                <div class="btn-options">
                  <?php foreach($vals as $v){
                    $v = (string)$v;
                    $is_sel = ($v === $selected);
                    $label = $v;
                    if (isset($attr_item_labels[$v]) && $attr_item_labels[$v] !== '') {
                      $label = (string)$attr_item_labels[$v];
                    }
                    $input_id = 'pd_attr_' . (string)$attr_key . '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $v);
                  ?>
                    <input
                      type="radio"
                      <?php echo $is_sel ? 'checked' : ''; ?>
                      name="pd_attr_<?php echo htmlspecialchars((string)$attr_key); ?>"
                      class="pd-attr-radio"
                      data-attr="<?php echo htmlspecialchars((string)$attr_key); ?>"
                      data-label="<?php echo htmlspecialchars($label); ?>"
                      id="<?php echo htmlspecialchars($input_id); ?>"
                      value="<?php echo htmlspecialchars($v); ?>" />
                    <label class="pd-option-btn" for="<?php echo htmlspecialchars($input_id); ?>"><?php echo htmlspecialchars($label); ?></label>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>
          </div>
        <?php } ?>

        <hr>

        <div class="shipping-section">
          <h4>Shipping</h4>
          <p>Shipping fee and delivery date to be negotiated. Chat with supplier now for more details.</p>
        </div>

        <div class="action-buttons">
          <button type="button" class="inquiry-btn" id="pd-send-enquiry">Send inquiry</button>

          <button type="button"
            class="add-cart-btn hover-btn text-uppercase is_stock_class_add add-to-cart-btn"
            style="background:#fff; color:#ff4d00;"
            data-variation_modal="1"
            data-product_id="<?php echo $product_id; ?>"
            data-vendor_id="<?php echo $vendor_id; ?>"
            data-quantity="1"
            data-variation_id=""
            data-attribute_item="[]"
            data-cart_endpoint="cart">
            <i class="uil uil-shopping-cart-alt"></i>Add to Cart
          </button>
          <a href="<?php echo base_url('customer/checkout/buy_now/'.$product_id); ?>" class="order-btn hover-btn" style="display:inline-flex; align-items:center; justify-content:center;">Buy Now</a>
        </div>

      </div>
    </div>

    <div class="row mt30">
      <div class="col-lg-12">
        <div class="shop_single3_style ui_kit_tab style2">
          <ul class="nav nav-tabs mb15" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link mt-3 mt-xl-0 mb-0 me-3 me-xl-5 active" id="description-tab" data-toggle="tab" data-target="#pd-desc" data-bs-toggle="tab" data-bs-target="#pd-desc" type="button" role="tab" aria-controls="pd-desc" aria-selected="true">Description</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link mt-3 mt-xl-0 mb-0" id="customerreview-tab" data-toggle="tab" data-target="#pd-reviews" data-bs-toggle="tab" data-bs-target="#pd-reviews" type="button" role="tab" aria-controls="pd-reviews" aria-selected="false">Customer Reviews</button>
            </li>
          </ul>
          <div class="tab-content pt20 row" id="myTabContent">
            <div class="tab-pane fade show active col-lg-12" id="pd-desc" role="tabpanel" aria-labelledby="description-tab">
              <div class="shop_single_description">
                <div class="para" id="pd-desc-body"><?php echo $desc_html ? $desc_html : '-'; ?></div>
              </div>
            </div>
            <div class="tab-pane fade col-lg-12" id="pd-reviews" role="tabpanel" aria-labelledby="customerreview-tab">
<?php
  // Fetch reviews directly in the view to bypass any controller syncing issues
  $ci =& get_instance();
  $c_sess = $ci->session->userdata('customer');
  $c_id = ($c_sess && isset($c_sess['login_id'])) ? (int)$c_sess['login_id'] : 0;

  $this->db
      ->select('r.review_rating_id, r.customer_id, r.title, r.rating, r.review, r.status, r.date_created')
      ->select("TRIM(CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,''))) AS reviewer_name", false)
      ->from('ec_review_rating r')
      ->join('ec_customer c', 'c.customer_id = r.customer_id', 'left')
      ->where('r.product_id', $product_id);

  if ($c_id > 0) {
      $this->db->where("(r.status = '1' OR r.customer_id = $c_id)");
  } else {
      $this->db->where('r.status', '1');
  }

  $direct_reviews = $this->db
      ->order_by('r.date_created', 'DESC')
      ->get()->result_array();

  $d_total = 0;
  $d_star_counts = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0];
  $d_sum = 0;
  $d_my_review = null;

  foreach($direct_reviews as &$drv) {
      $s = (int)$drv['rating'];
      
      if ($drv['status'] === '1') {
          $d_total++;
          if(isset($d_star_counts[$s])) $d_star_counts[$s]++;
          $d_sum += $s;
      }
      
      $drv['reviewer_name'] = trim($drv['reviewer_name']) ?: 'Anonymous';
      $drv['reviewer_initial'] = strtoupper(substr($drv['reviewer_name'], 0, 1));
      $drv['date_formatted'] = date('M j, Y', strtotime($drv['date_created']));
      
      if ($c_id && (int)$drv['customer_id'] === $c_id) {
          $d_my_review = $drv;
      }
  }
  unset($drv);

  $d_avg = $d_total > 0 ? round($d_sum / $d_total, 1) : 0;
  $d_breakdown = [];
  for($i=5; $i>=1; $i--){
      $cnt = $d_star_counts[$i];
      $d_breakdown[$i] = [
          'count' => $cnt,
          'percent' => $d_total > 0 ? round(($cnt / $d_total) * 100) : 0
      ];
  }

  $reviews        = $direct_reviews;
  $review_summary = ['total'=>$d_total, 'avg'=>$d_avg, 'breakdown'=>$d_breakdown];
  $my_review      = $d_my_review;
  
  $rv_avg         = (float)$d_avg;
  $rv_total       = (int)$d_total;
  $rv_breakdown   = $d_breakdown;

  // Helper: render star icons for a given rating value
  if (!function_exists('render_stars')) {
      function render_stars($rating, $max = 5) {
        $out = '';
        for ($i = 1; $i <= $max; $i++) {
          if ($i <= $rating)      $out .= '<i class="fas fa-star" style="color:#f5a623;"></i> ';
          elseif ($i - 0.5 <= $rating) $out .= '<i class="fas fa-star-half-alt" style="color:#f5a623;"></i> ';
          else                    $out .= '<i class="far fa-star" style="color:#f5a623;"></i> ';
        }
        return $out;
      }
  }
?>
              <div class="row">
                <!-- Left: avg + breakdown + write button -->
                <div class="col-lg-6 col-xl-4">
                  <div class="review_average mb30">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <div class="title" id="rv-avg-score"><?php echo $rv_avg > 0 ? number_format($rv_avg,1) : '—'; ?></div>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <div class="sspd_postdate">
                          <div class="sspd_review">
                            <ul class="mb0">
                              <?php for ($si=1;$si<=5;$si++): ?>
                              <li class="list-inline-item">
                                <?php if ($si <= floor($rv_avg)): ?>
                                  <i class="fas fa-star" style="color:#f5a623;"></i>
                                <?php elseif ($si - 0.5 <= $rv_avg): ?>
                                  <i class="fas fa-star-half-alt" style="color:#f5a623;"></i>
                                <?php else: ?>
                                  <i class="far fa-star" style="color:#ddd;"></i>
                                <?php endif; ?>
                              </li>
                              <?php endfor; ?>
                            </ul>
                          </div>
                        </div>
                        <div class="total_review" id="rv-total-label"><?php echo $rv_total; ?> review<?php echo $rv_total !== 1 ? 's' : ''; ?></div>
                      </div>
                    </div>
                  </div>

                  <!-- Per-star breakdown -->
                  <?php for ($si=5; $si>=1; $si--):
                    $pct = isset($rv_breakdown[$si]['percent']) ? (int)$rv_breakdown[$si]['percent'] : 0;
                  ?>
                  <div class="d-flex justify-content-between align-items-center single_line_review pr30 pr0-lg mb10">
                    <div class="me-1"><?php echo $si; ?> star</div>
                    <div class="progress mx-3" style="flex:1; height:8px; border-radius:4px; background:#eee;">
                      <div class="progress-bar" style="width:<?php echo $pct; ?>%; background:#f5a623;" role="progressbar"></div>
                    </div>
                    <div class="heading-color" style="min-width:38px; text-align:right;"><?php echo $pct; ?>%</div>
                  </div>
                  <?php endfor; ?>


                </div>

                <!-- Right: reviews list -->
                <div class="col-xl-8">
                  <div class="product_single_content mb30">
                    <div class="mbp_pagination_comments">
                      <h5 class="mb30" id="rv-heading">
                        <?php echo $rv_total > 0 ? $rv_total . ' Review' . ($rv_total !== 1 ? 's' : '') . ' For This Product' : 'No Reviews Yet'; ?>
                      </h5>
                      <style>
                        .pending-blur-wrap {
                          filter: blur(5px);
                          opacity: 0.3;
                          pointer-events: none;
                          user-select: none;
                        }
                      </style>
                      <div id="rv-list">
                      <?php if (count($reviews) === 0): ?>
                        <div class="text-center py-4" style="color:#888;">
                          <i class="far fa-comment-dots" style="font-size:40px;margin-bottom:10px;display:block;"></i>
                          Be the first to review this product!
                        </div>
                      <?php else: ?>
                        <?php foreach ($reviews as $rv): ?>
                        <div class="mbp_first d-flex align-items-start mb20" style="border-bottom:1px solid #f0f0f0; padding-bottom:16px; position: relative;">
                          <!-- Avatar -->
                          <div class="flex-shrink-0 me-3" style="width:46px; height:46px; border-radius:50%; background:linear-gradient(135deg,#f5a623,#e05c00); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:18px;">
                            <?php echo htmlspecialchars($rv['reviewer_initial']); ?>
                          </div>
                          
                          <div class="flex-grow-1" style="position: relative;">
                            <!-- Pending Blur Wrap if status is 0 -->
                            <div class="<?php echo $rv['status'] === '0' ? 'pending-blur-wrap' : ''; ?>">
                              <div class="d-flex flex-wrap align-items-center mb1">
                                <!-- Stars -->
                                <span class="me-2"><?php echo render_stars($rv['rating']); ?></span>
                                <?php if (!empty($rv['title'])): ?>
                                <h6 class="sub_title mb0 me-2" style="font-size:14px;"><?php echo htmlspecialchars($rv['title']); ?></h6>
                                <?php endif; ?>
                              </div>
                              <div class="review_post_meta" style="font-size:12px; color:#888; margin-bottom:6px;">
                                Reviewed by <strong><?php echo htmlspecialchars($rv['reviewer_name']); ?></strong>
                                &mdash; <?php echo htmlspecialchars($rv['date_formatted']); ?>
                              </div>
                              <?php if (!empty($rv['review'])): ?>
                              <p style="font-size:14px; color:#555; margin:0;"><?php echo nl2br(htmlspecialchars($rv['review'])); ?></p>
                              <?php endif; ?>
                            </div>

                            <!-- Floating badge overlay in middle if status is 0 -->
                            <?php if ($rv['status'] === '0'): ?>
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; z-index: 5;">
                              <div class="badge badge-warning bg-warning text-dark px-3 py-2" style="font-size: 13px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-clock-o"></i> Your review is pending admin approval
                              </div>
                            </div>
                            <?php endif; ?>
                          </div>
                        </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                      </div><!-- /#rv-list -->
                    </div>
                  </div>
                </div><!-- /.col-xl-8 -->

              </div><!-- /.row -->
            </div><!-- /#pd-reviews tab-pane -->
            </div><!-- /#myTabContent tab-content -->
          </div><!-- /.shop_single3_style -->
        </div><!-- /.col-lg-12 -->
      </div><!-- /.row.mt30 -->



    <!-- Related products loaded dynamically via AJAX -->
    <div class="row" style="margin-top:20px; display:none;" id="pd-related-products-section">
      <div class="col-lg-12">
        <h4>Related Products</h4>
        <div class="row" id="pd-related-products-list">
          <!-- Dynamically populated via AJAX/fetch -->
        </div>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="pdEnquiryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document" style="max-width:520px;">
    <div class="modal-content" style="border-radius:12px; overflow:hidden;">
      <div class="modal-header" style="border:0; padding:16px 18px;">
        <h5 class="modal-title" style="font-weight:700;">Send enquiry</h5>
        <button type="button" id="pdEnquiryCloseBtn" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="border:0; background:transparent; font-size:22px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding:0 18px 18px;">
        <div id="pdEnquiryError" class="alert alert-danger" style="display:none;"></div>
        <div id="pdEnquirySuccess" class="alert alert-success" style="display:none;"></div>
		<?php
			$customer_sess = isset($_SESSION['customer']) && is_array($_SESSION['customer']) ? $_SESSION['customer'] : null;
			$pd_logged_in = ($customer_sess && isset($customer_sess['login_id']) && (int)$customer_sess['login_id'] > 0);
			$pd_full_name = $pd_logged_in ? (string)($customer_sess['fname'] ?? ($customer_sess['name'] ?? '')) : '';
			$pd_email = $pd_logged_in ? (string)($customer_sess['email'] ?? '') : '';
			$pd_mobile = $pd_logged_in ? (string)($customer_sess['mobile'] ?? ($customer_sess['phone'] ?? '')) : '';
		?>
		<div style="font-size:14px; font-weight:800; color:#111; margin:0 0 12px;" id="pdEnquiryHeading">
			Product enquiry: <?php echo htmlspecialchars($title); ?>
		</div>

        <form id="pdEnquiryForm" method="post">
          <div class="form-group">
            <label style="font-size:12px; font-weight:600;">Full Name</label>
            <input type="text" class="form-control" name="fname" required value="<?php echo htmlspecialchars($pd_full_name); ?>">
          </div>
          <div class="form-group">
            <label style="font-size:12px; font-weight:600;">Email</label>
            <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($pd_email); ?>">
          </div>
		  <div class="form-group">
			<label style="font-size:12px; font-weight:600;">Mobile</label>
			<input type="text" class="form-control" name="mobile" value="<?php echo htmlspecialchars($pd_mobile); ?>">
		  </div>
		  <input type="hidden" name="product_id" value="<?php echo (int)$product_id; ?>">
		  <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($title); ?>">
          <div class="form-group">
            <label style="font-size:12px; font-weight:600;">Message</label>
            <textarea class="form-control" rows="4" name="message" id="pdEnquiryMessage" required></textarea>
          </div>
		  <?php if($csrf && isset($csrf->name) && isset($csrf->hash)){ ?>
			<input type="hidden" name="<?php echo $csrf->name; ?>" value="<?php echo $csrf->hash; ?>" />
		  <?php } ?>
          <button type="submit" id="pdEnquiryBtn" class="btn" style="width:100%; background:#ff6a00; color:#fff; font-weight:700; border-radius:10px;">
            Send
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  function initHoverZoom() {
    var wrapper = document.querySelector('.zoomimg_wrapper');
    var img = document.getElementById('pd-main-img');
    if (wrapper && img) {
      wrapper.addEventListener('mousemove', function(e) {
        if (img.style.display === 'none') {
          img.style.transform = 'none';
          return;
        }
        var rect = wrapper.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var xPercent = (x / rect.width) * 100;
        var yPercent = (y / rect.height) * 100;
        
        img.style.transformOrigin = xPercent + '% ' + yPercent + '%';
        img.style.transform = 'scale(2.2)';
      });
      
      wrapper.addEventListener('mouseleave', function() {
        img.style.transform = 'scale(1)';
        img.style.transformOrigin = 'center center';
      });
    }
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHoverZoom);
  } else {
    initHoverZoom();
  }

  window.pdSelectThumb = function(btn, type, url){
    try {
      if (typeof url === 'undefined') {
        url = type;
        type = 'image';
      }

      var mainImg = document.getElementById('pd-main-img');
      var mainVid = document.getElementById('pd-main-video');

      if (type === 'video') {
        if (mainImg) mainImg.style.display = 'none';
        if (mainVid) {
          mainVid.style.display = 'block';
          var source = mainVid.querySelector('source');
          if (source && source.src !== url) {
            source.src = url;
            mainVid.load();
          }
        }
      } else {
        if (mainVid) {
          mainVid.style.display = 'none';
          mainVid.pause();
        }
        if (mainImg) {
          mainImg.style.display = 'block';
          if (url) {
            mainImg.src = url;
          }
        }
      }

      var wrap = document.getElementById('pd-gallery-thumbs');
      if (wrap) {
        var buttons = wrap.querySelectorAll('button.nav-link');
        for (var i = 0; i < buttons.length; i++) {
          buttons[i].classList.remove('active');
        }
      }
      if (btn && btn.classList) {
        btn.classList.add('active');
      }
    } catch (e) {}
  };
})();

(function(){
  var variations = <?php echo json_encode($variations); ?>;
  var defaultSelection = <?php echo json_encode($default_selection); ?>;
  var productId = <?php echo (int)$product_id; ?>;
  var baseUrl = (typeof window.base_url !== 'undefined' && window.base_url) ? String(window.base_url) : '<?php echo base_url(); ?>';

  function money(n){
    var x = parseFloat(n);
    if (isNaN(x)) x = 0;
    x = x * (parseFloat(window.currentCurrencyRate) || 1.0);
    return (window.currentCurrencySymbol || '$') + x.toFixed(2);
  }

  function getQty(){
    return 1;
  }

  function applyApiHydration(apiData){
    try {
      if (!apiData || typeof apiData !== 'object') return;

      var keyMap = {};
      try {
        var domInputs = document.querySelectorAll('.pd-attr-radio');
        domInputs.forEach(function(i){
          var a = i.getAttribute('data-attr');
          if (!a) return;
          keyMap[String(a).trim().toLowerCase()] = String(a);
        });
      } catch (e0) {}

      function remapKeys(obj){
        if (!obj || typeof obj !== 'object') return obj;
        var out = {};
        for (var kk in obj) {
          if (!Object.prototype.hasOwnProperty.call(obj, kk)) continue;
          var nk = keyMap[String(kk).trim().toLowerCase()] || kk;
          out[nk] = obj[kk];
        }
        return out;
      }

      if (Array.isArray(apiData.variations)) {
        var nextVars = [];
        for (var i=0;i<apiData.variations.length;i++){
          var v = apiData.variations[i];
          if (!v || typeof v !== 'object') continue;
          var vv = v;
          try {
            if (vv.attributes && typeof vv.attributes === 'object') {
              vv.attributes = remapKeys(vv.attributes);
            }
          } catch (e1) {}
          nextVars.push(vv);
        }
        variations = nextVars;
      }

      if (apiData.default_selection && typeof apiData.default_selection === 'object') {
        defaultSelection = remapKeys(apiData.default_selection);
      }
    } catch (e) {}

    try {
      for (var k in defaultSelection) {
        if (defaultSelection.hasOwnProperty(k)) {
          setSelectedButton(k, defaultSelection[k]);
        }
      }
    } catch (e2) {}

    try { refresh(); } catch (e3) {}
  }

  function getSelection(){
    var sel = {};
    var inputs = document.querySelectorAll('.pd-attr-radio:checked');
    inputs.forEach(function(i){
      var attr = i.getAttribute('data-attr');
      var val = i.value;
      if (attr) {
        sel[attr] = val;
      }
    });
    for (var k in defaultSelection) {
      if (defaultSelection.hasOwnProperty(k) && typeof sel[k] === 'undefined') {
        sel[k] = defaultSelection[k];
      }
    }
    return sel;
  }

  function tierPriceForQty(tiers, qty){
    if (!Array.isArray(tiers) || !tiers.length) return null;
    for (var i=0;i<tiers.length;i++){
      var t = tiers[i] || {};
      var min = parseInt(t.min_qty || 1, 10);
      var max = parseInt(t.max_qty || 0, 10);
      var price = t.price;
      if (price === null || typeof price === 'undefined' || price === '') continue;
      if (qty >= min && (!max || qty <= max)) return price;
    }
    var last = tiers[tiers.length-1];
    return last && last.price ? last.price : null;
  }

  function isMatch(vAttrs, sel){
    for (var k in sel) {
      if (!sel.hasOwnProperty(k)) continue;
      if (typeof vAttrs[k] === 'undefined' || String(vAttrs[k]) !== String(sel[k])) {
        return false;
      }
    }
    return true;
  }

  function hasAnyVariation(sel){
    if (!Array.isArray(variations)) return false;
    for (var i=0;i<variations.length;i++){
      var v = variations[i];
      if (!v || typeof v !== 'object') continue;
      var attrs = v.attributes || {};
      if (isMatch(attrs, sel)) return true;
    }
    return false;
  }

  function updateOptionAvailability(){
    var inputs = document.querySelectorAll('.pd-attr-radio');
    var byAttr = {};
    var attrOrder = [];
    inputs.forEach(function(i){
      var a = i.getAttribute('data-attr');
      if (!a) return;
      if (!byAttr[a]) {
        byAttr[a] = [];
        attrOrder.push(a);
      }
      byAttr[a].push(i);
    });

    var runningSel = {};

    for (var idx = 0; idx < attrOrder.length; idx++) {
      var attr = attrOrder[idx];
      var list = byAttr[attr] || [];
      if (!list.length) continue;

      var enabledAny = false;
      for (var j = 0; j < list.length; j++) {
        var inp = list[j];
        var testSel = {};
        for (var k in runningSel) {
          if (runningSel.hasOwnProperty(k)) testSel[k] = runningSel[k];
        }
        testSel[attr] = inp.value;
        var ok = hasAnyVariation(testSel);
        inp.disabled = !ok;

        var lbl = document.querySelector('label.pd-option-btn[for="' + inp.id + '"]');
        if (lbl) {
          if (ok) {
            lbl.classList.remove('pd-disabled');
            lbl.style.opacity = '';
            lbl.style.pointerEvents = '';
            lbl.style.textDecoration = '';
          } else {
            lbl.classList.add('pd-disabled');
            lbl.style.opacity = '0.55';
            lbl.style.pointerEvents = 'none';
            lbl.style.textDecoration = 'line-through';
          }
        }
        if (ok) enabledAny = true;
      }

      var checked = document.querySelector('.pd-attr-radio[data-attr="' + String(attr).replace(/"/g,'\\"') + '"]:checked');
      if (checked && checked.disabled) {
        checked = null;
      }

      if (!checked && enabledAny) {
        for (var j2 = 0; j2 < list.length; j2++) {
          if (!list[j2].disabled) {
            list[j2].checked = true;
            checked = list[j2];
            break;
          }
        }
      }

      if (checked) {
        runningSel[attr] = checked.value;
      }
    }
  }

  function matchVariation(sel){
    if (!Array.isArray(variations)) return null;
    for (var i=0;i<variations.length;i++){
      var v = variations[i];
      if (!v || typeof v !== 'object') continue;
      var attrs = v.attributes || {};
      if (isMatch(attrs, sel)) return v;
    }
    return null;
  }

  function setSelectedButton(attr, value){
    var selector = '.pd-attr-radio[data-attr="' + String(attr).replace(/"/g,'\\"') + '"][value="' + String(value).replace(/"/g,'\\"') + '"]';
    var input = document.querySelector(selector);
    if (input) {
      input.checked = true;
    }
  }

  function refresh(){
    var sel = getSelection();
    var qty = getQty();

    updateOptionAvailability();
    sel = getSelection();

    var v = matchVariation(sel);

    var priceText = null;
    if (v) {
      if (v.effective_price) {
        priceText = v.effective_price;
      } else {
        var p = tierPriceForQty(v.price_tiers, qty);
        priceText = p ? p : v.price;
      }
    }

    var tierGrid = document.getElementById('pd-tier-grid');
    var sampleEl = document.getElementById('pd-sample-price');
    if (tierGrid) {
      tierGrid.innerHTML = '';
      var tiers = v && Array.isArray(v.price_tiers) ? v.price_tiers : [];
      if (tiers.length) {
        tiers.forEach(function(t){
          if (!t) return;
          var min = parseInt(t.min_qty || 1, 10);
          var max = t.max_qty === null || typeof t.max_qty === 'undefined' ? 0 : parseInt(t.max_qty || 0, 10);
          var label = '';
          if (!max || max <= 0) {
            label = '≥ ' + min + ' pieces';
          } else {
            label = min + ' - ' + max + ' pieces';
          }
          var price = (t.price !== null && typeof t.price !== 'undefined') ? t.price : null;
          var div = document.createElement('div');
          div.className = 'tier';
          div.innerHTML = '<span class="qty">' + label + '</span><span class="price">' + money(price) + '</span>';
          tierGrid.appendChild(div);
        });
        var first = tiers[0] || null;
        if (sampleEl && first && typeof first.price !== 'undefined') {
          sampleEl.textContent = money(first.price);
        }
      } else {
        tierGrid.innerHTML = '<div style="color:#777; font-size:12px;">-</div>';
        if (sampleEl) sampleEl.textContent = '-';
      }
    }

    var mainImg = document.getElementById('pd-main-img');
    var mainVid = document.getElementById('pd-main-video');
    if (v && Array.isArray(v.image_urls) && v.image_urls.length) {
      if (mainImg) {
        mainImg.src = v.image_urls[0];
        mainImg.style.display = 'block';
      }
      if (mainVid) {
        mainVid.style.display = 'none';
        mainVid.pause();
      }
      var thumbs = document.getElementById('pd-gallery-thumbs');
      if (thumbs) {
        var buttons = thumbs.querySelectorAll('button.nav-link');
        buttons.forEach(function(btn, idx) {
          if (idx === 0) btn.classList.add('active');
          else btn.classList.remove('active');
        });
      }
    }

    for (var k2 in sel) {
      if (!sel.hasOwnProperty(k2)) continue;
      var btnSel = document.querySelector('.pd-attr-btn[data-attr="'+String(k2).replace(/"/g,'\\"')+'"][data-value="'+String(sel[k2]).replace(/"/g,'\\"')+'"]');
      var label = btnSel ? btnSel.getAttribute('data-label') : '';
      var el = document.getElementById('pd-selected-' + k2);
      if (el) {
        el.textContent = label || '';
      }
    }

    var btn = document.querySelector('.add-to-cart-btn');
    if (btn) {
      btn.setAttribute('data-quantity', String(qty));
      btn.setAttribute('data-variation_id', v && v.variation_id ? String(v.variation_id) : '');

	  if (!v) {
		btn.disabled = true;
		btn.style.opacity = '0.6';
	  } else {
		btn.disabled = false;
		btn.style.opacity = '';
	  }

	  var attrIds = [];
	  for (var k in sel) {
		  if (sel.hasOwnProperty(k)) {
			  attrIds.push(String(sel[k]));
		  }
	  }
	  attrIds.sort();
	  btn.setAttribute('data-attribute_item', JSON.stringify(attrIds));
    }
  }

  document.addEventListener('change', function(e){
    var t = e.target;
    if (t && t.classList && t.classList.contains('pd-attr-radio')) {
      refresh();
    }
  });

  for (var k in defaultSelection) {
    if (defaultSelection.hasOwnProperty(k)) {
      setSelectedButton(k, defaultSelection[k]);
    }
  }
  refresh();

  // Make the product detail page visibly API-driven in the browser (Network tab)
  // by fetching the same detail payload from /api/v1/products/detail.
  try {
    var apiUrl = baseUrl.replace(/\/+$/,'/') + 'api/v1/products/detail';
    if (apiUrl.startsWith('http://') && window.location.protocol === 'https:') {
      apiUrl = apiUrl.replace('http://', 'https://');
    }
    var payload = { product_id: productId };
    try {
      var csrfName = (typeof window.csrf_token_name !== 'undefined' && window.csrf_token_name) ? String(window.csrf_token_name) : '';
      var csrfHash = (typeof window.csrf_hash !== 'undefined' && window.csrf_hash) ? String(window.csrf_hash) : '';
      if (csrfName && csrfHash) {
        payload[csrfName] = csrfHash;
      }
    } catch (e5) {}

    fetch(apiUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(function(r){ return r.json(); })
      .then(function(resp){
        if (resp && (parseInt(resp.status, 10) === 1) && resp.data) {
          // Intentionally do not override the server-rendered UI state.
          // This request exists to make the product-detail API visible in the browser Network tab
          // while preserving the existing working variation/price behavior.
        }
      })
      .catch(function(){ /* ignore */ });
  } catch (e4) {}

  // Fetch related products dynamically from the API and render them client-side
  try {
    var relatedUrl = baseUrl.replace(/\/+$/,'/') + 'api/v1/products/related_products';
    if (relatedUrl.startsWith('http://') && window.location.protocol === 'https:') {
      relatedUrl = relatedUrl.replace('http://', 'https://');
    }
    
    var placeholder = '<?php echo $placeholder; ?>';

    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    var relatedPayload = { product_id: productId };
    try {
      var csrfName = (typeof window.csrf_token_name !== 'undefined' && window.csrf_token_name) ? String(window.csrf_token_name) : '';
      var csrfHash = (typeof window.csrf_hash !== 'undefined' && window.csrf_hash) ? String(window.csrf_hash) : '';
      if (csrfName && csrfHash) {
        relatedPayload[csrfName] = csrfHash;
      }
    } catch (eRelCsrf) {}

    fetch(relatedUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(relatedPayload)
    })
      .then(function(r) { return r.json(); })
      .then(function(resp) {
        if (resp && (parseInt(resp.status, 10) === 1) && resp.data && Array.isArray(resp.data.items) && resp.data.items.length > 0) {
          var container = document.getElementById('pd-related-products-section');
          var list = document.getElementById('pd-related-products-list');
          if (container && list) {
            var html = '';
            var itemsToShow = resp.data.items.slice(0, 12);
            itemsToShow.forEach(function(rp) {
              // Related products API returns raw (unconverted) prices — apply rate here
              var rawPrice = parseFloat(rp.price || rp['price_range'] && rp['price_range']['min'] || 0);
              var displayPrice = (rawPrice * (parseFloat(window.currentCurrencyRate) || 1.0)).toFixed(2);
              var sym = window.currentCurrencySymbol || '$';
              var url = rp.url || '#';
              var img = rp.image_url || placeholder;
              var name = rp.name || '';

              html += '<div class="rp-col">' +
                      '  <a href="' + escapeHtml(url) + '" class="rp-card">' +
                      '    <div class="rp-card__img-wrap">' +
                      '      <img src="' + escapeHtml(img) + '" alt="' + escapeHtml(name) + '" onerror="this.src=\'' + escapeHtml(placeholder) + '\'">' +
                      '    </div>' +
                      '    <div class="rp-card__body">' +
                      '      <p class="rp-card__title">' + escapeHtml(name) + '</p>' +
                      '      <p class="rp-card__price">' + escapeHtml(sym) + displayPrice + '</p>' +
                      '    </div>' +
                      '  </a>' +
                      '</div>';
            });
            list.innerHTML = html;
            container.style.display = 'flex';
          }
        }
      })
      .catch(function(err) { console.error('Error fetching related products:', err); });
  } catch (eRelated) {}


  var sendBtn = document.getElementById('pd-send-enquiry');
  if (sendBtn) {
    sendBtn.addEventListener('click', function(){
	  var errBox2 = document.getElementById('pdEnquiryError');
	  var okBox2 = document.getElementById('pdEnquirySuccess');
	  if (errBox2) { errBox2.textContent = ''; errBox2.style.display = 'none'; }
	  if (okBox2) { okBox2.textContent = ''; okBox2.style.display = 'none'; }
      var msgEl = document.getElementById('pdEnquiryMessage');
	  if (msgEl) msgEl.value = '';
	  try {
		var el = document.getElementById('pdEnquiryModal');
		if (typeof window.bootstrap !== 'undefined' && window.bootstrap && window.bootstrap.Modal && el) {
			var inst = null;
			try { inst = window.bootstrap.Modal.getInstance(el); } catch (e) {}
			try { if (!inst) inst = new window.bootstrap.Modal(el); } catch (e) {}
			if (inst && typeof inst.show === 'function') inst.show();
		} else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
			jQuery('#pdEnquiryModal').modal('show');
		}
	  } catch (e) {}
    });
  }

	var closeBtn = document.getElementById('pdEnquiryCloseBtn');
	if (closeBtn) {
		closeBtn.addEventListener('click', function(){
			try {
				var el = document.getElementById('pdEnquiryModal');
				if (typeof window.bootstrap !== 'undefined' && window.bootstrap && window.bootstrap.Modal && el) {
					var inst = null;
					try { inst = window.bootstrap.Modal.getInstance(el); } catch (e) {}
					try { if (!inst) inst = new window.bootstrap.Modal(el); } catch (e) {}
					if (inst && typeof inst.hide === 'function') inst.hide();
				} else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
					jQuery('#pdEnquiryModal').modal('hide');
				}
			} catch (e) {}
		});
	}

  var form = document.getElementById('pdEnquiryForm');
  var errBox = document.getElementById('pdEnquiryError');
  var okBox = document.getElementById('pdEnquirySuccess');
  var btn = document.getElementById('pdEnquiryBtn');
  if (form) {
    form.addEventListener('submit', function(ev){
      ev.preventDefault();
      if (errBox) errBox.style.display = 'none';
      if (okBox) okBox.style.display = 'none';
      if (btn) btn.disabled = true;

      var payload = {
        product_id: parseInt(form.querySelector('input[name="product_id"]').value, 10),
        product_name: form.querySelector('input[name="product_name"]').value,
        fname: form.querySelector('input[name="fname"]').value,
        email: form.querySelector('input[name="email"]').value,
        mobile: form.querySelector('input[name="mobile"]').value,
        message: form.querySelector('textarea[name="message"]').value
      };

      var csrfName = (typeof window.csrf_token_name !== 'undefined' && window.csrf_token_name) ? String(window.csrf_token_name) : '';
      var csrfHash = (typeof window.csrf_hash !== 'undefined' && window.csrf_hash) ? String(window.csrf_hash) : '';
      if (csrfName && csrfHash) {
        payload[csrfName] = csrfHash;
      }

      var apiHeaders = {
        'Content-Type': 'application/json'
      };
      <?php
        $token = '';
        if (is_array($customer_sess) && !empty($customer_sess['access_token'])) {
            $token = (string)$customer_sess['access_token'];
        }
        if ($token !== '') {
      ?>
        apiHeaders['Authorization'] = 'Bearer <?= $token ?>';
      <?php } ?>

      var targetUrl = baseUrl.replace(/\/+$/,'/') + 'api/v1/product-enquiry/add';
      if (targetUrl.startsWith('http://') && window.location.protocol === 'https:') {
        targetUrl = targetUrl.replace('http://', 'https://');
      }

      fetch(targetUrl, {
        method: 'POST',
        headers: apiHeaders,
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      })
        .then(function(r){ return r.json(); })
        .then(function(data){
          if (data && (parseInt(data.status, 10) === 1)) {
            if (okBox) { okBox.textContent = data.message || 'Enquiry sent successfully'; okBox.style.display = 'block'; }
			setTimeout(function(){
				try {
					var el = document.getElementById('pdEnquiryModal');
					if (typeof window.bootstrap !== 'undefined' && window.bootstrap && window.bootstrap.Modal && el) {
						var inst = null;
						try { inst = window.bootstrap.Modal.getInstance(el); } catch (e) {}
						try { if (!inst) inst = new window.bootstrap.Modal(el); } catch (e) {}
						if (inst && typeof inst.hide === 'function') inst.hide();
					} else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
						jQuery('#pdEnquiryModal').modal('hide');
					}
				} catch (e) {}
			}, 2500);
			try {
				var msgEl2 = document.getElementById('pdEnquiryMessage');
				if (msgEl2) msgEl2.value = '';
			} catch (e) {}
          } else {
            var errMsg = 'Unable to submit';
            if (data && data.message === 'unauthorized') {
              errMsg = 'Please login for your query';
            } else if (data && data.errors && data.errors.length) {
              errMsg = data.errors.join(' ');
            } else if (data && data.message) {
              errMsg = data.message;
            }
            if (errBox) { errBox.textContent = errMsg; errBox.style.display = 'block'; }
          }
        })
        .catch(function(){
          if (errBox) { errBox.textContent = 'Unable to submit'; errBox.style.display = 'block'; }
        })
        .finally(function(){ if (btn) btn.disabled = false; });
    });
  }
})();
</script>


