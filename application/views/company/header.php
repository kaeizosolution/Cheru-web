<?php 
$TYPE = $_SESSION['type'];
$logged_in = FALSE;
if($this->session->userdata($TYPE)){
    $session_obj = $this->session->userdata($TYPE);
    $logged_in = $session_obj['logged_in'];
    $name  = $session_obj['fname'];
    $image = isset($session_obj['image']) ? $session_obj['image'] : '';
}

$controller = $this->router->fetch_class();
$model = $this->router->fetch_method();
if(isset($navigation) && $navigation == 99){
    $navigation = 0;
}else{
    $navigation = 1;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Limadi</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!-- bootstrap css -->
        <link rel="stylesheet" href="/assets/core/css/bootstrap.min.css"/>
        <link rel="stylesheet" href="/assets/core/css/all.css">
        <link rel="stylesheet" href="/assets/core/css/font-awesome.css">
        <link rel="stylesheet" href="/assets/core/css/owl.carousel.css"/>
        <link rel="stylesheet" href="/assets/core/css/owl.theme.css"/>
        <link rel="stylesheet" href="/assets/core/css/style.css?r=202007101241">

        <link rel="stylesheet" href="/assets/plugins/daterangepicker/daterangepicker.css" />
        <link rel="stylesheet" href="/assets/core/css/select2.min.css" />
        <link rel="stylesheet" href="/assets/plugins/parsley/parsley.css" />
        <link rel="stylesheet" href="/assets/core/css/custom.css?r=20201026">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- DataTables -->
        <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/plugins/datatables/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />

        <!-- ============================  Script Start ================================= -->
        <script src="/assets/core/js/jquery.min.js"></script>
        <script src="/assets/core/js/popper.min.js"></script>
        <script src="/assets/core/js/bootstrap.min.js"></script>
        <script src="/assets/core/js/owl.carousel.min.js"></script>
        <script src="/assets/core/js/custom.js"></script>

        <script src="/assets/plugins/daterangepicker/moment.min.js"></script>
        <script src="/assets/plugins/daterangepicker/daterangepicker.js?r=20201029"></script>
        <script src="/assets/plugins/sweetalert/sweetalert.min.js"></script>
        <script src="/assets/plugins/parsley/parsley.min.js"></script>
        <script src="/assets/core/js/select2.min.js"></script>
        <!--<script src="/assets/core/js/float-label.js"></script>-->

        <!-- Required datatable js -->
        <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="/assets/plugins/datatables/dataTables.responsive.min.js"></script>
        <script src="/assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
        <script src="/assets/plugins/datatables/dataTables.buttons.min.js"></script>
        <script src="/assets/plugins/datatables/buttons.html5.min.js"></script>

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
        <section class="menu2">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <a class="navbar-brand" href="/"><img src="/assets/core/images/Limadi.png"></a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <?php if($logged_in){ ?>
                        <ul class="navbar-nav m-auto">
                            <li class="nav-item active">
                                <a class="nav-link" href="javascript:void(0);">Hi <?= $name; ?></a>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav notifications">
                            <?php $notification_obj = get_notification();  $notification_count = get_notification_count(); ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bell" aria-hidden="true"></i>
                                <span class="notifyNos"><?= $notification_count ?></span></a>
                                <?php if($notification_count){ ?>
                                <ul class="dropdown-menu dropdown-menu-right customLoginInfo">
                                    <li class="notifyHead">Notifications</li>
                                    <?php if($notification_obj){foreach($notification_obj as $row){ ?>
                                    <li class="read"><i class="fa fa-commenting-o" aria-hidden="true"></i><a href="/<?= $TYPE ?>/dashboard/notification_track/<?= $row->notification_id ?>"><?= $row->title ?><span><?= format_date($row->date_added) ?></span></a></li>
                                    <?php }} ?>
                                    <li class="notifyFooter"><a href="/<?= $TYPE ?>/dashboard/notification">View All</a></li>
                                </ul>
                                <?php } ?>
                            </li>
                        </ul>
                        <ul class="navbar-nav p-0">
                            <li>
                                <div class="dropdown show">
                                    <a class="btn btn-secondary dropdown-toggle login-btn" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Task Manager
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right customLoginInfo" aria-labelledby="dropdownMenuLink">
                                        <ul>
                                            <li><a href="/<?= $TYPE ?>/dashboard">Dashboard <i class="fa fa-tachometer" aria-hidden="true"></i></a></li>
                                            <li><a href="/<?= $TYPE ?>/requirement/post_advance">Post Request<i class="fa fa-pencil" aria-hidden="true"></i></a></li>
                                            <li><a href="/<?= $TYPE ?>/dashboard/task">Task Manager<i class="fa fa-road" aria-hidden="true"></i></a></li>
                                            <li><a href="/<?= $TYPE ?>/requirement/open">Requests <i class="fa fa-folder-open-o" aria-hidden="true"></i></a></li>
                                            <!--<li><a href="/<?= $TYPE ?>/requirement/enroute">Tracking <i class="fa fa-road" aria-hidden="true"></i></a></li>-->
                                            <li><a href="/<?= $TYPE ?>/reports">Reports <i class="fa fa-check" aria-hidden="true"></i></a></li>
                                            <li><a href="/<?= $TYPE ?>/licence">Licence Registration <i class="fa fa-car" aria-hidden="true"></i></a></li>
                                            <li><a href="/<?= $TYPE ?>/driver">Driver Registration <i class="fa fa-id-card" aria-hidden="true"></i></a></li>
                                            <!--<li><a href="/<?= $TYPE ?>/membership">Membership Plan<i class="fa fa-users" aria-hidden="true"></i></a></li>-->
                                            <!--<li><a href="/<?= $TYPE ?>/staff">Staff<i class="fa fa-users" aria-hidden="true"></i></a></li>-->
                                            <!--<li><a href="/<?= $TYPE ?>/report">Reports <i class="fa fa-flag" aria-hidden="true"></i></a></li>-->
                                            <li><a href="/<?= $TYPE;?>/profile">Profile <i class="fa fa-user" aria-hidden="true"></i></a></li>
                                            <!--<li><a href="/">My Settings <i class="fa fa-cogs" aria-hidden="true"></i></a></li>-->
                                            <li><a href="/contact_us">Contact Limadi<i class="fa fa-paper-plane" aria-hidden="true"></i></a></li>
                                            <li><a href="/<?= $TYPE;?>/auth/logout">Logout <i class="fa fa-sign-out" aria-hidden="true"></i></a></li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <?php }else{ ?>
                        <ul class="navbar-nav m-auto"></ul>
                        <ul class="navbar-nav p-0">
                            <li class="nav-item">
                                <a class="nav-link" href="/about_us">About Us</a>
                            </li>
                            <li class="nav-item">
                                <a class="lng" href="/contact_us">Contact Us</a>
                            </li>
                            <li class="nav-item">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-primary dropdown-toggle customToggle" data-toggle="dropdown">Sign In</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0);" id="customer_login_btn">Customer Login</a>
                                        <!--<a class="dropdown-item" href="javascript:void(0);" id="driver_login_btn">Driver Login</a>-->
                                        <a class="dropdown-item" href="javascript:void(0);" id="company_login_btn">Company Login</a>
                                    </div>
                                </div>
                            </li>
                        </ul> 
                        <?php } ?>
                    </div>
                </nav>
            </div>
        </section>
        <?php if($controller != 'welcome') { ?>
        <div class="container-fluid" style="height:90px;"></div>
        <?php } ?>
        <?php if($logged_in && $model != 'post_advance'){ ?>
        <div class="fixed"><a href="/<?= $TYPE;?>/requirement/post_advance"><i class="fa fa-plus" aria-hidden="true"></i><span>Post Request</span></a></div>
        <?php } ?>
