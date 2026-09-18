<?php
$TYPE = $_SESSION['type'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <!--== META TAGS ==-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!--== FAV ICON ==-->
    <link rel="shortcut icon" href="/assets/images/fav.ico">

    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600|Quicksand:300,400,500" rel="stylesheet">

    <!-- FONT-AWESOME ICON CSS -->
    <link rel="stylesheet" href="/assets/css/font-awesome.min.css">

    <!--== ALL CSS FILES ==-->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mob.css">
    <link rel="stylesheet" href="/assets/css/materialize.css" />
    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/assets/css/custom.css" />
    <link rel="stylesheet" href="/assets/plugins/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="/assets/css/select2.min.css" />

    <!--======== SCRIPT FILES =========-->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/materialize.min.js"></script>
    <script src="/assets/js/custom.js"></script>
    <script src="/assets/plugins/daterangepicker/moment.min.js"></script>
    <script src="/assets/plugins/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/js/sweetalert.min.js"></script>
    <script src="/assets/js/parsley.min.js"></script>
    <script src="/assets/js/select2.min.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/assets/js/html5shiv.js"></script>
    <script src="/assets/js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">
        $(document).ready(function() {
            $('form').parsley();
    //        $('[data-toggle="tooltip"]').tooltip();
    //        $('[data-toggle="popover"]').popover();
        });
    </script>
</head>

<body>
    <!--== MAIN CONTRAINER ==-->
    <?php if(isset($navigation) && $navigation == 0){} else { ?>
    <div class="container-fluid sb1">
        <div class="row">
            <!--== LOGO ==-->
            <div class="col-md-2 col-sm-3 col-xs-6 sb1-1">
                <a href="#" class="btn-close-menu"><i class="fa fa-times" aria-hidden="true"></i></a>
                <a href="#" class="atab-menu"><i class="fa fa-bars tab-menu" aria-hidden="true"></i></a>
                <a href="index.html" class="logo"><img src="/assets/images/logo1.png" alt="" />
                </a>
            </div>
            <!--== SEARCH ==-->
            <div class="col-md-6 col-sm-6 mob-hide">
                <form class="app-search">
                    <input type="text" placeholder="Search..." class="form-control">
                    <a href="#"><i class="fa fa-search"></i></a>
                </form>
            </div>
            <!--== NOTIFICATION ==-->
            <div class="col-md-2 tab-hide">
                <div class="top-not-cen">
                    <a class='waves-effect btn-noti' href='#'><i class="fa fa-commenting-o" aria-hidden="true"></i><span>5</span></a>
                    <a class='waves-effect btn-noti' href='#'><i class="fa fa-envelope-o" aria-hidden="true"></i><span>5</span></a>
                    <a class='waves-effect btn-noti' href='#'><i class="fa fa-tag" aria-hidden="true"></i><span>5</span></a>
                </div>
            </div>
            <!--== MY ACCCOUNT ==-->
            <div class="col-md-2 col-sm-3 col-xs-6">
                <!-- Dropdown Trigger -->
                <a class='waves-effect dropdown-button top-user-pro' href='#' data-activates='top-menu'><img src="/assets/images/user/6.png" alt="" /><?php if(isset($TYPE) && isset($_SESSION[$TYPE]['fname'])){echo $_SESSION[$TYPE]['fname']; } ?><i class="fa fa-angle-down" aria-hidden="true"></i>
                </a>

                <!-- Dropdown Structure -->
                <ul id='top-menu' class='dropdown-content top-menu-sty'>
                    <li><a href="setting.html" class="waves-effect"><i class="fa fa-cogs" aria-hidden="true"></i>Admin Setting</a>
                    </li>
                    <li class="divider"></li>
                    <li><a href="/<?= $TYPE; ?>/auth/logout" class="ho-dr-con-last waves-effect"><i class="fa fa-sign-in" aria-hidden="true"></i>Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--== BODY CONTNAINER ==-->
    <div class="container-fluid sb2">
        <div class="row">
            <div class="sb2-1">
                <!--== USER INFO ==-->
                <div class="sb2-12">
                    <ul>
                        <li><img src="/assets/images/placeholder.jpg" alt="">
                        </li>
                        <li>
                            <h5>Victoria Baker <span> Santa Ana, CA</span></h5>
                        </li>
                        <li></li>
                    </ul>
                </div>
                <!--== LEFT MENU ==-->
                <div class="sb2-13">
                    <ul class="collapsible" data-collapsible="accordion">
                        <li id="dashboard_index"><a href="/<?= $TYPE; ?>/dashboard"><i class="fa fa-bar-chart" aria-hidden="true"></i>Dashboard</a>
                        </li>
                        <li><a href="javascript:void(0)" class="collapsible-header"><i class="fa fa-user" aria-hidden="true"></i>Users</a>
                            <div class="collapsible-body left-sub-menu">
                                <ul>
                                    <li id="user_index"><a href="/<?= $TYPE; ?>/user">All Users</a>
                                    </li>
                                    <li id="user_post"><a href="/<?= $TYPE; ?>/user/post">Add New User</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li><a href="javascript:void(0)" class="collapsible-header"><i class="fa fa-user" aria-hidden="true"></i>Customers</a>
                            <div class="collapsible-body left-sub-menu">
                                <ul>
                                    <li id="customer_index"><a href="/<?= $TYPE; ?>/customer">All Customers</a>
                                    </li>
                                    <li id="customer_post"><a href="/<?= $TYPE; ?>/customer/post">Add New Customer</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li><a href="javascript:void(0)" class="collapsible-header"><i class="fa fa-user" aria-hidden="true"></i>Drivers</a>
                            <div class="collapsible-body left-sub-menu">
                                <ul>
                                    <li id="driver_index"><a href="/<?= $TYPE; ?>/company">All Companies</a>
                                    </li>
                                    <!--<li id="driver_post"><a href="/<?= $TYPE; ?>/driver/post">Add New Driver</a>-->
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li><a href="javascript:void(0)" class="collapsible-header"><i class="fa fa-user" aria-hidden="true"></i>Plans</a>
                            <div class="collapsible-body left-sub-menu">
                                <ul>
                                    <li id="plan_index"><a href="/<?= $TYPE; ?>/plan">All Plans</a>
                                    </li>
                                    <li id="plan_post"><a href="/<?= $TYPE; ?>/plan/post">Add New Plan</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li><a href="javascript:void(0)" class="collapsible-header"><i class="fa fa-user" aria-hidden="true"></i>Requirements</a>
                            <div class="collapsible-body left-sub-menu">
                                <ul>
                                    <li id="requests_index"><a href="/<?= $TYPE; ?>/requirement">All Requests</a>
                                    </li>
                                    <li id="requirement_post"><a href="/<?= $TYPE; ?>/requirement/post">Add New Requirement</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li><a href="javascript:void(0)" class="collapsible-header"><i class="fa fa-user" aria-hidden="true"></i>Quotations</a>
                            <div class="collapsible-body left-sub-menu">
                                <ul>
                                    <li id="quotation_index"><a href="/<?= $TYPE; ?>/quotation">All Quotations</a>
                                    </li>
                                    <li id="quotation_post"><a href="/<?= $TYPE; ?>/quotation/post">Add New Quotation</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li><a href="javascript:void(0)" class="collapsible-header"><i class="fa fa-address-card-o" aria-hidden="true"></i>Contacts Query</a>
                            <div class="collapsible-body left-sub-menu">
                                <ul>
                                    <li id="query_index"><a href="/<?= $TYPE; ?>/query">All Query</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>                                        
                </div>
            </div>
    <?php } ?>
