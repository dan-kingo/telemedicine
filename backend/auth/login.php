<?php
// backend/auth/login.php

require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Only POST method is allowed";
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    http_response_code(400);
    echo "Email and password are required.";
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo "Invalid email or password.";
        exit;
    }

    // Store session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    // Redirect based on role
    if ($user['role'] === 'patient') {
        header('Location: ../../frontend/patient/dashboard.html');
        exit;
    } else {
        header('Location: ../../frontend/doctor/dashboard.html');
    }
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo "Server error: " . $e->getMessage();
}
?>
