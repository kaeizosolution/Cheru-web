<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<style>
    /* --- FORM STYLES --- */
    .form-label {
        font-weight: 600;
        color: #334155;
        font-size: 13px;
        margin-bottom: 8px;
    }
    
    .form-control, .custom-select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
        height: auto;
        font-size: 14px;
        color: #475569;
    }
    
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* --- TABS --- */
    .custom-tab-nav {
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 25px;
    }
    
    .custom-tab-nav .nav-link {
        border: none;
        color: #64748b;
        font-weight: 500;
        padding: 15px 25px;
        border-bottom: 2px solid transparent;
        transition: all 0.3s;
    }
    
    .custom-tab-nav .nav-link.active {
        color: var(--primary);
        border-bottom: 2px solid var(--primary);
        background: transparent;
    }
    
    .custom-tab-nav .nav-link:hover {
        color: var(--primary);
    }

    /* --- IMAGE UPLOAD --- */
    .image-upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    
    .image-upload-box:hover {
        border-color: var(--primary);
        background: #f1f5f9;
    }
    
    .image-preview-container {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 15px;
    }
    
    .preview-img-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    
    .preview-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }
</style>

<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles mb-4">
            <div class="col-lg-6 p-0">
                <h3 class="text-primary" style="font-weight: 700;">Add New Product</h3>
            </div>
            <div class="col-lg-6 p-0 text-right">
                <a href="<?= base_url().$TYPE; ?>/product" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <form action="<?= base_url().$TYPE; ?>/product/add" method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="post_title" class="form-control" required placeholder="e.g. Premium T-Shirt">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Product Name (Spanish)</label>
                                    <input type="text" name="post_title_es" class="form-control" placeholder="Nombre del producto">
                                </div>
                            </div>

                            <ul class="nav nav-tabs custom-tab-nav mt-3" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="desc-tab" data-toggle="tab" href="#desc" role="tab">Description</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="data-tab" data-toggle="tab" href="#data" role="tab">Data & Shipping</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="attr-tab" data-toggle="tab" href="#attr" role="tab">Attributes (Var)</a>
                                </li>
                            </ul>

                            <div class="tab-content" id="myTabContent">
                                
                                <div class="tab-pane fade show active" id="desc" role="tabpanel">
                                    <div class="form-group mb-4">
                                        <label class="form-label">Description (English)</label>
                                        <textarea name="post_content" class="form-control" rows="5"></textarea>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="form-label">Description (Spanish)</label>
                                        <textarea name="post_content_es" class="form-control" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="data" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">SKU <span class="text-danger">*</span></label>
                                            <input type="text" name="sku" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Stock Quantity</label>
                                            <input type="number" name="stock" class="form-control" value="0">
                                        </div>
                                    </div>
                                    
                                    <h6 class="section-title mt-3">Shipping Dimensions</h6>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Weight (kg)</label>
                                            <input type="text" name="weight" class="form-control">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Length (cm)</label>
                                            <input type="text" name="length" class="form-control">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Width (cm)</label>
                                            <input type="text" name="width" class="form-control">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Height (cm)</label>
                                            <input type="text" name="height" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="attr" role="tabpanel">
                                    <div id="simple_product_alert" class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> Select "Variable Product" in the Product Type box on the right to enable this section.
                                    </div>

                                    <div id="variable_product_interface" style="display:none;">
                                        
                                        <div class="card bg-light border-0 mb-3">
                                            <div class="card-body p-3">
                                                <h6 class="font-weight-bold">1. Choose Attributes</h6>
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <select id="attribute_selector" class="form-control">
                                                            <option value="">Select Attribute (e.g. Color, Size)</option>
                                                            <?php if(isset($all_attributes) && $all_attributes): ?>
                                                                <?php foreach($all_attributes as $attr): ?>
                                                                    <option value="<?= $attr->attribute_id; ?>"><?= $attr->name; ?></option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-secondary btn-block" onclick="loadAttributeValues()">Load Values</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="attributes_wrapper">
                                            </div>

                                        <div class="mt-3 mb-3 border-top pt-3">
                                            <button type="button" class="btn btn-primary" onclick="generateVariations()">
                                                <i class="fa fa-cogs"></i> Generate Variations
                                            </button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="variations_table" style="display:none;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Variation</th>
                                                        <th width="150">Price ($)</th>
                                                        <th width="150">Sale Price ($)</th>
                                                        <th width="100">Stock</th>
                                                        <th width="50">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="variations_body"></tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header customHeader">
                            <h4 class="card-title">Product Images</h4>
                        </div>
                        <div class="card-body">
                            <div class="image-upload-box" onclick="document.getElementById('gallery_upload').click()">
                                <i class="fa fa-cloud-upload fa-3x text-primary mb-3"></i>
                                <h5 class="font-weight-bold">Click to upload images</h5>
                                <p class="text-muted small">Supports JPG, PNG, WEBP</p>
                                <input type="file" name="upload_Files[]" id="gallery_upload" multiple style="display:none" onchange="previewImages(this)">
                            </div>
                            <div class="image-preview-container" id="preview_box">
                                </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Product Status</label>
                                <select name="enabled" class="form-control">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                             <div class="form-group mb-3">
                                <label class="form-label">Product Type</label>
                                <select name="type" class="form-control" id="product_type">
                                    <option value="simple" selected>Simple Product</option>
                                    <option value="variable">Variable Product</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm">
                                <i class="fa fa-save"></i> Save Product
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header customHeader pt-3 pb-3">
                            <h6 class="card-title" style="font-size: 15px;">Pricing</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Regular Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" name="regular_price" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Sale Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" name="sale_price" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header customHeader pt-3 pb-3">
                            <h6 class="card-title" style="font-size: 15px;">Associations</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Category</label>
                                <select name="cat_id" id="cat_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php if(isset($categories_obj) && !empty($categories_obj)): ?>
                                        <?php foreach($categories_obj as $cat): ?>
                                            <option value="<?= $cat->id; ?>"><?= $cat->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group mb-3" id="sub_cat_div" style="display:none;">
                                <label class="form-label">Sub Category</label>
                                <select name="sub_cat_id" id="sub_cat_id" class="form-control">
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>
                            
                             <div class="form-group mb-3">
                                <label class="form-label">Brand</label>
                                <select name="brand_id" class="form-control">
                                    <option value="">Select Brand</option>
                                    </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
