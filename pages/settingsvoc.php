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

$raw_voc  = getvoc($id);
$vouchers = json_decode($raw_voc, true);
if (!is_array($vouchers)) {
	$vouchers = [];
}
// Re-index array values cleanly
$vouchers = array_values(array_filter($vouchers));

$message = '';

// Handle Form Submission (Add / Edit / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['action_save_voucher'])) {
		$index = isset($_POST['voucher_index']) && $_POST['voucher_index'] !== '' ? intval($_POST['voucher_index']) : -1;

		$item = [
			"id"             => $index >= 0 ? strval($index) : strval(count($vouchers)),
			"Voucher"        => trim($_POST['Voucher']),
			"price"          => strval(intval($_POST['price'])),
			"markup"         => strval(intval($_POST['markup'])),
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
			$message = '<div class="alert alert-success mg-b-15">Berhasil memperbarui paket voucher <strong>' . htmlspecialchars($item['Voucher']) . '</strong>!</div>';
		} else {
			$vouchers[] = $item;
			$message = '<div class="alert alert-success mg-b-15">Berhasil menambahkan paket voucher baru <strong>' . htmlspecialchars($item['Voucher']) . '</strong>!</div>';
		}

		$json_save = json_encode(array_values($vouchers));
		upvoc($json_save, $id);
	} elseif (isset($_POST['action_delete_voucher'])) {
		$del_idx = intval($_POST['delete_index']);
		if (isset($vouchers[$del_idx])) {
			$deleted_name = $vouchers[$del_idx]['Voucher'];
			unset($vouchers[$del_idx]);
			$vouchers = array_values($vouchers);
			$json_save = json_encode($vouchers);
			upvoc($json_save, $id);
			$message = '<div class="alert alert-info mg-b-15">Berhasil menghapus paket voucher <strong>' . htmlspecialchars($deleted_name) . '</strong>!</div>';
		}
	}
}

?>

<div class="sl-pagebody">
	<div class="sl-page-title d-flex justify-content-between align-items-center">
		<div>
			<h5>Settings Voucher (Sistem Saldo)</h5>
			<p>Kelola paket voucher Hotspot, harga beli, markup harga jual, dan profil untuk Bot Telegram.</p>
		</div>
		<div>
			<a href="?Mikbotam=SettingsVocnonsaldo" class="btn btn-outline-info mg-r-5"><i class="fa fa-exchange"></i> Pengaturan Non-Saldo</a>
			<button type="button" class="btn btn-primary" onclick="openAddVoucherModal()"><i class="fa fa-plus-circle mg-r-5"></i> Tambah Paket Voucher</button>
		</div>
	</div>

	<?=$message;?>

	<!-- Voucher Table -->
	<div class="card bd-primary">
		<div class="card-header bg-primary tx-white d-flex align-items-center justify-content-between">
			<span><i class="fa fa-ticket mg-r-5"></i> Daftar Paket Voucher Saldo (Total: <?=count($vouchers);?> Paket)</span>
		</div>
		<div class="card-body pd-0">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover mg-b-0">
					<thead class="thead-colored bg-primary">
						<tr>
							<th>No</th>
							<th>Nama Voucher</th>
							<th>Profile Hotspot</th>
							<th>Harga Pokok</th>
							<th>Harga Jual (Markup)</th>
							<th>Limit Waktu / Kuota</th>
							<th>Format Kode</th>
							<th>Warna</th>
							<th>Aksi / Kelola</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($vouchers)): ?>
							<tr>
								<td colspan="9" class="text-center pd-20">Belum ada paket voucher disetting. Klik <strong>+ Tambah Paket Voucher</strong> untuk membuat paket baru.</td>
							</tr>
						<?php else: ?>
							<?php foreach ($vouchers as $idx => $v): 
								$price  = intval($v['price']);
								$markup = intval($v['markup']);
								$total  = $price + $markup;
								$color  = !empty($v['Color']) ? $v['Color'] : 'bg-primary';
							?>
								<tr>
									<td><?=$idx + 1;?></td>
									<td>
										<strong class="tx-inverse"><?=htmlspecialchars($v['Voucher']);?></strong><br>
										<small class="text-muted"><?=htmlspecialchars($v['Text_List']);?></small>
									</td>
									<td><span class="badge badge-info pd-5-8"><?=htmlspecialchars($v['profile']);?></span></td>
									<td>Rp <?=number_format($price, 0, ',', '.');?></td>
									<td>
										<span class="tx-success font-weight-bold">Rp <?=number_format($markup, 0, ',', '.');?></span><br>
										<small class="text-muted">Total: Rp <?=number_format($total, 0, ',', '.');?></small>
									</td>
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
											<form method="POST" action="?Mikbotam=SettingsVoc" style="display:inline;" onsubmit="return confirm('Hapus paket voucher <?=htmlspecialchars($v['Voucher']);?>?');">
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

