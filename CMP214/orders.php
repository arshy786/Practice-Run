<?php
require_once 'DB.php';
require_once 'navbar.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$db = new DB;
$conn = $db->connect();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $conn->prepare("SELECT * FROM tbl_orders WHERE user_id = :user_id ORDER BY order_date DESC");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders as &$order) {
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM tbl_order_items oi JOIN tbl_products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
    $stmt->bindParam(':order_id', $order['id'], PDO::PARAM_INT); 
    $stmt->execute();
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    } else {
        die("Invalid CSRF token.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Your Orders</h1>
    <p>Review your past orders.</p>
</header>

<section class="orders-container">
    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <h3>Order #<?= htmlspecialchars($order['order_number']); ?> - <?= date('F j, Y', strtotime($order['order_date'])); ?></h3>
                <p>Status: <strong><?= htmlspecialchars($order['status']); ?></strong></p>
                <p>Total: <strong>£<?= number_format($order['total_price'], 2); ?></strong></p>

                <h4>Order Details:</h4>
                <ul>
                    <?php foreach ($order['items'] as $item): ?>
                        <li>
                            <img src="<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="order-item-image">
                            <p><?= htmlspecialchars($item['name']); ?> (x<?= $item['quantity']; ?>) - £<?= number_format($item['price'], 2); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have no past orders.</p>
    <?php endif; ?>
</section>

</body>
</html>
