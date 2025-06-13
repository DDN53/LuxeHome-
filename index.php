<?php include('includes/db.php'); ?>
<?php include('includes/header.php'); ?>

<h2>Featured Smart Home Devices</h2>
<div class="product-list">
    <?php
    $result = $conn->query("SELECT * FROM products LIMIT 4");
    while ($row = $result->fetch_assoc()):
    ?>
        <div class="product">
            <img src="assets/images/<?= $row['image'] ?>" width="150">
            <h3><?= $row['name'] ?></h3>
            <p>Brand: <?= $row['brand'] ?></p>
            <p>Price: $<?= $row['price'] ?></p>
            <a href="product.php?id=<?= $row['id'] ?>">View Details</a>
        </div>
    <?php endwhile; ?>
</div>

<?php include('includes/footer.php'); ?>
