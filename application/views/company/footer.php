        <?php if(isset($navigation) && $navigation == 0){} else { ?>
        <!--<section class="footer-section-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 footer-bottom-left">
                        <p>© 2020 LIMADI ,ALL RIGHTS RESERVED.</p>
                    </div>
                    <div class="col-md-4 footer-bottom-right">
                        <ul class="social">
                            <li> <a href="#"><i class="fa fa-twitter"></i></a> </li>
                            <li> <a href="#"><i class="fa fa-google-plus"></i></a> </li>
                            <li> <a href="#"><i class="fa fa-pinterest"></i></a> </li>
                            <li> <a href="#"><i class="fa fa-youtube"></i></a> </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>-->
        <?php } ?>

        <script>
            $(document).ready(function(){
                $("input").attr('autocomplete', 'off');
                $('.fancy_select').select2({allowClear: true});
                $('#customer_login_btn').click(function(){
                    window.location.href = "/customer/auth/login";
                });
                $('#company_login_btn').click(function(){
                    window.location.href = "/company/auth/login";
                }); 
            });

function format_date(date_string){
    var today = new Date(date_string);
    var monthName = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var day = today.getDate() + "";
    var month = today.getMonth() + 1;
    var year = today.getFullYear() + "";
    var hour = today.getHours() + "";
    var minutes = today.getMinutes() + "";
    var seconds = today.getSeconds() + "";

    day = checkZero(day);
    month = checkZero(month);
    year = checkZero(year);
    hour = checkZero(hour);
    mintues = checkZero(minutes);
    seconds = checkZero(seconds);
    return day + "-" + month + "-" + year + " " + hour + ":" + minutes;
}

function checkZero(data){
    if(data.length == 1){
        data = "0" + data;
    }
    return data;
}

$(document).on("input", ".numeric", function() {
    var self = $(this);
    self.val(self.val().replace(/[^\d]+/, ""));
});

$(document).on("input", ".disable_space", function(e) {
    var self = $(this);
    self.val(self.val().replace(/\s/g, ""));
});

$("form").keypress(function(e) {
  //Enter key
  if (e.which == 13) {
    return false;
  }
});

$('.btnReset').on('click', function (e){
    var form = $(this).closest('form')[0];
    var form_id = $(this).closest("form").attr('id');
    form.reset();
    //float_label();
    blank_values(form_id);
});

$('.btnSearchReset').on('click', function (e){
    var url = '';
    <?php
    $CI =& get_instance();
    $controller = $CI->router->fetch_class();
    $model = $CI->router->fetch_method();
    $current_url = current_url();
    $base_url = base_url();
    $type = $_SESSION['type'];

    if(preg_match("/$controller\/$model\/(.*)/", $_SERVER['REQUEST_URI'], $output_array)){
        $redirect_url = $base_url.'/'.$type.'/'.$controller.'/'.$model;
    ?>
        url = '<?= $redirect_url ?>';
    <?php } ?>

    if(url){
        window.location.href = url;
    }

    var form = $(this).closest('form')[0];
    var form_id = $(this).closest("form").attr('id');
    form.reset();
    //float_label();
    blank_values(form_id);
    $('.btnSearchSubmit').click();
});

function blank_values(form_id)
{
    var form = $('#'+form_id).find('input[type=text][name!=cvr][name!=cname], input[type=email]');
    form.val('');
}

function float_label() 
{
  var onClass = "on";
  var showClass = "show";

  $("input, textarea, select")
  //$("#pickup_datetime")
    .bind("checkval", function () 
    {
      var label = $(this).prev("label");
      if (this.value !== ""){
        label.addClass(showClass);
      }else{
        label.removeClass(showClass);
      }
    })
    .on("keyup", function () 
    {
      $(this).trigger("checkval");
    })
    .on("focus", function () 
    {
      $(this).prev("label").addClass(onClass);
    })
    .on("blur", function () 
    {
        $(this).prev("label").removeClass(onClass);
    })
    .trigger("checkval");
    
  $("select")
    .on("change", function ()
    {
      var $this = $(this);
      
      if ($this.val() == "")
        $this.addClass("watermark");
      
      else
        $this.removeClass("watermark");
        
      $this.trigger("checkval");
    })
    .change();
}

$('.alert').delay(3000).fadeOut(300);
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});

function go_back() {
    window.history.back();
}
        </script>
    </body>
</html>
