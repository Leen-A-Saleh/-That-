<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/avatar-storage.php';

const CLIENT_PROFILE_PASSWORD_MIN_LENGTH = 8;

// ─── Post Dispatch

/**
 * @return array{success:bool,message:string,redirect?:string,avatar_url?:string}
 */
function client_profile_handle_post(): array
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
            $response = client_update_profile_personal_info(
                (string) ($input['name'] ?? ''),
                (string) ($input['email'] ?? ''),
                (string) ($input['phone'] ?? ''),
                (string) ($input['birthdate'] ?? '')
            );
            if ($response['success']) {
                $_SESSION['auth']['name'] = trim((string) ($input['name'] ?? ''));
                $_SESSION['auth']['email'] = trim((string) ($input['email'] ?? ''));
            }

            return $response;

        case 'change_password':
            return client_change_password(
                (string) ($input['current_password'] ?? ''),
                (string) ($input['new_password'] ?? '')
            );

        case 'delete_account':
            if (client_delete_account_hard()) {
                return [
                    'success' => true,
                    'message' => 'تم حذف الحساب بنجاح.',
                    'redirect' => '/That-Copy/Public/login/index.php',
                ];
            }

            return [
                'success' => false,
                'message' => 'تعذر حذف الحساب حالياً. يرجى المحاولة لاحقاً.',
            ];

        case 'upload_avatar':
            $file = $_FILES['avatar'] ?? null;
            if (!is_array($file)) {
                return ['success' => false, 'message' => 'لم يتم إرسال ملف.'];
            }

            $userId = client_current_user_id();
            $result = avatar_save_for_user($userId, $file);
            if (!empty($result['success']) && isset($result['stored_path']) && is_string($result['stored_path'])) {
                $_SESSION['auth']['avatar'] = $result['stored_path'];
            }

            return $result;

        case 'select_ready_avatar':
            return client_profile_select_ready_avatar((string) ($input['avatar_key'] ?? ''));

        case 'save_notifications':
            return client_update_notification_preferences_for_current_user(
                filter_var($input['appointment_notifications'] ?? false, FILTER_VALIDATE_BOOLEAN),
                filter_var($input['message_notifications'] ?? false, FILTER_VALIDATE_BOOLEAN),
                filter_var($input['activity_reminder_notifications'] ?? false, FILTER_VALIDATE_BOOLEAN)
            );

        default:
            return ['success' => false, 'message' => 'إجراء غير معروف.'];
    }
}

// ─── Avatar

/**
 * @return array{success:bool,avatars:list<array{key:string,url:string,label:string}>,gender:string,gender_label:string}
 */
function client_profile_list_ready_avatars(): array
{
    $genderFolder = client_profile_gender_folder(client_profile_get_gender());
    $genderLabel = client_profile_gender_label($genderFolder);
    $dir = client_profile_ready_avatars_directory($genderFolder);

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
function client_profile_select_ready_avatar(string $avatarKey): array
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
    $expectedFolder = client_profile_gender_folder(client_profile_get_gender());

    if ($folder !== $expectedFolder) {
        return ['success' => false, 'message' => 'هذه الصورة غير متاحة لحسابك.'];
    }

    if (!preg_match('/^[A-Za-z0-9._\-\x{0621}-\x{064A}]+$/u', $filename)) {
        return ['success' => false, 'message' => 'صورة غير صالحة.'];
    }

    $fullPath = client_profile_ready_avatars_directory($folder) . DIRECTORY_SEPARATOR . $filename;
    if (!is_file($fullPath)) {
        return ['success' => false, 'message' => 'الصورة المختارة غير موجودة.'];
    }

    $dbPath = 'storage/ready_avatars/' . $folder . '/' . $filename;

    return client_profile_apply_avatar_path(client_current_user_id(), $dbPath);
}

/**
 * @return array{success:bool,message:string,avatar_url?:string,stored_path?:string}
 */
