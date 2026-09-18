
window.csrf_token_name = "<?= $this->security->get_csrf_token_name(); ?>";
window.csrf_hash = "<?= $this->security->get_csrf_hash(); ?>";

// Flash prevention: if the URL hash indicates a tab different from what PHP rendered,
// hide all tab-panes immediately so there's no visible flash of the wrong content.
(function() {
    try {
        var hash = window.location.hash ? window.location.hash.substring(1) : '';
        var phpTab = '<?= $active_tab ?>';
        // If we have a hash and it differs from what PHP rendered as active, hide all panes now
        if (hash && hash !== phpTab) {
            var style = document.createElement('style');
            style.id = 'tab-flash-prevent';
            style.textContent = '.tab-pane { display: none !important; }';
            document.head.appendChild(style);
            // Remove after JS routing takes over (safety net)
            window.addEventListener('load', function() {
                var el = document.getElementById('tab-flash-prevent');
                if (el) el.parentNode.removeChild(el);
            });
        }
    } catch(e) {}
})();

var wallet_has_bank = <?= (int)($wallet_has_bank ?? 0); ?>;

function walletWaitForJQuery(cb) {
	if (window.jQuery && typeof window.jQuery === 'function') {
		window.jQuery(cb);
		return;
	}
	setTimeout(function() {
		walletWaitForJQuery(cb);
	}, 50);
}

// SPA Tab Navigation
walletWaitForJQuery(function() {
    $(window).on('hashchange', function() {
        var hash = window.location.hash.substring(1);
        if (!hash) hash = 'dashboard';
        
        // Handle nested or special tabs
        var actualTab = hash;
        if (hash === 'wallet_transactions') {
            actualTab = 'walletTransactions';
        }

        var validTabs = ['dashboard', 'order', 'address', 'details', 'wishlist', 'wallet', 'walletTransactions', 'returns'];
        if (validTabs.indexOf(actualTab) === -1) {
            return;
        }

        // Hide all tabs
        $('.tab-pane').hide();
        
        // Show the target tab
        $('#' + actualTab + 'Div').show();

        // If tab changed, we can clean up the URL query params to avoid redundancy (?tab=dashboard#dashboard)
        if (window.location.search && window.location.search.indexOf('tab=') !== -1) {
            try {
                var newUrl = window.location.pathname + window.location.hash;
                window.history.replaceState(null, '', newUrl);
            } catch(e) {}
        }

        // Update active class on sidebar
        $('.account-nav li').removeClass('active');
        if (hash === 'wallet_transactions') {
             $('.nav-item-wallet').addClass('active');
        } else {
             $('.nav-item-' + hash).addClass('active');
        }

        // Trigger data loading if needed
        if (hash === 'order') {
            if (typeof load_order_list === 'function') load_order_list();
        } else if (hash === 'address') {
            if (typeof get_addresses === 'function') get_addresses();
        } else if (hash === 'wishlist') {
            if (typeof load_wishlist === 'function') load_wishlist();
        } else if (hash === 'dashboard') {
            if (typeof load_dashboard_stats === 'function') load_dashboard_stats();
        } else if (hash === 'returns') {
            if (typeof load_returns === 'function') load_returns();
        } else if (hash === 'wallet') {
            if (typeof load_wallet === 'function') load_wallet();
        } else if (hash === 'wallet_transactions') {
            if (typeof load_wallet_transactions === 'function') load_wallet_transactions();
        }
    });

    // Trigger on load
    var hash = window.location.hash.substring(1);
    var urlParams = new URLSearchParams(window.location.search);
    var tab = urlParams.get('tab');
    
    if (hash) {
        $(window).trigger('hashchange');
    } else if (tab) {
        window.location.hash = tab;
    } else {
        window.location.hash = 'dashboard';
        $(window).trigger('hashchange');
    }
});

// Wallet functions moved to global scope so hashchange listener can find them on load
function walletSetErr(id, msg) {
	$('#' + id).text(msg).show();
}
function walletClearErr(id) {
	$('#' + id).hide().text('');
}
function walletUpdateBalance(balance) {
	if (typeof balance !== 'undefined' && balance !== null) {
		var rate = window.currentCurrencyRate || 1.0;
		var symbol = window.currentCurrencySymbol || '₹';
		var converted = parseFloat(balance) * rate;
		var fmt = converted.toFixed(2);
		$('#walletBalanceSymbol').text(symbol);
		$('#walletBalanceAmt').text(fmt);
		$('#walletBalance').find('span').text(fmt);
	}
}

var _walletApiBase = '<?= base_url("api/v1/Wallet"); ?>';
var _walletToken   = '<?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>';

function walletApiGet(path, cb) {
	$.ajax({
		url: _walletApiBase + path,
		type: 'GET',
		headers: { 'Authorization': 'Bearer ' + _walletToken },
		dataType: 'json'
	}).done(function(r){ cb(r); }).fail(function(){ cb({ status: 0 }); });
}

function walletApiPost(path, data, cb) {
	$.ajax({
		url: _walletApiBase + path,
		type: 'POST',
		headers: { 'Authorization': 'Bearer ' + _walletToken, 'Content-Type': 'application/json' },
		data: JSON.stringify(data || {}),
		dataType: 'json'
	}).done(function(r){ cb(r); }).fail(function(){ cb({ status: 0, msg: 'Request failed.' }); });
}

function _renderTxnList(txns, containerId, showPrefix) {
	var $c = $('#' + containerId);
	if (!$c.length) return;
	if (!txns || txns.length === 0) {
		$c.html('<div class="wallet-item"><div class="d-flex justify-content-between"><div><h6 class="mb2 fz14 fw600">No transactions found</h6><p class="mb0 fz13 text-muted">Your transactions will appear here</p></div></div></div>');
		return;
	}
	var html = '';
	txns.forEach(function(tx) {
		var isCredit = (tx.type || '').toLowerCase() === 'credit';
		var title = isCredit ? 'Credit' : 'Debit';
		var cls   = isCredit ? 'wallet-credit' : 'wallet-debit';
		var date  = tx.created_at ? new Date(tx.created_at).toLocaleDateString(undefined, {day:'2-digit',month:'short',year:'numeric'}) : '';
		var mkey  = tx.created_at ? new Date(tx.created_at).toLocaleString('default',{month:'short'}).toLowerCase() : 'all';
		var prefix = showPrefix ? (isCredit ? '+ ' : '- ') : '';
		var rate = window.currentCurrencyRate || 1.0;
		var symbol = window.currentCurrencySymbol || '₹';
		var amt  = (parseFloat(tx.amount || 0) * rate).toFixed(2);
		html += '<div class="wallet-item" data-type="'+(isCredit?'credit':'debit')+'" data-month="'+mkey+'" data-name="'+title+'" onclick="openTransaction(this)">';
		html += '<div class="d-flex justify-content-between">';
		html += '<div><h6 class="mb2 fz14 fw600">'+title+'</h6><p class="mb0 fz13 text-muted">'+date+'</p></div>';
		html += '<span class="'+cls+'">'+prefix+symbol+' '+amt+'</span>';
		html += '</div></div>';
	});
	$c.html(html);
}

function load_wallet() {
	$('#walletRecentTxns').html('<div class="text-center text-muted py-3">Loading...</div>');
	walletApiGet('', function(r) {
		var d = r && r.data ? r.data : r;
		if (d && typeof d.balance !== 'undefined') walletUpdateBalance(d.balance);
		_renderTxnList(d && d.transactions ? d.transactions : [], 'walletRecentTxns', false);
	});
}

function load_wallet_transactions() {
	$('#allTransactionList').html('<div class="text-center text-muted py-3">Loading...</div>');
	walletApiGet('/transactions', function(r) {
		var d = r && r.data ? r.data : r;
		if (d && typeof d.balance !== 'undefined') walletUpdateBalance(d.balance);
		var txns = d && d.transactions ? d.transactions : [];
		_renderTxnList(txns, 'allTransactionList', true);
		// rebuild month filter options
		var months = {};
		txns.forEach(function(tx){
			if (tx.created_at) {
				var d2 = new Date(tx.created_at);
				var mk = d2.toLocaleString('default',{month:'short'}).toLowerCase();
				var ml = d2.toLocaleString('default',{month:'long'});
				months[mk] = ml;
			}
		});
		var $mf = $('#monthFilter');
		$mf.find('option:not([value="all"])').remove();
		Object.keys(months).forEach(function(k){ $mf.append('<option value="'+k+'">'+months[k]+'</option>'); });
	});
}

// (No IIFE needed, hashchange handles it)

function walletPrependTxn(type, amount) {
	var t = (type || '').toString().toLowerCase();
	var isCredit = (t === 'credit');
	var title = isCredit ? 'Credit' : 'Debit';
	var cls = isCredit ? 'wallet-credit' : 'wallet-debit';
	var dateText = '';
	try {
		dateText = new Date().toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
	} catch (e) {
		dateText = '';
	}
	var amt = parseFloat(amount || 0);
	if (!amt || amt <= 0) return;
	var symbol = window.currentCurrencySymbol || '₹';
	var htmlRecent = '';
	htmlRecent += '<div class="wallet-item">';
	htmlRecent += '<div class="d-flex justify-content-between">';
	htmlRecent += '<div><h6 class="mb2 fz14 fw600">' + title + '</h6><p class="mb0 fz13 text-muted">' + dateText + '</p></div>';
	htmlRecent += '<span class="' + cls + '">' + symbol + ' ' + amt.toFixed(2) + '</span>';
	htmlRecent += '</div></div>';

	$('#walletRecentTxns').prepend(htmlRecent);

	var $txList = $('#allTransactionList');
	if ($txList && $txList.length) {
		var prefix = isCredit ? '+ ' : '- ';
		var htmlAll = '<div class="wallet-item" data-type="' + (isCredit ? 'credit' : 'debit') + '" data-month="all" data-name="' + title + '" onclick="openTransaction(this)">';
		htmlAll += '<div class="d-flex justify-content-between">';
		htmlAll += '<div><h6 class="mb2 fz14 fw600">' + title + '</h6><p class="mb0 fz13 text-muted">' + dateText + '</p></div>';
		htmlAll += '<span class="' + cls + '">' + prefix + symbol + ' ' + amt.toFixed(2) + '</span>';
		htmlAll += '</div></div>';
		$txList.prepend(htmlAll);
	}
}

function walletReload() {
	var nextUrl = '<?= base_url('customer/profile?tab=wallet'); ?>';
	var cur = (typeof window.location !== 'undefined') ? (window.location.href || '') : '';
	if (cur.indexOf('tab=wallet_transactions') !== -1) {
		nextUrl = '<?= base_url('customer/profile?tab=wallet_transactions'); ?>';
	}
	window.location.href = nextUrl;
}

function walletPost(url, data, cb) {
	var postData = data || {};
	if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
		postData[window.csrf_token_name] = window.csrf_hash;
	}
	$.ajax({
		url: url,
		type: 'POST',
		data: postData,
		dataType: 'json'
	}).done(function(resp) {
		cb(resp);
	}).fail(function() {
		cb({ STATUS: 0, MSG: 'Request failed.' });
	});
}

function walletMaskAcc(num) {
	var s = (num || '').toString();
	if (s.length <= 4) return s;
	return '****' + s.slice(-4);
}

