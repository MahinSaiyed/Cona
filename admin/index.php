<?php
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: /admin/dashboard.php');
} else {
    header('Location: /admin/login.php');
}
exit();
?>