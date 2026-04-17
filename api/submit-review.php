<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Please login to submit a review');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$user_id = getCurrentUserId();
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$review_title = sanitize($_POST['review_title'] ?? '');
$review_text = sanitize($_POST['review_text'] ?? '');

// Validation
if ($product_id <= 0) {
    jsonResponse(false, 'Invalid product');
}

if ($rating < 1 || $rating > 5) {
    jsonResponse(false, 'Rating must be between 1 and 5 stars');
}

if (empty($review_text)) {
    jsonResponse(false, 'Please write a review');
}

if (strlen($review_text) < 10) {
    jsonResponse(false, 'Review must be at least 10 characters');
}

try {
    // Check if user already reviewed this product
    $stmt = $db->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->fetch()) {
        jsonResponse(false, 'You have already reviewed this product');
    }

    // Check if user purchased this product (optional - can be disabled)
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'delivered'
    ");
    $stmt->execute([$user_id, $product_id]);
    $purchased = $stmt->fetch()['count'] > 0;

    // Insert review
    $stmt = $db->prepare("
        INSERT INTO product_reviews (user_id, product_id, rating, review_title, review_text, verified_purchase)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $product_id, $rating, $review_title, $review_text, $purchased ? 1 : 0]);

    jsonResponse(true, 'Review submitted successfully', [
        'review_id' => $db->lastInsertId(),
        'verified_purchase' => $purchased
    ]);

} catch (Exception $e) {
    error_log("Review submission error: " . $e->getMessage());
    jsonResponse(false, 'Failed to submit review. Please try again.');
}
?>