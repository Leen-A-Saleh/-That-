<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function avatar_storage_directory(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'avatars';
}

function avatar_relative_storage_path(string $filename): string
{
    return 'storage/avatars/' . ltrim($filename, '/');
}


function avatar_public_url(?string $storedPath): string
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
    $basePath = trim((string) parse_url($base, PHP_URL_PATH), '/');
    $relativePath = ltrim($storedPath, '/');

    if ($basePath !== '' && str_starts_with($relativePath, $basePath . '/')) {
        $relativePath = substr($relativePath, strlen($basePath) + 1);
    }

    return $base . '/' . $relativePath;
}

function avatar_is_managed_storage_path(?string $path): bool
{
    if ($path === null || trim((string) $path) === '') {
        return false;
    }

    $norm = str_replace('\\', '/', strtolower(trim((string) $path)));

    return str_starts_with($norm, 'storage/avatars/');
}

function avatar_delete_managed_file(?string $storedPath): void
{
    if (!avatar_is_managed_storage_path($storedPath)) {
        return;
    }

    $root = dirname(__DIR__);
    $rel = str_replace('/', DIRECTORY_SEPARATOR, trim((string) $storedPath, '/'));
    $full = $root . DIRECTORY_SEPARATOR . $rel;

    if (is_file($full)) {
        @unlink($full);
    }
}

/**
 * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
 * @return array{success:bool,message:string,avatar_url?:string,stored_path?:string}
 */
function avatar_save_for_user(int $userId, array $file): array
{
    if ($userId <= 0) {
        return ['success' => false, 'message' => 'معرّف المستخدم غير صالح.'];
    }

    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'يرجى اختيار صورة.'];
    }

    if ($error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'تعذر رفع الملف. حاول مرة أخرى.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['success' => false, 'message' => 'ملف الرفع غير صالح.'];
    }

    $size = (int) ($file['size'] ?? 0);
    $maxBytes = 2 * 1024 * 1024;

    if ($size <= 0 || $size > $maxBytes) {
        return ['success' => false, 'message' => 'حجم الصورة يجب ألا يتجاوز ٢ ميجابايت.'];
    }

    $info = @getimagesize($tmp);
    if ($info === false) {
        return ['success' => false, 'message' => 'الملف ليس صورة مدعومة.'];
    }

    $type = (int) $info[2];
    $map = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    if (!isset($map[$type])) {
        return ['success' => false, 'message' => 'يُسمح بصيغ JPG أو PNG أو GIF أو WEBP فقط.'];
    }

    if ($info[0] > 4096 || $info[1] > 4096) {
        return ['success' => false, 'message' => 'أبعاد الصورة كبيرة جداً.'];
    }

    $ext = $map[$type];
    $dir = avatar_storage_directory();

    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return ['success' => false, 'message' => 'تعذر إنشاء مجلد التخزين.'];
    }

    $safeName = 'u' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destFs = $dir . DIRECTORY_SEPARATOR . $safeName;
    $dbPath = avatar_relative_storage_path($safeName);

    $stmtOld = db()->prepare('SELECT avatar FROM users WHERE user_id = ? LIMIT 1');
    $stmtOld->execute([$userId]);
    $oldRow = $stmtOld->fetch();
    $oldAvatar = $oldRow ? ($oldRow['avatar'] ?? null) : null;

    if (!@move_uploaded_file($tmp, $destFs)) {
        return ['success' => false, 'message' => 'تعذر حفظ الصورة على الخادم.'];
    }

    $stmt = db()->prepare('UPDATE users SET avatar = ? WHERE user_id = ? LIMIT 1');
    $stmt->execute([$dbPath, $userId]);

    if ($stmt->rowCount() < 1) {
        @unlink($destFs);

        return ['success' => false, 'message' => 'تعذر تحديث حساب المستخدم.'];
    }

    if ($oldAvatar !== null && (string) $oldAvatar !== '' && (string) $oldAvatar !== $dbPath) {
        avatar_delete_managed_file((string) $oldAvatar);
    }

    return [
        'success' => true,
        'message' => 'تم تحديث الصورة بنجاح.',
        'avatar_url' => avatar_public_url($dbPath),
        'stored_path' => $dbPath,
    ];
}
