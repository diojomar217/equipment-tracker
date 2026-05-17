<?php
// Database connection settings

// Local and shared credential values
require_once __DIR__ . '/db_config.php';

// PROD
// $host = 'sql207.infinityfree.com';
// $username = 'if0_41849753';
// $password = '8JlOC2m9Zt8';
// $database = 'if0_41849753_equipment_tracker';

// Create connection
$connection = new mysqli($host, $username, $password, $database);

// Check connection
if ($connection->connect_error) {
    die('Database connection failed: ' . $connection->connect_error);
}

// $connection is now available to included scripts
?>