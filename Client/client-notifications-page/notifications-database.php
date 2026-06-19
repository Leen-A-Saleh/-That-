<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/profile-database.php';

// ─── Fetch

function client_get_notifications_for_current_user(): array
{
    $userId = client_current_user_id();
    $notificationFilterSql = client_notification_preferences_sql_filter();

    $stmt = db()->prepare(
        'SELECT
            notification_id,
            type,
            title,
            body,
            is_read,
            created_at
         FROM notifications
         WHERE user_id = :user_id' . $notificationFilterSql . '
         ORDER BY created_at DESC, notification_id DESC'
    );
    $stmt->execute(['user_id' => $userId]);

    /** @var list<array<string,mixed>> $rows */
    $rows = $stmt->fetchAll();
    $items = [];

    foreach ($rows as $row) {
        $createdAt = (string) ($row['created_at'] ?? '');
        $items[] = [
            'id' => (int) ($row['notification_id'] ?? 0),
            'type' => notifications_map_type((string) ($row['type'] ?? 'GENERAL')),
            'title' => trim((string) ($row['title'] ?? '')),
            'message' => trim((string) ($row['body'] ?? '')),
            'time' => notifications_humanize_time($createdAt),
            'read' => ((int) ($row['is_read'] ?? 0)) === 1,
            'created_at' => $createdAt,
        ];
    }

    return $items;
}


function client_get_notification_stats_for_current_user(): array
{
    $userId = client_current_user_id();
    $notificationFilterSql = client_notification_preferences_sql_filter();

    $stmt = db()->prepare(
        'SELECT
            COUNT(*) AS total_notifications,
            SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1) THEN 1 ELSE 0 END) AS this_week_notifications,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread_notifications
         FROM notifications
         WHERE user_id = :user_id' . $notificationFilterSql
    );
    $stmt->execute(['user_id' => $userId]);

    $stats = $stmt->fetch();
    if (!is_array($stats)) {
        return ['total' => 0, 'this_week' => 0, 'unread' => 0];
    }

    return [
        'total' => (int) ($stats['total_notifications'] ?? 0),
        'this_week' => (int) ($stats['this_week_notifications'] ?? 0),
        'unread' => (int) ($stats['unread_notifications'] ?? 0),
    ];
}

// ─── Actions

function client_mark_all_notifications_as_read_for_current_user(): int
{
    $userId = client_current_user_id();
    $notificationFilterSql = client_notification_preferences_sql_filter();

    $stmt = db()->prepare(
        'UPDATE notifications
         SET is_read = 1
         WHERE user_id = :user_id AND is_read = 0' . $notificationFilterSql
    );
    $stmt->execute(['user_id' => $userId]);

    return (int) $stmt->rowCount();
}


function client_delete_all_notifications_for_current_user(): int
{
    $userId = client_current_user_id();
    $notificationFilterSql = client_notification_preferences_sql_filter();

    $stmt = db()->prepare(
        'DELETE FROM notifications
         WHERE user_id = :user_id' . $notificationFilterSql
    );
    $stmt->execute(['user_id' => $userId]);

    return (int) $stmt->rowCount();
}

function client_toggle_notification_read_for_current_user(int $notificationId): ?bool
{
    if ($notificationId <= 0) {
        return null;
    }

    $userId = client_current_user_id();
    $connection = db();
    $notificationFilterSql = client_notification_preferences_sql_filter();

    try {
        $connection->beginTransaction();

        $selectStmt = $connection->prepare(
            'SELECT is_read
             FROM notifications
             WHERE notification_id = :notification_id AND user_id = :user_id' . $notificationFilterSql . '
             LIMIT 1'
        );
        $selectStmt->execute([
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);

        $row = $selectStmt->fetch();
        if (!is_array($row)) {
            $connection->rollBack();
            return null;
        }

        $nextReadValue = ((int) ($row['is_read'] ?? 0)) === 1 ? 0 : 1;

        $updateStmt = $connection->prepare(
            'UPDATE notifications
             SET is_read = :is_read
             WHERE notification_id = :notification_id AND user_id = :user_id
             LIMIT 1'
        );
        $updateStmt->execute([
            'is_read' => $nextReadValue,
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);

        $connection->commit();
        return $nextReadValue === 1;
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}

// ─── Utilities

function notifications_humanize_time(string $createdAt): string
{
    if ($createdAt === '') {
        return 'منذ لحظات';
    }

    try {
        $tz = new DateTimeZone(date_default_timezone_get());
        $createdDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt, $tz);

        if ($createdDate === false) {
            $createdDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', $createdAt, $tz);
        }

        if ($createdDate === false) {
            return 'منذ لحظات';
        }

        $now = new DateTimeImmutable('now', $tz);
    } catch (Throwable $exception) {
        return 'منذ لحظات';
    }

    $diffSeconds = max(0, $now->getTimestamp() - $createdDate->getTimestamp());

    $minutes = (int) floor($diffSeconds / 60);
    $hours = (int) floor($diffSeconds / 3600);
    $days = (int) floor($diffSeconds / 86400);
    $weeks = (int) floor($days / 7);
    $months = (int) floor($days / 30);
    $years = (int) floor($days / 365);

    if ($minutes < 1) {
        return 'منذ لحظات';
    }

    if ($minutes === 1) {
        return 'منذ دقيقة';
    }

    if ($minutes === 2) {
        return 'منذ دقيقتين';
    }

    if ($hours < 1) {
        // 3–59 minutes
        return 'منذ ' . $minutes . ' دقائق';
    }

    if ($hours === 1) {
        return 'منذ ساعة';
    }

    if ($hours === 2) {
        return 'منذ ساعتين';
    }

    if ($hours < 24) {
        return 'منذ ' . $hours . ' ساعات';
    }

    if ($days === 1) {
        return 'منذ يوم';
    }

    if ($days === 2) {
        return 'منذ يومين';
    }

    if ($days < 7) {
        return 'منذ ' . $days . ' أيام';
    }

    if ($weeks === 1) {
        return 'منذ أسبوع';
    }

    if ($weeks === 2) {
        return 'منذ أسبوعين';
    }

    if ($days < 30) {
        return 'منذ ' . $weeks . ' أسابيع';
    }

    if ($months === 1) {
        return 'منذ شهر';
    }

    if ($months === 2) {
        return 'منذ شهرين';
    }

    if ($months < 12) {
        return 'منذ ' . $months . ' أشهر';
    }

    if ($years === 1) {
        return 'منذ سنة';
    }

    if ($years === 2) {
        return 'منذ سنتين';
    }

    return 'منذ ' . $years . ' سنوات';
}

function notifications_map_type(string $databaseType): string
{
    $normalized = strtoupper(trim($databaseType));

    return match (true) {
        in_array($normalized, ['APPOINTMENT_REMINDER', 'SESSION_CONFIRMATION', 'APPOINTMENT'], true) => 'appointment',
        in_array($normalized, ['ACTIVITY_ASSIGNED', 'ACTIVITY_REMINDER'], true) => 'activity',
        $normalized === 'ASSESSMENT_READY' => 'test',
        $normalized === 'ALERT' => 'warning',
        default => 'info',
    };
}
