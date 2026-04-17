<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/admin/products.php');
}

// Fetch product details
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('error', 'Product not found');
    redirect('/admin/products.php');
}

$current_images = json_decode($product['images'], true) ?: ['front' => '', 'back' => '', 'left' => '', 'right' => ''];

// Get categories for selection
$stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $brand = sanitize($_POST['brand'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $original_price = floatval($_POST['original_price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $sku = sanitize($_POST['sku'] ?? '');
    $description = $_POST['description'] ?? '';
    $gender = sanitize($_POST['gender'] ?? 'unisex');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $slug = generateSlug($name);

    $errors = [];
    if (empty($name))
        $errors[] = "Product name is required";

    // Handle Image Uploads
    $main_image = $product['main_image'];
    $side_images = $current_images;

    // Main image update
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
        $upload = uploadImage($_FILES['main_image']);
        if ($upload['success']) {
            $main_image = $upload['filename'];
        } else {
            $errors[] = "Main Image: " . $upload['message'];
        }
    }

    // Side images update
    foreach (['front', 'back', 'left', 'right'] as $side) {
        $input_name = "image_" . $side;
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === 0) {
            $upload = uploadImage($_FILES[$input_name]);
            if ($upload['success']) {
                $side_images[$side] = $upload['filename'];
            } else {
                $errors[] = ucfirst($side) . " Image: " . $upload['message'];
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("UPDATE products SET 
                category_id = ?, name = ?, slug = ?, brand = ?, description = ?, 
                price = ?, original_price = ?, stock = ?, sku = ?, 
                main_image = ?, images = ?, is_featured = ?, 
                is_new_arrival = ?, is_on_sale = ?, gender = ? 
                WHERE id = ?");

            $images_json = json_encode($side_images);

            $stmt->execute([
                $category_id,
                $name,
                $slug,
                $brand,
                $description,
                $price,
                $original_price,
                $stock,
                $sku,
                $main_image,
                $images_json,
                $is_featured,
                $is_new_arrival,
                $is_on_sale,
                $gender,
                $id
            ]);

            setFlashMessage('success', 'Product updated successfully');
            redirect('/admin/products.php');
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
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

        .admin-menu a:hover {
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
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--color-border);
            border-radius: 4px;
        }

        .image-upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .image-upload-item {
            border: 1px dashed var(--color-border);
            padding: 1rem;
            text-align: center;
            background: #fafafa;
        }

        .current-img {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin: 0.5rem auto;
            object-fit: cover;
        }

        .error-list {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 4px;
            list-style: inside;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Edit Product:
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                <a href="/admin/products.php" class="btn btn-secondary">Back to List</a>
            </div>

            <?php if (!empty($errors)): ?>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?php echo $error; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Product Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" class="form-control"
                                value="<?php echo htmlspecialchars($product['brand']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Price (₹)</label>
                            <input type="number" name="price" class="form-control" step="0.01"
                                value="<?php echo $product['price']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Original Price (₹)</label>
                            <input type="number" name="original_price" class="form-control" step="0.01"
                                value="<?php echo $product['original_price']; ?>">
                        </div>

                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" class="form-control"
                                value="<?php echo $product['stock']; ?>">
                        </div>

                        <div class="form-group">
                            <label>SKU (Product Code)</label>
                            <input type="text" name="sku" class="form-control"
                                value="<?php echo htmlspecialchars($product['sku']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="unisex" <?php echo $product['gender'] === 'unisex' ? 'selected' : ''; ?>
                                    >Unisex</option>
                                <option value="men" <?php echo $product['gender'] === 'men' ? 'selected' : ''; ?>>Men
                                </option>
                                <option value="women" <?php echo $product['gender'] === 'women' ? 'selected' : ''; ?>
                                    >Women</option>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; gap: 2rem; align-items: center;">
                            <label><input type="checkbox" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>> Featured</label>
                            <label><input type="checkbox" name="is_new_arrival" <?php echo $product['is_new_arrival'] ? 'checked' : ''; ?>> New Arrival</label>
                            <label><input type="checkbox" name="is_on_sale" <?php echo $product['is_on_sale'] ? 'checked' : ''; ?>> On Sale</label>
                        </div>

                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description" class="form-control"
                                rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label>Main Product Image</label>
                            <?php if ($product['main_image']): ?>
                                <img src="/uploads/products/<?php echo $product['main_image']; ?>" class="current-img"
                                    alt="Current Main">
                            <?php endif; ?>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                            <small>Leave empty to keep current image</small>
                        </div>

                        <div class="form-group full-width">
                            <label>Other Views</label>
                            <div class="image-upload-grid">
                                <?php foreach (['front', 'back', 'left', 'right'] as $side): ?>
                                    <div class="image-upload-item">
                                        <label>
                                            <?php echo ucfirst($side); ?> View
                                        </label>
                                        <?php if (!empty($current_images[$side])): ?>
                                            <img src="/uploads/products/<?php echo $current_images[$side]; ?>"
                                                class="current-img" alt="<?php echo $side; ?>">
                                        <?php endif; ?>
                                        <input type="file" name="image_<?php echo $side; ?>" accept="image/*">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="width: 200px;">Update Product</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>