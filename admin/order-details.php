<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) {
    redirect('/admin/orders.php');
}

// Fetch order details with user and address info
$order = getOrderDetails($db, $order_id);

if (!$order) {
    redirect('/admin/orders.php');
}

// Fetch order items
$items = getOrderItems($db, $order_id);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['order_status'] ?? '');
    if (in_array($new_status, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        $stmt = $db->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        setFlashMessage('success', 'Order status updated successfully');
        header("Location: order-details.php?id=" . $order_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Order #
        <?php echo $order['order_number']; ?> - Admin
    </title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .admin-sidebar {
            background: var(--color-black);
            color: white;
            padding: 2rem 0;
        }

        .admin-menu {
            list-style: none;
        }

        .admin-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
        }

        .admin-menu a:hover,
        .admin-menu a.active {
            background: var(--color-gray);
        }

        .admin-content {
            padding: 2rem;
            background: var(--color-light-gray);
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 0.5rem;
        }

        .item-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--color-border);
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .item-info {
            flex: 1;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .detail-label {
            color: var(--color-gray);
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Order #
                    <?php echo $order['order_number']; ?>
                </h1>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="/invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary" target="_blank">View Invoice</a>
                    <a href="/invoice.php?id=<?php echo $order['id']; ?>&download=1" class="btn btn-primary">Download Invoice</a>
                    <a href="/admin/orders.php" class="btn btn-secondary">Back to Orders</a>
                </div>
            </div>

            <?php $flash = getFlashMessage();
            if ($flash): ?>
                <div style="background: #e8f5e9; padding: 1rem; margin-bottom: 1rem; color: #2e7d32;">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="order-details-grid">
                <div class="left-col">
                    <div class="card">
                        <div class="card-header">Order Items</div>
                        <?php foreach ($items as $item): ?>
                            <div class="item-row">
                                <img src="<?php echo htmlspecialchars(getProductImageUrl($item['product_image'])); ?>"
                                    class="item-image">
                                <div class="item-info">
                                    <div style="font-weight: bold;">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </div>
                                    <div style="color: var(--color-gray); font-size: 0.875rem;">
                                        Size:
                                        <?php echo $item['size'] ?: 'N/A'; ?> | Color:
                                        <?php echo $item['color'] ?: 'N/A'; ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div>
                                        <?php echo formatPrice($item['price']); ?> x
                                        <?php echo $item['quantity']; ?>
                                    </div>
                                    <div style="font-weight: bold;">
                                        <?php echo formatPrice($item['subtotal']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div style="margin-top: 2rem; text-align: right;">
                            <div class="detail-row" style="justify-content: flex-end; gap: 2rem;">
                                <span class="detail-label">Subtotal:</span>
                                <span class="detail-value">
                                    <?php echo formatPrice($order['subtotal']); ?>
                                </span>
                            </div>
                            <div class="detail-row" style="justify-content: flex-end; gap: 2rem;">
                                <span class="detail-label">Shipping:</span>
                                <span class="detail-value">
                                    <?php echo formatPrice($order['shipping_cost']); ?>
                                </span>
                            </div>
                            <div class="detail-row" style="justify-content: flex-end; gap: 2rem;">
                                <span class="detail-label">Tax:</span>
                                <span class="detail-value">
                                    <?php echo formatPrice($order['tax']); ?>
                                </span>
                            </div>
                            <div class="detail-row"
                                style="justify-content: flex-end; gap: 2rem; font-size: 1.25rem; margin-top: 0.5rem; border-top: 1px solid var(--color-border); padding-top: 0.5rem;">
                                <span class="detail-label">Total:</span>
                                <span class="detail-value" style="color: var(--color-red);">
                                    <?php echo formatPrice($order['total']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="right-col">
                    <div class="card">
                        <div class="card-header">Order Status</div>
                        <form method="POST">
                            <input type="hidden" name="update_status" value="1">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.5rem;">Update Status</label>
                                <select name="order_status" class="form-control"
                                    style="width: 100%; margin-bottom: 1rem;">
                                    <?php foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo $order['order_status'] === $status ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Update
                                    Status</button>
                            </div>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header">Customer Information</div>
                        <div class="detail-row"><span class="detail-label">Name:</span> <span class="detail-value">
                                <?php echo htmlspecialchars($order['customer_name']); ?>
                            </span></div>
                        <div class="detail-row"><span class="detail-label">Email:</span> <span class="detail-value">
                                <?php echo htmlspecialchars($order['customer_email']); ?>
                            </span></div>
                        <div class="detail-row"><span class="detail-label">Phone:</span> <span class="detail-value">
                                <?php echo htmlspecialchars($order['customer_phone']); ?>
                            </span></div>
                    </div>

                    <div class="card">
                        <div class="card-header">Shipping Address</div>
                        <?php if ($order['address_id']): ?>
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($order['shipping_name']); ?>
                            </div>
                            <div>
                                <?php echo htmlspecialchars($order['address_line1']); ?>
                            </div>
                            <?php if ($order['address_line2']): ?>
                                <div>
                                    <?php echo htmlspecialchars($order['address_line2']); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <?php echo htmlspecialchars($order['city']); ?>,
                                <?php echo htmlspecialchars($order['state']); ?>
                                <?php echo htmlspecialchars($order['pincode']); ?>
                            </div>
                            <div>
                                <?php echo htmlspecialchars($order['country']); ?>
                            </div>
                            <div style="margin-top: 0.5rem;">Phone:
                                <?php echo htmlspecialchars($order['shipping_phone']); ?>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--color-gray);">No address information available.</p>
                        <?php endif; ?>
                    </div>

                    <div class="card">
                        <div class="card-header">Payment Info</div>
                        <div class="detail-row"><span class="detail-label">Method:</span> <span class="detail-value">
                                <?php echo strtoupper($order['payment_method']); ?>
                            </span></div>
                        <div class="detail-row"><span class="detail-label">Status:</span> <span class="detail-value">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
