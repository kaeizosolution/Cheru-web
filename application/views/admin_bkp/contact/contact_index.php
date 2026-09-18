<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row"><div class="col-lg-6"><h3>Contact</h3> </div> <?= $breadcrumbs ?>  </div>
        </div>
    </div>
			<div class="container-fluid">
			<div class="row">
			<div class="col-sm-12">
			<div class="card">
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
				<div class="card-body">
					<div class="topAction-btn"><a href="javascript:void(0)" data-toggle="modal" data-target="#myModal" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a></div>
		
					<div class="dt-ext table-responsive">
						<div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
							<div class="row">
								<div class="col-sm-12">
									<table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
										<thead>
											<tr>											   
												<th>S.No.</th>
												<th>Full Name</th>
												<th>Email</th>
												<th>Message</th>
												<th>IP Address</th>
												<th>Date</th>											
											</tr>
										</thead>
										<tbody> </tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div></div></div></div> 

      
    
			
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
								<label for="supplier_name">Name</label>
                                <input type="text" id="fname" name="fname" class="form-control validate">
                            </div>
                            <div class="col-md-6 col s4">
								<label for="supplier_name">Email</label>
                                <input type="text" id="email" name="email" class="form-control validate">
                            </div>
							<div class="col-md-6 col s4">
								<label for="created">Created</label>
                                <input type="text" id="created" name="created" class="form-control validate">
                            </div>   
                        </div>
                        <div class="row">
                            <div class="input-field p-4">                               
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


$('#created').daterangepicker({
	autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,
    //"timePicker": true,
    //"timePicker24Hour": true,	
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#created').val(start.format('YYYY-MM-DD'));
});
$("#created").on('apply.daterangepicker', function(ev, Picker) {
    $('#created').val(Picker.startDate.format('YYYY-MM-DD'));
});
$('#created').on('cancel.daterangepicker', function(ev, picker) {
    $('#created').val('');
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
	   //alert(filterData.email);
	  
    }   
	if($('#email').val().trim())
    {
       filterData.email = $('#email').val();
	   //alert(filterData.email);
	  
    }   
    if($('#created').val().trim())
    {
       filterData.created = $('#created').val();
	   
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
            "url": "/<?= $TYPE; ?>/contact/index_ajax_post",
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
        "dom": 'Bfrtip',
        "columns": [
              
				{"data": "sno"},
               {"data": "fname"},
               {"data": "email"},
               {"data": "message"},
               {"data": "ip_address"},
               {"data": "created"},
          ]
    } );
}
$(document).ready(function(){
    $(".reset-btn").click(function(){
        $("#filter_data").trigger("reset");
    });
});
</script>
