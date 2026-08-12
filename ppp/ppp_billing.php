<?php
//=====================================================START SCRIPT====================//

error_reporting(0);

if (!isset($_SESSION["Mikbotamuser"])) {
	header("Location:../admin/login.php");
	exit();
}

include_once '../config/system.conn.php';
include_once '../config/system.byte.php';
include_once '../config/system.database.php';
include_once '../Api/routeros_api.class.php';

date_default_timezone_set('Asia/Jakarta');

$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query  = isset($_GET['q']) ? trim($_GET['q']) : '';

$message = '';

$API = new routeros_api();
$API->timeout = 3;

// Handle Generate Invoices for selected month
if (isset($_POST['action_generate'])) {
	if (!empty($mikrotik_ip) && $API->connect($mikrotik_ip, $mikrotik_username, $mikrotik_password, $mikrotik_port)) {
		$secrets = $API->comm("/ppp/secret/print");
		$API->disconnect();

		if (is_array($secrets)) {
			$created = generate_monthly_invoices($current_month, $secrets);
			$message = '<div class="alert alert-success mg-b-15">Berhasil membuat <strong>' . $created . '</strong> tagihan baru untuk periode <strong>' . htmlspecialchars($current_month) . '</strong>!</div>';
		}
	} else {
		$message = '<div class="alert alert-danger mg-b-15">Gagal terhubung ke router MikroTik untuk mengambil daftar PPP Secrets.</div>';
	}
}

// Handle Mark as Paid
if (isset($_POST['action_pay'])) {
	$inv_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
	$method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'CASH';
	$notes  = isset($_POST['notes']) ? trim($_POST['notes']) : '';
	$months = isset($_POST['months']) ? intval($_POST['months']) : 1;

	if ($inv_id > 0) {
		pay_ppp_invoice($inv_id, $method, $notes, $months);

		// If user was disabled/isolated, automatically re-enable on MikroTik
		$inv_data = $mikbotamdata->get('ppp_invoices', ['username_ppp'], ['id' => $inv_id]);
		if ($inv_data && !empty($mikrotik_ip) && $API->connect($mikrotik_ip, $mikrotik_username, $mikrotik_password, $mikrotik_port)) {
			$user_ppp = $inv_data['username_ppp'];
			$find_secret = $API->comm("/ppp/secret/print", ["?name" => $user_ppp]);
			if (isset($find_secret[0]['.id'])) {
				$sec_id = $find_secret[0]['.id'];
				$API->comm("/ppp/secret/enable", [".id" => $sec_id]);

				// Kick active connection to force re-auth
				$find_active = $API->comm("/ppp/active/print", ["?name" => $user_ppp]);
				if (isset($find_active[0]['.id'])) {
					$API->comm("/ppp/active/remove", [".id" => $find_active[0]['.id']]);
				}
			}
			$API->disconnect();
		}

		$message = '<div class="alert alert-success mg-b-15">Tagihan berhasil ditandai <strong>LUNAS</strong> dan koneksi user telah diaktifkan!</div>';
	}
}

// Handle Isolir / Disable Manual
if (isset($_POST['action_isolir'])) {
	$inv_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;

	if ($inv_id > 0) {
		update_ppp_invoice_status($inv_id, 'ISOLIR');
		$inv_data = $mikbotamdata->get('ppp_invoices', ['username_ppp'], ['id' => $inv_id]);

		if ($inv_data && !empty($mikrotik_ip) && $API->connect($mikrotik_ip, $mikrotik_username, $mikrotik_password, $mikrotik_port)) {
			$user_ppp = $inv_data['username_ppp'];
			$find_secret = $API->comm("/ppp/secret/print", ["?name" => $user_ppp]);
			if (isset($find_secret[0]['.id'])) {
				$sec_id = $find_secret[0]['.id'];
				$API->comm("/ppp/secret/disable", [".id" => $sec_id]);

				// Kick active connection to isolate user immediately
				$find_active = $API->comm("/ppp/active/print", ["?name" => $user_ppp]);
				if (isset($find_active[0]['.id'])) {
					$API->comm("/ppp/active/remove", [".id" => $find_active[0]['.id']]);
				}
			}
			$API->disconnect();
		}
		$message = '<div class="alert alert-warning mg-b-15">User PPPoE <strong>' . htmlspecialchars($inv_data['username_ppp']) . '</strong> berhasil di-ISOLIR!</div>';
	}
}

