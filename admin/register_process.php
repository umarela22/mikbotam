<?php
session_start();
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

include_once '../config/system.conn.php';
include_once '../config/system.database.php';

$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email     = isset($_POST['email']) ? trim($_POST['email']) : '';
$password  = isset($_POST['password']) ? $_POST['password'] : '';
$confirm   = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if (empty($full_name) || empty($email) || empty($password)) {
    $_SESSION['register_error'] = 'Seluruh kolom pendaftaran wajib diisi.';
    header("Location: register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Format email tidak valid.';
    header("Location: register.php");
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['register_error'] = 'Password minimal 6 karakter.';
    header("Location: register.php");
    exit();
}

if ($password !== $confirm) {
    $_SESSION['register_error'] = 'Konfirmasi password tidak cocok dengan password yang diinput.';
    header("Location: register.php");
    exit();
}

$res = register_new_app_user($email, $full_name, $password);

if ($res['success']) {
    $_SESSION['register_success'] = $res['message'];
} else {
    $_SESSION['register_error'] = $res['message'];
}

header("Location: register.php");
exit();
