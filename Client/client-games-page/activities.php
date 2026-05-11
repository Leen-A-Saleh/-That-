<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';

start_secure_session();
require_role(['CLIENT']);

header('Content-Type: application/json; charset=UTF-8');

$action = trim((string) ($_GET['action'] ?? 'stats'));

if ($action === 'stats') {
    echo json_encode([
        'completed' => 0,
        'pending' => 0,
        'total' => 0,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action !== 'upload' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$files = $_FILES['files'] ?? null;
if (!is_array($files) || empty($files['name']) || !is_array($files['name'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'No files uploaded.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$storageDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'activities';
if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Upload directory is not available.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$saved = [];
foreach ($files['name'] as $i => $name) {
    if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        continue;
    }

    $tmp = (string) ($files['tmp_name'][$i] ?? '');
    $ext = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
    $safeExt = preg_match('/^[a-z0-9]{1,8}$/', $ext) ? $ext : 'bin';
    $filename = 'activity_' . client_current_user_id() . '_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
    $target = $storageDir . DIRECTORY_SEPARATOR . $filename;

    if (is_uploaded_file($tmp) && move_uploaded_file($tmp, $target)) {
        $saved[] = '/That-Copy/storage/activities/' . $filename;
    }
}

if ($saved === []) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'No files were saved.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => true, 'files' => $saved], JSON_UNESCAPED_UNICODE);
