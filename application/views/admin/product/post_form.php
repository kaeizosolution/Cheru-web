<?php
$controller = $this->router->fetch_class();
$model = $this->router->fetch_method();
$page_lang = get_page_language_data('admin_page_lang');
?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?= $page_lang->product; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <form method="POST" class="addpost" id="postForm" action='<?php echo "/$TYPE/product/$action/$product_id"; ?>' enctype="multipart/form-data" autocomplete='off' onsubmit="return validatePrices()">
    <div class="container-fluid row">
        <div class="col-sm-9">
            <div class="row">
                <div class="col-sm-12 card">
                    <?php if (($this->session->flashdata('error')) || validation_errors()!='') { ?>
                    <div class="alert alert-danger">
                        <?= validation_errors();?>
                        <?= $this->session->flashdata('error')?>
                    </div>
                    <?php } ?>
                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('success')?>
                    </div>
                    <?php } ?>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-12">
                                <?php if(isset($post_title)) { ?><span>Permalink: <a target="_blank" href="<?php echo site_url('/product/detail/').$post_slug; ?>"><?php echo isset($post_slug) ? $post_slug : '' ?></a></span><?php } ?>
                            </div>
                            <div class="col-sm-12 col-md-12 m-t-20">
                                <div class="card">
                                    <ul class="nav nav-tabs profile-tab" role="tablist">
                                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab" style="font-size: 14px;">  English </a> </li>
                                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab" style="font-size: 14px;"> Spanish </a> </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="home" role="tabpanel">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label class="form-label">Title</label>
                                                    <input type="text" name="post_title"  class="form-control"  value="<?= isset($post_title) ? $post_title : '' ?>" placeholder="Enter Title" onkeypress="return check(event)" required>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label class="form-label">Body</label>
                                                    <textarea name="post_content" rows="5" class="form-control" placeholder="Write Something..." ><?= isset($post_content) ? $post_content : '' ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="profile" role="tabpanel">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label class="form-label">Título</label>
                                                    <input type="text" name="post_title_es"  class="form-control"  value="<?= isset($post_title_es) ? $post_title_es : '' ?>" placeholder="Ingrese el título" onkeypress="return check(event)">
                                                </div>
                                                <div class="form-group mb-0 m-t-10">
                                                    <label class="form-label">Cuerpo</label>
                                                    <textarea name="post_content_es" row="5" class="form-control" placeholder="Escribe algo..." ><?= isset($post_content_es) ? $post_content_es : '' ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6 m-t-20">
                                <label for="post_status"><?= $page_lang->categories; ?></label>
                                <?php 
                                if(isset($categories)){ ?>
                                    <select name="category[]" class="select2 form-control category_combo" multiple="1" required="required" data-parsley-errors-container="#category_error">
                                        <option></option>
                                        <?php $this->Category_prod_model->categoryTree(0,'',$category_ids); ?>
                                    </select>
                                <?php } ?>
                                    <div id="category_error"></div>
                            </div>
                            <?php   ?>
                            <div class="form-group col-md-6 m-t-20" style="display:none">
                                <label for="post_status"><?= $page_lang->tag; ?></label>
                                <?php                                                    
                                    if(isset($tag_ids)){
                                    echo form_dropdown('tag[]',$tags,$tag_ids,array('class' => 'select2-tags form-control','multiple' => true));
                                    }else{
                                    echo form_dropdown('tag[]',$tags,null,array('class' => 'select2-tags form-control','multiple' => true));
                                    }
                                    
                                    ?>
                            </div>
                            <div class="form-group col-md-6 m-t-20">
                                <label for="post_status"><?= $page_lang->brand; ?></label>
                                <select id="brand_id" name="brand_id" class="form-control brand_combo" data-parsley-errors-container="#brand_id_error" required>
                                    <option></option>
                                    <?php
                                        foreach($brand_obj as $row){
                                            $brand_seleted = (isset($brand_id) && $brand_id == $row->brand_id) ? 'Selected' : '';
                                            echo "<option value=\"$row->brand_id\" $brand_seleted>$row->name</option>";
                                        }
                                        ?>
                                </select>
                                <div id="brand_id_error"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="post_status"><?= $page_lang->supplier; ?></label>
                                <select id="supplier_id" name="supplier_id" class="form-control supplier_combo" data-parsley-errors-container="#supplier_id_error" required>
                                    <option></option>
                                    <?php
                                        foreach($supplier_obj as $row){
                                            $supplier_seleted = (isset($supplier_id) && $supplier_id == $row->supplier_id) ? 'Selected' : '';
                                            echo "<option value=\"$row->supplier_id\" $supplier_seleted>$row->cname</option>";
                                        }
                                        ?>
                                </select>
                                <div id="supplier_id_error"></div>
                            </div>
                            <?php  ?>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label><?= $page_lang->choose_files; ?></label>
                                    <input type="file" class="form-control" name="upload_Files[]" multiple data-parsley-filemaxsize="1000" data-parsley-fileextension="jpg|png|jpeg|webp" parsley-trigger="change" data-parsley-errors-container="#upload_files_error"/>	
                                </div>
                                <div id="upload_files_error"></div>
                            </div>
                            <div class="col-sm-12">&nbsp;</div>
                            <?php //echo "<pre>"; print_r($gallery); echo "</pre>"; ?>
                            <div class="col-sm-12 col-md-12">
                                <div class="gallery">
                                    <ul>
                                        <?php if(isset($gallery) && !empty($gallery)): foreach($gallery as $key=>$file):
                                            $file_name = isset($file->file_name) ? $file->file_name : '';
                                            $imgid = isset($file->id) ? $file->id : '';
                                            ?>
                                        <li>
                                            <button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" style=" background:none; color:red;" type="button" data-id="<?php echo $imgid; ?>"><i class="fa fa-trash"></i></button>	
                                            <img width="150" height="150" src="<?php echo base_url('assets/uploads/files/'); echo $file_name; ?>" alt="" >
                                        </li>
                                        <?php endforeach; else: ?>
                                        <!--<p>No File uploaded.....</p>-->
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12">&nbsp;</div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <br >
                                    <label><?= $page_lang->status; ?></label>									
                                    <select class="form-control status_combo" name="enabled" data-parsley-errors-container="#enabled_error" required="required">
                                        <option value=""><?= $page_lang->select; ?></option>
                                        <?php if($product_id) { ?>
                                        <option value="1" <?= isset($enabled) && $enabled == 1 ? 'selected' : ''?>><?= $page_lang->active; ?></option>
                                        <?php }else { ?>
                                        <option value="1" selected><?= $page_lang->active; ?></option>
                                        <?php } ?>
                                        <option value="0" <?= isset($enabled) && $enabled == 0 ? 'selected' : ''?>><?= $page_lang->inactive; ?></option>
                                    </select>
                                    <div id="enabled_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <br >
                                    <label><?= $page_lang->featured; ?></label>									
                                    <select class="form-control featured_combo" name="featured_product" data-parsley-errors-container="#featured_product_error" required="required">
                                        <option value=""> <?= $page_lang->featured_product; ?></option>
                                        <option value="1" <?= isset($featured_product) && $featured_product == 1 ? 'selected' : '' ?>><?= $page_lang->yes; ?></option>
                                        <?php if($product_id) { ?>
                                        <option value="0" <?= isset($featured_product) && $featured_product == 0 ? 'selected' : ''?>><?= $page_lang->no; ?></option>
                                        <?php }else { ?>
                                        <option value="0" selected><?= $page_lang->no; ?></option>
                                        <?php } ?>
                                    </select>
                                    <div id="featured_product_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 p-0">
                    <div class="card add-product">
                        <div class="card-header">
                            <h5 class="m-b-0">
                                <span>
                                    <?= $page_lang->product_data; ?>
                                    <span>
                                        -
                                        <label for="product-type">
                                            <select id="type" name="type" class="form-control" <?= isset($product_id) ? 'disabled' : '' ?>>
                                                <option value="simple" <?= (isset($type) && $type == 'simple') ? 'selected' : '' ?>> <?= $page_lang->simple; ?></option>
                                                <option value="variable" <?= (isset($type) && $type == 'variable') ? 'selected' : '' ?>> <?= $page_lang->variable; ?></option>
                                            </select>
                                            <?php if(isset($product_id)){ ?>
                                                <input type="hidden" name="type" value="<?= $type ?>">
                                            <?php } ?> 
                                        </label>
                                    </span>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-menu">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true"><i class="fa fa-wrench" aria-hidden="true"></i> <span><?= $page_lang->general; ?></span></a>
                                    <a class="nav-link" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false"><i class="fa fa-truck" aria-hidden="true"></i> <span><?= $page_lang->shipping; ?></span></a>
                                    <!--<a class="nav-link" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i class="fa fa-link" aria-hidden="true"></i> <span>Linked Products</span></a>-->
                                    <a class="nav-link" id="v-pills-attributes-tab" data-toggle="pill" href="#v-pills-attributes" role="tab" aria-controls="v-pills-attributes" aria-selected="false"><i class="fa fa-clipboard" aria-hidden="true"></i> <span><?= $page_lang->attributes; ?></span></a>
                                    <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fa fa-file-text" aria-hidden="true"></i> <span><?= $page_lang->price_range; ?></span></a>
                                    <a class="nav-link" id="v-pills-simple-tab" data-toggle="pill" href="#v-pills-simple" role="tab" aria-controls="v-pills-simple" aria-selected="false" <?= (isset($type) && $type == 'simple') ? '' : 'style="display:none;"' ?>><i class="fa fa-cog" aria-hidden="true"></i> <span><?= $page_lang->simple; ?></span></a>
                                    <a class="nav-link" id="v-pills-advanced-tab" data-toggle="pill" href="#v-pills-advanced" role="tab" aria-controls="v-pills-advanced" aria-selected="false" <?= (isset($type) && $type == 'variable') ? '' : 'style="display:none;"' ?>><i class="fa fa-cog" aria-hidden="true"></i> <span><?= $page_lang->variant; ?></span></a>
                                    <!--<a class="nav-link" id="v-pills-get-tab" data-toggle="pill" href="#v-pills-get" role="tab" aria-controls="v-pills-get" aria-selected="false"><i class="fa fa-plug" aria-hidden="true"></i> <span>Get more options</span></a>-->
                                </div>
                            </div>
                            <div class="form-detail">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                        <div class="form-group row">
                                            <label for="SKU" class="col-sm-3 col-form-label"><?= $page_lang->sku; ?></label>
                                            <div class="col-sm-6">
                                                <input type="text" name="sku" class="form-control"  value="<?= isset($sku) ? $sku : '' ?>" placeholder="Enter sku" onkeypress="return check(event)" required>
                                            </div>
                                            <div class="col-sm-3">
                                                <i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and services that can be purchased."></i>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-3 col-form-label"><?= $page_lang->tax; ?></label>
                                            <div class="col-sm-6">
                                                <select id="tax_id" name="tax_id" class="form-control tax_combo">
                                                <?php 
                                                    foreach($tax_obj as $row){
                                                        $tax_seleted = (isset($tax_id) && $tax_id == $row->tax_id) ? 'Selected' : '';
                                                        echo "<option value=\"$row->tax_id\" $tax_seleted>$row->name</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-3">
                                                <i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Define whether or not the entire product is taxable, or just the cost of shipping it."></i>
                                            </div>
                                        </div>
                                        <hr>
                                        <?php if( isset($sale_price_dates_from) && !empty($sale_price_dates_from)){	
                                            $st = "display:block";
                                            }else{
                                            $st = "display:block";
                                            } ?>
                                        <div class="timef" style="<?php echo $st; ?>">
                                            <div class="form-group row">
                                                <label for="Sale price" class="col-sm-3 col-form-label"><?= $page_lang->sale_price_dates; ?></label> 
                                                <div class="col-sm-6">
                                                    <input type="text" name="sale_price_dates_from" value="<?= isset($sale_price_dates_from) ? $sale_price_dates_from : '' ?>"  class="form-control fancy_date" id="sale_price_dates_from" placeholder="From… YYYY-MM-DD" readonly data-parsley-myvalidator="" data-parsley-error-message="Start Date can't be greater than End Date">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="Sale price" class="col-sm-3 col-form-label"></label>
                                                <div class="col-sm-6">
                                                    <input type="text" name="sale_price_dates_to" value="<?= isset($sale_price_dates_to) ? $sale_price_dates_to : '' ?>"  class="form-control fancy_date" id="sale_price_dates_to" placeholder="To…  YYYY-MM-DD" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <h5><?= $page_lang->multi_currency; ?></h5>
                                        <p><?= $page_lang->multi_currency_dic; ?></p>
                                    </div>
                                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                        <div class="form-group">
                                            <label for="Range" class="col-form-label"><?= $page_lang->range; ?><i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Qry (Ex:50-499) Price: (Ex:$120.50)"></i></label>
                                            <?php $quantity_range = isset($quantity_range) ? $quantity_range : '';
                                                $qrange = unserialize($quantity_range);
                                                $pricerange = isset($price_range) ? $price_range : '';
                                                $price_range = unserialize($pricerange); ?>	
                                            <div id="dynamic_field">
                                                <?php if($quantity_range){
                                                    for($i=0;$i<sizeof($qrange);$i++){ ?>
                                                <div class="row" id="row<?php echo $i ?>">
                                                    <div class="col-sm-4 col-md-4">
                                                        <div class="form-group">
                                                            <input type="text" name="quantity_range[]" value="<?php echo $qrange[$i]; ?>" class="form-control numeric_range" id="quantity_range" placeholder="Enter Quantity" autocomplete="off" required>      
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 col-md-4">
                                                        <div class="form-group">
                                                            <input type="text" name="price_range[]" value="<?php echo $price_range[$i]; ?>" class="form-control unsigned_float" id="price_range" placeholder="Enter price" autocomplete="off" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 col-md-4"><button type="button" name="remove" id="<?php echo $i; ?>" class="btn btn-danger btn_remove">X</button></div>
                                                </div>
                                                <?php }}else{ ?>								
                                                <!--<div class="row">
                                                    <div class="col-sm-4 col-md-4">
                                                        <div class="form-group">
                                                            <input type="text" name="quantity_range[]" value="" class="form-control numeric_range" id="quantity_range" placeholder="Enter Quantity" autocomplete="off" required>      
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 col-md-4">
                                                        <div class="form-group">
                                                            <input type="text" name="price_range[]" value="" class="form-control unsigned_float" id="price_range" placeholder="Enter price" autocomplete="off" required>
                                                        </div>
                                                    </div>
                                                </div>-->
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-3"></div>
                                            <div class="col-sm-6">
                                                <button type="button" name="add" id="add" class="btn btn-success"><?= $page_lang->add_more; ?></button>
                                            </div>
                                        </div>
                                        <script type="text/javascript">
                                            $(document).ready(function(){      
                                              var i=1;  
                                              $('#add').click(function(){  
                                            	   i++;             
                                            	   $('#dynamic_field').append('<div class="row" id="row'+i+'"> <div class="col-sm-4 col-md-4"><div class="form-group"><input type="text" class="form-control numeric_range"  placeholder="Enter quantity " name="quantity_range[]" autocomplete="off" required></div></div> <div class="col-sm-4 col-md-4"><div class="form-group"><input type="text" class="form-control unsigned_float" id="price_range" placeholder="Enter Price" name="price_range[]" autocomplete="off" required></div></div> <div class="col-sm-2 col-md-2"><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></div></div></div>');
                                             });
                                             
                                             $(document).on('click', '.btn_remove', function(){  
                                                var button_id = $(this).attr("id"); 
                                            	var res = confirm('Are You Sure You Want To Delete This..?');
                                            	if(res==true){
                                            	    $('#row'+button_id+'').remove();  
                                            	    $('#'+button_id+'').remove();  
                                            	}
                                             });  
                                            });  
                                        </script>
                                        <hr>
                                    </div>
                                    <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-3 col-form-label"><?= $page_lang->weight_kg; ?></label>
                                            <div class="col-sm-6">
                                                <input type="text" name="weight" value="<?= isset($weight) ? $weight : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_weight" placeholder="Weight">
                                            </div>
                                            <div class="col-sm-3">
                                                <i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Weight in decimal form"></i>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-3 col-form-label"><?= $page_lang->dimensions_cm; ?></label>
                                            <div class="col-sm-6 row pr-0">
                                                <div class="col-sm-4 pr-0">
                                                    <input type="text" name="length" value="<?= isset($length) ? $length : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_length" placeholder="Length">
                                                </div>
                                                <div class="col-sm-4 pr-0">
                                                    <input type="text" name="width" value="<?= isset($width) ? $width : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_width" placeholder="Width">
                                                </div>
                                                <div class="col-sm-4 pr-0">
                                                    <input type="text" name="height" value="<?= isset($height) ? $height : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_height" placeholder="Height">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <i class="fa fa-question-circle ttc pl-4" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="LxWxH in decimal form"></i>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group row mt-3">
                                            <label for="inputEmail3" class="col-sm-3 col-form-label"><?= $page_lang->shipping; ?></label>
                                            <div class="col-sm-6">
                                                <select id="shipping_id" name="shipping_id" class="form-control shipping_combo" style="width:247.5px">
                                                <?php
                                                    foreach($shipping_obj as $row){
                                                        $shipping_seleted = (isset($shipping_id) && $shipping_id == $row->shipping_id) ? 'Selected' : '';
                                                        echo "<option value=\"$row->shipping_id\" $shipping_seleted>$row->name</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-3">
                                                <i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Shipping classes are used by certain shipping methoods to group similar products."></i>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">...</div>
                                    <div class="tab-pane fade" id="v-pills-attributes" role="tabpanel" aria-labelledby="v-pills-attributes-tab">
                                        <div class="form-group row mt-3">
                                            <div class="col-sm-6">
                                                <select id="attr_class" name="attr_class" style="width:250px;" class="form-control attribute_combo">
                                                    <option></option>
                                                    <?php if($all_attribute_array) { foreach($all_attribute_array as $attribute_id=>$attribute_name){ ?>
                                                    <option value="<?= $attribute_id; ?>"><?= $attribute_name; ?></option>
                                                    <?php } } ?>    
                                                </select>
                                            </div>
                                            <div class="col-sm-2"><a href="javascript:void(0);"class="btn btn-primary" id="attribute_id"><?= $page_lang->add; ?></a></div>
                                        </div>
                                        <hr>
                                        <?php
                                            if($selected_attribute_item){
                                            foreach($selected_attribute_item as $att_id => $att_item_array){ 
                                            ?>	
                                        <div class="form-group row" id="attr-<?= $att_id; ?>" >
                                            <label for="Attribute-Colour" class="col-sm-2 col-form-label"><?= $all_attribute_array[$att_id] ?></label>
                                            <div class="col-sm-8">
                                                <select id="attribute_item_<?php echo $att_id; ?>" name="attribute_item[]" class="form-control attribute_item" multiple>
                                                    <?php foreach($att_item_array as $val2){?>
                                                    <option value="<?= $val2->attribute_item_id; ?>" selected><?= $val2->name; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-2 col-md-2"><button type="button" name="remove" id="<?php echo $att_id; ?>" class="btn btn-danger attr_remove">X</button></div>
                                        </div>
                                        <?php }}?>
                                        <div id="attributes_divs">&nbsp;</div>
                                    </div>
                                    <div class="tab-pane fade container-fluid" id="v-pills-simple" role="tabpanel" aria-labelledby="v-pills-simple-tab">
                                        <div class="form-group row m-t-20 ">
                                            <label for="Stock quantity" class="col-sm-3 col-form-label"><?= $page_lang->stock; ?></label>
                                            <div class="col-sm-6">
                                                <input type="text" name="stock" class="form-control numeric" id="stock" value="<?= isset($stock) ? $stock : '' ?>" placeholder="Stock Qty" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="Regular price" class="col-sm-3 col-form-label"><?= $page_lang->regular_price; ?></label>
                                            <div class="col-sm-6">
                                                <input type="text" name="regular_price" class="form-control no_validate" id="regular_price" value="<?= isset($regular_price) ? $regular_price : '' ?>" placeholder="Regular price" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="Sale price" class="col-sm-3 col-form-label"><?= $page_lang->sale_price; ?></label>
                                            <div class="col-sm-6">
                                                <input type="text" name="sale_price" class="form-control no_validate" id="sale_price" value="<?= isset($sale_price) ? $sale_price : '' ?>" placeholder="Sale price">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade container-fluid" id="v-pills-advanced" role="tabpanel" aria-labelledby="v-pills-advanced-tab">
                                        <div class="row">
                                            <?php
                                            if($selected_attribute_item){
                                            foreach($selected_attribute_item as $att_id => $att_item_array){
                                            ?>
                                            <div class="col-sm-3">
                                                <label><?= $all_attribute_array[$att_id] ?>:</label>
                                                <select id="variations_class" data-id="<?= $att_id ?>" name="variations_class[]" class="form-control jp variation_combo" >
                                                    <option></option>
                                                    <?php if($att_item_array) { foreach($att_item_array as $key=>$val){ ?>
                                                    <option value="<?= $val->attribute_item_id; ?>"><?= $val->name; ?></option>
                                                    <?php } } ?>    
                                                </select>
                                            </div>
                                            <?php } ?>
                                            <div class="col-sm-2"><label>&nbsp;</label><a href="javascript:void(0);"class="btn btn-primary" id="variations_id"><?= $page_lang->add; ?></a></div>
                                            <?php } ?>
                                        </div>
                                        <p style="color:#bb77ae;"><?= $page_lang->variant_disc; ?></p>
                                        <?php //echo "<pre>"; print_r($all_vari_term); echo "</pre>";die;  ?>
                                        <div id="variations_data">
                                            <?php if(isset($selected_variation_obj)) : foreach($variation_obj_keys as $k=>$v){
					 
                                               	//echo "<pre>"; print_r($v); echo "</pre>";die;
												//echo "dheeruu=== $v->variation_id\n";
												$all_values = $v;
												$v = $v[0];
                                                $variation_id = isset($v->variation_id) ? $v->variation_id:'';
                                                $attribute_item_id = isset($v->attribute_item_id) ? $v->attribute_item_id:'';
                                                $_sale_price = isset($v->_sale_price) ? $v->_sale_price:'';
                                                $_regular_price = isset($v->_regular_price) ? $v->_regular_price:'';
                                                $_stock = isset($v->_stock) ? $v->_stock:'';
                                                $_thumbnail = isset($v->_thumbnail_id) ? $v->_thumbnail_id:'';

                                                $item_name = '';
                                                if($attribute_item_id){
                                                    $item_name_array = array();
                                                    $attribute_item_array = explode(',',$attribute_item_id);
                                                    foreach($attribute_item_array as $ata){
                                                        if(isset($all_attribute_item_array[$ata])){
                                                            $item_name_array[] = $all_attribute_item_array[$ata];
                                                        }
                                                    }
                                                    $item_name = implode('#',$item_name_array);
                                                }
                                                ?>
                                            <div class="border p-2 sadow m-t-10" style="background: #fbfbfb;" id="variation-<?php echo $variation_id; ?>">
                                                <div class="form-group row">
                                                    <label for="variations-Black" class="col-sm-3 col-form-label"><?php echo '#'.$item_name ?></label>
                                                    <div class="col-sm-7 col-md-7"><?= $page_lang->upoad_image; ?><input type="hidden" name="attr_itemid[]" class="attr_itemid" value="<?= $attribute_item_id ?>">
													<?php 
													$html_img = '';
													$total_imgs = count($all_values); 
													$img_cnt=0; $remove_imgs='';
													foreach($all_values as $inner_row){
														$img_cnt++;
														if($total_imgs > 1 && $img_cnt > 1)
														{
															$variation_id = $inner_row->variation_id;
															$data_variationid = 'variation-'.$variation_id;							
															$remove_imgs = '<div class=""><button type="button" name="variation-remove" data-variationid="$data_variationid" data-type="site" class="btn btn-danger btn_remove_variation p-0" style="padding:0px 7px !important;">X</button></div>';
														}
														
														$thumbnail_id = isset($inner_row->_thumbnail_id) ? $inner_row->_thumbnail_id :'';	
														$all_attr = $inner_row->all_attr;
														$thumbnail_name = "_thumbnail_id_".$all_attr."[]";
 
                                                        $html_img .=<<<HTML
														<input type="file" name="$thumbnail_name" id="_thumbnail_id_$k" class="form-control" value="" ($thumbnail_id) ? '' : 'required' data-parsley-filemaxsize="1000" data-parsley-fileextension="jpg|png|jpeg|webp" parsley-trigger="change" data-parsley-errors-container="#variation_file_error_$attribute_item_id">
                                                        <input type="hidden" name="variations_image[]" value="$thumbnail_id">
                                                        <img src="/assets/uploads/$thumbnail_id" width="100" height="100">$remove_imgs
HTML;
													} echo $html_img; ?>
                                                    </div>
                                                    <div id="variation_file_error_<?=$attribute_item_id?>"></div>

                                                    <div class="col-sm-2 col-md-2"><button type="button" name="variation-remove" data-id="<?php echo $attribute_item_id; ?>" data-type="db" data-variationid="variation-<?php echo $variation_id; ?>" class="btn btn-danger btn_remove_variation p-0"  style="padding:0px 7px !important;">X</button></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-6 col-md-6"><label for="regular_price" class="col-sm-12 col-form-label"><?= $page_lang->regular_price; ?></label>
                                                        <input type="text" name="_regular_price[]" id="_regular_price_<?php echo $k; ?>" class="form-control unsigned_float" value="<?php echo $_regular_price; ?>" required>
                                                    </div>
                                                    <div class="col-sm-6 col-md-6"><label for="sale_price" class="col-sm-12 col-form-label"><?= $page_lang->sale_price; ?></label>
                                                        <input type="text" name="_sale_price[]" id="_sale_price_<?php echo $k; ?>" class="form-control unsigned_float" value="<?php echo $_sale_price; ?>" placeholder="">
                                                    </div>
                                                    <div class="col-sm-6 col-md-6"><label for="stock" class="col-sm-12 col-form-label"><?= $page_lang->stock; ?> </label>
                                                        <input type="text" name="_stock[]" id="_stock_<?php echo $k; ?>" class="form-control numeric" style="" value="<?php echo $_stock; ?>" placeholder="" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } endif; ?>
                                            <div id="variations_divs">&nbsp;</div>
                                        </div>
                                        <div class="tab-pane fade" id="v-pills-get" role="tabpanel" aria-labelledby="v-pills-get-tab"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3" style="position:relative;">
            <div style="position:sticky;top:100px;">
                <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                <button type="button" class="btn btn-light btn_back mb-2 w-100"> <?= $page_lang->back; ?></button>
                <button type="submit" class="btn btn-primary  w-100"><?= $page_lang->submit; ?></button>
            </div>
        </div>
    </div>
</div>
<?php echo form_close() ?> 	
<style>
    #formdiv {  text-align: center;}
    #file{  color: green;  padding: 5px;  border: 1px dashed #123456;  background-color: #f9ffe5;}
    #img{  width: 17px;  border: none;  height: 17px;  margin-left: -20px;  margin-bottom: 191px;}
    .upload{ width: 100%;  height: 30px;}
    .abcd{  text-align: center;  position: relative;}
    .abcd img{  height: 200px;  width: 200px;  padding: 5px;  border: 1px solid rgb(232, 222, 189);}
    .delete{  color: red;  font-weight: bold;  position: absolute;  top: 0;  cursor: pointer }
    .diss{ display:block !important;}
</style>
<script>
    /*
    $('.fancy_date').daterangepicker({
        autoUpdateInput: false,
        minDate: moment().format('DD-MM-YYYY'),
        startDate: nearestMinutes(0),
        timePickerIncrement: 15,
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        timePicker24Hour: true,
        drops: 'down',
        locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD H:mm' }
    });
    
    $('.fancy_date').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD H:mm'));
        $(this).parsley().reset();
    });
    
    $('.fancy_date').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $(this).parsley().validate()
    });
    
    */
    
    $(document).ready(function() {
    //    $('select').select2({allowClear: true, placeholder: "Attributes"});
        $('.category_combo').select2({allowClear: true, placeholder: "Category"});
        $('.brand_combo').select2({allowClear: true, placeholder: "Brand"});
        $('.status_combo').select2({allowClear: true, placeholder: "Status"});
        $('.featured_combo').select2({allowClear: true, placeholder: "Featured Product"});
        $('.supplier_combo').select2({allowClear: true, placeholder: "Supplier"});
        $('.tax_combo').select2({allowClear: true, placeholder: "Tax"});
        $('.shipping_combo').select2({allowClear: true, placeholder: "Shipping"});
        $('.attribute_combo').select2({allowClear: true, placeholder: "Attributes"});
        $('.variation_combo').select2({allowClear: true, placeholder: "Variations"});
        $('.attribute_item').select2({allowClear: true, placeholder: "Attribute Item"});
    });
    
    function nearestMinutes(mnt){
        time = moment();
        round_interval = 15;
        intervals = Math.ceil(time.minutes() / round_interval);
        minutes = intervals * round_interval;
        time.minutes(minutes);
        return time.add(mnt,'minutes').format('YYYY-MM-DD H:mm');
    }
    
    
    function validatePrices()
    {
        var range_length = $(".numeric_range").length;
        var count = 0;
        var error = 0;
        var next_val = 0;
        $(".numeric_range").each(function() {
            count++;
            var range_val = $(this).val();
            if(count < range_length){
                var regex = /^\d+-\d+$/gm;
                if(regex.test(range_val)){
                    var val_array = range_val.split('-');
                    var first_val = val_array[0];
                    var second_val = val_array[1];
                    //console.log('first_val: ' + first_val + ' second_val: ' + second_val);
                    if(parseInt(second_val) <= parseInt(first_val)){
                        error = 1;
                        $(this).addClass("parsley-err");
                    }

                    if(count == 1 && first_val == 1){
                        error = 1;
                        $(this).addClass("parsley-err");
                    }

                    if(count > 1 && next_val != first_val){
                        error = 1;
                        $(this).addClass("parsley-err");
                    }
                    next_val = parseInt(val_array[1])+1;

                    if(parseInt(val_array[0]) > parseInt(val_array[1])){
                        //error = 1;
                        //$(this).addClass("parsley-err");
                    }
                }else{
                    error = 1;
                    $(this).addClass("parsley-err");
                }
            }else{
                if(count > 1){
                    second_val--;
                }
                var regex = /^\d+>$/gm;
                if(regex.test(range_val)){
                    var val_array = range_val.split('>');
                    var first_val = val_array[0];
                    if(parseInt(next_val) > 1){
                        next_val = parseInt(next_val)-1;
                    }
                    //console.log('first_val:: ' + first_val + ' next_val: ' + next_val);
                    if(parseInt(next_val) && parseInt(first_val) != parseInt(next_val)){
                        error = 1;
                        $(this).addClass("parsley-err");
                    }else{
                        $(this).removeClass("parsley-err");
                    }
                }else{
                    error = 1;
                    $(this).addClass("parsley-err");
                }
            }
            if(error == 0){
                $(this).removeClass("parsley-err");
            }
        });
        if(error == 1){
            alert('Please check the Price Range');
            return false;
        }

        <?php if($model == 'update'){ ?>
        if($('#type').val() ==  'variable'){
            var rp = 0;
            $("input[name='_regular_price[]']").each(function() {
                if(this.value){
                    rp = 1;
                }
            });
            if(rp == 0){
                alert('Please Add at least one Variation');
                return false;
            }

            $(".attr_itemid").each(function() {
                var item_val = $(this).val();
                var item_array = item_val.split(',');
                //console.log(item_array);
            
                var attribute_item_array = [];
                $(".attribute_item").each(function() {
                    var tmp_val = $(this).val();
                    for(var i in tmp_val){
                        attribute_item_array.push(tmp_val[i]);
                    }
                });
            
                var err = 0;
                for(var j in item_array){
                    var a = attribute_item_array.includes(item_array[j]);
                    if(!a){
                        err = 1;
                        break;
                    }
                }
                if(err){
                    alert('Cant delete the attribute it is use in variation');
                    return false;
                }
                //console.log(attribute_item_array);
            });
        }
        <?php } ?>
    }
    
    function validateNumber(event) {
        var key = window.event ? event.keyCode : event.which;
        if (event.keyCode === 8 || event.keyCode === 46) {
            return true;
        } else if ( key < 48 || key > 57 ) {
            return false;
        } else {
            return true;
        }
    };
    
    
    $(document).ready(function(){
        $('[id^=stock]').keypress(validateNumber);
    });
    
    
    $('#add_more').click(function() {
          "use strict";
          $(this).before($("<div/>", {
            id: 'filediv'
          }).fadeIn('slow').append(
            $("<input/>", {
              name: 'file[]',
              type: 'file',
              id: 'file',
              multiple: 'multiple',
              accept: 'image/*'
            })
          ));
        });
    
        $('#upload').click(function(e) {
          "use strict";
          e.preventDefault();
    
          if (window.filesToUpload.length === 0 || typeof window.filesToUpload === "undefined") {
            alert("No files are selected.");
            return false;
          }
            
        });
    
        function deletePreview(ele, i) {
          "use strict";
          try {
            $(ele).parent().remove();
            window.filesToUpload.splice(i, 1);
          } catch (e) {
            console.log(e.message);
          }
        }
    
        $("#file").on('change', function() {
          "use strict";
    
    
          window.filesToUpload = [];
    
          if (this.files.length >= 1) {
            $("[id^=previewImg]").remove();
            $.each(this.files, function(i, img) {
              var reader = new FileReader(),
                newElement = $("<div id='previewImg" + i + "' class='abcd'><img /></div>"),
                deleteBtn = $("<span class='delete' onClick='deletePreview(this, " + i + ")'>delete</span>").prependTo(newElement),
                preview = newElement.find("img");
    
              reader.onloadend = function() {
                preview.attr("src", reader.result);
                preview.attr("alt", img.name);
              };
    
              try {
                window.filesToUpload.push(document.getElementById("file").files[i]);
              } catch (e) {
                console.log(e.message);
              }
    
              if (img) {
                reader.readAsDataURL(img);
              } else {
                preview.src = "";
              }
    
              newElement.appendTo("#filediv");
            });
          }
        });
    
    
    
</script>
<script>
    $(document).on('click','.btn-delete', function(e){
    	//alert('dd');
        var obj = $(this);
        var id=$(this).attr('data-id');  
        var postData = {};
    	var csrf_name = "<?= $csrf->name; ?>";
    	var csrf_value = "<?= $csrf->hash; ?>";
    	postData[csrf_name] =  csrf_value;
        postData.id = id;
        postData.product_id = <?= isset($product_id) ? $product_id : 0 ?>;
        $.ajax({
                url         :   '<?php echo base_url();?>admin/product/delimg',
                type        :   'post',
    		    enctype	    :   'multipart/form-data',
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if(data.success)
                    {
                        obj.closest('li').remove();
    			    } 
    			}
    	});
    } );
    	
    //variation delete query
    $(document).on('click','.btn_remove_variation', function(e){
    	var uid = $(this).attr("data-id");
        var product_id = <?= isset($product_id) ? $product_id : 0 ?>;
    	var type = $(this).attr("data-type");	
    	var variation_id = $(this).attr("data-variationid");	
    	//alert(uid);
        if(type == 'db'){	
    	    delete_variation_post(variation_id,product_id);
        }else{
            $('#'+ variation_id).remove();
        }
    } );
    
    function delete_variation_post(variation_id,product_id)
    {
        var variation_id_arr = variation_id.split('variation-');
        var postData = {};
    	var csrf_name = "<?= $csrf->name; ?>";
    	var csrf_value = "<?= $csrf->hash; ?>";
    	postData[csrf_name] =  csrf_value;
        postData.variation_id = variation_id_arr[1];
    	postData.product_id = product_id;
    	
        $.ajax({
                url         :   '<?php echo base_url();?>admin/product/delete_variation',
                type        :   'post',
    		    enctype	    :   'multipart/form-data',
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if(data.success)
                    {
                        $('#'+ variation_id).remove();
    			    } 
    			}
    	});
    }
    	
</script>
<script>
    $(document).on('click','#attribute_id', function(e){
    	var obj = $('#attr_class');
    	var attr = $('#attr_class').val();
        if(attr){
    	    attr_post(obj.val());
    	    e.stopPropagation();
        }else{
            swal({title: "Status!", text: 'Please select attribute', timer: 2000, buttons: false});
        }
    } );
    
    var attr_name = '';
    var attr_key = '';
    
    function attr_post(id)
    {
    	var postData = {};
    	var csrf_name = "<?= $csrf->name; ?>";
    	var csrf_value = "<?= $csrf->hash; ?>";
    	postData[csrf_name] =  csrf_value;
    	postData.id = id;
    
    
    	$.ajax({
    			url         :   '<?php echo base_url();?>admin/Product/get_attr',
    			type        :   'post',
    			//		    enctype	    :   'multipart/form-data',
    			dataType    :   "json",  
    			data        :   postData,
    			success     :   function(response){
    			response = eval(response);
    			if(response.status == 'success')
    			{
    				var data = response.data;
    				//alert(JSON.stringify(data));
    				var optionHtml = '';
    				
    				$.each( data, function( key, value ) {
    					optionHtml += '<option value='+key+'>'+value+'</option>';
    				});
    				//var attr_name = $("#attr_class option:selected").text();
    				//var attr_key = $("#attr_class option:selected").val();
    				var append_html =   '<div class="form-group row"><label for="Attribute-'+attr_name+'" class="col-sm-2 col-form-label">'+attr_name+'</label><div class="col-sm-8"><select id="attribute_item_'+attr_key+'" name="attribute_item[]" class="form-control select2ref" multiple="" tabindex="-1" aria-hidden="true">'+optionHtml+'</select></div></div>';
    				//alert(append_html);
    				$('#attributes_divs').append(append_html);	
    				$(".select2ref").select2();     
    			} 
    		}
    	});
    }
</script>
<script>
	var cnt_varns = 0;
    $(document).on('click','#variations_id', function(e){
	cnt_varns++;
    	var obj2 = $('.variation_combo');
        var combo_cnt = 0;
        var combo_sel_cnt = 0;
        var attribute_item_array = [];
        var attribute_item_txt_array = [];
        $.each($(".variation_combo option:selected"), function(){
            combo_cnt++
            if($(this).val()){
                attribute_item_array.push($(this).val());
                attribute_item_txt_array.push($(this).text());
                combo_sel_cnt++;
            }
        });
        if(combo_cnt == combo_sel_cnt){
            attribute_item_array.sort((a, b) => a - b);
            var ids = attribute_item_array.toString();
            var txt = attribute_item_txt_array.join('#');
    	    variations_post(ids,txt, cnt_varns);
            e.stopPropagation();
        }else{
            swal({title: "Status!", text: 'Please select each variation item', timer: 2000, buttons: false});
        }
    } );
    
    var attr_name = '';
    var attr_key = '';
    function variations_post(attr_key,hash, cnt_varns)
    {
		var attr_key_all = attr_key.replace(/,/g,'');

        var variation_id = Date.now();
        var variations_html =   '<div class="border p-2 sadow m-t-10" style="background: #fbfbfb;" id="variation-'+variation_id+'"><div class="form-group row pp"><label for="variations-Black" class="col-sm-3 col-form-label">#'+hash+'</label><div class="col-sm-7 col-md-7">Upload image<input type="hidden" name="attr_itemid[]" class="attr_itemid" value='+attr_key+'>'+ '<input type="file" name="_thumbnail_id_'+attr_key_all+'[]" id="_thumbnail_id" class="form-control" value="" required data-parsley-filemaxsize="1000" data-parsley-fileextension="jpg|png|jpeg|webp" parsley-trigger="change" data-parsley-errors-container="#variation_file_error_'+variation_id+'"><div id="add_more_images_'+variation_id+'"></div><button data-attrkey="'+attr_key_all+'" data-id="'+variation_id+'" type="button" class="btn btn-primary add_new_img">ADD</button><div id="variation_file_error_'+variation_id+'"></div></div>'+
        '<div class="col-sm-2 col-md-2"><button type="button" name="variation-remove" data-variationid="variation-'+variation_id+'" data-type="site" class="btn btn-danger btn_remove_variation p-0" style="padding:0px 7px !important;">X</button></div></div>'+
        '<div class="row mb-2">'+
    	'<div class="col-sm-6 col-md-6"><label for="Regular price" class="col-sm-12 col-form-label">Regular price</label><input type="text" name="_regular_price[]" id="_regular_price" class="form-control unsigned_float" value="" placeholder="" required></div>'+
    	'<div class="col-sm-6 col-md-6"><label for="Sale price" class="col-sm-12 col-form-label">Sale price</label><input type="text" name="_sale_price[]" id="_sale_price" class="form-control unsigned_float" value="" placeholder=""></div>'+
    	'<div class="col-sm-6 col-md-6"><label for="stock" class="col-sm-12 col-form-label">Stock</label><input type="text" name="_stock[]" id="_stock" class="form-control numeric" style="" value="" placeholder="" required></div>'+
        '</div></div></div>'; 
    
        $('#variations_divs').append(variations_html);	
    }

	$(document).on('click','.add_new_img', function(e){
		var variation_id = Date.now();
		var varn_attrkey = $(this).attr("data-attrkey");
		var varn_imgids = $(this).attr("data-id");
		console.log("varn_imgids== "+ varn_imgids);
		var image_html = '<div id="imgs-'+variation_id+'"><input type="file" name="_thumbnail_id_'+varn_attrkey+'[]" id="_thumbnail_id" class="form-control" value="" required data-parsley-filemaxsize="1000" data-parsley-fileextension="jpg|png|jpeg|webp" parsley-trigger="change" data-parsley-errors-container="#variation_file_error_'+variation_id+'"><button type="button" name="img-remove" data-varimgs="imgs-'+variation_id+'" data-type="site" class="btn btn-danger btn_remove_imgs p-0" style="padding:0px 7px !important;">X</button>';
		$('#add_more_images_'+varn_imgids).append(image_html);	
	});

	$(document).on('click', '.btn_remove_imgs', function(e){
		var uid = $(this).attr("data-id");
        var variation_id = $(this).attr("data-varimgs");    
        $('#'+ variation_id).remove();
	});
    
    $(document).on('click','#attribute_id', function(e){
        e.preventDefault();
        //var val = $(this).val();
        attr_key = $('#attr_class option:selected').val();
        attr_name = $('#attr_class option:selected').text();
        $("#attr_class option[value='"+attr_key+"']").remove(); 
    });
    
    $(document).on('click','#variations_id', function(e){
        e.preventDefault();
        //var val = $(this).val();
        attr_key = $('#variations_class option:selected').val();
        attr_name = $('#variations_class option:selected').text();
        //$("#variations_class option[value='"+attr_key+"']").remove();
    });
    
    $(document).on('click', '.attr_remove', function(){  
    	   var button_id = $(this).attr("id"); 
    	   var res = confirm('Are You Sure You Want To Delete This..?');
    	   if(res==true){
    	   $('#attr-'+button_id+'').remove();  
    	   $('#'+button_id+'').remove();  
    	   }
    });

    $('.fancy_date').daterangepicker({
        autoUpdateInput: false,
        minDate: moment().format('YYYY-MM-DD H:mm'),
        startDate: nearestMinutes(0),
        timePickerIncrement: 15,
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        timePicker24Hour: true,
        drops: 'up',
        locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD H:mm' }
    });
    
    $('.fancy_date').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD H:mm'));
        $(this).parsley().reset();
    });
    
    $('.fancy_date').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $(this).parsley().validate()
    });
    
    function nearestMinutes(mnt){
        time = moment();
        round_interval = 15;
        intervals = Math.ceil(time.minutes() / round_interval);
        minutes = intervals * round_interval;
        time.minutes(minutes);
        return time.add(mnt,'minutes').format('YYYY-MM-DD H:mm');
    }
    
    $(document).ready(function() {
        window.ParsleyValidator.addValidator('myvalidator',
        function (value) {
            var sdate = $('#sale_price_dates_from').val();
            var edate = $('#sale_price_dates_to').val();
            var error = 0;
            if(sdate && edate){
                var sfepoch = Date.parse(sdate)/1000;
                var stepoch = Date.parse(edate)/1000;
    
                if(sfepoch > stepoch){
                    error = 1;
                       // return false;
                }else{
                       // return true;
                }
            }else{
                //    return false;
            }
            
            if(error == 1){
                return false;
            }else{
                return true;
            }
        }, 32)
    
        .addMessage('en', 'myvalidator', 'Start Date can\'t be greater than End Date');
    });


    $('#type').on('change', function() {
        if(this.value == 'simple'){
            $('#v-pills-simple-tab').show();
            $('#v-pills-profile-tab').show();
            $('#v-pills-advanced-tab').hide();
            $('#v-pills-simple-tab').click();
            $("#stock").attr("required", true);
            $("#regular_price").attr("required", true);
        }else{
            $('#v-pills-simple-tab').hide();
            $('#v-pills-profile-tab').hide();
            $('#v-pills-advanced-tab').show();
            $('#v-pills-advanced-tab').click();
            $("#stock").attr("required", false);
            $("#regular_price").attr("required", false);
        }
    });

    $( document ).ready(function() {
    <?php if(isset($type) && $type == 'variable'){ ?>
        $('#stock').attr('required', false);
        $('#regular_price').attr('required', false);
        $('#v-pills-profile-tab').hide();
    <?php }else{  ?>
            $('#v-pills-simple-tab').show();
    <?php } ?>
    });


