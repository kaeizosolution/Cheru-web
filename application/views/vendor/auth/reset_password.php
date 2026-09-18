<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<div class="page-wrapper">
    <div class="container-fluid p-0">
        <div class="row m-0">

            <div class="col-12 col-md-5 col-lg-6 col-xl-7 p-0 auth-left-banner d-none d-md-block">
                <div class="banner-overlay">
                    <div class="brand-center">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo mb-4">
                        <h3 class="text-white font-weight-bold">Vendor Portal</h3>
                        <p class="text-white-50">Set a new password for your account.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7 col-lg-6 col-xl-5 p-0 auth-right-form">
                <div class="login-card-wrapper">

                    <div class="text-center d-md-none mb-4">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo-mobile">
                    </div>

                    <div class="login-header mb-4">
                        <h2 class="font-weight-bold text-dark">Reset Password</h2>
                        <p class="text-muted">Enter your new password below</p>
                    </div>

                    <div class="alert alert-danger custom-alert" style="display:none;" id="reset_error"></div>
                    <div class="alert alert-success custom-alert" style="display:none;" id="reset_success"></div>

                    <form id="resetForm" autocomplete="off" method="POST" class="theme-form login-form">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <input type="hidden" name="sid" value="<?= isset($sid)? $sid : '' ?>" />

                        <div class="form-group mb-3">
                            <label class="col-form-label pt-0">New Password</label>
                            <div class="custom-field-group">
                                <span class="field-icon-left"><i class="fa fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="custom-input no-right-radius" placeholder="At least 6 characters" required minlength="6">
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="col-form-label pt-0">Confirm Password</label>
                            <div class="custom-field-group">
                                <span class="field-icon-left"><i class="fa fa-lock"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="custom-input no-right-radius" placeholder="Confirm your new password" required>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block btn-login" id="resetBtn">
                                <span class="btn-text">Reset Password <i class="fa fa-check ml-2"></i></span>
                                <span class="btn-loader" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Resetting...</span>
                            </button>
                        </div>

                        <div class="new-account mt-4 text-center">
                            <p class="text-muted"><a class="text-primary font-weight-bold" href="/<?= $TYPE ?>/auth/login">Back To Login</a></p>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #6366f1;
        --dark: #1e293b;
        --border: #e2e8f0;
        --bg-light: #f8fafc;
    }
    body, html { min-height: 100%; font-family: 'Poppins', sans-serif; background: #fff; margin: 0; }
    .page-wrapper { min-height: 100vh; display: flex; align-items: stretch; }
    .container-fluid { width: 100%; padding: 0; }
    .row { min-height: 100vh; width: 100%; margin: 0; display: flex; flex-wrap: wrap; }
    .auth-left-banner {
        background: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center center/cover no-repeat;
        position: relative; min-height: 100vh;
    }
    .banner-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(25,28,77,0.9), rgba(99,102,241,0.8));
        display: flex; justify-content: center; align-items: center;
    }
    .brand-center { text-align: center; color: #fff; }
    .login-logo { height: 170px; width: auto; filter: brightness(0) invert(1); animation: bounceIn 1s ease-out; display: block; margin: 0 auto 20px; }
    .auth-right-form { display: flex; align-items: center; justify-content: center; background: #fff; min-height: 100vh; padding: 40px 0; overflow-y: auto; }
    .login-card-wrapper { width: 100%; max-width: 450px; padding: 20px 40px; }
    .custom-field-group { display: flex; width: 100%; height: 48px; align-items: stretch; }
    .field-icon-left { background: var(--bg-light); border: 1px solid var(--border); color: #94a3b8; display: flex; align-items: center; justify-content: center; width: 45px; flex-shrink: 0; border-radius: 8px 0 0 8px; border-right: none; }
    .custom-input { flex-grow: 1; border: 1px solid var(--border); padding: 0 15px; font-size: 14px; outline: none; transition: all 0.3s; border-radius: 0 8px 8px 0; }
    .no-right-radius { border-radius: 0 8px 8px 0; }
    .custom-field-group:focus-within .custom-input, .custom-field-group:focus-within .field-icon-left { border-color: var(--primary); color: var(--primary); }
    .login-logo-mobile { height: 110px; }
    .btn-login { background-color: var(--primary); border: none; height: 48px; border-radius: 8px; font-weight: 600; transition: all 0.3s; width: 100%; color: white; }
    .btn-login:hover { background-color: #4f46e5; transform: translateY(-2px); }
    .custom-alert { border-radius: 8px; }
    @keyframes bounceIn { 0% { opacity:0; transform:scale(0.3); } 50% { opacity:1; transform:scale(1.05); } 100% { transform:scale(1); } }
</style>

<script>
    $("#resetForm").on('submit', function(e) {
        e.preventDefault();
        var pass = $("#password").val();
        var confirmPass = $("#confirm_password").val();

        if (pass !== confirmPass) {
            $('#reset_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>Passwords do not match.');
            return;
        }

        var btn = $("#resetBtn");
        btn.prop('disabled', true);
        btn.find(".btn-text").hide();
        btn.find(".btn-loader").show();
        $('#reset_error').hide();
        $('#reset_success').hide();

        $.ajax({
            url: '/api/v1/vendor/auth/reset-password',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                btn.prop('disabled', false);
                btn.find(".btn-text").show();
                btn.find(".btn-loader").hide();
                try {
                    var res = (typeof response === 'string') ? JSON.parse(response) : response;
                    if (res.status == '1') {
                        $('#reset_success').show().html('<i class="fa fa-check-circle mr-2"></i>' + res.message + ' Redirecting to login...');
                        setTimeout(function() {
                            window.location.href = '/vendor/auth/login';
                        }, 2000);
                    } else {
                        var errMsg = (res.errors && res.errors.length > 0) ? res.errors[0] : res.message;
                        $('#reset_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>' + errMsg);
                    }
                } catch(err) {
                    $('#reset_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>An unexpected error occurred.');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                btn.find(".btn-text").show();
                btn.find(".btn-loader").hide();
                $('#reset_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>Failed to connect to server.');
            }
        });
    });
</script>
