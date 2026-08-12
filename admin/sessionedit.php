<?php
//=====================================================START SCRIPT====================//
error_reporting(0);

if (!isset($_SESSION["Mikbotamuser"])) {
    header("Location:../admin/login.php");
    exit();
}

include_once '../config/system.conn.php';
include_once '../config/system.database.php';

$message = '';
$profile = get_current_app_user_profile();

if (isset($_POST['save_profile'])) {
    $full_name    = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username     = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email        = isset($_POST['email']) ? trim($_POST['email']) : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';

    $res = update_app_user_profile($profile['id'], $full_name, $username, $email, $new_password);
    if ($res['success']) {
        $message = '<div class="alert alert-success mg-b-15"><i class="fa fa-check-circle mg-r-5"></i> ' . htmlspecialchars($res['message']) . '</div>';
        $profile = get_current_app_user_profile();
    } else {
        $message = '<div class="alert alert-danger mg-b-15"><i class="fa fa-exclamation-circle mg-r-5"></i> ' . htmlspecialchars($res['message']) . '</div>';
    }
}
?>

<div class="sl-pagebody">
    <div class="sl-page-title d-flex justify-content-between align-items-center">
        <div>
            <h5>Pengaturan Profil Akun Admin</h5>
            <p>Kelola data nama, email, username, dan password akun admin Anda.</p>
        </div>
    </div>

    <?= $message; ?>

    <div class="row row-sm">
        <div class="col-lg-4 mg-b-20">
            <div class="card bd-primary text-center pd-20">
                <div class="signin-logo mg-b-15">
                    <img src="../img/logoM.svg" alt="Mikbotam" style="width: 100px;">
                </div>
                <h5 class="font-weight-bold text-dark mg-b-2"><?= htmlspecialchars($profile['full_name']); ?></h5>
                <p class="text-muted tx-13 mg-b-10"><?= htmlspecialchars($profile['email']); ?></p>
                <span class="badge badge-primary tx-12 pd-5-10 font-weight-bold text-uppercase">Role: <?= htmlspecialchars($profile['role']); ?></span>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card bd-primary">
                <div class="card-header bg-primary tx-white font-weight-bold d-flex align-items-center">
                    <i class="fa fa-user-circle mg-r-10"></i> Form Edit Profil Akun
                </div>
                <div class="card-body pd-20">
                    <form method="POST" action="./?Mikbotam=sessionedit">
                        <div class="form-group mg-b-15">
                            <label class="font-weight-bold">Nama Lengkap:</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($profile['full_name']); ?>" required>
                        </div>

                        <div class="form-group mg-b-15">
                            <label class="font-weight-bold">Username Login:</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($profile['username']); ?>" required>
                        </div>

                        <div class="form-group mg-b-15">
                            <label class="font-weight-bold">Alamat Email:</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profile['email']); ?>" required>
                        </div>

                        <hr>

                        <div class="form-group mg-b-20">
                            <label class="font-weight-bold">Password Baru (Opsional):</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="text-muted">Biarkan kosong jika hanya ingin mengubah nama, email, atau username.</small>
                        </div>

                        <button type="submit" name="save_profile" class="btn btn-success font-weight-bold pd-x-20">
                            <i class="fa fa-save mg-r-5"></i> Simpan Perubahan Profil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>