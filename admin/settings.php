<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        
        $db->commit();
        $message = "Settings updated successfully!";
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Failed to update settings: " . $e->getMessage();
    }
}

// Get all settings
$stmt = $db->query("SELECT * FROM settings ORDER BY setting_group, setting_key");
$all_settings = $stmt->fetchAll();

$settings_by_group = [];
foreach ($all_settings as $setting) {
    $settings_by_group[$setting['setting_group']][] = $setting;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - <?php echo SITE_NAME; ?></title>
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
        .settings-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .setting-group { margin-bottom: 3rem; }
        .setting-group h3 { margin-bottom: 1.5rem; color: var(--color-red); border-bottom: 2px solid var(--color-light-gray); padding-bottom: 0.5rem; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 1px; }
        .setting-item { margin-bottom: 1.5rem; }
        .setting-item label { display: block; margin-bottom: 0.5rem; font-weight: 500; text-transform: capitalize; }
        .setting-item input, .setting-item textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        .btn-save { padding: 1rem 2rem; background: var(--color-black); color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; width: 200px; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 2rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Site Settings</h1>
                <div>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="settings-container">
                <?php foreach ($settings_by_group as $group => $items): ?>
                    <div class="setting-group">
                        <h3><?php echo htmlspecialchars($group); ?> Settings</h3>
                        <?php foreach ($items as $item): ?>
                            <div class="setting-item">
                                <label><?php echo str_replace('_', ' ', $item['setting_key']); ?></label>
                                <?php if (strlen($item['setting_value']) > 100 || strpos($item['setting_key'], 'address') !== false): ?>
                                    <textarea name="settings[<?php echo $item['setting_key']; ?>]" rows="3"><?php echo htmlspecialchars($item['setting_value']); ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="settings[<?php echo $item['setting_key']; ?>]" value="<?php echo htmlspecialchars($item['setting_value']); ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn-save">Save All Settings</button>
            </form>
        </main>
    </div>
</body>
</html>
