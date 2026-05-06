<?php
require_once __DIR__ . '/config/auth.php';
auth_require_role('Admin');

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if (!filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400);
    echo 'Invalid equipment ID.';
    exit;
}

function fetch_api_json($endpoint, $params = []) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . ($basePath === '/' ? '' : $basePath);
    $url = $baseUrl . '/api/' . ltrim($endpoint, '/');

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    if (function_exists('curl_version')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ? json_decode($response, true) : null;
    }

    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "Cookie: " . session_name() . "=" . session_id() . "\r\n",
            'timeout' => 10,
        ],
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    return $response ? json_decode($response, true) : null;
}

$equipmentResponse = fetch_api_json('get_equipment_detail.php', ['id' => $id]);
if (!$equipmentResponse || empty($equipmentResponse['success']) || empty($equipmentResponse['data'])) {
    http_response_code(404);
    echo 'Equipment not found.';
    exit;
}

$equipment = $equipmentResponse['data'];

$qrText = $equipment['qr_code'] ?: 'equipment_id=' . $equipment['id'];
?>
<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Print QR - ' . htmlspecialchars($equipment['name']);
$pageStyles = '<style>body { background: #fff; color: #212529; } .print-container { max-width: 700px; margin: 0 auto; padding: 20px; } .qr-box { width: 220px; height: 220px; margin: 0 auto 20px; } .print-btn { position: fixed; top: 20px; right: 20px; z-index: 1000; } @media print { .print-btn { display: none; } body { margin: 0; } }</style>';
include __DIR__ . '/includes/head.php';
?>
<body>
    <button class="btn btn-primary print-btn" onclick="window.print();">Print QR</button>
    <div class="print-container text-center">
        <div class="mb-4">
            <h2>Equipment QR Code</h2>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($equipment['name']); ?></p>
            <small class="text-muted"><?php echo htmlspecialchars($equipment['category']); ?> — <?php echo htmlspecialchars($equipment['location']); ?></small>
        </div>
        <div id="print-qrcode" class="qr-box"></div>
        <div class="mt-4 text-left">
            <h5>Details</h5>
            <p><strong>ID:</strong> <?php echo htmlspecialchars($equipment['id']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($equipment['name']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($equipment['category']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($equipment['status']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($equipment['location']); ?></p>
            <p><strong>QR Content:</strong> <?php echo htmlspecialchars($qrText); ?></p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        new QRCode(document.getElementById('print-qrcode'), {
            text: '<?php echo htmlspecialchars($qrText, ENT_QUOTES); ?>',
            width: 220,
            height: 220,
            correctLevel: QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>