function walletSetPayErr(msg) {
	$('#payMethodErr').text(msg || '').show();
}
function walletClearPayErr() {
	$('#payMethodErr').hide().text('');
}

function walletRenderBankOptions(banks) {
	var $wrap = $('#bankOptions');
	$wrap.empty();
	if (!Array.isArray(banks) || banks.length === 0) {
		return;
	}
	banks.forEach(function(b, idx){
		var bankName = (b && b.bank_name) ? b.bank_name : 'Bank';
		var acc = walletMaskAcc(b && b.account_number ? b.account_number : '');
		var bid = b && b.id ? b.id : '';
		var active = (idx === 0) ? ' active' : '';
		var checked = (idx === 0) ? ' checked' : '';
		var html = '';
		html += '<div class="position-relative">';
		html += '<label class="btn btn-light text-start w-100' + active + '" style="border:1px solid #e5e7eb; border-radius:10px; padding:12px;">';
		html += '<input type="radio" name="pay_bank_id" class="me-2" value="' + bid + '"' + checked + '>';
		html += '<span class="fw600">' + bankName + '</span> <span class="text-muted">' + acc + '</span>';
		html += '</label>';
		html += '</div>';
		$wrap.append(html);
	});
}

function walletOpenPaymentModal(amount) {
	walletClearPayErr();
	$('#btnPayNow').data('amount', amount);
	var symbol = window.currentCurrencySymbol || '₹';
	$('#btnPayNow').text('Pay ' + symbol + parseFloat(amount || 0).toFixed(2));
	walletApiGet('/list_banks', function(resp){
		var d = resp && resp.data ? resp.data : resp;
		var banks = (d && d.banks) ? d.banks : [];
		if (!banks || banks.length === 0) {
			$('#bankOptions').empty();
			$('#noBankState').show();
			if (window.bootstrap && bootstrap.Modal) new bootstrap.Modal(document.getElementById('paymentMethodModal')).show();
			return;
		}
		$('#noBankState').hide();
		walletRenderBankOptions(banks);
		if (window.bootstrap && bootstrap.Modal) new bootstrap.Modal(document.getElementById('paymentMethodModal')).show();
	});
}

