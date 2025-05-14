<?php
// backend/config/db.php

$host = 'localhost';
$dbname = 'telemedicine';
$username = 'root';      // Default for XAMPP
$password = '';          // Default is empty

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Enable error reporting
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: Use associative arrays as default fetch mode
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
