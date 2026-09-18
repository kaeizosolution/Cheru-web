<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Product Creation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f8f9fa; padding: 40px 0; }
        .card { border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); margin-bottom: 2rem; }
        .card-header { font-weight: bold; background-color: #fff; }
        .attr-badge { cursor: pointer; transition: 0.3s; }
        .price-tier-row { background: #fffdf5; border: 1px dashed #ffe082; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <form id="productForm" action="<?=base_url('products/save')?>" method="POST" enctype="multipart/form-data">
        
        <div class="card">
            <div class="card-header border-bottom">PRODUCT CORE DETAILS</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control" placeholder="e.g. Nivea Face Wash" required>
                </div>
                <div class="row">
                    <div class="col-md-6"><label>Category</label><select name="cat" class="form-control"><option>Skincare</option><option>Electronics</option></select></div>
                    <div class="col-md-6"><label>Sub-Category</label><input type="text" name="sub_cat" class="form-control"></div>
                </div>
            </div>
        </div>

        <div class="card border-primary">
            <div class="card-header bg-primary text-white">STEP 1: ENABLE VARIATION TYPES</div>
            <div class="card-body">
                <div class="row" id="attributeTriggerList">
                    </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>STEP 2: CONFIGURE VARIATIONS</span>
                <button type="button" class="btn btn-dark btn-sm" id="addVarRow">+ Add New Variation</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="variationTable" style="display:none;">
                    <thead class="thead-light" id="varHeader">
                        <th>Images (Max 5)</th>
                        <th>Action</th>
                    </thead>
                    <tbody id="varBody"></tbody>
                </table>
            </div>
            <div id="noAttrWarning" class="p-5 text-center text-muted">
                Select variation types above (e.g., Color or Scent) to start adding products.
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <strong>STEP 3: QUANTITY-BASED PRICE (QTY WISE)</strong>
                <button type="button" class="btn btn-sm btn-outline-dark" id="addTier">+ Add Price Tier</button>
            </div>
            <div class="card-body" id="tierContainer">
                <div class="row price-tier-row align-items-end">
                    <div class="col-md-3"><label>Min Qty</label><input type="number" name="t_min[]" class="form-control" value="1"></div>
                    <div class="col-md-3"><label>Max Qty</label><input type="number" name="t_max[]" class="form-control" value="100"></div>
                    <div class="col-md-4"><label>Price Per Unit</label><input type="number" step="0.01" name="t_price[]" class="form-control" placeholder="0.00"></div>
                    <div class="col-md-2 text-muted">Base Price</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg btn-block">SAVE PRODUCT</button>
    </form>
</div>

<script>
$(document).ready(function() {
    
    // --- PREDEFINED CONFIGURATION ---
    // You can also fetch this via AJAX from your database
    const attrRegistry = {
        'color':   { label: 'Color', options: ['Red', 'Blue', 'Green', 'Black', 'White', 'Silver'] },
        'ram':     { label: 'RAM', options: ['4GB', '8GB', '12GB', '16GB', '32GB'] },
        'storage': { label: 'Storage', options: ['64GB', '128GB', '256GB', '512GB', '1TB'] },
        'scent':   { label: 'Scent/Flavor', options: ['Lemon', 'Aloe Vera', 'Charcoal', 'Mint', 'Strawberry'] },
        'size':    { label: 'Weight/Size', options: ['50ml', '100ml', '200ml', '500ml', '1kg'] }
    };

    // Initialize Checkboxes
    Object.keys(attrRegistry).forEach(key => {
        $('#attributeTriggerList').append(`
            <div class="col-md-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input attr-trigger" id="chk_${key}" value="${key}">
                    <label class="custom-control-label" for="chk_${key}">${attrRegistry[key].label}</label>
                </div>
            </div>
        `);
    });

    let varRowCount = 0;

    // Toggle Table Visibility
    $('.attr-trigger').on('change', function() {
        const checked = $('.attr-trigger:checked');
        if(checked.length > 0) {
            $('#variationTable').show();
            $('#noAttrWarning').hide();
            updateHeaders();
        } else {
            $('#variationTable').hide();
            $('#noAttrWarning').show();
            $('#varBody').empty();
        }
    });

    function updateHeaders() {
        $('.dyn-h').remove();
        $('.attr-trigger:checked').each(function() {
            let key = $(this).val();
            $('#varHeader').prepend(`<th class="dyn-h">${attrRegistry[key].label}</th>`);
        });
    }

    // ADD VARIATION ROW
    $('#addVarRow').click(function() {
        const selected = $('.attr-trigger:checked');
        if(selected.length === 0) return alert('Select at least one attribute first!');

        varRowCount++;
        let row = `<tr class="var-row">`;

        selected.each(function() {
            let key = $(this).val();
            let options = attrRegistry[key].options;
            
            // Generate Dropdown
            let selectHtml = `<td><select name="vars[${varRowCount}][${key}]" class="form-control" required>`;
            selectHtml += `<option value="">Select ${attrRegistry[key].label}</option>`;
            options.forEach(opt => {
                selectHtml += `<option value="${opt}">${opt}</option>`;
            });
            selectHtml += `<option value="Other">Other...</option></select></td>`;
            
            row += selectHtml;
        });

        row += `
            <td><input type="file" name="v_images_${varRowCount}[]" multiple class="form-control-file"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
        </tr>`;

        $('#varBody').append(row);
    });

    // ADD PRICE TIER
    $('#addTier').click(function() {
        let tier = `
        <div class="row price-tier-row align-items-end">
            <div class="col-md-3"><input type="number" name="t_min[]" class="form-control" placeholder="Min Qty"></div>
            <div class="col-md-3"><input type="number" name="t_max[]" class="form-control" placeholder="Max Qty"></div>
            <div class="col-md-4"><input type="number" step="0.01" name="t_price[]" class="form-control" placeholder="0.00"></div>
            <div class="col-md-2"><button type="button" class="btn btn-link text-danger remove-tier">Remove</button></div>
        </div>`;
        $('#tierContainer').append(tier);
    });

    // Remove Elements
    $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); });
    $(document).on('click', '.remove-tier', function() { $(this).closest('.price-tier-row').remove(); });
});
</script>

</body>
</html>
