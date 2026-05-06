<?php
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);

require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="equipment-export-' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
if ($output === false) {
    http_response_code(500);
    echo 'Unable to create export output.';
    exit;
}

$columns = ['ID', 'Name', 'Category', 'Status', 'Location', 'QR Code'];
fputcsv($output, $columns);

$query = 'SELECT id, name, category, status, location, qr_code FROM equipment ORDER BY id DESC';
$result = $connection->query($query);
if ($result !== false) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['category'],
            $row['status'],
            $row['location'],
            $row['qr_code'],
        ]);
    }
}

fclose($output);
$connection->close();
