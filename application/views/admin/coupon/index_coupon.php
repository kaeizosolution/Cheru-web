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
    }

    .card-title-custom {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
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
    
    .btn-add, .btn-filter { background-color: var(--primary); }
    .btn-add:hover, .btn-filter:hover { 
        background-color: #4f46e5; 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3); 
        color: #fff !important;
    }
    .btn-add i, .btn-filter i { color: #fff; }

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

    /* --- BADGES --- */
    .badge-custom {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    .badge-active { background-color: rgba(16, 185, 129, 0.1); color: var(--success); }
    .badge-inactive { background-color: rgba(239, 68, 68, 0.1); color: var(--danger); }

    /* --- PREMIUM ACTION BUTTON --- */
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
    
    /* Popover Content Buttons */
    .custom-popover-btn {
        margin: 2px;
        border-radius: 4px;
        border: none;
        padding: 6px 10px;
    }
    .btn-status { background-color: #10b981; color: #fff !important; } /* Green for Toggle */
    .btn-status:hover { background-color: #059669; }
    
    .btn-edit { background-color: #3b82f6; color: #fff !important; } /* Blue for Edit */
    .btn-edit:hover { background-color: #2563eb; }

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
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->coupon; ?></h3>
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
                        <h5 class="card-title-custom">Coupon List</h5>
                        <div class="topAction-btn" style="display:flex; gap:10px;">
                            <a href="/<?= $TYPE?>/coupon/add" class="btn-custom btn-add" title="Add New">
                                <i class="fa fa-plus"></i> Add New
                            </a>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="btn-custom btn-filter" title="Filter">
                                <i class="fa fa-filter"></i> Filter
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="dt-ext table-responsive">
                            <table id="table" class="display dataTable custom-table">
                                <thead>
                                    <tr>
                                        <th><?= $page_lang->name; ?></th>
                                        <th><?= $page_lang->discount; ?></th>
                                        <th><?= $page_lang->discount_type; ?></th>
                                        <th>Applicable To</th>
                                        <th><?= $page_lang->start_date; ?></th>
                                        <th><?= $page_lang->end_date; ?></th>
                                        <th><?= $page_lang->status; ?></th>
                                        <th width="50px;" class="text-center"><?= $page_lang->action; ?></th>
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

<div id="myModal" class="modal fade customModel" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Filter Coupons</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <form id="filter_data" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->name; ?></label>
                            <input type="text" id="name" name="name" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->start_date; ?></label>
                            <input type="text" id="start_date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->end_date; ?></label>
                            <input type="text" id="end_date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->status; ?></label>                               
                            <select class="form-control" id="status" name="status">        
                                <option value="" selected><?= $page_lang->status; ?></option>   
                                <option value="1">Active</option>                          
                                <option value="0">Inactive</option>   
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
var table;
$(document).ready(function() {
    dataTable();

    // Close popover logic
    $('body').on('click', function (e) {
        if ($(e.target).data('toggle') !== 'popover' &&
            $(e.target).parents('.popover.show').length === 0 &&
            !$(e.target).hasClass('btn-premium-action')) { 
            $('[data-toggle="popover"]').popover('hide');
        }
    });
});

$('.btnSubmit').on('click', function (e){
    dataTable();
    $("#myModal .close").click()
});

function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
    if($('#name').val()) { filterData.name = $('#name').val(); }
    if($('#start_date').val()) { filterData.start_date = $('#start_date').val(); }
    if($('#end_date').val()) { filterData.end_date = $('#end_date').val(); }
    if($('#status').val()) { filterData.status = $('#status').val(); }
    
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
            "url": "/<?= $TYPE; ?>/coupon/index_ajax_coupon",
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
        "columnDefs": [ 
    {
        "targets": 6, // Status Column (shifted due to new column)
        "data": "status",
        "render": function ( data, type, row ) {
            // Fix: Check for numeric 1 or string '1'
            if(row.status == '1' || row.status == 1) {
                return '<span class="badge-custom badge-active">Active</span>';
            } else {
                return '<span class="badge-custom badge-inactive">Inactive</span>';
            }
        }
    },
    {
        "targets": -1, // Action Column
        "data": null,
        "className": "text-center",
        "render": function ( data, type, row ) {
            // Fix: Logic based on numeric status
            var is_active = (row.status == '1' || row.status == 1);
            var toggle_class = is_active ? 'fa-toggle-on' : 'fa-toggle-off';
            var toggle_title = is_active ? 'Disable' : 'Enable';
            
            var actionTemplate = '<button type="button" class="btn btn-premium-action" data-toggle="popover" data-placement="bottom" data-html="true" data-content=\''+
            '<div style="display:flex; gap:5px;">'+
            '<button title="'+toggle_title+'" class="btn custom-popover-btn btn-status" type="button" data-status="'+row.status+'" data-id="'+row.coupon_id+'"><i class="fa '+toggle_class+'"></i></button>'+
            '<button title="Edit" class="btn custom-popover-btn btn-edit" type="button" data-id="'+row.coupon_id+'"><i class="fa fa-pencil"></i></button>'+
            '</div>\''+
            '><i class="fa fa-cog"></i></button>';
            
            return actionTemplate;
        },
        "defaultContent": ''
    }
],
        "columns": [
               {"data": "name"},
               {"data": "discount"},
               {"data": "type"},
               {"data": "applicable_to"},
               {"data": "start_date"},
               {"data": "end_date"},
               {"data": "status"},
               {"data": "action"},
          ]
    } );
}

$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
    });
});

// Edit Logic
$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/coupon/update/"+uid+"";
    url_new_tab(e,url);
});

// Status Change Logic
$(document).ready(function() {
    $(document).on('click', '.btn-status', function (e) {
        var id = $(this).attr('data-id');
        var status = $(this).attr('data-status');
        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.id = id;
        postData.status = status;
        
        swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                 $.ajax( {
                     url:'/<?= $TYPE; ?>/coupon/status_change/',
                     type:'post',
                     dataType:"json",  
                     data: postData,
                     success:function(data) {
                         table.ajax.reload();
                         swal("Status has been changed.", {
                             icon: "success",
                             timer: 2000,
                             buttons: false
                         });
                     }
                 });
            }
        });
    }); 
});

function url_new_tab(e,url)
{
    if(e.ctrlKey){ window.open(url); } else { $(location).attr('href', url); }
}

// Premium Toggle Logic
$(document).on('click','.btn-premium-action', function(e){
    $('[data-toggle="popover"]').not(this).popover('hide');
    $(this).popover('toggle');
});
</script>