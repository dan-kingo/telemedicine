<?php
session_start();
require_once '../config/db.php'; // Adjust if needed

header('Content-Type: application/json');

// Ensure doctor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access. Please log in as a doctor.']);
    exit;
}

$doctorId = $_SESSION['user_id'];
$today = date('Y-m-d');
$monthStart = date('Y-m-01');

try {
    // Fetch doctor's name
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    $doctorName = $doctor ? $doctor['full_name'] : 'Doctor';

    // Fetch today's appointments WITH patient name
$stmt = $conn->prepare("
  SELECT u.full_name AS patient_name, a.appointment_time 
  FROM appointments a 
  JOIN users u ON a.patient_id = u.id 
  WHERE a.doctor_id = ? AND a.appointment_date = ? 
  ORDER BY a.appointment_time ASC
");
$stmt->execute([$doctorId, $today]);
$appointmentsToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch 3 most recent patients WITH name
$stmt = $conn->prepare("
  SELECT DISTINCT u.full_name AS patient_name 
  FROM appointments a 
  JOIN users u ON a.patient_id = u.id 
  WHERE a.doctor_id = ? 
  ORDER BY a.appointment_date DESC 
  LIMIT 3
");
$stmt->execute([$doctorId]);
$recentPatients = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Count of unique patients this month
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT patient_id) AS total_patients FROM appointments WHERE doctor_id = ? AND appointment_date >= ?");
    $stmt->execute([$doctorId, $monthStart]);
    $totalPatients = $stmt->fetchColumn();

    // Count of prescriptions written this month
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_prescriptions FROM prescriptions WHERE doctor_id = ? AND created_at >= ?");
    $stmt->execute([$doctorId, $monthStart]);
    $totalPrescriptions = $stmt->fetchColumn();

    echo json_encode([
        'doctor_name' => $doctorName,
        'appointments_today' => $appointmentsToday,
        'recent_patients' => $recentPatients,
        'total_patients_this_month' => (int)$totalPatients,
        'total_prescriptions_this_month' => (int)$totalPrescriptions
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
