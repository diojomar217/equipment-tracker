<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/auth.php';
auth_require_role('Admin', true);

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed.']);
    exit;
}

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$categoryId = isset($_POST['category']) ? (int)$_POST['category'] : 0;
$locationId = isset($_POST['location']) ? (int)$_POST['location'] : 0;
$returnLocationId = isset($_POST['return_location']) ? (int)$_POST['return_location'] : 0;
$returnLocationId = $returnLocationId > 0 ? $returnLocationId : null;

require_once __DIR__ . '/auth_helper.php';
auth_ensure_locations_table_exists($connection);
auth_ensure_categories_table_exists($connection);

// Fetch allowed locations from DB
$locationResult = $connection->query('SELECT id, name FROM locations ORDER BY name');
$allowedLocationIds = [];
if ($locationResult) {
    while ($row = $locationResult->fetch_assoc()) {
        $allowedLocationIds[] = (int)$row['id'];
    }
    $locationResult->free();
}

// Fetch allowed categories from DB
$categoryResult = $connection->query('SELECT id, name FROM categories ORDER BY name');
$allowedCategoryIds = [];
if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $allowedCategoryIds[] = (int)$row['id'];
    }
    $categoryResult->free();
}

if ($id === '' || !filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid equipment ID.']);
    exit;
}

if ($name === '' || $categoryId <= 0 || $locationId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name, category, and location are required.']);
    exit;
}

if ($returnLocationId !== null && !in_array($returnLocationId, $allowedLocationIds, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid return location selected.']);
    exit;
}

if (!in_array($locationId, $allowedLocationIds, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid location selected.']);
    exit;
}

if (!in_array($categoryId, $allowedCategoryIds, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid category selected.']);
    exit;
}

$columnCheck = $connection->query("SHOW COLUMNS FROM equipment LIKE 'location'");
if ($columnCheck && $columnCheck->num_rows === 0) {
    $connection->query("ALTER TABLE equipment ADD COLUMN location VARCHAR(100) NULL AFTER status");
}
$returnColumnCheck = $connection->query("SHOW COLUMNS FROM equipment LIKE 'return_location'");
if ($returnColumnCheck && $returnColumnCheck->num_rows === 0) {
    $connection->query("ALTER TABLE equipment ADD COLUMN return_location VARCHAR(100) NULL AFTER location");
}

// Check if equipment exists
$checkSql = 'SELECT id FROM equipment WHERE id = ? LIMIT 1';
$checkStmt = $connection->prepare($checkSql);
if ($checkStmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare check statement: ' . $connection->error]);
    exit;
}
$checkStmt->bind_param('i', $id);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows === 0) {
    $checkStmt->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Equipment not found.']);
    exit;
}
$checkStmt->close();

// Prevent duplicate names (excluding current)
$dupCheckSql = 'SELECT id FROM equipment WHERE name = ? AND id != ? LIMIT 1';
$dupCheckStmt = $connection->prepare($dupCheckSql);
if ($dupCheckStmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare duplicate check: ' . $connection->error]);
    exit;
}
$dupCheckStmt->bind_param('si', $name, $id);
$dupCheckStmt->execute();
$dupCheckStmt->store_result();
if ($dupCheckStmt->num_rows > 0) {
    $dupCheckStmt->close();
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'An equipment item with that name already exists.']);
    exit;
}
$dupCheckStmt->close();

$updateSql = 'UPDATE equipment SET name = ?, category = ?, location = ?, return_location = ? WHERE id = ?';
$stmt = $connection->prepare($updateSql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to prepare update statement: ' . $connection->error]);
    exit;
}

$stmt->bind_param('siiii', $name, $categoryId, $locationId, $returnLocationId, $id);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$stmt->close();

// Handle image upload if provided
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image'];
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
    ];

    if ($file['size'] > 2 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File size must be 2MB or less.']);
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!isset($allowedMimeTypes[$mimeType])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Only JPG and PNG images are allowed.']);
        exit;
    }

    $extension = $allowedMimeTypes[$mimeType];
    $uploadDir = __DIR__ . '/../uploads/equipment/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to create upload directory.']);
        exit;
    }

    // Get existing image to delete
    $existingSql = 'SELECT image FROM equipment WHERE id = ? LIMIT 1';
    $existingStmt = $connection->prepare($existingSql);
    $existingStmt->bind_param('i', $id);
    $existingStmt->execute();
    $existingResult = $existingStmt->get_result();
    $existingRow = $existingResult->fetch_assoc();
    $existingStmt->close();

    if ($existingRow && !empty($existingRow['image'])) {
        $oldPath = __DIR__ . '/../' . $existingRow['image'];
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
        exit;
    }

    $imagePath = 'uploads/equipment/' . $filename;

    // Update the image
    $imageUpdateSql = 'UPDATE equipment SET image = ? WHERE id = ?';
    $imageStmt = $connection->prepare($imageUpdateSql);
    if ($imageStmt === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to prepare image update: ' . $connection->error]);
        exit;
    }
    $imageStmt->bind_param('si', $imagePath, $id);
    if (!$imageStmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Image update failed: ' . $imageStmt->error]);
        $imageStmt->close();
        exit;
    }
    $imageStmt->close();
}

$connection->close();

echo json_encode(['success' => true]);
?>