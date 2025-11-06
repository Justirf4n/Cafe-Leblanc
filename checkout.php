<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details with address
$user_query = "SELECT u.*, a.address_line, a.city, a.postal_code 
               FROM users u 
               LEFT JOIN addresses a ON u.user_id = a.user_id AND a.is_default = TRUE
               WHERE u.user_id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

$success = false;
$error = '';

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_data = $_POST['cart_data'];
    $delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $subtotal = floatval($_POST['subtotal']);
    $delivery_fee = floatval($_POST['delivery_fee']);
    $total_amount = floatval($_POST['total_amount']);
    
    $cart = json_decode($cart_data, true);
    
    if (!empty($cart)) {
        // Generate order number manually
        $order_number = 'CL' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $estimated_delivery = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        // Insert order
        $order_query = "INSERT INTO orders (
            user_id, 
            order_number, 
            order_status,
            subtotal, 
            delivery_fee, 
            total_amount,
            payment_method,
            payment_status,
            estimated_delivery
        ) VALUES (
            $user_id, 
            '$order_number',
            'pending',
            $subtotal, 
            $delivery_fee, 
            $total_amount,
            '$payment_method',
            'pending',
            '$estimated_delivery'
        )";
        
        if (mysqli_query($conn, $order_query)) {
            $order_id = mysqli_insert_id($conn);
            
            // Insert delivery info
            $delivery_query = "INSERT INTO order_delivery (order_id, delivery_address, delivery_phone) 
                              VALUES ($order_id, '$delivery_address', '$phone')";
            mysqli_query($conn, $delivery_query);
            
            // Insert order items
            foreach ($cart as $item) {
                $product_id = intval($item['id']);
                $quantity = intval($item['quantity']);
                $price = floatval($item['price']);
                $product_name = mysqli_real_escape_string($conn, $item['name']);
                $line_total = $quantity * $price;
                
                $item_query = "INSERT INTO order_details (
                    order_id, 
                    product_id, 
                    product_name, 
                    quantity, 
                    unit_price,
                    line_total
                ) VALUES (
                    $order_id, 
                    $product_id, 
                    '$product_name', 
                    $quantity, 
                    $price,
                    $line_total
                )";
                mysqli_query($conn, $item_query);
                
                // Update product stock
                $update_stock = "UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE product_id = $product_id";
                mysqli_query($conn, $update_stock);
            }
            
            $success = true;
        } else {
            $error = 'Order placement failed: ' . mysqli_error($conn);
        }
    } else {
        $error = 'Cart is empty!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Cafe Leblanc</title>
    <link rel="stylesheet" href="styles/checkout.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">CAFE LEBLANC</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <?php if ($success): ?>
        <!-- Success Message -->
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon">✓</div>
                <h1 class="success-title">ORDER CONFIRMED!</h1>
                <p class="success-message">
                    Your order has been confirmed and is being prepared.<br>
                    We'll deliver your order within 30-45 minutes.<br><br>
                    <strong>Order confirmation has been sent to your email.</strong>
                </p>
                <a href="index.php" class="btn-home">Return to Cafe</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Checkout Form -->
        <div class="checkout-wrapper">
            <div class="checkout-header">
                <h1>CHECKOUT</h1>
                <p>Complete your order and get it delivered fresh</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="checkoutForm">
                <input type="hidden" name="cart_data" id="cartData">
                <input type="hidden" name="subtotal" id="subtotalInput">
                <input type="hidden" name="delivery_fee" id="deliveryFeeInput">
                <input type="hidden" name="total_amount" id="totalInput">

                <div class="checkout-grid">
                    <!-- Left Column -->
                    <div>
                        <!-- Customer Information -->
                        <div class="checkout-form">
                            <h3 class="section-title">CUSTOMER INFO</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phone Number *</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>

                            <h3 class="section-title" style="margin-top: 2.5rem;">DELIVERY INFO</h3>

                            <div class="form-group">
                                <label>Delivery Address *</label>
                                <textarea name="delivery_address" rows="3" required><?php echo htmlspecialchars($user['address_line'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['city'] ?? 'Not specified'); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Postal Code</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['postal_code'] ?? 'N/A'); ?>" readonly>
                                </div>
                            </div>

                            <h3 class="section-title" style="margin-top: 2.5rem;">PAYMENT METHOD</h3>

                            <div class="form-group">
                                <label>Payment Method *</label>
                                <select name="payment_method" required>
                                    <option value="cash">Cash on Delivery</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="online">Online Payment</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="order-summary">
                        <div class="summary-card">
                            <h3 class="summary-header">ORDER SUMMARY</h3>
                            <div id="orderItemsList">
                                <!-- Items will be loaded here -->
                            </div>

                            <div class="price-breakdown">
                                <div class="price-row">
                                    <span class="price-label">Subtotal</span>
                                    <span class="price-value" id="subtotalDisplay">¥0</span>
                                </div>
                                <div class="price-row">
                                    <span class="price-label">
                                        Delivery Fee
                                        <span class="delivery-badge" id="freeBadge" style="display: none;">FREE</span>
                                    </span>
                                    <span class="price-value" id="deliveryDisplay">¥500</span>
                                </div>
                                <div class="price-row total">
                                    <span class="price-label">Total</span>
                                    <span class="price-value" id="totalDisplay">¥0</span>
                                </div>
                            </div>

                            <button type="submit" class="btn-place-order">PLACE ORDER</button>
                        </div>

                        <div class="summary-card info-card">
                            <div class="info-card-icon">⚡</div>
                            <div class="info-card-content">
                                <h4>Fast Delivery</h4>
                                <p>Estimated delivery: 30-45 minutes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <script>
        // Load cart from session storage
        const cart = JSON.parse(sessionStorage.getItem('cart') || '[]');
        
        if (cart.length === 0 && !<?php echo $success ? 'true' : 'false'; ?>) {
            alert('Your cart is empty!');
            window.location.href = 'index.php';
        }
        
        // Display order items
        const orderItemsList = document.getElementById('orderItemsList');
        let subtotal = 0;
        
        if (orderItemsList) {
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                orderItemsList.innerHTML += `
                    <div class="order-item">
                        <div class="item-info">
                            <div class="item-name">${item.name}</div>
                            <div class="item-quantity">Qty: ${item.quantity}</div>
                        </div>
                        <div class="item-price">¥${itemTotal.toFixed(0)}</div>
                    </div>
                `;
            });
            
            // Calculate delivery fee (free over ¥3000)
            const deliveryFee = subtotal >= 3000 ? 0 : 500;
            const total = subtotal + deliveryFee;
            
            // Update displays
            document.getElementById('subtotalDisplay').textContent = `¥${subtotal.toFixed(0)}`;
            document.getElementById('deliveryDisplay').textContent = `¥${deliveryFee.toFixed(0)}`;
            document.getElementById('totalDisplay').textContent = `¥${total.toFixed(0)}`;
            
            // Show free delivery badge if applicable
            if (deliveryFee === 0) {
                document.getElementById('freeBadge').style.display = 'inline-block';
            }
            
            // Set hidden form fields
            document.getElementById('cartData').value = JSON.stringify(cart);
            document.getElementById('subtotalInput').value = subtotal.toFixed(2);
            document.getElementById('deliveryFeeInput').value = deliveryFee.toFixed(2);
            document.getElementById('totalInput').value = total.toFixed(2);
        }
        
        // Clear cart after successful order
        <?php if ($success): ?>
            sessionStorage.removeItem('cart');
        <?php endif; ?>
    </script>
</body>
</html>