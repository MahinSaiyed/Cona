<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
$total_products = $stmt->fetch()['count'];

$stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE order_status != 'cancelled'");
$total_orders = $stmt->fetch()['count'];

$stmt = $db->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

$stmt = $db->query("SELECT SUM(total) as revenue FROM orders WHERE payment_status = 'paid'");
$total_revenue = $stmt->fetch()['revenue'] ?? 0;

// Recent orders
$stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$recent_orders = $stmt->fetchAll();

// Low stock products
$stmt = $db->query("SELECT * FROM products WHERE stock < 10 AND is_active = 1 ORDER BY stock ASC LIMIT 10");
$low_stock = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard -
        <?php echo SITE_NAME; ?>
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
            color: var(--color-white);
            padding: 2rem 0;
        }

        .admin-sidebar h2 {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }

        .admin-menu {
            list-style: none;
        }

        .admin-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--color-white);
            transition: background var(--transition-fast);
        }

        .admin-menu a:hover,
        .admin-menu a.active {
            background: var(--color-gray);
        }

        .admin-content {
            padding: 2rem;
            background: var(--color-light-gray);
        }

        .admin-header {
            background: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: var(--font-weight-bold);
            color: var(--color-red);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--color-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }

        .data-table th {
            background: var(--color-light-gray);
            font-weight: var(--font-weight-semibold);
        }

        .badge-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: var(--font-weight-semibold);
        }

        .badge-pending {
            background: #fff3e0;
            color: #e65100;
        }

        .badge-confirmed {
            background: #e3f2fd;
            color: #1565c0;
        }

        .badge-shipped {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .badge-delivered {
            background: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div>Welcome,
                    <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $total_products; ?>
                    </div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $total_orders; ?>
                    </div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo $total_users; ?>
                    </div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo formatPrice($total_revenue); ?>
                    </div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <h2 style="margin-bottom: 1rem;">Recent Orders</h2>
            <table class="data-table" style="margin-bottom: 2rem;">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order):
                        $stmt = $db->prepare("SELECT full_name FROM users WHERE id = ?");
                        $stmt->execute([$order['user_id']]);
                        $customer = $stmt->fetch();
                        ?>
                        <tr>
                            <td><strong>
                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                </strong></td>
                            <td>
                                <?php echo htmlspecialchars($customer['full_name'] ?? 'Guest'); ?>
                            </td>
                            <td>
                                <?php echo formatPrice($order['total']); ?>
                            </td>
                            <td>
                                <?php echo strtoupper($order['payment_method']); ?>
                            </td>
                            <td><span class="badge-status badge-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span></td>
                            <td>
                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2 style="margin-bottom: 1rem;">Low Stock Products</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>SKU</th>
                        <th>Stock</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock as $product): ?>
                        <tr>
                            <td><strong>
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </strong></td>
                            <td>
                                <?php echo htmlspecialchars($product['brand']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($product['sku']); ?>
                            </td>
                            <td style="color: var(--color-red); font-weight: bold;">
                                <?php echo $product['stock']; ?>
                            </td>
                            <td>
                                <?php echo formatPrice($product['price']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($low_stock)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--color-gray);">All products have
                                sufficient stock</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>