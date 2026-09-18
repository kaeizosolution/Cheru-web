window.Parsley.addValidator('email', {
  validateString: function(value) {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return emailRegex.test(value);
  },
  messages: {
    en: 'Enter a valid email'
  }
});

$('#forgot-form').parsley().on('form:submit', function () {
	loading('.forgot-btn', true, 'Processing...');
	var post_data = $('#forgot-form').serializeArray();
	sign_signup_handler(post_data);
	return false;	

});

function sign_signup_handler(post_data){
	var url = '/customer/forgot_password/send_link';
	console.log("post_data", post_data);
	ajaxFileFunc(url, post_data, sucs_sign_signup, err_sign_signup);
}

function sucs_sign_signup(resp){
	loading('.forgot-btn', false, 'SUBMIT');
	console.log("Forgot Password Response:", resp);
	
    var status = (resp.status !== undefined) ? resp.status : (resp.STATUS !== undefined ? resp.STATUS : null);
    var message = resp.msg || resp.MSG || "";

    // If the API status implies success, show the alert block
	if(status == 1) {
		$('#forgot-alert-success').removeClass('d-none');
        // If the server returned a specific message, update the text (preserving the design)
        if(message) {
            $('#forgot-alert-success').text(message);
        }
        $('#forgot-form')[0].reset();	
	} else {
		// Fallback for errors
        if (typeof showSnackBar === 'function') {
		  showSnackBar({message: message || "Something went wrong"});
        } else {
          alert(message || "Something went wrong");
        }
	}
}

function err_sign_signup(status, xhr, error){
	loading('.forgot-btn', false, 'SUBMIT');
}
