<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();
include '../includes/db_connect.php';
include '../includes/csrf.php';

$errors = [];
$success_message = '';
$username = '';
$questions = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    validate_csrf_token();
    $username = trim($_POST['username']);
    $_SESSION['reset_username'] = $username;

    $stmt = $conn->prepare("SELECT auth_q1, auth_q2, auth_q3 FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($q1, $q2, $q3);
        $stmt->fetch();
        $questions = [$q1, $q2, $q3];
    } else {
        $errors[] = "Username not found.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['answers'])) {
    validate_csrf_token();
    $username = $_SESSION['reset_username'];
    $answers = $_POST['answers'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT auth_a1, auth_a2, auth_a3 FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($a1, $a2, $a3);
            $stmt->fetch();

            if ($answers[0] == $a1 && $answers[1] == $a2 && $answers[2] == $a3) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update_stmt->bind_param("ss", $hashed_password, $username);
                if ($update_stmt->execute()) {
                    $success_message = "Password has been reset successfully. You can now <a href='login.php'>log in</a>.";
                    unset($_SESSION['reset_username']);
                } else {
                    $errors[] = "Error updating password.";
                }
                $update_stmt->close();
            } else {
                $errors[] = "One or more answers are incorrect.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Secure User System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container">
        <div class="form-container">
            <h2>Forgot Password</h2>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="success">
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php else: ?>
                <?php if (empty($questions)): ?>
                    <form action="forgot_password.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="form-group">
                            <label for="username">Enter your username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <button type="submit" class="btn">Submit</button>
                    </form>
                <?php else: ?>
                    <form action="forgot_password.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="answers" value="">
                        <?php foreach ($questions as $i => $question): ?>
                            <div class="form-group">
                                <label><?php echo htmlspecialchars($question); ?></label>
                                <input type="text" name="answers[]" required>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn">Reset Password</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>