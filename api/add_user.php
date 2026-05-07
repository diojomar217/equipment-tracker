<?php
require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin'], true); // API mode
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

if (empty($name) || empty($username) || empty($password) || empty($role)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

if (!in_array($role, ['Admin', 'Staff'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid role.']);
    exit;
}

if (strlen($name) < 3 || strlen($username) < 3 || strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name and username must be at least 3 characters, password at least 6.']);
    exit;
}

// Check if username exists
$stmt = $connection->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('s', $username);
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

// Insert new user
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $connection->prepare('INSERT INTO users (username, name, password, role) VALUES (?, ?, ?, ?)');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
$stmt->bind_param('ssss', $username, $name, $hash, $role);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User added successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to add user.']);
}
$stmt->close();
$connection->close();
?>