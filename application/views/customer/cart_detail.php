<style>
  .shopping_cart_table .qty_update {
    display: block !important;
    color: #333 !important;
    -webkit-text-fill-color: #333 !important;
    opacity: 1 !important;
    visibility: visible !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    background-color: #ffffff !important;
    width: 100% !important; /* Let the container control width */
    min-width: 30px !important;
    text-align: center !important;
    border: none !important;
  }

  /* Ensure the container doesn't squash the input */
  .quantity-block {
    min-width: 100px !important;
    background: #fff !important;
  }

  .cart-variant-line {
    display: block;
    margin-top: 4px;
    line-height: 1.2;
  }
  .cart-variant-label {
    font-weight: 700;
    color: #222;
  }
  .cart-variant-value {
    display: inline-block;
    font-weight: 700;
    color: #111;
    background: #fff3cd;
    border: 1px solid #ffeeba;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 6px;
  }
</style>

<section class="shop-cart pt30">
  <div class="container">
    <div class="row">
      <div class="col-sm-6 col-lg-4 m-auto">
        <div class="main-title text-center mb50">
          <h2 class="title">Shopping Cart</h2>
        </div>
      </div>
    </div>
    <div class="row mt15">
      <div class="col-lg-8 col-xl-9">
        <div class="shopping_cart_table table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th scope="col">PRODUCT</th>
                <th scope="col">PRICE</th>
                <th scope="col">QUANTITY</th>
                <th scope="col">TOTAL</th>
                <th scope="col">REMOVE</th>
              </tr>
            </thead>
            <tbody class="table_body">
              <?php $token = (isset($this->session) && is_array($this->session->userdata('customer')) && !empty($this->session->userdata('customer')['access_token'])) ? (string)$this->session->userdata('customer')['access_token'] : ''; ?>
              <?php if(true): ?>
                <tr>
                  <td colspan="5" class="text-center p-5"><h4>Loading...</h4></td>
                </tr>
              <?php elseif(!empty($cart_items)): ?>
                <?php foreach($cart_items as $item): ?>
                  <?php $attrKey = is_array($item->attribute_item_id) ? json_encode($item->attribute_item_id) : (string)($item->attribute_item_id ?? ''); ?>
                  <?php $vid = isset($item->variation_id) ? (int)$item->variation_id : 0; ?>
                  <tr>
                    <th scope="row">
                      <ul class="cart_list d-block d-xl-flex">
                        <li class="ps-1 ps-sm-4 pe-1 pe-sm-4">
                          <a href="#">
                            <img src="<?= ($item->image_url ?? base_url('uploads/products/'.$item->image)); ?>" 
                                 onerror="this.src='<?= base_url('assets/customer/images/shop/s1.png'); ?>'" 
                                 alt="product" style="width: 80px;">
                          </a>
                        </li>
                        <li class="ms-2 ms-md-3">
                          <a class="cart_title" href="<?= base_url('product/details/'.$item->product_id); ?>">
                            <span class="fz16"><?= $item->post_title ?></span> <br>
                            <?php if(!empty($item->attributes)): ?>
                               <span class="fz14 cart-variant-line cart-attrs"><span class="cart-variant-label">Selected variant:</span><span class="cart-variant-value cart-attrs-val"><?= $item->attributes ?></span></span>
                            <?php endif; ?>
                          </a>
                        </li>
                      </ul>
                    </th>
                    <td><?= $symbol . number_format($item->regular_price, 2) ?></td>
                    <td>
                      <div class="cart_btn">
                        <div class="quantity-block" style="display: flex; align-items: center; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                          <button class="quantity-arrow-minus inner_page quantity_update minus" 
                                  data-product_id="<?= $item->product_id ?>"
                                  data-variation_id="<?= $vid ?>"
                                  data-attribute_item_id='<?= $attrKey ?>' 
                                  style="border: none; background: #f5f5f5; padding: 8px 12px; cursor: pointer;"> 
                            <span class="fa fa-minus"></span> 
                          </button>
                          
                          <input class="quantity-num inner_page qty_update" 
                                        type="text" 
                                        value="<?= !empty($item->quantity) ? $item->quantity : '1' ?>" 
                                        data-product_id="<?= $item->product_id ?>"
                                        data-variation_id="<?= $vid ?>"
                                        data-attribute_item_id='<?= $attrKey ?>'
                                        data-original_quantity="<?= $item->quantity ?>" 
                                        readonly 
                                        style="width: 50px; text-align: center; font-weight: 700; color: #333; background: #fff; border: none; display: inline-block;">
                          
                          <button class="quantity-arrow-plus inner_page quantity_update plus" 
                                  data-product_id="<?= $item->product_id ?>"
                                  data-variation_id="<?= $vid ?>"
                                  data-attribute_item_id='<?= $attrKey ?>' 
                                  style="border: none; background: #f5f5f5; padding: 8px 12px; cursor: pointer;"> 
                            <span class="fas fa-plus"></span> 
                          </button>
                        </div>
                      </div>
                    </td>
                    <td class="fw600 text-thm" style="white-space: nowrap;">
    <span class="item-price" data-unit-price="<?= $item->sale_price ?>" data-total="<?= $item->total ?>"><?= $symbol . number_format($item->total, 2) ?></span>
    <span class="unit-price" style="display:none;"><?= $item->sale_price ?></span>
