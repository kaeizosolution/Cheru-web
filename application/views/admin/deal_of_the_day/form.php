<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Deal of the Day</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <?php echo form_open("/$TYPE/deal_of_the_day/".(isset($id) ? 'update/'.$id : 'add'), array('autocomplete' => 'off', 'method' => 'POST')); ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Product</label>
                                    <select class="form-control" name="product_id" required>
                                        <option value="">Select</option>
                                        <?php if(isset($product_obj) && $product_obj){ foreach($product_obj as $p){ ?>
                                        <option value="<?= $p->id ?>" <?= (isset($product_id) && (string)$product_id === (string)$p->id) ? 'selected' : '' ?>><?= htmlspecialchars($p->name) ?></option>
                                        <?php } } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Discount Type</label>
                                    <select class="form-control" name="discount_type" id="discount_type" required>
                                        <option value="FIXED" <?= (isset($discount_type) && (string)$discount_type === 'FIXED') ? 'selected' : '' ?>>FIXED</option>
                                        <option value="PERCENT" <?= (isset($discount_type) && (string)$discount_type === 'PERCENT') ? 'selected' : '' ?>>PERCENT</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Discount Per</label>
                                    <input type="text" class="form-control" name="discount_percent" id="discount_percent" value="<?= isset($discount_percent) ? htmlspecialchars($discount_percent) : '' ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Discount Amount</label>
                                    <input type="text" class="form-control" name="discount_amount" id="discount_amount" value="<?= isset($discount_amount) ? htmlspecialchars($discount_amount) : '' ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Timezone</label>
                                    <input type="text" class="form-control" name="timezone" value="<?= isset($timezone) ? htmlspecialchars($timezone) : 'UTC' ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status">
                                        <option value="1" <?= (!isset($status) || (string)$status === '1') ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= (isset($status) && (string)$status === '0') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Start Date</label>
                                    <input type="datetime-local" class="form-control" name="start_date" value="<?= isset($start_date) ? date('Y-m-d\TH:i', strtotime($start_date)) : '' ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">End Date</label>
                                    <input type="datetime-local" class="form-control" name="end_date" value="<?= isset($end_date) ? date('Y-m-d\TH:i', strtotime($end_date)) : '' ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a href="/<?= $TYPE?>/deal_of_the_day" class="btn btn-light">Cancel</a>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDiscountFields(){
    var dtype = $('#discount_type').val();
    if(dtype === 'PERCENT'){
        $('#discount_percent').prop('disabled', false);
        $('#discount_amount').prop('disabled', true);
    }else{
        $('#discount_percent').prop('disabled', true);
        $('#discount_amount').prop('disabled', false);
    }
}
$(document).ready(function(){
    toggleDiscountFields();
    $('#discount_type').on('change', toggleDiscountFields);
});
</script>

