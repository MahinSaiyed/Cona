<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

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
    $description = $_POST['description'] ?? ''; // Keep HTML if needed
    $gender = sanitize($_POST['gender'] ?? 'unisex');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $slug = generateSlug($name);

    $errors = [];
    if (empty($name))
        $errors[] = "Product name is required";
    if ($category_id <= 0)
        $errors[] = "Please select a category";
    if ($price <= 0)
        $errors[] = "Price must be greater than zero";

    // Handle Image Uploads
    $main_image = '';
    $side_images = [
        'front' => '',
        'back' => '',
        'left' => '',
        'right' => ''
    ];

    // Main image
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
        $upload = uploadImage($_FILES['main_image']);
        if ($upload['success']) {
            $main_image = $upload['filename'];
        } else {
            $errors[] = "Main Image: " . $upload['message'];
        }
    } else {
        $errors[] = "Main image is required";
    }

    // Side images
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
            $stmt = $db->prepare("INSERT INTO products 
                (category_id, name, slug, brand, description, price, original_price, stock, sku, main_image, images, is_featured, is_new_arrival, is_on_sale, gender) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
                $gender
            ]);

            setFlashMessage('success', 'Product added successfully');
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
    <title>Add Product - Admin</title>
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
                <h1>Add New Product</h1>
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
                            <input type="text" name="name" class="form-control" placeholder="e.g. Jordan 1 Retro High"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. Nike">
                        </div>

                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Price (₹)</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label>Original Price (₹) - For Sale</label>
                            <input type="number" name="original_price" class="form-control" step="0.01">
                        </div>

                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" value="0">
                        </div>

                        <div class="form-group">
                            <label>SKU (Product Code)</label>
                            <input type="text" name="sku" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="unisex">Unisex</option>
                                <option value="men">Men</option>
                                <option value="women">Women</option>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; gap: 2rem; align-items: center;">
                            <label><input type="checkbox" name="is_featured"> Featured</label>
                            <label><input type="checkbox" name="is_new_arrival"> New Arrival</label>
                            <label><input type="checkbox" name="is_on_sale"> On Sale</label>
                        </div>

                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="5"></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label>Main Product Image (Required)</label>
                            <input type="file" name="main_image" class="form-control" accept="image/*" required>
                        </div>

                        <div class="form-group full-width">
                            <label>Other Views (Optional)</label>
                            <div class="image-upload-grid">
                                <div class="image-upload-item">
                                    <label>Front View</label>
                                    <input type="file" name="image_front" accept="image/*">
                                </div>
                                <div class="image-upload-item">
                                    <label>Back View</label>
                                    <input type="file" name="image_back" accept="image/*">
                                </div>
                                <div class="image-upload-item">
                                    <label>Left View</label>
                                    <input type="file" name="image_left" accept="image/*">
                                </div>
                                <div class="image-upload-item">
                                    <label>Right View</label>
                                    <input type="file" name="image_right" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="width: 200px;">Save Product</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>