$(document).ready(function(){
	$('.otp-verifaction').hide();
});


//jQuery submit form when Enter key is pressed
$("#login-form").keypress(function (e) {
  if (e.which == 13) {
    var postData = {};
    postData[csrf.name] = csrf.hash;
    postData['mobile'] = $('#mobile').val().trim();

    if($('#login-form').parsley().validate())
        send_otp_btn_func(postData);
    //$("#login-form").submit();
     return false; 
  }
});


$('.send_otp_btn').click(function(){
	var postData = {};
	postData[csrf.name] = csrf.hash;
	postData['mobile'] = $('#mobile').val().trim();

	if($('#login-form').parsley().validate())
        send_otp_btn_func(postData);	

});

function send_otp_btn_func(postData)
{
	 var request = $.ajax({
      url: "/customer/auth/register",
      type: "POST",
      data: postData,
      dataType: "json"
    });

	request.done(function(resp) {
		if(resp.STATUS)
		{
			
			$('.fill_mobile').hide(); $('.fill_otp').show();
			$('.loginphone').hide();
			$('.otp-verifaction').show();
			$('.user_mobile').html(postData.mobile +' - '+ resp.DATA.otp);	
		}
		
	})	
}


//jQuery submit form when Enter key is pressed
$("#otp-form").keypress(function (e) {
  if (e.which == 13) {
   var postData = {};
    postData[csrf.name] = csrf.hash;
    postData['mobile']  = $('#mobile').val().trim();
    postData['otp']     = $('#otp').val().trim();

    if($('#otp-form').parsley().validate())
        confirm_otp_func(postData); 
     return false;
  }
});


$('.confirm_otp').click(function(){
	var postData = {};
    postData[csrf.name] = csrf.hash;
    postData['mobile'] 	= $('#mobile').val().trim();
    postData['otp'] 	= $('#otp').val().trim();

	if($('#otp-form').parsley().validate())
		confirm_otp_func(postData);

});

function confirm_otp_func(postData)
{
	 var request = $.ajax({
      url: "/customer/auth/check_otp",
      type: "POST",
      data: postData,
      dataType: "json"
    });

	request.done(function(resp) {
		if(resp.STATUS == 1)
		{
			$('#msg').html('<div class="alert alert-success">Great! successfully Loggin.</div>');
		    window.location.href = '/';
		}else{
            $('#msg').html('<div class="alert alert-danger">Your account deactivated by admin</div>');
        }
		
	})	
}

$('.change_number').click(function(){
	$('.fill_mobile').show(); $('.fill_otp').hide();
    $("#login-form").trigger("reset");
});




$('.confirmotp').click(function(){
	var postData = {};
    postData[csrf.name] = csrf.hash;
    postData['mobile'] 	= $('#mobile').val().trim();
    postData['otp'] 	= $('#otp').val().trim();

	if($('#otp-form').parsley().validate())
		confirmotpfunc(postData);

});

function confirmotpfunc(postData)
{
	 var request = $.ajax({
      url: "/customer/auth/check_otp",
      type: "POST",
      data: postData,
      dataType: "json"
    });

	request.done(function(resp) {
		if(resp.STATUS)
		{
			$('#msg').html('<div class="alert alert-success">Great! successfully Loggin.</div>');
			window.location.href = '/checkout';
		}
		
	})	
}


function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
    {   return false;
        return false;
    }
        return true;
}


