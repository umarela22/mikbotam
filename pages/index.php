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
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}

ob_start();
error_reporting(0);
$_SESSION['MikbotamUrl'] = $_SERVER['REQUEST_URI'];

if (!isset($_SESSION["Mikbotamuser"])) {
	header("Location:../admin/login.php");
	exit();
} else {

	include_once __DIR__ . '/../config/system.conn.php';
	include_once __DIR__ . '/../config/system.database.php';
	include '../include/header.php';
	include '../include/Home.php';

	echo '<div class="sl-mainpanel">';

	$page = isset($_GET["Mikbotam"]) ? $_GET["Mikbotam"] : '';

	switch ($page) {
		case "Record":
			include "recordcounter.php";
			break;
		case "sendVoc":
			include "sendvoc.php";
			break;
		case "Settings":
			include "settings.php";
			break;
		case "addprofile":
			include "../hotspot/add_profile.php";
			break;
		case "Hotspotuserlist":
			include "../hotspot/user.php";
			break;
		case "comingsoon":
			include "comingson.php";
			break;
		case "sendMessage":
			include "sendmess.php";
			break;
		case "SettingsVoc":
			include "settingsvoc.php";
			break;
		case "SettingsVocnonsaldo":
			include "settingsvocnonsaldo.php";
			break;
		case "setwebhook":
			include "../tools/setwebhook.php";
			break;
		case "NewUser":
			include "nusercounter.php";
			break;
		case "topupsaldo":
			include "topup.php";
			break;
		case "topdownsaldo":
			include "topdown.php";
			break;
		case "monitortraffic":
			include "graphmikbotam.php";
			break;
		case "logout":
			session_destroy();
			echo "<script>sessionStorage.clear();</script>";
			echo "<script>window.location='../index.php'</script>";
			break;
		case "userlist":
			include "userlist.php";
			break;
		case "useractive":
			include "../hotspot/user_active.php";
			break;
		case "about":
			include "../about/about.php";
			break;
		case "boteditor":
			include "boteditor.php";
			break;
		case "pppuser":
			include "../ppp/ppp_user.php";
			break;
		case "pppactive":
			include "../ppp/ppp_active.php";
			break;
		case "pppprofile":
			include "../ppp/ppp_profile.php";
			break;
		case "pppbilling":
			include "../ppp/ppp_billing.php";
			break;
		case "ppppackages":
			include "../ppp/ppp_packages.php";
			break;
		case "pppisolir":
			include "../ppp/ppp_isolir_settings.php";
			break;
		case "manageusers":
			include "manage_users.php";
			break;
		case "Settingstext":
			include "settingstext.php";
			break;
		default:
			include "dashboard.php";
			break;
	}

	include '../include/footer.php';
}

?>
