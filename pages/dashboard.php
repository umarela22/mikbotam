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

	error_reporting(0);

	if (!isset($_SESSION["Mikbotamuser"])) {
		header("Location:../admin/login.php");
		exit();
	} else {
		include_once __DIR__ . '/../config/system.conn.php';
		include_once __DIR__ . '/../config/system.byte.php';
		include_once __DIR__ . '/../Api/routeros_api.class.php';
		$id = isset($id) ? $id : null;
		$datavoucher = sethistory($id);
		if (!is_array($datavoucher)) {
			$datavoucher = [];
		}
		date_default_timezone_set('Asia/Jakarta');
		$API = new routeros_api();
		$API->timeout = 1;

		if (!empty($mikrotik_ip) && $API->connect($mikrotik_ip, $mikrotik_username, $mikrotik_password, $mikrotik_port)) {
			$IDENTITY      = $API->comm('/system/identity/getall');
			$routername    = isset($IDENTITY['0']['name']) ? $IDENTITY['0']['name'] : 'Unknown';
			$health        = $API->comm("/system/health/print");
			$dhealth       = isset($health['0']) ? $health['0'] : [];
			$ARRAY         = $API->comm("/system/resource/print");
			$first         = isset($ARRAY['0']) ? $ARRAY['0'] : [];

			$total_mem     = isset($first['total-memory']) && $first['total-memory'] > 0 ? $first['total-memory'] : 1;
			$free_mem      = isset($first['free-memory']) ? $first['free-memory'] : 0;
			$total_hdd     = isset($first['total-hdd-space']) && $first['total-hdd-space'] > 0 ? $first['total-hdd-space'] : 1;
			$free_hdd      = isset($first['free-hdd-space']) ? $first['free-hdd-space'] : 0;

			$memperc       = ($free_mem / $total_mem);
			$hddperc       = ($free_hdd / $total_hdd);
			$mem           = ($memperc * 100);
			$hdd_calc      = ($hddperc * 100);
			$sehat         = isset($dhealth['temperature']) ? $dhealth['temperature'] : '-';
			$platform      = isset($first['platform']) ? $first['platform'] : '-';
			$board         = isset($first['board-name']) ? $first['board-name'] : '-';
			$version       = isset($first['version']) ? $first['version'] : '-';
			$architecture  = isset($first['architecture-name']) ? $first['architecture-name'] : '-';
			$cpu           = isset($first['cpu']) ? $first['cpu'] : '-';
			$cpuload       = isset($first['cpu-load']) ? $first['cpu-load'] : '-';
			$uptime        = isset($first['uptime']) ? $first['uptime'] : '-';
			$cpufreq       = isset($first['cpu-frequency']) ? $first['cpu-frequency'] : '-';
			$cpucount      = isset($first['cpu-count']) ? $first['cpu-count'] : '-';
			$memory        = formatBytes($total_mem);
			$fremem        = formatBytes($free_mem);
			$mempersen     = number_format($mem, 2);
			$hdd           = formatBytes($total_hdd);
			$frehdd        = formatBytes($free_hdd);
			$hddpersen     = number_format($hdd_calc, 2);
			$sector        = isset($first['write-sect-total']) ? $first['write-sect-total'] : '-';
			$setelahreboot = isset($first['write-sect-since-reboot']) ? $first['write-sect-since-reboot'] : '-';
			$kerusakan     = isset($first['bad-blocks']) ? $first['bad-blocks'] : '0';
			$API->disconnect();
		} else {
			$routername    = 'Disconnected';
			$board         = 'N/A';
			$version       = 'N/A';
			$cpu           = 'N/A';
			$cpufreq       = 'N/A';
			$cpucount      = 'N/A';
			$fremem        = '0 MB';
			$frehdd        = '0 MB';
			$kerusakan     = '0';
		}
	}

?>


<script type="text/javascript" src="../lib/jquery.marquee/lib/jquery.marquee.min.js"></script>
<link type="text/css" href="../lib/jquery.marquee/css/jquery.marquee.min.css" rel="stylesheet" title="default" media="all" />
<script type="text/javascript">
	$(document).ready(function () {
		$("#marquee1").marquee({
			showSpeed: 850, scrollSpeed: 40
		});
	});
		
