<?php
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin'], true);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Location name is required.']);
    exit;
}

if (strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Location name must be at least 2 characters.']);
    exit;
}

// Check if name exists
$stmt = $connection->prepare('SELECT id FROM locations WHERE name = ? LIMIT 1');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('s', $name);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $connection->close();
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Location name already exists.']);
    exit;
}
$stmt->close();

// Insert new location
$stmt = $connection->prepare('INSERT INTO locations (name) VALUES (?)');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('s', $name);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Location added successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to add location.']);
}
$stmt->close();
$connection->close();
?>