<?php
//=====================================================START SCRIPT====================//

error_reporting(0);

if (!isset($_SESSION["Mikbotamuser"])) {
	header("Location:../admin/login.php");
	exit();
}

include_once '../Api/routeros_api.class.php';
include_once '../config/system.conn.php';
include_once '../config/system.database.php';

$id = $_SESSION['Mikbotamid'];

$API = new routeros_api();
$API->timeout = 3;
$serverhot = [];
$ARRAY     = [];

if (!empty($mikrotik_ip) && $API->connect($mikrotik_ip, $mikrotik_username, $mikrotik_password, $mikrotik_port)) {
	$get_profiles = $API->comm('/ip/hotspot/user/profile/print');
	if (is_array($get_profiles)) {
		$ARRAY = $get_profiles;
	}
	$get_servers = $API->comm('/ip/hotspot/print');
	if (is_array($get_servers)) {
		$serverhot = $get_servers;
	}
	$API->disconnect();
}

$raw_voc  = getvocnon($id);
$vouchers = json_decode($raw_voc, true);
if (!is_array($vouchers)) {
	$vouchers = [];
}
$vouchers = array_values(array_filter($vouchers));

$message = '';

// Handle Form Submission (Add / Edit / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['action_save_voucher'])) {
		$index = isset($_POST['voucher_index']) && $_POST['voucher_index'] !== '' ? intval($_POST['voucher_index']) : -1;

		$item = [
			"id"             => $index >= 0 ? strval($index) : strval(count($vouchers)),
			"Voucher"        => trim($_POST['Voucher']),
			"profile"        => trim($_POST['profile']),
			"server"         => trim($_POST['server']),
			"Limit"          => trim($_POST['Limit']),
			"limit_download" => "",
			"limit_upload"   => "",
			"limit_total"    => trim($_POST['limit_total']),
			"Text_List"      => trim($_POST['Text_List']),
			"type"           => trim($_POST['type']),
			"typechar"       => trim($_POST['typechar']),
			"length"         => strval(intval($_POST['length'])),
			"prefix"         => trim($_POST['prefix']),
			"Color"          => trim($_POST['Color'])
		];

		if ($index >= 0 && isset($vouchers[$index])) {
			$vouchers[$index] = $item;
			$message = '<div class="alert alert-success mg-b-15">Berhasil memperbarui paket voucher non-saldo <strong>' . htmlspecialchars($item['Voucher']) . '</strong>!</div>';
		} else {
			$vouchers[] = $item;
			$message = '<div class="alert alert-success mg-b-15">Berhasil menambahkan paket voucher non-saldo baru <strong>' . htmlspecialchars($item['Voucher']) . '</strong>!</div>';
		}

		$json_save = json_encode(array_values($vouchers));
		upvocnon($json_save, $id);
	} elseif (isset($_POST['action_delete_voucher'])) {
		$del_idx = intval($_POST['delete_index']);
		if (isset($vouchers[$del_idx])) {
			$deleted_name = $vouchers[$del_idx]['Voucher'];
			unset($vouchers[$del_idx]);
			$vouchers = array_values($vouchers);
			$json_save = json_encode($vouchers);
			upvocnon($json_save, $id);
			$message = '<div class="alert alert-info mg-b-15">Berhasil menghapus paket voucher non-saldo <strong>' . htmlspecialchars($deleted_name) . '</strong>!</div>';
		}
	}
}

?>

