<?php
require_once __DIR__ . '/../config/config.php';

// Destroy session
session_start();
session_destroy();

// Redirect to home
header('Location: /');
exit();
?>