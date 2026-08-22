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
 *  Last Edited : 04 Desember 2018
 *
 *  Please do not change this code
 *  All damage caused by editing we will not be responsible please think carefully,
 *
 */

//=====================================================START SCRIPT====================//
date_default_timezone_set('Asia/Jakarta');

include 'system.config.php';

function get_current_tenant_id() {
	if (isset($_GET['filter_tenant_id'])) {
		if ($_GET['filter_tenant_id'] === 'all' || intval($_GET['filter_tenant_id']) === -1) {
			return 0; // 0 means all tenants / no tenant filter
		}
		if (intval($_GET['filter_tenant_id']) > 0) {
			return intval($_GET['filter_tenant_id']);
		}
	}
	if (isset($_GET['uid']) && intval($_GET['uid']) > 0) {
		return intval($_GET['uid']);
	}
	if (isset($_SESSION['impersonate_user_id']) && intval($_SESSION['impersonate_user_id']) > 0) {
		return intval($_SESSION['impersonate_user_id']);
	}
	if (isset($_SESSION['app_user_id']) && intval($_SESSION['app_user_id']) > 0) {
		if (isset($_SESSION['app_user_role']) && $_SESSION['app_user_role'] === 'superadmin') {
			return 0; // Superadmin default sees global aggregate unless filtered
		}
		return intval($_SESSION['app_user_id']);
	}
	return 1;
}

function daftar($id, $name) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$last_id = $mikbotamdata->insert('re_settings', [
		'id_user' => $id,
		'nama_seller' => $name,
		'app_user_id' => $tenant_id,
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),

	]);

	return $last_id;
}

function daftarid($id, $name, $notlp, $saldo) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();

	$last_id = $mikbotamdata->insert('re_settings', [
		'id_user' => $id,
		'nama_seller' => $name,
		'nomer_tlp' => $notlp,
		'saldo' => $saldo,
		'app_user_id' => $tenant_id,
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),

	]);

	if ($last_id == true) {
		$hasil_rupiah = "Rp " . number_format($saldo, 2, ',', '.');
		$text = "<code>  Informasi Add user</code>\n";
		$text .= "<code>========================</code>\n";
		$text .= "<code>  ID User  :</code> <code>$id</code>\n";
		$text .= "<code>  Username :</code> @$name\n";
		$text .= "<code>  Number   : $notlp </code>\n";
		$text .= "<code>  Saldo   : $hasil_rupiah </code>\n";
		$text .= "<code>  Status   : Berhasil  </code>\n";
		$text .= "<code>========================</code>\n";
	} else {
		$hasil_rupiah = "Rp " . number_format($saldo, 2, ',', '.');
		$text = "<code>  Informasi Add user</code>\n";
		$text .= "<code>========================</code>\n";
		$text .= "<code>  ID User  :</code> <code>$id</code>\n";
		$text .= "<code>  Username :</code> @$name\n";
		$text .= "<code>  Number   : $notlp </code>\n";
		$text .= "<code>  Saldo   : $hasil_rupiah </code>\n";
		$text .= "<code>  Status   : Gagal Terkoneksi dengan database  </code>\n";
		$text .= "<code>========================</code>\n";
	}

	return $text;
}
function encrypturl($pamerbojo) {
	if (empty($pamerbojo)) return '';
	$key = hash('sha256', '4ku4ll_mikbotam_secret_key', true);
	$iv = openssl_random_pseudo_bytes(16);
	$encrypted = openssl_encrypt($pamerbojo, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
	if ($encrypted !== false) {
		return 'enc:v2:' . base64_encode($iv . $encrypted);
	}
	$serondenggosong = '';
	$kunciobeng = '4ku4ll';
	for ($i = 0; $i < strlen($pamerbojo); $i++) {
		$buahnanas = substr($pamerbojo, $i, 1);
		$kunciinggris = substr($kunciobeng, ($i % strlen($kunciobeng)) - 1, 1);
		$buahnanas = chr(ord($buahnanas) + ord($kunciinggris));
		$serondenggosong .= $buahnanas;
	}
	return base64_encode($serondenggosong);
}

function decrypturl($pamerbojo) {
	if (empty($pamerbojo)) return '';
	if (strpos($pamerbojo, 'enc:v2:') === 0) {
		$raw = base64_decode(substr($pamerbojo, 7));
		if (strlen($raw) > 16) {
			$iv = substr($raw, 0, 16);
			$encrypted = substr($raw, 16);
			$key = hash('sha256', '4ku4ll_mikbotam_secret_key', true);
			$decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
			if ($decrypted !== false) {
				return $decrypted;
			}
		}
	}
	$pamerbojo = base64_decode($pamerbojo);
	$serondenggosong = '';
	$kunciobeng = '4ku4ll';
	for ($i = 0; $i < strlen($pamerbojo); $i++) {
		$buahnanas = substr($pamerbojo, $i, 1);
		$kunciinggris = substr($kunciobeng, ($i % strlen($kunciobeng)) - 1, 1);
		$buahnanas = chr(ord($buahnanas) - ord($kunciinggris));
		$serondenggosong .= $buahnanas;
	}
	return $serondenggosong;
}

function lihatsaldo($id) {
	global $mikbotamdata;
	if (empty($id)) {
		return 0;
	}
	$data = $mikbotamdata->get('re_settings', [
		'saldo',
		'id_user'
	], [
		'AND' => ['id_user' => strval($id)],
		'ORDER' => ['id' => 'DESC']
	]);

	if (!is_array($data) || !isset($data['saldo'])) {
		// Fallback try with integer id
		$data = $mikbotamdata->get('re_settings', [
			'saldo',
			'id_user'
		], [
			'AND' => ['id_user' => intval($id)],
			'ORDER' => ['id' => 'DESC']
		]);
	}

	return (is_array($data) && isset($data['saldo'])) ? intval($data['saldo']) : 0;
}

function bagisaldo($fromid, $to_id, $subtotal){
	
	global $mikbotamdata;
	$seefroom=$mikbotamdata-get('re_settings',[
		
   'saldo',
   'id_user'
   	
   ],[
   		
   	'id_user'=>$fromid,
   	
	]);
	
		$saldo= $seefroom['saldo'];
		
	$seeto_id=$mikbotamdata-get('re_settings',[
		
   'saldo',
   'id_user'
   	
   ],[
   		
   	'id_user'=>$to_id,
   	
	]);	
	
		$saldoto_id= $seeto_id['saldo'];
		
		
		return ;
}
function topupresseller($id, $name, $jumlah, $id_own) {

	global $mikbotamdata;
	$ceksaldoawal = $mikbotamdata->get('re_settings', [
		'id_user',
		'saldo',
	], [
		'id_user' => $id

	]);

	$saldoawal = $ceksaldoawal["saldo"];

	$update = $mikbotamdata->update('re_settings', [

		'saldo' => $jumlah + $saldoawal,
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),
	], [
		'id_user' => $id,
	]);
	if ($update == true) {
		$datacek = $mikbotamdata->get('re_settings', [
			'id_user',
			'nama_seller',
			'saldo',
		], [
			'id_user' => $id

		]);

		$nama = $datacek["nama_seller"];
		$saldo = $datacek["saldo"];

		$tenant_id = get_current_tenant_id();
		$hasil = $mikbotamdata->insert('re_operating', [
			'id_user' => $id,
			'nama_seller' => $nama,
			'saldo_awal' => $saldoawal,
			'saldo_akhir' => $saldo,
			'top_up' => $jumlah,
			'keterangan' => 'topup',
			'top_up_fromid' => $id_own,
			'app_user_id' => $tenant_id,
			'Waktu' => date('H:i:s'),
			'Tanggal' => date('Y-m-d'),
		]);
		$idowner = $mikbotamdata->select('st_mikbotam', [
			"Id_owner",
		]);

		$text = "<code>  Informasi TOP UP saldo</code>\n";
		$text .= "<code>========================</code>\n";
		$text .= "<code>ID User   :</code> <code>$id</code>\n";
		$text .= "<code>Username  :</code> @$nama\n";
		$text .= "<code>Status    : Berhasil </code>\n";
		$text .= "<code>Nominal   : " . rupiah($jumlah) . " </code>\n";
		$text .= "<code>Saldo awal: " . rupiah($saldoawal) . " </code>\n";
		$text .= "<code>Saldo akhir: " . rupiah($saldo) . " </code>\n";
		$text .= "<code>Outletid  : " . $idowner[0]['Id_owner'] . "</code>\n";
		$text .= "<code>========================</code>\n";
	} else {
		$text = "<code>Informasi TOP UP saldo</code>\n";
		$text .= "<code>========================</code>\n";
		$text .= "<code>ID User  :</code> <code>$id</code>\n";
		$text .= "<code>Username :</code> @$nama\n";
		$text .= "<code>Status   : Gagal  database error</code>\n";
		$text .= "<code>========================</code>\n";
	}
	$error = $mikbotamdata->error();
	return $text;
}
function updatesaldo($id, $jumlahan) {
	global $mikbotamdata;
	$ceksaldoawal = $mikbotamdata->get('re_settings', [
		'id_user',
		'saldo',
	], [
		'id_user' => $id

	]);

	$saldoawal = $ceksaldoawal["saldo"];

	$update = $mikbotamdata->update('re_settings', [
		'saldo[+]' => $jumlahan,
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),

	], [
		'id_user' => $id,
	]);

	$data = $mikbotamdata->get('re_settings', [

		'id_user',
		'nama_seller',
		'saldo',
	], [
		'id_user' => $id

	]);

	$nama = $data["nama_seller"];
	$saldo = $data["saldo"];
	$idowner = $mikbotamdata->select('st_mikbotam', [
		"Id_owner",
	]);

	$hasil = $mikbotamdata->insert('re_operating', [
		'id_user' => $id,
		'nama_seller' => $nama,
		'saldo_awal' => $saldoawal,
		'saldo_akhir' => $saldo,
		'top_up' => $jumlahan,
		'keterangan' => 'topup',
		'top_up_fromid' => $idowner[0]['Id_owner'],
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),
	]);

	$saldo = $data["saldo"];

	return $saldo;
}
function sisasaldo($id, $voucher) {
	global $mikbotamdata;

	$data = $mikbotamdata->get('re_settings', [
		'saldo',
		'id_user'
	], [
		'id_user' => $id

	]);

	$hasil = $data["saldo"];
	$hasilpenjumlahan = $hasil - $voucher;

	$max = max($hasilpenjumlahan, 0);
	if ($max == 0) {
		return true;
	} else {
		return false;
	}
}
function minussaldo($id, $hasilpenjumlahan) {
	global $mikbotamdata;

	$update = $mikbotamdata->update('re_settings', [

		'saldo' => $hasilpenjumlahan,
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),

	], [
		'id_user' => $id,
	]);

	$data = $mikbotamdata->get('re_settings', [

		'id_user',
		'nama_seller',
		'saldo',
	], [
		'id_user' => $id

	]);

	$nama = $data["nama_seller"];
	$saldo = $data["saldo"];

	return $saldo;
}

