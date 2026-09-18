<?php $page_lang = get_page_language_data('my_account_lang'); ?> 
<section>
    <img src="/assets/frontend/images/p_banner.jpg" class="img-fluid w-100" alt="">
  </section>

<section>
  <div class="container">
        
<nav aria-label="breadcrumb">
  <ol class="breadcrumb text-uppercase">
    <li class="breadcrumb-item"><a href="<?= base_url();?>"><?= $page_lang->home; ?></a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $page_lang->my_account; ?></li>
  </ol>
</nav>
    <div class="row">
    <?php include('profile_left_menu.php');?>

    <div class="col-md-9">
        <div class=" card border ac-detail">
            <h3><?= $page_lang->my_wishlist_items ?></h3>
            <hr>
            <div id="msg"></div>                            
            <div class="row m-1">
                <div class="col-md-12" id="WishlistData">
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
</section>    
<script type="text/javascript">

$(document).ready(function(){
    get_wishlist_product();
    wishlist_remove();
});

function get_wishlist_product(){
 //THIS FUNCTION USE FOR WISHLIST PRODUCT LISTING
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var request = $.ajax({
      url: "/<?= $TYPE; ?>/profile/ajax-get-wishlist-product",
      type: "POST",
      data: postData,
      dataType: "json"
    }); 

    request.done(function(response) {
        var Wishlist_html = '';
        if(response.status == '1'){
            var data = response.data;
            var buy     ='Add to cart';
            var Remove  ='Remove';

            $.each(data, function(key, row) {
                Wishlist_html+='<div class="ac-box border row h-a">'+
                    '<div class="col-sm-2 p-0">'+
                        '<a href="'+row.url+'" target="_blank" class="cartImg"><img src="'+row.productimg+'" alt="" class="img-fluid"></a>'+
                    '</div>'+
                    '<div class="col-sm-6 p-0">'+
                        '<h2><a href="'+row.url+'" target="_blank" class="unset">'+row.productname+'</a></h2>';
                        if(row.attribute_items){
                            for(i in row.attribute_items){
                Wishlist_html+='<span class="badge badge-secondary">'+row.attribute_items[i]+'</span>&nbsp;';
                            }
                        }
                    '</div>'+
                    '<div class="col-sm-1 price text-center p-0">';
                    if(row.discount){
        Wishlist_html+='<p>'+row.symbol+row.sale_price+'<del>'+row.symbol+row.regular_price+'</del></p>';
                    }else{
        Wishlist_html+='<p>'+row.symbol+row.regular_price+'</p>';
                    }

    Wishlist_html+='</div>'+
                    '<div class="col-sm-3 p-0 pl-2 gree">'+
                       '<button class="btn buy-now addtocart" data-wishlist_id="'+row.wishlist_id+'" data-product_id="'+row.product_id+'" data-attribute_item_id="'+row.attribute_item_id+'">'+buy+'</button>'+
                    '<button class="btn remove" id="'+row.wishlist_id+'"><i class="fa  fa-trash-o"></i> '+Remove+'</button>'+
                    '</div>'+
                '</div>';
            });

        Wishlist_html = Wishlist_html ? Wishlist_html : '<div class="container small-p mb-5"><?=$page_lang->there_is_no_information_to_display;?></div>';

        $('#WishlistData').html(Wishlist_html);

        }
    });
}

    function wishlist_remove(){
    // Remove product from Wishlist
    $('#WishlistData').on('click', '.remove', function (e) {
        var id = $(this).attr('id');
        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.id = id;
        swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                 $.ajax( {
                     url:'/<?= $TYPE; ?>/profile/ajax_remove_wishlist_product',
                     type:'post',
                     dataType:"json",
                     data: postData,
                     success:function(data) {
                        $('#msg').show();
                        get_wishlist_product();
                        $('#msg').addClass('alert alert-success');
                        $('#msg').html(data.message);
                        setTimeout(function() {
                            $('#msg').hide();
                        }, 1000);
                     }
                 });
            }
        });
    });

}

$(document).on('click','.addtocart', function(e){
    e.preventDefault();
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    postData.product_id = $(this).attr('data-product_id');
    postData.quantity = 1;
    postData.action = 'add';

    var attribute_item_id = $(this).attr('data-attribute_item_id');
    console.log(attribute_item_id);
    var attribute_item_array = [];
    if(attribute_item_id){
        attribute_item_array = attribute_item_id.split(',');
    }
    console.log(attribute_item_array);
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
                window.location.reload();
                swal({text: response.message, timer: 2000, buttons: false});
            //  $('#cart_count').html(response.data[0].cart_count);
            }
           // $("#loading-image").hide();
        },
        error: function (error) {
          //  $("#loading-image").hide();
        }
    });
});
</script>
