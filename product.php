<?php
include('includes/db.php');

// Handle filters with sanitization
$brand = $_GET['brand'] ?? '';
$compatibility = $_GET['compatibility'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build SQL query
$sql = "SELECT * FROM products WHERE 1=1";

if (!empty($brand)) {
    $sql .= " AND brand = '" . $conn->real_escape_string($brand) . "'";
}

if (!empty($compatibility)) {
    $sql .= " AND compatibility LIKE '%" . $conn->real_escape_string($compatibility) . "%'";
}

if (!empty($max_price)) {
    $sql .= " AND price <= " . (float)$max_price;
}

$result = $conn->query($sql);
if (isset($_POST['add_to_cart'])) {
    $item = [
        'id' => $_POST['product_id'],
        'name' => $_POST['product_name'],
        'price' => $_POST['product_price'],
        'quantity' => 1
    ];

    // Initialize cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if item is already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['id'] == $item['id']) {
            $cartItem['quantity']++;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = $item;
    }

    header("Location: cart.php");
    exit;
}

// Remove Item
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    foreach ($_SESSION['cart'] as $index => $cartItem) {
        if ($cartItem['id'] == $remove_id) {
            unset($_SESSION['cart'][$index]);
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
    header("Location: cart.php");
    exit;
}

// Clear Cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Home Products | Modern Filter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #6c757d;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        h1, h2, h3 {
            color: var(--dark-color);
        }
        
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        select, input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 107, 255, 0.2);
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
            align-self: flex-end;
            margin-top: 10px;
        }
        
        .btn:hover {
            background-color: #3a5bef;
            transform: translateY(-2px);
        }
        
        .btn-reset {
            background-color: var(--secondary-color);
            margin-left: 10px;
        }
        
        .btn-reset:hover {
            background-color: #5a6268;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            height: 200px;
            background-color: #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-image i {
            font-size: 60px;
            color: var(--secondary-color);
            opacity: 0.7;
        }
        
        .product-content {
            padding: 20px;
        }
        
        .product-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .product-brand {
            background-color: #e9ecef;
            color: var(--dark-color);
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .product-compatibility {
            color: var(--secondary-color);
        }
        
        .product-description {
            color: #666;
            margin-top: 10px;
            font-size: 14px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .no-results i {
            font-size: 50px;
            color: var(--secondary-color);
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .filter-tag {
            background-color: #e2e6ff;
            color: var(--primary-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .filter-tag button {
            background: none;
            border: none;
            color: var(--primary-color);
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Smart Home Products</h1>
            <p>Find the perfect smart devices for your home</p>
        </header>
        
        <section class="filter-section">
            <h2>Filter Products</h2>
            
            <!-- Active Filters -->
            <?php if ($brand || $compatibility || $max_price): ?>
            <div class="active-filters">
                <?php if ($brand): ?>
                <div class="filter-tag">
                    Brand: <?= $brand ?>
                    <button onclick="removeFilter('brand')">&times;</button>
                </div>
                <?php endif; ?>
                
                <?php if ($compatibility): ?>
                <div class="filter-tag">
                    Compatibility: <?= $compatibility ?>
                    <button onclick="removeFilter('compatibility')">&times;</button>
                </div>
                <?php endif; ?>
                
                <?php if ($max_price): ?>
                <div class="filter-tag">
                    Max Price: $<?= number_format($max_price, 2) ?>
                    <button onclick="removeFilter('max_price')">&times;</button>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <form method="GET" class="filter-form">
                <div class="filter-group">
                  <label>Brand:</label>
    <select name="brand">
        <option value="">-- Any --</option>
        <option value="Samsung" <?= ($_GET['brand'] ?? '') === 'Samsung' ? 'selected' : '' ?>>Samsung</option>
        <option value="Amazon" <?= ($_GET['brand'] ?? '') === 'Amazon' ? 'selected' : '' ?>>Amazon</option>
        <option value="Google" <?= ($_GET['brand'] ?? '') === 'Google' ? 'selected' : '' ?>>Google</option>
    </select>

    

   
                       
                </div>
                
                <div class="filter-group">
                    <label for="compatibility">Compatibility</label>
                    <select name="compatibility" id="compatibility">
                        <option value="">All Platforms</option>
                        <option value="Alexa" <?= $compatibility === 'Alexa' ? 'selected' : '' ?>>Amazon Alexa</option>
                        <option value="Google Home" <?= $compatibility === 'Google Home' ? 'selected' : '' ?>>Google Home</option>
                        <option value="Apple HomeKit" <?= $compatibility === 'Apple HomeKit' ? 'selected' : '' ?>>Apple HomeKit</option>
                        <option value="SmartThings" <?= $compatibility === 'SmartThings' ? 'selected' : '' ?>>Samsung SmartThings</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="max_price">Max Price ($)</label>
                    <input type="number" name="max_price" id="max_price" 
                           min="0" step="0.01" 
                           value="<?= $max_price ? htmlspecialchars($max_price) : '' ?>">
                </div>
                
                <div class="filter-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn">Apply Filters</button>
                    <?php if ($brand || $compatibility || $max_price): ?>
                    <a href="?" class="btn btn-reset">Reset All</a>
                    <?php endif; ?>
                </div>
            </form>
            <!-- <form method="POST" action="cart.php">
    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="product_name" value="<?= $row['name'] ?>">
    <input type="hidden" name="product_price" value="<?= $row['price'] ?>">
    <button type="submit" name="add_to_cart">Add to Cart</button>
</form> -->

        </section>
        
        <section class="products-section">
            <h2>Product Listings</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="products-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="product-card">
                          
                           <div style="border: 1px solid #ccc; padding: 10px; margin: 10px;">
    <h3><?= htmlspecialchars($row['name']) ?></h3>
    <img src="<?= htmlspecialchars($row['image']) ?>" alt="Product Image" width="200">
    <p>Brand: <?= htmlspecialchars($row['brand']) ?></p>
    <p>Compatibility: <?= htmlspecialchars($row['compatibility']) ?></p>
    <p>Price: $<?= htmlspecialchars($row['price']) ?></p>
    <p><?= htmlspecialchars($row['description']) ?></p>
</div>

                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters to see more results</p>
                    <?php if ($brand || $compatibility || $max_price): ?>
                        <a href="?" class="btn" style="margin-top: 10px;">Clear all filters</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    
    <script>
        function removeFilter(filterName) {
            const url = new URL(window.location.href);
            url.searchParams.delete(filterName);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>