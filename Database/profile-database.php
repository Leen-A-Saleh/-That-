<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/client.php';

const CLIENT_PASSWORD_MIN_LENGTH = 8;

/**
 * @return array{name:string,email:string,phone:string,birthdate:string,avatar:?string}
 */
function client_get_profile_personal_info(): array
{
    $clientId = client_current_user_id();

    $clientForeignKeyColumn = client_clients_foreign_key_column();
    $hasDateOfBirthColumn = client_clients_has_column('date_of_birth');

    $selectBirthdate = $hasDateOfBirthColumn ? 'c.date_of_birth AS birthdate' : 'NULL AS birthdate';
    $stmt = db()->prepare(
        'SELECT u.name, u.email, u.phone, u.avatar, ' . $selectBirthdate . '
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

    return [
        'name' => trim((string) ($user['name'] ?? '')),
        'email' => trim((string) ($user['email'] ?? '')),
        'phone' => trim((string) ($user['phone'] ?? '')),
        'birthdate' => client_format_birthdate_value($user['birthdate'] ?? null),
        'avatar' => $avatar,
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
        return [
            'success' => false,
            'message' => 'الاسم مطلوب.',
        ];
    }

    if ($email === '') {
        return [
            'success' => false,
            'message' => 'البريد الإلكتروني مطلوب.',
        ];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ];
    }

    if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        return [
            'success' => false,
            'message' => 'صيغة رقم الهاتف غير صحيحة.',
        ];
    }

    if ($birthdate !== '' && !client_is_valid_birthdate($birthdate)) {
        return [
            'success' => false,
            'message' => 'تاريخ الميلاد غير صالح.',
        ];
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
                'SELECT user_id
                 FROM users
                 WHERE user_id = :user_id AND role = :role
                 LIMIT 1'
            );
            $checkUser->execute([
                'user_id' => $clientId,
                'role' => 'CLIENT',
            ]);
            if (!$checkUser->fetch()) {
                $connection->rollBack();
                return [
                    'success' => false,
                    'message' => 'تعذر العثور على بيانات المستخدم.',
                ];
            }
        }

        client_update_birthdate_for_current_user($connection, $clientId, $birthdate);

        $connection->commit();
        return [
            'success' => true,
            'message' => 'تم تحديث المعلومات الشخصية بنجاح.',
        ];
    } catch (PDOException $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        if ((int) $exception->getCode() === 23000) {
            return [
                'success' => false,
                'message' => 'البريد الإلكتروني مستخدم بالفعل.',
            ];
        }

        throw $exception;
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}

/**
 * @return array{success:bool,message:string}
 */
function client_change_password(string $currentPassword, string $newPassword): array
{
    $currentPassword = trim($currentPassword);
    $newPassword = trim($newPassword);

    if ($currentPassword === '' || $newPassword === '') {
        return [
            'success' => false,
            'message' => 'يرجى تعبئة جميع حقول كلمة المرور.',
        ];
    }

    if (mb_strlen($newPassword) < CLIENT_PASSWORD_MIN_LENGTH) {
        return [
            'success' => false,
            'message' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.',
        ];
    }

    $clientId = client_current_user_id();

    $stmt = db()->prepare(
        'SELECT password
         FROM users
         WHERE user_id = :user_id AND role = :role
         LIMIT 1'
    );
    $stmt->execute([
        'user_id' => $clientId,
        'role' => 'CLIENT',
    ]);

    $user = $stmt->fetch();
    if (!$user) {
        return [
            'success' => false,
            'message' => 'تعذر العثور على بيانات المستخدم.',
        ];
    }

    $storedHash = (string) ($user['password'] ?? '');
    if (!password_verify($currentPassword, $storedHash)) {
        return [
            'success' => false,
            'message' => 'كلمة المرور الحالية غير صحيحة.',
        ];
    }

    if (password_verify($newPassword, $storedHash)) {
        return [
            'success' => false,
            'message' => 'كلمة المرور الجديدة يجب أن تختلف عن الحالية.',
        ];
    }

    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = db()->prepare(
        'UPDATE users
         SET password = :password
         WHERE user_id = :user_id AND role = :role
         LIMIT 1'
    );
    $updateStmt->execute([
        'password' => $newHashedPassword,
        'user_id' => $clientId,
        'role' => 'CLIENT',
    ]);

    return [
        'success' => true,
        'message' => 'تم تغيير كلمة المرور بنجاح.',
    ];
}

