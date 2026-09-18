<div class="container registrationBox">
		<div class="row">
			<div class="col-md-6 col-sm-12 d-sm-none d-md-block l-side-img noPaddingLR d-none">
				<img src="/assets/core/images/login_bg3.jpg" class="img-fluid imglogin">
				<h2 class="loginHeading">Company Registration</h2>
			</div>
			<div class="col-md-6 col-sm-12 noPaddingR">
				<div class="loginContainer">
                <?php if ((isset($message) && $message['error']!='') || validation_errors()!='') { ?>
                <div class="alert alert-danger">
                    <?= validation_errors();
                        #if($message['error']!=''){
                        #    echo $message['error'];
                        #}
                    ?>
                </div>
                <?php } ?>
                <?php if(isset($this->session) && $this->session->flashdata('message')){ ?>
                <div class="alert alert-success">
                    <?php echo $this->session->flashdata('message');
                        $this->session->sess_destroy(); ?>
                </div>
                <?php } ?>
					<form action="" method="post" autocomplete="off">
						<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
						<div class="container-fluid noPaddingLR">
							<div class="row">
								<div class="form-group float_Tab col-md-6 col-sm-12 noPaddingR-7 col-12">
									<label for="cvr" class="">CVR Number<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control company_class" placeholder="CVR Number *" id="cvr" name="cvr" required value="<?= isset($cvr) ? $cvr : "" ?>" autofocus maxlength="100">
								</div>
								<div class="form-group float_Tab col-md-6 col-sm-12 noPaddingL-7 col-12">
									<label for="cname">Company Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control company_class" placeholder="Company Name *" id="cname" name="cname" required value="<?= isset($cname) ? $cname : "" ?>" maxlength="100">
								</div>
								<div class="form-group float_Tab col-md-6 col-sm-12 noPaddingR-7 col-12">
									<label for="email">Email<span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" placeholder="Email *" id="email" name="email" required data-parsley-pattern="^[a-zA-Z0-9\'\.\-_\+]+@[a-zA-Z0-9\-]+\.([a-zA-Z0-9\-]+\.)*?[a-zA-Z]+$" data-parsley-error-message="This value should be a valid email" value="<?= isset($email) ? $email : "" ?>" maxlength="100"> 
								</div>
								<div class="form-group float_Tab col-md-6 col-sm-12 noPaddingL-7 col-12">
									<label for="mobile">Mobile<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control numeric" placeholder="Mobile Number *" id="mobile" name="mobile" required value="<?= isset($mobile) ? $mobile : "" ?>" data-parsley-pattern="^\d{8}$" data-parsley-error-message="Mobile number should be 8 digit only" maxlength="10">
								</div>
								<div class="form-group float_Tab col-md-6 col-sm-12 noPaddingR-7 col-12">
									<label for="password">Password<span class="text-danger">*</span></label>
                                    <input type="password" class="form-control disable_space" placeholder="Password *" id="password" name="password" required maxlength="20">
								</div>
								<div class="form-group float_Tab col-md-6 col-sm-12 noPaddingL-7 col-12">
									<label for="confirm_password">Confirm Password<span class="text-danger">*</span></label>
                                    <input type="password" class="form-control disable_space" placeholder="Confirm Password *" id="confirm_password" name="confirm_password" required data-parsley-equalto="#password" data-parsley-error-message="Password and Confirm password should be same" maxlength="20">
								</div>
								<div class="form-group float_Tab col-md-6 col-sm-12 noPaddingR-7 col-12 blackType">
									<label for="street">Address<span class="text-danger">*</span></label>
                                    <select class="form-control addressTypeHead select2" id="street" name="street" data-parsley-trigger="change" data-parsley-errors-container="#street_error" required>
                                        <option value=''>Address *</option>
                                    </select>
                                    <div id="street_error"></div>
								</div>
                                <input type="hidden" name="country_id" value="56" />
								<button type="submit" class="btn btn-primary btn-block m-LR-15">Submit</button>
								<div class="extraLink"><a href="/<?= $TYPE; ?>/auth/login" class="f-r"><i class="fa fa-arrow-left" aria-hidden="true"></i> Login</a></div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

<script  type="text/javascript" charset="UTF-8" >
$("#street").select2({
    minimumInputLength: 2,
    placeholder: 'Address *',
    allowClear: true,
    ajax: { 
        url: "/<?= $TYPE ?>/auth/index_ajax_address",
        type: "post",
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                searchTerm: params.term,
                <?= $csrf->name;?> : "<?= $csrf->hash; ?>"
            };
        },
        processResults: function (response) {
        return {
            results: response
        };
    },
    cache: true
    }
});

query = '';
$(document).on('keyup', '.addressTypeHead', function () {
    var textBox = $(this);
    var AUTOCOMPLETION_URL = '<?= $AUTOCOMPLETION_URL ?>';
    var APIKEY = '<?= $ROUTE_API_KEY ?>';

    if (query != textBox.val()){
        if (textBox.val().length >= 1){
            var params = '?' +
                'query=' +  encodeURIComponent(textBox.val()) +   // The search text which is the basis of the query
                '&maxresults=5' +  // The upper limit the for number of suggestions to be included
                '&apikey=' + APIKEY; // in the response.  Default is set to 5.

            $.ajax( {
                url: AUTOCOMPLETION_URL + params,
                type:'get',
                dataType:"json",
                success:function(data) {
                    var response = data.suggestions;
                    var li = '';
                    for(i in response){
                        var label = response[i].label;

                        var country = response[i].address.country;
                        var county = response[i].address.county;
                        var state = response[i].address.state;
                        var street = response[i].address.street;
                        var postalCode = response[i].address.postalCode;
                        var city = response[i].address.city;
                        label = label.replace('<mark>','').replace('</mark>','');
                        li+= '<li class="res_li">'+street+', '+postalCode+', '+city+'</li>';
                    }
                    textBox.next('ul').html(li);
                    textBox.next('ul').show();
                },
                error: function (error) {
                    console.log('Ooops!');
                }
            });
        }
    }
    query = textBox.val();
});

$(document).on('click', '.res_li', function(e){
    var street_val = $(this).html();
    $(this).parent().prev().val(street_val);
    $(this).parent('ul').toggle();
    if($(this).parent().prev().attr('id') == 'pickup_street'){
        $('.preview_pickup_address').html(street_val);
    }
});

$(document).on('click', function (e) {
    $(".customTypeHead").hide();
});
</script>
