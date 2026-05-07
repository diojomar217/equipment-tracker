<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/notifications_helper.php';
auth_ensure_locations_table_exists($connection);
ensure_equipment_status_updated_at_column($connection);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed.']);
    exit;
}

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$action = isset($_POST['status']) ? trim($_POST['status']) : '';
$returnLocation = isset($_POST['return_location']) ? trim($_POST['return_location']) : '';
$currentUserId = auth_user()['id'] ?? '';
$currentUser = auth_user()['username'] ?? '';

$allowedActions = ['BORROW', 'RETURN', 'MAINTENANCE', 'COMPLETE_MAINTENANCE'];
$allowedLocationIds = [];

// Fetch allowed locations from DB
$locationResult = $connection->query('SELECT id FROM locations ORDER BY name');
if ($locationResult) {
    while ($row = $locationResult->fetch_assoc()) {
        $allowedLocationIds[] = (int)$row['id'];
    }
    $locationResult->free();
}

if ($id === '' || $action === '' || $currentUser === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id and action are required.']);
    exit;
}

if ($returnLocation !== '' && !in_array((int)$returnLocation, $allowedLocationIds, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid return location selected.']);
    exit;
}

if (!filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid equipment ID.']);
    exit;
}

if (!in_array($action, $allowedActions, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action value.']);
    exit;
}

if (($action === 'MAINTENANCE' || $action === 'COMPLETE_MAINTENANCE') && auth_user()['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Only admins can perform maintenance actions.']);
    exit;
}

$selectSql = 'SELECT status, assigned_to, location, return_location FROM equipment WHERE id = ? LIMIT 1';
$stmt = $connection->prepare($selectSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare select statement: ' . $connection->error]);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$currentData = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$currentData) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Equipment not found.']);
    exit;
}

$currentStatus = $currentData['status'];
$currentAssignedTo = (int)($currentData['assigned_to'] ?? 0);
$assignedTo = null;
$newStatus = '';
$newLocation = (int)($currentData['location'] ?? 0);
$newReturnLocation = (int)($currentData['return_location'] ?? 0);

if ($action === 'BORROW') {
    if ($currentStatus !== 'AVAILABLE') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Equipment must be available before it can be borrowed.']);
        exit;
    }
    if ($returnLocation === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Return location is required when borrowing equipment.']);
        exit;
    }
    $newStatus = 'BORROWED';
    $assignedTo = $currentUserId;
    $newLocation = (int)$returnLocation;
    $newReturnLocation = $currentData['return_location'];
} elseif ($action === 'RETURN') {
    if ($currentStatus !== 'BORROWED' || $currentAssignedTo != $currentUserId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Only the user who borrowed the equipment can return it.']);
        exit;
    }
    $newStatus = 'AVAILABLE';
    $assignedTo = null;
    $newLocation = $newReturnLocation;
    // Keep return_location unchanged (set by admin)
} elseif ($action === 'MAINTENANCE') {
    if ($currentStatus === 'MAINTENANCE') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Equipment is already in maintenance.']);
        exit;
    }
    $newStatus = 'MAINTENANCE';
    $assignedTo = null;
    $newLocation = $currentData['location'];
    // Keep return_location unchanged (set by admin)
} elseif ($action === 'COMPLETE_MAINTENANCE') {
    if ($currentStatus !== 'MAINTENANCE') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Equipment must be in maintenance to complete maintenance.']);
        exit;
    }
    $newStatus = 'AVAILABLE';
    $assignedTo = null;
    $newLocation = $currentData['location'];
    // Keep return_location unchanged (set by admin)
}

if ($assignedTo === null) {
    $updateSql = 'UPDATE equipment SET status = ?, assigned_to = NULL, location = ?, return_location = ?, status_updated_at = NOW() WHERE id = ?';
    $stmt = $connection->prepare($updateSql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare update statement: ' . $connection->error]);
        exit;
    }
    $stmt->bind_param('siii', $newStatus, $newLocation, $newReturnLocation, $id);
} else {
    $updateSql = 'UPDATE equipment SET status = ?, assigned_to = ?, location = ?, return_location = ?, status_updated_at = NOW() WHERE id = ?';
    $stmt = $connection->prepare($updateSql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare update statement: ' . $connection->error]);
        exit;
    }
    $stmt->bind_param('siiii', $newStatus, $assignedTo, $newLocation, $newReturnLocation, $id);
}

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

$stmt->bind_param('iss', $id, $action, $currentUser);
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