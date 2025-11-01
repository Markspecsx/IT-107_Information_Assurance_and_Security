<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $question = sanitizeInput($_POST['question']);
    $answer = sanitizeInput($_POST['answer']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, $question FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && strtolower($user[$question]) === strtolower($answer)) {
            session_start();
            $_SESSION['reset_user_id'] = $user['id'];
            echo json_encode(['success' => true, 'message' => 'Verification successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect answer']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Verification failed: ' . $e->getMessage()]);
    }
}
?>
