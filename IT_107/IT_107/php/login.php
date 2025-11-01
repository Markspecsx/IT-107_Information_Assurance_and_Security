<?php
session_start();
require_once 'config.php';
session_start();

// DEBUG: enable temporary logging to php/login_debug.log (set to false to disable)
$ENABLE_DEBUG = true;
function debug_log($line) {
    global $ENABLE_DEBUG;
    if (!$ENABLE_DEBUG) return;
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'login_debug.log';
    $time = date('Y-m-d H:i:s');
    // append line
    file_put_contents($file, "[$time] " . $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Helper: compute lockout duration based on failed attempts (groups of 3)
function computeLockoutSeconds($failed) {
    $durations = [15, 30, 60];
    $group = intdiv(max(1, $failed) - 1, 3); // 0 -> first 3, 1 -> next 3, ...
    if ($group >= count($durations)) return end($durations);
    return $durations[$group];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Initialize session counters
    if (!isset($_SESSION['failed_attempts'])) $_SESSION['failed_attempts'] = 0;
    if (!isset($_SESSION['lockout_end'])) $_SESSION['lockout_end'] = 0;

    // If currently locked out, return remaining time
    $now = time();
    if ($_SESSION['lockout_end'] > $now) {
        $remaining = $_SESSION['lockout_end'] - $now;
        echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again later.', 'lockout' => true, 'remaining' => $remaining, 'failedAttempts' => $_SESSION['failed_attempts']]);
        exit;
    }

    // Basic server-side validation
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please provide username and password']);
        exit;
    }

    // Username validation: 3-50 chars, alphanumeric or underscore
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Invalid username format']);
        exit;
    }

    // Password validation: 8-100 chars, contains letter and number
    if (strlen($password) < 8 || strlen($password) > 100 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Invalid password format']);
        exit;
    }

    try {
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    debug_log("Login attempt for username='$username' - userFound=" . (boolval($user) ? '1' : '0') . " failedAttempts=" . $_SESSION['failed_attempts']);

        if ($user && verifyPassword($password, $user['password'])) {
            // successful login: reset counters
            debug_log("Password verified for username='$username'. Successful login.");
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['lockout_end'] = 0;
            echo json_encode(['success' => true, 'message' => 'Login successful']);
            exit;
        } else {
            // failed login
            debug_log("Password verification failed for username='$username'.");
            $_SESSION['failed_attempts']++;
            $failed = $_SESSION['failed_attempts'];

            // If this is a multiple of 3, apply lockout
            if ($failed % 3 === 0) {
                $seconds = computeLockoutSeconds($failed);
                $_SESSION['lockout_end'] = $now + $seconds;
                echo json_encode(['success' => false, 'message' => 'Too many attempts. Locked out.', 'lockout' => true, 'remaining' => $seconds, 'failedAttempts' => $failed]);
                exit;
            }

            echo json_encode(['success' => false, 'message' => 'Invalid username or password', 'failedAttempts' => $failed]);
            exit;
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
        exit;
    }
}
?>
