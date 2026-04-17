<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Get featured and new arrival products
$stmt = $db->prepare("SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 LIMIT 8");
$stmt->execute();
$featured_products = $stmt->fetchAll();

$stmt = $db->prepare("SELECT * FROM products WHERE is_active = 1 AND is_new_arrival = 1 ORDER BY created_at DESC LIMIT 12");
$stmt->execute();
$new_arrivals = $stmt->fetchAll();

include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Step Into Style</h1>
            <p>Discover the latest in streetwear, sneakers, and exclusive drops</p>
            <a href="/pages/new-arrivals.php" class="btn btn-outline">Shop New Arrivals</a>
        </div>
    </section>

    <!-- Section 1: New Arrivals (Interactive) -->
    <section class="style-transformation">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">The Fresh Drop</span>
                <h2>New Arrivals</h2>
                <p>Stay ahead of the curve with our latest selection of high-heat sneakers and apparel. Updated daily
                    with the most anticipated releases.</p>
                <a href="/pages/new-arrivals.php" class="btn btn-primary">Shop All New</a>
            </div>
            <div class="transformation-visual">
                <div class="interactive-container">
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1400&auto=format&fit=crop"
                        alt="Standard Drop" class="img-base">
                    <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=1400&auto=format&fit=crop"
                        alt="Premium Drop" class="img-premium">
                    <div class="premium-label">LATEST DROP</div>
                    <div class="scan-line"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Footwear (Interactive - Alternate) -->
    <section class="style-transformation alternate">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">Step Into Style</span>
                <h2>Footwear</h2>
                <p>From iconic court classics to the latest performance runners, find your perfect pair in our curated
                    collection of global brands.</p>
                <a href="/pages/footwear.php" class="btn btn-primary">Explore Footwear</a>
            </div>
            <div class="transformation-visual">
                <div class="interactive-container">
                    <img src="https://images.unsplash.com/photo-1460353581641-37baddab0fa2?q=80&w=1400&auto=format&fit=crop"
                        alt="Classic style" class="img-base">
                    <img src="https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=1400&auto=format&fit=crop"
                        alt="Modern style" class="img-premium">
                    <div class="premium-label">PREMIUM UPGRADE</div>
                    <div class="scan-line"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <?php if (!empty($featured_products)): ?>
        <section class="section">
            <div class="container">
                <h2 class="section-title">Featured Products</h2>
                <div class="grid grid-4">
                    <?php foreach ($featured_products as $product):
                        $all_images = !empty($product['images']) ? json_decode($product['images'], true) : [];
                        $images = array_values(array_filter($all_images));
                        $main_image = $product['main_image'] ?: ($images[0] ?? 'placeholder.jpg');
                        ?>
                        <div class="product-card" data-brand="<?php echo htmlspecialchars($product['brand']); ?>"
                            data-price="<?php echo $product['price']; ?>" data-sizes='<?php echo $product['sizes']; ?>'
                            data-colors='<?php echo $product['colors']; ?>' data-gender="<?php echo $product['gender']; ?>"
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

                                <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>, this)"
                                    aria-label="Add to wishlist">
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
                                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Section 3: Apparel (Interactive) -->
    <section class="style-transformation">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">Seamless Streetwear</span>
                <h2>Apparel</h2>
                <p>Elevate your everyday wardrobe with our premium streetwear essentials. Comfortable, versatile, and
                    designed for the modern lifestyle.</p>
                <a href="/pages/apparel.php" class="btn btn-primary">Shop Apparel</a>
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

    <!-- Section 4: Accessories (Interactive - Alternate) -->
    <section class="style-transformation alternate">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">The Final Touch</span>
                <h2>Accessories</h2>
                <p>Complete your look with our range of premium accessories. From high-quality caps to essential sneaker
                    care products.</p>
                <a href="/pages/accessories.php" class="btn btn-primary">Shop Accessories</a>
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

    <!-- Section 5: Brands (Interactive) -->
    <section class="style-transformation">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">Iconic Collaborations</span>
                <h2>Featured Brands</h2>
                <p>Discover the biggest names in the game. We partner with the world's most influential brands to bring
                    you exclusive releases.</p>
                <a href="/pages/brands.php" class="btn btn-primary">View All Brands</a>
            </div>
            <div class="transformation-visual">
                <div class="interactive-container">
                    <img src="https://images.unsplash.com/photo-1518002171953-a080ee817e1f?q=80&w=1400&auto=format&fit=crop"
                        alt="Brand Lineup" class="img-base">
                    <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=1400&auto=format&fit=crop"
                        alt="Exclusive Brand" class="img-premium">
                    <div class="premium-label">AUTHENTIC BRANDS</div>
                    <div class="scan-line"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 6: Sale (Interactive - Alternate) -->
    <section class="style-transformation alternate">
        <div class="container" style="margin-bottom: 4rem;">
            <div class="transformation-content">
                <span class="transformation-badge">Last Chance Luxury</span>
                <h2>Sale</h2>
                <p>Grab your favorites at unbeatable prices. High-quality streetwear and sneakers now available at a
                    discount.</p>
                <a href="/pages/sale.php" class="btn btn-primary" style="background-color: var(--color-red);">Check
                    Final Sale</a>
            </div>
            <div class="transformation-visual">
                <div class="interactive-container">
                    <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=1400&auto=format&fit=crop"
                        alt="Standard Price" class="img-base">
                    <img src="https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=1400&auto=format&fit=crop"
                        alt="Sale Price" class="img-premium">
                    <div class="premium-label">UP TO 50% OFF</div>
                    <div class="scan-line"></div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
