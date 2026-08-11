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

$message = '';

if (isset($_POST['save_package'])) {
	$profile_name = isset($_POST['profile_name']) ? trim($_POST['profile_name']) : '';
	$price        = isset($_POST['price']) ? intval($_POST['price']) : 0;
	$description  = isset($_POST['description']) ? trim($_POST['description']) : '';

	if (!empty($profile_name)) {
		save_ppp_package($profile_name, $price, $description);
		$message = '<div class="alert alert-success mg-b-15">Berhasil menyimpan tarif paket untuk <strong>' . htmlspecialchars($profile_name) . '</strong>!</div>';
	}
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
	delete_ppp_package($_GET['id']);
	$message = '<div class="alert alert-info mg-b-15">Berhasil menghapus tarif paket!</div>';
}

$db_packages = get_ppp_packages();
$pkg_map = [];
foreach ($db_packages as $pkg) {
	$pkg_map[$pkg['profile_name']] = $pkg;
}

$API = new routeros_api();
$API->timeout = 3;
$mikrotik_profiles = [];

if (!empty($mikrotik_ip) && $API->connect($mikrotik_ip, $mikrotik_username, $mikrotik_password, $mikrotik_port)) {
	$get_profiles = $API->comm("/ppp/profile/print");
	if (is_array($get_profiles)) {
		$mikrotik_profiles = $get_profiles;
	}
	$API->disconnect();
}

?>

<div class="sl-pagebody">
	<div class="sl-page-title">
		<h5>Paket & Tarif Bulanan PPPoE</h5>
		<p>Atur harga dan tarif bulanan untuk setiap profil paket PPPoE MikroTik Anda.</p>
	</div>

	<?=$message;?>

	<div class="row row-sm mg-t-10">
		<div class="col-lg-12">
			<div class="card bd-primary">
				<div class="card-header bg-primary tx-white d-flex align-items-center justify-content-between">
					<span><i class="fa fa-tags mg-r-5"></i> Daftar Profile & Tarif Bulanan</span>
				</div>
				<div class="card-body pd-20">
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover mg-b-0">
							<thead class="thead-colored bg-primary">
								<tr>
									<th>No</th>
									<th>Nama Profile MikroTik</th>
									<th>Harga / Bulan (Rp)</th>
									<th>Keterangan Paket</th>
									<th>Aksi / Simpan</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (empty($mikrotik_profiles) && empty($db_packages)) {
									echo '<tr><td colspan="5" class="text-center">Tidak ada profile MikroTik ditemukan atau router offline.</td></tr>';
								} else {
									$all_profiles = [];
									foreach ($mikrotik_profiles as $p) {
										if (isset($p['name'])) {
											$all_profiles[$p['name']] = $p['name'];
										}
									}
									foreach ($pkg_map as $name => $pkg) {
										$all_profiles[$name] = $name;
									}

									$no = 1;
									foreach ($all_profiles as $prof_name) {
										$current_price = isset($pkg_map[$prof_name]) ? $pkg_map[$prof_name]['price'] : 0;
										$current_desc  = isset($pkg_map[$prof_name]) ? $pkg_map[$prof_name]['description'] : '';
										$pkg_id        = isset($pkg_map[$prof_name]) ? $pkg_map[$prof_name]['id'] : null;
										?>
										<tr>
											<form method="POST" action="./?Mikbotam=ppppackages">
												<input type="hidden" name="profile_name" value="<?=htmlspecialchars($prof_name);?>">
												<td><?=$no++;?></td>
												<td>
													<strong class="tx-primary"><?=htmlspecialchars($prof_name);?></strong>
												</td>
												<td style="width: 200px;">
													<div class="input-group">
														<span class="input-group-addon">Rp</span>
														<input type="number" name="price" class="form-control" value="<?=$current_price;?>" placeholder="0" required>
													</div>
												</td>
												<td>
													<input type="text" name="description" class="form-control" value="<?=htmlspecialchars($current_desc);?>" placeholder="Contoh: Unlimited 10 Mbps">
												</td>
												<td>
													<button type="submit" name="save_package" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Simpan Tarif</button>
													<?php if ($pkg_id): ?>
														<a href="./?Mikbotam=ppppackages&action=delete&id=<?=$pkg_id;?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus tarif paket ini?');"><i class="fa fa-trash"></i></a>
													<?php endif; ?>
												</td>
											</form>
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
	</div>
</div>