</td>
                    <td class="">
                        <a href="javascript:void(0);" class="btn-delete text-danger" 
                           data-product_id="<?= $item->product_id ?>" 
                           data-variation_id="<?= $vid ?>"
                           data-attribute_item_id='<?= $attrKey ?>'>
                            <span class="flaticon-close"></span>
                        </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center p-5"><h4>Your cart is empty</h4></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
          
          <div class="checkout_form mt30">
            <div class="checkout_coupon posr d-block d-xl-flex">
              <form class="form_one posr mb10-lg">
                <input class="form-control coupon_input" type="search" placeholder="Coupon code">
                <a class="btn apply_count_btn" href="#">Apply Coupon</a>
              </form>
              <form class="form_two">
                <a href="<?= base_url('/') ?>" class="btn btn_shopping btn-white me-3">Continue Shopping</a>
              </form>
            </div>
            <div id="coupon-msg" style="display:none; margin-top:8px; padding:8px 14px; border-radius:6px; font-size:14px; font-weight:500;"></div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-xl-3">
        <div class="order_sidebar_widget style2">
          <h4 class="title">Cart Totals</h4>
          <ul>
            <?php 
              $coupon = $this->session->userdata('api_coupon');
              $coupon_discount = 0;
              $coupon_code = '';
              if (is_array($coupon) && !empty($coupon['code'])) {
                  $coupon_code = $coupon['code'];
                  $coupon_discount = (float)($coupon['discount'] ?? 0);
              }
              $final_total = max(0, (float)($cart_summary->grand_total ?? $cart_summary->total) - $coupon_discount);
            ?>
            <li class="subtitle"><p>Product Subtotal <span class="float-end"><?= $symbol . number_format($cart_summary->subtotal, 2) ?></span></p></li>
            <li class="subtitle"><p>Estimated Shipping <span class="float-end"><?= ((float)($cart_summary->shipping ?? 0) <= 0) ? 'Free' : $symbol . number_format((float)($cart_summary->shipping ?? 0), 2) ?></span></p></li>
            <li class="subtitle" id="coupon-discount-row" style="<?= $coupon_code ? '' : 'display: none;' ?>"><p>Coupon Discount (<span id="coupon-code-span"><?= $coupon_code ?></span>) <a href="javascript:void(0);" class="text-danger ms-1 remove-coupon-btn" style="font-size: 12px; font-weight: normal;">Remove</a> <span class="float-end text-success" id="coupon-discount-amount">-<?= $symbol . number_format($coupon_discount, 2) ?></span></p></li>
            <li class="subtitle"><hr></li>
            <li class="subtitle totals"><p>Total <span class="float-end text-thm fz20 fw600"><?= $symbol . number_format($final_total, 2) ?></span></p></li>
          </ul>
          <div class="ui_kit_button payment_widget_btn">
            <a href="<?= base_url('checkout') ?>" class="btn btn-thm btn-block w-100">Proceed to checkout</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Global variables for cart functionality
