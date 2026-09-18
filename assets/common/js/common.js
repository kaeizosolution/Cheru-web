var ajaxFunc = function(url, data, successFun, errorFun=null){
	if (typeof url === 'string' && url.charAt(0) === '/') {
		var prefix = null;
		if (typeof window.site_url !== 'undefined' && window.site_url) {
			prefix = String(window.site_url);
		} else if (typeof window.base_url !== 'undefined' && window.base_url) {
			prefix = String(window.base_url);
		}
		if (prefix) {
			url = prefix.replace(/\/+$/, '') + url;
		}
	}
	if (data && typeof data === 'object') {
		var csrfKey = null;
		var csrfVal = null;
		if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
			csrfKey = window.csrf_token_name;
			csrfVal = window.csrf_hash;
		} else if (typeof window.csrfName !== 'undefined' && typeof window.csrfHash !== 'undefined') {
			csrfKey = window.csrfName;
			csrfVal = window.csrfHash;
		}
		if (csrfKey && csrfVal) {
			if (typeof FormData !== 'undefined' && data instanceof FormData) {
				if (!data.has(csrfKey)) {
					data.append(csrfKey, csrfVal);
				}
			} else if (typeof data[csrfKey] === 'undefined') {
				data[csrfKey] = csrfVal;
			}
		}
	}
	jQuery.ajax({
		url: url,
		type: "POST",
		data: data,
		dataType: "json",
		success: function(resp){
			if (resp && typeof resp.csrf_hash !== 'undefined') {
				window.csrf_hash = resp.csrf_hash;
			}
			if (typeof successFun === 'function') successFun(resp);
		},
		error: errorFun
	});
}

var ajaxFileFunc = function(url, data, successFun, errorFun=null){
	if (typeof url === 'string' && url.charAt(0) === '/') {
		var prefix = null;
		if (typeof window.site_url !== 'undefined' && window.site_url) {
			prefix = String(window.site_url);
		} else if (typeof window.base_url !== 'undefined' && window.base_url) {
			prefix = String(window.base_url);
		}
		if (prefix) {
			url = prefix.replace(/\/+$/, '') + url;
		}
	}
	if (data && typeof data === 'object') {
		var csrfKey = null;
		var csrfVal = null;
		if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
			csrfKey = window.csrf_token_name;
			csrfVal = window.csrf_hash;
		} else if (typeof window.csrfName !== 'undefined' && typeof window.csrfHash !== 'undefined') {
			csrfKey = window.csrfName;
			csrfVal = window.csrfHash;
		}
		if (csrfKey && csrfVal) {
			if (typeof FormData !== 'undefined' && data instanceof FormData) {
				if (!data.has(csrfKey)) {
					data.append(csrfKey, csrfVal);
				}
			} else if (typeof data[csrfKey] === 'undefined') {
				data[csrfKey] = csrfVal;
			}
		}
	}
	jQuery.ajax({
		url: url,
		type: "POST",
		data: data,
		dataType: "json",
		success: function(resp){
			if (resp && typeof resp.csrf_hash !== 'undefined') {
				window.csrf_hash = resp.csrf_hash;
			}
			if (typeof successFun === 'function') successFun(resp);
		},
		error: errorFun
	});
}

var ajax_with_file_func = function(url, data, successFun, errorFun = null) {
        jQuery.ajax({url: url, type: "POST", data: data, dataType: "json", processData: false,contentType: false, success: successFun, error: errorFun});
};

var ajax_with_upload_func = function(url, data, successFun, errorFun = null, alwaysFun = null) {
    jQuery.ajax({
        url: url,
        type: "POST",
        data: data,
        dataType: "json",
        processData: false,
        contentType: false,
        success: successFun,
        error: errorFun
    }).always(function () {
        if (typeof alwaysFun === 'function') {
            alwaysFun();
        }
    });
};


function show_hide_load(first, sec){$('.'+first).show();$('.'+sec).hide();}

$(document).ready(function(){
});

function loading(selector, isLoading, text) {
  const $btn = $(selector);
  if (isLoading) {
    $btn.prop('disabled', true).html(
      `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`
    );
  } else {
    $btn.prop('disabled', false).text(text);
  }
}
