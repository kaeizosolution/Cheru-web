<section>
  <img src="/assets/frontend/images/p_banner.jpg" class="img-fluid w-100" alt="">
</section>

  <section>
	  
<div class="container">
		
<nav aria-label="breadcrumb">
  <ol class="breadcrumb text-uppercase">
    <li class="breadcrumb-item"><a href="<?= base_url();?>"><?= $page_lang->home;?></a></li>
    <li class="breadcrumb-item"><a href="<?=base_url()?>customer/profile/customer-order"><?= $page_lang->my_orders;?></a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $page_lang->order_tracking;?></li>
  </ol>
</nav>
			 
	<div class="container tracking">
    <article class="card">
        <header class="card-header"><?= $page_lang->my_order_tracking;?> </header>
        <div class="card-body">
        	<div id="OrderTrack">
            
           </div>
            <hr>
            <a href="<?=base_url()?>customer/profile/customer-order" class="btn btn-primary" data-abc="true"> <i class="fa fa-chevron-left"></i><?= $page_lang->back_to_order;?></a>
        </div>
    </article>
</div>
</div>
</section>	  
<script type="text/javascript">
$(document).ready(function() {

  //THIS FUNCTION USE FOR TRACKING ORDER
  var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
  postData.order_item_uid = "<?=$order_item_uid ?>";
  postData.order_uid      = "<?=$order_uid ?>";
  var request = $.ajax({
    url: "/<?= $TYPE; ?>/profile/ajax-get-track-package",
    type: "POST",
    data: postData,
    dataType: "json"
  }); 

  var tracking_status = { 1 : 'Order Pending', 2: 'Order Approved', 3: 'Order Shipped', 4 : 'Order Delivered'};

  request.done(function(response) {
        var OrderTrack_html = '';
        if(response.status == '1'){
            var data = response.data; 
            var OrderTrack_html='<h6 class="mb-2"><strong>'+data[0].page_lang.orderid+'</strong>: '+data[0].order_uid+'</h6>'+
            '<article class="card">'+
                '<div class="card-body row">'+
                    '<div class="col"> <strong>'+data[0].page_lang.delivery_time+': </strong>'+data[0].delivery_date+' </div>'+
                    //'<div class="col"> <strong>Shipping BY:</strong> <br> BLUEDART, | <i class="fa fa-phone"></i> +1598675986 </div>'+
                    '<div class="col"> <strong>'+data[0].page_lang.status+':</strong> '+data[0].orderstatus+'</div>'+
                    '<div class="col"> <strong>'+data[0].page_lang.orderid+' #:</strong>  '+data[0].order_uid+' </div>'+
                '</div>'+
            '</article>'+
            '<div class="track">';
            
            var count = data[0].orderstatus_id;
            
            if(count == 1){
               OrderTrack_html+='<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_pending+'</span> </div>'+
               '<div class="step"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_approved+'</span> </div>'+
               '<div class="step"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_shipped+'</span> </div>'+
               '<div class="step"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_delivered+'</span> </div>';

            }else if(count == 2){
               OrderTrack_html+='<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_pending+'</span> </div>'+
               '<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_approved+'</span> </div>'+
               '<div class="step"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_shipped+'</span> </div>'+
               '<div class="step"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_delivered+'</span> </div>';
            }else if(count == 4){
                OrderTrack_html+='<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_pending+'</span> </div>'+
               '<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_approved+'</span> </div>'+
               '<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_shipped+'</span> </div>'+
               '<div class="step"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_delivered+'</span> </div>';
            }else if(count == 5){
            OrderTrack_html+='<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_pending+'</span> </div>'+
               '<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_approved+'</span> </div>'+
               '<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_shipped+'</span> </div>'+
               '<div class="step active"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+data[0].page_lang.order_delivered+'</span> </div>';
            }

            /*$.each(tracking_status, function(key, value){
                  OrderTrack_html+='<div class="step"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">'+value+'</span> </div>';
              });*/

            OrderTrack_html+='</div>'+
            '<hr>'+
            '<ul class="row">';
         $.each(data, function(key, row) {
            var attribute_name = '';
            if(row.attribute_item){
                for(i in row.attribute_item){
                    attribute_name+='<span class="badge badge-secondary">'+row.attribute_item[i]+'</span>&nbsp;&nbsp;';
                }
            }

            OrderTrack_html+=
                '<li class="col-md-4">'+
                    '<figure class="itemside mb-3">'+
                        '<div class="aside"><a href="'+row.url+'"><img src="'+row.productimg+'" class="img-sm border"></a></div>'+
                        '<figcaption class="info align-self-center">'+
                            '<p class="title"><a href="'+row.url+'">'+row.productname+'</a></p> '+attribute_name+'<p><span class="text-muted">'+row.page_lang.price+': '+row.symbol+' '+row.net_price+' </span></p> <p><span class="text-muted">'+row.page_lang.taxlan+': '+row.symbol+' '+row.tax+' </span></p> <p><span class="text-muted">'+row.page_lang.shippinglan+': '+row.symbol+' '+row.shipping+' </span></p><p><span class="text-muted">'+row.page_lang.gross_price+': '+row.symbol+' '+row.price+' </span></p>'+
                        '</figcaption>'+
                    '</figure>'+
                '</li>';
        	});
        OrderTrack_html+='</ul>';

        OrderTrack_html = OrderTrack_html ? OrderTrack_html : '<div class="container small-p mb-5">There is no information to display.</div>';

        $('#OrderTrack').html(OrderTrack_html);

        }

      });

}); 

</script>
