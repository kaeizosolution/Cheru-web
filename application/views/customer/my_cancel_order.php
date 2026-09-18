<section>
  	<img src="/assets/frontend/images/p_banner.jpg" class="img-fluid w-100" alt="">
  </section>

  <section>
  
  <div class="container">
		
<nav aria-label="breadcrumb">
  <ol class="breadcrumb text-uppercase">
    <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">My Account</li>
  </ol>
</nav>
			 
	<div class="row">

	<?php include('profile_left_menu.php');?>
	
	<div class="col-md-9">
		<div class=" card border ac-detail">
			<h3><?= $my_orders; ?></h3>
			
			<div class="tabbable-panel">
				<div class="tabbable-line">
					<ul class="nav nav-tabs ">
						<li class="active">
							<a href="#tab_default_1" data-toggle="tab">
							<?= $cancel_order;?></a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tab_default_1">
							<form id="CancelOrder" name="cancel_order">
							  <input type="hidden" name="order_uid" value="<?= isset($order_uid) ? $order_uid : ''; ?>" />
							  <input type="hidden" name="order_item_uid" value="<?= isset($order_item_uid) ? $order_item_uid : ''; ?>" />
							  <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
							<div class="row m-1">
								<div class="col-md-12">
									<div class="row order-step">
										<div class="col-md-4">
											<h5><?= $order_placed;?><br><span><?= $date_created; ?></span></h5>
										</div>
										<div class="col-md-4">
											<h5><?= $total;?><br><?= $symbol.' '.$totalprice; ?></h5>
										</div>
										<!--<div class="col-md-3">
											<h5>Ship To<br><span>S.R.Verma <i class="fa fa-chevron-down" aria-hidden="true"></i></span></h5>
										</div>-->
										<div class="col-md-4 text-right">
											<h5><?= $orderid;?> # <?= $order_uid; ?></h5>
										</div>
									</div>
									<div class="ac-box row h-a">	
										<h4><span><?= $cancel_this_order; ?> </span></h4>
										<div class="col-sm-4 pt-1 pb-1 pl-0">	
											<h4><?= $reason_cancellation; ?>:</h4>
										</div>
										<div class="col-sm-8 pl-0 pr-0 text-right">
										<select class="form-control" name="cancel_order_reason" id="CancelOrder" required >
									        <option value=""><?= $select_reason; ?></option>
                                            <option value="<?= $changed_my_mind; ?>"><?= $changed_my_mind; ?></option>
                                            <option value="<?= $change_my_phone_number; ?>"><?= $change_my_phone_number; ?></option>
                                            <option value="<?= $change_address_for_the_order; ?>"><?= $change_address_for_the_order; ?></option>
                                            <option value="<?= $convert_my_order; ?>"><?= $convert_my_order; ?></option>
                                            <option value="<?= $product_has_decreased; ?>"><?= $product_has_decreased; ?></option>
                                            <option value="<?= $time_is_very_long; ?>"><?= $time_is_very_long; ?></option>
                                            <option value="<?= $product_quality_issues; ?>"><?= $product_quality_issues; ?></option>
                                            <option value="<?= $the_product_elsewhere; ?>"><?= $the_product_elsewhere; ?></option>	
                                            <option value="<?= $other; ?>"><?= $other; ?></option>	
                                        </select>
									</div>
										<div class="form-group w-100">
											<textarea class="form-control" placeholder="<?= $reason_cancellation; ?>" name="comment" required></textarea>
										</div>
										<div class="col-sm-6 p-0">
											<h2><a href="<?= $prourl;?>" target="_blank" class="unset"><?= $product_name; ?></a></h2>
                                            <?php if($attribute_item){ foreach($attribute_item as $val){ ?>
                                                <span class="badge badge-secondary"><?= $val ?></span>&nbsp;&nbsp;
                                            <?php }} ?>
											<p><?= $page_lang->price.': '.$symbol.' '.$proprice; ?></p>
											<p><?= $taxlan .': '.$symbol.' '.$tax;?></p>
											<p><?= $shippinglan.': '.$symbol.' '.$shipping;?></p>
											<p><?= $page_lang->gross_price.': '.$symbol.' '.$totalprice;?></p>
                                            <br/>
											<a href="<?= $prourl;?>" target="_blank" class="cartImg"><img src="<?= $product_image; ?>" alt="" width="100" height="150" /></a>
										</div>
										<div class="col-sm-6 p-0 pl-2 gree">
											<button class="btncancel" type="submit"><?= $can_order; ?></button>
											<!--<a href="javascript:void(0);" class="btnGrey">Return to order summary</a>-->
										</div>
									</div>
								</div>
							</div>
						</form>

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	</div>
</div>
</section>	  


<script type="text/javascript">

$(document).on('ready', function() {
$('#CancelOrder').on('submit', function (e) {
    e.preventDefault();
      $.ajax({
        type: 'POST',
        url: '/customer/profile/ajax_cancel_product',
        data: $('#CancelOrder').serialize(),
        dataType:"json",
        success: function (response) {          
                if(response.status == '1'){
                    $('#SubmitCancelOrder').html(response.message);
                    location.href = '<?=base_url();?>customer/profile/customer-order';
                    //$('#SubmitShippingFrom').show();
                }else{
                    //$('#SubmitShippingFrom').show();
                    $('#SubmitCancelOrder').html(response.message);
                    //location.href = '<?=base_url();?>customer/profile/shipping-address';
                }
            }
        });
    });

 });

</script>	
