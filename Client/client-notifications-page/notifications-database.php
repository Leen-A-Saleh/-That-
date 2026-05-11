<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/db.php';

/**
 * Returns the user_id of the currently authenticated client.
 *
 * @throws RuntimeException when no authenticated session exists.
 */
function notifications_current_user_id(): int
{
    start_secure_session();

    $user = current_user(); // reads $_SESSION['auth']

    if (!is_array($user) || !isset($user['user_id'])) {
        throw new RuntimeException('Client is not authenticated.');
    }

    return (int) $user['user_id'];
}

/**
 * Fetches all notifications for the current user, newest first.
 *
 * @return list<array{
 *   id:int,
 *   type:string,
 *   title:string,
 *   message:string,
 *   time:string,
 *   read:bool,
 *   created_at:string
 * }>
 */
function client_get_notifications_for_current_user(): array
{
    $userId = notifications_current_user_id();

    $stmt = db()->prepare(
        'SELECT
            notification_id,
            type,
            title,
            body,
            is_read,
            created_at
         FROM notifications
         WHERE user_id = :user_id
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

/**
 * Returns aggregate counts for the current user's notifications.
 *
 * @return array{total:int, this_week:int, unread:int}
 */
function client_get_notification_stats_for_current_user(): array
{
    $userId = notifications_current_user_id();

    $stmt = db()->prepare(
        'SELECT
            COUNT(*) AS total_notifications,
            SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1) THEN 1 ELSE 0 END) AS this_week_notifications,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread_notifications
         FROM notifications
         WHERE user_id = :user_id'
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

function client_has_unread_notifications_for_current_user(): bool
{
    $userId = notifications_current_user_id();

    $stmt = db()->prepare(
        'SELECT EXISTS(
            SELECT 1
            FROM notifications
            WHERE user_id = :user_id AND is_read = 0
        ) AS has_unread'
    );
    $stmt->execute(['user_id' => $userId]);

    $result = $stmt->fetch();
    if (!is_array($result)) {
        return false;
    }

    return ((int) ($result['has_unread'] ?? 0)) === 1;
}

/**
 * Marks every unread notification for the current user as read.
 *
 * @return int number of rows updated
 */
function client_mark_all_notifications_as_read_for_current_user(): int
{
    $userId = notifications_current_user_id();

    $stmt = db()->prepare(
        'UPDATE notifications
         SET is_read = 1
         WHERE user_id = :user_id AND is_read = 0'
    );
    $stmt->execute(['user_id' => $userId]);

    return (int) $stmt->rowCount();
}

/**
 * Deletes every notification belonging to the current user.
 *
 * @return int number of rows deleted
 */
function client_delete_all_notifications_for_current_user(): int
{
    $userId = notifications_current_user_id();

    $stmt = db()->prepare(
        'DELETE FROM notifications
         WHERE user_id = :user_id'
    );
    $stmt->execute(['user_id' => $userId]);

    return (int) $stmt->rowCount();
}

/**
 * Toggles the is_read flag of a single notification that belongs to the
 * current user.
 *
 * @return bool|null new read-state, or null when the notification was not
 *                   found (wrong id or wrong owner).
 */
function client_toggle_notification_read_for_current_user(int $notificationId): ?bool
{
    if ($notificationId <= 0) {
        return null;
    }

    $userId = notifications_current_user_id();
    $connection = db();

    try {
        $connection->beginTransaction();

        $selectStmt = $connection->prepare(
            'SELECT is_read
             FROM notifications
             WHERE notification_id = :notification_id AND user_id = :user_id
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
        in_array($normalized, ['APPOINTMENT_REMINDER', 'SESSION_CONFIRMATION'], true) => 'appointment',
        $normalized === 'ACTIVITY_ASSIGNED' => 'activity',
        $normalized === 'ASSESSMENT_READY' => 'test',
        $normalized === 'ALERT' => 'warning',
        default => 'info',
    };
}