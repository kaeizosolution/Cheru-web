
<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<div class="content-body">
	<div class="container-fluid">
		<div class="row page-titles">
			<?= $breadcrumbs ?> 
		</div>
		<!-- row -->
	   <?php     //echo "<pre>"; print_r($order_detail); echo "</pre>"; 
	   
	  
		
	   ?>
		<div class="row">
			<div class="col-xl-12">
				<div class="card">
					<div class="card-body">
						<div class="profile-tab">
							<div class="custom-tab-1">
								
								<div class="profile-about-me">
											<div class="pt-4 border-bottom-1 pb-3">
											<h4 class="text-primary">#<?= $order_uid ?> - <?php echo date("d M, Y h.i A", strtotime($order_detail->date_created)); ?></h4>
												<h6>Client Information</h6>

<p class="mb-2">
													<?= $page_lang->name; ?>: </b> <?= isset($customer_detail->fname) ? $customer_detail->fname: '-';?>&nbsp;<?= isset($customer_detail->lname) ? $customer_detail->lname: ''; ?><br>
													
													<?= $page_lang->email; ?>: </b><?= isset($customer_detail->email) ? $customer_detail->email: '-'; ?><br>
													
													<?= $page_lang->address; ?>: </b><?= isset($customer_detail->address) ? $customer_detail->address: '-'; ?>
													<br>
													<?= $page_lang->mobile; ?>: </b><?= isset($customer_detail->mobile) ? $customer_detail->mobile : '-'; ?>
												</p>
												<hr>
												
												<h6>Shipping information</h6>
<p class="mb-2"><?= $page_lang->name; ?>: </b> <?= isset($shipping_address->fname) ? $shipping_address->fname : '-'; ?>&nbsp;<?= isset($shipping_address->lname) ? $shipping_address->lname : ''; ?><br>
												<?= $page_lang->mobile; ?>: </b><?= isset($shipping_address->mobile) ? $shipping_address->mobile : '-'; ?><br>
												<?= $page_lang->city; ?>: </b><?= isset($shipping_address->city) ? $shipping_address->city : '-'; ?><br>
												<?= $page_lang->street; ?>: </b><?= isset($shipping_address->street) ? $shipping_address->street : '-'; ?><br>
												Postcode: </b><?= isset($shipping_address->postcode) ? $shipping_address->postcode : '-'; ?><br>
												Country: </b> <?= isset($shipping_country_name) ? $shipping_country_name : '-';?></p>

<hr>
												
												<h6>Order</h6>
												<div class="row">												
													<div class="col-sm-6">Item	<hr></div>
													<div class="col-sm-2">Qty	<hr></div>
													<div class="col-sm-2">Price	<hr></div>
													<div class="col-sm-2">Total	<hr></div>
												</div>	
												<div class="row">
												<?php foreach($order_info as $k=>$value){ ?>
													<div class="col-sm-6"><?php echo $value->product_name?>	</div>
													<div class="col-sm-2"><?php echo $value->quantity?></div>
													<div class="col-sm-2"><?php echo $value->unit_price?>	</div>
													<div class="col-sm-2"><?php echo $value->currency_symbol.$value->gtotal ?>	</div>
												<?php } ?>
												</div>
	
												<hr>
												<h6>Payment Method</h6>
												<p class="mb-2"><?= isset($order_detail->payment_mode) ? $order_detail->payment_mode: '';?></p>												
												
												<hr>
												<h6>Delivery method</h6>
												<p class="mb-2">
							<?php //echo $order_detail->status;?>
<?= order_status($order_detail->status); ?>
												</p>
																								
											   
											</div>
										</div>
										
								
							</div>
							<!-- Modal -->
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--**********************************
	Content body end
***********************************-->
