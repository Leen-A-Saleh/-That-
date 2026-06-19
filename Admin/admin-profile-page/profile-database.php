<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/avatar-storage.php';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatArabicDate(string $dateStr): string
{
    if (trim($dateStr) === '') {
        return '-';
    }

    $months = [
        1  => 'يناير',
        2  => 'فبراير',
        3  => 'مارس',
        4  => 'أبريل',
        5  => 'مايو',
        6  => 'يونيو',
        7  => 'يوليو',
        8  => 'أغسطس',
        9  => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    $date = new \DateTime($dateStr);

    return $date->format('j') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
}

function formatArabicTime(string $time): string
{
    $h = (int) substr($time, 0, 2);
    $m = substr($time, 3, 2);
    $suffix = $h >= 12 ? 'م' : 'ص';
    $h12 = $h % 12 ?: 12;

    return sprintf('%d:%s %s', $h12, $m, $suffix);
}

function getNameInitials(string $name): string
{
    $parts = array_filter(explode(' ', $name));
    $parts = array_values($parts);

    $first  = mb_substr($parts[0] ?? '', 0, 1);
    $second = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';

    return trim($first . ' ' . $second);
}

function admin_current_user_id(): int
{
    return (int) ($_SESSION['auth']['user_id'] ?? 0);
}

function admin_users_has_column(string $column): bool
{
    static $columns = null;

    if ($columns === null) {
        try {
            $stmt = db()->query('SHOW COLUMNS FROM users');
            $columns = array_map(
                static fn(array $row): string => (string) ($row['Field'] ?? ''),
                $stmt->fetchAll()
            );
        } catch (Throwable $e) {
            $columns = [];
        }
    }

    return in_array($column, $columns, true);
}

function admin_profile_gender_folder(string $gender): string
{
    return strtoupper($gender) === 'FEMALE' ? 'female' : 'male';
}

function admin_profile_gender_label(string $genderFolder): string
{
    return $genderFolder === 'female' ? 'أنثى' : 'ذكر';
}

function admin_profile_ready_avatars_directory(string $genderFolder): string
{
    return dirname(__DIR__, 2)
        . DIRECTORY_SEPARATOR . 'storage'
        . DIRECTORY_SEPARATOR . 'ready_avatars'
        . DIRECTORY_SEPARATOR . $genderFolder;
}

// ─── Queries ──────────────────────────────────────────────────────────────────

function admin_profile_get_gender(int $userId): string
{
    if ($userId <= 0) {
        return 'MALE';
    }

    if (!admin_users_has_column('gender')) {
        return 'MALE';
    }

    $stmt = db()->prepare('SELECT gender FROM users WHERE user_id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();
    $gender = strtoupper(trim((string) ($row['gender'] ?? '')));

    return $gender === 'FEMALE' ? 'FEMALE' : 'MALE';
}

function getAdminProfile(int $userId): array
{
    $genderSelect = admin_users_has_column('gender') ? 'gender' : "'MALE' AS gender";
    $twoFaSelect = admin_users_has_column('is_2fa_enabled') ? 'is_2fa_enabled' : '0 AS is_2fa_enabled';

    $stmt = db()->prepare("
        SELECT
            name,
            email,
            role,
            avatar,
            created_at,
            {$twoFaSelect},
            {$genderSelect}
        FROM users
        WHERE user_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        return [];
    }

    $gender = strtoupper(trim((string) ($row['gender'] ?? 'MALE')));

    return [
        'name' => $row['name'],
        'email' => $row['email'],
        'role' => $row['role'],
        'avatar' => $row['avatar'],
        'initials' => getNameInitials((string) $row['name']),
        'is_2fa_enabled' => (int) ($row['is_2fa_enabled'] ?? 0) === 1,
        'join_date' => formatArabicDate((string) $row['created_at']),
        'gender' => $gender === 'FEMALE' ? 'FEMALE' : 'MALE',
    ];
}

function getUserJoinDate(int $userId, string $email): string
{
    $email = trim($email);

    if ($userId <= 0 && $email === '') {
        return '-';
    }

    $stmt = db()->prepare("
        SELECT created_at
        FROM users
        WHERE user_id = :id
           OR email = :email
        LIMIT 1
    ");
    $stmt->execute([
        ':id' => $userId,
        ':email' => $email,
    ]);

    $row = $stmt->fetch();
    if (!$row) {
        return '-';
    }

    return formatArabicDate((string) $row['created_at']);
}

function getLoginActivities(int $userId, int $limit = 5): array
{
    try {
        $stmt = db()->prepare("
            SELECT
                login_date,
                login_time,
                browser,
                os,
                ip_address,
                city,
                country
            FROM login_activities
            WHERE user_id = :id
            ORDER BY created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }

    $result = [];
    foreach ($rows as $row) {
        $device = trim(($row['browser'] ?? '') . ' — ' . ($row['os'] ?? ''), ' —');

        $parts    = array_filter([$row['city'] ?? '', $row['country'] ?? '']);
        $location = implode('، ', $parts);

        $result[] = [
            'date'     => formatArabicDate($row['login_date']),
            'time'     => formatArabicTime($row['login_time']),
            'device'   => $device ?: '—',
            'ip'       => $row['ip_address'],
            'location' => $location ?: '—',
        ];
    }

    return $result;
}

// ─── Mutations ────────────────────────────────────────────────────────────────

/**
 * @return array{success:bool,message:string,redirect?:string,avatar_url?:string,initials?:string}
 */
function admin_profile_handle_post(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isJson = stripos($contentType, 'application/json') !== false;
    $input = $isJson
        ? (json_decode((string) file_get_contents('php://input'), true) ?? [])
        : $_POST;

    $action = trim((string) ($input['action'] ?? $_POST['action'] ?? ''));
    $csrfToken = trim((string) ($input['csrf_token'] ?? $_POST['csrf_token'] ?? ''));

    if (!verify_csrf($csrfToken)) {
        return [
            'success' => false,
            'message' => 'انتهت صلاحية الطلب. يرجى تحديث الصفحة والمحاولة مجدداً.',
        ];
    }

    switch ($action) {
        case 'update_profile':
            $response = admin_update_profile_info(
                (string) ($input['name'] ?? ''),
                (string) ($input['email'] ?? '')
            );
            if ($response['success']) {
                $_SESSION['auth']['name'] = trim((string) ($input['name'] ?? ''));
                $_SESSION['auth']['email'] = trim((string) ($input['email'] ?? ''));
                $_SESSION['user_name'] = $_SESSION['auth']['name'];
                $_SESSION['user_email'] = $_SESSION['auth']['email'];
            }

            return $response;

        case 'upload_avatar':
            $file = $_FILES['avatar'] ?? null;
            if (!is_array($file)) {
                return ['success' => false, 'message' => 'لم يتم إرسال ملف.'];
            }

            $userId = admin_current_user_id();
            $result = avatar_save_for_user($userId, $file);
            if (!empty($result['success']) && isset($result['stored_path']) && is_string($result['stored_path'])) {
                $_SESSION['auth']['avatar'] = $result['stored_path'];
            }

            return $result;

        case 'select_ready_avatar':
            return admin_profile_select_ready_avatar((string) ($input['avatar_key'] ?? ''));

        case 'logout_all_devices':
            $userId = admin_current_user_id();
            if ($userId <= 0 || !logout_user_from_all_devices($userId)) {
                return [
                    'success' => false,
                    'message' => 'تعذر تسجيل الخروج من جميع الأجهزة.',
                ];
            }

            db()->prepare('DELETE FROM login_activities WHERE user_id = :id')
               ->execute([':id' => $userId]);

            logout_user();

            return [
                'success' => true,
                'message' => 'تم تسجيل الخروج من جميع الأجهزة بنجاح.',
                'redirect' => '/That-Copy/Public/login/index.php',
            ];

        default:
            return ['success' => false, 'message' => 'إجراء غير معروف.'];
    }
}

/**
 * @return array{success:bool,avatars:list<array{key:string,url:string,label:string}>,gender:string,gender_label:string}
 */
function admin_profile_list_ready_avatars(): array
{
    $userId = admin_current_user_id();
    $genderFolder = admin_profile_gender_folder(admin_profile_get_gender($userId));
    $genderLabel = admin_profile_gender_label($genderFolder);
    $dir = admin_profile_ready_avatars_directory($genderFolder);

    if (!is_dir($dir)) {
        return [
            'success' => true,
            'avatars' => [],
            'gender' => $genderFolder,
            'gender_label' => $genderLabel,
        ];
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $avatars = [];

    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $full = $dir . DIRECTORY_SEPARATOR . $entry;
        if (!is_file($full)) {
            continue;
        }

        $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            continue;
        }

        $dbPath = 'storage/ready_avatars/' . $genderFolder . '/' . $entry;
        $avatars[] = [
            'key' => $genderFolder . '/' . $entry,
            'url' => avatar_public_url($dbPath),
            'label' => pathinfo($entry, PATHINFO_FILENAME),
        ];
    }

    usort($avatars, static fn(array $a, array $b): int => strcmp($a['label'], $b['label']));

    return [
        'success' => true,
        'avatars' => $avatars,
        'gender' => $genderFolder,
        'gender_label' => $genderLabel,
    ];
}

/**
 * @return array{success:bool,message:string,avatar_url?:string,stored_path?:string}
 */
function admin_profile_select_ready_avatar(string $avatarKey): array
{
    $avatarKey = trim(str_replace('\\', '/', $avatarKey));
    if ($avatarKey === '' || str_contains($avatarKey, '..')) {
        return ['success' => false, 'message' => 'صورة غير صالحة.'];
    }

    $parts = explode('/', $avatarKey, 2);
    if (count($parts) !== 2) {
        return ['success' => false, 'message' => 'صورة غير صالحة.'];
    }

    [$folder, $filename] = $parts;
    $userId = admin_current_user_id();
    $expectedFolder = admin_profile_gender_folder(admin_profile_get_gender($userId));

    if ($folder !== $expectedFolder) {
        return ['success' => false, 'message' => 'هذه الصورة غير متاحة لحسابك.'];
    }

    if (!preg_match('/^[A-Za-z0-9._\-\x{0621}-\x{064A}]+$/u', $filename)) {
        return ['success' => false, 'message' => 'صورة غير صالحة.'];
    }

    $fullPath = admin_profile_ready_avatars_directory($folder) . DIRECTORY_SEPARATOR . $filename;
    if (!is_file($fullPath)) {
        return ['success' => false, 'message' => 'الصورة المختارة غير موجودة.'];
    }

    $dbPath = 'storage/ready_avatars/' . $folder . '/' . $filename;

    return admin_profile_apply_avatar_path($userId, $dbPath);
}

/**
 * @return array{success:bool,message:string,avatar_url?:string,stored_path?:string}
 */
function admin_profile_apply_avatar_path(int $userId, string $dbPath): array
{
    if ($userId <= 0) {
        return ['success' => false, 'message' => 'معرّف المستخدم غير صالح.'];
    }

    $dbPath = trim(str_replace('\\', '/', $dbPath));
    if ($dbPath === '') {
        return ['success' => false, 'message' => 'مسار الصورة غير صالح.'];
    }

    $stmtOld = db()->prepare('SELECT avatar FROM users WHERE user_id = ? AND role = ? LIMIT 1');
    $stmtOld->execute([$userId, 'ADMIN']);
    $oldRow = $stmtOld->fetch();
    $oldAvatar = $oldRow ? ($oldRow['avatar'] ?? null) : null;

    $stmt = db()->prepare('UPDATE users SET avatar = ? WHERE user_id = ? AND role = ? LIMIT 1');
    $stmt->execute([$dbPath, $userId, 'ADMIN']);

    if ($stmt->rowCount() < 1) {
        return ['success' => false, 'message' => 'تعذر تحديث الصورة.'];
    }

    if ($oldAvatar !== null && (string) $oldAvatar !== '' && (string) $oldAvatar !== $dbPath) {
        avatar_delete_managed_file((string) $oldAvatar);
    }

    $_SESSION['auth']['avatar'] = $dbPath;

    return [
        'success' => true,
        'message' => 'تم تحديث الصورة بنجاح.',
        'avatar_url' => avatar_public_url($dbPath),
        'stored_path' => $dbPath,
    ];
}

/**
 * @return array{success:bool,message:string,initials?:string}
 */
function admin_update_profile_info(string $name, string $email): array
{
    $name = trim($name);
    $email = trim($email);

    if ($name === '') {
        return ['success' => false, 'message' => 'الاسم مطلوب.'];
    }

    if ($email === '') {
        return ['success' => false, 'message' => 'البريد الإلكتروني مطلوب.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'صيغة البريد الإلكتروني غير صحيحة.'];
    }

    if (mb_strlen($name) > 100) {
        return ['success' => false, 'message' => 'الاسم طويل جداً.'];
    }

    $userId = admin_current_user_id();
    $connection = db();

    try {
        $updateUser = $connection->prepare(
            'UPDATE users
             SET name = :name, email = :email
             WHERE user_id = :user_id AND role = :role
             LIMIT 1'
        );
        $updateUser->execute([
            'name' => $name,
            'email' => $email,
            'user_id' => $userId,
            'role' => 'ADMIN',
        ]);

        if ($updateUser->rowCount() < 1) {
            $checkUser = $connection->prepare(
                'SELECT user_id FROM users WHERE user_id = :user_id AND role = :role LIMIT 1'
            );
            $checkUser->execute(['user_id' => $userId, 'role' => 'ADMIN']);
            if (!$checkUser->fetch()) {
                return ['success' => false, 'message' => 'تعذر العثور على بيانات المستخدم.'];
            }
        }

        return [
            'success' => true,
            'message' => 'تم تحديث المعلومات بنجاح.',
            'initials' => getNameInitials($name),
        ];
    } catch (PDOException $exception) {
        if ((int) $exception->getCode() === 23000) {
            return ['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل.'];
        }

        throw $exception;
    }
}