<?php
//=====================================================START SCRIPT====================//

error_reporting(0);

if (!isset($_SESSION["Mikbotamuser"])) {
    header("Location:../admin/login.php");
    exit();
}

include_once '../config/system.conn.php';
include_once '../config/system.database.php';

$is_superadmin = (isset($_SESSION['app_user_role']) && $_SESSION['app_user_role'] === 'superadmin');

if (!$is_superadmin) {
    echo '<div class="alert alert-danger mg-b-0">Akses ditolak. Pengaturan SMTP hanya dapat diakses oleh SuperAdmin.</div>';
    exit();
}

$message = '';
$test_message = '';

// Handle Save SMTP Settings
if (isset($_POST['save_smtp'])) {
    $data = [
        'smtp_host'   => isset($_POST['smtp_host']) ? trim($_POST['smtp_host']) : '',
        'smtp_port'   => isset($_POST['smtp_port']) ? intval($_POST['smtp_port']) : 587,
        'smtp_user'   => isset($_POST['smtp_user']) ? trim($_POST['smtp_user']) : '',
        'smtp_pass'   => isset($_POST['smtp_pass']) ? $_POST['smtp_pass'] : '',
        'smtp_crypto' => isset($_POST['smtp_crypto']) ? trim($_POST['smtp_crypto']) : 'tls',
        'from_email'  => isset($_POST['from_email']) ? trim($_POST['from_email']) : '',
        'from_name'   => isset($_POST['from_name']) ? trim($_POST['from_name']) : 'Mikbotam Admin'
    ];

    save_smtp_settings($data);
    $message = '<div class="alert alert-success mg-b-15"><i class="fa fa-check-circle mg-r-5"></i> Pengaturan SMTP berhasil disimpan!</div>';
}

// Handle Test Email
if (isset($_POST['test_smtp'])) {
    $test_email = isset($_POST['test_email']) ? trim($_POST['test_email']) : '';
    if (!empty($test_email) && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        $subject = "Uji Coba Pengiriman Email SMTP Mikbotam";
        $html_body = '
        <html>
        <body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
            <div style="max-width: 500px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; border-top: 4px solid #008080;">
                <h3 style="color: #008080; margin-top: 0;">Koneksi SMTP Berhasil! 🎉</h3>
                <p>Email ini dikirimkan otomatis sebagai bentuk pengujian server SMTP Mikbotam.</p>
                <p><strong>Waktu Pengiriman:</strong> ' . date('d-m-Y H:i:s') . '</p>
                <hr style="border: 0; border-top: 1px solid #eee;">
                <small style="color: #888;">Mikbotam - Mod by Andro Network</small>
            </div>
        </body>
        </html>
        ';

        $test_res = send_custom_smtp_email($test_email, $subject, $html_body);
        if ($test_res['success']) {
            $test_message = '<div class="alert alert-success mg-b-15"><i class="fa fa-paper-plane mg-r-5"></i> <strong>SUKSES:</strong> ' . htmlspecialchars($test_res['message']) . '</div>';
        } else {
            $test_message = '<div class="alert alert-danger mg-b-15"><i class="fa fa-exclamation-triangle mg-r-5"></i> <strong>GAGAL:</strong> ' . htmlspecialchars($test_res['message']) . '</div>';
        }
    } else {
        $test_message = '<div class="alert alert-warning mg-b-15"><i class="fa fa-warning mg-r-5"></i> Harap masukkan alamat email tujuan pengujian yang valid.</div>';
    }
}

$settings = get_smtp_settings();
?>

