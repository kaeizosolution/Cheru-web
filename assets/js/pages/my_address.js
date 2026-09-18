$(document).ready(function() {
  $("#DefaultShippingAddress").hide();
  get_shipping_address();

 });

//THIS FUNCTION USE FOR SHIPPING ADDRESS LIST

function get_shipping_address(){

    var postData = {};
    postData[csrf.name]   = csrf.hash;

    var request = $.ajax({
      url: "/customer/profile/ajax-shipping-address",
      type: "POST",
      data: postData,
      dataType: "json"
    }); 

    request.done(function(response) {
        var ShippingAddress_html = '';
        if(response.status == '1'){
            var data = response.data;
            $.each(data, function(key, row) {
            ShippingAddress_html+='<div class="label-radio-wrp">'+
                    '<div class="address-related-div"> <span class="font-weight-bold">'+row.fullname+'</span> <span class="delivery-place-tag">'+row.address_type+'</span> <span class="font-weight-bold">'+row.mobile+'</span> <span class="d-block mt-2 mb-3">'+row.full_address+'<span class="pincode-address">'+row.postcode+'</span></span> </div>'+
                    '<div class="change-address-btn">'+
                      '<button class="edit-address-circle hover-btn EditAdd" data-id="'+row.shipping_address_id+'" onclick="EditAddress('+row.shipping_address_id+')" href="#edit-address" id="Edit_Address" data-tooltip="Edit" data-position="top center"><i class="fa fa-pencil-alt"></i></button>'+
                      '<button class="delete-address-circle" id="DeleteAddress" data-id="'+row.shipping_address_id+'"  data-tooltip="Delete" data-position="top center"><i class="fa fa-trash-alt"></i></button>'+
                    '</div>'+
                  '</div>'+
                  '<hr>';
            });

        ShippingAddress_html = ShippingAddress_html ? ShippingAddress_html :'<div class="col-lg-12 col-md-12">'+
										    '<div class="order-body10">'+
										      '<div class="order-dtsll myaccount-orderbox">'+
										         '<div class="row text-center p-5">'+
									                   '<div class="col-md-6 mx-auto">'+
									                        '<img src="/assets/default_images/no-address.svg" class="empty-image mb-2">'+    
									                        '<h2 class="text-emptycart mb-3">You have no address</h2>'+
										                  '<a href="javascript:void(0)" onclick="EditAddress()" id="Add_Address" class="cart-checkout-btn hover-btn btn-lg">Add Address</a>'+
										            '</div>'+
										         '</div>'+
    										  '</div>'+
									      '</div>'+
									  '</div>';
;

        $('#ShippingAddress').html(ShippingAddress_html);

        }
    });
}

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
		get_shipping_address();
            showToast(data.MSG, 'success');
        		$("#Address").trigger("reset");
        		$(".addNewAddress").toggle('slow');
            }else{
              showToast(data.MSG, 'danger');
            } 	
          }
      });
  });

});


// this function use for the delete address

$(document).on('click','#DeleteAddress', function(e){
    var postData = {};
    postData[csrf.name]   = csrf.hash;
    var shipping_address_id = $(this).attr('data-id');
    //var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"}; 
    postData.shipping_address_id = shipping_address_id;
    $("#myModal").modal('show');
    swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
   .then((willDelete) => {
   if (willDelete) {
    $.ajax({
      url: '/customer/profile/delete-my-address',
      type:"Post",
      dataType:"json",  
      data: postData,
      success: function (data) {
           if(data.status == '1'){
                get_shipping_address();
                showToast(data.message, 'success');     
            }else{
                showToast(data.message, 'danger');  
            }   
          }
	});
       }
    });
});

function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
    {   return false;
        return false;
    }
	return true;
}


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
      document.getElementById("place_id").value = placeId;
    });
  }
