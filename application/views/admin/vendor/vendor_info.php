<?php 
// Load Language Data
$page_lang = get_page_language_data('admin_page_lang'); 
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?= $page_lang->vendor_details; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab"><?= $page_lang->about_me; ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Product Listing</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab"><?= $page_lang->order_list; ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-4" role="tab"><?= $page_lang->earning; ?></a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                    <div class="profile-about-me">
                                        <div class="profile-personal-info">
                                            <h4 class="h4 mb-4"><?= $page_lang->store_information; ?></h4>

                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5"><?= $page_lang->name; ?> <span class="pull-end">:</span></div>
                                                <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vendor_info->store_name) ? $vendor_info->store_name : ''; ?></span></h5></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5"><?= $page_lang->address; ?><span class="pull-end">:</span></div>
                                                <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vendor_info->address) ? $vendor_info->address : ''; ?></span></h5></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5"><?= $page_lang->mobile; ?> <span class="pull-end">:</span></div>
                                                <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vendor_info->mobile) ? $vendor_info->mobile : ''; ?></span></h5></div>
                                            </div>
                                        </div>
                                        <div class="profile-personal-info">
                                            <h4 class="h4 mb-4">Verification</h4>
                                            <?php
                                                $__vstat = isset($vendor_document_status) ? (string)$vendor_document_status : '';
                                                $__vnote = isset($vendor_document_admin_note) ? (string)$vendor_document_admin_note : '';
                                                $__doc_front = isset($vendor_document_front) ? (string)$vendor_document_front : '';
                                                $__doc_back = isset($vendor_document_back) ? (string)$vendor_document_back : '';
                                                $__admin_doc = isset($vendor_admin_uploaded_doc) ? (string)$vendor_admin_uploaded_doc : '';
                                                $__doc_dir = 'uploads/vendor_documents/';
                                                $__front_url = ($__doc_front !== '') ? base_url($__doc_dir . $__doc_front) : '';
                                                $__back_url = ($__doc_back !== '') ? base_url($__doc_dir . $__doc_back) : '';
                                                $__admin_url = ($__admin_doc !== '') ? base_url($__doc_dir . $__admin_doc) : '';
                                                $__is_image = function($file){
                                                    $ext = strtolower(pathinfo((string)$file, PATHINFO_EXTENSION));
                                                    return in_array($ext, array('jpg','jpeg','png','gif','webp'));
                                                };
                                            ?>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5">Status <span class="pull-end">:</span></div>
                                                <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo ($__vstat !== '') ? ucfirst($__vstat) : 'N/A'; ?></span></h5></div>
                                            </div>
                                            <?php if(trim($__vnote) !== ''){ ?>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">Note <span class="pull-end">:</span></div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo nl2br(htmlspecialchars($__vnote, ENT_QUOTES, 'UTF-8')); ?></span></h5></div>
                                                </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="mb-2">Document Front</label>
                                                    <?php if($__front_url){ ?>
                                                        <?php if($__is_image($__doc_front)){ ?>
                                                            <a href="<?php echo $__front_url; ?>" target="_blank" rel="noopener">
                                                                <img src="<?php echo $__front_url; ?>" class="img-fluid rounded" style="max-height:240px;" alt="Document Front" />
                                                            </a>
                                                        <?php }else{ ?>
                                                            <a class="btn btn-outline-primary" href="<?php echo $__front_url; ?>" target="_blank" rel="noopener">View / Download</a>
                                                        <?php } ?>
                                                    <?php }else{ ?>
                                                        <div class="text-muted">Not uploaded</div>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="mb-2">Document Back</label>
                                                    <?php if($__back_url){ ?>
                                                        <?php if($__is_image($__doc_back)){ ?>
                                                            <a href="<?php echo $__back_url; ?>" target="_blank" rel="noopener">
                                                                <img src="<?php echo $__back_url; ?>" class="img-fluid rounded" style="max-height:240px;" alt="Document Back" />
                                                            </a>
                                                        <?php }else{ ?>
                                                            <a class="btn btn-outline-primary" href="<?php echo $__back_url; ?>" target="_blank" rel="noopener">View / Download</a>
                                                        <?php } ?>
                                                    <?php }else{ ?>
                                                        <div class="text-muted">Not uploaded</div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="mb-2">Document by Admin</label>
                                                    <?php if($__admin_url){ ?>
                                                        <?php if($__is_image($__admin_doc)){ ?>
                                                            <a href="<?php echo $__admin_url; ?>" target="_blank" rel="noopener">
                                                                <img src="<?php echo $__admin_url; ?>" class="img-fluid rounded" style="max-height:240px;" alt="Document by Admin" />
                                                            </a>
                                                        <?php }else{ ?>
                                                            <a class="btn btn-outline-primary" href="<?php echo $__admin_url; ?>" target="_blank" rel="noopener">View / Download</a>
                                                        <?php } ?>
                                                    <?php }else{ ?>
                                                        <div class="text-muted">Not uploaded</div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-personal-info">
                                            <h4 class="h4 mb-4"><?= $page_lang->owner_information; ?></h4>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5"><?= $page_lang->owner_name; ?> <span class="pull-end">:</span></div>

                                                <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vendor_info->fname) ? $vendor_info->fname : ''; ?></span></h5></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5"><?= $page_lang->owner_email; ?> <span class="pull-end">:</span></div>
                                                <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vendor_info->email) ? $vendor_info->email : ''; ?></span></h5></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5"><?= $page_lang->owner_phone; ?><span class="pull-end">:</span></div>
                                                <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vendor_info->mobile) ? $vendor_info->mobile : ''; ?></span></h5></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane mt-5" id="tabs-2" role="tabpanel">
                                    <div class="dt-ext table-responsive">
                                        <table id="ProductTable" class="display dataTable table-striped table-bordered custom-table w-100">
                                            <thead>
                                                <tr>
                                                    <th>Product ID</th>
                                                    <th>Product Image</th>
                                                    <th>Product Name</th>
                                                    <th>Product Mrp</th>
                                                    <th>Product Selling Price</th>
                                                    <th>Product Quantity</th>
                                                    <th>Created Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane order-details mt-5" id="tabs-3" role="tabpanel">
                                    <div class="dt-ext table-responsive">
                                        <table id="table" class="display dataTable table-striped table-bordered custom-table w-100">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Order Information</th> <th>Order Created Date</th>
                                                    <th>Order Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane order-details mt-5" id="tabs-4" role="tabpanel">
                                    <div class="dt-ext table-responsive">
                                        <table id="AmountTable" class="display dataTable table-striped table-bordered custom-table w-100">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th> <th>Order Amount</th> <th><?= $page_lang->admin_commission; ?> (<?=isset($admincom) ? $admincom : 'N/A';?>%)</th>
                                                    <th><?= $page_lang->vendor_earning; ?></th>
                                                    <th><?= $page_lang->mode_of_payment; ?></th>
                                                    <th><?= $page_lang->paid_date; ?></th>
                                                    <th><?= $page_lang->action; ?></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="PaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form name="transfer" id="AmountTransfer" method="Post">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $page_lang->payment_mode; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="messageLabel"></span>
                    <select class="form-control" name="paid_type" id="PaidType" required >
                        <option value=""><?= $page_lang->select_payment_mode; ?></option>
                        <option value="cash"><?= $page_lang->cash; ?></option>
                        <option value="check"><?= $page_lang->check; ?></option>
                        <option value="bank"><?= $page_lang->bank; ?></option>
                    </select>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="earning_id_hdn" id="earning_id_hdn">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $page_lang->close; ?></button>
                    <button type="button" class="btn btn-primary amnt_transfr"><?= $page_lang->amount_transfer; ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    productDataTable(); // TAB 2
    dataTable();        // TAB 3
    amountdataTable();  // TAB 4
    $('select').select2({allowClear: true, placeholder: "Select"});
});

