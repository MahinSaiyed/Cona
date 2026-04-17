<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$sub_message = '';

// Handle removal
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        if ($stmt->execute([$id])) $sub_message = "Subscriber removed successfully.";
    } catch (Exception $e) {
        $sub_message = "Error: " . $e->getMessage();
    }
}

// Get all subscribers
$stmt = $db->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
$subscribers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .admin-sidebar { background: var(--color-black); color: var(--color-white); padding: 2rem 0; }
        .admin-sidebar h2 { padding: 0 1.5rem; margin-bottom: 2rem; }
        .admin-menu { list-style: none; }
        .admin-menu a { display: block; padding: 1rem 1.5rem; color: var(--color-white); transition: background var(--transition-fast); }
        .admin-menu a:hover, .admin-menu a.active { background: var(--color-gray); }
        .admin-content { padding: 2rem; background: var(--color-light-gray); }
        .admin-header { background: white; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .data-table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border); }
        .data-table th { background: #f8f9fa; font-weight: 600; }
        .btn-delete { background: #ffebee; color: #c62828; padding: 0.4rem 0.8rem; border-radius: 4px; text-decoration: none; font-size: 0.8125rem; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 2rem; background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Newsletter Subscribers</h1>
                <div>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
            </div>

            <?php if ($sub_message): ?>
                <div class="alert"><?php echo $sub_message; ?></div>
            <?php endif; ?>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Email Address</th>
                        <th>Subscribed Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $sub): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($sub['email']); ?></strong></td>
                            <td><?php echo date('M d, Y H:i', strtotime($sub['created_at'])); ?></td>
                            <td><span style="color: <?php echo $sub['is_active'] ? '#2e7d32' : '#c62828'; ?>"><?php echo $sub['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                            <td>
                                <a href="?action=delete&id=<?php echo $sub['id']; ?>" class="btn-delete" onclick="return confirm('Remove this subscriber?')">Unsubscribe/Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($subscribers)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #999;">No subscribers yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
