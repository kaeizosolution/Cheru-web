<?php
$TYPE = $_SESSION['type'];
if($this->session->userdata($TYPE)){
    $session_obj = $this->session->userdata($TYPE);
    $logged_in = $session_obj['logged_in'];
    $name  = $session_obj['fname'];
    $super_admin  = $session_obj['super_admin'];
    $image = isset($session_obj['image']) ? $session_obj['image'] : '';
}
$controller = $this->router->fetch_class();
$model = $this->router->fetch_method();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <link rel="stylesheet" href="/assets/css/fontawesome.css">
    <link rel="stylesheet" href="/assets/css/themify.css">
	<link rel="stylesheet" href="/assets/css/bootstrap.css">
	<link rel="stylesheet" href="/assets/css/style.css">
	<link rel="stylesheet" href="/assets/css/cust_style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">

    <link rel="stylesheet" href="/assets/plugins/parsley/parsley.css" />
    <link rel="stylesheet" href="/assets/plugins/select2/select2.css" />
    <link rel="stylesheet" href="/assets/plugins/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="/assets/plugins/toolbar/jquery.toolbar.css" />

    <link rel="stylesheet" href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="/assets/plugins/datatables/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="/assets/css/custom.css">
	
    <!--<link rel="stylesheet" href="/assets/plugins/datatables/buttons.dataTables.min.css" />-->
    
    <script src="/assets/js/jquery-3.2.1.min.js"></script>
    <script src="/assets/js/popper.min.js"></script>
    <script src="/assets/js/bootstrap.js"></script>
	
    <script src="/assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/assets/plugins/parsley/parsley.min.js"></script>
    <!--<script src="/assets/plugins/float-label/jquery.placeholder.label.js"></script>-->
    <script src="/assets/plugins/select2/select2.min.js"></script>
    <script src="/assets/plugins/daterangepicker/moment.min.js"></script>
    <script src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="/assets/plugins/toolbar/jquery.toolbar.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
    <!--<script src="/assets/plugins/datatables/dataTables.buttons.min.js"></script>-->
