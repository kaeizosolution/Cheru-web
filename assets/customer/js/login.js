 $(function () {
  "use strict";

  $(document).on("click", ".signin-filter-btn", function (e) {
    e.preventDefault();
    showAuthSidebar('signin');
  });

  $(document).on("click", ".signup-filter-btn", function (e) {
    e.preventDefault();
    showAuthSidebar('signup');
  });

  $(document).on("click", ".cart-filter-btn", function (e) {
    e.preventDefault();
    // Usually standard sidebar logic handles this, but we can call it if needed
  });

  $(document).on("click", ".sidebar-close-icon", function (e) {
    e.preventDefault();
    hideAuthSidebars();
  });

  $(document).on("click", ".login-form-btn", function (e) {
    e.preventDefault();
    showAuthSidebar('signin');
  });

  $(document).on("click", ".hiddenbar-body-ovelay", function (e) {
    e.preventDefault();
    hideAuthSidebars();
  });

  // --- MOBILE/EMAIL TOGGLE ---
  $(document).on('click', '.toggle-login-method', function(e){
    e.preventDefault();
    var $btn = $(this);
    var target = $btn.data('target');
    var labelTarget = $btn.data('label');
    
    var $input = $(target);
    var $label = $(labelTarget);
    
    // Find the main form title (heading)
    var $form = $btn.closest('form');
    var $title = $form.find('.title').first();
    if (!$title.length) {
        $title = $btn.closest('.signin-hidden-sbar, .signup-hidden-sbar, .log_reg_form, section').find('.title, h2.title, h4.title').first();
    }
    
    // Check if we are in a Signup context
    var isSignup = ($form.attr('id') === 'signup-form') || ($btn.closest('.signup-hidden-sbar').length > 0) || (window.location.pathname.indexOf('register') !== -1);

    if (!$input.length) return;

    var mode = ($input.attr('data-login-mode') || 'mobile');
    var next = (mode === 'mobile') ? 'email' : 'mobile';
    $input.attr('data-login-mode', next);

    if (next === 'email') {
      $input.attr('placeholder', 'Email address');
      if ($label.length) $label.text('Email');
      if ($title.length) $title.text(isSignup ? 'Create Account with Email' : 'Email Sign-In');
      $btn.text('Use mobile instead');
    } else {
      $input.attr('placeholder', 'Mobile number');
      if ($label.length) $label.text('Mobile number');
      if ($title.length) $title.text(isSignup ? 'Create Account' : 'Sign-In');
      $btn.text('Use email instead');
    }
  });


  // --- FORM SUBMIT ---
  $(document).on("submit", "#login-form, #signup-form", function (e) {
    e.preventDefault();
    var $form = $(this);
    window._activeAuthForm = $form;
    var btnSelector = $form.attr("id") === "login-form" ? ".login-btn" : ".btn-signup";

    // Enforce mobile/email mode only for login form
    if ($form.attr('id') === 'login-form') {
      var $idInput = $form.find('input[name="email_mobile"]');
      if ($idInput.length) {
        var mode = ($idInput.attr('data-login-mode') || 'mobile');
        var val = ($idInput.val() || '').trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var phoneRegex = /^[0-9+]{8,15}$/;
        var ok = true;
        if (mode === 'mobile') {
          ok = phoneRegex.test(val);
        } else {
          ok = emailRegex.test(val);
        }
        if (!ok) {
          var msg = (mode === 'mobile') ? 'Please enter correct mobile number to login' : 'Please enter correct email to login';
          showAuthMessage(msg, 'error');
          return;
        }
      }
    }

    // Basic signup validation
    if ($form.attr('id') === 'signup-form') {
      var mob = ($form.find('input[name="mobile"]').val() || '').trim();
      var em = ($form.find('input[name="email"]').val() || '').trim();
      var emailRegex2 = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      var phoneRegex2 = /^[0-9+]{8,15}$/;
      if (!phoneRegex2.test(mob)) {
        showAuthMessage('Please enter correct mobile number to register', 'error');
        return;
      }
      if (!emailRegex2.test(em)) {
        showAuthMessage('Please enter correct email to register', 'error');
        return;
      }
    }

    if (typeof $form.parsley === "function" && !$form.parsley().isValid()) {
      return;
    }

    processSubmit($form, btnSelector);
  });
});

