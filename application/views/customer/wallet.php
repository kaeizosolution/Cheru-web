<?php include('header_cat.php'); ?>
<?php
$wallet_lang = get_page_language_data('wallet_lang');
$dash_lang   = get_page_language_data('dashboard_lang');
?>

<?php
	$customer = $this->session->userdata('customer');
	$customer_name = is_array($customer) && isset($customer['name']) ? $customer['name'] : 'User';
	$_w_symbol = '$';
	$_w_rate = 1.0;
	$_w_cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
	if ($_w_cc_id > 0) {
		$_w_cur = $this->db->select('symbol, rate')->from('ec_currency')->where('currency_id', $_w_cc_id)->where('status', '1')->get()->row();
		if ($_w_cur) {
			$_w_symbol = (string)$_w_cur->symbol;
			if ((float)$_w_cur->rate > 0) $_w_rate = (float)$_w_cur->rate;
		}
	}
?>

<section class="inner_page_breadcrumb">
	<div class="container">
		<div class="row">
			<div class="col-xl-6">
				<div class="breadcrumb_content">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="<?= base_url(); ?>"><?= $dash_lang->dashboard ?? 'Home' ?></a></li>
						<li class="breadcrumb-item"><a href="<?= base_url('customer/profile?tab=dashboard'); ?>"><?= $dash_lang->my_profile ?? 'My Account' ?></a></li>
						<li class="breadcrumb-item active" aria-current="page"><a href="#"><?= $wallet_lang->my_wallet ?? 'Wallet' ?></a></li>
					</ol>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="pt30 pb60">
	<div class="container">
		<div class="row">

			<aside class="col-lg-3 mb30">
				<div class="account-sidebar">
					<div class="account-user-block d-flex align-items-center mb20">
						<div class="account-user-avatar">
							<?php if (is_array($customer) && !empty($customer['user_img'])): ?>
								<img src="<?= $customer['user_img']; ?>" class="rounded-circle" style="width:50px; height:50px; object-fit:cover;">
							<?php else: ?>
								<span class="fa fa-user"></span>
							<?php endif; ?>
						</div>
						<div>
							<p class="account-hello mb0"><?= $dash_lang->hi ?? 'Hello' ?>,</p>
							<h6 class="account-name mb0"><?= htmlspecialchars($customer['name'] ?? 'User'); ?></h6>
						</div>
					</div>

					<ul class="account-nav list-unstyled profile-list">
						<li>
							<a href="<?= base_url('customer/profile?tab=dashboard') ?>">
								<i class="fa fa-home"></i><?= $dash_lang->dashboard ?? 'Dashboard' ?>
							</a>
						</li>

						<li>
							<a href="<?= base_url('customer/profile?tab=order') ?>">
								<i class="fa fa-shopping-bag"></i><?= $dash_lang->my_orders ?? 'My Orders' ?>
							</a>
						</li>

						<li>
							<a href="<?= base_url('customer/returns') ?>">
								<i class="fa fa-undo"></i><?= $dash_lang->my_profile ?? 'My Returns' ?>
							</a>
						</li>

						<li>
							<a href="<?= base_url('customer/profile?tab=address') ?>">
								<i class="fa fa-map-marker"></i><?= $dash_lang->edit ?? 'Addresses' ?>
							</a>
						</li>

						<li>
							<a href="<?= base_url('customer/profile?tab=details') ?>">
								<i class="fa fa-user"></i><?= $dash_lang->personal_information ?? 'Account Details' ?>
							</a>
						</li>

						<li>
							<a href="<?= base_url('customer/profile?tab=wishlist') ?>">
								<i class="fa fa-heart"></i><?= $dash_lang->my_orders ?? 'Wishlist' ?>
							</a>
						</li>

						<li class="active">
							<a href="<?= base_url('customer/profile?tab=wallet') ?>">
								<i class="fa fa-wallet"></i><?= $wallet_lang->my_wallet ?? 'Wallet' ?>
							</a>
						</li>

						<li>
							<a class="logout-link" href="<?= base_url('logout') ?>"><i class="fa fa-sign-out"></i><?= $dash_lang->cancel ?? 'Logout' ?></a>
						</li>
					</ul>
				</div>
			</aside>

			<main class="col-lg-9">
				<div class="card account-card">
					<div class="card-body">

						<div class="d-flex justify-content-between align-items-center mb20">
							<div>
								<h3 class="fz20 mb-1"><?= $wallet_lang->my_wallet ?? 'My Wallet' ?></h3>
								<p class="text-muted mb0 fz13"><?= $wallet_lang->wallet_subtitle ?? 'Manage your wallet balance and transactions' ?></p>
							</div>
						</div>

						<div class="wallet-actions mb25">
							<div class="row g-2">
								<div class="col-md-6">
									<button type="button" class="btn wallet-btn wallet-add w-100" id="btnOpenAddMoney" data-bs-toggle="modal" data-bs-target="#addMoneyModal"><?= $wallet_lang->add_money ?? '+ Add Money' ?></button>
								</div>
								<div class="col-md-6">
									<button type="button" class="btn wallet-btn wallet-withdraw w-100" id="btnOpenWithdraw" data-bs-toggle="modal" data-bs-target="#withdrawModal"><?= $wallet_lang->withdraw ?? 'Withdraw' ?></button>
								</div>
							</div>
						</div>

						<div class="wallet-balance-card mb30">
							<p class="mb5 fz13"><?= $wallet_lang->wallet_balance ?? 'Wallet Balance' ?></p>
							<h2 class="mb0" id="walletBalance"><?= htmlspecialchars($_w_symbol) . ' ' . number_format((float)$wallet_balance * $_w_rate, 2); ?></h2>
						</div>

						<div class="d-flex justify-content-between align-items-center mb15">
							<h6 class="mb0 fw600"><?= $wallet_lang->recent_transactions ?? 'Recent Transactions' ?></h6>
							<a href="<?= base_url('wallet/transactions'); ?>" class="text-primary fz13 fw500"><?= $wallet_lang->view_all ?? 'View All →' ?></a>
						</div>

						<div class="wallet-list">
							<?php if (!empty($transactions)) : ?>
								<?php foreach ($transactions as $tx) : ?>
									<?php
										$tx_type = isset($tx->type) ? strtolower((string)$tx->type) : '';
										$amount_class = ($tx_type === 'credit') ? 'wallet-credit' : 'wallet-debit';
										$title = !empty($tx->transaction_type) ? (string)$tx->transaction_type : (!empty($tx->type) ? (string)$tx->type : 'Transaction');
										$subtitle = !empty($tx->created_at) ? date('d M Y', strtotime($tx->created_at)) : '';
									?>
									<div class="wallet-item">
										<div class="d-flex justify-content-between">
											<div>
												<h6 class="mb2 fz14 fw600"><?= htmlspecialchars($title); ?></h6>
												<p class="mb0 fz13 text-muted"><?= htmlspecialchars($subtitle); ?></p>
											</div>
											<span class="<?= $amount_class; ?>"><?= htmlspecialchars($_w_symbol) . ' ' . number_format((float)$tx->amount * $_w_rate, 2); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="wallet-item">
									<div class="d-flex justify-content-between">
										<div>
											<h6 class="mb2 fz14 fw600"><?= $wallet_lang->no_transactions ?? 'No transactions found' ?></h6>
											<p class="mb0 fz13 text-muted"><?= $wallet_lang->no_transactions_subtitle ?? 'Your recent transactions will appear here' ?></p>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>

					</div>
				</div>
			</main>
		</div>
	</div>
