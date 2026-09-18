        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
				<div class="row page-titles">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
						<li class="breadcrumb-item active"><a href="shipping.html">Shipping</a></li>
					</ol>
                </div>
                <!-- row -->
                <div class="row">
					<div class="col-12">
                        <div class="card">
                            <div class="card-header customHeader">
                                <h4 class="card-title">Shipping List</h4>
								<div class="col mt-2 transactionBtn">
									<a type="submit" class="btn btn-primary" href="/<?= $TYPE?>/shipping/add"><i class="fa fa-plus" aria-hidden="true"></i></a>
									<a type="submit" class="btn btn-primary"><i class="fa fa-filter" aria-hidden="true"></i></a>
								</div>
								
                            </div>
                            <div class="card-body">
								<div class="basic-form">
									<form>
										<div class="mb-3 row">
											<div class="col-sm-3">
												
											</div>
										</div>
									</form>
								</div>
                                <div class="table-responsive">
                                    <table id="shipping_table" class="display customTableProduct" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Rate</th>
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
        <!--**********************************
            Content body end
        ***********************************-->

<script>
var table;
$(document).ready(function() {  
   dataTable();
    
});

function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
	
	
	
    postData.filter = filterData;
    table = $('#shipping_table').DataTable({
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
            "url": "/<?= $TYPE; ?>/shipping/index_ajax_shipping",
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
var actionTemplate = '<button  alt="Edit" title="Edit" class="btn-edit btn btn-primary" type="button" data-original-title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" data-id="'+row.shipping_id+'"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>';
	return actionTemplate;

                
            },
            "defaultContent": ''
            },
			
         ],
        "columns": [
			{"data": "shipping_id"},
			{"data": "name"},
			{"data": "rate"},
			{"data": "status"},
			{"data": "action"},

          ]
    } );
}

$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
	var url="/<?php echo $TYPE ?>/shipping/update/"+uid+"";
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
</script>