function topdown($id, $jumlah) {

	global $mikbotamdata;
	$lihatsaldo = lihatsaldo($id);
	$hasilpenjumlahan = $lihatsaldo-$jumlah;
	$max = min($hasilpenjumlahan, 0);

	if ($max == 0) {
		$update = $mikbotamdata->update('re_settings', [
			'saldo[-]' => $jumlah,
			'Waktu' => date('H:i:s'),
			'Tanggal' => date('Y-m-d'),
		], [
			'id_user' => $id,
		]);
		$seeuser = lihatuser($id);
		$maketable = '
              <div class="card-body">
              <div class="alert alert-success" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">X</span>
              </button>
              <div class="d-flex align-items-center justify-content-start">
              <pre>Successful Top Down saldo<br>ID              : ' . $id . "<br>Username        : " . $seeuser['nama_seller'] . "<br>Ending balance  : " . rupiah($seeuser['saldo']) . '<br></pre></div></div></div>';

	} else {
		$maketable = '
              <div class="card-body">
              <div class="alert alert-success" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">X</span>
              </button>
              <div class="d-flex align-items-center justify-content-start">
              <pre>failed Top Down saldo<br>ID              : ' . $id . '<br>Username        : ' . $seeuser['nama_seller'] . '<br>Saldo tidak mencukupi untuk top down<br></pre></div></div></div>';
	}


	return 	$maketable;
}
function belivoucher($id, $usernamepelanggan, $princevoc,$markup, $username, $password, $uptime, $keterangan) {
	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$data = $mikbotamdata->get('re_settings', [
		'saldo',
		'id_user'
	], [
		'id_user' => $id

	]);
	$saldoawal = $data["saldo"];

	if (isset($data)) {
		$last_id = $mikbotamdata->insert('re_operating', [
			'id_user' => $id,
			'nama_seller' => $usernamepelanggan,
			'saldo_awal' => $saldoawal,
			'saldo_akhir' => $saldoawal - $princevoc,
			'beli_voucher' => $princevoc,
			'markup_voucher'=>$markup,
			'username_voucher' => $username,
			'password_voucher' => $password,
			'exp_voucher' => $uptime,
			'keterangan' => $keterangan,
			'app_user_id' => $tenant_id,
			'Waktu' => date('H:i:s'),
			'Tanggal' => date('Y-m-d'),

		]);
	}

	$update = $mikbotamdata->update('re_settings', [
		'saldo[-]' => $princevoc,
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),
		'voucher_terjual[+]' => 1,
	], [
		'id_user' => $id,
	]);
	if ($keterangan == 'Success') {
		$report = $mikbotamdata->insert('st_reportdata', [
			'id_user' => $id,
			'nama_user' => $usernamepelanggan,
			'harga' => $princevoc,
			'status' => $keterangan,
			'transaksi' => 'halo',
			'pendapatan' => $princevoc,
			'app_user_id' => $tenant_id,
			'Waktu' => date('H:i:s'),
			'Tanggal' => date('Y-m-d'),

		]);
	}

	return $update;
}
function lihatdata() {
	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = [];
	if ($tenant_id) {
		$where['app_user_id'] = $tenant_id;
	}
	$data = $mikbotamdata->select('re_settings', [
		'id_user',
		'nama_seller',
		'nomer_tlp',
		'saldo',
		'voucher_terjual',
		'jumlah_debit_terjual',
		'type',
		'status',
		'keterangan',
		'Waktu',
		'Tanggal',

	], $where);

	return is_array($data) ? $data : [];
}

function sendsms($phone, $message) {
	global $mikbotamdata;
	$data = $mikbotamdata->get('st_smsgateway', [
		'id_user',
		'nama_seller',
		'nomer_tlp',
		'saldo',
		'voucher_terjual',
		'jumlah_debit_terjual',
		'type',
		'status',
		'keterangan',
		'Waktu',
		'Tanggal',

	], [
		'id_user' => $id

	]);

	return $data;
}

function lihatuser($id) {
	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = ['id_user' => $id];
	if ($tenant_id) {
		$where = ['AND' => ['id_user' => $id, 'app_user_id' => $tenant_id]];
	}
	$data = $mikbotamdata->get('re_settings', [
		'id_user',
		'nama_seller',
		'nomer_tlp',
		'saldo',
		'voucher_terjual',
		'jumlah_debit_terjual',
		'type',
		'status',
		'keterangan',
		'Waktu',
		'Tanggal',

	], $where);

	return is_array($data) ? $data : [];
}
function updateuser($id, $nama_seller, $nomer_tlp, $saldo) {
	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$update = $mikbotamdata->update('re_settings', [
		'nama_seller' => $nama_seller,
		'nomer_tlp'   => $nomer_tlp,
		'saldo'       => $saldo,
		'app_user_id' => $tenant_id ? $tenant_id : 1
	], [
		'id_user' => $id
	]);

	return $update;
}
function deleteuser($id) {
	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = ['id_user' => $id];
	if ($tenant_id) {
		$where = ['AND' => ['id_user' => $id, 'app_user_id' => $tenant_id]];
	}

	$datareseller = $mikbotamdata->delete('re_settings', $where);
	$deletoperating = $mikbotamdata->delete('re_operating', $where);
	$deletlaporan = $mikbotamdata->delete('st_reportdata', $where);

	return $datareseller;
}

function has($id) {
	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = ['id_user' => $id];
	if ($tenant_id) {
		$where = ['AND' => ['id_user' => $id, 'app_user_id' => $tenant_id]];
	}
	$data = $mikbotamdata->has('re_settings', $where);

	return $data;
}

function haspin($PIN) {
	global $mikbotamdata;
	$data = $mikbotamdata->has('mikhbotam_id', [

		'token' => $PIN

	]);

	return $data;
}

function lihatowner() {
	global $mikbotamdata;
	$data = $mikbotamdata->get('st_mikbotam', [
		'Id_owner'
	]);

	$hasil = $data[Id_owner];

	return $hasil;
}
function gettopupfrom($id) {
	global $mikbotamdata;
	$data = $mikbotamdata->get('re_operating', [
		'top_up_from',
		'id_user'
	], [
		'id_user' => $id

	]);

	$hasil = $data["top_up_from"];

	return $hasil;
}
function rupiah($angka) {

	$hasil_rupiah = "Rp " . number_format($angka, 0, ',', '.');

	return $hasil_rupiah;
}
function tambah($jumlah, $saldoakhir) {
	$z = $jumlah + $saldoakhir;
	return $z;
}
function minus($jumlah, $poucer) {
	$z = $jumlah - $poucer;
	return $z;
}
function markup($jumlah, $poucer) {
	$z = $jumlah - $poucer;

	$hasil = "$jumlah - $poucer =  $z Saldo terpotong $z";
	return $hasil;
}
function adduser($id, $name, $notlp, $saldo) {

	global $mikbotamdata;

	$data = $mikbotamdata->has('re_settings', [

		'id_user' => $id

	]);
	if ($data) {
		$hasil = 'wrongas';
	} elseif (preg_match('/^[0-9]+$/', $id) && preg_match('/^[0-9]+$/', $notlp) && preg_match('/^[0-9]+$/', $saldo)) {
		$last_id = $mikbotamdata->insert('re_settings', [
			'id_user' => $id,
			'nama_seller' => $name,
			'nomer_tlp' => $notlp,
			'saldo' => $saldo,

			'Waktu' => date('H:i:s'),
			'Tanggal' => date('Y-m-d'),

		]);
		$hasil = 'done';
	} else {
		$hasil = 'wrongas';
	}
	return $hasil;
}
function countuser() {
	date_default_timezone_set('Asia/Jakarta');
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, date("m"), $date, date("Y")))];
	global $mikbotamdata;
	$data = $mikbotamdata->select('re_settings', [
		'id_user',
		'nama_seller',
		'nomer_tlp',
		'saldo',
		'voucher_terjual',
		'jumlah_debit_terjual',
		'type',
		'status',
		'keterangan',
		'Waktu',
		'Tanggal',

	], [
		'AND' => [

			'Tanggal[<>]' => $startTime,
		]]);

	$ech = is_array($data) ? count($data) : 0;
	return $ech;
}
function counterror() {
	date_default_timezone_set('Asia/Jakarta');
	$date = date('Y-m-d');
	$makedate = date('Y-m-d', strtotime('-1 month'));
	
	global $mikbotamdata;
	$gethistory = $mikbotamdata->select('re_operating', [
		"No",
		"id_user",
		"nama_seller",
		"saldo_awal",
		"beli_voucher",
		"saldo_akhir",
		"top_up",
		"top_up_fromid",
		"username_voucher",
		"password_voucher",
		"exp_voucher",
		"keterangan",
		"Waktu",
		"Tanggal"

	], [

		'AND' => [
			'OR' => [
				'keterangan' => 'gagal',
				'Tanggal[<]' => $makedate,
			],
			'OR' => [
				'keterangan' => 'gagalprint',
				'Tanggal[<]' => $makedate
			]

		]

	]
	);
	$ech = is_array($gethistory) ? count($gethistory) : 0;
	return $ech;
}
function sethistoryidbymonth($id,$month) {
	
	$dateinput = date("Y")."-$month-".date("d");
	$newformat = date('Y-m-d',$dateinput);
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, $month, 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, $month, $date, date("Y")))];
	global $mikbotamdata;
	$gethistory = $mikbotamdata->select('re_operating', [
		"No",
		"id_user",
		"nama_seller",
		"saldo_awal",
		"beli_voucher",
		"markup_voucher",
		"saldo_akhir",
		"top_up",
		"top_up_fromid",
		"username_voucher",
		"password_voucher",
		"exp_voucher",
		"keterangan",
		"Waktu",
		"Tanggal"

	], [
		'AND' => [

			'id_user' => $id,
			'Tanggal[<>]' => $startTime,
			'OR' => [
				'keterangan[!]' => ['gagal', 'gagalprint'],

			],

		],

		'ORDER' => [
			'Tanggal' => 'ASC',
			'Waktu' => 'DESC',
		]
	]
	);

	return $gethistory;
}
function sethistoryidbyrange($id,$start,$end) {
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));

	global $mikbotamdata;
	$gethistory = $mikbotamdata->select('re_operating', [
		"No",
		"id_user",
		"nama_seller",
		"saldo_awal",
		"beli_voucher",
		"markup_voucher",
		"saldo_akhir",
		"top_up",
		"top_up_fromid",
		"username_voucher",
		"password_voucher",
		"exp_voucher",
		"keterangan",
		"Waktu",
		"Tanggal"

	], [
		'AND' => [

			'id_user' => $id,
			'Tanggal[<>]' => [$start, $end],
			'OR' => [
				'keterangan[!]' => ['gagal', 'gagalprint'],

			],

		],

		'ORDER' => [
			'Tanggal' => 'ASC',
			'Waktu' => 'DESC',
		]
	]
	);

	return $gethistory;
}
function sethistoryid($id) {
	date_default_timezone_set('Asia/Jakarta');
	$date = date('Y-m-d');
	$makedate = date('Y-m-d', strtotime('-1 month'));

	global $mikbotamdata;
	$gethistory = $mikbotamdata->select('re_operating', [
		"No",
		"id_user",
		"nama_seller",
		"saldo_awal",
		"beli_voucher",
		"markup_voucher",
		"saldo_akhir",
		"top_up",
		"top_up_fromid",
		"username_voucher",
		"password_voucher",
		"exp_voucher",
		"keterangan",
		"Waktu",
		"Tanggal"

	], [
		'AND' => [

			'id_user' => $id,
			'Tanggal[<>]' => [$makedate,$date],
			'OR' => [
				'keterangan[!]' => ['gagal', 'gagalprint'],

			],

		],

		'ORDER' => [
			'Tanggal' => 'DESC',
			'Waktu' => 'DESC',
		]
	]
	);

	return $gethistory;
}
function sethistory($id) {
	date_default_timezone_set('Asia/Jakarta');
	$date = date('Y-m-d');
	$makedate = date('Y-m-d', strtotime('-1 month'));

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where_and = ['Tanggal[<>]' => [$makedate,$date]];
	if ($tenant_id) {
		$where_and['app_user_id'] = $tenant_id;
	}
	$gethistory = $mikbotamdata->select('re_operating', [
		"No",
		"id_user",
		"nama_seller",
		"saldo_awal",
		"beli_voucher",
		"saldo_akhir",
		"top_up",
		"top_up_fromid",
		"username_voucher",
		"password_voucher",
		"exp_voucher",
		"keterangan",
		"Waktu",
		"Tanggal"

	], [
		'AND' => $where_and,
		'ORDER' => [
			'Tanggal' => 'DESC',
			'Waktu' => 'DESC',
		]]
	);

	return is_array($gethistory) ? $gethistory : [];
}
function estimasidata() {
	date_default_timezone_set('Asia/Jakarta');
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, date("m"), $date, date("Y")))];

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where_and = ['Tanggal[<>]' => $startTime];
	if ($tenant_id) {
		$where_and['app_user_id'] = $tenant_id;
	}
	$reportekstimasi = $mikbotamdata->sum('st_reportdata', [
		'pendapatan',
	], [
		'AND' => $where_and
	]);

	return $reportekstimasi ? $reportekstimasi : 0;
}
function getcounttopup() {
	date_default_timezone_set('Asia/Jakarta');
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, date("m"), $date, date("Y")))];

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where_and = [
		'keterangan' => 'topup',
		'Tanggal[<>]' => $startTime,
	];
	if ($tenant_id) {
		$where_and['app_user_id'] = $tenant_id;
	}
	$reportekstimasi = $mikbotamdata->sum('re_operating', [
		'top_up',
	], [
		'AND' => $where_and

	]);

	return $reportekstimasi ? $reportekstimasi : 0;
}
function countvoucher() {
	date_default_timezone_set('Asia/Jakarta');
	global $mikbotamdata;
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, date("m"), $date, date("Y")))];

	$tenant_id = get_current_tenant_id();
	$where_and = [
		'keterangan' => 'Success',
		'Tanggal[<>]' => $startTime,
	];
	if ($tenant_id) {
		$where_and['app_user_id'] = $tenant_id;
	}
	$gethistory = $mikbotamdata->select('re_operating', [
		"keterangan",
		"Waktu",
		"Tanggal"

	], [
		'AND' => $where_and

	]
	);
	$ech = is_array($gethistory) ? count($gethistory) : 0;
	return $ech;
}
function historydata($id) {
	date_default_timezone_set('Asia/Jakarta');
	$date = date('Y-m-d');
	$makedate = date('Y-m-d', strtotime('-1 month'));

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where_and = [
		'keterangan' => 'Success',
		'Tanggal[<>]' => [$makedate,$date],
	];
	if ($tenant_id) {
		$where_and['app_user_id'] = $tenant_id;
	}
	$gethistory = $mikbotamdata->select('re_operating', [
		"No",
		"id_user",
		"nama_seller",
		"saldo_awal",
		"beli_voucher",
		"saldo_akhir",
		"top_up",
		"top_up_fromid",
		"username_voucher",
		"password_voucher",
		"exp_voucher",
		"keterangan",
		"Waktu",
		"Tanggal"

	], [
		'AND' => [
			'keterangan' => 'Success',
			'Tanggal[<>]' => [$makedate,$date],
		]

	]
	);
	$ech = is_array($gethistory) ? count($gethistory) : 0;
	return $ech;
}
function gethistory($id) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = [
		"ORDER" => [
			"Tanggal" => "DESC",
			"Waktu" => "DESC"
		]
	];
	if ($tenant_id) {
		$where["AND"] = ["app_user_id" => $tenant_id];
	}
	$gethistory = $mikbotamdata->select('re_operating', [
		"No",
		"id_user",
		"nama_seller",
		"saldo_awal",
		"beli_voucher",
		"saldo_akhir",
		"top_up",
		"top_up_fromid",
		"username_voucher",
		"password_voucher",
		"exp_voucher",
		"keterangan",
		"Waktu",
		"Tanggal"

	], $where);

	return is_array($gethistory) ? $gethistory : [];
}
function login() {

	global $mikbotamdata;
	$settings = $mikbotamdata->get('mikhbotam_id', [
		"u_id",
		"u_user",
		"u_pass"

	]);
	$hasil = $settings["u_user"];
	return $hasil;
}
function ceklogin($user, $pass) {
	global $mikbotamdata;
	init_ppp_billing_tables();

	$cols = ['id', 'username', 'email', 'password', 'full_name', 'role', 'status', 'mikrotik_ip', 'mikrotik_username', 'mikrotik_password', 'mikrotik_port', 'bot_token', 'owner_telegram_id'];
	$rows = $mikbotamdata->select('app_users', $cols, [
		'OR' => [
			'username' => $user,
			'email' => $user
		]
	]);
	$app_user = (is_array($rows) && isset($rows[0])) ? $rows[0] : false;
	if ($app_user) {
		if ($app_user['status'] === 'unverified') {
			if (session_status() === PHP_SESSION_NONE) {
				@session_start();
			}
			$_SESSION['login_error'] = 'unverified';
			$_SESSION['unverified_email'] = !empty($app_user['email']) ? $app_user['email'] : $app_user['username'];
			return false;
		}

		if ($app_user['status'] === 'active') {
			$valid = false;
			if (password_verify($pass, $app_user['password'])) {
				$valid = true;
			} elseif ($app_user['password'] === $pass) {
				$valid = true;
				$newHash = password_hash($pass, PASSWORD_BCRYPT);
				$mikbotamdata->update('app_users', ['password' => $newHash], ['id' => $app_user['id']]);
			}

			if ($valid) {
				if (session_status() === PHP_SESSION_NONE) {
					@session_start();
				}
				$_SESSION['app_user_id']   = $app_user['id'];
				$_SESSION['app_user_role'] = $app_user['role'];
				$_SESSION['app_full_name'] = $app_user['full_name'];
				return true;
			}
		}
	}

	$account = $mikbotamdata->get('mikhbotam_id', [
		'u_id',
		'u_user',
		'u_pass'
	], [
		'u_user' => $user
	]);

	if ($account && !empty($account['u_pass'])) {
		$storedPass = $account['u_pass'];
		if (password_verify($pass, $storedPass)) {
			return true;
		}
		if ($storedPass === $pass) {
			$newHash = password_hash($pass, PASSWORD_BCRYPT);
			$mikbotamdata->update('mikhbotam_id', [
				'u_pass' => $newHash
			], [
				'u_id' => $account['u_id']
			]);
			return true;
		}
	}
	return false;
}
function lastlogin($ip, $user, $status) {

	global $mikbotamdata;
	$settings = $mikbotamdata->update('mikhbotam_id', [

		'lastlogin' => date('Y-m-d'),
		'ip' => $ip,
		'user' => $user,
		'status' => $status

	]);

	return $settings;
}
function getlastlogin() {

	global $mikbotamdata;
	$settings = $mikbotamdata->get('mikhbotam_id', [

		'lastlogin',
		'ip',
		'user',
		'status'

	]);

	return $settings;
}
function Mikbotamlogin($id, $user, $pass) {
	global $mikbotamdata;
	$hashedPass = password_hash($pass, PASSWORD_BCRYPT);
	$data = $mikbotamdata->update('mikhbotam_id', [

		'u_user' => $user,
		'u_pass' => $hashedPass,
	], [
		'u_id' => $id
	]);

	return $data;
}
function makesession($user) {
	global $mikbotamdata;
	$data = $mikbotamdata->get('mikhbotam_id', [
		'u_id',
		'u_user',
		'u_pass',
	], [
		'u_user' => $user
	]);

	if ($data && isset($data['u_id']) && !empty($data['u_id'])) {
		return $data['u_id'];
	}

	$app_user = $mikbotamdata->get('app_users', ['id'], [
		'OR' => [
			'username' => $user,
			'email' => $user
		]
	]);

	if ($app_user && isset($app_user['id'])) {
		return strval($app_user['id']);
	}

	$default_id = $mikbotamdata->get('mikhbotam_id', 'u_id');
	return $default_id ? $default_id : '12102';
}
function updatesession($user, $pass,$id) {
	global $mikbotamdata;
	$hashedPass = password_hash($pass, PASSWORD_BCRYPT);
	$data = $mikbotamdata->update('mikhbotam_id', [

		'u_user' => $user,
		'u_pass' => $hashedPass
	],[
		'u_id' => $id,
	]);


	return $data;
}
function seeusersession($id) {
	global $mikbotamdata;
	$data = $mikbotamdata->get('mikhbotam_id', [
		'u_user',
	], [
		'u_id' => $id

	]);


	return $data['u_user'];
}

