<?php
// backend/middleware/auth.php

session_start();  // Start session

function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // If no session is found for user ID or role, return an unauthorized response
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access. Please log in.']);
        exit;
    }
    // If session exists, user is authenticated
    return true;
}
?>
