<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<!--**********************************
Content body start
***********************************-->
<div class="content-body">
	<!-- row -->
	<div class="container-fluid">
		<div class="mb-sm-4 d-flex flex-wrap align-items-center text-head">
		<h2 class="font-w600 mb-2 me-auto">Finance</h2></div>
		<div class="row">
			<div class="col-md-4 col-sm-6">
				<div class="card">
					<div class="card-body d-flex">
						<div class="icon me-3"><div class="iconBase lightOrange"><i class="fa fa-money" aria-hidden="true"></i></div></div>
						<div> 
							<h2 class="invoice-num"><?=$order_counts['total_items']?></h2>
							<p class="mb-0 invoice-num1">
							<svg width="21" height="14" viewbox="0 0 21 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1 13C1.91797 11.9157 4.89728 8.72772 6.5 7L12.5 10L19.5 1" stroke="#13B440" stroke-width="2" stroke-linecap="round"></path></svg>
							<span class="text-success me-1">Total Items</span></p>
							

						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-4 col-sm-6">
				<div class="card">
					<div class="card-body d-flex">
						<div class="icon me-3"><div class="iconBase darkOrange"><i class="fa fa-money" aria-hidden="true"></i></div></div>
						<div>
							<h2 class="invoice-num"><span>$</span> <?=$order_counts['amount']?></h2>
							<p class="mb-0">
								<svg width="21" height="14" viewbox="0 0 21 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1 13C1.91797 11.9157 4.89728 8.72772 6.5 7L12.5 10L19.5 1" stroke="#13B440" stroke-width="2" stroke-linecap="round"></path>
								</svg>
								<span class="text-success me-1">Total Income</span>
							</p>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-4 col-sm-6">
				<div class="card">
					<div class="card-body d-flex">
						<div class="icon me-3">
							<div class="iconBase lightBlue"><i class="fa fa-money" aria-hidden="true"></i></div>
						</div>
						<div>
							<h2 class="invoice-num"><?=$order_counts['delivered']?></h2>
							<p class="mb-0">
								<svg width="21" height="14" viewbox="0 0 21 14" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M1 13C1.91797 11.9157 4.89728 8.72772 6.5 7L12.5 10L19.5 1" stroke="#13B440" stroke-width="2" stroke-linecap="round"></path>
								</svg>
								<span class="text-success me-1">Total Deliveries</span>
							</p>
							

						</div>
					</div>
				</div>
			</div>
			
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-body">
						
						<div class="mb-4">
							<h4 class="card-title">Filter</h4>								
						</div>
						
						<div class="row">
							<form id="data_filter" method="post">
									<div class="row">                           
										<div class="col-md-6 col s4">
											<label for="Date From">Date From</label>
											<input type="text" id="date_created" name="date_created" class="form-control" placeholder="YYYY-MM-DD" required=""/>
										</div>
										<div class="col-md-6 col s4">
											<label for="Date To">Date To</label>
											<input type="text" id="last_updated" name="last_updated" class="form-control" placeholder="YYYY-MM-DD" required=""/>
										</div>                           
									</div>
									<div class="row">
										<div class="mt-2">                              
											<button class="btn btn-primary btnSubmit" type="button"><?= $page_lang->submit; ?>
										</div>
									</div>						
							</form>
						</div>
							
						<!--<div class="row" style="display:none;">
							<div class="mb-3 form-group col-md-6">
								<label class="form-label">Date From</label>
								<div class="col-sm-12">
									<input class="form-control input-daterange-datepicker" type="text" name="daterange" value="01/10/2021 - 01/31/2021">
								</div>
							</div>
							<div class="mb-3 form-group col-md-6">
								<label class="form-label">Date To</label>
								<div class="col-sm-12">
									<input class="form-control input-daterange-datepicker" type="text" name="daterange" value="01/10/2021 - 01/31/2021">
								</div>
							</div>
							
						</div>-->
						
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<div class="card customCard">
					<div class="card-header">Order List</div>					
					<div class="card-body">                        
                        <div class="table-responsive">
							<table id="table" class="display customTableProduct">
								<thead>
									<tr>									   
										<th><?= $page_lang->order_uid; ?></th>  
										<th>Created Date</th>										
										<th>Method</th>		
										<th>Total Price</th>
										<th>Status</th>
										<th><?= $page_lang->view; ?></th>										
									</tr>
								</thead>
								<tbody>&nbsp;</tbody>
							</table>                                   
                        </div>
                    </div>					
				</div>
			</div>			
		</div>
		
	</div>
</div>


<script>

$("#data_filter").submit(function(){
	event.preventDefault();
	dataTable();
    $("#order_filter .close").click()
	
});
$('.btnSubmit').on('click', function (e){
    dataTable();
    $("#order_filter .close").click()
});


var table;
$(document).ready(function() {
    dataTable();
});


function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
	postData.vendor_id = "<?= $vendor_id; ?>";
	    
    if($('#date_created').val().trim())
    {
       filterData.date_created_range = $('#date_created').val();	   
    }
	
	if($('#last_updated').val().trim())
    {
       filterData.date_created_range += '::'+$('#last_updated').val();	  
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
            "url": "/<?= $TYPE; ?>/order/ajax_get_finance_order",
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
        "dom": '<"top"i>t<"bottom"flp><"clear">',
        "columnDefs": [ {
            "targets": -1,
            "data": null,         
            "defaultContent": ''
            },
			{
                'targets': 0,
				'searchable':false,
				'orderable':false,
				'className': 'dt-body-center',			
            }
         ],
        "columns": [				
				{"data": "order_uid_view"}, 
				{"data": "order_date"},						
				{"data": "payment_mode"},
				{"data": "total"},	
				{"data": "status"},	
				{"data": "order_id",  render: function ( data, type, row ) {
                  return '<a href="/<?= $TYPE; ?>/order/finance_detail/' + row.order_uid + '" class="cancelled_order" target="_blank" data-id="' + row.order_id + '"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                } },              
          ]
    } );
}

	// set default dates
	var start = new Date();
	// set end date to max one year period:
	//var end = new Date(new Date().setYear(start.getFullYear()+1));


$('#date_created').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    autoUpdateInput: false,
    autoApply: true,
    drops: "down",
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
});



var endDate = $('#last_updated').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    autoUpdateInput: false,
    autoApply: true,
    drops: "down",
    minDate: new Date(),
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
});

$('#date_created').on('apply.daterangepicker', function (ev, picker) {
    $('#date_created').val(picker.startDate.format('YYYY-MM-DD'));
    $('#last_updated').val('');
    endDate.data('daterangepicker').setMinDate(picker.startDate.format('YYYY-MM-DD'));
});



$('#last_updated').on('apply.daterangepicker', function (ev, picker) {
    $('#last_updated').val(picker.startDate.format('YYYY-MM-DD'));
   /* var ret_date = check_date();
    if(ret_date)
    {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    }
    else
    {
        $(this).val('');
        alert("To Date greater then Start date");
    }*/
});

/*function check_date() {
    var startDate = new Date($('#date_created').val());
    var endDate = new Date($('#last_updated').val());
    if (startDate < endDate)
    return 1;
    else
    return 0;
    
}*/
</script>
<!--**********************************
	Content body end
***********************************-->
