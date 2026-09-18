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
                <div class="col-lg-6 d-flex justify-content-end align-items-center">
                    <div class="mr-2">
                        <?= $breadcrumbs ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <?php if ($this->session->flashdata('error')) { ?>  <div class="alert alert-danger" data-auto-hide="1">
                    <?= $this->session->flashdata('error')?> </div> <?php } ?>

                    <?php if($this->session->flashdata('banner_message')){?>  <div class="alert alert-success" data-auto-hide="1">
                    <?= $this->session->flashdata('banner_message')?>  </div> <?php $this->session->unset_userdata('banner_message'); } ?>                   
                    <div class="card-body"> 
						<div class="row mb-3">
							<div class="col-md-6">
								<div id="bannerStats" style="font-weight:600;">Active banners: <span id="activeBannerCount">0</span></div>
								<div class="form-inline mt-2">
									<button type="button" class="btn btn-primary" id="showSelectedBtn">Show Selected</button>
								</div>
							</div>
							<div class="col-md-6 text-right">
								<a href="/<?= $TYPE?>/banner/add/<?=$segment?>" class="btn btn-primary" title="Add Banner" data-toggle="tooltip" data-placement="top" data-animation="false">Add Banner</a>
							</div>
						</div>
						
                        <div class="dt-ext table-responsive">
                            <div id="new-cons_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
						
						
                                        <table id="table" class="display dataTable table-striped table-bordered table-hover custom-table">
                                            <thead> <tr>
											<th><input type="checkbox" id="bannerSelectAll" /></th><th>Title</th><th>Banner</th><th>Tag</th>
											<th>Description</th><th>Status</th>	<th>Action</th>
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
	loadBannerStats();
});

function loadBannerStats()
{
	var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
	postData.widget_id = <?=$widget_id ?>;
	$.ajax({
		url: '/<?= $TYPE; ?>/banner/ajax-get-banner-stats',
		type: 'post',
		dataType: 'json',
		data: postData
	}).done(function(resp){
		if(!resp || !resp.success) return;
		$('#activeBannerCount').text(resp.active_count || 0);
	});
}

$(document).on('change', '#bannerSelectAll', function(){
	var checked = $(this).is(':checked');
	$('.banner-row-checkbox').prop('checked', checked);
});

$(document).on('click', '#showSelectedBtn', function(){
	var ids = [];
	$('.banner-row-checkbox:checked').each(function(){
		ids.push($(this).val());
	});
	var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
	postData.widget_id = <?=$widget_id ?>;
	postData.banner_ids = ids;

	swal({
		title: 'Are you sure?',
		text: 'Only selected banners will be shown on customer side.',
		icon: 'warning',
		buttons: true,
		dangerMode: true,
	}).then((ok) => {
		if(!ok) return;
		$.ajax({
			url: '/<?= $TYPE; ?>/banner/ajax-set-visible-selected',
			type: 'post',
			dataType: 'json',
			data: postData
		}).done(function(resp){
			if(resp && resp.success){
				table.ajax.reload(null, false);
				loadBannerStats();
				swal({
					title: 'Updated',
					text: 'Customer banner visibility updated.',
					timer: 1500,
					showConfirmButton: false
				});
			}
		});
	});
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
            var actionTemplate ='<button title="Edit" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-edit btncolor" type="button" data-id="'+row.banner_id+'"><i class="fa fa-edit"></i></button>'+'&nbsp;'+'<button title="'+ row.status +'" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-status btncolor" data-status="'+ row.status +'"  type="button" data-id="'+row.banner_id+'"><i class="'+toggle+'"></i></button>'+'&nbsp;'+'<!--button title="Delete" data-toggle="tooltip" data-placement="top" data-animation="false" data-html="true" class="btn custom-popover-btn btn-delete btncolor" type="button" data-id="'+row.banner_id+'"><i class="fa fa-trash"></i></button-->';
                return actionTemplate;
            },
            "defaultContent": ''
            },
         ],
        "columns": [
               {"data": "banner_id",
                "render": function (data, type, full, meta) {
                        var id = data || '';
                        var checked = (full && full.enabled == 1) ? 'checked' : '';
                        return '<input type="checkbox" class="banner-row-checkbox" value="' + id + '" ' + checked + ' />';
                    },
                },
               {"data": "title",
                "render": function (data, type, full, meta) {
                        var t = data || '';
                        var u = full && full.url ? full.url : '';
                        if (u) {
                            return '<a href="' + u + '" target="_blank">' + t + '</a>';
                        }
                        return t;
                    },
                },
               {"data": "banner_image",
                "render": function (data, type, full, meta) {
                         return "<img src=\"" + data + "\" height=\"50\"/>";
                    },
                },
               {"data": "tag"},
               {"data": "description"},
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

<script>
(function(){
	function hideAlerts(){
		try {
			var els = document.querySelectorAll('[data-auto-hide="1"]');
			for (var i = 0; i < els.length; i++) {
				els[i].style.display = 'none';
			}
		} catch(e) {}
	}
	function schedule(){
		setTimeout(hideAlerts, 5000);
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', schedule);
		window.addEventListener('load', schedule);
	} else {
		schedule();
	}
})();
</script>
