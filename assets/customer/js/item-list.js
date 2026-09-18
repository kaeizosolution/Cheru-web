$(document).ready(function(){
var post_data = {};
post_data['cat_id'] = cat_id;
post_data['product_cat_id'] = product_cat_id;
items_of_category(post_data);
});

function items_of_category(post_data) {
    var url = '/customer/item_list/get_items';
    ajaxFileFunc(url, post_data, sucs_items_of_category, err_items_of_category);
}

var productList = [];
function sucs_items_of_category(resp) {

	if(resp.STATUS == 1){
	productList = [...resp.DATA];
	renderProducts(resp.DATA)
	} else {
		 $('#product-list').html('<div style="text-align:center; padding:20px;">No products found</div>');
	}	
	
}

function err_items_of_category(xhr, status, error) {
}

  function renderProducts(products) {
    const container = $('#product-list');
    container.empty();

    products.forEach(product => {
      	const prices = parseJSON(product.prices);
      	const firstPrice = prices[0] || {};
		const imageUrl =
  			firstPrice.images && firstPrice.images[0]
    		? `/uploads/${product.product_type === 'attribute' ? 'attributes' : 'products'}/${firstPrice.images[0]}`
    		: '/assets/customer/images/menu/2.jpg';
      const productCard = $(`

	<div id="item-${product.id}" data-id="${product.id}" data-type="${product.product_type}">
       		<div class="product-details-box">
            	<div class="product-img">
                	<img class="img-fluid img" src="${imageUrl}" alt="${product.name}">
                </div>
                <div class="product-content">
                	<div class="description d-flex align-items-center justify-content-between gap-1">
                  		<div>
                        	<div class="d-flex align-items-center gap-2">
                            	<img class="img-fluid" src="/assets/customer/images/svg/${product.food_type == 1 ? 'veg' : 'nonveg'}.svg" alt="${product.food_type == 1 ?  'veg' : 'non-veg'}">
                               	<h6 class="product-name">
                                   	${product.name}
                                </h6>
                                ${product.product_type === "attribute" ? '<h6 class="customized">Customized</h6>' : ''}
                             </div>
                                                                        
                             <p>
								${product.short_description}
                             </p>
                         </div>
                         <div class="product-box-price">
                         	<h2 class="theme-color fw-semibold">
                            	${currency_icon}${firstPrice.sales_price}
                           	</h2>
                            <a href="javascript:void(0);" class="btn theme-outline add-btn mt-0" data-product_id="${product.id}">+Add</a>
                         </div>
                       </div>
                     </div>
                   </div>
                 </div>
      `);

      container.append(productCard);
    });
  }

  function parseJSON(str) {
    try {
      return JSON.parse(str || '[]');
    } catch (e) {
      console.warn("JSON Parse failed:", str);
      return [];
    }
  }

			
