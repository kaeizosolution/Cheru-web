<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Returns</h4>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <?php echo $breadcrumbs; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Return Requests</h4>
                    </div>
                    <div class="card-body">
                        <?php if($this->session->flashdata('success')): ?>
                            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
                        <?php endif; ?>
                        <?php if($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table <?php if(isset($use_api) && $use_api){ ?> id="returns-table" class="table table-bordered table-striped custom-table mb-0" <?php } else { ?> class="table table-bordered table-striped custom-table mb-0 datatable" <?php } ?>>
                                <thead>
                                    <tr>
                                        <th>Return ID</th>
                                        <th>Order ID</th>
                                        <th>Product</th>
                                        <th>Customer Reason</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($returns)): foreach($returns as $r): ?>
                                    <tr>
                                        <td>#<?= $r->return_id ?></td>
                                        <td>#<?= $r->order_id ?></td>
                                        <td><?= $r->product_name ?></td>
                                        <td><?= $r->reason ?></td>
                                        <td>
                                            <?php if(!empty($r->image_proof)): ?>
                                                <?php
                                                    $img = (string)$r->image_proof;
                                                    $imgUrl = (preg_match('#^https?://#i', $img)) ? $img : base_url(ltrim($img, '/'));
                                                ?>
                                                <a href="<?= $imgUrl ?>" target="_blank">
                                                    <img src="<?= $imgUrl ?>" width="50" alt="Return Image" style="height:50px; object-fit:cover; border-radius:6px;">
                                                </a>
                                            <?php else: ?>
                                                No Image
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $badge = 'badge-warning';
                                            $st = strtolower((string)$r->status);
                                            $st_display = $st;
                                            if($st_display == 'approved') $st_display = 'accepted';
                                            if($st_display == 'inspection_pending') $st_display = 'initiated';
                                            if($st_display == 'pickup_scheduled' || $st_display == 'picked_up') $st_display = 'completed';
                                            if($st_display == 'approved_refund' || $st_display == 'refund_completed') $st_display = 'refunded';
                                            if($st_display == 'initiated') $badge = 'badge-secondary';
                                            if($st_display == 'accepted') $badge = 'badge-success';
                                            if($st_display == 'completed') $badge = 'badge-primary';
                                            if($st_display == 'refunded') $badge = 'badge-info';
                                            if($st_display == 'rejected' || $st_display == 'return_cancelled') $badge = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badge ?> text-capitalize"><?= $st_display ?></span>
                                        </td>
                                        <td><?= date('Y-m-d', strtotime($r->created_at)) ?></td>
                                        <td>
                                            <a href="<?= base_url('vendor/returns/details/'.$r->return_id) ?>" class="btn btn-primary btn-sm">Review</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <?php if(!isset($use_api) || !$use_api){ ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No return requests found.</td>
                                    </tr>
                                    <?php } ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(isset($use_api) && $use_api && !empty($access_token)): ?>
<script>
function viewReturnDetails(returnId) {
    var form = $('<form action="<?= base_url('vendor/returns/details') ?>" method="POST">' +
                 '<input type="hidden" name="return_id" value="' + returnId + '">' +
                 '<?php if (is_object($csrf) && isset($csrf->name) && isset($csrf->hash)) { ?>' +
                 '<input type="hidden" name="<?= $csrf->name ?>" value="<?= $csrf->hash ?>">' +
                 '<?php } ?>' +
                 '</form>');
    $('body').append(form);
    form.submit();
}

$(document).ready(function() {
    $('#returns-table').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "searching": false,
        "ordering": false,
        "destroy": true,
        "ajax": {
            "url": "<?= base_url('api/v1/vendor/returns') ?>",
            "type": "POST",
            "headers": {
                "Authorization": "Bearer <?= $access_token ?>"
            },
            "dataType": 'json'
        },
        "columns": [
            {"data": "return_id"},
            {"data": "order_id"},
            {"data": "product_name"},
            {"data": "reason"},
            {
                "data": "image_proof_html",
                "orderable": false,
                "searchable": false
            },
            {
                "data": "status_html",
                "orderable": false,
                "searchable": false
            },
            {"data": "created_at_date"},
            {
                "data": "action_html",
                "orderable": false,
                "searchable": false
            }
        ]
    });
});
</script>
<?php endif; ?>