</section>

<div class="modal fade" id="addMoneyModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?= $wallet_lang->add_money_title ?? 'Add Money to Wallet' ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label><?= $wallet_lang->amount ?? 'Amount' ?></label>
					<input type="number" class="form-control" id="addMoneyAmount" min="1" step="0.01" placeholder="<?= $wallet_lang->enter_amount ?? 'Enter Amount' ?>">
				</div>
				<div class="alert alert-danger" id="addMoneyErr" style="display:none"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $dash_lang->cancel ?? 'Cancel' ?></button>
				<button type="button" class="btn btn-primary" id="btnAddMoneySubmit"><?= $wallet_lang->add_money ?? 'Add' ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="withdrawModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?= $wallet_lang->withdraw_title ?? 'Withdraw Money' ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label><?= $wallet_lang->amount ?? 'Amount' ?></label>
					<input type="number" class="form-control" id="withdrawAmount" min="1" step="0.01" placeholder="<?= $wallet_lang->enter_amount ?? 'Enter Amount' ?>">
				</div>
				<div class="alert alert-danger" id="withdrawErr" style="display:none"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $dash_lang->cancel ?? 'Cancel' ?></button>
				<button type="button" class="btn btn-primary" id="btnWithdrawSubmit"><?= $wallet_lang->submit ?? 'Submit' ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="bankModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?= $wallet_lang->bank_details_title ?? 'Add Bank Details' ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label><?= $dash_lang->full_name ?? 'Account Holder Name' ?></label>
							<input type="text" class="form-control" id="bankHolder" placeholder="<?= $dash_lang->full_name ?? 'Name' ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label><?= $wallet_lang->bank_name ?? 'Bank Name' ?></label>
							<input type="text" class="form-control" id="bankName" placeholder="<?= $wallet_lang->bank_name ?? 'Bank' ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label><?= $wallet_lang->account_number ?? 'Account Number' ?></label>
							<input type="text" class="form-control" id="bankAcc" placeholder="<?= $wallet_lang->account_number ?? 'Account number' ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label><?= $wallet_lang->ifsc_code ?? 'IFSC Code' ?></label>
							<input type="text" class="form-control" id="bankIfsc" placeholder="<?= $wallet_lang->ifsc_code ?? 'IFSC' ?>">
						</div>
					</div>
				</div>
				<div class="alert alert-danger" id="bankErr" style="display:none"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $dash_lang->cancel ?? 'Cancel' ?></button>
				<button type="button" class="btn btn-primary" id="btnBankSubmit"><?= $dash_lang->save ?? 'Save' ?></button>
			</div>
		</div>
	</div>
