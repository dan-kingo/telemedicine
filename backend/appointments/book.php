<?php
// backend/appointments/book.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

// Only authenticated patients can book
checkAuth();
checkRole('patient');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$doctor_id = intval($data['doctor_id'] ?? 0);
$appointment_date = trim($data['appointment_date'] ?? '');
$appointment_time = trim($data['appointment_time'] ?? '');
$reason = trim($data['reason'] ?? '');

$patient_id = $_SESSION['user_id']; // From session

// Validation
if (!$doctor_id || !$appointment_date || !$appointment_time) {
    http_response_code(400);
    echo json_encode(['error' => 'Doctor, date, and time are required.']);
    exit;
}

try {
    // Check for time conflict
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'This time slot is already booked.']);
        exit;
    }

    // Insert appointment
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $reason]);

    echo json_encode(['message' => 'Appointment booked successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
