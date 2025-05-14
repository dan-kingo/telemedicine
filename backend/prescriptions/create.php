<?php
require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$role = $_SESSION['role'] ?? '';
$doctor_id = $_SESSION['user_id'];

if ($role !== 'doctor') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Only doctors can create prescriptions.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$appointment_id = $data['appointment_id'] ?? null;
$prescription_text = $data['prescription_text'] ?? null;

if (!$appointment_id || !$prescription_text) {
    http_response_code(400);
    echo json_encode(['error' => 'appointment_id and prescription_text are required']);
    exit;
}

try {
    // 1. Validate appointment
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$appointment_id, $doctor_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        http_response_code(404);
        echo json_encode(['error' => 'Appointment not found or unauthorized']);
        exit;
    }

    $patient_id = $appointment['patient_id'];

    // 2. Insert prescription
    $stmt = $conn->prepare("INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, prescription_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$appointment_id, $doctor_id, $patient_id, $prescription_text]);

    echo json_encode(['message' => 'Prescription created successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
