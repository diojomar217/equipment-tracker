<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role('Admin', true);

require_once __DIR__ . '/../config/db.php';

$equipment_id = isset($_GET['equipment_id']) ? trim($_GET['equipment_id']) : '';
$filterClause = '';
if ($equipment_id !== '') {
    if (!filter_var($equipment_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid equipment ID.']);
        exit;
    }
    $filterClause = 'WHERE l.equipment_id = ' . intval($equipment_id);
}

$sql = "SELECT l.id, e.name AS equipment_name, l.action, l.user, l.created_at
        FROM logs l
        LEFT JOIN equipment e ON l.equipment_id = e.id
        $filterClause
        ORDER BY l.created_at DESC";

$result = $connection->query($sql);
if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $connection->error]);
    exit;
}

$logs = [];
while ($row = $result->fetch_assoc()) {
    $equipmentName = $row['equipment_name'] ?: 'Equipment item';
    $action = $row['action'];
    $user = $row['user'] ?: 'Unknown user';
    $activity = '';

    switch ($action) {
        case 'CHECK_OUT':
            $activity = sprintf('%s checked out %s', $user, $equipmentName);
            break;
        case 'CHECK_IN':
            $activity = sprintf('%s checked in %s', $user, $equipmentName);
            break;
        case 'MAINTENANCE':
            $activity = sprintf('%s sent %s to maintenance', $user, $equipmentName);
            break;
        default:
            $activity = sprintf('%s performed %s on %s', $user, strtolower(str_replace('_', ' ', $action)), $equipmentName);
            break;
    }

    $row['activity'] = $activity;
    $logs[] = $row;
}

$connection->close();

echo json_encode(['success' => true, 'data' => $logs]);
?>