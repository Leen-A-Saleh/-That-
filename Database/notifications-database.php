<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @param 'LOW'|'NORMAL'|'URGENT' $priority
 */
function create_user_notification(
    int $userId,
    string $title,
    string $body,
    string $type,
    string $priority = 'NORMAL'
): bool {
    if ($userId <= 0 || $title === '' || $body === '') {
        return false;
    }

    $allowedTypes = [
        'GENERAL',
        'APPOINTMENT_REMINDER',
        'MESSAGE',
        'AD',
        'SESSION_CONFIRMATION',
        'ALERT',
        'ACTIVITY_ASSIGNED',
        'ASSESSMENT_READY',
        'APPOINTMENT',
        'ACTIVITY_REMINDER',
    ];

    if (!in_array($type, $allowedTypes, true)) {
        $type = 'GENERAL';
    }

    if (!in_array($priority, ['LOW', 'NORMAL', 'URGENT'], true)) {
        $priority = 'NORMAL';
    }

    $statement = db()->prepare(
        'INSERT INTO notifications (user_id, title, body, type, priority, is_read, created_at)
         VALUES (:user_id, :title, :body, :type, :priority, 0, NOW())'
    );

    return $statement->execute([
        'user_id' => $userId,
        'title' => $title,
        'body' => $body,
        'type' => $type,
        'priority' => $priority,
    ]);
}

function get_user_display_name(int $userId): string
{
    if ($userId <= 0) {
        return 'مستخدم';
    }

    $statement = db()->prepare(
        'SELECT name FROM users WHERE user_id = :user_id LIMIT 1'
    );
    $statement->execute(['user_id' => $userId]);
    $name = trim((string) ($statement->fetchColumn() ?: ''));

    return $name !== '' ? $name : 'مستخدم';
}

function format_appointment_datetime_ar(string $dateTimeText): string
{
    try {
        $dateTime = new DateTime($dateTimeText);
    } catch (Throwable) {
        return $dateTimeText;
    }

    $hour = (int) $dateTime->format('G');
    $minute = $dateTime->format('i');
    $period = $hour >= 12 ? 'مساءً' : 'صباحاً';
    $hour12 = $hour % 12 ?: 12;
    $time = sprintf('%d:%s %s', $hour12, $minute, $period);

    return $dateTime->format('Y-m-d') . ' الساعة ' . $time;
}

function appointment_session_type_label(string $sessionType): string
{
    return match ($sessionType) {
        'consult', 'CONSULTATION' => 'جلسة استشارية',
        'therapy', 'THERAPY' => 'جلسة علاجية',
        default => 'جلسة',
    };
}

function notify_therapist_new_appointment_request(
    int $therapistUserId,
    int $clientUserId,
    string $dateTimeText,
    string $sessionType
): void {
    $clientName = get_user_display_name($clientUserId);
    $when = format_appointment_datetime_ar($dateTimeText);
    $sessionLabel = appointment_session_type_label($sessionType);

    create_user_notification(
        $therapistUserId,
        'طلب موعد جديد',
        'أرسل ' . $clientName . ' طلب ' . $sessionLabel . ' بتاريخ ' . $when . '. يرجى مراجعة طلبات المواعيد.',
        'APPOINTMENT',
        'NORMAL'
    );
}

function notify_client_appointment_confirmed(
    int $clientUserId,
    int $therapistUserId,
    string $dateTimeText
): void {
    $therapistName = get_user_display_name($therapistUserId);
    $when = format_appointment_datetime_ar($dateTimeText);

    create_user_notification(
        $clientUserId,
        'تم تأكيد موعدك',
        'أكّد الأخصائي ' . $therapistName . ' موعدك في ' . $when . '.',
        'SESSION_CONFIRMATION',
        'NORMAL'
    );
}

function notify_client_appointment_awaiting_payment(
    int $clientUserId,
    int $therapistUserId,
    string $dateTimeText
): void {
    $therapistName = get_user_display_name($therapistUserId);
    create_user_notification(
        $clientUserId,
        'موعد بانتظار الدفع',
        'تمت الموافقة على موعدك مع الأخصائي ' . $therapistName . '. يرجى الدفع خلال ٢٤ ساعة لتأكيد الحجز.',
        'SESSION_CONFIRMATION',
        'URGENT'
    );
}

function notify_client_appointment_rejected(
    int $clientUserId,
    int $therapistUserId,
    string $dateTimeText
): void {
    $therapistName = get_user_display_name($therapistUserId);
    $when = format_appointment_datetime_ar($dateTimeText);

    create_user_notification(
        $clientUserId,
        'تم رفض طلب الموعد',
        'لم يتم قبول طلب موعدك مع الأخصائي ' . $therapistName . ' في ' . $when . '.',
        'ALERT',
        'NORMAL'
    );
}

function notify_client_appointment_cancelled_slot_taken(
    int $clientUserId,
    int $therapistUserId,
    string $dateTimeText
): void {
    $therapistName = get_user_display_name($therapistUserId);
    $when = format_appointment_datetime_ar($dateTimeText);

    create_user_notification(
        $clientUserId,
        'تم إلغاء طلب الموعد',
        'تم إلغاء طلبك لأن الأخصائي ' . $therapistName . ' أكّد موعداً آخر في ' . $when . '.',
        'ALERT',
        'NORMAL'
    );
}

/**
 * @return list<int> client user ids for competing REQUESTED appointments (excluding the confirmed one).
 */
function get_competing_requested_client_ids(
    int $therapistId,
    string $dateTimeText,
    int $excludeAppointmentId
): array {
    $statement = db()->prepare(
        'SELECT client_id
         FROM appointments
         WHERE therapist_id = :therapist_id
           AND date_time = :date_time
           AND status = :status
           AND appointment_id <> :appointment_id'
    );
    $statement->execute([
        'therapist_id' => $therapistId,
        'date_time' => $dateTimeText,
        'status' => 'REQUESTED',
        'appointment_id' => $excludeAppointmentId,
    ]);

    $clientIds = [];
    foreach ($statement->fetchAll() as $row) {
        $clientId = (int) ($row['client_id'] ?? 0);
        if ($clientId > 0) {
            $clientIds[] = $clientId;
        }
    }

    return $clientIds;
}
