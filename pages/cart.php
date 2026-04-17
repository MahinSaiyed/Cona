<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get cart items
$user_id = getCurrentUserId();
$session_id = session_id();

if ($user_id) {
    $stmt = $db->prepare("
        SELECT c.*, p.name, p.brand, p.price, p.main_image, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
} else {
    $stmt = $db->prepare("
        SELECT c.*, p.name, p.brand, p.price, p.main_image, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.session_id = ?
    ");
    $stmt->execute([$session_id]);
}

$cart_items = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = calculateShipping($subtotal);
$tax = calculateTax($subtotal);
$total = $subtotal + $shipping + $tax;

include __DIR__ . '/../includes/header.php';
?>

<style>
    .cart-page {
        padding: 4rem 0;
    }

    .cart-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .cart-items {
        background: white;
        padding: 2rem;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 100px 1fr auto;
        gap: 1.5rem;
        padding: 1.5rem 0;
        border-bottom: 1px solid var(--color-border);
    }

    .cart-item:first-child {
        padding-top: 0;
    }

    .cart-item-image img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }

    .cart-item-details h3 {
        font-size: 1.125rem;
        margin-bottom: 0.5rem;
    }

    .cart-item-brand {
        color: var(--color-gray);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .cart-item-meta {
        font-size: 0.875rem;
        color: var(--color-gray);
    }

    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        border: 1px solid var(--color-border);
    }

    .quantity-selector button {
        padding: 0.5rem 0.75rem;
        background: var(--color-light-gray);
        border: none;
    }

    .quantity-selector input {
        width: 50px;
        text-align: center;
        border: none;
    }

    .remove-btn {
        color: var(--color-red);
        text-decoration: underline;
        font-size: 0.875rem;
    }

    .cart-summary {
        background: var(--color-light-gray);
        padding: 2rem;
        height: fit-content;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .summary-total {
        font-size: 1.25rem;
        font-weight: var(--font-weight-bold);
        padding-top: 1rem;
        border-top: 2px solid var(--color-black);
    }

    .empty-cart {
        text-align: center;
        padding: 4rem 2rem;
    }
</style>

<main class="cart-page">
    <div class="container">
        <h1 class="section-title">Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p style="margin: 1rem 0;">Start shopping to add items to your cart</p>
                <a href="/" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="/uploads/products/<?php echo htmlspecialchars($item['main_image']); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    onerror="this.src='/assets/images/placeholder.jpg'">
                            </div>

                            <div class="cart-item-details">
                                <h3>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </h3>
                                <p class="cart-item-brand">
                                    <?php echo htmlspecialchars($item['brand']); ?>
                                </p>
                                <div class="cart-item-meta">
                                    <?php if ($item['size']): ?>
                                        <span>Size:
                                            <?php echo htmlspecialchars($item['size']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($item['color']): ?>
                                        <span> | Color:
                                            <?php echo htmlspecialchars($item['color']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p style="font-weight: var(--font-weight-bold); margin-top: 0.5rem;">
                                    <?php echo formatPrice($item['price']); ?>
                                </p>
                            </div>

                            <div class="cart-item-actions">
                                <div class="quantity-selector">
                                    <button
                                        onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo max(1, $item['quantity'] - 1); ?>)">-</button>
                                    <input type="number" value="<?php echo $item['quantity']; ?>" min="1"
                                        max="<?php echo $item['stock']; ?>" readonly>
                                    <button
                                        onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo min($item['stock'], $item['quantity'] + 1); ?>)">+</button>
                                </div>
                                <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h2 style="margin-bottom: 1.5rem;">Order Summary</h2>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>
                            <?php echo formatPrice($subtotal); ?>
                        </span>
                    </div>

                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>
                            <?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?>
                        </span>
                    </div>

                    <?php if ($tax > 0): ?>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span>
                                <?php echo formatPrice($tax); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>
                            <?php echo formatPrice($total); ?>
                        </span>
                    </div>

                    <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                        <p style="font-size: 0.875rem; margin: 1rem 0; color: var(--color-red);">
                            Add
                            <?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal); ?> more for FREE shipping!
                        </p>
                    <?php endif; ?>

                    <a href="/pages/checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        Proceed to Checkout
                    </a>

                    <a href="/" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>