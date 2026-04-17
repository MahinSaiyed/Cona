<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user_id = getCurrentUserId();
$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    redirect('/pages/account.php');
}

// Fetch order details for the current user
$order = getOrderDetails($db, $order_id, $user_id);

if (!$order) {
    redirect('/pages/account.php');
}

// Fetch order items
$items = getOrderItems($db, $order_id);

include __DIR__ . '/../includes/header.php';
?>

<style>
    .order-details-page { padding: 3rem 0; }
    .order-card { background: white; border: 1px solid var(--color-border); padding: 2rem; margin-bottom: 2rem; }
    .order-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); gap: 1rem; flex-wrap: wrap; }
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
    .status-pending { background: #fff3e0; color: #e65100; }
    .status-confirmed-processing { background: #e3f2fd; color: #1565c0; }
    .status-shipped { background: #e0f7fa; color: #00695c; }
    .status-delivered { background: #e8f5e9; color: #2e7d32; }
    .status-cancelled { background: #ffebee; color: #c62828; }
    
    .items-list { margin-bottom: 2rem; }
    .item-row { display: flex; gap: 1.5rem; padding: 1.5rem 0; border-bottom: 1px solid var(--color-border); }
    .item-image { width: 100px; height: 100px; object-fit: cover; }
    .item-info { flex: 1; }
    
    .order-footer { display: grid; grid-template-columns: 1fr 300px; gap: 4rem; margin-top: 2rem; }
    .address-section h3 { margin-bottom: 1rem; font-size: 1.125rem; }
    .summary-section { background: var(--color-light-gray); padding: 1.5rem; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 0.75rem; }
    .summary-total { font-weight: bold; font-size: 1.25rem; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--color-border); border-bottom: none; }
    .order-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
</style>

<main class="order-details-page">
    <div class="container">
        <div style="margin-bottom: 2rem;">
            <a href="/pages/account.php#orders" style="color: var(--color-gray); text-decoration: none;">&larr; Back to My Orders</a>
            <h1 style="margin-top: 1rem;">Order Details</h1>
        </div>

        <div class="order-card">
            <div class="order-header">
                <div>
                    <h2 style="font-size: 1.5rem;">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                    <p style="color: var(--color-gray);">Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="order-actions">
                    <a href="/invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Invoice</a>
                    <a href="/invoice.php?id=<?php echo $order['id']; ?>&download=1" class="btn btn-primary">Download Invoice</a>
                    <span class="status-badge status-<?php echo str_replace(' ', '-', $order['order_status']); ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </div>
            </div>

            <div class="items-list">
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <img src="<?php echo htmlspecialchars(getProductImageUrl($item['product_image'])); ?>" class="item-image">
                        <div class="item-info">
                            <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p style="color: var(--color-gray); font-size: 0.875rem;">
                                Size: <?php echo $item['size'] ?: 'N/A'; ?> | Color: <?php echo $item['color'] ?: 'N/A'; ?>
                            </p>
                            <p style="margin-top: 0.5rem;">Quantity: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div style="text-align: right; font-weight: bold;">
                            <?php echo formatPrice($item['subtotal']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-footer">
                <div class="address-section">
                    <h3>Shipping Address</h3>
                    <?php if ($order['address_id']): ?>
                        <p><strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($order['address_line1']); ?></p>
                        <?php if ($order['address_line2']): ?>
                            <p><?php echo htmlspecialchars($order['address_line2']); ?></p>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['pincode']); ?></p>
                        <p><?php echo htmlspecialchars($order['country']); ?></p>
                        <p style="margin-top: 0.5rem;">Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                    <?php else: ?>
                        <p>No address information available.</p>
                    <?php endif; ?>

                    <h3 style="margin-top: 2rem;">Payment Method</h3>
                    <p><?php echo strtoupper($order['payment_method']); ?> (<?php echo ucfirst($order['payment_status']); ?>)</p>
                </div>

                <div class="summary-section">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo formatPrice($order['subtotal']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?php echo formatPrice($order['shipping_cost']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax</span>
                        <span><?php echo formatPrice($order['tax']); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span style="color: var(--color-red);"><?php echo formatPrice($order['total']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
