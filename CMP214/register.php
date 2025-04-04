<?php
require_once 'DB.php';
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Invalid CSRF token.");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            $error = "Password must be at least 8 characters long and include both letters and numbers.";
        } else {
            try {
                $db = new DB();
                $conn = $db->connect();

                $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_users WHERE username = :username OR email = :email");
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $userExists = $stmt->fetchColumn();

                if ($userExists > 0) {
                    $error = "Username or email is already taken.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO tbl_users (username, email, password, is_admin, created_at) VALUES (:username, :email, :password, 0, NOW())");
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                    $stmt->execute();

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header("Location: login.php?success=registered");
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                die("An error occurred. Please try again later.");
            }
        }
    } else {
        $error = "All fields are required.";
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="auth-form">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <button type="submit" class="button button-primary">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>

