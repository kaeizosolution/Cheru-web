<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Product - Alibaba Style</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f4f7f6; padding-top: 30px; }
        .card { border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card-header { background-color: #fff; font-weight: bold; border-bottom: 2px solid #eee; }
        .variation-row { background: #fafafa; }
        .attr-checkbox-group { display: flex; flex-wrap: wrap; gap: 15px; }
        .price-tier-row { border-left: 4px solid #ffc107; padding: 10px; margin-bottom: 10px; background: #fff9e6; }
        .image-preview-container { display: flex; gap: 5px; margin-top: 5px; }
        .image-preview-container img { width: 50px; height: 50px; object-fit: cover; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4 text-center">Add New Product</h2>
    
    <form id="productForm" action="<?=base_url('products/save')?>" method="POST" enctype="multipart/form-data">
        
        <div class="card">
            <div class="card-header text-primary">1. Basic Information</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control" placeholder="e.g. iPhone 15 Pro or Herbal Face Wash" required>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="electronics">Electronics</option>
                            <option value="beauty">Beauty & Care</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Sub Category</label>
                        <select name="sub_category" class="form-control">
                            <option value="mobiles">Mobiles</option>
                            <option value="skincare">Skincare</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Sub-Sub Category</label>
                        <input type="text" name="sub_sub_category" class="form-control" placeholder="e.g. Smart Phones">
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label>Product Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Write detailed description..."></textarea>
                </div>
            </div>
        </div>

        <div class="card border-info">
            <div class="card-header bg-info text-white">2. Select Product Attributes (Dynamic)</div>
            <div class="card-body">
                <p class="text-muted small">Select which features your product has. This will update the Variation table below.</p>
                <div class="attr-checkbox-group">
                    <label class="btn btn-outline-secondary">
                        <input type="checkbox" class="attr-check" data-label="Color" value="color"> Color
                    </label>
                    <label class="btn btn-outline-secondary">
                        <input type="checkbox" class="attr-check" data-label="RAM" value="ram"> RAM
                    </label>
                    <label class="btn btn-outline-secondary">
                        <input type="checkbox" class="attr-check" data-label="Storage" value="storage"> Storage
                    </label>
                    <label class="btn btn-outline-secondary">
                        <input type="checkbox" class="attr-check" data-label="Size" value="size"> Size/Weight
                    </label>
                    <label class="btn btn-outline-secondary">
                        <input type="checkbox" class="attr-check" data-label="Scent" value="scent"> Scent/Flavor
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>3. Variations & Images</span>
                <button type="button" class="btn btn-primary btn-sm" id="addVarRow">+ Add Variation Row</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="variationTable">
                    <thead class="bg-light text-center">
                        <tr id="varHeader">
                            <th style="width: 300px;">Images (Max 5 per variation)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="varBody">
                        </tbody>
                </table>
            </div>
            <div id="noAttrMsg" class="p-4 text-center text-muted">
                Please select at least one attribute above to start adding variations.
            </div>
        </div>

        <div class="card border-warning">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <strong>4. Wholesale Tier Pricing (QTY Wise)</strong>
                <button type="button" class="btn btn-dark btn-sm" id="addPriceTier">+ Add Pricing Tier</button>
            </div>
            <div class="card-body" id="tierContainer">
                <div class="row price-tier-row align-items-center">
                    <div class="col-md-3">
                        <label class="small">Min Qty</label>
                        <input type="number" name="tier_min[]" class="form-control" placeholder="e.g. 1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small">Max Qty</label>
                        <input type="number" name="tier_max[]" class="form-control" placeholder="e.g. 100">
                    </div>
                    <div class="col-md-4">
                        <label class="small">Price per Unit ($)</label>
                        <input type="number" step="0.01" name="tier_price[]" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2 text-right mt-3">
                        <small class="text-muted">Base Tier</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <button type="submit" class="btn btn-success btn-lg btn-block shadow">Publish Product to Marketplace</button>
        </div>

    </form>
</div>

<script>
$(document).ready(function() {
    let variationCount = 0;

    // 1. Listen for Attribute Checkbox changes
    $('.attr-check').on('change', function() {
        const checkedCount = $('.attr-check:checked').length;
        
        if(checkedCount > 0) {
            $('#noAttrMsg').hide();
            $('#variationTable').show();
            updateHeaders();
        } else {
            $('#noAttrMsg').show();
            $('#variationTable').hide();
            $('#varBody').empty(); // Reset variations if no attributes
        }
    });

    function updateHeaders() {
        $('.dynamic-head').remove();
        $('.attr-check:checked').each(function() {
            let label = $(this).data('label');
            $('#varHeader').prepend(`<th class="dynamic-head">${label}</th>`);
        });
    }

    // 2. Add Variation Row
    $('#addVarRow').click(function() {
        const selectedAttrs = $('.attr-check:checked');
        
        if(selectedAttrs.length === 0) {
            alert('Please select at least one attribute (like Color or Size) first!');
            return;
        }

        variationCount++;
        let rowHtml = `<tr class="var-row">`;
        
        // Dynamic Input Columns
        selectedAttrs.each(function() {
            let attrKey = $(this).val();
            rowHtml += `<td>
                <input type="text" name="vars[${variationCount}][${attrKey}]" class="form-control" placeholder="Value" required>
            </td>`;
        });

        // Image & Remove Columns
        rowHtml += `
            <td>
                <input type="file" name="var_images_${variationCount}[]" class="form-control-file mb-1" multiple accept="image/*">
                <small class="text-muted">Group upload (Max 5)</small>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-var">×</button>
            </td>
        </tr>`;

        $('#varBody').append(rowHtml);
    });

    // 3. Add Price Tier Row
    $('#addPriceTier').click(function() {
        let tierHtml = `
        <div class="row price-tier-row align-items-center">
            <div class="col-md-3">
                <input type="number" name="tier_min[]" class="form-control" placeholder="Min Qty" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="tier_max[]" class="form-control" placeholder="Max Qty">
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01" name="tier_price[]" class="form-control" placeholder="Price per Unit" required>
            </div>
            <div class="col-md-2 text-right">
                <button type="button" class="btn btn-outline-danger btn-sm remove-tier">Remove</button>
            </div>
        </div>`;
        $('#tierContainer').append(tierHtml);
    });

    // 4. Remove Handlers
    $(document).on('click', '.remove-var', function() { $(this).closest('tr').remove(); });
    $(document).on('click', '.remove-tier', function() { $(this).closest('.price-tier-row').remove(); });

});
</script>

</body>
</html>
