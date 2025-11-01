<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="container">
        <a href="../index.php" class="logo">Secure User System</a>
        <nav>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="logout.php">Log-out</a></li>
                    <li><a href="change_password.php">Change Password</a></li>
                <?php else: ?>
                    <li><a href="login.php">Log-in</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>