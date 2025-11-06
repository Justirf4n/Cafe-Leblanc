<?php
require_once 'config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$username = $isLoggedIn ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Leblanc - Tokyo's Hidden Gem</title>
    <link rel="icon" href="assets/Cafe_Leblance_Logo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles/index.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <h1>Cafe Leblanc</h1>
                </div>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="#home">HOME</a></li>
                    <li><a href="#about">ABOUT</a></li>
                    <li><a href="#why-us">WHY US</a></li>
                    <li><a href="#menu">MENU</a></li>
                    <li><a href="#contact">CONTACT</a></li>
                    
                    <?php if ($isLoggedIn): ?>
                        <?php if ($isAdmin): ?>
                            <li><a href="admin_dashboard.php" style="color: #ffd700;">ADMIN PANEL</a></li>
                        <?php endif; ?>
                        <li><a href="#" class="btn-login" style="pointer-events: none; opacity: 0.7;">👤 <?php echo strtoupper(htmlspecialchars($username)); ?></a></li>
                        <li><a href="logout.php" class="btn-login" style="background: rgba(255, 0, 0, 0.2); border-color: rgba(255, 0, 0, 0.5);">LOGOUT</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-login">LOGIN</a></li>
                    <?php endif; ?>
                    
                    <li><a href="#" class="btn-cart" id="cartBtn">🛒 <span id="cartCount">0</span></a></li>
                </ul>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="hero-tag">YONGEN-JAYA, TOKYO</div>
            <h1 class="hero-title">Cafe Leblanc</h1>
            <p class="hero-subtitle">Tokyo's hidden gem serving the finest coffee & curry</p>
            <p class="hero-text">Experience authentic Japanese cafe culture in the heart of the city</p>
            <a href="#menu" class="btn-hero">VIEW MENU</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-about">
        <div class="container">
            <h2 class="section-title">ABOUT CAFE LEBLANC</h2>
            <div class="p5-divider"></div>
            <div class="about-grid">
                <div class="about-card">
                    <div class="card-number">01</div>
                    <h3>Our Story</h3>
                    <p>Nestled in the backstreets of Yongen-Jaya, Cafe Leblanc has been serving exceptional coffee and curry since its founding. A cozy hideaway where time seems to slow down.</p>
                </div>
                <div class="about-card">
                    <div class="card-number">02</div>
                    <h3>Master Brewed</h3>
                    <p>Every cup is carefully crafted by our master barista, using premium beans sourced from around the world. Our signature curry is a closely guarded family recipe.</p>
                </div>
                <div class="about-card">
                    <div class="card-number">03</div>
                    <h3>The Experience</h3>
                    <p>More than just a cafe - it's a sanctuary from the bustling city. Enjoy the warm atmosphere, jazz music, and conversation that makes Leblanc special.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="why-us" class="section-why">
        <div class="container">
            <h2 class="section-title">WHY LEBLANC</h2>
            <div class="why-grid">
                <div class="why-card">
                    <h3>Premium Coffee</h3>
                    <p>Hand-selected beans roasted to perfection. Each cup tells a story of craftsmanship and passion.</p>
                </div>
                <div class="why-card">
                    <h3>Signature Curry</h3>
                    <p>Our legendary curry recipe has been perfected over decades. Rich, aromatic, and unforgettable.</p>
                </div>
                <div class="why-card">
                    <h3>Jazz Atmosphere</h3>
                    <p>Relax to smooth jazz in our vintage interior. The perfect escape from everyday life.</p>
                </div>
                <div class="why-card">
                    <h3>Fast Delivery</h3>
                    <p>Can't visit us? We'll bring Leblanc to you. Hot, fresh, and delivered with care.</p>
                </div>
            </div>
        </div>
    </section>

<!-- Menu Section - FIXED CATEGORY FILTERS -->
<section id="menu" class="section-menu">
    <div class="container">
        <h2 class="section-title">OUR MENU</h2>
        
        <div class="menu-filter">
            <button class="filter-btn active" data-filter="all">ALL ITEMS</button>
            <button class="filter-btn" data-filter="coffee">COFFEE</button>
            <button class="filter-btn" data-filter="food">FOOD</button>
            <button class="filter-btn" data-filter="beverages">BEVERAGES</button>
            <button class="filter-btn" data-filter="desserts">DESSERTS</button>
        </div>

        <div id="menuContainer" class="menu-grid">
            <div class="loading">Preparing menu...</div>
        </div>
    </div>
