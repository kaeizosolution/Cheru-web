<?php 
$page_lang = get_page_language_data('admin_page_lang'); 

// 1. Get order's currency
$order_cur = get_currency($order_detail->currency_id);
$order_symbol = isset($order_cur->symbol) ? html_entity_decode($order_cur->symbol) : '$';
$order_rate = (float)($order_detail->currency_rate ?? 1.0) ?: 1.0;

// 2. Get vendor's display currency
$display_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 1;
$display_cur = get_currency($display_cur_id);
$display_symbol = isset($display_cur->symbol) ? html_entity_decode($display_cur->symbol) : '$';
$display_rate = (float)($display_cur->rate ?? 1.0) ?: 1.0;

// Convert order totals to base, then to vendor's display currency
$base_total = (float)$order_detail->final_amount / $order_rate;
$vendor_display_total = round($base_total * $display_rate, 2);

$base_subtotal = (float)$order_detail->total_amount / $order_rate;
$vendor_display_subtotal = round($base_subtotal * $display_rate, 2);

$base_shipping = (float)$order_detail->shipping_amount / $order_rate;
$vendor_display_shipping = round($base_shipping * $display_rate, 2);

$base_discount = (float)$order_detail->discount_amount / $order_rate;
$vendor_display_discount = round($base_discount * $display_rate, 2);

