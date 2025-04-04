<?php
session_start();
require_once 'navbar.php';

if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Invalid CSRF token.");
    }

    $updated = false;
    foreach ($_POST['quantity'] as $id => $qty) {
        if (!ctype_digit((string)$id) || !ctype_digit((string)$qty) || intval($qty) <= 0) {
            die("Error: Invalid quantity.");
        }

        foreach ($_SESSION['basket'] as &$item) {
            if ($item['id'] == intval($id) && $item['quantity'] != intval($qty)) {
                $item['quantity'] = max(1, intval($qty));
                $updated = true;
            }
        }
    }

    if ($updated) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: basket.php");
        exit();
    }
}

if (isset($_GET['remove'])) {
    $idToRemove = filter_var($_GET['remove'], FILTER_VALIDATE_INT);
    if ($idToRemove === false) {
        die("Error: Invalid product ID.");
    }

    foreach ($_SESSION['basket'] as $key => $item) {
        if ($item['id'] == $idToRemove) {
            unset($_SESSION['basket'][$key]);
            break;
        }
    }

    $_SESSION['basket'] = array_values($_SESSION['basket']);
    header("Location: basket.php");
    exit();
}

$total = 0;
foreach ($_SESSION['basket'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basket - Tyne Brew Coffee</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="basket-container">
    <h2>Your Basket</h2>

    <?php if (empty($_SESSION['basket'])): ?>
        <p>Your basket is empty.</p>
    <?php else: ?>
        <form method="post" id="basketForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <table class="basket-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['basket'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><img src="<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" width="50"></td>
                            <td>£<?= number_format($item['price'], 2); ?></td>
                            <td>
                                <select name="quantity[<?= intval($item['id']); ?>]" class="quantity-dropdown">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?= $i; ?>" <?= $item['quantity'] == $i ? 'selected' : ''; ?>><?= $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td>£<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td><a href="basket.php?remove=<?= intval($item['id']); ?>" class="remove-btn">X</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="update" class="button button-primary" id="updateButton">Update Quantities</button>
        </form>

        <h3>Total: £<?= number_format($total, 2); ?></h3>
        <a href="checkout.php" class="button button-primary">Proceed to Checkout</a>
    <?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const quantityDropdowns = document.querySelectorAll(".quantity-dropdown");
        const updateButton = document.getElementById("updateButton");

        function enableButton() {
            updateButton.disabled = false;
            updateButton.style.backgroundColor = "#007bff";
            updateButton.style.color = "#fff";
        }

        quantityDropdowns.forEach(dropdown => {
            dropdown.addEventListener("change", enableButton);
        });

        updateButton.disabled = false;
    });
</script>

</body>
</html>