var base_url = '<?= base_url(); ?>';
var csrf_token_name = '<?= $this->security->get_csrf_token_name(); ?>';
var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
var currency_symbol = '<?= $symbol ?? '$'; ?>';
window.customer_access_token = '<?= isset($this->session) && is_array($this->session->userdata('customer')) && !empty($this->session->userdata('customer')['access_token']) ? $this->session->userdata('customer')['access_token'] : '' ?>';
window.__cheru_cart_api_mode__ = true;

// Seed currency from PHP-rendered values (will be overwritten by API response)
window.currentCurrencySymbol = window.currentCurrencySymbol || '<?= $symbol ?? '$'; ?>';
window.currentCurrencyRate   = window.currentCurrencyRate   || parseFloat('<?= isset($rate) ? $rate : '1'; ?>') || 1.0;

function formatMoneyJS(n) {
    if (typeof window.fmtMoney === 'function') {
        return window.fmtMoney(n);
    }
    var rate = parseFloat(window.currentCurrencyRate || window.currency_rate) || 1.0;
    var sym  = window.currentCurrencySymbol || window.currency_symbol || '$';
    return sym + (parseFloat(n || 0) * rate).toFixed(2);
}

function showCouponMsg(msg, type) {
    var $el = $('#coupon-msg');
    if (type === 'success') {
        $el.css({ background: '#d4edda', color: '#155724', border: '1px solid #c3e6cb' });
    } else {
        $el.css({ background: '#f8d7da', color: '#721c24', border: '1px solid #f5c6cb' });
    }
    $el.text(msg).show();
}

function clearCouponMsg() {
    $('#coupon-msg').hide().text('');
}

function runWhenJqReady(fn) {
    if (typeof window.jQuery !== 'undefined') {
        fn(window.jQuery);
        return;
    }
    var attempts = 0;
    var maxAttempts = 50;
    var timer = setInterval(function () {
        attempts++;
        if (typeof window.jQuery !== 'undefined') {
            clearInterval(timer);
            fn(window.jQuery);
        } else if (attempts >= maxAttempts) {
            clearInterval(timer);
        }
    }, 100);
}

