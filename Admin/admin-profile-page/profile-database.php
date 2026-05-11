<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';

// Format a DATE/DATETIME string as Arabic date (e.g. "2024-06-15" → "15 يونيو 2024")

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

// Format a TIME string as Arabic AM/PM (e.g. "09:15 ص")
function formatArabicTime(string $time): string
{
    $h = (int) substr($time, 0, 2);
    $m = substr($time, 3, 2);
    $suffix = $h >= 12 ? 'م' : 'ص';
    $h12 = $h % 12 ?: 12;
    return sprintf('%d:%s %s', $h12, $m, $suffix);
}

// Get the two initials from a full name (e.g. "محمد أحمد" → "م أ")
function getNameInitials(string $name): string
{
    $parts = array_filter(explode(' ', $name));
    $parts = array_values($parts);

    $first  = mb_substr($parts[0] ?? '', 0, 1);
    $second = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';

    return trim($first . ' ' . $second);
}

// admin profile info from users table
function getAdminProfile(int $userId): array
{
    $stmt = db()->prepare("
        SELECT
            name,
            email,
            role,
            avatar,
            created_at,
            is_2fa_enabled
        FROM users
        WHERE user_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        return [];
    }

    return [
        'name' => $row['name'],
        'email' => $row['email'],
        'role' => $row['role'],
        'avatar' => $row['avatar'],
        'initials' => getNameInitials($row['name']),
        'is_2fa_enabled' => (int) ($row['is_2fa_enabled'] ?? 0) === 1,
        'join_date' => formatArabicDate((string) $row['created_at']),
    ];
}

// last N login records for this user
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
        $stmt->bindValue(':id',  $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,  \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
    } catch (\PDOException $e) {
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
            'device'   => $device    ?: '—',
            'ip'       => $row['ip_address'],
            'location' => $location  ?: '—',
        ];
    }

    return $result;
}
