<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/pages/checkout.php';
    redirect('/auth/login.php');
}

// Get cart items
$user_id = getCurrentUserId();
$stmt = $db->prepare("
    SELECT c.*, p.name, p.brand, p.price, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    redirect('/pages/cart.php');
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = calculateShipping($subtotal);
$tax = calculateTax($subtotal);
$total = $subtotal + $shipping + $tax;

// Get user addresses
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<style>
.checkout-page {
    padding: 4rem 0;
}

.checkout-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.checkout-section {
    background: white;
    padding: 2rem;
    margin-bottom: 2rem;
}

.section-title-small {
    font-size: 1.5rem;
    font-weight: var(--font-weight-bold);
    margin-bottom: 1.5rem;
}

.address-card {
    border: 2px solid var(--color-border);
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.address-card.selected {
    border-color: var(--color-black);
    background: var(--color-light-gray);
}

.payment-option {
    border: 2px solid var(--color-border);
    padding: 1.5rem;
    margin-bottom: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all var(--transition-fast);
}

.payment-option.selected {
    border-color: var(--color-black);
    background: var(--color-light-gray);
}

.payment-icon {
    width: 40px;
    height: 40px;
    background: var(--color-light-gray);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    width: 500px;
    max-width: 90%;
    border-radius: 4px;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--color-border);
}
</style>

<main class="checkout-page">
    <div class="container">
        <h1 class="section-title">Checkout</h1>

        <form method="POST" action="/api/place-order.php" id="checkout-form">
            <div class="checkout-container">
                <div>
                    <!-- Shipping Address -->
                    <div class="checkout-section">
                        <h2 class="section-title-small">Shipping Address</h2>
                        
                        <?php if (!empty($addresses)): ?>
                            <?php 
                            $has_default = array_reduce($addresses, function($carry, $item) {
                                return $carry || $item['is_default'];
                            }, false);
                            ?>
                            <?php foreach ($addresses as $index => $address): 
                                $is_selected = $address['is_default'] || (!$has_default && $index === 0);
                            ?>
                            <div class="address-card <?php echo $is_selected ? 'selected' : ''; ?>" 
                                 onclick="selectAddress(this, <?php echo $address['id']; ?>)">
                                <input type="radio" name="address_id" value="<?php echo $address['id']; ?>" 
                                       <?php echo $is_selected ? 'checked' : ''; ?> style="display:none;">
                                <p><strong><?php echo htmlspecialchars($address['full_name']); ?></strong></p>
                                <p><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                <?php if ($address['address_line2']): ?>
                                <p><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> <?php echo htmlspecialchars($address['pincode']); ?></p>
                                <p>Phone: <?php echo htmlspecialchars($address['phone']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No saved addresses. Please add a new address.</p>
                        <?php endif; ?>

                        <button type="button" class="btn btn-secondary" style="margin-top: 1rem;" onclick="showAddressForm()">
                            + Add New Address
                        </button>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h2 class="section-title-small">Payment Method</h2>

                        <div class="payment-option selected" onclick="selectPayment(this, 'card')">
                            <input type="radio" name="payment_method" value="card" checked style="display:none;">
                            <div class="payment-icon">💳</div>
                            <div>
                                <strong>Card Payment</strong>
                                <p style="font-size: 0.875rem; color: var(--color-gray);">Credit/Debit Card via Razorpay</p>
                            </div>
                        </div>

                        <div class="payment-option" onclick="selectPayment(this, 'whatsapp')">
                            <input type="radio" name="payment_method" value="whatsapp" style="display:none;">
                            <div class="payment-icon">💬</div>
                            <div>
                                <strong>WhatsApp Payment</strong>
                                <p style="font-size: 0.875rem; color: var(--color-gray);">Pay via WhatsApp confirmation</p>
                            </div>
                        </div>

                        <div class="payment-option" onclick="selectPayment(this, 'cod')">
                            <input type="radio" name="payment_method" value="cod" style="display:none;">
                            <div class="payment-icon">💵</div>
                            <div>
                                <strong>Cash on Delivery</strong>
                                <p style="font-size: 0.875rem; color: var(--color-gray);">Pay when you receive</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <div class="cart-summary">
                        <h2 style="margin-bottom: 1.5rem;">Order Summary</h2>
                        
                        <div class="summary-row">
                            <span>Subtotal (<?php echo count($cart_items); ?> items)</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>

                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?></span>
                        </div>

                        <?php if ($tax > 0): ?>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem;">
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Address Modal -->
<div id="address-modal" class="modal">
    <div class="modal-content">
        <h2 style="margin-bottom: 1.5rem;">Add New Address</h2>
        <form id="address-form" onsubmit="saveAddress(event)">
            <div class="form-group">
                <label>Full Name*</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Phone Number*</label>
                <input type="tel" name="phone" required pattern="[6-9]\d{9}" title="Enter valid 10-digit Indian phone number">
            </div>
            <div class="form-group">
                <label>Address Line 1*</label>
                <input type="text" name="address_line1" required>
            </div>
            <div class="form-group">
                <label>Address Line 2</label>
                <input type="text" name="address_line2">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>City*</label>
                    <input type="text" name="city" required>
                </div>
                <div class="form-group">
                    <label>State*</label>
                    <input type="text" name="state" required>
                </div>
            </div>
            <div class="form-group">
                <label>Pincode*</label>
                <input type="text" name="pincode" required pattern="\d{6}">
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Address</button>
                <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="hideAddressForm()">Cancel</button>
            </div>
        </form>
    </div>
</div>


<script>
function selectAddress(element, addressId) {
    document.querySelectorAll('.address-card').forEach(card => card.classList.remove('selected'));
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}

function selectPayment(element, method) {
    document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}

function showAddressForm() {
    document.getElementById('address-modal').style.display = 'block';
}

function hideAddressForm() {
    document.getElementById('address-modal').style.display = 'none';
    document.getElementById('address-form').reset();
}

async function saveAddress(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/api/add-address.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload(); // Simple reload to show new address
        } else {
            showToast(data.message || 'Failed to save address', 'error');
        }
    } catch (error) {
        showToast('An error occurred while saving address', 'error');
        console.error(error);
    }
}

// Handle checkout form submission via AJAX
document.getElementById('checkout-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    if (!formData.get('address_id')) {
        showToast('Please select a delivery address', 'error');
        return;
    }

    try {
        const response = await fetch('/api/place-order.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('Order placed successfully!', 'success');
            setTimeout(() => {
                window.location.href = data.data.redirect || '/';
            }, 2000);
        } else {
            showToast(data.message || 'Failed to place order', 'error');
        }
    } catch (error) {
        showToast('An error occurred while placing order', 'error');
        console.error(error);
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
