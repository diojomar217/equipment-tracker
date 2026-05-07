<?php
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin'], true);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'User ID is required.']);
    exit;
}

$currentUser = auth_user();

// Prevent deleting self
if ($id == $currentUser['id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'You cannot delete your own account.']);
    exit;
}

// Check if this is the last admin
$result = $connection->query('SELECT COUNT(*) as admin_count FROM users WHERE role = "Admin"');
if ($result) {
    $row = $result->fetch_assoc();
    $adminCount = $row['admin_count'];
    $result->free();

    // Check if the user to delete is an admin
    $stmt = $connection->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && $user['role'] === 'Admin' && $adminCount <= 1) {
        $connection->close();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot delete the last admin user.']);
        exit;
    }
}

// Delete user
$stmt = $connection->prepare('DELETE FROM users WHERE id = ?');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete user.']);
}
$stmt->close();
$connection->close();
?>