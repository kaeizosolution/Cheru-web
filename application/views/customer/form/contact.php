<!-- Contact From -->
<section class="contact-From">
 <div class="container">
     <div class="row">
         <div class="col-md-12">
					<div class="contact-form">
                    <div id="ContactFormThank"></div>
                    <div id="Contactform">
                    <div class="heading_from">
                        <h2>Talk <span>To Us </span> Anytime</h2>
                    </div>
                    <?php if (validation_errors()!='') { ?>
                        <div class="alert alert-danger"> <?php echo validation_errors();?> </div>
                    <?php } ?>

                    <form class="contact-form-box" id="contact_form" method="post">
                        <div class="row gutters-15">
                            <div class="col-lg-6 col-12 2form-group has-error has-danger">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" placeholder="First name" class="form-control" name="fname"  required="">
                            </div>
                            <div class="col-lg-6 col-12 2form-group has-error has-danger">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Last name" class="form-control" name="lname"  required="">
                                
                            </div>
                            <div class="col-md-6 col-12 2form-group has-error has-danger">
                                <label>E-Mail <span class="text-danger">*</span></label>
                                <input type="email" placeholder="Email ID" class="form-control" name="email"  required="">
                                
                            </div>
                            <div class="col-md-6 col-12 2form-group has-error has-danger">
                                <label>Phone <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Phone no" class="form-control" name="phone_no"  required="">
                                
                            </div>
                            <div class="col-12 2form-group">
                                <label>Message <span class="text-danger">*</span></label>
                                <textarea placeholder="Message" class="textarea form-control" name="message" id="form-message" rows="5" cols="20" data-error="Message field is required" required=""></textarea>
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="col-12 2form-group">
							<input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                                <input type="hidden" class="form-control" name="type" value="contact_form">
                                <button type="submit" class="contact_btn" name="submit" value="submit">SUBMIT NOW <i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
						
                    </form>
                </div>   
               </div>
            </div>
     </div>
 </div>
</section>

<div class="container-fluid">
    <div>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2885.8208746412834!2d-79.49368768425911!3d43.67269505918752!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x882b36bf6bf330bb%3A0x7eacd79c208c548b!2s200%20Woolner%20Ave%2C%20York%2C%20ON%20M6N%201Y5%2C%20Canada!5e0!3m2!1sen!2sin!4v1580296272599!5m2!1sen!2sin"
         width="100%" height="400" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
    </div>
</div>

</div>
</section>
<style>
  .thankyou{
    text-align: center;
    border: 1px solid #ccc;
    margin: 20px;
    padding: 10px;
    border-radius: 10px;
    }
  .thankyou h3{
    color: green;
    font-size: 35px;
    }  

</style>

<script>
    $(document).ready(function(){
        $('#ContactFormThank').hide();
        $('#contact_form').on('submit', function (e) {
         e.preventDefault();
          $.ajax({
            type: 'POST',
            url: '/contact/ajax-form-data',
            data: $('#contact_form').serialize(),
            dataType:"json",
            success: function (data) {
                    if(data.success){
                        $('#ContactFormThank').html('<div class="thankyou"> <h3>Thank You</h3> <p>We appreciate you for contacting with us. One of our colleagues will get back to you shortly.</p> <p>Have a great day!</p></div>');
                        $('#Contactform').hide();
                        $("#contact_form").trigger("reset");
                        $('#ContactFormThank').show(); 
                    }
                }
            });
        });
      });

</script>
