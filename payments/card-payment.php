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
</style>

<div class="payment-page">
    <div class="payment-box">
        <h1 style="margin-bottom: 1rem;">💳 Card Payment</h1>
        <p style="margin-bottom: 2rem;">Order #
            <?php echo htmlspecialchars($order['order_number']); ?>
        </p>

        <div style="font-size: 2rem; font-weight: bold; margin: 2rem 0;">
            <?php echo formatPrice($order['total']); ?>
        </div>

        <div style="background: var(--color-light-gray); padding: 1.5rem; margin: 2rem 0; text-align: left;">
            <h3 style="margin-bottom: 1rem;">Razorpay Integration</h3>
            <p style="font-size: 0.875rem; color: var(--color-gray); margin-bottom: 1rem;">
                To enable card payments, you need to:
            </p>
            <ol style="font-size: 0.875rem; color: var(--color-gray); margin-left: 1.5rem;">
                <li>Sign up at <a href="https://razorpay.com" target="_blank">Razorpay.com</a></li>
                <li>Get your API Key ID and Key Secret</li>
                <li>Update credentials in <code>config/config.php</code></li>
                <li>This page will automatically show the payment gateway</li>
            </ol>
        </div>

        <button class="btn btn-primary" style="margin-bottom: 1rem;" onclick="simulatePayment()">
            Simulate Successful Payment (Demo)
        </button>

        <div>
            <a href="/pages/cart.php" style="color: var(--color-gray); text-decoration: underline;">Cancel and return to
                cart</a>
        </div>
    </div>
</div>

<script>
    function simulatePayment() {
        if (confirm('This is a demo. Simulate successful payment?')) {
            // Update payment status
            fetch('/api/update-payment-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'order_id=<?php echo $order_id; ?>&status=paid'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/pages/order-success.php?order_number=<?php echo $order['order_number']; ?>';
                    } else {
                        alert('Payment failed');
                    }
                });
        }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>