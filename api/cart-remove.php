<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$cart_id = intval($_POST['cart_id'] ?? 0);

if ($cart_id <= 0) {
    jsonResponse(false, 'Invalid cart item');
}

$user_id = getCurrentUserId();
$session_id = session_id();

// Remove item
if ($user_id) {
    $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
} else {
    $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
    $stmt->execute([$cart_id, $session_id]);
}

jsonResponse(true, 'Item removed from cart');
?>