walletWaitForJQuery(function() {
	var symbol = window.currentCurrencySymbol || '₹';
	$('.chip-symbol').text(symbol);
	$('#addMoneyAmount').attr('placeholder', symbol + ' 2000');

$(document).on('change', 'input[name="pay_bank_id"]', function() {
	$('#bankOptions label').removeClass('active');
	$(this).closest('label').addClass('active');
});

$(document).on('click', '#btnOpenAddMoney', function() {
	walletClearErr('addMoneyErr');
	$('#addMoneyAmount').val('');
});

$(document).on('click', '.wallet-amount-chip', function() {
	var amt = parseFloat($(this).data('amount') || 0);
	if (amt > 0) {
		$('#addMoneyAmount').val(amt);
	}
});

$(document).on('click', '#btnOpenWithdraw', function() {
	walletClearErr('withdrawErr');
	$('#withdrawAmount').val('');
});

$(document).on('click', '#btnAddMoneySubmit', function() {
	walletClearErr('addMoneyErr');
	var amount = parseFloat($('#addMoneyAmount').val() || 0);
	if (!amount || amount <= 0) {
		walletSetErr('addMoneyErr', 'Amount must be greater than 0.');
		return;
	}

	var inst = (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getInstance(document.getElementById('addMoneyModal')) : null;
	if (inst) inst.hide();
	walletOpenPaymentModal(amount);
});

$(document).on('click', '#btnPayNow', function() {
	walletClearPayErr();
	var amount = parseFloat($(this).data('amount') || 0);
	var bankId = parseInt($('input[name="pay_bank_id"]:checked').val() || 0, 10);
	if (!amount || amount <= 0) {
		walletSetPayErr('Invalid amount.');
		return;
	}
	if (!bankId || bankId <= 0) {
		walletSetPayErr('Please select a bank account.');
		return;
	}

	var rate = window.currentCurrencyRate || 1.0;
	var baseAmount = amount / rate;
	walletApiPost('/add_money', { amount: baseAmount }, function(resp){
		var ok  = resp && (resp.status === 1 || resp.STATUS == 1);
		var d   = resp && resp.data ? resp.data : resp;
		if (ok) {
			var instPm = (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getInstance(document.getElementById('paymentMethodModal')) : null;
			if (instPm) instPm.hide();
			if (d && typeof d.balance !== 'undefined') walletUpdateBalance(d.balance);
			walletPrependTxn('credit', amount);
			return;
		}
		var msg = (d && (d.message || d.msg)) || (resp && (resp.MSG || resp.msg)) || 'Failed to add money.';
		if (msg === 'Please add bank details first.') {
			var instPm2 = (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getInstance(document.getElementById('paymentMethodModal')) : null;
			if (instPm2) instPm2.hide();
			if (window.bootstrap && bootstrap.Modal) new bootstrap.Modal(document.getElementById('bankModal')).show();
			return;
		}
		walletSetPayErr(msg);
	});
});

$(document).on('click', '#btnAddNewBank', function() {
	walletClearPayErr();
	try {
		var instPm = (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getInstance(document.getElementById('paymentMethodModal')) : null;
		if (instPm) instPm.hide();
	} catch (e) {}
	if (window.bootstrap && bootstrap.Modal) {
		new bootstrap.Modal(document.getElementById('bankModal')).show();
	}
});

$(document).on('click', '#btnWithdrawSubmit', function() {
	walletClearErr('withdrawErr');
	var amount = parseFloat($('#withdrawAmount').val() || 0);
	if (!amount || amount <= 0) {
		walletSetErr('withdrawErr', 'Amount must be greater than 0.');
		return;
	}

	var rate = window.currentCurrencyRate || 1.0;
	var baseAmount = amount / rate;
	walletApiPost('/withdraw', { amount: baseAmount }, function(resp) {
		var ok = resp && (resp.status === 1 || resp.STATUS == 1);
		var d  = resp && resp.data ? resp.data : resp;
		if (ok) {
			var inst3 = (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getInstance(document.getElementById('withdrawModal')) : null;
			if (inst3) inst3.hide();
			if (d && typeof d.balance !== 'undefined') walletUpdateBalance(d.balance);
			walletPrependTxn('debit', amount);
			return;
		}
		var msg = (d && (d.message || d.msg)) || (resp && (resp.MSG || resp.msg)) || 'Failed to submit withdraw request.';
		walletSetErr('withdrawErr', msg);
	});
});

$(document).on('click', '#btnBankSubmit', function() {
	walletClearErr('bankErr');
	var payload = {
		account_holder_name: $('#bankHolder').val(),
		bank_name: $('#bankName').val(),
		account_number: $('#bankAcc').val(),
		ifsc_code: $('#bankIfsc').val()
	};

	walletApiPost('/save_bank', payload, function(resp) {
		var ok = resp && (resp.status === 1 || resp.STATUS == 1);
		if (ok) {
			var inst4 = (window.bootstrap && bootstrap.Modal) ? bootstrap.Modal.getInstance(document.getElementById('bankModal')) : null;
			if (inst4) inst4.hide();
			var amount = parseFloat($('#addMoneyAmount').val() || 0);
			if (amount && amount > 0) {
				walletOpenPaymentModal(amount);
			} else if (window.bootstrap && bootstrap.Modal) {
				new bootstrap.Modal(document.getElementById('addMoneyModal')).show();
			}
			return;
		}
		var d = resp && resp.data ? resp.data : resp;
		walletSetErr('bankErr', (d && (d.message || d.msg)) || (resp && (resp.MSG || resp.msg)) || 'Failed to save bank details.');
	});
});

function openTransaction(el) {
	if (!el) return;
	var title = el.getAttribute('data-name') || '';
	var dateEl = el.querySelector('.text-muted');
	var amountEl = el.querySelector('span');
	var titleNode = document.getElementById('txnTitle');
	var dateNode = document.getElementById('txnDate');
	var amountNode = document.getElementById('txnAmount');
	if (titleNode) titleNode.innerText = title;
	if (dateNode) dateNode.innerText = dateEl ? dateEl.innerText : '';
	if (amountNode) amountNode.innerText = amountEl ? amountEl.innerText : '';
	if (window.bootstrap && bootstrap.Modal) {
		new bootstrap.Modal(document.getElementById('transactionModal')).show();
	}
}

function applyTxnFilters() {
	var list = document.getElementById('allTransactionList');
	if (!list) return;
	var mf = document.getElementById('monthFilter');
	var tf = document.getElementById('typeFilter');
	var si = document.getElementById('searchInput');
	var month = mf ? mf.value : 'all';
	var type = tf ? tf.value : 'all';
	var q = (si ? si.value : '').toLowerCase();
	var items = list.querySelectorAll('.wallet-item');
	items.forEach(function(item) {
		var itMonth = item.getAttribute('data-month') || 'all';
		var itType = item.getAttribute('data-type') || 'all';
		var itName = (item.getAttribute('data-name') || '').toLowerCase();
		var ok = true;
		if (month !== 'all' && itMonth !== month) ok = false;
		if (type !== 'all' && itType !== type) ok = false;
		if (q && itName.indexOf(q) === -1) ok = false;
		item.style.display = ok ? '' : 'none';
	});
}

$(document).on('change', '#monthFilter', applyTxnFilters);
$(document).on('change', '#typeFilter', applyTxnFilters);
$(document).on('input', '#searchInput', applyTxnFilters);

});

var lang_data = {
    add_new_address: "<?= $page_lang->add_new_address; ?>",
    order_number: "<?= $page_lang->order_number; ?>",
    date_time: "<?= $page_lang->date_time; ?>",
    total: "<?= $page_lang->total; ?>",
    delivery_charge: "<?= $page_lang->delivery_charge; ?>",
    free: "<?= $page_lang->free; ?>",
    grand_total: "<?= $page_lang->grand_total; ?>",
    modifiers: "<?= $page_lang->modifiers; ?>",
    extras: "<?= $page_lang->extras; ?>",
    size: "<?= $page_lang->size; ?>",
    qty: "<?= $page_lang->qty; ?>",
    no_orders: "<?= $page_lang->no_orders_found; ?>"
};

function manualProfileUpdate(btnElement) {
    var form = $('#profileUpdateForm');
    var btn = $(btnElement);
    var formData = new FormData(form[0]);

    if(typeof loading === "function") loading(btn, true, 'Saving...');

    $.ajax({
        url: '<?= base_url("api/v1/Profile/update"); ?>',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(resp) {
            if(typeof loading === "function") loading(btn, false, 'Save Changes');
            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
            if(STATUS == 1) {
                if(typeof showSnackBar === "function") showSnackBar({message: 'Profile updated successfully'});
                // Refresh dashboard stats in case name changed
                if(typeof load_dashboard_stats === 'function') load_dashboard_stats();
            } else {
                alert("Error: " + (MSG || 'Failed to update profile'));
            }
        },
        error: function(xhr) {
            if(typeof loading === "function") loading(btn, false, 'Save Changes');
            console.error(xhr.responseText);
            alert("Server Error. Check console (F12) for details.");
        }
    });
}

function manualPasswordChange(btnElement){
    var form = $('#passwordChangeForm');
    var btn = $(btnElement);
    var $msg = $('#passwordChangeMsg');
    $msg.text('').removeClass('text-danger text-success');

    var formData = form.serializeArray();
    var payload = {};
    $(formData).each(function(index, obj){
        payload[obj.name] = obj.value;
    });

    if(typeof loading === "function") loading(btn, true, 'Updating...');

    $.ajax({
        url: '<?= base_url("api/v1/Profile/change_password"); ?>',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(payload),
        dataType: 'json',
        success: function(resp){
            if(typeof loading === "function") loading(btn, false, 'Update Password');
            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
            if(STATUS == 1){
                $msg.addClass('text-success').text(MSG || 'Password updated successfully');
                form[0].reset();
            }else{
                $msg.addClass('text-danger').text(MSG || 'Failed to update password');
            }
        },
        error: function(xhr){
            if(typeof loading === "function") loading(btn, false, 'Update Password');
            console.error(xhr.responseText);
            $msg.addClass('text-danger').text('Server error. Please try again.');
        }
    });
}



function load_returns() {
    $('.returns_html').html('<div class="text-center text-muted py-4">Loading...</div>');
    $.ajax({
        url: '<?= base_url("api/v1/Returns"); ?>',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
        },
        dataType: 'json',
        success: function(resp) {
            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var DATA = (resp && resp.data && resp.data.returns) ? resp.data.returns : [];

            if (STATUS !== 1 || !Array.isArray(DATA) || DATA.length === 0) {
                $('.returns_html').html('<div class="text-center text-muted py-5">No return requests found.</div>');
                return;
            }

            var html = '<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Return ID</th><th>Order</th><th>Product</th><th>Reason</th><th>Status</th><th>Requested</th></tr></thead><tbody>';
            DATA.forEach(function(r) {
                var status = (r.status || 'pending').toLowerCase();
                var badge = 'badge bg-secondary';
                if (status === 'pending') badge = 'badge bg-warning text-dark';
                if (status === 'approved' || status === 'accepted') badge = 'badge bg-success';
                if (status === 'rejected') badge = 'badge bg-danger';
                
                var productName = r.product ? r.product.name : (r.product_name || 'Item #' + (r.order_item_id || ''));
                
                html += `<tr>
                    <td>#${r.return_id}</td>
                    <td>#${r.order_id}</td>
                    <td style="max-width:200px;" class="text-truncate" title="${productName}">${productName}</td>
                    <td>${r.reason || ''}</td>
                    <td><span class="${badge}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
                    <td class="fz13">${r.created_at || ''}</td>
                </tr>`;
            });
            html += '</tbody></table></div>';
            $('.returns_html').html(html);
        },
        error: function() {
            $('.returns_html').html('<div class="text-center text-muted py-4">Failed to load returns</div>');
        }
    });
}


function waitForJQuery(cb) {
    if (window.jQuery && typeof window.jQuery === 'function') {
        window.jQuery(cb);
        return;
    }
    setTimeout(function () {
        waitForJQuery(cb);
    }, 50);
}

function initProfilePage() {
    // Priority: URL hash > ?tab= query param > PHP fallback
    // Hash is the primary SPA routing mechanism and is preserved on refresh
    var hashTab = window.location.hash ? window.location.hash.substring(1) : '';
    // Normalize wallet_transactions hash
    if (hashTab === 'wallet_transactions') hashTab = 'wallet_transactions';
    const urlParams = new URLSearchParams(window.location.search);
    var queryTab = urlParams.get('tab') || '';
    // Use hash first, then query param, then PHP server-side value as last resort
    let activeTab = hashTab || queryTab || 'dashboard';

	var tabToDiv = {
		dashboard: '#dashboardDiv',
		order: '#orderDiv',
		address: '#addressDiv',
		wishlist: '#wishlistDiv',
		details: '#detailsDiv',
		wallet: '#walletDiv',
		wallet_transactions: '#walletTransactionsDiv',
		returns: '#returnsDiv'
	};
	var targetDiv = tabToDiv[activeTab] || ('#' + activeTab + 'Div');

	function resetReturnModal(){
		$('#ret_order_id').val('');
		$('#ret_order_item_id').val('');
		$('#ret_product_id').val('');
		$('#ret_vendor_id').val('');
		$('#ret_order_item_select').html('<option value="">Select product</option>');
		$('#ret_item_help').hide();
		$('#returnReqAlert').hide().removeClass('alert-success alert-danger').text('');
		var form = document.getElementById('returnRequestForm');
		if (form) {
			try { form.reset(); } catch (e) {}
		}
	}

	function openReturnModal(){
		var modalEl = document.getElementById('returnRequestModal');
		var modal = new bootstrap.Modal(modalEl);
		modal.show();
	}

	$(document).on('change', '#ret_order_item_select', function(){
		var $opt = $(this).find('option:selected');
		$('#ret_order_item_id').val($opt.data('order-item-id') || $(this).val() || '');
		$('#ret_product_id').val($opt.data('product-id') || '');
		$('#ret_vendor_id').val($opt.data('vendor-id') || '');
	});

	$(document).on('click', '.return-item-btn', function(e){
		e.preventDefault();
		var $btn = $(this);
		var orderId = $btn.data('order-id') || '';
		var orderItemId = $btn.data('order-item-id') || '';
		var productId = $btn.data('product-id') || '';
		var vendorId = $btn.data('vendor-id') || '';
		var productName = $btn.data('product-name') || '';
		if (!orderId || !orderItemId) return;

		resetReturnModal();
		$('#ret_order_id').val(orderId);
		$('#ret_item_help').hide();
		openReturnModal();

		var opts = '<option value="">Select product</option>';
		opts += '<option value="' + escHtml(orderItemId) + '" data-order-item-id="' + escHtml(orderItemId) + '" data-product-id="' + escHtml(productId) + '" data-vendor-id="' + escHtml(vendorId) + '" selected>' + escHtml(productName || ('Item #' + orderItemId)) + '</option>';
		$('#ret_order_item_select').html(opts);
		$('#ret_order_item_select').prop('selectedIndex', 1).trigger('change');
		$('#submitReturnBtn').prop('disabled', false);
	});

	$(document).on('submit', '#returnRequestForm', function(e){
		e.preventDefault();

		var form = document.getElementById('returnRequestForm');
		if (!form) return;

		var orderId   = $('#ret_order_id').val() || '';
		var orderItemId = $('#ret_order_item_id').val() || '';
		var reason    = $('select[name="reason"]', form).val() || '';

		if (!orderItemId) {
			$('#returnReqAlert').addClass('alert-danger').show().text('Please select a product to return.');
			return;
		}
		if (!reason) {
			$('#returnReqAlert').addClass('alert-danger').show().text('Please select a return reason.');
			return;
		}

		var formData = new FormData(form);

		$('#returnReqAlert').hide().removeClass('alert-success alert-danger').text('');
		loading('#submitReturnBtn', true, 'Submitting...');

		$.ajax({
			url: '<?= base_url("api/v1/Returns/request"); ?>',
			type: 'POST',
			headers: {
				'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
			},
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			dataType: 'json',
			success: function(resp){
				loading('#submitReturnBtn', false, 'Submit Return Request');
				var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
				var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
				if (STATUS === 1) {
					$('#returnReqAlert').addClass('alert-success').show().text(MSG || 'Return request submitted successfully');
					try {
						var oid = String(orderId);
						var oiid = String(orderItemId);
						if (oid && oiid) {
							if (!window.__return_availability) window.__return_availability = { orders: {} };
							if (!window.__return_availability.orders) window.__return_availability.orders = {};
							if (!window.__return_availability.orders[oid]) {
								window.__return_availability.orders[oid] = { has_unreturned_item: 1, returned_order_item_ids: [] };
							}
							var arr = window.__return_availability.orders[oid].returned_order_item_ids;
							if (!Array.isArray(arr)) arr = [];
							var parsedId = parseInt(oiid, 10) || oiid;
							if (arr.indexOf(parsedId) === -1) {
								arr.push(parsedId);
							}
							window.__return_availability.orders[oid].returned_order_item_ids = arr;
							// Re-render the order detail modal to reflect updated state (Return button disappears)
							if (window.__last_order_detail_order_id && String(window.__last_order_detail_order_id) === oid && window.__last_order_detail) {
								$('#orderDetailsBody').html(renderOrderDetails(window.__last_order_detail));
							}
						}
					} catch (e) {}
					setTimeout(function(){
						try {
							load_order_list();
						} catch (e) {}
					}, 50);
					setTimeout(function(){
						try {
							var modalEl = document.getElementById('returnRequestModal');
							var inst = bootstrap.Modal.getInstance(modalEl);
							if (inst) inst.hide();
						} catch (e) {}
					}, 1200);
					form.reset();
				} else {
					$('#returnReqAlert').addClass('alert-danger').show().text(MSG || 'Failed to submit return request');
				}
			},
			error: function(xhr){
				loading('#submitReturnBtn', false, 'Submit Return Request');
				var errMsg = 'Failed to submit return request';
				try {
					var errResp = JSON.parse(xhr.responseText);
					errMsg = (errResp && (errResp.message || errResp.msg || errResp.MSG)) || errMsg;
				} catch(e) {}
				$('#returnReqAlert').addClass('alert-danger').show().text(errMsg);
			}
		});
	});

    // Tab display is handled by the hashchange listener in walletWaitForJQuery.
    // initProfilePage only sets up event handlers; routing is managed by hash.
    // We do NOT show/hide tabs here to avoid conflicting with hashchange handler.

    // However, trigger data loading for the current tab if hashchange already fired
    // (or will fire shortly). We schedule it slightly deferred to let hashchange run first.
    setTimeout(function() {
        var curHash = window.location.hash ? window.location.hash.substring(1) : 'dashboard';
        if (curHash === 'order') {
            if (typeof load_order_list === 'function') load_order_list();
        } else if (curHash === 'address') {
            if (typeof get_addresses === 'function') get_addresses();
        } else if (curHash === 'wishlist') {
            if (typeof load_wishlist === 'function') load_wishlist();
        } else if (curHash === 'returns') {
            if (typeof load_returns === 'function') load_returns();
        } else {
            // dashboard or unknown — load_order_list still populates recent orders preview
            if (typeof load_order_list === 'function') load_order_list();
        }
    }, 0);


    $(document).on('click', '.wl-remove', function(e){
        e.preventDefault();
        var pid = $(this).data('product-id');
        if (!pid) return;
        var postData = { product_id: pid };
        if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
            postData[window.csrf_token_name] = window.csrf_hash;
        }
        $.ajax({
            url: '<?= base_url("customer/wishlist/toggle"); ?>',
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: function(resp){
                var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
                if (STATUS === 1) {
                    load_wishlist();
                }
            }
        });
    });

    $(document).on('click', '.wl-add-to-cart', function(e){
        e.preventDefault();
        var pid = $(this).data('product-id');
        if (!pid) return;

        var postData = {
            product_id: pid,
            quantity: 1,
            action: 'add'
        };
        if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
            postData[window.csrf_token_name] = window.csrf_hash;
        }

        var $btn = $(this);
        var txt = $btn.text();
        $btn.prop('disabled', true).text('Adding...');
        $.ajax({
            url: '<?= base_url("customer/cart/ajax_add_to_cart"); ?>',
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: function(resp){
                $btn.prop('disabled', false).text(txt);
                var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
                if (STATUS == 1) {
                    if (typeof resp.cart_count !== 'undefined') {
                        $('#cart_count').text(resp.cart_count);
                    }
                    if (typeof resp.cart_qty !== 'undefined') {
                        $('#cart_qty_count').text(resp.cart_qty);
                    }
                    if (typeof resp.total_price !== 'undefined') {
                        $('#cart_total').text((window.currency_symbol || '$') + resp.total_price);
                        $('#side_total').text((window.currency_symbol || '$') + resp.total_price);
                    }

                    if (typeof openCartSidebar === 'function') {
                        openCartSidebar();
                    } else {
                        $('.cart-hidden-sbar').addClass('active');
                        $('body').addClass('cart-open');
                    }
                    if (typeof refreshHeaderAndSidebarCart === 'function') {
                        refreshHeaderAndSidebarCart();
                    }
                }
            },
            error: function(){
                $btn.prop('disabled', false).text(txt);
            }
        });
    });

    $(document).on('change', '#uploadPhoto', function () {
        let file = this.files[0];
        $("#errorMsg").text("");
        if (!file) return;

        if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
            $("#errorMsg").text("Only JPG, PNG, WEBP images are allowed.");
            this.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            $("#errorMsg").text("Max size 2MB allowed.");
            this.value = '';
            return;
        }

        let reader = new FileReader();
        reader.onload = function (e) {
            $('#profile-preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
    });

    $(document).on('submit', '#profileUpdateForm', function (e) {
        e.preventDefault();
        var btn = $(this).find('.btn-thm');
        manualProfileUpdate(btn[0]);
        return false;
    });

    $(document).on('submit', "#addAddressForm", function(e){
        e.preventDefault();
        loading('.address_submit', true, 'Wait...');
        var formData = $(this).serializeArray();
        var payload = {};
        $(formData).each(function(index, obj){
            payload[obj.name] = obj.value;
        });

        var id = payload.id || '';
        var apiUrl = id ? '<?= base_url("api/v1/Addresses/update"); ?>' : '<?= base_url("api/v1/Addresses/create"); ?>';
        
        $.ajax({
            url: apiUrl,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function(resp){
                loading('.address_submit', false, 'SUBMIT');
                var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
                var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
                if(typeof showSnackBar === "function") showSnackBar({message: MSG || 'Success'});
                if(STATUS == 1){
                    var modalEl = document.getElementById('profileAddAddressModal');
                    hideBsModal(modalEl);
                    get_addresses();
                }
            },
            error: function(){
                loading('.address_submit', false, 'SUBMIT');
                if(typeof showSnackBar === "function") showSnackBar({message: 'Failed to save address'});
            }
        });
    });

    $(document).on('click', '.cancel-order-btn', function(){
        var orderId = $(this).data('order-id');
        $('#cancel_order_id').val(orderId);
        $('#cancel_reason').val('');
        var modalEl = document.getElementById('profileCancelOrderModal');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });

    $(document).on('click', '#confirmCancelOrderBtn', function(){
        var orderId = $('#cancel_order_id').val();
        var reason = $('#cancel_reason').val();
        if (!orderId) {
            alert('Invalid order');
            return;
        }
        if (!reason || !reason.trim()) {
            alert('Please enter reason');
            return;
        }

        var payload = {
            order_id: orderId,
            reason: reason
        };

        loading('#confirmCancelOrderBtn', true, 'Cancelling...');
        $.ajax({
            url: '<?= base_url("api/v1/Orders/cancel"); ?>',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function(resp){
                loading('#confirmCancelOrderBtn', false, 'Cancel Order');
                var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
                var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
                if (STATUS === 1) {
                    var modalEl = document.getElementById('profileCancelOrderModal');
                    var inst = bootstrap.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                    load_order_list();
                } else {
                    alert(MSG || 'Failed to cancel');
                }
            },
            error: function(){
                loading('#confirmCancelOrderBtn', false, 'Cancel Order');
                alert('Failed to cancel');
            }
        });
    });

    $(document).on('click', '.view-order-btn', function(){
        var orderId = $(this).data('order-id');
        if (!orderId) return;
        $('#orderDetailsTitle').text('Order Details');
        $('#orderDetailsBody').html('<div class="text-muted">Loading...</div>');

        var modalEl = document.getElementById('orderDetailsModal');
        var modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Fetch order details and returns for this order in parallel
        var orderReq = $.ajax({
            url: '<?= base_url("api/v1/Orders/detail"); ?>',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ order_id: orderId }),
            dataType: 'json'
        });

        // Also fetch returns for this order so we know which items are already returned
        var returnsReq = $.ajax({
            url: '<?= base_url("api/v1/Returns"); ?>',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
            },
            dataType: 'json'
        });

        $.when(orderReq, returnsReq).done(function(orderResult, returnsResult) {
            var resp = orderResult[0];
            var returnsResp = returnsResult[0];

            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var DATA = (typeof resp.DATA !== 'undefined') ? resp.DATA : resp.data;
            var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
            if (STATUS !== 1 || !DATA || !DATA.order) {
                $('#orderDetailsBody').html('<div class="text-danger">' + escHtml(MSG || 'Failed to load order') + '</div>');
                return;
            }

            // Build returned items map for this order from the returns API
            try {
                var returnsData = (returnsResp && (returnsResp.data || returnsResp.DATA)) || {};
                var returnsList = returnsData.returns || [];
                if (!window.__return_availability) window.__return_availability = { orders: {} };
                if (!window.__return_availability.orders) window.__return_availability.orders = {};
                var returnedIds = [];
                returnsList.forEach(function(r) {
                    if (String(r.order_id) === String(orderId) && r.order_item_id) {
                        returnedIds.push(parseInt(r.order_item_id, 10));
                    }
                });
                window.__return_availability.orders[String(orderId)] = {
                    has_unreturned_item: 1,
                    returned_order_item_ids: returnedIds
                };
            } catch(e) {}

            // Map the API structure back to what the template expects
            var o = DATA.order;
            var mappedData = {
                id: o.order_id,
                order_number: o.order_number,
                date_added: o.order_date,
                total_amount: o.total_amount,
                discount_amount: o.discount_amount,
                shipping_amount: o.shipping_amount,
                final_amount: o.final_amount,
                payment_method: o.payment_method,
                payment_status: o.payment_status,
                status: o.order_status,
                address: DATA.shipping_address,
                items: DATA.items
            };

            window.__last_order_detail = mappedData;
            window.__last_order_detail_order_id = mappedData.id || orderId;
            $('#orderDetailsTitle').text('Order #' + (o.order_number || o.order_id || orderId));
            $('#orderDetailsBody').html(renderOrderDetails(mappedData));

        }).fail(function() {
            $('#orderDetailsBody').html('<div class="text-danger">Failed to load order details</div>');
        });
    });
}

