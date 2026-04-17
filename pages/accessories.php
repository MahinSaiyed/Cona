<?php
// Accessories collection page
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get category
$category_slug = 'accessories';
$stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$category_slug]);
$category = $stmt->fetch();

if (!$category) {
    $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES ('Accessories', 'accessories', 'Bags, caps, and more')");
    $stmt->execute();
    $category_id = $db->lastInsertId();
} else {
    $category_id = $category['id'];
}

// Get filters
$brand_filter = $_GET['brand'] ?? '';
$price_filter = $_GET['price'] ?? '';

// Build query
$query = "SELECT p.* FROM products p ";
$conditions = ["p.is_active = 1", "p.category_id = ?"];
$params = [$category_id];

if ($brand_filter) {
    $conditions[] = "p.brand = ?";
    $params[] = $brand_filter;
}

if ($price_filter) {
    $parts = explode('-', $price_filter);
    if (count($parts) === 2) {
        $conditions[] = "p.price >= ? AND p.price <= ?";
        $params[] = floatval($parts[0]);
        $params[] = floatval($parts[1]);
    }
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
                <span class="transformation-badge">The Final Touch</span>
                <h2>Accessories</h2>
                <p>Complete your look with our range of premium accessories. From high-quality caps to essential sneaker
                    care products.</p>
            </div>
            <div class="transformation-visual">
                <div class="interactive-container">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=1400&auto=format&fit=crop"
                        alt="Basic Accessory" class="img-base">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=1400&auto=format&fit=crop"
                        alt="Premium Gear" class="img-premium">
                    <div class="premium-label">ELITE GEAR</div>
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
                <label class="filter-label">Price Range</label>
                <select name="price" class="filter-select" onchange="filterProducts()">
                    <option value="">All Prices</option>
                    <option value="0-999" <?php echo $price_filter === '0-999' ? 'selected' : ''; ?>>Under ₹999</option>
                    <option value="1000-2999" <?php echo $price_filter === '1000-2999' ? 'selected' : ''; ?>>₹1,000 -
                        ₹2,999</option>
                    <option value="3000-4999" <?php echo $price_filter === '3000-4999' ? 'selected' : ''; ?>>₹3,000 -
                        ₹4,999</option>
                    <option value="5000-99999" <?php echo $price_filter === '5000-99999' ? 'selected' : ''; ?>>₹5,000+
                    </option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Type</label>
                <select name="type" class="filter-select" onchange="filterProducts()">
                    <option value="">All Types</option>
                    <option value="bags">Bags</option>
                    <option value="caps">Caps</option>
                    <option value="socks">Socks</option>
                    <option value="belts">Belts</option>
                    <option value="wallets">Wallets</option>
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
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No accessories found</h3>
                <p style="color: var(--color-gray); margin-bottom: 2rem;">We couldn't find any accessories matching your
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
                    ?>
                    <div class="product-card" data-brand="<?php echo htmlspecialchars($product['brand']); ?>"
                        data-price="<?php echo $product['price']; ?>" data-gallery='<?php echo json_encode($images); ?>'>
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
