<?php
// Security & Role check
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}

include_once __DIR__ . '/../config/system.conn.php';
include_once __DIR__ . '/../config/system.database.php';
include_once __DIR__ . '/../Api/routeros_api.class.php';

// Auto-resolve role from database if not yet set in session
if (!isset($_SESSION['app_user_role']) && isset($_SESSION['Mikbotamuser'])) {
    $u = get_app_user_by_username($_SESSION['Mikbotamuser']);
    if ($u && isset($u['role'])) {
        $_SESSION['app_user_role'] = $u['role'];
        $_SESSION['app_user_id']   = $u['id'];
        $_SESSION['app_full_name'] = $u['full_name'];
    }
}

$current_role = isset($_SESSION['app_user_role']) ? $_SESSION['app_user_role'] : 'user';

if ($current_role !== 'superadmin') {
	echo '<div class="pd-20 text-center">
			<div class="alert alert-danger" role="alert">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Akses Ditolak!</h4>
				<p>Halaman ini khusus untuk SuperAdmin. Anda tidak memiliki wewenang untuk mengelola akun pengguna.</p>
			</div>
		  </div>';
	return;
}

$message_alert = '';

// Handle Impersonate / Stop Impersonate
if (isset($_POST['action_impersonate'])) {
	$target_id = intval($_POST['user_id']);
	$_SESSION['impersonate_user_id'] = $target_id;
	$target_user = get_app_user_by_id($target_id);
	$message_alert = '<div class="alert alert-info alert-dismissible fade show" role="alert">
						<i class="fa fa-user-secret"></i> Berhasil beralih mode. Saat ini Anda sedang mengakses dashboard sebagai: <strong>' . htmlspecialchars($target_user['full_name']) . '</strong>.
						<a href="./?Mikbotam=manageusers&stop_impersonate=1" class="btn btn-sm btn-outline-dark ml-2">Keluar Mode Impersonate</a>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  </div>';
}

if (isset($_GET['stop_impersonate'])) {
	unset($_SESSION['impersonate_user_id']);
	$message_alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
						<i class="fa fa-check-circle"></i> Berhasil kembali ke akun SuperAdmin utama.
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  </div>';
}

// Handle Save User (Create/Update)
if (isset($_POST['action_save_user'])) {
	$user_id    = intval($_POST['user_id']);
	$username   = trim($_POST['username']);
	$full_name  = trim($_POST['full_name']);
	$password   = trim($_POST['password']);
	$role       = $_POST['role'];
	$status     = $_POST['status'];
	$mk_ip      = trim($_POST['mikrotik_ip']);
	$mk_user    = trim($_POST['mikrotik_username']);
	$mk_pass    = trim($_POST['mikrotik_password']);
	$mk_port    = intval($_POST['mikrotik_port']);
	$bot_token  = trim($_POST['bot_token']);
	$owner_id   = trim($_POST['owner_telegram_id']);

	$save_data = [
		'username' => $username,
		'full_name' => $full_name,
		'role' => $role,
		'status' => $status,
		'mikrotik_ip' => $mk_ip,
		'mikrotik_username' => $mk_user,
		'mikrotik_password' => $mk_pass,
		'mikrotik_port' => $mk_port > 0 ? $mk_port : 8728,
		'bot_token' => $bot_token,
		'owner_telegram_id' => $owner_id
	];

	if (!empty($password)) {
		$save_data['password'] = $password;
	}

	if ($user_id > 0) {
		$save_data['id'] = $user_id;
		save_app_user($save_data);
		$message_alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
							<i class="fa fa-check-circle"></i> Data User Admin <strong>' . htmlspecialchars($full_name) . '</strong> berhasil diperbarui!
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  </div>';
	} else {
		// Check existing username
		$check = get_app_user_by_username($username);
		if ($check) {
			$message_alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<i class="fa fa-exclamation-triangle"></i> Username <strong>' . htmlspecialchars($username) . '</strong> sudah digunakan. Silahkan gunakan username lain.
								<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							  </div>';
		} else {
			save_app_user($save_data);
			$message_alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
								<i class="fa fa-check-circle"></i> User Admin Baru <strong>' . htmlspecialchars($full_name) . '</strong> berhasil ditambahkan!
								<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							  </div>';
		}
	}
}

// Handle Delete User
if (isset($_POST['action_delete_user'])) {
	$del_id = intval($_POST['user_id']);
	delete_app_user($del_id);
	$message_alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
						<i class="fa fa-trash"></i> User berhasil dihapus.
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  </div>';
}

