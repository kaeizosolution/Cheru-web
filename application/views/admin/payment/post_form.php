<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?php echo $action.'&nbsp;'.$page_lang->payment; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>.
    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
                    <?php if (($this->session->flashdata('error')) || validation_errors()!='') { ?>
                    <div class="alert alert-danger"><?= validation_errors();?>  <?= $this->session->flashdata('error')?>
                    </div>
                    <?php } ?>

                    <?php if($this->session->flashdata('success')){?>
                       <div class="alert alert-success"> <?= $this->session->flashdata('success')?> </div>
                    <?php } ?>
					
					<ul class="nav nav-tabs profile-tab" role="tablist">
						<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#home" role="tab" style="font-size: 14px;"><?= $page_lang->payment; ?></a></li>
						
					</ul>
					
					<div class="tab-content">
					<div class="tab-pane active" id="home" role="tabpanel">
                        <div class="card"><div class="card-body">
										
					<form method="POST" class="addpost" id="postForm" action='<?php echo "/$TYPE/payment/$action/$payment_id"; ?>' enctype="multipart/form-data" autocomplete='off'>
					
					<div class="card-body">
                        <div class="row">							
							<div class="col-sm-6 col-md-4">
                                <div class="form-group"> 
								<label class="form-label"><?= $page_lang->title; ?></label>								
                                    <input type="text" name="title" class="form-control" value="<?php echo isset($title) ? $title : '' ?>" placeholder="<?= $page_lang->title; ?>" required readonly>  
                                	
								</div>
                            </div>
							<div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label"><?= $page_lang->description; ?></label>
                                    <textarea name="description" rows="5" class="form-control" placeholder="<?= $page_lang->description; ?>..." ><?php echo isset($description) ? $description : '' ?></textarea>
                                </div>
                            </div>
							<div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="form-label"><?= $page_lang->Instructions; ?></label>
                                    <textarea name="instructions" rows="5" class="form-control" placeholder="<?= $page_lang->Instructions; ?>..." ><?php echo isset($instructions) ? $instructions : '' ?></textarea>
                                </div>
                            </div>
							
							<div class="form-group col-md-3 m-t-10">
								<label><?= $page_lang->profile_image; ?></label>
								<input type="file" name="logo" id="image_url" class="form-control" value=""> 								
								<label for="image_url" id="img_error" style="display:none;"  generated="true" class="error">Please enter a value with a valid extension  and image size maximum 50kb.</label>										
								<?php $logo = isset($logo) ? $logo : ''; 	
								if($logo){
								?>
								<button title="Delete" data-toggle="tooltip" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete bg-none" 
										style=" background:none; color:red;" type="button" data-id="<?php echo $payment_id; ?>"><i class="fa fa-trash"></i></button>	

								<img src="<?php echo base_url();?>assets/images/<?php echo $logo; ?>" width="150px" height="150px" alt="feature image" />
								<?php } ?>
							</div>	
								
						    <div class="col-sm-6 col-md-4">
								<div class="form-group"><br >
								<label><?= $page_lang->status; ?></label>	
								<?php $status = isset($status) ? $status : ''; ?>									
								<select class="form-control" name="status" required="required">
								  <option value=""><?= $page_lang->select_status; ?></option>								  
								  <option value="1" <?php if($status=='1') echo "selected=selected"; ?>><?= $page_lang->active; ?></option>                          
								  <option value="0" <?php if($status=='0') echo "selected=selected"; ?>><?= $page_lang->inactive; ?></option>
								</select>
								</div>  
							</div>   
							
                        </div>
                    </div>
                    <div class="card-footer text-right">
						<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                        <button type="button" class="btn btn-light btn_back"><?= $page_lang->back; ?></button>
                        <button type="submit" id="btn-submit"class="btn btn-primary"><?= $page_lang->submit; ?></button>
                    </div>
                    </form>
					</div></div></div>
								
				   </div><!--table end-->
					   
					
                </div>
            </div>
        </div>
    </div>
</div>


<script>

//check image validateion
$("#btn-submit").click(function(){
 $("#img_error").hide();
    var flag=0;
    var files = $("#image_url").get(0).files;
	
	var img_name = $('#image_url').val();
	
    if(img_name!=''){
	var ext = $('#image_url').val().split('.').pop().toLowerCase();

    if($.inArray(ext, ['gif','png','jpg','jpeg','pdf']) == -1){
       flag=1;
    }

    for(var i=0; i<files.length;i++) {

        var file = files[i];
        var name = file.name;
        var size = file.size;
        if(size>1000000){
            flag=1;
        }
      }
    if(flag==1){
        $("#img_error").show();
        return false;
        }
    }
}) 

$(document).ready(function(){
    $(".onlystring").keypress(function(event){
        var inputValue = event.charCode;
        if(!(inputValue >= 65 && inputValue <= 122) && (inputValue != 32 && inputValue != 0)){
            event.preventDefault();
        }
    });
});

//update query
$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/payment/update/"+uid+"";
    url_new_tab(e,url);
} );

function url_new_tab(e,url)
{
    if(e.ctrlKey){
        window.open(url);
    }else{
        $(location).attr('href', url);
    }
}

$(document).on('click','.btn-delete', function(e){
	//alert('dd');
	var obj = $(this);
	var id=$(this).attr('data-id');  
	var postData = {};
	var csrf_name = "<?= $csrf->name; ?>";
	var csrf_value = "<?= $csrf->hash; ?>";
	postData[csrf_name] =  csrf_value;
	postData.id = id;
	postData.payment_id = <?= isset($payment_id) ? $payment_id : 0 ?>;
	$.ajax({
			url         :   '<?php echo base_url();?>admin/payment/delimg',
			type        :   'post',
			enctype	    :   'multipart/form-data',
			dataType    :   "json",  
			data        :   postData,
			success     :   function(data){
				if(data.success)
				{
					if(data.MSG)  {
						swal('Session expired.');
					}		 
					swal('Image Delete'); 
					location.reload(true);
				} 
			}
	});
} );
</script> 
