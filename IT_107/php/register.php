<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ensure responses are JSON
    header('Content-Type: application/json; charset=utf-8');
    // Sanitize inputs
    $id_no = sanitizeInput($_POST['id_no']);
    $first_name = sanitizeInput($_POST['first_name']);
    $middle_name = sanitizeInput($_POST['middle_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $extension_name = sanitizeInput($_POST['extension_name']);
    $other_extension = sanitizeInput($_POST['other_extension']);
    $birthdate = sanitizeInput($_POST['birthdate']);
    $age = (int)sanitizeInput($_POST['age']);
    $sex = sanitizeInput($_POST['sex']);
    $street = sanitizeInput($_POST['street']);
    $barangay = sanitizeInput($_POST['barangay']);
    $city = sanitizeInput($_POST['city']);
    $province = sanitizeInput($_POST['province']);
    $country = sanitizeInput($_POST['country']);
    $zipcode = sanitizeInput($_POST['zipcode']);
    $email = sanitizeInput($_POST['email']);
    $username = sanitizeInput($_POST['username']);
    $password = hashPassword($_POST['password']);
    $question1 = sanitizeInput($_POST['question1']);
    $question2 = sanitizeInput($_POST['question2']);
    $question3 = sanitizeInput($_POST['question3']);
    
    // Use other extension if selected
    if ($extension_name === 'Other' && !empty($other_extension)) {
        $extension_name = $other_extension;
    }
    
    try {
        // Check if ID number already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id_no = ?");
        $stmt->execute([$id_no]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'ID Number already exists']);
            exit;
        }
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
        
        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (
                id_no, first_name, middle_name, last_name, extension_name, 
                birthdate, age, sex, street, barangay, city, province, 
                country, zipcode, email, username, password, 
                question1, question2, question3, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $id_no, $first_name, $middle_name, $last_name, $extension_name,
            $birthdate, $age, $sex, $street, $barangay, $city, $province,
            $country, $zipcode, $email, $username, $password,
            $question1, $question2, $question3
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}
?>