function client_delete_account_hard(): bool
{
    $clientId = client_current_user_id();
    $connection = db();

    try {
        $connection->beginTransaction();

        $stmt = $connection->prepare(
            'DELETE FROM users
             WHERE user_id = :user_id AND role = :role
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $clientId,
            'role' => 'CLIENT',
        ]);

        if ($stmt->rowCount() < 1) {
            $connection->rollBack();
            return false;
        }

        $connection->commit();
        logout_user();
        return true;
    } catch (Throwable $e) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $e;
    }
}

// ============================================================
// Profile helper functions
// ============================================================

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
    if ($birthdate === '' || $birthdate === '0000-00-00') {
        return '';
    }

    return $birthdate;
}

function client_is_valid_birthdate(string $birthdate): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $birthdate);
    if (!$date || $date->format('Y-m-d') !== $birthdate) {
        return false;
    }

    $today = new DateTimeImmutable('today');
    return $date <= $today;
}

function client_update_birthdate_for_current_user(PDO $connection, int $clientId, string $birthdate): void
{
    if (!client_clients_has_column('date_of_birth')) {
        return;
    }

    $foreignKeyColumn = client_clients_foreign_key_column();
    $updateBirthdate = $connection->prepare(
        'UPDATE clients
         SET date_of_birth = :date_of_birth
         WHERE ' . $foreignKeyColumn . ' = :client_id
         LIMIT 1'
    );
    $updateBirthdate->execute([
        'date_of_birth' => ($birthdate === '' ? null : $birthdate),
        'client_id' => $clientId,
    ]);
}

// ============================================================
// Session stats & notifications
// ============================================================

/**
 * Returns session statistics for the current client.
 *
 * @return array{total:int,scheduled:int,completed:int}
 */
function client_get_session_stats(): array
{
    $clientId = client_current_user_id();

    $stmt = db()->prepare(
        'SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN s.status = \'SCHEDULED\' THEN 1 ELSE 0 END) AS scheduled,
            SUM(CASE WHEN s.status = \'COMPLETED\' THEN 1 ELSE 0 END) AS completed
         FROM sessions s
         INNER JOIN appointments a ON a.appointment_id = s.appointment_id
         WHERE a.client_id = :client_id'
    );
    $stmt->execute([
        'client_id' => $clientId,
    ]);

    $row = $stmt->fetch();

    return [
        'total' => (int) ($row['total'] ?? 0),
        'scheduled' => (int) ($row['scheduled'] ?? 0),
        'completed' => (int) ($row['completed'] ?? 0),
    ];
}

/**
 * Checks if the current client has any unread notifications.
 */
function client_has_unread_notifications_for_current_user(): bool
{
    $clientId = client_current_user_id();

    $stmt = db()->prepare(
        'SELECT 1
         FROM notifications
         WHERE user_id = :user_id AND is_read = 0
         LIMIT 1'
    );
    $stmt->execute([
        'user_id' => $clientId,
    ]);

    return (bool) $stmt->fetchColumn();
}

/**
 * Returns the earliest appointment date and therapist name for the current client.
 *
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
    $stmt->execute([
        'client_id' => $clientId,
    ]);

    $row = $stmt->fetch();
    if (!$row) {
        return ['date' => null, 'therapist_name' => null];
    }

    return [
        'date' => (string) ($row['date_time'] ?? ''),
        'therapist_name' => trim((string) ($row['therapist_name'] ?? '')),
    ];
}

