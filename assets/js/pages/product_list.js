var show_categories = 0;
var current_view = 'grid';
var last_grid_length = 0;
var use_delegated_sidebar_accordion = 1;

function bootstrapProductList() {
	if (typeof window.jQuery === 'undefined') {
		var attempts = 0;
		var timer = setInterval(function(){
			attempts++;
			if (typeof window.jQuery !== 'undefined') {
				clearInterval(timer);
				bootstrapProductList();
			} else if (attempts > 100) {
				clearInterval(timer);
			}
		}, 50);
		return;
	}

	var $ = window.jQuery;
	var _priceFilterActive = false; // set true only when user explicitly applies price filter
	var _sliderMax = 2000;

	function resetPriceFilter() {
		_priceFilterActive = false;
		$( ".amount" ).val( 0 );
		$( ".amount2" ).val( _sliderMax );
		try {
			if ($(".slider-range").data("ui-slider")) {
				$(".slider-range").slider("values", 0, 0);
				$(".slider-range").slider("values", 1, _sliderMax);
			}
		} catch(e) {}
		updatePriceRangeLabel();
	}

	// --- Shared price utility functions (module-level so Apply btn and other handlers can use them) ---
	function parsePriceVal(v){
		var s = (v || '').toString();
		s = s.replace(/[^0-9.]/g, '');
		var n = parseFloat(s);
		return isNaN(n) ? null : n;
	}
	function updatePriceRangeLabel(){
		var f = parsePriceVal($('.amount').val());
		var t = parsePriceVal($('.amount2').val());
		if (f === null) { f = 0; }
		if (t === null) { t = 0; }
		if (t > 0 && f > t) {
			var tmp = f;
			f = t;
			t = tmp;
		}
		var _sym = window.currentCurrencySymbol || '$';
		$('#price_range_label').text(_sym + f.toLocaleString() + ' - ' + _sym + t.toLocaleString());
	}

	function ensure_csrf_object(){
		if (typeof window.csrf !== 'undefined' && window.csrf && window.csrf.name && typeof window.csrf.hash !== 'undefined') {
			return;
		}
		if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
			window.csrf = { name: window.csrf_token_name, hash: window.csrf_hash };
		}
	}

	var page_length = 0;

	function get_data(page)
	{
		ensure_csrf_object();
		if (!window.csrf || !window.csrf.name) {
			return;
		}
		if ((typeof cat !== 'undefined' && parseInt(cat || 0, 10) > 0) || ((typeof window.cat_slug !== 'undefined') && window.cat_slug)) {
			show_categories = 0;
		}
		var postData = {};
		postData[csrf.name] 	= csrf.hash;
		postData.fts 			= fts;
		postData.category_id 	= cat;
		postData.category_slug = (typeof window.cat_slug !== 'undefined') ? window.cat_slug : '';
		postData.cat_image 		= cat_image;
		postData.page 			= page;
		postData.slug_param 	= slug_param;
		postData.vendor_obj 	= vendor_obj;
		postData.length 		= page_length;
		postData.view 			= current_view;
		postData.show_categories = show_categories;
		postData.currency_id     = window.currentCurrencyId || 1;

		if($('#price_sort_type').val())
		{
			postData.sorting = $('#price_sort_type').val();
		}	
		// Prefer visible select value (bootstrap-select can desync hidden input)
		if ($('#sort_select').length) {
			var sortVal = ($('#sort_select').val() || '').toString();
			if (sortVal) {
				postData.sorting = sortVal;
			}
		}

		// parsePriceVal and updatePriceRangeLabel are defined at module scope above
		var filterData = {};
		if($('input[name="brand"]:checked'))
		{
			var values = $('input[name="brand"]:checked').map(function () {return this.value;}).get();
			filterData.brand = JSON.stringify(values);
		}
		// Only include price_range when the user has explicitly applied the filter
		if (_priceFilterActive) {
			var fromVal = parsePriceVal($('.amount').val());
			var toVal = parsePriceVal($('.amount2').val());
			if (fromVal !== null || toVal !== null) {
				var pf = fromVal !== null ? fromVal : 0;
				var pt = toVal !== null ? toVal : 0;
				if (pt > 0 && pf > pt) {
					var tmp = pf;
					pf = pt;
					pt = tmp;
				}
				filterData.price_range = JSON.stringify({ from: pf, to: pt });
			}
		}
		postData.filter = filterData;

		var request = $.ajax({
	    	url: "/api/v1/product_list/ajax_search",
	      	type: "POST",
	      	data: postData,
	      	dataType: "json"
	    });

		request.done(function(resp){
			if (resp && resp.csrf_hash && window.csrf) {
				window.csrf.hash = resp.csrf_hash;
			}
			var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
			var DATA = (typeof resp.DATA !== 'undefined') ? resp.DATA : resp.data;
		if(STATUS)
		{
			var data = (DATA && DATA[0]) ? DATA[0] : {};
			var result = data.result;
			var brand = data.brand;
			var cat_data = data.cat_data;
			var categories_list = data.categories_list;

			if(!vendor_obj)
			{
				$('.breadcrumb').html('');
				$('.breadcrumb').append(data.breadcrumb);
			}
			var hn = (typeof data.heading_name !== 'undefined' ? (data.heading_name || '') : '').toString().trim();
			if (!hn) {
				hn = (typeof window.fts !== 'undefined' && window.fts) ? String(window.fts) : 'Products';
			}
			$('.heading_name').html(hn);

			var total = parseInt(data.recordsTotal || 0, 10);
			var perPage = parseInt(postData.length || 0, 10);
			if (!perPage || perPage < 0) {
				perPage = total || 0;
			}
			var pageNum = parseInt(postData.page || 1, 10);
			if (!pageNum || pageNum < 1) pageNum = 1;
			var start = total > 0 ? ((pageNum - 1) * perPage + 1) : 0;
			var end = total > 0 ? Math.min(pageNum * perPage, total) : 0;
			$('.total_count').html('Showing '+start+'–'+end+' of '+total+' results');

			if(postData.page == 1)
            {
                data.recordsTotal ? $('.product-sort').show() : $('.product-sort').hide();
               get_cagegory(cat_data, data.root_parent);
               get_brand(brand);
            }

			if (categories_list && categories_list.length)
			{
				var cdiv = '';
				for (var ci=0; ci<categories_list.length; ci++) {
					var c = categories_list[ci];
					if (!c) continue;
					cdiv += '<div class="col-6 col-md-4 col-lg-3 mb-4 px-2 d-flex align-items-stretch">'+
						'<div class="category_card w-100">'+
							'<a href="'+c.url+'" class="category_card_inner">'+
								'<div class="category_img_wrap">'+
									'<img src="'+(c.image_url || '/assets/default_images/product.jpg')+'" alt="'+(c.name || '')+'">'+
								'</div>'+
								'<div class="category_card_details">'+
									'<h4 class="category_card_title">'+(c.name || '')+'</h4>'+
									'<span class="category_card_action">Explore →</span>'+
								'</div>'+
							'</a>'+
						'</div>'+
					'</div>';
				}
				$('#search_result').html(cdiv);
				$('#no_data').html('');
				$('#pagination').html('');
			}
			else if(result)
			{
				var div = '';
				var i='';
				for(i in result)
				{
                	var row = result[i];
                    var str = row.post_title
					var title = '';
					//title = str.length < 44 ? row.post_title : str.substring(1, 40)+'...';
					title = row.post_title_trim;
					var percentage_off = row.percentage_off;

					var subtitle = row.store_name ? row.store_name : '';
					var pid = row.product_id ? row.product_id : (row.id ? row.id : '');
					var vid = row.vendor_id ? row.vendor_id : '';
					
					var avg = parseFloat(row.rating || row.avg_rating) || 0;
					var count = parseInt(row.review || row.review_count) || 0;
					var starsHtml = '<div class="review d-flex db-500 mb-2"><ul class="mb0 me-2">';
					for(var s=1; s<=5; s++) {
						starsHtml += '<li class="list-inline-item"><i class="' + (s <= avg ? 'fas' : 'far') + ' fa-star" style="color:#f5a623; font-size:12px;"></i></li>';
					}
					starsHtml += '</ul><div class="review_count" style="font-size:12px;">'+count+' review'+(count!==1?'s':'')+'</div></div>';

					if (current_view === 'list') {
						div +=
							'<div class="col-12">' +
								'<div class="shop_item bdr1 m--1 product-list-row">' +
									'<div class="thumb pb30">' +
										'<a href="'+row.url+'"><img src="'+row.featured_image+'" alt=""></a>' +
										'<div class="thumb_info">' +
											'<ul class="mb0">' +
												'<li><a href="javascript:void(0);" class="wishlist-toggle" data-product-id="'+pid+'"><i class="far fa-heart"></i></a></li>' +
												'<li><a href="'+row.url+'"><span class="flaticon-show"></span></a></li>' +
											'</ul>' +
										'</div>' +
										'<div class="shop_item_cart_btn d-grid">' +
											'<button type="button" class="btn btn-thm add-to-cart-btn" data-product_id="'+pid+'" data-vendor_id="'+vid+'" data-quantity="1" data-cart_endpoint="cart" data-product_type="'+(row.product_type || 'simple')+'" data-variation_modal="'+(row.product_type && row.product_type.toLowerCase() === 'variable' ? '1' : '0')+'">Add to Cart</button>' +
										'</div>' +
									'</div>' +
									'<div class="details">' +
										'<div class="sub_title">'+subtitle+'</div>' +
										'<div class="title"><a href="'+row.url+'">'+title+'</a></div>' +
										starsHtml +
										'<div class="si_footer">';
					} else {
						div +=
							'<div class="col-6 col-lg-4 col-xl-3 p0 pl15-520">' +
								'<div class="shop_item bdr1 m--1">' +
									'<div class="thumb pb30">' +
										'<a href="'+row.url+'"><img src="'+row.featured_image+'" alt=""></a>' +
										'<div class="thumb_info">' +
											'<ul class="mb0">' +
												'<li><a href="javascript:void(0);" class="wishlist-toggle" data-product-id="'+pid+'"><i class="far fa-heart"></i></a></li>' +
												'<li><a href="'+row.url+'"><span class="flaticon-show"></span></a></li>' +
											'</ul>' +
										'</div>' +
										'<div class="shop_item_cart_btn d-grid">' +
											'<button type="button" class="btn btn-thm add-to-cart-btn" data-product_id="'+pid+'" data-vendor_id="'+vid+'" data-quantity="1" data-cart_endpoint="cart" data-product_type="'+(row.product_type || 'simple')+'" data-variation_modal="'+(row.product_type && row.product_type.toLowerCase() === 'variable' ? '1' : '0')+'">Add to Cart</button>' +
										'</div>' +
									'</div>' +
									'<div class="details">' +
										'<div class="sub_title">'+subtitle+'</div>' +
										'<div class="title"><a href="'+row.url+'">'+title+'</a></div>' +
										starsHtml +
										'<div class="si_footer">';
					}
					if(row.discount)
					{
						div += '<div class="price">'+row.symbol+' '+parseInt(row.sale_price)+' <small><del>'+row.symbol+' '+parseInt(row.regular_price)+'</del></small></div>';
					}
					else
					{
						div += '<div class="price">'+row.symbol+' '+parseInt(row.regular_price)+'</div>';
					}
					div +=
										'</div>' +
									'</div>' +
								'</div>' +
							'</div>' +
						'</div>';
				}

				if (div) {
					$('#search_result').html(div);
					$('#no_data').html('');
					if (typeof window.__applyWishlistToDom === 'function') {
						window.__applyWishlistToDom();
					}
				} else {
					$('#search_result').html('');
					$('#no_data').html('<div class="col-12 text-center p-5"><img src="/assets/default_images/noorder.png" class="empty-image mb-2"> <h2 class="text-emptycart mb-3">No products found</h2></div>');
				}
				$('#pagination').html(formatPaginationToDesign(data.pagination));
                //$("html, body").animate({scrollTop:$(".product-left-title").offset().top - 15},1000);
                $("html, body").animate({ scrollTop: 0 }, "slow");
		}	
		}
		});		
	}
	// Make AJAX loader callable from delegated handlers defined outside this closure
	window.get_data = get_data;
	window.resetPriceFilter = resetPriceFilter;

	function formatPaginationToDesign(html){
	var src = (html || '').toString();
	if (!src) return '';
	var $tmp = $('<div/>').html(src);
	var $ul = $tmp.find('ul.pagination').first();
	if (!$ul.length) {
		return src;
	}
	$ul.removeClass('pagination justify-content-center').addClass('page_navigation');
	$tmp.find('li.page-item').each(function(){
		var $li = $(this);
		$li.removeClass('page-item').addClass('page-item');
		var $a = $li.find('a').first();
		if ($a.length) {
			$a.removeClass('page-link').addClass('page-link');
		}
	});
	return '<div class="mbp_pagination mt30 text-center"><ul class="page_navigation">' + $ul.html() + '</ul></div>';
	}

	$(document).on('click', '.per-page', function(e){
		e.preventDefault();
		var len = parseInt($(this).data('length'), 10);
		if (isNaN(len)) {
			len = 0;
		}
		page_length = len;
		get_data(1);
	});

	$(document).on('click', '.toggle-view', function(e){
		e.preventDefault();
		var v = ($(this).data('view') || '').toString();
		if (v !== 'list' && v !== 'grid') {
			return;
		}
		if (v === current_view) {
			return;
		}
		if (v === 'list') {
			if (!last_grid_length) {
				last_grid_length = page_length;
			}
			current_view = 'list';
			page_length = 10;
		} else {
			current_view = 'grid';
			if (last_grid_length !== null && typeof last_grid_length !== 'undefined' && parseInt(last_grid_length || 0, 10) >= 0) {
				page_length = last_grid_length;
			}
		}
		try { window.localStorage.setItem('product_list_view', current_view); } catch(e) {}
		$('.toggle-view').removeClass('active');
		$('.toggle-view[data-view="'+current_view+'"]').addClass('active');
		get_data(1);
	});

	$(document).on('changed.bs.select', '#sort_select', function(){
		var v = ($(this).val() || '').toString();
		$('#price_sort_type').val(v);
		get_data(1);
	});

	$(document).on('change', '.amount, .amount2', function(){
		updatePriceRangeLabel();
	});
	$(document).on('blur', '.amount, .amount2', function(){
		updatePriceRangeLabel();
	});
	$(document).on('click', '#price_apply_btn', function(){
		updatePriceRangeLabel();
		_priceFilterActive = true;
		get_data(1);
	});
	$(document).on('slidestop', '.slider-range', function(){
		updatePriceRangeLabel();
		_priceFilterActive = true;
		get_data(1);
	});
	$(document).on('slidechange', '.slider-range', function(){
		updatePriceRangeLabel();
	});

	$('.uil-refresh').on('click', function() {
		$("input:checkbox").prop('checked', false);
		get_data(1);
	});

	function getBaseUrl() {
		var u = (typeof base_url !== 'undefined' && base_url) ? base_url : '/';
		if (u.charAt(u.length - 1) !== '/') {
			u += '/';
		}
		return u;
	}

	function isLoggedIn(){
		if (typeof login_id !== 'undefined' && login_id !== '' && login_id !== '0') {
			return true;
		}
		return false;
	}

	function setHeartActive($el, active){
		if(!$el || !$el.length) return;
		$el.toggleClass('active', !!active);
		var $icon = $el.find('i.fa-heart');
		if($icon.length){
			if(active){
				$icon.removeClass('far').addClass('fas').css('color', '#dc3545');
			} else {
				$icon.removeClass('fas').addClass('far').css('color', '');
			}
		}
	}

	window.__applyWishlistToDom = function(){
		if(window.__wishlistIdsMap){
			$('.wishlist-toggle').each(function(){
				var pid = $(this).data('product-id');
				setHeartActive($(this), !!window.__wishlistIdsMap[pid]);
			});
		}
	};

	function loadWishlistIds(){
		if(!isLoggedIn()) return;
		$.ajax({
			url: getBaseUrl() + 'customer/wishlist/ids',
			type: 'GET',
			dataType: 'json',
			success: function(resp){
				var map = {};
				if(resp.status === 1 && Array.isArray(resp.data)){
					resp.data.forEach(function(id){
						map[String(id)] = true;
					});
				}
				window.__wishlistIdsMap = map;
				window.__applyWishlistToDom();
			}
		});
	}

	function toggleWishlist($btn){
		if(!isLoggedIn()){
			window.location.href = getBaseUrl() + 'login';
			return;
		}
		var productId = $btn.data('product-id');
		if(!productId) return;
		var postData = { product_id: productId };
		ensure_csrf_object();
		if (window.csrf && window.csrf.name && window.csrf.hash) {
			postData[window.csrf.name] = window.csrf.hash;
		}
		$.ajax({
			url: getBaseUrl() + 'customer/wishlist/toggle',
			type: 'POST',
			dataType: 'json',
			data: postData,
			success: function(resp){
				var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
				var DATA = (typeof resp.DATA !== 'undefined') ? resp.DATA : resp.data;
				if(STATUS === 1 && DATA && typeof DATA.in_wishlist !== 'undefined'){
					var active = Number(DATA.in_wishlist) === 1;
					setHeartActive($btn, active);
					if (typeof window.__wishlistIdsMap === 'object' && window.__wishlistIdsMap !== null) {
						var key = String(productId);
						if (active) {
							window.__wishlistIdsMap[key] = true;
						} else {
							try { delete window.__wishlistIdsMap[key]; } catch(e) { window.__wishlistIdsMap[key] = false; }
						}
					}
				}
			}
		});
	}

	$(document).off('click.wishlistToggle', '.wishlist-toggle');
	$(document).on('click.wishlistToggle', '.wishlist-toggle', function(e){
		e.preventDefault();
		toggleWishlist($(this));
	});

	loadWishlistIds();

	function init_product_list_page(){
		ensure_csrf_object();
		try {
			var sv = window.localStorage.getItem('product_list_view');
			if (sv === 'list' || sv === 'grid') {
				current_view = sv;
			}
		} catch(e) {}
		$('.toggle-view').removeClass('active');
		$('.toggle-view[data-view="'+current_view+'"]').addClass('active');
		if (current_view === 'list') {
			page_length = 10;
		}
		if (!$('#price_sort_type').val()) {
			$('#price_sort_type').val(1);
		}
		var path = (window.location && window.location.pathname) ? window.location.pathname : '';
		var normPath = (path || '').toString();
		if (normPath.length > 1 && normPath.charAt(normPath.length - 1) === '/') {
			normPath = normPath.slice(0, -1);
		}
		if (normPath === '/products/all-categories') {
			show_categories = 1;
			if (typeof window.fts !== 'undefined') window.fts = '';
			fts = '';
			cat = 0;
			if (typeof window.cat_slug !== 'undefined') window.cat_slug = '';
			if (typeof cat_slug !== 'undefined') cat_slug = '';
			// Hide price filter and list/grid toggle on all-categories page
			$('body').addClass('all-categories-mode');
		} else {
			$('body').removeClass('all-categories-mode');
		}
		if ($('#sort_select').length) {
			var v = $('#price_sort_type').val();
			if (typeof v !== 'undefined' && v !== null && String(v) !== '') {
				$('#sort_select').val(String(v));
			}
			if (typeof $('#sort_select').selectpicker === 'function') {
				$('#sort_select').selectpicker('refresh');
			}
		}

		// Initialize/re-initialize the price slider with the correct range for the active currency
		try {
			if ($(".slider-range").data("ui-slider")) {
				$(".slider-range").slider("destroy");
			}
		} catch(e) {}

		var cur_rate = parseFloat(window.currentCurrencyRate) || 1.0;
		_sliderMax = Math.round(2000 * cur_rate);

		$(".slider-range").slider({
			range: true,
			min: 0,
			max: _sliderMax,
			values: [ 0, _sliderMax ],
			slide: function( event, ui ) {
				$( ".amount" ).val( ui.values[ 0 ] );
				$( ".amount2" ).val( ui.values[ 1 ] );
				updatePriceRangeLabel();
			},
			change: function( event, ui ) {
				updatePriceRangeLabel();
			}
		});

		// Kick off initial data load BEFORE populating inputs
		// so no price filter is applied on the first load
		get_data(1);

		// Set display values for the slider inputs AFTER firing the AJAX
		// (AJAX is async, so get_data has already captured empty inputs)
		$( ".amount" ).val( 0 );
		$( ".amount2" ).val( _sliderMax );
		updatePriceRangeLabel();
	}

	$(document).ready(function(){
		init_product_list_page();
	});
}

