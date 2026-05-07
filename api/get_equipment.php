<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth_helper.php';
auth_ensure_locations_table_exists($connection);

$columnCheck = $connection->query("SHOW COLUMNS FROM equipment LIKE 'location'");
if ($columnCheck && $columnCheck->num_rows === 0) {
    $connection->query("ALTER TABLE equipment ADD COLUMN location INT NULL AFTER status");
}

$allowedLocationIds = [];

// Fetch allowed locations from DB
$locationResult = $connection->query('SELECT id, name FROM locations ORDER BY name');
if ($locationResult) {
    while ($row = $locationResult->fetch_assoc()) {
        $allowedLocationIds[] = (int)$row['id'];
    }
    $locationResult->free();
}

$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$assigned_to = isset($_GET['assigned_to']) ? trim($_GET['assigned_to']) : '';

$conditions = [];
$params = [];
$types = '';

if ($location !== '' && in_array((int)$location, $allowedLocationIds, true)) {
    $conditions[] = 'location = ?';
    $params[] = (int)$location;
    $types .= 'i';
}

if ($status !== '') {
    $conditions[] = 'status = ?';
    $params[] = $status;
    $types .= 's';
}

if ($assigned_to !== '') {
    $conditions[] = 'assigned_to = ?';
    $params[] = $assigned_to;
    $types .= 's';
}

$sql = 'SELECT e.*, c.name AS category, l.name AS location, ro.name AS return_location, e.category AS category_id, e.location AS location_id, e.return_location AS return_location_id FROM equipment e LEFT JOIN categories c ON e.category = c.id LEFT JOIN locations l ON e.location = l.id LEFT JOIN locations ro ON e.return_location = ro.id';
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
if ($search !== '') {
    $searchCondition = $sql . (empty($conditions) ? ' WHERE' : ' AND') . ' (e.name LIKE ? OR c.name LIKE ?)';
    $sql = $searchCondition;
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $types .= 'ss';
}
$sql .= ' ORDER BY e.id DESC';

$stmt = $connection->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database query failed: ' . $connection->error,
    ]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database query failed: ' . $connection->error,
    ]);
    exit;
}

$equipment = [];
while ($row = $result->fetch_assoc()) {
    $equipment[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $equipment,
]);

$connection->close();
?>