waitForJQuery(initProfilePage);

if (typeof window.loading !== 'function') {
    window.loading = function(target, isLoading, text) {
        try {
            var $btn = (target && target.jquery) ? target : $(target);
            if (!$btn.length) return;
            if (isLoading) {
                if (typeof $btn.data('original-text') === 'undefined') {
                    $btn.data('original-text', $btn.text());
                }
                $btn.prop('disabled', true);
                if (text) $btn.text(text);
            } else {
                var original = $btn.data('original-text');
                $btn.prop('disabled', false);
                if (original) $btn.text(original);
            }
        } catch (e) {}
    };
}

function fmtMoney(val) {
    var symbol = (typeof window.currentCurrencySymbol !== 'undefined') ? window.currentCurrencySymbol : ((typeof window.currency_symbol !== 'undefined') ? window.currency_symbol : '$');
    var num = parseFloat(val || 0);
    if (isNaN(num)) num = 0;
    var rate = (typeof window.currentCurrencyRate !== 'undefined' && parseFloat(window.currentCurrencyRate) > 0) ? parseFloat(window.currentCurrencyRate) : 1.0;
    num = num * rate;
    return symbol + num.toFixed(2);
}

function getCsrfPostData(){
    var d = {};
    if (typeof window.csrf_token_name !== 'undefined' && typeof window.csrf_hash !== 'undefined') {
        d[window.csrf_token_name] = window.csrf_hash;
    }
    return d;
}

function updateCsrfFromResponse(resp){
    try {
        if (!resp) return;
        var h = resp.csrf_hash || resp.CSRF_HASH || null;
        if (h && typeof window.csrf_hash !== 'undefined') {
            window.csrf_hash = h;
        }
    } catch (e) {}
}

function showBsModal(modalEl){
    if (!modalEl) return;
    try {
        if (window.bootstrap && bootstrap.Modal) {
            new bootstrap.Modal(modalEl).show();
            return;
        }
    } catch (e) {}
    try {
        if (window.jQuery && typeof $(modalEl).modal === 'function') {
            $(modalEl).modal('show');
        }
    } catch (e2) {}
}

function hideBsModal(modalEl){
    if (!modalEl) return;
    try {
        if (window.bootstrap && bootstrap.Modal) {
            var inst = bootstrap.Modal.getInstance(modalEl);
            if (inst) inst.hide();
            return;
        }
    } catch (e) {}
    try {
        if (window.jQuery && typeof $(modalEl).modal === 'function') {
            $(modalEl).modal('hide');
        }
    } catch (e2) {}
}

function openAddressModal(mode){
    var modalEl = document.getElementById('profileAddAddressModal');
    if(!modalEl) return;
    var titleEl = modalEl.querySelector('.modal-title');
    if (titleEl) titleEl.textContent = (mode === 'edit') ? 'Edit Address' : 'Add New Address';
    showBsModal(modalEl);
}

function resetAddressForm(){
    var $form = $('#addAddressForm');
    if(!$form.length) return;
    $form[0].reset();
    $form.find('input[name="id"]').val('');
    var modalEl = document.getElementById('profileAddAddressModal');
    if (modalEl) {
        var titleEl = modalEl.querySelector('.modal-title');
        if (titleEl) titleEl.textContent = 'Add New Address';
    }
}

function fillAddressForm(addr){
    var $form = $('#addAddressForm');
    if(!$form.length) return;
    $form.find('input[name="id"]').val(addr.shipping_address_id || addr.id || '');
    $form.find('input[name="fullname"]').val(addr.fullname || '');
    $form.find('input[name="address_1"]').val(addr.address_1 || '');
    $form.find('input[name="address_2"]').val(addr.address_2 || '');
    $form.find('input[name="city"]').val(addr.city || '');
    $form.find('input[name="state"]').val(addr.state || '');
    $form.find('input[name="mobile"]').val(addr.mobile || '');
    $form.find('input[name="postcode"]').val(addr.postcode || '');
    $form.find('input[name="address_type"]').val(addr.address_type || '');
    var modalEl = document.getElementById('profileAddAddressModal');
    if (modalEl) {
        var titleEl = modalEl.querySelector('.modal-title');
        if (titleEl) titleEl.textContent = 'Edit Address';
    }
}

