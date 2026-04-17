<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['code'])) {
    setFlashMessage('error', 'Google authentication failed');
    redirect('/auth/login.php');
}

$code = $_GET['code'];

// Exchange code for access token
$token_params = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    setFlashMessage('error', 'Failed to get access token');
    redirect('/auth/login.php');
}

// Get user info
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token_data['access_token']]);
$user_info = curl_exec($ch);
curl_close($ch);

$user = json_decode($user_info, true);

if (!isset($user['id'])) {
    setFlashMessage('error', 'Failed to get user info');
    redirect('/auth/login.php');
}

// Check if user exists
$stmt = $db->prepare("SELECT * FROM users WHERE google_id = ?");
$stmt->execute([$user['id']]);
$existing_user = $stmt->fetch();

if ($existing_user) {
    // Login existing user
    $_SESSION['user_id'] = $existing_user['id'];
    $_SESSION['user_name'] = $existing_user['full_name'];
    $_SESSION['user_email'] = $existing_user['email'];

    setFlashMessage('success', 'Welcome back, ' . $existing_user['full_name'] . '!');
} else {
    // Create new user
    $stmt = $db->prepare("INSERT INTO users (google_id, email, full_name, profile_image, email_verified) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([
        $user['id'],
        $user['email'],
        $user['name'],
        $user['picture'] ?? null
    ]);

    $user_id = $db->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    setFlashMessage('success', 'Account created successfully! Welcome to ' . SITE_NAME);
}

redirect('/');
?>