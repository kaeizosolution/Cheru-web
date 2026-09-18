<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
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
                    <?php if($st === 'cancelled'){
                        $cancelled_by = isset($order_detail->cancelled_by) ? strtolower((string)$order_detail->cancelled_by) : '';
                        if($cancelled_by === 'customer'){
                            $cancel_label = 'Cancelled by Customer';
                        } elseif($cancelled_by === 'vendor'){
                            $cancel_label = 'Cancelled by Vendor';
                        } elseif($cancelled_by === 'admin'){
                            $cancel_label = 'Cancelled by Admin';
                        } else {
                            $cancel_label = 'Cancelled';
                        }
                    ?>
                        <div class="mt-2"><span class="badge bg-danger"><?= $cancel_label; ?></span></div>
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
                                <?php
                                    $display_status = isset($order_detail->status) ? ucfirst($order_detail->status) : '';
                                    if(isset($order_detail->status) && strtolower($order_detail->status) === 'cancelled') {
                                        $cb = isset($order_detail->cancelled_by) ? strtolower((string)$order_detail->cancelled_by) : '';
                                        if($cb === 'customer') $display_status = 'Cancelled by Customer';
                                        elseif($cb === 'vendor') $display_status = 'Cancelled by Vendor';
                                        elseif($cb === 'admin') $display_status = 'Cancelled by Admin';
                                        else $display_status = 'Cancelled';
                                    }
                                ?>
                                <div><b>Order Status:</b> <?= $display_status; ?></div>
                            </div>
                            <div class="col-md-6">
                                <h5>Totals</h5>
                                <div><b>Subtotal:</b> <?= isset($order_detail->total_amount) ? $order_detail->total_amount : ''; ?></div>
                                <div><b>Shipping:</b> <?= isset($order_detail->shipping_amount) ? $order_detail->shipping_amount : ''; ?></div>
                                <div><b>Discount:</b> <?= isset($order_detail->discount_amount) ? $order_detail->discount_amount : ''; ?></div>
                                <div><b>Tax:</b> <?= isset($order_detail->tax_amount) ? $order_detail->tax_amount : ''; ?></div>
                                <div class="fw-bold"><b>Total:</b> <?= isset($order_detail->final_amount) ? $order_detail->final_amount : ''; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--<div class="row">
                <div class="col-sm-12">
                    <div class="card custom-card p-3">
                            <h4>PDF Invoice data</h4>
                            <hr>
                            <h5>Invoice</h5>

                            <button class="btn btn-primary" style="align-self: flex-start; ">Set Invoice number & date</button>
                    </div>
                </div>
            </div>-->

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
  
//THIS FUNCTION USE FOR SHIPPING ADDRESS LIST
var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
postData.order_uid = '<?= $order_uid;?>';
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
                //var selected = rowinner.order_detail_status == item_status_key ? 'selected' : '';
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

           // ShippingAddresshtml += more_then_one ;

            });

        ShippingAddresshtml += more_then_one ;

        ShippingAddresshtml2 = ShippingAddresshtml + ShippingAddress_html;

        ShippingAddresshtml2 = ShippingAddresshtml2 ? ShippingAddresshtml2 : '<div class="container small-p mb-5">There is no information to display.</div>';

        $('#OrderDetail').html(ShippingAddresshtml2);
        
    });

}

function order_status_change(){

// Handle click on "change" Status for product item
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
