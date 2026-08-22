<?php
//=====================================================START SCRIPT====================//

error_reporting(0);

if (!isset($_SESSION["Mikbotamuser"])) {
    header("Location:../admin/login.php");
    exit();
}

include_once '../config/system.conn.php';
include_once '../config/system.database.php';

$tenant_id = get_current_tenant_id();
$is_superadmin = (isset($_SESSION['app_user_role']) && $_SESSION['app_user_role'] === 'superadmin');

$message = '';

// Handle Save KlikQRIS Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_klikqris'])) {
    $mode = (isset($_POST['mode']) && $_POST['mode'] === 'production') ? 'production' : 'sandbox';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $data = [
        'api_key'        => isset($_POST['api_key']) ? trim($_POST['api_key']) : '',
        'merchant_id'    => isset($_POST['merchant_id']) ? trim($_POST['merchant_id']) : '',
        'mode'           => $mode,
        'sandbox_url'    => !empty($_POST['sandbox_url']) ? trim($_POST['sandbox_url']) : 'https://klikqris.com/api/sandbox',
        'production_url' => !empty($_POST['production_url']) ? trim($_POST['production_url']) : 'https://klikqris.com/api',
        'is_active'      => $is_active
    ];

    $res = save_klikqris_settings($data, $tenant_id);
    if ($res['success']) {
        $message = '<div class="alert alert-success alert-dismissible fade show mg-b-15" role="alert">
            <i class="fa fa-check-circle mg-r-5"></i> ' . htmlspecialchars($res['message']) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>';
    }
}

$settings = get_klikqris_settings($tenant_id);

// Generate Webhook / Callback Notification URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$callback_url = $protocol . $domain_host . '/tools/klikqris_callback.php?uid=' . $tenant_id;
?>

