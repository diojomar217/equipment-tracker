<?php
function ensure_notifications_table($connection) {
    $createSql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        equipment_id INT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'general',
        status ENUM('UNREAD','READ') NOT NULL DEFAULT 'UNREAD',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (equipment_id),
        INDEX (status),
        INDEX (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $connection->query($createSql);
}

function ensure_equipment_status_updated_at_column($connection) {
    $checkSql = "SHOW COLUMNS FROM equipment LIKE 'status_updated_at'";
    $result = $connection->query($checkSql);
    if ($result && $result->num_rows === 0) {
        $connection->query("ALTER TABLE equipment ADD COLUMN status_updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER status");
    }
}

function insert_notification($connection, $equipmentId, $message, $type = 'overdue', $status = 'UNREAD') {
    $stmt = $connection->prepare('INSERT INTO notifications (equipment_id, message, type, status, created_at) VALUES (?, ?, ?, ?, NOW())');
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('isss', $equipmentId, $message, $type, $status);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function overdue_notification_exists($connection, $equipmentId, $checkoutSince) {
    $stmt = $connection->prepare('SELECT id FROM notifications WHERE equipment_id = ? AND type = ? AND created_at >= ? LIMIT 1');
    if ($stmt === false) {
        return false;
    }
    $type = 'overdue';
    $stmt->bind_param('iss', $equipmentId, $type, $checkoutSince);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}
