    <?php if(isset($navigation) && $navigation == 0){} else { ?>
            </div>
        </div>
    <?php } ?>
<script src="/assets/js/sidebar-menu.js"></script>
<script src="/assets/js/script.js"></script>
<script>
$(document).ready(function() { 
    $('form').parsley();
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
});

$(document).ready(function (){
//    $('input[placeholder],textarea[placeholder],select[placeholder]').placeholderLabel({placeholderColor: "#898989", labelColor: "#4AA2CC", labelSize: "14px", fontStyle: "normal", useBorderColor: true, inInput: true, timeMove: 200});
});

$(document).on("input", ".numeric_range", function() {
    var self = $(this);
    self.val(self.val().replace(/[^\d->]+/, ""));
});

$(document).on("input", ".numeric", function() {
    var self = $(this);
    self.val(self.val().replace(/[^\d]+/, ""));
});

$(document).on("input", ".disable_space", function(e) {
    var self = $(this);
    self.val(self.val().replace(/\s/g, ""));
});

$(document).on("input", ".unsigned_float", function(evt) {
    var self = $(this);
    self.val(self.val().replace(/[^0-9\.]/g, ''));
});

$(".btn_back").click(function(){
    window.history.go(-1);
}); 
//$('.alert').delay(3000).fadeOut(300);


$('.no_validate').on('change, keyup', function() {
    var currentInput = $(this).val();
    var fixedInput = currentInput.replace(/[A-Za-z![\]\-\@#$%^&*=+,`_~\|;:\/\"'{}()<>?]/g, '');
    $(this).val(fixedInput);
    console.log(fixedInput);
});
	

function check(e) {
	var keynum
	var keychar
	var numcheck
	// For Internet Explorer
	if (window.event) {
		keynum = e.keyCode;
	}
	// For Netscape/Firefox/Opera
	else if (e.which) {
		keynum = e.which;
	}
	keychar = String.fromCharCode(keynum);
	//List of special characters you want to restrict
	if (keychar == "'" || keychar == "`" || keychar =="!" || keychar =="@" || keychar =="#" || keychar =="$" || keychar =="%" || keychar =="^" || keychar =="&" || keychar =="*" || keychar =="(" || keychar ==")" || keychar =="+" || keychar =="=" || keychar =="/" || keychar =="~" || keychar =="<" || keychar ==">" || keychar =="," || keychar ==";" || keychar ==":" || keychar =="|" || keychar =="?" || keychar =="{" || keychar =="}" || keychar =="[" || keychar =="]" || keychar =="¬" || keychar =="£" || keychar =='"' || keychar =="\\") {
		return false;
	} else {
		return true;
	}
}	
	
$('.btn-primary').click(function(){
	
	//if( document.getElementById('mob').value != ""){
		
		var elem =  document.getElementById('mob');		
		if(typeof elem !== 'undefined' && elem !== null) {
		var y = document.getElementById('mob').value;
       if(isNaN(y)||y.indexOf(" ")!=-1)
       {
         
		   $("#phone-error").html('Invalid Mobile No.');
				   
			document.getElementById('mob').focus();
			//document.getElementById("phone-error").style.display = "block";	
		
          return false;
       }

       if (y.length>10 || y.length<10)
       {
          
			 $("#phone-error").html('Mobile No. should be 10 digit');
            document.getElementById('mob').focus();
            return false;
       }
       if (!(y.charAt(0)=="9" || y.charAt(0)=="8" || y.charAt(0)=="7" || y.charAt(0)=="6"))
       {
          
			 $("#phone-error").html('Mobile No. should start with 6,7,8 or 9');			
            document.getElementById('mob').focus();
			//document.getElementById("phone-error").style.display = "block";
            return false
       }
 }
	

});
	
</script>
</body>
</html>
