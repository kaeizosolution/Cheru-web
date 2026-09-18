<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<div class="container-fluid" style="padding-top: 95px;">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Returns</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <?php echo $breadcrumbs; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header-custom">
            <h4 class="card-title-custom">Return Requests</h4>
            <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#returns_filter">
                <i class="fa fa-filter"></i> Filter
            </button>
        </div>

        <div class="card-body" style="padding: 25px;">
            <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>
            <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped" id="table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>Customer</th>
                            <th>Vendor</th>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="returns_filter" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Returns</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="filter_data_filter">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-muted small">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All</option>
                                <option value="requested">Requested</option>
                                <option value="initiated">Initiated</option>
                                <option value="accepted">Accepted</option>
                                <option value="completed">Completed</option>
                                <option value="refunded">Refunded</option>
                                <option value="rejected">Rejected</option>
                                <option value="approved">Approved (Legacy)</option>
                                <option value="inspection_pending">Inspection Pending (Legacy)</option>
                                <option value="pickup_scheduled">Pickup Scheduled (Legacy)</option>
                                <option value="picked_up">Picked Up (Legacy)</option>
                                <option value="approved_refund">Approved Refund (Legacy)</option>
                                <option value="refund_completed">Refund Completed (Legacy)</option>
                                <option value="return_cancelled">Return Cancelled (Legacy)</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-muted small">Vendor</label>
                            <select class="form-control" id="vendor_id" name="vendor_id">
                                <option value="">All</option>
                                <?php if(!empty($vendors)): foreach($vendors as $v):
                                    $label = '';
                                    if (isset($v->store_name) && $v->store_name !== '') $label = (string)$v->store_name;
                                    elseif (isset($v->name) && $v->name !== '') $label = (string)$v->name;
                                    if ($label === '') $label = 'Vendor #'.(int)$v->vendor_id;
                                ?>
                                    <option value="<?= (int)$v->vendor_id ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small">Date From</label>
                            <input type="date" id="date_from" name="date_from" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-muted small">Date To</label>
                            <input type="date" id="date_to" name="date_to" class="form-control" />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold text-muted small">Product</label>
                            <input type="text" id="product" name="product" class="form-control" placeholder="Search product name" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-light mr-2 rounded reset-btn" type="button">Clear</button>
                            <button class="btn btn-primary btnSubmit rounded" type="button">Submit</button>
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

    $('#filter_data_filter').on('submit', function(e){
        e.preventDefault();
        dataTable();
        $('#returns_filter .close').click();
    });

    $('.btnSubmit').on('click', function(){
        dataTable();
        $('#returns_filter .close').click();
    });

    $('.reset-btn').on('click', function(){
        $('#filter_data_filter').trigger('reset');
    });

});

function dataTable() {
    table = $('#table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: false,
        ordering: false,
        lengthChange: false,
        destroy: true,
        pageLength: <?= (int)$page_count ?>,
        ajax: {
            url: '/<?= $TYPE; ?>/returns/index_ajax_returns',
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                d['<?= $csrf->name; ?>'] = '<?= $csrf->hash; ?>';
                d.filter = {
                    status: $('#status').val(),
                    vendor_id: $('#vendor_id').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    product: $('#product').val()
                };
            }
        },
        columns: [
            { data: 'return_id' },
            { data: 'customer' },
            { data: 'vendor' },
            { data: 'product' },
            { data: 'status' },
            { data: 'date' },
            { data: 'action' }
        ],
        columnDefs: [
            { targets: [4,6], orderable: false, searchable: false }
        ],
        language: {
            paginate: { next: '<i class="fa fa-chevron-right"></i>', previous: '<i class="fa fa-chevron-left"></i>' }
        }
    });
}
</script>