// TAB 2: PRODUCT LISTING
function productDataTable() {
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>", "vendor_id" : "<?= $vendor_id;?>"};
    $('#ProductTable').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "destroy": true,
        "pageLength": <?= $page_count ?>,
        "ajax": {
            "url": "/<?= $TYPE; ?>/vendor/get_vendor_products",
            "data": postData,
            "type": "POST",
            "dataType": 'json'
        },
        "columns": [
            {"data" : "product_id"},
            {"data" : "image"},
            {"data" : "name"},
            {"data" : "mrp"},
            {"data" : "price"},
            {"data" : "quantity"},
            {"data" : "created_date"},
            {"data" : "status"}
        ]
    });
}

// TAB 3: ORDER LISTING
function dataTable() {
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>", "vendor_id" : "<?= $vendor_id;?>"};
    $('#table').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "destroy": true,
        "pageLength": <?= $page_count ?>,
        "ajax": {
            "url": "/<?= $TYPE; ?>/vendor/get-order-list-by-vendor",
            "data": postData,
            "type": "POST",
            "dataType": 'json'
        },
        "columns": [
            {"data" : "order_id"},
            {"data" : "order_info"},
            {"data" : "date"},
            {"data" : "amount"},
            {"data" : "status"}
        ]
    });
}

// TAB 4: EARNING
function amountdataTable() {
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>", "vendor_id" : "<?= $vendor_id;?>"};
    $('#AmountTable').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "destroy": true,
        "pageLength": <?= $page_count ?>,
        "ajax": {
            "url": "/<?= $TYPE; ?>/vendor/get-vendor-amount",
            "data": postData,
            "type": "POST",
            "dataType": 'json'
        },
        "columnDefs": [ {
            "targets": -1,
            "data": null,
            "render": function ( data, type, row ) {
                if(row.paid_status == '1'){
                    return '<?= $page_lang->paid; ?>';
                }else if(row.current_month == '1'){
                    return '<?= $page_lang->in_process; ?>';
                }else{
                    return '<button type="button" class="btn btn-primary btn-sm click_to_pay" data-toggle="modal" data-target="#PaymentModal" data-vamount="'+row.vendor_amount_raw+'" data-earning_id="'+row.earning_id+'"><?= $page_lang->click_to_pay; ?></button>';
                }
            }
        }],
        "columns": [
            {"data" : "order_id"},      // Header: Order ID
            {"data" : "total"},         // Header: Order Amount
            {"data" : "admin_amount"},
            {"data" : "vendor_amount"},
            {"data" : "paid_type"},
            {"data" : "paid_date"},
            {"data" : "action"}
        ]
    });
}

