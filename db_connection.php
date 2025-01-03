<?php
// Database connection settings
$host = 'localhost'; // Replace with your database host
$dbName = 'leads_db'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    // Create a new PDO instance
    $conn = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $username, $password);
    
    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
}
?>
