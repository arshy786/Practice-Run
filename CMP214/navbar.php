<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$welcomeMessage = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    $welcomeMessage = 'Welcome, ' . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
}
?>

<header class="header-container">
    <nav class="navbar">
        <a href="homepage.php" class="navbar-logo">
            <img src="IMG/logo.png" alt="Tyne Brew Coffee Logo">
        </a>

        <ul class="navbar-links">
            <li><a href="homepage.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="basket.php">Basket</a></li>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                <li class="dropdown">
                    <a href="#" class="dropbtn">Account ▾</a>
                    <div class="dropdown-content">
                        <a href="account.php">Profile</a>
                        <a href="orders.php">Orders</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
                <li class="welcome-message"><?= $welcomeMessage; ?></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <li><a href="admin.php">Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
