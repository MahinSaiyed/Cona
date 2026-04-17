<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Please login first');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request');
}

$order_id = intval($_POST['order_id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');
$user_id = getCurrentUserId();

if ($order_id <= 0 || empty($status)) {
    jsonResponse(false, 'Invalid data');
}

// Map sim status to actual DB status
$db_payment_status = ($status === 'paid') ? 'paid' : 'failed';
$db_payment_table_status = ($status === 'paid') ? 'success' : 'failed';

try {
    // 1. Verify ownership
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        jsonResponse(false, 'Order not found');
    }

    $db->beginTransaction();

    // 2. Update Order status
    $stmt = $db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
    $stmt->execute([$db_payment_status, $order_id]);

    // 3. Update Payment record
    $stmt = $db->prepare("UPDATE payments SET status = ? WHERE order_id = ?");
    $stmt->execute([$db_payment_table_status, $order_id]);

    $db->commit();

    jsonResponse(true, 'Payment status updated');

} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(false, 'Update failed: ' . $e->getMessage());
}
?>
