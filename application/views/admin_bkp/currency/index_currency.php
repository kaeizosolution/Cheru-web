<div class="page-body">
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-6">
                    <h3>Currency</h3>
                </div>
                <?= $breadcrumbs ?>
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
                     </div>
                     <?php } ?>

                    <?php if($this->session->flashdata('success')){?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('success')?>
                    </div>
                    <?php } ?>
                    <div class="card-body">
                        <?= $currency->iso_code.' '.$currency->symbol ?><span id="currency"><?= $currency->rate ?></span> <a href="javascript:void(0);" onclick="refresh()">Refresh</a> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function refresh()
{
    var postData = {"<?= $csrf->name;?>" : "<?= $csrf->hash; ?>"};
    $.ajax({
        url: "/<?= $TYPE; ?>/currency/index_ajax_refresh",
        type: 'post',
        data: postData,
        dataType: "json",
        success: function(response){
            if(response.status) {
                swal('Currency Rate updated Successfully.');
                $('#currency').html(response.data);
            }else{
            }
        }
    });
}
</script>