function client_profile_apply_avatar_path(int $userId, string $dbPath): array
{
    if ($userId <= 0) {
        return ['success' => false, 'message' => 'معرّف المستخدم غير صالح.'];
    }

    $dbPath = trim(str_replace('\\', '/', $dbPath));
    if ($dbPath === '') {
        return ['success' => false, 'message' => 'مسار الصورة غير صالح.'];
    }

    $stmtOld = db()->prepare('SELECT avatar FROM users WHERE user_id = ? AND role = ? LIMIT 1');
    $stmtOld->execute([$userId, 'CLIENT']);
    $oldRow = $stmtOld->fetch();
    $oldAvatar = $oldRow ? ($oldRow['avatar'] ?? null) : null;

    $stmt = db()->prepare('UPDATE users SET avatar = ? WHERE user_id = ? AND role = ? LIMIT 1');
    $stmt->execute([$dbPath, $userId, 'CLIENT']);

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

// ─── Gender

function client_profile_get_gender(): string
{
    if (!client_clients_has_column('gender')) {
        return 'MALE';
    }

    $clientId = client_current_user_id();
    $foreignKeyColumn = client_clients_foreign_key_column();
    $stmt = db()->prepare(
        'SELECT gender FROM clients WHERE ' . $foreignKeyColumn . ' = :client_id LIMIT 1'
    );
    $stmt->execute(['client_id' => $clientId]);
    $row = $stmt->fetch();
    $gender = strtoupper(trim((string) ($row['gender'] ?? '')));

    return $gender === 'FEMALE' ? 'FEMALE' : 'MALE';
}

function client_profile_gender_folder(string $gender): string
{
    return strtoupper($gender) === 'FEMALE' ? 'female' : 'male';
}

function client_profile_gender_label(string $genderFolder): string
{
    return $genderFolder === 'female' ? 'أنثى' : 'ذكر';
}

function client_profile_ready_avatars_directory(string $genderFolder): string
{
    return dirname(__DIR__, 2)
        . DIRECTORY_SEPARATOR . 'storage'
        . DIRECTORY_SEPARATOR . 'ready_avatars'
        . DIRECTORY_SEPARATOR . $genderFolder;
}

// ─── Profile Info

/**
 * @return array{name:string,email:string,phone:string,birthdate:string,avatar:?string,gender:string,avatar_url:string}
 */
function client_get_profile_personal_info(): array
{
    $clientId = client_current_user_id();
    $clientForeignKeyColumn = client_clients_foreign_key_column();
    $hasDateOfBirthColumn = client_clients_has_column('date_of_birth');
    $hasGenderColumn = client_clients_has_column('gender');

    $selectBirthdate = $hasDateOfBirthColumn ? 'c.date_of_birth AS birthdate' : 'NULL AS birthdate';
    $selectGender = $hasGenderColumn ? 'c.gender AS gender' : "'MALE' AS gender";

    $stmt = db()->prepare(
        'SELECT u.name, u.email, u.phone, u.avatar, ' . $selectBirthdate . ', ' . $selectGender . '
         FROM users u
         LEFT JOIN clients c ON c.' . $clientForeignKeyColumn . ' = u.user_id
         WHERE u.user_id = :user_id AND u.role = :role
         LIMIT 1'
    );
    $stmt->execute([
        'user_id' => $clientId,
        'role' => 'CLIENT',
    ]);

    $user = $stmt->fetch();
    if (!$user) {
        throw new RuntimeException('Client record not found.');
    }

    $avatar = $user['avatar'] ?? null;
    $avatar = $avatar !== null && trim((string) $avatar) !== '' ? trim((string) $avatar) : null;
    $gender = strtoupper(trim((string) ($user['gender'] ?? 'MALE')));

    return [
        'name' => trim((string) ($user['name'] ?? '')),
        'email' => trim((string) ($user['email'] ?? '')),
        'phone' => trim((string) ($user['phone'] ?? '')),
        'birthdate' => client_format_birthdate_value($user['birthdate'] ?? null),
        'avatar' => $avatar,
        'gender' => $gender === 'FEMALE' ? 'FEMALE' : 'MALE',
        'avatar_url' => $avatar !== null ? avatar_public_url($avatar) : '',
    ];
}

/**
 * @return array{success:bool,message:string}
 */
function client_update_profile_personal_info(string $name, string $email, string $phone, string $birthdate): array
{
    $name = trim($name);
    $email = trim($email);
    $phone = trim($phone);
    $birthdate = trim($birthdate);

    if ($name === '') {
        return ['success' => false, 'message' => 'الاسم مطلوب.'];
    }

    if ($email === '') {
        return ['success' => false, 'message' => 'البريد الإلكتروني مطلوب.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'صيغة البريد الإلكتروني غير صحيحة.'];
    }

    if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        return ['success' => false, 'message' => 'صيغة رقم الهاتف غير صحيحة.'];
    }

    if ($birthdate !== '' && !client_is_valid_birthdate($birthdate)) {
        return ['success' => false, 'message' => 'تاريخ الميلاد غير صالح.'];
    }

    $clientId = client_current_user_id();
    $connection = db();

    try {
        $connection->beginTransaction();

        $updateUser = $connection->prepare(
            'UPDATE users
             SET name = :name, email = :email, phone = :phone
             WHERE user_id = :user_id AND role = :role
             LIMIT 1'
        );
        $updateUser->execute([
            'name' => $name,
            'email' => $email,
            'phone' => ($phone === '' ? null : $phone),
            'user_id' => $clientId,
            'role' => 'CLIENT',
        ]);

        if ($updateUser->rowCount() < 1) {
            $checkUser = $connection->prepare(
                'SELECT user_id FROM users WHERE user_id = :user_id AND role = :role LIMIT 1'
            );
            $checkUser->execute(['user_id' => $clientId, 'role' => 'CLIENT']);
            if (!$checkUser->fetch()) {
                $connection->rollBack();

                return ['success' => false, 'message' => 'تعذر العثور على بيانات المستخدم.'];
            }
        }

        client_update_birthdate_for_current_user($connection, $clientId, $birthdate);
        $connection->commit();

        return ['success' => true, 'message' => 'تم تحديث المعلومات الشخصية بنجاح.'];
    } catch (PDOException $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        if ((int) $exception->getCode() === 23000) {
            return ['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل.'];
        }

        throw $exception;
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        throw $exception;
    }
}

// ─── Password

/**
 * @return array{success:bool,message:string}
 */
function client_change_password(string $currentPassword, string $newPassword): array
{
    $currentPassword = trim($currentPassword);
    $newPassword = trim($newPassword);

    if ($currentPassword === '' || $newPassword === '') {
        return ['success' => false, 'message' => 'يرجى تعبئة جميع حقول كلمة المرور.'];
    }

    if (mb_strlen($newPassword) < CLIENT_PROFILE_PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'message' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.'];
    }

    $clientId = client_current_user_id();
    $stmt = db()->prepare(
        'SELECT password FROM users WHERE user_id = :user_id AND role = :role LIMIT 1'
    );
    $stmt->execute(['user_id' => $clientId, 'role' => 'CLIENT']);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'تعذر العثور على بيانات المستخدم.'];
    }

    $storedHash = (string) ($user['password'] ?? '');
    if (!password_verify($currentPassword, $storedHash)) {
        return ['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة.'];
    }

    if (password_verify($newPassword, $storedHash)) {
        return ['success' => false, 'message' => 'كلمة المرور الجديدة يجب أن تختلف عن الحالية.'];
    }

    $updateStmt = db()->prepare(
        'UPDATE users SET password = :password WHERE user_id = :user_id AND role = :role LIMIT 1'
    );
    $updateStmt->execute([
        'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        'user_id' => $clientId,
        'role' => 'CLIENT',
    ]);

    return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح.'];
}

