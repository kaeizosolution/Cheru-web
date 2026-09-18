<?php
$cartCounts = get_cart_counts();
$cartQty = $cartCounts['total_items'];

// Load Footer Language File
$footer_lang = get_page_language_data('footer_page_lang');

$footerPages = array('about' => array(), 'support' => array(), 'services' => array());
try {
  if(isset($this->db) && $this->db->field_exists('footer_section', 'ec_page')){
    $rows = $this->db
      ->select('post_title, post_slug, footer_section')
      ->from('ec_page')
      ->where('enabled', '1')
      ->where('post_type', 'page')
      ->where_in('footer_section', array('about','support','services'))
      ->order_by('page_id', 'DESC')
      ->get()->result();
    if($rows){
      foreach($rows as $r){
        $sec = (string)$r->footer_section;
        if(!isset($footerPages[$sec])) continue;
        $footerPages[$sec][] = array(
          'title' => (string)$r->post_title,
          'url' => base_url('page/'.(string)$r->post_slug),
        );
      }
    }
  }
} catch (Exception $e) {
}
?>		
	<!-- Our Footer -->
    <section class="footer_one home1 bdrt1">
      <div class="container pb60">
        <div class="row">
          <div class="col-lg-6 offset-lg-3">
            <div class="mailchimp_widget mb30-md text-center">
              <div class="icon float-start"><span class="flaticon-email-1"></span></div>
              <div class="details">
                <h3 class="title"><?= $footer_lang->subscribe_title ?? 'Subscribe and get 20% discount.' ?></h3>
              </div>
            </div>
            <div class="footer_social_widget">
              <form class="footer_mailchimp_form" id="subscribe_form">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <input type="email" class="form-control" name="subscribe_email" placeholder="<?= $footer_lang->enter_your_email_id ?? 'Your email address' ?>" required>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <button class="ms-sm-2 btn-thm subscribe-btn" type="submit"><?= $footer_lang->subscribe ?? 'Subscribe' ?></button>
                    <span class="ms-2" id="subscribeStatus" style="color:#dc3545; font-weight:600;"></span>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="row mt60">
          <div class="col-sm-6 col-md-5 col-lg-3 col-xl-3">
            <div class="footer_contact_widget">
              <h4><?= $footer_lang->contact_us ?? 'Contact Us' ?></h4>
              <div class="footer_contact_iconbox d-flex mb-4">
                <div class="icon"><span class="flaticon-phone-call"></span></div>
                <div class="details ms-4">
                  <h5 class="title"><?= $footer_lang->office_hours ?? 'Monday-Friday: 08am-9pm' ?></h5>
                  <a href="#">+(1) 123 456 7890</a></div>
              </div>
              <div class="footer_contact_iconbox d-flex">
                <div class="icon"><span class="flaticon-email"></span></div>
                <div class="details ms-4">
                  <h5 class="title"><?= $footer_lang->need_help ?? 'Need help with your order?' ?></h5>
                  <a href="#">support@cheruapp.com</a></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 col-lg-2 col-xl-2">
            <div class="footer_qlink_widget">
              <h4><?= $footer_lang->about_us ?? 'About Cheru' ?></h4>
              <ul class="list-unstyled">
                <?php if(!empty($footerPages['about'])){ ?>
                  <?php foreach($footerPages['about'] as $p){ ?>
                    <li><a href="<?= $p['url']; ?>"><?= $p['title']; ?></a></li>
                  <?php } ?>
                <?php } else { ?>
                  <li><a href="#">Track Your Order</a></li>
                  <li><a href="#">Product Guides</a></li>
                  <li><a href="#">Wishlists</a></li>
                  <li><a href="#">Privacy Policy</a></li>
                  <li><a href="#">Store Locator</a></li>
                <?php } ?>
              </ul>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 col-lg-2 col-xl-2">
            <div class="footer_qlink_widget">
              <h4><?= $footer_lang->customer_services ?? 'Customer Support' ?></h4>
              <ul class="list-unstyled">
                <?php if(!empty($footerPages['support'])){ ?>
                  <?php foreach($footerPages['support'] as $p){ ?>
                    <li><a href="<?= $p['url']; ?>"><?= $p['title']; ?></a></li>
                  <?php } ?>
                <?php } else { ?>
                  <li><a href="/contact"><?= $footer_lang->contact_us ?? 'Contact Us' ?></a></li>
                  <li><a href="#"><?= $footer_lang->help_center ?? 'Help Centre' ?></a></li>
                  <li><a href="#"><?= $footer_lang->returns_exchanges ?? 'Returns &amp; Exchanges' ?></a></li>
                  <li><a href="#"><?= $footer_lang->financing ?? 'Financing' ?></a></li>
                  <li><a href="#"><?= $footer_lang->gift_card ?? 'Gift Card' ?></a></li>
                <?php } ?>
              </ul>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 col-lg-2 col-xl-2">
            <div class="footer_qlink_widget">
              <h4><?= $footer_lang->useful_links ?? 'Services' ?></h4>
              <ul class="list-unstyled">
                <?php if(!empty($footerPages['services'])){ ?>
                  <?php foreach($footerPages['services'] as $p){ ?>
                    <li><a href="<?= $p['url']; ?>"><?= $p['title']; ?></a></li>
                  <?php } ?>
                <?php } else { ?>
                  <li><a href="#">Geek Squad</a></li>
                  <li><a href="#">In-Home Advisor</a></li>
                  <li><a href="#">Trade-In Program</a></li>
                  <li><a href="#">Electronics Recycling</a></li>
                  <li><a href="#">Best Buy Health</a></li>
                <?php } ?>
              </ul>
            </div>
          </div>
          <div class="col-sm-8 col-md-5 col-lg-3 col-xl-3">
            <div class="footer_social_widget">
              <h4 class="title"><?= $footer_lang->follow_us ?? 'Follow us' ?></h4>
              <div class="social_icon_list mt30">
                <ul class="mb20">
                  <li class="list-inline-item"><a href="#"><i class="fab fa-facebook"></i></a></li>
                  <li class="list-inline-item"><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                  <li class="list-inline-item"><a href="#"><i class="fab fa-instagram"></i></a></li>
                  <li class="list-inline-item"><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                </ul>
              </div>
            </div>
            <div class="footer_mobile_app_widget mb25">
              <h4 class="title mb10"><?= $footer_lang->mobile_apps ?? 'Mobile Apps' ?></h4>
              <div class="mobile_app_list">
                <ul class="mb0">
                  <li><a href="#"><span class="flaticon-apple"></span><?= $footer_lang->ios_app ?? 'iOS App' ?></a></li>
                  <li><a href="#"><span class="flaticon-android"></span><?= $footer_lang->android_app ?? 'Android App' ?></a></li>
                </ul>
              </div>
            </div>
            <div class="footer_acceped_card_widget">
              <h4 class="title mb20"><?= $footer_lang->we_accept ?? 'We accept' ?></h4>
              <div class="acceped_card_list">
                <ul class="d-flex mb-0">
                  <li class="me-2"><a href="#"><img src="/assets/customer/images/resource/visa-card.png" alt="visa-card"></a></li>
                  <li class="me-2"><a href="#"><img src="/assets/customer/images/resource/master-card.png" alt="master-card"></a></li>
                  <li class="me-2"><a href="#"><img src="/assets/customer/images/resource/apple-pay.png" alt="apple-pay"></a></li>
                  <li class="me-2"><a href="#"><img src="/assets/customer/images/resource/discover-card.png" alt="discover-card"></a></li>
                  <li class="me-2"><a href="#"><img src="/assets/customer/images/resource/paypal.png" alt="paypal"></a></li>
                  <li><a href="#"><img src="/assets/customer/images/resource/amex-card.png" alt="amex-card"></a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container bdrt1 pt20 pb20">
        <div class="row">
          <div class="col-lg-6">
            <div class="copyright-widget text-center text-lg-start d-block d-lg-flex mb15-md">
              <p class="me-4"><?= $footer_lang->copyright ?? '© 2025 cheruapp. All Rights Reserved' ?></p>
              <p><a href="#"><?= $footer_lang->privacy ?? 'Privacy' ?></a>·<a href="#"><?= $footer_lang->terms ?? 'Terms' ?></a>·<a href="#"><?= $footer_lang->sitemap ?? 'Sitemap' ?></a></p>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="footer_bottom_right_widgets text-center text-lg-end">
              <ul class="mb0">
                <li class="list-inline-item mb20-340">
                  <?php
                  $_CI_ft =& get_instance();
                  $_ft_currencies = $_CI_ft->db
                      ->select('currency_id, name, iso_code, symbol, rate, basic')
                      ->from('ec_currency')
                      ->where('status', '1')
                      ->order_by('basic', 'desc')
                      ->order_by('name', 'asc')
                      ->get()->result();
                  $_ft_cur_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
                  $_ft_cur = null;
                  foreach ($_ft_currencies as $_fc) {
                      if ((int)$_fc->currency_id === $_ft_cur_id) {
                          $_ft_cur = $_fc;
                          break;
                      }
                  }
                  if (!$_ft_cur && !empty($_ft_currencies)) {
                      foreach ($_ft_currencies as $_fc) {
                          if ($_fc->basic == 1) { $_ft_cur = $_fc; break; }
                      }
                      if (!$_ft_cur) $_ft_cur = $_ft_currencies[0];
                  }
                  ?>
                  <?php if (!empty($_ft_currencies)): ?>
                  <select id="footer-currency-select" class="selectpicker show-tick">
                    <?php foreach ($_ft_currencies as $_fcur): ?>
                      <option value="<?= (int)$_fcur->currency_id ?>" data-symbol="<?= htmlspecialchars((string)$_fcur->symbol) ?>" data-rate="<?= (float)$_fcur->rate ?>" <?= ((int)$_fcur->currency_id === (int)($_ft_cur ? $_ft_cur->currency_id : 0)) ? 'selected' : '' ?>>
                        Currency : <?= htmlspecialchars($_fcur->iso_code) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <?php else: ?>
                  <select class="selectpicker show-tick">
                    <option>Currency : USD</option>
                  </select>
                  <?php endif; ?>
                </li>
                <li class="list-inline-item">
                  <?php
                  // --- Language Dropdown with guaranteed 8-language fallback ---
                  $_CI_ft2 =& get_instance();
                  $_ft_languages_db = array();
                  try {
                      if (isset($_CI_ft2->db)) {
                          $_ft_lang_rows = $_CI_ft2->db
                              ->select('language_id, name, iso_code, basic')
                              ->from('ec_language')
                              ->where('status', '1')
                              ->order_by('basic', 'desc')
                              ->order_by('name', 'asc')
                              ->get()->result();
                          if ($_ft_lang_rows) {
                              foreach ($_ft_lang_rows as $_lr) {
                                  $_ft_languages_db[(int)$_lr->language_id] = $_lr;
                              }
                          }
                      }
                  } catch (Exception $_le) {}

                  // Hardcoded fallback list — always shown even if DB is missing entries
                  $_ft_lang_fallback = array(
                      3  => array('language_id' => 3,  'name' => 'English',    'iso_code' => 'en', 'basic' => 1),
                      12 => array('language_id' => 12, 'name' => 'French',     'iso_code' => 'fr', 'basic' => 0),
                      13 => array('language_id' => 13, 'name' => 'Spanish',    'iso_code' => 'es', 'basic' => 0),
                      14 => array('language_id' => 14, 'name' => 'Canadian',   'iso_code' => 'ca', 'basic' => 0),
                      15 => array('language_id' => 15, 'name' => 'Chinese',    'iso_code' => 'zh', 'basic' => 0),
                      16 => array('language_id' => 16, 'name' => 'German',     'iso_code' => 'de', 'basic' => 0),
                      17 => array('language_id' => 17, 'name' => 'Indonesian', 'iso_code' => 'id', 'basic' => 0),
                      18 => array('language_id' => 18, 'name' => 'Japanese',   'iso_code' => 'ja', 'basic' => 0),
                      19 => array('language_id' => 19, 'name' => 'Korean',     'iso_code' => 'ko', 'basic' => 0),
                  );

                  // Merge: DB rows take priority; fallback fills in missing languages
                  $_ft_languages = array();
                  foreach ($_ft_lang_fallback as $_lid => $_ldef) {
                      if (isset($_ft_languages_db[$_lid])) {
                          $_ft_languages[] = $_ft_languages_db[$_lid];
                      } else {
                          // Build a stdClass to match DB result objects
                          $_lo = new stdClass();
                          $_lo->language_id = $_ldef['language_id'];
                          $_lo->name        = $_ldef['name'];
                          $_lo->iso_code    = $_ldef['iso_code'];
                          $_lo->basic       = $_ldef['basic'];
                          $_ft_languages[]  = $_lo;
                      }
                  }

                  // Determine currently selected language
                  $_ft_ln_id = isset($_SESSION['ln']) ? (int)$_SESSION['ln'] : 3;
                  $_ft_ln = null;
                  foreach ($_ft_languages as $_fl) {
                      if ((int)$_fl->language_id === $_ft_ln_id) {
                          $_ft_ln = $_fl;
                          break;
                      }
                  }
                  if (!$_ft_ln) {
                      // Default to English
                      foreach ($_ft_languages as $_fl) {
                          if ((int)$_fl->language_id === 3) { $_ft_ln = $_fl; break; }
                      }
                      if (!$_ft_ln) $_ft_ln = $_ft_languages[0];
                  }
                  ?>
                  <select id="footer-language-select" class="selectpicker show-tick">
                    <?php foreach ($_ft_languages as $_flang): ?>
                      <option value="<?= (int)$_flang->language_id ?>" <?= ((int)$_flang->language_id === (int)($_ft_ln ? $_ft_ln->language_id : 3)) ? 'selected' : '' ?>>
                        Language : <?= htmlspecialchars(ucfirst((string)$_flang->name)) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
        <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a> </div>
