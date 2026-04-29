<?php
// cron/status.php - Automated Order Status Updater
// Recommended frequency: Every 2-5 minutes

// Disable error display for cleaner output in logs, but log errors to file
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/SmmApi.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Cron Status Update...\n";

try {
    $db = Database::getInstance();
    $api = new SmmApi();

    // Get orders that are not in a final state
    // Final states: Completed, Canceled, Partial, Refunded
    $stmt = $db->query("SELECT id, api_order_id, status FROM orders WHERE status NOT IN ('Completed', 'Canceled', 'Partial', 'Refunded', 'Success') AND api_order_id IS NOT NULL AND api_order_id != '' LIMIT 50");
    $orders = $stmt->fetchAll();

    if (empty($orders)) {
        echo "[" . date('Y-m-d H:i:s') . "] No pending orders to update.\n";
        exit;
    }

    $api_order_ids = array_column($orders, 'api_order_id');
    $api_order_map = [];
    foreach ($orders as $order) {
        $api_order_map[$order['api_order_id']] = $order['id'];
    }

    echo "[" . date('Y-m-d H:i:s') . "] Fetching status for " . count($api_order_ids) . " orders from provider...\n";
    
    $response = $api->multiStatus($api_order_ids);

    if (!$response || isset($response->error)) {
        $error_msg = $response->error ?? 'Unknown API Error or Empty Response';
        echo "[" . date('Y-m-d H:i:s') . "] API Error: " . $error_msg . "\n";
        exit;
    }

    $updated_count = 0;
    foreach ($response as $api_order_id => $data) {
        if (!is_object($data) || isset($data->error)) {
            echo "[" . date('Y-m-d H:i:s') . "] Skipping API Order ID $api_order_id: " . ($data->error ?? 'Invalid Data') . "\n";
            continue;
        }
        
        $local_order_id = $api_order_map[$api_order_id] ?? null;
        if (!$local_order_id) continue;
        
        $status = ucfirst(strtolower($data->status ?? 'Pending')); // Normalize casing
        $remains = $data->remains ?? 0;
        
        // Map common API statuses to our local statuses if needed
        if ($status == 'Inprogress') $status = 'Processing';
        if ($status == 'Completed') $status = 'Completed';
        
        $updateStmt = $db->prepare("UPDATE orders SET status = ?, remains = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$status, $remains, $local_order_id]);
        $updated_count++;
    }

    // Record last run in settings
    $last_run = date('Y-m-d H:i:s');
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('last_cron_status_run', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$last_run, $last_run]);

    echo "[" . date('Y-m-d H:i:s') . "] Success: $updated_count orders updated. Last run: " . $last_run . "\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] CRITICAL ERROR: " . $e->getMessage() . "\n";
    error_log("Cron Status Error: " . $e->getMessage());
}