// ─── Account Deletion

function client_delete_account_hard(): bool
{
    $clientId = client_current_user_id();
    $connection = db();
    $filesToDelete = [];

    try {
        $connection->beginTransaction();

        $profileStmt = $connection->prepare(
            'SELECT u.avatar, c.survey_id
             FROM users u
             LEFT JOIN clients c ON c.client_id = u.user_id
             WHERE u.user_id = :user_id AND u.role = :role
             LIMIT 1'
        );
        $profileStmt->execute([
            'user_id' => $clientId,
            'role' => 'CLIENT',
        ]);
        $profile = $profileStmt->fetch();

        if (!$profile) {
            $connection->rollBack();

            return false;
        }

        $surveyId = isset($profile['survey_id']) ? (int) $profile['survey_id'] : 0;
        $filesToDelete[] = (string) ($profile['avatar'] ?? '');
        $filesToDelete = array_merge($filesToDelete, client_delete_account_collect_file_paths($connection, $clientId));

        $deleteSessionNotes = $connection->prepare(
            'DELETE sn
             FROM session_notes sn
             INNER JOIN sessions s ON s.session_id = sn.session_id
             LEFT JOIN appointments a ON a.appointment_id = s.appointment_id
             LEFT JOIN cases cs ON cs.case_id = s.case_id
             WHERE a.client_id = :client_id_appt OR cs.client_id = :client_id_case'
        );
        $deleteSessionNotes->execute([
            'client_id_appt' => $clientId,
            'client_id_case' => $clientId,
        ]);

        $deleteSessions = $connection->prepare(
            'DELETE s
             FROM sessions s
             LEFT JOIN appointments a ON a.appointment_id = s.appointment_id
             LEFT JOIN cases cs ON cs.case_id = s.case_id
             WHERE a.client_id = :client_id_appt OR cs.client_id = :client_id_case'
        );
        $deleteSessions->execute([
            'client_id_appt' => $clientId,
            'client_id_case' => $clientId,
        ]);

        $deleteMessages = $connection->prepare(
            'DELETE FROM messages
             WHERE sender_id = :sender_id OR receiver_id = :receiver_id'
        );
        $deleteMessages->execute([
            'sender_id' => $clientId,
            'receiver_id' => $clientId,
        ]);

        $stmt = $connection->prepare(
            'DELETE FROM users WHERE user_id = :user_id AND role = :role LIMIT 1'
        );
        $stmt->execute(['user_id' => $clientId, 'role' => 'CLIENT']);

        if ($stmt->rowCount() < 1) {
            $connection->rollBack();

            return false;
        }

        if ($surveyId > 0) {
            $deleteSurvey = $connection->prepare(
                'DELETE FROM client_surveys
                 WHERE id = :survey_id
                   AND NOT EXISTS (
                       SELECT 1
                       FROM clients
                       WHERE survey_id = :referenced_survey_id
                   )
                 LIMIT 1'
            );
            $deleteSurvey->execute([
                'survey_id' => $surveyId,
                'referenced_survey_id' => $surveyId,
            ]);
        }

        $connection->commit();
        client_delete_account_managed_files($filesToDelete);
        logout_user();

        return true;
    } catch (Throwable $e) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        throw $e;
    }
}

