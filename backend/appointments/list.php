<?php
// backend/appointments/list.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');
checkAuth();

$doctor_id = $_SESSION['user_id'] ?? null;

try {
    $stmt = $conn->prepare("
        SELECT a.id, a.appointment_date AS date, a.appointment_time as time, a.status, a.patient_id, u.full_name as patient_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$doctor_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($appointments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
