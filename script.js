// Improved Mobile Menu Toggle with Animation
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
        
        if (navMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    });

    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });

    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
            if (navMenu.classList.contains('active')) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navMenu.classList.contains('active')) {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
}

// Global products array
let allProducts = [];

// Cart Management
let cart = [];

// Load Menu Items
function loadMenu() {
    const menuContainer = document.getElementById('menuContainer');
    if (!menuContainer) {
        console.error('Menu container not found');
        return;
    }

    fetch('get_products.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Products loaded:', data);
            allProducts = data;
            displayMenu(data);
        })
        .catch(error => {
            console.error('Error loading menu:', error);
            menuContainer.innerHTML = '<p class="loading">Unable to load menu. Please check your connection or database setup.</p>';
        });
}

// Display Menu Items with IMPROVED UI
function displayMenu(items) {
    const menuContainer = document.getElementById('menuContainer');
    if (!menuContainer) return;

    if (items.length === 0) {
        menuContainer.innerHTML = '<p class="loading">No items available at the moment.</p>';
        return;
    }

    menuContainer.innerHTML = items.map(item => `
        <div class="menu-item" data-category="${item.category}">
            <div class="menu-item-image">
                <img src="${item.image_url || 'uploads/placeholder.jpg'}" 
                     alt="${item.name}"
                     onerror="this.onerror=null; this.src='uploads/placeholder.jpg';">
                <div class="menu-item-overlay">
                    <button class="btn-view-details" onclick='openProductModal(${JSON.stringify(item).replace(/'/g, "&apos;")})'>
                        VIEW DETAILS
                    </button>
                </div>
            </div>
            <div class="menu-item-content">
                <div class="menu-item-category">${item.category.toUpperCase()}</div>
                <h3 class="menu-item-title">${item.name}</h3>
                <p class="menu-item-description">${item.description}</p>
                <div class="menu-item-footer">
                    <span class="menu-item-price">¥${parseFloat(item.price).toFixed(0)}</span>
                    <button class="btn-add-to-cart" 
                            onclick='openProductModal(${JSON.stringify(item).replace(/'/g, "&apos;")})'
                            ${item.stock_quantity === 0 ? 'disabled' : ''}>
                        ${item.stock_quantity === 0 ? 'OUT OF STOCK' : 'VIEW DETAILS'}
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Add to Cart - Now adds directly from modal
function addToCart(product) {
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1,
            image_url: product.image_url || 'uploads/placeholder.jpg'
        });
    }

    updateCartUI();
    showNotification(`${product.name} added to cart!`);
}

// Remove from Cart
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartUI();
    displayCartItems();
}

// Update quantity in cart
function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            updateCartUI();
            displayCartItems();
        }
    }
}

// Update Cart UI
function updateCartUI() {
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    document.getElementById('cartCount').textContent = cartCount;
    sessionStorage.setItem('cart', JSON.stringify(cart));
}

// Display Cart Items in Modal
function displayCartItems() {
    const cartItemsContainer = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p class="empty-cart">Your cart is empty. Add some delicious items!</p>';
        document.getElementById('cartTotal').textContent = '¥0';
        return;
    }

    cartItemsContainer.innerHTML = cart.map(item => `
        <div class="cart-item">
            <img src="${item.image_url}" alt="${item.name}" class="cart-item-img">
            <div class="cart-item-details">
                <h4>${item.name}</h4>
                <p>¥${item.price.toFixed(0)} each</p>
            </div>
            <div class="cart-item-actions">
                <button onclick="updateQuantity(${item.id}, -1)">-</button>
                <span>${item.quantity}</span>
                <button onclick="updateQuantity(${item.id}, 1)">+</button>
            </div>
            <div class="cart-item-price">¥${(item.price * item.quantity).toFixed(0)}</div>
            <button class="cart-item-remove" onclick="removeFromCart(${item.id})">×</button>
        </div>
    `).join('');

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('cartTotal').textContent = `¥${total.toFixed(0)}`;
}

// Product Modal Functions
let currentModalProduct = null;
let modalQuantity = 1;

function openProductModal(product) {
    currentModalProduct = product;
    modalQuantity = 1;

    document.getElementById('modalProductImage').src = product.image_url;
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductDescription').textContent = product.description;
    document.getElementById('modalProductCategory').textContent = product.category.toUpperCase();
    document.getElementById('modalSpecCategory').textContent = product.category.toUpperCase();
    document.getElementById('modalSpecStock').textContent = product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock';
    document.getElementById('modalQuantity').value = 1;
    
    updateModalPrice();
    
    document.getElementById('productModal').style.display = 'flex';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

function updateModalPrice() {
    if (currentModalProduct) {
        const price = currentModalProduct.price * modalQuantity;
        document.getElementById('modalProductPrice').textContent = `¥${price.toFixed(0)}`;
    }
}

// Cart Modal
const cartModal = document.getElementById('cartModal');
const cartBtn = document.getElementById('cartBtn');
const closeModal = document.querySelector('.modal .close');
const checkoutBtn = document.getElementById('checkoutBtn');

if (cartBtn) {
    cartBtn.addEventListener('click', (e) => {
        e.preventDefault();
        displayCartItems();
        cartModal.style.display = 'block';
    });
}

if (closeModal) {
    closeModal.addEventListener('click', () => {
        cartModal.style.display = 'none';
    });
}

if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
        if (cart.length === 0) {
            showNotification('Your cart is empty!');
            return;
        }

        fetch('check_session.php')
            .then(response => response.json())
            .then(data => {
                if (data.loggedIn) {
                    window.location.href = 'checkout.php';
                } else {
                    showNotification('Please login to proceed with checkout');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                }
            })
            .catch(() => {
                showNotification('Please login to continue');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
            });
    });
}

// FIXED: Menu Filter - Now matches actual category values
const filterButtons = document.querySelectorAll('.filter-btn');
filterButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        button.classList.add('active');

        const filter = button.getAttribute('data-filter');
        
        if (filter === 'all') {
            displayMenu(allProducts);
        } else {
            // Filter products by matching category
            const filtered = allProducts.filter(item => item.category === filter);
            displayMenu(filtered);
        }
    });
});

// Contact Form
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        showNotification('Message received! We will respond soon.');
        contactForm.reset();
    });
}

// Notification System
function showNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: #ff0000;
        color: white;
        padding: 1rem 2rem;
        border: 2px solid white;
        z-index: 3000;
        font-weight: bold;
        letter-spacing: 1px;
        animation: slideIn 0.3s ease;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(255,0,0,0.5);
    `;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Initialize when page loads
window.addEventListener('load', () => {
    const savedCart = sessionStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartUI();
    }
    loadMenu();

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('registered') === '1') {
        showNotification('Welcome to Cafe Leblanc! Registration successful!');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Product Modal Controls
window.addEventListener('DOMContentLoaded', () => {
    const productModal = document.getElementById('productModal');
    const closeProductModalBtn = productModal?.querySelector('.product-modal-close');

    if (closeProductModalBtn) {
        closeProductModalBtn.addEventListener('click', closeProductModal);
    }

    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');
    const quantityInput = document.getElementById('modalQuantity');
    const modalAddBtn = document.getElementById('modalAddToCart');

    if (decreaseBtn) {
        decreaseBtn.addEventListener('click', () => {
            if (modalQuantity > 1) {
                modalQuantity--;
                quantityInput.value = modalQuantity;
                updateModalPrice();
            }
        });
    }

    if (increaseBtn) {
        increaseBtn.addEventListener('click', () => {
            modalQuantity++;
            quantityInput.value = modalQuantity;
            updateModalPrice();
        });
    }

    if (quantityInput) {
        quantityInput.addEventListener('input', (e) => {
            modalQuantity = parseInt(e.target.value) || 1;
            updateModalPrice();
        });
    }

    if (modalAddBtn) {
        modalAddBtn.addEventListener('click', () => {
            if (currentModalProduct) {
                for (let i = 0; i < modalQuantity; i++) {
                    addToCart(currentModalProduct);
                }
                closeProductModal();
            }
        });
    }

    // Close modals on outside click
    window.addEventListener('click', (e) => {
        if (e.target === cartModal) {
            cartModal.style.display = 'none';
        }
        if (e.target === productModal) {
            closeProductModal();
        }
    });
});

// Enhanced Navbar Scroll Effect
let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');

if (navbar) {
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        lastScrollTop = scrollTop;
    });
}

// Smooth Scroll for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        
        if (target) {
            const offset = 80;
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});