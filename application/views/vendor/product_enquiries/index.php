<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<!--**********************************
Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="mb-sm-4 d-flex flex-wrap align-items-center text-head">
            <h2 class="font-w600 mb-2 me-auto">Messagner &mdash; Product Enquiries</h2>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="card customCard">
                    <div class="card-header">Customer Enquiries on Your Products</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="vendor_enquiries_table" class="display customTableProduct" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Action</th>
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
var csrf         = <?= json_encode($csrf); ?>;
var use_api      = <?= isset($use_api) && $use_api ? 'true' : 'false'; ?>;
var access_token = '<?= isset($access_token) ? $access_token : ''; ?>';
var vendor_enq_base_url = '<?= base_url(); ?>';
</script>
<script src="/assets/js/vendor/pages/product_enquiries.js?r=1"></script>
<!--**********************************
    Content body end
***********************************-->
