<?php
function auth_ensure_users_table_exists($connection) {
    $createSql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL DEFAULT '',
        password VARCHAR(255) NOT NULL,
        role ENUM('Admin','Staff') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $connection->query($createSql);

    $columnCheck = $connection->query("SHOW COLUMNS FROM users LIKE 'name'");
    if ($columnCheck && $columnCheck->num_rows === 0) {
        $connection->query("ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT '' AFTER username");
    }

    $defaultUsers = [
        ['username' => 'admin', 'name' => 'Administrator', 'password' => 'admin123', 'role' => 'Admin'],
        ['username' => 'staff', 'name' => 'Staff User', 'password' => 'staff123', 'role' => 'Staff'],
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

function auth_ensure_locations_table_exists($connection) {
    $createSql = "CREATE TABLE IF NOT EXISTS locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $connection->query($createSql);

    $defaultLocations = ['Room 101', 'Storage', 'Office', 'Lab'];

    $stmt = $connection->prepare('SELECT id FROM locations WHERE name = ? LIMIT 1');
    if ($stmt === false) {
        return;
    }

    foreach ($defaultLocations as $loc) {
        $stmt->bind_param('s', $loc);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $insert = $connection->prepare('INSERT INTO locations (name) VALUES (?)');
            if ($insert) {
                $insert->bind_param('s', $loc);
                $insert->execute();
                $insert->close();
            }
        }
        $stmt->free_result();
    }
    $stmt->close();
}

function auth_ensure_categories_table_exists($connection) {
    $createSql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $connection->query($createSql);

    $defaultCategories = ['Computer', 'Tool', 'Furniture', 'Electronics', 'Lab Equipment'];

    $stmt = $connection->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
    if ($stmt === false) {
        return;
    }

    foreach ($defaultCategories as $cat) {
        $stmt->bind_param('s', $cat);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $insert = $connection->prepare('INSERT INTO categories (name) VALUES (?)');
            if ($insert) {
                $insert->bind_param('s', $cat);
                $insert->execute();
                $insert->close();
            }
        }
        $stmt->free_result();
    }
    $stmt->close();
}

function auth_verify_user($connection, $username, $password) {
    $nameColumn = false;
    $columnCheck = $connection->query("SHOW COLUMNS FROM users LIKE 'name'");
    if ($columnCheck && $columnCheck->num_rows > 0) {
        $nameColumn = true;
    }

    if ($nameColumn) {
        $stmt = $connection->prepare('SELECT id, username, name, password, role FROM users WHERE username = ? LIMIT 1');
    } else {
        $stmt = $connection->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
    }
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
            'name' => isset($user['name']) && $user['name'] !== '' ? $user['name'] : $user['username'],
            'role' => $user['role'],
        ];
    }

    return null;
}
