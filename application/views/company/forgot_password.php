<div class="container loginBox">
    <div class="row">
        <div class="col-md-6 col-sm-12 d-sm-none d-md-block l-side-img noPaddingLR">
            <img src="/assets/core/images/login_bg3.jpg" class="img-fluid">
            <h2 class="loginHeading">Forgot Password</h2>
        </div>
        <div class="col-md-6 col-sm-12 float_Tab">
            <div class="loginContainer">
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
                        <label for="email">Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" placeholder="Email" id="email" name="email" required data-parsley-pattern="^[a-zA-Z0-9\'\.\-_\+]+@[a-zA-Z0-9\-]+\.([a-zA-Z0-9\-]+\.)*?[a-zA-Z]+$" data-parsley-error-message="This value should be a valid email." autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    <div class="extraLink"><a href="/<?= $TYPE; ?>/auth/login" class="f-r">Back to Login</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
