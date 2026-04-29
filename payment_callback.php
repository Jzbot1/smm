<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/EkupiGateway.php';
require_once __DIR__ . '/includes/JzstoreGateway.php';
require_once __DIR__ . '/includes/Auth.php';

Auth::checkLogin();

$db = Database::getInstance();
EkupiGateway::ensureSchema($db);

$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$all_settings = [];
foreach ($stmt->fetchAll() as $row) {
    $all_settings[$row['setting_key']] = $row['setting_value'];
}

$active_gateway = $all_settings['active_gateway'] ?? 'ekupi';

// eKupi often sends order_id or client_txn_id
$clientTxnId = trim((string)($_GET['client_txn_id'] ?? $_POST['client_txn_id'] ?? $_GET['order_id'] ?? $_GET['txn_id'] ?? ''));

if ($clientTxnId === '') {
    error_log("Payment Callback Error: Missing client_txn_id. GET: " . json_encode($_GET) . " POST: " . json_encode($_POST));
    header('Location: ' . BASE_URL . '/add_funds?status=failed');
    exit;
}

$stmt = $db->prepare("SELECT * FROM payment_orders WHERE client_txn_id = ?");
$stmt->execute([$clientTxnId]);
$paymentOrder = $stmt->fetch();

if (!$paymentOrder) {
    error_log("Payment Callback Error: Order not found for ID: " . $clientTxnId);
    header('Location: ' . BASE_URL . '/add_funds?status=failed');
    exit;
}

if ($active_gateway === 'jzstore') {
    $gw_settings = JzstoreGateway::getSettings($db);
    $check = JzstoreGateway::checkOrderStatus($gw_settings, $clientTxnId);
    
    // Jzstore often has status at top level or inside data/result
    $rawJzStatus = strtolower((string)($check['data']['status'] ?? $check['data']['data']['status'] ?? $check['data']['result']['status'] ?? ''));
    $status = in_array($rawJzStatus, ['success', 'completed', '1', 'true']) ? 'success' : $rawJzStatus;
    
    // If top level is boolean true but status string is different, double check
    if ($status !== 'success' && isset($check['data']['status']) && $check['data']['status'] === true) {
        $innerStatus = strtolower((string)($check['data']['data']['status'] ?? $check['data']['result']['status'] ?? ''));
        if (in_array($innerStatus, ['success', 'completed'])) {
            $status = 'success';
        }
    }
    
    $utr = (string)($check['data']['data']['utr'] ?? $check['data']['result']['utr'] ?? $check['data']['utr'] ?? '');
} else {
    $gw_settings = EkupiGateway::getSettings($db);
    $check = EkupiGateway::checkOrderStatus($gw_settings, $clientTxnId);
    
    // eKupi success statuses can be 'SUCCESS', 'COMPLETED', etc.
    // Check both levels as some API versions vary
    $rawStatus = strtolower((string)($check['data']['data']['status'] ?? $check['data']['status'] ?? ''));
    $status = in_array($rawStatus, ['success', 'completed', 'success_scan', 'scan_pay', '1', 'true']) ? 'success' : $rawStatus;
    $utr = (string)($check['data']['data']['utr'] ?? $check['data']['utr'] ?? '');
}

if (!$check['ok']) {
    error_log("Payment Callback Error: Gateway check failed for ID $clientTxnId. " . ($check['error'] ?? 'Unknown error'));
    header('Location: ' . BASE_URL . '/add_funds?status=failed');
    exit;
}

if ($status === 'success') {
    EkupiGateway::finalizeSuccess($db, $clientTxnId, $utr, json_encode($check['data']));
    header('Location: ' . BASE_URL . '/add_funds?status=success');
    exit;
}

// Log non-success status for debugging
error_log("Payment Callback: Order status is [" . $status . "] for ID: " . $clientTxnId . ". Full Response: " . json_encode($check['data']));

// ONLY mark as failed if the gateway explicitly says it failed. 
// If it's 'pending', 'processing', etc., we just redirect without marking it failed in DB.
if (in_array($status, ['failed', 'failure', 'rejected', 'canceled'])) {
    EkupiGateway::markFailed($db, $clientTxnId, json_encode($check['data']));
}

header('Location: ' . BASE_URL . '/add_funds?status=failed');
exit;

