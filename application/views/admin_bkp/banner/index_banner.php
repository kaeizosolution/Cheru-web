<?php 
if ($this->uri->segment(4) === FALSE){
	$segment = 0;
}else{
	$segment = $this->uri->segment(4);
}
//echo $segment;die;
?>

<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">  <h3><?= $banner_type ?></h3> </div>
                <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <?php if ($this->session->flashdata('error')) { ?>  <div class="alert alert-danger">
                    <?= $this->session->flashdata('error')?> </div> <?php } ?>

                    <?php if($this->session->flashdata('message')){?>  <div class="alert alert-success">
                    <?= $this->session->flashdata('message')?>  </div> <?php } ?>                   
                    <div class="card-body">                       
						<div class="topAction-btn"><a href="/<?= $TYPE?>/banner/add/<?=$segment?>" class="btn btn-primary addNew" 
						title="Add New" data-toggle="tooltip" data-placement="top" data-animation="false">
						<i class="fa fa-plus"></i></a></div>
						
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead> <tr>
											<th>Title</th><th>Banner URL</th><th>Banner</th><th>Height</th>
											<th>Width</th><th>Status</th>	<th>Action</th>
											</tr> </thead>
                                            <tbody> </tbody>
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
<script>
var table;
$(document).ready(function() {
    dataTable();
});


$(document).ready(function() {

    // Handle click on "Ststus" button
    $('#table tbody').on('click', '.btn-status', function (e) {
        var id = $(this).attr('data-id');
        var status = $(this).attr('data-status');
        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.id = id;
        postData.status = status;
        swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                 $.ajax( {
                     url:'/<?= $TYPE; ?>/banner/update-ajax-status/',
                     type:'post',
                     dataType:"json",  
                     data: postData,
                     success:function(data) {
                         table.ajax.reload();
                         swal({
                              title: "Status!",
                              text: "Status has been changed.",
                              timer: 2000,
                              showConfirmButton: false
                         });
                     }
                 });
            }
        });
    }); 


// Handle click on "Delete" button
    $('#table tbody').on('click', '.btn-delete', function (e) {
        var id = $(this).attr('data-id');
        var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
        postData.id = id;
        swal({
            title: "Are you sure?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                 $.ajax( {
                     url:'/<?= $TYPE; ?>/banner/ajax-delete-banner/',
                     type:'post',
                     dataType:"json",  
                     data: postData,
                     success:function(data) {
                         table.ajax.reload();
                         swal({
                              title: "Status!",
                              text: "Data Deleted Successfully",
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
    postData.widget_id = <?=$widget_id ?>;
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
            "url": "/<?= $TYPE; ?>/banner/index_ajax_get_banner",
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
            var actionTemplate ='<button title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-edit btncolor" type="button" data-id="'+row.banner_id+'"><i class="fa fa-edit"></i></button>'+'&nbsp;'+'<button title="'+ row.status +'" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-status btncolor" data-status="'+ row.status +'"  type="button" data-id="'+row.banner_id+'"><i class="'+toggle+'"></i></button>'+'&nbsp;'+'<button title="Delete" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete btncolor" type="button" data-id="'+row.banner_id+'"><i class="fa fa-trash"></i></button>';
                return actionTemplate;
            },
            "defaultContent": ''
            },
         ],
        "columns": [
               {"data": "title"},
               {"data": "url"},
               {"data": "banner_image",
                "render": function (data, type, full, meta) {
                         return "<img src=\"" + data + "\" height=\"50\"/>";
                    },
                },
               {"data": "height"},
               {"data": "width"},
               {"data": "status"},
               {"data": "action"},
          ]
    } );
}

$(document).on('click','.btn-edit', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/banner/update/"+uid+"";
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

$("#slug").focus(function() {
    var str = $("#name").val().trim();
    var res = str.toLowerCase().replace(/\s+/g, '-');
    $("#slug").val(res);
});
</script>
