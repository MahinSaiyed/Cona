<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Information</h3>
                <ul>
                    <li><a href="/pages/about.php">About Us</a></li>
                    <li><a href="/pages/contact.php">Contact Us</a></li>
                    <li><a href="/pages/stores.php">Store Locator</a></li>
                    <li><a href="/pages/careers.php">Careers</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="/pages/help.php">Help Center</a></li>
                    <li><a href="/pages/faq.php">FAQ</a></li>
                    <li><a href="/pages/shipping.php">Shipping Info</a></li>
                    <li><a href="/pages/returns.php">Returns & Exchanges</a></li>
                    <li><a href="/pages/track-order.php">Track Order</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Legal</h3>
                <ul>
                    <li><a href="/pages/privacy.php">Privacy Policy</a></li>
                    <li><a href="/pages/terms.php">Terms & Conditions</a></li>
                    <li><a href="/pages/refund.php">Refund Policy</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Newsletter</h3>
                <p style="font-size: 0.875rem; margin-bottom: 1rem; opacity: 0.8;">
                    Subscribe to get special offers, early access to drops, and more.
                </p>
                <form class="newsletter-form" method="post">
                    <input type="email" name="email" class="newsletter-input" placeholder="Your email" required>
                    <button type="submit" class="newsletter-btn">Subscribe</button>
                </form>

                <h3 style="margin-top: 2rem;">Follow Us</h3>
                <div class="social-links">
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" fill="none" stroke="currentColor"
                                stroke-width="2"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="currentColor" stroke-width="2"></line>
                        </svg>
                    </a>
                    <a href="https://facebook.com" target="_blank" aria-label="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="https://twitter.com" target="_blank" aria-label="Twitter">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div
                style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <a href="/admin/login.php"
                    style="color: inherit; opacity: 0.5; font-size: 0.75rem; text-decoration: none;">Admin Login</a>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp Float Button -->
<?php 
$wa_number = getSetting($db, 'whatsapp_number', WHATSAPP_NUMBER); 
?>
<a href="https://wa.me/<?php echo str_replace('+', '', $wa_number); ?>?text=Hi%2C%20I%20need%20help!"
    class="whatsapp-float" target="_blank" aria-label="Chat on WhatsApp">
    <svg viewBox="0 0 24 24">
        <path
            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
    </svg>
</a>

<script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
</body>

</html>