<?php $page_lang = get_page_language_data('admin_page_lang'); ?>

<div class="page-wrapper">
    <div class="container-fluid p-0">
        <div class="row m-0">

            <div class="col-12 col-md-5 col-lg-6 col-xl-7 p-0 auth-left-banner d-none d-md-block">
                <div class="banner-overlay">
                    <div class="brand-center">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo mb-4">
                        <h3 class="text-white font-weight-bold">Vendor Portal</h3>
                        <p class="text-white-50">Reset your password to regain access to your store.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7 col-lg-6 col-xl-5 p-0 auth-right-form">
                <div class="login-card-wrapper">

                    <div class="text-center d-md-none mb-4">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo-mobile">
                    </div>

                    <div class="login-header mb-4">
                        <h2 class="font-weight-bold text-dark">Forgot Password?</h2>
                        <p class="text-muted">Enter your registered email to receive a reset link.</p>
                    </div>

                    <div class="alert alert-danger custom-alert" style="display:none;" id="forgot_error"></div>
                    <div class="alert alert-success custom-alert" style="display:none;" id="forgot_success"></div>

                    <form id="forgotForm" autocomplete="off" method="POST" class="theme-form login-form">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="form-group mb-3">
                            <label class="col-form-label pt-0">Email Address</label>
                            <div class="custom-field-group">
                                <span class="field-icon-left"><i class="fa fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="custom-input no-right-radius" placeholder="hello@example.com" required>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block btn-login" id="forgotBtn">
                                <span class="btn-text">Send Reset Link <i class="fa fa-paper-plane ml-2"></i></span>
                                <span class="btn-loader" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Sending...</span>
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
    $("#forgotForm").on('submit', function(e) {
        e.preventDefault();
        var btn = $("#forgotBtn");
        btn.prop('disabled', true);
        btn.find(".btn-text").hide();
        btn.find(".btn-loader").show();
        $('#forgot_error').hide();
        $('#forgot_success').hide();

        $.ajax({
            url: '<?= base_url("api/v1/vendor/auth/forgot-password") ?>',
            type: 'POST',
            data: { email: $('#email').val() },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false);
                btn.find(".btn-text").show();
                btn.find(".btn-loader").hide();
                if (res && res.status == '1') {
                    $('#forgot_success').show().html('<i class="fa fa-check-circle mr-2"></i>Reset password link has been sent to your email.');
                    $('#forgotForm')[0].reset();
                } else {
                    var errMsg = (res && res.errors && res.errors.length > 0) ? res.errors[0] : (res.message || 'An error occurred. Please try again.');
                    $('#forgot_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>' + errMsg);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                btn.find(".btn-text").show();
                btn.find(".btn-loader").hide();
                var errMsg = 'Failed to connect. Please try again.';
                if (xhr.responseText) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.errors && res.errors.length > 0) {
                            errMsg = res.errors[0];
                        } else if (res.message) {
                            errMsg = res.message;
                        }
                    } catch(e) {}
                }
                $('#forgot_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>' + errMsg);
            }
        });
    });
</script>