<div class="sl-pagebody">
	<div class="sl-page-title d-flex justify-content-between align-items-center">
		<div>
			<h5>Settings Voucher (Non-Saldo)</h5>
			<p>Kelola paket voucher Hotspot tanpa sistem saldo/deposit reseller.</p>
		</div>
		<div>
			<a href="?Mikbotam=SettingsVoc" class="btn btn-outline-info mg-r-5"><i class="fa fa-exchange"></i> Pengaturan Voucher Saldo</a>
			<button type="button" class="btn btn-primary" onclick="openAddVoucherModal()"><i class="fa fa-plus-circle mg-r-5"></i> Tambah Paket Voucher</button>
		</div>
	</div>

	<?=$message;?>

	<!-- Voucher Table -->
	<div class="card bd-primary">
		<div class="card-header bg-primary tx-white d-flex align-items-center justify-content-between">
			<span><i class="fa fa-ticket mg-r-5"></i> Daftar Paket Voucher Non-Saldo (Total: <?=count($vouchers);?> Paket)</span>
		</div>
		<div class="card-body pd-0">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover mg-b-0">
					<thead class="thead-colored bg-primary">
						<tr>
							<th>No</th>
							<th>Nama Voucher</th>
							<th>Profile Hotspot</th>
							<th>Server Hotspot</th>
							<th>Limit Waktu / Kuota</th>
							<th>Format Kode</th>
							<th>Warna</th>
							<th>Aksi / Kelola</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($vouchers)): ?>
							<tr>
								<td colspan="8" class="text-center pd-20">Belum ada paket voucher non-saldo disetting. Klik <strong>+ Tambah Paket Voucher</strong> untuk membuat paket baru.</td>
							</tr>
						<?php else: ?>
							<?php foreach ($vouchers as $idx => $v): 
								$color = !empty($v['Color']) ? $v['Color'] : 'bg-primary';
							?>
								<tr>
									<td><?=$idx + 1;?></td>
									<td>
										<strong class="tx-inverse"><?=htmlspecialchars($v['Voucher']);?></strong><br>
										<small class="text-muted"><?=htmlspecialchars($v['Text_List']);?></small>
									</td>
									<td><span class="badge badge-info pd-5-8"><?=htmlspecialchars($v['profile']);?></span></td>
									<td><?=htmlspecialchars($v['server']);?></td>
									<td>
										<i class="fa fa-clock-o"></i> <?=!empty($v['Limit']) ? htmlspecialchars($v['Limit']) : '-';?><br>
										<small class="text-muted"><i class="fa fa-database"></i> <?=!empty($v['limit_total']) ? htmlspecialchars($v['limit_total']) : 'Unlimited';?></small>
									</td>
									<td>
										<small>
											Prefix: <strong><?=!empty($v['prefix']) ? htmlspecialchars($v['prefix']) : '-';?></strong><br>
											Tipe: <strong><?=$v['type'] === 'up' ? 'User & Pass' : 'Voucher Only';?></strong><br>
											Char: <strong><?=htmlspecialchars($v['typechar']);?></strong> (<?=$v['length'];?> Karakter)
										</small>
									</td>
									<td><span class="badge <?=$color;?> tx-white pd-5-10"><?=$color;?></span></td>
									<td>
										<div class="btn-group" role="group">
											<button type="button" class="btn btn-sm btn-primary" onclick='openEditVoucherModal(<?=$idx;?>, <?=json_encode($v);?>)'>
												<i class="fa fa-pencil"></i> Edit
											</button>
											<form method="POST" action="?Mikbotam=SettingsVocnonsaldo" style="display:inline;" onsubmit="return confirm('Hapus paket voucher non-saldo <?=htmlspecialchars($v['Voucher']);?>?');">
												<input type="hidden" name="delete_index" value="<?=$idx;?>">
												<button type="submit" name="action_delete_voucher" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
											</form>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Modal Form Tambah / Edit Voucher -->
	<!-- Modal Add/Edit Voucher Non-Saldo -->
	<div class="modal fade" id="voucherModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content border-0 shadow-lg">
				<form method="POST" action="?Mikbotam=SettingsVocnonsaldo">
					<input type="hidden" name="voucher_index" id="form_voucher_index" value="-1">
					<div class="modal-header bg-primary tx-white pd-y-15 pd-x-20">
						<h5 class="modal-title font-weight-bold d-flex align-items-center mg-b-0" id="modalTitle">
							<i class="fa fa-ticket mg-r-10"></i> Form Paket Voucher Non-Saldo
						</h5>
						<button type="button" class="close tx-white op-8 hover-op-10" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body pd-25">
						
						<!-- Section 1: Informasi Paket -->
						<div class="card bd pd-15 mg-b-20 bg-gray-100">
							<h6 class="tx-13 tx-bold tx-uppercase tx-primary mg-b-15">
								<i class="fa fa-info-circle mg-r-5"></i> 1. Informasi Paket & Tampilan Bot
							</h6>
							<div class="row row-sm">
								<div class="col-md-6 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Nama Paket Voucher <span class="tx-danger">*</span></label>
									<div class="input-group">
										<span class="input-group-addon bg-white"><i class="fa fa-ticket tx-primary"></i></span>
										<input type="text" name="Voucher" id="field_Voucher" class="form-control" placeholder="Contoh: Voucher 3 Jam" required>
									</div>
									<small class="form-text text-muted">Nama singkat paket yang akan ditampilkan.</small>
								</div>
								<div class="col-md-6 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Teks Tombol / Deskripsi Bot <span class="tx-danger">*</span></label>
									<div class="input-group">
										<span class="input-group-addon bg-white"><i class="fa fa-list-alt tx-primary"></i></span>
										<input type="text" name="Text_List" id="field_Text_List" class="form-control" placeholder="Contoh: Paket 3 Jam Unlimited" required>
									</div>
									<small class="form-text text-muted">Teks tombol yang muncul di menu Telegram Bot.</small>
								</div>
							</div>
						</div>

						<!-- Section 2: Profil RouterOS -->
						<div class="card bd pd-15 mg-b-20 bg-gray-100">
							<h6 class="tx-13 tx-bold tx-uppercase tx-primary mg-b-15">
								<i class="fa fa-server mg-r-5"></i> 2. Profil & Server Hotspot MikroTik
							</h6>
							<div class="row row-sm">
								<div class="col-md-6 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Profil Hotspot MikroTik <span class="tx-danger">*</span></label>
									<div class="input-group">
										<span class="input-group-addon bg-white"><i class="fa fa-cogs tx-primary"></i></span>
										<select name="profile" id="field_profile" class="form-control" required>
											<?php if (empty($ARRAY)): ?>
												<option value="">-- Gagal mengambil profil / Router terputus --</option>
											<?php else: ?>
												<?php foreach ($ARRAY as $prof): ?>
													<option value="<?=$prof['name'];?>"><?=$prof['name'];?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
									</div>
									<small class="form-text text-muted">Pilih profil User Profile dari router MikroTik Anda.</small>
								</div>
								<div class="col-md-6 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Server Hotspot Target</label>
									<div class="input-group">
										<span class="input-group-addon bg-white"><i class="fa fa-wifi tx-primary"></i></span>
										<select name="server" id="field_server" class="form-control">
											<option value="all">all (Semua Server Hotspot)</option>
											<?php foreach ($serverhot as $serv): ?>
												<option value="<?=$serv['name'];?>"><?=$serv['name'];?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<small class="form-text text-muted">Pilih server hotspot spesifik atau 'all' untuk semua.</small>
								</div>
							</div>
						</div>

						<!-- Section 3: Limit Waktu & Kuota -->
						<div class="card bd pd-15 mg-b-20 bg-gray-100">
							<h6 class="tx-13 tx-bold tx-uppercase tx-primary mg-b-15">
								<i class="fa fa-clock-o mg-r-5"></i> 3. Batasan Waktu & Kuota
							</h6>
							<div class="row row-sm">
								<div class="col-md-6 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Limit Waktu (Uptime)</label>
									<div class="input-group">
										<span class="input-group-addon bg-white"><i class="fa fa-hourglass-half tx-primary"></i></span>
										<input type="text" name="Limit" id="field_Limit" class="form-control" placeholder="Contoh: 3h, 1d, 30m">
									</div>
									<small class="form-text text-muted">Gunakan: <code>m</code> (Menit), <code>h</code> (Jam), <code>d</code> (Hari). Contoh: <code>3h</code> atau <code>1d</code>.</small>
								</div>
								<div class="col-md-6 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Limit Kuota Data (Bytes)</label>
									<div class="input-group">
										<span class="input-group-addon bg-white"><i class="fa fa-database tx-primary"></i></span>
										<input type="text" name="limit_total" id="field_limit_total" class="form-control" placeholder="Contoh: 500M, 1G">
									</div>
									<small class="form-text text-muted">Gunakan: <code>M</code> (MB) atau <code>G</code> (GB). Kosongkan jika Unlimited.</small>
								</div>
							</div>
						</div>

						<!-- Section 4: Format Kode -->
						<div class="card bd pd-15 mg-b-20 bg-gray-100">
							<h6 class="tx-13 tx-bold tx-uppercase tx-primary mg-b-15">
								<i class="fa fa-key mg-r-5"></i> 4. Format Kode & Prefix Voucher
							</h6>
							<div class="row row-sm">
								<div class="col-md-3 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Prefix (Awalan)</label>
									<input type="text" name="prefix" id="field_prefix" class="form-control" placeholder="Contoh: VC-, NET-">
									<small class="form-text text-muted">Awalan kode voucher.</small>
								</div>
								<div class="col-md-3 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Tipe Login</label>
									<select name="type" id="field_type" class="form-control">
										<option value="vc">Voucher Only (User=Pass)</option>
										<option value="up">Username & Password Beda</option>
									</select>
									<small class="form-text text-muted">Model login voucher.</small>
								</div>
								<div class="col-md-3 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Karakter Kode</label>
									<select name="typechar" id="field_typechar" class="form-control">
										<option value="lower">Huruf Kecil (abc)</option>
										<option value="upper">Huruf Besar (ABC)</option>
										<option value="num">Angka (123)</option>
										<option value="mix">Campuran (aB1)</option>
									</select>
									<small class="form-text text-muted">Jenis karakter acak.</small>
								</div>
								<div class="col-md-3 form-group mg-b-10">
									<label class="font-weight-bold tx-12 tx-gray-700">Panjang Acak</label>
									<input type="number" name="length" id="field_length" class="form-control" value="4" min="3" max="12" required>
									<small class="form-text text-muted">Jumlah karakter acak.</small>
								</div>
							</div>
						</div>

						<!-- Section 5: Warna Tampilan Card -->
						<div class="card bd pd-15 bg-gray-100">
							<h6 class="tx-13 tx-bold tx-uppercase tx-primary mg-b-15">
								<i class="fa fa-paint-brush mg-r-5"></i> 5. Tampilan Visual Card
							</h6>
							<div class="row row-sm">
								<div class="col-md-12 form-group mg-b-0">
									<label class="font-weight-bold tx-12 tx-gray-700">Warna Tema Card Voucher</label>
									<div class="input-group">
										<span class="input-group-addon bg-white"><i class="fa fa-tint tx-primary"></i></span>
										<select name="Color" id="field_Color" class="form-control">
											<option value="bg-primary">Biru (Primary)</option>
											<option value="bg-teal">Teal / Hijau Muda</option>
											<option value="bg-pink">Pink</option>
											<option value="bg-warning">Kuning (Warning)</option>
											<option value="bg-danger">Merah (Danger)</option>
											<option value="bg-info">Cyan (Info)</option>
											<option value="bg-dark">Hitam (Dark)</option>
										</select>
									</div>
									<small class="form-text text-muted">Warna latar belakang kartu voucher pada tampilan daftar.</small>
								</div>
							</div>
						</div>

					</div>
					<div class="modal-footer pd-y-15 pd-x-20 bg-gray-200">
						<button type="button" class="btn btn-secondary pd-x-20" data-dismiss="modal"><i class="fa fa-times mg-r-5"></i> Batal</button>
						<button type="submit" name="action_save_voucher" class="btn btn-primary pd-x-25"><i class="fa fa-save mg-r-5"></i> Simpan Paket Voucher</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
