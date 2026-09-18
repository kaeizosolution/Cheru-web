<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"> <h3><?= $page_lang->categories; ?></h3></div> <?= $breadcrumbs ?>
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
                        <div class="topAction-btn"><a href="/<?= $TYPE?>/categories/add" class="btn btn-primary addNew" title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-plus"></i> </a>

						<a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a>
						
						</div>
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                         <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead> 
                                                <tr>                                                 
                                                    <th><?= $page_lang->category_name; ?></th>
													<th><?= $page_lang->slug; ?></th>	
													<th><?= $page_lang->status; ?></th>
													<th><?= $page_lang->action; ?></th>                                                   
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
								<label for="supplier_name"><?= $page_lang->category_name; ?></label>
                                <input type="text" id="name" name="name" class="form-control validate">
                            </div>
                           
                           <div class="col-md-6">
								<div class="form-group">
									<label><?= $page_lang->status; ?></label>									
									<select class="form-control" id="status" name="status">	
									<option value="" selected><?= $page_lang->status; ?></option>   
									<option value="1"><?= $page_lang->active; ?></option>                          
									<option value="0"><?= $page_lang->inactive; ?></option>   
									</select>
								</div>  
							</div>  
							
                        </div>
                        <div class="row">
                            <div class="input-field col s12 a-r">                              
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
            "url": "/<?= $TYPE; ?>/Categories/index_ajax_cat",
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
'<button title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-edit" type="button" data-id="'+row.id+'"><i class="fa fa-pencil-square-o"></i></button>\''+
'><i class="fa fa-cog"></i></button>';

                return actionTemplate;
            },
            "defaultContent": ''
            },
         ],
        "columns": [
               {"data": "name"}, 
			   {"data": "slug"}, 
			   {"data": "status"},			   
               {"data": "action"},
          ]
    } );
}

$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/Categories/update/"+uid+"";
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

$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
    });
});

</script>
