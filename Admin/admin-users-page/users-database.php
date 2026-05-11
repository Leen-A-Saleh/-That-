<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';

function users_getStats(): array
{
    $row = db()->query("
        SELECT
            COUNT(*) AS total,
            SUM(is_active = 1) AS active,
            SUM(is_active = 0) AS suspended
        FROM users
        WHERE role = 'CLIENT'
    ")->fetch();

    $sessions = (int) db()->query("
    SELECT COUNT(*) 
    FROM sessions
")->fetchColumn();

    return [
        'total' => (int) $row['total'],
        'active' => (int) $row['active'],
        'suspended' => (int) $row['suspended'],
        'sessions' => $sessions,
    ];
}

function users_getAll(): array
{
    $stmt = db()->query("
        SELECT
            u.user_id AS id,
            u.name,
            u.email,
            COALESCE(u.phone, '') AS phone,
            DATE(u.created_at) AS reg_date,
            u.is_active,
            COUNT(a.appointment_id) AS sessions
        FROM users u
        LEFT JOIN appointments a
               ON a.client_id = u.user_id
              AND a.status = 'COMPLETED'
        WHERE u.role = 'CLIENT'
        GROUP BY u.user_id, u.name, u.email, u.phone, u.created_at, u.is_active
        ORDER BY u.created_at DESC
    ");
    return $stmt->fetchAll();
}
function users_add(string $name, string $email, string $phone, string $status): int
{
    $name  = trim($name);
    $email = trim($email);
    $phone = trim($phone);
    $status = trim($status);

    if ($name === '' || $email === '' || $phone === '' || $status === '') {
        throw new RuntimeException('جميع الحقول مطلوبة');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('البريد الإلكتروني غير صالح');
    }

    if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
        throw new RuntimeException('رقم الهاتف غير صالح');
    }

    $isActive = ($status === 'active') ? 1 : 0;

    $base     = preg_replace('/[^a-z0-9_]/i', '', explode('@', $email)[0]);
    $username = strtolower($base) . '_' . random_int(1000, 9999);
    $password = password_hash('12345678', PASSWORD_BCRYPT);

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $pdo->prepare("
            INSERT INTO users (name, username, email, password, phone, role, is_active, created_at, updated_at)
            VALUES (:name, :username, :email, :password, :phone, 'CLIENT', :is_active, NOW(), NOW())
        ")->execute([
            ':name'      => $name,
            ':username'  => $username,
            ':email'     => $email,
            ':password'  => $password,
            ':phone'     => $phone,
            ':is_active' => $isActive,
        ]);

        $newId = (int) $pdo->lastInsertId();

        $pdo->prepare("
            INSERT INTO clients (client_id, treatment_type, preferred_session_type, preferred_session_time)
            VALUES (:id, 'INDIVIDUAL_THERAPY', 'BOTH', 'FLEXIBLE')
        ")->execute([':id' => $newId]);

        $pdo->commit();
        return $newId;
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * @throws RuntimeException if user not found
 */
function users_update(int $id, string $name, string $email, string $phone, string $status): void
{
    if ($id <= 0 || $name === '' || $email === '') {
        throw new RuntimeException('البيانات المُدخلة غير صالحة');
    }

    $stmt = db()->prepare("
        UPDATE users
        SET name = :name,
            email = :email,
            phone = :phone,
            is_active = :is_active,
            updated_at = NOW()
        WHERE user_id = :id
          AND role    = 'CLIENT'
    ");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone ?: null,
        ':is_active' => ($status === 'active') ? 1 : 0,
        ':id' => $id,
    ]);

    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('المستخدم غير موجود');
    }
}

/**
 * @return string  
 * @throws RuntimeException if user not found
 */
function users_toggleStatus(int $id): string
{
    $pdo = db();

    $row = $pdo->prepare("
        SELECT is_active FROM users WHERE user_id = :id AND role = 'CLIENT'
    ");
    $row->execute([':id' => $id]);
    $current = $row->fetch();

    if (!$current) {
        throw new RuntimeException('المستخدم غير موجود');
    }

    $newVal = ((int) $current['is_active'] === 1) ? 0 : 1;

    $pdo->prepare("
        UPDATE users SET is_active = :val, updated_at = NOW() WHERE user_id = :id
    ")->execute([':val' => $newVal, ':id' => $id]);

    return $newVal === 1 ? 'active' : 'suspended';
}

/**
 *
 * @throws RuntimeException if user not found
 */
function users_delete(int $id): void
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :id AND role = 'CLIENT'");
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            throw new RuntimeException('المستخدم غير موجود');
        }

        $pdo->prepare("DELETE FROM users WHERE user_id = :id")
            ->execute([':id' => $id]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
