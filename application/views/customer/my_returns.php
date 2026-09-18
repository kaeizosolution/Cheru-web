<?php
$customer = $this->session->userdata('customer');
$returns_lang = get_page_language_data('returns_lang');
$dash_lang    = get_page_language_data('dashboard_lang');
?>

<style>
.account-sidebar{
    background:#ffffff;
    padding:25px;
    border-radius:16px;
    box-shadow:0 10px 35px rgba(0,0,0,0.08);
    min-height:500px;
}

.account-sidebar a i{
    width:18px;
    display:inline-block;
    text-align:center;
    margin-right:10px;
}

.account-sidebar a.logout-link{
    color:#dc3545;
}

.account-sidebar a.logout-link:hover{
    background:rgba(220, 53, 69, 0.08);
    color:#dc3545;
}

.account-sidebar ul{
    margin:0;
    padding:0;
}

.account-sidebar li{
    list-style:none;
    margin-bottom:12px;
}

.account-sidebar a{
    display:block;
    padding:12px 16px;
    border-radius:10px;
    color:#333;
    font-weight:500;
    transition:0.3s;
}

.account-sidebar a:hover,
.account-sidebar a.active{
    background:#f5f7fb;
    color:#000;
}

 .return-row-requested td{ background:#fff3cd !important; }
 .return-row-initiated td{ background:#f1f3f5 !important; }
 .return-row-accepted td{ background:#d4edda !important; }
 .return-row-rejected td{ background:#f8d7da !important; }
 .return-row-completed td{ background:#d6ecff !important; }
 .return-row-refunded td{ background:#d1ecf1 !important; }
 .return-row-picked_up td{ background:#d6ecff !important; }
 .return-row-return_cancelled td{ background:#f8d7da !important; }
 .return-row-default td{ background:#ffffff !important; }

 /* Ensure status badges are always visible regardless of theme bootstrap overrides */
 .table .badge{
     display:inline-block;
     padding: .35em .6em;
     font-size: 12px;
     font-weight: 600;
     line-height: 1;
     border-radius: .25rem;
 }
 .table .badge.badge-warning{ background:#ffc107 !important; color:#111 !important; }
 .table .badge.badge-success{ background:#28a745 !important; color:#fff !important; }
 .table .badge.badge-danger{ background:#dc3545 !important; color:#fff !important; }
 .table .badge.badge-info{ background:#17a2b8 !important; color:#fff !important; }
 .table .badge.badge-primary{ background:#007bff !important; color:#fff !important; }
 .table .badge.badge-secondary{ background:#6c757d !important; color:#fff !important; }
 .table .badge.badge-light{ background:#e9ecef !important; color:#111 !important; }
 .table .badge.badge-dark{ background:#343a40 !important; color:#fff !important; }
</style>

<section class="inner_page_breadcrumb">
    <div class="container">
        <div class="row">
            <div class="col-xl-6">
                <div class="breadcrumb_content">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url(); ?>"><?= $dash_lang->dashboard ?? 'Home' ?></a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('customer/profile'); ?>"><?= $returns_lang->my_returns ?? 'My Account' ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $returns_lang->my_returns ?? 'My Returns' ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="account-page pt40 pb60">
    <div class="container">
        <div class="row">
            <aside class="col-lg-3 mb30">
                <div class="account-sidebar">
                    <div class="account-user-block d-flex align-items-center mb20">
                        <div class="account-user-avatar">
                            <?php if (!empty($customer['user_img'])): ?>
                                <img src="<?= $customer['user_img']; ?>" class="rounded-circle" style="width:50px; height:50px; object-fit:cover;">
                            <?php else: ?>
                                <span class="fa fa-user"></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="account-hello mb0"><?= $dash_lang->hi ?? 'Hello' ?>,</p>
                            <h6 class="account-name mb0"><?= htmlspecialchars($customer['name'] ?? 'User'); ?></h6>
                        </div>
                    </div>

                    <ul class="account-nav list-unstyled profile-list">
                        <li>
                            <a href="<?= base_url('customer/profile?tab=dashboard') ?>">
                                <i class="fa fa-home"></i><?= $dash_lang->dashboard ?? 'Dashboard' ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('customer/profile?tab=order') ?>">
                                <i class="fa fa-shopping-bag"></i><?= $dash_lang->my_orders ?? 'My Orders' ?>
                            </a>
                        </li>
                        <li class="active">
                            <a href="<?= base_url('customer/returns') ?>">
                                <i class="fa fa-undo"></i><?= $returns_lang->my_returns ?? 'My Returns' ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('customer/profile?tab=address') ?>">
                                <i class="fa fa-map-marker"></i><?= $dash_lang->edit ?? 'Addresses' ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('customer/profile?tab=details') ?>">
                                <i class="fa fa-user"></i><?= $dash_lang->personal_information ?? 'Account Details' ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('customer/profile?tab=wishlist') ?>">
                                <i class="fa fa-heart"></i><?= $dash_lang->my_orders ?? 'Wishlist' ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('customer/profile?tab=wallet') ?>">
                                <i class="fa fa-wallet"></i><?= $dash_lang->my_profile ?? 'Wallet' ?>
                            </a>
                        </li>
                        <li>
                            <a class="logout-link" href="<?= base_url('logout') ?>"><i class="fa fa-sign-out"></i><?= $dash_lang->cancel ?? 'Logout' ?></a>
                        </li>
                    </ul>
                </div>
            </aside>

            <main class="col-lg-9">
                <div class="account-panel">
                    <div class="d-flex justify-content-between align-items-center mb20 flex-wrap gap-2">
                        <div>
                            <h4 class="mb5"><?= $returns_lang->my_returns ?? 'My Returns' ?></h4>
                            <p class="text-muted mb0"><?= $returns_lang->returns_subtitle ?? 'Track your return requests and status updates.' ?></p>
                        </div>
                        <a class="btn btn-outline-dark btn-sm" href="<?= base_url('customer/profile?tab=order'); ?>"><?= $returns_lang->back_to_orders ?? 'Back to Orders' ?></a>
                    </div>

                    <?php if (!empty($api_error)): ?>
                        <div class="alert alert-danger mb20"><?= htmlspecialchars((string)$api_error); ?></div>
                    <?php endif; ?>

                    <?php if (empty($returns)): ?>
                        <div class="text-center text-muted py-5"><?= $returns_lang->no_returns_found ?? 'No return requests found.' ?></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th><?= $returns_lang->return_id ?? 'Return ID' ?></th>
                                        <th><?= $returns_lang->order ?? 'Order' ?></th>
                                        <th><?= $returns_lang->product ?? 'Product' ?></th>
                                        <th><?= $returns_lang->reason ?? 'Reason' ?></th>
                                        <th><?= $returns_lang->status ?? 'Status' ?></th>
                                        <th><?= $returns_lang->requested ?? 'Requested' ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($returns as $r):
                                    $row = is_array($r) ? $r : (array)$r;
                                    $status = strtolower(trim((string)($row['status'] ?? 'requested')));
                                    if ($status === 'pending') $status = 'requested';
                                    if ($status === 'pickedup') $status = 'picked_up';
                                    // map legacy DB statuses to the 5 canonical ones
                                    if ($status === 'inspection_pending') $status = 'initiated';
                                    if ($status === 'approved') $status = 'accepted';
                                    if ($status === 'pickup_scheduled' || $status === 'picked_up') $status = 'completed';
                                    if ($status === 'approved_refund' || $status === 'refund_completed') $status = 'refunded';

                                    $rowClass = 'return-row-default';
                                    if ($status === 'requested') $rowClass = 'return-row-requested';
                                    if ($status === 'initiated') $rowClass = 'return-row-initiated';
                                    if ($status === 'accepted') $rowClass = 'return-row-accepted';
                                    if ($status === 'completed') $rowClass = 'return-row-completed';
                                    if ($status === 'rejected') $rowClass = 'return-row-rejected';
                                    if ($status === 'refunded') $rowClass = 'return-row-refunded';
                                    if ($status === 'return_cancelled') $rowClass = 'return-row-return_cancelled';

                                    $badge = 'badge badge-secondary';
                                    if ($status === 'requested') $badge = 'badge badge-warning';
                                    if ($status === 'initiated') $badge = 'badge badge-secondary';
                                    if ($status === 'accepted') $badge = 'badge badge-success';
                                    if ($status === 'completed') $badge = 'badge badge-primary';
                                    if ($status === 'refunded') $badge = 'badge badge-info';
                                    if ($status === 'return_cancelled') $badge = 'badge badge-danger';
                                    if ($status === 'rejected') $badge = 'badge badge-danger';

                                    $orderNo = $row['order_number'] ?? $row['order_id'] ?? '';
                                    $productName = '';
                                    if (!empty($row['product']) && (is_array($row['product']) || is_object($row['product']))) {
                                        $prod = is_array($row['product']) ? $row['product'] : (array)$row['product'];
                                        $productName = $prod['name'] ?? '';
                                    }
                                    if ($productName === '') {
                                        $productName = $row['product_name'] ?? '';
                                    }
                                    $requestedAt = $row['created_at'] ?? '';

                                    $rid = $row['return_id'] ?? $row['id'] ?? 0;
                                ?>
                                    <tr class="<?= htmlspecialchars($rowClass); ?>">
                                        <td>#<?= (int)$rid; ?></td>
                                        <td><?= htmlspecialchars((string)$orderNo); ?></td>
                                        <td><?= htmlspecialchars((string)$productName); ?></td>
                                        <td><?= htmlspecialchars((string)($row['reason'] ?? '')); ?></td>
                                        <td>
                                            <div>
                                                <?php
                                                    $norm = $status;
                                                    $steps = [
                                                        'requested' => ($returns_lang->status_requested ?? 'Requested'),
                                                        'accepted'  => ($returns_lang->status_accepted  ?? 'Accepted'),
                                                        'initiated' => ($returns_lang->status_initiated ?? 'Initiated'),
                                                        'completed' => ($returns_lang->status_completed ?? 'Completed'),
                                                        'refunded'  => ($returns_lang->status_refunded  ?? 'Refunded'),
                                                    ];
                                                    if ($norm === 'rejected') {
                                                        echo '<span class="badge badge-danger">' . ($returns_lang->status_rejected ?? 'Rejected') . '</span>';
                                                    } elseif ($norm === 'return_cancelled') {
                                                        echo '<span class="badge badge-danger">' . ($returns_lang->status_cancelled ?? 'Cancelled') . '</span>';
                                                    } else {
                                                        $orderKeys = array_keys($steps);
                                                        $activeIdx = array_search($norm, $orderKeys, true);
                                                        if ($activeIdx === false) $activeIdx = 0;
                                                        foreach ($orderKeys as $idx => $k) {
                                                            $isActive = ($idx === $activeIdx);
                                                            $cls = 'badge badge-dark mr-1 mb-1';
                                                            if ($isActive) {
                                                                if ($k === 'requested') $cls = 'badge badge-warning mr-1 mb-1';
                                                                if ($k === 'initiated') $cls = 'badge badge-secondary mr-1 mb-1';
                                                                if ($k === 'accepted') $cls = 'badge badge-success mr-1 mb-1';
                                                                if ($k === 'completed') $cls = 'badge badge-primary mr-1 mb-1';
                                                                if ($k === 'refunded') $cls = 'badge badge-info mr-1 mb-1';
                                                            }
                                                            echo '<span class="' . $cls . '">' . htmlspecialchars($steps[$k]) . '</span>';
                                                        }
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars((string)$requestedAt); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
