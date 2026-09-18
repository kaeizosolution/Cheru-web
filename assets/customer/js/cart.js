/**
 * Cart Management JavaScript
 * Handles add to cart, remove from cart, and quantity updates
 */

$(document).ready(function() {
	var base_url = (typeof window.base_url !== 'undefined' && window.base_url) ? String(window.base_url) : '';
	var csrf_token_name = (typeof window.csrf_token_name !== 'undefined') ? window.csrf_token_name : (typeof csrf_token_name !== 'undefined' ? csrf_token_name : '');
	var csrf_hash = (typeof window.csrf_hash !== 'undefined') ? window.csrf_hash : (typeof csrf_hash !== 'undefined' ? csrf_hash : '');

    function formatMoneyJS(n) {
        if (typeof window.fmtMoney === 'function') {
            return window.fmtMoney(n);
        }
        var rate = parseFloat(window.currentCurrencyRate || window.currency_rate) || 1.0;
        var sym = window.currentCurrencySymbol || window.currency_symbol || '$';
        return sym + (parseFloat(n || 0) * rate).toFixed(2);
    }

    function cheruSyncCsrfFromApi(resp) {
        try {
            if (resp && resp.csrf_token_name && resp.csrf_hash) {
                window.csrf_token_name = resp.csrf_token_name;
                window.csrf_hash = resp.csrf_hash;
				csrf_token_name = resp.csrf_token_name;
				csrf_hash = resp.csrf_hash;
            }
        } catch (e) {}
    }

    // Prevent duplicate bindings if this script is included multiple times
    if (window.__cheru_cart_js_bound__) {
        return;
    }
    window.__cheru_cart_js_bound__ = true;

    if (typeof window.refreshHeaderAndSidebarCart !== 'function') {
        window.refreshHeaderAndSidebarCart = function() {
            if (typeof window.base_url === 'undefined') {
                return;
            }

            var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
            var forceApi = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__) || (typeof window.__cheru_cart_api_mode__ !== 'undefined' && window.__cheru_cart_api_mode__);
            if (token || forceApi) {
                $.ajax({
                    url: window.base_url + 'api/v1/cart',
                    type: 'GET',
                    dataType: 'json',
                    headers: token ? { 'Authorization': 'Bearer ' + token } : {},
                    success: function(resp) {
                        cheruSyncCsrfFromApi(resp);
                        if (!resp || (resp.STATUS !== 1 && resp.status !== 1) || (!resp.DATA && !resp.data)) {
                            return;
                        }
                        var apiData = resp.DATA || resp.data;
                        var items = apiData.items || [];
                        var summary = {
                            subtotal: apiData.cart_total || 0,
                            shipping: 0,
                            grand_total: apiData.final_total || apiData.cart_total || 0,
                            total_qty: (function(){
                                var q = 0;
                                for (var i=0;i<items.length;i++) q += parseInt(items[i].quantity || 0, 10) || 0;
                                return q;
                            })()
                        };

                        var symbol = (typeof window.currency_symbol !== 'undefined') ? window.currency_symbol : '$';
                        var uniqueCount = parseInt(items.length || 0, 10) || 0;
                        var totalQty = parseInt(summary.total_qty || 0, 10) || 0;
                        var grandTotal = parseFloat(apiData.final_total || apiData.cart_total || 0) || 0;
                        var subtotal = parseFloat(apiData.cart_total || 0) || 0;

                        $('[id="cart_count"]').text(uniqueCount);
                        $('[id="cart_qty_count"]').text(totalQty);
                        $('[id="cart_total"]').text(formatMoneyJS(grandTotal));
                        $('[id="side_total"]').text(formatMoneyJS(grandTotal));

                        if ($('.cart-hidden-sbar').length) {
                            var $list = $('.cart-hidden-sbar').find('.cart-list-vertical');
                            if ($list.length) {
                                if (!items.length) {
                                    $list.html('<li class="cart-item-row"><div class="content" style="padding:10px 0;">Your cart is empty.</div></li>');
                                } else {
                                    var html = '';
                                    for (var i2 = 0; i2 < items.length; i2++) {
                                        var it = items[i2] || {};
                                        var name = it.product_name || '';
                                        var img = it.image_url || '';
                                        var qty = parseInt(it.quantity || 1, 10) || 1;
                                        var price = parseFloat(it.price || 0) || 0;
                                        var pid = it.product_id || 0;
                                        var vid = it.variation_id || 0;
                                        var attr = it.attribute_item_id || '';

                                        html += '<li class="cart-item-row">'
                                            + '<div class="thumb">' + (img ? ('<img src="' + img + '" alt="">') : '') + '</div>'
                                            + '<div class="details">'
                                            +   '<div class="title">' + name + '</div>'
                                            +   '<div class="meta">' + formatMoneyJS(price * qty) + '</div>'
                                            +   '<div class="qty">'
                                            +     '<button class="quantity_update minus" data-product_id="' + pid + '" data-variation_id="' + vid + '" data-attribute_item_id=\'' + JSON.stringify(attr) + '\'>-</button>'
                                            +     '<input class="qty_update" type="text" value="' + qty + '" readonly>'
                                            +     '<button class="quantity_update plus" data-product_id="' + pid + '" data-variation_id="' + vid + '" data-attribute_item_id=\'' + JSON.stringify(attr) + '\'>+</button>'
                                            +   '</div>'
                                            + '</div>'
                                            + '<button class="btn-delete" data-product_id="' + pid + '" data-variation_id="' + vid + '" data-attribute_item_id=\'' + JSON.stringify(attr) + '\'>×</button>'
                                            + '</li>';
                                    }
                                    $list.html(html);
                                }
                            }
                        }

                        var $footer = $('#cartSidebarFooter');
                        if ($footer.length) {
                            if (items.length) {
                                $footer.show();
                                var freeShippingThreshold = 9800;
                                var progress = subtotal >= freeShippingThreshold ? 100 : Math.min(100, (subtotal / freeShippingThreshold) * 100);
                                $('#cartProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                                var shippingMsg = subtotal < freeShippingThreshold
                                    ? 'Buy ' + formatMoneyJS(freeShippingThreshold - subtotal) + ' more for FREE Shipping'
                                    : 'You have FREE Shipping!';
                                $('#cartShippingMsg').text(shippingMsg);
                            } else {
                                $footer.hide();
                            }
                        }

                        try {
                            $(document).trigger('cheru:cart-updated');
                        } catch (e) {}
                    }
                });
                return;
            }

            var postData = (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined')
                ? { [window.csrf_token_name]: window.csrf_hash }
                : {};

            $.ajax({
                url: window.base_url + 'customer/cart/ajax_cart_payload',
                type: 'POST',
                dataType: 'json',
                data: postData,
                success: function(resp) {
                    if (!resp || (resp.STATUS !== 1 && resp.status !== 1) || (!resp.DATA && !resp.data)) {
                        return;
                    }
                    var data = resp.DATA || resp.data;
                    var summary = data.cart_summary || {};
                    var symbol = (typeof window.currency_symbol !== 'undefined') ? window.currency_symbol : '$';
                    var uniqueCount = parseInt(data.cart_count || 0, 10) || 0;
                    var totalQty = parseInt(data.cart_qty || summary.total_qty || 0, 10) || 0;
                    var grandTotal = parseFloat(data.cart_total || summary.grand_total || 0) || 0;
                    var subtotal = parseFloat(summary.subtotal || 0) || 0;

                    $('[id="cart_count"]').text(uniqueCount);
                    $('[id="cart_qty_count"]').text(totalQty);
                    $('[id="cart_total"]').text(formatMoneyJS(grandTotal));
                    $('[id="side_total"]').text(formatMoneyJS(grandTotal));

                    var items = data.cart_items || [];

                    // Render sidebar cart items if sidebar exists on this page
                    if ($('.cart-hidden-sbar').length) {
                        var $list = $('.cart-hidden-sbar').find('.cart-list-vertical');
                        if ($list.length) {
                            if (!items.length) {
                                $list.html('<li class="cart-item-row"><div class="content" style="padding:10px 0;">Your cart is empty.</div></li>');
                            } else {
                                var html = '';
                                for (var i = 0; i < items.length; i++) {
                                    var it = items[i] || {};
                                    var name = it.name || it.product_name || '';
                                    var img = it.thumbnail || it.image || it.product_image || '';
                                    var qty = parseInt(it.qty || it.quantity || 1, 10) || 1;
                                    var price = parseFloat(it.price || it.unit_price || 0) || 0;
                                    var pid = it.product_id || it.id || 0;
                                    var vid = it.variation_id || 0;
                                    var attr = it.attribute_item_id || it.attribute_item || '';

                                    html += '<li class="cart-item-row">'
                                        + '<div class="thumb">' + (img ? ('<img src="' + img + '" alt="">') : '') + '</div>'
                                        + '<div class="details">'
                                        +   '<div class="title">' + name + '</div>'
                                        +   '<div class="meta">' + formatMoneyJS(price * qty) + '</div>'
                                        +   '<div class="qty">'
                                        +     '<button class="quantity_update minus" data-product_id="' + pid + '" data-variation_id="' + vid + '" data-attribute_item_id="' + attr + '">-</button>'
                                        +     '<input class="qty_update" type="text" value="' + qty + '" readonly>'
                                        +     '<button class="quantity_update plus" data-product_id="' + pid + '" data-variation_id="' + vid + '" data-attribute_item_id="' + attr + '">+</button>'
                                        +   '</div>'
                                        + '</div>'
                                        + '<button class="btn-delete" data-product_id="' + pid + '" data-variation_id="' + vid + '" data-attribute_item_id="' + attr + '">×</button>'
                                        + '</li>';
                                }
                                $list.html(html);
                            }
                        }
                    }
                    var $footer = $('#cartSidebarFooter');
                    if ($footer.length) {
                        if (items.length) {
                            $footer.show();
                            var freeShippingThreshold = 9800;
                            var progress = subtotal >= freeShippingThreshold ? 100 : Math.min(100, (subtotal / freeShippingThreshold) * 100);
                            $('#cartProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                            var shippingMsg = subtotal < freeShippingThreshold
                                ? 'Buy ' + formatMoneyJS(freeShippingThreshold - subtotal) + ' more for FREE Shipping'
                                : 'You have FREE Shipping!';
                            $('#cartShippingMsg').text(shippingMsg);
                        } else {
                            $footer.hide();
                        }
                    }

                    try {
                        $(document).trigger('cheru:cart-updated');
                    } catch (e) {}
                }
            });
            return;
        };
    }

// Ensure cart sidebar open/close works on pages that don't define it in header
if (typeof window.openCartSidebar !== 'function') {
    window.openCartSidebar = function() {
        $('.cart-hidden-sbar').addClass('active');
        $('.cart-overlay').addClass('active');
        $('body').addClass('cart-open');
    };
}
if (typeof window.closeCartSidebar !== 'function') {
    window.closeCartSidebar = function() {
        $('.cart-hidden-sbar').removeClass('active');
        $('.cart-overlay').removeClass('active');
        $('body').removeClass('cart-open');
    };
}

// Bind sidebar open/close handlers once
if (!window.__cheru_cart_sidebar_click_bound__) {
    $(document).off('click.cheruCartSidebar', '.cart-filter-btn').on('click.cheruCartSidebar', '.cart-filter-btn', function(e){
        e.preventDefault();
        if (typeof window.refreshHeaderAndSidebarCart === 'function') {
            window.refreshHeaderAndSidebarCart();
        }
        if (typeof window.openCartSidebar === 'function') {
            window.openCartSidebar();
        }
    });
}
$(document).off('click.cheruCartSidebarClose', '.sidebar-close-icon, .cart-overlay').on('click.cheruCartSidebarClose', '.sidebar-close-icon, .cart-overlay', function(){
    if (typeof window.closeCartSidebar === 'function') {
        window.closeCartSidebar();
    }
});

    // Sidebar: remove item
    $(document).off('click.cheruCartSidebarRemove', '.cart-hidden-sbar .btn-delete').on('click.cheruCartSidebarRemove', '.cart-hidden-sbar .btn-delete', function(e){
        e.preventDefault();
        if (typeof window.base_url === 'undefined') {
            return;
        }
        var $btn = $(this);

        var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
        var forceApi = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__) || (typeof window.__cheru_cart_api_mode__ !== 'undefined' && window.__cheru_cart_api_mode__);
        if (token || forceApi) {
            var payload = {
                product_id: $btn.data('product_id'),
                variation_id: $btn.data('variation_id') || 0,
                attribute_item: $btn.data('attribute_item_id') || ''
            };
            if (!token && typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
                payload[window.csrf_token_name] = window.csrf_hash;
            }
            $.ajax({
                url: window.base_url + 'api/v1/cart/remove',
                type: 'POST',
                dataType: 'json',
                headers: token ? { 'Authorization': 'Bearer ' + token } : {},
                data: payload,
                success: function(resp){
                    cheruSyncCsrfFromApi(resp);
                    if (typeof window.refreshHeaderAndSidebarCart === 'function') {
                        window.refreshHeaderAndSidebarCart();
                    }
                    if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                        window.refreshCartDetail();
                    }

                    try {
                        $(document).trigger('cheru:cart-updated');
                    } catch (e) {}
                }
            });
            return;
        }

        if (typeof window.csrf_token_name === 'undefined' || typeof window.csrf_hash === 'undefined') {
            return;
        }

        $.ajax({
            url: window.base_url + 'customer/cart/ajax_delete_to_cart',
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: $btn.data('product_id'),
                variation_id: $btn.data('variation_id') || 0,
                attribute_item: $btn.data('attribute_item_id') || '',
                [window.csrf_token_name]: window.csrf_hash
            },
            success: function(){
                if (typeof window.refreshHeaderAndSidebarCart === 'function') {
                    window.refreshHeaderAndSidebarCart();
                }
                if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                    window.refreshCartDetail();
                }

                try {
                    $(document).trigger('cheru:cart-updated');
                } catch (e) {}
            }
        });
    });

    // Sidebar: qty update
    $(document).off('click.cheruCartSidebarQty', '.cart-hidden-sbar .quantity_update').on('click.cheruCartSidebarQty', '.cart-hidden-sbar .quantity_update', function(e){
        e.preventDefault();
        if (typeof window.base_url === 'undefined') {
            return;
        }
        var $btn = $(this);
        var action = $btn.hasClass('plus') ? 'increase' : 'decrease';

        var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
        var forceApi = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__) || (typeof window.__cheru_cart_api_mode__ !== 'undefined' && window.__cheru_cart_api_mode__);
        if (token || forceApi) {
            var pid = $btn.data('product_id');
            var vid = $btn.data('variation_id') || 0;
            var attr = $btn.data('attribute_item_id') || '';
            var $input = $btn.closest('.quantity-block, .qty').find('input.qty_update').first();
            if (!$input.length) {
                $input = $btn.siblings('input.qty_update').first();
            }
            var currentQty = parseInt($input.val(), 10) || 0;
            var nextQty = action === 'increase' ? (currentQty + 1) : Math.max(1, currentQty - 1);

            var payload = {
                product_id: parseInt(pid, 10) || 0,
                variation_id: parseInt(vid, 10) || 0,
                attribute_item: attr || '',
                quantity: nextQty
            };
            if (!token && typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
                payload[window.csrf_token_name] = window.csrf_hash;
            }
            $.ajax({
                url: window.base_url + 'api/v1/cart/update',
                type: 'POST',
                dataType: 'json',
                headers: token ? { 'Authorization': 'Bearer ' + token } : {},
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify(payload),
                success: function(resp){
                    cheruSyncCsrfFromApi(resp);
                    if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                        window.refreshCartDetail();
                    }
                    if (typeof window.refreshHeaderAndSidebarCart === 'function') {
                        window.refreshHeaderAndSidebarCart();
                    }

                    try {
                        $(document).trigger('cheru:cart-updated');
                    } catch (e) {}
                }
            });
            return;
        }

        if (typeof window.csrf_token_name === 'undefined' || typeof window.csrf_hash === 'undefined') {
            return;
        }
        $.ajax({
            url: window.base_url + 'customer/cart/ajax_update_quantity',
            type: 'POST',
            dataType: 'json',
            data: {
                product_id: $btn.data('product_id'),
                variation_id: $btn.data('variation_id') || 0,
                attribute_item: $btn.data('attribute_item_id') || '',
                action: action,
                [window.csrf_token_name]: window.csrf_hash
            },
            success: function(){
                if (typeof window.refreshHeaderAndSidebarCart === 'function') {
                    window.refreshHeaderAndSidebarCart();
                }
                if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                    window.refreshCartDetail();
                }
            }
        });
    });

    if (typeof window.addToCart !== 'function') {
        window.addToCart = function(p_id) {
            var pid = parseInt(p_id, 10);
            if (!pid) return;

            if (typeof window.base_url === 'undefined' || typeof window.csrf_token_name === 'undefined' || typeof window.csrf_hash === 'undefined') {
                return;
            }

            var postData = {
                product_id: pid,
                quantity: 1,
                action: 'add'
            };
            postData[window.csrf_token_name] = window.csrf_hash;

            var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
            var forceApi = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__);
            if (token || forceApi) {
                $.ajax({
                    type: 'POST',
                    url: window.base_url + 'api/v1/cart/add',
                    data: postData,
                    dataType: 'json',
                    headers: token ? { 'Authorization': 'Bearer ' + token } : {},
                    success: function(resp) {
                        cheruSyncCsrfFromApi(resp);
                        if (resp && (resp.status == 1 || resp.STATUS == 1)) {
                            if (typeof window.openCartSidebar === 'function') {
                                window.openCartSidebar();
                            }
                            if (typeof window.refreshHeaderAndSidebarCart === 'function') {
                                window.refreshHeaderAndSidebarCart();
                            }
                            if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                                window.refreshCartDetail();
                            }
                        }
                    }
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: window.base_url + 'customer/cart/ajax_add_to_cart',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    if (response && response.status == 1) {
                        if (typeof window.openCartSidebar === 'function') {
                            window.openCartSidebar();
                        }
                        if (typeof window.refreshHeaderAndSidebarCart === 'function') {
                            window.refreshHeaderAndSidebarCart();
                        }
                        if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                            window.refreshCartDetail();
                        }
                        if (typeof window.updateCartHeader === 'function') {
                            window.updateCartHeader(response.cart_count, response.total_price);
                        }
                    }
                }
            });
        };
    }
    
    // Add to Cart functionality
    // NOTE: Variation modal flow is handled by assets/customer/js/add_to_cart.js.
    // This handler must ignore modal-trigger buttons to avoid double requests.
    $(document).off('click.cheruCart', '.add-to-cart-btn').on('click.cheruCart', '.add-to-cart-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
		// If this button is meant to open the variation modal, do not add directly from here.
		if ($btn.is('[data-variation_modal="1"]') || $btn.hasClass('variation-add-btn')) {
			return;
		}
		// If the unified variation flow is available, let add_to_cart.js decide (modal vs direct).
		if (typeof window.openModalForProduct === 'function') {
			return;
		}
		if (typeof window.base_url === 'undefined' || typeof window.csrf_token_name === 'undefined' || typeof window.csrf_hash === 'undefined') {
			return;
		}
        var productId = $btn.data('product_id');
        var vendorId = $btn.data('vendor_id') || 0;
        var quantity = $btn.data('quantity') || 1;
        var attributeItem = $btn.data('attribute_item');
        if (typeof attributeItem === 'undefined' || attributeItem === null || attributeItem === '') {
            attributeItem = $btn.data('attribute_item_id');
        }
        if (typeof attributeItem === 'string') {
            var sAttr = attributeItem.trim();
            if (sAttr && (sAttr.charAt(0) === '[' || sAttr.charAt(0) === '{')) {
                try { attributeItem = JSON.parse(sAttr); } catch(e) {}
            }
        }
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');
        
        var token2 = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
        var forceApi2 = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__);
        var postData2 = {
            product_id: productId,
            vendor_id: vendorId,
            quantity: quantity,
            attribute_item: attributeItem,
            action: 'add'
        };
        if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
            postData2[window.csrf_token_name] = window.csrf_hash;
        }

        if (token2 || forceApi2) {
            $.ajax({
                url: window.base_url + 'api/v1/cart/add',
                type: 'POST',
                data: postData2,
                dataType: 'json',
                headers: token2 ? { 'Authorization': 'Bearer ' + token2 } : {},
                success: function(resp) {
                    cheruSyncCsrfFromApi(resp);
                    if (resp && (resp.status == 1 || resp.STATUS == 1)) {
                        if (typeof refreshHeaderAndSidebarCart === 'function') {
                            refreshHeaderAndSidebarCart();
                        }
                        showNotification((resp && (resp.message || resp.MSG || resp.msg)) ? (resp.message || resp.MSG || resp.msg) : 'Item added to cart successfully!', 'success');
                        $btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fa fa-check"></i> Added');
                        setTimeout(function() {
                            $btn.prop('disabled', false).removeClass('btn-success').addClass('btn-primary').html('Add to Cart');
                        }, 2000);
                    } else {
                        showNotification((resp && (resp.message || resp.MSG || resp.msg)) ? (resp.message || resp.MSG || resp.msg) : 'Failed to add item to cart', 'error');
                        $btn.prop('disabled', false).html('Add to Cart');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Cart Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                    $btn.prop('disabled', false).html('Add to Cart');
                }
            });
            return;
        }

        $.ajax({
            url: window.base_url + 'customer/cart/ajax_add_to_cart',
            type: 'POST',
            data: postData2,
            dataType: 'json',
            success: function(response) {
                if (response && response.status == 1) {
                    updateCartHeader(response.cart_count, response.total_price);
                    if (typeof response.cart_qty !== 'undefined') {
                        $('#cart_qty_count').text(response.cart_qty || 0);
                    }

                    if (typeof refreshHeaderAndSidebarCart === 'function') {
                        refreshHeaderAndSidebarCart();
                    }

                    try {
                        $(document).trigger('cheru:cart-updated');
                    } catch (e) {}
                    
                    showNotification(response.message || 'Item added to cart successfully!', 'success');
                    
                    $btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fa fa-check"></i> Added');
                    
                    setTimeout(function() {
                        $btn.prop('disabled', false).removeClass('btn-success').addClass('btn-primary').html('Add to Cart');
                    }, 2000);
                } else {
                    showNotification(response.message || 'Failed to add item to cart', 'error');
                    $btn.prop('disabled', false).html('Add to Cart');
                }
            },
            error: function(xhr, status, error) {
                console.error('Cart Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                $btn.prop('disabled', false).html('Add to Cart');
            }
        });
    });
    
    // Remove from Cart functionality
    $(document).off('click.cheruCart', '.btn-delete').on('click.cheruCart', '.btn-delete', function(e) {
        // Cart sidebar uses its own handlers in header.php.
        // IMPORTANT: Do not call preventDefault here, otherwise we can interfere with the sidebar handler.
        if ($(e.target).closest('.cart-hidden-sbar').length) {
            return;
        }

        e.preventDefault();
        
        var $btn = $(this);
        var productId = $btn.data('product_id');
        var variationId = $btn.data('variation_id') || 0;
        var attributeItemId = $btn.data('attribute_item_id') || '';

        var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
        var forceApi = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__) || (typeof window.__cheru_cart_api_mode__ !== 'undefined' && window.__cheru_cart_api_mode__);
        if (token || forceApi) {
            var payload = {
                product_id: productId,
                variation_id: variationId,
                attribute_item: attributeItemId
            };
            if (!token && typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
                payload[window.csrf_token_name] = window.csrf_hash;
            }
            $.ajax({
                url: base_url + 'api/v1/cart/remove',
                type: 'POST',
                dataType: 'json',
                headers: token ? { 'Authorization': 'Bearer ' + token } : {},
                data: payload,
                success: function(response) {
                    cheruSyncCsrfFromApi(response);
                    if (response && (response.status == 1 || response.STATUS == 1)) {
                        if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                            window.refreshCartDetail();
                        }
                        if (typeof refreshHeaderAndSidebarCart === 'function') {
                            refreshHeaderAndSidebarCart();
                        }

                        try {
                            $(document).trigger('cheru:cart-updated');
                        } catch (e) {}
                    } else {
                        showNotification((response && (response.message || response.msg)) ? (response.message || response.msg) : 'Failed to remove item from cart', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Cart Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
            return;
        }
        
        $.ajax({
            url: base_url + 'customer/cart/ajax_delete_to_cart',
            type: 'POST',
            data: {
                product_id: productId,
                variation_id: variationId,
                attribute_item: attributeItemId,
                [csrf_token_name]: csrf_hash
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.status == 1) {
                    updateCartHeader(response.cart_count, response.total_price);
                    if (typeof response.cart_qty !== 'undefined') {
                        $('#cart_qty_count').text(response.cart_qty || 0);
                    }

                    // Remove immediately from DOM (table row on cart_detail page, list item on sidebar-like renders)
                    var $rowEl = $btn.closest('tr');
                    if (!$rowEl.length) {
                        $rowEl = $btn.closest('.cart-item-row');
                    }
                    if ($rowEl.length) {
                        $rowEl.fadeOut(200, function(){
                            $(this).remove();
                        });
                    }
                    
                    // FULL SYNC: Refresh sidebar cart to keep in sync
                    if (typeof refreshHeaderAndSidebarCart === 'function') {
                        refreshHeaderAndSidebarCart();
                    }

                    try {
                        $(document).trigger('cheru:cart-updated');
                    } catch (e) {}

                    // Cart detail page: refresh table + totals without reloading
                    if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                        window.refreshCartDetail();
                    } else {
                        updateCartSummary();
                    }
                    
                    showNotification(response.message || 'Item removed from cart successfully!', 'success');
                } else {
                    showNotification(response.message || 'Failed to remove item from cart', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Cart Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    });

    // Quantity Update functionality (Increase/Decrease)
    $(document).off('click.cheruCart', '.quantity_update').on('click.cheruCart', '.quantity_update', function(e) {
        e.preventDefault();

        // Cart sidebar uses its own handlers. Do NOT stop propagation here,
        // otherwise the sidebar handler will never run and no API call happens.
        if ($(e.target).closest('.cart-hidden-sbar').length) {
            return;
        }

        // Non-sidebar: prevent duplicate handlers on the same element.
        e.stopImmediatePropagation();

        var $btn = $(this);
        var action = $btn.hasClass('plus') ? 'increase' : ($btn.hasClass('minus') ? 'decrease' : $btn.data('id'));

        // Fallback for action if classes are not used (for cart_detail.php compatibility)
        if (!action && $btn.data('id')) {
            action = $btn.data('id') === 'plus' ? 'increase' : 'decrease';
        }

        var $input = $btn.closest('.quantity-block').find('input.qty_update').first();
        if (!$input.length) {
            $input = $btn.siblings('input.qty_update').first();
        }
        var productId = $btn.data('product_id') || $input.data('product_id');

        var variationId = $btn.data('variation_id');
        if (typeof variationId === 'undefined' || variationId === null || variationId === '') {
            variationId = $input.data('variation_id') || 0;
        }
        variationId = parseInt(variationId, 10) || 0;

        var attributeItemId = $btn.data('attribute_item_id') || $input.data('attribute_item_id') || '';

        if (!productId) {
            console.error('Product ID not found');
            return false;
        }

        // Show loading state
        $btn.prop('disabled', true);

        var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
        var forceApi = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__) || (typeof window.__cheru_cart_api_mode__ !== 'undefined' && window.__cheru_cart_api_mode__);
        if (token || forceApi) {
            var currentQty = parseInt($input.val(), 10) || 1;
            var nextQty = action === 'increase' ? (currentQty + 1) : Math.max(1, currentQty - 1);

            var payload = {
                product_id: parseInt(productId, 10) || 0,
                variation_id: parseInt(variationId, 10) || 0,
                attribute_item: attributeItemId || '',
                quantity: nextQty
            };

            // Session API mode (no Bearer): require CSRF
            if (!token && typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
                payload[window.csrf_token_name] = window.csrf_hash;
            }

            $.ajax({
                url: base_url + 'api/v1/cart/update',
                type: 'POST',
                dataType: 'json',
                headers: token ? { 'Authorization': 'Bearer ' + token } : {},
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify(payload),
                success: function(response) {
                    cheruSyncCsrfFromApi(response);
                    $btn.prop('disabled', false);
                    if (response && (response.status == 1 || response.STATUS == 1)) {
                        if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                            window.refreshCartDetail();
                        }
                        if (typeof refreshHeaderAndSidebarCart === 'function') {
                            refreshHeaderAndSidebarCart();
                        }
                    } else {
                        showNotification((response && (response.message || response.msg)) ? (response.message || response.msg) : 'Failed to update quantity', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false);
                    console.error('Cart Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
            return;
        }

        $.ajax({
            url: base_url + 'customer/cart/ajax_update_quantity',
            type: 'POST',
            data: {
                product_id: productId,
                variation_id: variationId,
                attribute_item: attributeItemId,
                current_quantity: (parseInt($input.val(), 10) || 1),
                action: action,
                [csrf_token_name]: csrf_hash
            },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false);

                if (response.status == 1) {
                    // Update quantity input
                    $input.val(response.new_quantity);

                    // Update item total display
                    var $row = $btn.closest('tr');
                    if ($row.length) {
                        var unitPrice = parseFloat($row.find('.item-price').data('unit-price')) || 0;
                        var newTotal = unitPrice * response.new_quantity;
                        $row.find('.item-price').text(formatMoneyJS(newTotal)).data('total', newTotal);
                        $row.find('.unit-price').text(unitPrice);
                    }

                    updateCartHeader(response.cart_count, response.total_price);
                    if (typeof response.cart_qty !== 'undefined') {
                        $('#cart_qty_count').text(response.cart_qty || 0);
                    }

                    // Update cart summary
                    updateCartSummary();

                    // FULL SYNC: Refresh sidebar cart to keep in sync
                    if (typeof refreshHeaderAndSidebarCart === 'function') {
                        refreshHeaderAndSidebarCart();
                    }
                } else {
                    showNotification(response.message || 'Failed to update quantity', 'error');
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false);
                console.error('Cart Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    });
    
    // Direct quantity input change
    $(document).off('change.cheruCart', '.qty_update').on('change.cheruCart', '.qty_update', function(e) {
        // Sidebar inputs are controlled elsewhere
        if ($(e.target).closest('.cart-hidden-sbar').length) {
            return;
        }

        var $input = $(this);
        var newQuantity = parseInt($input.val(), 10) || 1;
        var productId = $input.data('product_id');
        var variationId = parseInt($input.data('variation_id') || 0, 10) || 0;
        var attributeItemId = $input.data('attribute_item_id') || '';

        if (newQuantity < 1) {
            $input.val(1);
            return;
        }

        if (!productId) {
            console.error('Product ID not found');
            return;
        }

        var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
        var forceApi = (typeof window.__cheru_cart_use_api__ !== 'undefined' && window.__cheru_cart_use_api__) || (typeof window.__cheru_cart_api_mode__ !== 'undefined' && window.__cheru_cart_api_mode__);

        if (token || forceApi) {
            var payload = {
                product_id: parseInt(productId, 10) || 0,
                variation_id: parseInt(variationId, 10) || 0,
                attribute_item: attributeItemId || '',
                quantity: newQuantity
            };
            if (!token && typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
                payload[window.csrf_token_name] = window.csrf_hash;
            }

            $.ajax({
                url: base_url + 'api/v1/cart/update',
                type: 'POST',
                dataType: 'json',
                headers: token ? { 'Authorization': 'Bearer ' + token } : {},
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify(payload),
                success: function(response) {
                    cheruSyncCsrfFromApi(response);
                    if (response && (response.status == 1 || response.STATUS == 1)) {
                        updateCartHeader(response.cart_count, response.total_price);
                        if (typeof response.cart_qty !== 'undefined') {
                            $('#cart_qty_count').text(response.cart_qty || 0);
                        }

                        var $rowEl = $input.closest('tr');
                        if (!$rowEl.length) {
                            $rowEl = $input.closest('.cart-item-row');
                        }
                        updateItemTotal($rowEl, response.new_quantity);
                        updateCartSummary();

                        if ($('.shopping_cart_table').length > 0 && typeof window.refreshCartDetail === 'function') {
                            window.refreshCartDetail();
                        } else if (typeof refreshHeaderAndSidebarCart === 'function') {
                            refreshHeaderAndSidebarCart();
                        }

                        showNotification(response.message || 'Cart updated successfully!', 'success');
                    } else {
                        showNotification((response && (response.message || response.msg)) ? (response.message || response.msg) : 'Failed to update quantity', 'error');
                        $input.val($input.data('original_quantity') || 1);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Cart Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
            return;
        }
    });

});

/**
 * Update cart header with new count and total
 */
function updateCartHeader(cartCount, cartTotal) {
    // IMPORTANT: Some layouts render multiple headers (e.g. header.php + header_inner.php)
    // and may contain duplicate ids. Update all matching nodes.
    $('[id="cart_count"]').text(cartCount || 0);
    $('[id="cart_total"]').text(formatMoneyJS(cartTotal));
}

/**
 * Update individual item total price
 */
function updateItemTotal($itemRow, quantity) {
    var $priceSpan = $itemRow.find('.item-price');
    var $totalSpan = $itemRow.find('.item-total');
    var $displayPrice = $itemRow.find('.fw600 .item-price, .text-thm .item-price');
    
    var price = 0;
    var symbol = typeof currency_symbol !== 'undefined' ? currency_symbol : '₹';
    
    if ($priceSpan.length > 0) {
        // Use data-unit-price for unit price, fallback to data-price for backward compatibility
        price = parseFloat($priceSpan.data('unit-price')) || parseFloat($priceSpan.data('price')) || 0;
    } else if ($itemRow.find('.fw600').length > 0) {
        var regularPriceText = $itemRow.find('td').eq(1).text();
        price = parseFloat(regularPriceText.replace(/[^0-9.]/g, '')) || 0;
    }
    
    var total = price * quantity;
    
    if ($priceSpan.length > 0) {
        $priceSpan.text(formatMoneyJS(total)).data('total', total);
    }
    if ($totalSpan.length > 0) {
        $totalSpan.text(total.toFixed(2));
    }
}

/**
 * Update cart summary section - matches Cart_model calculation logic
 * Free shipping at 9800+, otherwise 50 shipping fee
 */
function updateCartSummary() {
    var newSubtotal = 0;
    var totalQty = 0;  // Total quantity (sum of all items)
    var productCount = 0;  // Count of unique products
    var symbol = typeof currency_symbol !== 'undefined' ? currency_symbol : '₹';
    var freeShippingThreshold = 9800;
    var shippingFee = 50;
    
    // IMPORTANT: On cart_detail page, both the cart table AND the sidebar items exist in DOM.
    // If we sum both, it doubles the quantity and makes cart_count look wrong.
    // Prefer cart_detail table if present, otherwise use sidebar list.
    var $calcItems = ($('.table_body tr').length > 0) ? $('.table_body tr') : $('.cart-item-row');

    // Calculate from selected items
    $calcItems.each(function() {
        var $item = $(this);
        // Skip if this is the empty cart row
        if ($item.find('td[colspan]').length > 0) return;
        
        var qty = parseInt($item.find('.qty_update').val()) || 0;
        var price = 0;
        
        // Get unit price from data attribute
        var $priceSpan = $item.find('.item-price');
        if ($priceSpan.length > 0) {
            // Get unit price from data-unit-price attribute
            price = parseFloat($priceSpan.data('unit-price')) || 0;
        } else {
            // Fallback: parse from regular price column
            var regularPriceText = $item.find('td').eq(1).text();
            price = parseFloat(regularPriceText.replace(/[^0-9.]/g, '')) || 0;
        }
        
        newSubtotal += (price * qty);
        totalQty += qty;
        productCount++;
    });

    // Match Cart_model calculation exactly: shipping = (subtotal <= 0) ? 0 : ((subtotal >= 9800) ? 0 : 50)
    var shipping = (newSubtotal <= 0) ? 0 : ((newSubtotal >= freeShippingThreshold) ? 0 : shippingFee);
    var grandTotal = newSubtotal + shipping;
    
    // Update visible total quantity without overwriting unique count badge
    if ($('#cart_qty_count').length > 0) {
        $('#cart_qty_count').text(totalQty);
    }
    $('#cart_total, #side_total').text(formatMoneyJS(grandTotal));
    
    // Update cart_detail page summary
    $('.order_sidebar_widget .subtitle span.float-end').eq(0).text(formatMoneyJS(newSubtotal));
    $('.order_sidebar_widget .subtitle span.float-end').eq(1).text(shipping > 0 ? formatMoneyJS(shipping) : 'Free');
    $('.order_sidebar_widget .totals span.float-end').text(formatMoneyJS(grandTotal));
    
    // Update sidebar shipping message
    var $shippingMsg = $('#cartShippingMsg');
    if ($shippingMsg.length > 0) {
        if (newSubtotal > 0 && newSubtotal < freeShippingThreshold) {
            $shippingMsg.text('Buy ' + formatMoneyJS(freeShippingThreshold - newSubtotal) + ' more for FREE Shipping');
        } else if (newSubtotal >= freeShippingThreshold) {
            $shippingMsg.text('You have FREE Shipping!');
        } else {
            $shippingMsg.text('');
        }
    }
    
    // Update progress bar
    var progress = newSubtotal >= freeShippingThreshold ? 100 : Math.min(100, (newSubtotal / freeShippingThreshold) * 100);
    $('#cartProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
}

/**
 * Show notification message
 */
function showNotification(message, type) {
    type = type || 'info';
    
    // Create notification element
    var $notification = $('<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '<strong>' + (type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info!') + '</strong> ' + message +
        '</div>');
    
    // Add to body
    $('body').append($notification);
    
    // Auto remove after 3 seconds
    setTimeout(function() {
        $notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}
