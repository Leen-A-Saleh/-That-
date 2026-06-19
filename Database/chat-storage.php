<?php

declare(strict_types=1);


function chat_messages_storage_directory(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'messages';
}

function chat_messages_relative_path(string $filename): string
{
    return 'storage/messages/' . ltrim($filename, '/');
}

function chat_public_file_url(?string $storedPath): string
{
    if ($storedPath === null) {
        return '';
    }

    $storedPath = trim(str_replace('\\', '/', $storedPath));
    if ($storedPath === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $storedPath)) {
        return $storedPath;
    }

    $config = require __DIR__ . '/config.php';
    $base = rtrim((string) ($config['app_url'] ?? ''), '/');
    $relative = ltrim($storedPath, '/');

    if (str_starts_with($relative, 'uploads/messages/')) {
        $relative = 'Therapist/' . $relative;
    }

    return $base . '/' . $relative;
}

/**
 * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
 * @return array{success:bool,message:string,type?:string,stored_path?:string,label?:string}
 */
function chat_store_message_upload(int $userId, array $file): array
{
    if ($userId <= 0) {
        return ['success' => false, 'message' => 'معرّف المستخدم غير صالح.'];
    }

    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'لم يتم اختيار ملف.'];
    }
    if ($error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'تعذر رفع الملف.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['success' => false, 'message' => 'ملف الرفع غير صالح.'];
    }

    $origName = trim((string) ($file['name'] ?? 'file'));
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 10 * 1024 * 1024) {
        return ['success' => false, 'message' => 'حجم الملف يتجاوز 10 ميجا'];
    }

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $voiceExts = ['mp3', 'wav', 'm4a', 'ogg', 'webm'];
    $allowed = array_merge(
        $imageExts,
        $voiceExts,
        ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'mp4']
    );

    if ($ext === '' || !in_array($ext, $allowed, true)) {
        return ['success' => false, 'message' => 'نوع الملف غير مدعوم'];
    }

    if (in_array($ext, $imageExts, true)) {
        $type = 'IMAGE';
    } elseif (in_array($ext, $voiceExts, true)) {
        $type = 'VOICE';
    } else {
        $type = 'FILE';
    }

    $dir = chat_messages_storage_directory();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return ['success' => false, 'message' => 'تعذر إنشاء مجلد التخزين.'];
    }

    $safeBase = preg_replace('/[^A-Za-z0-9._\-\x{0621}-\x{064A}]+/u', '_', pathinfo($origName, PATHINFO_FILENAME));
    if ($safeBase === '' || $safeBase === null) {
        $safeBase = 'file';
    }

    $filename = 'msg_' . $userId . '_' . bin2hex(random_bytes(8)) . '_' . $safeBase . '.' . $ext;
    $destFs = $dir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $destFs)) {
        return ['success' => false, 'message' => 'تعذّر حفظ الملف'];
    }

    return [
        'success' => true,
        'message' => 'تم رفع الملف',
        'type' => $type,
        'stored_path' => chat_messages_relative_path($filename),
        'label' => $origName,
    ];
}
