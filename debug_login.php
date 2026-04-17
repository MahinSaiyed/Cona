<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h1>Admin Login Debug</h1>";

// 1. Check Database Connection
try {
    $db->query("SELECT 1");
    echo "<p style='color:green;'>✅ Database connection successful.</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Check Tables
try {
    $db->query("SELECT 1 FROM admin_users LIMIT 1");
    echo "<p style='color:green;'>✅ Table 'admin_users' exists.</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Table 'admin_users' NO EXIST. Run schema.sql.</p>";
    exit;
}

// 3. Check Admin User
$username = 'admin';
$stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin) {
    echo "<p style='color:green;'>✅ Admin user '$username' found.</p>";
    echo "<ul>";
    echo "<li>Full Name: " . $admin['full_name'] . "</li>";
    echo "<li>Email: " . $admin['email'] . "</li>";
    echo "<li>Password Hash in DB: <code>" . $admin['password'] . "</code></li>";
    echo "</ul>";

    // 4. Test Password Hashing
    $test_pass = 'admin123';
    if (password_verify($test_pass, $admin['password'])) {
        echo "<p style='color:green;'>✅ Password verification test for 'admin123' PASSED.</p>";
    } else {
        echo "<p style='color:red;'>❌ Password verification test for 'admin123' FAILED.</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Admin user '$username' NOT FOUND. Run create_admin.php first.</p>";
}

// 5. Session Test
$_SESSION['debug_test'] = 'working';
if (isset($_SESSION['debug_test']) && $_SESSION['debug_test'] === 'working') {
    echo "<p style='color:green;'>✅ PHP Sessions are working correctly.</p>";
} else {
    echo "<p style='color:red;'>❌ PHP Sessions NOT working. Check server configuration.</p>";
}
?>