<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3><?= $page_lang->coupon; ?></h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                     <?php if ($this->session->flashdata('error')) { ?>
                     <div class="alert alert-danger">
                         <?= $this->session->flashdata('error')?>
                         <?php $this->session->unset_userdata('error');?>
                     </div>
                     <?php } ?>
                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('success')?>
                        <?php $this->session->unset_userdata('success');?>
                    </div>
                    <?php } ?>
                    <div class="card-body">
                        <div class="topAction-btn"><a href="/<?= $TYPE?>/product/save" class="btn btn-primary addNew" title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-plus"></i></a>
						<a href="javascript:void(0)"data-toggle="modal" data-target="#myModal" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a></div>
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                   <?php /* <th><?= $page_lang->id; ?></th>*/ ?>
                                                    <th><?= $page_lang->name; ?></th>
                                                    <th><?= $page_lang->discount; ?></th>
                                                    <th><?= $page_lang->discount_type; ?></th>
                                                    <th><?= $page_lang->start_date; ?></th>
                                                    <th><?= $page_lang->end_date; ?></th>
                                                    <th><?= $page_lang->status; ?></th>
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
<div id="myModal" class="modal fade customModel" role="dialog">
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
								<label for="name"><?= $page_lang->name; ?></label>
                                <input type="text" id="name" name="name" class="form-control validate">
                            </div>
                            <div class="col-md-6 col s4">
								<label for="Start Date"><?= $page_lang->start_date; ?></label>
                                <input type="text" id="start_date" name="start_date" class="form-control validate">
                            </div>
							<div class="col-md-6 col s4 p-4">
								<label for="Start Date"><?= $page_lang->end_date; ?></label>
                                <input type="text" id="end_date" name="end_date" class="form-control validate">
                            </div>
                           <div class="col-md-6 p-4">
								<div class="form-group">
									<label><?= $page_lang->status; ?></label>									
									<select class="form-control" id="status" name="status" required="required">	 		<option value="" selected><?= $page_lang->status; ?></option>   
									<option value="1">Active</option>                          
									<option value="0">Inactive</option>   
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

$('#start_date').daterangepicker({
	autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,    
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#start_date').val(start.format('YYYY-MM-DD'));
});
$("#start_date").on('apply.daterangepicker', function(ev, Picker) {
    $('#start_date').val(Picker.startDate.format('YYYY-MM-DD'));
});
$('#start_date').on('cancel.daterangepicker', function(ev, picker) {
    $('#start_date').val('');
});

$('#end_date').daterangepicker({
	autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,    
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#end_date').val(start.format('YYYY-MM-DD'));
});
$("#end_date").on('apply.daterangepicker', function(ev, Picker) {
    $('#end_date').val(Picker.startDate.format('YYYY-MM-DD'));
});
$('#end_date').on('cancel.daterangepicker', function(ev, picker) {
    $('#end_date').val('');
});

$("#filter_data").submit(function(){
	event.preventDefault();
	dataTable();
    $("#myModal .close").click()
	
});

$('.btnSubmit').on('click', function (e){
    dataTable();
    $("#myModal .close").click()
});


var table;
$(document).ready(function() {
    dataTable();
});

function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
	if($('#name').val().trim())
    {
       filterData.name = $('#name').val();
    }
    if($('#start_date').val().trim())
    {
       filterData.start_date = $('#start_date').val();
    }
	 if($('#end_date').val().trim())
    {
       filterData.end_date = $('#end_date').val();
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
            "url": "/<?= $TYPE; ?>/coupon/index_ajax_coupon",
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

var actionTemplate = '<button type="button" class="btn btn-primary btn-sm" data-toggle="popover" data-placement="top" data-html="true" data-content=\''+
'<button title="'+row.status+'" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-status" type="button" data-status="'+row.status+'" data-id="'+row.coupon_id+'"><i class="fa fa fa-toggle-on"></i></button>'+
'<button type="button" title="Edit" data-toggle="tooltip" class="btn custom-popover-btn btn-edit" data-id="'+row.coupon_id+'"><i class="fa fa-pencil-square-o"></i></button>'+
'\''+'><i class="fa fa-cog"></i></button>';


                return actionTemplate;
            },
            "defaultContent": ''
            },
         ],
        "columns": [
               //{"data": "coupon_id"},
               {"data": "name"},
               {"data": "discount"},
               {"data": "type"},
               {"data": "start_date"},
               {"data": "end_date"},
               {"data": "status"},
               {"data": "action"},
          ]
    } );
}

$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
    });
});

//update query
$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    //alert(uid);
    var url="/<?php echo $TYPE ?>/coupon/update/"+uid+"";
    url_new_tab(e,url);
    
} );


$(document).ready(function() {

    // Handle click on "Ststus" button
    $(document).on('click', '.btn-status', function (e) {
        var id = $(this).attr('data-id');
        var status = $(this).attr('data-status');
        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.id = id;
        postData.status = status;
        swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                 $.ajax( {
                     url:'/<?= $TYPE; ?>/coupon/status_change/',
                     type:'post',
                     dataType:"json",  
                     data: postData,
                     success:function(data) {
                         table.ajax.reload();
                         swal({
                              title: "Status!",
                              text: "Status has been changed.",
                              timer: 2000,
                              showConfirmButton: false
                         });
                     }
                 });
            }
        });
    }); 
        
});


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
