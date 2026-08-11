<?php

//=====================================================START====================//

/*
 *  Base Code   : BangAchil
 *  Email       : kesumaerlangga@gmail.com
 *  Telegram    : @bangachil
 *
 *  Name        : Mikrotik bot telegram - php
 *  Function    : Mikortik api
 *  Manufacture : November 2018
 *  Last Edited : 26 Desember 2018
 *
 *  Please do not change this code
 *  All damage caused by editing we will not be responsible please think carefully,
 *
 */

//=====================================================START SCRIPT====================//

session_start();
error_reporting(0);
require "function.php";
include '../config/system.conn.php';
function IP() {
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP')) {
		$ipaddress = getenv('HTTP_CLIENT_IP');
	} else if (getenv('HTTP_X_FORWARDED_FOR')) {
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	} else if (getenv('HTTP_X_FORWARDED')) {
		$ipaddress = getenv('HTTP_X_FORWARDED');
	} else if (getenv('HTTP_FORWARDED_FOR')) {
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	} else if (getenv('HTTP_FORWARDED')) {
		$ipaddress = getenv('HTTP_FORWARDED');
	} else if (getenv('REMOTE_ADDR')) {
		$ipaddress = getenv('REMOTE_ADDR');
	} else {
		$ipaddress = 'IP Tidak Dikenali';
	}

	return $ipaddress;
}

$ip = IP();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($_POST)) {
	header("Location: login.php");
	exit();
}

$user = isset($_POST['username']) ? trim($_POST['username']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($user) || empty($pass)) {
	create_validasi(
		"Autentifikasi Valid",
		"<img style='width:30%;' class='responsive-image center'; src='../img/loading.svg'/><br><center>Incorrect username or password.</center>",
		"login.php");
} else {
	if (ceklogin($user, $pass)) {
		session_regenerate_id(true);
		$_SESSION['Mikbotamuser'] = $user;
		$_SESSION['Mikbotamid']   = makesession($user);
		$status   = 'Success';
		$sendlast = lastlogin($ip, $user, $status);

		unset($_SESSION['MikbotamUrl']);
		if (isset($_SESSION['app_user_role']) && $_SESSION['app_user_role'] === 'superadmin') {
			header("Location: ../pages/index.php?Mikbotam=manageusers");
		} else {
			header("Location: ../pages/index.php");
		}
		exit();
	} else {
		$status   = 'Valid';
		$sendlast = lastlogin($ip, $user, $status);
		create_validasi(
			"Autentifikasi Valid!",
			"<img style='width:30%;' class='responsive-image center'; src='../img/loading.svg'/><br><center>Incorrect username or password.</center>",
			"login.php"
		);
	}
}
