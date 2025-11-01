<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['reset_user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $new_password = hashPassword($_POST['new_password']);
    $user_id = $_SESSION['reset_user_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);
        
        unset($_SESSION['reset_user_id']);
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Password change failed: ' . $e->getMessage()]);
    }
}
?>
