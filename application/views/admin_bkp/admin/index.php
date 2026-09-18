<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"> <h3>Admin</h3> </div> <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                     <?php if ($this->session->flashdata('error')) { ?>
                     <div class="alert alert-danger"> <?= $this->session->flashdata('error')?>   </div>
                     <?php } ?>
                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success"> <?= $this->session->flashdata('success')?> </div>
                    <?php } ?>					
                    <div class="card-body">
					
					
					 <div class="topAction-btn">
					 
					 <div class="row">
							<div class="col-md-3">
								<div class="form-group">																									
									<select class="form-control btn-checkall" id="selectStatus" name="checkbox_all">
									<option value="">Status</option>   
									<option value="1">Active</option>                          
									<option value="0">Inactive</option>   
									</select>
								</div> 
							</div>  							
							<div class="col-md-1">							
								<div class="form-group">								
								<button class="btn waves-effect waves-light btn-large" type="button" onclick="checkAll()">Submit</button>
								</div>
							</div>							
						</div> 
					 
					 <a href="/<?= $TYPE?>/admin/add" class="btn btn-primary addNew" title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-plus"></i></a>
					 
					<a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a>
					 
					 </div>
					 
					 
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
													<th><input type="checkbox" id="selectall" onClick="toggle(this)" />All </th>
                                                    <th>Full Name</th>
                                                    <th>Email</th>
                                                    <th>Mobile</th>
                                                    <th>User Role</th>
                                                    <th>Date Added</th>                                                   
													<th>Status</th>
													<th>Action</th> 
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
                            <div class="col-md-6 p-4">
								<label for="fname">Name</label>
                                <input type="text" id="fname" name="fname" class="form-control">
                            </div>
							<div class="col-md-6 p-4">
								<label for="fname">Email</label>
                                <input type="text" id="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
								<label for="city">Date</label>
                                <input type="text" id="last_updated" name="last_updated" class="form-control validate">
                            </div>
                           <div class="col-md-6">
								<div class="form-group">
									<label>Status</label>									
									<select class="form-control" id="status" name="status" required="required">	 		<option value="" selected>status</option>   
									<option value="1">Active</option>                          
									<option value="0">Inactive</option>   
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

$('#last_updated').daterangepicker({
	autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,
    //"timePicker": true,
    //"timePicker24Hour": true,	
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#last_updated').val(start.format('YYYY-MM-DD')); // h:MM:ss
});
$("#last_updated").on('apply.daterangepicker', function(ev, Picker) {
    $('#last_updated').val(Picker.startDate.format('YYYY-MM-DD'));
});
$('#last_updated').on('cancel.daterangepicker', function(ev, picker) {
    $('#last_updated').val('');
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
	
	if($('#fname').val().trim())
    {
       filterData.fname = $('#fname').val();	 
    }
	if($('#email').val().trim())
    {
       filterData.email = $('#email').val();	 
    }
    if($('#last_updated').val().trim())
    {
       filterData.last_updated = $('#last_updated').val();
	   
    }
    if($('#status').val().trim())
    {
       filterData.status = $('#status').val();
	   //alert(filterData.status); 
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
		//"order":[],
        "pageLength": <?= $page_count ?>,
        //Load data for the table's content from an Ajax source
        "ajax": {
            "url": "/<?= $TYPE; ?>/admin/index_ajax_post",
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
        "dom": 'Bfrtip',
		"columnDefs": [ {
            "targets": -1,
            "data": null, 
            "render": function ( data, type, row ) {
var actionTemplate = '<button type="button" class="btn btn-primary btn-sm" data-toggle="popover" data-placement="top" data-html="true" data-content=\''+
'<button title="'+row.status+'" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-status" type="button" data-status="'+row.status+'" data-id="'+row.admin_id+'"><i class="fa fa fa-toggle-on"></i></button>'+
'<button type="button" title="Edit" data-toggle="tooltip" class="btn custom-popover-btn btn-edit" data-id="'+row.admin_id+'"><i class="fa fa-pencil-square-o"></i></button>'+
'<button type="button" title="Delete" data-toggle="tooltip" class="btn custom-popover-btn btn-delete" data-id="'+row.admin_id+'"><i class="fa fa-trash"></i></button>'+
'<button type="button" title="View" data-toggle="tooltip" class="btn custom-popover-btn btn-view" data-id="'+row.admin_id+'"><i class="fa fa-eye"></i></button>'+ 
'\''+
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
				{"data": "admin_id"},
				{"data": "fname"},
				{"data": "email"},
				{"data": "mobile"},
				{"data": "superadmin"}, 
			    {"data": "last_updated"},
				{"data": "status"},
				{"data": ""},
			   
          ]
    } );
}


$(document).on('click','.btn-primary', function(e){
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip();
});

//update query
$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
	//alert(uid);
    var url="/<?php echo $TYPE ?>/admin/update/"+uid+"";
    url_new_tab(e,url);
	
} );

function url_new_tab(e,url)
{
	//alert(url);
   if(e.ctrlKey){
        window.open(url); 
    }else{
        $(location).attr('href', url);
    }
}

//delete query
$(document).on('click','.btn-delete', function(e){
    var uid=$(this).attr('data-id');  
	//alert(uid);
	delete_post(uid);
} );

function delete_post(id)
    {
        var postData = {};
		var csrf_name = "<?= $csrf->name; ?>";
		var csrf_value = "<?= $csrf->hash; ?>";
		postData[csrf_name] =  csrf_value;
        postData.id = id;
		
		
        $.ajax({
                url         :   '<?php echo base_url();?>admin/admin/del',
                type        :   'post',
			    enctype	    :   'multipart/form-data',
                dataType    :   "json",  
                data        :   postData,
                success     :   function(data){
                    if(data.success)
                    {
					
						if(data.MSG)
						   {
							swal('Session expired.');
						   }
		 
                        swal('Delete Successfully.'); 
                        location.reload(true);
				   } 
			   
				}
		});
    }
//Change status	
$(document).on('click','.btn-status', function(e){
    var uid=$(this).attr('data-id'); 
	var status=$(this).attr('data-status');  	
	//alert(status);
	status_change(uid,status);
} );

function status_change(id,status)
    {
        var postData = {};
		var csrf_name = "<?= $csrf->name; ?>";
		var csrf_value = "<?= $csrf->hash; ?>";
		postData[csrf_name] =  csrf_value;
        postData.id = id;
		postData.status = status;
		
		
        $.ajax({
                url         :   '<?php echo base_url();?>admin/admin/status_change',
                type        :   'post',
			    enctype	    :   'multipart/form-data',
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

//redirect to profile view page
$(document).on('click','.btn-view', function(e){
    var uid=$(this).attr('data-id');  
	var base_url = '<?php base_url() ?>';
	window.location.href = base_url+"/admin/admin/view/"+uid;	
	//view_order(uid);
} );

$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
    });
});

/*******All status change by select status*********/
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
                url         :   '<?php echo base_url();?>admin/admin/checkall_status_change',
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
		 
                        swal('Supplier status changed.'); 
                        location.reload(true);
				   } 
			   
				}
		});
    }
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}


</script>
