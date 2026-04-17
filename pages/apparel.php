<?php
// Apparel collection page (similar to footwear but for apparel category)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get category
$category_slug = 'apparel';
$stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$category_slug]);
$category = $stmt->fetch();

if (!$category) {
    // Create category if doesn't exist
    $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES ('Apparel', 'apparel', 'Clothing and fashion')");
    $stmt->execute();
    $category_id = $db->lastInsertId();
} else {
    $category_id = $category['id'];
}

// Get filters
$brand_filter = $_GET['brand'] ?? '';
$size_filter = $_GET['size'] ?? '';
$price_filter = $_GET['price'] ?? '';
$gender_filter = $_GET['gender'] ?? '';

// Build query
$query = "SELECT p.* FROM products p ";
$conditions = ["p.is_active = 1", "p.category_id = ?"];
$params = [$category_id];

if ($brand_filter) {
    $conditions[] = "p.brand = ?";
    $params[] = $brand_filter;
}

if ($gender_filter) {
    $conditions[] = "p.gender = ?";
    $params[] = $gender_filter;
}

if ($price_filter) {
    $parts = explode('-', $price_filter);
    if (count($parts) === 2) {
        $conditions[] = "p.price >= ? AND p.price <= ?";
        $params[] = floatval($parts[0]);
        $params[] = floatval($parts[1]);
    }
}

if ($size_filter) {
    $conditions[] = "p.sizes LIKE ?";
    $params[] = '%"' . $size_filter . '"%';
}

$query .= " WHERE " . implode(" AND ", $conditions);
$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get unique brands for this category
$stmt = $db->prepare("SELECT DISTINCT brand FROM products WHERE category_id = ? AND is_active = 1 AND brand IS NOT NULL AND brand != '' ORDER BY brand");
$stmt->execute([$category_id]);
$brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<main>
    <!-- Page Header: Interactive Transformation -->
    <section class="style-transformation" style="padding: 4rem 0;">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">Seamless Streetwear</span>
                <h2>Apparel</h2>
                <p>Elevate your everyday wardrobe with our premium streetwear essentials. Comfortable, versatile, and
                    designed for the modern lifestyle.</p>
            </div>
            <div class="transformation-visual">
                <div class="interactive-container">
                    <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=1400&auto=format&fit=crop"
                        alt="Casual Tee" class="img-base">
                    <img src="https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=1400&auto=format&fit=crop"
                        alt="Streetwear Look" class="img-premium">
                    <div class="premium-label">URBAN STYLE</div>
                    <div class="scan-line"></div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label class="filter-label">Brand</label>
                <select name="brand" class="filter-select" onchange="filterProducts()">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo htmlspecialchars($brand); ?>" <?php echo $brand_filter === $brand ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Size</label>
                <select name="size" class="filter-select" onchange="filterProducts()">
                    <option value="">All Sizes</option>
                    <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size): ?>
                        <option value="<?php echo $size; ?>" <?php echo $size_filter === $size ? 'selected' : ''; ?>>
                            <?php echo $size; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Price Range</label>
                <select name="price" class="filter-select" onchange="filterProducts()">
                    <option value="">All Prices</option>
                    <option value="0-1999" <?php echo $price_filter === '0-1999' ? 'selected' : ''; ?>>Under ₹1,999
                    </option>
                    <option value="2000-3999" <?php echo $price_filter === '2000-3999' ? 'selected' : ''; ?>>₹2,000 -
                        ₹3,999</option>
                    <option value="4000-6999" <?php echo $price_filter === '4000-6999' ? 'selected' : ''; ?>>₹4,000 -
                        ₹6,999</option>
                    <option value="7000-99999" <?php echo $price_filter === '7000-99999' ? 'selected' : ''; ?>>₹7,000+
                    </option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Gender</label>
                <select name="gender" class="filter-select" onchange="filterProducts()">
                    <option value="">All</option>
                    <option value="men" <?php echo $gender_filter === 'men' ? 'selected' : ''; ?>>Men</option>
                    <option value="women" <?php echo $gender_filter === 'women' ? 'selected' : ''; ?>>Women</option>
                    <option value="unisex" <?php echo $gender_filter === 'unisex' ? 'selected' : ''; ?>>Unisex</option>
                </select>
            </div>
        </div>

        <!-- Product Grid -->
        <?php if (empty($products)): ?>
            <div
                style="text-align: center; padding: 5rem 0; background: var(--color-light-gray); margin: 2rem 0; border-radius: 8px;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--color-gray)" stroke-width="1"
                    style="margin-bottom: 1.5rem; opacity: 0.5;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="11" y1="8" x2="11" y2="14"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No apparel found</h3>
                <p style="color: var(--color-gray); margin-bottom: 2rem;">We couldn't find any clothing matching your
                    current filters.</p>
                <button onclick="clearFilters()" class="btn btn-primary" style="padding: 0.75rem 2rem;">Clear All
                    Filters</button>
            </div>
        <?php else: ?>
            <div class="grid grid-4">
                <?php foreach ($products as $product):
                    $all_images = !empty($product['images']) ? json_decode($product['images'], true) : [];
                    $images = array_values(array_filter($all_images));
                    $main_image = $product['main_image'] ?: ($images[0] ?? 'placeholder.jpg');
                    $sizes = !empty($product['sizes']) ? json_decode($product['sizes'], true) : [];
                    $colors = !empty($product['colors']) ? json_decode($product['colors'], true) : [];
                    ?>
                    <div class="product-card" data-brand="<?php echo htmlspecialchars($product['brand']); ?>"
                        data-price="<?php echo $product['price']; ?>" data-sizes='<?php echo json_encode($sizes); ?>'
                        data-colors='<?php echo json_encode($colors); ?>' data-gender="<?php echo $product['gender']; ?>"
                        data-gallery='<?php echo json_encode($images); ?>'>
                        <div class="product-image">
                            <a href="/pages/product.php?id=<?php echo $product['id']; ?>" class="product-image-link">
                                <img src="/uploads/products/<?php echo htmlspecialchars($main_image); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    onerror="this.src='/assets/images/placeholder.jpg'">
                            </a>

                            <div class="product-badges">
                                <?php if ($product['is_new_arrival']): ?>
                                    <span class="badge-new">New</span>
                                <?php endif; ?>
                                <?php if ($product['is_on_sale']): ?>
                                    <span class="badge-sale">Sale</span>
                                <?php endif; ?>
                            </div>

                            <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>, this)">
                                <svg viewBox="0 0 24 24">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <div class="product-info">
                            <p class="product-brand">
                                <?php echo htmlspecialchars($product['brand']); ?>
                            </p>
                            <h3 class="product-name">
                                <a href="/pages/product.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <span class="price-current">
                                    <?php echo formatPrice($product['price']); ?>
                                </span>
                                <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                    <span class="price-original">
                                        <?php echo formatPrice($product['original_price']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <button class="add-to-cart-btn"
                                onclick="addToCart(<?php echo $product['id']; ?>, null, null, event)">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
