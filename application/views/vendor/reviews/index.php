<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<!--**********************************
Content body start
***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="mb-sm-4 d-flex flex-wrap align-items-center text-head">
            <h2 class="font-w600 mb-2 me-auto">Product Reviews &amp; Ratings</h2>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="card customCard">
                    <div class="card-header">Customer Reviews on Your Products</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reviews_table" class="display customTableProduct" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Rating</th>
                                        <th>Title</th>
                                        <th>Review</th>
                                        <th>Status</th>
                                        <th>Date</th>
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
var csrf   = <?= json_encode($csrf); ?>;
var use_api = <?= isset($use_api) && $use_api ? 'true' : 'false'; ?>;
var access_token = '<?= isset($access_token) ? $access_token : ''; ?>';
</script>
<script src="/assets/js/vendor/pages/reviews.js?r=1"></script>
<!--**********************************
    Content body end
***********************************-->
