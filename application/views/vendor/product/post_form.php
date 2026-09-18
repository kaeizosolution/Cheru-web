<!--<link rel="stylesheet" href="/assets/plugins/parsley/parsley.css" />
<script src="/assets/plugins/parsley/parsley.min.js"></script> -->

<?php
$controller = $this->router->fetch_class();
$model = $this->router->fetch_method();
$page_lang = get_page_language_data('admin_page_lang');
?>

	
<!--********************************** 
	Content body start
***********************************-->
<div class="content-body">
	<div class="container-fluid">
		<div class="row page-titles">
			 <?= $breadcrumbs ?>
		</div>
		
		<form method="POST" class="addpost" id="postForm" action='<?php echo base_url()."$TYPE/product/$action/$product_id"; ?>' enctype="multipart/form-data" autocomplete='off' onsubmit="return validatePrices()">
    
		<!-- row -->
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<div class="col-md-12 col-sm-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Product</h4>
						</div>
						
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
                                        <li class="nav-item" style="display:none;"> <a class="nav-link" data-toggle="tab" href="#profile" role="tab" style="font-size: 14px;"> Spanish </a> </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="home" role="tabpanel">
                                            <div class="card-body row">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Title </label>
                                                    <input type="text" name="post_title"  class="form-control"  value="<?= isset($post_title) ? $post_title : '' ?>" placeholder="Enter Title" onkeypress="return check(event)" required>
                                                </div>
												<div class="form-group col-md-6">
													<label class="form-label">Highlights</label>
													<textarea class="form-control" rows="2" name="highlight" id="highlight"><?= isset($highlight) ? $highlight : '' ?></textarea>
												</div>

												<div class="form-group col-md-6 col-sm-6">
													<label class="form-label">Easy Payment Options</label>
													<textarea class="form-control" rows="2" id="easy_pymnt_option" name="easy_pymnt_option"><?= isset($easy_pymnt_option) ? $easy_pymnt_option : '' ?></textarea>
												</div>

                                                <div class="form-group mb-0">
                                                    <label class="form-label">Product Details</label>
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


							<div class="col-md-6">
                                <div class="form-group">                                    
                                    <label>Category</label>
										<select name="cat_id" id="parent_category_combo" class="select2 form-control main_category_combo" required data-parsley-errors-container="#main_category_error">								<option value="">Select Category</option>
											<?php
												$main_cat = '';
												if($parent_categories)
												{
													foreach($parent_categories as $row_wise)
													{
														$id = $row_wise['id'];
														$name = $row_wise['name'];
														$selected = $id == $cat_id ? 'selected' : '';
														$main_cat .=<<<HTML
															<option value="$id" $selected>$name</option>
HTML;
													}
												}
											?>	
                                        	<?= $main_cat?>	
                                        
                                    </select>
                                    <div id="main_category_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                           		<div class="form-group">                                    
                                    <label>Sub Category</label>
										<select name="sub_cat_id" id="sub_cat" class="select2 form-control sub_category_combo" placeholder="subcat">											
											<option>Select Category</option>
											
                                    </select>
                                </div> 
                            </div>	

                            <div class="form-group col-md-6 m-t-20 mb-3">
                                <label for="post_status"><?= $page_lang->categories; ?></label>
                                <?php 
                                if(isset($categories)){ ?>
                                    <select name="s_sub_cat_id" id="subsub_cat" class="select2 form-control sub_subcategory_combo" data-parsley-errors-container="#category_error">
                                        <option></option>
                                    </select>
                                <?php } ?>
                                    <div id="category_error"></div>
                            </div>
                            <?php   ?>
                            <div class="form-group col-md-6 m-t-20" style="display:none">
                                <label for="post_status">Tag</label>
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
                                </select>
                                <div id="brand_id_error"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="post_status"><?= $page_lang->supplier; ?></label>
                                <select id="supplier_id" name="supplier_id" class="form-control supplier_combo" data-parsley-errors-container="#supplier_id_error" required readonly>
                                    <option></option>
									<option value="<?=$supplier_obj->supplier_id?>" selected><?=$supplier_obj->cname?></option>
                                </select>
                            </div>
                             <div class="form-group col-sm-6 col-md-6">
                                
							<label class="form-label"><?= $page_lang->choose_files; ?></label>
                            <?php if(isset($gallery) <= 0) { $required ='required'; }else{ $required = '';} ?>
							<div class="input-group new-formfile mb-3"><div class="form-file">
								<input type="file" class="form-control" id="image_url" name="upload_Files[]" multiple data-parsley-filemaxsize="1000" data-parsley-fileextension="jpg|png|jpeg|webp" parsley-trigger="change" data-parsley-errors-container="#upload_files_error" <?= $required; ?>/>	
							</div></div>
							
							<div id="upload_files_error"></div>
                            </div>
							
                            <div class="col-sm-12">&nbsp;</div>                           
                            <div class="col-sm-12 col-md-12">
                                <div class="gallery">
                                    <ul>
                                        <?php if(isset($gallery) && !empty($gallery)): foreach($gallery as $key=>$file):
                                            $file_name = isset($file->file_name) ? $file->file_name : '';
                                            $imgid = isset($file->id) ? $file->id : '';
                                            ?>
                                        <li>
                                            <button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none"  type="button" data-id="<?php echo $imgid; ?>" data-imgdeltype="galry"><i class="fa fa-trash"></i></button>	
                                            <img width="150" height="150" src="<?php echo base_url('assets/uploads/files/'); echo $file_name; ?>" alt="" >
                                        </li>
										<input type="hidden" name="gallery_images[]" value="<?=$file_name?>">
                                        <?php endforeach; else: ?>                                     
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                           
                            
                        </div>
                    </div>
					</div>
				</div>
				
				<div class="col-md-12 col-sm-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title"> <?= $page_lang->product_data; ?></h4>
							  <label for="product-type"><select id="type" name="type" class="form-control" <?= isset($product_id) ? 'disabled' : '' ?>>
								<option value="simple" <?= (isset($type) && $type == 'simple') ? 'selected' : '' ?>> <?= $page_lang->simple; ?></option>
								<option value="variable" <?= (isset($type) && $type == 'variable') ? 'selected' : '' ?>> <?= $page_lang->variable; ?></option>
							</select>
							<?php if(isset($product_id)){ ?>
								<input type="hidden" name="type" value="<?= $type ?>">
							<?php } ?>  
							</label>      
						</div>
						
						
						 <div class="card-body"><div class="row">
							<div class="col-md-12 varient_errors" id="varient_errors"></div> 
						   <div class="col-sm-3 form-menu">
                                <div class="nav flex-column nav-pills mb-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true"><i class="fa fa-wrench" aria-hidden="true"></i> <span><?= $page_lang->general; ?></span></a>
                                     <a class="nav-link" id="v-pills-simple-tab" data-toggle="pill" href="#v-pills-simple" role="tab" aria-controls="v-pills-simple" aria-selected="false" <?= (isset($type) && $type == 'simple') ? '' : 'style="display:none;"' ?>><i class="fa fa-cog" aria-hidden="true"></i> <span><?= $page_lang->simple; ?></span></a>
									<a class="nav-link" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false"><i class="fa fa-truck" aria-hidden="true"></i> <span><?= $page_lang->shipping; ?></span></a>
                                    <a class="nav-link" id="v-pills-attributes-tab" data-toggle="pill" href="#v-pills-attributes" role="tab" aria-controls="v-pills-attributes" aria-selected="false"><i class="fa fa-clipboard" aria-hidden="true"></i> <span><?= $page_lang->attributes; ?></span></a>
                                    <!--<a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="fa fa-file-text" aria-hidden="true"></i> <span><?= $page_lang->price_range; ?></span></a>-->
                                   
                                    <a class="nav-link" id="v-pills-advanced-tab" data-toggle="pill" href="#v-pills-advanced" role="tab" aria-controls="v-pills-advanced" aria-selected="false" <?= (isset($type) && $type == 'variable') ? '' : 'style="display:none;"' ?>><i class="fa fa-cog" aria-hidden="true"></i> <span><?= $page_lang->variant; ?></span></a>
								</div>
                            </div>
							
							<div class="col-sm-9 form-detail">
							
                                <div class="tab-content" id="v-pills-tabContent">
									<div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                        
										<div class="row">
                                            <label for="SKU" class="col-sm-3 col-form-label"><?= $page_lang->sku; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="sku" class="form-control"  value="<?= isset($sku) ? $sku : '' ?>" placeholder="Enter sku" onkeypress="return check(event)" data-parsley-errors-container="#varient_errors" data-parsley-error-message="Please enter SKU." required>
                                            </div>
                                            <div class="col-sm-1">
                                                <i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and services that can be purchased."></i>
                                            </div>
                                        </div>
										
                                        <div class="row mt-3">
                                            <label for="tax" class="col-sm-3 col-form-label"><?= $page_lang->tax; ?></label>
                                            <div class="col-sm-8">
                                                <select id="tax_id" name="tax_id" class="form-control tax_combo"><option value="">Select</option>
                                                <?php 
                                                    foreach($tax_obj as $row){
                                                        $tax_seleted = (isset($tax_id) && $tax_id == $row->tax_id) ? 'Selected' : '';
                                                        echo "<option value=\"$row->tax_id\" $tax_seleted>$row->name</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-1">
                                                <i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Define whether or not the entire product is taxable, or just the cost of shipping it."></i>
                                            </div>
                                        </div>
                                        <hr>
                                        <?php if( isset($sale_price_dates_from) && !empty($sale_price_dates_from)){	
                                            $st = "display:block";
                                            }else{
                                            $st = "display:block";
                                            } ?>
                                        <div class="row" style="<?php echo $st; ?>">
                                            <div class="form-group row">
                                                <label for="Sale price" class="col-sm-3 col-form-label"><?= $page_lang->sale_price_dates; ?></label> 
                                                <div class="col-sm-9">
                                                    <input type="text" name="sale_price_dates_from" value="<?= isset($sale_price_dates_from) ? $sale_price_dates_from : '' ?>"  class="form-control fancy_date" id="sale_price_dates_from" placeholder="From… YYYY-MM-DD" data-parsley-myvalidator="" data-parsley-error-message="Start Date can't be greater than End Date" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-3">
                                                <label for="Sale price" class="col-sm-3 col-form-label"></label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="sale_price_dates_to" value="<?= isset($sale_price_dates_to) ? $sale_price_dates_to : '' ?>"  class="form-control fancy_date" id="sale_price_dates_to" placeholder="To…  YYYY-MM-DD" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div> 
									 <div class="tab-pane fade container-fluid" id="v-pills-simple" role="tabpanel" aria-labelledby="v-pills-simple-tab">
                                        <div class="form-group row mt-20">
                                            <label for="Stock" class="col-sm-3 col-form-label"><?= $page_lang->stock; ?></label>
                                            <div class="col">
                                                <input type="text" name="stock" class="form-control numeric" id="stock" value="<?= isset($stock) ? $stock : '' ?>" placeholder="Stock Qty" data-parsley-errors-container="#varient_errors" data-parsley-error-message="Please enter Stock Qty" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mt-3">
                                            <label for="Regular price" class="col-sm-3 col-form-label"><?= $page_lang->regular_price; ?></label>
                                            <div class="col">
                                                <input type="text" name="regular_price" class="form-control no_validate" id="regular_price" value="<?= isset($regular_price) ? $regular_price : '' ?>" placeholder="Regular price" data-parsley-errors-container="#varient_errors" data-parsley-error-message="Please enter Regular Price" required>
                                            </div>
                                        </div>
                                        <div class="form-group row mt-3">
                                            <label for="Sale price" class="col-sm-3 col-form-label"><?= $page_lang->sale_price; ?></label>
                                            <div class="col">
                                                <input type="text" name="sale_price" class="form-control no_validate" id="sale_price" value="<?= isset($sale_price) ? $sale_price : '' ?>" placeholder="Sale price">
                                            </div>
                                        </div>
                                    </div>
									<div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
									
                                        <div class="form-group row">
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
                                                <?php }}else{ } ?>
                                            </div>
                                        </div>
                                        <div class="form-group row mt-3">
                                            <!--<div class="col-sm-3"></div>-->
                                            <div class="col-sm-6">
                                                <button type="button" name="add" id="add" class="btn btn-success"><?= $page_lang->add_more; ?></button>
                                            </div>
                                        </div>
                                        <script type="text/javascript">
                                            $(document).ready(function(){      
                                              var i=1;  
                                              $('#add').click(function(){  
                                            	   i++;             
                                            	   $('#dynamic_field').append('<div class="row mt-2" id="row'+i+'"> <div class="col-sm-5 col-md-5"><div class="form-group"><input type="text" class="form-control numeric_range"  placeholder="Enter quantity " name="quantity_range[]" autocomplete="off" required></div></div> <div class="col-sm-5 col-md-5"><div class="form-group"><input type="text" class="form-control unsigned_float" id="price_range" placeholder="Enter Price" name="price_range[]" autocomplete="off" required></div></div> <div class="col-sm-1 col-md-1"><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></div></div></div>');
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
                                            <label for="Weight" class="col-sm-3 col-form-label"><?= $page_lang->weight_kg; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="weight" value="<?= isset($weight) ? $weight : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_weight" placeholder="Weight">
                                            </div>
                                            <div class="col-sm-1">
                                                <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Weight in decimal form"></i>
                                            </div>
                                        </div>
                                        <div class="form-group row mt-3">
										
												<label class="col-sm-3 col-form-label" for="Dimensions"><?= $page_lang->dimensions_cm; ?></label>                                           
                                                <div class="col">
                                                    <input type="text" name="length" value="<?= isset($length) ? $length : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_length" placeholder="Length">
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="width" value="<?= isset($width) ? $width : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_width" placeholder="Width">
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="height" value="<?= isset($height) ? $height : '' ?>" class="form-control wc_input_decimal unsigned_float" id="product_height" placeholder="Height">
                                                </div>
                                           
                                            <div class="col-sm-1">
                                                <i class="fa fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="LxWxH in decimal form"></i>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group row mt-3">
                                            <label for="Shipping" class="col-sm-3 col-form-label"><?= $page_lang->shipping; ?></label>
                                            <div class="col-sm-8">
                                                <select id="shipping_id" name="shipping_id" class="form-control shipping_combo" data-parsley-errors-container="#varient_errors" data-parsley-error-message="Please select shipping type in shipping tab."><option value="">Select</option>
                                                <?php
                                                    //$vehicle_type_obj = ;
                                                    foreach($shipping_obj as $row){
                                                        if(empty($vehicle_type_obj[$row->vehicle_type])){continue;}
                                                        $name = $vehicle_type_obj[$row->vehicle_type];
                                         
                                                        $shipping_seleted = (isset($shipping_id) && $shipping_id == $row->vehicle_type) ? 'Selected' : '';
                                                        echo "<option value=\"$row->vehicle_type\" $shipping_seleted>$name</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-1">
                                                <i class="fa fa-question-circle ttc" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Shipping classes are used by certain shipping methoods to group similar products."></i>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
									
									<div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">...</div>
									 <div class="tab-pane fade" id="v-pills-attributes" role="tabpanel" aria-labelledby="v-pills-attributes-tab">
                                        <div class="form-group row mt-3">
                                            <div class="col-sm-6">
                                                <select id="attr_class" name="attr_class" class="form-control attribute_combo">
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
                                                <select id="attribute_item_<?php echo $att_id; ?>" name="attribute_item[]" class="form-control attribute_item attr_item" multiple required>
                                                <?php
                                                foreach($att_item_array as $val2)
                                                {
                                                    $atrr_id = $val2->attribute_id;
                        
                                                    if(isset($attribute_by_item[$atrr_id]))
                                                    {
                                                        $html = '';
                                                        foreach($attribute_by_item[$atrr_id] as $inner_val)
                                                        {
                                                            $item_id = $inner_val->attribute_item_id;
                                                            $name = $inner_val->name;
                                                            $selected = $val2->attribute_item_id == $item_id ? 'selected' : '';
                                                            $html .=<<<HTML
                                                                <option value="$item_id" $selected>$name</option>           
HTML;
                                                        }
                                                        echo $html;
                                                    }
                }
        ?>

                                                </select>
                                            </div>
                                            <div class="col-sm-2 col-md-2"><button type="button" name="remove" id="<?php echo $att_id; ?>" class="btn btn-danger attr_remove">X</button></div>
                                        </div>
                                        <?php }}?>
                                        <div id="attributes_divs">&nbsp;</div>
										<div class="col-sm-8 mt-2 save_attr_btn" style="<?=(isset($type) && $type == 'variable') ? 'display:block;' : 'display:none;' ?>">                            <button type="submit" class="btn btn-primary btn-block" id="save_attr" name="save_attr" value="save_attr">Save Attributes</button>                                              </div>
                                    </div>
									
								  
									<div class="tab-pane fade container-fluid" id="v-pills-advanced" role="tabpanel" aria-labelledby="v-pills-advanced-tab">
                                        <div class="row">
                                            <?php
                                            if($selected_attribute_item){
                                            foreach($selected_attribute_item as $att_id => $att_item_array){
                                            ?>
                                            <div class="col-sm-5">
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
                                        <p style="color:#bb77ae;"><?= $page_lang->variant_disc; ?> NOTE: No of files upload at one time: 20</p>                                    
									    <div class="vartions_comb_msg"></div>		  
                                        <div id="variations_data">
                                            <?php
											$img_html1 = ''; 
											if(isset($selected_variation_obj)) : foreach($selected_variation_obj_key as $k=>$v){
	
												$v_all = $v; 
												$v = $v[0];
                                                $variation_id = isset($v->variation_id) ? $v->variation_id:'';
                                                $attribute_item_id = isset($v->attribute_item_id) ? $v->attribute_item_id:'';
                                                $_sale_price = isset($v->_sale_price) ? $v->_sale_price:'';
                                                $_regular_price = isset($v->_regular_price) ? $v->_regular_price:'';
                                                $_stock = isset($v->_stock) ? $v->_stock:'';
                                                $_thumbnail = isset($v->_thumbnail_id) ? $v->_thumbnail_id:'';
												$img_html = '<ul class="imagesVarient">';
												$img_cnt = 0; $del_btn = '';
												$thumbnail_vall_img = '';
												foreach($v_all as $v_all_row)
												{
													$img_cnt++;
													$variation_id_vall = $v_all_row->variation_id;
													$thumbnail_vall = $v_all_row->_thumbnail_id;
													$thumbnail_vall_img .= $thumbnail_vall.',';
													
													$rquired = $thumbnail_vall ? '' : 'required';
																

													if(count($v_all) > 1)
														{
														
															$del_btn = '<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" data-vid="'.str_replace(',','_',$attribute_item_id).'" class="btn-delete del-v-img-'.str_replace(',','_',$attribute_item_id).'" style=" background:none; color:red;" type="button" data-id="'.$variation_id_vall.'" data-imgdeltype="variton"><i class="fa fa-trash"></i></button>';
													}	
														$img_html .= '<li>'.$del_btn.'<img width="150" height="150" src="/assets/uploads/'.$thumbnail_vall.'" alt="" ></li><input type="hidden" id="hidden_'.$variation_id_vall.'" name="variations_image[]" value="'.$thumbnail_vall.'">';
												}
												$img_html .='</ul>';

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
                                                    <label for="variations-Black" class="col-sm-12 col-form-label"><?php echo '#'.$item_name ?></label>
                                                    <div class="col-sm-12 col-md-12"><?= $page_lang->upoad_image; ?><input type="hidden" name="attr_itemid[]" class="attr_itemid" value="<?= $attribute_item_id ?>">
													<input type="file" multiple name="_thumbnail_id_<?=$k?>[]" id="_thumbnail_id_<?php echo $k; ?>" class="form-control varient_files iv-<?=str_replace(',','_',$attribute_item_id)?>" value="<?=$thumbnail_vall_img ?>" <?= ($thumbnail_vall) ? '' : 'required' ?> data-parsley-filemaxsize="2040000000" data-parsley-fileextension="jpg|png|jpeg|webp" parsley-trigger="change" data-parsley-errors-container="#variation_file_error_<?=str_replace(',','_',$attribute_item_id)?>" >

                                                       <?= $img_html?> 
                                                    </div>
                                                    <div id="variation_file_error_<?=str_replace(',','_',$attribute_item_id)?>"></div>

                                                    <div class="col-sm-2 col-md-2"><button type="button" name="variation-remove" data-id="<?php echo $attribute_item_id; ?>" data-type="db" data-variationid="variation-<?php echo $variation_id; ?>" class="btn btn-danger btn_remove_variation p-0"  style="padding:0px 7px !important; position: absolute; right: -1px; top: -1px;  border-radius: 0px;">X</button></div>
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
							</div><!-------end row------->
						 
						</div><!-------end card-body------->
						
						
						
						
						
						
					</div>
				</div>
				
			</div>
			
			<div class="col-md-12 col-sm-12">
				<div class="basic-form">					
						<div class="mb-3 row">
							<div class="col-sm-4 mt-2">
							<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
							<button type="button" onclick="location.href='/vendor/product'"  class="btn btn-outline-primary btn-block"> <?= $page_lang->back; ?></button>
							
							</div>
							<div class="col-sm-8 mt-2">
							<button type="submit" id="confirm" class="btn btn-primary btn-block"><?= $page_lang->submit; ?></button>								
							</div>
						</div>				
				</div>	
			</div>
		</div>
		
		<?php echo form_close() ?> 	
		
	</div>
</div>
<!--**********************************
	Content body end
***********************************-->


<script>
var cat_id_db = "<?=isset($cat_id) ? $cat_id : ''?>";
var sub_cat_id_db = "<?=isset($sub_cat_id) ? $sub_cat_id : ''?>";
var s_sub_cat_id_db = "<?=isset($s_sub_cat_id) ? $s_sub_cat_id : ''?>";
var brand_id_db = "<?=isset($brand_id) ? $brand_id : ''?>";

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
   var is_duplicate_varient = [];
 
    $(document).ready(function() {
    //    $('select').select2({allowClear: true, placeholder: "Attributes"});
        $('.main_category_combo').select2({allowClear: true, placeholder: "Main Category"});
        $('.sub_category_combo').select2({allowClear: true, placeholder: "Sub Category"});
        $('.sub_subcategory_combo').select2({allowClear: true, placeholder: "Sub Category"});
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
        var btn_click = $(document.activeElement).val();
        if($('#type').val() ==  'variable' && btn_click != 'save_attr'){
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
                
            });
        }
        <?php } ?>

        if($('#type').val() ==  'variable')
        {
        var attri = 0;
            $(".attr_item").each(function() {
                if(this.value){
                    attri = 1;
                }
            });
            if(attri == 0){
                alert('Please Add at least one Attribute');
                return false;
            }
        }

 
			
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
        
        //$('.attr_item').on('select2:unselecting', function (e) {
            //alert('You clicked on '+ this.value);
        //});

        $(document).on('select2:unselecting', '.attribute_item', function(evt){  
            var id = evt.params.args.data.id
            var all_item_array = [];
            $(".attr_itemid").each(function() {
                var item_val = $(this).val();
                var item_array = item_val.split(',');
                for(var j in item_array){
                    all_item_array.push(item_array[j]);
                }
            });
            var a = all_item_array.includes(id);
            if(a){
                alert('Cant delete the attribute it is use in variation');
                return false;
            }
    });


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
    	
        var obj = $(this);
        var id=$(this).attr('data-id'); 
		var vid=$(this).data('vid'); 

		
		$('#hidden_'+id).val(''); 
        var postData = {};
    	var csrf_name = "<?= $csrf->name; ?>";
    	var csrf_value = "<?= $csrf->hash; ?>";
    	postData[csrf_name] =  csrf_value;
        postData.id = id;
        postData.product_id = <?= isset($product_id) ? $product_id : 0 ?>;
		postData.imgdeltype = $(this).attr('data-imgdeltype');
        $.ajax({
                url         :   '<?php echo base_url();?>vendor/product/delimg',
                type        :   'post',
    		    enctype	    :   'multipart/form-data',
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if(data.success)
                    {
						
						
		
                        obj.closest('li').remove();

						if(!$('.del-v-img-'+vid).length)
								$('.iv-'+vid).attr('required',true);
						else if($('.del-v-img-'+vid).length == 1){
							$('.del-v-img-'+vid).hide();
						}else{
							$('.del-v-img-'+vid).show();
						}

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
    	var attr_key = $(this).attr("data-id");	
    	//alert(uid);
        if(type == 'db'){	
    	    delete_variation_post(variation_id,product_id,attr_key);
        }else{
            $('#'+ variation_id).remove();
            is_duplicate_varient = arrayRemove(is_duplicate_varient, $(this).attr("data-hash")); 
            
        }
    } );
    
    function delete_variation_post(variation_id,product_id,attr_key)
    {
        var variation_id_arr = variation_id.split('variation-');
        var postData = {};
    	var csrf_name = "<?= $csrf->name; ?>";
    	var csrf_value = "<?= $csrf->hash; ?>";
    	postData[csrf_name] =  csrf_value;
        postData.variation_id = variation_id_arr[1];
    	postData.product_id = product_id;
    	postData.attr_key = attr_key;
    	
        $.ajax({
                url         :   '<?php echo base_url();?>vendor/product/delete_variation',
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
    			url         :   '<?php echo base_url();?>vendor/Product/get_attr',
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
    				var append_html =   '<div class="form-group row mt-3" id="'+attr_key+'"><label for="Attribute-'+attr_name+'" class="col-sm-2 col-form-label">'+attr_name+'</label><div class="col-sm-8"><select id="attribute_item_'+attr_key+'" name="attribute_item[]" class="form-control select2ref attr_item" multiple="" tabindex="-1" aria-hidden="true" required>'+optionHtml+'</select></div><div class="col-sm-2 col-md-2"><button type="button" name="remove" id="'+attr_key+'" class="btn btn-danger attr_remove">X</button></div></div>';
    				//alert(append_html);
    				$('#attributes_divs').append(append_html);	
    				$(".select2ref").select2();     
    			} 
    		}
    	});
    }
</script>
<script>
    //var is_duplicate_varient = [];
    $(document).on('click','#variations_id', function(e){
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
            if(is_duplicate_varient.indexOf(txt) != '-1')
            {
                $('.vartions_comb_msg').addClass('alert alert-danger');
                $('.vartions_comb_msg').show();
                $('.vartions_comb_msg').html('This combination already added.');
                $('.vartions_comb_msg').delay(2000).fadeOut();
                
                return false;
            }
            is_duplicate_varient.push(txt);        
    
    	    variations_post(ids,txt);
            e.stopPropagation();
        }else{
            alert('Please select each variation item');
            //swal({title: "Status!", text: 'Please select each variation item', timer: 2000, buttons: false});
        }
        
    } );

    function arrayRemove(arr, value) {
 
        return arr.filter(function(del_val){
        return del_val != value;
   });
 
}
    
    var attr_name = '';
    var attr_key = '';
    function variations_post(attr_key,hash)
    {
        var variation_id = Date.now();
        var variations_html =   '<div class="border p-2 sadow m-t-10 mb-4" style="background: #fbfbfb;" id="variation-'+variation_id+'"><div class="form-group row pp"><label for="variations-Black" class="col-sm-3 col-form-label">#'+hash+'</label><div class="col-sm-7 col-md-7">Upload image<input type="hidden" name="attr_itemid[]" class="attr_itemid" value='+attr_key+'>'+ '<input type="file" multiple name="_thumbnail_id_'+attr_key+'[]" id="_thumbnail_id_'+variation_id+'" class="form-control varient_files" value="" required data-parsley-filemaxsize="1000" data-parsley-fileextension="jpg|png|jpeg"  onchange="onchangeHandle(this);" accept="image/jpeg,image/png" parsley-trigger="change" data-parsley-errors-container="#variation_file_error_'+variation_id+'"><div id="variation_file_error_'+variation_id+'"></div></div>'+
        '<div class="col-sm-2 col-md-2"><button type="button" name="variation-remove" data-variationid="variation-'+variation_id+'" data-id="'+attr_key+'" data-hash="'+hash+'" data-type="site" class="btn btn-danger btn_remove_variation p-0" style="padding:0px 7px !important; position: absolute; right: -1px; top: -1px;  border-radius: 0px;">X</button></div></div>'+
        '<div class="row mb-2">'+
    	'<div class="col-sm-6 col-md-6"><label for="Regular price" class="col-sm-12 col-form-label">Regular price</label><input type="text" name="_regular_price[]" id="_regular_price" class="form-control unsigned_float" value="" placeholder="" required></div>'+
    	'<div class="col-sm-6 col-md-6"><label for="Sale price" class="col-sm-12 col-form-label">Sale price</label><input type="text" name="_sale_price[]" id="_sale_price" class="form-control unsigned_float" value="" placeholder=""></div>'+
    	'<div class="col-sm-6 col-md-6"><label for="stock" class="col-sm-12 col-form-label">Stock</label><input type="text" name="_stock[]" id="_stock" class="form-control numeric" style="" value="" placeholder="" required></div>'+
        '</div></div></div>'; 
    
        $('#variations_divs').append(variations_html);	
    }
    
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
			 /* var html = `<ul class="parsley-errors-list filled" id="parsley-id-29" aria-hidden="false"><li class="parsley-fileextension parsley-custom-error-message">The extension doesn't match the required</li></ul>`;
			$('#upload_files_error').html(html); */
			
          $('.parsley-fileextension').addClass('parsley-custom-error-message');
          },1);
          return false;
        }
    }, 32)
    .addMessage('en', 'fileextension', 'The extension doesn\'t match the required');

	  
