<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = sanitize($_POST['order_status'] ?? '');

    if ($order_id > 0 && in_array($new_status, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        $stmt = $db->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        setFlashMessage('success', 'Order status updated');
    }
}

// Get all orders
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT o.*, u.full_name, u.email FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($status_filter) {
    $query .= " AND o.order_status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $query .= " AND (o.order_number LIKE ? OR u.full_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Order Management - Admin</title>
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
        }

        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--color-border);
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

        .status-select {
            padding: 0.5rem;
            border: 1px solid var(--color-border);
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

        .badge-processing {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .badge-shipped {
            background: #e0f7fa;
            color: #00695c;
        }

        .badge-delivered {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-cancelled {
            background: #ffebee;
            color: #c62828;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Order Management</h1>
            </div>

            <?php $flash = getFlashMessage();
            if ($flash): ?>
                <div style="background: #e8f5e9; padding: 1rem; margin-bottom: 1rem; color: #2e7d32;">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filters -->
            <form class="search-bar" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search by order # or customer..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed
                    </option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>
                        >Processing</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped
                    </option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered
                    </option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled
                    </option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            <!-- Orders Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>
                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                </strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['full_name']); ?><br>
                                <small>
                                    <?php echo htmlspecialchars($order['email']); ?>
                                </small>
                            </td>
                            <td>
                                <?php echo formatPrice($order['total']); ?>
                            </td>
                            <td>
                                <?php echo strtoupper($order['payment_method']); ?>
                            </td>
                            <td><span class="badge-status badge-<?php echo $order['payment_status']; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span></td>
                            <td>
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="order_status" class="status-select" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>
                                <?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="/admin/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary"
                                        style="padding: 0.5rem 1rem; font-size: 0.875rem;">View Details</a>
                                    <a href="/invoice.php?id=<?php echo $order['id']; ?>&download=1" class="btn btn-primary"
                                        style="padding: 0.5rem 1rem; font-size: 0.875rem;">Invoice</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--color-gray);">
                                No orders found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>