function escHtml(str){
    return (str || '').toString().replace(/[&<>"]/g, function(c){
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[c] || c;
    });
}

function formatAddress(addr){
    if(!addr) return '<div class="text-muted">No address found</div>';
    var line1 = [addr.address_1, addr.address_2].filter(Boolean).join(', ');
    var line2 = [addr.city, addr.state].filter(Boolean).join(', ');
    var pc = addr.postcode ? (' - ' + addr.postcode) : '';
    return `
        <div class="fw600">${escHtml(addr.fullname || '')}</div>
        <div class="text-muted fz13">${escHtml(line1)}</div>
        <div class="text-muted fz13">${escHtml(line2)}${escHtml(pc)}</div>
        <div class="text-muted fz13">Phone: ${escHtml(addr.mobile || '')}</div>
    `;
}

function statusSteps(status){
    if (status && typeof status === 'object') {
        status = status.label || status.name || status.status || status.title || '';
    }
    var s = String(status || 'pending').toLowerCase();
    var asInt = parseInt(s, 10) || 0;
    if (asInt > 0) {
        if (asInt === 1) s = 'pending';
        if (asInt === 2) s = 'confirmed';
        if (asInt === 3) s = 'shipped';
        if (asInt === 10) s = 'delivered';
        if (asInt === 17 || asInt === 5) s = 'cancelled';
    }
    var order = ['pending', 'confirmed', 'shipped', 'delivered'];
    if (s === 'cancelled') s = 'canceled';
    var activeIndex = order.indexOf(s);
    if (activeIndex < 0) activeIndex = 0;
    var html = '<div class="d-flex gap-2 flex-wrap">';
    order.forEach(function(st, idx){
        var cls = idx <= activeIndex ? 'badge bg-success' : 'badge bg-light text-dark';
        html += `<span class="${cls}">${st.charAt(0).toUpperCase() + st.slice(1)}</span>`;
    });
    html += '</div>';
    return html;
}

function renderOrderDetails(order){
    var items = Array.isArray(order.items) ? order.items : [];
    var itemsHtml = '';
    var subtotal = 0;
    var returnedMap = {};
    try {
        var per = (window.__return_availability && window.__return_availability.orders) ? window.__return_availability.orders : {};
        var row = per[String(order.id || '')] || per[order.id] || null;
        var arr = (row && Array.isArray(row.returned_order_item_ids)) ? row.returned_order_item_ids : [];
        arr.forEach(function(v){ returnedMap[String(v)] = 1; });
    } catch (e) {}

    // Detect order-level delivered status for fallback
    var orderStatusStr = String(order.status || '').toLowerCase();
    var orderIsDelivered = (orderStatusStr === 'delivered' || orderStatusStr.indexOf('deliver') !== -1);

    items.forEach(function(it){
        var qty = parseInt(it.quantity || it.qty || 0, 10) || 0;
        var price = parseFloat(it.price || 0) || 0;
        var rowSub = parseFloat(it.subtotal || (qty * price)) || 0;
        subtotal += rowSub;

        // Normalize item status — DB stores numeric codes: 1=pending, 2=confirmed, 3=shipped, 10=delivered, 17=cancelled
        // API returns these as strings e.g. '1', '10', '17' or word strings like 'delivered'
        var stRaw = (typeof it.status !== 'undefined' && it.status !== null) ? it.status : '';
        var stStr = String(stRaw || '').toLowerCase().trim();
        var stInt = parseInt(stStr, 10);           // may be NaN for word strings
        if (isNaN(stInt)) stInt = 0;

        // Resolve stStr to a word-label for badge display
        var stLabel = stStr;
        if (stInt === 1  || stStr === 'pending')   stLabel = 'pending';
        if (stInt === 2  || stStr === 'confirmed' || stStr === 'processing') stLabel = 'confirmed';
        if (stInt === 3  || stStr === 'shipped')   stLabel = 'shipped';
        if (stInt === 10 || stStr === 'delivered')  stLabel = 'delivered';
        if (stInt === 17 || stInt === 5 || stStr === 'cancelled' || stStr === 'canceled') stLabel = 'canceled';

        // Item is explicitly delivered
        var isDeliveredItem = (stLabel === 'delivered');

        // Item is explicitly cancelled
        var isCancelledItem = (stLabel === 'canceled');

        // KEY FIX: If item has no status OR has a non-cancelled status (pending/confirmed/shipped)
        // but the WHOLE ORDER is delivered → treat item as delivered too.
        // (Vendors frequently don't update individual item statuses.)
        if (!isDeliveredItem && !isCancelledItem && orderIsDelivered) {
            isDeliveredItem = true;
            stLabel = 'delivered';
        }

        // Build status label badge (inherit order status when item status is empty/stale)
        var displayStatus = stLabel || orderStatusStr;
        var itemStatusLabel = '';
        try {
            // Use the full badge HTML (with color) directly — no stripping
            var badge = getStatusBadge(displayStatus);
            if (badge && String(badge).trim() !== '') {
                itemStatusLabel = '<span class="ms-2 fz12">' + badge + '</span>';
            }
        } catch (e) {
            itemStatusLabel = '';
        }

        var orderItemId = it.id || it.order_item_id || 0;
        var isAlreadyReturned = !!(orderItemId && returnedMap[String(orderItemId)]);
        var returnedBadgeRight = isAlreadyReturned ? '<span class="badge bg-warning text-dark ms-2">Return Requested</span>' : '';

        // 7-day return window: use item last_updated (delivery date), fallback to order placed date
        var deliveredAt = (it.last_updated || it.updated_at || order.date_added || '') + '';
        var deliveredTs = 0;
        if (deliveredAt) {
            var p = Date.parse(deliveredAt);
            if (!isNaN(p)) deliveredTs = Math.floor(p / 1000);
        }
        var isWithin7Days;
        if (deliveredTs > 0) {
            isWithin7Days = (Math.floor(Date.now() / 1000) <= (deliveredTs + (7 * 24 * 60 * 60)));
        } else {
            // No delivery timestamp: give benefit of doubt if item is delivered
            isWithin7Days = isDeliveredItem;
        }

        var canReturnItem = (orderItemId && isDeliveredItem && !isCancelledItem && isWithin7Days && !isAlreadyReturned);
        var returnLink = canReturnItem
            ? `<a href="#" class="btn btn-sm btn-outline-warning ms-2 py-0 return-item-btn" data-order-id="${escHtml(order.id || '')}" data-order-item-id="${escHtml(orderItemId)}" data-product-id="${escHtml(it.product_id || '')}" data-vendor-id="${escHtml(it.login_id || it.vendor_id || '')}" data-product-name="${escHtml(it.name || it.product_name || '')}">Return</a>`
            : '';

        itemsHtml += `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${escHtml(it.image_url || '')}" onerror="this.src='<?= base_url('assets/customer/images/shop/s1.png'); ?>'" style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid #eee;" />
                        <div style="min-width:0; width:100%;">
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <span class="fw600">${escHtml(it.name || it.product_name || '')}</span>
                                ${itemStatusLabel}${returnedBadgeRight}${returnLink}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-end">${qty}</td>
                <td class="text-end">${fmtMoney(price)}</td>
                <td class="text-end">${fmtMoney(rowSub)}</td>
            </tr>
        `;
    });

    var shipping = parseFloat(order.shipping_amount || 0) || 0;
    var discount = parseFloat(order.discount_amount || 0) || 0;
    var total = parseFloat(order.final_amount || (subtotal + shipping - discount)) || 0;
    var orderNo = escHtml(order.order_number || order.id || '');
    var mainStatus = (typeof order.status !== 'undefined' && order.status !== null) ? order.status : 'pending';

    return `
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb15">
            <div>
                <div class="fw700" style="font-size:18px;">Order #${orderNo} <span class="ms-2">${getStatusBadge(mainStatus)}</span></div>
                <div class="text-muted fz13">Placed on ${escHtml(formatOrderDate(order.date_added || ''))}</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-sm btn-outline-dark" href="<?= base_url('customer/order/download_invoice'); ?>?order_id=${encodeURIComponent(order.id)}" target="_blank">Download Invoice</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml || '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>'}
                </tbody>
            </table>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-lg-6">
                <div class="p15 border bdrs12 h-100">
                    <div class="fw700 mb10">Shipping Address</div>
                    ${formatAddress(order.address)}
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p15 border bdrs12 h-100">
                    <div class="fw700 mb10">Payment & Delivery</div>
                    <div class="text-muted fz13">Payment Method: <span class="text-dark">${escHtml(order.payment_method || 'COD')}</span></div>
                    <div class="text-muted fz13">Payment Status: <span class="text-dark">${escHtml(order.payment_status || 'pending')}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between fz14"><span>Subtotal</span><span>${fmtMoney(subtotal)}</span></div>
                    <div class="d-flex justify-content-between fz14"><span>Shipping</span><span>${fmtMoney(shipping)}</span></div>
                    ${discount > 0 ? `<div class="d-flex justify-content-between fz14 text-success" style="color: #2e7d32!important;"><span>Discount ${order.coupon_code ? '(' + escHtml(order.coupon_code) + ')' : ''}</span><span>-${fmtMoney(discount)}</span></div>` : ''}
                    <div class="d-flex justify-content-between fz15 mt-2"><span class="fw700">Total</span><span class="fw700">${fmtMoney(total)}</span></div>
                </div>
            </div>
        </div>
    `;
}

function formatOrderDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
}

function parseAnyDate(dateStr) {
    if (!dateStr) return null;
    if (dateStr instanceof Date) {
        if (!isNaN(dateStr.getTime())) return dateStr;
        return null;
    }

    var s = String(dateStr).trim();
    if (!s) return null;

	// Normalize common MySQL datetime format: YYYY-MM-DD HH:MM:SS
	// Some browsers treat it as invalid unless it uses 'T'.
	var mysqlDt = s.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})(?::(\d{2}))?$/);
	if (mysqlDt) {
		var isoLike = mysqlDt[1] + '-' + mysqlDt[2] + '-' + mysqlDt[3] + 'T' + mysqlDt[4] + ':' + mysqlDt[5] + ':' + (mysqlDt[6] || '00');
		var dMysql = new Date(isoLike);
		if (!isNaN(dMysql.getTime())) return dMysql;
	}

    // Try native parsing first
    var d0 = new Date(s);
    if (!isNaN(d0.getTime())) return d0;

	// DD Mon YYYY / DD Month YYYY (e.g., 31 Mar 2026)
	var mText = s.match(/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})(?:\s.*)?$/);
	if (mText) {
		var day = parseInt(mText[1], 10);
		var monRaw = (mText[2] || '').toLowerCase();
		var year = parseInt(mText[3], 10);
		var monMap = {
			jan: 0, january: 0,
			feb: 1, february: 1,
			mar: 2, march: 2,
			apr: 3, april: 3,
			may: 4,
			jun: 5, june: 5,
			jul: 6, july: 6,
			aug: 7, august: 7,
			sep: 8, sept: 8, september: 8,
			oct: 9, october: 9,
			nov: 10, november: 10,
			dec: 11, december: 11
		};
		var monIdx = (typeof monMap[monRaw] !== 'undefined') ? monMap[monRaw] : (typeof monMap[monRaw.slice(0, 3)] !== 'undefined' ? monMap[monRaw.slice(0, 3)] : null);
		if (monIdx !== null && !isNaN(day) && !isNaN(year)) {
			var dText = new Date(year, monIdx, day);
			if (!isNaN(dText.getTime())) return dText;
		}
	}

    // DD-MM-YYYY or DD/MM/YYYY
    var m1 = s.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})(?:\s.*)?$/);
    if (m1) {
        var dd = parseInt(m1[1], 10);
        var mm = parseInt(m1[2], 10);
        var yy = parseInt(m1[3], 10);
        if (!isNaN(dd) && !isNaN(mm) && !isNaN(yy)) {
            var d1 = new Date(yy, mm - 1, dd);
            if (!isNaN(d1.getTime())) return d1;
        }
    }

    // YYYY-MM-DD
    var m2 = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(?:\s.*)?$/);
    if (m2) {
        var y2 = parseInt(m2[1], 10);
        var mo2 = parseInt(m2[2], 10);
        var da2 = parseInt(m2[3], 10);
        if (!isNaN(y2) && !isNaN(mo2) && !isNaN(da2)) {
            var d2 = new Date(y2, mo2 - 1, da2);
            if (!isNaN(d2.getTime())) return d2;
        }
    }

    return null;
}

