<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Driver Details</h3>
                </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <?php if (validation_errors()!='') { ?>
            <div class="alert alert-danger">
                <?= validation_errors();?>
            </div>
        <?php } ?>

        <div class="edit-profile">
            <div class="row ">
                <div class="col-lg-12">
                  <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" role="tablist">
                             <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">About me</a>
                             </li>
                             <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Order items</a>
                             </li>
                             <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Driver Earnings</a>
                             </li>
                         </ul><!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="tabs-1" role="tabpanel">
                           				<div class="profile-about-me">
	                                        <div class="pt-4 border-bottom-1 pb-3">    
	                                            <img  src="<?= base_url().'assets/driver_profile/'.$driver_info->user_img; ?>" alt="Snow" style="width:20%;max-width:auto" />                                                    
	                                        </div>
                                            </div>
                                            <div class="profile-personal-info">
                                                <h4 class="h4 mb-4">Driver Information</h4>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">Name <span class="pull-end">:</span></div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($driver_info->name) ? $driver_info->name : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                               <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">Email ID<span class="pull-end">:</span></div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($driver_info->email) ? $driver_info->email : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                       Address<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($driver_info->address) ? $driver_info->address : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                        Phone <span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($driver_info->mobile) ? $driver_info->mobile : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="profile-personal-info">
                                                <h4 class="h4 mb-4">Driver Bank Information</h4>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                        Bank Name<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($bank_info->bank_name) ? $bank_info->bank_name : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                        Account Holder Name<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($bank_info->account_holder_name) ? $bank_info->account_holder_name : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                 <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                        Account No.<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($bank_info->account_number) ? $bank_info->account_number : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                     IFSC Code<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($bank_info->ifsc) ? $bank_info->ifsc : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                 <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                     Bank Location<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($bank_info->bank_location) ? $bank_info->bank_location : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="profile-personal-info">
                                                <h4 class="h4 mb-4">Vehicle Information</h4>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                        Vehicle Type<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span>
                                                      
                                                            <?php echo isset($vehicle_type_info) ? $vehicle_type_info : 'NA'; ?>
                                                        </span></h5>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                        Model Name<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vehicle_info->model_name) ? $vehicle_info->model_name : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                 <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                        Brand Name<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vehicle_info->brand_name) ? $vehicle_info->brand_name : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                     Vehicle No<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7"><h5 class="f-w-500"><span><?php echo isset($vehicle_info->vehicle_no) ? $vehicle_info->vehicle_no : 'NA'; ?></span></h5>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            <div class="profile-personal-info">
                                                <h4 class="h4 mb-4">Driver Documents information</h4>
                                            </div>
                                             <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                     Insurance Certificate<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7">
                                                    <?php if(!empty($driver_doc_info->insurance_certificate)) {?>
                                                   <img id="myImg3" src="<?= base_url().'assets/driver_doc/'.$driver_id.'/'.$driver_doc_info->insurance_certificate; ?>" alt="Snow" style="width:20%;max-width:auto" /> 
                                                    <?php }else{?>
                                                    <img id="myImg3" src="<?= base_url().'assets/images/no-image.png' ?>" alt="Snow" style="width:20%;max-width:auto" /> 
                                                    <?php }?>
                                                    </div>
                                                </div>
                                                  
                                               <div class="row mb-2">
                                                    <div class="col-sm-5 col-5">
                                                      Driving License<span class="pull-end">:</span>
                                                    </div>
                                                    <div class="col-sm-7 col-7">
                                                    <?php if(!empty($driver_doc_info->driving_license)){?>
                                                   <img id="myImg5" src="<?= base_url().'assets/driver_doc/'.$driver_id.'/'.$driver_doc_info->driving_license; ?>" alt="Snow" style="width:20%;max-width:auto" />
                                                   <?php }else{?>
                                                    <img id="myImg3" src="<?= base_url().'assets/images/no-image.png' ?>" alt="Snow" style="width:20%;max-width:auto" /> 
                                                   <?php }?> 
                                                    </div>
                                                </div>    
                                             <div class="profile-personal-info">
                                                <h4 class="h4 mb-4">Vehicle Documents Information</h4>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-5 col-5">
                                                     RC Certificate<span class="pull-end">:</span>
                                                  </div>
                                                <div class="col-sm-7 col-7">                         <?php if(!empty($vehicle_info->rc_certificate)){?>
                                                <img id="myImg1" src="<?= base_url().'assets/driver_doc/'.$driver_id.'/'.$vehicle_info->rc_certificate; ?>" alt="Snow" style="width:20%;max-width:auto" />
                                                <?php }else{?>
                                                    <img id="myImg3" src="<?= base_url().'assets/images/no-image.png' ?>" alt="Snow" style="width:20%;max-width:auto" /> 
                                                <?php }?>
                                                    </div>

                                                </div>
                                                
                                                <div class="row mb-2">
                                                <div class="col-sm-5 col-5">
                                                     Insurance Policy<span class="pull-end">:</span>
                                                  </div>
                                                 <div class="col-sm-7 col-7">
                                                <?php if(!empty($vehicle_info->insurance_policy)){?>
                                                    <img id="myImg2" src="<?= base_url().'assets/driver_doc/'.$driver_id.'/'.$vehicle_info->insurance_policy; ?>" alt="Snow" style="width:20%;max-width:auto" />
                                                <?php }else{?>
                                                    <img id="myImg3" src="<?= base_url().'assets/images/no-image.png' ?>" alt="Snow" style="width:20%;max-width:auto" />
                                                <?php }?>
                                                    </div>
                                                </div>
	                        <form method="POST" class="addpost" id="postForm" action='<?php echo base_url()."$TYPE/driver/update/$driver_info->driver_id"; ?>' enctype="multipart/form-data" autocomplete='off'>
	                          <div class="row">
	                           
	                            <div class="col-md-4  form-group">
	                                <label>Admin Commission % </label>
	                                 <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>"/>
	                                <input  class="form-control" required="" pattern="^[0-9]+$" maxlength="2" title="Enter valid number" placeholder="Admin commission" type="text" name="commission" value="<?= isset($driver_info->commission) ? $driver_info->commission : ''; ?>"/>
	                            </div>
	                            <div class="col-md-4 form-group">
	                                <label>KYC Status</label>
	                                    <select class="form-control" name="status" required="required">
	                                    <option value=""> Select KYC status</option>
	                                    <option value="0" <?php if($driver_info->status =='0') echo "selected=selected"; ?> >Inactive</option>
	                                    <option value="1" <?php if($driver_info->status =='1') echo "selected=selected"; ?> >Approved</option>
	                                    <option value="2" <?php if($driver_info->status =='2') echo "selected=selected"; ?>>Rejected</option>
	                                    </select>
	                                </div>
	                            
	                            <div class="col-md-12 card-footer text-right">
	                                <button type="button" class="btn btn-light btn_back">Back</button>
	                                <button type="submit" class="btn btn-primary">Submit</button>
	                            </div>
	                            
	                        </div>
	                    	</form>
	                    </div>
                        <div class="tab-pane order-details mt-5" id="tabs-2" role="tabpanel">
                                    <div class="row">
                                    <?php 
                                    $Status = order_status();
                                    $class = array(1=>"text-primary", 2=>'text-secondary',3=>'text-dark', 5=>"text-primary", 16=>"text-warning", 8=>"text-primary", 10=>"text-success", 17=>"text-danger", 19=>"text-primary");
                                    foreach($order_info as $itemcount){ 
                                    if(empty($Status[$itemcount->status])){continue;}
                                    ?>
                                    <div class="col-sm-6 col-md-3">
                                    <div class="card">
                                    <div class="profile-about-me text-center">
                                    <div class="pt-4 border-bottom-1 pb-3">    
                                    <h4 class="h4 <?= $class[$itemcount->status]; ?>"><?= isset($Status[$itemcount->status]) && $Status[$itemcount->status] ? $Status[$itemcount->status] : ''; ?></h4>
                                    <p class="mb-2"><?= isset($itemcount->CNT) ? $itemcount->CNT : '0'; ?></p>                                                      
                                    </div>
                                    </div>
                                    </div>
                                    </div>
                                    <?php } ?>
                                    </div>
                        </div>
                         <div class="tab-pane" id="tabs-3" role="tabpanel">
                            <div class="row">
                                <div class="col-sm-6 col-md-12">
                                     <div class="card">
                                        <div class="topAction-btn">
                                            <div class="dt-ext table-responsive" style="text-align: left;">
                                                <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                          <table id="AmountTable" class="display dataTable table-striped table-bordered table-hover custom-table">
                                                              <thead>
                                                               <tr>
                                                                 <th>Billing Month</th>
                                                                 <th>Total Earning</th>
                                                                 <th>Admin Commission (<?= isset($driver_info->commission) ? $driver_info->commission : 0; ?>)%</th>
                                                                 <th>Driver Earning</th>
                                                                 <th>Mode of Payment</th>
                                                                 <th>Paid Date</th>
                                                                 <th>Action</th>
                                                               </tr>
                                                               </thead>
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
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="PaymentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form name="transfer" id="AmountTransfer" method="Post">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Payment Mode</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <span id="messageLabel"></span>
            <select class="form-control" name="paid_type" id="PaidType" required >
              <option value="">Select Payment Mode</option>
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="bank">Bank</option>
            </select>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="earning_id_hdn" id="earning_id_hdn">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary amnt_transfr">Amount Transfer</button>
        </div>
      </div>
    </div>
  </form>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">
  <span class="close">×</span>
  <img class="modal-content" id="img01">
  <div id="caption"></div>
