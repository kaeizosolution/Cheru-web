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
                            <h4>FORGOT PASSWORD</h4>
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

                                <?php echo form_open("/$TYPE/auth/forgot", array('id' => 'loginForm', 'autocomplete' => 'off', 'method' => 'POST', 'class' => 'theme-form')) ?>
                                    <div class="form-group">
                                        <label class="col-form-label pt-0">Email</label>
                                        <input type="text" class="form-control form-control-lg" placeholder="Email" name="email" id="email" required data-parsley-pattern="^[a-zA-Z0-9\'\.\-_\+]+@[a-zA-Z0-9\-]+\.([a-zA-Z0-9\-]+\.)*?[a-zA-Z]+$" data-parsley-error-message="This value should be a valid email.">
                                    </div>
                                    <div class="form-row">
                                        <div class="col-sm-4">
                                            <button type="submit" class="btn btn-secondary">Submit</button>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="text-right mt-2 m-l-20">
                                                <a href="/<?= $TYPE?>/auth/login" class="btn-link text-capitalize">Back To Login?</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php echo form_close() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