<div class="sl-pagebody">
    <div class="sl-page-title d-flex justify-content-between align-items-center">
        <div>
            <h5>Pengaturan Mailer Server SMTP</h5>
            <p>Konfigurasi server SMTP untuk pengiriman email verifikasi registrasi dan notifikasi akun Admin Mikbotam.</p>
        </div>
    </div>

    <?= $message; ?>
    <?= $test_message; ?>

    <div class="row row-sm">
        <!-- Form Pengaturan SMTP -->
        <div class="col-lg-7 mg-b-20">
            <div class="card bd-primary">
                <div class="card-header bg-primary tx-white font-weight-bold d-flex align-items-center">
                    <i class="fa fa-envelope mg-r-10"></i> Parameter Server SMTP
                </div>
                <div class="card-body pd-20">
                    <form method="POST" action="./?Mikbotam=smtpsettings">
                        <div class="row row-xs">
                            <div class="col-md-8 form-group">
                                <label class="font-weight-bold">SMTP Host / Server:</label>
                                <input type="text" name="smtp_host" class="form-control" placeholder="Contoh: smtp.gmail.com atau mail.domain.com" value="<?= htmlspecialchars($settings['smtp_host']); ?>">
                                <small class="text-muted">Kosongkan jika ingin menggunakan mail() bawaan server.</small>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="font-weight-bold">SMTP Port:</label>
                                <input type="number" name="smtp_port" class="form-control" placeholder="587 / 465 / 25" value="<?= htmlspecialchars($settings['smtp_port']); ?>">
                            </div>
                        </div>

                        <div class="row row-xs">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">SMTP Username / Email:</label>
                                <input type="text" name="smtp_user" class="form-control" placeholder="admin@domain.com" value="<?= htmlspecialchars($settings['smtp_user']); ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">SMTP Password / App Password:</label>
                                <input type="password" name="smtp_pass" class="form-control" placeholder="••••••••" value="<?= htmlspecialchars($settings['smtp_pass']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Enkripsi (Encryption Protocol):</label>
                            <select name="smtp_crypto" class="form-control">
                                <option value="tls" <?= ($settings['smtp_crypto'] === 'tls') ? 'selected' : ''; ?>>TLS (Recommended for Port 587)</option>
                                <option value="ssl" <?= ($settings['smtp_crypto'] === 'ssl') ? 'selected' : ''; ?>>SSL (Recommended for Port 465)</option>
                                <option value="none" <?= ($settings['smtp_crypto'] === 'none') ? 'selected' : ''; ?>>None / No Encryption (Port 25)</option>
                            </select>
                        </div>

                        <hr>

                        <div class="row row-xs">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Email Pengirim (From Email):</label>
                                <input type="email" name="from_email" class="form-control" placeholder="no-reply@domain.com" value="<?= htmlspecialchars($settings['from_email']); ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Nama Pengirim (From Name):</label>
                                <input type="text" name="from_name" class="form-control" placeholder="Mikbotam Admin" value="<?= htmlspecialchars($settings['from_name']); ?>">
                            </div>
                        </div>

                        <button type="submit" name="save_smtp" class="btn btn-primary font-weight-bold mg-t-10">
                            <i class="fa fa-save mg-r-5"></i> Simpan Pengaturan SMTP
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Uji Coba Pengiriman Email -->
        <div class="col-lg-5">
            <div class="card bd-info">
                <div class="card-header bg-info tx-white font-weight-bold d-flex align-items-center">
                    <i class="fa fa-paper-plane mg-r-10"></i> Uji Coba Koneksi Email (Test Mail)
                </div>
                <div class="card-body pd-20">
                    <p class="tx-13 text-muted">Gunakan fitur ini untuk memastikan server SMTP Anda terhubung dan dapat mengirimkan email verifikasi secara lancar.</p>
                    <form method="POST" action="./?Mikbotam=smtpsettings">
                        <div class="form-group">
                            <label class="font-weight-bold">Email Tujuan Uji Coba:</label>
                            <input type="email" name="test_email" class="form-control" placeholder="emailanda@gmail.com" required>
                        </div>
                        <button type="submit" name="test_smtp" class="btn btn-info font-weight-bold btn-block">
                            <i class="fa fa-paper-plane mg-r-5"></i> Kirim Email Uji Coba
                        </button>
                    </form>

                    <div class="alert alert-outline-info pd-15 mg-t-20 tx-12">
                        <strong>💡 Panduan Umum SMTP:</strong>
                        <ul class="mg-b-0 pd-l-20 mg-t-5">
                            <li><strong>Gmail:</strong> Host <code>smtp.gmail.com</code>, Port <code>587</code> (TLS), gunakan <i>App Password</i>.</li>
                            <li><strong>cPanel Mail:</strong> Host <code>mail.domain.com</code>, Port <code>465</code> (SSL) atau <code>587</code> (TLS).</li>
                            <li><strong>Mailgun / SendGrid:</strong> Gunakan SMTP credentials dari dashboard provider Anda.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
