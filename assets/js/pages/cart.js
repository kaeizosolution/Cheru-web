$(function(){
    $("input[name='product_item[]']").click(function(){
      var currency_symbol = currency_symbol;
      var val = []; var k=0; item='';
      $(':checkbox:checked').each(function(i){
        k += Number($(this).val());
        item += $(this).attr('data-id') + ',';
      //  alert(item);
      });
      $('#cart_sum').html(currency_symbol +'' + k.toFixed(2));
      $('#grand_total').html(currency_symbol +'' + k.toFixed(2));
      $('#proceed_amount').val(currency_symbol + '' + k.toFixed(2));
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

function update_cart(quantity, product_id, attribute_item_id) {
    var postData = {};
    // Ensure csrf is defined globally in your header
    if (typeof csrf !== 'undefined') {
        postData[csrf.name] = csrf.hash;
    }

    postData.product_id = product_id;
    postData.quantity = quantity;
    postData.attribute_item = JSON.stringify(attribute_item_id.split(','));
    postData.action = 'update';

    $.ajax({
       type: 'POST',
       // REMOVED leading slash to make it relative, or use a global BASE_URL variable
       url: "customer/cart/ajax_add_to_cart", 
       data: postData,
       dataType: "json",
       success: function (response) {
        if(response.status == '1') {
            window.location.reload();
        } else {
            alert(response.message);
        }
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
    //window.location.href = "/checkout";
    var post_data = {};
    post_data[csrf.name]   = csrf.hash;
 
    var request = $.ajax({
      url: "/customer/cart/ajax_check_inventory",
      type: "POST",
      data: post_data,
      dataType: "json"
    });
    
    request.done(function(resp) {
        if(resp.STATUS)
        {
            $.each(resp.DATA, function (key, val) {
                $('#ofs_'+key).addClass('alert alert-danger');
                $('#ofs_'+key).html(val);
            }); 
        }
        else
        {
            window.location.href = "/checkout";
        }
    });

    request.fail(function(jqXHR, textStatus) {
      //alert( "Request failed: " + textStatus );
   });
});




$(document).ready(function() {
	getWishlist_Product();
});

function getWishlist_Product(){

  var postData = {};
  var heart = heart;
  postData[csrf.name]   = csrf.hash;
  $.ajax({
        type: "POST",
        url: '/customer/cart/get_wishlist',
        data: postData,
        dataType: "json",

        success: function(data){

            if(data.STATUS == 1){
              var data = data.DATA;
	      var WishlistHtml = '<div id="wishdata" class="owl-carousel featured-slider owl-theme owl-loaded owl-drag">';
              $.each(data, function(data, row) {	
	
              WishlistHtml+='<div class="item">'+
                  '<div class="product-item">'+
                  '<div class="product-absolute-options"><span data-data="'+row.wishlist_id+'" id="WishlistProduct" onclick="removeWishlist(this);" title="wishlist" class="like-icon liked" title="wishlist"></span> </div>'+
                  '<a href="'+row.url+'" class="product-img"><img src="'+row.productimg+'" alt="">'+
                    
                    '</a>'+
                    '<div class="product-text-dt">'+
                      '<h4>'+row.productname+'</h4>'+
                      '<div class="product-price">'+row.iso_code+' '+parseInt(row.regular_price)+' </div>'+
                      /*'<div class="product-buynow">'+
                        '<button type="button" onclick="#" class="btn btn-buynow">Add to Cart</button>'+
                        '<span class="cart-icon"><i class="uil uil-shopping-cart-alt"></i></span> </div>'+*/
                    '</div>'+
                    '</div>'+
                '</div>';
             });

	           WishlistHtml += '</div>';
            $('#Wishlist').html(WishlistHtml);
            reinitialsie_owl(data);

          }

        }

    });

}


function reinitialsie_owl(data)
{
  $("#wishdata").owlCarousel({
	navigation : true,
        items: 4,
        loop: (data.length > 4) ? true : false,
        nav: false,
        dots: false,
        margin:20,
        autoplay: true,
        singleItem:true,
        navText: ["<i class='uil uil-angle-left'></i>", "<i class='uil uil-angle-right'></i>"],
        responsive: {
          0: {
            items: 1
          },
          600: {
            items: 1
          },
          1000: {
            items: 2
          },
          1200: {
            items: 4
          },
          1400: {
            items: 5
          }
        }

      });
}

function removeWishlist(t){

	var postData = {};
  var wishlist_id = $(t).data('data');
	postData[csrf.name]   = csrf.hash;
	postData.wishlist_id = wishlist_id;
	$.ajax({
	    type: 'POST',
	    url: '/customer/profile/ajax_remove_wishlist_product',
	    data: postData,
	    dataType:"json",
	    success: function (response) {
	        //swal({text: response.message, timer: 2000, buttons: false});
	        if(response.status == '1'){
              showToast(response.message, 'success');
	            getWishlist_Product();
	        }
	    }
	});

}



