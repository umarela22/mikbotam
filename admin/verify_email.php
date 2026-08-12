<?php
session_start();
error_reporting(0);

include_once '../config/system.conn.php';
include_once '../config/system.database.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$res = verify_app_user_email($token);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verifikasi Email - MIKBOTAM</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link href="../lib/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/Mikbotam.min.css">
    <style>
        body {
            background-color: #f0f4f7;
        }
        .verify-box {
            width: 100%;
            max-width: 480px;
            padding: 30px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0, 128, 128, 0.15);
            border: 1px solid #008080;
            margin: 50px auto;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="d-flex align-items-center justify-content-center min-vh-100 pd-t-40 pd-b-40">
        <div class="verify-box">
            <div class="signin-logo mg-b-20">
                <img src="../img/logoM.svg" alt="Mikbotam" class="img-fluid center mg-b-10" style="width: 80px;">
                <h4 class="font-weight-bold text-dark mg-b-0">MIKBOTAM</h4>
                <span class="tx-12 font-weight-bold text-success d-block mg-t-2">Verifikasi Email Akun Admin</span>
            </div>

            <?php if ($res['success']): ?>
                <div class="mg-b-20 text-success" style="font-size: 60px;">
                    <i class="fa fa-check-circle-o"></i>
                </div>
                <h5 class="font-weight-bold text-success mg-b-15">Verifikasi Email Berhasil!</h5>
                <p class="tx-14 text-muted mg-b-25"><?= $res['message']; ?></p>
                <a href="login.php" class="btn btn-success btn-block pd-y-12 font-weight-bold">
                    <i class="fa fa-sign-in mg-r-5"></i> Sign In Sekarang
                </a>
            <?php else: ?>
                <div class="mg-b-20 text-danger" style="font-size: 60px;">
                    <i class="fa fa-times-circle-o"></i>
                </div>
                <h5 class="font-weight-bold text-danger mg-b-15">Verifikasi Email Gagal</h5>
                <p class="tx-14 text-muted mg-b-25"><?= $res['message']; ?></p>

                <?php if (isset($res['email'])): ?>
                    <form action="resend_verification.php" method="POST" class="mg-b-15">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($res['email']); ?>">
                        <button type="submit" class="btn btn-warning btn-block font-weight-bold">
                            <i class="fa fa-refresh mg-r-5"></i> Kirim Ulang Link Verifikasi Baru
                        </button>
                    </form>
                <?php endif; ?>

                <a href="login.php" class="btn btn-secondary btn-block font-weight-bold">Kembali ke Halaman Login</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
