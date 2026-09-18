<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"><h3><?= $page_lang->products; ?></h3></div><?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                     <?php if ($this->session->flashdata('error')) { ?> <div class="alert alert-danger">  <?= $this->session->flashdata('error')?></div> <?php } ?>

                    <?php if($this->session->flashdata('success')){?> <div class="alert alert-success">
                    <?= $this->session->flashdata('success')?></div> <?php } ?>
                    <div class="card-body">
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">																
									<select class="form-control btn-checkall" id="selectStatus" name="checkbox_all">
									<option value=""><?= $page_lang->status; ?></option>   
									<option value="1"><?= $page_lang->active; ?></option>                          
									<option value="0"><?= $page_lang->inactive; ?></option>   
									</select>
								</div> 
							</div>  							
							<div class="col-md-1">							
								<div class="form-group">								
									<button class="btn waves-effect waves-light btn-large" type="button" onclick="checkAll()"><?= $page_lang->submit; ?></button>
								</div>
							</div>							
						</div>
						
                        <div class="topAction-btn">
						
						

						<a href="/<?= $TYPE?>/product/add" class="btn btn-primary addNew" title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-plus"></i></a>
						
						<a href="javascript:void(0)" data-toggle="modal" data-target="#productFilter" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a>
						
						</div>
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
												
                                                   <th><input type="checkbox" id="selectall" onClick="toggle(this)" /><?= $page_lang->all; ?></th>
                                                    <th><?= $page_lang->title; ?></th>
                                                    <th><?= $page_lang->type; ?></th>
                                                    <th><?= $page_lang->status; ?></th>
                                                    <th width="100px;"><?= $page_lang->date; ?></th>
                                                    <th width="35px;"><?= $page_lang->action; ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Modal -->
<div id="productFilter" class="modal fade customModel" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <div class="col-sm-12 sp-top-30">
                    <form id="filter_product" method="post">
                        <div class="row">
                            <div class="col-md-6 col s4">
								<label for="supplier_name"><?= $page_lang->title; ?></label>
                                <input type="text" id="product_name" name="product_name" class="form-control">
                            </div>
							 <div class="col-md-6 col s4">
								<label for="city"><?= $page_lang->date; ?></label>
                                <input type="text" id="date" name="date" class="form-control">
                            </div>
                            <div class="col-md-6 p-4">
								<label for="city"><?= $page_lang->author; ?></label>
                                <input type="text" id="author" name="author" class="form-control">
                            </div>
                           <div class="col-md-6 p-4">
								<div class="form-group">
									<label><?= $page_lang->status; ?></label>									
									<select class="form-control" id="enabled" name="enabled" required="required">	 		<option value="" selected><?= $page_lang->status; ?></option>   
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
	
	
</div>
<script>


$("#filter_product").submit(function(){
	event.preventDefault();
	dataTable();
    $("#productFilter .close").click()
	
});
$('.btnSubmit').on('click', function (e){
    dataTable();
    $("#productFilter .close").click()
});



var table;
$(document).ready(function() {
    dataTable();
});

