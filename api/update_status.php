<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/notifications_helper.php';
ensure_equipment_status_updated_at_column($connection);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed.']);
    exit;
}

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$user = isset($_POST['user']) ? trim($_POST['user']) : '';

$allowedStatuses = ['CHECK_IN', 'CHECK_OUT', 'MAINTENANCE'];

if ($id === '' || $status === '' || $user === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id, status, and user are required.']);
    exit;
}

if (!filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid equipment ID.']);
    exit;
}

if (!in_array($status, $allowedStatuses, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status value.']);
    exit;
}

$updateSql = 'UPDATE equipment SET status = ?, status_updated_at = NOW() WHERE id = ?';
$stmt = $connection->prepare($updateSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare update statement: ' . $connection->error]);
    exit;
}

$stmt->bind_param('si', $status, $id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Status update failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

if ($stmt->affected_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'No equipment record updated.']);
    exit;
}

$stmt->close();

$logSql = 'INSERT INTO logs (equipment_id, action, user, created_at) VALUES (?, ?, ?, NOW())';
$stmt = $connection->prepare($logSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare log insert statement: ' . $connection->error]);
    exit;
}

$stmt->bind_param('iss', $id, $status, $user);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Log insert failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$stmt->close();
$connection->close();

echo json_encode(['success' => true, 'message' => 'Equipment status updated successfully.']);
?>