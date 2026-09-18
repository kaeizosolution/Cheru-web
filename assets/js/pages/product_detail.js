    var variations= {};
    var variations = variations;
    var variation_mapping = variation_mapping;
    $(document).on('ready', function() {
        $(".hp_slick").slick({
            dots: true,
            infinite: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000,
            margin:5,
            pauseOnHover: true,
            responsive: [
            {
                breakpoint: 1040,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
         }); 
       }); 
             
        $(document).on('hover', '.dropdown-menu', function (e) {
        e.stopPropagation();
    });
        
    setTimeout(() => { $('#myCarousel').carousel({
       interval: 4000
    }) }, 2500); 
   
    
    // handles the carousel thumbnails
    $('[id^=carousel-selector-]').click( function(){
         var id_selector = $(this).attr("id");
         var id = id_selector.substr(id_selector.length -1);
         id = parseInt(id);
         $('#bannerCarousel').carousel(id);
         $('[id^=carousel-selector-]').removeClass('selected');
         $(this).addClass('selected');
    });
    
    // when the carousel slides, auto update
    $('#myCarousel').on('slid', function (e) {
         var id = $('.item.active').data('slide-number');
         id = parseInt(id);
         $('[id^=carousel-selector-]').removeClass('selected');
         $('[id=carousel-selector-'+id+']').addClass('selected');
    }); 
    
    $(document).ready(function(){
      $('#addtocart').on('submit', function (e) { 
            e.preventDefault();
            var postData = '';
            postData[csrf.name]   = csrf.hash;
            postData.product_id = <?= isset($detail->product_id) ? $detail->product_id : 0 ?>;
            postData.vendor_id = <?= isset($detail->vendor_id) ? $detail->vendor_id : 0 ?>;
            if($('#quantity').val().trim())
            {
                postData.quantity = $('#quantity').val();
            }
            postData.action = 'add';

            var attribute_item_array = [];
            $(".attr_item:checked").each(function() {
                attribute_item_array.push($(this).val())
            });
            postData.attribute_item = JSON.stringify(attribute_item_array);
          $.ajax({
            type: 'POST',
            url:  "/<?= $TYPE ?>/cart/ajax_add_to_cart",
            data: postData,
            dataType:"json",
                beforeSend: function() {
                  //  $("#loading-image").show();
                },
            success: function (response) {
            if(response.status){
                        showToast(response.message, 'success');
                        window.location.reload();
                        //swal({text: response.message, timer: 2000, buttons: false});
            //  $('#cart_count').html(response.data[0].cart_count);
            }
                   // $("#loading-image").hide();
            },
                error: function (error) {
                  //  $("#loading-image").hide();
                }
          });
      });
    });

    $(".btn-checkout").on('click', function(e) {
        window.location.href = "/checkout";
    });

    $(()=>{
    // THIS FUNCTION USE FOR THE ADD WISHLIST PRODUCT
        $("#WishlistProduct").on('click', function(e) {

            var product_id = $(this).data('data');
            var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
            postData.product_id = product_id;
            var attribute_item_array = [];
            $(".attr_item:checked").each(function() {
                attribute_item_array.push($(this).val())
            });
            postData.attribute_item = JSON.stringify(attribute_item_array);
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: '/welcome/ajax_add_to_wishlist',
                data: postData,
                dataType:"json",
                success: function (response) {
                    showToast(response.message, 'success');      
                    //swal({text: response.message, timer: 2000, buttons: false});
                    if(response.status == '1'){
                        $('.like-icon').addClass('liked');
                    }else{
                        $('.like-icon').removeClass('liked');
                    }
/*
                    if(response.success == 0){
                        $('#msg').append(response.msg);
                    }else{
                        $('#msg').append(response.msg);
                    }
*/
                }
            });
        });
    });

    $("input[type=radio]").click(function() {
        var attribute_item_array = [];
        $(".attr_item:checked").each(function() {
            attribute_item_array.push($(this).val());
        });
        var current=$(this); 
        var attrlvl=$(this).attr('data-attrlvl'); 
        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.product_id = <?=$detail->product_id; ?>;
        postData.attribute_item_id = this.value;
        postData.attribute_item = JSON.stringify(attribute_item_array);
        $.ajax({
           type: 'POST',
           url:  "/<?= $TYPE ?>/product/ajax_product_variation",
           data: postData,
           dataType:"json",
           beforeSend: function() {
             //  $("#loading-image").show();
           },
           success: function (response) {
            if(response.status == 0)
            {
                return false;
            }
          if(response.status ){
                if(response.data[0].is_price_update && response.data[0].is_price_update == 0)
                {
                    return false;
                }
                var symbol = '<?= $symbol ?>';
                var iso_code = '<?= $iso_code ?>';
                var row = response.data[0];
                var variations = row.variations;
                var is_price_update = row.is_price_update;
                attributes_images(row);
                if(is_price_update){
                    $(".attr_item").each(function() {
                        $(this).parent().parent().removeClass('category_selected');
                        $(this).parent().parent().removeClass('category_disabled');
                        $(this).attr("checked",false);
                    });
                    for(var i in variations){
                        var attr_itemid = 'attr_itemid_'+i;
                        var cls_name = 'category_enabled';
                        if(variations[i] == 0){
                            cls_name = 'category_disabled';
                        }else if(variations[i] == 1){
                            cls_name = 'category_selected';
                            $('#'+attr_itemid).prop("checked", true);
                        }else if(variations[i] == 2){
                            cls_name = 'category_enabled';
                        }
                        $('#'+attr_itemid).parent().parent().addClass(cls_name);
                    }
                    var discount = row.discount;
                    var sale_price = row.sale_price;
                    var regular_price = row.regular_price;
                    var subtotal = row.subtotal;
                    var shipping = row.shipping;
                    var tax = row.tax;
                    if(discount){
                        //$('#regular_price').html(symbol+regular_price);
                        //$('#sale_price').html(symbol+sale_price);
                        //$('#subtotal').html(symbol+sale_price);
                        
                        var discount_html = '<div class="product-price" id="regular_price">'+symbol+' '+sale_price+'<span class="line-through-price">'+symbol+' '+regular_price+'</span> <span class="product-off">'+row.percentage_off+'</span></div>';
                        $('#discount_price_div').html(discount_html);
                    }else{
                        $('#regular_price').html(symbol+regular_price);
                        $('#subtotal').html(symbol+regular_price);
                    }
                    var price = 0;
                    if(discount){
                        price = parseFloat(sale_price);
                    }else{
                        price = parseFloat(regular_price);
                    }
                    var total = price;
                    if(!isNaN(shipping)){
                        total+= parseFloat(shipping);
                    }
                    if(!isNaN(tax)){
                        total+= parseFloat(tax);
                    } 
                    total = total.toFixed(2);
                    if(!isNaN(tax)){
                        $('#tax').html(symbol+tax);
                    }else{
                        $('#tax').html(tax);
                    }
                    $('#total').html(symbol+total);
                }else{
                    $(".term_item_"+attrlvl).parent().removeClass('category_selected').addClass('category');
                    $(current).parent().addClass("category_selected");
                }
          }
            
              // $("#loading-image").hide();
           },
           error: function (error) {
             //  $("#loading-image").hide();
           }
        });
    });

