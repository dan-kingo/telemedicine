<?php
// backend/history/fetch.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

checkAuth();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

try {
    if ($role === 'patient') {
        // Patient sees their own uploads
        $stmt = $conn->prepare("SELECT id, file_name, uploaded_at FROM medical_history WHERE patient_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$user_id]);

    } elseif ($role === 'doctor') {
        // Doctor can optionally view a specific patient's files
        $patient_id = $_GET['patient_id'] ?? null;

        if (!$patient_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing patient_id']);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, file_name, uploaded_at FROM medical_history WHERE patient_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$patient_id]);

    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }

    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Append full download URL using the stored filename (with timestamp)
    foreach ($files as &$file) {
        $file['download_url'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/telemedicine-app/uploads/history/' . $file['file_name'];
    }

    echo json_encode($files);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
