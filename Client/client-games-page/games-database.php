<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/client.php';

if (realpath(__FILE__) === realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    start_secure_session();
    require_auth();
    require_role(['CLIENT']);

    header('Content-Type: application/json; charset=UTF-8');

    $action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

    if ($action !== 'upload' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $clientId = client_current_user_id();
    } catch (Throwable) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = games_store_activity_uploads($clientId, $_FILES['files'] ?? null);

    if (!$result['success']) {
        http_response_code((int) ($result['status'] ?? 422));
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── Upload ───────────────────────────────────────────────────────────────────

/**
 * @param array<string,mixed>|null $files
 * @return array{success:bool,message:string,status?:int,files?:list<array{id:int,file_path:string,file_type:string}>}
 */
function games_store_activity_uploads(int $clientId, ?array $files): array
{
    if ($clientId <= 0) {
        return ['success' => false, 'message' => 'Invalid client.', 'status' => 401];
    }

    if (!is_array($files) || empty($files['name']) || !is_array($files['name'])) {
        return ['success' => false, 'message' => 'No files uploaded.', 'status' => 422];
    }

    $storageDir = games_activity_storage_directory();
    if (!is_dir($storageDir) && !@mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
        return ['success' => false, 'message' => 'Upload directory is not available.', 'status' => 500];
    }

    $saved = [];
    $createdFiles = [];

    try {
        db()->beginTransaction();

        foreach ($files['name'] as $index => $name) {
            $file = games_uploaded_file_at($files, (int) $index);
            $validation = games_validate_activity_file($file);

            if (!$validation['success']) {
                continue;
            }

            $fileType = (string) $validation['file_type'];
            $extension = (string) $validation['extension'];
            $filename = 'activity_' . $clientId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $target = $storageDir . DIRECTORY_SEPARATOR . $filename;
            $dbPath = games_activity_relative_path($filename);

            if (!@move_uploaded_file((string) $file['tmp_name'], $target)) {
                continue;
            }

            $createdFiles[] = $target;

            $stmt = db()->prepare(
                'INSERT INTO activity_uploads (client_id, file_path, file_type)
                 VALUES (:client_id, :file_path, :file_type)'
            );
            $stmt->execute([
                'client_id' => $clientId,
                'file_path' => $dbPath,
                'file_type' => $fileType,
            ]);

            $saved[] = [
                'id' => (int) db()->lastInsertId(),
                'file_path' => $dbPath,
                'file_type' => $fileType,
            ];
        }

        if ($saved === []) {
            db()->rollBack();
            games_delete_created_files($createdFiles);

            return ['success' => false, 'message' => 'No valid image or video files were saved.', 'status' => 422];
        }

        db()->commit();

        return [
            'success' => true,
            'message' => 'Files uploaded successfully.',
            'files' => $saved,
        ];
    } catch (Throwable) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        games_delete_created_files($createdFiles);

        return ['success' => false, 'message' => 'Failed to save uploaded files.', 'status' => 500];
    }
}

function games_activity_storage_directory(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'activities';
}

function games_activity_relative_path(string $filename): string
{
    return '/storage/activities/' . ltrim($filename, '/');
}

/**
 * @param array<string,mixed> $files
 * @return array{name:string,type:string,tmp_name:string,error:int,size:int}
 */
function games_uploaded_file_at(array $files, int $index): array
{
    return [
        'name' => (string) ($files['name'][$index] ?? ''),
        'type' => (string) ($files['type'][$index] ?? ''),
        'tmp_name' => (string) ($files['tmp_name'][$index] ?? ''),
        'error' => (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE),
        'size' => (int) ($files['size'][$index] ?? 0),
    ];
}

/**
 * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file
 * @return array{success:bool,file_type?:string,extension?:string}
 */
function games_validate_activity_file(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false];
    }

    if ($file['tmp_name'] === '' || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false];
    }

    $maxBytes = 50 * 1024 * 1024;
    if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
        return ['success' => false];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mimeType = games_detect_mime_type($file['tmp_name']);

    $imageExtensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $videoExtensions = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
    ];

    if (isset($imageExtensions[$mimeType])) {
        return [
            'success' => true,
            'file_type' => 'IMAGE',
            'extension' => $imageExtensions[$mimeType],
        ];
    }

    if (isset($videoExtensions[$mimeType])) {
        return [
            'success' => true,
            'file_type' => 'VIDEO',
            'extension' => $videoExtensions[$mimeType],
        ];
    }

    $fallbackVideoExtensions = ['mp4', 'webm', 'ogv', 'mov'];
    if (in_array($extension, $fallbackVideoExtensions, true) && str_starts_with(strtolower($file['type']), 'video/')) {
        return [
            'success' => true,
            'file_type' => 'VIDEO',
            'extension' => $extension,
        ];
    }

    return ['success' => false];
}

function games_detect_mime_type(string $path): string
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);

            if (is_string($mimeType) && $mimeType !== '') {
                return strtolower($mimeType);
            }
        }
    }

    return '';
}

/**
 * @param list<string> $paths
 */
function games_delete_created_files(array $paths): void
{
    foreach ($paths as $path) {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// ─── Stats ────────────────────────────────────────────────────────────────────

function client_game_activity_stats(int $clientId): array
{
    $completedStmt = db()->prepare(
        'SELECT COUNT(DISTINCT game_name)
         FROM game_results
         WHERE client_id = :client_id AND is_completed = 1'
    );
    $completedStmt->execute(['client_id' => $clientId]);

    $pendingStmt = db()->prepare(
        'SELECT COUNT(*) FROM game_results WHERE client_id = :client_id AND is_completed = 0'
    );
    $pendingStmt->execute(['client_id' => $clientId]);

    $totalStmt = db()->prepare(
        'SELECT COUNT(*)
         FROM case_activities ca
         INNER JOIN cases c ON c.case_id = ca.case_id
         WHERE c.client_id = :client_id'
    );
    $totalStmt->execute([
        'client_id' => $clientId,
    ]);

    return [
        'completed' => (int) $completedStmt->fetchColumn(),
        'pending'   => (int) $pendingStmt->fetchColumn(),
        'total'     => (int) $totalStmt->fetchColumn(),
    ];
}
