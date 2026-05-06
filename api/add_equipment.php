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

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$allowedLocations = ['Room 101', 'Storage', 'Office', 'Lab'];

if ($name === '' || $category === '' || $location === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name, category, and location are required.']);
    exit;
}

if (!in_array($location, $allowedLocations, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid location selected.']);
    exit;
}

$columnCheck = $connection->query("SHOW COLUMNS FROM equipment LIKE 'location'");
if ($columnCheck && $columnCheck->num_rows === 0) {
    $connection->query("ALTER TABLE equipment ADD COLUMN location VARCHAR(100) NULL AFTER status");
}

// Prevent duplicate equipment names
$checkSql = 'SELECT id FROM equipment WHERE name = ? LIMIT 1';
$checkStmt = $connection->prepare($checkSql);
if ($checkStmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare duplicate check: ' . $connection->error]);
    exit;
}
$checkStmt->bind_param('s', $name);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'An equipment item with that name already exists.']);
    exit;
}
$checkStmt->close();

$status = 'AVAILABLE';
$assignedTo = null;

$insertSql = 'INSERT INTO equipment (name, category, status, location, assigned_to) VALUES (?, ?, ?, ?, ?)';
$stmt = $connection->prepare($insertSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare insert statement: ' . $connection->error]);
    exit;
}

$stmt->bind_param('sssss', $name, $category, $status, $location, $assignedTo);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Insert failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$insertId = $stmt->insert_id;
$stmt->close();

$qrCode = 'equipment_id=' . $insertId;
$updateSql = 'UPDATE equipment SET qr_code = ? WHERE id = ?';
$stmt = $connection->prepare($updateSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare update statement: ' . $connection->error]);
    exit;
}

$stmt->bind_param('si', $qrCode, $insertId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$stmt->close();
$connection->close();

echo json_encode([
    'success' => true,
    'data' => [
        'id' => $insertId,
        'qr_code' => $qrCode,
    ],
]);
?>