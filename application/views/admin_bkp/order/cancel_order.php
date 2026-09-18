<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"> <h3>Cancel Orders</h3> </div> <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                     <?php if ($this->session->flashdata('error')) { ?>
                     <div class="alert alert-danger">  <?= $this->session->flashdata('error')?> </div>
                     <?php } ?>

                    <?php if($this->session->flashdata('message')){?>
                    <div class="alert alert-success">  <?= $this->session->flashdata('message')?> </div>
                    <?php } ?>
                    <div class="card-body">                        
                        <div class="dt-ext table-responsive">
                         <div class="topAction-btn">
                        <!--<div class="row">
                            <div class="col-md-3">
                                <div class="form-group">                                    
                                <form method="POST" name="myform" action="/">                               
                                    <select class="form-control btn-checkall" id="selectStatus" name="checkbox_all">
                                        <option value="" selected>Status</option>   
                                        <option value="1">Pending</option>                          
                                        <option value="0">Incomplete</option>   
                                    </select>                                   
                                </form>                                 
                                </div> 
                            </div>                              
                            <div class="col-md-1">                          
                                <div class="form-group">                                
                                    <button class="btn waves-effect waves-light btn-large btnSubmit" type="button" onclick="checkAll()">Submit</button>
                                </div>
                            </div>                          
                        </div> -->
                                            
                        <a href="javascript:void(0)" data-toggle="modal" data-target="#order_filter" class="dropdown-button btn btn-primary addNew" title="Filter" data-toggle="tooltip" data-placement="top" data-animation="false"><i class="fa fa-filter"></i></a>
                        </div> 
                        
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                   <!-- <th><input type="checkbox" id="selectall" onClick="toggle(this)" /> </th>-->
                                                    <th>Order uid</th>
                                                    <th>Customer name</th>                                                   
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Order Date</th>
                                                    <th>Delivery Date</th>                                                  
                                                    <th>View</th>
                                                    <!--<th>Action Status</th>-->
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
</div>

<div class="modal fade" id="exampleModalfat" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">hi</div>
                <div class="alert alert-success">hi</div>
                <?php echo form_open("/$TYPE/attributes/", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST')) ?>
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="name" value="" placeholder="Name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" id="slug" value="" placeholder="Slug" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea rows="5" class="form-control" placeholder="Description" name="description"></textarea>
                    </div>
                <?php echo form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
        
</div>


<!-- Modal -->
<div id="order_filter" class="modal fade customModel" role="dialog">
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
                                <label for="supplier_name">Order Uid</label>
                                <input type="text" id="order_uid" name="order_uid" class="form-control validate">
                            </div>
                            <div class="col-md-6 col s4">
                                <label for="city">Order Date</label>
                                <input type="text" id="date_created" name="date_created" class="form-control validate">
                            </div>
                           <div class="col-md-6 p-4">
                                <!--<div class="form-group">
                                    <label>Status</label>                                   
                                    <select class="form-control" id="status" name="status" required="required">         <option value="" selected>Status</option>   
                                    <option value="1">Pending</option>                          
                                    <option value="0">Incomplete</option>   
                                    </select>
                                </div>-->  
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
<script>

$("#filter_data_filter").submit(function(){
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


$(document).ready(function() {

  // Handle click on "Approved" button
    $('#table tbody').on('click', '.dropdown-menu a', function (e) {
        var statusval = $(this).attr('data-id');
        var orderid = $(this).attr('data-orderid');
    
        

        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.statusval = statusval;
        postData.orderid = orderid;
        swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                 $.ajax( {
                     url:'/<?= $TYPE; ?>/order/update-ajax-status/',
                     type:'post',
                     dataType:"json",  
                     data: postData,
                     success:function(data) {
                         table.ajax.reload();
                         swal({
                              title: "Status!",
                              text: "Status Update Successfully",
                              timer: 2000,
                              showConfirmButton: false
                         });
                     }
                 });
            }
        });
    });


}); 


function dataTable()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    var filterData = {};
    postData.customer_uid = "<?= $customer_uid; ?>";
    if($('#order_uid').val().trim())
    {
       filterData.order_uid = $('#order_uid').val();
      // alert('ppp');
    }
    if($('#date_created').val().trim())
    {
       filterData.date_created = $('#date_created').val();
    }
    /*
    if($('#status').val().trim())
    {
       filterData.status = $('#status').val();
       //alert('kkk');
    }
    */
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
            "url": "/<?= $TYPE; ?>/order/ajax-get-cancel-order",
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
            "render": function ( data, type, row ) {
            var toggle = row.status == 'Active' ? "fa fa-toggle-on" : "fa fa-toggle-off";
            var actionTemplate ='<div class="btn-group">'+
                      '<button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'+
                        'Action'+
                      '</button>'+
                      '<div class="dropdown-menu">'+
                        //'<a class="dropdown-item" href="#" class="Action" data-orderid="'+ row.order_id +'"  data-id="1" >Pending</a>'+
                        '<a class="dropdown-item" href="#" class="Action" data-orderid="'+ row.order_id +'"   data-id="15">Approved</a>'+
                        '<a class="dropdown-item" href="#" class="Action" data-orderid="'+ row.order_id +'"  data-id="2">Shipped</a>'+
                        '<a class="dropdown-item" href="#" class="Action" data-orderid="'+ row.order_id +'"  data-id="4">Refunded</a>'+
                        '<a class="dropdown-item" href="#" class="Action" data-orderid="'+ row.order_id +'"  data-id="5">Cancelled</a>'+
                        '<a class="dropdown-item" href="#" class="Action" data-orderid="'+ row.order_id +'"  data-id="10">Completed</a>'+
                      '</div>'+
                    '</div>';
                return actionTemplate;
            },
            "defaultContent": ''
            },
            {
                'targets': 0,
                'searchable':false,
                'orderable':false,
                'className': 'dt-body-center',
                //'render': function (data, type, full, meta){
                //return '<input type="checkbox" name="id[]" value="' 
                //+ $('<div/>').text(data).html() + '">';
                //}
            }
         ],
        "columns": [
               // {"data": "order_id"},
                {"data": "order_uid_view"},
                {"data": "customer_name"},
               //{"data": "customer_name"},
                {"data": "total"},
                {"data": "quantity"},
                {"data": "order_date"},
                {"data": "delivery_date"},
                //{"data": "status"},
                {"data": "order_id",  render: function ( data, type, row ) {
                  return '<a href="/<?= $TYPE; ?>/order/order_detail/' + row.order_uid + '" class="cancelled_order" target="_blank" data-id="' + row.order_id + '"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                } },
              // {"data": "action"},
          ]
    } );
}


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

function toggle(source) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
    }
}

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
                url         :   '<?php echo base_url();?>admin/order/checkall_status_change',
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
    
$('#date_created').daterangepicker({
    autoUpdateInput: false,
    "singleDatePicker": true,
    "showDropdowns": true,
    //"timePicker": true,
    //"timePicker24Hour": true, 
    locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
}, function(start, end, label) {
    $('#date_created').val(start.format('YYYY-MM-DD'));
});
$("#date_created").on('apply.daterangepicker', function(ev, Picker) {
    $('#date_created').val(Picker.startDate.format('YYYY-MM-DD'));
});
$('#date_created').on('cancel.daterangepicker', function(ev, picker) {
    $('#date_created').val('');
});



</script>
