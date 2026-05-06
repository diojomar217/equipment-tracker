<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);
require_once __DIR__ . '/../config/db.php';

$notifications = [];
$unread_count = 0;

$checkTable = $connection->query("SHOW TABLES LIKE 'notifications'");
if (!$checkTable) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $connection->error]);
    exit;
}
if ($checkTable->num_rows > 0) {
    $countResult = $connection->query("SELECT COUNT(*) AS unread_count FROM notifications WHERE status = 'UNREAD'");
    $unread_count = $countResult ? (int)$countResult->fetch_assoc()['unread_count'] : 0;

    $isAll = isset($_GET['all']) && $_GET['all'] === '1';
    $limitSql = $isAll ? '' : ' LIMIT 50';
    $sql = "SELECT n.id, n.equipment_id, n.message, n.type, n.status, n.created_at, e.name AS equipment_name
            FROM notifications n
            LEFT JOIN equipment e ON n.equipment_id = e.id
            ORDER BY n.created_at DESC" . $limitSql;
    $result = $connection->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
}

echo json_encode([
    'success' => true,
    'unread_count' => $unread_count,
    'data' => $notifications,
]);
