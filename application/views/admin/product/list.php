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

    /* --- ACTION BAR --- */
    .bulk-actions-bar {
        padding: 15px 25px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .bulk-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .bulk-right {
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
    
    .btn-export { background-color: #10b981; } /* Green */
    .btn-export:hover { background-color: #059669; transform: translateY(-2px); }

    .btn i { color: #fff; }

    /* --- SEARCH INPUT --- */
    .search-input {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 8px 15px;
        font-size: 14px;
        min-width: 250px;
        transition: all 0.2s;
    }
    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    /* --- TABLE STYLES --- */
    #productTable {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

    #productTable thead th {
        background-color: var(--light-gray);
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    #productTable tbody td {
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
    .badge-inactive { background-color: rgba(239, 68, 68, 0.1); color: var(--danger); }

    /* --- ACTION BUTTONS --- */
    .action-btn-group {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-action-sm {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        cursor: pointer !important;
    }
    
    .btn-edit { background-color: var(--sidebar-color); color: #fff !important; }
    .btn-edit:hover { background-color: #2a2e65; transform: translateY(-1px); }
    
    .btn-delete { background-color: #fff; border: 1px solid #e2e8f0; color: var(--dark) !important; }
    .btn-delete:hover { background-color: #f1f5f9; }
    .btn-delete i { font-size: 16px; }
    
    .fa-toggle-on { color: var(--success); }
    .fa-toggle-off { color: var(--danger); }

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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->products; ?></h3>
                </div>
                <div class="col-lg-6">
                    <ol class="breadcrumb pull-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard'); ?>"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item active"><?= $page_lang->products ?? 'Products'; ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <?php if ($this->session->flashdata('error')) { ?> 
                        <div class="alert alert-danger m-3 rounded"> <?= $this->session->flashdata('error')?></div> 
                    <?php } ?>
                    <?php if($this->session->flashdata('success')){?> 
                        <div class="alert alert-success m-3 rounded"> <?= $this->session->flashdata('success')?></div> 
                    <?php } ?>

                    <div class="card-header-custom">
                        <h5 class="card-title-custom">Product List</h5>
                        
                        <div class="topAction-btn" style="display:flex; gap:10px;">
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <span style="font-weight:600; color:#1e293b; white-space:nowrap;">Trending Limit</span>
                                <select id="adminTrendingLimitSelect" class="form-control" style="width:auto; min-width:90px; height:38px;">
                                    <option value="8">8</option>
                                    <option value="12">12</option>
                                    <option value="16">16</option>
                                    <option value="20">20</option>
                                    <option value="24">24</option>
                                    <option value="32">32</option>
                                    <option value="40">40</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#productFilter" class="btn-custom btn-filter" title="Filter">
                                <i class="fa fa-filter"></i> Filter
                            </a>
                        </div>
                    </div>

                    <div class="bulk-actions-bar">
                        <div class="bulk-left">
                            <select class="form-control-sm-custom btn-checkall" id="selectStatus" name="checkbox_all" style="min-width: 200px;">
                                <option value=""><?= $page_lang->status; ?></option>
                                <option value="1"><?= $page_lang->active; ?></option>
                                <option value="0"><?= $page_lang->inactive; ?></option>
                            </select>
                            <button class="btn-custom btn-submit-bulk" style="padding: 8px 15px; font-size: 13px;" type="button" onclick="checkAll()">
                                <?= $page_lang->submit; ?>
                            </button>
                        </div>

                        <div class="bulk-right">
                            <div class="search-wrapper">
                                <input type="text" id="searchName" class="search-input" placeholder="Search products...">
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="dt-ext table-responsive">
                            <table id="productTable" class="display dataTable custom-table">
                                <thead>
                                    <tr>
                                        <th width="50" class="text-center">
                                            <input type="checkbox" id="selectall" onClick="toggle(this)" />
                                        </th>
                                        <th><?= $page_lang->title; ?></th>
                                        <th><?= $page_lang->type; ?></th>
                                        <th><?= $page_lang->status; ?></th>
                                        <th width="100px;"><?= $page_lang->date; ?></th>
                                        <th width="80px;" class="text-center"><?= $page_lang->action; ?></th>
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

    <div id="productFilter" class="modal fade customModel" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Filter Products</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>                
                </div>
                <div class="modal-body">
                    <form id="filter_product" method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted small"><?= $page_lang->title; ?></label>
                                <input type="text" id="product_name" name="product_name" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted small"><?= $page_lang->date; ?></label>
                                <input type="text" id="date" name="date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted small"><?= $page_lang->author; ?></label>
                                <input type="text" id="author" name="author" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted small"><?= $page_lang->status; ?></label>                                  
                                <select class="form-control" id="enabled" name="enabled">          
                                    <option value="" selected><?= $page_lang->status; ?></option>   
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

</div>

<script>
var csrf_name = "<?= $csrf->name; ?>";
var csrf_value = "<?= $csrf->hash; ?>";

function init_admin_trending_limit_dropdown()
{
	var $sel = (typeof window.jQuery !== 'undefined') ? window.jQuery('#adminTrendingLimitSelect') : null;
	if (!$sel || !$sel.length) return;

	var defaultLimit = 12;
	try {
		var saved = window.localStorage ? window.localStorage.getItem('homepage_trending_limit_admin') : null;
		if (saved !== null && saved !== '') {
			var sv = parseInt(saved, 10);
			if (!isNaN(sv) && sv > 0) defaultLimit = sv;
		}
	} catch(e) {}

	$sel.val(String(defaultLimit));

	$sel.off('change.adminTrending').on('change.adminTrending', function(){
		var v = parseInt(window.jQuery(this).val(), 10);
		if (isNaN(v) || v <= 0) return;
		if (v > 50) v = 50;

		var postData = {};
		postData[csrf_name] = csrf_value;
		postData.limit = v;

		window.jQuery.ajax({
			url: "<?= base_url('admin/product/ajax_set_homepage_trending_limit'); ?>",
			type: 'POST',
			dataType: 'json',
			data: postData
		}).done(function(resp){
			if (resp && resp.csrf_token) {
				csrf_value = resp.csrf_token;
			}
			if (resp && parseInt(resp.success, 10) === 1) {
				try { if (window.localStorage) window.localStorage.setItem('homepage_trending_limit_admin', String(v)); } catch(e) {}
				try { swal('Trending limit updated.'); } catch(e) {}
			}
		});
	});
}

$("#filter_product").submit(function(event){
    event.preventDefault();
    table.draw();
    $("#productFilter .close").click();
});

$('.btnSubmit').on('click', function (e){
    table.draw();
    $("#productFilter .close").click();
});

var table;
$(document).ready(function() {
	init_admin_trending_limit_dropdown();
    $(".reset-btn").click(function(){
        $("#filter_product").trigger("reset");
    });

    table = $('#productTable').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "searching": false,
        "ordering": false,
        "pageLength": <?= $page_count ?>,
        "ajax": {
            "url": "<?= base_url('admin/product/ajax_list') ?>",
            "type": "POST",
            "data": function (d) {
                d.name = $('#searchName').val();
                d.post_title = $('#product_name').val();
                d.modified = $('#date').val();
                d.user_id = $('#author').val();
                d.enabled = $('#enabled').val();
                d[csrf_name] = csrf_value;
            },
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        },
        "dom": '<"top"i>t<"bottom"lp><"clear">',
        "columns": [
            { "data": "id" },
            { "data": "name" },
            { "data": "type" },
            { "data": "status" },
            { "data": "date" },
            { "data": "action" }
        ],
        "columnDefs": [
            {
                "targets": -1,
                "data": null,
                "className": "text-center",
                "render": function ( data, type, row ) {
                    return '<div class="action-btn-group">' + row.action + '</div>';
                }
            },
            {
                'targets': 0,
                'searchable': false,
                'orderable': false,
                'className': 'text-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
                }
            },
            {
                "targets": 3,
                "render": function (data, type, row) {
                    if((data + '').includes('Active') || data == '1') {
                        return '<span class="badge-custom badge-active">Active</span>';
                    }
                    return '<span class="badge-custom badge-inactive">Inactive</span>';
                }
            }
        ]
    });

    // Quick search bar: debounced, triggers after 2+ characters (or on clear)
    var _searchTimer = null;
    $('#searchName').on('input keyup', function () {
        var val = $(this).val().trim();
        clearTimeout(_searchTimer);
        // Trigger immediately on clear, otherwise wait for 2+ chars
        if (val.length === 0 || val.length >= 2) {
            _searchTimer = setTimeout(function () {
                table.ajax.reload();
            }, 300);
        }
    });

    // Filter modal: also debounce the product_name field inside the modal
    var _modalSearchTimer = null;
    $('#product_name').on('input keyup', function () {
        clearTimeout(_modalSearchTimer);
        _modalSearchTimer = setTimeout(function () {
            // Only auto-reload if modal is not open (button submit handles the open case)
            if (!$('#productFilter').hasClass('show')) {
                table.ajax.reload();
            }
        }, 300);
    });
});

// Delete / Toggle Status Logic
$(document).on('click','.btn-toggle', function(e){
    var uid = $(this).attr('data-id');
    var current_status = $(this).attr('data-status');
    var next_status = (String(current_status) === '1') ? '0' : '1';
    toggle_product_status(uid, next_status);
});

function toggle_product_status(id, status)
{
    var postData = {};
    postData[csrf_name] = csrf_value;
    postData.product_id = id;
    postData.status = status;

    $.ajax({
        url         : '<?php echo base_url();?>admin/product/ajax_toggle_product_status',
        type        : 'post',
        dataType    : 'json',
        data        : postData,
        success     : function(data){
            if (data && data.csrf_token) {
                csrf_value = data.csrf_token;
            }
            if (data && data.success) {
                swal('Status changed.');
                table.ajax.reload(null, false);
            }
        },
        error: function (error) { }
    });
}

$('#date').daterangepicker({
    autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,  
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#date').val(start.format('YYYY-MM-DD'));
});

$("#date").on('apply.daterangepicker', function(ev, Picker) {
    $('#date').val(Picker.startDate.format('YYYY-MM-DD'));
});

$('#date').on('cancel.daterangepicker', function(ev, picker) {
    $('#date').val('');
});


function toggle(source) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
    }
}


function checkAll() { 
    var st = document.getElementById("selectStatus");
    var status = st.options[st.selectedIndex].value;
    var supplierId = [];

    // Only include row checkboxes, not the header select-all checkbox
    $('input[name="id[]"]:checked').each(function(i){
        supplierId.push($(this).val());
    });
    
    if(status != '' && supplierId.length > 0){
       checkall_status_change(supplierId,status);
       table.ajax.reload(null, false);
    }
}

function checkall_status_change(id,status)
{
        var postData = {};
        postData[csrf_name] =  csrf_value;
        postData.id = JSON.stringify(id);
        postData.status = status;
        
        $.ajax({
                url         :   '<?php echo base_url();?>admin/product/checkall_status_change',
                type        :   'post',            
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if (data && data.csrf_token) {
                        csrf_value = data.csrf_token;
                    }
                    if(data.success)
                    {
                        if(data.MSG) { swal('Session expired.'); }
                        swal('Status changed.'); 
                        table.ajax.reload(null, false);
                   } 
                }
        });
}
</script>