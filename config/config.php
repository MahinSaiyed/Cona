<?php
// Application Configuration

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_NAME', 'Cona Store');
define('SITE_URL', 'http://localhost:8000');
define('ADMIN_EMAIL', 'admin@gmail.com');

// Path configuration
define('BASE_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('PRODUCT_IMAGE_PATH', UPLOAD_PATH . '/products');

// URL paths
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOAD_URL', SITE_URL . '/uploads');

// Create upload directories if they don't exist
$directories = [UPLOAD_PATH, PRODUCT_IMAGE_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/auth/google-callback.php');

// Razorpay Configuration (for card payments)
define('RAZORPAY_KEY_ID', 'YOUR_RAZORPAY_KEY_ID_HERE');
define('RAZORPAY_KEY_SECRET', 'YOUR_RAZORPAY_KEY_SECRET_HERE');

// WhatsApp Configuration
define('WHATSAPP_NUMBER', '+919876543210'); // Replace with your business WhatsApp number
define('WHATSAPP_MESSAGE', 'Hello! I would like to confirm my payment for Order #');

// Pagination
define('PRODUCTS_PER_PAGE', 12);

// Shipping configuration
define('FREE_SHIPPING_THRESHOLD', 1999);
define('SHIPPING_COST', 99);

// Tax configuration
define('TAX_RATE', 0); // 0% for now, can be changed to 0.18 for 18% GST

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>