<?php
require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/db.php';

function getCurrentClientId()
{
    $user = current_user();

    if (!$user || empty($user['user_id'])) {
        return 0;
    }

    return (int) $user['user_id'];
}

function getBookingTherapist($therapistId)
{
    $sql = "
        SELECT
            u.user_id,
            u.name,
            u.email,
            u.avatar,
            t.specialization,
            t.certification,
            t.bio,
            t.experience_years
        FROM users u
        INNER JOIN therapists t ON t.therapist_id = u.user_id
        WHERE u.user_id = ?
          AND u.role = 'THERAPIST'
          AND u.is_active = 1
        LIMIT 1
    ";

    $statement = db()->prepare($sql);
    $statement->execute([$therapistId]);
    $row = $statement->fetch();

    if (!$row) {
        return null;
    }

    $image = $row['avatar'];
    if (!$image) {
        $image = '../images/default-doctor.png';
    }

    $experience = trim((string) $row['bio']);
    if ((int) $row['experience_years'] > 0) {
        $experience = 'خبرة ' . (int) $row['experience_years'] . ' سنوات';
    }

    return [
        'id' => (int) $row['user_id'],
        'name' => $row['name'],
        'image' => $image,
        'special' => $row['specialization'],
        'degree' => $row['certification'],
        'experience' => $experience,
        'work' => getTherapistWorkText($therapistId),
        'availability' => getTherapistAvailability($therapistId),
        'email' => $row['email'],
        'consultPrice' => '150 شيكل',
        'therapyPrice' => '120 شيكل',
    ];
}

function getTherapistAvailability($therapistId)
{
    $sql = "
        SELECT day_of_week, start_time, end_time
        FROM therapist_availability
        WHERE therapist_id = ?
          AND is_active = 1
        ORDER BY day_of_week, start_time
    ";

    $statement = db()->prepare($sql);
    $statement->execute([$therapistId]);
    $rows = $statement->fetchAll();

    $availability = [];
    foreach ($rows as $row) {
        $availability[] = [
            'day' => $row['day_of_week'],
            'day_label' => getArabicDay($row['day_of_week']),
            'start' => substr($row['start_time'], 0, 5),
            'end' => substr($row['end_time'], 0, 5),
        ];
    }

    return $availability;
}

function getTherapistWorkText($therapistId)
{
    $availability = getTherapistAvailability($therapistId);

    if (count($availability) === 0) {
        return 'غير متاح';
    }

    $text = [];
    foreach ($availability as $slot) {
        $text[] = $slot['day_label'] . ' ' . $slot['start'] . ' - ' . $slot['end'];
    }

    return implode(' | ', $text);
}

function saveBookingRequest($clientId, $therapistId, $sessionType, $meetingType, $date, $time)
{
    if ($clientId <= 0 || $therapistId <= 0) {
        return ['success' => false, 'message' => 'بيانات الحجز غير صحيحة.'];
    }

    if ($date === '' || $time === '') {
        return ['success' => false, 'message' => 'يرجى اختيار التاريخ والوقت.'];
    }

    if ($sessionType !== 'consult' && $sessionType !== 'therapy') {
        return ['success' => false, 'message' => 'نوع الجلسة غير صحيح.'];
    }

    if ($meetingType === 'online') {
        $mode = 'ONLINE';
    } elseif ($meetingType === 'offline') {
        $mode = 'IN_CENTER';
    } else {
        return ['success' => false, 'message' => 'طريقة الجلسة غير صحيحة.'];
    }

    $dateTimeText = $date . ' ' . $time . ':00';
    $dateTime = new DateTime($dateTimeText);
    $now = new DateTime();

    if ($dateTime <= $now) {
        return ['success' => false, 'message' => 'يرجى اختيار موعد قادم.'];
    }

    if (!isClientExists($clientId)) {
        return ['success' => false, 'message' => 'تعذر العثور على بيانات العميل.'];
    }

    if (!isTherapistAvailableAt($therapistId, $dateTime)) {
        return ['success' => false, 'message' => 'الوقت المختار غير متاح لهذا الأخصائي.'];
    }

    if (isAppointmentTaken($therapistId, $dateTimeText)) {
        return ['success' => false, 'message' => 'هذا الموعد محجوز مسبقا.'];
    }

    $caseId = getClientCaseId($clientId, $therapistId);

    $sql = "
        INSERT INTO appointments
            (case_id, therapist_id, client_id, date_time, duration_min, mode, status)
        VALUES
            (?, ?, ?, ?, 60, ?, 'REQUESTED')
    ";

    $statement = db()->prepare($sql);
    $statement->execute([
        $caseId,
        $therapistId,
        $clientId,
        $dateTimeText,
        $mode,
    ]);

    return [
        'success' => true,
        'appointment_id' => (int) db()->lastInsertId(),
        'message' => 'تم إرسال طلب الحجز بنجاح. بانتظار موافقة الأخصائي.',
    ];
}

function isClientExists($clientId)
{
    $statement = db()->prepare('SELECT client_id FROM clients WHERE client_id = ? LIMIT 1');
    $statement->execute([$clientId]);

    return (bool) $statement->fetch();
}

function getClientCaseId($clientId, $therapistId)
{
    $sql = "
        SELECT case_id
        FROM cases
        WHERE client_id = ?
          AND therapist_id = ?
          AND status <> 'CLOSED'
        ORDER BY case_id DESC
        LIMIT 1
    ";

    $statement = db()->prepare($sql);
    $statement->execute([$clientId, $therapistId]);
    $caseId = $statement->fetchColumn();

    if (!$caseId) {
        return null;
    }

    return (int) $caseId;
}

function isTherapistAvailableAt($therapistId, $dateTime)
{
    $day = strtoupper($dateTime->format('l'));
    $time = $dateTime->format('H:i:s');

    $sql = "
        SELECT availability_id
        FROM therapist_availability
        WHERE therapist_id = ?
          AND day_of_week = ?
          AND is_active = 1
          AND start_time <= ?
          AND end_time > ?
        LIMIT 1
    ";

    $statement = db()->prepare($sql);
    $statement->execute([$therapistId, $day, $time, $time]);

    return (bool) $statement->fetch();
}

function isAppointmentTaken($therapistId, $dateTimeText)
{
    $sql = "
        SELECT appointment_id
        FROM appointments
        WHERE therapist_id = ?
          AND date_time = ?
          AND status IN ('REQUESTED', 'CONFIRMED')
        LIMIT 1
    ";

    $statement = db()->prepare($sql);
    $statement->execute([$therapistId, $dateTimeText]);

    return (bool) $statement->fetch();
}

function hasUnreadNotifications()
{
    $clientId = getCurrentClientId();

    $statement = db()->prepare("
        SELECT notification_id
        FROM notifications
        WHERE user_id = ?
          AND is_read = 0
        LIMIT 1
    ");
    $statement->execute([$clientId]);

    return (bool) $statement->fetch();
}

function getArabicDay($day)
{
    $days = [
        'SUNDAY' => 'الأحد',
        'MONDAY' => 'الإثنين',
        'TUESDAY' => 'الثلاثاء',
        'WEDNESDAY' => 'الأربعاء',
        'THURSDAY' => 'الخميس',
        'FRIDAY' => 'الجمعة',
        'SATURDAY' => 'السبت',
    ];

    return $days[$day] ?? $day;
}
