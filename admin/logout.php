<?php
require_once __DIR__ . '/../config/config.php';

session_start();
session_destroy();
header('Location: /admin/login.php');
exit();
?>