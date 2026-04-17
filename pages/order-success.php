<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$order_number = sanitize($_GET['order_number'] ?? '');

if (empty($order_number)) {
    redirect('/');
}

// Get order details
$user_id = getCurrentUserId();
$stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$order_number, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('/');
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .success-page {
        padding: 6rem 0;
        text-align: center;
        background-color: #f8fafc;
        min-height: calc(100vh - 400px);
        display: flex;
        align-items: center;
    }

    .success-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 4rem 2rem;
        border-radius: 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .success-icon {
        width: 80px;
        height: 80px;
        background: #22c55e;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 3rem;
        animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes scaleIn {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .success-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        color: #1e293b;
    }

    .order-number-box {
        background: #f1f5f9;
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        display: inline-block;
        margin: 1.5rem 0;
        font-weight: 700;
        font-family: monospace;
        font-size: 1.25rem;
        color: #334155;
    }

    .success-message {
        color: #64748b;
        margin-bottom: 2.5rem;
        line-height: 1.6;
    }

    .success-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #000;
        color: #fff;
    }

    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #fff;
        color: #000;
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        transform: translateY(-2px);
    }
</style>

<main class="success-page">
    <div class="container">
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h1 class="success-title">Order Placed!</h1>
            <p class="success-message">
                Thank you for your purchase. We've received your order and we'll start processing it right away.
            </p>
            
            <div class="order-number-box">
                Order #<?php echo htmlspecialchars($order['order_number']); ?>
            </div>

            <p style="margin-bottom: 2.5rem; font-size: 0.875rem; color: #94a3b8;">
                A confirmation email has been sent to your registered email address.
            </p>

            <div class="success-actions">
                <a href="/invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Invoice</a>
                <a href="/invoice.php?id=<?php echo $order['id']; ?>&download=1" class="btn btn-secondary">Download Invoice</a>
                <a href="/" class="btn btn-primary">Continue Shopping</a>
                <a href="/pages/account.php" class="btn btn-secondary">View Orders</a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
