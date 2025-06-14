<?php
session_start();

// Add to Cart
if (isset($_POST['add_to_cart'])) {
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$product_name = isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : '';
$product_price = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;

$item = [
    'id' => $product_id,
    'name' => $product_name,
    'price' => $product_price,
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

<h2>Your Shopping Cart</h2>
<?php if (!empty($_SESSION['cart'])): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
        <?php
        $total = 0;
        foreach ($_SESSION['cart'] as $item):
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td>$<?= number_format($item['price'], 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>$<?= number_format($subtotal, 2) ?></td>
            <td><a href="cart.php?remove=<?= $item['id'] ?>">Remove</a></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3"><strong>Total:</strong></td>
            <td colspan="2"><strong>$<?= number_format($total, 2) ?></strong></td>
        </tr>
    </table>

    <br>
    <a href="checkout.php">Proceed to Checkout</a> |
    <a href="cart.php?clear=1">Clear Cart</a>
<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>
