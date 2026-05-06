<?php
// Database connection settings
$host = 'localhost';
$username = 'root';
$password = 'admin';
$database = 'equipment_tracker';

// Create connection
$connection = new mysqli($host, $username, $password, $database);

// Check connection
if ($connection->connect_error) {
    die('Database connection failed: ' . $connection->connect_error);
}

// $connection is now available to included scripts
?>