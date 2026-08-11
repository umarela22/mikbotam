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

$message = '';

if (isset($_POST['save_settings'])) {
	$data = [
		'due_date'       => isset($_POST['due_date']) ? intval($_POST['due_date']) : 20,
		'isolir_mode'    => isset($_POST['isolir_mode']) ? $_POST['isolir_mode'] : 'disable',
		'isolir_profile' => isset($_POST['isolir_profile']) ? trim($_POST['isolir_profile']) : 'ISOLIR_PPPOE',
		'reminder_days'  => isset($_POST['reminder_days']) ? intval($_POST['reminder_days']) : 3
	];

	save_ppp_isolir_settings($data);
	$message = '<div class="alert alert-success mg-b-15">Berhasil menyimpan pengaturan isolir & pengingat tagihan!</div>';
}

$settings = get_ppp_isolir_settings();

?>

<div class="sl-pagebody">
	<div class="sl-page-title">
		<h5>Pengaturan Isolir & Pengingat Tagihan</h5>
		<p>Konfigurasi aturan otomatisasi jatuh tempo, pengingat Bot Telegram, dan metode isolir pelanggan PPPoE.</p>
	</div>

	<?=$message;?>

	<div class="row row-sm mg-t-10">
		<div class="col-lg-8">
			<div class="card bd-primary">
				<div class="card-header bg-primary tx-white">
					<i class="fa fa-cogs mg-r-5"></i> Form Pengaturan Isolir & Tagihan
				</div>
				<div class="card-body pd-20">
					<form method="POST" action="./?Mikbotam=pppisolir">
						<div class="form-group">
							<label class="font-weight-bold">Tanggal Standar Jatuh Tempo (Setiap Bulan):</label>
							<div class="input-group">
								<input type="number" name="due_date" min="1" max="28" class="form-control" value="<?=htmlspecialchars($settings['due_date']);?>" required>
								<span class="input-group-addon">Setiap Tanggal Bulan Berjalan</span>
							</div>
							<small class="form-text text-muted">Pelanggan yang belum melunasi tagihan hingga tanggal ini akan dikategorikan menunggak.</small>
						</div>

						<div class="form-group">
							<label class="font-weight-bold">Pengiriman Pengingat Tagihan Telegram (H-):</label>
							<div class="input-group">
								<input type="number" name="reminder_days" min="1" max="10" class="form-control" value="<?=htmlspecialchars($settings['reminder_days']);?>" required>
								<span class="input-group-addon">Hari Sebelum Jatuh Tempo</span>
							</div>
							<small class="form-text text-muted">Bot Telegram akan otomatis mengirim pesan rincian tagihan H-hari sebelum jatuh tempo.</small>
						</div>

						<hr>

						<div class="form-group">
							<label class="font-weight-bold">Strategi Eksekusi Isolir MikroTik:</label>
							<select name="isolir_mode" class="form-control">
								<option value="disable" <?=$settings['isolir_mode'] === 'disable' ? 'selected' : '';?>>Mode 1: Nonaktifkan / Disable Secret (Rekomendasi Utama)</option>
								<option value="profile" <?=$settings['isolir_mode'] === 'profile' ? 'selected' : '';?>>Mode 2: Ganti Profile ke Profile ISOLIR</option>
							</select>
							<small class="form-text text-muted">Mode 1 akan langsung mematikan akses koneksi. Mode 2 akan memindahkan profile user ke profile khusus isolir.</small>
						</div>

						<div class="form-group">
							<label class="font-weight-bold">Nama Profile ISOLIR MikroTik (Opsional untuk Mode 2):</label>
							<input type="text" name="isolir_profile" class="form-control" value="<?=htmlspecialchars($settings['isolir_profile']);?>" placeholder="ISOLIR_PPPOE">
							<small class="form-text text-muted">Pastikan Profile ini sudah dibuat di router MikroTik jika Anda menggunakan Mode 2.</small>
						</div>

						<hr>

						<button type="submit" name="save_settings" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Pengaturan</button>
					</form>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="card bd-info">
				<div class="card-header bg-info tx-white">
					<i class="fa fa-info-circle mg-r-5"></i> Informasi Cron Task Otomatis
				</div>
				<div class="card-body pd-15 tx-13">
					<p>Untuk mengaktifkan otomatisasi pengiriman pengingat Telegram dan eksekusi isolir tanpa membuka web, tambahkan perintah berikut ke <strong>Cron Job Linux</strong> atau <strong>Windows Task Scheduler</strong>:</p>

					<div class="p-2 bg-dark text-white rounded font-monospace tx-11" style="word-break: break-all;">
						php.exe c:\xampp\htdocs\mikbotam\tools\cron_ppp_billing.php
					</div>

					<p class="mg-t-15 mg-b-0">Disarankan untuk menjalankan script ini 1x sehari pada jam 07:00 WIB.</p>
				</div>
			</div>
		</div>
	</div>
</div>
