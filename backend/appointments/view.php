<?php
// backend/history/view.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

checkAuth();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

// For doctor: can pass ?patient_id=2
$target_patient_id = $role === 'doctor' ? ($_GET['patient_id'] ?? null) : $user_id;

if (!$target_patient_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Patient ID required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, file_name, file_path, description, uploaded_at FROM medical_history WHERE patient_id = ?");
    $stmt->execute([$target_patient_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert absolute path to downloadable URL
    $base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/uploads/history/';

    $files = array_map(function ($row) use ($base_url) {
        return [
            'id' => $row['id'],
            'file_name' => $row['file_name'],
            'description' => $row['description'],
            'uploaded_at' => $row['uploaded_at'],
            'download_url' => $base_url . basename($row['file_path'])
        ];
    }, $results);

    echo json_encode($files);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
