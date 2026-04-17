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
$phone = sanitize($_POST['phone'] ?? '');
$address_line1 = sanitize($_POST['address_line1'] ?? '');
$address_line2 = sanitize($_POST['address_line2'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$state = sanitize($_POST['state'] ?? '');
$pincode = sanitize($_POST['pincode'] ?? '');

if (empty($full_name) || empty($phone) || empty($address_line1) || empty($city) || empty($state) || empty($pincode)) {
    jsonResponse(false, 'Please fill all required fields');
}

try {
    $stmt = $db->prepare("
        INSERT INTO addresses (user_id, full_name, phone, address_line1, address_line2, city, state, pincode)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $full_name, $phone, $address_line1, $address_line2, $city, $state, $pincode]);
    $address_id = $db->lastInsertId();

    jsonResponse(true, 'Address added successfully', ['address_id' => $address_id]);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to add address: ' . $e->getMessage());
}
?>