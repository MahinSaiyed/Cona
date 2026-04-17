<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = "Contact Us - " . SITE_NAME;

// Fetch settings
$contact_settings = getSettingsByGroup($db, 'contact');
$address = $contact_settings['store_address'] ?? '123 Fashion Street, Suite 456, Mumbai, MH 400001';
$phone = $contact_settings['store_phone'] ?? WHATSAPP_NUMBER;
$email = $contact_settings['support_email'] ?? ADMIN_EMAIL;
$hours = $contact_settings['opening_hours'] ?? 'Mon - Sat: 10:00 AM - 8:00 PM | Sun: 11:00 AM - 6:00 PM';

$success_msg = '';
$error_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = sanitize($_POST['name'] ?? '');
    $user_email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($user_email) || empty($subject) || empty($message)) {
        $error_msg = "All fields are required.";
    } elseif (!isValidEmail($user_email)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $user_email, $subject, $message])) {
                $success_msg = "Thank you! Your message has been sent successfully. We'll get back to you soon.";
            } else {
                $error_msg = "Something went wrong. Please try again later.";
            }
        } catch (Exception $e) {
            $error_msg = "An error occurred. Please try again later.";
            error_log($e->getMessage());
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<main class="container" style="padding: 4rem 2rem;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 4rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Get in Touch</h1>
            <p style="color: var(--color-gray);">Have a question or feedback? We'd love to hear from you.</p>
        </div>

        <?php if ($success_msg): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 4px; border: 1px solid #c8e6c9; margin-bottom: 2rem; text-align: center;">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 4px; border: 1px solid #ffcdd2; margin-bottom: 2rem; text-align: center;">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
            <!-- Contact Information -->
            <div>
                <h2 style="font-size: 1.5rem; margin-bottom: 2rem;">Contact Information</h2>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Address</h3>
                    <p style="color: var(--color-gray);"><?php echo nl2br(htmlspecialchars($address)); ?></p>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Phone</h3>
                    <p style="color: var(--color-gray);"><?php echo htmlspecialchars($phone); ?></p>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Email</h3>
                    <p style="color: var(--color-gray);"><?php echo htmlspecialchars($email); ?></p>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Opening Hours</h3>
                    <p style="color: var(--color-gray);"><?php echo nl2br(htmlspecialchars($hours)); ?></p>
                </div>
            </div>

            <!-- Contact Form -->
            <div>
                <form action="" method="POST" style="background: var(--color-white); padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid var(--color-light-gray);">
                    <div style="margin-bottom: 1.5rem;">
                        <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Name</label>
                        <input type="text" id="name" name="name" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email</label>
                        <input type="email" id="email" name="email" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="subject" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Subject</label>
                        <select id="subject" name="subject" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Order Status">Order Status</option>
                            <option value="Returns & Exchanges">Returns & Exchanges</option>
                            <option value="Feedback">Feedback</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Message</label>
                        <textarea id="message" name="message" rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="submit_contact" class="btn btn-primary" style="width: 100%; padding: 1rem;">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
