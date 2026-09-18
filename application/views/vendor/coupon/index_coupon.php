<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="content-body">
    <div class="container-fluid">       
		<div class="row page-titles">                
			<?= $breadcrumbs ?>
		</div>   
        <div class="row">
            <div class="col-12">
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
				    <div class="card-header customHeader">
						<h4 class="card-title"><?= $page_lang->coupon; ?> List</h4>
						<div class="mt-2 transactionBtn">
							<a type="submit" class="btn btn-primary" href="<?= base_url().$TYPE; ?>/coupon/add"><i class="fa fa-plus" aria-hidden="true"></i></a>
							<a href="javascript:void(0)"data-toggle="modal" data-target="#myModal" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a>
						</div>
					</div>							
                    <div class="card-body">
                         <div class="table-responsive"> 
							<table id="table" class="display customTableProduct">
								<thead>
									<tr>
										<th><?= $page_lang->id; ?></th>
										<th><?= $page_lang->name; ?></th>
										<th><?= $page_lang->discount; ?></th>
										<th><?= $page_lang->discount_type; ?></th>
										<th><?= $page_lang->start_date; ?></th>
										<th><?= $page_lang->end_date; ?></th>
										<th><?= $page_lang->status; ?></th>
										<th width="35px;"><?= $page_lang->action; ?></th>
									</tr>
								</thead>
								<tbody>&nbsp;</tbody>
							</table>                                  
                        </div><!-- End table-responsive -->
                    </div>
                </div><!-- End card -->
            </div>
        </div>
    </div> 
	</div>	<!-- End content-body -->
		
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
								<div class="form-group col-md-6 mt-2">
									<label for="name"><?= $page_lang->name; ?></label>
									<input type="text" id="name" name="name" class="form-control validate">
								</div>
								<div class="col-md-6 col s4">
									<label for="Start Date"><?= $page_lang->start_date; ?></label>
									<input type="text" id="start_date" name="start_date" class="form-control validate">
								</div>
								<div class="form-group col-md-6 mt-2">
									<label for="Start Date"><?= $page_lang->end_date; ?></label>
									<input type="text" id="end_date" name="end_date" class="form-control validate">
								</div>
							   <div class="form-group col-md-6 mt-2">
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
								<div class="form-group col-md-6 mt-2">
									<button class="btn btn-primary reset-btn" type="button"><?= $page_lang->clear; ?></button>
									<button class="btn btn-primary btnSubmit" type="button"><?= $page_lang->submit; ?></button>
								</div>
							</div>
						</form>
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
            "url": "<?= base_url().$TYPE; ?>/coupon/index_ajax_coupon",
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
				var actionTemplate = '<a href="<?= base_url().$TYPE; ?>/coupon/update/'+row.coupon_id+'" title = "Edit" class="btn btn-primary btn-sm"><i class="fa fa-cog"></i></a>';
				return actionTemplate;
            },
            "defaultContent": ''
            },
         ],
        "columns": [
               {"data": "coupon_id"},
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
		$("#myModal .close").click()
		dataTable();
    });
});

$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="<?php echo base_url().$TYPE; ?>/coupon/update/"+uid+"";
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
