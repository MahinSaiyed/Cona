<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $stmt = $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->execute([$product_id]);
        setFlashMessage('success', 'Product deleted successfully');
        redirect('/admin/products.php');
    }

    if ($action === 'toggle_featured') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $stmt = $db->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = ?");
        $stmt->execute([$product_id]);
        setFlashMessage('success', 'Product updated');
        redirect('/admin/products.php');
    }
}

// Get all products
$search = $_GET['search'] ?? '';
$category_filter = intval($_GET['category'] ?? 0);

$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_active = 1";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.sku LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($category_filter > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin</title>
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

        .product-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Product Management</h1>
                <a href="/admin/product-add.php" class="btn btn-primary">+ Add New Product</a>
            </div>

            <?php $flash = getFlashMessage();
            if ($flash): ?>
                <div class="flash-message flash-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filters -->
            <form class="search-bar" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search products..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <select name="category" class="filter-select">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary">Search</button>
            </form>

            <!-- Products Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img src="/uploads/products/<?php echo htmlspecialchars($product['main_image'] ?? 'placeholder.jpg'); ?>"
                                    class="product-thumb" alt="Product" onerror="this.src='/assets/images/placeholder.jpg'">
                            </td>
                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </strong><br>
                                <small>
                                    <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>
                                </small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($product['brand']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($product['category_name'] ?? 'Unknown'); ?>
                            </td>
                            <td>
                                <?php echo formatPrice($product['price']); ?>
                            </td>
                            <td
                                style="color: <?php echo $product['stock'] < 10 ? 'var(--color-red)' : 'inherit'; ?>; font-weight: bold;">
                                <?php echo $product['stock']; ?>
                            </td>
                            <td>
                                <?php if ($product['is_featured']): ?>
                                    <span
                                        style="background: #ffd700; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 3px;">★
                                        Featured</span>
                                <?php endif; ?>
                                <?php if ($product['is_new_arrival']): ?>
                                    <span class="badge-new" style="font-size: 0.75rem;">New</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="/admin/product-edit.php?id=<?php echo $product['id']; ?>"
                                        class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="POST" style="display: inline;"
                                        onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-sm"
                                            style="background: var(--color-error); color: white;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--color-gray);">
                                No products found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>