function seepasssession($id) {
	global $mikbotamdata;
	$data = $mikbotamdata->get('mikhbotam_id', [
		'u_pass',
	], [
		'u_id' => $id

	]);


	return $data['u_pass'];
}

function getid($id) {

	global $mikbotamdata;
	$settings = $mikbotamdata->get('st_mikbotam', [
		"_id",

	]);
	$hasil = isset($settings['_id']) ? $settings['_id'] : null;
	return $hasil;
}

function getsettings() {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();

	if ($tenant_id) {
		$u = get_app_user_by_id($tenant_id);
		if ($u) {
			$st = $mikbotamdata->get('st_mikbotam', '*', ['app_user_id' => $tenant_id]);
			if (!$st) {
				$st = [
					"_id" => 1,
					"Token_bot" => isset($u['bot_token']) ? $u['bot_token'] : '',
					"Username_bot" => '',
					"Nama_router" => !empty($u['full_name']) ? $u['full_name'] : $u['username'],
					"IP_router" => isset($u['mikrotik_ip']) ? $u['mikrotik_ip'] : '',
					"Username_router" => isset($u['mikrotik_username']) ? $u['mikrotik_username'] : '',
					"Pass_router" => isset($u['mikrotik_password']) ? encrypturl($u['mikrotik_password']) : '',
					"Port" => isset($u['mikrotik_port']) ? $u['mikrotik_port'] : 8728,
					"Owner" => $u['full_name'],
					"Id_owner" => isset($u['owner_telegram_id']) ? $u['owner_telegram_id'] : '',
					"dnsname" => '',
					"Voucher_1" => '',
					"Voucher_nonsaldo" => '',
					"Tanggal_diubah" => date('Y-m-d')
				];
			} else {
				$st['IP_router'] = isset($u['mikrotik_ip']) ? $u['mikrotik_ip'] : (isset($st['IP_router']) ? $st['IP_router'] : '');
				$st['Username_router'] = isset($u['mikrotik_username']) ? $u['mikrotik_username'] : (isset($st['Username_router']) ? $st['Username_router'] : '');
				$st['Pass_router'] = isset($u['mikrotik_password']) ? encrypturl($u['mikrotik_password']) : (isset($st['Pass_router']) ? $st['Pass_router'] : '');
				$st['Port'] = isset($u['mikrotik_port']) ? $u['mikrotik_port'] : (isset($st['Port']) ? $st['Port'] : 8728);
				$st['Token_bot'] = isset($u['bot_token']) ? $u['bot_token'] : (isset($st['Token_bot']) ? $st['Token_bot'] : '');
				$st['Id_owner'] = isset($u['owner_telegram_id']) ? $u['owner_telegram_id'] : (isset($st['Id_owner']) ? $st['Id_owner'] : '');
			}
			return $st;
		}
	}

	$settings = $mikbotamdata->get('st_mikbotam', [
		"_id",
		"Token_bot",
		"Username_bot",
		"Nama_router",
		"IP_router",
		"Username_router",
		"Pass_router",
		"Port",
		"Owner",
		"Id_owner",
		"dnsname",
		"Voucher_1",
		"Voucher_nonsaldo",
		"Tanggal_diubah"

	]);

	return is_array($settings) ? $settings : [];
}
function upvoc($sendfungsi, $id) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = ["_id" => $id];
	if ($tenant_id) {
		$check = $mikbotamdata->get('st_mikbotam', '_id', ['app_user_id' => $tenant_id]);
		if ($check) {
			$where = ["_id" => $check];
		}
	}
	$settings = $mikbotamdata->update('st_mikbotam', [

		"Voucher_1" => $sendfungsi,
		"Tanggal_diubah" => date('Y-m-d'),

	], $where);

	return $settings;
}
function upvocnon($sendfungsi, $id) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = ["_id" => $id];
	if ($tenant_id) {
		$check = $mikbotamdata->get('st_mikbotam', '_id', ['app_user_id' => $tenant_id]);
		if ($check) {
			$where = ["_id" => $check];
		}
	}
	$settings = $mikbotamdata->update('st_mikbotam', [

		"Voucher_nonsaldo" => $sendfungsi,
		"Tanggal_diubah" => date('Y-m-d'),

	], $where);

	return $settings;
}
function getvocnon($id) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	if ($tenant_id) {
		$check = $mikbotamdata->get('st_mikbotam', 'Voucher_nonsaldo', ['app_user_id' => $tenant_id]);
		if ($check !== null && $check !== false) {
			return $check;
		}
	}
	$settings = $mikbotamdata->get('st_mikbotam', [

		"Voucher_nonsaldo"
	],
		[
			"_id" => $id,
		]);
	$hasil = isset($settings["Voucher_nonsaldo"]) ? $settings["Voucher_nonsaldo"] : '';
	return $hasil;
}

