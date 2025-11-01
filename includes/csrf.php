<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // CSRF token mismatch
        die('CSRF token validation failed.');
    }
    // Invalidate the token so it can't be used again
    unset($_SESSION['csrf_token']);
}
?>