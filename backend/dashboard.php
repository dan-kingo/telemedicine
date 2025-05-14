<?php
// backend/dashboard.php

require_once './middleware/auth.php';  // Include the auth middleware

checkAuth();  // Check if the user is authenticated

// If authenticated, fetch and show dashboard content
echo json_encode(['message' => 'Welcome to the Dashboard!']);
?>
