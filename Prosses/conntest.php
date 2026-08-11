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
 *  Last Edited : 26 Desember 2019
 *
 *  Please do not change this code
 *  All damage caused by editing we will not be responsible please think carefully,
 *
 */

//=====================================================START SCRIPT====================//
session_start();
error_reporting(0); 

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';
if ($cmd === "testcon") {
	$ip = isset($_POST['ip']) ? trim($_POST['ip']) : '';
	$user = isset($_POST['user']) ? trim($_POST['user']) : '';
	$pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';
	$ports = isset($_POST['portapi']) && !empty($_POST['portapi']) ? intval($_POST['portapi']) : 8728;

	if (empty($ip) || empty($user)) {
		echo '        <div class="card pd-20 pd-sm-20 bg-danger "><div class="signin-logo tx-center tx-40 tx-bold tx-white">DISCONNECT </div>
        <div class="tx-center  tx-white">Silahkan isi IP Router dan Username MikroTik terlebih dahulu.</div>
        </div>';
		exit();
	}

	include_once '../Api/routeros_api.class.php';
	$wait = 3; // wait Timeout In Seconds
	$host = $ip;
	$API = new routeros_api();
	$API->timeout = 3;

	$fp = @fsockopen($host, $ports, $errCode, $errStr, $wait);
	if ($fp) {
		if ($API->connect($ip, $user, $pass, $ports)) {
			echo '        <div class="card pd-20 pd-sm-20 bg-primary "><div class="signin-logo tx-center tx-40 tx-bold tx-white">CONNECTED </div>
        <div class="tx-center  tx-white">Mikbotam Connected To Your Router Successfully</div>
        </div>';
			$API->disconnect();
		} else {
			echo '        <div class="card pd-20 pd-sm-20 bg-danger "><div class="signin-logo tx-center tx-40 tx-bold tx-white">DISCONNECT </div>
        <div class="tx-center  tx-white">Please check again to make sure credentials are correct</div>
        <div class="tx-center  tx-white">ERROR : Incorrect Username Or Password</div>
        </div>';
		}
		@fclose($fp);
	} else {
		echo '        <div class="card pd-20 pd-sm-20 bg-danger "><div class="signin-logo tx-center tx-40 tx-bold tx-white">DISCONNECT </div>
        <div class="tx-center  tx-white">Gagal terhubung ke Router IP: ' . htmlspecialchars($ip) . ' Port: ' . htmlspecialchars($ports) . '</div>
        <div class="tx-center  tx-white">ERROR : ' . htmlspecialchars($errCode) . ' ' . htmlspecialchars($errStr) . '</div>
        </div>';
	}
}
?>