/*
    window.Parsley
    .addValidator('fileextension', function (value, requirement, parsleyInstance) {
        console.log(parsleyInstance);return false;
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
*/
    /*window.Parsley.addValidator('filemaxsize', {
        validateString: function(_value, maxSize, parsleyInstance) {
            console.log(parsleyInstance);return false;
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
    });*/

	$('#type').change(function(){
        if($('#type').val() == 'variable') {
            $('.save_attr_btn').show(); 
        } else {
            $('.save_attr_btn').hide(); 
        } 
    });

sub_cat_combo(cat_id_db);
brand_combo(cat_id_db);

$("#parent_category_combo").change(function(){
	var current_val = $(this).val();
    sub_cat_combo(current_val);

    brand_combo(current_val);

    set_subcat_blank();
    set_brand_blank();

    if(!current_val)
    set_subsubcat_blank();
    //set_brand_blank();

    });

$("#sub_cat").change(function(){
	var current_val = $(this).val();
    subsub_cat_combo(current_val);

    if(!current_val)
    set_subsubcat_blank();

});


});

function set_subsubcat_blank()
{
        $('#subsub_cat').html('<option value="">Select Location</option>');
}

function set_subcat_blank()
{
        $('#sub_cat').html('<option value="">Select Sub Cat</option>');
}

