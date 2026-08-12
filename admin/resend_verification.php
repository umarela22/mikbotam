<?php
session_start();
error_reporting(0);

include_once '../config/system.conn.php';
include_once '../config/system.database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $res = resend_verification_email($email);
    if ($res['success']) {
        $_SESSION['resend_success'] = $res['message'];
    } else {
        $_SESSION['resend_error'] = $res['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kirim Ulang Email Verifikasi - MIKBOTAM</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link href="../lib/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/Mikbotam.min.css">
    <style>
        body {
            background-color: #f0f4f7;
        }
        .resend-box {
            width: 100%;
            max-width: 440px;
            padding: 30px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0, 128, 128, 0.15);
            border: 1px solid #008080;
            margin: 50px auto;
        }
    </style>
</head>
<body>

    <div class="d-flex align-items-center justify-content-center min-vh-100 pd-t-40 pd-b-40">
        <div class="resend-box">
            <div class="signin-logo text-center mg-b-20">
                <img src="../img/logoM.svg" alt="Mikbotam" class="img-fluid center mg-b-10" style="width: 80px;">
                <h4 class="font-weight-bold text-dark mg-b-0">MIKBOTAM</h4>
                <span class="tx-12 font-weight-bold text-success d-block mg-t-2">Kirim Ulang Email Verifikasi</span>
            </div>

            <?php if (isset($_SESSION['resend_error'])): ?>
                <div class="alert alert-danger pd-10 tx-13 mg-b-15">
                    <i class="fa fa-exclamation-circle mg-r-5"></i>
                    <?= $_SESSION['resend_error']; ?>
                </div>
                <?php unset($_SESSION['resend_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['resend_success'])): ?>
                <div class="alert alert-success pd-15 tx-13 mg-b-15">
                    <i class="fa fa-check-circle mg-r-5"></i>
                    <?= $_SESSION['resend_success']; ?>
                </div>
                <div class="text-center mg-t-15">
                    <a href="login.php" class="btn btn-success btn-block font-weight-bold">Kembali ke Halaman Login</a>
                </div>
                <?php unset($_SESSION['resend_success']); ?>
            <?php else: ?>

                <form action="resend_verification.php" method="POST">
                    <div class="form-group mg-b-20">
                        <label class="tx-12 font-weight-bold text-uppercase text-muted">Masukkan Email Anda:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope tx-primary"></i></span>
                            <input type="email" class="form-control" name="email" placeholder="nama@email.com" required value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block pd-y-12 font-weight-bold">
                        <i class="fa fa-paper-plane mg-r-5"></i> Kirim Ulang Link Verifikasi
                    </button>

                    <div class="text-center mg-t-20 tx-13">
                        <a href="login.php" class="font-weight-bold text-success">Kembali ke Sign In</a>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>

</body>
</html>
