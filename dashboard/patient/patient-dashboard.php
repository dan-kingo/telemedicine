<?php
// backend/patient-dashboard.php

require_once '../../backend/middleware/auth.php';

checkAuth();  // Ensure user is authenticated
checkRole('patient');  // Ensure user is a patient

// Your code for patient dashboard
echo json_encode(['message' => 'Welcome to the Patient Dashboard!']);
?>
