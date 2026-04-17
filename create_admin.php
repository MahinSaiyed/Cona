<!DOCTYPE html>
<html>

<head>
    <title>Create Admin Account</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #f1f5f9;
            margin: 0;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .success {
            color: #166534;
            background: #f0fdf4;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            border: 1px solid #dcfce7;
        }

        .error {
            color: #991b1b;
            background: #fef2f2;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            border: 1px solid #fee2e2;
        }

        .btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Admin Account Setup</h2>
        <?php
        require_once __DIR__ . '/config/config.php';
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/includes/functions.php';

        $username = 'admin';
        $password = 'admin123';
        $email = 'admin@example.com';
        $full_name = 'Super Admin';

        try {
            $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                echo "<div class='error'>Error: Admin account already exists.</div>";
            } else {
                $hashed_password = hashPassword($password);
                $stmt = $db->prepare("INSERT INTO admin_users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')");
                $stmt->execute([$username, $email, $hashed_password, $full_name]);

                echo "<div class='success'>
                <strong>Success!</strong> Admin account created.<br><br>
                <strong>Username:</strong> $username<br>
                <strong>Password:</strong> $password
              </div>";
            }
            echo "<a href='admin/login.php' class='btn'>Go to Login Page</a>";
            echo "<p style='color:#ef4444; font-size: 0.875rem; margin-top: 1.5rem;'><strong>Notice:</strong> Please delete this file (create_admin.php) from your project folder now.</p>";

        } catch (PDOException $e) {
            echo "<div class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
    </div>
</body>

</html>