function getStatusBadge(status) {
    if (status && typeof status === 'object') {
        status = status.label || status.name || status.status || status.title || '';
    }
    var st = String(status || 'pending').toLowerCase();
    var asInt = parseInt(st, 10) || 0;
    if (asInt > 0) {
        if (asInt === 1) st = 'pending';
        if (asInt === 2) st = 'confirmed';
        if (asInt === 3) st = 'shipped';
        if (asInt === 10) st = 'delivered';
        if (asInt === 17 || asInt === 5) st = 'canceled';
    }
    if (st === 'cancelled') st = 'canceled';
    var label = st.charAt(0).toUpperCase() + st.slice(1);

    // All statuses: black text on light bg. Cancelled: forced red via inline style.
    if (st === 'canceled') {
        return '<span class="badge bg-light border fw-semibold" style="color:#dc3545!important;">' + label + '</span>';
    }
    return '<span class="badge bg-light text-dark border">' + label + '</span>';
}

function isReturnEligible(order){
	try {
		if (!order) return false;

		var rawStatus = (
			order.status_label ||
			order.status_name ||
			order.order_status ||
			order.orderstatus ||
			order.status ||
			''
		);
		if (rawStatus && typeof rawStatus === 'object') {
			rawStatus = rawStatus.label || rawStatus.name || rawStatus.status || rawStatus.title || '';
		}
		var statusStr = (rawStatus || '').toString().trim().toLowerCase();
		var statusStrAsInt = parseInt(statusStr, 10) || 0;
		var statusId = (
			order.order_status_id ||
			order.status_id ||
			order.orderstatus_id ||
			order.order_statusid ||
			order.orderstatusid ||
			0
		);
		statusId = parseInt(statusId, 10) || 0;

		var isDelivered = false;
		if (statusStr) {
			isDelivered = (statusStr === 'delivered' || statusStr.indexOf('deliver') !== -1);
		}
		if (!isDelivered && statusStrAsInt > 0) {
			isDelivered = (statusStrAsInt === 4);
		}
		if (!isDelivered && statusId > 0) {
			isDelivered = (statusId === 4);
		}
		if (!isDelivered) return false;

		var deliveredDateStr = (
			order.delivered_at ||
			order.delivered_date ||
			order.delivery_date ||
			order.deliveryDate ||
			order.date_delivered ||
			order.date_added ||
			''
		);
		var deliveredDate = parseAnyDate(deliveredDateStr);
		// If the backend doesn't provide a consistently parseable delivery date,
		// don't block the Return button for a Delivered order.
		if (!deliveredDate) return true;

		var now = new Date();
		var diffMs = now.getTime() - deliveredDate.getTime();
		var diffDays = diffMs / (1000 * 60 * 60 * 24);
		if (diffDays < 0) return false;
		return diffDays <= 7;
	} catch (e) {
		return false;
	}
}

function isOrderDelivered(order){
	try {
		if (!order) return false;
		var rawStatus = (
			order.status_label ||
			order.status_name ||
			order.order_status ||
			order.orderstatus ||
			order.status ||
			''
		);
		if (rawStatus && typeof rawStatus === 'object') {
			rawStatus = rawStatus.label || rawStatus.name || rawStatus.status || rawStatus.title || '';
		}
		var statusStr = (rawStatus || '').toString().trim().toLowerCase();
		var statusStrAsInt = parseInt(statusStr, 10) || 0;
		var statusId = (
			order.order_status_id ||
			order.status_id ||
			order.orderstatus_id ||
			order.order_statusid ||
			order.orderstatusid ||
			0
		);
		statusId = parseInt(statusId, 10) || 0;
		if (statusStr && (statusStr === 'delivered' || statusStr.indexOf('deliver') !== -1)) return true;
		if (statusStrAsInt > 0 && statusStrAsInt === 4) return true;
		if (statusId > 0 && statusId === 4) return true;
		return false;
	} catch (e) {
		return false;
	}
}

function load_dashboard_stats() {
    $.ajax({
        url: '<?= base_url("api/v1/Profile"); ?>',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
        },
        dataType: 'json',
        success: function(resp) {
            if (resp && resp.data && resp.data.stats) {
                var stats = resp.data.stats;
                $('#dash-total-orders').text(stats.total_orders || 0);
                $('#dash-in-progress').text(stats.orders_in_progress || 0);
                $('#dash-delivered').text(stats.orders_delivered || 0);
                $('#dash-wishlist').text(stats.total_wishlist || 0);
            }
            if (resp && resp.data && resp.data.recent_orders) {
                var recentHtml = '';
                resp.data.recent_orders.forEach(function(o) {
                    var st = o.order_status || 'pending';
                    recentHtml += '<tr>';
                    recentHtml += '<td>' + (o.order_number || o.order_id) + '</td>';
                    recentHtml += '<td>' + formatOrderDate(o.order_date || '') + '</td>';
                    recentHtml += '<td>' + getStatusBadge(st) + '</td>';
                    recentHtml += '<td>' + fmtMoney(o.final_amount || 0) + '</td>';
                    recentHtml += '<td><a class="text-thm" href="#order">View details</a></td>';
                    recentHtml += '</tr>';
                });
                if (recentHtml === '') {
                    recentHtml = '<tr><td colspan="5" class="text-center text-muted">No recent orders</td></tr>';
                }
                $('.recent-orders-preview').html(recentHtml);
            }
        }
    });
}

