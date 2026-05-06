<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role('Admin');

// Include the database connection file
require_once __DIR__ . '/config/db.php';

// If we reach this point, the connection is successful
echo 'Database connected successfully';

// Close the connection when finished
$connection->close();
?>