function set_brand_blank()
{
        $('#brand_id').html('<option value="">Select Brand</option>');
}

function sub_cat_combo(current_val)
{
	var post_data = {};
	var csrf_name = "<?= $csrf->name; ?>";
    var csrf_value = "<?= $csrf->hash; ?>";
    post_data[csrf_name] =  csrf_value;
    post_data['category_id'] = current_val;

        var request = $.ajax({
      url: "/vendor/product/ajax_sub_cat",
      type: "POST",
      data: post_data,
      dataType: "json"
    });

	request.done(function(resp) {
		if(resp.STATUS)
        {
        	if(current_val)
            {
            	$('#sub_cat').html('<option value="">Select Sub Category</option>');
                var combo = '';
                $.each(resp.DATA, function( index, value ) {
                	combo += '<option value='+value.id+'>'+value.name+'</option>';
                });
                $('#sub_cat').append(combo);
				$("#sub_cat").val(sub_cat_id_db).trigger('change');
                }
           }	
	});	
}

function subsub_cat_combo(current_val)
{
	var post_data = {};
	var csrf_name = "<?= $csrf->name; ?>";
    var csrf_value = "<?= $csrf->hash; ?>";
    post_data[csrf_name] =  csrf_value;
    post_data['category_id'] = current_val;

        var request = $.ajax({
      url: "/vendor/product/ajax_sub_cat",
      type: "POST",
      data: post_data,
      dataType: "json"
    });

	request.done(function(resp) {
		if(resp.STATUS)
        {
        	if(current_val)
            {
            	$('#subsub_cat').html('<option value="">Select Sub Category</option>');
                var combo = '';
                $.each(resp.DATA, function( index, value ) {
                	combo += '<option value='+value.id+'>'+value.name+'</option>';
                });
                $('#subsub_cat').append(combo);
				$("#subsub_cat").val(s_sub_cat_id_db).trigger('change');
                }
           }
			else
            {
            	$('#subsub_cat').html('<option value="">Select Sub Category</option>');
            }	
	});	
}

