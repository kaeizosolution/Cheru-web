<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

// Product list: All categories grid view
$route['products/all-categories'] = 'customer/product_list/index';

// Admin: Banner (uniform URL)
$route['admin/banner'] = 'admin/banner/get_ajax_banner/1';

// Admin: Deal of the Day (uniform URL)
$route['admin/deal_of_the_day'] = 'admin/deal_of_the_day/index';
$route['admin/deal_of_the_day_add'] = 'admin/deal_of_the_day/add';
$route['admin/deal_of_the_day_add/(:num)'] = 'admin/deal_of_the_day/update/$1';

$route['page/(.*)'] = "/customer/page/index";
$route['post(.*)'] = "/customer/posts/index";
$route['post/(.*)'] = "/customer/posts/index";
$route['shop/(.*)'] = "/customer/product_list/index/$1";
$route['shop'] = "/customer/product_list/index";
$route['category/(.*)'] = "/customer/product_list/category/$1";
$route['taxonomy/(.*)'] = "/customer/taxonomy/index/$1";
$route['product/detail/(.*)'] = "/customer/product/detail/$1";
$route['product/details/(:num)'] = "/customer/product/details/$1";
$route['product/details/(.*)'] = "/customer/product/detail/$1";
$route['product/(:any)'] = "/customer/product/slug/$1";
$route['store/(.*)/(.*)'] = "/customer/product_list/vendor_cat/$1/$2";
$route['tags/(.*)'] = "/customer/Tags";
$route['cart'] = "/customer/cart/index";
$route['cart/ajax_add_to_cart'] = "/customer/cart/ajax_add_to_cart";
$route['cart/ajax_cart_payload'] = "/customer/cart/ajax_cart_payload";
$route['cart/ajax_delete_to_cart'] = "/customer/cart/ajax_delete_to_cart";
$route['cart/ajax_update_quantity'] = "/customer/cart/ajax_update_quantity";
$route['customer/cart/ajax_add_to_cart'] = "/customer/cart/ajax_add_to_cart";
$route['customer/cart/ajax_cart_payload'] = "/customer/cart/ajax_cart_payload";
$route['customer/cart/ajax_delete_to_cart'] = "/customer/cart/ajax_delete_to_cart";
$route['customer/cart/ajax_update_quantity'] = "/customer/cart/ajax_update_quantity";
$route['checkout'] = "/customer/checkout/index";
$route['checkout/buy-now/(:num)'] = "/customer/checkout/buy_now/$1";
$route['products/category/(.*)'] = "/customer/product_list/category/$1";
$route['thanks/(.*)'] = "/customer/thanks/index/$1";
$route['stores'] = "/welcome/stores";

$route['login'] = "customer/login";
$route['register'] = "customer/login/register";
$route['logout'] = "customer/login/logout";
$route['forgot-password'] = "customer/forgot_password";
$route['customer/check_session'] = "customer/Login/check_session";

// Currency Switcher routes (customer & vendor)
$route['switch-currency/(:num)']                  = 'customer/CurrencySwitcher/switchCurrency/$1';
$route['customer/switch-currency/(:num)']         = 'customer/CurrencySwitcher/switchCurrency/$1';
$route['vendor/currency-switcher/(:num)']         = 'vendor/CurrencySwitcher/switchCurrency/$1';

// NOTE: category/(.*) and product/(.*) wildcard routes handled above (lines 72 and 77).
// Do NOT redefine them here — Item_list.php is empty and overrides break routing.
$route['profile'] = "customer/profile";
$route['checkout'] = "/customer/checkout";
$route['contact'] = "/customer/contact";

 $route['wallet'] = 'customer/wallet/index';
 $route['wallet/transactions'] = 'customer/wallet/transactions';
 $route['wallet/ajax_save_bank'] = 'customer/wallet/ajax_save_bank';
 $route['wallet/ajax_list_banks'] = 'customer/wallet/ajax_list_banks';
 $route['wallet/ajax_delete_bank'] = 'customer/wallet/ajax_delete_bank';
 $route['wallet/ajax_add_money'] = 'customer/wallet/ajax_add_money';
 $route['wallet/ajax_withdraw'] = 'customer/wallet/ajax_withdraw';

