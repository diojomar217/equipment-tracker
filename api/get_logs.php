<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);

require_once __DIR__ . '/../config/db.php';

$equipment_id = isset($_GET['equipment_id']) ? trim($_GET['equipment_id']) : '';
$user_filter = isset($_GET['user']) ? trim($_GET['user']) : '';
$filterClauses = [];
if ($equipment_id !== '') {
    if (!filter_var($equipment_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid equipment ID.']);
        exit;
    }
    $filterClauses[] = 'l.equipment_id = ' . intval($equipment_id);
}
if ($user_filter !== '') {
    $filterClauses[] = 'l.user = ?';
}

$filterClause = !empty($filterClauses) ? 'WHERE ' . implode(' AND ', $filterClauses) : '';

$sql = "SELECT l.id, e.name AS equipment_name, COALESCE(u.name, l.user) AS user_display, l.action, l.created_at
        FROM logs l
        LEFT JOIN equipment e ON l.equipment_id = e.id
        LEFT JOIN users u ON l.user = u.username
        $filterClause
        ORDER BY l.created_at DESC";

$stmt = $connection->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $connection->error]);
    exit;
}

$params = [];
$types = '';
if ($user_filter !== '') {
    $params[] = $user_filter;
    $types .= 's';
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $connection->error]);
    exit;
}

$logs = [];
while ($row = $result->fetch_assoc()) {
    $equipmentName = $row['equipment_name'] ?: 'Equipment item';
    $action = $row['action'];
    $user = $row['user_display'] ?: 'Unknown user';
    $activity = '';

    switch ($action) {
        case 'CHECK_OUT':
        case 'BORROW':
            $activity = sprintf('%s borrowed %s', $user, $equipmentName);
            break;
        case 'CHECK_IN':
        case 'RETURN':
            $activity = sprintf('%s returned %s', $user, $equipmentName);
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