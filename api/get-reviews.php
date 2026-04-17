<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$product_id = intval($_GET['product_id'] ?? 0);
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

if ($product_id <= 0) {
    jsonResponse(false, 'Invalid product ID');
}

try {
    // Get average rating and total reviews
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            COALESCE(AVG(rating), 0) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM product_reviews
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $summary = $stmt->fetch();

    // Get reviews with user details
    $stmt = $db->prepare("
        SELECT 
            pr.*,
            u.full_name,
            u.email
        FROM product_reviews pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.product_id = ?
        ORDER BY pr.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$product_id, $limit, $offset]);
    $reviews = $stmt->fetchAll();

    // Format reviews for display
    $formatted_reviews = array_map(function ($review) {
        return [
            'id' => $review['id'],
            'rating' => intval($review['rating']),
            'title' => $review['review_title'],
            'text' => $review['review_text'],
            'author' => $review['full_name'],
            'verified_purchase' => (bool) $review['verified_purchase'],
            'helpful_count' => intval($review['helpful_count']),
            'created_at' => date('F j, Y', strtotime($review['created_at']))
        ];
    }, $reviews);

    jsonResponse(true, 'Reviews loaded', [
        'summary' => [
            'total_reviews' => intval($summary['total_reviews']),
            'average_rating' => round($summary['average_rating'], 1),
            'rating_distribution' => [
                5 => intval($summary['five_star']),
                4 => intval($summary['four_star']),
                3 => intval($summary['three_star']),
                2 => intval($summary['two_star']),
                1 => intval($summary['one_star'])
            ]
        ],
        'reviews' => $formatted_reviews,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($summary['total_reviews'] / $limit),
            'per_page' => $limit
        ]
    ]);

} catch (Exception $e) {
    error_log("Get reviews error: " . $e->getMessage());
    jsonResponse(false, 'Failed to load reviews');
}
?>