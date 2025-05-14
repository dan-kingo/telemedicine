<?php
// backend/appointments/view.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

// Step 1: Auth middleware
checkAuth();

// Get current user details
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    if ($role === 'patient') {
        // Step 2: Get appointments booked by this patient
        $stmt = $conn->prepare("
            SELECT a.*, u.full_name AS doctor_name 
            FROM appointments a
            JOIN users u ON a.doctor_id = u.id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([$user_id]);

    } elseif ($role === 'doctor') {
        // Step 3: Get appointments booked with this doctor
        $stmt = $conn->prepare("
            SELECT a.*, u.full_name AS patient_name 
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE a.doctor_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([$user_id]);

    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized role.']);
        exit;
    }

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['appointments' => $appointments]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