<!-- Modal Add/Edit Voucher -->
<div class="modal fade" id="voucherModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<form method="POST" action="?Mikbotam=SettingsVoc">
				<input type="hidden" name="voucher_index" id="form_voucher_index" value="-1">
				<div class="modal-header bg-primary tx-white">
					<h5 class="modal-title" id="modalTitle">Tambah Paket Voucher Baru</h5>
					<button type="button" class="close tx-white" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body pd-20">
					<div class="row row-sm">
						<!-- Nama Paket & Keterangan -->
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Nama Paket Voucher:</label>
							<input type="text" name="Voucher" id="field_Voucher" class="form-control" placeholder="Contoh: 1 Jam 2rb" required>
						</div>
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Teks Tombol / Deskripsi Bot:</label>
							<input type="text" name="Text_List" id="field_Text_List" class="form-control" placeholder="Contoh: 1 Jam - Rp 2.000" required>
						</div>

						<!-- Profil MikroTik & Server -->
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Profil Hotspot MikroTik:</label>
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
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Server Hotspot:</label>
							<select name="server" id="field_server" class="form-control">
								<option value="all">all (Semua Server)</option>
								<?php foreach ($serverhot as $serv): ?>
									<option value="<?=$serv['name'];?>"><?=$serv['name'];?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<!-- Harga Beli & Harga Jual -->
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Harga Modal / Potong Saldo (Rp):</label>
							<input type="number" name="price" id="field_price" class="form-control" placeholder="2000" required>
						</div>
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Harga Jual / Markup Reseller (Rp):</label>
							<input type="number" name="markup" id="field_markup" class="form-control" placeholder="3000" required>
						</div>

						<!-- Limit Waktu & Kuota -->
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Limit Waktu (Uptime):</label>
							<input type="text" name="Limit" id="field_Limit" class="form-control" placeholder="Contoh: 1h, 1d, 30m">
						</div>
						<div class="col-md-6 form-group">
							<label class="font-weight-bold">Limit Kuota (Bytes):</label>
							<input type="text" name="limit_total" id="field_limit_total" class="form-control" placeholder="Contoh: 500M, 1G (Kosongkan jika Unlimited)">
						</div>

						<!-- Format Kode -->
						<div class="col-md-3 form-group">
							<label class="font-weight-bold">Prefix Kode (Awalan):</label>
							<input type="text" name="prefix" id="field_prefix" class="form-control" placeholder="Contoh: VC-, NET-">
						</div>
						<div class="col-md-3 form-group">
							<label class="font-weight-bold">Tipe Login Voucher:</label>
							<select name="type" id="field_type" class="form-control">
								<option value="vc">Voucher Only (User = Pass)</option>
								<option value="up">Username & Password Beda</option>
							</select>
						</div>
						<div class="col-md-3 form-group">
							<label class="font-weight-bold">Karakter Kode:</label>
							<select name="typechar" id="field_typechar" class="form-control">
								<option value="lower">Huruf Kecil (abc)</option>
								<option value="upper">Huruf Besar (ABC)</option>
								<option value="num">Angka (123)</option>
								<option value="mix">Campuran (aB1)</option>
							</select>
						</div>
						<div class="col-md-3 form-group">
							<label class="font-weight-bold">Panjang Kode (Karakter):</label>
							<input type="number" name="length" id="field_length" class="form-control" value="4" min="3" max="12" required>
						</div>

						<!-- Warna Card -->
						<div class="col-md-12 form-group">
							<label class="font-weight-bold">Warna Card Tampilan:</label>
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
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
					<button type="submit" name="action_save_voucher" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Paket Voucher</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
function openAddVoucherModal() {
	document.getElementById('form_voucher_index').value = '-1';
	document.getElementById('modalTitle').innerText = 'Tambah Paket Voucher Baru';
	document.getElementById('field_Voucher').value = '';
	document.getElementById('field_Text_List').value = '';
	document.getElementById('field_price').value = '';
	document.getElementById('field_markup').value = '';
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
	document.getElementById('field_price').value = v.price || '0';
	document.getElementById('field_markup').value = v.markup || '0';
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