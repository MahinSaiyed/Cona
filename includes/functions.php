<?php
// Helper Functions

// Sanitize input
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number (Indian format)
function isValidPhone($phone)
{
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

// Hash password
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// Generate unique order number
function generateOrderNumber()
{
    return 'ORD-' . time() . '-' . rand(1000, 9999);
}

// Format price in Indian Rupees
function formatPrice($amount)
{
    return '₹' . number_format($amount, 0);
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Check if admin is logged in
function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

// Redirect function
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

// Flash message functions
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Upload image
function uploadImage($file, $path = PRODUCT_IMAGE_PATH)
{
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $path . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Upload failed'];
}

// Get cart count
function getCartCount($db)
{
    $user_id = getCurrentUserId();
    $session_id = session_id();

    if ($user_id) {
        $stmt = $db->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $db->prepare("SELECT SUM(quantity) as count FROM cart WHERE session_id = ?");
        $stmt->execute([$session_id]);
    }

    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Get wishlist count
function getWishlistCount($db)
{
    if (!isLoggedIn()) {
        return 0;
    }

    $stmt = $db->prepare("SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?");
    $stmt->execute([getCurrentUserId()]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Generate slug from string
function generateSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Get user data
function getUserData($db, $user_id)
{
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Calculate shipping cost
function calculateShipping($subtotal)
{
    if ($subtotal >= FREE_SHIPPING_THRESHOLD) {
        return 0;
    }
    return SHIPPING_COST;
}

// Calculate tax
function calculateTax($subtotal)
{
    return $subtotal * TAX_RATE;
}

// JSON response helper
function jsonResponse($success, $message = '', $data = [])
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Get site setting from database
function getSetting($db, $key, $default = '')
{
    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Get settings by group
function getSettingsByGroup($db, $group)
{
    try {
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = ?");
        $stmt->execute([$group]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        return [];
    }
}

// Get uploaded/local/external product image URL
function getProductImageUrl($image)
{
    $image = trim((string) $image);

    if ($image === '') {
        return ASSETS_URL . '/images/placeholder.jpg';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    return UPLOAD_URL . '/products/' . rawurlencode($image);
}

// Get order details with customer and shipping information
function getOrderDetails($db, $order_id, $user_id = null)
{
    $query = "
        SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
               a.full_name as shipping_name, a.phone as shipping_phone,
               a.address_line1, a.address_line2, a.city, a.state, a.pincode, a.country
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN addresses a ON o.address_id = a.id
        WHERE o.id = ?
    ";
    $params = [$order_id];

    if ($user_id !== null) {
        $query .= " AND o.user_id = ?";
        $params[] = $user_id;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Get order items
function getOrderItems($db, $order_id)
{
    $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}
?>