// --- 1. IMAGE PREVIEW ---
function previewImages(input) {
    var preview = document.getElementById('preview_box');
    preview.innerHTML = ''; 
    if (input.files) {
        var filesAmount = input.files.length;
        for (i = 0; i < filesAmount; i++) {
            var reader = new FileReader();
            reader.onload = function(event) {
                var html = '<div class="preview-img-wrapper"><img src="'+event.target.result+'"></div>';
                $($.parseHTML(html)).appendTo(preview);
            }
            reader.readAsDataURL(input.files[i]);
        }
    }
}

// --- 2. CATEGORY AJAX ---
$(document).ready(function() {
    $('#cat_id').change(function(){
        var cat_id = $(this).val();
        var csrfName = '<?= $csrf->name; ?>';
        var csrfHash = '<?= $csrf->hash; ?>';
        
        if(cat_id != ''){
            $.ajax({
                url: "<?= base_url().$TYPE; ?>/product/ajax_sub_cat",
                method: "POST",
                data: {category_id: cat_id, [csrfName]: csrfHash},
                dataType: "json",
                success: function(response) {
                    if(response.status == 1){
                        var html = '<option value="">Select Sub Category</option>';
                        $.each(response.data, function(index, value){
                            html += '<option value="'+value.id+'">'+value.name+'</option>';
                        });
                        $('#sub_cat_id').html(html);
                        $('#sub_cat_div').slideDown();
                    } else {
                        $('#sub_cat_div').slideUp();
                        $('#sub_cat_id').html('');
                    }
                }
            });
        } else {
            $('#sub_cat_div').slideUp();
        }
    });

    // --- 3. PRODUCT TYPE TOGGLE ---
    $('#product_type').change(function(){
        if($(this).val() == 'variable'){
            $('#simple_product_alert').hide();
            $('#variable_product_interface').fadeIn();
            $('#attr-tab').tab('show'); // Switch to tab automatically
        } else {
            $('#variable_product_interface').hide();
            $('#simple_product_alert').show();
        }
    });
});

// --- 4. ATTRIBUTE LOGIC ---

// A. Load Attribute Values (AJAX)
function loadAttributeValues() {
    var attr_id = $('#attribute_selector').val();
    var attr_name = $('#attribute_selector option:selected').text();
    var csrfName = '<?= $csrf->name; ?>';
    var csrfHash = '<?= $csrf->hash; ?>';

    if(attr_id == '') { alert('Please select an attribute first'); return; }

    // Check if already added
    if($('#attr_block_'+attr_id).length > 0) { alert('Attribute already added'); return; }

    $.ajax({
        url: "<?= base_url().$TYPE; ?>/product/get_attr", // Endpoint from your controller
        method: "POST",
        data: {id: attr_id, [csrfName]: csrfHash},
        dataType: "json",
        success: function(response) {
            // Depending on controller, response might be direct data or {status:1, data:...}
            var items = response.data || response; 

            var html = '<div class="card mb-2" id="attr_block_'+attr_id+'"><div class="card-body p-2">';
            html += '<h6 class="text-primary">'+attr_name+' <button type="button" class="btn btn-sm btn-danger float-right" onclick="$(\'#attr_block_'+attr_id+'\').remove()">Remove</button></h6>';
            html += '<div class="d-flex flex-wrap gap-2">';
            
            $.each(items, function(key, val){
                 html += '<div class="custom-control custom-checkbox mr-3">';
                 html += '<input type="checkbox" class="custom-control-input attr-checkbox" id="chk_'+key+'" value="'+key+'" data-name="'+val+'">';
                 html += '<label class="custom-control-label" for="chk_'+key+'">'+val+'</label>';
                 html += '</div>';
            });

            html += '</div></div></div>';
            $('#attributes_wrapper').append(html);
        }
    });
}

// B. Generate Variations Table
function generateVariations() {
    var selected = [];
    
    // Collect all checked items
    $('.attr-checkbox:checked').each(function() {
        selected.push({
            id: $(this).val(),
            name: $(this).data('name')
        });
    });

    if(selected.length == 0) { alert('Please select at least one attribute value.'); return; }

    var tbody = $('#variations_body');
    tbody.html(''); // Clear previous
    $('#variations_table').show();

    $.each(selected, function(index, item){
        var row = '<tr>';
        row += '<td><span class="badge badge-info">'+item.name+'</span>';
        row += '<input type="hidden" name="attr_itemid[]" value="'+item.id+'"></td>';
        
        row += '<td><input type="text" name="_regular_price[]" class="form-control form-control-sm" placeholder="100"></td>';
        row += '<td><input type="text" name="_sale_price[]" class="form-control form-control-sm" placeholder="90"></td>';
        row += '<td><input type="number" name="_stock[]" class="form-control form-control-sm" value="10"></td>';
        row += '<td><button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-trash"></i></button></td>';
        
        row += '</tr>';
        tbody.append(row);
    });
}
</script>