</section>

    <!-- Contact Section -->
    <section id="contact" class="section-contact">
        <div class="container">
            <h2 class="section-title">FIND US</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Cafe Leblanc</h3>
                    <div class="contact-item">
                        <strong>Location:</strong>
                        <p>Yongen-Jaya Backstreets, Tokyo, Japan</p>
                    </div>
                    <div class="contact-item">
                        <strong>Phone:</strong>
                        <p>+81 (0)3-LEBLANC</p>
                    </div>
                    <div class="contact-item">
                        <strong>Email:</strong>
                        <p>hello@cafeleblanc.jp</p>
                    </div>
                    <div class="contact-item">
                        <strong>Hours:</strong>
                        <p>Mon-Sun: 7:00 AM - 11:00 PM</p>
                    </div>
                </div>
                <div class="contact-form">
                    <form id="contactForm">
                        <input type="text" placeholder="Your Name" required>
                        <input type="email" placeholder="Your Email" required>
                        <input type="text" placeholder="Subject" required>
                        <textarea rows="5" placeholder="Your Message" required></textarea>
                        <button type="submit" class="btn-submit">SEND MESSAGE</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Cafe Leblanc</h3>
                    <p>Your sanctuary in the heart of Tokyo. Experience the finest coffee, curry, and hospitality in a cozy, welcoming atmosphere.</p>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#menu">Menu</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="#">Track Order</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#">FB</a>
                        <a href="#">TW</a>
                        <a href="#">IG</a>
                        <a href="#">LI</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Cafe Leblanc. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Cart Modal -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>YOUR ORDER</h2>
                <span class="close">&times;</span>
            </div>
            <div id="cartItems" class="cart-items">
                <p class="empty-cart">Your cart is empty. Add some delicious items!</p>
            </div>
            <div class="cart-footer">
                <div class="cart-total">
                    <strong>TOTAL:</strong>
                    <span id="cartTotal">¥0</span>
                </div>
                <button class="btn-checkout" id="checkoutBtn">CHECKOUT</button>
            </div>
        </div>
    </div>

<!-- Product Details Modal - ADD THIS BEFORE </body> TAG -->
<div id="productModal" class="product-modal">
    <div class="product-modal-content">
        <span class="product-modal-close">&times;</span>
        <div class="product-modal-grid">
            <div class="product-modal-image">
                <img id="modalProductImage" src="" alt="Product">
            </div>
            <div class="product-modal-info">
                <div>
                    <div class="product-category" id="modalProductCategory">CATEGORY</div>
                    <h2 class="product-modal-title" id="modalProductName">Product Name</h2>
                    <p class="product-modal-description" id="modalProductDescription">
                        Product description goes here
                    </p>
                    <div class="stock-badge" id="modalStockBadge">
                        <span>✓</span> In Stock
                    </div>
                    <div class="product-specs">
                        <h4>Product Details</h4>
                        <div class="spec-item">
                            <span class="spec-label">Category</span>
                            <span class="spec-value" id="modalSpecCategory">-</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Available</span>
                            <span class="spec-value" id="modalSpecStock">-</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Delivery</span>
                            <span class="spec-value">30-45 minutes</span>
                        </div>
                    </div>
                </div>
                <div class="product-modal-actions">
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="quantity-controls">
                            <button class="quantity-btn" id="decreaseQty">-</button>
                            <input type="number" class="quantity-input" id="modalQuantity" value="1" min="1" max="99">
                            <button class="quantity-btn" id="increaseQty">+</button>
                        </div>
                    </div>
                    <div class="product-price-row">
                        <span class="price-label">Total Price</span>
                        <span class="product-modal-price" id="modalProductPrice">¥0</span>
                    </div>
                    <button class="btn-add-to-cart-modal" id="modalAddToCart">
                        🛒 Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="script.js"></script>
</body>
</html>