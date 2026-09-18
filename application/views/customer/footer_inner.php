<?php
$cartCounts = get_cart_counts();
$cartQty = $cartCounts['total_items'];

// Load Footer Language File
$footer_lang = get_page_language_data('footer_page_lang');
?>
<!-- footer section starts -->
    <footer class="footer-section section-t-space">
        <div class="subscribe-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="subscribe-part">
                            <h5>
                                <?= $footer_lang->subscribe_title ?? "Don't pass up our fantastic discounts. email offers from all of our best eateries" ?>
                            </h5>
                            <div class="position-relative w-100">
                                <input type="email" class="form-control subscribe-form-control"
                                    placeholder="<?= $footer_lang->enter_email ?? 'Enter your Email' ?>">
                                <a href="#" class="btn theme-btn subscribe-btn mt-0"><?= $footer_lang->subscribe_now ?? 'Subscribe Now' ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="main-footer">
                <div class="row g-3">
                    <div class="col-xl-4 col-lg-12">
                        <div class="footer-logo-part">
                            <img class="img-fluid logo" src="/assets/customer/images/svg/logo.svg" alt="logo">
                            <p>
                                <?= $footer_lang->welcome_message ?? 'Welcome to our online order website! Here, you can browse our wide selection of products and place orders from the comfort of your own home.' ?>
                            </p>
                            <div class="social-media-part">
                                <ul class="social-icon">
                                    <li>
                                        <a href="https://www.facebook.com/login/">
                                            <i class="ri-facebook-fill icon"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://twitter.com/i/flow/login">
                                            <i class="ri-twitter-fill icon"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.linkedin.com/login/">
                                            <i class="ri-linkedin-fill icon"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/accounts/login/">
                                            <i class="ri-instagram-fill icon"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/">
                                            <i class="ri-youtube-fill icon"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="row g-3">
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                                <h5 class="footer-title"><?= $footer_lang->company ?? 'Company' ?></h5>
                                <ul class="content">
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6><?= $footer_lang->about_us ?? 'About us' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6><?= $footer_lang->contact_us ?? 'Contact us' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6><?= $footer_lang->offer ?? 'Offer' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="faq.html">
                                            <h6><?= $footer_lang->faqs ?? 'FAQs' ?></h6>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                                <h5 class="footer-title"><?= $footer_lang->account ?? 'Account' ?></h5>
                                <ul class="content">
                                    <li>
                                        <a href="<?= base_url('my_order') ?>">
                                            <h6><?= $footer_lang->my_orders ?? 'My orders' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= base_url('wishlist') ?>">
                                            <h6><?= $footer_lang->wishlist ?? 'Wishlist' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= base_url('checkout') ?>">
                                            <h6><?= $footer_lang->shopping_cart ?? 'Shopping Cart' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= base_url('my_address') ?>">
                                            <h6><?= $footer_lang->saved_address ?? 'Saved Address' ?></h6>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                                <h5 class="footer-title"><?= $footer_lang->useful_links ?? 'Useful links' ?></h5>
                                <ul class="content">
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6><?= $footer_lang->blog ?? 'Blogs' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= base_url('login') ?>">
                                            <h6><?= $footer_lang->login ?? 'Login' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= base_url('register') ?>">
                                            <h6><?= $footer_lang->register ?? 'Register' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= base_url('profile') ?>">
                                            <h6><?= $footer_lang->profile ?? 'Profile' ?></h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= base_url('my_account') ?>">
                                            <h6><?= $footer_lang->settings ?? 'Settings' ?></h6>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-12">
                                <h5 class="footer-title"><?= $footer_lang->top_brands ?? 'Top Brands' ?></h5>
                                <ul class="content">
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6>PizzaBoy</h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6>Saladish</h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6>IcePops</h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6>Maxican Hoy</h6>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            <h6>La Foodie</h6>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bottom-footer-part">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6><?= $footer_lang->copyright ?? '@ Copyright 2025 LA TROÏKA LIBANAISE. All rights Reserved.' ?></h6>
                    <img class="img-fluid cards" src="/assets/customer/images/icons/footer-card.png" alt="card">
                </div>
            </div>
        </div>
    </footer>
    <!-- footer section end -->

    <!-- cart fix panel -->
	<?php if($cartQty > 0 ) { ?>
    <div class="fixed-btn d-lg-none d-block">
        <div class="custom-container">
            <div class="cart-fixed-bottom">
                <h6 class="fw-medium cart_cnt"><?=$cartQty?> <?= $footer_lang->items ?? 'items' ?></h6>
                <a href="/checkout" class="cart-fixed-right">
                    <h6 class="fw-medium text-white">
                        <?= $footer_lang->view_cart ?? 'View cart' ?> <i class="ri-arrow-right-line"></i>
                    </h6>
                </a>
            </div>
        </div>
    </div>
	<?php } ?>
    <!-- cart fix panel -->

    <!-- location offcanvas start -->
    <div class="modal fade location-modal" id="location" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <h5 class="fw-semibold"><?= $footer_lang->select_location ?? 'Select a Location' ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="search-section">
                        <form class="form_search" role="form">
                            <input type="search" placeholder="<?= $footer_lang->search_location ?? 'Search Location' ?>" class="nav-search nav-search-field">
                        </form>
                    </div>
                    <a href="#!" class="current-location">
                        <div class="current-address">
                            <i class="ri-focus-3-line focus"></i>
                            <div>
                                <h5><?= $footer_lang->use_current_location ?? 'Use current-location' ?></h5>
                                <h6>Wellington St., Ottawa, Ontario, Canada</h6>
                            </div>
                        </div>
                        <i class="ri-arrow-right-s-line arrow"></i>
                    </a>
                    <h5 class="mt-sm-3 mt-2 fw-medium recent-title dark-text">
                        <?= $footer_lang->recent_location ?? 'Recent Location' ?>
                    </h5>
                    <a href="#!" class="recent-location">
                        <div class="recant-address">
                            <i class="ri-map-pin-line theme-color"></i>
                            <div>
                                <h5>Bayshore</h5>
                                <h6>Kingston St., Ottawa, Ontario, Canada</h6>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn gray-btn" data-bs-dismiss="modal"><?= $footer_lang->close ?? 'Close' ?></a>
                    <a href="#" class="btn theme-btn mt-0" data-bs-dismiss="modal"><?= $footer_lang->save ?? 'Save' ?></a>
                </div>
            </div>
        </div>
    </div>
    <!-- location offcanvas end -->

    <!-- toast starts -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex bg-theme text-white">
                <div class="toast-body">Restaurant URL Copied to Clipboard</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <!-- toast end -->

    <!-- Bg Overlay Start -->
    <div class="bg-overlay" id="overlay"></div>
    <!-- Bg Overlay end -->

    <!-- tap to top start -->
    <button class="scroll scroll-to-top menu-page">
        <i class="ri-arrow-up-s-line arrow"></i>
    </button>
    <!-- tap to top end -->

