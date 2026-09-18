<?php

function build_nav_menu($categories, $parent_slug = 'item', $level = 1)
{
    if (empty($categories)) return '';

    $html = '';
    $index = 1;

    foreach ($categories as $cat) {
        $name = htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8');
        #$slug = $parent_slug . '-' . $index;
	$slug = $cat['slug'];

        $html .= '<li><a class="nav-link" href="' . $slug . '">' . $name . '</a></li>';

        if (!empty($cat['children'])) {
            $html .= '<li>';
            $html .= '<nav class="nav nav-pills sub-nav-pills">';
            $html .= '<ul>';
            $html .= build_nav_menu($cat['children'], $slug, $level + 1);
            $html .= '</ul>';
            $html .= '</nav>';
            $html .= '</li>';
        }

        $index++;
    }

    return $html;
}

?>

<!-- page head section starts -->
<section class="page-head-section">
    <div class="container page-heading">
        <h2 class="h3 mb-3 text-white text-center">Menu Listing</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-star">
                <li class="breadcrumb-item">
                    <a href="/"><i class="ri-home-line"></i>Home</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Menu Listing</li>
            </ol>
        </nav>
    </div>
</section>
<!-- page head section end -->

    <!-- tab section starts -->
    <section class="tab-details-section section-b-space">
        <div class="container">
            <div class="category-detail-tab">
                <div class="row g-4">
                    <div class="col-lg-9">
                        
                        <ul class="nav nav-tabs tab-style1" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="order-tab" data-bs-toggle="tab"
                                    data-bs-target="#online" type="button" role="tab">
                                    Order Online
                                </button>
                            </li>
                            
                            
                        </ul>
                        <div class="tab-content product-details-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="online" role="tabpanel" tabindex="0">
                                <div class="row g-lg-3 g-2">
                                    <div class="col-lg-4">
                                        <div class="product-sidebar sticky-top">
                                            <div class="sidebar-search">
                                                <input type="text" placeholder="Search Product..">
                                                <i class="ri-search-line"></i>
                                            </div>
                                            <nav id="navbar" class="product-items pb-0">
                                                <ul class="nav nav-pills">
                                                   <?=build_nav_menu($category_tree)?> 
                                                            </ul>
                                                        </nav>
                                                    </li>
                                                    
                                                    
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="product-box-section section-b-space">
                                            <div data-bs-spy="scroll" data-bs-target="#navbar"
                                                data-bs-smooth-scroll="true" class="scrollspy-example-2" tabindex="0">
                                                <div class="product-details-box-list" id="product-list"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            
                            
                        </div>
                    </div>
                    <div class="col-lg-3 product-details-content">
                        <div class="order-summery-section sticky-top" style="display:none">
                            <div class="checkout-detail">
                                <h3 class="fw-semibold dark-text checkout-title">
                                    Cart Items
                                </h3>
                                <div class="order-summery-section mt-0">
                                    <div class="checkout-detail p-0">
                                        <ul id="cart-list">
                                            
                                        </ul>
                                       	<div class="cart_total"></div> 
                                    </div>
                                </div>
                                <a href="/customer/checkout" class="btn theme-btn restaurant-btn w-100 rounded-2">Proceed to
                                    payment</a>
                                <img class="dots-design" src="/assets/customer/images/svg/dots-design.svg" alt="dots">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- tab section end -->
<div class="modal customized-modal" id="customized" tabindex="-1" aria-modal="true" role="dialog" style="display: none; padding-left: 0px;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="container">
                    <div class="filter-header">
                        <h5 class="title">Custom Food</h5>
                        <a href="#" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></a>
                    </div>
					<form method="post" name="customize-item-form" id="customize-item-form">
                    <div class="filter-body"></div>
                    <div class="filter-footer">
                        <a href="javascript:void(0);" class="btn theme-btn add-to-cart-btn w-100 mt-0" type="submit">Apply</a>
                    </div>
					</form>
                </div>
            </div>
        </div>
    </div>


<script>
	var cat_id 		= '<?=$cat_id?>';
	var product_cat_id 		= '<?=$product_cat_id?>';

<?php
    $modifiers = (isset($modifiers) && is_array($modifiers)) ? $modifiers : [];
    $extras = (isset($extras) && is_array($extras)) ? $extras : [];

    function safe_json($data) {
      return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
?>


try {
    var modifiers_json = <?php echo safe_json($modifiers); ?>;
    var extras_json = <?php echo safe_json($extras); ?>;

    if (typeof modifiers_json !== 'object' || modifiers_json === null) {
      modifiers_json = {};
    }

    if (typeof extras_json !== 'object' || extras_json === null) {
      extras_json = {};
    }

  } catch (e) {
    var modifiers_json = {};
    var extras_json = {};
  }
</script>
<script src="/assets/customer/js/item-list.js?r=1"></script> 
<script src="/assets/customer/js/cart_html.js?r=4"></script>
<script src="/assets/customer/js/add_to_cart.js?r=4"></script>
