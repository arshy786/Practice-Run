<?php
session_start();
require_once 'DB.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'], $_POST['new_quantity'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = intval($_POST['new_quantity']);

    $db = new DB();
    $conn = $db->connect();

    $stmt = $conn->prepare("UPDATE tbl_products SET quantity = ? WHERE id = ?");
    $stmt->execute([$new_quantity, $product_id]);

    $_SESSION['success'] = "Stock updated successfully!";
}

header("Location: admin.php");
exit;
?>
