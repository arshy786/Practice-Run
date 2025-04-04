<?php
session_start();
require_once 'navbar.php';
require_once 'DB.php';

$db = new DB();
$conn = $db->connect();

$order_number = $_GET['order_number'] ?? $_SESSION['order_number'] ?? '';
unset($_SESSION['order_number']);

if (empty($order_number)) {
    die("Error: Invalid order number.");
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_orders WHERE order_number = :order_number");
$stmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
$stmt->execute();
$orderExists = $stmt->fetchColumn();

if (!$orderExists) {
    die("Error: Order not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
            background: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .success-container h2 {
            color: #1e3a5f;
        }

        .success-container p {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

    <div class="success-container">
        <h2>Thank You for Your Order!</h2>

        <p>Your order has been successfully placed. You will receive an email confirmation shortly.</p>

        <p><strong>Order Number:</strong> <?= htmlspecialchars($order_number, ENT_QUOTES, 'UTF-8'); ?></p>

        <p>Continue shopping or check your order status in your account.</p>
        
        <a href="homepage.php" class="button button-primary">Return to Home</a>
        <a href="account.php" class="button button-success">View Orders</a>
    </div>

</body>
</html>


