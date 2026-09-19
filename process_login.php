<?php
session_start();
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role_id'] = $user['role_id'];

    $log = $conn->prepare("INSERT INTO audit_logs (user_id, activity, ip_address) VALUES (?, 'User logged in', ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $log->bind_param("is", $user['user_id'], $ip);
    $log->execute();
       header("Location: index.php");
    exit();
} else {
    header("Location: login.php?error=1");
    exit();
}
?>