<div class="sl-pagebody">
    <div class="sl-page-title d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h5><i class="fa fa-qrcode text-primary mg-r-8"></i> Pengaturan Payment Gateway KlikQRIS</h5>
            <p class="mg-b-0">Konfigurasi API Key, ID Merchant, dan Endpoint Mode KlikQRIS untuk transaksi otomatis.</p>
        </div>
        <div>
            <span class="badge <?= ($settings['mode'] === 'production') ? 'badge-success' : 'badge-warning'; ?> pd-y-7 pd-x-12 tx-12 font-weight-bold">
                <i class="fa <?= ($settings['mode'] === 'production') ? 'fa-check-circle' : 'fa-flask'; ?> mg-r-5"></i> Mode: <?= strtoupper($settings['mode']); ?>
            </span>
        </div>
    </div>

    <?= $message; ?>

    <!-- Banner Info / Promo Aktivasi KlikQRIS -->
    <div class="card bd-0 bg-gradient-primary text-white shadow-sm mg-b-20" style="background: linear-gradient(135deg, #10b759 0%, #008080 100%); border-radius: 8px;">
        <div class="card-body pd-20">
            <div class="row align-items-center">
                <div class="col-md-8 mg-b-15 mg-md-b-0">
                    <div class="d-flex align-items-center">
                        <div class="mg-r-15 d-none d-sm-block">
                            <span class="d-inline-flex justify-content-center align-items-center rounded-circle bg-white text-success" style="width: 54px; height: 54px; font-size: 26px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                <i class="fa fa-qrcode"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="tx-white font-weight-bold mg-b-5">
                                <i class="fa fa-bullhorn mg-r-5 text-warning"></i> Belum Memiliki Akun Merchant KlikQRIS?
                            </h5>
                            <p class="tx-white tx-13 mg-b-0" style="opacity: 0.95;">
                                Nikmati kemudahan terima pembayaran QRIS otomatis 24 Jam untuk top up saldo reseller dan tagihan PPP. Daftar dan lakukan aktivasi akun merchant melalui tautan resmi di bawah ini:
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-right">
                    <a href="https://klikqris.com/r/178014423787" target="_blank" class="btn btn-warning btn-block btn-lg font-weight-bold tx-uppercase tx-12 shadow" style="color: #212529; border-radius: 6px; padding: 12px 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                        <i class="fa fa-external-link mg-r-5"></i> Daftar & Aktivasi KlikQRIS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-sm justify-content-center">
        <!-- Form Pengaturan KlikQRIS -->
        <div class="col-lg-8 mg-b-20">
            <div class="card bd-primary shadow-sm">
                <div class="card-header bg-primary tx-white font-weight-bold d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-cogs mg-r-8"></i> Parameter Kredensial KlikQRIS</span>
                    <span class="tx-12 text-light">User ID Tenant: <strong>#<?= $tenant_id; ?></strong></span>
                </div>
                <div class="card-body pd-25">
                    <form method="POST" action="./?Mikbotam=klikqris_settings">
                        
                        <!-- Status Gateway Switch -->
                        <div class="form-group row align-items-center mg-b-20">
                            <label class="col-sm-4 form-control-label font-weight-bold tx-gray-800">
                                Status Payment Gateway:
                            </label>
                            <div class="col-sm-8">
                                <label class="ckbox">
                                    <input type="checkbox" name="is_active" value="1" <?= ($settings['is_active'] == 1) ? 'checked' : ''; ?>>
                                    <span class="font-weight-bold text-success">Aktifkan Pembayaran Otomatis via KlikQRIS</span>
                                </label>
                                <small class="text-muted d-block mg-t-3">Jika dinonaktifkan, pembayaran otomatis melalui QRIS akan dihentikan sementara.</small>
                            </div>
                        </div>

                        <hr class="mg-y-15">

                        <!-- Mode Pilihan: Sandbox vs Production -->
                        <div class="form-group row align-items-center mg-b-20">
                            <label class="col-sm-4 form-control-label font-weight-bold tx-gray-800">
                                Pilihan Mode Operasi: <span class="tx-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <div class="row row-xs">
                                    <div class="col-6">
                                        <label class="rdiobox">
                                            <input name="mode" type="radio" value="sandbox" id="mode_sandbox" <?= ($settings['mode'] === 'sandbox') ? 'checked' : ''; ?> onchange="toggleModeUrl()">
                                            <span>
                                                <strong class="text-warning"><i class="fa fa-flask mg-r-3"></i> Sandbox</strong> (Uji Coba)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <label class="rdiobox">
                                            <input name="mode" type="radio" value="production" id="mode_production" <?= ($settings['mode'] === 'production') ? 'checked' : ''; ?> onchange="toggleModeUrl()">
                                            <span>
                                                <strong class="text-success"><i class="fa fa-rocket mg-r-3"></i> Production</strong> (Live)
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mg-t-5">Gunakan Sandbox untuk simulasi tanpa transaksi uang riil, dan Production untuk menerima pembayaran riil.</small>
                            </div>
                        </div>

                        <!-- ID Merchant -->
                        <div class="form-group row align-items-center mg-b-20">
                            <label class="col-sm-4 form-control-label font-weight-bold tx-gray-800">
                                ID Merchant (id_merchant): <span class="tx-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-addon bg-gray-200"><i class="fa fa-id-card-o text-primary"></i></span>
                                    <input type="text" name="merchant_id" class="form-control" placeholder="Contoh: MCH-12345 atau ID Merchant KlikQRIS" value="<?= htmlspecialchars($settings['merchant_id']); ?>" required>
                                </div>
                                <small class="text-muted d-block mg-t-3">Dapatkan ID Merchant dari dashboard akun KlikQRIS Anda.</small>
                            </div>
                        </div>

                        <!-- x-api-key -->
                        <div class="form-group row align-items-center mg-b-20">
                            <label class="col-sm-4 form-control-label font-weight-bold tx-gray-800">
                                x-api-key: <span class="tx-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-addon bg-gray-200"><i class="fa fa-key text-primary"></i></span>
                                    <input type="password" name="api_key" id="api_key_input" class="form-control" placeholder="Masukkan x-api-key KlikQRIS" value="<?= htmlspecialchars($settings['api_key']); ?>" required>
                                    <span class="input-group-btn">
                                        <button class="btn btn-secondary btn-flat" type="button" onclick="toggleApiKeyVisibility()" title="Lihat / Sembunyikan API Key">
                                            <i id="eye_icon" class="fa fa-eye"></i>
                                        </button>
                                    </span>
                                </div>
                                <small class="text-muted d-block mg-t-3">API Key disimpan secara aman dan dienkripsi di sistem database.</small>
                            </div>
                        </div>

                        <hr class="mg-y-15">

                        <!-- Sandbox Endpoint URL -->
                        <div class="form-group row align-items-center mg-b-15">
                            <label class="col-sm-4 form-control-label font-weight-bold text-muted">
                                Sandbox Base URL:
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" name="sandbox_url" id="field_sandbox_url" class="form-control bg-light" value="<?= htmlspecialchars($settings['sandbox_url']); ?>" readonly>
                                    <span class="input-group-addon bg-warning text-dark font-weight-bold tx-12">Sandbox</span>
                                </div>
                            </div>
                        </div>

                        <!-- Production Endpoint URL -->
                        <div class="form-group row align-items-center mg-b-20">
                            <label class="col-sm-4 form-control-label font-weight-bold text-muted">
                                Production Base URL:
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" name="production_url" id="field_production_url" class="form-control bg-light" value="<?= htmlspecialchars($settings['production_url']); ?>" readonly>
                                    <span class="input-group-addon bg-success text-white font-weight-bold tx-12">Production</span>
                                </div>
                            </div>
                        </div>

                        <!-- Webhook / Callback Notification URL -->
                        <div class="form-group row align-items-center mg-b-25 bg-gray-100 pd-15 rounded">
                            <label class="col-sm-4 form-control-label font-weight-bold tx-gray-800">
                                <i class="fa fa-bell text-warning mg-r-3"></i> Webhook / Callback URL:
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" id="callback_url_field" class="form-control bg-white font-weight-bold text-primary" value="<?= htmlspecialchars($callback_url); ?>" readonly>
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="copyCallbackUrl()" title="Salin URL Callback">
                                            <i class="fa fa-copy mg-r-3"></i> Salin
                                        </button>
                                    </span>
                                </div>
                                <small class="text-muted d-block mg-t-5">
                                    Tempelkan URL ini di pengaturan <strong>Webhook Callback Notification</strong> pada dashboard KlikQRIS agar status transaksi otomatis terupdate (LUNAS).
                                </small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-group row mg-b-0">
                            <div class="col-sm-8 offset-sm-4">
                                <button type="submit" name="save_klikqris" class="btn btn-success pd-x-25 mg-r-5">
                                    <i class="fa fa-save mg-r-5"></i> Simpan Pengaturan
                                </button>
                                <a href="./?Mikbotam=Settings" class="btn btn-secondary pd-x-20">
                                    <i class="fa fa-arrow-left mg-r-5"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleApiKeyVisibility() {
    var input = document.getElementById('api_key_input');
    var icon = document.getElementById('eye_icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa fa-eye-slash text-danger';
    } else {
        input.type = 'password';
        icon.className = 'fa fa-eye';
    }
}

function copyCallbackUrl() {
    var copyText = document.getElementById('callback_url_field');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    if (typeof alertify !== 'undefined') {
        alertify.success('URL Callback Webhook berhasil disalin!');
    } else {
        alert('URL Callback Webhook berhasil disalin!');
    }
}
</script>