function openAddVoucherModal() {
	document.getElementById('form_voucher_index').value = '-1';
	document.getElementById('modalTitle').innerText = 'Tambah Paket Voucher Non-Saldo';
	document.getElementById('field_Voucher').value = '';
	document.getElementById('field_Text_List').value = '';
	document.getElementById('field_Limit').value = '';
	document.getElementById('field_limit_total').value = '';
	document.getElementById('field_prefix').value = '';
	document.getElementById('field_type').value = 'vc';
	document.getElementById('field_typechar').value = 'lower';
	document.getElementById('field_length').value = '4';
	document.getElementById('field_Color').value = 'bg-primary';
	$('#voucherModal').modal('show');
}

function openEditVoucherModal(idx, v) {
	document.getElementById('form_voucher_index').value = idx;
	document.getElementById('modalTitle').innerText = 'Edit Paket Voucher: ' + v.Voucher;
	document.getElementById('field_Voucher').value = v.Voucher || '';
	document.getElementById('field_Text_List').value = v.Text_List || '';
	document.getElementById('field_profile').value = v.profile || '';
	document.getElementById('field_server').value = v.server || 'all';
	document.getElementById('field_Limit').value = v.Limit || '';
	document.getElementById('field_limit_total').value = v.limit_total || '';
	document.getElementById('field_prefix').value = v.prefix || '';
	document.getElementById('field_type').value = v.type || 'vc';
	document.getElementById('field_typechar').value = v.typechar || 'lower';
	document.getElementById('field_length').value = v.length || '4';
	document.getElementById('field_Color').value = v.Color || 'bg-primary';
	$('#voucherModal').modal('show');
}
</script>
