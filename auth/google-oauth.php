<?php
/*
 * Google OAuth Integration
 * 
 * SETUP INSTRUCTIONS:
 * 1. Go to https://console.cloud.google.com/
 * 2. Create a new project or select existing
 * 3. Enable Google+ API
 * 4. Create OAuth 2.0 credentials
 * 5. Add authorized redirect URI: http://localhost:8000/auth/google-callback.php
 * 6. Update GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in config/config.php
 */

require_once __DIR__ . '/../config/config.php';

// Redirect to Google OAuth
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online'
];

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $auth_url);
exit();
?>