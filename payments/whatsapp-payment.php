<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$order_id = intval($_GET['order_id'] ?? 0);

if (!isLoggedIn() || $order_id <= 0) {
    redirect('/');
}

// Get order details
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, getCurrentUserId()]);
$order = $stmt->fetch();

if (!$order) {
    redirect('/');
}

$whatsapp_number = str_replace(['+', ' ', '-'], '', WHATSAPP_NUMBER);
$message = urlencode("Hi! I would like to confirm payment for Order #" . $order['order_number'] . " totaling " . formatPrice($order['total']));
$whatsapp_link = "https://wa.me/{$whatsapp_number}?text={$message}";

include __DIR__ . '/../includes/header.php';
?>

<style>
    .payment-page {
        max-width: 600px;
        margin: 4rem auto;
        padding: 2rem;
        text-align: center;
    }

    .payment-box {
        background: white;
        padding: 3rem;
        box-shadow: var(--shadow-lg);
    }

    .whatsapp-btn {
        background: #25D366;
        color: white;
        padding: 1rem 2rem;
        font-size: 1.125rem;
        font-weight: bold;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin: 2rem 0;
    }

    .whatsapp-btn:hover {
        background: #128C7E;
        color: white;
    }
</style>

<div class="payment-page">
    <div class="payment-box">
        <h1 style="margin-bottom: 1rem;">💬 WhatsApp Payment</h1>
        <p style="margin-bottom: 2rem;">Order #
            <?php echo htmlspecialchars($order['order_number']); ?>
        </p>

        <div style="font-size: 2rem; font-weight: bold; margin: 2rem 0;">
            <?php echo formatPrice($order['total']); ?>
        </div>

        <div style="background: var(--color-light-gray); padding: 1.5rem; margin: 2rem 0; text-align: left;">
            <h3 style="margin-bottom: 1rem;">Payment Instructions:</h3>
            <ol style="margin-left: 1.5rem; line-height: 1.8;">
                <li>Click the WhatsApp button below</li>
                <li>Send us a message with your payment screenshot</li>
                <li>Our team will verify and confirm your order</li>
                <li>You'll receive order confirmation once payment is verified</li>
            </ol>
        </div>

        <a href="<?php echo $whatsapp_link; ?>" target="_blank" class="whatsapp-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
            </svg>
            Send Payment Confirmation
        </a>

        <div style="border-top: 1px solid var(--color-border); padding-top: 1.5rem; margin-top: 1.5rem;">
            <p style="font-size: 0.875rem; color: var(--color-gray);">
                Your order has been created with <strong>Pending Payment</strong> status.<br>
                It will be confirmed once we receive your payment.
            </p>
        </div>

        <div style="margin-top: 1.5rem;">
            <a href="/" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>