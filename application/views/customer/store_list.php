<?php include('header_cat.php'); ?>
  
  <!--Product-Listing Area Start Here-->
  <div class="all-product-imosys mobile-listing">
    <div class="container-fluid">     
		
		<!--Store-Listing Area Here--> 	
		  <div class="storeList row"></div> 
            <div class="float-right" id="pagination"> </div> 
		<!--Store-Listing Area End Here-->
		
		<div class="products-listing-area">
            <div class="row" id="search_result"></div>
            <div class="row" id="no_data"></div>
			<div class="float-right" id="pagination1"> </div>
        </div>    
      <!--store-List Area End Here--> 
      
    </div>
  </div>
  <!--Store-Listing Area End Here--> 
  
</div>
<!--Wrapper Area End Here-->
<script>

var postData = {};
var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
$(document).ready(function(){
    postData['page'] = 1;	
	//get_data(postData);
    get_data(1);

});

function get_data(page)
{
    var postData = {};
    postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    postData.page           = page;

	var request = $.ajax({
      url: "/welcome/get_ajax_stores",
      type: "POST",
      data: postData,
      dataType: "json"
    });

	request.done(function(resp) {
		var resp = resp.data;
		if(resp.result)
		{
		    store_func(resp.result);
            $('#pagination').html(resp.pagination);
		}

		//oblc();	
	});

	request.fail(function(jqXHR, textStatus) {
	});	
}

function store_func(args_store)
{
	var store_html = '';
	$.each(args_store, function (key, val) {
		console.log(val.address); 
		store_html += 
			'<div class="col-md-4">'+
              '<div class="store-listings">'+
                '<div class="store-listings-photo">'+				
                 '<img src="'+val.vendor_img_modify+'" alt="" class="img-fluid"> </div>'+				
				'<div class="store-listings-details">'+
					'<h4 class="store-listings-name mb-1 text-black">'+val.store_name+'</h4>'+
                    '<div class="store-listings-address text-grey">'+val.address+'</div>'+	
				'</div>'+		
				
			  '</div>'+
            '</div>';	
    });
	$('.storeList').html(store_html);
	
}


</script>
