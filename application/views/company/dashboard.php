<?php $notification_obj = get_notification();  $notification_count = get_notification_count(); ?>
<link rel="stylesheet" href="/assets/core/css/dashboard.css" />
<div class="container custombreadcrumb">
    <div class="row">
        <div class="col-sm-12">
            <h2 class="heading3">Dashboard</h2>
            <ul class="breadcrumb3"><li></li></ul>
        </div>
    </div>
</div>
    
<div class="container dashboard_info">
    <div class="row">
        <div class="col-md-8 col-sm-12 dashboardCard">
            <a href="/<?= $TYPE ?>/profile" class="bgDarkBlue">
                <div class="row">
                    <div class="col-md-2 col-sm-12">
                        <div class="profilePicture"><img src="<?= $company_obj->profile_image ?>"></div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <span class="mainTxtGreen"><?= $company_obj->name ?></span><span class="mainTxtGreensml"><?= $company_obj->mobile ?><br><?= $company_obj->email ?></span>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <span class="mainTxtOrange"><?= $company_obj->address ?></span><span class="mainTxtOrange-small">View Profile <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-sm-12 dashboard_notify"><a href="/<?= $TYPE ?>/dashboard/notification" class="bgOrangeLite"><i class="fa fa-bell-o" aria-hidden="true"></i> <span class="txtlbl">Notification</span> <span class="tabNo"><?= $notification_count ?></span></a></div>
    </div>
</div>
<div class="container dashboard_info">
    <div class="row">
        <div class="col-md-4 col-sm-12 dashboardCard"><a href="/<?= $TYPE ?>/requirement/open"><img src="/assets/core/images/dashboard_tab_openRequest.png"> <span class="txtlbl">Request</span> <span class="tabNo"><?= $request_cnt ?></span></a></div>
        <div class="col-md-4 col-sm-12 dashboardCard"><a href="/<?= $TYPE ?>/requirement/open/bid"><img src="/assets/core/images/dashboard_tab_Bidding.png"> <span class="txtlbl">Bid</span> <span class="tabNo"><?= $bid_cnt ?></span></a></div>
        <div class="col-md-4 col-sm-12 dashboardCard"><a href="/<?= $TYPE ?>/requirement/open/awarded"><img src="/assets/core/images/dashboard_tab_awarded.png"> <span class="txtlbl">Awarded</span> <span class="tabNo"><?= $awarded_cnt ?></span></a></div>
    </div>
</div>

<div class="container dashboard_info">
    <div class="row">
        <div class="col-md-4 col-sm-12 dashboardCard"><a href="/<?= $TYPE ?>/licence"><img src="/assets/core/images/dashboard_tab_licenceReg.png"> <span class="txtlbl">Car Expiry</span> <span class="tabNo"><?= $licence_expiry_cnt ?></span></a></div>
        <div class="col-md-4 col-sm-12 dashboardCard"><a href="/<?= $TYPE ?>/dashboard/task"><img src="/assets/core/images/dashboard_tab_Track_Vehicle.png"> <span class="txtlbl">Track Vehicle</span> <span class="tabNo"><i class="fa fa-pencil" aria-hidden="true"></i></span></a></div>
        <div class="col-md-4 col-sm-12 dashboardCard"><a href="/<?= $TYPE ?>/requirement/post_advance"><img src="/assets/core/images/dashboard_tab_postReq.png">  <span class="txtlbl">Post Request</span> <span class="tabNo"><i class="fa fa-pencil" aria-hidden="true"></i></span></a></div>
    </div>
</div>
