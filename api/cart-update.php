<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$cart_id = intval($_POST['cart_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($cart_id <= 0 || $quantity <= 0) {
    jsonResponse(false, 'Invalid input');
}

$user_id = getCurrentUserId();
$session_id = session_id();

// Verify cart item belongs to current user/session
if ($user_id) {
    $stmt = $db->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
} else {
    $stmt = $db->prepare("SELECT * FROM cart WHERE id = ? AND session_id = ?");
    $stmt->execute([$cart_id, $session_id]);
}

$cart_item = $stmt->fetch();

if (!$cart_item) {
    jsonResponse(false, 'Cart item not found');
}

// Check stock
$stmt = $db->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->execute([$cart_item['product_id']]);
$product = $stmt->fetch();

if ($product['stock'] < $quantity) {
    jsonResponse(false, 'Insufficient stock');
}

// Update quantity
$stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
$stmt->execute([$quantity, $cart_id]);

jsonResponse(true, 'Cart updated');
?>