// SHARED MODAL LOGIC
$(document).on('click', '.close', function(){
    $('#AmountTransfer').trigger("reset");
});

$(document).on('click','.click_to_pay', function(){
    var earning_id = $(this).attr('data-earning_id');
    var vendor_amount = $(this).attr('data-vamount');
    $('#earning_id_hdn').val(JSON.stringify({'earning_id': earning_id, 'vendor_amount': vendor_amount}));
});

$(document).on('click','.amnt_transfr', function(){
    var PaidType = $('#PaidType').val();
    if(PaidType == '') {
        $('#messageLabel').html('<span style="color:red"> <?= $page_lang->please_select_payment; ?></span>');
        return false;
    }

    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>", "PaidType": PaidType, "all_info": $('#earning_id_hdn').val()};
    
    swal({
        title: "<?= $page_lang->are_you_sure; ?>",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willTransfer) => {
        if (willTransfer) {
            $.ajax({
                url:'/<?= $TYPE; ?>/vendor/amount-transfer/',
                type:'post',
                dataType:"json",
                data: postData,
                success:function(data) {
                    $('#AmountTable').DataTable().ajax.reload();
                    swal({ title: "Status!", text: data.success, timer: 2000, showConfirmButton: false });
                    $('#PaymentModal').modal('hide');
                }
            });
        }
    });
});
</script>