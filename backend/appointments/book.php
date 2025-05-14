<?php
// backend/appointments/book.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

// Step 1: Auth middleware
checkAuth();
checkRole('patient'); // Only patients can book appointments

// Step 2: Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

// Step 3: Parse and validate request body
$data = json_decode(file_get_contents('php://input'), true);

$doctor_id = intval($data['doctor_id'] ?? 0);
$appointment_date = trim($data['appointment_date'] ?? '');
$appointment_time = trim($data['appointment_time'] ?? '');
$reason = trim($data['reason'] ?? '');

$patient_id = $_SESSION['user_id']; // From session

if (!$doctor_id || !$appointment_date || !$appointment_time) {
    http_response_code(400);
    echo json_encode(['error' => 'Doctor ID, date, and time are required.']);
    exit;
}

try {
    // Step 4: Check if doctor exists and is a valid doctor
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'doctor'");
    $stmt->execute([$doctor_id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Doctor not found or not a valid doctor.']);
        exit;
    }

    // Step 5: Check for conflicting appointment
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'This time slot is already booked.']);
        exit;
    }

    // Step 6: Insert the appointment
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $reason]);

    http_response_code(200);
    echo json_encode(['message' => 'Appointment booked successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
