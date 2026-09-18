  
  // shipping list
  $(document).ready(function() {
  shipping_address();
  });
    
    function shipping_address() {
      //THIS FUNCTION USE FOR SHIPPING ADDRESS LIST
        var postData = {};
        postData[csrf.name]   = csrf.hash;
        var request = $.ajax({
          url: "/customer/checkout/ajax_get_shipping_address",
          type: "POST",
          data: postData,
          dataType: "json"
        }); 
     
        request.done(function(response) {
            var ShippingAddress_html = '';
            if(response.status == '1'){     
                var data = response.data;
                $.each(data, function(key, row) {
                    var checked = '';
                    if(row.status == 1){
                        checked = 'checked';
                    }
                    ShippingAddress_html+='<div class="delivery-address-checkout id-'+row.customer_id+'">'+
                  '<div class="ui radio checkbox chck-rdio">'+
                    '<input type="checkbox" data-id='+row.shipping_address_id+'  id="DefaultAddress" class="shipping_address_radio" name="default_address" value="'+row.shipping_address_id+'" '+row.default_address+'><label>'+
                    '<div class="label-radio-wrp">'+
                      '<div class="address-related-div"> <span class="font-weight-bold">'+row.name+'</span> <span class="delivery-place-tag">'+row.loc_type+'</span> <span class="font-weight-bold">'+row.mobile+'</span> <span class="d-block mt-2 mb-3">'+row.address_1+' '+row.city+' <span class="pincode-address">'+row.postcode+'</span></span>';
                      if(row.default_address == 'checked'){
            ShippingAddress_html+='<button class="next-btn16">Deliver Here</button>';
                        }
                ShippingAddress_html+='</div>'+
                      '<div class="change-address-btn">'+
                      '<a class="edit-address-circle hover-btn EditAdd" data-id="'+row.shipping_address_id+'" onclick="EditAddress('+row.shipping_address_id+')" href="#edit-address" id="Edit_Address" data-tooltip="Edit" data-position="top center"><i class="fa fa-pencil-alt"></i></a>'+
                      '</div>'+
                    '</div>'+
                   '</label>'+
                  '</div>'+
                  '</div>';
                });
    
            ShippingAddress_html = ShippingAddress_html ? ShippingAddress_html : '<div class="col-lg-12 col-md-12">'+
                        '<div class="order-body10">'+
                          '<div class="order-dtsll myaccount-orderbox">'+
                             '<div class="row text-center p-5">'+
                                     '<div class="col-md-6 mx-auto">'+
                                          '<img src="/assets/default_images/no-address.svg" class="empty-image mb-2">'+    
                                          '<h2 class="text-emptycart mb-3">You have no address</h2>'+
                                '</div>'+
                             '</div>'+
                          '</div>'+
                        '</div>'+
                    '</div>';     
            $('#addr').html(ShippingAddress_html); 
            }
        });
        request.fail(function(jqXHR, textStatus) {
            // $('.page-loading').hide();
            //console.log( "Request failed: " + textStatus );
        });
    }
   

    //order now
    $(document).ready(function(){ 
        var postData = {};
        postData[csrf.name]   = csrf.hash;
        $('#confirmorder').hide();
        $('#order_confirm').on('submit', function (e) {
            e.preventDefault();
            var shipping_addr_id = 0;
            $('.shipping_address_radio:checked').each(function(i){
                shipping_addr_id = $(this).val()
            });
            
            if(shipping_addr_id){
                postData.shipping_address_id = shipping_addr_id;
            }else{
                swal('Please select any Shipping Address');
                return false;
            }

            var payment = 0;
            $('.payment_radio:checked').each(function(i){
                payment = $(this).val()
            });

            if(payment){
                postData.payment_mode = payment;
            }else{
                swal('Please select any Payment Type');
                return false;
            }

            $.ajax({
            type: 'POST',
            url: '/customer/checkout/ajax_order_confirm',
            data: postData,
            dataType:"json",
                beforeSend: function() {
                    //$("#loading-image").show();
                },
            success: function (response) {
              if(response.status){
                        var order_id = response.data[0].order_id;
                        showToast(response.message, 'success');
                        //swal({text: response.message, timer: 2000, buttons: false});
                        window.location.href = "/thanks/"+order_id;
                    }
                    else{
                        showToast(response.message, 'danger');
                    }
            },
                error: function (error) {
                    //$("#loading-image").hide();
                }
          });
        });
    });
    

