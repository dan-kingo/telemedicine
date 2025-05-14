<?php
// backend/middleware/auth.php

session_start();  // Start session

// Function to check if user is authenticated
function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access. Please log in.']);
        exit;
    }
    return true;
}

// Function to check if user is a specific role
function checkRole($requiredRole) {
    if ($_SESSION['role'] !== $requiredRole) {
        http_response_code(403);  // Forbidden
        echo json_encode(['error' => 'Access denied. You do not have permission to view this resource.']);
        exit;
    }
}
?>
