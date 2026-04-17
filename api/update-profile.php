<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Please login first');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$user_id = getCurrentUserId();
$full_name = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');

if (empty($full_name) || empty($email) || empty($phone)) {
    jsonResponse(false, 'Please fill all required fields');
}

if (!isValidEmail($email)) {
    jsonResponse(false, 'Invalid email address');
}

if (!isValidPhone($phone)) {
    jsonResponse(false, 'Invalid phone number format');
}

try {
    $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $phone, $user_id]);

    // Update session data
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;

    jsonResponse(true, 'Profile updated successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to update profile: ' . $e->getMessage());
}
?>