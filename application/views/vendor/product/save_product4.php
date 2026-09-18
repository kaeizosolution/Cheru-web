<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advance Product Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root { --primary: #ff6600; --secondary: #212121; --bg: #f5f5f7; }
        body { background-color: var(--bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding-bottom: 50px; }
        .page-header { background: #fff; padding: 20px 0; border-bottom: 1px solid #ddd; margin-bottom: 30px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; overflow: hidden; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 15px 25px; font-weight: 700; color: var(--secondary); display: flex; align-items: center; }
        .card-header i { margin-right: 10px; color: var(--primary); }
        
        /* Type Switcher */
        .type-selector { background: #eee; padding: 5px; border-radius: 10px; display: inline-flex; }
        .type-selector label { padding: 10px 30px; margin: 0; border-radius: 8px; cursor: pointer; transition: 0.3s; font-weight: 600; }
        .type-selector input { display: none; }
        .type-selector label.active { background: var(--primary); color: #fff; }

        .tier-card { background: #fff; border: 1px solid #ffe0b2; border-left: 5px solid var(--primary); position: relative; }
        .btn-primary { background-color: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background-color: #e65c00; border-color: #e65c00; }
        .remove-t { cursor: pointer; }
    </style>
</head>
<body>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="m-0"><i class="fas fa-box-open text-warning"></i> Create New Product</h4>
        </div>
    </div>
</div>

<div class="container">
    <form id="productForm" action="<?=base_url('products/save')?>" method="POST" enctype="multipart/form-data">
        
        <div class="card">
            <div class="card-body text-center">
                <div class="type-selector">
                    <label class="active" id="lblSimple">
                        <input type="radio" name="product_type" value="simple" checked> Simple Product
                    </label>
                    <label id="lblVariable">
                        <input type="radio" name="product_type" value="variable"> Attribute Product
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><i class="fas fa-info-circle"></i> Basic Information</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="product_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>

                <div id="attributeSection" style="display:none;">
                    <div class="card">
                        <div class="card-header">Select Attributes</div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <label class="mr-4"><input type="checkbox" class="attr-check" value="color" data-label="Color"> Color</label>
                                <label class="mr-4"><input type="checkbox" class="attr-check" value="size" data-label="Size"> Size</label>
                                <label class="mr-4"><input type="checkbox" class="attr-check" value="ram" data-label="RAM"> RAM</label>
                                <label class="mr-4"><input type="checkbox" class="attr-check" value="scent" data-label="Scent"> Scent</label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header justify-content-between">
                            <span>Variations</span>
                            <button type="button" class="btn btn-primary btn-sm" id="addVarRow">+ Add Variation</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0" id="variationTable" style="display:none;">
                                <thead>
                                    <tr id="varHeader">
                                        <th>Media (Max 5)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="varBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="simpleImageSection" class="card">
                    <div class="card-header"><i class="fas fa-camera"></i> Product Gallery</div>
                    <div class="card-body border-dashed text-center p-5">
                        <input type="file" name="simple_images[]" multiple>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-wallet"></i> Wholesale Tiers</div>
                    <div class="card-body p-3">
                        <div id="tierContainer">
                            <div class="tier-card p-3 mb-3">
                                <div class="row no-gutters">
                                    <div class="col-6 pr-1"><label class="small">Min Qty</label><input type="number" name="tier_min[]" class="form-control form-control-sm" value="1"></div>
                                    <div class="col-6 pl-1"><label class="small">Max Qty</label><input type="number" name="tier_max[]" class="form-control form-control-sm"></div>
                                    <div class="col-12 mt-2"><label class="small">Price ($)</label><input type="number" step="0.01" name="tier_price[]" class="form-control"></div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-block" id="btnPriceTier">
                            <i class="fas fa-plus"></i> Add Price Tier
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Publish Product</button>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    const attributeData = {
        'color':   ['Red', 'Blue', 'Black', 'White'],
        'ram':     ['8GB', '16GB', '32GB'],
        'size':    ['Small', 'Medium', 'Large', '100ml'],
        'scent':   ['Lemon', 'Aloe', 'Mint']
    };

    let varCount = 0;

    // Fixed: Toggle Radio Logic
    $('input[name="product_type"]').on('change', function() {
        $('.type-selector label').removeClass('active');
        $(this).parent().addClass('active');
        if ($(this).val() === 'simple') {
            $('#simpleImageSection').show(); $('#attributeSection').hide();
        } else {
            $('#simpleImageSection').hide(); $('#attributeSection').show();
        }
    });

    // Fixed: Attribute Header Logic
    $('.attr-check').on('change', function() {
        if($('.attr-check:checked').length > 0) {
            $('#variationTable').show();
            updateTableHeaders();
        } else {
            $('#variationTable').hide(); $('#varBody').empty();
        }
    });

    function updateTableHeaders() {
        $('.dyn-h').remove();
        $('.attr-check:checked').each(function() {
            $('#varHeader').prepend(`<th class="dyn-h">${$(this).data('label')}</th>`);
        });
    }

    // Fixed: Add Variation Row
    $('#addVarRow').click(function() {
        const selected = $('.attr-check:checked');
        if(selected.length === 0) return alert('Select attributes!');
        varCount++;
        let row = `<tr class="var-row">`;
        selected.each(function() {
            let key = $(this).val();
            let opts = attributeData[key] || [];
            let sel = `<td><select name="vars[${varCount}][${key}]" class="form-control form-control-sm">`;
            opts.forEach(o => sel += `<option value="${o}">${o}</option>`);
            row += sel + `</select></td>`;
        });
        row += `<td><input type="file" name="v_img_${varCount}[]" multiple></td>
                <td><button type="button" class="btn btn-link text-danger remove-v p-0"><i class="fas fa-trash"></i></button></td></tr>`;
        $('#varBody').append(row);
    });

    // FIXED: Correct ID and Event Listener for Price Tier
    $('#btnPriceTier').on('click', function() {
        let t = `
        <div class="tier-card p-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="font-weight-bold">Tier Detail</small>
                <span class="text-danger remove-t"><i class="fas fa-times"></i></span>
            </div>
            <div class="row no-gutters">
                <div class="col-6 pr-1"><input type="number" name="tier_min[]" class="form-control form-control-sm" placeholder="Min"></div>
                <div class="col-6 pl-1"><input type="number" name="tier_max[]" class="form-control form-control-sm" placeholder="Max"></div>
                <div class="col-12 mt-2"><input type="number" step="0.01" name="tier_price[]" class="form-control form-control-sm" placeholder="Price"></div>
            </div>
        </div>`;
        $('#tierContainer').append(t);
    });

    // Remove logic
    $(document).on('click', '.remove-v', function() { $(this).closest('tr').remove(); });
    $(document).on('click', '.remove-t', function() { $(this).closest('.tier-card').remove(); });
});
</script>
</body>
</html>
