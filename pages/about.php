<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = "About Us - " . SITE_NAME;
include __DIR__ . '/../includes/header.php';
?>

<main class="container" style="padding: 4rem 2rem;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; margin-bottom: 2rem;"><?php echo SITE_NAME; ?></h1>
        
        <div style="line-height: 1.8; color: var(--color-gray);">
            <p style="margin-bottom: 1.5rem; font-size: 1.125rem;">
                Welcome to <?php echo SITE_NAME; ?>, your premier destination for high-quality footwear and lifestyle apparel. 
                Our mission is to provide our customers with a curated selection of the world's most iconic brands and the latest trends 
                in street culture.
            </p>

            <h2 style="color: var(--color-dark); margin: 2rem 0 1rem; font-size: 1.75rem;">Our Story</h2>
            <p style="margin-bottom: 1.5rem;">
                Founded with a passion for sneaker culture, <?php echo SITE_NAME; ?> has grown from a local initiative into a trusted name 
                for enthusiasts across the region. We believe that what you wear tells a story, and we're here to help you make yours 
                unforgettable.
            </p>

            <h2 style="color: var(--color-dark); margin: 2rem 0 1rem; font-size: 1.75rem;">Why Choose Us?</h2>
            <ul style="margin-bottom: 2rem; padding-left: 1.5rem;">
                <li style="margin-bottom: 0.5rem;"><strong>Authenticity Guaranteed:</strong> Every product in our store is 100% authentic, sourced directly from brands.</li>
                <li style="margin-bottom: 0.5rem;"><strong>Premium Selection:</strong> We hand-pick every item to ensure it meets our standards of quality and style.</li>
                <li style="margin-bottom: 0.5rem;"><strong>Customer First:</strong> Our dedicated team is here to ensure your shopping experience is seamless and satisfying.</li>
            </ul>

            <div style="background: var(--color-light-gray); padding: 2rem; border-radius: 8px; margin-top: 3rem;">
                <h3 style="margin-top: 0;">Visit Our Store</h3>
                <p>Located in the heart of the city, our flagship store offers a premium shopping environment where you can experience our collection first-hand.</p>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