$(document).ready(function() {
    window.Parsley
    .addValidator('fileextension', function (value, requirement) {
        var tagslistarr = requirement.split('|');
        var fileExtension = value.split('.').pop();
        var arr=[];
        $.each(tagslistarr,function(i,val){
         arr.push(val);
        });
        if(jQuery.inArray(fileExtension, arr)!='-1') {
          return true;
        } else {
          setTimeout(function(){
          $('.parsley-fileextension').addClass('parsley-custom-error-message');
          },1);
          return false;
        }
    }, 32)
    .addMessage('en', 'fileextension', 'The extension doesn\'t match the required');

    window.Parsley.addValidator('filemaxsize', {
        validateString: function(_value, maxSize, parsleyInstance) {
            var files = parsleyInstance.$element[0].files;
            var ret = files.length != 1  || files[0].size <= maxSize * 1024;
            if(ret){
                return true;
            }else{
                setTimeout(function(){
                $('.parsley-filemaxsize').addClass('parsley-custom-error-message');
                },1);
                return false;
            }
        },
        requirementType: 'integer',
        messages: {
            en: 'This file should not be larger than %s Kb',
        }
    });
});

<?php if($model == 'update'){ ?>
if($('#type').val() ==  'variable'){
    $(document).on('select2:unselecting', '.attribute_item', function(evt){  
    //$('.attribute_item').on("select2:unselecting", function(evt) { 
        var id = evt.params.args.data.id
        //console.log(id);
        var all_item_array = [];
        $(".attr_itemid").each(function() {
            var item_val = $(this).val();
            var item_array = item_val.split(',');
            for(var j in item_array){
                all_item_array.push(item_array[j]);
            }
        });
        //console.log(all_item_array);
        var a = all_item_array.includes(id);
        if(a){
            alert('Cant delete the attribute it is use in variation');
            return false;
        }
    });
}
<?php } ?>
</script>
