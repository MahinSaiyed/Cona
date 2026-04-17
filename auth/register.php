<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $register_type = $_POST['register_type'] ?? 'email';

    $errors = [];

    // Validation
    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }

    if ($register_type === 'email') {
        if (empty($email) || !isValidEmail($email)) {
            $errors[] = 'Valid email is required';
        }

        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered';
        }
    } else if ($register_type === 'phone') {
        if (empty($phone) || !isValidPhone($phone)) {
            $errors[] = 'Valid 10-digit phone number is required';
        }

        // Check if phone exists
        $stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $errors[] = 'Phone number already registered';
        }
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if (empty($errors)) {
        $hashed_password = hashPassword($password);

        if ($register_type === 'email') {
            $stmt = $db->prepare("INSERT INTO users (email, password, full_name) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashed_password, $full_name]);
        } else {
            $stmt = $db->prepare("INSERT INTO users (phone, password, full_name) VALUES (?, ?, ?)");
            $stmt->execute([$phone, $hashed_password, $full_name]);
        }

        $user_id = $db->lastInsertId();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $full_name;

        setFlashMessage('success', 'Registration successful! Welcome to ' . SITE_NAME);
        redirect('/');
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

    .tab-buttons {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        background: #f1f5f9;
        padding: 0.25rem;
        border-radius: 0.5rem;
    }

    .tab-btn {
        flex: 1;
        padding: 0.625rem;
        background: transparent;
        border: none;
        border-radius: 0.375rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tab-btn.active {
        background: white;
        color: #000;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

    .btn-register {
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

    .btn-register:hover {
        opacity: 0.9;
    }
</style>

<div class="auth-section">
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join us and start shopping</p>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="tab-buttons">
                <button class="tab-btn active" id="tab-email" onclick="switchTab('email')">Email</button>
                <button class="tab-btn" id="tab-phone" onclick="switchTab('phone')">Phone</button>
            </div>

            <form method="POST">
                <input type="hidden" name="register_type" id="register_type" value="email">

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-input" required placeholder="John Doe">
                </div>

                <div class="form-group" id="email-field">
                    <label class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="name@example.com">
                </div>

                <div class="form-group" id="phone-field" style="display: none;">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" placeholder="10-digit mobile number">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-register">Register</button>
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
                <p>Already have an account? <a href="/auth/login.php">Log in</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(type) {
        const emailField = document.getElementById('email-field');
        const phoneField = document.getElementById('phone-field');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const registerType = document.getElementById('register_type');
        const tabEmail = document.getElementById('tab-email');
        const tabPhone = document.getElementById('tab-phone');

        if (type === 'email') {
            emailField.style.display = 'block';
            phoneField.style.display = 'none';
            emailInput.required = true;
            phoneInput.required = false;
            registerType.value = 'email';
            tabEmail.classList.add('active');
            tabPhone.classList.remove('active');
        } else {
            emailField.style.display = 'none';
            phoneField.style.display = 'block';
            emailInput.required = false;
            phoneInput.required = true;
            registerType.value = 'phone';
            tabEmail.classList.remove('active');
            tabPhone.classList.add('active');
        }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>