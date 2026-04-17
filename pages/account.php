<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user_id = getCurrentUserId();
$user = getUserData($db, $user_id);

// Get user orders
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get user addresses
$stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<style>
    .account-page {
        padding: 3rem 0;
    }

    .account-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
    }

    .account-sidebar {
        background: white;
        padding: 1.5rem;
        height: fit-content;
    }

    .account-menu {
        list-style: none;
    }

    .account-menu a {
        display: block;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border-radius: 4px;
        transition: all var(--transition-fast);
    }

    .account-menu a:hover,
    .account-menu a.active {
        background: var(--color-light-gray);
    }

    .account-content {
        background: white;
        padding: 2rem;
    }

    .section-heading {
        font-size: 1.5rem;
        font-weight: var(--font-weight-bold);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--color-black);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .info-item {
        padding: 1rem;
        background: var(--color-light-gray);
    }

    .info-label {
        font-size: 0.875rem;
        color: var(--color-gray);
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-weight: var(--font-weight-semibold);
    }

    .order-card {
        border: 1px solid var(--color-border);
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--color-border);
    }

    .address-card {
        border: 1px solid var(--color-border);
        padding: 1.5rem;
        margin-bottom: 1rem;
        position: relative;
    }

    .default-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--color-success);
        color: white;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        border-radius: 20px;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        padding: 2.5rem;
        width: 100%;
        max-width: 500px;
        position: relative;
        box-shadow: var(--shadow-lg);
    }

    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--color-gray);
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: var(--font-weight-bold);
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: var(--font-weight-semibold);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--color-border);
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-black);
    }
</style>

<main class="account-page">
    <div class="container">
        <div class="account-layout">
            <!-- Sidebar -->
            <aside class="account-sidebar">
                <h2 style="margin-bottom: 1.5rem;">My Account</h2>
                <ul class="account-menu">
                    <li><a href="#profile" class="active">Profile</a></li>
                    <li><a href="#orders">Orders</a></li>
                    <li><a href="#addresses">Addresses</a></li>
                    <li><a href="/pages/wishlist.php">Wishlist</a></li>
                    <li><a href="/auth/logout.php" style="color: var(--color-red);">Logout</a></li>
                </ul>
            </aside>

            <!-- Content -->
            <div class="account-content">
                <!-- Profile Section -->
                <section id="profile">
                    <h2 class="section-heading">Profile Information</h2>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Member Since</div>
                            <div class="info-value">
                                <?php echo date('F Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary" onclick="openModal('profileModal')">Edit Profile</button>
                </section>

                <!-- Orders Section -->
                <section id="orders" style="margin-top: 3rem;">
                    <h2 class="section-heading">Recent Orders</h2>

                    <?php if (empty($orders)): ?>
                        <p style="text-align: center; padding: 2rem; color: var(--color-gray);">
                            No orders yet. <a href="/" style="color: var(--color-red); text-decoration: underline;">Start
                                shopping!</a>
                        </p>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div>
                                        <strong>Order #
                                            <?php echo htmlspecialchars($order['order_number']); ?>
                                        </strong><br>
                                        <small>Placed on
                                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                        </small>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 1.25rem; font-weight: bold;">
                                            <?php echo formatPrice($order['total']); ?>
                                        </div>
                                        <span class="badge-status badge-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong>Payment:</strong>
                                        <?php echo strtoupper($order['payment_method']); ?>
                                        (
                                        <?php echo ucfirst($order['payment_status']); ?>)
                                    </div>
                                    <a href="/pages/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

                <!-- Addresses Section -->
                <section id="addresses" style="margin-top: 3rem;">
                    <h2 class="section-heading">Saved Addresses</h2>

                    <?php if (empty($addresses)): ?>
                        <p style="text-align: center; padding: 2rem; color: var(--color-gray);">
                            No saved addresses.
                        </p>
                    <?php else: ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-card">
                                <?php if ($address['is_default']): ?>
                                    <span class="default-badge">Default</span>
                                <?php endif; ?>

                                <p><strong>
                                        <?php echo htmlspecialchars($address['full_name']); ?>
                                    </strong></p>
                                <p>
                                    <?php echo htmlspecialchars($address['address_line1']); ?>
                                </p>
                                <?php if ($address['address_line2']): ?>
                                    <p>
                                        <?php echo htmlspecialchars($address['address_line2']); ?>
                                    </p>
                                <?php endif; ?>
                                <p>
                                    <?php echo htmlspecialchars($address['city']); ?>,
                                    <?php echo htmlspecialchars($address['state']); ?>
                                    <?php echo htmlspecialchars($address['pincode']); ?>
                                </p>
                                <p>Phone:
                                    <?php echo htmlspecialchars($address['phone']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <button class="btn btn-primary" style="margin-top: 1rem;" onclick="openModal('addressModal')">+ Add
                        New Address</button>
                </section>
            </div>
        </div>
    </div>
</main>

<!-- Edit Profile Modal -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('profileModal')">&times;</span>
        <h2 class="modal-title">Edit Profile</h2>
        <form id="profileForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
        </form>
    </div>
</div>

<!-- Add Address Modal -->
<div id="addressModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('addressModal')">&times;</span>
        <h2 class="modal-title">Add New Address</h2>
        <form id="addressForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" required placeholder="Receiver's name">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" required placeholder="10-digit mobile number">
            </div>
            <div class="form-group">
                <label>Address Line 1</label>
                <input type="text" name="address_line1" class="form-control" required placeholder="House No, Building Name, Street">
            </div>
            <div class="form-group">
                <label>Address Line 2 (Optional)</label>
                <input type="text" name="address_line2" class="form-control" placeholder="Area, Colony, Landmark">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" name="pincode" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Address</button>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}

// Handle Profile Update
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/api/update-profile.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
        console.error('Error:', error);
    }
});

// Handle Add Address
document.getElementById('addressForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/api/add-address.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
        console.error('Error:', error);
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
