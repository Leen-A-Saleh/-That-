<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/avatar-storage.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$csrfRaw = $_POST['csrf_token'] ?? null;
$csrf = is_string($csrfRaw) ? $csrfRaw : null;

if (!verify_csrf($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'انتهت صلاحية الطلب. أعد المحاولة.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) ($_SESSION['auth']['user_id'] ?? 0);

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'جلسة غير صالحة.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$file = $_FILES['avatar'] ?? null;

if (!is_array($file)) {
    echo json_encode(['success' => false, 'message' => 'لم يتم إرسال ملف.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = avatar_save_for_user($userId, $file);

if (!empty($result['success']) && isset($result['stored_path']) && is_string($result['stored_path'])) {
    $_SESSION['auth']['avatar'] = $result['stored_path'];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
