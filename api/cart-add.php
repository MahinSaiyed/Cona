<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$product_id = intval($_POST['product_id'] ?? 0);
$size = sanitize($_POST['size'] ?? '');
$color = sanitize($_POST['color'] ?? '');
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id <= 0) {
    jsonResponse(false, 'Invalid product');
}

// Check if product exists and is in stock
$stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    jsonResponse(false, 'Product not found');
}

if ($product['stock'] < $quantity) {
    jsonResponse(false, 'Insufficient stock');
}

$user_id = getCurrentUserId();
$session_id = session_id();

// Check if item already in cart
if ($user_id) {
    $stmt = $db->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?");
    $stmt->execute([$user_id, $product_id, $size, $color]);
} else {
    $stmt = $db->prepare("SELECT * FROM cart WHERE session_id = ? AND product_id = ? AND size = ? AND color = ?");
    $stmt->execute([$session_id, $product_id, $size, $color]);
}

$existing = $stmt->fetch();

if ($existing) {
    // Update quantity
    $new_quantity = $existing['quantity'] + $quantity;
    $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->execute([$new_quantity, $existing['id']]);
} else {
    // Add new item
    if ($user_id) {
        $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, size, color, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $size, $color, $quantity]);
    } else {
        $stmt = $db->prepare("INSERT INTO cart (session_id, product_id, size, color, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$session_id, $product_id, $size, $color, $quantity]);
    }
}

jsonResponse(true, 'Product added to cart', ['cart_count' => getCartCount($db)]);
?>