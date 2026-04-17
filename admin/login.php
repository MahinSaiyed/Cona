<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect to unified login page
redirect(SITE_URL . '/auth/login.php');
?>