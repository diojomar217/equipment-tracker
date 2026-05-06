<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);

require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if ($id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Equipment id is required.']);
    exit;
}

$sql = 'SELECT * FROM equipment WHERE id = ? LIMIT 1';
$stmt = $connection->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $connection->error]);
    exit;
}

$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();
$equipment = $result ? $result->fetch_assoc() : null;
$stmt->close();
$connection->close();

if (!$equipment) {
    echo json_encode(['success' => false, 'error' => 'Equipment not found.']);
    exit;
}

echo json_encode(['success' => true, 'data' => $equipment]);
?>