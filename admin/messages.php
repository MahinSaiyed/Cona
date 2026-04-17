<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$message_info = '';
$error_info = '';

// Handle status updates or deletions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    try {
        if ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
            if ($stmt->execute([$id])) $message_info = "Message deleted successfully.";
        } elseif ($action === 'read') {
            $stmt = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            if ($stmt->execute([$id])) $message_info = "Message marked as read.";
        }
    } catch (Exception $e) {
        $error_info = "Action failed: " . $e->getMessage();
    }
}

// Get all messages
$stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - <?php echo SITE_NAME; ?></title>
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
        .status-unread { color: var(--color-red); font-weight: bold; }
        .status-read { color: var(--color-gray); }
        .btn-action { padding: 0.4rem 0.8rem; border-radius: 4px; text-decoration: none; font-size: 0.8125rem; margin-right: 0.5rem; }
        .btn-read { background: #e3f2fd; color: #1565c0; }
        .btn-delete { background: #ffebee; color: #c62828; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 2rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Contact Messages</h1>
                <div>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
            </div>

            <?php if ($message_info): ?>
                <div class="alert alert-success"><?php echo $message_info; ?></div>
            <?php endif; ?>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($msg['name']); ?></strong><br>
                                <span style="font-size: 0.75rem; color: #666;"><?php echo htmlspecialchars($msg['email']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                            <td><div style="max-width: 300px; font-size: 0.875rem;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div></td>
                            <td><span class="status-<?php echo $msg['status']; ?>"><?php echo ucfirst($msg['status']); ?></span></td>
                            <td style="white-space: nowrap;">
                                <?php if ($msg['status'] === 'unread'): ?>
                                    <a href="?action=read&id=<?php echo $msg['id']; ?>" class="btn-action btn-read">Mark Read</a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $msg['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Archive/Delete this message?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($messages)): ?>
                        <tr><td colspan="6" style="text-align: center; color: #999;">No messages found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
