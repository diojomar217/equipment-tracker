<?php
function auth_ensure_users_table_exists($connection) {
    $createSql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('Admin','Staff') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $connection->query($createSql);

    $defaultUsers = [
        ['username' => 'admin', 'password' => 'admin123', 'role' => 'Admin'],
        ['username' => 'staff', 'password' => 'staff123', 'role' => 'Staff'],
    ];

    $stmt = $connection->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    if ($stmt === false) {
        return;
    }

    foreach ($defaultUsers as $user) {
        $stmt->bind_param('s', $user['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $insert = $connection->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            if ($insert) {
                $hash = password_hash($user['password'], PASSWORD_DEFAULT);
                $insert->bind_param('sss', $user['username'], $hash, $user['role']);
                $insert->execute();
                $insert->close();
            }
        }
        $stmt->free_result();
    }
    $stmt->close();
}

function auth_verify_user($connection, $username, $password) {
    $stmt = $connection->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
    if ($stmt === false) {
        return null;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
    }

    return null;
}
