<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) {
    redirect('/');
}

// Get product details
$stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/');
}

// Update view count
$stmt = $db->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
$stmt->execute([$product_id]);

// Get related products (same category)
$stmt = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

$all_images = !empty($product['images']) ? json_decode($product['images'], true) : [];
$images = array_values(array_filter($all_images));
$sizes = !empty($product['sizes']) ? json_decode($product['sizes'], true) : [];
$colors = !empty($product['colors']) ? json_decode($product['colors'], true) : [];
$main_image = $product['main_image'] ?: ($images[0] ?? 'placeholder.jpg');

// Check if in wishlist
$in_wishlist = false;
if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT * FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([getCurrentUserId(), $product_id]);
    $in_wishlist = $stmt->fetch() ? true : false;
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.product-detail {
    padding: 2rem 0;
}

.product-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 4rem;
}

.product-gallery {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.main-image {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: var(--color-light-gray);
    margin-bottom: 1rem;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-strip {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 0.5rem;
}

.thumbnail {
    aspect-ratio: 1 / 1;
    border: 2px solid var(--color-border);
    cursor: pointer;
    transition: border-color var(--transition-fast);
}

.thumbnail.active,
.thumbnail:hover {
    border-color: var(--color-black);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details h1 {
    font-size: 2rem;
    font-weight: var(--font-weight-bold);
    margin-bottom: 0.5rem;
}

.brand-link {
    font-size: 1.125rem;
    color: var(--color-gray);
    margin-bottom: 1rem;
    display: block;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.price-section {
    margin: 2rem 0;
}

.current-price {
    font-size: 2rem;
    font-weight: var(--font-weight-bold);
}

.original-price {
    font-size: 1.25rem;
    color: var(--color-gray);
    text-decoration: line-through;
    margin-left: 1rem;
}

.discount-badge {
    background: var(--color-red);
    color: white;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    font-weight: var(--font-weight-bold);
    margin-left: 1rem;
}

.size-selector,
.color-selector {
    margin: 2rem 0;
}

.selector-label {
    font-weight: var(--font-weight-semibold);
    margin-bottom: 1rem;
    display: block;
}

.size-options,
.color-options {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.size-option,
.color-option {
    padding: 0.75rem 1.5rem;
    border: 2px solid var(--color-border);
    background: white;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.size-option:hover,
.color-option:hover {
    border-color: var(--color-black);
}

.size-option.selected,
.color-option.selected {
    background: var(--color-black);
    color: white;
    border-color: var(--color-black);
}

.action-buttons {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    margin: 2rem 0;
}

.stock-status {
    margin: 1rem 0;
    padding: 0.75rem;
    background: var(--color-light-gray);
    font-weight: var(--font-weight-semibold);
}

.stock-status.in-stock {
    color: var(--color-success);
}

.stock-status.low-stock {
    color: #ff9800;
}

.stock-status.out-of-stock {
    color: var(--color-error);
}

.product-description {
    margin: 2rem 0;
    line-height: 1.8;
}

.product-meta {
    margin: 2rem 0;
    padding: 1.5rem;
    background: var(--color-light-gray);
}

.meta-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--color-border);
}

.meta-item:last-child {
    border-bottom: none;
}

.related-products {
    margin-top: 4rem;
}

@media (max-width: 768px) {
    .product-layout {
        grid-template-columns: 1fr;
    }
    
    .product-gallery {
        position: static;
    }
}
</style>

<main class="product-detail">
    <!-- Page Header: Interactive Transformation -->
    <section class="style-transformation" style="padding: 3rem 0; background-color: var(--color-light-gray); margin-bottom: 2rem;">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">Product Spotlight</span>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>Experience the detail and craft of this <?php echo htmlspecialchars($product['brand']); ?> essential. Move your mouse to see every angle in high definition.</p>
            </div>
            <div class="transformation-visual">
                <div class="interactive-container">
                    <img src="/uploads/products/<?php echo htmlspecialchars($main_image); ?>" alt="Main View" class="img-base">
                    <img src="<?php echo isset($images[0]) ? (strpos($images[0], 'http') === 0 ? $images[0] : '/uploads/products/' . htmlspecialchars($images[0])) : '/uploads/products/' . htmlspecialchars($main_image); ?>" alt="Alternative View" class="img-premium">
                    <div class="premium-label">ELITE DETAIL</div>
                    <div class="scan-line"></div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="product-layout">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="main-image">
                    <div class="interactive-container" style="height: 100%;">
                        <img src="/uploads/products/<?php echo htmlspecialchars($main_image); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="main-product-image img-base"
                             onerror="this.src='/assets/images/placeholder.jpg'">
                        <?php if (isset($images[0])): ?>
                        <img src="<?php echo (strpos($images[0], 'http') === 0) ? $images[0] : '/uploads/products/' . htmlspecialchars($images[0]); ?>" 
                             alt="Premium View" 
                             class="img-premium"
                             onerror="this.style.display='none'">
                        <div class="premium-label">PREMIUM DETAIL</div>
                        <div class="scan-line"></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($images)): ?>
                <div class="thumbnail-strip">
                    <?php 
                    $labels = ['Front', 'Back', 'Right', 'Left', 'Top', 'Bottom'];
                    foreach ($images as $index => $image): 
                        $label = $labels[$index] ?? 'View ' . ((int)$index + 1);
                    ?>
                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" title="<?php echo $label; ?>">
                        <img src="<?php echo (strpos($image, 'http') === 0) ? $image : '/uploads/products/' . htmlspecialchars($image); ?>" 
                             alt="<?php echo $label; ?>"
                             onerror="this.src='/assets/images/placeholder.jpg'">
                        <span style="display: block; text-align: center; font-size: 10px; color: var(--color-gray);"><?php echo $label; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div class="product-details">
                <a href="/pages/footwear.php?brand=<?php echo urlencode($product['brand']); ?>" class="brand-link">
                    <?php echo htmlspecialchars($product['brand']); ?>
                </a>
                
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="price-section">
                    <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): 
                        $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                    ?>
                    <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                    <span class="discount-badge"><?php echo $discount; ?>% OFF</span>
                    <?php endif; ?>
                </div>

                <!-- Stock Status -->
                <div class="stock-status <?php 
                    echo $product['stock'] <= 0 ? 'out-of-stock' : ($product['stock'] < 10 ? 'low-stock' : 'in-stock');
                ?>">
                    <?php 
                    if ($product['stock'] <= 0) {
                        echo '❌ Out of Stock';
                    } elseif ($product['stock'] < 10) {
                        echo '⚠️ Only ' . $product['stock'] . ' left in stock';
                    } else {
                        echo '✓ In Stock';
                    }
                    ?>
                </div>

                <!-- Size Selector -->
                <?php if (!empty($sizes)): ?>
                <div class="size-selector">
                    <label class="selector-label">Select Size:</label>
                    <div class="size-options">
                        <?php foreach ($sizes as $size): ?>
                        <button class="size-option" onclick="selectSize(this)" data-size="<?php echo htmlspecialchars($size); ?>">
                            <?php echo htmlspecialchars($size); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Color Selector -->
                <?php if (!empty($colors)): ?>
                <div class="color-selector">
                    <label class="selector-label">Select Color:</label>
                    <div class="color-options">
                        <?php foreach ($colors as $color): ?>
                        <button class="color-option" onclick="selectColor(this)" data-color="<?php echo htmlspecialchars($color); ?>">
                            <?php echo htmlspecialchars($color); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="addProductToCart()" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        Add to Cart
                    </button>
                    <button class="btn btn-secondary" onclick="toggleWishlist(<?php echo $product_id; ?>, this)" 
                            <?php echo $in_wishlist ? 'class="btn btn-secondary active"' : 'class="btn btn-secondary"'; ?>>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $in_wishlist ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </button>
                </div>

                <!-- Description -->
                <div class="product-description">
                    <h3 style="margin-bottom: 1rem;">Product Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?></p>
                </div>

                <!-- Product Meta -->
                <div class="product-meta">
                    <div class="meta-item">
                        <span>SKU</span>
                        <strong><?php echo htmlspecialchars($product['sku'] ?: 'N/A'); ?></strong>
                    </div>
                    <div class="meta-item">
                        <span>Category</span>
                        <strong>
                            <?php 
                            $stmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
                            $stmt->execute([$product['category_id']]);
                            $category = $stmt->fetch();
                            echo htmlspecialchars($category['name'] ?? 'Unknown');
                            ?>
                        </strong>
                    </div>
                    <div class="meta-item">
                        <span>Gender</span>
                        <strong><?php echo ucfirst($product['gender']); ?></strong>
                    </div>
                    <div class="meta-item">
                        <span>Views</span>
                        <strong><?php echo number_format($product['views']); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h2 class="section-title">You May Also Like</h2>
            <div class="grid grid-4">
                <?php foreach ($related_products as $related): 
                    $all_rel_images = !empty($related['images']) ? json_decode($related['images'], true) : [];
                    $rel_images = array_values(array_filter($all_rel_images));
                    $rel_main = $related['main_image'] ?: ($rel_images[0] ?? 'placeholder.jpg');
                ?>
                <div class="product-card" data-gallery='<?php echo json_encode($rel_images); ?>'>
                    <div class="product-image">
                        <a href="/pages/product.php?id=<?php echo $related['id']; ?>" class="product-image-link">
                            <img src="/uploads/products/<?php echo htmlspecialchars($rel_main); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 onerror="this.src='/assets/images/placeholder.jpg'">
                        </a>
                        
                        <?php if ($related['is_new_arrival']): ?>
                        <div class="product-badges">
                            <span class="badge-new">New</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <p class="product-brand"><?php echo htmlspecialchars($related['brand']); ?></p>
                        <h3 class="product-name">
                            <a href="/pages/product.php?id=<?php echo $related['id']; ?>">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h3>
                        <div class="product-price">
                            <span class="price-current"><?php echo formatPrice($related['price']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Product Reviews Section -->
        <div class="product-reviews" style="margin-top: 4rem;">
            <h2 class="section-title">Customer Reviews</h2>
            
            <!-- Reviews Summary -->
            <div id="reviews-summary" class="reviews-summary">
                <div class="summary-loading">Loading reviews...</div>
            </div>

            <!-- Write Review Button (for logged-in users) -->
            <?php if (isLoggedIn()): ?>
            <button class="btn btn-secondary" onclick="openReviewForm()" style="margin: 2rem 0;">
                Write a Review
            </button>
            <?php else: ?>
            <p style="margin: 2rem 0; color: var(--color-gray);">
                <a href="/auth/login.php" style="color: var(--color-red); text-decoration: underline;">Login</a> to write a review
            </p>
            <?php endif; ?>

            <!-- Review Form (initially hidden) -->
            <div id="review-form-container" class="review-form-container" style="display: none; margin: 2rem 0; padding: 2rem; background: var(--color-light-gray);">
                <h3 style="margin-bottom: 1.5rem;">Write Your Review</h3>
                <form id="review-form">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Your Rating *</label>
                        <div class="star-rating-input">
                            <span class="star" data-rating="1">☆</span>
                            <span class="star" data-rating="2">☆</span>
                            <span class="star" data-rating="3">☆</span>
                            <span class="star" data-rating="4">☆</span>
                            <span class="star" data-rating="5">☆</span>
                        </div>
                        <input type="hidden" name="rating"id="rating-value" required>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Review Title</label>
                        <input type="text" name="review_title" class="form-control" placeholder="Sum up your experience" maxlength="200">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Your Review *</label>
                        <textarea name="review_text" class="form-control" rows="5" placeholder="Share your thoughts about this product..." required minlength="10"></textarea>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                        <button type="button" class="btn btn-secondary" onclick="closeReviewForm()">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Reviews List -->
            <div id="reviews-list" class="reviews-list">
                <div class="reviews-loading">Loading reviews...</div>
            </div>

            <!-- Pagination -->
            <div id="reviews-pagination" class="reviews-pagination"></div>
        </div>

    </div>
</main>

<script>
let selectedSize = null;
let selectedColor = null;

function selectSize(button) {
    document.querySelectorAll('.size-option').forEach(btn => btn.classList.remove('selected'));
    button.classList.add('selected');
    selectedSize = button.dataset.size;
}

function selectColor(button) {
    document.querySelectorAll('.color-option').forEach(btn => btn.classList.remove('selected'));
    button.classList.add('selected');
    selectedColor = button.dataset.color;
}

function addProductToCart() {
    addToCart(<?php echo $product_id; ?>, selectedSize, selectedColor);
}

// Auto-select first size and color if available
document.addEventListener('DOMContentLoaded', function() {
    const firstSize = document.querySelector('.size-option');
    if (firstSize) {
        firstSize.click();
    }
    
    const firstColor = document.querySelector('.color-option');
    if (firstColor) {
        firstColor.click();
    }
});

</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
