<?php
require_once __DIR__ . '/../config/auth.php';
auth_require_login(true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../api/auth_helper.php';

auth_ensure_categories_table_exists($connection);

header('Content-Type: application/json; charset=utf-8');

$categories = [];
$result = $connection->query('SELECT id, name FROM categories ORDER BY name');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $result->free();
}
$connection->close();

echo json_encode(['success' => true, 'categories' => $categories]);
?>