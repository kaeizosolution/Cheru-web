<?php
$page_lang = get_page_language_data('contact_us_lang');
?>
<style>
    .contact-uspage {
        padding: 50px 0;
        background-color: #f9f9f9;
    }
    .contact-card {
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .contact-title h2 {
        font-weight: 700;
        color: #333;
    }
    .contact-title p {
        color: #666;
    }
    .address-box {
        background: #f4f7f6;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        border-left: 5px solid #ff4d4d;
    }
    .form-control {
        height: 50px;
        border-radius: 8px;
        border: 1px solid #e1e1e1;
        padding-left: 20px;
        font-size: 14px;
    }
    .form-control:focus {
        border-color: #ff4d4d;
        box-shadow: none;
    }
    textarea.form-control {
        height: auto;
        padding-top: 15px;
    }
    .next-btn16 {
        background: #333;
        color: #fff;
        border: none;
        padding: 12px 30px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }
    .next-btn16:hover {
        background: #ff4d4d;
        transform: translateY(-2px);
    }
    .mandatory { color: red; }
</style>

<div class="wrapper">
    <div class="imosysnew-Breadcrumb">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>"><?= $page_lang->home ?? 'Home' ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= $page_lang->contact_us ?? 'Contact Us' ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="all-product-grid contact-uspage">
        <div class="container"> <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="contact-card">
                        <div class="row">
                            <div class="col-lg-5 col-md-6 mb-4">
                                <div class="contact-title">
                                    <h2><?= $page_lang->get_in_touch ?? 'Get in Touch' ?></h2>
                                    <p><?= $page_lang->contact_subtitle ?? 'If you have a question about our service or have an issue to report, please fill out the form below.' ?></p>
                                </div>
                                
                                <div class="address-box">
                                    <h4 class="mb-3"><?= $page_lang->head_office ?? 'Head Office' ?></h4>
                                    <p>
                                        <strong><?= $page_lang->address_label ?? 'Address:' ?></strong><br>
                                        #0000, St No. 0, Main road,<br>
                                        Bangaluru, Karnataka - 141001
                                    </p>
                                    <p>
                                        <strong><?= $page_lang->phone_label ?? 'Phone:' ?></strong><br>
                                        <span class="color-pink">0000-000-000</span>
                                    </p>
                                </div>
                            </div>

                            <div class="col-lg-7 col-md-7">
                                <div id="contactThank" style="display:none;"></div>
                                
                                <?php if (validation_errors() != '') { ?>
                                    <div class="alert alert-danger"> <?php echo validation_errors(); ?> </div>
                                <?php } ?>

                                <div id="error" class="alert alert-danger" style="display:none;"></div>

                                <form id="contact_form" method="post">
                                    <div class="row">
                                        <div class="col-md-6 form-group mt-3">
                                            <label class="control-label"><?= $page_lang->full_name ?? 'Full Name' ?> <span class="mandatory">*</span></label>
                                            <input type="text" class="form-control" name="fname" id="sendername" placeholder="<?= $page_lang->john_doe_placeholder ?? 'John Doe' ?>" required>
                                        </div>

                                        <div class="col-md-6 form-group mt-3">
                                            <label class="control-label"><?= $page_lang->email_address ?? 'Email Address' ?> <span class="mandatory">*</span></label>
                                            <input type="email" class="form-control" name="email" id="emailaddress" placeholder="<?= $page_lang->email_placeholder ?? 'name@example.com' ?>" required>
                                        </div>

                                        <div class="col-md-12 form-group mt-3">
                                            <label class="control-label"><?= $page_lang->subject ?? 'Subject' ?> <span class="mandatory">*</span></label>
                                            <input type="text" class="form-control" name="subject" id="sendersubject" placeholder="<?= $page_lang->subject_placeholder ?? 'How can we help?' ?>" required>
                                        </div>

                                        <div class="col-md-12 form-group mt-3">
                                            <label class="control-label"><?= $page_lang->message ?? 'Message' ?> <span class="mandatory">*</span></label>
                                            <textarea rows="4" class="form-control" id="sendermessage" name="message" required placeholder="<?= $page_lang->message_placeholder ?? 'Write your message here...' ?>" maxlength="500"></textarea>
                                        </div>

                                        <div class="col-md-12 form-group mt-4">
                                            <input type="hidden" name="<?= $csrf->name; ?>" value="<?= $csrf->hash; ?>" />
                                            
                                            <button class="next-btn16 hover-btn" id="contact_form_btn" type="submit">
                                                <?= $page_lang->submit_request ?? 'Submit Request' ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
	(function () {
		var form = document.getElementById('contact_form');
		var btn = document.getElementById('contact_form_btn');
		var thank = document.getElementById('contactThank');
		var error = document.getElementById('error');
		if (!form || !btn) return;

		if (thank) thank.style.display = 'none';
		if (error) error.style.display = 'none';

		var url = '<?= base_url("customer/contact/ajax_cform") ?>';
		var originalText = btn.textContent;

		function showError(msg) {
			if (!error) return;
			error.innerHTML = msg || '<?= addslashes($page_lang->something_went_wrong ?? 'Something went wrong. Please check your connection.') ?>';
			error.style.display = 'block';
			setTimeout(function () {
				error.style.display = 'none';
			}, 3000);
		}

		function showSuccess(msg) {
			if (!thank) return;
			thank.innerHTML = '<div class="alert alert-success">' + (msg || '<?= addslashes($page_lang->success_message ?? 'Thank you contacting with us, will reach out you soon.') ?>') + '</div>';
			thank.style.display = 'block';
			setTimeout(function () {
				thank.style.display = 'none';
			}, 3000);
		}

		form.addEventListener('submit', async function (e) {
			e.preventDefault();
			if (error) error.style.display = 'none';

			btn.disabled = true;
			btn.textContent = '<?= addslashes($page_lang->sending ?? 'Sending...') ?>';

			try {
				var fd = new FormData(form);
				var resp = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
				var data = await resp.json();
				if (data && data.success) {
					showSuccess(data.msg);
					form.reset();
				} else {
					showError((data && data.msg) ? data.msg : '<?= addslashes($page_lang->form_not_submitted ?? 'Form is not submitted') ?>');
				}
			} catch (err) {
				showError();
			} finally {
				btn.disabled = false;
				btn.textContent = originalText;
			}
		});
	})();
</script>