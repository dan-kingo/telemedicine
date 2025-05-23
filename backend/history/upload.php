<?php
// backend/history/upload.php

require_once '../config/db.php';
require_once '../middleware/auth.php';

header('Content-Type: application/json');

checkAuth();
checkRole('patient'); // Only patients can upload

$patient_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

// Check if file exists and has a name
if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$description = $_POST['description'] ?? '';
$file = $_FILES['file'];

$targetDir = '../../uploads/history/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$originalFilename = basename($file['name']);

// Create saved filename with timestamp prefix to avoid conflicts
$savedFileName = time() . '_' . $originalFilename;
$targetFile = $targetDir . $savedFileName;

$fileType = strtolower(pathinfo($savedFileName, PATHINFO_EXTENSION));
$allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];

if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only PDF, JPG, and PNG allowed.']);
    exit;
}

// Move uploaded file to target directory
if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    try {
        // Insert into database: store savedFileName (with timestamp), not originalFilename
        $stmt = $conn->prepare("INSERT INTO medical_history (patient_id, file_name, file_path, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $patient_id,
            $savedFileName,  // this is the key fix: store saved filename here
            $targetFile,
            $description
        ]);

        echo json_encode(['message' => 'File uploaded successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to upload file.']);
}