function getvoc($id) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	if ($tenant_id) {
		$check = $mikbotamdata->get('st_mikbotam', 'Voucher_1', ['app_user_id' => $tenant_id]);
		if ($check !== null && $check !== false) {
			return $check;
		}
	}
	$settings = $mikbotamdata->get('st_mikbotam', [

		"Voucher_1"
	],
		[
			"_id" => $id,
		]);
	$hasil = isset($settings["Voucher_1"]) ? $settings["Voucher_1"] : '';
	return $hasil;
}
function upbot($id, $token, $usernamebot, $namarouter, $ipmik, $usernamemik, $passmik, $port, $dns, $owner, $idowner) {
	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();

	if ($tenant_id) {
		save_app_user([
			'id' => $tenant_id,
			'mikrotik_ip' => $ipmik,
			'mikrotik_username' => $usernamemik,
			'mikrotik_password' => decrypturl($passmik),
			'mikrotik_port' => intval($port),
			'bot_token' => $token,
			'owner_telegram_id' => $idowner,
			'full_name' => $owner
		]);

		$check = $mikbotamdata->get('st_mikbotam', '_id', ['app_user_id' => $tenant_id]);
		if ($check) {
			return $mikbotamdata->update('st_mikbotam', [
				"Token_bot" => $token,
				"Username_bot" => $usernamebot,
				"Nama_router" => $namarouter,
				"IP_router" => $ipmik,
				"Username_router" => $usernamemik,
				"Pass_router" => $passmik,
				"Port" => $port,
				"dnsname" => $dns,
				"Owner" => $owner,
				"Id_owner" => $idowner,
				"Tanggal_diubah" => date('Y-m-d'),
			], ["_id" => $check]);
		} else {
			return $mikbotamdata->insert('st_mikbotam', [
				"_id" => $tenant_id,
				"Token_bot" => $token,
				"Username_bot" => $usernamebot,
				"Nama_router" => $namarouter,
				"IP_router" => $ipmik,
				"Username_router" => $usernamemik,
				"Pass_router" => $passmik,
				"Port" => $port,
				"dnsname" => $dns,
				"Owner" => $owner,
				"Id_owner" => $idowner,
				"app_user_id" => $tenant_id,
				"Tanggal_diubah" => date('Y-m-d'),
			]);
		}
	}

	$settings = $mikbotamdata->update('st_mikbotam', [
		"Token_bot" => $token,
		"Username_bot" => $usernamebot,
		"Nama_router" => $namarouter,
		"IP_router" => $ipmik,
		"Username_router" => $usernamemik,
		"Pass_router" => $passmik,
		"Port" => $port,
		"dnsname" => $dns,
		"Owner" => $owner,
		"Id_owner" => $idowner,
		"Tanggal_diubah" => date('Y-m-d'),
	], ["_id" => $id]);

	return $settings;
}
function inbot($id, $token, $usernamebot, $namarouter, $ipmik, $usernamemik, $passmik, $port, $dns, $owner, $idowner) {
	global $mikbotamdata;
	$settings = $mikbotamdata->insert('st_mikbotam', [
		"_id" => $id,
		"Token_bot" => $token,
		"Username_bot" => $usernamebot,
		"Nama_router" => $namarouter,
		"Username_router" => $usernamemik,
		"IP_router" => $ipmik,
		"Port" => $port,
		"Pass_router" => $passmik,
		"dnsname" => $dns,
		"Owner" => $owner,
		"Id_owner" => $idowner,
		"Tanggal_diubah" => date('Y-m-d'),

	]);

	return $settings;
}
function sendMessage($id, $text, $token) {
	$website = "https://api.telegram.org/bot" . $token;
	$params = [
		'chat_id' => $id,
		'text' => $text,
		'parse_mode' => 'html',
	];
	$ch = curl_init($website . '/sendMessage');
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
function sendPhoto($id, $image, $caption, $token) {
	$website = "https://api.telegram.org/bot" . $token;
	$post_fields = [
		'photo' => $image,
		'chat_id' => $id,
		'caption' => $caption,
		'parse_mode' => 'html',

	];
	$ch = curl_init($website . '/sendPhoto');
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($post_fields));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
function tokenGenerate() {
	$chars = "1234567890";
	$i = 1;
	$token = "";
	$maxLen = strlen($chars) - 1;
	while ($i <= 8) {
		$token .= $chars[mt_rand(0, $maxLen)];
		$i++;
	}
	return $token;
}
function sendreset($username) {
	global $mikbotamdata;
	$idowner = $mikbotamdata->get('st_mikbotam', [
		"Id_owner",
	]);
	$idreal = $idowner['Id_owner'];
	$token = $mikbotamdata->get('st_mikbotam', [
		"Token_bot",
	]);
	$tokenreal = $token['Token_bot'];
	$getoken = tokenGenerate();

	$text = "<code>Mikbotam Password Reset</code>\n";
	$text .= "<code>========================</code>\n";
	$text .= "<code>Username :</code> $username\n";
	$text .= "<code>PIN      : $getoken </code>\n";
	$text .= "<code>========================</code>\n";
	$text .= "<code>Jika anda tidak merasa Melakukan tindakan ini\nSilahkan amankan Mikbotam secepatnya</code>\n";
	$update = $mikbotamdata->update('mikhbotam_id', [

		'token' => $getoken,
	]);

	$send = sendMessage($idreal, $text, $tokenreal);

	return $send;
}

function resetdone($password) {
	global $mikbotamdata;
	$idowner = $mikbotamdata->get('st_mikbotam', [
		"Id_owner",
	]);
	$idreal = $idowner['Id_owner'];
	$token = $mikbotamdata->get('st_mikbotam', [
		"Token_bot",
	]);
	$tokenreal = $token['Token_bot'];
	$hashedPass = password_hash($password, PASSWORD_BCRYPT);
	$update = $mikbotamdata->update('mikhbotam_id', [

		'u_pass' => $hashedPass,
		'token' => null,
	]);

	$text = "<code>Mikbotam Password Reset</code>\n";
	$text .= "<code>========================</code>\n";
	$text .= "<code>Password berhasil diperbarui</code>\n";
	$text .= "<code>     Terima kasih</code>\n";

	$send = sendMessage($idreal, $text, $tokenreal);

	return $send;
}

function st_monitoring() {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = [];
	if ($tenant_id) {
		$where['app_user_id'] = $tenant_id;
	}
	$idowner = $mikbotamdata->select('st_monitoring', [
		'id',
		'Name',
		'Host',
		'Lokasi',
	], $where);
	return is_array($idowner) ? $idowner : [];
}

function st_monitoringnew($host, $name, $lokasi) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$idowner = $mikbotamdata->insert('st_monitoring', [
		'Name' => $name,
		'Host' => $host,
		'Lokasi' => $lokasi,
		'app_user_id' => $tenant_id
	]);
}

function st_monitoringupd($id, $host, $name, $lokasi) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = ['id' => $id];
	if ($tenant_id) {
		$where = ['AND' => ['id' => $id, 'app_user_id' => $tenant_id]];
	}
	$idowner = $mikbotamdata->update('st_monitoring', [
		'Name' => $name,
		'Host' => $host,
		'Lokasi' => $lokasi,
	], $where);
}

function st_monitoringdel($id) {

	global $mikbotamdata;
	$tenant_id = get_current_tenant_id();
	$where = ['id' => $id];
	if ($tenant_id) {
		$where = ['AND' => ['id' => $id, 'app_user_id' => $tenant_id]];
	}
	$idowner = $mikbotamdata->delete('st_monitoring', $where);
}

function sikider() {
	$getdata=file_get_contents('https://download.mikbotam.net/scari.php?img');

	echo $getdata;
}

function setwebhook($urlpath,$token) {

	$url = "https://api.telegram.org/bot".$token."/setWebhook";

	$ch = curl_init($url);
	$post_data = [
		"url" => $urlpath,
	];

	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$result = curl_exec($ch);
	return $result;
}

function unssetwebhook($token) {

	$url = file_get_contents("https://api.telegram.org/bot".$token."/setWebhook");

	return $url;
}

function getWebhookInfo($token) {

	$url = file_get_contents("https://api.telegram.org/bot".$token."/getWebhookInfo");

	return $url;
}

function info() {

	$getdata=file_get_contents('https://download.mikbotam.net/scari.php?Runing');
echo  $getdata;
}
function Version() {

	$getdata=file_get_contents('https://download.mikbotam.net/scari.php?Version');
echo  $getdata;
}

//===================================================== START PPP BILLING DATABASE FUNCTIONS ====================//

