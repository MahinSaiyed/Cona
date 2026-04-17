// Main JavaScript for Superkicks E-Commerce

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function () {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function () {
            navMenu.classList.toggle('active');
        });
    }

    // Update cart and wishlist counts
    updateCartCount();
    updateWishlistCount();
});

function setBadgeCount(selector, count) {
    const badge = document.querySelector(selector);
    if (!badge) return;

    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'block';
    } else {
        badge.textContent = '0';
        badge.style.display = 'none';
    }
}

// Add to cart function
async function addToCart(productId, size = null, color = null, event = null) {
    let btn = null;
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        btn = event.currentTarget;
    }

    // Start loading state
    if (btn) btn.classList.add('btn-adding');

    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        if (size) formData.append('size', size);
        if (color) formData.append('color', color);

        const response = await fetch('/api/cart-add.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            const cartCount = Number(data.data?.cart_count ?? 0);
            setBadgeCount('.cart-count', cartCount);

            if (btn) {
                btn.classList.remove('btn-adding');
                btn.classList.add('btn-added');
                const originalText = btn.innerHTML;
                btn.innerHTML = '✓ Added';

                // Trigger flying particle
                createCartAnimation(btn);

                setTimeout(() => {
                    btn.classList.remove('btn-added');
                    btn.innerHTML = originalText;
                }, 2000);
            } else {
                showToast('Product added to cart!', 'success');
                updateCartCount();
            }
        } else {
            if (btn) btn.classList.remove('btn-adding');
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    } catch (error) {
        if (btn) btn.classList.remove('btn-adding');
        showToast('An error occurred', 'error');
        console.error('Error:', error);
    }
}

function createCartAnimation(startElement) {
    const cartIcon = document.querySelector('.cart-link'); // Using the link since it contains the icon/badge
    if (!cartIcon) {
        updateCartCount();
        return;
    }

    const startRect = startElement.getBoundingClientRect();
    const endRect = cartIcon.getBoundingClientRect();

    const particle = document.createElement('div');
    particle.className = 'cart-particle';
    particle.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';

    particle.style.left = `${startRect.left + startRect.width / 2 - 20}px`;
    particle.style.top = `${startRect.top + startRect.height / 2 - 20}px`;

    document.body.appendChild(particle);

    // Initial scale for 'pop' effect
    particle.style.transform = 'scale(0.5)';

    requestAnimationFrame(() => {
        particle.style.transform = 'scale(1.2)';

        setTimeout(() => {
            const x = endRect.left + endRect.width / 2 - (startRect.left + startRect.width / 2);
            const y = endRect.top + endRect.height / 2 - (startRect.top + startRect.height / 2);

            particle.style.transform = `translate(${x}px, ${y}px) scale(0.2)`;
            particle.style.opacity = '0';

            setTimeout(() => {
                particle.remove();
                // Shake cart icon
                cartIcon.classList.add('cart-icon-shake');
                updateCartCount();
                setTimeout(() => cartIcon.classList.remove('cart-icon-shake'), 500);
            }, 800);
        }, 100);
    });
}