$("#couponForm").on('submit', function(e) {
    e.preventDefault();
    var loginForm = $(this);
    $.ajax({
        url: loginForm.attr('action'),
        type: 'post',
        data: loginForm.serialize(),
        success: function(response){
            $('#couponForm_error').html('');
            $('#couponForm_error').hide();

            if(response.status == '1') {
                var cart_summary = response.data[0].cart_summary;
                $('#cart_subtotal').html(cart_summary.symbol+'&nbsp;'+ cart_summary.subtotal);
                if(!isNaN(cart_summary.tax)){
                    $('#cart_tax').html(cart_summary.symbol +'&nbsp;'+ cart_summary.tax)
                }
                if(!isNaN(cart_summary.shipping)){
                    $('#cart_shipping').html(cart_summary.symbol +'&nbsp;'+ cart_summary.shipping);
                }
                $('#cart_total').html(cart_summary.symbol +'&nbsp;'+ cart_summary.total);
                $('#couponForm_error').show();
                $('#couponForm_error').html(response.message).addClass('alert alert-success');

                if(cart_summary.coupon_amount){
                    var coupon = response.data[0].coupon;
                    $('#coupon_applied').show();
                    //$('#coupon_applied_amount').html(coupon.name + ' : ' + cart_summary.symbol +'&nbsp;'+cart_summary.coupon_amount);
                    $('#coupon_applied_amount').html('<h4><span style="display: block;font-size: 10px;">Coupon Applied</span>'+coupon.name+'</h4><span class="text-success text-uppercase">- '+cart_summary.symbol +'&nbsp;'+cart_summary.coupon_amount+'</span>');
                }
            }else{
                $('#couponForm_error').show();
                $('#couponForm_error').html(response.message).addClass('alert alert-danger');
                $('#coupon_applied').hide();
                
            }
            setTimeout(function () {
                    $('#couponForm_error').hide(500);
                }, 3000);
        }
    });
});

function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
    {
        return false;
    }
    return true;
}


$(function(){
    $("input[name='product_item[]']").click(function(){
      var currency_symbol = '<?php echo $symbol ?>';
      var val = []; var k=0; item='';
      $(':checkbox:checked').each(function(i){
        k += Number($(this).val());
        item += $(this).attr('data-id') + ',';
      //  alert(item);
      });
      $('#cart_sum').html(currency_symbol +' ' + k.toFixed(2));
      $('#grand_total').html(currency_symbol +' ' + k.toFixed(2));
      $('#proceed_amount').val(currency_symbol + ' ' + k.toFixed(2));
      $('#cart_item').val(item.replace(/,\s*$/, ""));
    });
});

$(document).on('click','.quantity_update', function(e){
    var id = $(this).attr('data-id');
    var txtBox = $(this).closest('.numbering').find('input[type=text]');
    var quantity = txtBox.val().trim();
    var attribute_item_id = txtBox.attr('data-attribute_item_id');
    var product_id = txtBox.attr('data-product_id');

    if(id == 'plus'){
        quantity++;
    }else if(id == 'minus'){
        if(quantity > 1){
            quantity--;
        }
    }
    txtBox.val(quantity);
    update_cart(quantity,product_id,attribute_item_id);
});

$(document).on('keyup','.qty_update', function(e){
    var item_id = $(this).attr('data-itemid');
    var quantity = 1;
    if($(this).val() == '' || $(this).val() == 0){
        quantity = 1;
    }else{
        quantity = $(this).val();
    }
    $(this).val(quantity);
} );

function update_cart(quantity,product_id,attribute_item_id)
{ 
    var postData = {};
    postData[csrf.name]   = csrf.hash;
    var attribute_item_array = attribute_item_id.split(',');

    postData.product_id = product_id;
    postData.quantity = quantity;
    postData.attribute_item = JSON.stringify(attribute_item_array);
    postData.action = 'update';
    $.ajax({
       type: 'POST',
       url:  "/customer/cart/ajax_add_to_cart",
       data: postData,
       dataType:"json",
       beforeSend: function() {
         //  $("#loading-image").show();
       },
       success: function (response) {
      if(response.status){
               window.location.reload();
               showToast(response.message, 'success');
               //swal({text: response.message, timer: 2000, buttons: false});
      //  $('#cart_count').html(response.data[0].cart_count);
      }
          // $("#loading-image").hide();
       },
       error: function (error) {
         //  $("#loading-image").hide();
       }
    });
}

$(document).on('click','.btn-delete', function(e){

    var postData = {};
    postData[csrf.name]   = csrf.hash;

    var product_id = $(this).attr('data-product_id');
    var attribute_item_id = $(this).attr('data-attribute_item_id');
    var attribute_item_array = attribute_item_id.split(',');
    postData.product_id = product_id;
    postData.attribute_item = JSON.stringify(attribute_item_array);
    postData.action = 'delete';
    $.ajax({
       type: 'POST',
       url:  "/customer/cart/ajax_add_to_cart",
       data: postData,
       dataType:"json",
       beforeSend: function() {
         //  $("#loading-image").show();
       },
       success: function (response) {
      if(response.status){
               window.location.reload();
               showToast(response.message, 'success');
               //swal({text: response.message, timer: 2000, buttons: false});
      //  $('#cart_count').html(response.data[0].cart_count);
      }
          // $("#loading-image").hide();
       },
       error: function (error) {
         //  $("#loading-image").hide();
       }
    });
} );

$('.btn-checkout').click(function() {
    window.location.href = "/checkout";
});