var _0x4214=["\x66\x61\x73\x74","\x66\x61\x64\x65\x49\x6E","\x2E\x2E\x2F\x47\x72\x61\x70\x68\x2F\x47\x65\x74\x61\x63\x74\x69\x76\x65\x2E\x70\x68\x70\x3F\x4F\x6E\x6C\x69\x6E\x65","\x6C\x6F\x61\x64","\x2E\x75\x73\x65\x72\x2D\x6F\x6E\x6C\x69\x6E\x65","\x2E\x2E\x2F\x47\x72\x61\x70\x68\x2F\x47\x65\x74\x61\x63\x74\x69\x76\x65\x2E\x70\x68\x70\x3F\x63\x70\x75","\x2E\x63\x70\x75\x2D\x6C\x6F\x61\x64","\x2E\x2E\x2F\x47\x72\x61\x70\x68\x2F\x47\x65\x74\x61\x63\x74\x69\x76\x65\x2E\x70\x68\x70\x3F\x66\x72\x65\x65\x2D\x6D\x65\x6D\x6F\x72\x79","\x2E\x66\x72\x65\x65\x2D\x6D\x65\x6D\x6F\x72\x79","\x2E\x2E\x2F\x47\x72\x61\x70\x68\x2F\x47\x65\x74\x61\x63\x74\x69\x76\x65\x2E\x70\x68\x70\x3F\x75\x70\x74\x69\x6D\x65","\x2E\x75\x70\x2D\x74\x69\x6D\x65","\x2E\x2E\x2F\x47\x72\x61\x70\x68\x2F\x47\x65\x74\x61\x63\x74\x69\x76\x65\x2E\x70\x68\x70\x3F\x61\x70\x6F\x6E\x6C\x69\x6E\x65","\x2E\x61\x70\x2D\x6F\x6E\x6C\x69\x6E\x65"];var timer;var auto_refresh=setInterval(function(){$(_0x4214[4])[_0x4214[3]](_0x4214[2])[_0x4214[1]](_0x4214[0]);$(_0x4214[6])[_0x4214[3]](_0x4214[5])[_0x4214[1]](_0x4214[0]);$(_0x4214[8])[_0x4214[3]](_0x4214[7])[_0x4214[1]](_0x4214[0]);$(_0x4214[10])[_0x4214[3]](_0x4214[9])[_0x4214[1]](_0x4214[0]);$(_0x4214[12])[_0x4214[3]](_0x4214[11])[_0x4214[1]](_0x4214[0])},10000)
</script>
<div class="sl-pagebody">

<?php
$is_superadmin = (isset($_SESSION['app_user_role']) && $_SESSION['app_user_role'] === 'superadmin' && (!isset($_SESSION['impersonate_user_id']) || intval($_SESSION['impersonate_user_id']) === 0));
$all_tenants = $is_superadmin ? get_all_app_users() : [];
$selected_tenant_filter = isset($_GET['filter_tenant_id']) ? $_GET['filter_tenant_id'] : 'all';
?>