function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
	
	if($('#product_name').val().trim())
    {
       filterData.post_title = $('#product_name').val();
    }
	if($('#date').val().trim())
	{
       filterData.modified = $('#date').val();
    }
    if($('#author').val().trim())
    {
       filterData.user_id = $('#author').val();
    }
    if($('#enabled').val().trim())
    {
       filterData.enabled = $('#enabled').val();
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
            "url": "/<?= $TYPE; ?>/product/index_ajax_post",
            "data": postData,
            "type": "POST",
            "dataType": 'json',
            complete: function () {
               $('[data-toggle="tooltip"]').tooltip();
               $('[data-toggle="popover"]').popover();
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
/*var actionTemplate = '<button type="button" class="btn btn-primary btn-sm" data-toggle="popover" data-placement="top" data-html="true" data-content=\''+
'<button title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-edit" type="button" data-id="'+row.product_id+'"><i class="fa fa-pencil-square-o"></i></button>\''+
'><i class="fa fa-cog"></i></button>';*/

                var toggle_class = '';
                var toggle_title = '';
                if(row.status == '1'){
                    toggle_class = 'fa fa-toggle-off';
                    toggle_title = 'Disable';
                }else{
                    toggle_class = 'fa fa-toggle-on';
                    toggle_title = 'Enable';
                }
var actionTemplate = '<button type="button" class="btn btn-primary btn-sm" data-toggle="popover" data-placement="top" data-html="true" data-content=\''+
'<button title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-edit" type="button" data-id="'+row.product_id+'"><i class="fa fa-pencil-square-o"></i></button>'+
'<button title="'+toggle_title+'" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete" type="button" data-status="'+row.status+'" data-id="'+row.product_id+'"><i class="'+toggle_class+'"></i></button>\''+
'><i class="fa fa-cog"></i></button>';

                return actionTemplate;
            },
            "defaultContent": ''
            },
			{
                'targets': 0,
				'searchable':false,
				'orderable':false,
				'className': 'dt-body-center',
				'render': function (data, type, full, meta){
				return '<input type="checkbox" name="id[]" value="' 
                + $('<div/>').text(data).html() + '">';
				}
            }
         ],
        "columns": [
               {"data": "product_id"},
               {"data": "post_title"},
               {"data": "type"},
               {"data": "status_str"},
               {"data": "date"},
               {"data": "action"},
          ]
    } );
}

$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_product").trigger("reset");
    });
});
$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/product/update/"+uid+"";
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
</script>

<script>
$(document).on('click','.btn-delete', function(e){
    var uid=$(this).attr('data-id');  
    var status=$(this).attr('data-status');  
	delete_post(uid,status);
} );

function delete_post(id,status)
{
    var postData = {};
	var csrf_name = "<?= $csrf->name; ?>";
	var csrf_value = "<?= $csrf->hash; ?>";
	postData[csrf_name] =  csrf_value;
    postData.product_id = id;
    postData.status = status;
	
    $.ajax({
            url         :   '<?php echo base_url();?>admin/product/ajax_update_product_status',
            type        :   'post',
            dataType    :   "json",  
            data        :   postData,
            success     :   function(data){
                if(data.success)
                {	
                    swal('Status changed Successfully.'); 
                    location.reload(true);
			    }
			},
            error: function (error) {
                //  $("#loading-image").hide();
            }
	});
}

$('#date').daterangepicker({
	autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,	
    //"timePicker": true,
    //"timePicker24Hour": true,	
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#date').val(start.format('YYYY-MM-DD'));
});

$("#date").on('apply.daterangepicker', function(ev, Picker) {
    $('#date').val(Picker.startDate.format('YYYY-MM-DD'));
});

$('#date').on('cancel.daterangepicker', function(ev, picker) {
    $('#date').val('');
});


function toggle(source) {
	var checkboxes = document.querySelectorAll('input[type="checkbox"]');
	for (var i = 0; i < checkboxes.length; i++) {
		if (checkboxes[i] != source)
			checkboxes[i].checked = source.checked;
	}
}


function checkAll() { 
	var st = document.getElementById("selectStatus");
	var status = st.options[st.selectedIndex].value;
	//alert(status);
	var supplierId = [];
	$(':checkbox:checked').each(function(i){
	  supplierId.push($(this).val());
	});
	
	if(status != '' && supplierId.length > 0){
	   checkall_status_change(supplierId,status);
       dataTable();
    }
	
}

function checkall_status_change(id,status)
    {
		//alert(id);
	    var postData = {};
		var csrf_name = "<?= $csrf->name; ?>";
		var csrf_value = "<?= $csrf->hash; ?>";
		postData[csrf_name] =  csrf_value;
        postData.id = JSON.stringify(id);
		postData.status = status;
		
		
        $.ajax({
                url         :   '<?php echo base_url();?>admin/product/checkall_status_change',
                type        :   'post',			    
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if(data.success)
                    {
					
						if(data.MSG)
						   {
							swal('Session expired.');
						   }
		 
                        swal('Status changed.'); 
                        location.reload(true);
				   } 
			   
				}
		});
    }

</script>
