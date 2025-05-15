<?php
require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');
checkAuth();

$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'];
$appointment_id = $_GET['appointment_id'] ?? null;

try {
    if ($role === 'doctor') {
        $query = "
            SELECT p.*, 
                   d.full_name AS doctor_name, 
                   u.full_name AS patient_name 
            FROM prescriptions p
            JOIN users d ON p.doctor_id = d.id
            JOIN users u ON p.patient_id = u.id
            WHERE p.doctor_id = ?" . ($appointment_id ? " AND p.appointment_id = ?" : "") . "
            ORDER BY p.created_at DESC
        ";

        $stmt = $conn->prepare($query);
        $stmt->execute($appointment_id ? [$user_id, $appointment_id] : [$user_id]);

    } elseif ($role === 'patient') {
        $query = "
            SELECT p.*, 
                   d.full_name AS doctor_name, 
                   u.full_name AS patient_name 
            FROM prescriptions p
            JOIN users d ON p.doctor_id = d.id
            JOIN users u ON p.patient_id = u.id
            WHERE p.patient_id = ?" . ($appointment_id ? " AND p.appointment_id = ?" : "") . "
            ORDER BY p.created_at DESC
        ";

        $stmt = $conn->prepare($query);
        $stmt->execute($appointment_id ? [$user_id, $appointment_id] : [$user_id]);

    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized role']);
        exit;
    }

    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($prescriptions);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
