<div class="container loginBox">
    <div class="row">
        <div class="col-md-6 col-sm-12 d-sm-none d-md-block l-side-img noPaddingLR">
            <img src="/assets/core/images/login_bg3.jpg" class="img-fluid">
            <h2 class="loginHeading">Company Login</h2>
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
                <form action="" method="post" autocomplete="off">
                    <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                    <div class="form-group">
                        <label for="email">Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" placeholder="Email *" id="email" name="email" required data-parsley-pattern="^[a-zA-Z0-9\'\.\-_\+]+@[a-zA-Z0-9\-]+\.([a-zA-Z0-9\-]+\.)*?[a-zA-Z]+$" data-parsley-error-message="This value should be a valid email." autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password<span class="text-danger">*</span></label>
                        <input type="password" class="form-control" placeholder="Password *" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    <div class="extraLink"><a href="/<?= $TYPE; ?>/auth/register" class="f-l">Create New Account</a><a href="/<?= $TYPE; ?>/auth/forgot" class="f-r">Forgot Password</a></div>
                </form>
            </div>
        </div>
    </div>
</div>
