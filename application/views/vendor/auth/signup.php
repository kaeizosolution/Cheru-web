<div class="page-wrapper">
    <div class="container-fluid p-0">
        <div class="row m-0 auth-layout-row">
            
            <div class="col-12 col-lg-5 col-xl-5 p-0 auth-left-banner d-none d-lg-block">
                <div class="banner-overlay">
                    <div class="brand-center">
                        <img src="/assets/vendor/images/cheru-removebg-preview.png" alt="Logo" class="login-logo mb-4">
                        <h3 class="text-white font-weight-bold">Join Our Marketplace</h3>
                        <p class="text-white-50">Create your vendor account and start selling today.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7 col-xl-7 p-0 auth-right-form">
                <div class="login-card-wrapper">
                    
                    <div class="text-center d-lg-none mb-4">
                        <img src="/assets/vendor/images/logo-full.png" alt="Logo" class="login-logo-mobile">
                    </div>

                    <div class="alert alert-danger custom-alert" style="display:none;" id="loginForm_error"></div>
                    <div class="alert alert-success custom-alert" style="display:none;" id="loginForm_success"></div>
                    
                    <?php if ((isset($message) && $message['error']!='') || validation_errors()!='') { ?>
                    <div class="alert alert-danger custom-alert">
                        <?= validation_errors(); if($message['error']!='') echo $message['error']; ?>
                    </div>
                    <?php } ?>

                    <?php echo form_open_multipart("api/v1/vendor/register", array('id' => 'loginForm1', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'theme-form')) ?>
                        
                        <div class="form-heading mb-4">
                            <h3 class="font-weight-bold text-dark mb-1">Vendor Registration</h3>
                            <p class="text-muted mb-0">Please fill the form below to create your vendor account.</p>
                        </div>

                        <!-- SECTION: Personal Information -->
                        <div class="form-section">
                            <div class="section-label">
                                <span class="section-icon">👤</span> Personal Information
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile No <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Mobile Number" maxlength="10" onkeypress="return isNumberKey(event);" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" placeholder="Enter Password" minlength="3" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION: Store Details -->
                        <div class="form-section">
                            <div class="section-label">
                                <span class="section-icon">🏪</span> Store Details
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="store_name" id="store_name" class="form-control" placeholder="Enter Store Name" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION: Address -->
                        <div class="form-section">
                            <div class="section-label">
                                <span class="section-icon">📍</span> Address Details
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Street Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" id="locality" class="form-control" placeholder="Enter full street address" required>
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">
                                    <input type="hidden" name="place_id" id="location_id">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">House No.</label>
                                    <input type="text" name="house_no" id="house_no" class="form-control" placeholder="e.g. 12A, B-204">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" id="city" class="form-control" placeholder="City" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Zip / PIN Code</label>
                                    <input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code" maxlength="10">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <input type="text" name="country" id="country" class="form-control" placeholder="Country" required>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION: Documents -->
                        <div class="form-section">
                            <div class="section-label">
                                <span class="section-icon">📄</span> Identity Documents
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Document Front <span class="text-danger">*</span></label>
                                    <input type="file" name="document_front" id="document_front" class="form-control" required>
                                    <small class="text-muted">JPG, PNG or PDF (max 20MB)</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Document Back <span class="text-danger">*</span></label>
                                    <input type="file" name="document_back" id="document_back" class="form-control" required>
                                    <small class="text-muted">JPG, PNG or PDF (max 20MB)</small>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="api" value="1">
                        <input type="hidden" name="role" value="2">

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary btn-block btn-login w-100">
                                Sign Up <i class="fa fa-user-plus ml-2"></i>
                            </button>
                        </div>

                        <div class="col-12 mt-3 text-center signin-link-wrap">
                            <p class="text-muted mb-0">Already have an account? <a href="/vendor/auth/login" class="text-primary font-weight-bold">Sign in</a></p>
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
        --section-bg: #f8fafc;
    }

    body, html { height: 100%; font-family: 'Poppins', sans-serif; background: #fff; margin: 0; }
    
    .page-wrapper { min-height: 100vh; display: flex; align-items: stretch; }
    .container-fluid { width: 100%; padding: 0; }
    .auth-layout-row { height: 100vh; width: 100%; margin: 0; }

    /* --- LEFT BANNER --- */
    .auth-left-banner {
        background: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center center/cover no-repeat;
        position: relative;
        height: 100vh;
    }
    
    .banner-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(25, 28, 77, 0.9), rgba(99, 102, 241, 0.8));
        display: flex; justify-content: center; align-items: center;
    }

    .brand-center { text-align: center; color: #fff; }
    
    .login-logo {
        height: 170px; width: auto;
        filter: brightness(0) invert(1);
        animation: bounceIn 1s ease-out;
        display: block; margin: 0 auto 20px;
    }
    
    .login-logo-mobile { height: 60px; width: auto; }

    @keyframes bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); }
    }

    /* --- RIGHT FORM SECTION --- */
    .auth-right-form {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        background: #f1f5f9;
        height: 100vh;
        overflow-y: auto;
        padding: 20px 16px;
    }

    .login-card-wrapper {
        width: 100%;
        max-width: 640px;
        padding: 24px 28px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        margin: 0 auto;
    }

    .form-heading { margin-bottom: 14px !important; }
    .form-heading h3 { font-size: 20px; color: var(--dark); margin-bottom: 2px !important; }
    .form-heading p { font-size: 12px; color: #64748b; margin: 0; }

    /* --- FORM SECTIONS --- */
    .form-section {
        background: var(--section-bg);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 14px 16px 4px;
        margin-bottom: 12px;
    }

    .section-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-color);
    }

    .section-icon { margin-right: 6px; }

    /* --- INPUT FIELDS --- */
    .form-label { font-weight: 600; color: #475569; font-size: 12px; margin-bottom: 4px; }
    .mb-3 { margin-bottom: 10px !important; }
    
    .form-control {
        height: 38px;
        border: 1px solid var(--border-color);
        border-radius: 7px;
        font-size: 13px;
        padding-left: 12px;
        background: #fff;
    }

    input[type="file"].form-control { height: auto; padding: 6px 12px; }
    
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }

    /* --- BUTTON --- */
    .btn-login {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
        height: 48px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        letter-spacing: 0.3px;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        transition: all 0.3s;
        color: #fff;
    }
    .btn-login:hover {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(99, 102, 241, 0.45);
    }
    
    .custom-alert { border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .signin-link-wrap { position: relative; z-index: 5; }
    .signin-link-wrap a { position: relative; z-index: 6; font-weight: 600; }

    small.text-muted { font-size: 11px; margin-top: 4px; display: block; }
</style>

<script>
// AJAX Submit Logic
$("#loginForm1").on('submit', function(e) {
    e.preventDefault();
    if(typeof $(this).parsley === "function" && !$(this).parsley().isValid()) {
        return false;
    }
    var loginForm = $(this);
	var formData = new FormData(this);
    $.ajax({
        url: loginForm.attr('action'),
        type: 'post',
        data: formData,
		contentType: false,
		processData: false,
        success: function(response){
            var res = (typeof response === 'string') ? JSON.parse(response) : response;
            if(res.status == '1') {
                $('#loginForm_error').hide();
                $('#loginForm_success').show().html('<i class="fa fa-check-circle mr-2"></i>' + res.message);
                
                // Add a visual scroll to the message or highlight it
                $('html, body').animate({ scrollTop: $(".login-card-wrapper").offset().top }, 500);

				var redirectUrl = res.redirect_url ? res.redirect_url : '/vendor/profile';
                setTimeout(function(){ window.location.href = redirectUrl; }, 3000); // Increased delay
            } else {
                $('#loginForm_success').hide();
                var errMsg = (res.errors && res.errors.length > 0) ? res.errors[0] : res.message;
                $('#loginForm_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>' + errMsg);
                $('html, body').animate({ scrollTop: $(".login-card-wrapper").offset().top }, 500);
            }
        },
        error: function(xhr) {
            $('#loginForm_success').hide();
            var errMsg = 'Registration failed. Please try again.';
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
            $('#loginForm_error').show().html('<i class="fa fa-exclamation-triangle mr-2"></i>' + errMsg);
            $('html, body').animate({ scrollTop: $(".login-card-wrapper").offset().top }, 500);
        }
    });
});

function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) return false;
    return true;
}
</script>