// this function use for the Edit address
function EditAddress(id)
{
  $(".addNewAddress").toggle('slow');
  ajax_edit_address(id)
}


function cancel()
{
    $(".addNewAddress").toggle('slow');
    $("#Address").trigger("reset");
}


function ajax_edit_address(id)
{
  var postData = {};
  postData[csrf.name]   = csrf.hash;
  postData.id = id;
  $.ajax({    
        type: "POST",
        url:'/customer/profile/ajax-get-address-byid',
        dataType:"json",
  data: postData,
        success: function(data){  
    var AddData = data.data;
    $("#ShippingAddressId").val(AddData.shipping_address_id);               
    $("#Fullname").val(AddData.fullname);               
          $("#Mobile").val(AddData.mobile);
          $("#Address1").val(AddData.address_1);
          $("#City").val(AddData.city);
          $("#PostCode").val(AddData.postcode);
          $("#HowToReach").val(AddData.how_to_reach);
    $("#loc_type").val(AddData.loc_type);
    $("input[name=loc_type][value=" + AddData.loc_type + "]").attr('checked', 'true');
        }
    }); 
}


$(document).ready(function(){
$('#msgaddress').hide();

//this is use for the update address

$('#Address').on('submit', function (e) {
  e.preventDefault();
    $.ajax({
      type: 'POST',
      url: '/customer/profile/ajax_update_address',
      data: $('#Address').serialize(),
      dataType:"json",
      success: function (data) {
    $('#msgaddress').show();
     if(data.STATUS == '1'){
    shipping_address();
        showToast(data.MSG, 'success');
        //$('#msgaddress').addClass('alert alert-success').html(data.MSG);
        $("#Address").trigger("reset");
        $(".addNewAddress").toggle('slow');
        //setTimeout(function() {
               // $('#msgaddress').fadeOut('slow');
          //}, 2200); 
            }else{
              showToast(data.MSG, 'danger');
              /*$('#msgaddress').addClass('alert alert-danger').html(data.MSG);
              setTimeout(function() {
                      $('#msgaddress').fadeOut('slow');
                }, 2200);*/
            }   
          }
      });
  });

});


$(document).on('click','#DefaultAddress', function(e){

  var postData = {};
  postData[csrf.name]   = csrf.hash;
  var shipping_address_id = $(this).attr('data-id');
  var default_address   = $(this).attr('value');

    postData.shipping_address_id = shipping_address_id;
    postData.default_address = default_address;

     $.ajax( {
         url:'/customer/checkout/add-default-shipping-address',
         type:'post',
         dataType:"json",  
         data: postData,
         success:function(response) {
          $('#msgaddress').show();
          if(response.status == '1'){
            shipping_address();
                showToast(response.message, 'success');
            }else{
                showToast(response.message, 'danger');
                
            }
         }
     });

});



//search address by google api
function check_initializeAutocomplete(){
    var input = document.getElementById('Address1');
    var options = {}
    /*var options = {
        types: ['address'],
        componentRestrictions: {
          country: 'in'
        }
    };*/
      
    var autocomplete = new google.maps.places.Autocomplete(input, options);

    google.maps.event.addListener(autocomplete, 'place_changed', function() {
      var place = autocomplete.getPlace();
      var lat = place.geometry.location.lat();
      var lng = place.geometry.location.lng();
      var placeId = place.place_id;
      // to set city name, using the locality param
      var componentForm = {
        Address1: 'short_name',
        
      };
      
      
      
      for (var i = 0; i < place.address_components.length; i++) {
          
       for (var j = 0; j < place.address_components[i].types.length; j++) {
            console.log(place.address_components[i].types[j]);
            if (place.address_components[i].types[j] == "postal_code") {
                //var postal_code = document.getElementById('postal_code').innerHTML = place.address_components[i].long_name;
                document.getElementById("PostCode").value = place.address_components[i].long_name;
            }
            if (place.address_components[i].types[j] == "country1") {
                //var postal_code = document.getElementById('postal_code').innerHTML = place.address_components[i].long_name;
                document.getElementById("country_check").value = place.address_components[i].long_name;
            }
            if (place.address_components[i].types[j] == "locality") {
                //var postal_code = document.getElementById('postal_code').innerHTML = place.address_components[i].long_name;
                document.getElementById("City").value = place.address_components[i].long_name;
            }
            var addressType = place.address_components[i].types[j];
            //console.log(componentForm[addressType]);          
            if (componentForm[addressType]) {           
              var val = place.address_components[i][componentForm[addressType]];         
             //document.getElementById("city").value = val;          
            }
            
        }
        
        //address filter  and city
       // var addressType = place.address_components[i].types[0];
        //console.log(componentForm[addressType]);
        //
        //if (componentForm[addressType]) {         
        //  var val = place.address_components[i][componentForm[addressType]];       
         // document.getElementById("city").value = val;
        //}
        
        
      }
      document.getElementById("latitude_check").value = lat;
      document.getElementById("longitude_check").value = lng;
      document.getElementById("location_id_check").value = placeId;
    });
  }

