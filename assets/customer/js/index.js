$(document).ready(function() {
	var post_data = {};
	home_page_func(post_data);	
});

function home_page_func(post_data)
{
    var url = '/welcome/home_page';
    ajaxFileFunc(url, post_data, sucs_home_page, err_home_page)
}

function sucs_home_page(resp)
{
	if(resp.STATUS == 1)
	{
		rand_products(resp.DATA)
		new_products(resp.DATA)
		categories(resp.DATA);
		fast_food(resp.DATA);
		icecream_func(resp.DATA);
		pastry_func(resp.DATA);
		grocery_func(resp.DATA);
		butcher_func(resp.DATA);
	}
}

function err_home_page(xhr, status, error)
{
}

function categories(data)
{
	$.each(data.categories, function(index, cat) {
  $('#category-list').append(`
	<div class="swiper-slide">
                            <a href="/category/${cat.slug}" class="food-categories">
                                <img class="img-fluid categories-img" src="${cat.full_path_image}" alt="p-3">
                                <h4 class="dark-text">${cat.name}</h4>
                            </a>
                        </div>


  `);
});	
}

function rand_products(data)
{
	var products = data.randum_product;
	var get_items_div = products_html(products);
	if(get_items_div){
		$('.randum_product_section').show();
		$('.randum_product').html(get_items_div);
	}
	 
		
}

function new_products(data)
{
	var products = data.new_product;
	var get_items_div = products_html(products);
	if(get_items_div){
		$('.new_product_section').show();
		$('.new_product').html(get_items_div);
	}
	 
		
}

function butcher_func(data)
{
	var fast_food_data 	= data.home_page_product[6];
	var product_category= fast_food_data.product_category_name;
	var products		= fast_food_data.products;
	var get_items_div = products_html(products);
	if(get_items_div){
		$('.butcher_section').show();
		$('.butcher_items').html(get_items_div);
	}
	 
		
}

function grocery_func(data)
{
	var fast_food_data 	= data.home_page_product[5];
	var product_category= fast_food_data.product_category_name;
	var products		= fast_food_data.products;
	var get_items_div = products_html(products);
	if(get_items_div){
		$('.grocery_section').show();
		$('.grocery_items').html(get_items_div);
	}
	 
		
}

function butcher_func(data)
{
	var fast_food_data 	= data.home_page_product[6];
	var product_category= fast_food_data.product_category_name;
	var products		= fast_food_data.products;
	var get_items_div = products_html(products);
	if(get_items_div){
		$('.butcher_section').show();
		$('.butcher_items').html(get_items_div);
	}
	 
		
}

function pastry_func(data)
{
	var fast_food_data 	= data.home_page_product[4];
	var product_category= fast_food_data.product_category_name;
	var products		= fast_food_data.products;
	var get_items_div = products_html(products);
	if(get_items_div){
		$('.pastry_section').show();
		$('.pastry_items').html(get_items_div);
	}
	 
		
}

function icecream_func(data)
{
	var fast_food_data 	= data.home_page_product[3];
	var product_category= fast_food_data.product_category_name;
	var products		= fast_food_data.products;
	var get_items_div = products_html(products);
	if(get_items_div){
		$('.icecream_section').show();
		$('.icecream_items').html(get_items_div);
	}
	 
		
}

function fast_food(data)
{
	var fast_food_data 	= data.home_page_product[2];
	var product_category= fast_food_data.product_category_name;
	var products		= fast_food_data.products;
	var get_items_div = products_html(products);	
	if(get_items_div){
		$('.fast_food_section').show();
		$('.fast_food_items').html(get_items_div);
	}
	 
		
}

function products_html(data)
{
	var item_html = '';
	$.each(data, function(index, item) {
		let salesPrice = ''; let image = '';
		try {
			
  			const priceArray = JSON.parse(item.prices);
  			salesPrice = priceArray.length > 0 ? priceArray[0].sales_price : '';
			image = priceArray.length > 0 ? priceArray[0].images[0] : '';
		} catch (e) {
  		salesPrice = '';
		image = '';
		}
		
		item_html += `
			<div class="col-xl-3 col-lg-4 col-sm-6" data-id="${item.id}">
                            <div class="vertical-product-box">
                                <div class="vertical-product-box-img">
                                    <a href="javascript:void(0);">
                                        <img class="product-img-top w-100 bg-img" src="${item.image_path}"
                                            alt="vp1">
                                    </a>
                                    
                                </div>
                                <div class="vertical-product-body">
                                    <div class="d-flex align-items-center justify-content-between mt-sm-3 mt-2">
                                        <a href="javascript:void(0);">
                                            <h4 class="vertical-product-title">${item.name}</h4>
                                        </a>
                                        <h6 class="rating-star">
                                            <span class="star"><i class="ri-star-s-fill"></i></span>3.9
                                        </h6>
                                    </div>
                                    <h5 class="product-items">
                                    	${item.short_description}
                                    </h5>
									<div class="d-flex align-items-center justify-content-between mt-3">
                                        <h2 class="theme-color fw-semibold">
                                            ${currency_icon}${salesPrice}
                                        </h2>
                                        <button class="btn theme-outline add-btn w-50" data-product='${JSON.stringify(item)}' data-product_id="${item.id}">
                                            + Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
		`;
	});
	return item_html;
}


$(document).ready(function () {
  $("#searchInput").keyup(function () {
    let query = $(this).val().trim();

    function highlight(text, term) {
      if (!term) return text;
      // Escape special regex chars in term
      const escapedTerm = term.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
      const regex = new RegExp(`(${escapedTerm})`, 'gi');
      return text.replace(regex, '<strong>$1</strong>');
    }

    if (query.length > 2) {
      $.ajax({
        url: "/customer/item/ajax_search",
        method: "POST",
        data: { q: query },
        success: function (data) {
          let results = data.DATA;
          let html = "";

          if (results.length > 0) {
            results.forEach(p => {
              let highlightedName = highlight(p.name, query);

              html += `<a href="${p.category_url}" class="list-group-item list-group-item-action d-flex align-items-center">
                         <img src="${p.image_url}" width="40" class="me-2 rounded">
                         <div class="w-100">
                           <strong class="float-start">${highlightedName}</strong>
                           <small>${p.category_name} › ${p.subcategory_name || ''}</small>
                           <span class="text-primary float-end">${currency_icon}${p.price || ''}</span>
                         </div>
                       </a>`;
            });
          } else {
            html = '<p class="list-group-item">No results found</p>';
          }

          $("#searchResults").html(html).show();
        }
      });
    } else {
      $("#searchResults").hide();
    }
  });

  $(document).click(function (e) {
    if (!$(e.target).closest('.search-box').length) {
      $("#searchResults").hide();
    }
  });
});



  