function brand_combo(current_val)
{
	var post_data = {};
	var csrf_name = "<?= $csrf->name; ?>";
    var csrf_value = "<?= $csrf->hash; ?>";
    post_data[csrf_name] =  csrf_value;
    post_data['category_id'] = current_val;

        var request = $.ajax({
      url: "/vendor/product/ajax_brand_combo",
      type: "POST",
      data: post_data,
      dataType: "json"
    });

	request.done(function(resp) {
		if(resp.STATUS)
        {
        	if(current_val)
            {
            	$('#brand_id').html('<option value="">Select Brand</option>');
                var combo = '';
                $.each(resp.DATA, function( index, value ) {
                	combo += '<option value='+value.brand_id+'>'+value.name+'</option>';
                });
                $('#brand_id').append(combo);
				$("#brand_id").val(brand_id_db).trigger('change');
                }
           }	
	});	
}

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



  function loadMime(th) {
    var files = th.files;
    //List of known mimes
    var mimes = [
        {
            mime: 'image/jpeg',
            pattern: [0xFF, 0xD8, 0xFF],
            mask: [0xFF, 0xFF, 0xFF],
        },
        {
            mime: 'image/png',
            pattern: [0x89, 0x50, 0x4E, 0x47],
            mask: [0xFF, 0xFF, 0xFF, 0xFF],
        }
        // you can expand this list @see https://mimesniff.spec.whatwg.org/#matching-an-image-type-pattern
    ];

    function check(bytes, mime) {
        for (var i = 0, l = mime.mask.length; i < l; ++i) {
            if ((bytes[i] & mime.mask[i]) - mime.pattern[i] !== 0) {
                return false;
            }
        }
        return true;
    }
	
	

var j = 0;

readImage(files);
	
  function readImage(files){
    var file = files[j];
	
    var blob = file.slice(0, 4); //read the first 4 bytes of the file

    var reader = new FileReader();
    reader.onloadend = function(e) {
        if (e.target.readyState === FileReader.DONE) {
            var bytes = new Uint8Array(e.target.result);

           
	mimes.map(mime=>{
				
                if (check(bytes, mime))
						return ckValidate(mime.mime==file.type,th);
					
			
            })
			
		
		   j++;
		  if(files.length != j){
			readImage(files);
		  }

        }
    };
    reader.readAsArrayBuffer(blob);
  
}


}







