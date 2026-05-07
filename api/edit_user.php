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
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

if (!$id || empty($name) || empty($username) || empty($role)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID, username, and role are required.']);
    exit;
}

if (!in_array($role, ['Admin', 'Staff'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid role.']);
    exit;
}

if (strlen($name) < 3 || strlen($username) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name and username must be at least 3 characters.']);
    exit;
}

// Check if username exists for another user
$stmt = $connection->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('si', $username, $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $connection->close();
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Username already exists.']);
    exit;
}
$stmt->close();

// Update user
if (!empty($password)) {
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $connection->prepare('UPDATE users SET username = ?, name = ?, password = ?, role = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $username, $name, $hash, $role, $id);
} else {
    $stmt = $connection->prepare('UPDATE users SET username = ?, name = ?, role = ? WHERE id = ?');
    $stmt->bind_param('sssi', $username, $name, $role, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update user.']);
}
$stmt->close();
$connection->close();
?>