function load_order_list() {
    $.ajax({
        url: '<?= base_url("api/v1/Orders"); ?>',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
        },
        dataType: 'json',
        success: function(resp) {
            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var DATA = (typeof resp.DATA !== 'undefined') ? resp.DATA : resp.data;

            if (STATUS !== 1 || !DATA || !Array.isArray(DATA.orders)) {
                $('.order-box-list').html('<li class="text-center text-muted py-4">' + (lang_data.no_orders || 'No orders found') + '</li>');
                $('.recent-orders-preview').html('<tr><td colspan="5" class="text-center text-muted">' + (lang_data.no_orders || 'No orders found') + '</td></tr>');
                return;
            }

            var apiOrders = DATA.orders;
            apiOrders.forEach(function(o) {
                o.id = o.order_id;
                o.date_added = o.order_date;
                o.status = o.order_status;
                o.final_amount = o.total_amount;
            });

            var orderIds = [];
            apiOrders.forEach(function(o){
                if (o && typeof o.id !== 'undefined' && o.id !== null) {
                    orderIds.push(parseInt(o.id, 10) || 0);
                }
            });
            orderIds = orderIds.filter(function(v){ return v > 0; });

            $.ajax({
                url: '<?= base_url("api/v1/Returns/order-availability"); ?>',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({ order_ids: orderIds }),
                dataType: 'json',

                success: function(r2){
                    var S2 = (typeof r2.STATUS !== 'undefined') ? r2.STATUS : r2.status;
                    var D2 = (typeof r2.DATA !== 'undefined') ? r2.DATA : r2.data;
                    var ordersMap = (S2 === 1 && D2 && D2.orders) ? D2.orders : {};
                    window.__return_availability = { orders: ordersMap };

                    var ordersHtml = '';
                    apiOrders.forEach(function(o){
                        var stRaw = (typeof o.status !== 'undefined' && o.status !== null) ? o.status : 'pending';
                        var st = String(stRaw || 'pending').toLowerCase();
                        if (st === 'cancelled') st = 'canceled';
                        var canCancel = (st === 'pending' || st === 'confirmed');

                        var isDelivered = (st === 'delivered' || st.indexOf('deliver') !== -1);
                        var info = ordersMap[String(o.id)] || ordersMap[o.id] || null;
                        var hasUnreturned = info ? (parseInt(info.has_unreturned_item || 0, 10) === 1) : true;
                        var canReturn = isDelivered && hasUnreturned;

                        ordersHtml += `
                            <li class="mb15 p20 border bdrs12">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <div class="fw600">Order: ${o.order_number || o.id}</div>
                                        <div class="text-muted fz13">Date: ${formatOrderDate(o.date_added || '')}</div>
                                        <div class="text-muted fz13">Status: ${getStatusBadge(st)}</div>
                                        <div class="text-muted fz13">Total: ${fmtMoney(o.final_amount || o.total_amount || 0)}</div>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-dark me-2 view-order-btn" data-order-id="${o.id}">View Details</button>
                                        <a class="btn btn-sm btn-outline-primary me-2" href="<?= base_url('customer/order/download_invoice'); ?>?order_id=${encodeURIComponent(o.id)}" target="_blank">Invoice</a>
                                        ${canCancel ? `<button type="button" class="btn btn-sm btn-outline-danger cancel-order-btn" data-order-id="${o.id}">Cancel Order</button>` : ''}
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    $('.order-box-list').html(ordersHtml);

					var recent = apiOrders.slice(0, 5);
					var recentHtml = '';
					recent.forEach(function(o){
						var st = (typeof o.status !== 'undefined' && o.status !== null) ? o.status : 'pending';
						st = (st && typeof st === 'object') ? (st.label || st.name || st.status || st.title || 'pending') : st;
						st = String(st || 'pending');
						recentHtml += `
							<tr>
								<td>${o.order_number || o.id}</td>
								<td>${formatOrderDate(o.date_added || '')}</td>
								<td>${getStatusBadge(st)}</td>
								<td>${fmtMoney(o.final_amount || o.total_amount || 0)}</td>
								<td><a class="text-thm" href="#order">View details</a></td>
							</tr>
						`;
					});
					$('.recent-orders-preview').html(recentHtml);
                },
                error: function(){
                    window.__return_availability = { orders: {} };

                    var ordersHtml = '';
                    apiOrders.forEach(function(o){
						var stRaw = (typeof o.status !== 'undefined' && o.status !== null) ? o.status : 'pending';
						var st = String(stRaw || 'pending').toLowerCase();
						if (st === 'cancelled') st = 'canceled';
						var canCancel = (st === 'pending' || st === 'confirmed');
						var isDelivered = (st === 'delivered' || st.indexOf('deliver') !== -1);
						var canReturn = isDelivered;
                        ordersHtml += `
                            <li class="mb15 p20 border bdrs12">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <div class="fw600">Order: ${o.order_number || o.id}</div>
                                        <div class="text-muted fz13">Date: ${formatOrderDate(o.date_added || '')}</div>
                                        <div class="text-muted fz13">Status: ${getStatusBadge(st)}</div>
                                        <div class="text-muted fz13">Total: ${fmtMoney(o.final_amount || o.total_amount || 0)}</div>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-dark me-2 view-order-btn" data-order-id="${o.id}">View Details</button>
                                        <a class="btn btn-sm btn-outline-primary me-2" href="<?= base_url('customer/order/download_invoice'); ?>?order_id=${encodeURIComponent(o.id)}" target="_blank">Invoice</a>
                                        ${canCancel ? `<button type="button" class="btn btn-sm btn-outline-danger cancel-order-btn" data-order-id="${o.id}">Cancel Order</button>` : ''}
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    $('.order-box-list').html(ordersHtml);

					var recent = DATA.slice(0, 5);
					var recentHtml = '';
					recent.forEach(function(o){
						var st = (typeof o.status !== 'undefined' && o.status !== null) ? o.status : 'pending';
						st = (st && typeof st === 'object') ? (st.label || st.name || st.status || st.title || 'pending') : st;
						st = String(st || 'pending');
						recentHtml += `
							<tr>
								<td>${o.order_number || o.id}</td>
								<td>${formatOrderDate(o.date_added || '')}</td>
								<td>${getStatusBadge(st)}</td>
								<td>${fmtMoney(o.final_amount || o.total_amount || 0)}</td>
								<td><a class="text-thm" href="#order">View details</a></td>
							</tr>
						`;
					});
					$('.recent-orders-preview').html(recentHtml);
                }
            });
        }
    });
}



if (typeof window.loading !== 'function') {
    window.loading = function(target, isLoading, text) {
        try {
            var $btn = (target && target.jquery) ? target : $(target);
            if (!$btn.length) return;
            if (isLoading) {
                if (typeof $btn.data('original-text') === 'undefined') {
                    $btn.data('original-text', $btn.text());
                }
                $btn.prop('disabled', true);
                if (text) $btn.text(text);
            } else {
                var original = $btn.data('original-text');
                $btn.prop('disabled', false);
                if (original) $btn.text(original);
            }
        } catch (e) {}
    };
}



function formatAddress(addr){
    if(!addr) return '<div class="text-muted">No address found</div>';
    var line1 = [addr.address_1, addr.address_2].filter(Boolean).join(', ');
    var line2 = [addr.city, addr.state].filter(Boolean).join(', ');
    var pc = addr.postcode ? (' - ' + addr.postcode) : '';
    return `
        <div class="fw600">${escHtml(addr.fullname || '')}</div>
        <div class="text-muted fz13">${escHtml(line1)}</div>
        <div class="text-muted fz13">${escHtml(line2)}${escHtml(pc)}</div>
        <div class="text-muted fz13">Phone: ${escHtml(addr.mobile || '')}</div>
    `;
}

function statusSteps(status){
    if (status && typeof status === 'object') {
        status = status.label || status.name || status.status || status.title || '';
    }
    var s = String(status || 'pending').toLowerCase();
    var asInt = parseInt(s, 10) || 0;
    if (asInt > 0) {
        if (asInt === 1) s = 'pending';
        if (asInt === 2) s = 'confirmed';
        if (asInt === 3) s = 'shipped';
        if (asInt === 10) s = 'delivered';
        if (asInt === 17 || asInt === 5) s = 'cancelled';
    }
    var order = ['pending', 'confirmed', 'shipped', 'delivered'];
    if (s === 'cancelled') s = 'cancelled';
    var activeIndex = order.indexOf(s);
    if (activeIndex < 0) activeIndex = 0;
    var html = '<div class="d-flex gap-2 flex-wrap">';
    order.forEach(function(st, idx){
        var cls = idx <= activeIndex ? 'badge bg-success' : 'badge bg-light text-dark';
        html += `<span class="${cls}">${st.charAt(0).toUpperCase() + st.slice(1)}</span>`;
    });
    html += '</div>';
    return html;
}

function renderOrderDetails(order){
    var items = Array.isArray(order.items) ? order.items : [];
    var itemsHtml = '';
    var subtotal = 0;
    var returnedMap = {};
    try {
        var per = (window.__return_availability && window.__return_availability.orders) ? window.__return_availability.orders : {};
        var row = per[String(order.id || '')] || per[order.id] || null;
        var arr = (row && Array.isArray(row.returned_order_item_ids)) ? row.returned_order_item_ids : [];
        arr.forEach(function(v){ returnedMap[String(v)] = 1; });
    } catch (e) {}

    // Detect order-level delivered/cancelled status for fallback
    var orderStatusStr = String(order.status || '').toLowerCase();
    var orderStatusInt  = parseInt(orderStatusStr, 10) || 0;
    if (orderStatusInt === 10) orderStatusStr = 'delivered';
    if (orderStatusInt === 17 || orderStatusInt === 5) orderStatusStr = 'canceled';
    var orderIsDelivered = (orderStatusStr === 'delivered' || orderStatusStr.indexOf('deliver') !== -1);
    var orderIsCancelled = (orderStatusStr === 'canceled' || orderStatusStr === 'cancelled' || orderStatusStr === 'cancel');

    items.forEach(function(it){
        var qty = parseInt(it.quantity || it.qty || 0, 10) || 0;
        var price = parseFloat(it.price || 0) || 0;
        var rowSub = parseFloat(it.subtotal || (qty * price)) || 0;
        subtotal += rowSub;

        var stRaw  = (typeof it.status !== 'undefined' && it.status !== null) ? it.status : '';
        var stStr  = String(stRaw || '').toLowerCase().trim();
        var stInt  = parseInt(stStr, 10);
        if (isNaN(stInt)) stInt = 0;

        var stLabel = stStr;
        if (stInt === 1  || stStr === 'pending')                             stLabel = 'pending';
        if (stInt === 2  || stStr === 'confirmed' || stStr === 'processing') stLabel = 'confirmed';
        if (stInt === 3  || stStr === 'shipped')                             stLabel = 'shipped';
        if (stInt === 10 || stStr === 'delivered')                           stLabel = 'delivered';
        if (stInt === 17 || stInt === 5 || stStr === 'cancelled' || stStr === 'canceled') stLabel = 'canceled';

        var isDeliveredItem = (stLabel === 'delivered');
        var isCancelledItem = (stLabel === 'canceled');

        if (!isDeliveredItem && !isCancelledItem && orderIsDelivered) { isDeliveredItem = true; stLabel = 'delivered'; }
        if (!isCancelledItem && orderIsCancelled && stLabel !== 'delivered') { isCancelledItem = true; stLabel = 'canceled'; }

        var displayStatus = stLabel || orderStatusStr;
        var itemStatusLabel = '';
        try {
            var badge = getStatusBadge(displayStatus);
            if (badge && String(badge).trim() !== '') {
                itemStatusLabel = '<span class="ms-2 fz12">' + badge + '</span>';
            }
        } catch (e) { itemStatusLabel = ''; }

        var orderItemId = it.id || it.order_item_id || 0;
        var returnedBadgeRight = (orderItemId && returnedMap[String(orderItemId)]) ? '<span class="badge bg-warning text-dark ms-1">Returned</span>' : '';

        var deliveredAt = it.last_updated || it.updated_at || order.date_added || '';
        var deliveredTs = deliveredAt ? (new Date(deliveredAt)).getTime() : 0;
        var isWithin7Days = false;
        if (deliveredTs && !isNaN(deliveredTs)) {
            var diffDays = (Date.now() - deliveredTs) / (1000 * 60 * 60 * 24);
            isWithin7Days = (diffDays >= 0 && diffDays <= 7);
        }

        var canReturnItem = (orderItemId && isDeliveredItem && isWithin7Days && !returnedMap[String(orderItemId)]);
        var productName   = escHtml(it.name || it.product_name || '');
        var returnLink    = canReturnItem
            ? '<a href="#" class="btn btn-sm btn-outline-warning ms-2 py-0 return-item-btn"' +
              ' data-order-id="' + escHtml(order.id || '') + '"' +
              ' data-order-item-id="' + escHtml(orderItemId) + '"' +
              ' data-product-id="' + escHtml(it.product_id || '') + '"' +
              ' data-vendor-id="' + escHtml(it.login_id || it.vendor_id || '') + '"' +
              ' data-product-name="' + productName + '">Return</a>'
            : '';

        itemsHtml += '<tr><td>' +
            '<div class="d-flex align-items-center gap-2">' +
            '<img src="' + escHtml(it.image_url || '') + '" onerror="this.src=\'<?= base_url('assets/customer/images/shop/s1.png'); ?>\'" style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid #eee;" />' +
            '<div style="min-width:0;width:100%;"><div class="d-flex flex-wrap align-items-center gap-1">' +
            '<span class="fw600">' + escHtml(it.name || it.product_name || '') + '</span>' +
            itemStatusLabel + returnedBadgeRight + returnLink +
            '</div></div></div>' +
            '</td>' +
            '<td class="text-end">' + qty + '</td>' +
            '<td class="text-end">' + fmtMoney(price) + '</td>' +
            '<td class="text-end">' + fmtMoney(rowSub) + '</td>' +
            '</tr>';
    });

    var shipping  = parseFloat(order.shipping_amount || 0) || 0;
    var discount  = parseFloat(order.discount_amount  || 0) || 0;
    var total     = parseFloat(order.final_amount || (subtotal + shipping - discount)) || 0;
    var orderNo   = escHtml(order.order_number || order.id || '');
    var mainStatus = (typeof order.status !== 'undefined' && order.status !== null) ? order.status : 'pending';

    return '<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb15">' +
        '<div><div class="fw700" style="font-size:18px;">Order #' + orderNo + ' <span class="ms-2">' + getStatusBadge(mainStatus) + '</span></div>' +
        '<div class="text-muted fz13">Placed on ' + escHtml(formatOrderDate(order.date_added || '')) + '</div></div>' +
        '<div class="d-flex gap-2"><a class="btn btn-sm btn-outline-dark" href="<?= base_url('customer/order/download_invoice'); ?>?order_id=' + encodeURIComponent(order.id) + '" target="_blank">Download Invoice</a></div>' +
        '</div>' +
        '<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Subtotal</th></tr></thead>' +
        '<tbody>' + (itemsHtml || '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>') + '</tbody></table></div>' +
        '<div class="row g-3 mt-2">' +
        '<div class="col-lg-6"><div class="p15 border bdrs12 h-100"><div class="fw700 mb10">Shipping Address</div>' + formatAddress(order.address) + '</div></div>' +
        '<div class="col-lg-6"><div class="p15 border bdrs12 h-100">' +
        '<div class="fw700 mb10">Payment &amp; Delivery</div>' +
        '<div class="text-muted fz13">Payment Method: <span class="text-dark">' + escHtml(order.payment_method || 'COD') + '</span></div>' +
        '<div class="text-muted fz13">Payment Status: <span class="text-dark">' + escHtml(order.payment_status || 'pending') + '</span></div><hr>' +
        '<div class="d-flex justify-content-between fz14"><span>Subtotal</span><span>' + fmtMoney(subtotal) + '</span></div>' +
        '<div class="d-flex justify-content-between fz14"><span>Shipping</span><span>' + fmtMoney(shipping) + '</span></div>' +
        '<div class="d-flex justify-content-between fz14"><span>Discount</span><span>' + fmtMoney(discount) + '</span></div>' +
        '<div class="d-flex justify-content-between fz15 mt-2"><span class="fw700">Total</span><span class="fw700">' + fmtMoney(total) + '</span></div>' +
        '</div></div></div>';
}

function formatOrderDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
}

