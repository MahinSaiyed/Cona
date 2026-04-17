<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get all brands
$stmt = $db->query("SELECT DISTINCT brand FROM products WHERE is_active = 1 AND brand IS NOT NULL ORDER BY brand");
$brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<style>
    .brands-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .brand-card {
        background: white;
        border: 1px solid var(--color-border);
        padding: 3rem 2rem;
        text-align: center;
        transition: all var(--transition-normal);
        cursor: pointer;
    }

    .brand-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-color: var(--color-black);
    }

    .brand-name {
        font-size: 1.5rem;
        font-weight: var(--font-weight-bold);
        text-transform: uppercase;
        letter-spacing: 2px;
    }
</style>

<main>
    <!-- Page Header: Interactive Transformation -->
    <section class="style-transformation" style="padding: 4rem 0;">
        <div class="container">
            <div class="transformation-content">
                <span class="transformation-badge">Iconic Collaborations</span>
                <h2>Our Brands</h2>
                <p>Discover the biggest names in the game. We partner with the world's most influential brands to bring
                    you exclusive releases.</p>
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

    <div class="container">
        <section class="section">

            <div class="brands-grid">
                <?php foreach ($brands as $brand): ?>
                    <a href="/pages/footwear.php?brand=<?php echo urlencode($brand); ?>" class="brand-card">
                        <div class="brand-name">
                            <?php echo htmlspecialchars($brand); ?>
                        </div>
                    </a>
                <?php endforeach; ?>

                <?php if (empty($brands)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; color: var(--color-gray);">
                        <h2>No brands available</h2>
                        <p>Check back soon for our brand partners!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>