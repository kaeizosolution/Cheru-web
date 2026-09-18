<?php
$page_lang = get_page_language_data('register_page_lang');
?>
  <div class="body_content_wrapper position-relative">
  	<section class="our-log-reg bgc-f5">
  		<div class="container">
  			<div class="row">
          <div class="col-lg-6 col-xl-5 col-xxl-4 m-auto">
            <div class="log_reg_form mt70-992">
              <?php if($this->input->get('signup') == 'success'): ?>
                 <div class="alert alert-success mb-4" role="alert">
                   Account created successfully! Please login to continue.
                 </div>
              <?php endif; ?>
              <h2 class="title"><?= $page_lang->sign_in ?? 'Sign-In' ?></h2>
              <div id="login-page-message" class="auth-page-message mb-3" style="display:none;"></div>
              <div class="login_form">
                <form method="POST" class="auth-form" name="login-form" id="login-form">
                  <input type="hidden" name="action" value="login">
                  
                  <div class="form-group mb-2 mr-sm-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label" id="label_email_mobile_login">Mobile number</label>
                        <a href="#" class="toggle-login-method" data-target="#email_mobile_login" data-label="#label_email_mobile_login" style="font-size:12px;">Use email instead</a>
                    </div>
                    <input type="text" class="form-control" name="email_mobile" id="email_mobile_login" placeholder="Mobile number" maxlength="200" required data-parsley-emailorphone data-login-mode="mobile">
                  </div>

                  <div class="form-group mb5 password_div">
                    <label class="form-label"><?= $page_lang->enter_password ?? 'Password' ?></label>
                    <input type="password" class="form-control" placeholder="<?= $page_lang->enter_password ?? 'Password' ?>" name="password" id="password" maxlength="50" required>
                  </div>
                  <div class="custom-control custom-checkbox password_div">
                    <input type="checkbox" class="custom-control-input" id="exampleCheck3">
                    <label class="custom-control-label" for="exampleCheck3">Remember me</label>
                    <a class="btn-fpswd float-end" href="/forgot-password">Lost your password?</a>
                  </div>
                  <button type="submit" class="btn btn-log btn-thm mt20 login-btn w-100"><?= $page_lang->continue ?? 'Login' ?></button>
                  <p class="text-center mb25 mt10">Don't have an account? <a href="/register">Create account</a></p>
                  <div class="hr_content">
                    <hr>
                    <span class="hr_top_text">or</span>
                  </div>
                  <ul class="login_with_social text-center mt30 mb0">
                    <li class="list-inline-item"><a href="#"><i class="fab fa-facebook"></i></a></li>
                    <li class="list-inline-item"><a href="#"><i class="fab fa-google"></i></a></li>
                    <li class="list-inline-item"><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                    <li class="list-inline-item"><a href="#"><i class="fab fa-apple"></i></a></li>
                  </ul>
                </form>
              </div>
            </div>
          </div>
  			</div>
  		</div>
  	</section>
  </div>
  <script src="<?= base_url('assets/customer/js/login.js?v='.(defined('FCPATH') ? @filemtime(FCPATH.'assets/customer/js/login.js') : time())); ?>"></script>