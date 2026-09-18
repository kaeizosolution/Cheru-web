    <?php if(isset($navigation) && $navigation == 0){} else { ?>
        </div>
    </div>

    <?php } ?>

<script>
$(document).ready(function() {
    $('.fancy_select').select2({allowClear: true});
<?php
    $CI =& get_instance();
    $controller = $CI->router->fetch_class();
    $model = $CI->router->fetch_method();
    $selector = $controller.'_'.$model;
?>
    $("#<?= $selector;?>").closest('ul').closest('div').css("display", "block");
    $("#<?= $selector;?>").children('a').addClass('menu-active');
});

$( ".fancy_select" ).change(function() {
    if(this.value != '' && $(this).siblings('span').siblings('div').children("ul.parsley-errors-list").length > 0){
        $(this).siblings('span').siblings('div').children("ul.parsley-errors-list").hide();
    }else{
        $(this).siblings('span').siblings('div').children("ul.parsley-errors-list").show();
    }
});
</script>
</body>
</html>
