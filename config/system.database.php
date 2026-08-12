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
	if (isset($_GET['uid']) && intval($_GET['uid']) > 0) {
		return intval($_GET['uid']);
	}
	if (isset($_SESSION['impersonate_user_id']) && intval($_SESSION['impersonate_user_id']) > 0) {
		return intval($_SESSION['impersonate_user_id']);
	}
	if (isset($_SESSION['app_user_id']) && intval($_SESSION['app_user_id']) > 0) {
		return intval($_SESSION['app_user_id']);
	}
	return null;
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
	$data = $mikbotamdata->get('re_settings', [
		'saldo',
		'id_user'
	], [
		'id_user' => $id

	]);

	$hasil = $data["saldo"];

	return $hasil;
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
		$where['app_user_id'] = $tenant_id;
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

	return $data;
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

	$cols = ['id', 'username', 'password', 'full_name', 'role', 'status', 'mikrotik_ip', 'mikrotik_username', 'mikrotik_password', 'mikrotik_port', 'bot_token', 'owner_telegram_id'];
	$rows = $mikbotamdata->select('app_users', $cols, ['AND' => ['username' => $user, 'status' => 'active']]);
	$app_user = (is_array($rows) && isset($rows[0])) ? $rows[0] : false;
	if ($app_user) {
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

	$hasil = isset($data['u_id']) ? $data['u_id'] : null;
	return $hasil;
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
                'mikrotik_ip' => $mk_ip,
                'mikrotik_username' => $mk_user,
                'mikrotik_password' => $mk_pass,
                'mikrotik_port' => $mk_port,
                'bot_token' => $bot_tok,
                'owner_telegram_id' => $own_id
            ]);
        }
    } catch (Exception $e) {
        // Table creation fallback
    }
}

function get_all_app_users() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    return $mikbotamdata->select('app_users', '*');
}

function get_app_user_by_id($id) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $rows = $mikbotamdata->select('app_users', '*', ['id' => intval($id)]);
    return (is_array($rows) && isset($rows[0])) ? $rows[0] : null;
}

function get_app_user_by_username($username) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $rows = $mikbotamdata->select('app_users', '*', ['username' => $username]);
    return (is_array($rows) && isset($rows[0])) ? $rows[0] : null;
}

function save_app_user($data) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    
    if (isset($data['id']) && intval($data['id']) > 0) {
        $id = intval($data['id']);
        unset($data['id']);
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return $mikbotamdata->update('app_users', $data, ['id' => $id]);
    } else {
        if (isset($data['password'])) {
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

function pay_ppp_invoice($id, $method = 'CASH', $notes = '') {
    global $mikbotamdata;
    init_ppp_billing_tables();
    date_default_timezone_set('Asia/Jakarta');
    $tenant_id = get_current_tenant_id();

    $where_inv = ['id' => intval($id)];
    if ($tenant_id) {
        $where_inv = ['AND' => ['id' => intval($id), 'app_user_id' => $tenant_id]];
    }

    $inv = $mikbotamdata->get('ppp_invoices', ['username_ppp', 'month_year'], $where_inv);
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
            $next_exp = date('Y-m-d', strtotime('+1 month', strtotime($cur_exp)));
        } else {
            $next_month = date('Y-m', strtotime('+1 month'));
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

        $mikbotamdata->update('ppp_invoices', [
            'status' => 'PAID',
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_method' => $method,
            'notes' => $notes
        ], $where_inv);
    }
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
            $inv_no = 'INV-' . str_replace('-', '', $month_year) . '-' . sprintf('%04d', rand(1, 9999));
            $mikbotamdata->insert('ppp_invoices', [
                'invoice_number' => $inv_no,
                'username_ppp' => $user,
                'month_year' => $month_year,
                'amount' => $price,
                'status' => 'UNPAID',
                'app_user_id' => $tenant_id
            ]);
            $count++;
        }
    }
    return $count;
}