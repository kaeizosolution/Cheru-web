<?php
#session_destroy();
#print_r($_SESSION);
$pw_lang = get_page_language_data('password_lang');
?>


    <!-- page head section starts -->
    <section class="page-head-section">
        <div class="container page-heading d-none">
            <h2 class="h3 mb-3 text-white text-center"><?= $pw_lang->reset_password ?? 'Reset Password' ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-star">
                    <li class="breadcrumb-item">
                        <a href="/"><i class="ri-home-line"></i><?= $pw_lang->back_to_login ?? 'Home' ?></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $pw_lang->reset_password ?? 'Reset Password' ?></li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- page head section end -->

    <!-- signin page start -->
    <section class="login-hero-section section-b-space">
        <div class="container">
            <div class="row">
                <div class="col-xl-5 col-lg-6 col-md-10 m-auto">
                    <div class="login-data">
                        <form method="POST" class="auth-form" name="reset-form" id="reset-form">
                            <h2><?= $pw_lang->reset_password ?? 'Reset Password' ?></h2>
                            <div class="form-input">
                                <input type="text" class="form-control" placeholder="<?= $pw_lang->password_placeholder ?? 'Password' ?>" name="new_password" id="new_password" minlength="4" maxlength="20" required>
                                <i class="ri-account-circle-line"></i>
                            </div>
                           	<input type="hidden" name="token" value="<?=$token?>"> 
						<button type="submit" class="btn theme-btn submit-btn w-100 rounded-2 reset-btn"> <?= $pw_lang->submit ?? 'Submit' ?> </button>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<script src="/assets/customer/js/reset_password.js"></script> 
