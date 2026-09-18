<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

    <div class="content-body">
      <div class="container-fluid">			
           <div class="row page-titles"> <?= $breadcrumbs ?> </div> 
        <div class="row">
            <div class="col-sm-12">
			<?php if ($this->session->flashdata('error')) { ?>
			 <div class="alert alert-danger">
				 <?= $this->session->flashdata('error')?>
			 </div>
			 <?php } ?>

			<?php if($this->session->flashdata('success')){?>
			<div class="alert alert-success">
				<?= $this->session->flashdata('success')?>
			</div>
			<?php } ?>
					
			<div class="card">                     
				<div class="card-body">					
					<div class="card-header customHeader">
						<h4 class="card-title">Attribute</h4>
						<div class="transactionBtn">
							<a href="<?= base_url().$TYPE; ?>/attributes/add" type="submit" class="btn btn-primary" ><i class="fa fa-plus" aria-hidden="true"></i></a>
							<a type="submit" href="javascript:void(0)" class="btn btn-primary" data-toggle="modal" data-target="#attribute" ><i class="fa fa-filter" aria-hidden="true"></i></a>
						</div>
					</div>
			   
					<div class="table-responsive">                                    
						<table id="table" class="display customTableProduct">
							<thead>
								<tr>
									<th><?= $page_lang->id; ?></th>
									<th><?= $page_lang->name; ?></th>
									<th><?= $page_lang->items; ?></th>
									<th><?= $page_lang->status; ?></th>
									<th width="35px;"><?= $page_lang->action; ?></th>
								</tr>
							</thead>
							<tbody>&nbsp;</tbody>
						</table>                            
					</div>
				</div>
		</div>
		</div>
        </div><!------end row-------->
		 
	 </div></div><!------end content-body-------->
		

<div class="modal fade" id="exampleModalfat" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">hi</div>
                <div class="alert alert-success">hi</div>
                <?php echo form_open("/$TYPE/attributes/", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST')) ?>
                    <div class="form-group">
                        <label class="form-label"><?= $page_lang->name; ?></label>
                        <input type="text" class="form-control" name="name" id="name" value="" placeholder="<?= $page_lang->name; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $page_lang->slug; ?></label>
                        <input type="text" class="form-control" name="slug" id="slug" value="" placeholder="<?= $page_lang->slug; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $page_lang->description; ?></label>
                        <textarea rows="5" class="form-control" placeholder="<?= $page_lang->description; ?>" name="description"></textarea>
                    </div>
                <?php echo form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"><?= $page_lang->submit; ?></button>
            </div>
        </div>
    </div>

</div>

<!-- Modal -->
<div id="attribute" class="modal fade customModel" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <div class="col-sm-12 sp-top-30">
                    <form id="filter_data" method="post">
                        <div class="row">
                            <div class="col-md-6 col s4">
								<label for="supplier_name"><?= $page_lang->name; ?></label>
                                <input type="text" id="attribute_name" name="name" class="form-control">
                            </div>	
                          <div class="col-md-6">
								<div class="form-group">
									<label><?= $page_lang->status; ?></label>									
									<select class="form-control" id="status" name="status" required="required">	 		
									<option value="" selected><?= $page_lang->status; ?></option>   
									<option value="1"><?= $page_lang->active; ?></option>                          
									<option value="0"><?= $page_lang->inactive; ?></option>   
									</select>
								</div>  
							</div>  
                        </div>
                        <div class="row">
                            <div class="input-field col s12 a-r">
                                <button class="btn waves-effect waves-light btn-large reset-btn" type="button"><?= $page_lang->clear; ?></button>
                                <button class="btn waves-effect waves-light btn-large btnSubmit" type="button"><?= $page_lang->submit; ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$("#filter_data").submit(function(){
	event.preventDefault();
	dataTable();
    $("#attribute .close").click()
	
});

$('.btnSubmit').on('click', function (e){	
    dataTable();
    $("#attribute .close").click()
});

var table;
$(document).ready(function() {
    dataTable();
});

function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
	
	if($('#attribute_name').val().trim())    {
       filterData.name = $('#attribute_name').val();	    
    }	   
	if($('#status').val().trim())
    {
       filterData.status = $('#status').val();	  
    }
	
    postData.filter = filterData;
    table = $('#table').DataTable({
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "responsive": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "orderCellsTop": true,
        "destroy": true,
     //   "order":[],
        "pageLength": <?= $page_count ?>,
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "<?= base_url().$TYPE; ?>/attributes/index_ajax_attributes",
            "data": postData,
            "type": "POST",
            "dataType": 'json',
            complete: function () {
               $('[data-toggle="tooltip"]').tooltip();
            },
        },
        "language": {
           "paginate": {
           "next": '&raquo;', // or '→'
           "previous": '&laquo;' // or '←'
           }
        },
        "dom": '<"top"i>t<"bottom"flp><"clear">',
        "columnDefs": [ {
            "targets": -1,
            "data": null,
            "render": function ( data, type, row ) {
var actionTemplate = '<button title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-edit btn btn-primary" type="button" data-id="'+row.attribute_id+'"><i class="fa fa-pencil-square-o"></i></button>';
                if(row.master == 1){
                    actionTemplate = '';
                }
                return actionTemplate;
            },
            "defaultContent": ''
            },
         ],
        "columns": [
               {"data": "attribute_id"},
               {"data": "name"},
               {"data": "item"},
               {"data": "status"},
               {"data": "action"},
          ]
    } );
}

$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    //var url="<?php echo base_url().$TYPE; ?>/attributes/update/"+uid+"";
	var url='';
    url_new_tab(e,url);
} );

$(document).on('click','.btn-item', function(e){
    var uid=$(this).attr('data-id');
    var url="<?php echo base_url().$TYPE; ?>/attributes/index_attribute_item/"+uid+"";
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

$(document).on('click','.btn-primary', function(e){
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip();
});

$("#slug").focus(function() {
    var str = $("#name").val().trim();
    var res = str.toLowerCase().replace(/\s+/g, '-');
    $("#slug").val(res);
});

$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
    });
});

</script>
