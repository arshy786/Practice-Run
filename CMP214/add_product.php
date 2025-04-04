<?php
session_start();
require_once "DB.php";
require_once "navbar.php";

if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    die("Access Denied: Admins Only.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$addError = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Invalid CSRF token.");
    }
    $name = trim($_POST['productName']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $info = trim($_POST['info']);

    if (empty($name) || empty($_FILES['image']['name']) || empty($category) || empty($price) || empty($info)) {
        $addError = "Please fill in all required fields.";
    } elseif (!is_numeric($price) || floatval($price) <= 0) {
        $addError = "Error: Price must be a valid number greater than 0.";
    } else {
        $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        $fileInfo = pathinfo($_FILES['image']['name']);
        $fileExt = strtolower($fileInfo['extension']);
        $fileSize = $_FILES['image']['size'];

        if (!in_array($fileExt, $allowedExtensions)) {
            $addError = "Error: Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif ($fileSize > $maxFileSize) {
            $addError = "Error: File size must be under 2MB.";
        } else {
            $targetDir = "IMG/";
            $newFileName = $targetDir . time() . "_" . basename($_FILES['image']['name']);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $newFileName)) {
                try {
                    $db = new DB();
                    $conn = $db->connect();

                    $stmt = $conn->prepare('INSERT INTO tbl_products (name, image, category, info, price) VALUES (:name, :image, :category, :info, :price)');
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':image', $newFileName, PDO::PARAM_STR);
                    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
                    $stmt->bindParam(':info', $info, PDO::PARAM_STR);
                    $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                    $stmt->execute();

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header('Location: admin.php');
                    exit;
                } catch (PDOException $e) {
                    error_log("Database Error: " . $e->getMessage());
                    die("An error occurred. Please try again later.");
                }
            } else {
                $addError = "Error: Failed to upload the image.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <h2>Add Product</h2>
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> (Admin)</p>
        <a href="logout.php" class="button button-danger">Logout</a>

        <?php if (!empty($addError)): ?>
            <p style="color: red;"><?= htmlspecialchars($addError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <h3>Add New Product</h3>
        <p>* Required fields</p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            
            <label for="productName">New Product Name*:</label><br>
            <input type="text" id="productName" name="productName" required><br><br>

            <label for="image">New Product Image*:</label><br>
            <input type="file" id="image" name="image" accept="image/*" required><br><br>

            <label for="category">New Product Category*:</label><br>
            <input type="text" id="category" name="category" required><br><br>

            <label for="price">New Product Price*:</label><br>
            <input type="text" id="price" name="price" required><br><br>

            <label for="info">New Product Info*:</label><br>
            <textarea id="info" name="info" rows="3" required></textarea><br><br>

            <button type="submit" name="submit" class="button button-primary">Add Product</button>
        </form>
    </div>
</body>
</html>


