<?php
// backend/appointments/respond.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

// Step 1: Auth middleware
checkAuth();
checkRole('doctor'); // Only doctors can respond

// Step 2: Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

// Step 3: Parse and validate input
$data = json_decode(file_get_contents('php://input'), true);

$appointment_id = intval($data['appointment_id'] ?? 0);
$status = strtolower(trim($data['status'] ?? ''));

$valid_statuses = ['accepted', 'rejected', 'completed'];

if (!$appointment_id || !in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid appointment_id and status are required.']);
    exit;
}

$doctor_id = $_SESSION['user_id'];

try {
    // Step 4: Check if the appointment belongs to the doctor
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$appointment_id, $doctor_id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Appointment not found or not yours.']);
        exit;
    }

    // Step 5: Update appointment status
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->execute([$status, $appointment_id]);

    echo json_encode(['message' => 'Appointment status updated to ' . $status]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