// Handle Adjust Due Date (Tambah / Kurangi Bulan)
if (isset($_POST['action_adjust_exp'])) {
	$username_ppp  = isset($_POST['username_ppp']) ? trim($_POST['username_ppp']) : '';
	$change_months = isset($_POST['change_months']) ? intval($_POST['change_months']) : 0;

	if (!empty($username_ppp) && $change_months != 0) {
		$cust = $mikbotamdata->get('ppp_customers', ['id', 'exp_date', 'due_date'], ['username_ppp' => $username_ppp]);
		$settings = get_ppp_isolir_settings();
		$default_day = intval($settings['due_date']);
		$day = ($cust && !empty($cust['due_date'])) ? intval($cust['due_date']) : $default_day;

		$cur_exp = ($cust && !empty($cust['exp_date'])) ? $cust['exp_date'] : date('Y-m') . '-' . sprintf('%02d', $day);
		$sign = ($change_months > 0) ? "+$change_months month" : "$change_months month";
		$new_exp = date('Y-m-d', strtotime($sign, strtotime($cur_exp)));

		if ($cust) {
			$mikbotamdata->update('ppp_customers', ['exp_date' => $new_exp], ['id' => $cust['id']]);
		} else {
			$mikbotamdata->insert('ppp_customers', [
				'username_ppp' => $username_ppp,
				'due_date' => $day,
				'exp_date' => $new_exp,
				'app_user_id' => get_current_tenant_id()
			]);
		}

		$message = '<div class="alert alert-success mg-b-15">Masa aktif jatuh tempo untuk <strong>' . htmlspecialchars($username_ppp) . '</strong> berhasil diubah menjadi <strong>' . date('d-m-Y', strtotime($new_exp)) . '</strong>!</div>';
	}
}

// Fetch invoices
$invoices = get_ppp_invoices($current_month, $status_filter, $search_query);

// Compute statistics
$total_amount = 0;
$total_paid   = 0;
$total_unpaid = 0;
$total_isolir = 0;

foreach ($invoices as $inv) {
	$amt = intval($inv['amount']);
	$total_amount += $amt;
	if ($inv['status'] === 'PAID') {
		$total_paid += $amt;
	} elseif ($inv['status'] === 'ISOLIR') {
		$total_isolir++;
		$total_unpaid += $amt;
	} else {
		$total_unpaid += $amt;
	}
}

?>

