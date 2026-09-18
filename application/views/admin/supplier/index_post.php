<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row"><div class="col-lg-6"> <h3> <?= $page_lang->supplier; ?></h3> </div>  <?= $breadcrumbs ?> </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">    
                    <div class="card-body">
                        <div class="topAction-btn">
						
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
						<button onclick="window.location.href = '<?php echo base_url();?>admin/supplier/export_csv';" type="button" class="btn btn-info"><i class="fa fa-cloud-download"></i><i class="" aria-hidden="true"></i> Export(CSV)</button>
						
						<a href="/<?= $TYPE?>/supplier/add" class="btn btn-primary addNew" title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-plus"></i></a>
						
						<a href="javascript:void(0)" data-toggle="modal" data-target="#supplier" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a>
						
						</div>
						
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12"><form action="/" id="check_all_table" method="post">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="selectall" onClick="toggle(this)" /> </th>
													<th><?= $page_lang->name;?></th>
                                                    <th><?= $page_lang->email;?></th>
                                                    <th><?= $page_lang->mobile;?></th>
                                                    <th><?= $page_lang->address;?></th>
													<th><?= $page_lang->city;?></th>
													<th><?= $page_lang->status;?></th>
													<th><?= $page_lang->created;?></th>
                                                    <th width="35px;"><?= $page_lang->action;?></th>
                                                </tr>
                                            </thead>
                                            <tbody> </tbody>
                                        </table></form>
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
<div id="supplier" class="modal fade customModel" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>                
            </div>
            <div class="modal-body">
                <div class="col-sm-12 sp-top-30">
                    <form id="filter_data_filter" method="post">
                        <div class="row">
                            <div class="col-md-6 col s4">
								<label for="supplier_name"><?= $page_lang->supplier_name; ?></label>
                                <input type="text" id="cname" name="cname" class="form-control">
                            </div>
							 <div class="col-md-6 col s4">
								<label for="city"><?= $page_lang->email; ?></label>
                                <input type="text" id="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6 p-4">
								<label for="city"><?= $page_lang->city; ?></label>
                                <input type="text" id="city" name="city" class="form-control">
                            </div>
                           <div class="col-md-6 p-4">
								<div class="form-group">
									<label><?= $page_lang->status; ?></label>									
									<select class="form-control" id="enabled" name="enabled" required="required">
                        	 		<option value="" selected><?= $page_lang->status; ?></option>   
									<option value="1"><?= $page_lang->active; ?></option>                          
									<option value="0"><?= $page_lang->inactive; ?></option>   
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


$("#filter_data_filter").submit(function(){
	event.preventDefault();
	dataTable();
    $("#supplier .close").click()
	
});
$('.btnSubmit').on('click', function (e){
    dataTable();
    $("#supplier .close").click()
});


var table;
$(document).ready(function() {
	
    dataTable();
	
});

function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
	if($('#cname').val().trim())
    {
       filterData.cname = $('#cname').val();
    }
	if($('#email').val().trim())
	{
       filterData.email = $('#email').val();
    }
    if($('#city').val().trim())
    {
       filterData.city = $('#city').val();
    }
    if($('#enabled').val().trim())
    {
       filterData.status = $('#enabled').val();
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
            "url": "/<?= $TYPE; ?>/supplier/index_ajax_post",
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
				
				 //return '<input id="defaultUnchecked-' + $('<div/>').text(data).html() + '" class="custom-control-input" type="checkbox" name="campaing_name[]" value="' + $('<div/>').text(data).html() + '"><label class="custom-control-label" for="defaultUnchecked-'+ $('<div/>').text(data).html()+'">'+ $('<div/>').text(data).html() +'</label>';
				 
				var actionTemplate = '<button type="button" class="btn btn-primary btn-sm" data-toggle="popover" data-placement="top" data-html="true" data-content=\''+
				'<button title="'+row.status+'" data-toggle="tooltip" data-placement="top" datai-animation="false" data-html="true" class="btn custom-popover-btn btn-status" type="button" data-status="'+row.status+'" data-id="'+row.supplier_id+'"><i class="fa fa fa-toggle-on"></i></button>'+
				'<button type="button" title="Edit" data-toggle="tooltip" class="btn custom-popover-btn btn-edit" data-id="'+row.supplier_id+'"><i class="fa fa-pencil-square-o"></i></button>'+
				'<button type="button" title="View" data-toggle="tooltip" class="btn custom-popover-btn btn-view" data-id="'+row.supplier_id+'"><i class="fa fa-eye"></i></button>'+ 
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
				{"data": "supplier_id"},
				{"data": "cname"},
			    {"data": "email"},
				{"data": "mobile"},
                {"data": "address"},                           
			    {"data": "city"},						
			    {"data": "status"},
				{"data": "date_added"},		
                {"data": "action"},
          ]
    } );
}

//update query
$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/supplier/update/"+uid+"";
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

//delete query
$(document).on('click','.btn-delete', function(e){
    var uid=$(this).attr('data-id');  
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
                url         :   '<?php echo base_url();?>admin/supplier/del',
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
		 
                        swal('Supplier Delete Successfully.'); 
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
                url         :   '<?php echo base_url();?>admin/supplier/status_change',
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
		 
                        swal('Supplier status changed.'); 
                        location.reload(true);
				   } 
			   
				}
		});
    }

//redirect to profile view page
$(document).on('click','.btn-view', function(e){
    var uid=$(this).attr('data-id');  
	var base_url = '<?php base_url() ?>';
	window.location.href = base_url+"/admin/supplier/view/"+uid;	
	//view_order(uid);
} );

$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data_filter").trigger("reset");
    });
});

function toggle(source) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
    }
}


/*
function showData() {
	var theSelect = demoForm.demoSelect;
	alert('fff');
	//var firstP = document.getElementById('firstP');
	//var secondP = document.getElementById('secondP');
	//var thirdP = document.getElementById('thirdP');
	//firstP.innerHTML = ('This option\'s index number is: ' + theSelect.selectedIndex + ' (Javascript index numbers start at 0)');
	//secondP.innerHTML = ('Its value is: ' + theSelect[theSelect.selectedIndex].value);
	//thirdP.innerHTML = ('Its text is: ' + theSelect[theSelect.selectedIndex].text);
}


//Change status	
$(document).on('submit','.btnSubmit', function(e){
	alert('dd');
    var uid=$(this).attr('data-id'); 
	var status=$(this).attr('data-status');  	
	//alert(status);
	checkall_status_change(uid,status);
} );
*/

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
                url         :   '<?php echo base_url();?>admin/supplier/checkall_status_change',
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

	
</script>
