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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $conn->prepare("SELECT id, username, email, is_admin, created_at FROM tbl_users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM tbl_products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM tbl_products WHERE quantity < 10");
$stmt->execute();
$low_stock_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) AS pending_orders FROM tbl_orders WHERE status = 'Pending'");
$stmt->execute();
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-container">
    <h2>Admin Dashboard</h2>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong> (Admin)</p>
    
    <a href="logout.php" class="btn btn-danger">Logout</a>

    <h3>Admin Notifications</h3>

    <?php if (!empty($low_stock_products)): ?>
        <div class="low-stock-alert">
            <p style="color: red; font-weight: bold;">⚠️ Low Stock Warning:</p>
            <ul>
                <?php foreach ($low_stock_products as $low_stock): ?>
                    <li><strong><?= htmlspecialchars($low_stock['name'], ENT_QUOTES, 'UTF-8'); ?></strong> - Only <?= intval($low_stock['quantity']); ?> left.</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <p style="color: green;">✅ All products have sufficient stock.</p>
    <?php endif; ?>

    <?php if ($pending_orders > 0): ?>
        <div class="pending-orders-alert">
            <p style="color: orange; font-weight: bold;">🔔 You have <?= intval($pending_orders); ?> pending orders waiting for approval.</p>
            
            <a href="admin_orders.php" class="btn btn-primary">Manage Orders</a>
        </div>
    <?php endif; ?>

    <h3>Manage Users</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Admin</th>
            <th>Registered</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= intval($user['id']); ?></td>
                <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Manage Products</h3>
    
    <a href="add_product.php" class="btn btn-success">Add New Product</a><br><br>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= intval($product['id']); ?></td>
                <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>£<?= number_format($product['price'], 2); ?></td>
                <td>
                    <form method="post" action="update_stock.php" class="stock-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="product_id" value="<?= intval($product['id']); ?>">
                        <input type="number" name="new_quantity" value="<?= intval($product['quantity']); ?>" min="0" required>
                        
                        <button type="submit" class="btn btn-success">Update Stock</button>
                    </form>
                </td>
                <td>
                    <a href="edit_product.php?id=<?= intval($product['id']); ?>" class="btn btn-primary">Edit</a>
                    <a href="delete_product.php?id=<?= intval($product['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>






