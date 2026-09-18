<?php $page_lang = get_page_language_data('admin_page_lang'); ?>
<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6"> <h3><?= $page_lang->banner; ?> </h3> </div> <?= $breadcrumbs ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
					<?php if ($this->session->flashdata('error')) { ?> <div class="alert alert-danger">
					<?= $this->session->flashdata('error')?> </div> <?php } ?>

                    <?php if($this->session->flashdata('success')){?>  <div class="alert alert-success"> <?= $this->session->flashdata('success')?> </div> <?php } ?>

                    <div class="card-body">                       
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead>
                                                <tr>
                                                    <th><?= $page_lang->id; ?></th>
                                                    <th><?= $page_lang->widget_name; ?></th>
                                                    <th><?= $page_lang->items; ?></th> 
                                                    <th width="35px;"><?= $page_lang->action; ?></th>
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
                        <label class="form-label"><?= $page_lang->name; ?></label>
                        <input type="text" class="form-control" name="name" id="name" value="" placeholder="<?= $page_lang->name; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $page_lang->slug; ?></label>
                        <input type="text" class="form-control" name="slug" id="slug" value="" placeholder="<?= $page_lang->slug; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= $page_lang->description; ?></label>
                        <textarea rows="5" class="form-control" placeholder="<?= $page_lang->description; ?>" name="description"></textarea>
                    </div>
                <?php echo form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"><?= $page_lang->submit; ?></button>
            </div>
        </div>
    </div>
</div>
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
            "url": "/<?= $TYPE; ?>/banner/ajax_get_widget_type",
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
            var actionTemplate ='<button title="View" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-view btncolor" type="button" data-id="'+row.widget_id+'"><i class="fa fa-eye"></i></button>';
                return actionTemplate;
            },
            "defaultContent": ''
            },
         ],
        "columns": [
               {"data": "sl_id"},
               {"data": "widget_name"},              
               {"data": "button_widget_id"},
               {"data": "action"},
          ]
    } );
}


$(document).on('click','.btn-item', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/banner/index_attribute_item/"+uid+"";
    url_new_tab(e,url);
});

$(document).on('click','.btn-view', function(e){
    var uid=$(this).attr('data-id');
    var url="/<?php echo $TYPE ?>/banner/get_ajax_banner/"+uid+"";
    url_new_tab(e,url);
});

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
