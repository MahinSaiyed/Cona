<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// Handle status messages
$flash = getFlashMessage();

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category']) || isset($_POST['edit_category'])) {
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $parent_id = intval($_POST['parent_id'] ?? 0);
        $display_order = intval($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $slug = generateSlug($name);

        if (empty($name)) {
            setFlashMessage('error', 'Category name is required');
        } else {
            if (isset($_POST['add_category'])) {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, parent_id, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $parent_val = $parent_id > 0 ? $parent_id : null;
                $stmt->execute([$name, $slug, $parent_val, $description, $display_order, $is_active]);
                setFlashMessage('success', 'Category added successfully');
            } else {
                $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, parent_id = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
                $parent_val = $parent_id > 0 ? $parent_id : null;
                $stmt->execute([$name, $slug, $parent_val, $description, $display_order, $is_active, $edit_id]);
                setFlashMessage('success', 'Category updated successfully');
            }
            redirect('/admin/categories.php');
        }
    }

    if (isset($_POST['delete_category'])) {
        $id = intval($_POST['id'] ?? 0);
        // Check if there are products in this category
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetch()['count'] > 0) {
            setFlashMessage('error', 'Cannot delete category containing products');
        } else {
            $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', 'Category deleted successfully');
        }
        redirect('/admin/categories.php');
    }
}

// Get all categories for listing and parent selection
$stmt = $db->query("SELECT c1.*, c2.name as parent_name FROM categories c1 LEFT JOIN categories c2 ON c1.parent_id = c2.id ORDER BY c1.display_order ASC, c1.name ASC");
$all_categories = $stmt->fetchAll();

$edit_cat = null;
if ($action === 'edit' && $edit_id > 0) {
    foreach ($all_categories as $cat) {
        if ($cat['id'] == $edit_id) {
            $edit_cat = $cat;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Admin</title>
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

        .card {
            background: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--color-border);
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.75rem;
        }

        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-error {
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
                <h1>Category Management</h1>
            </div>

            <?php if ($flash): ?>
                <div class="flash-message flash-<?php echo $flash['type']; ?>"
                    style="padding: 1rem; margin-bottom: 1rem; background: <?php echo $flash['type'] === 'success' ? '#e8f5e9' : '#ffebee'; ?>; color: <?php echo $flash['type'] === 'success' ? '#2e7d32' : '#c62828'; ?>;">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <!-- Add/Edit Form -->
                <div class="card">
                    <h3>
                        <?php echo $edit_cat ? 'Edit Category' : 'Add New Category'; ?>
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?php echo $edit_cat ? htmlspecialchars($edit_cat['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Parent Category (Optional)</label>
                            <select name="parent_id" class="form-control">
                                <option value="0">None (Top Level)</option>
                                <?php foreach ($all_categories as $cat): ?>
                                    <?php if (!$edit_cat || $cat['id'] != $edit_cat['id']): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_cat && $edit_cat['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control"
                                rows="3"><?php echo $edit_cat ? htmlspecialchars($edit_cat['description']) : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="display_order" class="form-control"
                                value="<?php echo $edit_cat ? $edit_cat['display_order'] : '0'; ?>">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" <?php echo (!$edit_cat || $edit_cat['is_active']) ? 'checked' : ''; ?>> Is Active
                            </label>
                        </div>
                        <div class="action-btns">
                            <?php if ($edit_cat): ?>
                                <button type="submit" name="edit_category" class="btn btn-primary">Update Category</button>
                                <a href="/admin/categories.php" class="btn btn-secondary">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Categories List -->
                <div class="card" style="overflow-x: auto;">
                    <h3>Existing Categories</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_categories as $cat): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </strong><br>
                                        <small>
                                            <?php echo htmlspecialchars($cat['slug']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($cat['parent_name'] ?? 'None'); ?>
                                    </td>
                                    <td>
                                        <?php echo $cat['display_order']; ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge <?php echo $cat['is_active'] ? 'badge-success' : 'badge-error'; ?>">
                                            <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="action-btns">
                                        <a href="/admin/categories.php?action=edit&id=<?php echo $cat['id']; ?>"
                                            class="btn btn-secondary"
                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</a>
                                        <form method="POST" style="display:inline"
                                            onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                            <button type="submit" name="delete_category" class="btn"
                                                style="background: var(--color-error); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>