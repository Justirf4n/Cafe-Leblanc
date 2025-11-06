<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check admin status from database
$user_id = $_SESSION['user_id'];
$admin_check = "SELECT a.*, u.username, u.full_name FROM admins a 
                JOIN users u ON a.user_id = u.user_id 
                WHERE a.user_id = $user_id";
$admin_result = mysqli_query($conn, $admin_check);

if (mysqli_num_rows($admin_result) == 0) {
    header('Location: index.php');
    exit();
}

$admin_data = mysqli_fetch_assoc($admin_result);

// Handle Product Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
        $stock = intval($_POST['stock']);
        
        $query = "INSERT INTO products (category_id, product_name, description, price, image_url, stock_quantity) 
                 VALUES ($category_id, '$name', '$description', $price, '$image_url', $stock)";
        mysqli_query($conn, $query);
    }
    
    elseif ($action === 'update') {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
        $stock = intval($_POST['stock']);
        
        $query = "UPDATE products SET 
                 product_name='$name', description='$description', price=$price, 
                 category_id=$category_id, image_url='$image_url', stock_quantity=$stock 
                 WHERE product_id=$id";
        mysqli_query($conn, $query);
    }
    
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $query = "DELETE FROM products WHERE product_id=$id";
        mysqli_query($conn, $query);
    }
}

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM products WHERE is_available = TRUE) as total_products,
    (SELECT COUNT(*) FROM users WHERE is_active = TRUE) as total_users,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled') as total_revenue,
    (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') as pending_orders,
    (SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()) as today_orders";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get all products with category
$products_query = "SELECT p.*, c.category_name FROM products p 
                   JOIN categories c ON p.category_id = c.category_id 
                   ORDER BY p.product_id DESC";
$products_result = mysqli_query($conn, $products_query);

// Get categories for dropdown
$categories_query = "SELECT * FROM categories WHERE is_active = TRUE ORDER BY display_order";
$categories_result = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cafe Leblanc</title>
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">CAFE LEBLANC</div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active">Dashboard</a></li>
            <li><a href="#products">Products</a></li>
        </ul>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?php echo strtoupper(substr($admin_data['username'], 0, 1)); ?>
                </div>
                <div class="sidebar-user-info">
                    <h4><?php echo htmlspecialchars($admin_data['full_name']); ?></h4>
                    <p>Admin</p>
                </div>
            </div>
            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 0.5rem;">View Site</a>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h1 class="page-title">COMMAND CENTER</h1>
                <p>Welcome back, <?php echo htmlspecialchars($admin_data['username']); ?></p>
            </div>
            <div class="top-actions">
                <button class="btn btn-primary" onclick="openAddModal()">+ ADD PRODUCT</button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">🍽️</div>
                </div>
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo $stats['total_products']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">📦</div>
                </div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">💰</div>
                </div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">¥<?php echo number_format($stats['total_revenue'], 0); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">⏳</div>
                </div>
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">🎯</div>
                </div>
                <div class="stat-label">Today's Orders</div>
                <div class="stat-value"><?php echo $stats['today_orders']; ?></div>
            </div>
        </div>

        <!-- Products Section -->
        <div id="products">
            <div class="section-header">
                <h2 class="section-title">PRODUCT MANAGEMENT</h2>
            </div>

            <!-- DESKTOP TABLE VIEW -->
            <div class="products-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                         class="product-img">
                                    <div>
                                        <div style="font-weight: 700;"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                        <div style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.5);">
                                            <?php echo substr(htmlspecialchars($product['description']), 0, 40) . '...'; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="product-category"><?php echo $product['category_name']; ?></span>
                            </td>
                            <td style="font-weight: 700; color: var(--p5-red);">¥<?php echo number_format($product['price'], 0); ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td>
                                <span style="color: <?php echo $product['is_available'] ? '#00ff00' : '#ff6666'; ?>">
                                    <?php echo $product['is_available'] ? '✓ Active' : '✗ Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" onclick='openEditModal(<?php echo json_encode($product); ?>)'>Edit</button>
                                <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE/TABLET CARD VIEW -->
            <div class="products-mobile">
                <?php 
                mysqli_data_seek($products_result, 0);
                while ($product = mysqli_fetch_assoc($products_result)): 
                ?>
                <div class="product-card-mobile">
                    <div class="product-card-header">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-card-image"
                             onerror="this.src='assets/placeholder.jpg'">
                        <div class="product-card-info">
                            <div class="product-card-id">ID: <?php echo $product['product_id']; ?></div>
                            <div class="product-card-title"><?php echo htmlspecialchars($product['product_name']); ?></div>
                            <span class="product-card-category"><?php echo $product['category_name']; ?></span>
                        </div>
                    </div>

                    <div class="product-card-description">
                        <?php echo htmlspecialchars($product['description']); ?>
                    </div>

                    <div class="product-card-details">
                        <div class="product-detail-item">
                            <div class="product-detail-label">Price</div>
                            <div class="product-detail-value">¥<?php echo number_format($product['price'], 0); ?></div>
                        </div>
                        <div class="product-detail-item">
                            <div class="product-detail-label">Stock</div>
                            <div class="product-detail-value"><?php echo $product['stock_quantity']; ?></div>
                        </div>
                        <div class="product-detail-item">
                            <div class="product-detail-label">Status</div>
                            <div class="product-detail-value">
                                <span class="product-status-badge <?php echo $product['is_available'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $product['is_available'] ? '✓ Active' : '✗ Inactive'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="product-detail-item">
                            <div class="product-detail-label">Prep Time</div>
                            <div class="product-detail-value"><?php echo $product['preparation_time']; ?> min</div>
                        </div>
                    </div>

                    <div class="product-card-actions">
                        <button class="btn-mobile-action btn-mobile-edit" 
                                onclick='openEditModal(<?php echo json_encode($product); ?>)'>Edit</button>
                        <button class="btn-mobile-action btn-mobile-delete" 
                                onclick="deleteProduct(<?php echo $product['product_id']; ?>)">Delete</button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>ADD NEW PRODUCT</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="addForm">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Price (¥) *</label>
                        <input type="number" name="price" step="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <?php 
                            mysqli_data_seek($categories_result, 0);
                            while ($cat = mysqli_fetch_assoc($categories_result)): 
                            ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Image URL *</label>
                        <input type="text" name="image_url" placeholder="e.g., assets/coffee/espresso.jpg" required>
                        <small>Upload image to assets/ folder first</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock" value="100" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="addForm" class="btn btn-primary">
                    ADD PRODUCT
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>EDIT PRODUCT</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="editForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" id="edit_description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Price (¥) *</label>
                        <input type="number" name="price" id="edit_price" step="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" id="edit_category_id" required>
                            <?php 
                            mysqli_data_seek($categories_result, 0);
                            while ($cat = mysqli_fetch_assoc($categories_result)): 
                            ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Image URL *</label>
                        <input type="text" name="image_url" id="edit_image_url" placeholder="e.g., assets/coffee/espresso.jpg" required>
                        <small>Upload image to assets/ folder first</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock" id="edit_stock" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="editForm" class="btn btn-primary">
                    UPDATE PRODUCT
                </button>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function openEditModal(product) {
            document.getElementById('edit_id').value = product.product_id;
            document.getElementById('edit_name').value = product.product_name;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_image_url').value = product.image_url;
            document.getElementById('edit_stock').value = product.stock_quantity;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>