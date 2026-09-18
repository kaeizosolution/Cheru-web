<?php 
$controller = $this->router->fetch_class();
$model = $this->router->fetch_method();
$path = base_url();

?>
<?php
$TYPE = isset($_SESSION['type']) ? $_SESSION['type'] : 'vendor';
$logged_in = false;
if($this->session->userdata($TYPE)){
    $session_obj = $this->session->userdata($TYPE);
    $logged_in = isset($session_obj['vendor_id']) ? $session_obj['vendor_id'] : (isset($session_obj['login_id']) ? $session_obj['login_id'] : '');
}
?>
<?php if($logged_in){ ?>
    </div>
    <!--*********  Main wrapper end***************-->
<?php } ?>

</body>

<script>

$(document).ready(function() { 
    $('form').parsley();
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
});

</script>

 
<!--***************** Scripts *************-->
<!-- Required vendors -->
<script src="/assets/vendor/plugins/global/global.min.js"></script>
<script src="/assets/vendor/plugins/jquery-nice-select/js/jquery.nice-select.min.js"></script>
<script src="/assets/vendor/plugins/select2/js/select2.full.min.js"></script>
<script src="/assets/vendor/js/plugins-init/select2-init.js"></script>

<!-- Datatable -->
<script src="/assets/plugins/vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="/assets/vendor/js/plugins-init/datatables.init.js"></script>
<script src="/assets/vendor/js/custom.min.js"></script>
<script src="/assets/vendor/js/deznav-init.js"></script>
<script src="/assets/vendor/js/demo.js"></script>
<!--script src="js/imosys_styleSwitcher.js"></script-->
<script src="/assets/plugins/parsley/parsley.min.js"></script>
<!--script>jQuery.noConflict();</script-->
</html>