$base_tax = (float)$order_detail->tax_amount / $order_rate;
$vendor_display_tax = round($base_tax * $display_rate, 2);
?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Order Details</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
            <div class="container-fluid">
                <div class="row">
          <div class="col-sm-12">
            <div class="card custom-card p-3">
                <h3><?= $page_lang->orders; ?> #<?= $order_uid.' '.$page_lang->details; ?></h3>
                <div class="text-muted small">
                    Placed on <?= isset($order_detail->date_added) ? date('d M Y', strtotime($order_detail->date_added)) : ''; ?>
                </div>
                <hr>

                <?php
                    $st = isset($order_detail->status) ? strtolower((string)$order_detail->status) : 'pending';
                    $steps = array('pending' => 'Pending', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered');
                    $rank = array('pending' => 1, 'confirmed' => 2, 'shipped' => 3, 'delivered' => 4);
                    $currentRank = isset($rank[$st]) ? $rank[$st] : 1;
                ?>

                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <?php foreach($steps as $key => $label){
                            $isDone = $currentRank >= $rank[$key];
                            $cls = $isDone ? 'badge bg-success text-white' : 'badge bg-dark text-white';
                        ?>
                            <span class="<?= $cls; ?>" style="padding:6px 10px; font-size:12px; line-height:1;">
                                <?= $label; ?>
                            </span>
                            <?php if($key !== 'delivered'){ ?>
                                <div class="flex-fill" style="height:3px;background:#343a40;"></div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <?php if($st === 'cancelled'){ ?>
                        <div class="mt-2"><span class="badge bg-danger">Cancelled</span></div>
                    <?php } ?>
                </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5><?= $page_lang->customer_info; ?></h5>
                                <div class="form-group">
                                    <div><b><?= $page_lang->customer_uid; ?>: </b> <?= isset($customer_detail->customer_uid) ? $customer_detail->customer_uid : '';?></div>
                                    <div><b><?= $page_lang->name; ?>: </b> <?= isset($customer_detail->fname) ? $customer_detail->fname: '';?>&nbsp;<?= isset($customer_detail->lname) ? $customer_detail->lname: ''; ?></div>
                                    <div><b><?= $page_lang->email; ?>: </b><?= isset($customer_detail->email) ? $customer_detail->email: ''; ?></div>
                                    <div><b><?= $page_lang->mobile; ?>: </b><?= isset($customer_detail->mobile) ? $customer_detail->mobile : ''; ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 pl-5">
                                <div class="form-group">
                                    <h5><?= $page_lang->shipping; ?> Address</h5>  
                                    <div><?= isset($shipping_address->fullname) ? $shipping_address->fullname : ''; ?></div>
                                    <div><b>Mobile:</b> <?= isset($shipping_address->mobile) ? $shipping_address->mobile : ''; ?></div>
                                    <div><b>Address:</b> <?= isset($shipping_address->address_1) ? $shipping_address->address_1 : ''; ?></div>
                                    <div><b>City:</b><?= isset($shipping_address->city) ? $shipping_address->city : (isset($shipping_address->street) ? $shipping_address->street : ''); ?></div>
                                    <div><b>Pincode:</b> <?= isset($shipping_address->postcode) ? $shipping_address->postcode : ''; ?></div>
                               </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h5>Payment &amp; Delivery</h5>
                                <div><b>Payment Method:</b> <?= isset($order_detail->payment_method) ? $order_detail->payment_method : ''; ?></div>
                                <div><b>Payment Status:</b> <?= isset($order_detail->payment_status) ? $order_detail->payment_status : ''; ?></div>
                                <div><b>Order Status:</b> <?= isset($order_detail->status) ? $order_detail->status : ''; ?></div>
                            </div>
                            <div class="col-md-6">
                                <h5>Totals</h5>
                                <div><b>Subtotal:</b> <?= $order_symbol; ?> <?= isset($order_detail->total_amount) ? $order_detail->total_amount : ''; ?>
                                    <?php if ($order_detail->currency_id != $display_cur_id) { ?>
                                        <span class="text-muted" style="font-size: 0.85em;">(<?= $display_symbol; ?> <?= $vendor_display_subtotal; ?>)</span>
                                    <?php } ?>
                                </div>
                                <div><b>Shipping:</b> <?= $order_symbol; ?> <?= isset($order_detail->shipping_amount) ? $order_detail->shipping_amount : ''; ?>
                                    <?php if ($order_detail->currency_id != $display_cur_id) { ?>
                                        <span class="text-muted" style="font-size: 0.85em;">(<?= $display_symbol; ?> <?= $vendor_display_shipping; ?>)</span>
                                    <?php } ?>
                                </div>
                                <div><b>Discount:</b> <?= $order_symbol; ?> <?= isset($order_detail->discount_amount) ? $order_detail->discount_amount : ''; ?>
                                    <?php if ($order_detail->currency_id != $display_cur_id) { ?>
                                        <span class="text-muted" style="font-size: 0.85em;">(<?= $display_symbol; ?> <?= $vendor_display_discount; ?>)</span>
                                    <?php } ?>
                                </div>
                                <div><b>Tax:</b> <?= $order_symbol; ?> <?= isset($order_detail->tax_amount) ? $order_detail->tax_amount : ''; ?>
                                    <?php if ($order_detail->currency_id != $display_cur_id) { ?>
                                        <span class="text-muted" style="font-size: 0.85em;">(<?= $display_symbol; ?> <?= $vendor_display_tax; ?>)</span>
                                    <?php } ?>
                                </div>
                                <div class="fw-bold"><b>Total:</b> <?= $order_symbol; ?> <?= isset($order_detail->final_amount) ? $order_detail->final_amount : ''; ?>
                                    <?php if ($order_detail->currency_id != $display_cur_id) { ?>
                                        <span class="text-muted" style="font-size: 0.9em; font-weight: normal;">(<?= $display_symbol; ?> <?= $vendor_display_total; ?> in your display currency)</span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="card custom-card p-3">
                            <h4><?= $page_lang->order_item_detail; ?> &nbsp; &nbsp;&nbsp;<span id="StatusUpdateMsg" style="color: green"></span></h4>
                            
                             <table class="table table-responsive w-100 c-table">
                                <thead>
                                    <tr>
                                        <th><?= $page_lang->items;?></th>
                                        <th><?= $page_lang->item_name; ?></th>
                                        <th><?= $page_lang->cat_name; ?></th>
                                        <th><?= $page_lang->status; ?></th>
                                        <th><?= $page_lang->item_price; ?></th>
                                        <th><?= $page_lang->qty; ?></th>
                                        <th style="text-align: right"><?= $page_lang->net_price; ?></th>
                                    </tr>
                                </thead>
                                <tbody id="OrderDetail">
                                    
                                </tbody>
                             </table>
                        </div>
                    </div>
                </div>  

          </div>

<script type="text/javascript">

$(document).ready(function() {
  $("#StatusUpdateMsg").hide();
  order_detail();
  order_status_change();
});


function order_detail(){
  
var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
postData.order_id = '<?= isset($order_id) ? (int)$order_id : 0;?>';
var request = $.ajax({
  url: "/<?= $TYPE; ?>/order/ajax-order-detail",
  type: "POST",
  data: postData,
  dataType: "json"
}); 

request.done(function(response) {

        var ShippingAddresshtml =  ''; var ShippingAddresshtml2 = ''; var ShippingAddress_html = '';
        var data              = response.data;
        var tax               = response.tax;
        var shipping          = response.shipping;
        var unit_gross_price  = response.unit_gross_price;
        var currency_symbol   = response.currency_symbol;
        var iso_code          = response.iso_code;
        var order_item_status = response.order_item_status;
        var more_then_one=''; var order_last_tr = '';
        var option_html='';

        $.each(data, function(key, row) {

        $.each(row, function(keyinner, rowinner) {

          $.each(order_item_status, function(item_status_key, item_status_value) {
               option_html +='+item_status_value';
              });

          var imgSrc = rowinner.image;
          if((!imgSrc || imgSrc.indexOf('default_images/product.jpg') !== -1) && rowinner.image_alt){
              imgSrc = rowinner.image_alt;
          }

          more_then_one+='<tr>'+
                  '<td><a href="'+rowinner.url+'" target="_blank"><img src="'+imgSrc+'" class="pr-4" width="150"></a></td>'+
                  '<td><a href="'+rowinner.url+'" target="_blank">'+rowinner.productname+'</a>'+'</br>';
                   if(rowinner.attribute_item){
                       for(i in rowinner.attribute_item){                            
                            more_then_one+='<span class="badge badge-secondary">'+rowinner.attribute_item[i]+'</span>&nbsp;&nbsp;';
                        }
                    }   
        more_then_one+='</td>'+
                  '<td>'+rowinner.category_name+'</td>'+
                  '<td>'+rowinner.orderstatus+'</td>'+
                  '<td>'+rowinner.currency_symbol+' '+rowinner.unit_price+'</td>'+
                  '<td>x '+rowinner.quantity+'</td>'+
                  '<td align="right">'+rowinner.currency_symbol+' '+rowinner.subtotal+'</td>'+
                '</tr>';
                option_html='';
             });

            ShippingAddress_html='<tr>'+
                  '<td colspan="7" align="right">Tax: <strong class="pl-5">'+iso_code+' '+currency_symbol+' '+tax+'</strong></td>'+
               '</tr>'+
               '<tr>'+
                  '<td class="border-0" colspan="7" align="right">Shipping: <strong class="pl-5">'+iso_code+' '+currency_symbol+' '+shipping+'</strong></td>'+
               '</tr>'+
               '<td></td>'+
               '<tr>'+
                  '<td class="border-0" colspan="7" align="right">Gross Total: <strong class="pl-5">'+iso_code+' '+currency_symbol+'  '+unit_gross_price+'</strong></td>'+
               '</tr>';

            });

        ShippingAddresshtml += more_then_one ;

        ShippingAddresshtml2 = ShippingAddresshtml + ShippingAddress_html;

        ShippingAddresshtml2 = ShippingAddresshtml2 ? ShippingAddresshtml2 : '<div class="container small-p mb-5">There is no information to display.</div>';

        $('#OrderDetail').html(ShippingAddresshtml2);
        
    });

}

function order_status_change(){

    $('tbody').on('change', '.statusval', function (e) {
        var postData  = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        var status    = $(this).val();
        var order_item_id = $(this).attr('data-productid');
        postData.status = status;
        postData.order_item_id = order_item_id;

         $.ajax( {
              url:'/<?= $TYPE; ?>/order/update-ajax-item-status/',
              type:'post',
              dataType:"json",  
              data: postData,
              success:function(data) {
                 order_detail();
                 $("#StatusUpdateMsg").show();
                 $('#StatusUpdateMsg').html(data.message);
                 setTimeout(function() { 
                     $('#StatusUpdateMsg').fadeOut('fast'); 
                 }, 3000); 
              }
          });
     }); 

 }

</script>   
