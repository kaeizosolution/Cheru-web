<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<style>
    :root {
        --primary: #6366f1;
        --secondary: #ec4899;
        --dark: #1e293b;
        --sidebar-color: #191c4d; /* Matches Sidebar */
        --light-gray: #f8fafc;
        --border-color: #e2e8f0;
    }

    /* --- CARD --- */
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
    }

    .card-title-custom {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
    }

    /* --- FILTER BUTTON --- */
    .btn-filter-custom {
        background-color: var(--primary);
        color: #fff !important;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-filter-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
        color: #fff !important;
    }
    .btn-filter-custom i { color: #fff !important; }

    /* --- PREMIUM ACTION BUTTON (Deep Blue Default) --- */
    .btn-premium-action {
        background-color: var(--sidebar-color) !important; /* Deep Blue ALWAYS */
        color: #fff !important; /* White Icon ALWAYS */
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
    }
    
    .btn-premium-action:hover {
        background-color: #2a2e65 !important; /* Slightly lighter on hover */
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(25, 28, 77, 0.4);
    }
    
    .btn-premium-action:focus, 
    .btn-premium-action:active {
        box-shadow: none !important;
        outline: none !important;
    }
    
    .popover-body {
    padding: 0.5rem !important;
}
    .custom-popover {
    display: flex;
    flex-direction: column;
    gap: 2px; /* Small space between buttons */
    min-width: 80px; /* Reduced width */
}
    /* Popover Content Button Style */
   .custom-popover-btn {
    margin-top: 4px !important;
    display: flex !important;
    align-items: center;
    justify-content: flex-start;
    width: 100% !important;
    padding: 4px 8px !important; /* Very small padding */
    font-size: 12px !important;   /* Smaller font */
    font-weight: 500;
    border-radius: 4px !important;
    border: none !important;
    color: #ffffffff !important;
    background-color: #007bff;
    text-decoration: none !important;
}
    .custom-popover-btn:hover { color: #fff; opacity: 0.9; }

    /* --- TABLE STYLES --- */
    #table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

	/* Keep DataTables scroll inside the card */
	.dt-ext.table-responsive{
		overflow: hidden;
		max-width:100%;
	}
	.dataTables_wrapper{
		width:100%;
	}
	.dataTables_scroll{
		width:100%;
	}
	.dataTables_scrollBody{
		overflow-x:auto !important;
	}

    .custom-popover-btn i {
    font-size: 11px;
    width: 16px;
    margin-right: 6px;
    text-align: center;
}

    #table thead th {
        background-color: var(--light-gray);
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
    }

    #table tbody td {
        padding: 15px 20px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
    }

    /* --- STATUS DROPDOWN (Standard Look) --- */
    .status-select {
        display: block;
        width: 100%;
        min-width: 110px;
        padding: 6px 12px;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .status-select:focus {
        border-color: var(--primary);
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }

    /* --- PAGINATION --- */
    .dataTables_paginate { padding: 15px 25px; }
    .page-item.active .page-link {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
    }
    
    /* --- MODAL --- */
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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->vendor; ?></h3>
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
                        <h5 class="card-title-custom">Vendor List</h5>
                        <div class="topAction-btn">
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="btn-filter-custom" title="Filter">
                                <i class="fa fa-filter"></i> Filter
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <table id="table" class="display dataTable custom-table">
                                    <thead>
                                            <tr>
                                                <th><?= isset($page_lang->vendor) ? $page_lang->vendor : 'Vendor'; ?> Details</th> 
                                                
                                                <th>Total Products</th> 
                                                
                                                <th><?= $page_lang->total_order; ?></th>
                                                <th><?= $page_lang->pending_orders; ?></th>
                                                <th><?= $page_lang->delivered_orders; ?></th> 
                                                <th><?= isset($page_lang->admin_commission) ? $page_lang->admin_commission : 'Commission'; ?></th>
                                                <th>Status</th>
                                                <th style="width:90px; min-width:90px;"><span style="display:block;line-height:1.2;">Enabled<small class="d-block text-muted" style="font-weight:400;font-size:10px;">/ Disabled</small></span></th>
                                                <th width="50px;" class="text-center"><?= $page_lang->action; ?></th>
                                            </tr>
                                        </thead>
                                    <tbody>
                                        </tbody>
                                </table>
                            </div>
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
                <h5 class="modal-title font-weight-bold">Filter Vendors</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <form id="filter_data" method="post">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->name; ?></label>
                            <input type="text" id="name" name="name" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->email; ?></label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>                            
                        <div class="col-md-12 mb-4">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->mobile; ?></label>
                            <input type="text" id="mobile" name="mobile" class="form-control">
                        </div>  
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-light mr-2 rounded" type="reset"><?= $page_lang->clear; ?></button>
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

    // Close popover when clicking outside
    $('body').on('click', function (e) {
        // Did not click a popover toggle or popover content
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
    if($('#email').val().trim()) { filterData.email = $('#email').val(); }
    if($('#name').val().trim()) { filterData.name = $('#name').val(); } 
    if($('#mobile').val().trim()) { filterData.mobile = $('#mobile').val(); }
    
    postData.filter = filterData;
    
    table = $('#table').DataTable({
        "processing": true,
        "serverSide": true, 
        "responsive": false,
		"scrollX": true,
		"scrollCollapse": true,
		"autoWidth": false,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "orderCellsTop": true,
        "destroy": true,
        "pageLength": <?= $page_count ?>,
        "ajax": {
            "url": "/<?= $TYPE; ?>/vendor/index_ajax_vendor",
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
        "targets": 6,
        "data": "status_html",
        "render": function ( data, type, row ) {
            return data;
        }
    },
    {
        "targets": 7,
        "data": "enabled_html",
        "width": "90px",
        "render": function ( data, type, row ) {
            return data;
        }
    },
    {
        "targets": -1, 
        "data": null,
        "className": "text-center",
        "render": function ( data, type, row ) {
            var actionTemplate = '<button type="button" class="btn btn-premium-action" data-toggle="popover" data-placement="bottom" data-html="true" data-content=\''+
            '<div class="btn-group-vertical w-100">' +
                '<button class="btn custom-popover-btn btn-edit" style="background:#007bff;" type="button"  data-id="'+row.admin_id+'"><i class="fa fa-pencil"></i> <?= $page_lang->edit; ?></button>'+ 
                '<button class="btn custom-popover-btn btn-view" style="background:#17a2b8;" type="button"  data-id="'+row.admin_uid+'"><i class="fa fa-eye"></i> <?= $page_lang->view; ?></button>'+ 
            '</div>' +
            '\'><i class="fa fa-cog"></i></button>';
            return actionTemplate;
        }
    }
],
"columns": [
	{"data": "contact_info"}, 
	{"data": "total_products"},
	{"data": "total_order"}, 
	{"data": "pending_order"},
	{"data": "delivered_order"}, 
	{"data": "commission"},
	{"data": "status_html"},
	{"data": "enabled_html"},
	{"data": "action"}
]
    } );
}

$(document).on('click','.btn-view', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/vendor/vendor_details/"+uid+"";
    url_new_tab(e,url);
});

$(document).on('click', '.btn-edit', function(e){
    var id = $(this).attr('data-id');
    var url = "/<?php echo $TYPE ?>/vendor/update/" + id;
    window.location.href = url;
});

function url_new_tab(e,url)
{
    if(e.ctrlKey){ window.open(url); } else { $(location).attr('href', url); }
}


// FIX: Correct Toggle Logic for Popover with new class
$(document).on('click','.btn-premium-action', function(e){
    $('[data-toggle="popover"]').not(this).popover('hide');
    $(this).popover('toggle');
    $('[data-toggle="tooltip"]').tooltip();
});
</script>