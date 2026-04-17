<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Please login first');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$product_id = intval($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    jsonResponse(false, 'Invalid product');
}

$user_id = getCurrentUserId();

// Check if already in wishlist
$stmt = $db->prepare("SELECT * FROM wishlists WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$existing = $stmt->fetch();

if ($existing) {
    // Remove from wishlist
    $stmt = $db->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    jsonResponse(true, 'Removed from wishlist', ['action' => 'removed']);
} else {
    // Add to wishlist
    $stmt = $db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
    jsonResponse(true, 'Added to wishlist', ['action' => 'added']);
}
?>