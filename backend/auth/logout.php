<?php
// backend/auth/logout.php

session_start();  // Start session

// Destroy all session data
session_unset();
session_destroy();

header('Location: ../../index.html');
?>