// Function to refresh cart_detail page content without full reload
function refreshCartDetail() {
    var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
    var headers = {};
    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }
    $.ajax({
        url: base_url + 'api/v1/cart',
        type: 'GET',
        dataType: 'json',
        headers: headers,
        success: function(resp) {
            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var data   = (typeof resp.DATA  !== 'undefined') ? resp.DATA  : resp.data;

            // Update live currency from API response
            if (resp && resp.currency) {
                var c = resp.currency;
                if (c.symbol)                       window.currentCurrencySymbol = String(c.symbol);
                if (c.rate && parseFloat(c.rate))   window.currentCurrencyRate   = parseFloat(c.rate);
                window.currency_symbol = window.currentCurrencySymbol;
                window.currency_rate   = window.currentCurrencyRate;
            }

            if (STATUS == 1 && data) {
                var items = data.items || [];
                var summary = {
                    subtotal:    (typeof data.subtotal    !== 'undefined') ? data.subtotal    : (data.cart_total || 0),
                    shipping:    (typeof data.shipping    !== 'undefined') ? data.shipping    : 0,
                    grand_total: data.final_total || data.grand_total || data.cart_total || 0
                };
                var symbol = window.currentCurrencySymbol || currency_symbol || '$';

                try {
                    if (typeof window.renderCartSidebar === 'function') {
                        var totalQty2 = 0;
                        for (var ii = 0; ii < items.length; ii++) {
                            totalQty2 += parseInt(items[ii].quantity || 0, 10) || 0;
                        }

                        var sidebarPayload = {
                            cart_items: items.map(function(it){
                                var qty = parseInt(it.quantity || 0, 10) || 0;
                                var unit = parseFloat(it.price || it.sale_price || 0) || 0;
                                return {
                                    product_id: it.product_id,
                                    variation_id: it.variation_id || 0,
                                    attribute_item_id: it.attribute_item_id || '',
                                    post_title: it.product_name || it.post_title || '',
                                    image_url: it.image_url || '',
                                    quantity: qty,
                                    sale_price: unit,
                                    total: unit * qty,
                                    attributes: it.attributes || ''
                                };
                            }),
                            cart_summary: {
                                subtotal: parseFloat(summary.subtotal || 0) || 0,
                                shipping: parseFloat(summary.shipping || 0) || 0,
                                grand_total: parseFloat(summary.grand_total || 0) || 0,
                                total: parseFloat(summary.grand_total || 0) || 0
                            },
                            cart_count: items.length,
                            cart_qty: totalQty2,
                            cart_total: parseFloat(summary.grand_total || 0) || 0
                        };
                        window.renderCartSidebar(sidebarPayload);
                    }
                } catch (e) {}

                function normalizeAttr(val) {
                    if (!val) return [];
                    if (Array.isArray(val)) return val.slice().sort();
                    if (typeof val === 'string') {
                        var s = val.trim();
                        if (!s) return [];
                        try {
                            var decoded = JSON.parse(s);
                            if (Array.isArray(decoded)) return decoded.slice().sort();
                        } catch (e) {}
                        return s.split(',').map(function(x){ return x.trim(); }).filter(Boolean).sort();
                    }
                    return [String(val)];
                }

                function rowKey(productId, attrVal) {
                    var attrs = normalizeAttr(attrVal);
                    return String(productId) + '|' + JSON.stringify(attrs);
                }

				function rowKey2(productId, variationId, attrVal) {
					var vid = parseInt(variationId || 0);
					if (vid > 0) {
						return 'v|' + String(vid);
					}
					return rowKey(productId, attrVal);
				}
                
                var uniqueCount = items.length;
                var totalQty = 0;
                for (var qi = 0; qi < items.length; qi++) {
                    totalQty += parseInt(items[qi].quantity || 0, 10) || 0;
                }
                $('[id="cart_count"]').text(uniqueCount);
                $('[id="cart_qty_count"]').text(totalQty);
                $('[id="cart_total"]').text(formatMoneyJS(data.final_total || data.cart_total || 0));
                $('[id="side_total"]').text(formatMoneyJS(data.final_total || data.cart_total || 0));
                
                // If cart is empty, show empty state immediately
                if (items.length === 0) {
                    $('.table_body').html('<tr><td colspan="5" class="text-center p-5"><h4>Your cart is empty</h4></td></tr>');
                    $('.order_sidebar_widget .subtitle span.float-end').eq(0).text(formatMoneyJS(0));
                    $('.order_sidebar_widget .subtitle span.float-end').eq(1).text('Free');
                    $('.order_sidebar_widget .totals span.float-end').text(formatMoneyJS(0));
                    return;
                }
                
                // Build a map of existing rows by product_id + attribute_item_id for quick lookup
                var existingRows = {};
                $('.table_body tr').each(function() {
                    var $row = $(this);
                    var productId = $row.find('.qty_update').data('product_id');
					var variationId = $row.find('.qty_update').data('variation_id') || 0;
                    var attrId = $row.find('.qty_update').data('attribute_item_id');
                    if (productId) {
						existingRows[rowKey2(productId, variationId, attrId)] = $row;
                    }
                });

                // If this page was loaded in API-driven mode (server printed only Loading...), build rows now
                if ($('.table_body').find('.qty_update').length === 0) {
                    var rowsHtml = '';
                    items.forEach(function(item) {
                        var pid2 = item.product_id || 0;
                        var vid2 = item.variation_id || 0;
                        var attrKey2 = (typeof item.attribute_item_id !== 'undefined') ? JSON.stringify(item.attribute_item_id) : '';
                        var img2 = item.image_url || '';
                        var title2 = item.product_name || '';
                        var unit2 = parseFloat(item.regular_price || item.price || 0) || 0;
                        var qty2 = parseInt(item.quantity || 1, 10) || 1;
                        var total2 = (parseFloat(item.price || 0) || 0) * qty2;

                        rowsHtml += '<tr>'
                            + '<th scope="row">'
                            +   '<ul class="cart_list d-block d-xl-flex">'
                            +     '<li class="ps-1 ps-sm-4 pe-1 pe-sm-4">'
                            +       '<a href="#">'
                            +         '<img src="' + img2 + '" onerror="this.src=\'<?= base_url('assets/customer/images/shop/s1.png'); ?>\'" alt="product" style="width: 80px;">'
                            +       '</a>'
                            +     '</li>'
                            +     '<li class="ms-2 ms-md-3">'
                            +       '<a class="cart_title" href="<?= base_url('product/details/'); ?>' + pid2 + '">'
                            +         '<span class="fz16">' + title2 + '</span> <br>'
                            +         (item.attributes ? ('<span class="fz14 cart-variant-line cart-attrs"><span class="cart-variant-label">Selected variant:</span><span class="cart-variant-value cart-attrs-val">' + item.attributes + '</span></span>') : '')
                            +       '</a>'
                            +     '</li>'
                            +   '</ul>'
                            + '</th>'
                            + '<td>' + formatMoneyJS(unit2) + '</td>'
                            + '<td>'
                            +   '<div class="cart_btn">'
                            +     '<div class="quantity-block" style="display: flex; align-items: center; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">'
                            +       '<button class="quantity-arrow-minus inner_page quantity_update minus" data-product_id="' + pid2 + '" data-variation_id="' + vid2 + '" data-attribute_item_id=\'' + attrKey2 + '\' style="border: none; background: #f5f5f5; padding: 8px 12px; cursor: pointer;">'
                            +         '<span class="fa fa-minus"></span>'
                            +       '</button>'
                            +       '<input class="quantity-num inner_page qty_update" type="text" value="' + qty2 + '" data-product_id="' + pid2 + '" data-variation_id="' + vid2 + '" data-attribute_item_id=\'' + attrKey2 + '\' data-original_quantity="' + qty2 + '" readonly style="width: 50px; text-align: center; font-weight: 700; color: #333; background: #fff; border: none; display: inline-block;">'
                            +       '<button class="quantity-arrow-plus inner_page quantity_update plus" data-product_id="' + pid2 + '" data-variation_id="' + vid2 + '" data-attribute_item_id=\'' + attrKey2 + '\' style="border: none; background: #f5f5f5; padding: 8px 12px; cursor: pointer;">'
                            +         '<span class="fas fa-plus"></span>'
                            +       '</button>'
                            +     '</div>'
                            +   '</div>'
                            + '</td>'
                            + '<td class="fw600 text-thm" style="white-space: nowrap;">'
                            +   '<span class="item-price" data-unit-price="' + (parseFloat(item.price || 0) || 0) + '" data-total="' + total2 + '">' + formatMoneyJS(total2) + '</span>'
                            +   '<span class="unit-price" style="display:none;">' + (parseFloat(item.price || 0) || 0) + '</span>'
                            + '</td>'
                            + '<td class="">'
                            +   '<a href="javascript:void(0);" class="btn-delete text-danger" data-product_id="' + pid2 + '" data-variation_id="' + vid2 + '" data-attribute_item_id=\'' + attrKey2 + '\'>'
                            +     '<span class="flaticon-close"></span>'
                            +   '</a>'
                            + '</td>'
                            + '</tr>';
                    });
                    $('.table_body').html(rowsHtml);
                }
                
                // Track which rows are in the cart
                var cartRowKeys = [];
                
                // Update each cart item row with new quantities and totals
                items.forEach(function(item) {
					var key = rowKey2(item.product_id, item.variation_id || 0, item.attribute_item_id);
                    cartRowKeys.push(key);
                    var $row = existingRows[key];
                    
                    if ($row && $row.length) {
                        if (item.attributes) {
                            var $attrValEl = $row.find('.cart-attrs-val');
                            if ($attrValEl.length) {
                                $attrValEl.text(item.attributes);
                            }
                        }
                        // Update quantity input
                        var $qtyInput = $row.find('.qty_update');
                        $qtyInput.val(item.quantity);
                        $qtyInput.data('original_quantity', item.quantity);
                        
                        // Update item total display
                        var unit = parseFloat(item.price || item.sale_price || 0) || 0;
                        var itemTotal = unit * (parseInt(item.quantity || 0, 10) || 0);
                        $row.find('.item-price').text(formatMoneyJS(itemTotal)).data('total', itemTotal);
                    }
                });
                
                // Remove rows no longer in cart
                $('.table_body tr').each(function() {
                    var $row = $(this);
                    var productId = $row.find('.qty_update').data('product_id');
					var variationId = $row.find('.qty_update').data('variation_id') || 0;
                    var attrId = $row.find('.qty_update').data('attribute_item_id');
					var rk = rowKey2(productId, variationId, attrId);
					if (productId && cartRowKeys.indexOf(rk) === -1) {
                        $row.fadeOut(300, function() { $(this).remove(); });
                    }
                });
                
                // Update cart summary sidebar
                var subtotal = parseFloat(summary.subtotal || 0);
                var shipping = parseFloat(summary.shipping || 0);
                var grandTotal = parseFloat(data.final_total || summary.grand_total || 0);
                
                $('.order_sidebar_widget .subtitle span.float-end').eq(0).text(formatMoneyJS(subtotal));
                $('.order_sidebar_widget .subtitle span.float-end').eq(1).text(shipping > 0 ? formatMoneyJS(shipping) : 'Free');
                
                if (data.coupon) {
                    $('#coupon-code-span').text(data.coupon.coupon_code);
                    $('#coupon-discount-amount').text('-' + formatMoneyJS(data.coupon.discount));
                    $('#coupon-discount-row').show();
                    $('.coupon_input').val(data.coupon.coupon_code);
                    $('.apply_count_btn').text('Coupon Applied').addClass('btn-success').removeClass('btn-thm');
                } else {
                    $('#coupon-discount-row').hide();
                    $('.coupon_input').val('');
                    $('.apply_count_btn').text('Apply Coupon').addClass('btn-thm').removeClass('btn-success');
                }
                
                $('.order_sidebar_widget .totals span.float-end').text(formatMoneyJS(grandTotal));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error refreshing cart:', error);
        }
    });
}