<div class="sl-pagebody">
	<div class="sl-page-title d-flex justify-content-between align-items-center">
		<div>
			<h5>Kelola Tagihan Bulanan PPPoE</h5>
			<p>Manajemen tagihan bulanan, konfirmasi pembayaran, cetak kwitansi, dan kontrol isolir pelanggan PPPoE.</p>
		</div>
		<form method="POST" action="./?Mikbotam=pppbilling&month=<?=$current_month;?>" onsubmit="return confirm('Generate otomatis tagihan bulan periode <?=$current_month;?> dari daftar PPP Secrets MikroTik?');">
			<button type="submit" name="action_generate" class="btn btn-primary"><i class="fa fa-refresh mg-r-5"></i> Generate Tagihan Periode Ini</button>
		</form>
	</div>

	<?=$message;?>

	<!-- Statistics Cards -->
	<div class="row row-sm mg-b-15">
		<div class="col-sm-6 col-xl-3">
			<div class="card pd-15 bg-primary tx-white">
				<div class="d-flex align-items-center">
					<i class="fa fa-money tx-40 mg-r-15"></i>
					<div>
						<span class="tx-12 text-uppercase">Total Tagihan</span>
						<h5 class="mg-b-0">Rp <?=number_format($total_amount, 0, ',', '.');?></h5>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3 mg-t-10 mg-sm-t-0">
			<div class="card pd-15 bg-success tx-white">
				<div class="d-flex align-items-center">
					<i class="fa fa-check-circle tx-40 mg-r-15"></i>
					<div>
						<span class="tx-12 text-uppercase">Total Lunas</span>
						<h5 class="mg-b-0">Rp <?=number_format($total_paid, 0, ',', '.');?></h5>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3 mg-t-10 mg-xl-t-0">
			<div class="card pd-15 bg-warning tx-white">
				<div class="d-flex align-items-center">
					<i class="fa fa-clock-o tx-40 mg-r-15"></i>
					<div>
						<span class="tx-12 text-uppercase">Belum Dibayar</span>
						<h5 class="mg-b-0">Rp <?=number_format($total_unpaid, 0, ',', '.');?></h5>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-xl-3 mg-t-10 mg-xl-t-0">
			<div class="card pd-15 bg-danger tx-white">
				<div class="d-flex align-items-center">
					<i class="fa fa-ban tx-40 mg-r-15"></i>
					<div>
						<span class="tx-12 text-uppercase">Terasolir</span>
						<h5 class="mg-b-0"><?=$total_isolir;?> User</h5>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Filter Bar -->
	<div class="card pd-15 mg-b-15">
		<form method="GET" action="./" class="row row-xs align-items-center">
			<input type="hidden" name="Mikbotam" value="pppbilling">
			<div class="col-md-3">
				<label class="tx-12 font-weight-bold">Periode Bulan:</label>
				<input type="month" name="month" class="form-control" value="<?=$current_month;?>" onchange="this.form.submit()">
			</div>
			<div class="col-md-3">
				<label class="tx-12 font-weight-bold">Status Pembayaran:</label>
				<select name="status" class="form-control" onchange="this.form.submit()">
					<option value="">-- Semua Status --</option>
					<option value="UNPAID" <?=$status_filter === 'UNPAID' ? 'selected' : '';?>>Belum Lunas (UNPAID)</option>
					<option value="PAID" <?=$status_filter === 'PAID' ? 'selected' : '';?>>Lunas (PAID)</option>
					<option value="ISOLIR" <?=$status_filter === 'ISOLIR' ? 'selected' : '';?>>Terasolir (ISOLIR)</option>
				</select>
			</div>
			<div class="col-md-4">
				<label class="tx-12 font-weight-bold">Cari Username PPPoE:</label>
				<input type="text" name="q" class="form-control" placeholder="Ketik username..." value="<?=htmlspecialchars($search_query);?>">
			</div>
			<div class="col-md-2 mg-t-20">
				<button type="submit" class="btn btn-secondary btn-block"><i class="fa fa-search"></i> Cari</button>
			</div>
		</form>
	</div>

	<!-- Invoice Table -->
	<div class="card bd-primary">
		<div class="card-header bg-primary tx-white d-flex align-items-center justify-content-between">
			<span><i class="fa fa-list mg-r-5"></i> Daftar Invoice Periode <?=htmlspecialchars($current_month);?></span>
		</div>
		<div class="card-body pd-0">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover mg-b-0">
					<thead class="thead-colored bg-primary">
						<tr>
							<th>No</th>
							<th>No. Invoice</th>
							<th>Username PPPoE</th>
							<th>Periode</th>
							<th>Jatuh Tempo / Expired</th>
							<th>Jumlah Tagihan</th>
							<th>Status</th>
							<th>Tgl & Metode Bayar</th>
							<th>Aksi / Kelola</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (empty($invoices)) {
							echo '<tr><td colspan="9" class="text-center pd-20">Belum ada data tagihan untuk periode ini. Klik <strong>Generate Tagihan Periode Ini</strong> untuk membuat otomatis dari daftar MikroTik Secret.</td></tr>';
						} else {
							$no = 1;
							foreach ($invoices as $inv) {
								$st = $inv['status'];
								if ($st === 'PAID') {
									$badge = '<span class="badge badge-success tx-12 pd-5-10">LUNAS</span>';
								} elseif ($st === 'ISOLIR') {
									$badge = '<span class="badge badge-danger tx-12 pd-5-10">TERASOLIR</span>';
								} else {
									$badge = '<span class="badge badge-warning tx-12 pd-5-10">BELUM BAYAR</span>';
								}

								$cust_info = $mikbotamdata->get('ppp_customers', ['exp_date', 'due_date'], ['username_ppp' => $inv['username_ppp']]);
								$exp_disp = ($cust_info && !empty($cust_info['exp_date'])) ? $cust_info['exp_date'] : $inv['month_year'] . '-' . sprintf('%02d', ($cust_info && !empty($cust_info['due_date']) ? $cust_info['due_date'] : 20));
								?>
								<tr>
									<td><?=$no++;?></td>
									<td><strong class="tx-inverse"><?=htmlspecialchars($inv['invoice_number']);?></strong></td>
									<td><strong class="tx-primary"><?=htmlspecialchars($inv['username_ppp']);?></strong></td>
									<td><?=htmlspecialchars($inv['month_year']);?></td>
									<td>
										<div class="d-flex align-items-center justify-content-between">
											<span class="badge tx-12 pd-6-10 font-weight-bold" style="background-color: #1d212a; color: #ffffff; border: 1px solid #343a40; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
												<i class="fa fa-calendar text-info mg-r-3"></i> <?=$exp_disp;?>
											</span>
											<div class="btn-group mg-l-5" role="group">
												<form method="POST" action="./?Mikbotam=pppbilling&month=<?=$current_month;?>" style="display:inline;">
													<input type="hidden" name="username_ppp" value="<?=htmlspecialchars($inv['username_ppp']);?>">
													<input type="hidden" name="change_months" value="-1">
													<button type="submit" name="action_adjust_exp" class="btn btn-xs btn-outline-danger" title="Kurangi 1 Bulan Jatuh Tempo (-1 Bln)" style="padding: 1px 6px; font-size: 11px;"><i class="fa fa-minus"></i></button>
												</form>
												<form method="POST" action="./?Mikbotam=pppbilling&month=<?=$current_month;?>" style="display:inline;">
													<input type="hidden" name="username_ppp" value="<?=htmlspecialchars($inv['username_ppp']);?>">
													<input type="hidden" name="change_months" value="1">
													<button type="submit" name="action_adjust_exp" class="btn btn-xs btn-outline-success" title="Tambah 1 Bulan Jatuh Tempo (+1 Bln)" style="padding: 1px 6px; font-size: 11px;"><i class="fa fa-plus"></i></button>
												</form>
											</div>
										</div>
									</td>
									<td><strong>Rp <?=number_format($inv['amount'], 0, ',', '.');?></strong></td>
									<td><?=$badge;?></td>
									<td>
										<?php if ($st === 'PAID'): ?>
											<small><i class="fa fa-calendar"></i> <?=$inv['payment_date'];?><br><i class="fa fa-credit-card"></i> <?=$inv['payment_method'];?></small>
										<?php else: ?>
											<span class="text-muted">-</span>
										<?php endif; ?>
									</td>
									<td>
										<div class="btn-group" role="group">
											<?php if ($st !== 'PAID'): ?>
												<!-- Pay Modal Trigger -->
												<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#payModal<?=$inv['id'];?>">
													<i class="fa fa-check"></i> Bayar
												</button>
												<form method="POST" action="./?Mikbotam=pppbilling&month=<?=$current_month;?>" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin meng-ISOLIR pelanggan ini?');">
													<input type="hidden" name="invoice_id" value="<?=$inv['id'];?>">
													<button type="submit" name="action_isolir" class="btn btn-sm btn-warning"><i class="fa fa-ban"></i> Isolir</button>
												</form>
											<?php else: ?>
												<!-- Print Receipt -->
												<button type="button" class="btn btn-sm btn-info" onclick="printReceipt('<?=$inv['invoice_number'];?>', '<?=$inv['username_ppp'];?>', '<?=$inv['month_year'];?>', '<?=$inv['amount'];?>', '<?=$inv['payment_date'];?>', '<?=$inv['payment_method'];?>')">
													<i class="fa fa-print"></i> Struk
												</button>
											<?php endif; ?>
										</div>

										<!-- Pay Modal -->
										<div class="modal fade" id="payModal<?=$inv['id'];?>" tabindex="-1" role="dialog" aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered" role="document">
												<div class="modal-content">
													<form method="POST" action="./?Mikbotam=pppbilling&month=<?=$current_month;?>">
														<input type="hidden" name="invoice_id" value="<?=$inv['id'];?>">
														<div class="modal-header bg-success tx-white">
															<h6 class="modal-title font-weight-bold">Konfirmasi Pembayaran Tagihan</h6>
															<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
																<span aria-hidden="true">&times;</span>
															</button>
														</div>
														<div class="modal-body pd-20">
															<p>Username PPPoE: <strong><?=htmlspecialchars($inv['username_ppp']);?></strong></p>
															<p>No Invoice: <strong><?=htmlspecialchars($inv['invoice_number']);?></strong></p>
															<p>Total Tagihan: <strong class="tx-success tx-18">Rp <?=number_format($inv['amount'], 0, ',', '.');?></strong></p>
															<hr>
															<div class="form-group">
																<label class="font-weight-bold">Durasi Pembayaran:</label>
																<select name="months" class="form-control">
																	<option value="1">1 Bulan (Normal)</option>
																	<option value="2">2 Bulan sekaligus</option>
																	<option value="3">3 Bulan sekaligus</option>
																	<option value="6">6 Bulan sekaligus</option>
																	<option value="12">12 Bulan (1 Tahun)</option>
																</select>
															</div>
															<div class="form-group">
																<label class="font-weight-bold">Metode Pembayaran:</label>
																<select name="payment_method" class="form-control">
																	<option value="CASH">Tunai / Cash</option>
																	<option value="BANK_TRANSFER">Transfer Bank</option>
																	<option value="QRIS">QRIS / E-Wallet</option>
																	<option value="TELEGRAM_BOT">Bot Telegram</option>
																</select>
															</div>
															<div class="form-group">
																<label class="font-weight-bold">Catatan Pembayaran (Opsional):</label>
																<input type="text" name="notes" class="form-control" placeholder="Contoh: Diterima oleh Kasir A">
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
															<button type="submit" name="action_pay" class="btn btn-success"><i class="fa fa-check"></i> Simpan Lunas</button>
														</div>
													</form>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Receipt Modal & Thermal Printer Script -->
<script type="text/javascript">
function printReceipt(invNo, username, month, amount, date, method) {
	var formattedAmount = new Intl.NumberFormat('id-ID').format(amount);
	var receiptWindow = window.open('', 'KWITANSI_PEMBAYARAN', 'width=400,height=600');
	receiptWindow.document.write('<html><head><title>Struk Pembayaran PPPoE</title>');
	receiptWindow.document.write('<style>body{font-family:monospace;font-size:12px;margin:10px;text-align:center;} table{width:100%;text-align:left;} .line{border-top:1px dashed #000;margin:10px 0;} .total{font-size:14px;font-weight:bold;}</style>');
	receiptWindow.document.write('</head><body>');
	receiptWindow.document.write('<h3>MIKBOTAM BILLING</h3>');
	receiptWindow.document.write('<p>Bukti Pembayaran Tagihan PPPoE</p>');
	receiptWindow.document.write('<div class="line"></div>');
	receiptWindow.document.write('<table>');
	receiptWindow.document.write('<tr><td>No Invoice</td><td>: ' + invNo + '</td></tr>');
	receiptWindow.document.write('<tr><td>Username</td><td>: ' + username + '</td></tr>');
	receiptWindow.document.write('<tr><td>Periode</td><td>: ' + month + '</td></tr>');
	receiptWindow.document.write('<tr><td>Metode</td><td>: ' + method + '</td></tr>');
	receiptWindow.document.write('<tr><td>Tgl Bayar</td><td>: ' + date + '</td></tr>');
	receiptWindow.document.write('</table>');
	receiptWindow.document.write('<div class="line"></div>');
	receiptWindow.document.write('<p class="total">TOTAL: Rp ' + formattedAmount + '</p>');
	receiptWindow.document.write('<p>STATUS: LUNAS / PAID</p>');
	receiptWindow.document.write('<div class="line"></div>');
	receiptWindow.document.write('<p>Terima kasih atas pembayaran Anda.</p>');
	receiptWindow.document.write('</body></html>');
	receiptWindow.document.close();
	receiptWindow.print();
}
</script>