// API v1 routes (isolated; does not affect existing web controllers)
$route['api/v1/auth/login']         = 'api/v1/Auth/login';
$route['api/v1/auth/register']       = 'api/v1/Auth/register';
$route['api/v1/auth/logout']         = 'api/v1/Auth/logout';
$route['api/v1/auth/refresh-token']  = 'api/v1/Auth/refresh_token';
$route['api/v1/auth/forgot-password'] = 'api/v1/Auth/forgot_password';
$route['api/v1/auth/reset-password']  = 'api/v1/Auth/reset_password';


$route['api/v1/categories'] = 'api/v1/Categories/index';
$route['api/v1/banners'] = 'api/v1/Banners/index';
$route['api/v1/homepage/banners'] = 'api/v1/homepage/Banners/index';
$route['api/v1/homepage/deal-of-the-day'] = 'api/v1/homepage/Deal_of_the_day/index';
$route['api/v1/homepage/shop-by-category'] = 'api/v1/homepage/Shop_by_category/index';
$route['api/v1/homepage/advertisements'] = 'api/v1/homepage/Advertisements/index';
$route['api/v1/homepage/best-sellers'] = 'api/v1/homepage/Best_sellers/index';
$route['api/v1/homepage/products'] = 'api/v1/homepage/Products/index';
$route['api/v1/homepage/subscription'] = 'api/v1/homepage/Subscription/index';
$route['api/v1/homepage/categories'] = 'api/v1/homepage/Categories/index';
$route['api/v1/homepage/smart-search'] = 'api/v1/homepage/Smart_search/index';

$route['api/v1/products'] = 'api/v1/Products/index';
$route['api/v1/products/add_variable'] = 'api/v1/Products/add_variable';
$route['api/v1/products/detail'] = 'api/v1/Products/detail';
$route['api/v1/products/by-category'] = 'api/v1/Products/by_category';
$route['api/v1/products/related_products'] = 'api/v1/Products/related_products';
$route['api/v1/products/(:any)'] = 'api/v1/Products/show/$1';
$route['api/v1/single_product'] = 'api/v1/Single_product/index';

// Currency API - NOTE: method is set_currency() not switch() — 'switch' is a PHP reserved keyword
$route['api/v1/currency/set-currency'] = 'api/v1/Currency/set_currency';
$route['api/v1/set-currency']          = 'api/v1/Currency/set_currency';
$route['api/v1/currency/change']       = 'api/v1/Currency/set_currency';
$route['api/v1/change-currency']       = 'api/v1/Currency/set_currency';
$route['api/v1/currency/switch'] = 'api/v1/Currency/set_currency';
$route['api/v1/switch-currency'] = 'api/v1/Currency/set_currency';

// Language API
$route['api/v1/language/set-language'] = 'api/v1/Language/set_language';
$route['api/v1/set-language']          = 'api/v1/Language/set_language';
$route['api/v1/language/change']       = 'api/v1/Language/set_language';
$route['api/v1/change-language']       = 'api/v1/Language/set_language';
$route['api/v1/language/switch']       = 'api/v1/Language/set_language';
$route['api/v1/switch-language']       = 'api/v1/Language/set_language';

// Cart APIs (Phase 3)
$route['api/v1/cart'] = 'api/v1/Cart/index';
$route['api/v1/cart/add'] = 'api/v1/Cart/add';
$route['api/v1/cart/update'] = 'api/v1/Cart/update';
$route['api/v1/cart/remove'] = 'api/v1/Cart/remove';
$route['api/v1/cart/apply-coupon']  = 'api/v1/Cart/apply_coupon';
$route['api/v1/cart/remove-coupon'] = 'api/v1/Cart/remove_coupon';
// Underscore aliases (required: translate_uri_dashes converts hyphens before route matching)
$route['api/v1/cart/apply_coupon']  = 'api/v1/Cart/apply_coupon';
$route['api/v1/cart/remove_coupon'] = 'api/v1/Cart/remove_coupon';


// Checkout APIs (Phase 4)
$route['api/v1/checkout'] = 'api/v1/Checkout/index';
$route['api/v1/checkout/step'] = 'api/v1/Checkout/step';
$route['api/v1/checkout/select-address'] = 'api/v1/Checkout/select_address';
$route['api/v1/checkout/shipping-methods'] = 'api/v1/Checkout/shipping_methods';
$route['api/v1/checkout/payment-methods'] = 'api/v1/Checkout/payment_methods';
$route['api/v1/checkout/place-order'] = 'api/v1/Checkout/place_order';
$route['api/v1/place-order'] = 'api/v1/Checkout/place_order_simple';
$route['api/v1/checkout/payment-verify'] = 'api/v1/Checkout/payment_verify';


