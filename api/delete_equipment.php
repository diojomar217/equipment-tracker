<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role('Admin', true);

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed.']);
    exit;
}

$id = isset($_POST['id']) ? trim($_POST['id']) : '';

if ($id === '' || !filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid equipment ID.']);
    exit;
}

// Check if equipment exists and get image path
$checkSql = 'SELECT image FROM equipment WHERE id = ? LIMIT 1';
$checkStmt = $connection->prepare($checkSql);
if ($checkStmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare check statement: ' . $connection->error]);
    exit;
}
$checkStmt->bind_param('i', $id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$equipment = $result->fetch_assoc();
$checkStmt->close();

if (!$equipment) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Equipment not found.']);
    exit;
}

// Delete the image file if exists
if (!empty($equipment['image'])) {
    $imagePath = __DIR__ . '/../' . $equipment['image'];
    if (file_exists($imagePath)) {
        @unlink($imagePath);
    }
}

// Delete logs associated with the equipment
$deleteLogsSql = 'DELETE FROM logs WHERE equipment_id = ?';
$deleteLogsStmt = $connection->prepare($deleteLogsSql);
if ($deleteLogsStmt) {
    $deleteLogsStmt->bind_param('i', $id);
    $deleteLogsStmt->execute();
    $deleteLogsStmt->close();
}

// Delete the equipment
$deleteSql = 'DELETE FROM equipment WHERE id = ?';
$deleteStmt = $connection->prepare($deleteSql);
if ($deleteStmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare delete statement: ' . $connection->error]);
    exit;
}
$deleteStmt->bind_param('i', $id);
if (!$deleteStmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Delete failed: ' . $deleteStmt->error]);
    $deleteStmt->close();
    exit;
}

$deleteStmt->close();
$connection->close();

echo json_encode(['success' => true]);
?>