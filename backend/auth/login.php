<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    $redirectUrl = $user['role'] === 'doctor' 
        ? 'frontend/doctor/dashboard.html'
        : 'frontend/patient/dashboard.html';

    echo json_encode([
        'success' => true,
        'redirect' => $redirectUrl
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