// Orders APIs (Phase 5)
$route['api/v1/orders']        = 'api/v1/Orders/index';
$route['api/v1/orders/list']   = 'api/v1/Orders/list_orders'; // POST: {"filter":"active|completed|cancelled|all"}
$route['api/v1/orders/cancel'] = 'api/v1/Orders/cancel';
$route['api/v1/orders/detail'] = 'api/v1/Orders/detail';
$route['api/v1/orders/(:num)'] = 'api/v1/Orders/detail';


// Profile APIs
$route['api/v1/profile'] = 'api/v1/Profile/index';
$route['api/v1/profile/update'] = 'api/v1/Profile/update';
$route['api/v1/profile/change-password'] = 'api/v1/Profile/change_password';


// Address APIs
$route['api/v1/addresses'] = 'api/v1/Addresses/index';
$route['api/v1/addresses/add'] = 'api/v1/Addresses/create';
$route['api/v1/addresses/update'] = 'api/v1/Addresses/update';
$route['api/v1/addresses/update/(:num)'] = 'api/v1/Addresses/update/$1';
$route['api/v1/addresses/delete'] = 'api/v1/Addresses/delete';
$route['api/v1/addresses/delete/(:num)'] = 'api/v1/Addresses/delete/$1';
$route['api/v1/addresses/default'] = 'api/v1/Addresses/set_default';
$route['api/v1/addresses/default/(:num)'] = 'api/v1/Addresses/set_default/$1';


// Wishlist APIs
$route['api/v1/wishlist'] = 'api/v1/Wishlist/index';
$route['api/v1/wishlist/add'] = 'api/v1/Wishlist/add';
$route['api/v1/wishlist/remove'] = 'api/v1/Wishlist/remove';


// Product Enquiry APIs
$route['api/v1/product-enquiry/add'] = 'api/v1/products/enquiry_add';

// Vendor APIs (Phase 6)
// Vendor Auth APIs (Moved to VendorAuth)
$route['api/v1/vendor/login'] = 'api/v1/vendor/VendorAuth/login';
$route['api/v1/vendor/refresh-token'] = 'api/v1/vendor/VendorAuth/refresh_token';
$route['api/v1/vendor/logout'] = 'api/v1/vendor/VendorAuth/logout';
$route['api/v1/vendor/register'] = 'api/v1/vendor/VendorAuth/register';
$route['api/v1/vendor/auth/forgot-password'] = 'api/v1/vendor/VendorAuth/forgot_password';
$route['api/v1/vendor/auth/forgot_password'] = 'api/v1/vendor/VendorAuth/forgot_password';
$route['api/v1/vendor/auth/reset-password'] = 'api/v1/vendor/VendorAuth/reset_password';
$route['api/v1/vendor/auth/reset_password'] = 'api/v1/vendor/VendorAuth/reset_password';

// Underscore aliases — required because translate_uri_dashes=TRUE converts
// all dashes in the browser URI to underscores before route matching.
$route['api/v1/vendor/forgot_password'] = 'api/v1/vendor/VendorAuth/forgot_password';
$route['api/v1/vendor/reset_password']  = 'api/v1/vendor/VendorAuth/reset_password';
$route['api/v1/vendor/auth_logout']     = 'api/v1/vendor/VendorAuth/logout';

// Flat controller aliases (at api/v1/ level, same as Cart.php) — bypasses
// subdirectory routing failures on the live server.
$route['api/v1/vendor_forgot'] = 'api/v1/VendorForgot/index';

// Proven-working alias: maps API-style URL to vendor/Auth::forgot_json()
// Uses the vendor/ controller directory which resolves correctly on the live server.
$route['api/vendor/forgot'] = 'vendor/Auth/forgot_json';

// Browser-accessible flat aliases (avoids auth/ sub-path routing conflict)
$route['api/v1/vendor/forgot-password'] = 'api/v1/vendor/VendorAuth/forgot_password';
$route['api/v1/vendor/reset-password']  = 'api/v1/vendor/VendorAuth/reset_password';
$route['api/v1/vendor/auth-logout']     = 'api/v1/vendor/VendorAuth/logout';

