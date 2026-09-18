<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Return Details <span id="title-return-id"><?php if(!empty($return_data)) { echo '#' . $return_data->return_id; } ?></span></h4>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                <?php echo $breadcrumbs; ?>
            </div>
        </div>

        <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-xl-8 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Customer Reason & Info</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5>Order Information</h5>
                            <p><strong>Order ID:</strong> <span id="order-number"><?php if(!empty($return_data)) { echo '#' . (isset($return_data->order_number) && !empty($return_data->order_number) ? $return_data->order_number : $return_data->order_id); } else { echo 'Loading...'; } ?></span></p>
                            <p><strong>Order Date:</strong> <span id="order-date"><?php if(!empty($return_data)) { echo date('Y-m-d', strtotime($return_data->order_date)); } else { echo 'Loading...'; } ?></span></p>
                            <p><strong>Customer:</strong> <span id="customer-name"><?php if(!empty($return_data)) { echo $return_data->first_name . ' ' . $return_data->last_name; } else { echo 'Loading...'; } ?></span></p>
                            <p><strong>Product:</strong> <span id="product-name"><?php if(!empty($return_data)) { echo $return_data->product_name; } else { echo 'Loading...'; } ?></span></p>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Return Request</h5>
                            <p><strong>Status:</strong> 
                                <span id="status-badge" class="badge text-capitalize <?php 
                                if(!empty($return_data)) {
                                    $badge = 'badge-warning';
                                    $st = strtolower((string)$return_data->status);
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
                                    echo $badge;
                                }
                                ?>"><?php if(!empty($return_data)) { echo $st_display; } else { echo 'Loading...'; } ?></span>
                            </p>
                            <p><strong>Reason:</strong> <span id="reason"><?php if(!empty($return_data)) { echo nl2br(htmlspecialchars($return_data->reason)); } else { echo 'Loading...'; } ?></span></p>
                            
                            <div id="image-container" class="mt-3">
                                <?php if(!empty($return_data) && !empty($return_data->image_proof)): ?>
                                <p><strong>Uploaded Image:</strong></p>
                                <?php
                                    $img = (string)$return_data->image_proof;
                                    $imgUrl = (preg_match('#^https?://#i', $img)) ? $img : base_url(ltrim($img, '/'));
                                ?>
                                <a href="<?= $imgUrl ?>" target="_blank">
                                    <img src="<?= $imgUrl ?>" class="img-fluid rounded" style="max-height: 300px;" alt="Uploaded Proof">
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Vendor Notes</h4>
                    </div>
                    <div class="card-body">
                        <?php
                            $vendor_note_str = '';
                            if(!empty($return_data)) {
                                $vendor_note_val = $return_data->vendor_note ?? '';
                                if (is_string($vendor_note_val)) {
                                    $vendor_note_str = $vendor_note_val;
                                } elseif (is_null($vendor_note_val)) {
                                    $vendor_note_str = '';
                                } else {
                                    $vendor_note_str = json_encode($vendor_note_val);
                                    if (!is_string($vendor_note_str)) $vendor_note_str = '';
                                }
                            }
                        ?>
                        <form action="<?= base_url('vendor/returns/add_note') ?>" method="POST">
                            <?php if (is_object($csrf) && isset($csrf->name) && isset($csrf->hash)) { ?>
                                <input type="hidden" name="<?= $csrf->name ?>" value="<?= $csrf->hash ?>">
                            <?php } ?>
                            <input type="hidden" name="return_id" id="form-return-id" value="<?= !empty($return_data) ? $return_data->return_id : $return_id ?>">
                            <div class="form-group mb-3">
                                <label>Inspection Note</label>
                                <textarea name="vendor_note" id="vendor-note-textarea" class="form-control" rows="4" placeholder="Add your inspection notes here..."><?= htmlspecialchars($vendor_note_str) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-info mt-2">Save Note</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(isset($use_api) && $use_api && !empty($access_token) && !empty($return_id)): ?>
<script>
$(document).ready(function() {
    $.ajax({
        url: "<?= base_url('api/v1/vendor/Vendor/return_details') ?>",
        type: "POST",
        headers: {
            "Authorization": "Bearer <?= $access_token ?>"
        },
        data: JSON.stringify({
            "return_id": <?= (int)$return_id ?>
        }),
        contentType: "application/json",
        dataType: "json",
        success: function(response) {
            if (response.status === 1 && response.data) {
                var d = response.data;
                $('#title-return-id').text('#' + d.return_id);
                $('#form-return-id').val(d.return_id);
                $('#order-number').text('#' + d.order_number);
                $('#order-date').text(d.order_date);
                $('#customer-name').text(d.customer_name);
                $('#product-name').text(d.product_name);
                
                // Status badge
                $('#status-badge')
                    .text(d.status_display)
                    .removeClass('badge-warning badge-secondary badge-success badge-primary badge-info badge-danger')
                    .addClass(d.status_badge_class);
                
                $('#reason').html(d.reason.replace(/\n/g, '<br>'));
                
                // Image proof container
                $('#image-container').empty();
                if (d.image_url) {
                    $('#image-container').append(
                        '<p><strong>Uploaded Image:</strong></p>' +
                        '<a href="' + d.image_url + '" target="_blank">' +
                        '<img src="' + d.image_url + '" class="img-fluid rounded" style="max-height: 300px;" alt="Uploaded Proof">' +
                        '</a>'
                    );
                }
                
                // Vendor Note
                $('#vendor-note-textarea').val(d.vendor_note);
            } else {
                console.error("Failed to load return details via API");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error while loading return details:", error);
        }
    });
});
</script>
<?php endif; ?>
