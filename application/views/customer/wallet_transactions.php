<?php include('header_cat.php'); ?>
<?php
$wallet_lang = get_page_language_data('wallet_lang');
$dash_lang   = get_page_language_data('dashboard_lang');
?>

<?php
	$customer = $this->session->userdata('customer');
	$customer_name = is_array($customer) && isset($customer['name']) ? $customer['name'] : 'User';
	$_wt_symbol = '$';
	$_wt_rate = 1.0;
	$_wt_cc_id = isset($_SESSION['cur']) ? (int)$_SESSION['cur'] : 0;
	if ($_wt_cc_id > 0) {
		$_wt_cur = $this->db->select('symbol, rate')->from('ec_currency')->where('currency_id', $_wt_cc_id)->where('status', '1')->get()->row();
		if ($_wt_cur) {
			$_wt_symbol = (string)$_wt_cur->symbol;
			if ((float)$_wt_cur->rate > 0) $_wt_rate = (float)$_wt_cur->rate;
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
						<li class="breadcrumb-item active" aria-current="page"><a href="#"><?= $wallet_lang->transactions ?? 'Transactions' ?></a></li>
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
								<h3 class="fz20 mb-1"><?= $wallet_lang->all_transactions ?? 'All Transactions' ?></h3>
								<p class="text-muted mb0 fz13"><?= $wallet_lang->track_activities ?? 'Track all your wallet activities' ?></p>
							</div>
						</div>

						<?php
							$months = array();
							if (!empty($transactions)) {
								foreach ($transactions as $trow) {
									if (!empty($trow->created_at)) {
										$mon = strtolower(date('M', strtotime($trow->created_at)));
										$months[$mon] = date('F', strtotime($trow->created_at));
									}
								}
							}
						?>
						<div class="row g-2 mb20">
							<div class="col-md-4">
								<select id="monthFilter" class="form-control">
									<option value="all"><?= $wallet_lang->all_months ?? 'All Months' ?></option>
									<?php foreach ($months as $mkey => $mlabel) : ?>
										<option value="<?= htmlspecialchars($mkey); ?>"><?= htmlspecialchars($mlabel); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4">
								<select id="typeFilter" class="form-control">
									<option value="all"><?= $wallet_lang->all_types ?? 'All Types' ?></option>
									<option value="credit"><?= $wallet_lang->credit ?? 'Credit' ?></option>
									<option value="debit"><?= $wallet_lang->debit ?? 'Debit' ?></option>
								</select>
							</div>
							<div class="col-md-4">
								<input id="searchInput" type="text" class="form-control" placeholder="<?= $wallet_lang->search_transaction_placeholder ?? 'Search transaction...' ?>">
							</div>
						</div>

						<div class="wallet-list" id="transactionList">
							<?php if (!empty($transactions)) : ?>
								<?php foreach ($transactions as $tx) : ?>
									<?php
										$tx_type = isset($tx->type) ? strtolower((string)$tx->type) : '';
										$is_credit = ($tx_type === 'credit');
										$amount_class = $is_credit ? 'wallet-credit' : 'wallet-debit';
										$title = !empty($tx->transaction_type) ? (string)$tx->transaction_type : (!empty($tx->type) ? (string)$tx->type : 'Transaction');
										$date_text = !empty($tx->created_at) ? date('d M Y', strtotime($tx->created_at)) : '';
										$month_key = !empty($tx->created_at) ? strtolower(date('M', strtotime($tx->created_at))) : 'all';
										$amount_prefix = $is_credit ? '+ ' : '- ';
									?>
									<div class="wallet-item" data-type="<?= $is_credit ? 'credit' : 'debit'; ?>" data-month="<?= htmlspecialchars($month_key); ?>" data-name="<?= htmlspecialchars($title); ?>" onclick="openTransaction(this)">
										<div class="d-flex justify-content-between">
											<div>
												<h6 class="mb2 fz14 fw600"><?= htmlspecialchars($title); ?></h6>
												<p class="mb0 fz13 text-muted"><?= htmlspecialchars($date_text); ?></p>
											</div>
											<span class="<?= $amount_class; ?>"><?= $amount_prefix; ?><?= htmlspecialchars($_wt_symbol) . ' ' . number_format((float)$tx->amount * $_wt_rate, 2); ?></span>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="wallet-item">
									<div class="d-flex justify-content-between">
										<div>
											<h6 class="mb2 fz14 fw600"><?= $wallet_lang->no_transactions ?? 'No transactions found' ?></h6>
											<p class="mb0 fz13 text-muted"><?= $wallet_lang->try_again_later ?? 'Try again later' ?></p>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>

						<div class="mt20">
							<a href="<?= base_url('wallet'); ?>" class="btn btn-light"><?= $wallet_lang->back_to_wallet ?? 'Back to Wallet' ?></a>
						</div>

					</div>
				</div>
			</main>

		</div>
	</div>
</section>

<div class="modal fade" id="transactionModal">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content p-4">
			<h5 id="txnTitle"></h5>
			<p class="text-muted" id="txnDate"></p>
			<h4 id="txnAmount" class="mb20"></h4>
			<button class="btn btn-dark w-100" data-bs-dismiss="modal"><?= $dash_lang->close ?? 'Close' ?></button>
		</div>
	</div>
</div>

<script>
	function openTransaction(el) {
		var title = el.getAttribute('data-name') || '';
		var dateEl = el.querySelector('.text-muted');
		var amountEl = el.querySelector('span');
		document.getElementById('txnTitle').innerText = title;
		document.getElementById('txnDate').innerText = dateEl ? dateEl.innerText : '';
		document.getElementById('txnAmount').innerText = amountEl ? amountEl.innerText : '';
		if (window.bootstrap && bootstrap.Modal) {
			new bootstrap.Modal(document.getElementById('transactionModal')).show();
		}
	}

	function applyFilters() {
		var month = document.getElementById('monthFilter').value;
		var type = document.getElementById('typeFilter').value;
		var q = (document.getElementById('searchInput').value || '').toLowerCase();
		var items = document.querySelectorAll('#transactionList .wallet-item');
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

	document.addEventListener('DOMContentLoaded', function() {
		var mf = document.getElementById('monthFilter');
		var tf = document.getElementById('typeFilter');
		var si = document.getElementById('searchInput');
		if (mf) mf.addEventListener('change', applyFilters);
		if (tf) tf.addEventListener('change', applyFilters);
		if (si) si.addEventListener('input', applyFilters);
	});
</script>
