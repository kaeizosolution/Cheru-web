<?php $page_lang = get_page_language_data('my_account_lang'); ?>
<section>
    <img src="/assets/frontend/images/p_banner.jpg" class="img-fluid w-100" alt="">
</section>
<section>
    <div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb text-uppercase">
            <li class="breadcrumb-item"><a href="<?= base_url();?>"><?=$page_lang->home; ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?=$page_lang->my_account; ?></li>
        </ol>
    </nav>
    <div class="row">
        <?php include('profile_left_menu.php');?>
        <div class="col-md-9">
            <div class=" card border ac-detail">
                <h3><?= $page_lang->my_voucher_credit; ?></h3>
                <hr>
                <div class="row" id="CustomerVoucher">
                </div>
            </div>
        </div>
    </div>
</section> 
<script type="text/javascript">
$(document).ready(function(){

 //THIS FUNCTION USE FOR WISHLIST PRODUCT LISTING
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var request = $.ajax({
      url: "/<?= $TYPE; ?>/profile/ajax-get-customer-voucher",
      type: "POST",
      data: postData,
      dataType: "json"
    }); 

    request.done(function(response) {
        var customer_voucher_html = '';
        if(response.status == '1'){
            var data = response.data;
            var COPY_CODE = 'COPY CODE';
            $.each(data, function(key, row) {
                customer_voucher_html+='<div class="col-md-4">'+
							'<div class="ac-box h-a gift">'+
							'<div class="cp">'+row.name+'<span></span>'+'</div>'+
							'<h5>'+
							'<img src="/assets/frontend/images/g-card.png">'+row.discount+
							'</h5>'+
							'<p class="text-right">Expire on '+row.end_date+'</p>'+
					'</div>'+
				'</div>';
            });

        customer_voucher_html = customer_voucher_html ? customer_voucher_html : '<div class="container small-p mb-5"><?=$page_lang->there_is_no_information_to_display;?><div>';

        $('#CustomerVoucher').html(customer_voucher_html);

        }
    });
});
</script>
