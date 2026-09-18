<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<style>
    :root {
        --primary: #6366f1;
        --secondary: #ec4899;
        --success: #10b981;
        --danger: #ef4444;
        --dark: #1e293b;
        --sidebar-color: #191c4d;
        --light-gray: #f8fafc;
        --border-color: #e2e8f0;
    }

    /* --- CARD --- */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .card-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-title-custom {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
    }

    /* --- TABLE STYLES --- */
    #table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

    #table thead th {
        background-color: var(--light-gray);
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    #table tbody td {
        padding: 15px 20px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
    }

    /* --- BADGES (Status) --- */
    .badge-custom {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    .badge-active { background-color: rgba(16, 185, 129, 0.1); color: var(--success); }
    .badge-inactive { background-color: rgba(245, 158, 11, 0.1); color: #d97706; }

    /* --- ACTION BUTTON --- */
    .btn-premium-action {
        background-color: var(--sidebar-color) !important;
        color: #fff !important;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(25, 28, 77, 0.3);
        transition: all 0.3s ease;
        outline: none !important;
        padding: 0;
        cursor: pointer;
    }
    .btn-premium-action:hover {
        background-color: #2a2e65 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(25, 28, 77, 0.4);
    }
    .btn-premium-action:focus { box-shadow: none !important; outline: none !important; }
    
    .custom-popover-btn {
        margin: 6px 4px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        cursor: pointer;
        display: block;
        width: 140px;
        text-align: center;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .custom-popover-btn-approve {
        background: #191c4d;
        color: #fff !important;
        border: 1px solid #191c4d;
    }
    .custom-popover-btn-approve:hover {
        background: #24296b;
    }
    .custom-popover-btn-reject {
        background: #6366f1;
        color: #fff !important;
        border: 1px solid #6366f1;
    }
    .custom-popover-btn-reject:hover {
        background: #4f46e5;
    }
        background: #ffffff;
        color: #191c4d !important;
        border: 1px solid #e2e8f0;
    }
        background: #f8fafc;
        color: #ef4444 !important;
        border-color: #cbd5e1;
    }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;">Product Reviews</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger m-3 rounded">
                        <?= $this->session->flashdata('error')?>
                        <?php $this->session->unset_userdata('error');?>
                    </div>
                    <?php } ?>
                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success m-3 rounded">
                        <?= $this->session->flashdata('success')?>
                        <?php $this->session->unset_userdata('success');?>
                    </div>          
                    <?php } ?>

                    <!-- Form wrapper for bulk actions -->
                    <form id="bulk-reviews-form" method="post" action="">
                        <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>">

                        <div class="card-header-custom">
                            <h5 class="card-title-custom">Submitted Reviews & Ratings</h5>
                            
                            <!-- Dynamic bulk controls -->
                            <div class="bulk-actions-wrap" style="display: none; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span class="badge badge-info p-2" id="selected-count" style="background-color: var(--light-gray); color: var(--dark); font-weight: 600; border: 1px solid var(--border-color); font-size:12px;">0 Selected</span>
                                <button type="button" class="btn btn-sm btn-bulk-action" data-action="bulk_approve" style="background-color:#191c4d; border-color:#191c4d; color:#fff; font-weight:600; padding: 6px 14px; border-radius: 6px; font-size:12px;">
                                    <i class="fa fa-check mr-1"></i> Approve Selected
                                </button>
                                <button type="button" class="btn btn-sm btn-bulk-action" data-action="bulk_reject" style="background-color:#6366f1; border-color:#6366f1; color:#fff; font-weight:600; padding: 6px 14px; border-radius: 6px; font-size:12px;">
                                    <i class="fa fa-times mr-1"></i> Reject Selected
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="table" class="table">
                                    <thead>
                                        <tr>
                                            <th width="40px;"><input type="checkbox" id="select-all-reviews" style="transform: scale(1.2); cursor: pointer;"></th>
                                            <th>Date</th>
                                            <th>Product</th>
                                            <th>Customer</th>
                                            <th>Rating</th>
                                            <th>Review Content</th>
                                            <th>Status</th>
                                            <th width="50px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($reviews)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">No reviews submitted yet.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($reviews as $rv): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="review_ids[]" class="review-checkbox" value="<?= $rv->review_rating_id; ?>" style="transform: scale(1.2); cursor: pointer;">
                                                    </td>
                                                    <td style="white-space: nowrap;"><?= date('d-M-Y H:i', strtotime($rv->date_created)); ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($rv->product_name); ?></strong>
                                                        <br><small class="text-muted">ID: <?= $rv->product_id; ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($rv->customer_name ?: 'Anonymous'); ?></td>
                                                    <td>
                                                        <span style="color:#f5a623; white-space:nowrap;">
                                                            <?php for ($s=1; $s<=5; $s++) {
                                                                echo $s <= $rv->rating ? '<i class="fa fa-star"></i>' : '<i class="fa fa-star-o"></i>';
                                                            } ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($rv->title)): ?>
                                                            <strong><?= htmlspecialchars($rv->title); ?></strong><br>
                                                        <?php endif; ?>
                                                        <?= nl2br(htmlspecialchars($rv->review)); ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($rv->status === '1'): ?>
                                                            <span class="badge-custom badge-active">Approved</span>
                                                        <?php else: ?>
                                                            <span class="badge-custom badge-inactive">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-premium-action" 
                                                                data-toggle="popover" 
                                                                data-placement="bottom" 
                                                                data-html="true" 
                                                                data-content='
                                                                    <?php if ($rv->status === '0'): ?>
                                                                        <a href="/<?= $TYPE; ?>/reviews/approve/<?= $rv->review_rating_id; ?>" class="custom-popover-btn custom-popover-btn-approve"><i class="fa fa-check"></i> Approve</a>
                                                                    <?php else: ?>
                                                                        <a href="/<?= $TYPE; ?>/reviews/reject/<?= $rv->review_rating_id; ?>" class="custom-popover-btn custom-popover-btn-reject"><i class="fa fa-times"></i> Reject</a>
                                                                    <?php endif; ?>
                                                                '>
                                                            <i class="fa fa-cog"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize popover with downward placement
    $('[data-toggle="popover"]').popover({
        trigger: 'manual',
        html: true,
        animation: true
    });

    // Toggle popover on action button click
    $(document).on('click', '.btn-premium-action', function(e) {
        e.stopPropagation();
        $('[data-toggle="popover"]').not(this).popover('hide');
        $(this).popover('toggle');
    });

    // Close popover when clicking anywhere else
    $('body').on('click', function(e) {
        if ($(e.target).data('toggle') !== 'popover' &&
            $(e.target).parents('.popover.show').length === 0 &&
            !$(e.target).hasClass('btn-premium-action')) { 
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    // --- BULK ACTION HANDLERS ---
    
    // Master checkbox select-all toggle
    $('#select-all-reviews').on('change', function() {
        var checked = this.checked;
        $('.review-checkbox').prop('checked', checked);
        updateBulkControls();
    });

    // Individual checkbox toggle
    $(document).on('change', '.review-checkbox', function() {
        var allChecked = $('.review-checkbox').length === $('.review-checkbox:checked').length;
        $('#select-all-reviews').prop('checked', allChecked);
        updateBulkControls();
    });

    // Update visibility and counts of bulk buttons
    function updateBulkControls() {
        var selectedCount = $('.review-checkbox:checked').length;
        if (selectedCount > 0) {
            $('#selected-count').text(selectedCount + ' Selected');
            $('.bulk-actions-wrap').css('display', 'flex');
        } else {
            $('.bulk-actions-wrap').hide();
        }
    }

    // Submit form with corresponding action
    $('.btn-bulk-action').on('click', function() {
        var action = $(this).attr('data-action');
        
        var form = $('#bulk-reviews-form');
        form.attr('action', '/<?= $TYPE; ?>/reviews/' + action);
        form.submit();
    });
});
</script>