function init_ppp_billing_tables() {
    global $mikbotamdata;
    if (!$mikbotamdata) return;
    try {
        $pdo = $mikbotamdata->pdo;

        // Core base tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS mikhbotam_id (
            u_id TEXT PRIMARY KEY,
            u_user TEXT,
            u_pass TEXT,
            lastlogin TEXT,
            ip TEXT,
            user TEXT,
            status TEXT
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS st_mikbotam (
            _id INTEGER PRIMARY KEY AUTOINCREMENT,
            Nama_router TEXT,
            IP_router TEXT,
            Username_router TEXT,
            Pass_router TEXT,
            Port TEXT DEFAULT '8728',
            dnsname TEXT,
            Owner TEXT,
            Id_owner TEXT,
            Token_bot TEXT,
            Username_bot TEXT,
            Voucher_1 TEXT,
            Voucher_nonsaldo TEXT,
            Tanggal_diubah TEXT,
            app_user_id INTEGER
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS voc_mikbotam (
            _id INTEGER PRIMARY KEY AUTOINCREMENT,
            Voucher TEXT,
            u_id TEXT,
            app_user_id INTEGER
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS re_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_user TEXT,
            nama_seller TEXT,
            nomer_tlp TEXT,
            saldo INTEGER DEFAULT 0,
            Waktu TEXT,
            Tanggal TEXT,
            app_user_id INTEGER
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS re_operating (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_user TEXT,
            nama_seller TEXT,
            saldo_awal INTEGER DEFAULT 0,
            saldo_akhir INTEGER DEFAULT 0,
            top_up INTEGER DEFAULT 0,
            top_up_fromid TEXT,
            keterangan TEXT,
            Waktu TEXT,
            Tanggal TEXT,
            operat TEXT,
            app_user_id INTEGER
        )");
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN nama_seller TEXT"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN saldo_awal INTEGER DEFAULT 0"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN saldo_akhir INTEGER DEFAULT 0"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN top_up INTEGER DEFAULT 0"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN top_up_fromid TEXT"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN keterangan TEXT"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN Waktu TEXT"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN Tanggal TEXT"); } catch (Exception $ex) {}

        $pdo->exec("CREATE TABLE IF NOT EXISTS st_reportdata (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_user TEXT,
            username_voucher TEXT,
            password_voucher TEXT,
            profile TEXT,
            price INTEGER DEFAULT 0,
            date TEXT,
            time TEXT,
            app_user_id INTEGER
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS st_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_user TEXT,
            nominal INTEGER DEFAULT 0,
            type TEXT,
            date TEXT,
            time TEXT,
            app_user_id INTEGER
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS st_monitoring (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            status TEXT,
            app_user_id INTEGER
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ppp_packages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            profile_name TEXT NOT NULL,
            price INTEGER NOT NULL DEFAULT 0,
            description TEXT,
            app_user_id INTEGER
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS ppp_customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username_ppp TEXT NOT NULL,
            customer_name TEXT,
            phone_number TEXT,
            address TEXT,
            due_date INTEGER DEFAULT 20,
            exp_date TEXT,
            telegram_id TEXT,
            app_user_id INTEGER
        )");

        // Migrate legacy ppp_packages table to remove global UNIQUE constraint
        try {
            $index_info = $pdo->query("PRAGMA index_list(ppp_packages)")->fetchAll(PDO::FETCH_ASSOC);
            $has_unique = false;
            foreach ($index_info as $idx) {
                if (isset($idx['unique']) && $idx['unique'] == 1) {
                    $has_unique = true;
                    break;
                }
            }
            if ($has_unique) {
                $cols = $pdo->query("PRAGMA table_info(ppp_packages)")->fetchAll(PDO::FETCH_ASSOC);
                $col_names = array_column($cols, 'name');
                $pdo->exec("CREATE TABLE ppp_packages_temp (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    profile_name TEXT NOT NULL,
                    price INTEGER NOT NULL DEFAULT 0,
                    description TEXT,
                    app_user_id INTEGER
                )");
                if (in_array('app_user_id', $col_names)) {
                    $pdo->exec("INSERT INTO ppp_packages_temp (id, profile_name, price, description, app_user_id) SELECT id, profile_name, price, description, app_user_id FROM ppp_packages");
                } else {
                    $pdo->exec("INSERT INTO ppp_packages_temp (id, profile_name, price, description, app_user_id) SELECT id, profile_name, price, description, 1 FROM ppp_packages");
                }
                $pdo->exec("DROP TABLE ppp_packages");
                $pdo->exec("ALTER TABLE ppp_packages_temp RENAME TO ppp_packages");
            }
        } catch (Exception $ex) {}
        try {
            $pdo->exec("ALTER TABLE ppp_customers ADD COLUMN exp_date TEXT");
        } catch (Exception $e) {
            // Column already exists
        }
        $pdo->exec("CREATE TABLE IF NOT EXISTS ppp_invoices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            invoice_number TEXT NOT NULL UNIQUE,
            username_ppp TEXT NOT NULL,
            month_year TEXT NOT NULL,
            amount INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'UNPAID',
            payment_date DATETIME,
            payment_method TEXT,
            notes TEXT
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS ppp_isolir_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT,
            setting_value TEXT,
            app_user_id INTEGER
        )");

        // Migrate legacy ppp_isolir_settings table to remove global UNIQUE constraint on setting_key
        try {
            $index_info = $pdo->query("PRAGMA index_list(ppp_isolir_settings)")->fetchAll(PDO::FETCH_ASSOC);
            $has_unique = false;
            foreach ($index_info as $idx) {
                if (isset($idx['unique']) && $idx['unique'] == 1) {
                    $has_unique = true;
                    break;
                }
            }
            if ($has_unique) {
                $cols = $pdo->query("PRAGMA table_info(ppp_isolir_settings)")->fetchAll(PDO::FETCH_ASSOC);
                $col_names = array_column($cols, 'name');
                $pdo->exec("CREATE TABLE ppp_isolir_settings_temp (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT,
                    setting_value TEXT,
                    app_user_id INTEGER
                )");
                if (in_array('app_user_id', $col_names)) {
                    $pdo->exec("INSERT INTO ppp_isolir_settings_temp (id, setting_key, setting_value, app_user_id) SELECT id, setting_key, setting_value, app_user_id FROM ppp_isolir_settings");
                } else {
                    $pdo->exec("INSERT INTO ppp_isolir_settings_temp (id, setting_key, setting_value, app_user_id) SELECT id, setting_key, setting_value, 1 FROM ppp_isolir_settings");
                }
                $pdo->exec("DROP TABLE ppp_isolir_settings");
                $pdo->exec("ALTER TABLE ppp_isolir_settings_temp RENAME TO ppp_isolir_settings");
            }
        } catch (Exception $ex) {}

        $pdo->exec("CREATE TABLE IF NOT EXISTS app_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            full_name TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            status TEXT NOT NULL DEFAULT 'active',
            mikrotik_ip TEXT,
            mikrotik_username TEXT,
            mikrotik_password TEXT,
            mikrotik_port INTEGER DEFAULT 8728,
            bot_token TEXT,
            owner_telegram_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        try { $pdo->exec("ALTER TABLE app_users ADD COLUMN email TEXT"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE app_users ADD COLUMN verification_token TEXT"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE app_users ADD COLUMN token_expires_at DATETIME"); } catch (Exception $ex) {}

        $pdo->exec("CREATE TABLE IF NOT EXISTS app_smtp_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            smtp_host TEXT,
            smtp_port INTEGER DEFAULT 587,
            smtp_user TEXT,
            smtp_pass TEXT,
            smtp_crypto TEXT DEFAULT 'tls',
            from_email TEXT,
            from_name TEXT DEFAULT 'Mikbotam Admin',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS app_payment_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gateway_name TEXT NOT NULL DEFAULT 'klikqris',
            api_key TEXT,
            merchant_id TEXT,
            mode TEXT NOT NULL DEFAULT 'sandbox',
            sandbox_url TEXT NOT NULL DEFAULT 'https://klikqris.com/api/sandbox',
            production_url TEXT NOT NULL DEFAULT 'https://klikqris.com/api',
            is_active INTEGER NOT NULL DEFAULT 1,
            app_user_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS app_qris_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id TEXT NOT NULL UNIQUE,
            merchant_id TEXT,
            telegram_id TEXT,
            telegram_username TEXT,
            app_user_id INTEGER,
            amount INTEGER NOT NULL,
            amount_uniq INTEGER DEFAULT 0,
            total_amount INTEGER NOT NULL,
            qris_url TEXT,
            status TEXT NOT NULL DEFAULT 'PENDING',
            keterangan TEXT,
            expired_at DATETIME,
            paid_at DATETIME,
            signature TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");
        try { $pdo->exec("ALTER TABLE re_settings ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE re_operating ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE st_reportdata ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE ppp_packages ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE ppp_customers ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE ppp_invoices ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE ppp_isolir_settings ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE st_mikbotam ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try { $pdo->exec("ALTER TABLE st_monitoring ADD COLUMN app_user_id INTEGER"); } catch (Exception $ex) {}
        try {
            $pdo->exec("UPDATE re_settings SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE re_operating SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE st_reportdata SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE ppp_packages SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE ppp_customers SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE ppp_invoices SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE ppp_isolir_settings SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE st_mikbotam SET app_user_id = 1 WHERE app_user_id IS NULL");
            $pdo->exec("UPDATE st_monitoring SET app_user_id = 1 WHERE app_user_id IS NULL");
        } catch (Exception $ex) {}

        // Seed default legacy mikhbotam_id if empty
        $count_mikh = $mikbotamdata->count('mikhbotam_id');
        if ($count_mikh == 0) {
            $mikbotamdata->insert('mikhbotam_id', [
                'u_id' => '12102',
                'u_user' => 'admin',
                'u_pass' => password_hash('admin', PASSWORD_BCRYPT),
                'lastlogin' => date('Y-m-d'),
                'ip' => '127.0.0.1',
                'user' => 'admin',
                'status' => 'Success'
            ]);
        }

        // Seed default st_mikbotam if empty
        $count_st = $mikbotamdata->count('st_mikbotam');
        if ($count_st == 0) {
            $mikbotamdata->insert('st_mikbotam', [
                '_id' => 1,
                'Nama_router' => 'MikroTik',
                'IP_router' => '',
                'Username_router' => 'admin',
                'Pass_router' => '',
                'Port' => '8728',
                'dnsname' => '',
                'Owner' => 'Administrator',
                'Id_owner' => '',
                'Token_bot' => '',
                'Username_bot' => '',
                'Voucher_1' => '[]',
                'Voucher_nonsaldo' => '[]',
                'Tanggal_diubah' => date('Y-m-d'),
                'app_user_id' => 1
            ]);
        }

        // Seed default voc_mikbotam if empty
        $count_voc = $mikbotamdata->count('voc_mikbotam');
        if ($count_voc == 0) {
            $mikbotamdata->insert('voc_mikbotam', [
                '_id' => 1,
                'u_id' => '12102',
                'Voucher' => '[]',
                'app_user_id' => 1
            ]);
        }

        // Auto-migrate existing admin as Superadmin if app_users is empty
        $count = $mikbotamdata->count('app_users');
        if ($count == 0) {
            $mikh = $mikbotamdata->get('mikhbotam_id', ['u_user', 'u_pass']);
            $st   = $mikbotamdata->get('st_mikbotam', '*');

            $admin_user = ($mikh && !empty($mikh['u_user'])) ? $mikh['u_user'] : 'admin';
            $admin_pass = ($mikh && !empty($mikh['u_pass'])) ? $mikh['u_pass'] : password_hash('admin', PASSWORD_BCRYPT);
            $mk_ip   = ($st && !empty($st['IP_router'])) ? $st['IP_router'] : '';
            $mk_user = ($st && !empty($st['Username_router'])) ? $st['Username_router'] : '';
            $mk_pass = ($st && !empty($st['Pass_router'])) ? decrypturl($st['Pass_router']) : '';
            $mk_port = ($st && !empty($st['Port'])) ? intval($st['Port']) : 8728;
            $bot_tok = ($st && !empty($st['Token_bot'])) ? $st['Token_bot'] : '';
            $own_id  = ($st && !empty($st['Id_owner'])) ? $st['Id_owner'] : '';

            $mikbotamdata->insert('app_users', [
                'username' => $admin_user,
                'password' => $admin_pass,
                'full_name' => 'Super Admin',
                'role' => 'superadmin',
                'status' => 'active',
                'email' => 'admin@mikbotam.local',
                'mikrotik_ip' => $mk_ip,
                'mikrotik_username' => $mk_user,
                'mikrotik_password' => $mk_pass,
                'mikrotik_port' => $mk_port,
                'bot_token' => $bot_tok,
                'owner_telegram_id' => $own_id
            ]);
        }
        // Encrypt existing plain text credentials in app_users
        try {
            $existing_users = $mikbotamdata->select('app_users', ['id', 'mikrotik_password', 'bot_token']);
            if (is_array($existing_users)) {
                foreach ($existing_users as $usr) {
                    $upd = [];
                    if (!empty($usr['mikrotik_password']) && strpos($usr['mikrotik_password'], 'enc:v2:') !== 0) {
                        $upd['mikrotik_password'] = encrypturl($usr['mikrotik_password']);
                    }
                    if (!empty($usr['bot_token']) && strpos($usr['bot_token'], 'enc:v2:') !== 0) {
                        $upd['bot_token'] = encrypturl($usr['bot_token']);
                    }
                    if (!empty($upd)) {
                        $mikbotamdata->update('app_users', $upd, ['id' => $usr['id']]);
                    }
                }
            }
        } catch (Exception $ex) {}
    } catch (Exception $e) {
        // Table creation fallback
    }
}

function decrypt_app_user_fields($user) {
    if (!is_array($user)) return $user;
    if (isset($user['mikrotik_password']) && !empty($user['mikrotik_password'])) {
        $user['mikrotik_password'] = decrypturl($user['mikrotik_password']);
    }
    if (isset($user['bot_token']) && !empty($user['bot_token'])) {
        $user['bot_token'] = decrypturl($user['bot_token']);
    }
    return $user;
}

function get_all_app_users() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $users = $mikbotamdata->select('app_users', '*');
    if (is_array($users)) {
        foreach ($users as &$u) {
            $u = decrypt_app_user_fields($u);
        }
    }
    return is_array($users) ? $users : [];
}

function get_app_user_by_id($id) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $rows = $mikbotamdata->select('app_users', '*', ['id' => intval($id)]);
    $user = (is_array($rows) && isset($rows[0])) ? $rows[0] : null;
    return decrypt_app_user_fields($user);
}

function get_app_user_by_username($username) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $rows = $mikbotamdata->select('app_users', '*', ['username' => $username]);
    $user = (is_array($rows) && isset($rows[0])) ? $rows[0] : null;
    return decrypt_app_user_fields($user);
}

function save_app_user($data) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    
    if (isset($data['mikrotik_password']) && !empty($data['mikrotik_password'])) {
        if (strpos($data['mikrotik_password'], 'enc:v2:') !== 0) {
            $data['mikrotik_password'] = encrypturl($data['mikrotik_password']);
        }
    }
    if (isset($data['bot_token']) && !empty($data['bot_token'])) {
        if (strpos($data['bot_token'], 'enc:v2:') !== 0) {
            $data['bot_token'] = encrypturl($data['bot_token']);
        }
    }

    if (isset($data['id']) && intval($data['id']) > 0) {
        $id = intval($data['id']);
        unset($data['id']);
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password']) && strpos($data['password'], '$2y$') !== 0) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return $mikbotamdata->update('app_users', $data, ['id' => $id]);
    } else {
        if (isset($data['password']) && strpos($data['password'], '$2y$') !== 0) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return $mikbotamdata->insert('app_users', $data);
    }
}

function delete_app_user($id) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    return $mikbotamdata->delete('app_users', ['id' => intval($id)]);
}