bootstrapProductList();

function get_cagegory(cat_data, root_parent){
	label ='';
    cat_div='';
    var j = '';

	if(cat_data)
	{
		var $root = $('#accordion-subcategory');
		cat_div = cat_data;	
		cat_div = cat_div ? cat_div : '';
		$root.html(cat_div);
		$root.find('.all-category-link').closest('li').remove();
		$root.prepend('<li class="mb-1"><div class="link"><a href="/products/all-categories" class="main_cat_css parent_list all-category-link">All Category</a></div></li>');
		get_expand();
		open2_refresh();

		// Always start with sidebar collapsed
		$root.children('li').removeClass('open');
		$root.children('li').children('ul').hide();
		$root.find('.link2').each(function(){
			$(this).removeClass('open2');
			var $next = $(this).next('ul');
			if ($next.length) {
				$next.hide();
			}
		});

		// Re-open only the active chain (main -> sub -> sub-sub)
		if (root_parent && root_parent.length) {
			var root_cat_id = root_parent[0] ? root_parent[0].category_id : '';
			var root_sub_cat_id = root_parent[1] && root_parent[1].category_id ? root_parent[1].category_id : '';
			var root_s_s_cat_id = root_parent[2] && root_parent[2].category_id ? root_parent[2].category_id : '';

			if (root_cat_id) {
				$root.find('.cat_'+root_cat_id).addClass('open');
				$root.find('.submenu_cat_'+root_cat_id).show();
			}
			if (root_sub_cat_id) {
				$root.find('.link2_'+root_sub_cat_id).parent().addClass('open2');
				$root.find('.link2_'+root_sub_cat_id).next('ul').show();
			}
			if (root_s_s_cat_id) {
				// no-op: leaf node, nothing to expand further
			}
		}

		// Active category indicator
		var path = (window.location && window.location.pathname) ? window.location.pathname : '';
		$root.find('a').removeClass('active-cat');
		var $byHref = path ? $root.find('a[href="'+path+'"]').first() : $();
		if ($byHref.length) {
			$byHref.first().addClass('active-cat');
		} else {
			var $active = $root.find('li.active a').first();
			if ($active.length) {
				$active.addClass('active-cat');
			}
		}
		if (show_categories === 1) {
			$root.find('a').removeClass('active-cat');
			$root.find('.all-category-link').first().addClass('active-cat');
		}
	}	

}