var formDefault = $('#postForm').attr('onsubmit');
var stCountArr = [];
var instance = null;

$(()=>{
	 instance = $('#highlight').parsley({message:'ss'}); 

  

})


function onchangeHandle(t){
	var errID = $(t).attr('data-parsley-errors-container');
	
	$(errID).html('');
	console.log('preArr ',stCountArr)
	 var index = stCountArr.indexOf(errID);
if (index !== -1) {
  stCountArr.splice(index, 1);
}

console.log('postArr ',stCountArr)

	loadMime(t);

	setTimeout(()=>{
		if(!stCountArr.length){
			$('#postForm').attr('onsubmit',formDefault);
		}
	},1500)
}


//check image validateion
$("#image_url").change(function(){
	onchangeHandle(this);
})


$(".varient_files").change(function(){
	onchangeHandle(this);
  /* var errID = $(this).attr('data-parsley-errors-container');
	
	
	$(errID).html('');
	console.log('preArr ',stCountArr)
	 var index = stCountArr.indexOf(errID);
if (index !== -1) {
  stCountArr.splice(index, 1);
}

console.log('postArr ',stCountArr)
	 
	loadMime(this,errID);

	setTimeout(()=>{
		if(!stCountArr.length){
			$('#postForm').attr('onsubmit',formDefault);
		}
	},1500)*/
})


function ckSubmit(t,e,id){
	e.preventDefault();
	location.href=id;
	
	return false;
}

function ckValidate(st,th){
		var errID = $(th).attr('data-parsley-errors-container');

	
		if(!st){
			var html = `<ul class="parsley-errors-list filled" id="parsley-id-29" aria-hidden="false"><li class="parsley-fileextension parsley-custom-error-message">The extension doesn't match the required</li></ul>`;
			$(errID).html(html); 
			$('#postForm').attr('onsubmit','return ckSubmit(this,event,"'+errID+'")');
			if(!stCountArr.includes(errID))
				stCountArr.push(errID);
		}

}


$( document ).ready(function() {
    $('#type').on('change', function() {
    var variable = this.value;
        if(variable == 'variable')
        {
            $('#image_url').removeAttr('required');
        }else
        {
            $('#image_url').prop('required',true);
        }
    });
});
	
</script>