<?php if ($is_superadmin): ?>
<div class="card pd-20 mg-b-20 bg-white bd bd-warning shadow-sm">
	<div class="d-flex align-items-center justify-content-between flex-wrap">
		<div>
			<h5 class="tx-inverse font-weight-bold mg-b-5">
				<i class="fa fa-dashboard text-warning mg-r-8"></i> Dashboard Monitoring SuperAdmin
			</h5>
			<p class="tx-12 text-muted mg-b-0">
				Monitor seluruh transaksi, mutasi voucher, dan deposit reseller dari semua Admin (Tenant) atau pilih admin spesifik.
			</p>
		</div>
		<div class="mg-t-10 mg-sm-t-0 d-flex align-items-center flex-wrap">
			<form method="GET" action="./" class="form-inline">
				<input type="hidden" name="Mikbotam" value="Dashboard">
				<label class="tx-13 font-weight-bold mg-r-10 text-inverse"><i class="fa fa-filter text-primary"></i> Filter Tenant / Admin:</label>
				<select name="filter_tenant_id" class="form-control wd-220" onchange="this.form.submit()">
					<option value="all" <?=($selected_tenant_filter === 'all' || $selected_tenant_filter === '0') ? 'selected' : '';?>>🌐 Semua Admin (Keseluruhan System)</option>
					<?php foreach ($all_tenants as $tn): ?>
						<option value="<?=$tn['id'];?>" <?=($selected_tenant_filter == $tn['id']) ? 'selected' : '';?>>
							👤 <?=htmlspecialchars($tn['full_name']);?> (<?=htmlspecialchars($tn['username']);?>)
						</option>
					<?php endforeach; ?>
				</select>
			</form>

			<?php if (intval($selected_tenant_filter) > 0): ?>
				<form method="POST" action="./?Mikbotam=manageusers" class="mg-l-10">
					<input type="hidden" name="user_id" value="<?=intval($selected_tenant_filter);?>">
					<button type="submit" name="action_impersonate" class="btn btn-warning btn-sm font-weight-bold">
						<i class="fa fa-user-secret mg-r-5"></i> Beralih ke Dashboard Admin Ini
					</button>
				</form>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php if ($selected_tenant_filter === 'all' || $selected_tenant_filter === '0'): ?>