// Auto-run table initialization
init_ppp_billing_tables();

function get_ppp_packages() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    $where = [];
    if ($tenant_id) {
        $where['app_user_id'] = $tenant_id;
    }
    $data = $mikbotamdata->select('ppp_packages', ['id', 'profile_name', 'price', 'description'], $where);
    return is_array($data) ? $data : [];
}

function save_ppp_package($profile_name, $price, $description = '') {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    $where_check = ['profile_name' => $profile_name];
    if ($tenant_id) {
        $where_check = ['AND' => ['profile_name' => $profile_name, 'app_user_id' => $tenant_id]];
    }
    $check = $mikbotamdata->get('ppp_packages', 'id', $where_check);
    if ($check) {
        return $mikbotamdata->update('ppp_packages', [
            'price' => intval($price),
            'description' => $description
        ], ['id' => $check]);
    } else {
        return $mikbotamdata->insert('ppp_packages', [
            'profile_name' => $profile_name,
            'price' => intval($price),
            'description' => $description,
            'app_user_id' => $tenant_id
        ]);
    }
}

function delete_ppp_package($id) {
    global $mikbotamdata;
    $tenant_id = get_current_tenant_id();
    $where = ['id' => intval($id)];
    if ($tenant_id) {
        $where = ['AND' => ['id' => intval($id), 'app_user_id' => $tenant_id]];
    }
    return $mikbotamdata->delete('ppp_packages', $where);
}

function get_ppp_isolir_settings() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    $where = [];
    if ($tenant_id) {
        $where['app_user_id'] = $tenant_id;
    }
    $rows = $mikbotamdata->select('ppp_isolir_settings', ['setting_key', 'setting_value'], $where);
    $settings = [
        'due_date' => 20,
        'isolir_mode' => 'disable', // 'disable' or 'profile'
        'isolir_profile' => 'ISOLIR_PPPOE',
        'reminder_days' => 3
    ];
    if (is_array($rows)) {
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function save_ppp_isolir_settings($data) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    foreach ($data as $key => $val) {
        $where_check = ['setting_key' => $key];
        if ($tenant_id) {
            $where_check = ['AND' => ['setting_key' => $key, 'app_user_id' => $tenant_id]];
        }
        $check = $mikbotamdata->get('ppp_isolir_settings', 'id', $where_check);
        if ($check) {
            $mikbotamdata->update('ppp_isolir_settings', ['setting_value' => $val], ['id' => $check]);
        } else {
            $mikbotamdata->insert('ppp_isolir_settings', ['setting_key' => $key, 'setting_value' => $val, 'app_user_id' => $tenant_id]);
        }
    }
    return true;
}

function get_ppp_invoices($month_year = null, $status = null, $search = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    $and = [];
    if ($tenant_id) {
        $and['app_user_id'] = $tenant_id;
    }
    if (!empty($month_year)) {
        $and['month_year'] = $month_year;
    }
    if (!empty($status)) {
        $and['status'] = $status;
    }
    if (!empty($search)) {
        $and['username_ppp[~]'] = $search;
    }
    
    $where = [];
    if (!empty($and)) {
        $where['AND'] = $and;
    }
    $where['ORDER'] = ['id' => 'DESC'];
    $data = $mikbotamdata->select('ppp_invoices', [
        'id', 'invoice_number', 'username_ppp', 'month_year', 'amount', 'status', 'payment_date', 'payment_method', 'notes'
    ], $where);
    return is_array($data) ? $data : [];
}

function pay_ppp_invoice($id, $method = 'CASH', $notes = '', $months = 1) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    date_default_timezone_set('Asia/Jakarta');
    $tenant_id = get_current_tenant_id();

    $months = intval($months) > 0 ? intval($months) : 1;

    $where_inv = ['id' => intval($id)];
    if ($tenant_id) {
        $where_inv = ['AND' => ['id' => intval($id), 'app_user_id' => $tenant_id]];
    }

    $inv = $mikbotamdata->get('ppp_invoices', ['username_ppp', 'month_year', 'amount'], $where_inv);
    if ($inv) {
        $user_ppp = $inv['username_ppp'];
        $settings = get_ppp_isolir_settings();
        $default_day = intval($settings['due_date']);

        $where_cust = ['username_ppp' => $user_ppp];
        if ($tenant_id) {
            $where_cust = ['AND' => ['username_ppp' => $user_ppp, 'app_user_id' => $tenant_id]];
        }
        $cust = $mikbotamdata->get('ppp_customers', ['id', 'exp_date', 'due_date'], $where_cust);
        $day = ($cust && !empty($cust['due_date'])) ? intval($cust['due_date']) : $default_day;

        if ($cust && !empty($cust['exp_date'])) {
            $cur_exp = $cust['exp_date'];
            $next_exp = date('Y-m-d', strtotime("+$months month", strtotime($cur_exp)));
        } else {
            $next_month = date('Y-m', strtotime("+$months month"));
            $next_exp = $next_month . '-' . sprintf('%02d', $day);
        }

        if ($cust) {
            $mikbotamdata->update('ppp_customers', ['exp_date' => $next_exp], ['id' => $cust['id']]);
        } else {
            $mikbotamdata->insert('ppp_customers', [
                'username_ppp' => $user_ppp,
                'due_date' => $day,
                'exp_date' => $next_exp,
                'app_user_id' => $tenant_id
            ]);
        }

        $paid_notes = $notes . ($months > 1 ? " ($months Bulan)" : "");
        $total_amount = intval($inv['amount']) * $months;

        $mikbotamdata->update('ppp_invoices', [
            'status' => 'PAID',
            'amount' => $total_amount,
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_method' => $method,
            'notes' => $paid_notes
        ], $where_inv);

        // Mark any future invoices for this customer within the paid period as PAID as well
        if ($months > 1) {
            for ($m = 1; $m < $months; $m++) {
                $future_m = date('Y-m', strtotime("+$m month", strtotime($inv['month_year'] . '-01')));
                $mikbotamdata->update('ppp_invoices', [
                    'status' => 'PAID',
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_method' => $method,
                    'notes' => 'Dibayar di awal via invoice #' . $id
                ], [
                    'AND' => [
                        'username_ppp' => $user_ppp,
                        'month_year' => $future_m
                    ]
                ]);
            }
        }

        return isset($next_exp) ? $next_exp : null;
    }
    return null;
}

function get_or_create_next_unpaid_invoice($user) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();

    $unpaid_invoices = get_ppp_invoices(null, 'UNPAID', $user);
    if (!empty($unpaid_invoices)) {
        return $unpaid_invoices[0];
    }

    $last_invoices = get_ppp_invoices(null, null, $user);
    $amount = (!empty($last_invoices) && isset($last_invoices[0]['amount']) && intval($last_invoices[0]['amount']) > 0) ? intval($last_invoices[0]['amount']) : 150000;

    if (!empty($last_invoices) && isset($last_invoices[0]['month_year'])) {
        $last_m = $last_invoices[0]['month_year'];
        $next_m = date('Y-m', strtotime('+1 month', strtotime($last_m . '-01')));
    } else {
        $next_m = date('Y-m');
    }

    $existing = get_ppp_invoices($next_m, null, $user);
    if (!empty($existing)) {
        return $existing[0];
    }

    $inv_num = 'INV-' . str_replace('-', '', $next_m) . '-' . sprintf('%04d', rand(100, 9999));
    $mikbotamdata->insert('ppp_invoices', [
        'invoice_number' => $inv_num,
        'username_ppp' => $user,
        'month_year' => $next_m,
        'amount' => $amount,
        'status' => 'UNPAID',
        'notes' => 'Tagihan lanjutan via bot',
        'app_user_id' => $tenant_id
    ]);

    $new_id = $mikbotamdata->get('ppp_invoices', 'id', ['invoice_number' => $inv_num]);
    return [
        'id' => $new_id,
        'invoice_number' => $inv_num,
        'username_ppp' => $user,
        'month_year' => $next_m,
        'amount' => $amount,
        'status' => 'UNPAID',
        'notes' => 'Tagihan lanjutan via bot'
    ];
}

function update_ppp_invoice_status($id, $status) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    $where = ['id' => intval($id)];
    if ($tenant_id) {
        $where = ['AND' => ['id' => intval($id), 'app_user_id' => $tenant_id]];
    }
    return $mikbotamdata->update('ppp_invoices', [
        'status' => $status
    ], $where);
}

function generate_monthly_invoices($month_year, $secrets_list) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    $packages = get_ppp_packages();
    $pkg_map = [];
    foreach ($packages as $pkg) {
        $pkg_map[$pkg['profile_name']] = intval($pkg['price']);
    }

    $count = 0;
    foreach ($secrets_list as $secret) {
        $user = isset($secret['name']) ? $secret['name'] : '';
        $profile = isset($secret['profile']) ? $secret['profile'] : '';
        if (empty($user)) continue;

        $price = isset($pkg_map[$profile]) ? $pkg_map[$profile] : 0;
        if ($price <= 0) continue;

        $where_and = [
            'username_ppp' => $user,
            'month_year' => $month_year
        ];
        if ($tenant_id) {
            $where_and['app_user_id'] = $tenant_id;
        }

        $exists = $mikbotamdata->get('ppp_invoices', 'id', [
            'AND' => $where_and
        ]);

        if (!$exists) {
            // Check if customer has already paid advance (exp_date >= target month_year)
            $status = 'UNPAID';
            $notes = null;
            $cust = $mikbotamdata->get('ppp_customers', '*', ['username_ppp' => $user]);

            if ($cust && !empty($cust['exp_date'])) {
                $exp_month_year = date('Y-m', strtotime($cust['exp_date']));
                if ($exp_month_year > $month_year) {
                    $status = 'PAID';
                    $notes  = 'Lunas (Bayar Dimuka)';
                }
            }

            $inv_no = 'INV-' . str_replace('-', '', $month_year) . '-' . sprintf('%04d', rand(1, 9999));
            $mikbotamdata->insert('ppp_invoices', [
                'invoice_number' => $inv_no,
                'username_ppp' => $user,
                'month_year' => $month_year,
                'amount' => $price,
                'status' => $status,
                'notes' => $notes,
                'app_user_id' => $tenant_id
            ]);

            // Auto-create customer record with standard isolir due date if not present
            if (!$cust) {
                $isolir_set = get_ppp_isolir_settings();
                $def_day = intval($isolir_set['due_date']);
                $init_exp = $month_year . '-' . sprintf('%02d', $def_day);
                $mikbotamdata->insert('ppp_customers', [
                    'username_ppp' => $user,
                    'due_date' => $def_day,
                    'exp_date' => $init_exp,
                    'app_user_id' => $tenant_id
                ]);
            }

            $count++;
        }
    }
    return $count;
}

function get_smtp_settings() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $row = $mikbotamdata->get('app_smtp_settings', '*', ['id' => 1]);
    if ($row) {
        if (!empty($row['smtp_pass'])) {
            $row['smtp_pass'] = decrypturl($row['smtp_pass']);
        }
        return $row;
    }
    return [
        'id' => 1,
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_crypto' => 'tls',
        'from_email' => '',
        'from_name' => 'Mikbotam Admin'
    ];
}

function save_smtp_settings($data) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $pass = isset($data['smtp_pass']) ? $data['smtp_pass'] : '';
    $enc_pass = !empty($pass) ? encrypturl($pass) : '';

    $insert_data = [
        'smtp_host' => isset($data['smtp_host']) ? trim($data['smtp_host']) : '',
        'smtp_port' => isset($data['smtp_port']) ? intval($data['smtp_port']) : 587,
        'smtp_user' => isset($data['smtp_user']) ? trim($data['smtp_user']) : '',
        'smtp_pass' => $enc_pass,
        'smtp_crypto' => isset($data['smtp_crypto']) ? trim($data['smtp_crypto']) : 'tls',
        'from_email' => isset($data['from_email']) ? trim($data['from_email']) : '',
        'from_name' => isset($data['from_name']) ? trim($data['from_name']) : 'Mikbotam Admin',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $exists = $mikbotamdata->get('app_smtp_settings', 'id', ['id' => 1]);
    if ($exists) {
        return $mikbotamdata->update('app_smtp_settings', $insert_data, ['id' => 1]);
    } else {
        $insert_data['id'] = 1;
        return $mikbotamdata->insert('app_smtp_settings', $insert_data);
    }
}

