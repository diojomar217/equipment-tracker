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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (!$id || empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID and name are required.']);
    exit;
}

if (strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Location name must be at least 2 characters.']);
    exit;
}

// Check if name exists for another location
$stmt = $connection->prepare('SELECT id FROM locations WHERE name = ? AND id != ? LIMIT 1');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('si', $name, $id);
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

// Update location
$stmt = $connection->prepare('UPDATE locations SET name = ? WHERE id = ?');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('si', $name, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Location updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update location.']);
}
$stmt->close();
$connection->close();
?>