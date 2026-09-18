<!DOCTYPE html><html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="universal admin is super flexible, powerful, clean & modern responsive bootstrap 4 admin template with unlimited possibilities.">
	<meta name="keywords" content="admin template, universal admin template, dashboard template, flat admin template, responsive admin template, web app">
	<meta name="author" content="pixelstrap">
	<link rel="icon" href="/assets/images/favicon.png" type="image/x-icon"/>
	<link rel="shortcut icon" href="/assets/images/favicon.png" type="image/x-icon"/>
	<title>klentano - Premium Admin Template</title>
	<!--Google font-->
	<?php /* ?><link href="https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700" rel="stylesheet"><?php */ ?>
	<?php /* ?><link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800" rel="stylesheet"><?php */ ?>
	<!-- Font Awesome -->
	<link rel="stylesheet" type="text/css" href="/assets/css/fontawesome.css">
	<!-- ico-font -->
	<link rel="stylesheet" type="text/css" href="/assets/css/icofont.css">
	<!-- Themify icon -->
	<link rel="stylesheet" type="text/css" href="/assets/css/themify.css">
	<!-- Flag icon -->
	<?php /* ?><link rel="stylesheet" type="text/css" href="/assets/css/flag-icon.css"><?php */ ?>
	<!-- prism css -->
	<?php /* ?><link rel="stylesheet" type="text/css" href="/assets/css/prism.css"><?php */ ?>
	<!-- Bootstrap css -->
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/animate.css">
	<!-- SVG icon css -->
	<?php /* ?><link rel="stylesheet" type="text/css" href="/assets/css/whether-icon.css"><?php */ ?>
	<!-- Chartist -->
	<?php /* ?><link rel="stylesheet" type="text/css" href="/assets/css/chartist.css"><<?php */ ?>
	<!-- App css -->
	<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
	<!-- Responsive css -->
	<link rel="stylesheet" type="text/css" href="/assets/css/responsive.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/custom_dashboard.css">
</head>
<div class="page-body"><div class="container-fluid"><div class="page-header">
<div class="row"><div class="col-lg-6"><h3>Dashboard</h3></div><?= $breadcrumbs ?>
</div></div></div>
<!-- Container-fluid Ends -->
<!-- Container-fluid starts -->
<?php 
if ($this->uri->segment(4) === FALSE){
	$arg = 0;
}else{
	$arg = $this->uri->segment(4);
	
}	

$staus =''; $customdate_staus='';$today_staus='';$week_staus='';$annual_staus='';$month_staus='';

if($arg =='customdate'){
	$customdate_staus ='active'; 
}elseif($arg =='today'){
	$today_staus ='active'; 
}elseif($arg =='week'){
	$week_staus ='active'; 
}elseif($arg =='month'){
	$month_staus ='active'; 
}elseif($arg =='annual'){
	$annual_staus ='active'; 
}else{
	$staus ='active'; 
}


?>
<div class="container-fluid">
<div class="row m-0">
<div class="card border-widgets col-xl-6">
<ul class="row p-10 report_filter" id="report_filter">

	<li class="col-xl-2 <?php echo $today_staus ?>"><a id="day" href="/admin/dashboard/index/today">Today</a></li>
	<li class="col-xl-2 <?php echo $week_staus ?>"><a id="week" href="/admin/dashboard/index/week">Week</a></li>
	<li class="col-xl-2 <?php echo $month_staus; echo $staus; ?>"><a id="month" href="/admin/dashboard/index/month">Month</a></li>
	<li class="col-xl-2 <?php echo $annual_staus ?>"><a  id="month" href="/admin/dashboard/index/annual">Annual</a></li>
	<li class="col-xl-4 <?php echo $customdate_staus ?>"><a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="1btn 1btn-primary" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i> Custom Date</a></li>
</ul>
</div>

</div></div>

<div class="container-fluid"><div class="card border-widgets">
<div class="row m-0"><div class="col-xl-3 col-6 xs-width-100">
<div class="crm-top-widget card-body">
	<div class="media">
		<i class="icon-user font-primary align-self-center mr-3"></i>
		<div class="media-body">
			<?php $count_all_visiter = isset($count_all_visiter) ? $count_all_visiter:'-'; ?>  
			<span class="mt-0">ORDER</span>
			<h4 class="counter"><?php echo $monthly = (isset($orders->total)) ? $orders->total:0;   ?></h4>
		</div>
	</div>
</div>
</div>
<div class="col-xl-3 col-6 xs-width-100">
<div class="crm-top-widget card-body">
	<div class="media">
		<i class="icon-email font-secondary align-self-center mr-3"></i>
		<div class="media-body">
		<?php $count_all_subscribe= isset($count_all_subscribe) ? $count_all_subscribe:'-'; ?> 
			<span class="mt-0">INCOME</span>
			<h4 class="counter"><?php echo $subtotal = isset($subtotal->total) ? $subtotal->total:0.00; ?></h4>
		</div>
	</div>