/**
 * @return list<string>
 */
function client_delete_account_collect_file_paths(PDO $connection, int $clientId): array
{
    $files = [];

    $queries = [
        [
            'sql' => 'SELECT file_path FROM activity_uploads WHERE client_id = :client_id',
            'params' => ['client_id' => $clientId],
        ],
        [
            'sql' => 'SELECT file_path FROM activity_submissions WHERE client_id = :client_id',
            'params' => ['client_id' => $clientId],
        ],
        [
            'sql' => 'SELECT file_path FROM messages WHERE sender_id = :sender_id OR receiver_id = :receiver_id',
            'params' => ['sender_id' => $clientId, 'receiver_id' => $clientId],
        ],
    ];

    foreach ($queries as $query) {
        $stmt = $connection->prepare($query['sql']);
        $stmt->execute($query['params']);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $path) {
            if (is_string($path) && trim($path) !== '') {
                $files[] = $path;
            }
        }
    }

    return $files;
}

/**
 * @param list<string> $storedPaths
 */
function client_delete_account_managed_files(array $storedPaths): void
{
    foreach (array_values(array_unique($storedPaths)) as $storedPath) {
        $storedPath = trim(str_replace('\\', '/', $storedPath));
        if ($storedPath === '') {
            continue;
        }

        if (avatar_is_managed_storage_path($storedPath)) {
            avatar_delete_managed_file($storedPath);
            continue;
        }

        client_delete_account_storage_file($storedPath, [
            'storage/messages/',
            'storage/activities/',
            '/storage/messages/',
            '/storage/activities/',
            '/That-Copy/storage/messages/',
            '/That-Copy/storage/activities/',
        ]);
    }
}

/**
 * @param list<string> $allowedPrefixes
 */
