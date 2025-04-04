<?php
session_start();
require_once 'DB.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if ($_SESSION['login_attempts'] > 5) {
    die("Too many failed login attempts. Please wait before trying again.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Invalid CSRF token.");
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            $db = new DB();
            $conn = $db->connect();

            $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM tbl_users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
                $_SESSION['is_admin'] = $user['is_admin'];
                $_SESSION['user_id'] = $user['id'];

                $_SESSION['login_attempts'] = 0;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header("Location: " . ($user['is_admin'] ? "admin.php" : "homepage.php"));
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            die("An error occurred. Please try again later.");
        }
    } else {
        $error = "All fields are required.";
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="auth-form">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <button type="submit" class="button button-primary">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>


