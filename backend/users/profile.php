<?php
require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');
checkAuth();

try {
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Return user profile info (you can add more fields as needed)
    echo json_encode([
        'id' => $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role']
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
