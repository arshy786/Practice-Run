<?php
session_start();
require_once 'DB.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    die("Error: Unauthorized access.");
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Error: Invalid product ID.");
}

$product_id = intval($_GET['id']);

try {
    $db = new DB();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_products WHERE id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $productExists = $stmt->fetchColumn();

    if ($productExists) {
        $stmt = $conn->prepare("DELETE FROM tbl_products WHERE id = :id");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success'] = "Product deleted successfully.";
    } else {
        $_SESSION['error'] = "Product not found.";
    }

    header("Location: admin.php");
    exit();
} catch (PDOException $e) {
    error_log("Delete error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header("Location: admin.php");
    exit();
}
?>