function send_custom_smtp_email($to, $subject, $html_body) {
    $settings = get_smtp_settings();

    // If SMTP Host or User is empty, fallback to standard mail()
    if (empty($settings['smtp_host']) || empty($settings['smtp_user'])) {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $from = !empty($settings['from_email']) ? $settings['from_email'] : "no-reply@" . $host;
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $settings['from_name'] . " <" . $from . ">" . "\r\n";
        $sent = @mail($to, $subject, $html_body, $headers);
        if ($sent) {
            return ['success' => true, 'message' => "Email dikirim via PHP mail() standar."];
        } else {
            return ['success' => false, 'message' => "Gagal mengirim email via mail() standar. Silakan konfigurasi server SMTP di Pengaturan SMTP."];
        }
    }

    $host = $settings['smtp_host'];
    $port = intval($settings['smtp_port']) > 0 ? intval($settings['smtp_port']) : 587;
    $user = $settings['smtp_user'];
    $pass = $settings['smtp_pass'];
    $crypto = strtolower($settings['smtp_crypto']);
    $from_email = !empty($settings['from_email']) ? $settings['from_email'] : $user;
    $from_name  = !empty($settings['from_name']) ? $settings['from_name'] : 'Mikbotam Admin';

    $remote_host = ($crypto === 'ssl') ? 'ssl://' . $host : $host;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $socket = @stream_socket_client($remote_host . ':' . $port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        return ['success' => false, 'message' => "Koneksi SMTP ke $host:$port gagal: $errstr ($errno)"];
    }

    stream_set_timeout($socket, 10);

    $read_resp = function() use ($socket) {
        $resp = '';
        while ($line = fgets($socket, 512)) {
            $resp .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $resp;
    };

    $send_cmd = function($cmd) use ($socket, $read_resp) {
        fputs($socket, $cmd . "\r\n");
        return $read_resp();
    };

    $greeting = $read_resp();
    if (substr($greeting, 0, 3) !== '220') {
        fclose($socket);
        return ['success' => false, 'message' => "Respons server SMTP invalid: $greeting"];
    }

    $ehlo = $send_cmd("EHLO " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'));

    if ($crypto === 'tls') {
        $starttls = $send_cmd("STARTTLS");
        if (substr($starttls, 0, 3) === '220') {
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return ['success' => false, 'message' => "Gagal mengaktifkan enkripsi STARTTLS dengan server SMTP."];
            }
            $send_cmd("EHLO " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'));
        }
    }

    if (!empty($user) && !empty($pass)) {
        $auth = $send_cmd("AUTH LOGIN");
        if (substr($auth, 0, 3) === '334') {
            $user_resp = $send_cmd(base64_encode($user));
            if (substr($user_resp, 0, 3) === '334') {
                $pass_resp = $send_cmd(base64_encode($pass));
                if (substr($pass_resp, 0, 3) !== '235') {
                    fclose($socket);
                    return ['success' => false, 'message' => "Autentikasi SMTP gagal: Username atau Password SMTP salah ($pass_resp)"];
                }
            } else {
                fclose($socket);
                return ['success' => false, 'message' => "Username SMTP ditolak: $user_resp"];
            }
        }
    }

    $send_cmd("MAIL FROM:<$from_email>");
    $send_cmd("RCPT TO:<$to>");
    $send_cmd("DATA");

    $headers = "MIME-Version: 1.0\r\n"
             . "Content-Type: text/html; charset=UTF-8\r\n"
             . "From: =?UTF-8?B?" . base64_encode($from_name) . "?= <$from_email>\r\n"
             . "To: <$to>\r\n"
             . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
             . "Date: " . date('r') . "\r\n";

    $email_data = $headers . "\r\n" . $html_body . "\r\n.\r\n";
    $data_resp = $send_cmd($email_data);

    $send_cmd("QUIT");
    fclose($socket);

    if (substr($data_resp, 0, 3) === '250') {
        return ['success' => true, 'message' => "Email berhasil dikirimkan via SMTP!"];
    } else {
        return ['success' => false, 'message' => "Gagal mengirim data email: $data_resp"];
    }
}

function send_verification_email($email, $full_name, $token) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $script_dir = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
    $script_dir = str_replace(['/admin', '\\admin'], '', $script_dir);
    $base_url = rtrim($protocol . $host . $script_dir, '/');
    
    $verify_link = $base_url . '/admin/verify_email.php?token=' . urlencode($token);

    $subject = "Verifikasi Email Akun Admin Mikbotam";
    $message = '
    <html>
    <head>
      <title>Verifikasi Email Akun Admin Mikbotam</title>
      <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 550px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-top: 4px solid #008080; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #008080; color: #ffffff !important; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 25px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 15px; }
      </style>
    </head>
    <body>
      <div class="container">
        <h2 style="color: #008080; margin-top: 0;">Selamat Datang di Mikbotam!</h2>
        <p>Halo <strong>' . htmlspecialchars($full_name) . '</strong>,</p>
        <p>Terima kasih telah mendaftar akun Admin Mikbotam. Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda:</p>
        <p style="text-align: center;">
          <a href="' . $verify_link . '" class="btn">✉️ Verifikasi Email Saya Sekarang</a>
        </p>
        <p style="font-size: 13px; color: #666;">Atau salin dan tempel tautan berikut di browser Anda:<br>
          <a href="' . $verify_link . '">' . $verify_link . '</a>
        </p>
        <p style="font-size: 12px; color: #999;">Link verifikasi ini berlaku selama 24 jam.</p>
        <div class="footer">
          <p>Jika Anda tidak merasa mendaftar akun ini, silakan abaikan pesan email ini.<br>&copy; ' . date('Y') . ' Mikbotam - Mod by Andro Network</p>
        </div>
      </div>
    </body>
    </html>
    ';

    $res = send_custom_smtp_email($email, $subject, $message);
    return is_array($res) ? $res['success'] : $res;
}

function register_new_app_user($email, $full_name, $password) {
    global $mikbotamdata;
    init_ppp_billing_tables();

    $email = trim(strtolower($email));
    $full_name = trim($full_name);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Format email tidak valid.'];
    }

    if (empty($full_name)) {
        return ['success' => false, 'message' => 'Nama lengkap wajib diisi.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal harus 6 karakter.'];
    }

    $username = explode('@', $email)[0];
    $exists = $mikbotamdata->get('app_users', 'id', [
        'OR' => [
            'email' => $email,
            'username' => $username,
            'username' => $email
        ]
    ]);

    if ($exists) {
        return ['success' => false, 'message' => 'Email atau Username sudah terdaftar di sistem.'];
    }

    $token = bin2hex(random_bytes(32));
    $token_expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $insert = [
        'username' => $email,
        'email' => $email,
        'password' => $hashed_password,
        'full_name' => $full_name,
        'role' => 'admin',
        'status' => 'unverified',
        'verification_token' => $token,
        'token_expires_at' => $token_expires_at,
        'mikrotik_port' => 8728
    ];

    $mikbotamdata->insert('app_users', $insert);
    $user_id = $mikbotamdata->get('app_users', 'id', ['email' => $email]);

    if ($user_id) {
        send_verification_email($email, $full_name, $token);
        return [
            'success' => true,
            'message' => 'Registrasi berhasil! Link verifikasi telah dikirim ke email <strong>' . htmlspecialchars($email) . '</strong>. Silakan cek Inbox atau folder Spam email Anda untuk mengaktifkan akun.',
            'token' => $token
        ];
    }

    return ['success' => false, 'message' => 'Gagal mendaftarkan akun. Silakan coba lagi.'];
}

function verify_app_user_email($token) {
    global $mikbotamdata;
    init_ppp_billing_tables();

    if (empty($token)) {
        return ['success' => false, 'message' => 'Token verifikasi tidak valid.'];
    }

    $user = $mikbotamdata->get('app_users', '*', ['verification_token' => $token]);

    if (!$user) {
        return ['success' => false, 'message' => 'Token verifikasi tidak ditemukan atau sudah pernah digunakan.'];
    }

    if (!empty($user['token_expires_at']) && strtotime($user['token_expires_at']) < time()) {
        return ['success' => false, 'message' => 'Token verifikasi telah kadaluarsa (lebih dari 24 jam). Silakan kirim ulang email verifikasi.', 'email' => $user['email']];
    }

    $mikbotamdata->update('app_users', [
        'status' => 'active',
        'verification_token' => null,
        'token_expires_at' => null
    ], ['id' => $user['id']]);

    return [
        'success' => true,
        'message' => 'Email Anda (<strong>' . htmlspecialchars($user['email']) . '</strong>) berhasil diverifikasi! Akun Anda kini AKTIF. Silakan Sign In.',
        'email' => $user['email']
    ];
}

function resend_verification_email($email) {
    global $mikbotamdata;
    init_ppp_billing_tables();

    $email = trim(strtolower($email));
    $user = $mikbotamdata->get('app_users', '*', ['email' => $email]);

    if (!$user) {
        return ['success' => false, 'message' => 'Email tidak terdaftar di sistem.'];
    }

    if ($user['status'] === 'active') {
        return ['success' => false, 'message' => 'Akun ini sudah AKTIF. Silakan langsung Sign In.'];
    }

    $token = bin2hex(random_bytes(32));
    $token_expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $mikbotamdata->update('app_users', [
        'verification_token' => $token,
        'token_expires_at' => $token_expires_at
    ], ['id' => $user['id']]);

    send_verification_email($user['email'], $user['full_name'], $token);

    return [
        'success' => true,
        'message' => 'Email verifikasi baru telah dikirimkan ke <strong>' . htmlspecialchars($email) . '</strong>. Silakan cek Inbox atau folder Spam.'
    ];
}

function get_current_app_user_profile() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $tenant_id = get_current_tenant_id();
    if ($tenant_id) {
        $usr = $mikbotamdata->get('app_users', '*', ['id' => $tenant_id]);
        if ($usr) {
            if (empty($usr['email'])) {
                $usr['email'] = $usr['username'] . '@domain.com';
            }
            return $usr;
        }
    }
    $leg = $mikbotamdata->get('mikhbotam_id', '*');
    return [
        'id' => 1,
        'full_name' => 'Administrator',
        'username' => isset($leg['u_user']) ? $leg['u_user'] : 'admin',
        'email' => 'admin@domain.com',
        'role' => 'superadmin'
    ];
}

function update_app_user_profile($user_id, $full_name, $username, $email, $new_password = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();

    $user_id = intval($user_id);
    if ($user_id <= 0) return ['success' => false, 'message' => 'User ID tidak valid.'];

    $full_name = trim($full_name);
    $username  = trim($username);
    $email     = trim(strtolower($email));

    if (empty($full_name) || empty($username) || empty($email)) {
        return ['success' => false, 'message' => 'Nama lengkap, username, dan email wajib diisi.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Format email tidak valid.'];
    }

    $upd = [
        'full_name' => $full_name,
        'username'  => $username,
        'email'     => $email
    ];

    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            return ['success' => false, 'message' => 'Password minimal harus 6 karakter.'];
        }
        $upd['password'] = password_hash($new_password, PASSWORD_BCRYPT);
    }

    $exists = $mikbotamdata->get('app_users', 'id', [
        'AND' => [
            'id[!]' => $user_id,
            'OR' => [
                'username' => $username,
                'email'    => $email
            ]
        ]
    ]);

    if ($exists) {
        return ['success' => false, 'message' => 'Username atau Email sudah digunakan oleh pengguna lain.'];
    }

    $mikbotamdata->update('app_users', $upd, ['id' => $user_id]);

    if ($user_id === 1 && !empty($new_password)) {
        $mikbotamdata->update('mikhbotam_id', [
            'u_user' => $username,
            'u_pass' => password_hash($new_password, PASSWORD_BCRYPT)
        ], ['u_id' => 1]);
    }

    $_SESSION['app_full_name'] = $full_name;
    $_SESSION['Mikbotamuser']  = $username;

    return ['success' => true, 'message' => 'Profil akun berhasil diperbarui!'];
}