function attributes_images(args)
{
   
    var varimgs = args.variations_img;
    //console.log("varimgs== "+ JSON.stringify(varimgs));
    var sync1_js_html = '<div id="sync1" class="owl-carousel owl-theme">'; 
    var sync2_js_html='<div id="sync2" class="owl-carousel owl-theme">';
    $.each(varimgs, function (key, val) {
        var active_class = key == 0 ? 'ative' : '';
        sync1_js_html += '<div class="item '+active_class+'" data-slide-number="'+key+'" > <div class="big-product-wrap"><img src="'+val.url+'" alt="'+val.file_name+'" class="big-img-product"> </div></div>';
        sync2_js_html += '<div class="item item-thumb '+active_class+'" id="carousel-selector-'+key+'"> <img src="'+val.url+'"  alt="'+val.file_name+'" class="mini-img-product "> </div>';
    });
    sync1_js_html += '</div>';
    sync2_js_html += '</div>';
    $('.sync1_js').html(sync1_js_html);
    $('.sync2_js').html(sync2_js_html);
    reinitialsie_owl();     
}

function reinitialsie_owl()
{
    $("#sync2").owlCarousel();
    $("#sync1").owlCarousel({
        navigation : true,
        items: 1,
        loop: true,
        slideSpeed : 300,
        paginationSpeed : 400,
        nav: true,
        dots: false,
        autoplay: true,
        singleItem:true,
        navText: ["<i class='uil uil-angle-left'></i>", "<i class='uil uil-angle-right'></i>"]
    });
}

$('.quantity_update').on('click', function (e) {
    var id = $(this).attr('data-id');
    var txtBox = $(this).closest('.numbering').find('input[type=text]');
    var quantity = txtBox.val().trim();

    if(id == 'plus'){
        quantity++;
    }else if(id == 'minus'){
        if(quantity > 1){
            quantity--;
        }
    }
    txtBox.val(quantity);
});

$('.qty_update').on('keyup', function (e) {
    if($(this).val() == '' || $(this).val() == 0){
        $(this).val(1);
    }
});

$(".nav a").on("click", function(){
   $(".nav").find(".active").removeClass("active");
   $(this).parent().addClass("active");
});


      $(document).on('hover', '.dropdown-menu', function (e) {
    e.stopPropagation();
  });
       
       $(function() {
    $('.mob-menu').click(function() {
      $('#sidebar').toggleClass('visible');
    });
  });
       
       $(document).ready(function () {
  $("#accordion li > h4").click(function () {

    if ($(this).next().is(':visible')) {
      $(this).next().slideUp(300);
      $(this).children(".plusminus").text('+');
    } else {
      $(this).next("#accordion ul").slideDown(300);
      $(this).children(".plusminus").text('-');
    }
  });
});