</div>

<script>
// Get the modal
var modal = document.getElementById('myModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var img1 = document.getElementById('myImg1');
var img2 = document.getElementById('myImg2');
var img3 = document.getElementById('myImg3');
var img4 = document.getElementById('myImg4');
var img5 = document.getElementById('myImg5');

var modalImg = document.getElementById("img01");
var captionText = document.getElementById("caption");
img1.onclick = function(){
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
    captionText.innerHTML = this.alt;
}
img2.onclick = function(){
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
    captionText.innerHTML = this.alt;
}

img3.onclick = function(){
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
    captionText.innerHTML = this.alt;
}

img4.onclick = function(){
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
    captionText.innerHTML = this.alt;
}
img5.onclick = function(){
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
    captionText.innerHTML = this.alt;
}
// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
span.onclick = function() { 
    modal.style.display = "none";
}
</script>


<script>
$('.fancy_date').daterangepicker({
    autoUpdateInput: false,
    minDate: moment().format('YYYY-MM-DD H:mm'),
    startDate: nearestMinutes(0),
    timePickerIncrement: 15,
    singleDatePicker: true,
    showDropdowns: true,
    timePicker: true,
    timePicker24Hour: true,
    drops: 'down',
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD H:mm' }
});

$('.fancy_date').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD H:mm'));
    $(this).parsley().reset();
});

