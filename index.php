<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure User System</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <h1>Welcome to the Secure User System</h1>
        <?php if (isset($_SESSION['username'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <?php else: ?>
            <p>Please <a href="php/register.php">register</a> or <a href="php/login.php">log in</a> to continue.</p>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    <?php if (isset($_SESSION['username'])): ?>
    <script>
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
    <?php endif; ?>
</body>
</html>