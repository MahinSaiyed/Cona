<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h1>Force Admin Reset</h1>";

try {
    // 1. Wipe existing admins to avoid conflicts
    $db->exec("DELETE FROM admin_users");
    echo "<p style='color:orange;'>Cleaned existing admin users.</p>";

    // 2. Insert Fresh Admin
    $username = 'admin';
    $password = 'admin123';
    $email = 'admin@example.com';
    $full_name = 'Super Admin';
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare("INSERT INTO admin_users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')");
    $stmt->execute([$username, $email, $hashed, $full_name]);

    echo "<p style='color:green;'>✅ Fresh Admin Created Successfully!</p>";
    echo "<ul>
            <li>Username: <b>admin</b></li>
            <li>Password: <b>admin123</b></li>
          </ul>";

    // 3. Immediate Verification Test
    $stmt = $db->prepare("SELECT password FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['password'])) {
        echo "<p style='color:green;'>✅ Verification Test PASSED in this script.</p>";
    } else {
        echo "<p style='color:red;'>❌ Verification Test FAILED. Check PHP BCRYPT support.</p>";
    }

    echo "<p>Now try logging in again: <a href='admin/login.php'>Login Page</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>