// Handle Test Connection
$test_results = null;
if (isset($_POST['action_test_conn'])) {
	$test_ip   = trim($_POST['test_ip']);
	$test_user = trim($_POST['test_user']);
	$test_pass = trim($_POST['test_pass']);
	$test_port = intval($_POST['test_port']) > 0 ? intval($_POST['test_port']) : 8728;

	$API = new routeros_api();
	$API->timeout = 5;

	if (!empty($test_ip) && $API->connect($test_ip, $test_user, $test_pass, $test_port)) {
		$res = $API->comm("/system/identity/print");
		$router_identity = isset($res[0]['name']) ? $res[0]['name'] : 'Unknown';
		$API->disconnect();
		$test_results = [
			'status' => 'success',
			'msg' => "Koneksi ke MikroTik KELAS KONEKTIF! Identity Router: <strong>$router_identity</strong> ($test_ip:$test_port)"
		];
	} else {
		$test_results = [
			'status' => 'danger',
			'msg' => "Gagal terhubung ke Router MikroTik ($test_ip:$test_port). Periksa IP, Username, Password, dan Port API."
		];
	}
}

$all_users = get_all_app_users();
?>

<div class="pd-x-20 pd-sm-x-30 pd-t-20 pd-sm-t-30">
	<div class="d-flex align-items-center justify-content-between mg-b-20">
		<div>
			<h4 class="tx-gray-800 mg-b-5"><i class="fa fa-users mg-r-10 text-primary"></i> Kelola User Admin / Tenant (SuperAdmin)</h4>
			<p class="mg-b-0 text-muted">SuperAdmin mengelola akun User Admin. Pengaturan Router MikroTik & Bot Telegram dikonfigurasi mandiri oleh masing-masing Admin di menu <strong>Settings</strong>.</p>
		</div>
		<div>
			<button type="button" class="btn btn-primary btn-spl" onclick="openAddUserModal()">
				<i class="fa fa-plus-circle mg-r-5"></i> Tambah User Admin Baru
			</button>
		</div>
	</div>

	<?=$message_alert;?>

	<?php if ($test_results): ?>
		<div class="alert alert-<?=$test_results['status'];?> alert-dismissible fade show" role="alert">
			<i class="fa fa-network-wired mg-r-5"></i> <?=$test_results['msg'];?>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
	<?php endif; ?>

	<!-- User List Card -->
	<div class="card bd-primary mg-t-15">
		<div class="card-header bg-primary tx-white d-flex align-items-center justify-content-between pd-y-12">
			<span class="font-weight-bold"><i class="fa fa-list mg-r-5"></i> Daftar Pengguna Aplikasi (<?=count($all_users);?> User)</span>
		</div>
		<div class="card-body pd-0">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover mg-b-0">
					<thead class="thead-colored bg-primary">
						<tr>
							<th class="wd-5p text-center">No</th>
							<th>Nama Lengkap & Username</th>
							<th class="text-center">Role</th>
							<th>IP Router MikroTik</th>
							<th>Token Bot Telegram</th>
							<th class="text-center">Status</th>
							<th class="text-center">Aksi / Kelola</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (empty($all_users)) {
							echo '<tr><td colspan="7" class="text-center pd-20">Belum ada user terdaftar.</td></tr>';
						} else {
							$no = 1;
							foreach ($all_users as $u) {
								$role_badge = ($u['role'] === 'superadmin')
									? '<span class="badge badge-purple tx-12 pd-5-10"><i class="fa fa-crown"></i> SuperAdmin</span>'
									: '<span class="badge badge-info tx-12 pd-5-10"><i class="fa fa-user"></i> User Admin</span>';

								$status_badge = ($u['status'] === 'active')
									? '<span class="badge badge-success tx-12 pd-5-10">Aktif</span>'
									: '<span class="badge badge-danger tx-12 pd-5-10">Nonaktif</span>';

								$is_impersonating_this = (isset($_SESSION['impersonate_user_id']) && intval($_SESSION['impersonate_user_id']) === intval($u['id']));
								?>
								<tr class="<?=$is_impersonating_this ? 'bg-warning-light' : '';?>">
									<td class="text-center"><?=$no++;?></td>
									<td>
										<strong class="tx-inverse"><?=htmlspecialchars($u['full_name']);?></strong>
										<br><small class="text-muted">@<?=htmlspecialchars($u['username']);?></small>
									</td>
									<td class="text-center"><?=$role_badge;?></td>
									<td>
										<?php if (!empty($u['mikrotik_ip'])): ?>
											<code><i class="fa fa-server"></i> <?=htmlspecialchars($u['mikrotik_ip']);?>:<?=$u['mikrotik_port'];?></code>
											<br><small class="text-muted">User: <?=htmlspecialchars($u['mikrotik_username']);?></small>
										<?php else: ?>
											<span class="text-muted"><i>Mandiri oleh Admin</i></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if (!empty($u['bot_token'])): ?>
											<small class="text-success"><i class="fa fa-robot"></i> Terpasang</small>
											<?php if (!empty($u['owner_telegram_id'])): ?>
												<br><small class="text-muted">Owner ID: <code><?=$u['owner_telegram_id'];?></code></small>
											<?php endif; ?>
										<?php else: ?>
											<span class="text-muted"><i>Mandiri oleh Admin</i></span>
										<?php endif; ?>
									</td>
									<td class="text-center"><?=$status_badge;?></td>
									<td class="text-center">
										<div class="btn-group" role="group">
											<!-- Test Connection -->
											<?php if (!empty($u['mikrotik_ip'])): ?>
												<form method="POST" action="./?Mikbotam=manageusers" style="display:inline;">
													<input type="hidden" name="test_ip" value="<?=htmlspecialchars($u['mikrotik_ip']);?>">
													<input type="hidden" name="test_user" value="<?=htmlspecialchars($u['mikrotik_username']);?>">
													<input type="hidden" name="test_pass" value="<?=htmlspecialchars($u['mikrotik_password']);?>">
													<input type="hidden" name="test_port" value="<?=$u['mikrotik_port'];?>">
													<button type="submit" name="action_test_conn" class="btn btn-sm btn-info" title="Test Koneksi Router">
														<i class="fa fa-plug"></i> Test
													</button>
												</form>
											<?php endif; ?>

											<!-- Edit Button -->
											<button type="button" class="btn btn-sm btn-warning" onclick='openEditUserModal(<?=json_encode($u);?>)' title="Edit User">
												<i class="fa fa-edit"></i> Edit
											</button>

											<!-- Impersonate -->
											<?php if ($u['role'] !== 'superadmin'): ?>
												<form method="POST" action="./?Mikbotam=manageusers" style="display:inline;">
													<input type="hidden" name="user_id" value="<?=$u['id'];?>">
													<button type="submit" name="action_impersonate" class="btn btn-sm btn-secondary" title="Login Sebagai User Ini">
														<i class="fa fa-user-secret"></i> View
													</button>
												</form>
											<?php endif; ?>

											<!-- Delete Button -->
											<?php if ($u['role'] !== 'superadmin'): ?>
												<form method="POST" action="./?Mikbotam=manageusers" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
													<input type="hidden" name="user_id" value="<?=$u['id'];?>">
													<button type="submit" name="action_delete_user" class="btn btn-sm btn-danger" title="Hapus User">
														<i class="fa fa-trash"></i>
													</button>
												</form>
											<?php endif; ?>
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

<!-- Modal Form User (Tambah / Edit) -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<form method="POST" action="./?Mikbotam=manageusers">
				<input type="hidden" name="user_id" id="form_user_id" value="0">
				<div class="modal-header bg-primary tx-white">
					<h5 class="modal-title font-weight-bold" id="modalUserTitle"><i class="fa fa-user-plus mg-r-5"></i> Tambah User Admin Baru</h5>
					<button type="button" class="close tx-white" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body pd-25">
					<h6 class="tx-13 tx-uppercase tx-inverse font-weight-bold mg-b-15 text-primary"><i class="fa fa-id-card mg-r-5"></i> 1. Informasi Akun Admin</h6>
					<div class="row">
						<div class="col-md-6 form-group">
							<label class="tx-12 font-weight-bold">Nama Lengkap Pemilik:</label>
							<input type="text" name="full_name" id="field_full_name" class="form-control" placeholder="Contoh: Budi Santoso" required>
						</div>
						<div class="col-md-6 form-group">
							<label class="tx-12 font-weight-bold">Username Login:</label>
							<input type="text" name="username" id="field_username" class="form-control" placeholder="Contoh: budi_net" required>
						</div>
						<div class="col-md-6 form-group">
							<label class="tx-12 font-weight-bold">Password Login:</label>
							<input type="password" name="password" id="field_password" class="form-control" placeholder="Kosongkan jika tidak diubah">
							<small class="text-muted" id="pass_help_text">Wajib diisi untuk user baru.</small>
						</div>
						<div class="col-md-3 form-group">
							<label class="tx-12 font-weight-bold">Role Akses:</label>
							<select name="role" id="field_role" class="form-control">
								<option value="user">User Admin (Tenant)</option>
								<option value="superadmin">SuperAdmin</option>
							</select>
						</div>
						<div class="col-md-3 form-group">
							<label class="tx-12 font-weight-bold">Status Akun:</label>
							<select name="status" id="field_status" class="form-control">
								<option value="active">Aktif 🟢</option>
								<option value="inactive">Nonaktif 🔴</option>
							</select>
						</div>
					</div>

					<hr class="mg-y-20">

					<div class="alert alert-light bd font-weight-normal text-muted">
						<i class="fa fa-info-circle text-info mg-r-5"></i> Kredensial MikroTik & Telegram di bawah bersifat <strong>opsional</strong>. User Admin dapat mengisinya secara mandiri melalui menu <strong>Settings</strong> setelah login.
					</div>

					<h6 class="tx-13 tx-uppercase tx-inverse font-weight-bold mg-b-15 text-primary"><i class="fa fa-server mg-r-5"></i> 2. Kredensial Router MikroTik (Opsional oleh SuperAdmin)</h6>
					<div class="row">
						<div class="col-md-6 form-group">
							<label class="tx-12 font-weight-bold">IP Address / Hostname MikroTik:</label>
							<input type="text" name="mikrotik_ip" id="field_mikrotik_ip" class="form-control" placeholder="192.168.88.1 atau domain.net">
						</div>
						<div class="col-md-3 form-group">
							<label class="tx-12 font-weight-bold">Port API RouterOS:</label>
							<input type="number" name="mikrotik_port" id="field_mikrotik_port" class="form-control" value="8728">
						</div>
						<div class="col-md-6 form-group">
							<label class="tx-12 font-weight-bold">Username API MikroTik:</label>
							<input type="text" name="mikrotik_username" id="field_mikrotik_username" class="form-control" placeholder="admin">
						</div>
						<div class="col-md-6 form-group">
							<label class="tx-12 font-weight-bold">Password API MikroTik:</label>
							<input type="password" name="mikrotik_password" id="field_mikrotik_password" class="form-control" placeholder="Password MikroTik">
						</div>
					</div>

					<hr class="mg-y-20">

					<h6 class="tx-13 tx-uppercase tx-inverse font-weight-bold mg-b-15 text-primary"><i class="fa fa-robot mg-r-5"></i> 3. Kredensial Bot Telegram Khusus</h6>
					<div class="row">
						<div class="col-md-8 form-group">
							<label class="tx-12 font-weight-bold">Token Bot Telegram:</label>
							<input type="text" name="bot_token" id="field_bot_token" class="form-control" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ">
						</div>
						<div class="col-md-4 form-group">
							<label class="tx-12 font-weight-bold">ID Telegram Owner:</label>
							<input type="text" name="owner_telegram_id" id="field_owner_telegram_id" class="form-control" placeholder="123456789">
						</div>
					</div>
				</div>
				<div class="modal-footer bg-gray-100">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
					<button type="submit" name="action_save_user" class="btn btn-primary"><i class="fa fa-save mg-r-5"></i> Simpan User</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
function openAddUserModal() {
	document.getElementById('form_user_id').value = '0';
	document.getElementById('modalUserTitle').innerHTML = '<i class="fa fa-user-plus mg-r-5"></i> Tambah User Admin Baru';
	document.getElementById('field_full_name').value = '';
	document.getElementById('field_username').value = '';
	document.getElementById('field_username').readOnly = false;
	document.getElementById('field_password').value = '';
	document.getElementById('field_password').required = true;
	document.getElementById('pass_help_text').innerText = 'Wajib diisi untuk user baru.';
	document.getElementById('field_role').value = 'user';
	document.getElementById('field_status').value = 'active';
	document.getElementById('field_mikrotik_ip').value = '';
	document.getElementById('field_mikrotik_username').value = '';
	document.getElementById('field_mikrotik_password').value = '';
	document.getElementById('field_mikrotik_port').value = '8728';
	document.getElementById('field_bot_token').value = '';
	document.getElementById('field_owner_telegram_id').value = '';
	$('#userModal').modal('show');
}

function openEditUserModal(u) {
	document.getElementById('form_user_id').value = u.id;
	document.getElementById('modalUserTitle').innerHTML = '<i class="fa fa-edit mg-r-5"></i> Edit User Admin: ' + u.full_name;
	document.getElementById('field_full_name').value = u.full_name || '';
	document.getElementById('field_username').value = u.username || '';
	document.getElementById('field_username').readOnly = true;
	document.getElementById('field_password').value = '';
	document.getElementById('field_password').required = false;
	document.getElementById('pass_help_text').innerText = 'Kosongkan jika tidak ingin mengubah password.';
	document.getElementById('field_role').value = u.role || 'user';
	document.getElementById('field_status').value = u.status || 'active';
	document.getElementById('field_mikrotik_ip').value = u.mikrotik_ip || '';
	document.getElementById('field_mikrotik_username').value = u.mikrotik_username || '';
	document.getElementById('field_mikrotik_password').value = u.mikrotik_password || '';
	document.getElementById('field_mikrotik_port').value = u.mikrotik_port || '8728';
	document.getElementById('field_bot_token').value = u.bot_token || '';
	document.getElementById('field_owner_telegram_id').value = u.owner_telegram_id || '';
	$('#userModal').modal('show');
}
</script>
