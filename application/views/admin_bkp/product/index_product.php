<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"><h3>Products</h3></div><?= $breadcrumbs ?>
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
                        <div class="topAction-btn"><a href="/<?= $TYPE?>/product/add" class="btn btn-primary addNew" title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-plus"></i></a>
						
						<a href="javascript:void(0)" data-toggle="modal" data-target="#productFilter" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a>
						
						</div>
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Product ID</th>
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th width="100px;">Date</th>
                                                    <th width="35px;">Action</th>
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
								<label for="supplier_name">Title</label>
                                <input type="text" id="product_name" name="product_name" class="form-control">
                            </div>
							 <div class="col-md-6 col s4">
								<label for="city">Date</label>
                                <input type="text" id="date" name="date" class="form-control">
                            </div>
                            <div class="col-md-6 p-4">
								<label for="city">Author</label>
                                <input type="text" id="author" name="author" class="form-control">
                            </div>
                           <div class="col-md-6 p-4">
								<div class="form-group">
									<label>Status</label>									
									<select class="form-control" id="enabled" name="enabled" required="required">	 		<option value="" selected>status</option>   
									<option value="1">Active</option>                          
									<option value="0">Disable</option>   
									</select>
								</div>  
							</div>  
							
                        </div>
                        <div class="row">
                            <div class="input-field col s12 a-r">
                                <button class="btn waves-effect waves-light btn-large reset-btn" type="button">Clear</button>
                                <button class="btn waves-effect waves-light btn-large btnSubmit" type="button">Submit</button>
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
</script>