// Expose refreshCartDetail globally for cart.js
window.refreshCartDetail = refreshCartDetail;

// API-driven initial render + bindings (wait for jQuery)
runWhenJqReady(function($){
	try {
		if (typeof window.refreshCartDetail === 'function') {
			window.refreshCartDetail();
		}
	} catch (e) {}

	$(document).off('click.cheruCartDetailQty', '.shopping_cart_table .quantity_update').on('click.cheruCartDetailQty', '.shopping_cart_table .quantity_update', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();

		var $btn = $(this);
		var $input = $btn.closest('.quantity-block').find('input.qty_update').first();
		var action = $btn.hasClass('plus') ? 'increase' : 'decrease';
		var productId = parseInt($btn.data('product_id') || $input.data('product_id') || 0, 10) || 0;
		var variationId = parseInt($btn.data('variation_id') || $input.data('variation_id') || 0, 10) || 0;
		var attributeItemId = $btn.data('attribute_item_id') || $input.data('attribute_item_id') || '';
		var currentQty = parseInt($input.val() || 1, 10) || 1;
		var nextQty = action === 'increase' ? (currentQty + 1) : Math.max(1, currentQty - 1);

		var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
		var payload = {
			product_id: productId,
			variation_id: variationId,
			attribute_item: attributeItemId,
			quantity: nextQty
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
			success: function(resp){
				try {
					if (resp && resp.csrf_hash) { window.csrf_hash = resp.csrf_hash; csrf_hash = resp.csrf_hash; }
					if (resp && resp.csrf_token_name) { window.csrf_token_name = resp.csrf_token_name; csrf_token_name = resp.csrf_token_name; }
				} catch (e2) {}
				if (typeof window.refreshCartDetail === 'function') {
					window.refreshCartDetail();
				}
				if (typeof window.refreshHeaderAndSidebarCart === 'function') {
					window.refreshHeaderAndSidebarCart({ open: false });
				}
			}
		});
	});

	$(document).off('click.cheruCartDetailRemove', '.shopping_cart_table .btn-delete').on('click.cheruCartDetailRemove', '.shopping_cart_table .btn-delete', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();

		var $btn = $(this);
		var productId = parseInt($btn.data('product_id') || 0, 10) || 0;
		var variationId = parseInt($btn.data('variation_id') || 0, 10) || 0;
		var attributeItemId = $btn.data('attribute_item_id') || '';
		var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';

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
			success: function(resp){
				try {
					if (resp && resp.csrf_hash) { window.csrf_hash = resp.csrf_hash; csrf_hash = resp.csrf_hash; }
					if (resp && resp.csrf_token_name) { window.csrf_token_name = resp.csrf_token_name; csrf_token_name = resp.csrf_token_name; }
				} catch (e2) {}
				if (typeof window.refreshCartDetail === 'function') {
					window.refreshCartDetail();
				}
				if (typeof window.refreshHeaderAndSidebarCart === 'function') {
					window.refreshHeaderAndSidebarCart({ open: false });
				}
			}
		});
	});

	// Apply Coupon Action
	$(document).off('click.cheruApplyCoupon', '.apply_count_btn').on('click.cheruApplyCoupon', '.apply_count_btn', function(e){
		e.preventDefault();
		
		// If already applied, let click act as remove coupon
		if ($(this).hasClass('btn-success')) {
			$('.remove-coupon-btn').trigger('click');
			return;
		}
		
		var coupon_code = $('.coupon_input').val().trim();
		if (!coupon_code) {
			showCouponMsg('Please enter a coupon code.', 'error');
			return;
		}

		var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
		var payload = {
			coupon_code: coupon_code
		};
		if (!token && typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
			payload[window.csrf_token_name] = window.csrf_hash;
		}

		$.ajax({
			url: base_url + 'api/v1/cart/apply-coupon',
			type: 'POST',
			dataType: 'json',
			headers: token ? { 'Authorization': 'Bearer ' + token } : {},
			contentType: 'application/json; charset=utf-8',
			data: JSON.stringify(payload),
			success: function(resp){
				try {
					if (resp && resp.csrf_hash) { window.csrf_hash = resp.csrf_hash; csrf_hash = resp.csrf_hash; }
					if (resp && resp.csrf_token_name) { window.csrf_token_name = resp.csrf_token_name; csrf_token_name = resp.csrf_token_name; }
				} catch (e2) {}
				
				var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
				if (STATUS == 1) {
					clearCouponMsg();
					if (typeof window.refreshCartDetail === 'function') {
						window.refreshCartDetail();
					}
					if (typeof window.refreshHeaderAndSidebarCart === 'function') {
						window.refreshHeaderAndSidebarCart({ open: false });
					}
				} else {
					var msg = resp.errors ? resp.errors[0] : (resp.message || 'Failed to apply coupon.');
					showCouponMsg(msg, 'error');
				}
			},
			error: function(xhr) {
				var resp = xhr.responseJSON || {};
				var msg = resp.errors ? resp.errors[0] : (resp.message || 'Invalid or expired coupon.');
				showCouponMsg(msg, 'error');
			}
		});
	});

	// Prevent coupon form submit reloading page
	$(document).on('submit', '.checkout_coupon form', function(e){
		e.preventDefault();
		$('.apply_count_btn').trigger('click');
	});

	// Remove Coupon Action
	// NOTE: Uses POST (not DELETE) because jQuery DELETE requests don't reliably send
	// a request body with the CSRF token, causing 405 Method Not Allowed on the server.
	$(document).off('click.cheruRemoveCoupon', '.remove-coupon-btn').on('click.cheruRemoveCoupon', '.remove-coupon-btn', function(e){
		e.preventDefault();
		var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';

		var payload = {};
		if (!token && typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
			payload[window.csrf_token_name] = window.csrf_hash;
		}

		$.ajax({
			url: base_url + 'api/v1/cart/remove-coupon',
			type: 'POST',
			dataType: 'json',
			headers: token ? { 'Authorization': 'Bearer ' + token } : {},
			contentType: 'application/json; charset=utf-8',
			data: JSON.stringify(payload),
			success: function(resp){
				try {
					if (resp && resp.csrf_hash)       { window.csrf_hash       = resp.csrf_hash;       csrf_hash       = resp.csrf_hash; }
					if (resp && resp.csrf_token_name) { window.csrf_token_name = resp.csrf_token_name; csrf_token_name = resp.csrf_token_name; }
				} catch (e2) {}
				if (typeof window.refreshCartDetail === 'function') {
					window.refreshCartDetail();
				}
				if (typeof window.refreshHeaderAndSidebarCart === 'function') {
					window.refreshHeaderAndSidebarCart({ open: false });
				}
			},
			error: function(xhr) {
				console.error('Remove coupon failed:', xhr.status, xhr.responseText);
			}
		});
	});
});
</script>