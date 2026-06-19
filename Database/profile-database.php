<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/client.php';

function hasUnreadNotifications(): bool
{
    $clientId = client_current_user_id();
    $notificationFilterSql = client_notification_preferences_sql_filter();

    $stmt = db()->prepare(
        'SELECT EXISTS(
            SELECT 1
            FROM notifications
            WHERE user_id = :user_id AND is_read = 0' . $notificationFilterSql . '
        ) AS has_unread'
    );
    $stmt->execute(['user_id' => $clientId]);

    return ((int) ($stmt->fetchColumn() ?: 0)) === 1;
}

/**
 * @return array{appointment_notifications:bool,message_notifications:bool,activity_reminder_notifications:bool}
 */
function client_get_notification_preferences_for_current_user(): array
{
    $clientId = client_current_user_id();

    $stmt = db()->prepare(
        'SELECT appointment_notifications, message_notifications, activity_reminder_notifications
         FROM users
         WHERE user_id = :user_id AND role = :role
         LIMIT 1'
    );
    $stmt->execute([
        'user_id' => $clientId,
        'role' => 'CLIENT',
    ]);

    $row = $stmt->fetch();
    if (!is_array($row)) {
        return client_default_notification_preferences();
    }

    return [
        'appointment_notifications' => ((int) ($row['appointment_notifications'] ?? 1)) === 1,
        'message_notifications' => ((int) ($row['message_notifications'] ?? 1)) === 1,
        'activity_reminder_notifications' => ((int) ($row['activity_reminder_notifications'] ?? 1)) === 1,
    ];
}

/**
 * @return array{success:bool,message:string,preferences?:array{appointment_notifications:bool,message_notifications:bool,activity_reminder_notifications:bool}}
 */
function client_update_notification_preferences_for_current_user(
    bool $appointmentNotifications,
    bool $messageNotifications,
    bool $activityReminderNotifications
): array {
    $clientId = client_current_user_id();

    $stmt = db()->prepare(
        'UPDATE users
         SET appointment_notifications = :appointment_notifications,
             message_notifications = :message_notifications,
             activity_reminder_notifications = :activity_reminder_notifications
         WHERE user_id = :user_id AND role = :role
         LIMIT 1'
    );
    $stmt->execute([
        'appointment_notifications' => $appointmentNotifications ? 1 : 0,
        'message_notifications' => $messageNotifications ? 1 : 0,
        'activity_reminder_notifications' => $activityReminderNotifications ? 1 : 0,
        'user_id' => $clientId,
        'role' => 'CLIENT',
    ]);

    return [
        'success' => true,
        'message' => 'تم حفظ إعدادات الإشعارات بنجاح.',
        'preferences' => [
            'appointment_notifications' => $appointmentNotifications,
            'message_notifications' => $messageNotifications,
            'activity_reminder_notifications' => $activityReminderNotifications,
        ],
    ];
}

function client_notification_preferences_sql_filter(string $tableAlias = ''): string
{
    $preferences = client_get_notification_preferences_for_current_user();
    $prefix = $tableAlias !== '' ? $tableAlias . '.' : '';
    $hiddenTypes = [];

    if (!$preferences['appointment_notifications']) {
        $hiddenTypes = array_merge($hiddenTypes, [
            'APPOINTMENT',
            'APPOINTMENT_REMINDER',
            'SESSION_CONFIRMATION',
        ]);
    }

    if (!$preferences['activity_reminder_notifications']) {
        $hiddenTypes[] = 'ACTIVITY_REMINDER';
    }

    if ($hiddenTypes === []) {
        return '';
    }

    $quotedTypes = array_map(
        static fn(string $type): string => db()->quote($type),
        array_values(array_unique($hiddenTypes))
    );

    return ' AND UPPER(' . $prefix . 'type) NOT IN (' . implode(', ', $quotedTypes) . ')';
}

/**
 * @return array{appointment_notifications:bool,message_notifications:bool,activity_reminder_notifications:bool}
 */
function client_default_notification_preferences(): array
{
    return [
        'appointment_notifications' => true,
        'message_notifications' => true,
        'activity_reminder_notifications' => true,
    ];
}
