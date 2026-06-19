<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/helpers.php';

// ─── Stats ────────────────────────────────────────────────────────────────────

function notifications_getStats(): array
{
    $total = (int) db()->query("
        SELECT COUNT(*) FROM (
            SELECT 1 FROM notifications
            GROUP BY title, body, type, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')
        ) AS grouped
    ")->fetchColumn();

    $recipients = (int) db()->query("
        SELECT COUNT(*) FROM notifications
    ")->fetchColumn();

    return [
        'total' => $total,
        'sent' => $total,
        'scheduled' => 0,
        'recipients' => $recipients,
    ];
}

// ─── Audience ─────────────────────────────────────────────────────────────────

function notifications_getAudienceCounts(): array
{
    $all = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
    $clients = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'CLIENT'    AND is_active = 1")->fetchColumn();
    $therapists = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'THERAPIST' AND is_active = 1")->fetchColumn();

    return ['all' => $all, 'clients' => $clients, 'therapists' => $therapists];
}

// ─── Fetch ────────────────────────────────────────────────────────────────────

function notifications_getAll(): array
{
    $rows = db()->query("
        SELECT
            MIN(notification_id) AS id,
            title,
            body,
            type,
            COUNT(*) AS recipient_count,
            DATE_FORMAT(MIN(created_at), '%Y-%m-%d %H:%i') AS created_at
        FROM notifications
        GROUP BY title, body, type, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')
        ORDER BY MIN(created_at) DESC
    ")->fetchAll();

    $typeToDisplay = [
        'GENERAL'              => ['type' => 'announcement', 'badge' => 'إعلان',   'audience' => 'الجميع',       'audIcon' => 'fa-solid fa-users',    'icon' => '../images/Container (1).png'],
        'APPOINTMENT_REMINDER' => ['type' => 'reminder',     'badge' => 'تذكير',   'audience' => 'المرضى',       'audIcon' => 'fa-solid fa-user',     'icon' => '../images/Container.png'],
        'SESSION_CONFIRMATION' => ['type' => 'message',      'badge' => 'رسالة',   'audience' => 'المرضى',       'audIcon' => 'fa-solid fa-user',     'icon' => '../images/Container (2).png'],
        'ALERT'                => ['type' => 'alert',        'badge' => 'تنبيه',   'audience' => 'الأخصائيين',   'audIcon' => 'fa-solid fa-user-tie', 'icon' => '../images/Container (4).png'],
        'MESSAGE'              => ['type' => 'message',      'badge' => 'رسالة',   'audience' => 'الجميع',       'audIcon' => 'fa-solid fa-users',    'icon' => '../images/Container (2).png'],
        'AD'                   => ['type' => 'announcement', 'badge' => 'إعلان',   'audience' => 'الجميع',       'audIcon' => 'fa-solid fa-users',    'icon' => '../images/Container (1).png'],
        'ACTIVITY_ASSIGNED'    => ['type' => 'announcement', 'badge' => 'إعلان',   'audience' => 'المرضى',       'audIcon' => 'fa-solid fa-user',     'icon' => '../images/Container (1).png'],
        'ASSESSMENT_READY'     => ['type' => 'reminder',     'badge' => 'تذكير',   'audience' => 'المرضى',       'audIcon' => 'fa-solid fa-user',     'icon' => '../images/Container.png'],
    ];

    $default = ['type' => 'system', 'badge' => 'نظامي', 'audience' => 'الجميع', 'audIcon' => 'fa-solid fa-users', 'icon' => '../images/Container (3).png'];

    return array_map(function (array $r) use ($typeToDisplay, $default): array {
        $map = $typeToDisplay[$r['type']] ?? $default;
        return [
            'id'          => (int) $r['id'],
            'type'        => $map['type'],
            'icon'        => $map['icon'],
            'title'       => $r['title'],
            'desc'        => $r['body'],
            'badge'       => $map['badge'],
            'audience'    => $map['audience'],
            'audienceIcon'=> $map['audIcon'],
            'count'       => (int) $r['recipient_count'],
            'date'        => $r['created_at'],
            'status'      => 'sent',
        ];
    }, $rows);
}

// ─── Send ─────────────────────────────────────────────────────────────────────

function notifications_send(string $title, string $body, string $audience): int
{
    if ($title === '' || $body === '') {
        throw new RuntimeException('العنوان والنص مطلوبان');
    }

    $typeMap = [
        'all' => 'GENERAL',
        'patients' => 'APPOINTMENT_REMINDER',
        'specialists' => 'ALERT',
    ];

    $dbType = $typeMap[$audience] ?? 'GENERAL';

    if ($audience === 'patients') {
        $sql = "SELECT user_id FROM users WHERE role = 'CLIENT'    AND is_active = 1";
    } elseif ($audience === 'specialists') {
        $sql = "SELECT user_id FROM users WHERE role = 'THERAPIST' AND is_active = 1";
    } else {
        $sql = "SELECT user_id FROM users WHERE is_active = 1";
    }

    $users = db()->query($sql)->fetchAll();

    if (empty($users)) {
        throw new RuntimeException('لا يوجد مستخدمون في هذه الفئة');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, body, type, priority, is_read, created_at)
            VALUES (:user_id, :title, :body, :type, 'NORMAL', 0, NOW())
        ");

        foreach ($users as $user) {
            $stmt->execute([
                ':user_id' => $user['user_id'],
                ':title'   => $title,
                ':body'    => $body,
                ':type'    => $dbType,
            ]);
        }

        $pdo->commit();
        return count($users);

    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}