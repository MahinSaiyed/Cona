<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('/');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email) || !isValidEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        // In a real app, you would verify the email exists and send a reset link
        // For this demo/setup, we'll just show a success message
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'If an account exists with this email, you will receive a password reset link shortly.';
        } else {
            // We still show the same message for security reasons (to prevent email enumeration)
            $message = 'If an account exists with this email, you will receive a password reset link shortly.';
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
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 1.5rem;
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

    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #dcfce7;
        color: #166534;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #991b1b;
    }

    .btn-reset {
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

    .btn-reset:hover {
        opacity: 0.9;
    }

    .auth-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #f1f5f9;
        font-size: 0.875rem;
    }

    .auth-footer a {
        color: #000;
        font-weight: 700;
        text-decoration: none;
    }
</style>

<div class="auth-section">
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password.</p>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!$message): ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" required placeholder="name@example.com"
                            autofocus>
                    </div>

                    <button type="submit" class="btn-reset">Send Reset Link</button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Remember your password? <a href="/auth/login.php">Back to Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>