$(document).ready(function() {
  my_order();
});

function my_order() {

var postData = {};
postData[csrf.name]   = csrf.hash;
$.ajax({
  type: "POST",
  url: "/customer/profile/ajax-get-customer-order",
  data: postData,
  dataType: "json",
  success: function(response){
	var data = response.data;
	var outerHtml = '';
    
    Object.keys(data).reverse().forEach(function (key) {
             var MyOrderList_html = ''; var more_then_one=''; var order_last_div = '';
        $.each(data[key], function(keyinner, rowinner) {

                var args_initmap = {};
                args_initmap = JSON.stringify({"supplier_id": rowinner.supplier_id, "customer_id":rowinner.customer_id});
                args_initmap = args_initmap.toString().replace(/\\"/g, '"').replace(/"/g, '\\"');

            
                MyOrderList_html='<div class="pdpt-bg">'+
                  '<div class="order-body10">'+
                  '<div class="myaccount-orderheader">'+
                      '<div class="row imosysorder-step">'+
                          '<div class="col-md-2">'+
                              '<h5>Order Placed<br>'+
                                  '<span>'+rowinner.order_date+'</span>'+
                              '</h5>'+
                          '</div>'+

                          '<div class="col-md-3">'+
                              '<h5>Total<br>'+
                                  '<span>'+rowinner.symbol+' '+rowinner.totalprice+'</span>'+
                              '</h5>'+
                          '</div>'+
                          '<div class="col-md-3">'+
                              '<h5>Ship To<br>'+
                                  '<span class="text-orange" title="">'+rowinner.shipping_name+'</span>'+
                                  ' / '+
                                  '<span class="text-orange" title="">'+rowinner.shipping_mobile+'</span>'+
                              '</h5>'+
                          '</div>'+
                          '<div class="col-md-4 text-right">'+
                              '<h5>Order ID # '+rowinner.order_uid+'</h5>'+
                          '</div>'+
                      '</div>'+
                  '</div>'+
                 '<div class="order-dtsll myaccount-orderbox">';
          more_then_one+='<div class="myaccount-ordernew-wrap">'+
                          '<div id="SubmitCancelOrder'+rowinner.order_item_uid+'"></div>'+
                          '<div class="myaccount-multipleorderswrap">'+
                              '<div class="row">'+
                                  '<div class="col-md-8">'+
                                      '<div class="myaccount-ordernew">'+
                                          '<div class="order-dtsll-wrap">'+
                                              '<div class="order-dt-img"><a href="'+rowinner.url+'"><img src="'+rowinner.image+'" alt=""></a></div>'+
                                              '<div class="order-dt47">'+
                                                  '<div class="myaccount-order-details">'+
                                                      '<h4><a href="'+rowinner.url+'">'+rowinner.productname+'</a></h4>';
                                                      if(rowinner.attribute_item){
															$.each(rowinner.attribute_item, function(attr_key, attr_value) {	
                                                            more_then_one+='<p><b>'+attr_key+': </b>'+attr_value+'</p>';
                                                          });
                                                        }
                                  more_then_one+='<p class="text-grey"><b>Quantity: </b>'+rowinner.quantity+'</p>'+
                                  '<p class="text-grey"><b>Seller:</b> <span class="text-uppercase">'+rowinner.supplier+'</span></p>'+
                                                      '<div class="myaccount-order-price"><span class="text-orange">'+rowinner.symbol+' '+rowinner.unit_gross_price+'</span></div>'+
                                                  '</div>'+
                                              '</div>'+
                                          '</div>';
                                      if(rowinner.rating_obj!=""){
                                        var i;
                        more_then_one+='<div class="self-ratings-product">';
                                      for(var i in rowinner.rating_obj){
									   
                                       var rat = rowinner.rating_obj[i];
                        more_then_one+='<div class="customers-row-div">'+
                                          '<div class="customers-ratings"> <span class="ratings-badge-1">'+rat.rating+'.0 <i class="fa fa-star pl-1"></i></span><span class="customers-words">'+rat.title+'</span></div>'+
                                          '<div class="customers-desc">'+rat.review+'</div>'+
                                          '</div>';
                                        }
                  more_then_one+='</div>';
                                 }   
                  more_then_one+='</div>'+
                                  '</div>'+
                                  '<div class="col-md-4">'+
                                      '<div class="order-myaccount-status">'+
                                          '<h4 class="mb-0 mt-0">Delivered on '+rowinner.delivery_date+'</h4>'+
                                          '<p class="mb-1">Your item has been '+rowinner.orderstatus+'</p>'+
                                          '<span class="badge badge-success p-2">'+rowinner.orderstatus+'</span>'+
                                          '<div class="myaccount-total-cart pl-0 pr-0">';
					     if(rowinner.status == 1 || rowinner.status == 2 || rowinner.status == 16 || rowinner.status == 17 || rowinner.status == 18 || rowinner.status == 19){
                            more_then_one+= '<button class="trackorder-btn" data-toggle="collapse" data-order_item_uid="'+rowinner.order_item_uid+'" data-order_uid="'+rowinner.order_uid+'" data-target="#od'+rowinner.order_item_id+'" id="trackorder" onClick="initMap(\''+rowinner.vendor_latlong+'\',\''+rowinner.customer_latlong+'\',\''+rowinner.order_item_uid+'\');"><i class="fa fa-map-marker-alt pr-2"></i>Track Order</button>';
                            more_then_one+= '<button class="trackorder-btn btn-dark" data-toggle="collapse" data-target="#cno'+rowinner.order_item_uid+'"><i class="uil uil-trash-alt pr-2"></i>Cancel Item</button>';

					     }else if(rowinner.status == 5){
                            more_then_one+='<p>'+rowinner.cancel_order_reason+'</p>';
					     }else if(rowinner.status == 10)
                         {
                            more_then_one+='<button class="rateorder-btn" data-toggle="collapse" data-target="#rating'+rowinner.order_item_uid+'"><i class="fa fa-star pr-2"></i>Rate & Review</button>';
                         }
                         more_then_one+='</div>'+
                                      '</div>'+
                                  '</div>'+
                              '</div>'+
                          '</div>'+
                          '<div class="myaccount-writereviewform collapse" id="cno'+rowinner.order_item_uid+'">'+
                              '<div class="review-product-wrap">'+
                                '<h4>Reason for Cancellation</h4>'+
                                '<form method="post" class="write-reviewform" onsubmit="cancelOrder(this, event); return !1; " id="CancelOrder" name="cancel_order">'+
                                 '<input type="hidden" name="order_uid" value="'+rowinner.order_uid+'" />'+
                                  '<input type="hidden" name="order_item_uid" value="'+rowinner.order_item_uid+'" />'+
                                  '<input type="hidden" name="'+[csrf.name]+'" value="'+csrf.hash+'" />'+
                                  '<div class="row">'+
                                    '<div class="col-lg-12">'+
                                      '<div class="form-group mt-1">'+
                                        '<select class="form-control" name="cancel_order_reason" id="CancelOrder" required>'+
                                            '<option value="">Select Reason</option>'+
                                            '<option value="I have changed my mind">I have changed my mind</option>'+
                                            '<option value="I want to change my phone number">I want to change my phone number</option>'+
                                            '<option value="I want to change address for the order">I want to change address for the order</option>'+
                                            '<option value="I want to convert my order to Prepaid">I want to convert my order to Prepaid</option>'+
                                            '<option value="Price for the product has decreased">Price for the product has decreased</option>'+
                                            '<option value="Expected delivery time is very long">Expected delivery time is very long</option>'+
                                            '<option value="I want to cancel due to product quality issues">I want to cancel due to product quality issues</option>'+
                                            '<option value="I have purchased the product elsewhere">I have purchased the product elsewhere</option>'+  
                                            '<option value="Other">Other</option>'+
                                        '</select>'+
                                      '</div>'+
                                      '<div class="form-group mt-1">'+
                                        '<label class="control-label">Add Comment</label>'+
                                        '<textarea rows="3" name="comment" required class="form-control" placeholder="Add your comment"></textarea>'+
                                      '</div>'+
                                    '</div>'+
                                    '<div class="col-lg-12">'+
                                      '<button class="post-btn hover-btn" type="submit" name="submit">Submit</button>'+
                                    '</div>'+
                                  '</div>'+
                                '</form>'+
                              '</div>'+
                            '</div>'+
			    '<div class="myaccount-writereviewform collapse" id="rating'+rowinner.order_item_uid+'">'+
							'<form class="write-reviewform" id="Ratingfor" onsubmit="RatingSubmit(this, event); return !1;" method="Post">'+
                              '<div class="rate-product-wrap clearfix">'+
								'<input type="hidden" id="rating_'+rowinner.order_item_uid+'" name="order_item_uid" value="'+rowinner.order_item_uid+'" />'+
								'<input type="hidden" name="product_id" id="product_idd" value="'+rowinner.product_id+'" />'+
								'<input type="hidden" name="attribute_item" id="attribute_item_idd" value="'+rowinner.attribute_item_id+'" />'+
                                '<h4>Rate this product</h4>'+
                                '<div class="rating-wrap clearfix">'+
                                  '<div class="rating clearfix">'+
                                    '<input type="radio" class="ratingstar" id="star5'+rowinner.order_item_uid+'" name="rating" value="5" data-tooltip="Awesome - 5 stars" data-position="top center" />'+
                                    '<label class = "full" for="star5'+rowinner.order_item_uid+'"  title="Awesome - 5 stars"></label>'+
                                    '<input type="radio" id="star4'+rowinner.order_item_uid+'" class="ratingstar"  name="rating" value="4" />'+
                                    '<label class="full" for="star4'+rowinner.order_item_uid+'" title="Pretty good - 4 stars"></label>'+
                                    '<input type="radio" id="star3'+rowinner.order_item_uid+'" class="ratingstar" name="rating" value="3" />'+
                                    '<label class="full" for="star3'+rowinner.order_item_uid+'" title="Meh - 3 stars"></label>'+
                                    '<input type="radio" id="star2'+rowinner.order_item_uid+'" name="rating" class="ratingstar" value="2" />'+
                                    '<label class="full" for="star2'+rowinner.order_item_uid+'" title="Kinda bad - 2 stars"></label>'+
                                    '<input type="radio" id="star1'+rowinner.order_item_uid+'" name="rating" class="ratingstar" value="1" />'+
                                    '<label class="full" for="star1'+rowinner.order_item_uid+'" title="Sucks big time - 1 star"></label>'+
                                  '</div>'+
                                '</div>'+
                              '</div>'+
                              '<div class="review-product-wrap">'+
                                '<h4>Review this product</h4>'+
                                  '<div class="row">'+
                                    '<div class="col-lg-6">'+
                                      '<div class="form-group mt-1">'+
                                        '<label class="control-label">Add Comment</label>'+
                                        '<textarea rows="7" name="review" required="" id="Review'+rowinner.order_item_uid+'" class="form-control" placeholder="Add your comment"></textarea>'+
                                      '</div>'+
                                    '</div>'+
                                    '<div class="col-lg-6">'+
                                      '<div class="form-group mt-1">'+
                                        '<label class="control-label">Title</label>'+
                                        '<input class="form-control" type="text" required="" name="title" id="RatTitle'+rowinner.order_item_uid+'" placeholder="Title" />'+
                                      '</div>'+
                                    '</div>'+
                                    '<div class="col-lg-12">'+
                                      '<button class="post-btn hover-btn" type="submit" name="submit" >Submit</button>'+
                                    '</div>'+
                                  '</div>'+
                                '</form>'+
                              '</div>'+
                            '</div>'+
                           '<div class="track-order collapse" id="od'+rowinner.order_item_id+'">'+
                          '<div class="bs-wizard" style="border-bottom:0;">'+
                            '<div class="bs-wizard-step Pending" id="p1"><span class="bs-wizard-dot"></span><span class="bs-wizard-text">Ordered</span></div>'+
                            '<div class="bs-wizard-step Approved" id="a2"><span class="bs-wizard-dot"></span><span class="bs-wizard-text">Shipped</span></div>'+
                            '<div class="bs-wizard-step Shipped" id="s4"><span class="bs-wizard-dot"></span><span class="bs-wizard-text">Out For Delivery</span></div>'+
                            '<div class="bs-wizard-step linenone Delivered" id="d5"><span class="bs-wizard-dot"></span><span class="bs-wizard-text">Delivered</span></div>'+
                          '</div>'+
                        '</div>';
                        if(rowinner.status == 18)
                        {
                            more_then_one += '<div id="map-layer_'+rowinner.order_item_uid+'" style="width:100%; height:300px; display:none;"></div>';
                        }
                        more_then_one+='<hr class="mt-4 mb-4">';
                        });
        more_then_one+='</div>'+
                  '</div>'+
                '</div>'+
                '</div>'+
            '</div>';

              outerHtml += MyOrderList_html + more_then_one;

	            });
              outerHtml = outerHtml ? outerHtml : '<div class="col-lg-12 col-md-12">'+
 						 	 '<div class="pdpt-bg">'+
							    '<div class="order-body10">'+
							      '<div class="order-dtsll myaccount-orderbox">'+
								    '<div class="row text-center p-5">'+
									  '<div class="col-md-6 mx-auto">'+
								  		'<img src="/assets/default_images/noorder.png" class="empty-image mb-2">'+	
							      			'<h2 class="text-emptycart mb-3">You have no orders</h2>'+
							   		        '<a href="/" class="cart-checkout-btn hover-btn btn-lg">Start Shopping</a>'+
						   			    '</div>'+
									   '</div>'+
								      '</div>'+
								    '</div>'+
								  '</div>'+
								'</div>';
	  
              $('#MyOrderList').html(outerHtml);
        }

      });
}



$(document).on('click','#trackorder', function(e){
     var postData = {};
     postData[csrf.name]   = csrf.hash;
     var {order_uid, order_item_uid} = $(this).data();
     postData.order_item_uid = order_item_uid;
     postData.order_uid = order_uid;
    
    $("#map-layer_"+order_item_uid).toggle(); 
    
	   $('.Pending').removeClass('active').removeClass('disabledstep');
	   $('.Approved').removeClass('active').removeClass('disabledstep');
	   $('.Shipped').removeClass('active').removeClass('disabledstep');
	   $('.Delivered').removeClass('active').removeClass('disabledstep');

     $.ajax({
	type: "POST",
	url: "/customer/profile/ajax-get-track-package",
	data: postData,
	dataType: "json",
	success: function(response){
	 data = response.data;
     //console.log(data);
	 switch(parseInt(data.status))
	 {
	   case 1: 
	   case 16:
       case 19:
	   $('.Pending').addClass('active');
	   $('.Approved').addClass('disabledstep');
	   $('.Shipped').addClass('disabledstep');
	   $('.Delivered').addClass('disabledstep');
	   break;
		
    	  case 2:
           $('.Pending').addClass('active');
           $('.Approved').addClass('active');
           $('.Shipped').addClass('disabledstep');
           $('.Delivered').addClass('disabledstep');
           break;

	   //case 4:
	   case 18:
           $('.Pending').addClass('active');
           $('.Approved').addClass('active');
           $('.Shipped').addClass('active').removeClass('disabledstep');
           $('.Delivered').addClass('disabledstep');
           break;

	   case 10:
           $('.Pending').addClass('active');
           $('.Approved').addClass('active');
           $('.Shipped').addClass('active');
           $('.Delivered').addClass('active');
           break;

	 }
	}     
    });
});


//this function use for the cancel order
function cancelOrder(t, e)
{
var formdata  = new FormData(t); 
var item_id   = t.order_item_uid.value;
$.ajax({
      type: 'POST',
      url: '/customer/profile/ajax_cancel_product',
      data:formdata,
      contentType: false,
      processData: false,
      success: function (response) {
              $('#SubmitShippingFrom').show();          
              if(response.status == '1'){
                  $(`#SubmitCancelOrder${item_id}`).html(response.message).addClass('alert-success');
                  setTimeout(function(e){
                      $(`#SubmitCancelOrder${item_id}`).fadeOut();
                  }, 2000);
                  $(`cno${item_id}`).hide('slow');
                  my_order();
              }else{
                  $(`#SubmitCancelOrder${item_id}`).html(response.message).addClass('alert-danger');
                 setTimeout(function(e){
                      $(`#SubmitCancelOrder${item_id}`).fadeOut();
                  }, 2000);
                 my_order();
              }
          }
      });
  return false;

}


//this function use for the add rating and review

function RatingSubmit(t, e)
{
	var postData = {};
	postData[csrf.name]   = csrf.hash;
    var product_idd   = t.product_idd.value;
	postData.product_id = product_idd;			
	var attribute_item_id = t.attribute_item_idd.value;
    var attribute_item_array = [];

	var order_item_uid = t.order_item_uid.value;
	
	var ratingValue = t.rating.value;
    postData.rating = ratingValue; 
    var reviewValue = t.review.value;
    postData.review = reviewValue;
    var titleValue =  t.title.value;
    postData.title = titleValue;

    if(attribute_item_id){
        attribute_item_array = attribute_item_id.split(',');
    }
    postData.attribute_item = JSON.stringify(attribute_item_array);
	    $.ajax({
        type: 'POST',
        url:  "/customer/profile/ajax-add-to-review-rating",
        data: postData,
        dataType:"json",
        success: function(response) {
				if(response.STATUS == '1'){
				  showToast(response.MSG, 'success');
				  $(`#rating${order_item_uid}`).hide();
				  my_order();
				}else{
					showToast(response.MSG, 'danger');
					$(`#rating${order_item_uid}`).show();
				}
        }
    });
}

/////
var map; var waypoints; var position=[]; 
//var position = [28.650813460822636, 77.33970507219242];
function initMap(vendor_latlong, customer_latlong, item_uid) 
{
    position = vendor_latlong.split(','); 
    var map_div_id = "map-layer_"+item_uid;
    var mapLayer = document.getElementById(map_div_id);
     
    var centerCoordinates = new google.maps.LatLng(20.5937, 78.9629);
    //var defaultOptions = { center: centerCoordinates, zoom: 4 }
	var defaultOptions = {zoom: 20}
    map = new google.maps.Map(mapLayer, defaultOptions);

    ///
    var latlng = new google.maps.LatLng(position[0], position[1]);
    marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title: "Latitude:"+position[0]+" | Longitude:"+position[1],
        icon: "/assets/images/map-logo.png"
    });
    ////

    var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer;
    directionsDisplay.setMap(map);
    var start   = vendor_latlong;
    var end     = customer_latlong;
    
    if(start && end)
    drawPath(directionsService, directionsDisplay,start,end);

    ////
    var cnt=80;
    var intervalId = window.setInterval(function(){
        cnt++;
        //move_loc(cnt);
    }, 5000);
    ////
	showTracking();	
}

