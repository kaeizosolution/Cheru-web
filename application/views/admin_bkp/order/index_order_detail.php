<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Orders</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
            <div class="container-fluid">
                <div class="row">
          <div class="col-sm-12">
            <div class="card custom-card p-3">
                <h3>Order #<?= $order_uid; ?> details</h3>
                <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Customer Info</h5>
                                <div class="form-group">
                                    <div><b>customer uid: </b> <?= isset($customer_detail->customer_uid) ? $customer_detail->customer_uid : '';?></div>
                                    <div><b>Name: </b> <?= isset($customer_detail->fname) ? $customer_detail->fname: '';?>&nbsp;<?= isset($customer_detail->lname) ? $customer_detail->lname: ''; ?></div>
                                    <div><b>Email: </b><?= isset($customer_detail->email) ? $customer_detail->email: ''; ?></div>
                                    <div><b>Mobile: </b><?= isset($customer_detail->mobile) ? $customer_detail->mobile : ''; ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 pl-5">
                                <h5>Shipping</h5>  
                                <p><?= isset($shipping_address->fname) ? $shipping_address->fname : ''; ?>&nbsp;<?= isset($shipping_address->lname) ? $shipping_address->lname : ''; ?><br>
                                <?= isset($shipping_address->mobile) ? $shipping_address->mobile : ''; ?><br>
                                <?= isset($shipping_address->city) ? $shipping_address->city : ''; ?><br>
                                <?= isset($shipping_address->street) ? $shipping_address->street : ''; ?><br>
                                <?= isset($shipping_address->postcode) ? $shipping_address->postcode : ''; ?><br>
                                <?= isset($shipping_country_name) ? $shipping_country_name : '';?></p>
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
                            <h4>Order Item Detail &nbsp; &nbsp;&nbsp;<span id="StatusUpdateMsg" style="color: green"></span></h4>
                            
                             <table class="table table-responsive w-100 c-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Item Name</th>
                                        <th>Cat Name</th>
                                        <th>Status</th>
                                        <th>Item Price</th>
                                        <th>Qty</th>
                                        <th style="text-align: right">Net Price</th>
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
                var selected = rowinner.order_detail_status == item_status_key ? 'selected' : '';
               option_html +='<option value="'+item_status_key+'" '+selected+'>'+item_status_value+'</option>';
              });

          more_then_one+='<tr>'+
                  '<td><a href="'+rowinner.url+'" target="_blank"><img src="'+rowinner.image+'" class="pr-4" width="150"></a></td>'+
                  '<td><a href="'+rowinner.url+'" target="_blank">'+rowinner.productname+'</a>'+'</br>';
                   if(rowinner.attribute_item){
                       for(i in rowinner.attribute_item){                            
                            more_then_one+='<span class="badge badge-secondary">'+rowinner.attribute_item[i]+'</span>&nbsp;&nbsp;';
                        }
                    }   
        more_then_one+='</td>'+
                  '<td>'+rowinner.category_name+'</td>'+
                  '<td>'+
                    '<select name="status" id="status_id" class="statusval" data-productid = "'+rowinner.order_item_id+'">'+              
                    option_html+'</select>'+
                  '</td>'+
                  '<td>'+rowinner.iso_code+' '+rowinner.currency_symbol+' '+rowinner.unit_price+'</td>'+
                  '<td>x '+rowinner.quantity+'</td>'+
                  '<td align="right">'+rowinner.iso_code+' '+rowinner.currency_symbol+' '+rowinner.subtotal+'</td>'+
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
