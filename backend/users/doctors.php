<?php
require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');
checkAuth();

try {
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE role = 'doctor'");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($doctors);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