</div>
</div>
<div class="col-xl-3 col-6 xs-width-100">
<div class="crm-top-widget card-body">
	<div class="media">
		<i class="icon-package font-success align-self-center mr-3"></i>
		<div class="media-body">
			<span class="mt-0">Products</span>
			<h4 class="counter"><?php 
			if( isset($new_product) && !empty($new_product) ){
				echo $new_product = isset($new_product->total) ? $new_product->total:0;
			}
			?></h4>
		</div>
	</div>
</div>
</div>
<div class="col-xl-3 col-6 xs-width-100">
	<div class="crm-top-widget card-body">
		<div class="media">
			<i class="icon-direction-alt font-info align-self-center mr-3"></i>
			<div class="media-body">		
				<span class="mt-0">Users</span>
				<h4 class="counter"><?php 
			
				if( isset($users_report) && !empty($users_report) ){
					echo $users = isset($users_report->total) ? $users_report->total:0;	
				}
				?></h4>
			</div>
		</div>
	</div>
</div>
</div></div>

<div class="row"><div class="col-md-12"><div class="card">
<div class="card-header"><h5 class="m-b-0">Summary </h5></div>
<div class="card-body ">
<div class="row">
	<div class="col-sm-6 text-center p-15"><h5><?php echo $total_product_qty = isset($total_product_qty) ? $total_product_qty:0; ?><br><small>Products</small></h5></div>
	<div class="col-sm-6 text-center p-15">	<h5><?php echo $sale_amount = isset($sale_amount) ? $sale_amount:0; ?><br><small>Sale </small></h5></div>
	
</div>
<canvas id="myChart" width="400" height="100"></canvas>
</div>
</div>
</div>


</div>
					
					
					
<div class="row">

<div class="container-fluid">

<div class="row">

<div class="col-lg-4 col-sm-12 col-12">
	<div class="card">
		<div class="card-header">
			<h5 class="m-b-0">Order Activity </h5>
		</div>
		<div class="card-body act-box">
			<ul>
			
			<li><i class="fa fa-map-marker icon1"></i><h6>Pending</h6><span><?php echo $pending_order; ?></span> </li>
			<li><i class="fa fa-shopping-cart icon2"></i><h6>Approved</h6><span><?php echo $approved_order; ?></span> </li>
			<li><i class="fa fa-file icon3"></i><h6>Cancelled</h6><span><?php echo $cancelled_order; ?></span> </li>
			<li><i class="fa fa-trash icon4"></i><h6>Shipped</h6><span><?php echo $shipped_order; ?></span> </li>
			<li><i class="fa fa-shield icon5"></i><h6>Delivered</h6><span><?php echo $delivered_order; ?></span> </li>
								
								
								
			</ul>
		</div>
	</div>
</div>

<div class="col-lg-4 col-sm-12 col-12">
	<div class="card">
		<div class="card-header">
			<h5 class="m-b-0">Recent Products</h5>
		</div>
		
		<div class="card-body pb-0 act-box">
			
			<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
				<div class="carousel-inner">
					
					<?php

					$type_status = array(1 => 'active', 0 => 'inactive');
					if(isset($recent_product)){
					$i=0;	
					foreach($recent_product as $k=>$val){
						$price = '';
						if(isset($val->sale_price) && !empty($val->sale_price)){
							$price = $val->sale_price;
						}else{
							$price = $val->regular_price;
						}
						
						if (array_key_exists($val->enabled, $type_status)) {
							$status= $type_status[$val->enabled];
						}	
					?>
					<div class="carousel-item <?php if($i==0){ echo "active"; } ?>">
					
					
					<?php $img = isset($val->image_path) ? $val->image_path:'';
					if( isset($img) && !empty($img) ){ ?>
						<img src="<?= $img ?>" alt="<?php echo split_words($val->post_title,30,''); ?>" width="300" height="200">
					<?php }else{ ?>
						<img width="300" height="300" src="<?php echo base_url(); ?>assets/uploads/dummy.jpg" alt="<?php echo split_words($val->post_title,30,'' ); ?>" class="img-fluid" >
					<?php } ?>	

					<h5 class="pt-2 pb-2"><?php echo split_words($val->post_title,30,'...' ); ?></h5>
					<p class="pt-2 pb-2"><?php echo substr($val->post_content,0,200); ?></p>
					</div>
					<?php $i++; } } ?>
				</div>
				<a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>
			
			
		</div>
		
	</div>
</div>

