<?php
include '../includes/db_connect.php';

if (isset($_POST['username'])) {
    $username = trim($_POST['username']);

    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "unavailable";
    } else {
        echo "available";
    }
    $stmt->close();
}
$conn->close();
?>