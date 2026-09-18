<?php
#session_destroy();
#print_r($_SESSION);
?>


    <!-- page head section starts -->
    <section class="page-head-section">
        <div class="container page-heading d-none">
            <h2 class="h3 mb-3 text-white text-center">Forgot Password</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-star">
                    <li class="breadcrumb-item">
                        <a href="/"><i class="ri-home-line"></i>Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Forgot Password</li>
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
                        <form method="POST" class="auth-form" name="forgot-form" id="forgot-form">
                            <h2>Forgot Password</h2>
                            <!--<h5>
                                or
                                <a href="signup.html"><span class="theme-color">create an a account</span></a>
                            </h5>-->
                            <div class="form-input">
                                <input type="text" class="form-control" placeholder="Email" name="email" id="email" maxlength="200" required data-parsley-emailorphone>
                                <i class="ri-account-circle-line"></i>
                            </div>
                            
							<button type="submit" class="btn theme-btn submit-btn w-100 rounded-2 forgot-btn"> Submit </button>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- signin page end -->
<script src="/assets/customer/js/forgot_password.js"></script> 