<div class="col-lg-4 col-sm-12 col-12">
<div class="card height-equal" >
	<div class="card-header">
		<h5 class="text-uppercase">Top Selling Products</h5>
		<div class="card-header-right">
			<ul class="list-unstyled card-option">
				<li><i class="icofont icofont-simple-left "></i></li>
				<li><i class="view-html fa fa-code"></i></li>
				<li><i class="icofont icofont-maximize full-card"></i></li>
				<li><i class="icofont icofont-minus minimize-card"></i></li>
				<li><i class="icofont icofont-refresh reload-card"></i></li>
				<li><i class="icofont icofont-error close-card"></i></li>
			</ul>
		</div>
	</div>
	<div class="card-body">
		<ul class="crm-activity equal-height-xl">
			<?php 
			$price = '';
			$sku='';
			$ptitle='';
			if(isset($top_selling_products)){
				foreach($top_selling_products as $key=>$val){
					if( isset($val->sale_price) && !empty($val->sale_price)){
						$price = $val->sale_price;}else{
						$price = $val->regular_price;}
					if( isset($val->sku) && !empty($val->sku)){
						$sku = $val->sku;}
					if( isset($val->post_title) && !empty($val->post_title)){
						$ptitle = $val->post_title;	}					
					
					$img = isset($val->image_path) ? $val->image_path:'';
					?>	
					<li class="media"><span class="mr-3 font-primary"><?php if( isset($img) && !empty($img) ){ ?><img src="<?= $img ?>" alt="<?php echo $ptitle; ?>" width="80px" height="80px" >
					<?php }else{ ?>
					<img src="<?php echo base_url(); ?>assets/uploads/dummy.jpg" alt="<?php echo $ptitle; ?>" class="img-fluid" width="80px" height="80px" ><?php } ?>	</span>
					
					<div class="align-self-center media-body">
						<h6 class="mt-0"><?php echo split_words($ptitle,40,'...');?></h6>
						<ul class="dates">
							<li class="digits">Sku: <?php echo $sku; ?></li>
							<li class="digits">Sale: <?php echo $price; ?></li>
						</ul>
					</div>
					</li>
				
				<?php }				
				} ?>
						
		</ul>
		
	</div>
</div></div></div></div></div>

<div class="row">
<div class="col-md-12 col-sm-12">
<div class="card height-equal">
<div class="card-header"><h5>Sold Products (30days)</h5>
	<div class="card-header-right">
		<ul class="list-unstyled card-option">
			<li><i class="icofont icofont-simple-left "></i></li>
			<li><i class="view-html fa fa-code"></i></li>
			<li><i class="icofont icofont-maximize full-card"></i></li>
			<li><i class="icofont icofont-minus minimize-card"></i></li>
			<li><i class="icofont icofont-refresh reload-card"></i></li>
			<li><i class="icofont icofont-error close-card"></i></li>
		</ul>
	</div>
</div>

<div class="card-body">
<div class="user-status table-responsive product-chart">
<table class="table table-bordernone">
<thead>
	<tr>
		<th scope="col">Product</th>
		<th scope="col">Sale</th>
		<th scope="col">Sold</th>
	</tr>
</thead>

<tbody>


<?php 

if( isset($sold_product)){
	foreach( $sold_product as $k=>$row ) { ?>
	
	<tr>
		<td>
		<?php $img = isset($row->image_path) ? $row->image_path:'';
		if( isset($img) && !empty($img) ){ ?><img src="<?= $img ?>" alt="<?php echo split_words($row->post_title,30,'.' ); ?>" width="80px" height="80px" ><?php }else{ ?>
		<img src="<?php echo base_url(); ?>assets/uploads/dummy.jpg" alt="<?php echo split_words($row->post_title,'30',''); ?>" class="img-fluid" width="80px" height="80px" ><?php } ?>
		<?php echo "<span>".split_words($row->post_title,50,'...')."</span>"; ?></td>
		<td><?php echo $row->Totalsale ?></td>
		<td><?php echo $row->TotalQuantity ?></td>
	</tr>	
	<?php } }  ?>

</tbody>
</table>
</div>

</div></div></div>
</div>



			
</div></div></div>
	
<!-- Modal -->
<div id="myModal" class="modal fade customModel" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <div class="col-sm-12 sp-top-30">
                    <form id="filter_data" method="post">
                        <div class="row">                           
							<div class="col-md-6">
								<label for="Order Date">Date</label>
                                <input type="text" id="date" name="date" class="form-control validate">								
                            </div>
							<div class="col-md-6 p-4">					
								<button class="btn waves-effect waves-light btn-large btnSubmit" type="button">Submit</button>
							</div>							
                        
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>

$('#date').daterangepicker({
	autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,	
    //"timePicker": true,
    //"timePicker24Hour": true,	
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#date').val(start.format('YYYY-MM-DD'));
});

$("#date").on('apply.daterangepicker', function(ev, Picker) {
    $('#date').val(Picker.startDate.format('YYYY-MM-DD'));
});

$('#date').on('cancel.daterangepicker', function(ev, picker) {
    $('#date').val('');
});

$("#filter_data").submit(function(){
	event.preventDefault();
	//dataTable();
    $("#myModal .close").click()
	
	
});
$('.btnSubmit').on('click', function (e){
	var dt = $('#date').val();
	window.location.replace("/admin/dashboard/index/customdate/"+dt);
    $("#myModal .close").click()
});



$(document).on('ready', function() {
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
			swal({text: response.message, timer: 2000, buttons: false});
			if(response.status == '1'){
				$('#heart_class').removeClass('fa-heart-o').addClass('fa-heart');
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
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>

var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: '# Sale',
            data: <?= $product_qty; ?>,
            backgroundColor: [
               /* 'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',*/
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});

$('.carousel').carousel({
  interval: false,
});
</script>

</body>
</html>
