<?php
/**
 * Email Helper Functions
 * Functions for sending various types of emails
 */

require_once __DIR__ . '/../config/email.php';

/**
 * Send an email
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $html_body HTML email body
 * @param string $text_body Plain text email body (optional)
 * @return bool Success status
 */
function sendEmail($to, $subject, $html_body, $text_body = '')
{
    if (!EMAILS_ENABLED) {
        error_log("Email sending disabled. Would have sent to: $to, Subject: $subject");
        return true; // Return true to not break workflows during development
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . EMAIL_FROM_NAME . ' <' . EMAIL_FROM_ADDRESS . '>',
        'Reply-To: ' . EMAIL_FROM_ADDRESS,
        'X-Mailer: PHP/' . phpversion()
    ];

    try {
        $success = mail($to, $subject, $html_body, implode("\r\n", $headers));

        if (!$success) {
            error_log("Failed to send email to: $to, Subject: $subject");
        }

        return $success;
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}

/**
 * Load and process an email template
 */
function loadEmailTemplate($template_name, $variables = [])
{
    $template_file = EMAIL_TEMPLATES_DIR . '/' . $template_name . '.html';

    if (!file_exists($template_file)) {
        error_log("Email template not found: $template_file");
        return false;
    }

    $template = file_get_contents($template_file);

    // Replace variables in template
    foreach ($variables as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }

    return $template;
}

/**
 * Send order confirmation email
 */
function sendOrderConfirmationEmail($order_id, $customer_email, $customer_name)
{
    global $db;

    // Fetch order details
    $stmt = $db->prepare("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        return false;
    }

    // Fetch order items
    $stmt = $db->prepare("
        SELECT oi.*, p.name as product_name, p.brand
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Build items HTML
    $items_html = '';
    foreach ($items as $item) {
        $items_html .= '<tr>
            <td style="padding: 10px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($item['product_name']) . '</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">' . $item['quantity'] . '</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">₹' . number_format($item['price'], 2) . '</td>
        </tr>';
    }

    $variables = [
        'CUSTOMER_NAME' => htmlspecialchars($customer_name),
        'ORDER_NUMBER' => $order['order_number'],
        'ORDER_DATE' => date('F j, Y', strtotime($order['created_at'])),
        'ORDER_ITEMS' => $items_html,
        'SUBTOTAL' => '₹' . number_format($order['subtotal'], 2),
        'SHIPPING' => '₹' . number_format($order['shipping_cost'], 2),
        'TOTAL' => '₹' . number_format($order['total'], 2),
        'SHIPPING_ADDRESS' => nl2br(htmlspecialchars(
            $order['shipping_address_line1'] . "\n" .
            ($order['shipping_address_line2'] ? $order['shipping_address_line2'] . "\n" : '') .
            $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_pincode']
        )),
        'TRACK_URL' => BASE_URL . '/pages/order-details.php?id=' . $order['id'],
        'SITE_NAME' => SITE_NAME,
        'SITE_URL' => BASE_URL
    ];

    $html = loadEmailTemplate('order-confirmation', $variables);

    if ($html) {
        return sendEmail($customer_email, 'Order Confirmation - ' . $order['order_number'], $html);
    }

    return false;
}

/**
 * Send order status update email
 */
function sendOrderStatusUpdateEmail($order_id, $new_status)
{
    global $db;

    $stmt = $db->prepare("SELECT o.*, u.email, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        return false;
    }

    $status_messages = [
        'pending' => 'Your order has been received and is being processed.',
        'processing' => 'We are preparing your order for shipment.',
        'shipped' => 'Your order has been shipped and is on its way!',
        'delivered' => 'Your order has been delivered. Thank you for shopping with us!',
        'cancelled' => 'Your order has been cancelled.'
    ];

    $variables = [
        'CUSTOMER_NAME' => htmlspecialchars($order['full_name']),
        'ORDER_NUMBER' => $order['order_number'],
        'STATUS' => ucfirst($new_status),
        'STATUS_MESSAGE' => $status_messages[$new_status] ?? 'Your order status has been updated.',
        'TRACK_URL' => BASE_URL . '/pages/order-details.php?id=' . $order['id'],
        'SITE_NAME' => SITE_NAME,
        'SITE_URL' => BASE_URL
    ];

    $html = loadEmailTemplate('order-status-update', $variables);

    if ($html) {
        return sendEmail($order['email'], 'Order Update - ' . $order['order_number'], $html);
    }

    return false;
}

/**
 * Send welcome email to new users
 */
function sendWelcomeEmail($email, $name)
{
    $variables = [
        'CUSTOMER_NAME' => htmlspecialchars($name),
        'SITE_NAME' => SITE_NAME,
        'SITE_URL' => BASE_URL,
        'SHOP_URL' => BASE_URL
    ];

    $html = loadEmailTemplate('welcome', $variables);

    if ($html) {
        return sendEmail($email, 'Welcome to ' . SITE_NAME, $html);
    }

    return false;
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $reset_token, $name = '')
{
    $reset_url = BASE_URL . '/auth/reset-password.php?token=' . urlencode($reset_token);

    $variables = [
        'CUSTOMER_NAME' => $name ? htmlspecialchars($name) : 'Customer',
        'RESET_URL' => $reset_url,
        'EXPIRY_TIME' => '1 hour',
        'SITE_NAME' => SITE_NAME,
        'SITE_URL' => BASE_URL
    ];

    $html = loadEmailTemplate('password-reset', $variables);

    if ($html) {
        return sendEmail($email, 'Password Reset Request - ' . SITE_NAME, $html);
    }

    return false;
}
?>