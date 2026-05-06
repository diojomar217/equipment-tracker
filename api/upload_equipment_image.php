<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/auth.php';
auth_require_role(['Admin', 'Staff'], true);
require_once __DIR__ . '/../config/pdo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed.']);
    exit;
}

$equipmentId = isset($_POST['equipment_id']) ? trim($_POST['equipment_id']) : '';
if (!filter_var($equipmentId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid equipment ID.']);
    exit;
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please upload a valid image file.']);
    exit;
}

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

$filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$destination = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
    exit;
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $columnCheck = $pdo->query("SHOW COLUMNS FROM equipment LIKE 'image'");
    if ($columnCheck->fetch(PDO::FETCH_ASSOC) === false) {
        $pdo->exec("ALTER TABLE equipment ADD COLUMN image VARCHAR(255) NULL AFTER qr_code");
    }

    $existingStmt = $pdo->prepare('SELECT image FROM equipment WHERE id = :id LIMIT 1');
    $existingStmt->execute([':id' => $equipmentId]);
    $existingRow = $existingStmt->fetch(PDO::FETCH_ASSOC);
    if ($existingRow && !empty($existingRow['image'])) {
        $oldPath = __DIR__ . '/../' . $existingRow['image'];
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    $relativePath = 'uploads/equipment/' . $filename;
    $updateStmt = $pdo->prepare('UPDATE equipment SET image = :image WHERE id = :id');
    $updateStmt->execute([
        ':image' => $relativePath,
        ':id' => $equipmentId,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully.',
        'image_path' => $relativePath,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
