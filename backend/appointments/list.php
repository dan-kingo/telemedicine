<?php
// backend/appointments/list.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');
checkAuth();

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';

try {
    if ($role === 'doctor') {
        $stmt = $conn->prepare("
            SELECT a.id, a.appointment_date AS date, a.appointment_time AS time, a.status, a.patient_id, u.full_name AS patient_name
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE a.doctor_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([$user_id]);
    } elseif ($role === 'patient') {
        $stmt = $conn->prepare("
            SELECT a.id, a.appointment_date AS date, a.appointment_time AS time, a.status, a.doctor_id, u.full_name AS doctor_name
            FROM appointments a
            JOIN users u ON a.doctor_id = u.id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([$user_id]);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized role']);
        exit;
    }

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($appointments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
