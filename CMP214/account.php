<?php
session_start();
require_once 'DB.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

session_regenerate_id(true);

$db = new DB();
$conn = $db->connect();
$user_id = $_SESSION['user_id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$stmt = $conn->prepare("SELECT id, username, email FROM tbl_users WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header('Location: logout.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="account-container">
    <h2>Welcome, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Email: <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>

    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

    <a href="logout.php" class="button button-danger">Logout</a>
</section>

</body>
</html>


