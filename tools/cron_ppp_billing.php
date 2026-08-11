<?php
//=====================================================START SCRIPT====================//

error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/system.conn.php';
require_once __DIR__ . '/../config/system.byte.php';
require_once __DIR__ . '/../config/system.database.php';
require_once __DIR__ . '/../Api/routeros_api.class.php';

echo "[CRON PPP BILLING] Started at " . date('Y-m-d H:i:s') . "\n";

$settings = get_ppp_isolir_settings();
$due_date_day = intval($settings['due_date']);
$isolir_mode  = $settings['isolir_mode'];
$isolir_prof  = $settings['isolir_profile'];

$current_month = date('Y-m');
$today_day     = intval(date('d'));

$API = new routeros_api();
$API->timeout = 5;

if (!empty($mikrotik_ip) && $API->connect($mikrotik_ip, $mikrotik_username, $mikrotik_password, $mikrotik_port)) {
	echo "[CRON PPP BILLING] Connected to MikroTik IP: $mikrotik_ip\n";
	$secrets = $API->comm("/ppp/secret/print");

	if (is_array($secrets)) {
		// 1. Auto generate invoices
		$generated = generate_monthly_invoices($current_month, $secrets);
		echo "[CRON PPP BILLING] Generated $generated new invoices for month $current_month\n";

		// 2. Auto Isolir if today >= customer's exp_date
		$today_date = date('Y-m-d');
		echo "[CRON PPP BILLING] Checking unpaid invoices for auto-isolir (Today: $today_date)...\n";
		$unpaid_invoices = get_ppp_invoices($current_month, 'UNPAID');

		foreach ($unpaid_invoices as $inv) {
			$user_ppp = $inv['username_ppp'];
			$cust_data = $mikbotamdata->get('ppp_customers', ['exp_date', 'due_date'], ['username_ppp' => $user_ppp]);
			$cust_exp  = ($cust_data && !empty($cust_data['exp_date'])) ? $cust_data['exp_date'] : $current_month . '-' . sprintf('%02d', ($cust_data && !empty($cust_data['due_date']) ? $cust_data['due_date'] : $due_date_day));

			if ($today_date >= $cust_exp) {
				$find_sec = $API->comm("/ppp/secret/print", ["?name" => $user_ppp]);

				if (isset($find_sec[0]['.id'])) {
					$sec_id = $find_sec[0]['.id'];

					if ($isolir_mode === 'profile') {
						$API->comm("/ppp/secret/set", [".id" => $sec_id, "profile" => $isolir_prof]);
					} else {
						$API->comm("/ppp/secret/disable", [".id" => $sec_id]);
					}

					// Kick active connection
					$find_act = $API->comm("/ppp/active/print", ["?name" => $user_ppp]);
					if (isset($find_act[0]['.id'])) {
						$API->comm("/ppp/active/remove", [".id" => $find_act[0]['.id']]);
					}

					update_ppp_invoice_status($inv['id'], 'ISOLIR');
					echo "[CRON PPP BILLING] Isolated user: $user_ppp (Expired: $cust_exp)\n";
				}
			}
		}
	}
	$API->disconnect();
} else {
	echo "[CRON PPP BILLING] Failed to connect to MikroTik router.\n";
}

echo "[CRON PPP BILLING] Finished at " . date('Y-m-d H:i:s') . "\n";
