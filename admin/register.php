<?php
session_start();
error_reporting(0);

if (isset($_SESSION["Mikbotamid"])) {
    header("Location: ../pages/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Registrasi Akun Admin Mikbotam">
    <meta name="author" content="Andro Network">
    <title>Registrasi Akun Admin - MIKBOTAM</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link href="../lib/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/Mikbotam.min.css">
    <style>
        body {
            background-color: #f0f4f7;
        }
        .register-box {
            width: 100%;
            max-width: 440px;
            padding: 30px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0, 128, 128, 0.15);
            border: 1px solid #008080;
            margin: 40px auto;
        }
        .signin-logo img {
            width: 90px;
        }
        .btn-register {
            background-color: #008080;
            color: #ffffff;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background-color: #006666;
            color: #ffffff;
        }
        .input-group-text {
            background-color: #e9ecef;
            border-color: #ced4da;
        }
    </style>
</head>
<body>

    <div class="d-flex align-items-center justify-content-center min-vh-100 pd-t-30 pd-b-30">
        <div class="register-box">
            <div class="signin-logo text-center mg-b-20">
                <img src="../img/logoM.svg" alt="Mikbotam" class="img-fluid center mg-b-10">
                <h4 class="font-weight-bold text-dark mg-b-0">MIKBOTAM</h4>
                <span class="tx-12 font-weight-bold text-success d-block mg-t-2">Registrasi Akun Admin Baru</span>
            </div>

            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="alert alert-danger pd-10 tx-13 mg-b-15">
                    <i class="fa fa-exclamation-circle mg-r-5"></i>
                    <?= $_SESSION['register_error']; ?>
                </div>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="alert alert-success pd-15 tx-13 mg-b-15">
                    <i class="fa fa-check-circle mg-r-5"></i>
                    <?= $_SESSION['register_success']; ?>
                </div>
                <div class="text-center mg-t-15">
                    <a href="login.php" class="btn btn-success btn-block font-weight-bold">Sign In Sekarang</a>
                </div>
                <?php unset($_SESSION['register_success']); ?>
            <?php else: ?>

                <form action="register_process.php" method="POST">
                    <div class="form-group mg-b-15">
                        <label class="tx-12 font-weight-bold text-uppercase text-muted">Nama Lengkap:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user tx-primary"></i></span>
                            <input type="text" class="form-control" name="full_name" placeholder="Nama Lengkap Anda" required autocomplete="name">
                        </div>
                    </div>

                    <div class="form-group mg-b-15">
                        <label class="tx-12 font-weight-bold text-uppercase text-muted">Email (Digunakan untuk Login):</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope tx-primary"></i></span>
                            <input type="email" class="form-control" name="email" placeholder="nama@email.com" required autocomplete="email">
                        </div>
                        <small class="form-text text-muted">Link verifikasi akan dikirimkan ke email ini.</small>
                    </div>

                    <div class="form-group mg-b-15">
                        <label class="tx-12 font-weight-bold text-uppercase text-muted">Password:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock tx-primary"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-group mg-b-20">
                        <label class="tx-12 font-weight-bold text-uppercase text-muted">Konfirmasi Password:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-check-square-o tx-primary"></i></span>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Ulangi Password" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register btn-block pd-y-12 font-weight-bold">
                        <i class="fa fa-paper-plane mg-r-5"></i> Daftar & Kirim Email Verifikasi
                    </button>

                    <div class="text-center mg-t-20 tx-13">
                        Sudah punya akun? <a href="login.php" class="font-weight-bold text-success">Sign In di sini</a>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>

    <script src="../lib/jquery/jquery.js"></script>
</body>
</html>