// Vendor Currency API — persists preferred_currency_id to ec_vendor table
$route['api/v1/vendor/set-currency']    = 'api/v1/vendor/VendorCurrency/set_currency';
$route['api/v1/vendor/set_currency']    = 'api/v1/vendor/VendorCurrency/set_currency';
$route['api/v1/vendor/currency/set']    = 'api/v1/vendor/VendorCurrency/set_currency';
$route['api/v1/vendor/currency']        = 'api/v1/vendor/VendorCurrency/get_currency';
$route['api/v1/vendor/currency/get']    = 'api/v1/vendor/VendorCurrency/get_currency';


// Vendor Profile APIs (Moved to VendorProfile)
$route['api/v1/vendor/profile'] = 'api/v1/vendor/VendorProfile/get_profile';
$route['api/v1/vendor/profile/update'] = 'api/v1/vendor/VendorProfile/update_profile';
$route['api/v1/vendor/profile/update_profile'] = 'api/v1/vendor/VendorProfile/update_account_info';
$route['api/v1/vendor/profile/password'] = 'api/v1/vendor/VendorProfile/update_password';
$route['api/v1/vendor/profile/documents'] = 'api/v1/vendor/VendorProfile/reupload_documents';
$route['api/v1/vendor/profile/upload_photo'] = 'api/v1/vendor/VendorProfile/upload_vendor_photo';
$route['api/v1/vendor/profile/upload_banner'] = 'api/v1/vendor/VendorProfile/upload_vendor_banner';

$route['api/v1/vendor/dashboard'] = 'api/v1/vendor/Dashboard/index';
$route['api/v1/vendor/vendor/dashboard'] = 'api/v1/vendor/Dashboard/index';
$route['api/v1/vendor/Vendor/dashboard'] = 'api/v1/vendor/Dashboard/index';


$route['api/v1/vendor/products'] = 'api/v1/vendor/Products/index';
$route['api/v1/vendor/Products'] = 'api/v1/vendor/Products/index';
$route['api/v1/vendor/products/categories'] = 'api/v1/vendor/Products/categories';
$route['api/v1/vendor/Products/categories'] = 'api/v1/vendor/Products/categories';
$route['api/v1/vendor/products/brands'] = 'api/v1/vendor/Products/brands';
$route['api/v1/vendor/Products/brands'] = 'api/v1/vendor/Products/brands';
$route['api/v1/vendor/products/add'] = 'api/v1/vendor/Products/create';
$route['api/v1/vendor/Products/add'] = 'api/v1/vendor/Products/create';
$route['api/v1/vendor/products/update'] = 'api/v1/vendor/Products/update';
$route['api/v1/vendor/Products/update'] = 'api/v1/vendor/Products/update';
$route['api/v1/vendor/products/update/(:num)'] = 'api/v1/vendor/Products/update/$1';
$route['api/v1/vendor/Products/update/(:num)'] = 'api/v1/vendor/Products/update/$1';
$route['api/v1/vendor/products/(:num)'] = 'api/v1/vendor/Products/update/$1';
$route['api/v1/vendor/Products/(:num)'] = 'api/v1/vendor/Products/update/$1';
$route['api/v1/vendor/products/delete/(:num)'] = 'api/v1/vendor/Products/delete/$1';
$route['api/v1/vendor/Products/delete/(:num)'] = 'api/v1/vendor/Products/delete/$1';
$route['api/v1/vendor/products/attributes'] = 'api/v1/vendor/Products/attributes';
$route['api/v1/vendor/Products/attributes'] = 'api/v1/vendor/Products/attributes';
$route['api/v1/vendor/products/attribute_values'] = 'api/v1/vendor/Products/attribute_values';
$route['api/v1/vendor/Products/attribute_values'] = 'api/v1/vendor/Products/attribute_values';

$route['api/v1/vendor/orders'] = 'api/v1/vendor/Orders/index';
$route['api/v1/vendor/Orders'] = 'api/v1/vendor/Orders/index';
$route['api/v1/vendor/orders/(:num)'] = 'api/v1/vendor/Orders/show/$1';
$route['api/v1/vendor/Orders/(:num)'] = 'api/v1/vendor/Orders/show/$1';
$route['api/v1/vendor/orders/update-status'] = 'api/v1/vendor/Orders/update_status';
$route['api/v1/vendor/Orders/update-status'] = 'api/v1/vendor/Orders/update_status';