$('.fancy_date').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    $(this).parsley().validate()
});

$(document).ready(function() {
    $('select').select2({allowClear: true, placeholder: "Discount Type"});
});

function nearestMinutes(mnt){
    time = moment();
    round_interval = 15;
    intervals = Math.ceil(time.minutes() / round_interval);
    minutes = intervals * round_interval;
    time.minutes(minutes);
    return time.add(mnt,'minutes').format('YYYY-MM-DD H:mm');
}

var table;
$(document).ready(function() {
    dataTable();
});


function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    postData.driver_id = "<?= $driver_id;?>"; 
    table = $('#AmountTable').DataTable({
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "responsive": true,
        "searching": false,
        "ordering": false,
        "lengthChange": false,
        "orderCellsTop": true,
        "destroy": true,
     //   "order":[],
        "pageLength": '10',
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "/<?= $TYPE; ?>/driver/get-driver-amount",
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
            console.log(row);
            var status = row.paid_status;
            if(status == '1'){
                var actionTemplate = 'Paid';
            }else if(row.current_month == '1'){
                var actionTemplate = 'Inprocess';
            }else{
            var actionTemplate = '<button type="button" class="btn btn-primary btn-sm click_to_pay" data-toggle="modal" data-target="#PaymentModal" data-vamount="'+row.driver_amount+'"  data-placement="top" data-html="true" data-earning_id="'+row.earning_id+'">Click to Pay</button>';
            }
            //var actionTemplate =''+row.paid_status+'';
            return actionTemplate;
                },
            "defaultContent": ''
            },
         ],
        "columns": [
               {"data" : "from_date"},
               {"data" : "total"},
               {"data" : "admin_amount"},
               {"data" : "driver_amount"},
               {"data" : "paid_type"},
               {"data" : "paid_date"},
               {"data" : "paid_status"},
          ]
    } );
}


$(document).on('click','.click_to_pay', function(e){
  var earning_id    = $(this).attr('data-earning_id');
  var driver_amount = $(this).attr('data-vamount');
    var alld = {};
    alld['earning_id'] = earning_id;
    alld['driver_amount'] = driver_amount;
    $('#earning_id_hdn').val(JSON.stringify(alld));
});


//function PaymentTranfer(earning_id, vendor_amount)
$(document).on('click','.amnt_transfr', function(e){

var PaidType = $('#PaidType').val();
    if(PaidType == '')
    {
        $('#messageLabel').html('<span style="color:red"> Please select payment type</span>');
        return false;
    }

var dfg = $('#earning_id_hdn').val();
//$('#AmountTransfer').submit(function(){

var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
var PaidType = $("#PaidType").val();
postData.PaidType       = PaidType;
postData.all_info = dfg;
swal({
        title: "Are you sure?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
.then((willTransfer) => {
    if (willTransfer) {
     $.ajax( {
         url:'/<?= $TYPE; ?>/driver/amount-transfer/',
         type:'post',
         dataType:"json",  
         data: postData,
         success:function(data) {
             table.ajax.reload();
             var msg = data.success;
             swal({
                  title: "Status!",
                  text: msg,
                  timer: 2000,
                  showConfirmButton: false
             });
           }
         });
      }
    });
});

</script>
