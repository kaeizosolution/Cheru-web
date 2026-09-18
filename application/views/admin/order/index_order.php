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
        overflow: visible;
    }
    
    .card-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        border-radius: 16px 16px 0 0;
    }

    .card-title-custom {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
    }

    /* --- BULK ACTION BAR --- */
    .bulk-actions-bar {
        padding: 15px 25px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* --- BUTTONS --- */
    .btn-custom {
        color: #fff !important;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
        cursor: pointer;
    }
    
    .btn-filter, .btn-submit-bulk { background-color: var(--primary); }
    .btn-filter:hover, .btn-submit-bulk:hover { 
        background-color: #4f46e5; 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3); 
        color: #fff !important;
    }
    
    .btn-export { background-color: #10b981; } /* Green */
    .btn-export:hover { background-color: #059669; transform: translateY(-2px); }

    .btn i { color: #fff; }

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

    /* --- PAGINATION & MODAL --- */
    .dataTables_paginate { padding: 15px 25px; }
    .page-item.active .page-link {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
    }
    .modal-content { border-radius: 12px; border: none; }
    .modal-header { border-bottom: 1px solid var(--border-color); padding: 15px 20px; }
    .modal-body { padding: 20px; }
    .form-control { border-radius: 6px; }
    .form-control-sm-custom {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 13px;
        min-width: 150px;
    }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->all_orders; ?></h3>
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

                    <div class="card-header-custom">
                        <h5 class="card-title-custom">Order List</h5>
                        <div class="topAction-btn" style="display:flex; gap:10px;">
                            <button onclick="window.location.href = '<?php echo base_url();?>admin/order/export_pending_csv';" type="button" class="btn-custom btn-export">
                                <i class="fa fa-cloud-download"></i> Export(CSV)
                            </button>
                            
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#order_filter" class="btn-custom btn-filter" title="Filter">
                                <i class="fa fa-filter"></i> Filter
                            </a>
                        </div>
                    </div>

                    <form onsubmit="checkAll(event)" class="bulk-actions-bar">
                        <select class="form-control-sm-custom btn-checkall" id="selectStatus" name="checkbox_all" required>
                            <option value=""><?= $page_lang->status; ?></option>   
                            <option value="1"><?= $page_lang->active; ?></option>                          
                            <option value="0"><?= $page_lang->inactive; ?></option>   
                        </select>
                        <button class="btn-custom btn-submit-bulk" type="submit">
                            <?= $page_lang->submit; ?>
                        </button>
                    </form>

                    <div class="card-body p-0">
                        <div class="dt-ext table-responsive">
                            <table id="table" class="display dataTable custom-table">
                                <thead>
                                    <tr>
                                        <th><?= $page_lang->order_uid; ?></th>
                                        <th><?= $page_lang->customer_name; ?></th>
                                        <th>Vendor Name</th>
                                        <th><?= $page_lang->price; ?></th>
                                        <th><?= $page_lang->quantity; ?></th>
                                        <th><?= $page_lang->order_date; ?></th>
                                        <th><?= $page_lang->delivery_date; ?></th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="order_filter" class="modal fade customModel" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Filter Orders</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <form id="filter_data_filter" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->order_uid; ?></label>
                            <input type="text" id="order_uid" name="order_uid" class="form-control" required="" data-parsley-error-message="Please enter Order Uid." />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->order_date; ?></label>
                            <input type="text" id="date_created" name="date_created" class="form-control" required=""/>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="font-weight-bold text-muted small">Status</label>                               
                            <select class="form-control" id="status" name="status" required="required">        
                                <option value="" selected>Status</option>   
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-light mr-2 rounded reset-btn" type="button"><?= $page_lang->clear; ?></button>
                            <button class="btn btn-primary btnSubmit rounded" type="button"><?= $page_lang->submit; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$("#filter_data_filter").submit(function(){
    event.preventDefault();
    dataTable();
    $("#order_filter .close").click();
});
$('.btnSubmit').on('click', function (e){
    dataTable();
    $("#order_filter .close").click()
});

$('.reset-btn').on('click', function (e){
    $('#filter_data_filter').trigger("reset");
});

var table;
$(document).ready(function() {
    dataTable();
});

function dataTable() {
    table = $('#table').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "orderCellsTop": true,
        "destroy": true,
        "pageLength": <?= $page_count ?>,
        "ajax": {
            "url": "/<?= $TYPE; ?>/order/ajax-get-customer-order",
            "type": "POST",
            "dataType": 'json',
            "data": function(d) {
                d["<?= $csrf->name; ?>"] = "<?= $csrf->hash; ?>";
                d.user_id = "<?= $customer_uid; ?>";
                d.filter = {
                    order_number: $('#order_uid').val().trim(), 
                    date_added: $('#date_created').val().trim(), 
                    status: $('#status').val().trim()
                };
            },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
        },
        "language": {
            "paginate": { "next": '<i class="fa fa-chevron-right"></i>', "previous": '<i class="fa fa-chevron-left"></i>' }
        },
        "dom": '<"top"i>t<"bottom"flp><"clear">',
        "columnDefs": [{
            "targets": -1,
            "data": null,
            "render": function(data, type, row) {
                // Assuming status returns HTML or text, just passing it through as per original logic
                return row.status;
            },
            "defaultContent": ''
        }, {
            'targets': 0,
            'searchable': false,
            'orderable': false,
            'className': 'dt-body-center',
        }],
        "columns": [
        {"data": "order_uid_view"}, 
        { "data": "customer_name"}, 
        {"data": "vendor_name"},
        {"data": "total" },
        {"data": "quantity"},
        {"data": "order_date"},
        {"data": "delivery_date"},
        {"data": "status"},
     ]
    });
}

// Bulk Actions
function checkAll(event) { 
    event.preventDefault();
    // Implementation for bulk action if needed
    // Currently just placeholder based on your provided code
    var status = event.target.checkbox_all.value;   
    var supplierId = [];
    $(':checkbox:checked').each(function(i){
      supplierId.push($(this).val());
    });
    
    if(status != '' && supplierId.length > 0){
       checkall_status_change(supplierId,status);
    }
}

function checkall_status_change(id,status)
    {
        var postData = {};
        var csrf_name = "<?= $csrf->name; ?>";
        var csrf_value = "<?= $csrf->hash; ?>";
        postData[csrf_name] =  csrf_value;
        postData.id = JSON.stringify(id);
        postData.status = status;
        
        $.ajax({
                url         :   '<?php echo base_url();?>admin/order/checkall_status_change',
                type        :   'post',            
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if(data.success) {
                        if(data.MSG) { swal('Session expired.'); }
                        swal('Supplier status changed.'); 
                        location.reload(true);
                   } 
                }
        });
    }
    
$('#date_created').daterangepicker({
    autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#date_created').val(start.format('YYYY-MM-DD'));
});
$("#date_created").on('apply.daterangepicker', function(ev, Picker) {
    $('#date_created').val(Picker.startDate.format('YYYY-MM-DD'));
});
$('#date_created').on('cancel.daterangepicker', function(ev, picker) {
    $('#date_created').val('');
});
</script>