// Update cart quantity
async function updateCartQuantity(cartId, quantity) {
    try {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);

        const response = await fetch('/api/cart-update.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            updateCartCount();
            location.reload(); // Reload to update totals
        } else {
            showToast(data.message || 'Failed to update cart', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
        console.error('Error:', error);
    }
}

// Remove from cart
async function removeFromCart(cartId) {
    if (!confirm('Remove this item from cart?')) return;

    try {
        const formData = new FormData();
        formData.append('cart_id', cartId);

        const response = await fetch('/api/cart-remove.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showToast('Item removed from cart', 'success');
            updateCartCount();
            location.reload();
        } else {
            showToast(data.message || 'Failed to remove item', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
        console.error('Error:', error);
    }
}

// Toggle wishlist
async function toggleWishlist(productId, button) {
    try {
        const formData = new FormData();
        formData.append('product_id', productId);

        const response = await fetch('/api/wishlist-toggle.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            if (data.action === 'added') {
                button.classList.add('active');
                showToast('Added to wishlist', 'success');
            } else {
                button.classList.remove('active');
                showToast('Removed from wishlist', 'success');
            }
            updateWishlistCount();
        } else {
            if (data.message === 'Please login first') {
                window.location.href = '/auth/login.php';
            } else {
                showToast(data.message || 'Failed to update wishlist', 'error');
            }
        }
    } catch (error) {
        showToast('An error occurred', 'error');
        console.error('Error:', error);
    }
}

// Update cart count badge
async function updateCartCount() {
    try {
        const response = await fetch('/api/cart-count.php');
        const data = await response.json();
        const count = Number(data.data?.count ?? 0);
        setBadgeCount('.cart-count', count);
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Update wishlist count badge
async function updateWishlistCount() {
    try {
        const response = await fetch('/api/wishlist-count.php');
        const data = await response.json();
        const count = Number(data.data?.count ?? 0);
        setBadgeCount('.wishlist-count', count);
    } catch (error) {
        console.error('Error updating wishlist count:', error);
    }
}

// Search functionality
function toggleSearch() {
    const overlay = document.getElementById('searchOverlay');
    const input = document.getElementById('headerSearch');

    if (overlay.classList.contains('active')) {
        overlay.classList.remove('active');
        input.value = '';
        // Clear filters when closing
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => card.style.display = '');
    } else {
        overlay.classList.add('active');
        setTimeout(() => input.focus(), 300);
    }
}

// Global toggle search from icons
document.addEventListener('DOMContentLoaded', function () {
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', toggleSearch);
    }
});

const searchInput = document.getElementById('headerSearch');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function (e) {
        const query = e.target.value.toLowerCase();
        const productCards = document.querySelectorAll('.product-card');

        productCards.forEach(card => {
            const name = card.querySelector('.product-name').textContent.toLowerCase();
            const brand = card.querySelector('.product-brand').textContent.toLowerCase();

            if (name.includes(query) || brand.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }, 300));
}

// Filter products
function filterProducts() {
    const filters = {
        brand: document.querySelector('[name="brand"]')?.value || '',
        size: document.querySelector('[name="size"]')?.value || '',
        color: document.querySelector('[name="color"]')?.value || '',
        price: document.querySelector('[name="price"]')?.value || '',
        gender: document.querySelector('[name="gender"]')?.value || ''
    };

    const url = new URL(window.location);

    // Update or remove parameters
    Object.entries(filters).forEach(([key, value]) => {
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
    });

    // Reset to first page if pagination exists (good practice)
    url.searchParams.delete('page');

    window.location.href = url.toString();
}

function clearFilters() {
    const url = new URL(window.location);
    url.search = '';
    window.location.href = url.pathname;
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        background-color: ${type === 'success' ? '#00C853' : type === 'error' ? '#FF5252' : '#333'};
        color: white;
        padding: 1rem 2rem;
        border-radius: 4px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        animation: slideUp 0.3s ease;
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(2rem);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    @keyframes slideDown {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(2rem);
        }
    }
`;
document.head.appendChild(style);

// Product image gallery
function initProductGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-product-image');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function () {
            const img = this.querySelector('img');
            const newSrc = img ? img.src : '';
            if (mainImage && newSrc) {
                mainImage.src = newSrc;
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
}

// Initialize gallery on page load
if (document.querySelector('.product-gallery')) {
    initProductGallery();
}

// Form validation
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    return /^[6-9]\d{9}$/.test(phone);
}

// Newsletter subscription
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const email = this.querySelector('input[name="email"]').value;

        if (!validateEmail(email)) {
            showToast('Please enter a valid email address', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('email', email);

            const response = await fetch('/api/newsletter-subscribe.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showToast('Successfully subscribed to newsletter!', 'success');
                this.reset();
            } else {
                showToast(data.message || 'Subscription failed', 'error');
            }
        } catch (error) {
            showToast('An error occurred', 'error');
            console.error('Error:', error);
        }
    });
}

// Interactive Transformation Logic
function initInteractiveTransforms() {
    const containers = document.querySelectorAll('.interactive-container');

    containers.forEach(container => {
        const premiumImg = container.querySelector('.img-premium');
        const scanLine = container.querySelector('.scan-line');

        if (!premiumImg || !scanLine) return;

        container.addEventListener('mousemove', (e) => {
            const rect = container.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;

            premiumImg.style.clipPath = `inset(0 ${100 - x}% 0 0)`;
            scanLine.style.left = `${x}%`;
            scanLine.style.opacity = '1';
        });

        container.addEventListener('mouseleave', () => {
            premiumImg.style.clipPath = 'inset(0 100% 0 0)';
            scanLine.style.left = '0%';
            scanLine.style.opacity = '0';
        });
    });
}

function initProductGalleries() {
    const cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        const galleryData = card.getAttribute('data-gallery');
        if (!galleryData) return;

        let images = [];
        try {
            images = JSON.parse(galleryData);
        } catch (e) {
            console.error("Failed to parse gallery data", e);
            return;
        }

        if (images.length <= 1) return;

        const imgContainer = card.querySelector('.product-image');
        if (!imgContainer) return;

        // Create gallery overlay
        const galleryOverlay = document.createElement('div');
        galleryOverlay.className = 'gallery-container';

        // Create indicator container
        const indicators = document.createElement('div');
        indicators.className = 'gallery-indicators';

        images.forEach((_, i) => {
            const bar = document.createElement('div');
            bar.className = 'indicator-bar';
            indicators.appendChild(bar);
        });

        imgContainer.appendChild(galleryOverlay);
        imgContainer.appendChild(indicators);

        let cycleInterval = null;
        let currentIndex = 0;
        const SLIDE_DURATION = 2500; // 2.5 seconds per image

        const stopCycle = () => {
            if (cycleInterval) {
                clearInterval(cycleInterval);
                cycleInterval = null;
            }
            // Reset state
            galleryOverlay.innerHTML = '';
            currentIndex = 0;
            indicators.querySelectorAll('.indicator-bar').forEach(b => {
                b.style.transition = 'none';
                b.style.transform = 'scaleX(0)';
            });
        };

        const updateSlide = () => {
            const oldSlide = galleryOverlay.querySelector('.gallery-slide');
            const newSlide = document.createElement('div');
            newSlide.className = 'gallery-slide next';

            const imgPath = images[currentIndex].startsWith('http') ? images[currentIndex] : `/uploads/products/${images[currentIndex]}`;
            newSlide.style.backgroundImage = `url('${imgPath}')`;

            // Create scan line for the wipe effect
            const scanLine = document.createElement('div');
            scanLine.className = 'scan-line';
            scanLine.style.left = '0%';
            scanLine.style.opacity = '0';
            galleryOverlay.appendChild(scanLine);

            // Create label
            const label = document.createElement('div');
            label.className = 'premium-label';
            label.style.fontSize = '10px';
            label.style.padding = '2px 8px';
            label.innerHTML = `VIEW ${currentIndex + 1} / ${images.length}`;
            label.style.opacity = '0';
            galleryOverlay.appendChild(label);

            // Reset indicators
            const bars = indicators.querySelectorAll('.indicator-bar');
            bars.forEach((bar, i) => {
                bar.style.transition = 'none';
                if (i < currentIndex) {
                    bar.style.transform = 'scaleX(1)';
                } else if (i === currentIndex) {
                    bar.style.transform = 'scaleX(0)';
                    bar.offsetHeight; // Reflow
                    bar.style.transition = `transform ${SLIDE_DURATION}ms linear`;
                    bar.style.transform = 'scaleX(1)';
                } else {
                    bar.style.transform = 'scaleX(0)';
                }
            });

            galleryOverlay.appendChild(newSlide);

            setTimeout(() => {
                const wipeDuration = 800;
                newSlide.style.transition = `clip-path ${wipeDuration}ms cubic-bezier(0.4, 0, 0.2, 1)`;
                newSlide.style.clipPath = 'inset(0 0 0 0)';

                scanLine.style.opacity = '1';
                scanLine.style.transition = `left ${wipeDuration}ms cubic-bezier(0.4, 0, 0.2, 1)`;
                scanLine.style.left = '100%';

                label.style.transition = 'opacity 0.3s ease';
                label.style.opacity = '1';

                setTimeout(() => {
                    if (oldSlide) oldSlide.remove();
                    newSlide.classList.remove('next');
                    newSlide.style.transition = 'none';
                    scanLine.remove();
                    setTimeout(() => {
                        if (label) label.style.opacity = '0';
                        setTimeout(() => label.remove(), 300);
                    }, 1000);
                }, wipeDuration);
            }, 50);
        };

        card.addEventListener('mouseenter', () => {
            currentIndex = 0;
            updateSlide();
            cycleInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % images.length;
                updateSlide();
            }, SLIDE_DURATION);
        });

        card.addEventListener('mouseleave', stopCycle);
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    initInteractiveTransforms();
    initProductGalleries();
    initReviews();
});

// ========== PRODUCT REVIEWS SYSTEM ==========

let currentReviewsPage = 1;

function initReviews() {
    // Only initialize if we're on a product page
    const productId = document.querySelector('input[name="product_id"]')?.value;
    if (!productId) return;

    loadReviews(productId, 1);
    setupStarRatingInput();
    setupReviewForm();
}

// Star Rating Input
function setupStarRatingInput() {
    const stars = document.querySelectorAll('.star-rating-input .star');
    const ratingInput = document.getElementById('rating-value');

    if (!stars.length) return;

    stars.forEach(star => {
        star.addEventListener('click', function () {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;

            stars.forEach((s, index) => {
                if (index < rating) {
                    s.textContent = '★';
                    s.classList.add('active');
                } else {
                    s.textContent = '☆';
                    s.classList.remove('active');
                }
            });
        });

        star.addEventListener('mouseenter', function () {
            const rating = parseInt(this.dataset.rating);
            stars.forEach((s, index) => {
                s.textContent = index < rating ? '★' : '☆';
            });
        });
    });

    document.querySelector('.star-rating-input')?.addEventListener('mouseleave', function () {
        const currentRating = parseInt(ratingInput.value) || 0;
        stars.forEach((s, index) => {
            if (index < currentRating) {
                s.textContent = '★';
            } else {
                s.textContent = '☆';
            }
        });
    });
}

// Generate star display HTML
function generateStarsHTML(rating, small = false) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    let html = `<div class="stars-display ${small ? 'small' : ''}">`;

    for (let i = 0; i < fullStars; i++) {
        html += '★';
    }
    if (hasHalfStar) {
        html += '⯨';
    }
    for (let i = fullStars + (hasHalfStar ? 1 : 0); i < 5; i++) {
        html += '☆';
    }

    html += '</div>';
    return html;
}

// Load Reviews
async function loadReviews(productId, page = 1) {
    try {
        const response = await fetch(`/api/get-reviews.php?product_id=${productId}&page=${page}`);
        const data = await response.json();

        if (data.success) {
            displayReviewsSummary(data.data.summary);
            displayReviewsList(data.data.reviews);
            displayPagination(data.data.pagination, productId);
            currentReviewsPage = page;
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
        document.getElementById('reviews-summary').innerHTML = '<p>Failed to load reviews</p>';
    }
}

// Display Reviews Summary
function displayReviewsSummary(summary) {
    const summaryEl = document.getElementById('reviews-summary');

    if (summary.total_reviews === 0) {
        summaryEl.innerHTML = `
            <div class="no-reviews">
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        `;
        return;
    }

    const distribution = summary.rating_distribution;
    const total = summary.total_reviews;

    summaryEl.innerHTML = `
        <div class="rating-overview">
            <div class="average-rating">${summary.average_rating}</div>
            ${generateStarsHTML(summary.average_rating)}
            <div style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--color-gray);">
                Based on ${total} review${total !== 1 ? 's' : ''}
            </div>
        </div>
        <div class="rating-bars">
            ${[5, 4, 3, 2, 1].map(star => {
        const count = distribution[star] || 0;
        const percentage = total > 0 ? (count / total * 100) : 0;
        return `
                    <div class="rating-bar">
                        <div class="rating-bar-label">${star} star${star !== 1 ? 's' : ''}</div>
                        <div class="rating-bar-fill">
                            <div class="rating-bar-fill-inner" style="width: ${percentage}%"></div>
                        </div>
                        <div class="rating-bar-count">${count}</div>
                    </div>
                `;
    }).join('')}
        </div>
    `;
}

// Display Reviews List
function displayReviewsList(reviews) {
    const listEl = document.getElementById('reviews-list');

    if (!reviews || reviews.length === 0) {
        listEl.innerHTML = '<div class="no-reviews"><p>No reviews to display</p></div>';
        return;
    }

    listEl.innerHTML = reviews.map(review => `
        <div class="review-card">
            <div class="review-header">
                <div>
                    <div class="review-author">${escapeHtml(review.author)}</div>
                    <div class="review-meta">
                        ${generateStarsHTML(review.rating, true)}
                        ${review.verified_purchase ? '<span class="verified-badge">✓ Verified Purchase</span>' : ''}
                        <span>${review.created_at}</span>
                    </div>
                </div>
            </div>
            ${review.title ? `<div class="review-title">${escapeHtml(review.title)}</div>` : ''}
            <div class="review-text">${escapeHtml(review.text)}</div>
            ${review.helpful_count > 0 ? `<div class="review-helpful">${review.helpful_count} people found this helpful</div>` : ''}
        </div>
    `).join('');
}

// Display Pagination
function displayPagination(pagination, productId) {
    const paginationEl = document.getElementById('reviews-pagination');

    if (pagination.total_pages <= 1) {
        paginationEl.innerHTML = '';
        return;
    }

    let html = '';

    // Previous button
    html += `<button class="page-btn" ${pagination.current_page === 1 ? 'disabled' : ''} 
             onclick="loadReviews(${productId}, ${pagination.current_page - 1})">Previous</button>`;

    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === pagination.current_page) {
            html += `<button class="page-btn active">${i}</button>`;
        } else {
            html += `<button class="page-btn" onclick="loadReviews(${productId}, ${i})">${i}</button>`;
        }
    }

    // Next button
    html += `<button class="page-btn" ${pagination.current_page === pagination.total_pages ? 'disabled' : ''} 
             onclick="loadReviews(${productId}, ${pagination.current_page + 1})">Next</button>`;

    paginationEl.innerHTML = html;
}

// Review Form Functions
function openReviewForm() {
    document.getElementById('review-form-container').style.display = 'block';
    document.getElementById('review-form-container').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function closeReviewForm() {
    document.getElementById('review-form-container').style.display = 'none';
    document.getElementById('review-form').reset();
    document.getElementById('rating-value').value = '';
    document.querySelectorAll('.star-rating-input .star').forEach(star => {
        star.textContent = '☆';
        star.classList.remove('active');
    });
}

// Setup Review Form Submission
function setupReviewForm() {
    const form = document.getElementById('review-form');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const productId = formData.get('product_id');

        // Validation
        if (!formData.get('rating')) {
            showToast('Please select a rating', 'error');
            return;
        }

        try {
            const response = await fetch('/api/submit-review.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showToast('Review submitted successfully!', 'success');
                closeReviewForm();
                // Reload reviews
                loadReviews(productId, 1);
            } else {
                showToast(data.message || 'Failed to submit review', 'error');
            }
        } catch (error) {
            console.error('Error submitting review:', error);
            showToast('An error occurred. Please try again.', 'error');
        }
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== MOBILE MENU ==========

function initMobileMenu() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('nav');

    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
        this.classList.toggle('active');
        nav.classList.toggle('mobile-active');
        document.body.style.overflow = nav.classList.contains('mobile-active') ? 'hidden' : '';
    });

    // Close mobile menu when clicking a link
    const navLinks = nav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            toggle.classList.remove('active');
            nav.classList.remove('mobile-active');
            document.body.style.overflow = '';
        });
    });

    // Close menu on window resize if menu is open
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768 && nav.classList.contains('mobile-active')) {
            toggle.classList.remove('active');
            nav.classList.remove('mobile-active');
            document.body.style.overflow = '';
        }
    });
}

// Initialize mobile menu on load
document.addEventListener('DOMContentLoaded', function () {
    initMobileMenu();
});
