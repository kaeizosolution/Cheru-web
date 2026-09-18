<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<?php
// Resolve display currency variables with safe fallbacks
$display_symbol        = isset($display_symbol) ? $display_symbol : '$';
$display_currency_code = isset($display_currency_code) ? $display_currency_code : 'USD';
?>
<!--**********************************
Content body start
***********************************-->
<div class="content-body">
    <!-- row -->
    <div class="container-fluid">
        <div class="mb-sm-4 d-flex flex-wrap align-items-center text-head">
            <h2 class="font-w600 mb-2 me-auto">Earning</h2>
            <span class="badge badge-primary ms-2" style="font-size:13px; padding:6px 12px; border-radius:6px;">
                Displaying in: <?= htmlspecialchars($display_currency_code) ?> <?= htmlspecialchars($display_symbol) ?>
            </span>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="card customCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Billing list</span>
                        <small class="text-muted">All amounts are shown in <strong><?= htmlspecialchars($display_currency_code) ?></strong></small>
                    </div>                   
                    <div class="card-body">                        
                        <div class="table-responsive">
                            <table id="table" class="display customTableProduct">
                                <thead>
                                    <tr>
                                        <th>Item UID</th>
                                        <th>Total Earning (<?= htmlspecialchars($display_symbol) ?>)</th>
                                        <th>Commission (<?= htmlspecialchars($display_symbol) ?>)</th>
                                        <th>My Earning (<?= htmlspecialchars($display_symbol) ?>)</th>
                                        <th>Comm %</th>
                                        <th>Payment Mode</th>
                                        <th>Paid Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>&nbsp;</tbody>
                            </table>                                   
                        </div>
                    </div>                  
                </div>
            </div>          
        </div>
    </div>
</div>


<script>

$("#data_filter").submit(function(){
    event.preventDefault();
    dataTable();
    $("#order_filter .close").click()
    
});
$('.btnSubmit').on('click', function (e){
    dataTable();
    $("#order_filter .close").click()
});


var table;
$(document).ready(function() {
    dataTable();
});


function dataTable()
{
    table = $('#table').DataTable({
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "responsive": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "orderCellsTop": true,
        "destroy": true,
        "pageLength": <?= $page_count ?>,
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": <?php if (isset($use_api) && $use_api) { ?> "<?= base_url('api/v1/vendor/earnings') ?>" <?php } else { ?> "/<?= $TYPE; ?>/earning/get-vendor-amount" <?php } ?>,
            "type": "POST",
            <?php if (isset($use_api) && $use_api && !empty($access_token)) { ?>
            "headers": {
                "Authorization": "Bearer <?= $access_token ?>"
            },
            <?php } ?>
            "data": function(d) {
                <?php if (!isset($use_api) || !$use_api) { ?>
                d["<?= $csrf->name;?>"] = "<?= $csrf->hash; ?>";
                d.vendor_id = "<?= $vendor_id; ?>";
                <?php } ?>
                return d;
            },
            "dataType": 'json',
            complete: function () {
               $('[data-toggle="tooltip"]').tooltip();
            },
        },
        "language": {
           "paginate": {
           "next": '&raquo;', // or '→'
           "previous": '&laquo;' // or '←'
           }
        },
        "dom": '<"top"i>t<"bottom"flp><"clear">',
        "columnDefs": [ {
            "targets": -1,
            "data": null,         
            "defaultContent": ''
            },
            {
                'targets': 0,
                'searchable':false,
                'orderable':false,
                'className': 'dt-body-center',          
            }
         ],
       "columns": [
            {"data" : "order_uid"},       // 1. UID
            {"data" : "total_earning"},   // 2. Total
            {"data" : "commission_val"},  // 3. Comm Amt
            {"data" : "vendor_earning"},  // 4. Vendor Amt
            {"data" : "commission_pct"},  // 5. %
            {"data" : "payment_mode"},    // 6. Mode (Text only)
            {"data" : "paid_date"},       // 7. Date (Text only)
            {
                "data" : "status_html",   // 8. Status Badge
                "render": function(data, type, row) {
                    if (data && data.indexOf('<') !== -1) {
                        return data;
                    }
                    var badge = row.status_badge || 'badge-warning';
                    var status = row.status || data || '';
                    return '<span class="badge ' + badge + '">' + status + '</span>';
                }
            }
        ]
    } );
}

    // set default dates
    var start = new Date();
    // set end date to max one year period:
    //var end = new Date(new Date().setYear(start.getFullYear()+1));


$('#date_created').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    autoUpdateInput: false,
    autoApply: true,
    drops: "down",
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
    autoApply: true,
    drops: "down",
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
});

$('#last_updated').on('apply.daterangepicker', function (ev, picker) {
    $('#last_updated').val(picker.startDate.format('YYYY-MM-DD'));
    var ret_date = check_date();
    if(ret_date)
    {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    }
    else
    {
        $(this).val('');
        alert("To Date greater then Start date");
    }
});

function check_date() {
    var startDate = new Date($('#date_created').val());
    var endDate = new Date($('#last_updated').val());
    if (startDate < endDate)
    return 1;
    else
    return 0;
    
}
</script>
<!--**********************************
    Content body end
***********************************-->

