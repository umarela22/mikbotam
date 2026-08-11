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

function daftar($id, $name) {

	global $mikbotamdata;
	$last_id = $mikbotamdata->insert('re_settings', [
		'id_user' => $id,
		'nama_seller' => $name,
		'Waktu' => date('H:i:s'),
		'Tanggal' => date('Y-m-d'),

	]);

	return $last_id;
}

function daftarid($id, $name, $notlp, $saldo) {

	global $mikbotamdata;

	$last_id = $mikbotamdata->insert('re_settings', [
		'id_user' => $id,
		'nama_seller' => $name,
		'nomer_tlp' => $notlp,

		'saldo' => $saldo,
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

		$hasil = $mikbotamdata->insert('re_operating', [
			'id_user' => $id,
			'nama_seller' => $nama,
			'saldo_awal' => $saldoawal,
			'saldo_akhir' => $saldo,
			'top_up' => $jumlah,
			'keterangan' => 'topup',
			'top_up_fromid' => $id_own,
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
			'Waktu' => date('H:i:s'),
			'Tanggal' => date('Y-m-d'),

		]);
	}

	return $update;
}
function lihatdata() {
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

	]);

	return $data;
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

	], [
		'id_user' => $id

	]);

	return $data;
}
function deleteuser($id) {
	global $mikbotamdata;

	$datareseller = $mikbotamdata->delete('re_settings', [

		'id_user' => $id

	]);
	$deletoperating = $mikbotamdata->delete('re_operating', [

		'id_user' => $id

	]);
	$deletlaporan = $mikbotamdata->delete('st_reportdata', [

		'id_user' => $id

	]);

	return $datareseller;
}

function has($id) {
	global $mikbotamdata;
	$data = $mikbotamdata->has('re_settings', [

		'id_user' => $id

	]);

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

	$ech = count($data);
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
	$ech = count($gethistory);
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

			'Tanggal[<>]' => [$makedate,$date]

		],
		'ORDER' => [
			'Tanggal' => 'DESC',
			'Waktu' => 'DESC',
		]]
	);

	return $gethistory;
}
function estimasidata() {
	date_default_timezone_set('Asia/Jakarta');
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, date("m"), $date, date("Y")))];

	global $mikbotamdata;
	$reportekstimasi = $mikbotamdata->sum('st_reportdata', [
		'pendapatan',
	], [

		'Tanggal[<>]' =>$startTime,
	]);

	return $reportekstimasi;
}
function getcounttopup() {
	date_default_timezone_set('Asia/Jakarta');
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, date("m"), $date, date("Y")))];

	global $mikbotamdata;
	$reportekstimasi = $mikbotamdata->sum('re_operating', [
		'top_up',
	], [
		'AND' => [
			'keterangan' => 'topup',
			'Tanggal[<>]' => $startTime,
		]

	]);

	return $reportekstimasi;
}
function countvoucher() {
	date_default_timezone_set('Asia/Jakarta');
	global $mikbotamdata;
	$dateinput = date("Y-m-d");
	$date = date('t',strtotime($dateinput));
	$startTime = [date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))), date("Y-m-d", mktime(0, 0, 0, date("m"), $date, date("Y")))];

	$gethistory = $mikbotamdata->select('re_operating', [
		"keterangan",
		"Waktu",
		"Tanggal"

	], [
		'AND' => [
			'keterangan' => 'Success',
			'Tanggal[<>]' => $startTime,
		]

	]
	);
	$ech = count($gethistory);
	return $ech;
}
function historydata($id) {
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
			'keterangan' => 'Success',
			'Tanggal[<>]' => [$makedate,$date],
		]

	]
	);
	$ech = count($gethistory);
	return $ech;
}
function gethistory($id) {

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
		"ORDER" => [
			"Tanggal" => "DESC"
		]

	]);

	return $gethistory;
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
	$settings = $mikbotamdata->get('st_mikbotam', [
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

	return $settings;
}
function upvoc($sendfungsi, $id) {

	global $mikbotamdata;
	$settings = $mikbotamdata->update('st_mikbotam', [

		"Voucher_1" => $sendfungsi,
		"Tanggal_diubah" => date('Y-m-d'),

	],
		[
			"_id" => $id,
		]);

	return $settings;
}
function upvocnon($sendfungsi, $id) {

	global $mikbotamdata;
	$settings = $mikbotamdata->update('st_mikbotam', [

		"Voucher_nonsaldo" => $sendfungsi,
		"Tanggal_diubah" => date('Y-m-d'),

	],
		[
			"_id" => $id,
		]);

	return $settings;
}
function getvocnon($id) {

	global $mikbotamdata;
	$settings = $mikbotamdata->get('st_mikbotam', [

		"Voucher_nonsaldo"
	],
		[
			"_id" => $id,
		]);
	$hasil = $settings["Voucher_nonsaldo"];
	return $hasil;
}

function getvoc($id) {

	global $mikbotamdata;
	$settings = $mikbotamdata->get('st_mikbotam', [

		"Voucher_1"
	],
		[
			"_id" => $id,
		]);
	$hasil = $settings["Voucher_1"];
	return $hasil;
}
function upbot($id, $token, $usernamebot, $namarouter, $ipmik, $usernamemik, $passmik, $port, $dns, $owner, $idowner) {

	global $mikbotamdata;
	$settings = $mikbotamdata->update('st_mikbotam', [
		"_id" => $id,
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

	]);

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
	$idowner = $mikbotamdata->select('st_monitoring', [
		'id',
		'Name',
		'Host',
		'Lokasi',
	]);
	return $idowner;
}

function st_monitoringnew($host, $name, $lokasi) {

	global $mikbotamdata;
	$idowner = $mikbotamdata->insert('st_monitoring', [
		'Name' => $name,
		'Host' => $host,
		'Lokasi' => $lokasi,
	]);
}

function st_monitoringupd($id, $host, $name, $lokasi) {

	global $mikbotamdata;
	$idowner = $mikbotamdata->update('st_monitoring', [
		'Name' => $name,
		'Host' => $host,
		'Lokasi' => $lokasi,
	], [
		'id' => $id
	]);
}

function st_monitoringdel($id) {

	global $mikbotamdata;
	$idowner = $mikbotamdata->delete('st_monitoring', [
		'id' => $id
	]);
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
            profile_name TEXT NOT NULL UNIQUE,
            price INTEGER NOT NULL DEFAULT 0,
            description TEXT
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS ppp_customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username_ppp TEXT NOT NULL UNIQUE,
            customer_name TEXT,
            phone_number TEXT,
            address TEXT,
            due_date INTEGER DEFAULT 20,
            telegram_id TEXT
        )");
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
            setting_key TEXT UNIQUE,
            setting_value TEXT
        )");
    } catch (Exception $e) {
        // Table creation fallback
    }
}

