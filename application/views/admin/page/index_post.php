<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        overflow: visible !important;
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

    /* --- BULK ACTIONS BAR --- */
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
    
    .btn-add, .btn-filter, .btn-submit-bulk { background-color: var(--primary); }
    .btn-add:hover, .btn-filter:hover, .btn-submit-bulk:hover { 
        background-color: #4f46e5; 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3); 
        color: #fff !important;
    }
    .btn-export { background-color: #10b981; }
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
    
    /* Popover Content Button Style */
    .custom-popover-btn {
        margin: 2px;
        border-radius: 4px;
        background: #3b82f6;
        color: #fff;
        border: none;
        padding: 6px 12px;
    }
    .custom-popover-btn:hover { color: #fff; opacity: 0.9; }
    
    .btn-delete-pop { background-color: var(--danger); }
    .btn-delete-pop:hover { background-color: #dc2626; }

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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->page; ?></h3>
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
                    
                    <div class="alert alert-danger alert-checkbox-error text-left m-3 rounded" role="alert" style="display:none;">Select at least one item</div>

                    <div class="card-header-custom">
                        <h5 class="card-title-custom">Web Pages List</h5>
                        <div class="topAction-btn" style="display:flex; gap:10px;">
                            <button onclick="window.location.href = '<?php echo base_url();?>admin/page/export_csv';" type="button" class="btn-custom btn-export" style="display:none;">
                                <i class="fa fa-cloud-download"></i> Export(CSV)
                            </button>
                            
                            <a href="/<?= $TYPE?>/page/add" class="btn-custom btn-add" title="Add New">
                                <i class="fa fa-plus"></i> Add New
                            </a>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="btn-custom btn-filter" title="Filter">
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
                                        <th width="50" class="text-center">
                                            <input type="checkbox" id="selectall" onClick="toggle(this)" />
                                        </th>
                                        <th><?= $page_lang->title; ?></th>
                                        <th><?= $page_lang->author; ?></th>
                                        <th><?= $page_lang->comment; ?></th>
                                        <th><?= $page_lang->date; ?></th>
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
                <h5 class="modal-title font-weight-bold">Filter Pages</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <form id="filter_data" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->page_title; ?></label>
                            <input type="text" id="post_title" name="post_title" class="form-control">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-bold text-muted small"><?= $page_lang->status; ?></label>                               
                            <select class="form-control" id="enabled" name="enabled">        
                                <option value="" selected><?= $page_lang->select_status; ?></option>   
                                <option value="1"><?= $page_lang->active; ?></option>                          
                                <option value="0"><?= $page_lang->inactive; ?></option>   
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
    
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
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
    if($('#post_title').val()) { filterData.post_title = $('#post_title').val(); }
    if($("#enabled :selected").val()) { filterData.enabled = $("#enabled :selected").val(); }
    
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
            "url": "<?php echo base_url(); ?><?= $TYPE; ?>/page/index_ajax_post",
            "data": postData,
            "type": "POST",
            "dataType": 'json',
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({
                    html: true,
                    trigger: 'manual'
                });
            },
        },
        "language": {
           "paginate": { "next": '<i class="fa fa-chevron-right"></i>', "previous": '<i class="fa fa-chevron-left"></i>' }
        },
        "dom": '<"top"i>t<"bottom"flp><"clear">',
        "columnDefs": [ 
            {
                "targets": 5,
                "render": function ( data, type, row ) {
                    if(data == 'Active' || data == '1' || row.enabled == '1') {
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
                        // Create the buttons as clean strings first
                        // Use &quot; for the class names to avoid quote-nesting issues in the popover
                        var editBtn = '<button title="Edit" class="btn custom-popover-btn update_page" type="button" data-id="'+row.page_id+'"><i class="fa fa-pencil-square-o"></i></button>';
                        var delBtn  = '<button title="Delete" class="btn custom-popover-btn btn-delete-pop" type="button" data-id="'+row.page_id+'"><i class="fa fa-trash"></i></button>';
                        
                        // Combine them and put them inside the popover
                        return '<button type="button" class="btn btn-premium-action" data-toggle="popover" data-placement="bottom" data-html="true" data-content=\'' + editBtn + delBtn + '\'><i class="fa fa-cog"></i></button>';
                    }
                },
            {
                'targets': 0,
                'className': 'text-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="id[]" value="' + data + '">';
                }
            }
         ],
        "columns": [
               {"data": "page_id"},
               {"data": "post_title"},
               {"data": "user_id"},
               {"data": "comment"},
               {"data": "date"},
               {"data": "enabled"},
               {"data": "action"},
          ]
    });
}

// Edit Logic - Ensure URL is correct
$(document).on('click','.update_page', function(e){
    var uid = $(this).attr('data-id');
    // alert(uid)
    var url = "<?php echo base_url(); ?><?= $TYPE ?>/page/update/" + uid;
    url_new_tab(e,url);
});

function url_new_tab(e,url)
{
    if(e.ctrlKey){ window.open(url); } else { window.location.href = url; }
}

// Delete Logic
$(document).on('click','.btn-delete-pop', function(e){
    var uid=$(this).attr('data-id');  
    if(confirm('Are you sure you want to delete this?')) {
        delete_post(uid);
    }
});

function delete_post(id)
{
    var postData = {
        "<?= $csrf->name; ?>": "<?= $csrf->hash; ?>",
        "id": id
    };

    $.ajax({
            url         :   '<?php echo base_url();?><?= $TYPE ?>/page/del',
            type        :   'post',
            dataType    :   "json",  
            data        :   postData,
            success     :   function(data){
                if(data.success) {
                    swal('Deleted Successfully.'); 
                    table.ajax.reload(null, false);
               } 
            }
    });
}

// Bulk Logic
function checkAll(event) {
    event.preventDefault();
    var status = event.target.checkbox_all.value;   
    var supplierId = [];
    $(':checkbox:checked').each(function(i){
      if($(this).val() != 'on') supplierId.push($(this).val());
    });
    
    if(status != '' && supplierId.length > 0){
       checkall_status_change(supplierId,status);
    } else {
       if(status != '') $('.alert-checkbox-error').fadeIn().delay(2000).fadeOut();
    }
}

function checkall_status_change(id,status)
{
        var postData = {
            "<?= $csrf->name; ?>": "<?= $csrf->hash; ?>",
            "id": JSON.stringify(id),
            "status": status
        };
        
        $.ajax({
                url         :   '<?php echo base_url();?><?= $TYPE ?>/page/checkall_status_change',
                type        :   'post',            
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if(data.success) {
                        swal('Status changed.'); 
                        table.ajax.reload(null, false);
                   } 
                }
        });
}

function toggle(source) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
    }
}

$(document).on('click','.btn-premium-action', function(e){
    $('[data-toggle="popover"]').not(this).popover('hide');
    $(this).popover('toggle');
});
</script>