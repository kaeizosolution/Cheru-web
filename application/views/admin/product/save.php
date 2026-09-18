<?php 
// RESTORED MISSING LOGIC
$TYPE = $_SESSION['type'];
if($this->session->userdata($TYPE)){
    $session_obj = $this->session->userdata($TYPE);
}
$page_lang = get_page_language_data('admin_page_lang'); 
// Helper for data
$prodData = isset($get_product) ? $get_product : []; 
?>

<style>
    :root {
        --primary: #6366f1;
        --secondary: #64748b;
        --dark: #1e293b;
        --light-gray: #f8fafc;
        --border-color: #e2e8f0;
        --danger: #ef4444;
    }

    /* --- CARD --- */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: visible !important;
        background: #fff;
    }
    
    .card-header-custom {
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        border-radius: 16px 16px 0 0;
        /* Flexbox for Title + Close Button */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title-custom {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: var(--dark);
        font-size: 18px;
        margin: 0;
    }

    /* --- CLOSE BUTTON (Cross) --- */
    .btn-close-card {
        color: #94a3b8;
        font-size: 20px;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
    }
    .btn-close-card:hover {
        color: var(--danger);
        background-color: #fef2f2;
        transform: rotate(90deg);
    }

    /* --- FORM ELEMENTS --- */
    .form-group label {
        font-weight: 600;
        color: #475569;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 10px 15px;
        font-size: 14px;
        color: var(--dark);
        transition: all 0.2s;
        height: auto;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* --- FILE UPLOAD PREVIEW --- */
    .image-preview img {
        height: 70px;
        width: 70px;
        margin: 5px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* --- DYNAMIC ATTRIBUTE SECTION --- */
    .attribute-group {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        position: relative;
    }

    /* --- BUTTONS --- */
    .btn-submit-custom {
        background-color: var(--primary);
        color: #fff !important;
        border: none;
        border-radius: 8px;
        padding: 12px 30px;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        transition: all 0.2s;
    }
    .btn-submit-custom:hover {
        background-color: #4f46e5;
        transform: translateY(-2px);
    }

    .btn-add-attr {
        background-color: #10b981; /* Green */
        color: white !important;
        border-radius: 8px;
        font-weight: 500;
        border: none;
    }
    
    .btn-remove-attr {
        background-color: #fee2e2;
        color: var(--danger) !important;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-weight: 600;
        width: 100%;
    }
    .btn-remove-attr:hover {
        background-color: var(--danger);
        color: white !important;
    }

    /* --- RADIO BUTTONS --- */
    input[type="radio"] {
        accent-color: var(--primary);
        transform: scale(1.2);
        margin-right: 5px;
    }
    .radio-label {
        margin-right: 20px;
        font-weight: 500;
        color: var(--dark);
        cursor: pointer;
    }
</style>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3 style="font-family: 'Poppins', sans-serif; font-weight: 700; color: #1e293b;"><?= $page_lang->product; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-lg-12">
                    
                    <?php if (($this->session->flashdata('error')) || validation_errors()!=''){ ?>
                    <div class="alert alert-danger m-3 rounded">
                        <?= validation_errors();?>
                        <?= $this->session->flashdata('error') ?>
                    </div>
                    <?php } ?>

                    <?php if($this->session->flashdata('success')){ ?>
                    <div class="alert alert-success m-3 rounded">
                        <?= $this->session->flashdata('success') ?>
                    </div>
                    <?php } ?>

                    <?php echo form_open('/admin/product/save', array('id' => 'productForm', 'method' => 'POST', 'class' => 'card', 'enctype' => 'multipart/form-data')) ?>
                    
                    <div class="card-header-custom">
                        <h5 class="card-title-custom">Product Details</h5>
                        <a href="/<?= $TYPE; ?>/product" class="btn-close-card" title="Close">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label><?= $page_lang->name; ?> <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="form-group col-md-3">
                                <label><?= $page_lang->product_category; ?> <span class="text-danger">*</span></label>
                                <select name="product_category" id="product_category" class="select2 form-control" required data-parsley-errors-container="#product_category_error">
                                    <option value=""><?= $page_lang->select_product_category; ?></option>
                                    <?php
                                        if($product_category) {
                                            foreach($product_category as $key => $val) {
                                                echo '<option value="'.$key.'">'.$val.'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                                <div id="product_category_error"></div>
                            </div>

                            <div class="form-group col-md-3">
                                <label><?= $page_lang->food_type; ?> <span class="text-danger">*</span></label>
                                <select name="food_type" id="food_type" class="select2 form-control" required data-parsley-errors-container="#food_type_error">
                                    <option value=""><?= $page_lang->select_food_type; ?></option>
                                    <?php
                                        if($food_type) {
                                            foreach($food_type as $key => $val) {
                                                echo '<option value="'.$key.'">'.$val.'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                                <div id="food_type_error"></div>
                            </div>

                            <div class="form-group col-md-6">
                                <label><?= $page_lang->category; ?> <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="select2 form-control main_category_combo" required data-parsley-errors-container="#main_category_error">
                                    <option value=""><?= $page_lang->select_category; ?></option>
                                    <?php
                                        if($category) {
                                            foreach($category as $row_wise) {
                                                echo '<option value="'.$row_wise['id'].'">'.$row_wise['name'].'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                                <div id="main_category_error"></div>
                            </div>

                            <div class="form-group col-md-6">
                                <label><?= $page_lang->sub_category; ?></label>
                                <select name="sub_cat_id" id="sub_cat" class="select2 form-control sub_category_combo">
                                    <option><?= $page_lang->select_category; ?></option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label><?= $page_lang->short_description; ?></label>
                                <textarea name="short_description" id="short_description" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <label><?= $page_lang->sales_duration; ?></label>
                                <input type="text" name="sales_duration" id="sales_duration" class="form-control sales_duration">
                            </div>

                            <div class="form-group col-md-6">
                                <label><?= $page_lang->modifiers; ?></label>
                                <select name="modifiers[]" id="modifiers" class="form-control select2" multiple>
                                    <?php foreach($modifiers as $mod) { ?>
                                        <option value="<?= $mod->id ?>"><?= $mod->name ?></option>
                                    <?php } ?>
                                </select>
                                <div id="modifier_prices" class="mt-2"></div>
                            </div>

                            <div class="form-group col-md-6">
                                <label><?= $page_lang->extras; ?></label>
                                <select name="extras[]" id="extras" class="form-control select2" multiple>
                                    <?php foreach($extras as $ex) { ?>
                                        <option value="<?= $ex->id ?>"><?= $ex->name ?></option>
                                    <?php } ?>
                                </select>
                                <div id="extra_prices" class="mt-2"></div>
                            </div>

                            <div class="form-group col-md-12 mt-3 mb-4">
                                <label class="d-block mb-2"><?= $page_lang->product_type; ?></label>
                                <label class="radio-label">
                                    <input type="radio" name="product_type" value="simple" checked> <?= $page_lang->simple; ?>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="product_type" value="attribute"> <?= $page_lang->attribute; ?>
                                </label>
                            </div>

                            <div id="simple-fields" class="row w-100 ml-0">
                                <div class="form-group col-md-4">
                                    <label><?= $page_lang->regular_price; ?></label>
                                    <input type="text" name="regular_price" id="regular_price" class="form-control">
                                </div>

                                <div class="form-group col-md-4">
                                    <label><?= $page_lang->sale_price; ?></label>
                                    <input type="text" name="sales_price" id="sales_price" class="form-control">
                                </div>

                                <div class="form-group col-md-4">
                                    <label><?= $page_lang->upload_images; ?></label>
                                    <input type="file" name="image[]" class="form-control" id="simpleImages" multiple>
                                    <div id="simplePreview" class="image-preview mt-2 d-flex flex-wrap"></div>
                                </div>
                            </div>

                            <div id="attribute-section" style="display:none;" class="col-md-12">
                                <div id="attribute-wrapper">
                                    <div class="row attribute-group">
                                        <div class="form-group col-md-3">
                                            <label><?= $page_lang->attribute_name; ?></label>
                                            <input type="text" name="attribute_name[]" class="form-control">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label><?= $page_lang->regular_price; ?></label>
                                            <input type="text" name="attribute_regular_price[]" class="form-control">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label><?= $page_lang->sale_price; ?></label>
                                            <input type="text" name="attribute_sales_price[]" class="form-control">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label><?= $page_lang->upload_image; ?></label>
                                            <input type="file" name="attribute_image[0][]" class="form-control" multiple>
                                        </div>
                                        <div class="form-group col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-remove-attr remove-attribute">
                                                <i class="fa fa-trash"></i> <?= $page_lang->remove; ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-add-attr mt-3" id="add-attribute">
                                    <i class="fa fa-plus"></i> <?= $page_lang->add_attribute; ?>
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer text-right bg-white border-top p-4">
                        <button type="submit" class="btn btn-submit-custom"><?= $page_lang->submit; ?></button>
                    </div>

                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pass PHP data to JS safely
var rawData = '<?= addslashes(json_encode($prodData)) ?>';
</script>
<script src="/assets/admin/js/product.js"></script>