function drawPath(directionsService, directionsDisplay,start,end)
{
    directionsService.route({
        origin: start,
        destination: end,
        travelMode: google.maps.TravelMode.DRIVING
    },
    function(response, status) {
        if (status === 'OK') 
        {
            directionsDisplay.setDirections(response);
        } 
        else 
        {
            //window.alert('Problem in showing direction due to ' + status);
        }
    });
}

/*var cnt=80;
var intervalId = window.setInterval(function(){
    cnt++;
    move_loc(cnt);
}, 5000);*/


function move_loc(cnt)
{
    var formData = {};
    formData['cnt'] = cnt;
    var request = $.ajax({
        url: "/ajax_lat.php",
        type: "POST",
        data: formData,
        dataType: "json"
    });

    request.done(function(resp) {
        if(resp.lat_long.lat)
        {
            var result = [resp.lat_long.lat, resp.lat_long.lng];
            //if(cnt == 81)
            //transition(result);               
        }
    });    
}



function transition(result){
    //alert(position[0]);
    //if(position[0])
    //{
        //echo "position== "+ position[0];
    //}

    i = 0;
    deltaLat = (result[0] - position[0])/numDeltas;
    deltaLng = (result[1] - position[1])/numDeltas;
    moveMarker();
}

var numDeltas = 100;
var delay = 10; //milliseconds
var i = 0;
var deltaLat;
var deltaLng;

// this function use for live tracking
var socket = io('https://thin-chicken-20.loca.lt',{secure: true})

function moveMarker(){
    position[0] += deltaLat;
    position[1] += deltaLng;
    //console.log("latl=== "+ position[0]);
    
	//var latlng = new google.maps.LatLng(position[0], position[1]);

    //marker = new google.maps.Marker({});

    //marker.setTitle("Latitude:"+position[0]+" | Longitude:"+position[1]);
    //marker.setPosition(latlng);
    if(i!=numDeltas){
        i++;
        //setTimeout(moveMarker, delay);
    }
}

socket.on('move', data =>{
	var data = data.data;
	 var latlng = new google.maps.LatLng(data.lat, data.lng);
    
     marker.setTitle("Latitude:"+data.lat+" | Longitude:"+data.lng);
     marker.setPosition(latlng);
	 console.log(data, 'lat');
});

const showTracking=()=>{
	var arr = prompt("Enter your numbers").split(",")
	
	socket.emit('move', {lat:arr[0], lng:arr[1]});		
}