// Vendor Split Controller API mappings
// Earnings
$route['api/v1/vendor/earnings'] = 'api/v1/vendor/Earnings/index';
$route['api/v1/vendor/vendor/earnings'] = 'api/v1/vendor/Earnings/index';
$route['api/v1/vendor/Vendor/earnings'] = 'api/v1/vendor/Earnings/index';

// Returns
$route['api/v1/vendor/returns'] = 'api/v1/vendor/Returns/index';
$route['api/v1/vendor/vendor/returns'] = 'api/v1/vendor/Returns/index';
$route['api/v1/vendor/Vendor/returns'] = 'api/v1/vendor/Returns/index';
$route['api/v1/vendor/returns/details'] = 'api/v1/vendor/Returns/details';
$route['api/v1/vendor/vendor/return_details'] = 'api/v1/vendor/Returns/details';
$route['api/v1/vendor/Vendor/return_details'] = 'api/v1/vendor/Returns/details';

// Reviews
$route['api/v1/vendor/reviews'] = 'api/v1/vendor/Reviews/index';
$route['api/v1/vendor/vendor/reviews'] = 'api/v1/vendor/Reviews/index';
$route['api/v1/vendor/Vendor/reviews'] = 'api/v1/vendor/Reviews/index';

// Product Enquiries (Messagner)
$route['api/v1/vendor/product_enquiries'] = 'api/v1/vendor/Product_enquiries/index';
$route['api/v1/vendor/product_enquiries/response'] = 'api/v1/vendor/Product_enquiries/response';

// Order operations
// Clean URLs (No double vendor)
$route['api/v1/vendor/order_counts'] = 'api/v1/vendor/Order/order_counts';
$route['api/v1/vendor/order_list'] = 'api/v1/vendor/Order/order_list';
$route['api/v1/vendor/update_order_item_status'] = 'api/v1/vendor/Order/update_order_item_status';
$route['api/v1/vendor/cancel_order'] = 'api/v1/vendor/Order/cancel_order';

// JS Assets URLs
$route['api/v1/vendor/order/counts'] = 'api/v1/vendor/Order/order_counts';
$route['api/v1/vendor/order/list'] = 'api/v1/vendor/Order/order_list';
$route['api/v1/vendor/order/update-status'] = 'api/v1/vendor/Order/update_order_item_status';
$route['api/v1/vendor/order/cancel'] = 'api/v1/vendor/Order/cancel_order';
$route['api/v1/vendor/order/cancel-reasons'] = 'api/v1/vendor/Order/cancel_reasons';
$route['api/v1/vendor/Order/cancel-reasons'] = 'api/v1/vendor/Order/cancel_reasons';
$route['api/v1/vendor/order/cancel_reasons'] = 'api/v1/vendor/Order/cancel_reasons';
$route['api/v1/vendor/order/detail'] = 'api/v1/vendor/Order/order_detail';
$route['api/v1/vendor/Order/detail'] = 'api/v1/vendor/Order/order_detail';

// Legacy double-vendor URLs (fallback)
$route['api/v1/vendor/Vendor/order_counts'] = 'api/v1/vendor/Order/order_counts';
$route['api/v1/vendor/vendor/order_counts'] = 'api/v1/vendor/Order/order_counts';
$route['api/v1/vendor/Vendor/order_list'] = 'api/v1/vendor/Order/order_list';
$route['api/v1/vendor/vendor/order_list'] = 'api/v1/vendor/Order/order_list';
$route['api/v1/vendor/Vendor/update_order_item_status'] = 'api/v1/vendor/Order/update_order_item_status';
$route['api/v1/vendor/vendor/update_order_item_status'] = 'api/v1/vendor/Order/update_order_item_status';
$route['api/v1/vendor/Vendor/cancel_order'] = 'api/v1/vendor/Order/cancel_order';
$route['api/v1/vendor/vendor/cancel_order'] = 'api/v1/vendor/Order/cancel_order';


// Returns / Refund APIs (Phase 8)
$route['api/v1/returns'] = 'api/v1/Returns/index';
$route['api/v1/returns/request'] = 'api/v1/Returns/request';
$route['api/v1/returns/approve'] = 'api/v1/Returns/approve';
$route['api/v1/returns/reject'] = 'api/v1/Returns/reject';
$route['api/v1/returns/order-availability'] = 'api/v1/Returns/order_availability';


