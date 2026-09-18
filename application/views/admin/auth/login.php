<div class="page-wrapper">
    <div class="container-fluid p-0">
        <div class="row m-0">
            
            <div class="col-12 col-md-5 col-lg-6 col-xl-7 p-0 auth-left-banner d-none d-md-block">
                <div class="banner-overlay">
                    <div class="brand-center">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo mb-4">
                        <h3 class="text-white font-weight-bold">Welcome Back!</h3>
                        <p class="text-white-50">Please sign in to access your dashboard.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7 col-lg-6 col-xl-5 p-0 auth-right-form">
                <div class="login-card-wrapper">
                    
                    <div class="text-center d-md-none mb-4">
                        <img src="<?php echo base_url('assets/vendor/images/cheru-logo.png'); ?>" alt="Logo" class="login-logo-mobile">
                    </div>

                    <div class="login-header mb-4">
                        <h2 class="font-weight-bold text-dark">Sign In</h2>
                        <p class="text-muted">Enter your email and password to login</p>
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

                    <?php echo form_open("/$TYPE/auth/login", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'theme-form login-form')) ?>
                        
                        <div class="form-group mb-3">
                            <label class="col-form-label pt-0">Email Address</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                </div>
                                <input type="text" class="form-control" placeholder="name@example.com" name="email" id="email" required autofocus>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="col-form-label">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" name="password" id="password" placeholder="********" required>
                                <div class="input-group-append" style="cursor:pointer;" onclick="togglePassword()">
                                    <span class="input-group-text border-left-0 bg-white" style="border-radius: 0 8px 8px 0;">
                                        <i class="fa fa-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                <label class="custom-control-label text-muted" for="remember">Remember me</label>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block btn-login" id="submitBtn">
                                <span class="btn-text">Sign In <i class="fa fa-arrow-right ml-2"></i></span>
                                <span class="btn-loader" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Signing In...</span>
                            </button>
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
        --border-color: #e2e8f0;
    }

    body, html { height: 100%; font-family: 'Poppins', sans-serif; background: #fff; }
    
    .page-wrapper { min-height: 100vh; display: flex; align-items: stretch; }
    .container-fluid { width: 100%; padding: 0; }
    .row { height: 100vh; }

    /* --- LEFT BANNER --- */
    .auth-left-banner {
        background: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center center/cover no-repeat;
        position: relative;
    }
    
    .banner-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(25, 28, 77, 0.9), rgba(99, 102, 241, 0.8));
        display: flex; justify-content: center; align-items: center;
    }

    .brand-center { text-align: center; color: #fff; }
    
    /* --- LOGO ANIMATION --- */
    .login-logo {
        height: 170px; 
        width: auto;
        filter: brightness(0) invert(1);
        animation: bounceIn 1s ease-out;
    }

    @keyframes bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); }
    }

    /* --- RIGHT FORM SECTION --- */
    .auth-right-form {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .login-card-wrapper {
        width: 100%;
        max-width: 450px;
        padding: 40px;
    }

    .login-header h2 { font-size: 28px; margin-bottom: 10px; color: var(--dark); }
    .login-header p { font-size: 14px; }

    /* --- INPUT FIELDS --- */
    .form-group label { font-weight: 600; color: #475569; font-size: 13px; margin-bottom: 8px; }
    
    .input-group-text {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-right: none;
        border-radius: 8px 0 0 8px;
        color: #94a3b8;
    }
    
    .form-control {
        height: 48px;
        border: 1px solid var(--border-color);
        border-left: none;
        border-radius: 0 8px 8px 0;
        font-size: 14px;
        padding-left: 10px;
    }
    .login-logo-mobile{
        height: 110px;
    }
    
    /* Input with Append (Eye Icon) adjustment */
    .input-group .form-control:not(:last-child) {
        border-radius: 0;
        border-right: none;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: none;
    }
    .form-control:focus + .input-group-prepend .input-group-text,
    .form-control:focus ~ .input-group-append .input-group-text {
        border-color: var(--primary);
    }
    
    .input-group:focus-within .input-group-text { border-color: var(--primary); color: var(--primary); }
    .input-group:focus-within .form-control { border-color: var(--primary); }

    /* --- BUTTON --- */
    .btn-login {
        background-color: var(--primary);
        border: none;
        height: 48px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        transition: all 0.3s;
    }
    .btn-login:hover {
        background-color: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
    }
    .btn-login:disabled {
        background-color: #818cf8;
        cursor: not-allowed;
        transform: none;
    }

    

    /* --- ALERT --- */
    .custom-alert {
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 20px;
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }
    
    /* --- CUSTOM CHECKBOX --- */
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: var(--primary);
        background-color: var(--primary);
    }
</style>

<script>
    // 1. Password Toggle
    function togglePassword() {
        var x = document.getElementById("password");
        var icon = document.getElementById("toggleIcon");
        if (x.type === "password") {
            x.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            x.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    // 2. Loading State on Submit
    document.getElementById("loginForm").addEventListener("submit", function() {
        var btn = document.getElementById("submitBtn");
        var btnText = btn.querySelector(".btn-text");
        var btnLoader = btn.querySelector(".btn-loader");
        
        btn.disabled = true; // Prevent double submit
        btnText.style.display = "none";
        btnLoader.style.display = "inline-block";
    });
</script>