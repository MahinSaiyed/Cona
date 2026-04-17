<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/admin/users.php');
}

// Fetch user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'User not found');
    redirect('/admin/users.php');
}

// Fetch user addresses
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$id]);
$addresses = $stmt->fetchAll();

// Fetch user orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Admin</title>
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
        .card { background: white; padding: 2rem; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        .info-item { margin-bottom: 1rem; }
        .info-label { font-weight: bold; color: var(--color-gray); font-size: 0.875rem; text-transform: uppercase; }
        .info-value { font-size: 1.125rem; margin-top: 0.25rem; }
        .data-table { width: 100%; background: white; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border); }
        .data-table th { background: var(--color-light-gray); font-weight: var(--font-weight-semibold); }
        .badge { padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.75rem; }
        .badge-pending { background: #fff3e0; color: #e65100; }
        .badge-delivered { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>User Details: <?php echo htmlspecialchars($user['full_name']); ?></h1>
                <a href="/admin/users.php" class="btn btn-secondary">Back to List</a>
            </div>

            <div class="info-grid">
                <div class="card">
                    <h3>Basic Information</h3>
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Joined On</div>
                        <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($user['created_at'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Google ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['google_id'] ?: 'Not linked'); ?></div>
                    </div>
                </div>

                <div class="card">
                    <h3>Authentication Status</h3>
                    <div class="info-item">
                        <div class="info-label">Email Verification</div>
                        <div class="info-value">
                            <span class="badge <?php echo $user['email_verified'] ? 'badge-delivered' : 'badge-pending'; ?>">
                                <?php echo $user['email_verified'] ? 'Verified' : 'Pending Verification'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Verification</div>
                        <div class="info-value">
                            <span class="badge <?php echo $user['phone_verified'] ? 'badge-delivered' : 'badge-pending'; ?>">
                                <?php echo $user['phone_verified'] ? 'Verified' : 'Pending Verification'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Addresses</h3>
                <?php if (empty($addresses)): ?>
                    <p style="color: var(--color-gray);">No addresses saved.</p>
                <?php else: ?>
                    <div class="info-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                        <?php foreach ($addresses as $addr): ?>
                            <div style="border: 1px solid var(--color-border); padding: 1rem; border-radius: 4px; position: relative;">
                                <?php if ($addr['is_default']): ?>
                                    <span class="badge badge-delivered" style="position: absolute; top: 0.5rem; right: 0.5rem;">Default</span>
                                <?php endif; ?>
                                <strong><?php echo htmlspecialchars($addr['full_name']); ?></strong><br>
                                <?php echo htmlspecialchars($addr['phone']); ?><br>
                                <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                                <?php if ($addr['address_line2']) echo htmlspecialchars($addr['address_line2']) . '<br>'; ?>
                                <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']); ?><br>
                                <?php echo htmlspecialchars($addr['country']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Order History</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo formatPrice($order['total']); ?></td>
                                <td><span class="badge badge-<?php echo $order['payment_status'] === 'paid' ? 'delivered' : 'pending'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                <td><span class="badge badge-<?php echo $order['order_status'] === 'delivered' ? 'delivered' : 'pending'; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                <td><a href="/admin/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--color-gray);">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
