<div class="page-wrapper">
    <div class="container-fluid p-0">
        <div class="row m-0">
            
            <div class="col-12 col-md-5 col-lg-6 col-xl-7 p-0 auth-left-banner d-none d-md-block">
                <div class="banner-overlay">
                    <div class="brand-center">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo mb-4">
                        <h3 class="text-white font-weight-bold">Vendor Portal</h3>
                        <p class="text-white-50">Manage your store, products, and orders efficiently.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7 col-lg-6 col-xl-5 p-0 auth-right-form">
                <div class="login-card-wrapper">
                    
                    <div class="text-center d-md-none mb-4">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo-mobile">
                    </div>

                    <div class="login-header mb-4">
                        <h2 class="font-weight-bold text-dark">Welcome Back!</h2>
                        <p class="text-muted">Sign in to your vendor account</p>
                    </div>

                    <?php if ((isset($message) && $message['error']!='') || validation_errors()!='') { ?>
                    <div class="alert alert-danger custom-alert">
                        <i class="fa fa-exclamation-circle mr-2"></i>
                        <?= validation_errors();
                            if($message['error']!=''){
                                echo $message['error'];
                            }
                        ?>
                    </div>
                    <?php } ?>
                    
                    <?php if(isset($this->session) && $this->session->flashdata('message')){ ?>
                        <div class="alert alert-success custom-alert">
                            <?php echo $this->session->flashdata('message'); ?>
                        </div>
                    <?php } ?>

                    <div class="alert alert-danger custom-alert" style="display:none;" id="loginForm_error"></div>
                    <div class="alert alert-success custom-alert" style="display:none;" id="loginForm_success"></div>

                    <?php echo form_open("/api/v1/vendor/login", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'theme-form login-form')) ?>
                        
                        <div class="form-group mb-3">
                            <label class="col-form-label pt-0">Email Address</label>
                            <div class="custom-field-group">
                                <span class="field-icon-left"><i class="fa fa-envelope"></i></span>
                                <input type="text" name="email" id="email" class="custom-input no-right-radius" value="<?php if(isset($_COOKIE["loginId"])) { echo $_COOKIE["loginId"]; } ?>" placeholder="hello@example.com" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="col-form-label">Password</label>
                            <div class="custom-field-group">
                                <span class="field-icon-left"><i class="fa fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="custom-input no-radius" placeholder="********" value="<?php if(isset($_COOKIE["loginPass"])) { echo $_COOKIE["loginPass"]; } ?>" required>
                                <span class="field-icon-right" onclick="togglePassword()">
                                    <i class="fa fa-eye" id="toggleIcon"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember" name="remember" <?php if(isset($_COOKIE["loginId"])) { ?> checked="checked" <?php } ?>>
                                <label class="custom-control-label text-muted" for="remember">Remember me</label>
                            </div>
                            <a href="/<?= $TYPE?>/auth/forgot" class="text-primary small font-weight-bold">Forgot Password?</a>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block btn-login" id="submitBtn">
                                <span class="btn-text">Sign In <i class="fa fa-arrow-right ml-2"></i></span>
                                <span class="btn-loader" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Signing In...</span>
                            </button>
                        </div>
                        
                        <div class="new-account mt-4 text-center">
                            <p class="text-muted">Don't have an account? <a class="text-primary font-weight-bold" href="/vendor/auth/signup">Sign up</a></p>
                        </div>

                    <?php echo form_close() ?>
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

    body, html { 
        min-height: 100%; 
        font-family: 'Poppins', sans-serif; 
        background: #fff; 
        margin: 0; 
    }

    .page-wrapper { min-height: 100vh; display: flex; align-items: stretch; }
    .container-fluid { width: 100%; padding: 0; }
    .row { min-height: 100vh; width: 100%; margin: 0; }

    /* --- RESTORED ORIGINAL IMAGE URL --- */
    .auth-left-banner {
        background: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center center/cover no-repeat;
        position: relative;
        min-height: 100vh;
    }
    
    .banner-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(25, 28, 77, 0.9), rgba(99, 102, 241, 0.8));
        display: flex; justify-content: center; align-items: center;
    }

    .brand-center { text-align: center; color: #fff; }
    .login-logo { height: 170px; width: auto; filter: brightness(0) invert(1); animation: bounceIn 1s ease-out; display: block; margin: 0 auto 20px; }

    /* --- FORM SECTION & SCROLL FIX --- */
    .auth-right-form { 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        background: #fff; 
        min-height: 100vh; 
        padding: 40px 0; /* Prevents bottom cut-off */
        overflow-y: auto; 
    }

    .login-card-wrapper { width: 100%; max-width: 450px; padding: 20px 40px; }

    /* --- INPUT ALIGNMENT FIXES --- */
    .custom-field-group {
        display: flex;
        width: 100%;
        height: 48px;
        align-items: stretch;
    }

    .field-icon-left, .field-icon-right {
        background: var(--bg-light);
        border: 1px solid var(--border);
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        flex-shrink: 0;
    }

    .field-icon-left { border-radius: 8px 0 0 8px; border-right: none; }
    .field-icon-right { border-radius: 0 8px 8px 0; border-left: none; background: #fff; cursor: pointer; }

    .custom-input {
        flex-grow: 1;
        border: 1px solid var(--border);
        padding: 0 15px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s;
    }

    .no-right-radius { border-radius: 0 8px 8px 0; border-left: none; }
    .no-radius { border-radius: 0; border-left: none; border-right: none; }

    .custom-field-group:focus-within .custom-input,
    .custom-field-group:focus-within .field-icon-left,
    .custom-field-group:focus-within .field-icon-right {
        border-color: var(--primary);
        color: var(--primary);
    }

    .login-logo-mobile{
        height: 110px;
        /* width: 140px; */
    }

    /* --- BUTTONS --- */
    .btn-login { background-color: var(--primary); border: none; height: 48px; border-radius: 8px; font-weight: 600; transition: all 0.3s; width: 100%; color: white; }
    .btn-login:hover { background-color: #4f46e5; transform: translateY(-2px); }

    @keyframes bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>

<script>
    function togglePassword() {
        var x = document.getElementById("password");
        var icon = document.getElementById("toggleIcon");
        if (x.type === "password") {
            x.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            x.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }

    $("#loginForm").on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $("#submitBtn");
        
        btn.prop('disabled', true);
        btn.find(".btn-text").hide();
        btn.find(".btn-loader").show();
        
        $('#loginForm_error').hide();
        $('#loginForm_success').hide();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response){
                btn.prop('disabled', false);
                btn.find(".btn-text").show();
                btn.find(".btn-loader").hide();
                
                try {
                    var res = (typeof response === 'string') ? JSON.parse(response) : response;
                    if(res.status == '1') {
                        $('#loginForm_success').show().html('<i class="fa fa-check-circle mr-2"></i>' + res.message);
                        setTimeout(function(){ 
                            window.location.href = res.redirect_url ? res.redirect_url : '/vendor/dashboard'; 
                        }, 1000);
                    } else {
                        var errMsg = (res.errors && res.errors.length > 0) ? res.errors[0] : res.message;
                        $('#loginForm_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>' + errMsg);
                    }
                } catch(err) {
                    $('#loginForm_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i> An unexpected error occurred.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                btn.find(".btn-text").show();
                btn.find(".btn-loader").hide();
                var errMsg = 'Failed to connect to server.';
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
                $('#loginForm_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i> ' + errMsg);
            }
        });
    });
</script>