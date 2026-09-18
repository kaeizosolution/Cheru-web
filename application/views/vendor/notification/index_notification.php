<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<style>
    :root {
        --primary: #6366f1;
        --secondary: #ec4899;
        --success: #10b981;
        --danger: #ef4444;
        --dark: #1e293b;
        --light-gray: #f8fafc;
        --border-color: #e2e8f0;
    }

    /* --- CARD --- */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: visible !important;
        background: #fff;
        margin-bottom: 30px;
    }
    
    .card-header {
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        border-radius: 16px 16px 0 0;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
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
    
    .btn-submit { background-color: var(--primary); }
    .btn-submit:hover { background-color: #4f46e5; transform: translateY(-2px); }
    
    /* --- FILTER SECTION --- */
    .filter-section {
        padding: 20px 25px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
    }

    /* --- PAGINATION --- */
    .dataTables_paginate { padding: 15px 25px; }
    .page-item.active .page-link {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
    }
    
    .form-control { border-radius: 6px; border: 1px solid #cbd5e1; }
</style>

<div class="content-body" style="padding-top: 100px; margin-left: 17rem;">
    <div class="container-fluid">
        <div class="mb-sm-4 d-flex flex-wrap align-items-center text-head">
            <h2 class="font-w600 mb-2 me-auto" style="font-family: 'Poppins', sans-serif; color: #1e293b;">Notification</h2>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Filter Notifications</div>
                    
                    <div class="filter-section">
                        <form id="data_filter" method="post">
                            <div class="row align-items-end">
                                <div class="col-md-5 mb-3">
                                    <label for="date_created" class="font-weight-bold text-muted small">Date From</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fa fa-calendar"></i></span>
                                        </div>
                                        <input type="text" id="date_created" name="date_created" class="form-control border-left-0" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="last_updated" class="font-weight-bold text-muted small">Date To</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fa fa-calendar"></i></span>
                                        </div>
                                        <input type="text" id="last_updated" name="last_updated" class="form-control border-left-0" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button class="btn-custom btn-submit btn-block btnSubmit" type="button"><?= $page_lang->submit; ?></button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body p-0">
                        <div class="dt-ext table-responsive">
                            <table id="table" class="display dataTable custom-table">
                                <thead>
                                    <tr>                                     
                                        <th>Order Id</th>
                                        <th>Product Title</th>  
                                        <th>Date</th>   
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

<script>
var table;
$(document).ready(function() {
    dataTable();
});

$('.btnSubmit').on('click', function (e){
    dataTable();
});

function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
    postData.vendor_id = "<?=$vendor_id?>";
     
    if($('#date_created').val().trim()) {
       filterData.date_created_range = $('#date_created').val();       
    }
    
    if($('#last_updated').val().trim()) {
       filterData.date_created_range += '::'+$('#last_updated').val();    
    }
    
    postData.filter = filterData; 
    
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
            "url": "/<?= $TYPE; ?>/notification/ajax_notify",
            "data": postData,
            "type": "POST",
            "dataType": 'json',
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            },
        },
        "language": {
           "paginate": { "next": '<i class="fa fa-chevron-right"></i>', "previous": '<i class="fa fa-chevron-left"></i>' }
        },
        "dom": '<"top"i>t<"bottom"flp><"clear">',
        "columns": [                
                {"data": "order_id"},
                {"data": "name"},
                {"data": "notify_date"}
        ]
    } );
}

// Datepicker Logic
$('#date_created').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    autoclose: true,
    autoUpdateInput: false,
    autoApply:true,
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }  
});

$('#date_created').on('apply.daterangepicker', function (ev, picker) {
    $('#date_created').val(picker.startDate.format('YYYY-MM-DD'));
    $('#last_updated').val('');
});

$('#last_updated').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    autoUpdateInput: false,
    autoclose: true,
    autoApply:true,
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }  
});

$('#last_updated').on('apply.daterangepicker', function (ev, picker) {
    $('#last_updated').val(picker.startDate.format('YYYY-MM-DD'));
    var ret_date = check_date();
    if(ret_date) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    } else {
        $(this).val('');
        alert("To Date greater then Start date");
    }
});

function check_date() {
    var startDate = new Date($('#date_created').val());
    var endDate = new Date($('#last_updated').val());
    if (startDate < endDate) return 1;
    else return 0;
}
</script>