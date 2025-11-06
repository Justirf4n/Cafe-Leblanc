<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

header('Content-Type: application/json');

// Query to get products with category information
// FIXED: Now returns category_slug which matches the filter buttons
$query = "SELECT
    p.product_id as id,
    p.product_name as name,
    p.description,
    p.price,
    p.image_url,
    p.stock_quantity,
    p.is_available,
    c.category_slug as category,
    c.category_name
FROM products p
JOIN categories c ON p.category_id = c.category_id
WHERE p.is_available = TRUE AND p.stock_quantity > 0
ORDER BY p.is_featured DESC, p.product_id DESC";

$result = mysqli_query($conn, $query);

// Check for query errors
if (!$result) {
    echo json_encode([
        'error' => true,
        'message' => mysqli_error($conn),
        'query' => $query
    ]);
    exit;
}

$products = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'category' => $row['category'], // This is now category_slug (coffee, food, beverages, desserts)
            'image_url' => $row['image_url'],
            'stock_quantity' => (int)$row['stock_quantity']
        ];
    }
}

// Return products array (even if empty)
echo json_encode($products);
?>