// Auto-run table initialization
init_ppp_billing_tables();

function get_ppp_packages() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $data = $mikbotamdata->select('ppp_packages', ['id', 'profile_name', 'price', 'description']);
    return is_array($data) ? $data : [];
}

function save_ppp_package($profile_name, $price, $description = '') {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $check = $mikbotamdata->get('ppp_packages', 'id', ['profile_name' => $profile_name]);
    if ($check) {
        return $mikbotamdata->update('ppp_packages', [
            'price' => intval($price),
            'description' => $description
        ], ['profile_name' => $profile_name]);
    } else {
        return $mikbotamdata->insert('ppp_packages', [
            'profile_name' => $profile_name,
            'price' => intval($price),
            'description' => $description
        ]);
    }
}

function delete_ppp_package($id) {
    global $mikbotamdata;
    return $mikbotamdata->delete('ppp_packages', ['id' => intval($id)]);
}

function get_ppp_isolir_settings() {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $rows = $mikbotamdata->select('ppp_isolir_settings', ['setting_key', 'setting_value']);
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
    foreach ($data as $key => $val) {
        $check = $mikbotamdata->get('ppp_isolir_settings', 'id', ['setting_key' => $key]);
        if ($check) {
            $mikbotamdata->update('ppp_isolir_settings', ['setting_value' => $val], ['setting_key' => $key]);
        } else {
            $mikbotamdata->insert('ppp_isolir_settings', ['setting_key' => $key, 'setting_value' => $val]);
        }
    }
    return true;
}

function get_ppp_invoices($month_year = null, $status = null, $search = null) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    $and = [];
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
    return $mikbotamdata->update('ppp_invoices', [
        'status' => 'PAID',
        'payment_date' => date('Y-m-d H:i:s'),
        'payment_method' => $method,
        'notes' => $notes
    ], ['id' => intval($id)]);
}

function update_ppp_invoice_status($id, $status) {
    global $mikbotamdata;
    init_ppp_billing_tables();
    return $mikbotamdata->update('ppp_invoices', [
        'status' => $status
    ], ['id' => intval($id)]);
}

function generate_monthly_invoices($month_year, $secrets_list) {
    global $mikbotamdata;
    init_ppp_billing_tables();
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

        $exists = $mikbotamdata->get('ppp_invoices', 'id', [
            'AND' => [
                'username_ppp' => $user,
                'month_year' => $month_year
            ]
        ]);

        if (!$exists) {
            $inv_no = 'INV-' . str_replace('-', '', $month_year) . '-' . sprintf('%04d', rand(1, 9999));
            $mikbotamdata->insert('ppp_invoices', [
                'invoice_number' => $inv_no,
                'username_ppp' => $user,
                'month_year' => $month_year,
                'amount' => $price,
                'status' => 'UNPAID'
            ]);
            $count++;
        }
    }
    return $count;
}