<div id="snackbar" style="z-index: 5000000000; !important;"></div>

    <!-- bootstrap js -->
    <script src="/assets/customer/js/bootstrap.bundle.min.js"></script>

    <!-- swiper js -->
    <script src="/assets/customer/js/swiper-bundle.min.js"></script>
    <script src="/assets/customer/js/custom-swiper.js"></script>

    <!-- footer accordion js -->
    <script src="/assets/customer/js/footer-accordion.js"></script>

    <!-- menu button js -->
    <script src="/assets/customer/js/menu-button.js"></script>

    <!-- fancybox js -->
    <script src="/assets/customer/js/fancybox.js"></script>

    <!-- toast js -->
    <script src="/assets/customer/js/toast.js"></script>

    <!-- script js -->
    <script src="/assets/customer/js/script.js"></script>

	<script src="/assets/common/js/snackbar.js"></script>
	<script src="/assets/common/js/common.js"></script>
	<script src="<?= base_url('assets/customer/js/add_to_cart.js?v='.(defined('FCPATH') ? @filemtime(FCPATH.'assets/customer/js/add_to_cart.js') : time())); ?>"></script>

<!-- Auto-logout on session expiry -->
<script>
(function () {
    var HOME_URL = "<?= base_url(); ?>";
    var SESSION_PING_URL = "<?= base_url('customer/check_session'); ?>";
    var _redirecting = false;

    function doSessionLogout() {
        if (_redirecting) return;
        // Do not redirect to home if we are on the checkout page
        if (window.location.pathname.indexOf('/checkout') !== -1) {
            return;
        }
        _redirecting = true;
        window.location.href = HOME_URL;
    }

    function isSessionExpiredResponse(data) {
        if (!data) return false;
        var status = (typeof data.STATUS !== 'undefined') ? parseInt(data.STATUS) :
                     (typeof data.status !== 'undefined' ? parseInt(data.status) : null);
        if (status === null) return false;
        var msg = '';
        if (data.MSG) msg = Array.isArray(data.MSG) ? data.MSG.join(' ') : String(data.MSG);
        if (data.msg) msg = msg || String(data.msg);
        return (status === 0 && msg.toLowerCase().indexOf('login required') !== -1);
    }

    if (window.jQuery) {
        // Catch HTTP 401 on any AJAX call
        $(document).ajaxError(function (event, xhr, settings) {
            if (xhr.status === 401) {
                if (settings && settings.url && settings.url.indexOf('/api/v1/auth/login') !== -1) {
                    return;
                }
                doSessionLogout();
            }
        });

        // Catch JSON "login required" responses
        $(document).ajaxSuccess(function (event, xhr, settings, data) {
            if (isSessionExpiredResponse(data)) { doSessionLogout(); }
        });

        // Periodic session ping every 5 minutes (only on customer-authenticated pages)
        if (document.querySelector('.account-page, .account-sidebar') || window.location.pathname.indexOf('/customer/') !== -1) {
            setInterval(function () {
                $.get(SESSION_PING_URL).done(function (resp) {
                    if (resp && (resp.status === 0 || resp.STATUS === 0)) { doSessionLogout(); }
                });
            }, 5 * 60 * 1000);
        }
    }
})();
</script>
</body>


</html>
