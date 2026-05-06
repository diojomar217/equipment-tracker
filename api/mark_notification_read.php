<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed.']);
    exit;
}

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$all = isset($_POST['all']) ? $_POST['all'] === '1' : false;

if (!$all && ($id === '' || !filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Notification ID is required.']);
    exit;
}

if ($all) {
    $updateSql = "UPDATE notifications SET status = 'READ' WHERE status = 'UNREAD'";
    $success = $connection->query($updateSql);
} else {
    $stmt = $connection->prepare("UPDATE notifications SET status = 'READ' WHERE id = ?");
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement.']);
        exit;
    }
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();
}

if ($success === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update notification status.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Notification(s) marked as read.']);