</div>
<!-- Wrapper End --> 
<script src="/assets/customer/js/jquery-3.6.0.js"></script> 
<script src="/assets/customer/js/jquery-migrate-3.0.0.min.js"></script> 
<script src="<?= base_url('assets/customer/js/cart.js?v='.(defined('FCPATH') ? @filemtime(FCPATH.'assets/customer/js/cart.js') : time())); ?>"></script>
<script src="<?= base_url('assets/common/js/snackbar.js'); ?>"></script>
<script src="<?= base_url('assets/common/js/common.js'); ?>"></script>
<script src="<?= base_url('assets/customer/js/add_to_cart.js?v='.(defined('FCPATH') ? @filemtime(FCPATH.'assets/customer/js/add_to_cart.js') : time())); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
<script src="/assets/customer/js/popper.min.js"></script> 
<script src="/assets/customer/js/bootstrap.min.js"></script> 
<script src="/assets/customer/js/bootstrap-select.min.js"></script> 
<script src="/assets/customer/js/jquery.mmenu.all.js"></script> 
<script src="/assets/customer/js/ace-responsive-menu.js"></script> 
<script src="/assets/customer/js/jquery-scrolltofixed-min.js"></script> 
<script src="/assets/customer/js/wow.min.js"></script> 
<script src="/assets/customer/js/slider.js"></script> 
<script src="<?= base_url('assets/customer/js/login.js?v='.(defined('FCPATH') ? @filemtime(FCPATH.'assets/customer/js/login.js') : time())); ?>"></script>
<!-- Custom script for all pages --> 
<script src="<?= base_url('assets/customer/js/script.js?v='.(defined('FCPATH') ? @filemtime(FCPATH.'assets/customer/js/script.js') : time())); ?>"></script>
    <script>

      var base_url = "<?= base_url(); ?>";
      // Global currency vars — used by all JS price formatters across the site
      window.currentCurrencySymbol = "<?= (isset($_ft_cur) && $_ft_cur && !empty($_ft_cur->symbol)) ? addslashes(html_entity_decode((string)$_ft_cur->symbol)) : '$' ?>";
      window.currentCurrencyRate   = <?= (isset($_ft_cur) && $_ft_cur && is_numeric($_ft_cur->rate)) ? (float)$_ft_cur->rate : 1.0 ?>;

      // API-driven currency switcher
      // Binds to both native 'change' AND bootstrap-select's 'changed.bs.select' event.
      // Calls POST api/v1/currency/switch (visible in Network tab), then reloads so
      // PHP-rendered prices refresh with the new session currency.
      (function () {
        var _switching = false; // debounce: prevent double-fire from both events

        function doSwitchCurrency($sel) {
          if (_switching) return;
          var currency_id = parseInt($sel.val(), 10) || 0;
          if (currency_id <= 0) return;
          var newSymbol = $sel.find('option:selected').data('symbol') || '$';
          var newRate   = parseFloat($sel.find('option:selected').data('rate')) || 1.0;

          _switching = true;

          var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
          $.ajax({
            url: base_url + 'api/v1/currency/set-currency',
            type: 'POST',
            dataType: 'json',
            // Send as form-data (same as Postman) — goes into $_POST on server.
            // JSON content-type is NOT used because CI may consume php://input before the method runs.
            headers: token ? { 'Authorization': 'Bearer ' + token } : {},
            data: { currency_id: currency_id },
            success: function (resp) {
              var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
              if (STATUS == 1) {
                // Update live currency globals from API response
                var c = (resp.data && resp.data.currency) ? resp.data.currency : (resp.currency || null);
                window.currentCurrencySymbol = (c && c.symbol) ? String(c.symbol) : newSymbol;
                window.currentCurrencyRate   = (c && parseFloat(c.rate) > 0) ? parseFloat(c.rate) : newRate;
                window.currency_symbol = window.currentCurrencySymbol;
                window.currency_rate   = window.currentCurrencyRate;
                // Reload so all server-rendered PHP prices reflect the new session currency
                window.location.reload();
              } else {
                _switching = false;
                console.error('Currency switch failed:', resp);
              }
            },
            error: function (xhr) {
              _switching = false;
              console.error('Currency switch error:', xhr.status, xhr.responseText);
            }
          });
        }

        // Native select change (works when selectpicker not active or as fallback)
        $(document).on('change', '#footer-currency-select', function () {
          doSwitchCurrency($(this));
        });

        // bootstrap-select canonical event (fired when user picks from the custom dropdown UI)
        $(document).on('changed.bs.select', '#footer-currency-select', function () {
          doSwitchCurrency($(this));
        });
      })();

      // API-driven language switcher
      (function () {
        var _lang_switching = false;

        function doSwitchLanguage($sel) {
          if (_lang_switching) return;
          var language_id = parseInt($sel.val(), 10) || 0;
          if (language_id <= 0) return;

          _lang_switching = true;

          var token = (typeof window.customer_access_token !== 'undefined') ? String(window.customer_access_token || '') : '';
          $.ajax({
            url: base_url + 'api/v1/language/set-language',
            type: 'POST',
            dataType: 'json',
            headers: token ? { 'Authorization': 'Bearer ' + token } : {},
            data: { language_id: language_id },
            success: function (resp) {
              var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
              if (STATUS == 1) {
                window.location.reload();
              } else {
                _lang_switching = false;
                console.error('Language switch failed:', resp);
              }
            },
            error: function (xhr) {
              _lang_switching = false;
              console.error('Language switch error:', xhr.status, xhr.responseText);
            }
          });
        }

        $(document).on('change', '#footer-language-select', function () {
          doSwitchLanguage($(this));
        });

        $(document).on('changed.bs.select', '#footer-language-select', function () {
          doSwitchLanguage($(this));
        });
      })();


	  if (window.jQuery && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.parsley === 'function') {
		jQuery(function () {
			jQuery('form').parsley();
		});
	  }

          function increaseQty(button) {
            const input = button.parentElement.querySelector('input');
            input.value = parseInt(input.value) + 1;
        }

        function decreaseQty(button) {
            const input = button.parentElement.querySelector('input');
            if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            }
        }

        

	  (function () {
			var form = document.getElementById('subscribe_form');
			if (!form) return;

			var btn = form.querySelector('.subscribe-btn');
			var statusEl = document.getElementById('subscribeStatus');
			var url = '<?= base_url("welcome/subscription") ?>';

			function setStatus(text) {
				if (statusEl) {
					statusEl.textContent = text || '';
				}
			}

			form.addEventListener('submit', async function (e) {
				e.preventDefault();

				setStatus('');
				if (btn) {
					btn.disabled = true;
					btn.textContent = 'Processing...';
				}

				try {
					var fd = new FormData(form);
					var resp = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
					var data = await resp.json();
					var STATUS = (typeof data.STATUS !== 'undefined') ? data.STATUS : data.status;
					var MSG = data.MSG || data.msg || '';

					if (STATUS == 1) {
						setStatus((MSG && MSG.toLowerCase().includes('already')) ? 'Already Subscribed' : 'Thanks for subscription');
						form.reset();
					} else {
						setStatus(MSG || 'Subscription failed');
					}
				} catch (err) {
					setStatus('Subscription failed');
				} finally {
					if (btn) {
						btn.disabled = false;
						btn.textContent = 'Subscribe';
					}
				}
			});
	  })();

	  if (window.jQuery) {
		jQuery(document).on('hidden.bs.modal', '.video-modal', function (e) {
			const $modal = jQuery(this);
			const $iframe = $modal.find('iframe');
			if ($iframe.length) {
				const src = $iframe.attr('src');
				$iframe.attr('src', '');
				$iframe.attr('src', src);
			}
		});
	  }
    

