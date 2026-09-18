$(document).ready(function (){
  $("#CloseEdit").hide();
  get_profile_data();
})


jQuery(document).ready(function($){
  // Get current path and find target link

  var path = window.location.pathname.split("/").pop();
 // Add active class to target link
 
  $('div a[data-slide=' + path + ']').addClass("active");

  if(path == 'customer-profile')
  {
    $('div a[data-slide=profile]').addClass("active");
  }

});


function uploadimg(e,t)
{
   var file = t.files[0];
   var formdata = new FormData();
   //var postData = {};
   //postData[csrf.name]   = csrf.hash;
   formdata.append('profileimg', file);
   formdata.append('customer_id', customer_id);
   formdata.append('csrf_im_token', csrf.hash); 
   $.ajax({
    url:'/customer/profile/ajax-user-image-upload',
    data:formdata,
    contentType: false,
    processData: false,
    type:'POST',
    success:response=>{
         if(response.status==1)
        {
           $('#UserProfile').attr('src', response.data);
        }
        else
        {
            alert(response.message);
        }
    },

  });

}


// this function use for the show profile form and hide

$(function (){
   $("#CloseEdit").hide();
   $('#EditProfile').click(function (){
	$("#CloseEdit").show('slow');
	$("#Personal_Information").hide('slow');
   });
   
   $('#CloseProfile').click(function (){
	$("#CloseEdit").hide('slow');
	$("#Personal_Information").show('slow');
   });

   $('#CancelForm').click(function (){
	$("#CloseEdit").hide('slow');
	$("#Personal_Information").show('slow');
   });


});

 
//this function use for the update profile data

function update_profile(t, e)
{
  var formdata = new FormData(t); 
  $.ajax({
      type: 'POST',
      url: '/customer/profile/ajax_get_profile',
      data:formdata,
      contentType: false,
      processData: false,
      success: function (response) {          
              if(response.STATUS == '1'){
		  showToast(response.MSG, 'success');
                  $('#CloseEdit').hide('slow');
		  $("#Personal_Information").show('slow');
		  get_profile_data();
              }else{
		  showToast(response.MSG, 'danger');
                  $('#UpdateProfile').show('slow');
		  get_profile_data();
              }
          }
      });  
  return false;
}


function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
    {
        return false;
    }
    return true;
}


function get_profile_data()
{
	var postData = {};
	postData[csrf.name]   = csrf.hash;
	postData.request_type = "get";
	$.ajax({    
    	type: "POST",
    	url:'/customer/profile/ajax-get-profile',
    	dataType:"json",
    	data: postData,
    	success: function(DATA){
	     var ProData = DATA.DATA[0];
	    //alert(JSON.stringify(ProData));
	    var name = ProData.fname !=null ? ProData.fname+' ' : ''; 
	    name += ProData.lname !=null ? ProData.lname : ''; 
            $("#Full_Name").val(name);
	        $("#Email_Address").val(ProData.email);
            $("#Mobile_No").val(ProData.mobile);
	        $("input[name=gender][value=" + ProData.gender + "]").prop('checked', true);
    	}	
	});
 	
}

