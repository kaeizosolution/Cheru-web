<?php
$page_lang = get_page_language_data('register_page_lang');
?>
  <div class="body_content_wrapper position-relative">
  	<section class="our-log-reg bgc-f5">
  		<div class="container">
  			<div class="row">
          <div class="col-lg-6 col-xl-5 col-xxl-4 m-auto">
            <div class="log_reg_form mt70-992">
              <h2 class="title">Create your account</h2>
              <div id="signup-page-message" class="auth-page-message mb-3" style="display:none;"></div>
              <div class="sign_up_form">
                <form method="POST" id="signup-form">
                  <input type="hidden" name="action" value="signup">
                  <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" class="form-control" placeholder="Full Name" name="fname" id="fname" maxlength="150" required>
                  </div>
                  <div class="form-group mb-2">
                    <label class="form-label">Mobile number</label>
                    <input type="text" class="form-control" placeholder="Mobile number" name="mobile" id="mobile_signup" maxlength="15" required>
                  </div>

                  <div class="form-group mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" placeholder="Email address" name="email" id="email_signup" maxlength="200" required>
                  </div>

                  <div class="form-group mb20">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" placeholder="******************" name="password" id="password_signup" maxlength="50" required>
                  </div>
                  <button type="submit" class="btn btn-signup btn-thm w-100">Create Account</button>
                  <p class="text-center mb25 mt10">Already have an account? <a href="/login">Sign in</a></p>
                </form>
              </div>
            </div>
          </div>
  			</div>
  		</div>
  	</section>
  </div>
  <script src="<?= base_url('assets/customer/js/login.js?v='.(defined('FCPATH') ? @filemtime(FCPATH.'assets/customer/js/login.js') : time())); ?>"></script>
