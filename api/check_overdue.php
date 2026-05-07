<?php
header('Content-Type: application/json; charset=utf-8');
if (PHP_SAPI !== 'cli') {
    require_once __DIR__ . '/../config/auth.php';
    auth_require_role(['Admin'], true);
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/notifications_helper.php';

ensure_notifications_table($connection);
ensure_equipment_status_updated_at_column($connection);

$query = "SELECT id, name, status_updated_at FROM equipment WHERE status = 'BORROWED' AND status_updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = $connection->query($query);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database query failed: ' . $connection->error]);
    exit;
}

$createdCount = 0;
while ($row = $result->fetch_assoc()) {
    if (!overdue_notification_exists($connection, $row['id'], $row['status_updated_at'])) {
        $message = 'Equipment "' . $row['name'] . '" has been borrowed for more than 7 days and is overdue.';
        if (insert_notification($connection, $row['id'], $message, 'overdue', 'UNREAD')) {
            $createdCount++;
        }
    }
}

$connection->close();

echo json_encode([
    'success' => true,
    'created' => $createdCount,
    'message' => $createdCount > 0 ? 'Created new overdue notifications.' : 'No overdue equipment found.',
]);
