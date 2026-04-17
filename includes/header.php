<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo SITE_NAME; ?>
    </title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/responsive.css">
</head>

<body>
    <header class="header">
        <div class="header-top">
            <div class="container">
                Free shipping on orders above ₹
                <?php echo FREE_SHIPPING_THRESHOLD; ?>
            </div>
        </div>

        <?php if (isAdminLoggedIn()): ?>
            <div class="admin-top-bar">
                <div class="container d-flex justify-between align-center">
                    <span>Logged in as <strong>Admin</strong></span>
                    <div class="admin-links">
                        <a href="/admin/dashboard.php">Go to Dashboard</a>
                        <span class="separator">|</span>
                        <a href="/admin/logout.php">Logout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="header-main">
            <button class="mobile-menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <a href="/" class="logo">
                <?php echo SITE_NAME; ?>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="/pages/new-arrivals.php">New Arrivals</a></li>
                    <li><a href="/pages/footwear.php">Footwear</a></li>
                    <li><a href="/pages/apparel.php">Apparel</a></li>
                    <li><a href="/pages/accessories.php">Accessories</a></li>
                    <li><a href="/pages/brands.php">Brands</a></li>
                    <li class="sale"><a href="/pages/sale.php">Sale</a></li>
                </ul>
            </nav>

            <div class="header-icons">
                <button class="icon-btn search-btn" aria-label="Search">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>

                <a href="/pages/wishlist.php" class="icon-btn" aria-label="Wishlist">
                    <svg viewBox="0 0 24 24">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                        </path>
                    </svg>
                    <?php if (isLoggedIn()): ?>
                        <span class="badge wishlist-count" style="display: none;">0</span>
                    <?php endif; ?>
                </a>

                <a href="<?php echo isLoggedIn() ? '/pages/account.php' : '/auth/login.php'; ?>" class="icon-btn"
                    aria-label="Account">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>

                <a href="/pages/cart.php" class="icon-btn cart-link" aria-label="Cart">
                    <svg viewBox="0 0 24 24">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="badge cart-count" style="display: none;">0</span>
                </a>
            </div>
        </div>

        <!-- Search Overlay -->
        <div class="search-overlay" id="searchOverlay">
            <div class="container">
                <div class="search-container">
                    <svg viewBox="0 0 24 24" class="search-icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="headerSearch" placeholder="Search for products, brands..."
                        autocomplete="off">
                    <button class="close-search" onclick="toggleSearch()">&times;</button>
                </div>
            </div>
        </div>
    </header>

    <?php
    $flash = getFlashMessage();
    if ($flash):
        ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
