<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

$status_filter = $_GET['status'] ?? '';

$query = "SELECT p.*, o.order_number FROM payments p 
          JOIN orders o ON p.order_id = o.id 
          WHERE 1=1";
$params = [];

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Admin</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .admin-sidebar { background: var(--color-black); color: var(--color-white); padding: 2rem 0; }
        .admin-sidebar h2 { padding: 0 1.5rem; margin-bottom: 2rem; }
        .admin-menu { list-style: none; }
        .admin-menu a { display: block; padding: 1rem 1.5rem; color: var(--color-white); }
        .admin-menu a:hover, .admin-menu a.active { background: var(--color-gray); }
        .admin-content { padding: 2rem; background: var(--color-light-gray); }
        .admin-header { background: white; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .filter-bar { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .data-table { width: 100%; background: white; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border); }
        .data-table th { background: var(--color-light-gray); font-weight: var(--font-weight-semibold); }
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .badge-pending { background: #fff3e0; color: #e65100; }
        .badge-failed { background: #ffebee; color: #c62828; }
        .proof-thumb { width: 50px; height: 50px; object-fit: cover; cursor: pointer; border: 1px solid var(--color-border); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Payment Management</h1>
            </div>

            <form class="filter-bar" method="GET">
                <select name="status" class="form-control" style="width: 200px;">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="success" <?php echo $status_filter === 'success' ? 'selected' : ''; ?>>Success</option>
                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Order #</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Proof</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($payment['transaction_id'] ?: 'N/A'); ?></code></td>
                            <td><strong><a href="/admin/order-details.php?id=<?php echo $payment['order_id']; ?>"><?php echo htmlspecialchars($payment['order_number']); ?></a></strong></td>
                            <td><?php echo strtoupper($payment['payment_method']); ?></td>
                            <td><?php echo formatPrice($payment['amount']); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo ($payment['status'] === 'success') ? 'success' : (($payment['status'] === 'failed') ? 'failed' : 'pending'); 
                                ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($payment['whatsapp_proof']): ?>
                                    <a href="/uploads/payments/<?php echo $payment['whatsapp_proof']; ?>" target="_blank">
                                        <img src="/uploads/payments/<?php echo $payment['whatsapp_proof']; ?>" class="proof-thumb" alt="Proof">
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 3rem; color: var(--color-gray);">No payments found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