// Wallet APIs (both cases to handle CI case-sensitivity)
$route['api/v1/wallet']              = 'api/v1/Wallet/index';
$route['api/v1/wallet/transactions'] = 'api/v1/Wallet/transactions';
$route['api/v1/wallet/list_banks']   = 'api/v1/Wallet/list_banks';
$route['api/v1/wallet/save_bank']    = 'api/v1/Wallet/save_bank';
$route['api/v1/wallet/delete_bank']  = 'api/v1/Wallet/delete_bank';
$route['api/v1/wallet/add_money']    = 'api/v1/Wallet/add_money';
$route['api/v1/wallet/withdraw']     = 'api/v1/Wallet/withdraw';
$route['api/v1/Wallet']              = 'api/v1/Wallet/index';
$route['api/v1/Wallet/transactions'] = 'api/v1/Wallet/transactions';
$route['api/v1/Wallet/list_banks']   = 'api/v1/Wallet/list_banks';
$route['api/v1/Wallet/save_bank']    = 'api/v1/Wallet/save_bank';
$route['api/v1/Wallet/delete_bank']  = 'api/v1/Wallet/delete_bank';
$route['api/v1/Wallet/add_money']    = 'api/v1/Wallet/add_money';
$route['api/v1/Wallet/withdraw']     = 'api/v1/Wallet/withdraw';

$route['api/v1/category_list'] = 'api/v1/Category_list/index';
$route['api/v1/category-list'] = 'api/v1/Category_list/index';

// Defensive catch-all: ensures /api/v1/* routes to application/controllers/api/v1/*
// and avoids hitting legacy application/controllers/Api.php when explicit routes are absent.
$route['api/v1/(:any)/(:any)'] = 'api/v1/$1/$2';
$route['api/v1/(:any)'] = 'api/v1/$1/index';

// Vendor web routes (explicit)
$route['vendor/profile'] = 'vendor/admin/profile/0';
$route['vendor/profile/ajaxform'] = 'vendor/admin/ajaxform';
$route['vendor/profile/ajax_accountform'] = 'vendor/admin/ajax_accountform';
$route['vendor/profile/reupload_documents'] = 'vendor/admin/reupload_documents';
$route['vendor/profile/change_password'] = 'vendor/admin/change_password';

$route['vendor/dashboard'] = 'vendor/dashboard/index';
$route['vendor/order'] = 'vendor/order/index';
$route['vendor/order/ajax_order_list'] = 'vendor/order/ajax_order_list';
$route['vendor/order/ajax_order_counts'] = 'vendor/order/ajax_order_counts';
$route['vendor/order/ajax_order_status_update'] = 'vendor/order/ajax_order_status_update';

$route['vendor/product'] = 'vendor/product/index';
$route['vendor/product/index_ajax_post'] = 'vendor/product/index_ajax_post';
$route['vendor/product/export'] = 'vendor/product/export';
$route['vendor/product/save_product'] = 'vendor/product/add';
$route['vendor/product/update/(:num)'] = 'vendor/product/update/$1';
$route['vendor/product/delete/(:num)'] = 'vendor/product/delete/$1';
$route['vendor/product/ajax_update_product_status'] = 'vendor/product/ajax_update_product_status';
$route['vendor/product/checkall_status_change'] = 'vendor/product/checkall_status_change';
$route['vendor/product/delimg'] = 'vendor/product/delimg';
$route['vendor/product/delete_variation'] = 'vendor/product/delete_variation';

$route['admin/advertisement'] = 'admin/advertisement/index';

// Vendor Item routes (new product form - save_product5.php)
$route['vendor/item/create']              = 'vendor/item/create';
$route['vendor/item/edit/(:num)']         = 'vendor/item/edit/$1';
$route['vendor/item/save_product']        = 'vendor/item/save_product';
$route['vendor/item/delete_image']        = 'vendor/item/delete_image';
$route['vendor/item/delete_video']        = 'vendor/item/delete_video';
$route['vendor/item/ajax_brand_combo']    = 'vendor/item/ajax_brand_combo';
$route['vendor/product/ajax_brand_combo'] = 'vendor/item/ajax_brand_combo';

// Customer Review / Rating routes
$route['customer/rating']                         = 'customer/Rating/index';
$route['customer/rating/submit']                  = 'customer/Rating/submit';
$route['customer/rating/product_reviews/(:num)']  = 'customer/Rating/product_reviews/$1';