</head>
<body>
<?php if(isset($navigation) && $navigation == 0){} else { ?>
    <!-- Loader starts -->
    <div class="loader-wrapper">
        <div class="loader bg-white">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <h4>Have a great day at work today <span>&#x263A;</span></h4>
        </div>
    </div>
    <!-- Loader ends -->
    <!--page-wrapper Start-->
    <div class="page-wrapper">
        <!--Page Header Start-->
        <div class="page-main-header">
            <div class="main-header-left">
                <div class="logo-wrapper">
                    <a href="/admin/dashboard">
                    <img src="/assets/images/logo-light.png" class="image-dark" alt="" height="100px" width="100px" />
                    <img src="/assets/images/logo-light-dark-layout.png" class="image-light" alt=""/>
                    </a>
                </div>
            </div>
            <div class="main-header-right row">
                <div class="mobile-sidebar">
                    <div class="media-body text-right switch-sm">
                    </div>
                </div>
                <div class="nav-right col">
                    <ul class="nav-menus">
                        <li class="onhover-dropdown">
                            <a href="#!" class="txt-dark">
                                <img class="align-self-center pull-right mr-2" src="/assets/images/notification.png" alt="header-notification">
                                <span class="badge badge-pill badge-primary notification">3</span>
                            </a>
                            <ul class="notification-dropdown onhover-show-div">
                                <li>Notification <span
                                    class="badge badge-pill badge-secondary text-white text-uppercase pull-right">3 New</span>
                                </li>                              
                             
                                <li>
                                    <div class="media">
                                        <i class="align-self-center notification-icon icofont icofont-recycle bg-danger"></i>
                                        <div class="media-body">
                                            <h6 class="mt-0 txt-danger">250 MB trush files</h6>
                                            <p class="mb-0">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                            <span><i class="icofont icofont-clock-time p-r-5"></i>25 minutes ago</span>
                                        </div>
                                    </div>
                                </li>
                                <li class="text-center">You have Check <a href="#">all</a> notification</li>
                            </ul>
                        </li>
                        <li class="onhover-dropdown">
                            <div class="media  align-items-center">
                                <div class="media-body">
                                    <h6 class="m-0 txt-dark f-16">
                                        My Account
                                        <i class="fa fa-angle-down pull-right ml-2"></i>
                                    </h6>
                                </div>
                            </div>
                            <ul class="profile-dropdown onhover-show-div p-20">
                                <li>
                                    <a href="#"><i class="icon-user"></i>Edit Profile</a>
                                </li>
                                <li>
                                    <a href="#"><i class="icon-email"></i>Inbox</a>
                                </li>
                                <li>
                                    <a href="#"><i class="icon-check-box"></i>Task</a>
                                </li>
                                <li>
                                    <a href="#"><i class="icon-comments"></i>Chat</a>
                                </li>
                                <li>
                                    <a href="/<?= $TYPE?>/auth/logout"><i class="icon-power-off"></i>Logout</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <div class="d-lg-none mobile-toggle">
                        <i class="icon-more"></i>
                    </div>
                </div>
            </div>
        </div>
        <!--Page Header Ends-->
        <!--Page Body Start-->
        <div class="page-body-wrapper">
            <div class="page-sidebar custom-scrollbar">
                <ul class="sidebar-menu">
                    <li>
                        <a href="/admin/dashboard" class="sidebar-header">
                        <i class="icon-desktop"></i><span>Dashboard</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/coupon"><i class="fa fa-angle-right"></i>Coupon</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Administrator</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/admin"><i class="fa fa-angle-right"></i>Admin</a></li>
                        </ul>
                    </li>
					<li>
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Supplier</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/supplier"><i class="fa fa-angle-right"></i>Supplier</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Widget</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/banner"><i class="fa fa-angle-right"></i>All Widgets</a></li>
                        </ul>
                    </li>
                    <li id="nev_parent_product">
                        <a href="#" class="sidebar-header">
                        <i class="icon-settings"></i><span>Product</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/product" id="nev_product" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Product</a></li>
                            <?php if($super_admin) { ?>
                            <li><a href="/<?= $TYPE ?>/vendor" id="nev_vendor" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Vendor</a></li>
                            <?php } ?>
                            <li><a href="/<?= $TYPE ?>/coupon" id="nev_coupon" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Coupon</a></li>
                            <li><a href="/<?= $TYPE ?>/tax" id="nev_tax" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Tax</a></li>
                            <li><a href="/<?= $TYPE ?>/shipping" id="nev_shipping" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Shipping</a></li>
                            <li><a href="/<?= $TYPE ?>/brand" id="nev_brand" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Brand</a></li>
                            <li><a href="/<?= $TYPE ?>/currency" id="nev_currency" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Currency</a></li>
                            <li><a href="/<?= $TYPE ?>/attributes" id="nev_attributes" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Attributes</a></li>
                            <li><a href="/<?= $TYPE ?>/categories_prod" id="nev_categories_prod" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Category</a></li>
                            <li style="display:none"><a href="/<?= $TYPE ?>/tags_prod" id="nev_tags_prod" data-parent="nev_parent_product"><i class="fa fa-angle-right"></i>Tag</a></li>
                        </ul>
                    </li>
					 <li style="display:none;">
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Post</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/posts"><i class="fa fa-angle-right"></i>All Post</a></li>                           
                            <li><a href="/<?= $TYPE ?>/posts/add"><i class="fa fa-angle-right"></i>Add new</a></li>
							<li><a href="/<?= $TYPE ?>/categories"><i class="fa fa-angle-right"></i>Category</a></li>
							<li><a href="/<?= $TYPE ?>/tags"><i class="fa fa-angle-right"></i>Tag</a></li>
                           
                        </ul>
                    </li>
					 <li>
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Page</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/page"><i class="fa fa-angle-right"></i>All Page</a></li>                           
                            <li><a href="/<?= $TYPE ?>/page/add"><i class="fa fa-angle-right"></i>Add new</a></li>
                           
                        </ul>
                    </li>

					<li>
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Customer</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/user"><i class="fa fa-angle-right"></i>All Customer</a></li> 
                        </ul>
                    </li>
					
                    <li>
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Order Management</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/order"><i class="fa fa-angle-right"></i>All Order</a></li>
                             <li><a href="/<?= $TYPE ?>/order/cancel-order"><i class="fa fa-angle-right"></i>Cancel Order</a></li>
                             <li><a href="/<?= $TYPE ?>/order/complete-order"><i class="fa fa-angle-right"></i>Complete Order</a></li>                           
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="sidebar-header">
                        <i class="icon-package"></i><span>Enquiry</span>
                        <i class="fa fa-angle-right pull-right"></i>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/subscription"><i class="fa fa-angle-right"></i>Subscription</a></li>
                        </ul>
                        <ul class="sidebar-submenu">
                            <li><a href="/<?= $TYPE ?>/contact"><i class="fa fa-angle-right"></i>Contact</a></li>
                        </ul>
                    </li>
                    
                </ul>
            </div>
<?php } ?>
<script>
var controller = '<?= $controller ?>';
var parent_nev = $('#nev_' + controller).attr('data-parent');
$('#' + parent_nev).addClass('active');
$('#nev_' + controller).addClass('active');
</script>
