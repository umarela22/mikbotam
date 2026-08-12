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

	// Enable automatic PHP app error logging
	$app_log_dir = __DIR__ . '/../logs';
	if (!is_dir($app_log_dir)) {
		@mkdir($app_log_dir, 0755, true);
	}
	ini_set('log_errors', 1);
	ini_set('error_log', $app_log_dir . '/app_error.log');

	require_once __DIR__ . '/system.database.php';

	$active_user_id = null;
	if (isset($_GET['uid']) && intval($_GET['uid']) > 0) {
		$active_user_id = intval($_GET['uid']);
	} elseif (isset($_SESSION['impersonate_user_id']) && intval($_SESSION['impersonate_user_id']) > 0) {
		$active_user_id = intval($_SESSION['impersonate_user_id']);
	} elseif (isset($_SESSION['app_user_id']) && intval($_SESSION['app_user_id']) > 0) {
		$active_user_id = intval($_SESSION['app_user_id']);
	} elseif (isset($_GET['token']) && !empty($_GET['token'])) {
		$u_by_token = $mikbotamdata->get('app_users', ['id'], ['bot_token' => trim($_GET['token'])]);
		if ($u_by_token && isset($u_by_token['id'])) {
			$active_user_id = intval($u_by_token['id']);
		}
	}

	$active_app_user = null;
	if ($active_user_id) {
		$active_app_user = get_app_user_by_id($active_user_id);
	}

	if (session_status() === PHP_SESSION_ACTIVE && $_SERVER['REQUEST_METHOD'] !== 'POST') {
		@session_write_close();
	}

	$settings = getsettings();
	if (!is_array($settings)) {
		$settings = [];
	}
	global $settings;

	if ($active_app_user) {
		$identitiy 			= !empty($active_app_user['full_name']) ? $active_app_user['full_name'] : 'Router_' . $active_app_user['username'];
		$mikrotik_ip 		= isset($active_app_user['mikrotik_ip']) ? $active_app_user['mikrotik_ip'] : '';
		$mikrotik_username  = isset($active_app_user['mikrotik_username']) ? $active_app_user['mikrotik_username'] : '';
		$mikrotik_password  = isset($active_app_user['mikrotik_password']) ? $active_app_user['mikrotik_password'] : '';
		$mikrotik_port 	    = (isset($active_app_user['mikrotik_port']) && intval($active_app_user['mikrotik_port']) > 0) ? intval($active_app_user['mikrotik_port']) : 8728;
		$dnsname			= isset($settings["dnsname"]) ? $settings["dnsname"] : '';	
		$Name_router 		= $identitiy;
		$owner 				= $active_app_user['full_name'];
		$id_own 			= isset($active_app_user['owner_telegram_id']) ? $active_app_user['owner_telegram_id'] : '';
		$token 				= isset($active_app_user['bot_token']) ? $active_app_user['bot_token'] : '';
		$usernamebot 		= isset($settings["Username_bot"]) ? $settings["Username_bot"] : '';
		$voucher_1			= isset($settings["Voucher_1"]) ? $settings["Voucher_1"] : '';
		$Voucher_nonsaldo	= isset($settings["Voucher_nonsaldo"]) ? $settings["Voucher_nonsaldo"] : '';
		$lastupdate         = isset($settings["Tanggal_diubah"]) ? $settings["Tanggal_diubah"] : '';
	} else {
		$identitiy 			= isset($settings["Nama_router"]) ? $settings["Nama_router"] : '';
		$mikrotik_ip 		= isset($settings["IP_router"]) ? $settings["IP_router"] : '';
		$mikrotik_username  = isset($settings["Username_router"]) ? $settings["Username_router"] : '';
		$mikrotik_password  = isset($settings["Pass_router"]) ? decrypturl($settings["Pass_router"]) : '';
		$mikrotik_port 	    = isset($settings["Port"]) ? $settings["Port"] : '';
		$dnsname			= isset($settings["dnsname"]) ? $settings["dnsname"] : '';	
		$Name_router 		= isset($settings["Nama_router"]) ? $settings["Nama_router"] : '';
		$owner 				= isset($settings["Owner"]) ? $settings["Owner"] : '';
		$id_own 			= isset($settings["Id_owner"]) ? $settings["Id_owner"] : '';
		$token 				= isset($settings["Token_bot"]) ? $settings["Token_bot"] : '';
		$usernamebot 		= isset($settings["Username_bot"]) ? $settings["Username_bot"] : '';
		$voucher_1			= isset($settings["Voucher_1"]) ? $settings["Voucher_1"] : '';
		$Voucher_nonsaldo	= isset($settings["Voucher_nonsaldo"]) ? $settings["Voucher_nonsaldo"] : '';
		$lastupdate         = isset($settings["Tanggal_diubah"]) ? $settings["Tanggal_diubah"] : '';
	}
	
	