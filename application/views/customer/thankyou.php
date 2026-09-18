<?php
$page_lang = get_page_language_data('thankyou_lang');
?>
<!--Header Area End Here-->

<div class="wrapper"> 
  
  <!--Confirm Order Area Start Here-->
  <div class="all-product-grid">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
          <div class="order-placed-dt"> <i class="uil uil-check-circle icon-circle"></i>
            <h2><?= $page_lang->order_success_title ?? 'Order Successfully Placed' ?></h2>
            <!--<p>Your Order ID: <b></b></p>-->
            <p><?= $page_lang->thank_you_order_msg ?? 'Thank you for your order ! your Order ID -' ?> <span class="text-orange"><?= $order_uid; ?></span></p>
            <br>
            <a href="/"><h3><?= $page_lang->continue_shopping ?? 'Continue Shopping' ?></h3></a>
            <div class="delivery-address-bg">
              <div class="title585">
                <div class="pln-icon"><i class="uil uil-telegram-alt"></i></div>
                <h4><?= $page_lang->delivery_address_title ?? 'Your order will be sent to this address' ?></h4>
              </div>
              <ul class="address-placed-dt1">
                <li>
                  <p><i class="uil uil-chat-bubble-user"></i><?= $page_lang->name_label ?? 'Name :' ?><span><?= $name; ?></span></p>
                </li>
                <li>
                  <p><i class="uil uil-map-marker-alt"></i><?= $page_lang->address_label ?? 'Address :' ?><span><?= $address; ?></span></p>
                </li>
                <li>
                  <p><i class="uil uil-phone-alt"></i><?= $page_lang->phone_label ?? 'Phone Number :' ?><span>+<?= $mobile; ?></span></p>
                </li>
                <li>
                  <p><i class="uil uil-card-atm"></i><?= $page_lang->payment_method_label ?? 'Payment Method :' ?><span><?= $payment_mode; ?></span></p>
                </li>
              </ul>
              <div class="stay-invoice" style="flex-direction: column; align-items: stretch; border-top: 1px dashed #ddd; padding-top: 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 13px; color: #666; margin-bottom: 5px;">
                  <span><?= $page_lang->subtotal ?? 'Subtotal' ?></span>
                  <span><?= htmlspecialchars($symbol) . ' ' . number_format($total_amount * $cur_rate, 2); ?></span>
                </div>
                <?php if ($discount_amount > 0): ?>
                <div style="display: flex; justify-content: space-between; font-size: 13px; color: #2e7d32; margin-bottom: 5px;">
                  <span><?= ($page_lang->coupon_discount ?? 'Coupon Discount (') . htmlspecialchars($coupon_code) . ')' ?></span>
                  <span>-<?= htmlspecialchars($symbol) . ' ' . number_format($discount_amount * $cur_rate, 2); ?></span>
                </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 16px; margin-top: 5px; padding-top: 5px; border-top: 1px solid #eee;">
                  <span class="st-hm" style="color: #111;"><?= $page_lang->total_paid ?? 'Total Paid' ?></span>
                  <span class="text-orange"><span><?= htmlspecialchars($symbol) . ' ' . number_format($order_total * $cur_rate, 2); ?></span></span>
                </div>
              </div>
              <!--<div class="placed-bottom-dt text-center"> <a href="myaccount-orders.html" class="invc-link hover-btn">Track Order</a> </div>-->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--Confirm Order Area End Here--> 
  
</div>
<!--Wrapper Area End Here-->