function get_klikqris_settings($tenant_id = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    if ($tenant_id === null) {
        $tenant_id = get_current_tenant_id();
    }
    $tenant_id = intval($tenant_id);

    $defaults = [
        'api_key'        => '',
        'merchant_id'    => '',
        'mode'           => 'sandbox',
        'sandbox_url'    => 'https://klikqris.com/api/sandbox',
        'production_url' => 'https://klikqris.com/api',
        'active_url'     => 'https://klikqris.com/api/sandbox',
        'is_active'      => 1
    ];

    if (!$mikbotamdata) return $defaults;

    $row = $mikbotamdata->get('app_payment_settings', '*', [
        'AND' => [
            'gateway_name' => 'klikqris',
            'app_user_id'  => $tenant_id
        ]
    ]);

    if (!$row && $tenant_id !== 1 && $tenant_id !== 0) {
        $row = $mikbotamdata->get('app_payment_settings', '*', [
            'AND' => [
                'gateway_name' => 'klikqris',
                'app_user_id'  => 1
            ]
        ]);
    }

    if ($row) {
        $mode = (!empty($row['mode']) && in_array($row['mode'], ['sandbox', 'production'])) ? $row['mode'] : 'sandbox';
        $sandbox_url = !empty($row['sandbox_url']) ? $row['sandbox_url'] : 'https://klikqris.com/api/sandbox';
        $production_url = !empty($row['production_url']) ? $row['production_url'] : 'https://klikqris.com/api';
        $active_url = ($mode === 'production') ? $production_url : $sandbox_url;

        return [
            'id'             => $row['id'],
            'api_key'        => !empty($row['api_key']) ? decrypturl($row['api_key']) : '',
            'merchant_id'    => isset($row['merchant_id']) ? $row['merchant_id'] : '',
            'mode'           => $mode,
            'sandbox_url'    => $sandbox_url,
            'production_url' => $production_url,
            'active_url'     => $active_url,
            'is_active'      => isset($row['is_active']) ? intval($row['is_active']) : 1
        ];
    }

    return $defaults;
}

function save_klikqris_settings($data, $tenant_id = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    if ($tenant_id === null) {
        $tenant_id = get_current_tenant_id();
    }
    $tenant_id = intval($tenant_id);

    $api_key        = isset($data['api_key']) ? trim($data['api_key']) : '';
    $merchant_id    = isset($data['merchant_id']) ? trim($data['merchant_id']) : '';
    $mode           = (isset($data['mode']) && in_array($data['mode'], ['sandbox', 'production'])) ? $data['mode'] : 'sandbox';
    $sandbox_url    = !empty($data['sandbox_url']) ? trim($data['sandbox_url']) : 'https://klikqris.com/api/sandbox';
    $production_url = !empty($data['production_url']) ? trim($data['production_url']) : 'https://klikqris.com/api';
    $is_active      = isset($data['is_active']) ? intval($data['is_active']) : 1;

    $encrypted_key = !empty($api_key) ? encrypturl($api_key) : '';

    $exists = $mikbotamdata->get('app_payment_settings', 'id', [
        'AND' => [
            'gateway_name' => 'klikqris',
            'app_user_id'  => $tenant_id
        ]
    ]);

    $payload = [
        'gateway_name'   => 'klikqris',
        'api_key'        => $encrypted_key,
        'merchant_id'    => $merchant_id,
        'mode'           => $mode,
        'sandbox_url'    => $sandbox_url,
        'production_url' => $production_url,
        'is_active'      => $is_active,
        'app_user_id'    => $tenant_id,
        'updated_at'     => date('Y-m-d H:i:s')
    ];

    if ($exists) {
        $mikbotamdata->update('app_payment_settings', $payload, ['id' => $exists]);
    } else {
        $payload['created_at'] = date('Y-m-d H:i:s');
        $mikbotamdata->insert('app_payment_settings', $payload);
    }

    return ['success' => true, 'message' => 'Pengaturan Payment Gateway KlikQRIS berhasil disimpan!'];
}

function create_klikqris_transaction($amount, $telegram_id, $telegram_username = '', $keterangan = 'Top Up Saldo', $tenant_id = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    if ($tenant_id === null) {
        $tenant_id = get_current_tenant_id();
    }
    $tenant_id = intval($tenant_id);
    $settings = get_klikqris_settings($tenant_id);

    if (empty($settings['is_active']) || empty($settings['api_key']) || empty($settings['merchant_id'])) {
        return [
            'success' => false,
            'message' => 'Layanan QRIS saat ini belum aktif atau belum dikonfigurasi oleh Administrator.'
        ];
    }

    $amount = intval($amount);
    if ($amount < 1000) {
        return [
            'success' => false,
            'message' => 'Minimal transaksi QRIS adalah Rp 1.000.'
        ];
    }

    // Generate unique order_id
    $order_id = 'DEP-' . $telegram_id . '-' . date('ymdHis') . rand(10, 99);
    $base_url = rtrim($settings['active_url'], '/');
    $endpoint = $base_url . '/qris/create';

    $payload = [
        'order_id'    => $order_id,
        'id_merchant' => $settings['merchant_id'],
        'amount'      => $amount,
        'keterangan'  => !empty($keterangan) ? $keterangan : ('Deposit Saldo #' . $telegram_id)
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $settings['api_key'],
            'id_merchant: ' . $settings['merchant_id']
        ]
    ]);

    $response = curl_exec($curl);
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curl_error) {
        return [
            'success' => false,
            'message' => 'Koneksi ke server KlikQRIS gagal: ' . $curl_error
        ];
    }

    $json = json_decode($response, true);
    if (!$json || !isset($json['status']) || $json['status'] !== true) {
        $err_msg = isset($json['message']) ? $json['message'] : 'Gagal membuat QRIS (HTTP ' . $http_code . ')';
        return [
            'success' => false,
            'message' => $err_msg,
            'raw'     => $response
        ];
    }

    $data = isset($json['data']) ? $json['data'] : [];
    $total_amount = isset($data['total_amount']) ? intval(round(floatval($data['total_amount']))) : $amount;
    $amount_uniq  = isset($data['amount_uniq']) ? intval(round(floatval($data['amount_uniq']))) : ($total_amount - $amount);
    $qris_url     = isset($data['qris_url']) ? $data['qris_url'] : '';
    $qris_image   = isset($data['qris_image']) ? $data['qris_image'] : '';
    $expired_at   = isset($data['expired_at']) ? $data['expired_at'] : date('Y-m-d H:i:s', strtotime('+60 minutes'));
    $expired_menit = isset($data['expired_menit']) ? $data['expired_menit'] : '60';
    $signature    = isset($data['signature']) ? $data['signature'] : '';

    // Save transaction record in database
    $mikbotamdata->insert('app_qris_transactions', [
        'order_id'          => $order_id,
        'merchant_id'       => $settings['merchant_id'],
        'telegram_id'       => (string)$telegram_id,
        'telegram_username' => (string)$telegram_username,
        'app_user_id'       => $tenant_id,
        'amount'            => $amount,
        'amount_uniq'       => $amount_uniq,
        'total_amount'      => $total_amount,
        'qris_url'          => $qris_url,
        'status'            => 'PENDING',
        'keterangan'        => $payload['keterangan'],
        'expired_at'        => $expired_at,
        'signature'         => $signature,
        'created_at'        => date('Y-m-d H:i:s'),
        'updated_at'        => date('Y-m-d H:i:s')
    ]);

    return [
        'success'       => true,
        'order_id'      => $order_id,
        'amount'        => $amount,
        'amount_uniq'   => $amount_uniq,
        'total_amount'  => $total_amount,
        'qris_url'      => $qris_url,
        'qris_image'    => $qris_image,
        'expired_at'    => $expired_at,
        'expired_menit' => $expired_menit,
        'data'          => $data
    ];
}

function get_qris_transaction_by_order_id($order_id) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    if (!$mikbotamdata) return null;
    return $mikbotamdata->get('app_qris_transactions', '*', ['order_id' => $order_id]);
}

function update_qris_transaction_status($order_id, $status = 'PAID', $paid_at = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    if (!$mikbotamdata) return false;
    $upd = [
        'status'     => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    if ($paid_at) {
        $upd['paid_at'] = $paid_at;
    } elseif ($status === 'PAID') {
        $upd['paid_at'] = date('Y-m-d H:i:s');
    }
    return $mikbotamdata->update('app_qris_transactions', $upd, ['order_id' => $order_id]);
}

function check_klikqris_status($order_id, $tenant_id = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    if (!$mikbotamdata) {
        return ['success' => false, 'message' => 'Database connection unavailable'];
    }

    $trx = get_qris_transaction_by_order_id($order_id);
    if (!$trx) {
        return ['success' => false, 'message' => 'Transaksi tidak ditemukan'];
    }

    if (empty($tenant_id)) {
        $tenant_id = isset($trx['app_user_id']) ? intval($trx['app_user_id']) : get_current_tenant_id();
    }

    $settings = get_klikqris_settings($tenant_id);
    if (empty($settings['api_key']) || empty($settings['merchant_id'])) {
        return [
            'success' => true,
            'status'  => $trx['status'],
            'message' => 'Status transaksi: ' . $trx['status'],
            'trx'     => $trx
        ];
    }

    $base_url = rtrim($settings['active_url'], '/');
    $endpoint = $base_url . '/qris/status/' . rawurlencode($order_id);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $settings['api_key'],
            'id_merchant: ' . $settings['merchant_id']
        ]
    ]);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error || empty($response)) {
        return [
            'success' => true,
            'status'  => $trx['status'],
            'message' => 'Status saat ini: ' . $trx['status'],
            'trx'     => $trx
        ];
    }

    $json = json_decode($response, true);
    if (!$json || !isset($json['status']) || $json['status'] !== true) {
        return [
            'success' => true,
            'status'  => $trx['status'],
            'message' => isset($json['message']) ? $json['message'] : 'Status transaksi: ' . $trx['status'],
            'trx'     => $trx
        ];
    }

    $data = isset($json['data']) ? $json['data'] : [];
    $remote_status = isset($data['status']) ? strtoupper(trim((string)$data['status'])) : $trx['status'];
    $paid_at = isset($data['paid_at']) && !empty($data['paid_at']) ? $data['paid_at'] : date('Y-m-d H:i:s');

    if (in_array($remote_status, ['SUCCESS', 'PAID', 'SETTLED', '1', 'TRUE'])) {
        if ($trx['status'] !== 'PAID') {
            $settings_bot = getsettings();
            $id_own       = isset($settings_bot['Id_owner']) ? $settings_bot['Id_owner'] : '';

            $user_id      = $trx['telegram_id'];
            $user_name    = $trx['telegram_username'];
            $amount       = intval($trx['amount']);

            topupresseller($user_id, $user_name, $amount, $id_own);
            update_qris_transaction_status($order_id, 'PAID', $paid_at);
        }

        return [
            'success'       => true,
            'status'        => 'PAID',
            'remote_status' => $remote_status,
            'message'       => 'Pembayaran LUNAS',
            'paid_at'       => $paid_at,
            'amount'        => $trx['amount'],
            'total_amount'  => $trx['total_amount'],
            'trx'           => get_qris_transaction_by_order_id($order_id),
            'data'          => $data
        ];
    } elseif ($remote_status === 'EXPIRED') {
        if ($trx['status'] === 'PENDING') {
            update_qris_transaction_status($order_id, 'EXPIRED');
        }
        return [
            'success'       => true,
            'status'        => 'EXPIRED',
            'remote_status' => $remote_status,
            'message'       => 'Transaksi Kedaluwarsa',
            'trx'           => get_qris_transaction_by_order_id($order_id),
            'data'          => $data
        ];
    } else {
        return [
            'success'       => true,
            'status'        => 'PENDING',
            'remote_status' => $remote_status,
            'message'       => 'Menunggu Pembayaran',
            'trx'           => $trx,
            'data'          => $data
        ];
    }
}