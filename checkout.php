<?php
session_start();

if (empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='products.php'>Go Shopping</a></p>";
    exit;
}
?>

<h2>Checkout</h2>
<p>This is a placeholder for your checkout logic (payment, billing, etc).</p>

<?php
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<p>Total amount: <strong>$<?= number_format($total, 2) ?></strong></p>

<form method="POST" action="order_success.php">
    <label>Name: <input type="text" name="customer_name" required></label><br><br>
    <label>Email: <input type="email" name="customer_email" required></label><br><br>
    <button type="submit">Place Order</button>
</form>
