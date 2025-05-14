<?php
// backend/doctor-dashboard.php

require_once '../../backend/middleware/auth.php';

checkAuth();  // Ensure user is authenticated
checkRole('doctor');  // Ensure user is a doctor

// Your code for doctor dashboard
echo json_encode(['message' => 'Welcome to the Doctor Dashboard!']);
?>
