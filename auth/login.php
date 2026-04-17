<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = sanitize($_POST['identifier'] ?? ''); // Can be email or phone
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($identifier)) {
        $errors[] = 'Email or phone number is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        // 1. Check if it's an Admin login first
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $admin = $stmt->fetch();

        if ($admin && verifyPassword($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];

            // Update last login
            $updateStmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$admin['id']]);

            setFlashMessage('success', 'Admin login successful. Welcome to the portal!');
            redirect('/admin/dashboard.php');
        }

        // 2. Proceed with regular User login if not admin
        $isEmail = isValidEmail($identifier);

        if ($isEmail) {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE phone = ?");
        }

        $stmt->execute([$identifier]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];

            // Redirect to intended page or home
            $redirect_to = $_SESSION['redirect_after_login'] ?? '/';
            unset($_SESSION['redirect_after_login']);

            setFlashMessage('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($redirect_to);
        } else {
            $errors[] = 'Invalid credentials';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .auth-section {
        padding: 6rem 0;
        background-color: #f8fafc;
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
    }

    .auth-container {
        max-width: 460px;
        margin: 0 auto;
        width: 100%;
    }

    .auth-box {
        background: white;
        padding: 3rem;
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    }

    .auth-title {
        font-size: 2.25rem;
        font-weight: 800;
        text-align: center;
        margin-bottom: 0.5rem;
        letter-spacing: -0.025em;
    }

    .auth-subtitle {
        text-align: center;
        color: #64748b;
        margin-bottom: 2.5rem;
        font-size: 0.875rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: #334155;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #000;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
    }

    .error-list {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #991b1b;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
    }

    .error-list p {
        margin: 0;
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1.5rem 0;
        font-size: 0.875rem;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        color: #64748b;
    }

    .forgot-link {
        color: #000;
        font-weight: 600;
        text-decoration: none;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    .divider {
        text-align: center;
        margin: 2rem 0;
        position: relative;
    }

    .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e2e8f0;
    }

    .divider span {
        background: white;
        padding: 0 1rem;
        position: relative;
        color: #94a3b8;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .google-btn {
        width: 100%;
        padding: 0.75rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.2s;
        text-decoration: none;
    }

    .google-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .auth-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #f1f5f9;
        font-size: 0.875rem;
        color: #64748b;
    }

    .auth-footer a {
        color: #000;
        font-weight: 700;
        text-decoration: none;
    }

    .btn-login {
        width: 100%;
        padding: 0.875rem;
        background: #000;
        color: #fff;
        border: none;
        border-radius: 0.5rem;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .btn-login:hover {
        opacity: 0.9;
    }
</style>

<div class="auth-section">
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">Welcome</h1>
            <p class="auth-subtitle">Log in to your account or portal</p>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email or Phone</label>
                    <input type="text" name="identifier" class="form-input" required placeholder="name@example.com"
                        autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required placeholder="••••••••">
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="/auth/forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <a href="/auth/google-oauth.php" class="google-btn">
                <svg width="20" height="20" viewBox="0 0 18 18">
                    <path fill="#4285F4"
                        d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" />
                    <path fill="#34A853"
                        d="M9 18c2.43 0 4.467-.806 5.956-2.183l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" />
                    <path fill="#FBBC05"
                        d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707 0-.593.102-1.17.282-1.709V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.335z" />
                    <path fill="#EA4335"
                        d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" />
                </svg>
                Continue with Google
            </a>

            <div class="auth-footer">
                <p>New here? <a href="/auth/register.php">Create an account</a></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>