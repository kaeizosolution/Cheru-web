<?php $path = base_url(); ?>
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
        --sidebar-color: #191c4d;
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

    .card-header.customHeader {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        border-radius: 16px 16px 0 0;
    }

    .card-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
    }

    /* --- BUTTONS --- */
    .btn-custom,
    .btn-primary,
    .btn-outline-primary {
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }

    /* --- TABLE STYLES --- */
    #table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
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

    /* --- STATUS BADGES --- */
    .badge-custom {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }

    .badge-active {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .badge-inactive {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

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
    }

    /* Popover Buttons Style */
    .custom-popover-btn {
        margin: 0;
        border-radius: 4px;
        border: none;
        padding: 6px 10px;
        color: #fff !important;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
    }

    .custom-popover-btn i {
        color: #fff !important;
        margin: 0;
    }

    .btn-status-active {
        background-color: var(--success);
    }

    .btn-status-inactive {
        background-color: var(--danger);
    }

    .btn-edit-pop {
        background-color: var(--primary);
    }

    .btn-edit-pop:hover {
        background-color: #4f46e5;
    }

    /* --- BULK FILTER BAR --- */
    .basic-form {
        background: #f8fafc;
        padding: 10px 15px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .form-control {
        border-radius: 6px;
    }

    /* --- POPOVER FIXES (CRITICAL) --- */
    .popover {
        /* Override Bootstrap/Theme defaults to remove white space */
        max-width: none !important;
        width: auto !important;
        min-width: 0px !important;
        border-radius: 8px !important;
        border: none !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        z-index: 1050;
    }

    /* Remove padding from the container so we control it */
    .popover-body,
    .popover-content {
        padding: 0 !important;
        margin: 0 !important;
    }

    .popover .arrow {
        margin: 0 auto;
    }
</style>

<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-lg-6 p-0">
                <h3 class="text-primary"
                    style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b !important;">Product
                    List</h3>
            </div>
            <?= $breadcrumbs ?>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if (($this->session->flashdata('error')) || validation_errors() != '') { ?>
                    <div class="alert alert-danger m-3 rounded">
                        <?= validation_errors(); ?>
                        <?= $this->session->flashdata('error') ?>
                    </div>
                <?php } ?>
                <?php if ($this->session->flashdata('success')) { ?>
                    <div class="alert alert-success m-3 rounded">
                        <?= $this->session->flashdata('success') ?>
                    </div>
                <?php } ?>

                <div class="card">
                    <div class="card-header customHeader">
                        <h4 class="card-title" style="margin:0; font-weight:600; font-size:18px;">All Products</h4>

                        <div class="mt-2 transactionBtn" style="display:flex; gap:10px;">
                            <a onclick="window.location.href = '<?php echo base_url(); ?>vendor/product/export';"
                                href="javascript:void(0)" class="btn btn-outline-primary">
                                <i class="fa fa-arrow-down" aria-hidden="true"></i> Export CSV
                            </a>
                            <a href="<?= base_url() . $TYPE; ?>/item/create" class="btn btn-primary" title="Add New">
                                <i class="fa fa-plus" aria-hidden="true"></i> Add New
                            </a>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#productFilter"
                                class="btn btn-primary" title="Filter">
                                <i class="fa fa-filter"></i> Filter
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="basic-form">
                                    <select class="form-control btn-checkall" id="selectStatus" name="checkbox_all"
                                        style="max-width: 200px;">
                                        <option value="">Update Bulk Status</option>
                                        <option value="1"><?= $page_lang->active; ?></option>
                                        <option value="0"><?= $page_lang->inactive; ?></option>
                                    </select>
                                    <button class="btn btn-primary" type="button"
                                        onclick="checkAll()"><?= $page_lang->submit; ?></button>
                                </div>
                            </div>
                        </div>

                        <div class="dt-ext table-responsive">
                            <table id="table" class="display dataTable custom-table" style="min-width: 545px">
                                <thead>
                                    <tr>
                                        <th width="50"><input type="checkbox" id="selectall" onClick="toggle(this)" />
                                        </th>
                                        <th><?= $page_lang->title; ?></th>
                                        <th>Stock</th>
                                        <th><?= $page_lang->type; ?></th>
                                        <th><?= $page_lang->status; ?></th>
                                        <th><?= $page_lang->date; ?></th>
                                        <th width="50" class="text-center"><?= $page_lang->action; ?></th>
                                    </tr>
                                </thead>
                                <tbody> </tbody>
                            </table>
                        </div>
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
                <h5 class="modal-title font-weight-bold">Filter</h5>
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
                        <div class="col-md-6 mb-4">
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
                            <button class="btn btn-light mr-2 rounded reset-btn"
                                type="button"><?= $page_lang->clear; ?></button>
                            <button class="btn btn-primary btnSubmit rounded"
                                type="button"><?= $page_lang->submit; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // ── API-direct config ─────────────────────────────────────────────────
    var useApi   = <?= (!empty($use_api)) ? 'true' : 'false' ?>;
    var apiUrl   = '<?= base_url("api/v1/vendor/products") ?>';
    var apiToken = '<?= isset($access_token) ? addslashes($access_token) : "" ?>';

    var csrf_name  = "<?= is_object($csrf) ? $csrf->name  : (function(){ $CI =& get_instance(); return $CI->security->get_csrf_token_name(); })() ?>";
    var csrf_value = "<?= is_object($csrf) ? $csrf->hash  : (function(){ $CI =& get_instance(); return $CI->security->get_csrf_hash(); })() ?>";

    // --- Datepicker & Filter Logic ---
    $('#date').daterangepicker({
        autoUpdateInput: false,
        singleDatePicker: true,
        showDropdowns: true,
        locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
    }, function (start, end, label) {
        $('#date').val(start.format('YYYY-MM-DD'));
    });

    $("#date").on('apply.daterangepicker', function (ev, Picker) {
        $('#date').val(Picker.startDate.format('YYYY-MM-DD'));
    });

    $('#date').on('cancel.daterangepicker', function (ev, picker) {
        $('#date').val('');
    });

    $("#filter_product").submit(function (event) {
        event.preventDefault();
        dataTable();
        $("#productFilter .close").click()
    });
    $('.btnSubmit').on('click', function (e) {
        dataTable();
        $("#productFilter .close").click()
    });

    var table;
    $(document).ready(function () {
        dataTable();

        $(".reset-btn").click(function () {
            $("#filter_product").trigger("reset");
        });

        // --- GLOBAL CLOSE POPOVER (Click Outside) ---
        $('body').on('click', function (e) {
            if (!$(e.target).closest('.btn-premium-action').length &&
                !$(e.target).parents('.popover').length) {
                $('.btn-premium-action').popover('hide');
            }
        });
    });

    // Helper: format YYYY-MM-DD → DD-MM-YYYY
    function fmtDate(str) {
        if (!str) return '';
        var p = str.split(/[- ]/);
        if (p.length >= 3) return p[2].substring(0,2) + '-' + p[1] + '-' + p[0];
        return str;
    }

    function dataTable() {
        var filterData = {};
        if ($('#product_name').val().trim()) { filterData.name       = $('#product_name').val().trim(); }
        if ($('#date').val().trim())          { filterData.date_added = $('#date').val().trim(); }
        if ($('#enabled').val().trim())       { filterData.status     = $('#enabled').val().trim(); }

        // ── Choose ajax config: API-direct or legacy web controller ───────
        var ajaxConfig;

        if (useApi && apiToken) {
            // Call the REST API directly from the browser
            ajaxConfig = function (dtData, callback) {
                var pg = dtData.length > 0 ? Math.floor(dtData.start / dtData.length) + 1 : 1;
                var postBody = {
                    page: pg,
                    limit: dtData.length
                };
                if (filterData.name)       postBody.name = filterData.name;
                if (filterData.date_added) postBody.date_added = filterData.date_added;
                if (filterData.status !== undefined && filterData.status !== '') {
                    postBody.status = filterData.status;
                }

                $.ajax({
                    url:          apiUrl,
                    type:         'POST',
                    headers:      { 'Authorization': 'Bearer ' + apiToken },
                    contentType:  'application/json',
                    data:         JSON.stringify(postBody),
                    dataType:     'json',
                    success: function (res) {
                        var products = (res && res.data && Array.isArray(res.data.products)) ? res.data.products : [];
                        var total    = (res && res.data && res.data.pagination) ? parseInt(res.data.pagination.total) || products.length : products.length;
                        var rows = products.map(function (p) {
                            var st   = parseInt(p.status) || 0;
                            var name = $('<div/>').text(p.name || '').html();
                            return {
                                product_id:  p.id,
                                post_title:  st == 1 ? '<a href="javascript:void(0)">' + name + '</a>' : name,
                                stock_count: parseInt(p.stock) || 0,
                                type:        p.product_type || 'simple',
                                status:      st,
                                status_str:  st == 1 ? 'Active' : 'Inactive',
                                date:        fmtDate(p.date_added || ''),
                                action:      ''
                            };
                        });
                        callback({ draw: dtData.draw, recordsTotal: total, recordsFiltered: total, data: rows });
                    },
                    error: function () {
                        callback({ draw: dtData.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                    }
                });
            };
        } else {
            // Legacy fallback: go through the web controller
            var postData = {};
            postData[csrf_name] = csrf_value;
            postData.filter = filterData;
            ajaxConfig = {
                "url":      "/<?= $TYPE; ?>/product/index_ajax_post",
                "data":     postData,
                "type":     "POST",
                "dataType": 'json',
                "complete": function () { $('[data-toggle="tooltip"]').tooltip(); }
            };
        }

        table = $('#table').DataTable({
            "processing":   true,
            "serverSide":   true,
            "responsive":   true,
            "searching":    false,
            "ordering":     false,
            "lengthChange": false,
            "destroy":      true,
            "pageLength":   <?= $page_count ?>,
            "ajax":         ajaxConfig,
            "language": {
                "paginate": { "next": '<i class="fa fa-chevron-right"></i>', "previous": '<i class="fa fa-chevron-left"></i>' }
            },
            "dom": '<"top"i>t<"bottom"flp><"clear">',

            // --- POPOVER INITIALIZATION ---
            "drawCallback": function (settings) {
                $('[data-toggle="tooltip"]').tooltip();
                $('.btn-premium-action').popover({
                    html: true,
                    sanitize: false,
                    trigger: 'manual',
                    container: 'body',
                    placement: 'bottom',
                    content: function () {
                        var $btn = $(this);
                        var id = $btn.attr('data-id');
                        var status = $btn.attr('data-status');

                        var toggle_class = '', toggle_bg = '', toggle_title = '';
                        if (status == 1) {
                            toggle_class = 'fa-toggle-on';
                            toggle_bg    = 'btn-status-inactive';
                            toggle_title = 'Disable';
                        } else {
                            toggle_class = 'fa-toggle-off';
                            toggle_bg    = 'btn-status-active';
                            toggle_title = 'Enable';
                        }

                        return '<div style="display:flex; gap:5px; align-items:center; padding: 6px;">' +
                            '<a href="<?= base_url() ?><?= $TYPE; ?>/item/edit/' + id + '" title="Edit" class="btn custom-popover-btn btn-edit-pop"><i class="fa fa-pencil-alt"></i></a>' +
                            '<button title="' + toggle_title + '" class="btn custom-popover-btn ' + toggle_bg + ' btn-status-toggle" type="button" data-status="' + status + '" data-id="' + id + '"><i class="fa ' + toggle_class + '"></i></button>' +
                            '</div>';
                    }
                }).on("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var _this = this;
                    $('.btn-premium-action').not(_this).popover('hide');
                    $(this).popover("toggle");
                });
            },
            "columnDefs": [
                {
                    "targets": 4,
                    "data": "status_str",
                    "render": function (data, type, row) {
                        if (data == 'Active' || row.status == 1 || row.status == '1') {
                            return '<span class="badge-custom badge-active">Active</span>';
                        } else {
                            return '<span class="badge-custom badge-inactive">Inactive</span>';
                        }
                    }
                },
                {
                    "targets": -1,
                    "data": null,
                    "className": "text-center",
                    "render": function (data, type, row) {
                        return '<button type="button" class="btn btn-premium-action" data-id="' + row.product_id + '" data-status="' + row.status + '"><i class="fa fa-cog"></i></button>';
                    }
                },
                {
                    'targets': 0,
                    'searchable': false,
                    'orderable': false,
                    'className': 'dt-body-center',
                    'render': function (data, type, full, meta) {
                        return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
                    }
                }
            ],
            "columns": [
                { "data": "product_id" },
                { "data": "post_title" },
                { "data": "stock_count" },
                { "data": "type" },
                { "data": "status_str" },
                { "data": "date" },
                { "data": "action" },
            ]
        });
    }

    // --- BUTTON ACTIONS ---

    // 2. Status Button (Inside Popover)
    $(document).on('click', '.btn-status-toggle', function (e) {
        var uid = $(this).attr('data-id');
        var status = $(this).attr('data-status');
        delete_post(uid, status);
    });

    function delete_post(id, status) {
        var postData = {};
        postData[csrf_name] = csrf_value;
        postData.product_id = id;
        postData.status = status;

        $.ajax({
            url: '<?php echo base_url(); ?>vendor/product/ajax_update_product_status',
            type: 'post',
            dataType: "json",
            data: postData,
            success: function (data) {
                if (data.success) {
                    if (data && data.csrf_token) {
                        csrf_value = data.csrf_token;
                    }

                    // Update UI instantly (no need to wait for reload)
                    var newStatus = (data && typeof data.status !== 'undefined') ? String(data.status) : ((String(status) === '1') ? '0' : '1');
                    var $actionBtn = $('.btn-premium-action[data-id="' + id + '"]');
                    $actionBtn.attr('data-status', newStatus);

                    // Update status badge in table row
                    var badgeHtml = (newStatus === '1')
                        ? '<span class="badge-custom badge-active">Active</span>'
                        : '<span class="badge-custom badge-inactive">Inactive</span>';
                    var $row = $actionBtn.closest('tr');
                    // Status column index = 4 (0-based) based on table header
                    $row.find('td').eq(4).html(badgeHtml);

                    $('.btn-premium-action').popover('hide');
                    swal('Status changed Successfully.');
                    table.ajax.reload(null, false);
                }
            },
            error: function (error) { }
        });
    }

    function checkAll() {
        var st = document.getElementById("selectStatus");
        var status = st.options[st.selectedIndex].value;
        var supplierId = [];

        // Only include row checkboxes, not the header select-all checkbox
        $('input[name="id[]"]:checked').each(function (i) {
            supplierId.push($(this).val());
        });

        if (status != '' && supplierId.length > 0) {
            checkall_status_change(supplierId, status);
        }
    }

    function checkall_status_change(id, status) {
        var postData = {};
        postData[csrf_name] = csrf_value;
        postData.id = JSON.stringify(id);
        postData.status = status;

        $.ajax({
            url: '<?php echo base_url(); ?>vendor/product/checkall_status_change',
            type: 'post',
            dataType: "json",
            data: postData,
            success: function (data) {
                if (data.success) {
                    if (data && data.csrf_token) {
                        csrf_value = data.csrf_token;
                    }
                    if (data.MSG) { swal('Session expired.'); }

                    // Update UI instantly for selected rows
                    var newStatus = (data && typeof data.status !== 'undefined')
                        ? String(data.status)
                        : String(status);
                    var badgeHtml = (newStatus === '1')
                        ? '<span class="badge-custom badge-active">Active</span>'
                        : '<span class="badge-custom badge-inactive">Inactive</span>';
                    $('input[name="id[]"]:checked').each(function () {
                        var pid = $(this).val();
                        var $row = $(this).closest('tr');
                        // Status column index = 4 (0-based) based on table header
                        $row.find('td').eq(4).html(badgeHtml);
                        $row.find('.btn-premium-action[data-id="' + pid + '"]')
                            .attr('data-status', newStatus);
                    });
                    $('.btn-premium-action').popover('hide');
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
</script>