<div class="page-wrapper">
    <div class="container-fluid">
        <div class="authentication-main">
            <div class="row">
                <div class="col-md-4 p-0">
                    <div class="auth-innerleft">
                        <div class="text-center">
                        </div>
                    </div>
                </div>
                <div class="col-md-8 p-0">
                    <div class="auth-innerright">
                        <div class="authentication-box">
                            <h4>RESET PASSWORD</h4>
                            <div class="card mt-4 p-4 mb-0">
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
                                <?= $this->session->flashdata('message');
                                    $this->session->sess_destroy(); ?>
                            </div>
                            <?php } ?>

                                <?php echo form_open("/$TYPE/auth/reset", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'theme-form')) ?>
                                    <div class="form-group">
                                        <label class="col-form-label pt-0">Password</label>
                                        <input type="password" class="form-control form-control-lg" placeholder="Password" id="password" name="password" type="password" required >
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label pt-0">Confirm Password</label>
                                        <input type="password" class="form-control form-control-lg" placeholder="Confirm Password" id="confirm_password" name="confirm_password" type="password" required data-parsley-equalto="#password" data-parsley-error-message="Password and Confirm password should be same" >
                                    </div>
                                    <div class="form-row">
                                        <div class="col-sm-4">
                                            <button type="submit" class="btn btn-secondary">Submit</button>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="text-right mt-2 m-l-20">
                                                <a href="/<?= $TYPE?>/auth/login" class="btn-link text-capitalize">Back to Login?</a>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="sid" value="<?= isset($sid)? $sid : '' ?>" />
                                <?php echo form_close() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
