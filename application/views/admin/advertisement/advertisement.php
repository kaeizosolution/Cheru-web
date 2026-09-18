<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<style>
    :root {
        --primary: #6366f1;
        --secondary: #ec4899;
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

    /* --- BUTTONS (Add & Filter) --- */
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
    
    /* Both buttons use Primary Color now */
    .btn-add, .btn-filter { 
        background-color: var(--primary); 
    }
    
    .btn-add:hover, .btn-filter:hover { 
        background-color: #4f46e5; 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3); 
        color: #fff !important;
    }
    
    .btn-filter i { color: #fff; }

    /* --- TABLE STYLES --- */
    #advertisement-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }

    #advertisement-table thead th {
        background-color: var(--light-gray);
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    #advertisement-table tbody td {
        padding: 15px 20px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
    }

    /* --- ACTION BUTTONS (Edit/Delete) --- */
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
        cursor: pointer !important; /* FIX: Hand Cursor */
    }
    
    .btn-edit { background-color: #3b82f6; color: #fff !important; }
    .btn-edit:hover { background-color: #2563eb; transform: translateY(-1px); }
    
    .btn-delete { background-color: var(--danger); color: #fff !important; }
    .btn-delete:hover { background-color: #dc2626; transform: translateY(-1px); }
    
    .btn-action-sm i { font-size: 14px; color: #fff !important; }

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
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->manage_advertisements; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger m-3 rounded" data-auto-hide="1"><?= $this->session->flashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success m-3 rounded" data-auto-hide="1"><?= $this->session->flashdata('success') ?></div>
                    <?php endif; ?>

                    <script>
                    (function(){
                        function hideAlerts(){
                            try {
                                var nodes = document.querySelectorAll('[data-auto-hide="1"]');
                                if (!nodes || !nodes.length) return;
                                for (var i=0;i<nodes.length;i++) {
                                    var el = nodes[i];
                                    if (el && el.style) {
                                        el.style.display = 'none';
                                    }
                                }
                            } catch(e) {}
                        }
                        function schedule(){
                            setTimeout(hideAlerts, 5000);
                        }
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', schedule);
                            window.addEventListener('load', schedule);
                        } else {
                            schedule();
                        }
                    })();
                    </script>

                    <div class="card-header-custom">
                        <h5 class="card-title-custom">Advertisement List</h5>
                        <div class="topAction-btn" style="display:flex; gap:10px;">
                            <button type="button" class="btn-custom btn-add" id="showSelectedAdsBtn" title="Show Selected">
                                <i class="fa fa-check"></i> Show Selected
                            </button>
                            <a href="<?= base_url($TYPE . '/advertisement/add'); ?>" class="btn-custom btn-add" title="<?= $page_lang->add_new_advertisement; ?>">
                                <i class="fa fa-plus"></i> Add New
                            </a>
                            <a href="javascript:void(0)" class="btn-custom btn-filter" title="<?= $page_lang->filter; ?>" data-toggle="modal" data-target="#filterModal">
                                <i class="fa fa-filter"></i> Filter
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="dt-ext table-responsive">
                            <table id="advertisement-table" class="display dataTable custom-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="50"><input type="checkbox" id="selectall" /></th>
                                        <th><?= $page_lang->title; ?></th>
                                        <th><?= $page_lang->image; ?></th>
                                        <th><?= $page_lang->link; ?></th>
                                        <th class="text-center" width="110"><?= $page_lang->status ?? 'Status'; ?></th>
                                        <th width="100" class="text-center"><?= $page_lang->action; ?></th>
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

<div id="filterModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Filter Advertisements</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="filter_form">
                    <div class="form-group">
                        <label class="font-weight-bold text-muted small text-uppercase" for="filter_title"><?= $page_lang->advertisement_title; ?></label>
                        <input type="text" id="filter_title" name="title" class="form-control" placeholder="Search by title...">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light rounded" type="button" id="clear-filter-btn"><?= $page_lang->clear; ?></button>
                <button class="btn btn-primary rounded" type="button" id="apply-filter-btn"><?= $page_lang->submit; ?></button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table;

    function initializeDataTable() {
        var postData = { "<?= $csrf['name']; ?>" : "<?= $csrf['hash']; ?>" };
        var filterData = {};

        // Collect filter values
        filterData.title = $('#filter_title').val().trim();
        postData.filter = filterData;

        table = $('#advertisement-table').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "searching": false,
            "ordering": false,
            "destroy": true,
            "pageLength": <?= $page_count; ?>,
            "ajax": {
                "url": "<?= base_url($TYPE . '/advertisement/index_ajax_post'); ?>",
                "type": "POST",
                "data": postData,
                "dataType": 'json',
                "complete": function () {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            },
            "columns": [
                { "data": "advertisement_id" },
                { "data": "title" },
                { "data": "image" },
                { "data": "link" },
                { "data": "is_visible" },
                { "data": "action" }
            ],
            "columnDefs": [
                {
                    'targets': 0,
                    'searchable': false,
                    'orderable': false,
                    'className': 'text-center',
                    'render': function (data, type, full, meta){
                        return '<input type="checkbox" name="id[]" class="dt-checkbox" value="' + data + '">';
                    }
                }, 
                {
                    'targets': 4,
                    'searchable': false,
                    'orderable': false,
                    'className': 'text-center',
                    'render': function(data, type, row) {
                        var isVisible = parseInt(data || 0, 10) ? 1 : 0;
                        var label = isVisible ? 'Active' : 'Inactive';
                        return `<span style="font-weight:600;">${label}</span>`;
                    }
                },
                {
                    'targets': -1,
                    'searchable': false,
                    'orderable': false,
                    'className': 'text-center',
                    'render': function(data, type, row) {
                        var editUrl = `<?= base_url($TYPE . '/advertisement/update/'); ?>${row.advertisement_id}`;
                        var isVisible = parseInt(row.is_visible || 0, 10) ? 1 : 0;
                        var btnClass = isVisible ? 'btn-edit' : 'btn-delete';
                        
                        // New Clean Button Group
                        var actionTemplate = `
                            <div class="action-btn-group">
                                <button type="button" class="btn-action-sm btn-status ${btnClass}" data-id="${row.advertisement_id}" data-visible="${isVisible}" title="Toggle Status" data-toggle="tooltip">
                                    <i class="fa fa-refresh"></i>
                                </button>
                                <a href="${editUrl}" title="Edit" class="btn-action-sm btn-edit" data-toggle="tooltip">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </div>`;
                        
                        return actionTemplate;
                    }
                }
            ],
            "language": { 
                "paginate": { "next": '<i class="fa fa-chevron-right"></i>', "previous": '<i class="fa fa-chevron-left"></i>' } 
            }
        });
    }

    // Initial load
    initializeDataTable();

    // Filter logic
    $('#apply-filter-btn').on('click', function() {
        initializeDataTable();
        $('#filterModal').modal('hide');
    });

    $('#clear-filter-btn').on('click', function() {
        $('#filter_form')[0].reset();
        initializeDataTable();
        $('#filterModal').modal('hide');
    });

    // Status toggle logic
    $(document).on('click', '.btn-status', function(){
        var id = $(this).attr('data-id');
        var isVisible = $(this).attr('data-visible');

        var postData = {
            "<?= $csrf['name']; ?>": "<?= $csrf['hash']; ?>",
            id: id,
            is_visible: isVisible
        };

        swal({
            title: 'Are you sure?',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((ok) => {
            if(!ok) return;
            $.ajax({
                url: "<?= base_url($TYPE . '/advertisement/update_ajax_visible'); ?>",
                type: 'POST',
                dataType: 'json',
                data: postData
            }).done(function(resp){
                if(resp && resp.success){
                    table.ajax.reload(null, false);
                    swal({ title: 'Updated', text: 'Status has been changed.', timer: 1500, showConfirmButton: false });
                } else {
                    swal({ title: 'Error', text: 'Unable to update status.', icon: 'error' });
                }
            });
        });
    });

    // "Select All" checkbox logic
    $('#selectall').on('click', function() {
        $('.dt-checkbox').prop('checked', this.checked);
    });

    $(document).on('click', '#showSelectedAdsBtn', function(){
        var ids = [];
        $('.dt-checkbox:checked').each(function(){
            ids.push($(this).val());
        });

        var postData = {
            "<?= $csrf['name']; ?>": "<?= $csrf['hash']; ?>",
            advertisement_ids: ids
        };

        swal({
            title: 'Are you sure?',
            text: 'Only selected advertisements will be shown on customer side.',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((ok) => {
            if(!ok) return;
            $.ajax({
                url: "<?= base_url($TYPE . '/advertisement/ajax_set_visible_selected'); ?>",
                type: 'POST',
                dataType: 'json',
                data: postData
            }).done(function(resp){
                if(resp && resp.success){
                    swal({ title: 'Updated', text: 'Customer advertisement visibility updated.', timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                } else {
                    swal({ title: 'Error', text: (resp && resp.message) ? resp.message : 'Unable to update visibility', icon: 'error' });
                }
            });
        });
    });

});
</script>