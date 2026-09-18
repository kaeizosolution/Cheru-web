<div class="container loginBox">
    <div class="row">
        <div class="col-md-6 col-sm-12 d-sm-none d-md-block l-side-img noPaddingLR">
            <img src="/assets/core/images/login_bg3.jpg" class="img-fluid">
        </div>
        <div class="col-md-6 col-sm-12 float_Tab">
            <div class="loginContainer">
                <h2 class="heading3">Reset Password</h2>
                <?php if ((isset($message) && $message['error']!='') || validation_errors()!='') { ?>
                <div class="alert alert-danger">
                    <?= validation_errors();
                        if($message['error']!=''){
                            echo $message['error'];
                        }
                    ?>
                </div>
                <?php } ?>
                <?php if(isset($this->session) && $this->session->flashdata('message')){ ?>
                <div class="alert alert-success">
                    <?php echo $this->session->flashdata('message');
                        $this->session->sess_destroy(); ?>
                </div>
                <?php } ?>
                <form action="" method="post" autocomplete="off">
                    <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                    <div class="form-group">
                        <label for="password">Password<span class="text-danger">*</span></label>
                        <input type="password" class="form-control disable_space" placeholder="Password *" id="password" name="password" required autofocus maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password<span class="text-danger">*</span></label>
                        <input type="password" class="form-control disable_space" placeholder="Confirm Password *" id="confirm_password" name="confirm_password" required data-parsley-equalto="#password" data-parsley-error-message="Password and Confirm password should be same" maxlength="20">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    <div class="extraLink"><a href="/<?= $TYPE; ?>/auth/login" class="f-r">Back to Login</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
