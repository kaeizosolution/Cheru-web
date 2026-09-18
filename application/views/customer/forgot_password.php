<?php $pw_lang = get_page_language_data('password_lang'); ?>
  <!-- page head section starts -->
  <div class="body_content_wrapper position-relative">
      <!-- Forgot Password -->
      <section class="our-log-reg bgc-f5">
        <div class="container">
          <div class="row">
            <div class="col-lg-6 col-xl-5 col-xxl-4 m-auto">
              <div class="log_reg_form mt70-992">
                <div class="alert alert-success mt-3 mb-4 d-none" id="forgot-alert-success" role="alert">
                  <?= $pw_lang->password_reset_sent ?? 'A password reset link has been sent to your email address.' ?>
                </div>
                <h2 class="title"><?= $pw_lang->forgot_password ?? 'Forgot Password' ?></h2>
                <p class="mb20"><?= $pw_lang->forgot_instructions ?? 'Enter your email address and we will send you a link to reset your password.' ?></p>
                <div class="login_form">
                  <form method="POST" class="auth-form" name="forgot-form" id="forgot-form">
                    <div class="mb-2 mr-sm-2">
                      <label class="form-label"><?= $pw_lang->email_address_label ?? 'Email address' ?></label>
                      <input type="text" class="form-control" placeholder="<?= $pw_lang->enter_email_placeholder ?? 'Enter your email' ?>" name="email" id="email" maxlength="200" required data-parsley-emailorphone>
                    </div>
                    <button type="submit" class="btn btn-log btn-thm mt20 forgot-btn w-100"><?= $pw_lang->submit ?? 'Submit' ?></button>
                    <p class="text-center mb25 mt10"><?= $pw_lang->remembered_password ?? 'Remembered your password?' ?> <a href="/login"><?= $pw_lang->back_to_login ?? 'Back to login' ?></a></p>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
  </div>
  <script src="/assets/customer/js/forgot_password.js"></script> 
