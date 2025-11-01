<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();
include '../includes/db_connect.php';
include '../includes/csrf.php';

$errors = [];

if (isset($_SESSION['locked_out']) && $_SESSION['locked_out'] > time()) {
    $remaining_time = $_SESSION['locked_out'] - time();
    $errors[] = "Too many failed login attempts. Please try again in {$remaining_time} seconds.";
} elseif (isset($_SESSION['locked_out'])) {
    unset($_SESSION['locked_out']);
    unset($_SESSION['login_attempts']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION['locked_out'])) {
    validate_csrf_token();
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $username;
            unset($_SESSION['login_attempts']);
            header("Location: ../index.php"); // Redirect to a logged-in home page
            exit();
        } else {
            $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
            if ($_SESSION['login_attempts'] >= 3) {
                $lockout_time = 15; // 15 seconds for the first lockout
                if ($_SESSION['login_attempts'] >= 6) {
                    $lockout_time = 30;
                }
                if ($_SESSION['login_attempts'] >= 9) {
                    $lockout_time = 60;
                }
                $_SESSION['locked_out'] = time() + $lockout_time;
                $errors[] = "Too many failed login attempts. You are locked out for {$lockout_time} seconds.";
            } else {
                $errors[] = "Invalid username or password.";
            }
        }
    } else {
        $errors[] = "Invalid username or password.";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure User System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head><body>
    <?php
        $page = 'login';
        include '../includes/header.php';
    ?>

    <main class="container">
        <div class="form-container">
            <h2>Log in to your account</h2>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="POST" id="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="show-password">
                    <label for="show-password">Show Password</label>
                </div>
                <div class="form-group">
                    <a href="forgot_password.php" id="forgot-password-link" style="display: none;">Forgot Password? Reset Here</a>
                </div>
                <button type="submit" class="btn" id="login-button">Log in</button>
                <a href="register.php" class="btn" id="register-button">Register</a>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="../js/validation.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginAttempts = <?php echo isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0; ?>;
            const forgotPasswordLink = document.getElementById('forgot-password-link');
            if (loginAttempts >= 2) {
                forgotPasswordLink.style.display = 'block';
            }

            const lockedOut = <?php echo isset($_SESSION['locked_out']) && $_SESSION['locked_out'] > time() ? 'true' : 'false'; ?>;
            if (lockedOut) {
                document.getElementById('login-button').disabled = true;
                document.getElementById('register-button').disabled = true;
            }
        });
    </script>
</body>
</html>