<?php
/**
 * Email Configuration
 * Configure your SMTP or mail settings here
 */

// Email sending method: 'smtp' or 'mail' (PHP mail function)
define('EMAIL_METHOD', 'mail'); // Change to 'smtp' for production

// SMTP Settings (if using EMAIL_METHOD = 'smtp')
define('SMTP_HOST', 'smtp.gmail.com'); // Your SMTP server
define('SMTP_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_USERNAME', 'your-email@gmail.com'); // SMTP username
define('SMTP_PASSWORD', 'your-app-password'); // SMTP password or app password
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// From Email Settings
define('EMAIL_FROM_ADDRESS', 'noreply@' . str_replace('www.', '', $_SERVER['HTTP_HOST'] ?? 'yourdomain.com'));
define('EMAIL_FROM_NAME', SITE_NAME);

// Email Templates Directory
define('EMAIL_TEMPLATES_DIR', __DIR__ . '/../email/templates');

// Enable/Disable Emails
define('EMAILS_ENABLED', true); // Set to false to disable all emails (useful for development)
?>