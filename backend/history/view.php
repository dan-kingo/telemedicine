<?php
// backend/history/view.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

checkAuth();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

$target_patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;

try {
    if ($role === 'doctor' && !$target_patient_id) {
        // Case 1: Return appointments assigned to this doctor
        $stmt = $conn->prepare("
            SELECT a.id, a.date, a.time, a.status, a.patient_id, u.name AS patient_name
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE a.doctor_id = ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$user_id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($appointments);
        exit;
    }

    // Case 2: Return medical history for the patient (doctor or patient)
    if (!$target_patient_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Patient ID required']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT id, file_name, file_path, description, uploaded_at
        FROM medical_history
        WHERE patient_id = ?
    ");
    $stmt->execute([$target_patient_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/uploads/history/';

    $files = array_map(function ($row) use ($base_url) {
    return [
        'id' => $row['id'],
        'file_name' => $row['file_name'],
        'description' => $row['description'],
        'uploaded_at' => $row['uploaded_at'],
        'download_url' => $base_url . rawurlencode(basename($row['file_path']))
    ];
}, $results);


    echo json_encode($files);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