function get_brand(brand)
{
	label =''; brand_div='';
    var j = '';

    if(brand)
	{
        var j = '';
        for(j in brand)
		{
			var row = brand[j];
            var checked = '';
            if(row.checked)
			{
             	checked = 'checked';
            }
			brand_div += '<label class="custom_checkbox">'+row.name+
						  '<input type="checkbox" name="brand" class="search_click" id="'+row.brand_id+'" data-id="'+row.brand_id+'" value="'+row.brand_id+'" '+checked+'>'+
						  '<span class="checkmark"></span>'+
						'</label>';
		}
        brand_div = brand_div ? brand_div : ''; 
        label = brand_div ? label : '';
        
	}
   
	brand_div = brand_div ? brand_div : ''; 
    label = brand_div ? label : '';
	$('#brand_result').html(brand_div);
    //$('#search_brand_label').html(label);
}

// NOTE: brand filter handlers are bound in bindSidebarDelegates() to avoid '$ is not defined'

function get_price_type(priceTy)
{
	$('#price_sort_type').val(priceTy);
	get_data(1);
}

function get_expand()
{
	if (use_delegated_sidebar_accordion) {
		return;
	}
	var Accordion = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		
		var links = this.el.find('.link');
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
	}

	Accordion.prototype.dropdown = function(e) {
		var $el = e.data.el;
			$this = $(this),
			$next = $this.next();

		$next.slideToggle();
		$this.parent().toggleClass('open');

		if (!e.data.multiple) {
			$el.find('.submenu').not($next).slideUp().parent().removeClass('open');
		};
	}	

	var accordion = new Accordion($('#accordion-subcategory'), false);


}

