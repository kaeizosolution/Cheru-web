<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<meta name="robots" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Boltz : Crypto Admin Template">
	<meta property="og:title" content="Boltz : Crypto Admin Template">
	<meta property="og:description" content="Boltz : Crypto Admin Template">
	<meta property="og:image" content="https://boltz.dexignzone.com/xhtml/social-image.png">
	<meta name="format-detection" content="telephone=no">
	
	<!-- PAGE TITLE HERE -->
	<title>Imosys</title>
	
	<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png">
	
	<!-- Daterange picker -->
    <link href="vendor/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
	
	<!-- Pick date -->
    <link rel="stylesheet" href="vendor/pickadate/themes/default.css">
    <link rel="stylesheet" href="vendor/pickadate/themes/default.date.css">
	
    <link rel="stylesheet" href="vendor/select2/css/select2.min.css">
	<!-- Datatable -->
    <link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
	
	<link href="vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
	<link rel="stylesheet" href="vendor/chartist/css/chartist.min.css">
	<link href="vendor/jquery-nice-select/css/nice-select.css" rel="stylesheet">
	<!-- Style css -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
	
</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="loader">
			<span>I</span>
			<span>M</span>
			<span>O</span>
			<span>S</span>
			<span>Y</span>
			<span>S</span>
		</div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="index.html" class="brand-logo">
				<img class="logo-abbr" width="54" src="images/logo-unit.png" >
					
				<img class="brand-title" width="79" src="images/logo.png">
					

            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->
		
		
		
		<!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
							<div class="nav-item">
								<div class="input-group search-area">
									<input type="text" class="form-control" placeholder="Find something here......">
									<span class="input-group-text"><a href="javascript:void(0)"><i class="flaticon-381-search-2"></i></a></span>
								</div>
							</div>
                        </div>
                        <ul class="navbar-nav header-right">
							<li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link  ai-icon" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
                                   <svg width="28" height="28" viewbox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M22.75 15.8385V13.0463C22.7471 10.8855 21.9385 8.80353 20.4821 7.20735C19.0258 5.61116 17.0264 4.61555 14.875 4.41516V2.625C14.875 2.39294 14.7828 2.17038 14.6187 2.00628C14.4546 1.84219 14.2321 1.75 14 1.75C13.7679 1.75 13.5454 1.84219 13.3813 2.00628C13.2172 2.17038 13.125 2.39294 13.125 2.625V4.41534C10.9736 4.61572 8.97429 5.61131 7.51794 7.20746C6.06159 8.80361 5.25291 10.8855 5.25 13.0463V15.8383C4.26257 16.0412 3.37529 16.5784 2.73774 17.3593C2.10019 18.1401 1.75134 19.1169 1.75 20.125C1.75076 20.821 2.02757 21.4882 2.51969 21.9803C3.01181 22.4724 3.67904 22.7492 4.375 22.75H9.71346C9.91521 23.738 10.452 24.6259 11.2331 25.2636C12.0142 25.9013 12.9916 26.2497 14 26.2497C15.0084 26.2497 15.9858 25.9013 16.7669 25.2636C17.548 24.6259 18.0848 23.738 18.2865 22.75H23.625C24.321 22.7492 24.9882 22.4724 25.4803 21.9803C25.9724 21.4882 26.2492 20.821 26.25 20.125C26.2486 19.117 25.8998 18.1402 25.2622 17.3594C24.6247 16.5786 23.7374 16.0414 22.75 15.8385ZM7 13.0463C7.00232 11.2113 7.73226 9.45223 9.02974 8.15474C10.3272 6.85726 12.0863 6.12732 13.9212 6.125H14.0788C15.9137 6.12732 17.6728 6.85726 18.9703 8.15474C20.2677 9.45223 20.9977 11.2113 21 13.0463V15.75H7V13.0463ZM14 24.5C13.4589 24.4983 12.9316 24.3292 12.4905 24.0159C12.0493 23.7026 11.716 23.2604 11.5363 22.75H16.4637C16.284 23.2604 15.9507 23.7026 15.5095 24.0159C15.0684 24.3292 14.5411 24.4983 14 24.5ZM23.625 21H4.375C4.14298 20.9999 3.9205 20.9076 3.75644 20.7436C3.59237 20.5795 3.50014 20.357 3.5 20.125C3.50076 19.429 3.77757 18.7618 4.26969 18.2697C4.76181 17.7776 5.42904 17.5008 6.125 17.5H21.875C22.571 17.5008 23.2382 17.7776 23.7303 18.2697C24.2224 18.7618 24.4992 19.429 24.5 20.125C24.4999 20.357 24.4076 20.5795 24.2436 20.7436C24.0795 20.9076 23.857 20.9999 23.625 21Z" fill="#342E59"></path>
									</svg>

                                    <span class="badge light text-white bg-primary rounded-circle">12</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3" style="height:380px;">
										<ul class="timeline">
											<li>
												<div class="timeline-panel">
													<div class="media me-2">
														<img alt="image" width="50" src="images/avatar/1.jpg">
													</div>
													<div class="media-body">
														<h6 class="mb-1">Dr sultads Send you Photo</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media me-2 media-info">
														KG
													</div>
													<div class="media-body">
														<h6 class="mb-1">Resport created successfully</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media me-2 media-success">
														<i class="fa fa-home"></i>
													</div>
													<div class="media-body">
														<h6 class="mb-1">Reminder : Treatment Time!</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											 <li>
												<div class="timeline-panel">
													<div class="media me-2">
														<img alt="image" width="50" src="images/avatar/1.jpg">
													</div>
													<div class="media-body">
														<h6 class="mb-1">Dr sultads Send you Photo</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media me-2 media-danger">
														KG
													</div>
													<div class="media-body">
														<h6 class="mb-1">Resport created successfully</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media me-2 media-primary">
														<i class="fa fa-home"></i>
													</div>
													<div class="media-body">
														<h6 class="mb-1">Reminder : Treatment Time!</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
										</ul>
									</div>
                                    <a class="all-notification" href="javascript:void(0);">See all notifications <i class="ti-arrow-end"></i></a>
                                </div>
                            </li>
						
						</ul>
                    
					</div>
				</nav>
			</div>
		</div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <div class="deznav">
            <div class="deznav-scroll">
				<div class="dropdown header-profile">
					<a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
						<img src="images/profile/pic1.jpg" width="20" alt="">
						<div class="header-info">
							<span class="font-w400 mb-0">Hello,<b>William</b></span>
							<small class="text-end font-w400">williamfrancisson@mail.com</small>
						</div>
					</a>
					<div class="dropdown-menu dropdown-menu-end">
						<a href="app-profile.html" class="dropdown-item ai-icon">
							<svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
							<span class="ms-2">Profile </span>
						</a>
						<a href="page-error-404.html" class="dropdown-item ai-icon">
							<svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
							<span class="ms-2">Logout </span>
						</a>
					</div>
				</div>
				<ul class="metismenu" id="menu">
                    <li><a class="ai-icon" href="index.html" aria-expanded="false">
							<i class="flaticon-025-dashboard"></i>
							<span class="nav-text">Dashboard</span>
						</a>

                    </li>
					<li><a class="ai-icon" href="liveorders.html" aria-expanded="false">
							<i class="flaticon-017-clipboard"></i>
							<span class="nav-text">Orders</span>
						</a>

                    </li>
					
					<li><a href="javascript:void(0);" class="has-arrow ai-icon" aria-expanded="false">
							<i class="fa fa-archive" aria-hidden="true"></i>
							<span class="nav-text">Products</span>
						</a>
						<ul aria-expanded="false">
                            <li><a href="product.html">Products</a></li>
                            <li><a href="coupon.html">Coupon</a></li>
							<li><a href="tax.html">Tax</a></li>
							<li><a href="shipping.html">Shipping</a></li>
							<li><a href="brand.html">Brand</a></li>
							<li><a href="attributes.html">Attributes</a></li>
							<li><a href="category.html">Category</a></li>
                        </ul>
					</li>
                    <li><a class="ai-icon" href="finance.html" aria-expanded="false">
							<i class="fa fa-money" aria-hidden="true"></i>
							<span class="nav-text">Finance</span>
						</a>

                    </li>
					
                </ul>
				<div class="copyright">
					<p><strong>Imosys</strong> © 2021 All Rights Reserved</p>
				</div>
			</div>
        </div>
        <!--**********************************
            Sidebar end
        ***********************************-->
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
				<div class="mb-sm-4 d-flex flex-wrap align-items-center text-head">
					<h2 class="font-w600 mb-2 me-auto">Finance</h2>
					
					
				</div>	
				<div class="row">
					<div class="col-md-4 col-sm-6">
						<div class="card">
							<div class="card-body d-flex">
								<div class="icon me-3">
									<div class="iconBase lightOrange"><i class="fa fa-money" aria-hidden="true"></i></div>
								</div>
								<div>
									<h2 class="invoice-num"><span></span> 34</h2>
									<p class="mb-0 invoice-num1">
										<svg width="21" height="14" viewbox="0 0 21 14" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M1 13C1.91797 11.9157 4.89728 8.72772 6.5 7L12.5 10L19.5 1" stroke="#13B440" stroke-width="2" stroke-linecap="round"></path>
										</svg>
										<span class="text-success me-1">Total Orders</span>
									</p>
									

								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-sm-6">
						<div class="card">
							<div class="card-body d-flex">
								<div class="icon me-3">
									<div class="iconBase darkOrange"><i class="fa fa-money" aria-hidden="true"></i></div>
								</div>
								<div>
									<h2 class="invoice-num"><span>$</span> 222,567</h2>
									<p class="mb-0">
										<svg width="21" height="14" viewbox="0 0 21 14" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M1 13C1.91797 11.9157 4.89728 8.72772 6.5 7L12.5 10L19.5 1" stroke="#13B440" stroke-width="2" stroke-linecap="round"></path>
										</svg>
										<span class="text-success me-1">Total Income</span>
									</p>
									

								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-sm-6">
						<div class="card">
							<div class="card-body d-flex">
								<div class="icon me-3">
									<div class="iconBase lightBlue"><i class="fa fa-money" aria-hidden="true"></i></div>
								</div>
								<div>
									<h2 class="invoice-num"><span></span> 34</h2>
									<p class="mb-0">
										<svg width="21" height="14" viewbox="0 0 21 14" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M1 13C1.91797 11.9157 4.89728 8.72772 6.5 7L12.5 10L19.5 1" stroke="#13B440" stroke-width="2" stroke-linecap="round"></path>
										</svg>
										<span class="text-success me-1">Total Deliveries</span>
									</p>
									

								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-4">
                                    <h4 class="card-title">Filter</h4>
                                </div>
								<div class="row">
									<div class="mb-3 form-group col-md-6">
										<label class="form-label">Date From</label>
										<div class="col-sm-12">
											<input class="form-control input-daterange-datepicker" type="text" name="daterange" value="01/10/2021 - 01/31/2021">
										</div>
									</div>
									<div class="mb-3 form-group col-md-6">
										<label class="form-label">Date To</label>
										<div class="col-sm-12">
											<input class="form-control input-daterange-datepicker" type="text" name="daterange" value="01/10/2021 - 01/31/2021">
										</div>
									</div>
									<div class="mb-3 form-group col-md-6">
										<label class="form-label">Filter by Client</label>
										<select id="single-select">
											<option value="AL">John</option>
											<option value="WY">Steve</option>
										</select>
									</div>
									<div class="mb-3 form-group col-md-6">
										<label class="form-label">Filter by Driver</label>
										<select id="single-select2">
											<option value="AL">John</option>
											<option value="WY">Steve</option>
										</select>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
				</div>
				
				<div class="row">
					<div class="col-md-12 col-sm-12">
						<div class="card customCard">
                            <div class="card-header">
								Order List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" class="display customTableProduct" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Created</th>
                                                <th>Method</th>
                                                <th>Total Price</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>#20</td>
                                                <td>31 Oct 2021 -  03:49 AM </td>
                                                <td>Delivery <span class="txtBlue">COD</span></td>
                                                <td>$12289.00 </td>
                                                <td>
													<div class="d-flex">
														<a href="fin-order-detail.html" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fa fa-eye"></i></a>
													</div>												
												</td>												
                                            </tr>
											
											
											
                                        </tbody>
                                    </table>
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
		
		
		
        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
		
            <div class="copyright">
                
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

		<!--**********************************
           Support ticket button start
        ***********************************-->
		
        <!--**********************************
           Support ticket button end
        ***********************************-->


	</div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
	<script src="vendor/chart.js/Chart.bundle.min.js"></script>
	<script src="vendor/jquery-nice-select/js/jquery.nice-select.min.js"></script>
	<script src="vendor/select2/js/select2.full.min.js"></script>
    <script src="js/plugins-init/select2-init.js"></script>
	
	
	<!-- Apex Chart -->
	<script src="vendor/apexchart/apexchart.js"></script>
	<script src="vendor/owl-carousel/owl.carousel.js"></script>
	<script src="vendor/chartist/js/chartist.min.js"></script>
    <script src="vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js"></script>
	<script src="js/plugins-init/chartist-init.js"></script>
	
	<!-- Dashboard 1 -->
	<script src="js/dashboard/dashboard-1.js"></script>
	
	<!-- Daterangepicker -->
    <!-- momment js is must -->
    <script src="vendor/moment/moment.min.js"></script>
    <script src="vendor/bootstrap-daterangepicker/daterangepicker.js"></script>
	<!-- Daterangepicker -->
    <script src="js/plugins-init/bs-daterange-picker-init.js"></script>
	
	<!-- Datatable -->
    <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="js/plugins-init/datatables.init.js"></script>

    <script src="js/custom.min.js"></script>
	<script src="js/deznav-init.js"></script>
	<script src="js/demo.js"></script>
    <!--script src="js/styleSwitcher.js"></script-->
</body>
</html>