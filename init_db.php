<?php
$host = "127.0.0.1;port=3307";
$username = "root";
$password = "";
$dbname = "superkicks_db_new";

try {
    // Connect without database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' created or already exists.\n";
    
    // Select the database
    $conn->exec("USE `$dbname`");
    
    // Now include setup.php to run the rest of the installation
    // However, setup.php already includes config/database.php which will try to connect to the DB
    // Since we created it, setup.php should work now.
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
