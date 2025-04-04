<?php
session_start();
require_once 'navbar.php';
require_once 'DB.php';

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$db = new DB();
$conn = $db->connect();

session_regenerate_id(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Invalid CSRF token.");
    }

    $user_id = intval($_SESSION['user_id']);
    $total_price = filter_input(INPUT_POST, 'total', FILTER_VALIDATE_FLOAT);

    if ($total_price === false || $total_price <= 0) {
        die("Error: Invalid total price.");
    }

    $order_number = "TYNE" . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));

    $conn->beginTransaction();

    try {
        foreach ($_SESSION['basket'] as $item) {
            $stmt = $conn->prepare("SELECT quantity FROM tbl_products WHERE id = :product_id FOR UPDATE");
            $stmt->bindParam(':product_id', $item['id'], PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product || $product['quantity'] < $item['quantity']) {
                $_SESSION['error'] = "Not enough stock available for: " . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
                header("Location: basket.php");
                exit();
            }
        }

        $stmt = $conn->prepare("INSERT INTO tbl_orders (user_id, total_price, status, order_number) VALUES (:user_id, :total_price, 'Pending', :order_number)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':total_price', $total_price, PDO::PARAM_STR);
        $stmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
        $stmt->execute();
        $order_id = $conn->lastInsertId();

        foreach ($_SESSION['basket'] as $item) {
            $stmt = $conn->prepare("INSERT INTO tbl_order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE tbl_products SET quantity = quantity - :quantity WHERE id = :product_id");
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['id'], PDO::PARAM_INT);
            $stmt->execute();
        }

        $_SESSION['basket'] = [];
        $_SESSION['order_number'] = $order_number;

        $conn->commit();

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header("Location: success.php?order_number=$order_number");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Order placement error: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
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
    <title>Checkout</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php require_once 'navbar.php'; ?>

<section class="checkout-container">
    <h2>Checkout</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <p style="color: red; font-weight: bold;"> <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?> </p>
    <?php endif; ?>

    <?php if (empty($_SESSION['basket'])): ?>
        <p>Your basket is empty.</p>
    <?php else: ?>
        <form method="post">
            <table class="checkout-table">
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
                <?php
                $total = 0;
                foreach ($_SESSION['basket'] as $item):
                    $total += $item['quantity'] * $item['price'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= intval($item['quantity']) ?></td>
                    <td>£<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2"><strong>Total:</strong></td>
                    <td><strong>£<?= number_format($total, 2) ?></strong></td>
                </tr>
            </table>
            <input type="hidden" name="total" value="<?= htmlspecialchars($total, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <button type="submit" name="checkout" class="button button-primary">Place Order</button>
        </form>
    <?php endif; ?>
</section>

</body>
</html>