function getStatusBadge(status) {
    if (status && typeof status === 'object') {
        status = status.label || status.name || status.status || status.title || '';
    }
    var st = String(status || 'pending').toLowerCase();
    var asInt = parseInt(st, 10) || 0;
    if (asInt > 0) {
        if (asInt === 1) st = 'pending';
        if (asInt === 2) st = 'confirmed';
        if (asInt === 3) st = 'shipped';
        if (asInt === 10) st = 'delivered';
        if (asInt === 17 || asInt === 5) st = 'canceled';
    }
    if (st === 'cancelled') st = 'canceled';
    var label = st.charAt(0).toUpperCase() + st.slice(1);
    // All statuses: black text on light bg. Cancelled: forced red via inline style.
    if (st === 'canceled') {
        return '<span class="badge bg-light border fw-semibold" style="color:#dc3545!important;">' + label + '</span>';
    }
    return '<span class="badge bg-light text-dark border">' + label + '</span>';
}

function get_addresses(){
    $.ajax({
        url: '<?= base_url("api/v1/Addresses"); ?>',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
        },
        dataType: 'json',
        success: function(resp) {
            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var DATA = (typeof resp.DATA !== 'undefined') ? resp.DATA : resp.data;
            var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
            var addresses = [];
            if (STATUS === 1 && DATA && DATA.addresses) {
                addresses = DATA.addresses;
            }

            window.__profileAddressesById = {};
            addresses.forEach(function(a){
                var _id = a.shipping_address_id || a.id || '';
                if (_id !== '') window.__profileAddressesById[String(_id)] = a;
            });

            var html = '';
            if (!addresses.length) {
                html += '<div class="col-12 text-center text-muted py-4">' + (MSG || 'No saved addresses found') + '</div>';
            } else {
                addresses.forEach(function(addr){
                    var id = addr.shipping_address_id || addr.id || '';
                    var isDefault = String(addr.default_address || '0') === '1';
                    var label = escHtml(addr.address_type || 'Address');
                    var hint = isDefault ? 'Used as your primary delivery address' : 'You can set this as default anytime';
                    var line1 = [addr.address_1, addr.address_2].filter(Boolean).join(', ');
                    var line2 = [addr.city].filter(Boolean).join(', ');
                    var pc = addr.postcode ? (' - ' + addr.postcode) : '';
                    var isSelectedCls = isDefault ? ' selected' : '';
                    var tagHtml = isDefault
                        ? '<span class="address-tag badge-default">Default</span>'
                        : '<span class="address-tag">Saved</span>';

                    html += `
                        <div class="col-md-6 address-col">
                          <div class="address-card${isSelectedCls} h-100" data-address-id="${escHtml(id)}">
                            <div class="d-flex justify-content-between align-items-start mb12">
                              <div>
                                <div class="d-flex align-items-center gap-2">
                                  <h6 class="mb0 fz14 fw600 address-label">${label}</h6>
                                  ${tagHtml}
                                </div>
                                <p class="mb0 fz12 text-muted mt4">${escHtml(hint)}</p>
                              </div>
                              <div class="form-check mb0 d-flex align-items-center">
                                <input class="form-check-input me10 address-radio" type="radio" name="addressRadio" ${isDefault ? 'checked' : ''}>
                                <label class="form-check-label fz12 text-muted">Use for deliveries</label>
                              </div>
                            </div>

                            <p class="mb0 fz13 address-name">${escHtml(addr.fullname || '')}</p>
                            <p class="mb0 fz13 address-line1">${escHtml(line1)}</p>
                            <p class="mb0 fz13 address-line2">${escHtml(line2)}${escHtml(pc)}</p>
                            <p class="mb10 fz13 address-phone">Phone: ${escHtml(addr.mobile || '')}</p>

                            <div class="d-flex justify-content-between align-items-center pt10 mt10 address-actions">
                              <button class="btn btn-link p-0 fz13 text-thm btn-edit-address" type="button">Edit address</button>
                              <div class="d-flex gap-3">
                                <button class="btn btn-link p-0 fz13 text-muted btn-set-default" type="button">Set as default</button>
                                <button class="btn btn-link p-0 fz13 text-danger btn-delete-address" type="button">Delete</button>
                              </div>
                            </div>
                          </div>
                        </div>
                    `;
                });
            }

            html += `
                <div class="col-md-6">
                  <button class="address-card address-add-card w-100 h-100 text-center d-flex flex-column align-items-center justify-content-center" type="button" id="btnAddAddressCard">
                    <span class="address-add-icon">+</span>
                    <p class="mb0 fz13 mt5">Add another address</p>
                  </button>
                </div>
            `;

            $('.address_html').html(html);
        },
        error: function() {
            $('.address_html').html('<div class="col-12 text-center text-muted py-4">Failed to load addresses</div>');
        }
    });
}

function load_wishlist() {
    $('.wishlist_html').html('<div class="text-center text-muted py-4">Loading wishlist...</div>');
    $.ajax({
        url: '<?= base_url("api/v1/Wishlist"); ?>',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>'
        },
        dataType: 'json',
        success: function(resp) {
            var STATUS = (typeof resp.STATUS !== 'undefined') ? resp.STATUS : resp.status;
            var DATA = (typeof resp.DATA !== 'undefined') ? resp.DATA : resp.data;
            
            var items = Array.isArray(DATA) ? DATA : (DATA && DATA.wishlist ? DATA.wishlist : []);

            if (STATUS !== 1 || items.length === 0) {
                $('.wishlist_html').html('<div class="text-center text-muted py-4">Your wishlist is empty</div>');
                return;
            }
            
            var html = '<div class="wishlist-list">';
            items.forEach(function(item) {
                var img = item.image_url ? item.image_url : '<?= base_url("assets/customer/images/shop/s1.png") ?>';
                var productName = item.name || item.product_name || 'Product';
                var price = parseFloat(item.price || 0);
                
                html += `
                <div class="wishlist-row">
                    <div class="wl-left">
                        <img src="${img}" alt="${escHtml(productName)}" class="wl-img">
                        <div class="wl-info">
                            <h6 class="mb5">${escHtml(productName)}</h6>
                            <p class="wl-price">${fmtMoney(price)}</p>
                        </div>
                    </div>
                    <div class="wl-right">
                        <button type="button" class="btn wl-btn-cart wl-add-to-cart" data-product-id="${item.product_id}">Add to Cart</button>
                        <a href="javascript:void(0)" class="wl-btn-remove btn-remove-wishlist" data-product-id="${item.product_id}">Remove</a>
                    </div>
                </div>
                `;
            });
            html += '</div>';
            $('.wishlist_html').html(html);
        },
        error: function() {
            $('.wishlist_html').html('<div class="text-center text-danger py-4">Failed to load wishlist</div>');
        }
    });
}

waitForJQuery(function(){

$(document).on('click', '.btn-remove-wishlist', function() {
    var productId = $(this).data('product-id');
    if (!productId) return;
    if (!confirm('Remove from wishlist?')) return;
    
    var payload = { product_id: productId };
    $.ajax({
        url: '<?= base_url("api/v1/Wishlist/remove"); ?>',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(payload),
        dataType: 'json',
        success: function(resp) {
            var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
            if(typeof showSnackBar === "function") showSnackBar({message: MSG || 'Removed'});
            load_wishlist();
        }
    });
});

$(document).on('click', '#btnAddAddress, #btnAddAddressCard', function(){
    resetAddressForm();
    openAddressModal('add');
});

$(document).on('click', '.btn-edit-address', function(){
    var $card = $(this).closest('.address-card');
    var addressId = $card.data('address-id');
    var addr = null;
    if (typeof window.__profileAddressesById !== 'undefined' && addressId) {
        addr = window.__profileAddressesById[String(addressId)] || null;
    }
    if (!addr) return;
    fillAddressForm(addr);
    openAddressModal('edit');
});

function setDefaultAddress(addressId){
    if(!addressId) return;
    var payload = { address_id: addressId };
    $.ajax({
        url: '<?= base_url("api/v1/Addresses/set_default"); ?>',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
            'Content-Type': 'application/json'
        },
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function(resp){
            var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
            if(typeof showSnackBar === "function") showSnackBar({message: MSG || 'Updated'});
            
            // Update UI locally without full re-render to keep positions fixed
            $('.address-card').removeClass('selected border-warning').addClass('border-light');
            var $card = $('.address-card[data-address-id="' + addressId + '"]');
            $card.addClass('selected border-warning').removeClass('border-light');
            $card.find('.address-radio').prop('checked', true);
            
            // Update the "Default" badge and hint text for all cards
            $('.address-tag').text('Saved').removeClass('badge-default');
            $('.address-card .text-muted.mt4').text('You can set this as default anytime');
            
            $card.find('.address-tag').text('Default').addClass('badge-default');
            $card.find('.text-muted.mt4').text('Used as your primary delivery address');
        },
        error: function(){
            if(typeof showSnackBar === "function") showSnackBar({message:'Failed to set default address'});
        }
    });
}

$(document).on('click', '.btn-set-default', function(){
    var addressId = $(this).closest('.address-card').data('address-id');
    setDefaultAddress(addressId);
});

$(document).on('change', '.address-radio', function(){
    if(!this.checked) return;
    var $card = $(this).closest('.address-card');
    var addressId = $card.data('address-id');
    $('.address-card').removeClass('selected');
    $card.addClass('selected');
    setDefaultAddress(addressId);
});

$(document).on('click', '.address-card', function(e){
    if ($(e.target).closest('button, a, input, label, .address-actions').length) return;
    var $card = $(this);
    var addressId = $card.data('address-id');
    if(!addressId) return;
    $('.address-card').removeClass('selected');
    $card.addClass('selected');
    $card.find('.address-radio').prop('checked', true);
    setDefaultAddress(addressId);
});

$(document).on('click', '.btn-delete-address', function(){
    var addressId = $(this).closest('.address-card').data('address-id');
    if(!addressId) return;
    if(!confirm('Delete this address?')) return;
    var payload = { address_id: addressId };
    $.ajax({
        url: '<?= base_url("api/v1/Addresses/delete"); ?>',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer <?= $customer["access_token"] ?? ($_SESSION["token"] ?? "") ?>',
            'Content-Type': 'application/json'
        },
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function(resp){
            var MSG = (typeof resp.MSG !== 'undefined') ? resp.MSG : resp.msg;
            if(typeof showSnackBar === "function") showSnackBar({message: MSG || 'Deleted'});
            get_addresses();
        },
        error: function(){
            if(typeof showSnackBar === "function") showSnackBar({message:'Failed to delete address'});
        }
    });
});

});