</div>

<script>
	var hasBank = <?= (int)$has_bank; ?>;

	function setErr(id, msg) {
		$('#' + id).text(msg).show();
	}
	function clearErr(id) {
		$('#' + id).hide().text('');
	}

	function updateBalance(balance) {
		if (typeof balance !== 'undefined' && balance !== null) {
			var rate = window.currentCurrencyRate || 1.0;
			var symbol = window.currentCurrencySymbol || '<?= htmlspecialchars($_w_symbol) ?>';
			$('#walletBalance').text(symbol + ' ' + (parseFloat(balance) * rate).toFixed(2));
		}
	}

	function postWithCsrf(url, data, cb) {
		var postData = {};
		postData[csrf.name] = csrf.hash;
		$.extend(postData, data);

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

	$(document).ready(function() {
		$('#btnOpenAddMoney').on('click', function() {
			clearErr('addMoneyErr');
			$('#addMoneyAmount').val('');
		});

		$('#btnOpenWithdraw').on('click', function() {
			clearErr('withdrawErr');
			$('#withdrawAmount').val('');
		});

		$('#btnAddMoneySubmit').on('click', function() {
			clearErr('addMoneyErr');
			var amount = parseFloat($('#addMoneyAmount').val() || 0);
			if (!amount || amount <= 0) {
				setErr('addMoneyErr', 'Amount must be greater than 0.');
				return;
			}

			postWithCsrf('<?= base_url('wallet/ajax_add_money'); ?>', { amount: amount }, function(resp) {
				if (resp && resp.STATUS == 1) {
					if (window.bootstrap && bootstrap.Modal) {
						var inst = bootstrap.Modal.getInstance(document.getElementById('addMoneyModal'));
						if (inst) inst.hide();
					} else {
						$('#addMoneyModal').modal('hide');
					}
					updateBalance(resp.balance);
					return;
				}
				if (resp && resp.need_bank == 1) {
					if (window.bootstrap && bootstrap.Modal) {
						var inst2 = bootstrap.Modal.getInstance(document.getElementById('addMoneyModal'));
						if (inst2) inst2.hide();
						new bootstrap.Modal(document.getElementById('bankModal')).show();
					} else {
						$('#addMoneyModal').modal('hide');
						$('#bankModal').modal('show');
					}
					return;
				}
				setErr('addMoneyErr', (resp && resp.MSG) ? resp.MSG : 'Failed to add money.');
			});
		});

		$('#btnWithdrawSubmit').on('click', function() {
			clearErr('withdrawErr');
			var amount = parseFloat($('#withdrawAmount').val() || 0);
			if (!amount || amount <= 0) {
				setErr('withdrawErr', 'Amount must be greater than 0.');
				return;
			}

			postWithCsrf('<?= base_url('wallet/ajax_withdraw'); ?>', { amount: amount }, function(resp) {
				if (resp && resp.STATUS == 1) {
					if (window.bootstrap && bootstrap.Modal) {
						var inst3 = bootstrap.Modal.getInstance(document.getElementById('withdrawModal'));
						if (inst3) inst3.hide();
					} else {
						$('#withdrawModal').modal('hide');
					}
					updateBalance(resp.balance);
					return;
				}
				setErr('withdrawErr', (resp && resp.MSG) ? resp.MSG : 'Failed to submit withdraw request.');
			});
		});

		$('#btnBankSubmit').on('click', function() {
			clearErr('bankErr');
			var payload = {
				account_holder_name: $('#bankHolder').val(),
				bank_name: $('#bankName').val(),
				account_number: $('#bankAcc').val(),
				ifsc_code: $('#bankIfsc').val()
			};

			postWithCsrf('<?= base_url('wallet/ajax_save_bank'); ?>', payload, function(resp) {
				if (resp && resp.STATUS == 1) {
					hasBank = 1;
					if (window.bootstrap && bootstrap.Modal) {
						var inst4 = bootstrap.Modal.getInstance(document.getElementById('bankModal'));
						if (inst4) inst4.hide();
						new bootstrap.Modal(document.getElementById('addMoneyModal')).show();
					} else {
						$('#bankModal').modal('hide');
						$('#addMoneyModal').modal('show');
					}
					return;
				}
				setErr('bankErr', (resp && resp.MSG) ? resp.MSG : 'Failed to save bank details.');
			});
		});
	});
</script>
