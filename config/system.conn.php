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
	require_once ('system.database.php');
	$settings=getsettings();
	if (!is_array($settings)) {
		$settings = [];
	}
	global $settings;
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
	
	