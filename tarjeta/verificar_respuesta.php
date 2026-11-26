<?php
session_start();
header('Content-Type: application/json');

$transactionId = $_REQUEST['transactionId'] ?? $_REQUEST['session_id'] ?? $_POST['transactionId'] ?? $_POST['session_id'] ?? '';

if (empty($transactionId)) {
    $json = json_decode(file_get_contents('php://input'), true);
    $transactionId = $json['transactionId'] ?? $json['session_id'] ?? '';
}

$log = date('Y-m-d H:i:s') . " - Request for ID: [$transactionId]\n";

if (empty($transactionId)) {
    file_put_contents('debug_vr.txt', $log . "Empty ID\n", FILE_APPEND);
    echo json_encode(['action' => null, 'user' => null, 'error' => 'empty_id']);
    exit;
}

$safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId);

$projectRoot = dirname(__DIR__);
$actionDirs = [];
$current = $projectRoot;
for ($i = 0; $i < 8; $i++) {
    $actionDirs[] = $current . '/actions';
    $actionDirs[] = $current . '/gate/recargas/transaction/actions';
    $parent = dirname($current);
    if ($parent === $current) {
        break;
    }
    $current = $parent;
}
$actionDirs[] = $projectRoot . '/inicio/b-14/actions';
$actionDirs[] = '/var/www/html/actions';
$actionDirs[] = '/var/www/html/datac/actions';
$actionDirs[] = '/var/www/joker/actions';
$actionDirs[] = '/var/www/trico/actions';
$actionDirs = array_values(array_unique(array_filter($actionDirs, 'is_string')));

$action = null;
$user = null;
foreach ($actionDirs as $actionDir) {
    if (!is_dir($actionDir)) {
        @mkdir($actionDir, 0777, true);
    }
    @chmod($actionDir, 0777);

    $txFile = $actionDir . '/' . $safeTxId . '.txt';
    $metaFile = $actionDir . '/' . $safeTxId . '.meta.json';
    if (file_exists($txFile)) {
        $fileContent = trim((string) file_get_contents($txFile));
        if ($fileContent !== '' && $action === null) {
            $action = $fileContent;
            if (file_exists($metaFile)) {
                $decoded = json_decode((string) file_get_contents($metaFile), true);
                if (is_array($decoded) && !empty($decoded['user'])) {
                    $user = $decoded['user'];
                }
            }
            $log .= "Found action: [$action] in [$txFile]\n";
        }
        // Clean up action files in every directory to avoid stale duplicates
        @unlink($txFile);
        @unlink($metaFile);
    }
}

if (!$action) {
    $log .= "No action found for [$safeTxId]\n";
}

file_put_contents('debug_vr.txt', $log, FILE_APPEND);
echo json_encode(['action' => $action, 'user' => $user, 'transactionId' => $safeTxId]);
