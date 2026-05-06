<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);
require_once __DIR__ . '/../config/db.php';

$stats = [
    'total' => 0,
    'available' => 0,
    'in_use' => 0,
    'maintenance' => 0,
];
$recentLogs = [];

$statsSql = "SELECT
    COUNT(*) AS total,
    SUM(status = 'AVAILABLE') AS available,
    SUM(status = 'CHECK_OUT') AS in_use,
    SUM(status = 'MAINTENANCE') AS maintenance
FROM equipment";
$statsResult = $connection->query($statsSql);
if ($statsResult) {
    $stats = array_merge($stats, $statsResult->fetch_assoc());
}

$logsSql = "SELECT l.id, e.name AS equipment_name, l.action, l.user, l.created_at
    FROM logs l
    LEFT JOIN equipment e ON l.equipment_id = e.id
    ORDER BY l.created_at DESC
    LIMIT 5";
$logsResult = $connection->query($logsSql);
if ($logsResult) {
    while ($row = $logsResult->fetch_assoc()) {
        $recentLogs[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'stats' => [
        'total' => (int)$stats['total'],
        'available' => (int)$stats['available'],
        'in_use' => (int)$stats['in_use'],
        'maintenance' => (int)$stats['maintenance'],
    ],
    'recentLogs' => $recentLogs,
]);
