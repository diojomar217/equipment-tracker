<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);

require_once __DIR__ . '/../config/db.php';

$columnCheck = $connection->query("SHOW COLUMNS FROM equipment LIKE 'location'");
if ($columnCheck && $columnCheck->num_rows === 0) {
    $connection->query("ALTER TABLE equipment ADD COLUMN location VARCHAR(100) NULL AFTER status");
}

$allowedLocations = ['Room 101', 'Storage', 'Office', 'Lab'];
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

$sql = 'SELECT * FROM equipment';
if ($location !== '' && in_array($location, $allowedLocations, true)) {
    $sql .= ' WHERE location = ?';
}
$sql .= ' ORDER BY id DESC';

$stmt = $connection->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database query failed: ' . $connection->error,
    ]);
    exit;
}

if ($location !== '' && in_array($location, $allowedLocations, true)) {
    $stmt->bind_param('s', $location);
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