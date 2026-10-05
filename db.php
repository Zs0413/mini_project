<?php
// Database configuration
$host = "sql103.iceiy.com"; 
$username = "icei_42968571"; 
$password = "Zhaoshi0103";     
$dbname = "icei_42968571_splash_mania_db"; 

// Create MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("A database connection error occurred. Please try again later.");
}

$conn->set_charset("utf8mb4");
?>