function open2_refresh()
{
	if (use_delegated_sidebar_accordion) {
		return;
	}
	var Accordions = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		
		var links = this.el.find('.link2');
		
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
	}

	Accordions.prototype.dropdown = function(e) {
		var $el = e.data.el;
			$this = $(this),
			$next = $this.next();

		$next.slideToggle();
		$this.parent().toggleClass('open2');

		if (!e.data.multiple) {
			$el.find('.submenu2').not($next).slideUp().parent().removeClass('open2');
		};
	}	
	var accordions2 = new Accordions($('.accordion-subcategory-three'), false);

}

function bindSidebarDelegates(){
	if (typeof window.jQuery === 'undefined') {
		return false;
	}
	var $ = window.jQuery;
	$(document).off('click.productListSidebarLink', '.link');

	$(document).off('click.productListAjaxNav', '.side_categories a');
	$(document).on('click.productListAjaxNav', '.side_categories a', function(e){
		var $a = $(this);
		if ($a.hasClass('all-category-link')) {
			return;
		}
		var href = ($a.attr('href') || '').toString();
		if (!href || href === '#' || href.indexOf('javascript:') === 0) {
			return;
		}
		var m = href.match(/\/products\/category\/([^\/\?#]+)/i);
		if (!m || !m[1]) {
			return;
		}
		e.preventDefault();
		e.stopPropagation();
		if (typeof window.resetPriceFilter === 'function') {
			window.resetPriceFilter();
		}
		show_categories = 0;
		// Remove all-categories-mode when navigating to a specific category
		$('body').removeClass('all-categories-mode');
		cat = 0;
		if (typeof window.fts !== 'undefined') window.fts = '';
		fts = '';
		var slug = decodeURIComponent(m[1]);
		if (typeof window.cat_slug !== 'undefined') {
			window.cat_slug = slug;
		}
		if (typeof cat_slug !== 'undefined') {
			cat_slug = slug;
		}
		if (window.history && typeof window.history.pushState === 'function') {
			window.history.pushState({}, '', '/products/category/' + slug);
		}
		$('#accordion-subcategory a').removeClass('active-cat');
		$a.addClass('active-cat');
		if (typeof window.get_data === 'function') {
			window.get_data(1);
		}
		return false;
	});

	$(document).off('click.productListPlusIcon', '.side_categories .accordion-plusicon');
	$(document).on('click.productListPlusIcon', '.side_categories .accordion-plusicon', function(e){
		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();
		var $wrap = $(this).closest('div.link, div.link2');
		if (!$wrap.length) return false;
		var $next = $wrap.next('ul');
		if ($next.length) {
			$next.slideToggle();
			$wrap.parent().toggleClass($wrap.hasClass('link2') ? 'open2' : 'open');
		}
		return false;
	});

	$(document).off('click.productListSidebarCat', '.side_categories a');
	$(document).on('click.productListSidebarCat', '.side_categories a', function(){
		if ($(this).hasClass('all-category-link')) {
			show_categories = 1;
			return;
		}
		show_categories = 0;
		$('#accordion-subcategory a').removeClass('active-cat');
		$(this).addClass('active-cat');
	});

	$(document).off('click.productListAllCategory', '.all-category-link');
	$(document).on('click.productListAllCategory', '.all-category-link', function(e){
		e.preventDefault();
		e.stopPropagation();
		if (typeof window.resetPriceFilter === 'function') {
			window.resetPriceFilter();
		}
		show_categories = 1;
		$('#accordion-subcategory a').removeClass('active-cat');
		$(this).addClass('active-cat');
		if (window.history && typeof window.history.pushState === 'function') {
			window.history.pushState({}, '', '/products/all-categories');
		}
		if (typeof window.fts !== 'undefined') window.fts = '';
		fts = '';
		cat = 0;
		if (typeof window.cat_slug !== 'undefined') window.cat_slug = '';
		if (typeof cat_slug !== 'undefined') cat_slug = '';
		// Show all-categories-mode styles
		$('body').addClass('all-categories-mode');
		get_data(1);
		return false;
	});

	$(document).off('click.productListParentToggle', '.side_categories a.parent_list');

	$(document).off('click.productListBrandClick', '.search_click');
	$(document).on('click.productListBrandClick', '.search_click', function() {
		get_data(1);
	});

	$(document).off('keyup.productListBrandSearch', '#brand_search_input');
	$(document).on('keyup.productListBrandSearch', '#brand_search_input', function(){
		var q = ($(this).val() || '').toString().toLowerCase();
		$('#brand_result label.custom_checkbox').each(function(){
			var txt = ($(this).clone().children().remove().end().text() || '').toString().trim().toLowerCase();
			if (!q || txt.indexOf(q) > -1) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});

	return true;
}

(function ensureSidebarDelegatesBound(){
	if (bindSidebarDelegates()) {
		return;
	}
	var tries = 0;
	var t = setInterval(function(){
		tries++;
		if (bindSidebarDelegates() || tries > 100) {
			clearInterval(t);
		}
	}, 50);
})();