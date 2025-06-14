<?php
session_start();
unset($_SESSION['cart']);
?>

<h2>Order Placed Successfully!</h2>
<p>Thank you for your purchase.</p>
<a href="products.php">Back to Products</a>
