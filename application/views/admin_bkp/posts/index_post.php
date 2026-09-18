<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"> <h3>Posts</h3> </div><?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                     <?php if ($this->session->flashdata('error')) { ?>
                     <div class="alert alert-danger">  <?= $this->session->flashdata('error')?>  </div>
                     <?php } ?>
                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success">  <?= $this->session->flashdata('success')?> </div>
                    <?php } ?>
                    <div class="card-body">
                        <div class="topAction-btn">
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">	
								
								<form method="POST" name="myform" action="/">								
									<select class="form-control btn-checkall" id="selectStatus" name="checkbox_all">
									<option value="" selected>status</option>   
									<option value="1">Active</option>                          
									<option value="0">Inactive</option>   
									</select>
									
								</form>
									
								</div> 
							</div>  							
							<div class="col-md-1">							
								<div class="form-group">								
									<button class="btn waves-effect waves-light btn-large btnSubmit" type="button" onclick="checkAll()">Submit</button>
								</div>
							</div>							
						</div> 
						<a href="/<?= $TYPE?>/posts/add" class="btn btn-primary addNew" title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-plus"></i></a>
<a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a></div>
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
													<th><input type="checkbox" id="selectall" onClick="toggle(this)" />All </th>
                                                    <th>Title</th>
                                                    <th>Author</th>
                                                    <th>-</th>
                                                    <th>Date</th>
                                                    <td>Status</td>
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
                                <label for="post title">Title</label>
                                <input type="text" id="post_title" name="post_title" class="form-control validate">
                            </div>
                            <div class="col-md-6 col s4">
                                <label for="city">Date</label>
                                <input type="text" id="modified" name="modified" class="form-control validate">
                            </div>
                           <div class="col-md-6 p-4">
                                <div class="form-group">
                                    <label>Status</label>                                   
                                    <select class="form-control" id="enabled" name="enabled" required="required">           <option value="" selected>status</option>   
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
<!------ ---END MODEL-------->
</div>
<script>

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
	
	if($('#post_title').val().trim()){
       filterData.post_title = $('#post_title').val();
    }	
	if($('#enabled').val().trim()){
       filterData.enabled = $('#enabled').val();
    }
	
	//console.log($('#modified_date').val());
    if($('#modified').val().trim()){
       filterData.modified = $('#modified').val(); 
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
            "url": "/<?= $TYPE; ?>/posts/index_ajax_post",
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
'<button title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-edit" type="button" data-id="'+row.post_id+'"><i class="fa fa-pencil-square-o"></i></button>'+
'<button title="Delete" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete" type="button" data-id="'+row.post_id+'"><i class="fa fa-trash"></i></button>\''+
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
				{"data": "post_id"},
               {"data": "post_title"},
               {"data": "user_id"},
               {"data": "comment"},
               {"data": "date"},
               {"data": "enabled"},
               {"data": "action"},
          ]
    } );
}

$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/posts/update/"+uid+"";
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
                url         :   '<?php echo base_url();?>admin/Posts/del',
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
		 
                        swal('User Delete Successfully.'); 
                        location.reload(true);
				   } 
			   
				}
		});
    }
	
$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
    });
});

function checkAll() { 
	var st = document.getElementById("selectStatus");
	var status = st.options[st.selectedIndex].value;
	//alert(status);
	var supplierId = [];
	$(':checkbox:checked').each(function(i){
	  supplierId.push($(this).val());
	});
	
	checkall_status_change(supplierId,status);
	
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
                url         :   '<?php echo base_url();?>admin/posts/checkall_status_change',
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
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}


</script>