// Automatically stop YouTube video on modal close
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('hidden.bs.modal', function () {
            const iframe = modal.querySelector('iframe');
            if (iframe) {
                const src = iframe.src;
                iframe.src = '';       // Reset to stop video
                iframe.src = src;      // Restore src if reopened
            }
            });
        });
    </script>
<!-- Auto-logout on session expiry -->
<script>
(function () {
    var SESSION_PING_URL = "<?= base_url('customer/check_session'); ?>";
    var _redirecting = false;

    function doSessionLogout() {
        if (_redirecting) return;
        _redirecting = true;

        // Clear client-side tokens
        try {
            localStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            sessionStorage.removeItem('access_token');
            sessionStorage.removeItem('refresh_token');
        } catch (ex) {}

        // Redirect to server-side logout to destroy cart and PHP session
        window.location.href = "<?= base_url('logout'); ?>";
    }

    function isSessionExpiredResponse(data) {
        if (!data) return false;
        var status = (typeof data.STATUS !== 'undefined') ? parseInt(data.STATUS) :
                     (typeof data.status !== 'undefined' ? parseInt(data.status) : null);
        if (status === null) return false;
        var msg = '';
        if (data.MSG)  msg = Array.isArray(data.MSG) ? data.MSG.join(' ') : String(data.MSG);
        if (data.msg)  msg = msg || String(data.msg);
        return (status === 0 && msg.toLowerCase().indexOf('login required') !== -1);
    }

    if (window.jQuery) {
        // Catch HTTP 401 on any AJAX call
        $(document).ajaxError(function (event, xhr, settings) {
            if (xhr.status === 401) {
                if (settings && settings.url && settings.url.indexOf('/api/v1/auth/login') !== -1) {
                    return;
                }
                doSessionLogout();
            }
        });

        // Catch JSON "login required" responses
        $(document).ajaxSuccess(function (event, xhr, settings, data) {
            if (isSessionExpiredResponse(data)) { doSessionLogout(); }
        });

        // Periodic session ping every 5 minutes (runs on any page, but ONLY if customer is logged in)
        var isCustomerLoggedIn = (typeof window.customer_access_token !== 'undefined' && window.customer_access_token !== '');
        if (isCustomerLoggedIn) {
            setInterval(function () {
                $.get(SESSION_PING_URL).done(function (resp) {
                    if (resp && (resp.status === 0 || resp.STATUS === 0)) { doSessionLogout(); }
                });
            }, 5 * 60 * 1000);
        }
    }
})();
</script>
<script>
/**
 * customerApiLogout()
 * Called when the logout button is clicked.
 * Sends the refresh_token to the API logout endpoint so the token is revoked
 * and the PHP session (including currency) is cleared server-side.
 * Then redirects to home. Falls back to the web /logout route on any error.
 */
function customerApiLogout(e) {
    if (e) e.preventDefault();

    var HOME_URL  = "<?= base_url(); ?>";
    var API_URL   = "<?= base_url('api/v1/auth/logout'); ?>";
    var FALLBACK  = "<?= base_url('/logout'); ?>";

    // Read stored tokens (set by login.js into localStorage / sessionStorage)
    var refreshToken = '';
    try {
        refreshToken = localStorage.getItem('refresh_token') || sessionStorage.getItem('refresh_token') || '';
    } catch (ex) {}

    $.ajax({
        url: API_URL,
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ refresh_token: refreshToken }),
        dataType: 'json',
        complete: function () {
            // Whether success or error, clear local tokens and go home
            try {
                localStorage.removeItem('access_token');
                localStorage.removeItem('refresh_token');
                sessionStorage.removeItem('access_token');
                sessionStorage.removeItem('refresh_token');
            } catch (ex) {}
            window.location.href = HOME_URL;
        }
    });
}
</script>
</body>

</html>