function hideAuthSidebars(){
  $('body').removeClass('signin-hidden-sidebar-content singup-hidden-sidebar-content');
}

function showAuthSidebar(type){
  hideAuthSidebars();
  if (type === 'signup') {
    $('body').addClass('singup-hidden-sidebar-content');
  } else {
    $('body').addClass('signin-hidden-sidebar-content');
  }
}

function processSubmit($form, btnSelector) {
  var post_data = $form.serializeArray();
  var formId = $form.attr('id') || '';
  var isSignup = (formId === 'signup-form');
  var apiUrl = isSignup ? '/api/v1/auth/register' : '/api/v1/auth/login';
  var apiPayload = {};
  try {
    (post_data || []).forEach(function(it){
      if (!it || !it.name) return;
      apiPayload[it.name] = it.value;
    });
  } catch (e) {}
  
  if (typeof loading === "function") {
    loading(btnSelector, true, "Processing...");
  }

  $.ajax({
    url: apiUrl,
    type: "POST",
    contentType: 'application/json; charset=utf-8',
    data: JSON.stringify(apiPayload),
    dataType: "json",
    success: function (resp) {
      if (typeof loading === "function") {
        loading(btnSelector, false, "CONTINUE");
      }
      // Normalize API response into legacy shape expected by handleResponse
      var status = (resp && resp.status !== undefined) ? resp.status : (resp && resp.STATUS !== undefined ? resp.STATUS : 0);
      var msg = (resp && (resp.msg || resp.MSG)) ? (resp.msg || resp.MSG) : (resp && resp.message ? resp.message : '');
      if (isSignup) {
        // API register returns {status:1} on success; legacy expected status=2
        if (String(status) === '1') {
          handleResponse({ status: 2, msg: msg || 'Account created! Please login', data: {} });
          return;
        }
        handleResponse({ status: 0, msg: msg || 'Registration failed', data: {} });
        return;
      }

      // login
      if (String(status) === '1') {
        // Persist tokens in localStorage so logout can revoke them via API
        try {
          if (resp.data && resp.data.access_token)  localStorage.setItem('access_token',  resp.data.access_token);
          if (resp.data && resp.data.refresh_token) localStorage.setItem('refresh_token', resp.data.refresh_token);
        } catch (ex) {}
        handleResponse({ status: 3, msg: msg || 'Login successful! Redirecting...', data: { redirect_url: (resp.data && resp.data.redirect_url) ? resp.data.redirect_url : '/customer/profile' } });
        return;
      }
      handleResponse({ status: 0, msg: msg || 'Login failed', data: {} });
    },
    error: function (xhr) {
      if (typeof loading === "function") {
        loading(btnSelector, false, "CONTINUE");
      }
      var msg = "Something went wrong. Please try again.";
      try {
        if (xhr && xhr.responseText) {
          var json = JSON.parse(xhr.responseText);
          msg = json.message || json.msg || msg;
          if (json.errors && json.errors.length) {
            msg = json.errors[0] || msg;
          }
        }
      } catch (e) {}
      showAuthMessage(msg, 'error');
    }
  });
}

function handleResponse(resp) {
  var status = (resp.status !== undefined) ? resp.status : (resp.STATUS !== undefined ? resp.STATUS : null);
  var msg = resp.msg || resp.MSG || "";
  var redirect = resp.redirect || resp.REDIRECT || "";
  if (!redirect && resp.data && resp.data.redirect_url) {
    redirect = resp.data.redirect_url;
  }

  if (status == 1 || status == 3) {
    // Success - show green message then redirect
    showAuthMessage(msg || 'Login successful!', 'success');
    setTimeout(function() {
      if (redirect) {
        window.location.href = redirect;
      } else {
        window.location.reload();
      }
    }, 1500);
  } else if (status == 2) {
    // Signup success - show green message
    showAuthMessage(msg || 'Account created! Please login', 'success');

    try {
      $('#signup-form').trigger('reset');
    } catch (e) {}

    setTimeout(function () {
      if (window.location.pathname.indexOf('register') !== -1) {
        window.location.href = '/login';
        return;
      }
      showAuthSidebar('signin');
    }, 2000);
  } else {
    // Error - show red message
    showAuthMessage(msg || "Invalid credentials", 'error');
  }
}

