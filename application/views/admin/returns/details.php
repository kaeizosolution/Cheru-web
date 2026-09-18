<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<div class="container-fluid" style="padding-top: 95px;">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Return Details #<?= (int)($return_data->return_id ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <?php echo $breadcrumbs; ?>
        </div>
    </div>

    <?php
        $img = isset($return_data->image_proof) ? (string)$return_data->image_proof : '';
        $imgUrl = '';
        if ($img !== '') {
            $imgUrl = (preg_match('#^https?://#i', $img)) ? $img : base_url(ltrim($img, '/'));
        }

        $st = isset($return_data->status) ? strtolower((string)$return_data->status) : 'requested';
        // Map legacy statuses to the 5 canonical ones for display
        $st_display = $st;
        if ($st_display === 'approved') $st_display = 'accepted';
        if ($st_display === 'inspection_pending') $st_display = 'initiated';
        if ($st_display === 'pickup_scheduled' || $st_display === 'picked_up') $st_display = 'completed';
        if ($st_display === 'approved_refund' || $st_display === 'refund_completed') $st_display = 'refunded';

        $badgeClass = 'badge badge-secondary';
        if ($st_display === 'requested' || $st_display === 'pending') $badgeClass = 'badge badge-warning';
        if ($st_display === 'initiated') $badgeClass = 'badge badge-secondary';
        if ($st_display === 'accepted') $badgeClass = 'badge badge-success';
        if ($st_display === 'completed') $badgeClass = 'badge badge-primary';
        if ($st_display === 'refunded') $badgeClass = 'badge badge-info';
        if ($st_display === 'rejected' || $st_display === 'return_cancelled') $badgeClass = 'badge badge-danger';

        $vendorLabel = (string)($return_data->vendor_store_name ?? '');
        if ($vendorLabel === '') $vendorLabel = (string)($return_data->vendor_name ?? '');
        if ($vendorLabel === '') $vendorLabel = 'N/A';

        $customerLabel = trim(((string)($return_data->customer_fname ?? '') . ' ' . (string)($return_data->customer_lname ?? '')));
        if ($customerLabel === '') $customerLabel = 'N/A';

        $orderDate = isset($return_data->order_date) && $return_data->order_date ? date('Y-m-d', strtotime($return_data->order_date)) : '-';
    ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header-custom">
                    <h4 class="card-title-custom">Customer Reason & Info</h4>
                </div>
                <div class="card-body" style="padding:25px;">
                    <p><strong>Status:</strong> <span class="<?= $badgeClass ?>"><?= ucfirst($st_display) ?></span></p>
                    <p><strong>Order:</strong> #<?= htmlspecialchars((string)($return_data->order_number ?? $return_data->order_id ?? '')) ?></p>
                    <p><strong>Order Date:</strong> <?= $orderDate ?></p>
                    <p><strong>Customer:</strong> <?= htmlspecialchars($customerLabel) ?></p>
                    <p><strong>Vendor:</strong> <?= htmlspecialchars($vendorLabel) ?></p>
                    <p><strong>Product:</strong> <?= htmlspecialchars((string)($return_data->product_name ?? '')) ?></p>
                    <hr>
                    <p><strong>Reason:</strong><br><?= nl2br(htmlspecialchars((string)($return_data->reason ?? ''))) ?></p>

                    <?php if ($imgUrl !== ''): ?>
                        <p><strong>Uploaded Image:</strong></p>
                        <a href="<?= $imgUrl ?>" target="_blank">
                            <img src="<?= $imgUrl ?>" alt="Return Image" style="max-width: 320px;" class="img-thumbnail" />
                        </a>
                    <?php endif; ?>

                    <?php if ($st === 'rejected' && !empty($return_data->rejection_reason)): ?>
                        <hr>
                        <p><strong>Rejection Reason:</strong><br><?= nl2br(htmlspecialchars((string)$return_data->rejection_reason)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header-custom">
                    <h4 class="card-title-custom">Admin Actions</h4>
                </div>
                <div class="card-body" style="padding:25px;">
                    <form method="post" action="<?= base_url($TYPE . '/returns/action') ?>" onsubmit="return confirm('Apply this action?');">
                        <?php if (is_object($csrf) && isset($csrf->name) && isset($csrf->hash)) { ?>
                            <input type="hidden" name="<?= $csrf->name ?>" value="<?= $csrf->hash ?>">
                        <?php } ?>
                        <input type="hidden" name="return_id" value="<?= (int)($return_data->return_id ?? 0) ?>">

                        <div class="form-group mb-3">
                            <label>Action</label>
                            <select name="action" class="form-control" id="actionSelect" onchange="toggleRejectReason()" required>
                                <option value="">Select</option>
                                <option value="accept">Accepted</option>
                                <option value="initiate">Initiated</option>
                                <option value="reject">Rejected</option>
                                <option value="complete">Completed</option>
                                <option value="refund">Refunded</option>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="rejectReasonDiv" style="display:none;">
                            <label>Rejection Reason (required)</label>
                            <textarea name="rejection_reason" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </form>

                    <hr>
                    <a href="<?= base_url($TYPE . '/returns') ?>" class="btn btn-light btn-block">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRejectReason(){
    var v = document.getElementById('actionSelect').value;
    var div = document.getElementById('rejectReasonDiv');
    if (v === 'reject') {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
    }
}
</script>
