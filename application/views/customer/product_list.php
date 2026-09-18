<?php 
if(isset($slug))
{
	$slug = $slug == 'all' ? 'All Items' : $slug;
}
?>
<link rel="stylesheet" href="<?= base_url('assets/customer/css/jquery-ui.min.css') ?>">
<?php

if (!isset($symbol) || !isset($rate)) {
    // Resolve active currency and rate
    $_CI =& get_instance();
    $_currencies = $_CI->db
        ->select('currency_id, name, iso_code, symbol, rate, basic')
        ->from('ec_currency')
        ->where('status', '1')
        ->order_by('basic', 'desc')
        ->order_by('name', 'asc')
        ->get()->result();
    $_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
    $_cur = null;
    foreach ($_currencies as $_fc) {
        if ((int)$_fc->currency_id === $_cur_id) {
            $_cur = $_fc;
            break;
        }
    }
    if (!$_cur && !empty($_currencies)) {
        foreach ($_currencies as $_fc) {
            if ($_fc->basic == 1) { $_cur = $_fc; break; }
        }
        if (!$_cur) $_cur = $_currencies[0];
    }
    $symbol = $_cur ? $_cur->symbol : '$';
    $rate = $_cur ? (float)$_cur->rate : 1.0;
}
?>
<div class="wrapper">
	<!-- Custom Shop Category List Menu -->
	<section class="p0 bb1 overflow-hidden d-none">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="custom_shop_category_nav_list_menu">
						<ul class="mb0 d-flex">
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Inner Page Breadcrumb -->
	<section class="inner_page_breadcrumb">
		<div class="container">
			<div class="row">
				<div class="col-xl-12">
					<div class="breadcrumb_content">
						<ol class="breadcrumb">
						</ol>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Listing Grid View -->
	<section class="our-listing pt10">
		<div class="container">
			<style>
				.side_categories a.active-cat{border-left:2px solid #111;padding-left:8px;display:block;box-sizing:border-box;}
				.shop_item{height:100%;display:flex;flex-direction:column;}
				.shop_item:not(.product-list-row){min-height:420px;width:100%;}
				.shop_item .thumb{min-height:220px;display:flex;align-items:center;justify-content:center;}
				.shop_item:not(.product-list-row) .thumb{height:220px;overflow:hidden;}
				.shop_item .thumb img{height:200px;max-width:100%;width:auto;object-fit:contain;}
				.shop_item:not(.product-list-row) .thumb img{width:100%;}
				.shop_item .details{margin-top:auto;}
				.shop_item:not(.product-list-row) .details{flex:1 1 auto;display:flex;flex-direction:column;min-height:170px;}
				.shop_item .details .title{
					min-height: 54px;
					line-height: 1.4;
					overflow: hidden;
					display: -webkit-box;
					-webkit-line-clamp: 2;
					-webkit-box-orient: vertical;
					margin-bottom: 8px;
				}
				.shop_item:not(.product-list-row) .si_footer{margin-top:auto;}
				.toggle-view.active{font-weight:700;text-decoration:underline;}
				.product-list-row{flex-direction:row;align-items:center;gap:16px;}
				.product-list-row .thumb{min-height:120px;width:180px;flex:0 0 180px;}
				.product-list-row .thumb img{height:120px;}
				.product-list-row .details{margin-top:0;flex:1;}
				#accordion-subcategory a{position:relative;padding-right:18px;display:flex;align-items:flex-start;gap:8px;white-space:normal;word-break:break-word;}
				#accordion-subcategory a:before{content:none !important;}
				#accordion-subcategory a:after{content:'›';position:absolute;right:6px;top:14px;opacity:0.7;}
				
				/* Premium Category Cards Styling */
				.category_card {
					background: #ffffff;
					border: 1.5px solid #cccccc;
					border-radius: 16px;
					padding: 20px;
					text-align: center;
					box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
					transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
					display: flex;
					flex-direction: column;
					align-items: center;
					justify-content: center;
					cursor: pointer;
				}
				.category_card:hover {
					border-color: #f5c34b;
					box-shadow: 0 12px 30px rgba(0, 0, 0, 0.07);
					transform: translateY(-5px);
				}
				.category_card_inner {
					text-decoration: none !important;
					display: flex;
					flex-direction: column;
					align-items: center;
					width: 100%;
				}
				.category_img_wrap {
					width: 160px;
					height: 160px;
					border-radius: 50%;
					background: #fdfaf2;
					display: flex;
					align-items: center;
					justify-content: center;
					overflow: hidden;
					margin-bottom: 18px;
					border: 2.5px solid #cccccc;
					transition: all 0.35s ease;
				}
				.category_card:hover .category_img_wrap {
					transform: scale(1.08);
					background: #fdf7e3;
					border-color: #f5c34b;
				}
				.category_img_wrap img {
					max-width: 90%;
					max-height: 90%;
					object-fit: contain;
					transition: transform 0.35s ease;
				}
				.category_card_details {
					display: flex;
					flex-direction: column;
					align-items: center;
					width: 100%;
				}
				.category_card_title {
					font-size: 15px;
					font-weight: 600;
					color: #041e42;
					margin-bottom: 8px;
					line-height: 1.3;
					height: 40px;
					display: -webkit-box;
					-webkit-line-clamp: 2;
					-webkit-box-orient: vertical;
					overflow: hidden;
					transition: color 0.3s ease;
				}
				.category_card:hover .category_card_title {
					color: #f5c34b;
				}
				.category_card_action {
					font-size: 12px;
					font-weight: 700;
					color: #4059ad;
					text-transform: uppercase;
					letter-spacing: 0.5px;
					transition: all 0.3s ease;
					opacity: 0.85;
				}
				.category_card:hover .category_card_action {
					color: #4059ad;
					opacity: 1;
					transform: translateX(3px);
				}
				/* Hide sort dropdown, price filter and list/grid toggle on all-categories page */
				.all-categories-mode #headingTwo,
				.all-categories-mode #collapseTwo {
					display: none !important;
				}
				.all-categories-mode .page_control_shorting {
					display: none !important;
				}
				.all-categories-mode .toggle-view,
				.all-categories-mode li.list,
				.all-categories-mode li.gird {
					display: none !important;
				}
			</style>
			<div class="row">
				<div class="col-lg-3 col-xl-2 d-none d-lg-block">
					<div class="sidebar_accordion_widget">
						<i class="uil-refresh d-none"></i>
						<div class="faq_according text-start">
							<div class="accordion" id="accordionExample">
								<div class="card">
									<div class="card-header active" id="headingZero">
										<h4>
											<button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseZero" aria-expanded="true" aria-controls="collapseZero">Category</button>
										</h4>
									</div>
									<div id="collapseZero" class="collapse show" aria-labelledby="headingZero" data-parent="#accordionExample">
										<div class="card-body">
											<div class="left_sidebar_department_widgets">
												<ul class="side_categories" id="accordion-subcategory">
													<li class="mb-1"><div class="link"><a href="/products/all-categories" class="main_cat_css parent_list all-category-link">All Category</a></div></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<div class="card">
									<div class="card-header" id="headingOne">
										<h4>
											<button class="btn btn-link text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Filter by Brands</button>
										</h4>
									</div>
									<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
										<div class="card-body">
											<div class="sidebar_search_widget">
												<div class="blog_search_widget">
													<div class="input-group" id="search_brand">
														<input type="text" class="form-control mb15" id="brand_search_input" placeholder="Find a Brand" aria-label="Find a Brand">
													</div>
												</div>
											</div>
											<div class="sidebar_widget_checkbox">
												<div class="ui_kit_checkbox pb30" id="brand_result"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="card">
									<div class="card-header" id="headingTwo">
										<h4>
											<button class="btn btn-link text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">Price</button>
										</h4>
									</div>
									<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
										<div class="card-body">
											<div class="sidebar_widget_checkbox">
												<div class="zmart_custom_range_slider mb-4 mt10">
													<div class="d-flex align-items-center justify-content-between mb-2">
														<div id="price_range_label"><?= htmlspecialchars($symbol, ENT_QUOTES, 'UTF-8') ?>0 - <?= htmlspecialchars($symbol, ENT_QUOTES, 'UTF-8') ?>0</div>
														<button type="button" class="btn btn-sm btn-thm" id="price_apply_btn">Apply</button>
													</div>
													<input type="text" class="amount mt-0" placeholder="<?= $symbol ?>0">
													<input type="text" class="amount2 mt-0" placeholder="<?= $symbol ?><?= round(2000 * $rate) ?>">
													<div class="slider-range mt-3 ms-2"></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-9 col-xl-10 pl50 pl15-md">
					<div class="row">
						<div class="col-lg-12">
							<div class="main-title bb1 pb10">
								<h2 class="title heading_name"></h2>
								<p class="search-result-text"><span class="a-size-base a-color-base a-text-normal"></span></p>
							</div>
						</div>
					</div>

					<?php if(isset($vendor_obj->admin_uid)) { ?>
						<div class="store-listed mb20">
							<div class="storelisted-wrap">
								<div class="storelisted-photo"> <img src="<?=$vendor_obj->vendor_img?>" class="img-fluid"> </div>
								<div class="storelisted-details">
									<h4 class="storelisted-name mb-1 text-black"><?= $vendor_obj->store_name?></h4>
									<div class="storelisted-address text-grey"> <?= $vendor_obj->address?> </div>
								</div>
							</div>
						</div>
					<?php } ?>

					<div class="row">
						<div class="col-lg-7">
							<div class="filter_components">
								<ul class="mb0 align-items-center text-center text-lg-start">
									<li class="list-inline-item me-2 mb-3"><p class="pagination_page_count total_count"></p></li>
									<li class="list-inline-item me-2 list mb-3 pl10"><a href="#" class="per-page" data-length="20">20</a></li>
									<li class="list-inline-item me-2 list mb-3 pl10"><a href="#" class="per-page" data-length="40">40</a></li>
									<li class="list-inline-item me-2 list mb-3 pl10"><a href="#" class="per-page" data-length="60">60</a></li>
									<li class="list-inline-item me-2 list mb-3 pl10"><a href="#" class="per-page" data-length="0">All</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-5">
							<div class="filter_components text-center text-lg-end">
								<ul class="mb-2 mb-md-0">
									<li class="list-inline-item me-0">
										<div class="page_control_shorting mb20 text-center text-md-end">
											<input name="price_sort_type" id="price_sort_type" type="hidden">
											<select class="selectpicker show-tick" id="sort_select">
												<option value="">Default sorting</option>
												<option value="1">Latest</option>
												<option value="2">Price - Low to High</option>
												<option value="3">Price - High to Low</option>
											</select>
										</div>
									</li>
									<li class="d-none d-lg-inline-block list px-2"><a href="#" class="toggle-view" data-view="list">List</a></li>
									<li class="d-none d-lg-inline-block gird ps-2"><a href="#" class="toggle-view" data-view="grid">Grid</a></li>
								</ul>
							</div>
						</div>
					</div>

					<div class="row" id="search_result"></div>
					<div class="row" id="no_data"></div>
					<div class="row">
						<div class="col-lg-12">
							<div class="mbp_pagination mt30 text-center" id="pagination"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<script>
var csrf 	= <?=json_encode($csrf);?>;
var cat 	= <?=$cat?>;
var cat_slug 	= <?=json_encode(isset($cat_slug) ? (string)$cat_slug : '');?>;
var cat_image 	= "<?=$cat_image?>";
var login_id 	= "<?=isset($login_id) ? $login_id : ''?>";
var vendor_obj 	= <?=isset($vendor_obj) ? json_encode($vendor_obj) : '""'?>;
var fts 	= <?=json_encode((string)$fts);?>;
var slug_param 	= <?=json_encode(isset($slug_param) ? (string)$slug_param : '');?>;
</script>

<script src="/assets/js/pages/product_list.js?v=<?=@filemtime(FCPATH.'assets/js/pages/product_list.js')?>"></script>
