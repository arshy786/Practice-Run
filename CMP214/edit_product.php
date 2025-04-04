<?php
session_start();
require_once "DB.php";
require_once "product.php";
require_once 'navbar.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    die("Error: Unauthorized access.");
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Error: Invalid product ID.");
}

$product_id = intval($_GET['id']);

$db = new DB();
$conn = $db->connect();

$query = $conn->prepare("SELECT * FROM tbl_products WHERE id = :id LIMIT 1");
$query->bindParam(':id', $product_id, PDO::PARAM_INT);
$query->execute();
$productData = $query->fetch(PDO::FETCH_ASSOC);

if (!$productData) {
    die("Error: Product not found.");
}

$product = new Product(
    $product_id,
    $productData['name'],
    $productData['image'],
    $productData['category'],
    $productData['price'],
    $productData['info']
);

$updateError = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Invalid CSRF token.");
    }

    $updatedFields = [];
    $params = [':id' => $product_id];

    if (!empty($_POST['productName'])) {
        $updatedFields[] = 'name = :name';
        $params[':name'] = $_POST['productName'];
    }

    if (!empty($_POST['category'])) {
        $updatedFields[] = 'category = :category';
        $params[':category'] = $_POST['category'];
    }

    if (!empty($_POST['price']) && is_numeric($_POST['price'])) {
        $updatedFields[] = 'price = :price';
        $params[':price'] = $_POST['price'];
    }

    if (!empty($_POST['info'])) {
        $updatedFields[] = 'info = :info';
        $params[':info'] = $_POST['info'];
    }

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "IMG/";
        $targetFile = $targetDir . basename($_FILES['image']['name']);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $updatedFields[] = 'image = :image';
            $params[':image'] = $targetFile;
        } else {
            $updateError = "Failed to upload image.";
        }
    }

    if (!empty($updatedFields)) {
        $query = $conn->prepare("UPDATE tbl_products SET " . implode(", ", $updatedFields) . " WHERE id = :id");
        $query->execute($params);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header("Location: edit_product.php?id=" . $product_id);
        exit();
    } else {
        $updateError = "No changes were made.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <h2>Edit Product</h2>
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> (Admin)</p>
        <a href="logout.php" class="logout-button">Logout</a>
        
        <h3>Current Product Details</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($product->getName()); ?></p>
        <img src="<?= htmlspecialchars($product->getImage()); ?>" width="100px" height="100px">
        <p><strong>Category:</strong> <?= htmlspecialchars($product->getCategory()); ?></p>
        <p><strong>Price:</strong> £<?= htmlspecialchars($product->getPrice()); ?></p>
        <p><strong>Info:</strong> <?= htmlspecialchars($product->getInfo()); ?></p>  

        <?php if (!empty($updateError)): ?>
            <p class="error-message"><?= htmlspecialchars($updateError); ?></p>
        <?php endif; ?>

        <h3>Update Details</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="input-group">
                <label for="productName">New Product Name:</label>
                <input type="text" id="productName" name="productName">
            </div>

            <div class="input-group">
                <label for="image">New Product Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="input-group">
                <label for="category">New Product Category:</label>
                <input type="text" id="category" name="category">
            </div>

            <div class="input-group">
                <label for="price">New Product Price (£):</label>
                <input type="number" step="0.01" id="price" name="price">
            </div>

            <div class="input-group">
                <label for="info">New Product Info:</label>
                <input type="text" id="info" name="info">
            </div>

            <button type="submit" name="submit" class="button button-primary">Update Product</button>
        </form>
    </div>
</body>
</html>

