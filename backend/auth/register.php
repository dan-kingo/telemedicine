<?php
// backend/auth/register.php

require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? '');

if (!$fullName || !$email || !$password || !in_array($role, ['patient', 'doctor'])) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required and role must be patient or doctor.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered.']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $hashedPassword, $role]);

    // After successful insert:
header('Location: ../../login.html');
exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
