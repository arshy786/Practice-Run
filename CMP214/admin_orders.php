<?php
session_start();
require_once 'navbar.php';
require_once 'DB.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

session_regenerate_id(true);

$db = new DB();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT * FROM tbl_orders ORDER BY order_date DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders as &$order) {
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM tbl_order_items oi 
                            JOIN tbl_products p ON oi.product_id = p.id 
                            WHERE oi.order_id = :order_id");
    $stmt->bindParam(':order_id', $order['id'], PDO::PARAM_INT);
    $stmt->execute();
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Invalid CSRF token.");
    }

    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    if (!$order_id) {
        die("Error: Invalid order ID.");
    }

    if (isset($_POST['approve_order'])) {
        $stmt = $conn->prepare("UPDATE tbl_orders SET status = 'Approved' WHERE id = :order_id LIMIT 1");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("SELECT product_id, quantity FROM tbl_order_items WHERE order_id = :order_id");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($order_items as $item) {
            $stmt = $conn->prepare("UPDATE tbl_products SET quantity = quantity - :quantity WHERE id = :product_id LIMIT 1");
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $stmt->execute();
        }

        $_SESSION['success'] = "Order approved and stock updated!";
        header("Location: admin_orders.php");
        exit;
    }

    if (isset($_POST['cancel_order'])) {
        $stmt = $conn->prepare("UPDATE tbl_orders SET status = 'Cancelled' WHERE id = :order_id LIMIT 1");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success'] = "Order has been cancelled.";
        header("Location: admin_orders.php");
        exit;
    }

    if (isset($_POST['complete_order'])) {
        $stmt = $conn->prepare("UPDATE tbl_orders SET status = 'Completed' WHERE id = :order_id LIMIT 1");
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success'] = "Order has been marked as completed.";
        header("Location: admin_orders.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-container">
    <h2>Manage Orders</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <h3>Order #<?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'); ?> - <?= date('F j, Y', strtotime($order['order_date'])); ?></h3>
                <p>Status: <strong><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                <p>Total: <strong>£<?= number_format($order['total_price'], 2); ?></strong></p>

                <h4>Order Items:</h4>
                <ul>
                    <?php foreach ($order['items'] as $item): ?>
                        <li>
                            <img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="order-item-image">
                            <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?> (x<?= intval($item['quantity']); ?>) - £<?= number_format($item['price'], 2); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>">

                    <?php if ($order['status'] == 'Pending'): ?>
                        <button type="submit" name="approve_order" class="button button-success">Approve Order</button>
                        <button type="submit" name="cancel_order" class="button button-danger">Cancel Order</button>
                    <?php elseif ($order['status'] == 'Approved'): ?>
                        <button type="submit" name="complete_order" class="button button-success">Mark as Completed</button>
                    <?php elseif ($order['status'] == 'Cancelled'): ?>
                        <p style="color: red;">Order Cancelled</p>
                    <?php elseif ($order['status'] == 'Completed'): ?>
                        <p style="color: green;">Order Completed</p>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

</body>
</html>

