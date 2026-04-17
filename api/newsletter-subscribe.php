<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$email = sanitize($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address');
}

try {
    // Check if email already exists
    $stmt = $db->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $subscriber = $stmt->fetch();

    if ($subscriber) {
        if ($subscriber['is_active']) {
            jsonResponse(false, 'You are already subscribed to our newsletter');
        } else {
            // Reactivate subscription
            $stmt = $db->prepare("UPDATE newsletter_subscribers SET is_active = 1 WHERE id = ?");
            $stmt->execute([$subscriber['id']]);
            jsonResponse(true, 'Welcome back! You have been resubscribed.');
        }
    } else {
        // Create new subscription
        $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);

        // Optional: Send welcome email (if email functions exist)
        if (file_exists(__DIR__ . '/../includes/email-functions.php')) {
            require_once __DIR__ . '/../includes/email-functions.php';
            // Simple generic welcome or specific newsletter welcome
            // sendNewsletterWelcomeEmail($email); 
        }

        jsonResponse(true, 'Thanks for subscribing!');
    }

} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred. Please try again later.');
}
?>