    <?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/pages/wishlist.php';
    redirect('/auth/login.php');
}

$user_id = getCurrentUserId();

// Get wishlist items
$stmt = $db->prepare("
    SELECT w.*, p.name, p.brand, p.price, p.main_image, p.id as product_id
    FROM wishlists w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<style>
    .wishlist-page {
        padding: 4rem 0;
    }

    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
    }

    .empty-wishlist {
        text-align: center;
        padding: 4rem 2rem;
    }
</style>

<main class="wishlist-page">
    <div class="container">
        <h1 class="section-title">My Wishlist</h1>

        <?php if (empty($wishlist_items)): ?>
            <div class="empty-wishlist">
                <h2>Your wishlist is empty</h2>
                <p style="margin: 1rem 0;">Save items you like to see them here later</p>
                <a href="/" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="/pages/product.php?id=<?php echo $item['product_id']; ?>" class="product-image-link">
                                <img src="/uploads/products/<?php echo htmlspecialchars($item['main_image']); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    onerror="this.src='/assets/images/placeholder.jpg'">
                            </a>

                            <button class="wishlist-btn active"
                                onclick="toggleWishlist(<?php echo $item['product_id']; ?>, this)"
                                aria-label="Remove from wishlist">
                                <svg viewBox="0 0 24 24">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        <div class="product-info">
                            <p class="product-brand">
                                <?php echo htmlspecialchars($item['brand']); ?>
                            </p>
                            <h3 class="product-name">
                                <a href="/pages/product.php?id=<?php echo $item['product_id']; ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <span class="price-current">
                                    <?php echo formatPrice($item['price']); ?>
                                </span>
                            </div>
                            <button class="add-to-cart-btn"
                                onclick="addToCart(<?php echo $item['product_id']; ?>, null, null, event)">
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