<div class="card bd-warning mg-b-20">
	<div class="card-header bg-warning tx-white font-weight-bold d-flex justify-content-between align-items-center">
		<span><i class="fa fa-users mg-r-5"></i> Rekapitulasi Aktivitas Seluruh Admin (Tenant Multi-User)</span>
		<span class="badge badge-light tx-12"><?=count($all_tenants);?> Admin Terdaftar</span>
	</div>
	<div class="card-body pd-0">
		<div class="table-responsive">
			<table class="table table-hover table-striped mg-b-0">
				<thead class="bg-gray-100 tx-12 text-uppercase">
					<tr>
						<th>No</th>
						<th>Nama Admin / Owner</th>
						<th>Username</th>
						<th>Router MikroTik</th>
						<th>Role</th>
						<th>Status</th>
						<th class="text-right">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$no = 1;
					foreach ($all_tenants as $tenant_item): 
					?>
						<tr>
							<td><?=$no++;?></td>
							<td class="font-weight-bold"><?=htmlspecialchars($tenant_item['full_name']);?></td>
							<td><code><?=htmlspecialchars($tenant_item['username']);?></code></td>
							<td>
								<span class="badge badge-info pd-5 font-weight-bold">
									<i class="fa fa-server mg-r-3"></i> <?=htmlspecialchars($tenant_item['mikrotik_ip'] ?: '127.0.0.1');?>
								</span>
							</td>
							<td>
								<span class="badge badge-<?=($tenant_item['role'] === 'superadmin') ? 'warning' : 'primary';?>">
									<?=strtoupper($tenant_item['role']);?>
								</span>
							</td>
							<td>
								<span class="badge badge-<?=($tenant_item['status'] === 'active') ? 'success' : 'danger';?>">
									<?=strtoupper($tenant_item['status']);?>
								</span>
							</td>
							<td class="text-right">
								<form method="POST" action="./?Mikbotam=manageusers" style="display:inline;">
									<input type="hidden" name="user_id" value="<?=$tenant_item['id'];?>">
									<button type="submit" name="action_impersonate" class="btn btn-sm btn-outline-warning font-weight-bold" title="Masuk Mode Dashboard Admin Ini">
										<i class="fa fa-user-secret mg-r-3"></i> Impersonate
									</button>
								</form>
								<a href="./?Mikbotam=Dashboard&filter_tenant_id=<?=$tenant_item['id'];?>" class="btn btn-sm btn-outline-info font-weight-bold mg-l-3" title="Filter Statistik Dashboard">
									<i class="fa fa-filter mg-r-3"></i> Filter Stats
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php endif; ?>
<?php endif; ?>

	<div class="row row-sm">
		<div class="col-sm-6 col-xl-3">
			<div class="card pd-20 pd-sm-10 boxed">
				<div class="d-flex align-items-center">
					<span> <img src="../img/vocher.svg" alt="mikbotam.id"></span>
					<div class="mg-l-15">
						<span class="tx-15 tx-spacing-1 tx-gray-500">
							Total Voucher<br> Bulan ini<br><br>   </span>     
							<h6 class="tx-inverse mg-b-0"><?=countvoucher();
								?> Voucher</h6>
						</div>
					</div>
				</div>
				<!-- card -->
			</div>
			<!-- col-3 -->
			<div class="col-sm-6 col-xl-3 mg-t-10 mg-sm-t-0">
				<div class="card pd-20 pd-sm-10 boxed">
					<div class="d-flex align-items-center">
						<img src="../img/topup.svg" alt="mikbotam.id">
						<div class="mg-l-10">
							<span class="tx-15 tx-spacing-1 tx-gray-500">
								Top up Debit</br> bulan ini<br><br>       </span>
							<h6 class="tx-inverse mg-b-0">  <?=rupiah(getcounttopup());
								?></h6>
						</div>
					</div>
				</div>



				<!-- card -->
			</div>
			<!-- col-3 -->
			<div class="col-sm-6 col-xl-3 mg-t-10 mg-xl-t-0">
				<div class="card pd-15 pd-sm-10 boxed">
					<div class="d-flex align-items-center">

						<img src="../img/mutasi.svg" alt="mikbotam.id">
						<div class="mg-l-10">
							<span class="tx-15 tx-spacing-1 tx-gray-500">
								Mutasi Voucher</br> bulan ini<br><br>  </span>
							<h6 class="tx-inverse mg-b-0">   <?=rupiah(estimasidata());
								?></h6>
						</div>
					</div>
				</div>

				<!-- card -->
			</div>
			<!-- col-3 -->
			<div class="col-sm-6 col-xl-3 mg-t-10 mg-xl-t-0">
				<div class="card pd-20 pd-sm-10 boxed">
					<div class="d-flex align-items-center">

						<img src="../img/newuser.svg" alt="mikbotam.id">
						<div class="mg-l-10">
							<span class="tx-15 tx-spacing-1 tx-gray-500">
								User + </br> bulan ini<br><br>       </span>
							<h6 class="tx-inverse mg-b-0">   <?='+' . countuser() . ' User';
								?></h6>
						</div>
					</div>
				</div>
				<!-- card -->
			</div>
			<!-- col-3 -->
		</div>
	
		<div class="row row-sm mg-t-10-force">
			<div class="col-lg-8">	<?php info();
		?>
				<div class="card bd-primary mg-t-10 ">
					<div class="card-header bg-primary tx-white ">
							Transaksi dalam 1 bulan terkahir
					</div>
					<div class="card-body">
						<div class="table-wrapper">
							<table id="userhistory" class="table display  nowrap " width="100%">
								<thead>
									<tr>
										<th>No</th>
										<th>Id User</th>
										<th>Username</th>
										<th>Keterangan</th>
										<th>Jumlah</th>
										<th>Waktu</th>
										<th>Tanggal</th>
										<th>Saldo Akhir</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$TotalReg = is_array($datavoucher) ? count($datavoucher) : 0;
									for ($i = 0; $i < $TotalReg; $i++) {
										$datas = $datavoucher[$i];
										$no = $i + 1;
										$id_user = $datas['id_user'];
										$nama_seller = $datas['nama_seller'];
										$saldo_awal = $datas['saldo_awal'];
										$beli_voucher = $datas['beli_voucher'];
										$saldo_akhir = $datas['saldo_akhir'];
										$top_up = $datas['top_up'];
										$top_up_fromid = $datas['top_up_fromid'];
										$username_voucher = $datas['username_voucher'];
										$password_voucher = $datas['password_voucher'];
										$exp_voucher = $datas['exp_voucher'];

										$keterangan = $datas['keterangan'];
										if ($keterangan == 'Success') {
											$ket = "<span class='label label-success m-r-15 text-uppercase'>SUCCESS  Voc $exp_voucher</span>";
										} elseif ($keterangan == 'gagalprint') {
											$ket = "<span class='label label-warning m-r-15 text-uppercase'>VALID PRINT </span>";
										} elseif ($keterangan == 'gagal') {
											$ket = "<span class='label label-warning m-r-15 text-uppercase'>VALID SERVER</span>";
										} else {
											$ket = "<span class='label label-info m-r-15 text-uppercase'>TOP UP ".rupiah($top_up)." </span>";
											$beli_voucher = $top_up;
										}
										$Waktu = $datas['Waktu'];
										$Tanggal = $datas['Tanggal'];
										echo "<tr>";
										echo "<td>" . $no . "</td>";
										echo "<td>" . $id_user . "</td>";
										echo "<td>" . $nama_seller . "</td>";
										echo "<td>" . $ket . "</td>";
										echo "<td>" . rupiah($beli_voucher) . "</td>";
										echo "<td>" . $Waktu . "</td>";
										echo "<td>" . $Tanggal . "</td>";

										echo "<td>" . rupiah($saldo_akhir) . "</td>";

										echo "</tr>";
									}

									?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- card-body -->
				</div>

			</div>
		
			<div class="col-lg-4 mg-t-10">
			
	
		
			
				<div class="card bd bd-primary">
					<div class="card-body ">
						<div class="bd">
							<div class="pd-8 pd-sm-8 bg-primary">
								<div class="d-flex align-items-center">
									<img src="../img/router.svg" alt="mikbotam.id" style="width: 20%;">
									<div class="mg-l-10">
										<span class="tx-15 tx-spacing-1 tx-white">
											Router Name : <?=$routername;
											?><br>
											Model :  <?=$board;
											?><br>
											Router OS : <?=$version;
											?><br>
											<div class="up-time">Loading..
											</div>
										</span>
										<h6 class="tx-inverse mg-b-0"></h6>
									</div>
								</div>
							</div>
							<!-- card -->
							<hr>
							<div class="pd-8 pd-sm-8  mg-t-2">
								<div class="d-flex align-items-center">
									<img src="../img/cpu.svg" alt="mikbotam.id" style="width: 20%;">
									<div class="mg-l-15">
										<span class="tx-15 tx-spacing-1 tx-gray-500">
											Cpu : <?=$cpu;
											?><br>
											Cpu Freq : <?=$cpufreq;
											?><br>
											<div class="cpu-load">Loading..
											</div>
											Cpu Count: <?=$cpucount;
											?></span>
										<h6 class="tx-inverse mg-b-0"></h6>
									</div>
								</div>
							</div>
							<hr>
							<div class="pd-8 pd-sm-8  mg-t-2">
								<div class="d-flex align-items-center">
									<img src="../img/tools.svg" alt="mikbotam.id" style="width: 20%;">
									<div class="mg-l-15">
										<span class="tx-15 tx-spacing-1 tx-gray-500">
											Memory free : <?=$fremem;
											?><br>
											Hardisk free :  <?=$frehdd;
											?><br>
											BadBlock hd : <?=$kerusakan . ' %';
											?></span>
										<h6 class="tx-inverse mg-b-0"></h6>
									</div>
								</div>
							</div>
							<!-- card -->

							<!-- card -->
							<hr>
							<div class="pd-8 pd-sm-8  mg-t-2">
								<div class="d-flex align-items-center">
									<img src="../img/tower.svg" alt="mikbotam.id" style="width: 20%;">
									<div class="mg-l-15">
										<span class="tx-15 tx-spacing-1 tx-gray-500">
											<div class="ap-online">Loading..
											</div>
											<div class="user-online">Loading..</div>
											Tgl    :  <?=date('d-m-Y');
											?><br>
										</span>
										<h6 class="tx-inverse mg-b-0"></h6>
									</div>
								</div>
							</div>
							<!-- card -->
						
							<hr>
							<div class="pd-8 pd-sm-8  mg-t-2">
								<div class="d-flex align-items-center">
									<img src="../img/logoM.svg" alt="mikbotam.id" style="width: 20%;">
									<div class="mg-l-15">
										<span class="tx-15 tx-spacing-1 tx-gray-500">
											Name : Mikbotam <br>
											Version : <?=Version();?> <br>
											Tgl    :  <?=date('d-m-Y');
											?><br>
										</span>
										<h6 class="tx-inverse mg-b-0"></h6>
									</div>
								</div>
							</div>
							<!-- card -->
						</div>
						<!-- card -->
					</div>
				</div>
			</div>
		</div>
	</div>