<?php
//=====================================================START SCRIPT====================//

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/../config/system.conn.php';
include_once __DIR__ . '/../config/system.database.php';
include_once __DIR__ . '/../Saldo/src/FrameBot.php';
include_once __DIR__ . '/../Saldo/src/Bot.php';

// Log raw callback data for audit and debugging
$raw_input = file_get_contents('php://input');
$log_dir = __DIR__ . '/../logs';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}
@file_put_contents($log_dir . '/klikqris_callback.log', date('[Y-m-d H:i:s] ') . $raw_input . PHP_EOL, FILE_APPEND);

$payload = json_decode($raw_input, true);
if (!$payload && !empty($_POST)) {
    $payload = $_POST;
}

if (!$payload) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid or empty JSON payload']);
    exit();
}

// Extract fields from payload
$order_id = '';
if (isset($payload['order_id'])) {
    $order_id = trim($payload['order_id']);
} elseif (isset($payload['data']['order_id'])) {
    $order_id = trim($payload['data']['order_id']);
}

$status = '';
if (isset($payload['status'])) {
    $status = strtoupper(trim((string)$payload['status']));
} elseif (isset($payload['data']['status'])) {
    $status = strtoupper(trim((string)$payload['data']['status']));
}

$incoming_signature = '';
if (isset($payload['signature'])) {
    $incoming_signature = trim((string)$payload['signature']);
} elseif (isset($payload['data']['signature'])) {
    $incoming_signature = trim((string)$payload['data']['signature']);
}

$payment_date = '';
if (isset($payload['payment_date']) && !empty($payload['payment_date'])) {
    $payment_date = trim($payload['payment_date']);
} elseif (isset($payload['paid_at']) && !empty($payload['paid_at'])) {
    $payment_date = trim($payload['paid_at']);
} else {
    $payment_date = date('Y-m-d H:i:s');
}

if (empty($order_id)) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Missing order_id in payload']);
    exit();
}

// 1. Find transaction in database
$trx = get_qris_transaction_by_order_id($order_id);
if (!$trx) {
    http_response_code(404);
    echo json_encode(['status' => false, 'message' => 'Transaction not found for order_id: ' . $order_id]);
    exit();
}

// 2. Double Security: Validate Signature to prevent fake webhooks
if (!empty($trx['signature']) && !empty($incoming_signature)) {
    if (!hash_equals($trx['signature'], $incoming_signature)) {
        @file_put_contents($log_dir . '/klikqris_callback.log', date('[Y-m-d H:i:s] ') . "[SECURITY REJECTION] Signature mismatch for order_id: $order_id. Stored: {$trx['signature']} | Received: $incoming_signature" . PHP_EOL, FILE_APPEND);
        http_response_code(403);
        echo json_encode([
            'status' => false,
            'message' => 'Signature verification failed. Potential unauthorized webhook.'
        ]);
        exit();
    }
}

// 3. Process Transaction Status
$is_paid_status = in_array($status, ['PAID', 'SUCCESS', 'SETTLED', '1', 'TRUE']);

if ($is_paid_status) {
    // Check current status in DB to prevent duplicate processing
    if ($trx['status'] === 'PENDING' || $trx['status'] === 'EXPIRED') {
        $settings = getsettings();
        $id_own   = isset($settings['Id_owner']) ? $settings['Id_owner'] : '';
        $token    = isset($settings['Token_bot']) ? $settings['Token_bot'] : '';

        $user_id      = $trx['telegram_id'];
        $user_name    = $trx['telegram_username'];
        $amount       = intval($trx['amount']);
        $total_amount = intval($trx['total_amount']);

        // Execute top up to reseller account
        $res_topup = topupresseller($user_id, $user_name, $amount, $id_own);
        
        // Update database status to PAID
        update_qris_transaction_status($order_id, 'PAID', $payment_date);

        // Send Telegram Notifications
        if (!empty($token)) {
            FrameBot::init(['token' => $token]);

            // Notification to User
            $user_text = "✅ <b>PEMBAYARAN QRIS DITERIMA!</b>\n";
            $user_text .= "━━━━━━━━━━━━━━━━━━━━━\n";
            $user_text .= "🆔 <b>Order ID:</b> <code>" . htmlspecialchars($order_id) . "</code>\n";
            $user_text .= "💰 <b>Nominal Saldo:</b> " . rupiah($amount) . "\n";
            $user_text .= "💵 <b>Total Dibayar:</b> " . rupiah($total_amount) . "\n";
            $user_text .= "⏰ <b>Waktu Bayar:</b> " . htmlspecialchars($payment_date) . "\n";
            $user_text .= "━━━━━━━━━━━━━━━━━━━━━\n";
            $user_text .= "🎉 Saldo Anda berhasil ditambahkan sebesar <b>" . rupiah($amount) . "</b>. Terima kasih!\n\n";

            $user_opt = [
                'chat_id' => $user_id,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => '🔎 Beli Voucher', 'callback_data' => 'Menu'],
                            ['text' => '💰 Cek Saldo', 'callback_data' => 'ceksaldo']
                        ]
                    ]
                ]),
                'parse_mode' => 'html'
            ];
            Bot::sendMessage($user_text, $user_opt);

            // Notification to Admin / Owner
            if (!empty($id_own)) {
                $admin_text = "💰 <b>NOTIFIKASI DEPOSIT QRIS OTOMATIS</b>\n";
                $admin_text .= "━━━━━━━━━━━━━━━━━━━━━\n";
                $admin_text .= "👤 <b>User:</b> @" . htmlspecialchars($user_name) . " (<code>$user_id</code>)\n";
                $admin_text .= "🆔 <b>Order ID:</b> <code>" . htmlspecialchars($order_id) . "</code>\n";
                $admin_text .= "💰 <b>Nominal Saldo:</b> " . rupiah($amount) . "\n";
                $admin_text .= "💵 <b>Total Diterima:</b> " . rupiah($total_amount) . "\n";
                $admin_text .= "⏰ <b>Waktu:</b> " . htmlspecialchars($payment_date) . "\n";
                $admin_text .= "Status: <b>LUNAS (Otomatis Terisi)</b>\n";
                $admin_text .= "━━━━━━━━━━━━━━━━━━━━━";

                $admin_opt = [
                    'chat_id' => $id_own,
                    'parse_mode' => 'html'
                ];
                Bot::sendMessage($admin_text, $admin_opt);
            }
        }

        http_response_code(200);
        echo json_encode([
            'status' => true,
            'message' => 'Webhook received and payment processed successfully',
            'order_id' => $order_id
        ]);
        exit();
    } else {
        // Already PAID or SUCCESS -> Ignore and acknowledge with HTTP 200 OK to prevent duplicate top-up
        http_response_code(200);
        echo json_encode([
            'status' => true,
            'message' => 'Transaction was already processed. Skipped to prevent duplicate delivery.',
            'order_id' => $order_id
        ]);
        exit();
    }
} elseif (in_array($status, ['EXPIRED', 'FAILED', 'CANCELLED'])) {
    if ($trx['status'] === 'PENDING') {
        update_qris_transaction_status($order_id, $status);
    }
    http_response_code(200);
    echo json_encode([
        'status' => true,
        'message' => 'Transaction marked as ' . $status,
        'order_id' => $order_id
    ]);
    exit();
}

http_response_code(200);
echo json_encode([
    'status' => true,
    'message' => 'Callback acknowledged with status: ' . $status,
    'order_id' => $order_id
]);
