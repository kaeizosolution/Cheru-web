window.Parsley.addValidator('email', {
  validateString: function(value) {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return emailRegex.test(value);
  },
  messages: {
    en: 'Enter a valid email'
  }
});

$('#reset-form').parsley().on('form:submit', function () {
	loading('.reset-btn', true, 'Processing...');
	var post_data = $('#reset-form').serializeArray();
	sign_signup_handler(post_data);
	return false;	

});

function sign_signup_handler(post_data){
	var url = '/customer/forgot_password/reset_password';
	console.log("post_data", post_data);
	ajaxFileFunc(url, post_data, sucs_sign_signup, err_sign_signup);
}

function sucs_sign_signup(resp){
	loading('.reset-btn', false, 'SUBMIT');
	showSnackBar({message:resp.MSG})
	window.location.href='/login'
		
}

function err_sign_signup(status, xhr, error){
	loading('.reset-btn', false, 'SUBMIT');
}
