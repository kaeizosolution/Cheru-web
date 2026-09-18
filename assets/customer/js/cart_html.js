$(document).ready(function(){
load_cart();
});

function load_cart(){
	var post_data = {};
	var url = '/customer/cart/ajax_cart_payload';
	ajaxFileFunc(url, post_data, sucs_load_cart, err_load_cart);	
}

function sucs_load_cart(resp){
	$('.cart_total').html('');
	if(resp.STATUS == 1){
		cart_html(resp.DATA);
		cart_total_html(resp.DATA);
	}
}

function err_load_cart(xhr, status, error){
}
function cart_html(cart_data){
		var cartHtml = '';
		(cart_data.cart_items || []).forEach(function(item) {
			cartHtml += `
				<li class="cart-item">
					<div class="horizontal-product-box">
						<div class="product-content">
							<div class="d-flex align-items-center justify-content-between">
								<h5>${item.post_title}</h5>
								<h6 class="product-price">${currency_icon}${parseFloat(item.total || 0).toFixed(2)}</h6>
							</div>
							<div class="d-flex align-items-center justify-content-between mt-md-2 mt-1 gap-1">
								<h6></h6>
								<div class="plus-minus">
									<input type="number" value="${item.quantity}" min="1" readonly>
								</div>
							</div>
						</div>
					</div>
				</li>`;
		});

		cartHtml = cartHtml ? cartHtml : '<a href="/">Continue your shopping</a>';
		$('#cart-list').html(cartHtml);
	}

	function cart_total_html(cart_data){
		var html = '';
		var summary = cart_data.cart_summary || {};
		var subtotal = parseFloat(summary.subtotal || 0);
		var shipping = parseFloat(summary.shipping || 0);
		var grandTotal = parseFloat(summary.grand_total || summary.total || 0);
		var uniqueCount = parseInt(cart_data.cart_count || ((cart_data.cart_items || []).length) || 0, 10) || 0;
		var totalQty = parseInt(summary.total_qty || 0, 10) || 0;
		html =`
			<h5 class="bill-details-title fw-semibold dark-text">
	            	Bill Details
	           	</h5>
	            <div class="sub-total">
	            	<h6 class="content-color fw-normal">Sub Total</h6>
	                <h6 class="fw-semibold">${currency_icon}${subtotal.toFixed(2)}</h6>
	            </div>
			<div class="sub-total">
				<h6 class="content-color fw-normal">Shipping</h6>
				<h6 class="fw-semibold">${shipping <= 0 ? 'Free' : currency_icon + shipping.toFixed(2)}</h6>
			</div>

	            <div class="grand-total">                                            
				<h6 class="fw-semibold dark-text">To Pay</h6>
	                <h6 class="fw-semibold amount">${currency_icon}${grandTotal.toFixed(2)}</h6>
	            </div>	
		`;


		$('.cart_cnt').html(totalQty);
		// Modern header elements (some layouts may render multiple headers with duplicate ids)
		$('[id="cart_count"]').text(uniqueCount);
		$('[id="cart_qty_count"]').text(totalQty);
		$('[id="cart_total"]').text(currency_icon + grandTotal.toFixed(2));
		$('[id="side_total"]').text(currency_icon + grandTotal.toFixed(2));
		if ($('.order-summery-section').length) {
		    $('.order-summery-section').show();
		}
		$('.cart_total').html(html);

	}
