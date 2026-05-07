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

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Location ID is required.']);
    exit;
}

// Check if location is in use
$stmt = $connection->prepare('SELECT COUNT(*) as count FROM equipment WHERE location = ?');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $row['count'];
$stmt->close();

if ($count > 0) {
    $connection->close();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cannot delete location that is currently assigned to equipment.']);
    exit;
}

// Delete location
$stmt = $connection->prepare('DELETE FROM locations WHERE id = ?');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Location deleted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete location.']);
}
$stmt->close();
$connection->close();
?>