function showAuthMessage(message, type) {
  try {
    type = type || 'error'; // 'error' = red, 'success' = green
    var bgColor = type === 'success' ? '#28a745' : '#dc3545';
    var textColor = '#ffffff';
    
    console.log('showAuthMessage called:', message, type);
    console.log('Body classes:', $('body').attr('class'));
    
    var $msgContainer = null;
    
    // 1. Try to find the message container related to the active form
    if (window._activeAuthForm && window._activeAuthForm.length) {
      var formId = window._activeAuthForm.attr('id');
      if (window._activeAuthForm.closest('.signin-hidden-sbar, .signup-hidden-sbar').length) {
        $msgContainer = formId === 'login-form' ? $('#login-sidebar-message') : $('#signup-sidebar-message');
      } else {
        $msgContainer = formId === 'login-form' ? $('#login-page-message') : $('#signup-page-message');
      }
    }
    
    // 2. Fallback based on current page URL if active form isn't set
    if (!$msgContainer || !$msgContainer.length) {
      if (window.location.pathname.indexOf('/login') !== -1) {
        $msgContainer = $('#login-page-message');
      } else if (window.location.pathname.indexOf('/register') !== -1) {
        $msgContainer = $('#signup-page-message');
      } else {
        $msgContainer = $('#login-sidebar-message');
        if (!$msgContainer.length) {
          $msgContainer = $('#signup-sidebar-message');
        }
      }
    }
    
    console.log('Message container found:', $msgContainer.length > 0);
    
    // If no container found, fallback to toast
    if (!$msgContainer || !$msgContainer.length) {
      console.log('No container found, using toast fallback');
      $('.auth-message-toast').remove();
      var toastHtml = '<div class="auth-message-toast" style="' +
        'position: fixed !important;' +
        'top: 80px !important;' +
        'left: 50% !important;' +
        'transform: translateX(-50%) !important;' +
        'background-color: ' + bgColor + ' !important;' +
        'color: ' + textColor + ' !important;' +
        'padding: 15px 30px !important;' +
        'border-radius: 8px !important;' +
        'font-size: 15px !important;' +
        'font-weight: 600 !important;' +
        'z-index: 2147483647 !important;' +
        'box-shadow: 0 4px 20px rgba(0,0,0,0.3) !important;' +
        'opacity: 1 !important;' +
        'transition: opacity 0.3s ease-in-out !important;' +
        'max-width: 400px !important;' +
        'text-align: center !important;' +
        'cursor: pointer !important;' +
        '">' + message + '</div>';
      $('body').append(toastHtml);
      var $toast = $('.auth-message-toast');
      $toast.on('click', function() {
        $(this).fadeOut(300, function() { $(this).remove(); });
      });
      setTimeout(function() {
        $toast.fadeOut(300, function() { $(this).remove(); });
      }, 6000);
      return;
    }
    
    // Show message in sidebar container
    console.log('Showing message in container:', message);
    
    // Use Bootstrap alert classes
    $msgContainer.attr('style', '').removeClass().addClass('alert ' + (type === 'success' ? 'alert-success' : 'alert-danger')).css({
      'display': 'block',
      'margin': '15px',
      'padding': '15px',
      'border-radius': '6px',
      'font-size': '14px'
    }).text(message);
    
    console.log('Message container after styling:', $msgContainer.css('display'));
    
    // Auto-dismiss after 6 seconds
    var dismissTimer = setTimeout(function() {
      $msgContainer.fadeOut(300, function() {
        $(this).css('display', 'none');
      });
    }, 6000);
    
    // Click to dismiss
    $msgContainer.off('click').on('click', function() {
      clearTimeout(dismissTimer);
      $(this).fadeOut(300, function() {
        $(this).css('display', 'none');
      });
    });
    
  } catch (e) {
    console.error('Error in showAuthMessage:', e);
    // Fallback to alert
    alert(message);
  }
}