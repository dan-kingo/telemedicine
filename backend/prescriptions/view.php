<?php
require_once '../config/db.php';
require_once '../middleware/auth.php';
require_once '../../lib/fpdf/fpdf.php';

checkAuth();

$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'];

$appointment_id = $_GET['appointment_id'] ?? null;
$download = isset($_GET['download']) && $_GET['download'] == 1;

try {
    if ($role === 'doctor') {
        $query = "SELECT p.*, 
                         d.full_name AS doctor_name, 
                         u.full_name AS patient_name 
                  FROM prescriptions p
                  JOIN users d ON p.doctor_id = d.id
                  JOIN users u ON p.patient_id = u.id
                  WHERE p.doctor_id = ?" . ($appointment_id ? " AND p.appointment_id = ?" : "") . " 
                  ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute($appointment_id ? [$user_id, $appointment_id] : [$user_id]);
    } elseif ($role === 'patient') {
        $query = "SELECT p.*, 
                         d.name AS doctor_name, 
                         u.name AS patient_name 
                  FROM prescriptions p
                  JOIN users d ON p.doctor_id = d.id
                  JOIN users u ON p.patient_id = u.id
                  WHERE p.patient_id = ?" . ($appointment_id ? " AND p.appointment_id = ?" : "") . " 
                  ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute($appointment_id ? [$user_id, $appointment_id] : [$user_id]);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized role']);
        exit;
    }

    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($download) {
        if (empty($prescriptions)) {
            echo "No prescriptions to download.";
            exit;
        }

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Prescriptions Report', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);

        foreach ($prescriptions as $p) {
            $pdf->Ln(10);
            $pdf->Cell(0, 10, 'Prescription ID: ' . $p['id'], 0, 1);
            $pdf->Cell(0, 10, 'Doctor: ' . $p['doctor_name'], 0, 1);
            $pdf->Cell(0, 10, 'Patient: ' . $p['patient_name'], 0, 1);
            $pdf->Cell(0, 10, 'Appointment ID: ' . $p['appointment_id'], 0, 1);
            $pdf->MultiCell(0, 10, 'Prescription: ' . $p['prescription_text']);
            $pdf->Cell(0, 10, 'Date: ' . $p['created_at'], 0, 1);
            $pdf->Ln(5);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        }

        $pdf->Output('D', 'prescriptions.pdf');
        exit;

    } else {
        echo json_encode($prescriptions);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