function client_delete_account_storage_file(string $storedPath, array $allowedPrefixes): void
{
    $normalized = str_replace('\\', '/', $storedPath);
    $pathOnly = (string) (parse_url($normalized, PHP_URL_PATH) ?: $normalized);
    $pathOnly = ltrim($pathOnly, '/');

    if (str_starts_with($pathOnly, 'That-Copy/')) {
        $pathOnly = substr($pathOnly, strlen('That-Copy/'));
    }

    $allowed = false;
    foreach ($allowedPrefixes as $prefix) {
        $prefix = ltrim(str_replace('\\', '/', $prefix), '/');
        if (str_starts_with($pathOnly, $prefix)) {
            $allowed = true;
            break;
        }
    }

    if (!$allowed || str_contains($pathOnly, '..')) {
        return;
    }

    $root = dirname(__DIR__, 2);
    $fullPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $pathOnly);
    $realRoot = realpath($root);
    $realFile = realpath($fullPath);

    if ($realRoot === false || $realFile === false || !str_starts_with($realFile, $realRoot . DIRECTORY_SEPARATOR)) {
        return;
    }

    if (is_file($realFile)) {
        @unlink($realFile);
    }
}

// ─── Schema Helpers

function client_clients_foreign_key_column(): string
{
    return client_clients_has_column('user_id') ? 'user_id' : 'client_id';
}

function client_clients_has_column(string $column): bool
{
    static $columns = null;

    if ($columns === null) {
        $stmt = db()->query('SHOW COLUMNS FROM clients');
        $columns = array_map(
            static fn(array $row): string => (string) ($row['Field'] ?? ''),
            $stmt->fetchAll()
        );
    }

    return in_array($column, $columns, true);
}

function client_format_birthdate_value(mixed $value): string
{
    if ($value === null) {
        return '';
    }

    $birthdate = trim((string) $value);

    return ($birthdate === '' || $birthdate === '0000-00-00') ? '' : $birthdate;
}

function client_is_valid_birthdate(string $birthdate): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $birthdate);

    return $date && $date->format('Y-m-d') === $birthdate && $date <= new DateTimeImmutable('today');
}

function client_update_birthdate_for_current_user(PDO $connection, int $clientId, string $birthdate): void
{
    if (!client_clients_has_column('date_of_birth')) {
        return;
    }

    $foreignKeyColumn = client_clients_foreign_key_column();
    $updateBirthdate = $connection->prepare(
        'UPDATE clients SET date_of_birth = :date_of_birth
         WHERE ' . $foreignKeyColumn . ' = :client_id LIMIT 1'
    );
    $updateBirthdate->execute([
        'date_of_birth' => ($birthdate === '' ? null : $birthdate),
        'client_id' => $clientId,
    ]);
}

// ─── Statistics

/**
 * @return array{total:int,scheduled:int,completed:int}
 */
function client_get_session_stats(): array
{
    $clientId = client_current_user_id();
    $stmt = db()->prepare(
        'SELECT COUNT(*) AS total,
                SUM(CASE WHEN status = \'CONFIRMED\' THEN 1 ELSE 0 END) AS scheduled,
                SUM(CASE WHEN status = \'COMPLETED\' THEN 1 ELSE 0 END) AS completed
         FROM appointments
         WHERE client_id = :client_id'
    );
    $stmt->execute(['client_id' => $clientId]);
    $row = $stmt->fetch();

    return [
        'total' => (int) ($row['total'] ?? 0),
        'scheduled' => (int) ($row['scheduled'] ?? 0),
        'completed' => (int) ($row['completed'] ?? 0),
    ];
}

/**
 * @return array{date:string|null,therapist_name:string|null}
 */
function client_get_first_appointment_info(): array
{
    $clientId = client_current_user_id();
    $stmt = db()->prepare(
        'SELECT a.date_time, u.name AS therapist_name
         FROM appointments a
         INNER JOIN users u ON u.user_id = a.therapist_id
         WHERE a.client_id = :client_id
         ORDER BY a.date_time ASC
         LIMIT 1'
    );
    $stmt->execute(['client_id' => $clientId]);
    $row = $stmt->fetch();

    if (!$row) {
        return ['date' => null, 'therapist_name' => null];
    }

    return [
        'date' => (string) ($row['date_time'] ?? ''),
        'therapist_name' => trim((string) ($row['therapist_name'] ?? '')),
    ];
}
