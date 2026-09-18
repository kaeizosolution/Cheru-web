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

    /* --- CARD & CONTAINER --- */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
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

    /* --- TABLE STYLES & SCROLL FIX --- */
    .dataTables_wrapper {
        width: 100% !important;
        position: relative;
        clear: both;
    }

    .table-scroll-container {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        margin-top: 10px;
        margin-bottom: 15px;
        border-radius: 8px;
    }

    #table_enquiries {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

    #table_enquiries thead th {
        background-color: var(--light-gray);
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 14px 14px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    #table_enquiries tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid var(--border-color);
        font-size: 13px;
        white-space: nowrap;
    }

    /* Truncation classes for single-line display with ellipsis */
    .prod-name-truncate {
        max-width: 170px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
        font-weight: 600;
        color: #1e293b;
    }

    .vendor-name-truncate {
        max-width: 130px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
        color: #0f172a;
    }

    .cust-name-truncate {
        max-width: 130px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
        color: #0f172a;
    }

    .msg-cell {
        white-space: normal !important;
        max-width: 200px;
        min-width: 140px;
        word-break: break-word;
        font-size: 13px;
        line-height: 1.4;
    }

    /* DataTables Controls & Pagination Styling */
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        outline: none !important;
        margin-left: 8px !important;
        box-shadow: none !important;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 4px 10px !important;
        outline: none !important;
    }
    .dataTables_wrapper .dataTables_info {
        padding-top: 12px !important;
        color: #64748b !important;
        font-size: 13px !important;
        font-weight: 500;
        float: left;
    }
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 10px !important;
        float: right;
    }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;">Product Enquiries</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">Customer Enquiries on Products</h5>
                    </div>

                    <div class="card-body p-4">
                        <table id="table_enquiries" class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Vendor</th>
                                    <th>Customer</th>
                                    <th>Contact Details</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($enquiries)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No product enquiries submitted yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($enquiries as $eq): ?>
                                        <?php 
                                            $is_contacted = isset($eq->is_contacted) ? (int)$eq->is_contacted : 0; 
                                            $prod_name    = htmlspecialchars($eq->db_product_name ?: $eq->product_name);
                                            $vendor_name  = htmlspecialchars($eq->db_vendor_name ?: ($eq->vendor_id ? 'Vendor #'.$eq->vendor_id : 'Store Admin'));
                                            $cust_name    = htmlspecialchars($eq->full_name ?: ($eq->db_customer_name ?: 'Anonymous'));
                                        ?>
                                        <tr>
                                            <td style="white-space: nowrap; font-size: 12px;"><?= date('d-M-Y H:i', strtotime($eq->created_at)); ?></td>
                                            <td>
                                                <span class="prod-name-truncate" title="<?= $prod_name; ?>"><?= $prod_name; ?></span>
                                                <br><small class="text-muted">ID: <?= $eq->product_id; ?></small>
                                            </td>
                                            <td>
                                                <span class="vendor-name-truncate" title="<?= $vendor_name; ?>"><strong><?= $vendor_name; ?></strong></span>
                                                <?php if (!empty($eq->vendor_id)): ?>
                                                    <br><small class="text-muted">Vendor ID: <?= $eq->vendor_id; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="cust-name-truncate" title="<?= $cust_name; ?>"><strong><?= $cust_name; ?></strong></span>
                                                <br><small class="text-muted">Cust ID: <?= $eq->customer_id; ?></small>
                                            </td>
                                            <td>
                                                <div style="font-size: 12px;">
                                                    <i class="fa fa-envelope mr-1 text-muted"></i> <?= htmlspecialchars($eq->email); ?>
                                                    <?php if (!empty($eq->mobile)): ?>
                                                        <br><i class="fa fa-phone mr-1 text-muted"></i> <?= htmlspecialchars($eq->mobile); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="msg-cell">
                                                <?= nl2br(htmlspecialchars($eq->message)); ?>
                                            </td>
                                            <td class="status-cell-<?= $eq->id; ?>">
                                                <?php if ($is_contacted == 1): ?>
                                                    <span class="badge badge-success" style="padding:5px 9px; border-radius:12px; font-weight:600; font-size:11px;"><i class="fa fa-check-circle mr-1"></i> Contacted</span>
                                                    <?php if (!empty($eq->contacted_at)): ?>
                                                        <br><small class="text-muted" style="font-size:10px;"><?= date('d-M-Y H:i', strtotime($eq->contacted_at)); ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-warning" style="padding:5px 9px; border-radius:12px; font-weight:600; background:#f59e0b; color:#fff; font-size:11px;"><i class="fa fa-clock-o mr-1"></i> Not Contacted</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="action-cell-<?= $eq->id; ?>">
                                                <?php if ($is_contacted == 1): ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm toggle-btn" data-id="<?= $eq->id; ?>" data-status="0" style="border-radius:8px; font-size:11px; padding:4px 8px; white-space:nowrap;">Mark Not Contacted</button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-primary btn-sm toggle-btn" data-id="<?= $eq->id; ?>" data-status="1" style="border-radius:8px; font-size:11px; padding:4px 8px; background:var(--primary); border:none; white-space:nowrap;"><i class="fa fa-paper-plane mr-1"></i> Mark Contacted</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#table_enquiries').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 10,
        "language": {
            "search": "Search enquiries:"
        }
    });

    // Wrap table in scroll container AFTER DataTables init so pagination stays fixed outside
    $('#table_enquiries').wrap('<div class="table-scroll-container"></div>');

    $(document).on('click', '.toggle-btn', function() {
        var btn = $(this);
        var id = btn.data('id');
        var newStatus = btn.data('status');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '<?= base_url($TYPE . "/product_enquiries/toggle_status"); ?>',
            type: 'POST',
            data: {
                id: id,
                status: newStatus,
                '<?= $csrf->name; ?>': '<?= $csrf->hash; ?>'
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false);
                if (res.status === 1) {
                    if (newStatus == 1) {
                        $('.status-cell-' + id).html('<span class="badge badge-success" style="padding:5px 9px; border-radius:12px; font-weight:600; font-size:11px;"><i class="fa fa-check-circle mr-1"></i> Contacted</span><br><small class="text-muted" style="font-size:10px;">' + res.contacted_at + '</small>');
                        $('.action-cell-' + id).html('<button type="button" class="btn btn-outline-secondary btn-sm toggle-btn" data-id="' + id + '" data-status="0" style="border-radius:8px; font-size:11px; padding:4px 8px; white-space:nowrap;">Mark Not Contacted</button>');
                    } else {
                        $('.status-cell-' + id).html('<span class="badge badge-warning" style="padding:5px 9px; border-radius:12px; font-weight:600; background:#f59e0b; color:#fff; font-size:11px;"><i class="fa fa-clock-o mr-1"></i> Not Contacted</span>');
                        $('.action-cell-' + id).html('<button type="button" class="btn btn-primary btn-sm toggle-btn" data-id="' + id + '" data-status="1" style="border-radius:8px; font-size:11px; padding:4px 8px; background:var(--primary); border:none; white-space:nowrap;"><i class="fa fa-paper-plane mr-1"></i> Mark Contacted</button>');
                    }
                } else {
                    alert(res.message || 'Error updating status');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('Try Again');
                alert('Server error while updating status');
            }
        });
    });
});
</script>



