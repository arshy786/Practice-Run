<?php
session_start();
require_once 'DB.php';
require_once 'product.php';

if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

$db = new DB();

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = intval($_GET['id']);

    $query = $db->connect()->prepare("SELECT * FROM tbl_products WHERE id = :id");
    $query->bindParam(":id", $id, PDO::PARAM_INT);
    $query->execute();
    $product = $query->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: homepage.php');
        exit();
    }
} else {
    header('Location: homepage.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']); ?> - Product Info</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="product-container">
    <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" class="product-image">
    <h1 class="product-name"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="product-category">Category: <?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p class="product-description"><?= nl2br(htmlspecialchars($product['info'], ENT_QUOTES, 'UTF-8')); ?></p> 
    <p class="product-price">£<?= number_format($product['price'], 2); ?></p>

    <form method="post" action="homepage.php">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id" value="<?= $product['id']; ?>">
        <button type="submit" name="add" class="add-to-basket">Add to Basket</button>
    </form>
</div>

</body>
</html>

