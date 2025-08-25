<?php
// Database credentials
$servername = "localhost"; // Or your database server IP/hostname
$username = "root"; // Your database username
$password = "0012"; // Your database password
$dbname = "HMC_ASSESSMENT"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8 for proper encoding
$conn->set_charset("utf8");

// Optional: Error reporting for development
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
?>