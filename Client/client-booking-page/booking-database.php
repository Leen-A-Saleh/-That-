<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/profile-database.php';
require_once __DIR__ . '/../../Database/appointments-database.php';
require_once __DIR__ . '/../../Database/notifications-database.php';

// ─── Utilities

function getCurrentClientId(): int
{
    try {
        return client_current_user_id();
    } catch (Throwable) {
        return 0;
    }
}

// ─── Lookup

/**
 * @return array<string, mixed>|null
 */
function getBookingTherapist(int $therapistId): ?array
{
    if ($therapistId <= 0) {
        return null;
    }

    $statement = db()->prepare(
        'SELECT
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
         WHERE u.user_id = :therapist_id
           AND u.role = :role
           AND u.is_active = 1
         LIMIT 1'
    );
    $statement->execute([
        'therapist_id' => $therapistId,
        'role' => 'THERAPIST',
    ]);
    $row = $statement->fetch();

    if (!$row) {
        return null;
    }

    $image = trim((string) ($row['avatar'] ?? ''));
    if ($image === '') {
        $image = '../images/default-doctor.png';
    }

    $experienceYears = (int) ($row['experience_years'] ?? 0);
    $experience = $experienceYears > 0
        ? 'خبرة ' . $experienceYears . ' سنوات'
        : trim((string) ($row['bio'] ?? ''));

    return [
        'id' => (int) $row['user_id'],
        'name' => (string) $row['name'],
        'image' => $image,
        'special' => (string) ($row['specialization'] ?? ''),
        'degree' => (string) ($row['certification'] ?? ''),
        'experience' => $experience,
        'work' => getTherapistWorkText($therapistId),
        'availability' => getTherapistAvailability($therapistId),
        'email' => (string) ($row['email'] ?? ''),
        'consultPrice' => '150 شيكل',
        'therapyPrice' => '120 شيكل',
    ];
}

/**
 * @return list<array{day:string, day_label:string, start:string, end:string}>
 */
function getTherapistAvailability(int $therapistId): array
{
    $statement = db()->prepare(
        'SELECT day_of_week, start_time, end_time
         FROM therapist_availability
         WHERE therapist_id = :therapist_id
           AND is_active = 1
         ORDER BY day_of_week, start_time'
    );
    $statement->execute(['therapist_id' => $therapistId]);

    $availability = [];
    foreach ($statement->fetchAll() as $row) {
        $availability[] = [
            'day' => (string) ($row['day_of_week'] ?? ''),
            'day_label' => client_translate_day((string) ($row['day_of_week'] ?? '')),
            'start' => substr((string) ($row['start_time'] ?? ''), 0, 5),
            'end' => substr((string) ($row['end_time'] ?? ''), 0, 5),
        ];
    }

    return $availability;
}

function getTherapistWorkText(int $therapistId): string
{
    $availability = getTherapistAvailability($therapistId);

    if ($availability === []) {
        return 'غير متاح';
    }

    $text = [];
    foreach ($availability as $slot) {
        $text[] = $slot['day_label'] . ' ' . $slot['start'] . ' - ' . $slot['end'];
    }

    return implode(' | ', $text);
}

// ─── Booking

/**
 * @return array{success:bool, message:string, appointment_id?:int}
 */
function saveBookingRequest(
    int $clientId,
    int $therapistId,
    string $sessionType,
    string $meetingType,
    string $date,
    string $time
): array {
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

    if (hasConfirmedAppointmentAt($therapistId, $dateTimeText)) {
        return ['success' => false, 'message' => 'هذا الموعد محجوز مسبقا.'];
    }

    $caseId = getClientCaseId($clientId, $therapistId);
    $amount = getBookingPaymentAmount($sessionType);
    $pdo = db();

    try {
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO appointments
            (
                case_id,
                therapist_id,
                client_id,
                session_type,
                date_time,
                duration_min,
                mode,
                status
            )
         VALUES
            (
                :case_id,
                :therapist_id,
                :client_id,
                :session_type,
                :date_time,
                60,
                :mode,
                :status
            )'
        );

        $statement->execute([
            'case_id' => $caseId,
            'therapist_id' => $therapistId,
            'client_id' => $clientId,

            'session_type' => $sessionType === 'consult'
                ? 'CONSULTATION'
                : 'THERAPY',

            'date_time' => $dateTimeText,
            'mode' => $mode,
            'status' => 'REQUESTED',
        ]);

        $appointmentId = (int) $pdo->lastInsertId();

        $paymentStatement = $pdo->prepare(
            'INSERT INTO payments
            (
                client_id,
                therapist_id,
                amount
            )
         VALUES
            (
                :client_id,
                :therapist_id,
                :amount
            )'
        );
        $paymentStatement->execute([
            'client_id' => $clientId,
            'therapist_id' => $therapistId,
            'amount' => $amount,
        ]);

        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    }

    try {
        notify_therapist_new_appointment_request(
            $therapistId,
            $clientId,
            $dateTimeText,
            $sessionType
        );
    } catch (Throwable) {
        // Notification failure must not undo a successful booking request
    }

    return [
        'success' => true,
        'appointment_id' => $appointmentId,
        'message' => 'تم إرسال طلب الحجز بنجاح. بانتظار موافقة الأخصائي.',
    ];
}

function getBookingPaymentAmount(string $sessionType): string
{
    return $sessionType === 'consult' ? '150.00' : '120.00';
}

// ─── Validation Helpers

function isClientExists(int $clientId): bool
{
    $statement = db()->prepare(
        'SELECT client_id
         FROM clients
         WHERE client_id = :client_id
         LIMIT 1'
    );
    $statement->execute(['client_id' => $clientId]);

    return (bool) $statement->fetch();
}

function getClientCaseId(int $clientId, int $therapistId): ?int
{
    $statement = db()->prepare(
        'SELECT case_id
         FROM cases
         WHERE client_id = :client_id
           AND therapist_id = :therapist_id
           AND status <> :status
         ORDER BY case_id DESC
         LIMIT 1'
    );
    $statement->execute([
        'client_id' => $clientId,
        'therapist_id' => $therapistId,
        'status' => 'CLOSED',
    ]);
    $caseId = $statement->fetchColumn();

    if ($caseId === false) {
        return null;
    }

    return (int) $caseId;
}

function isTherapistAvailableAt(int $therapistId, DateTime $dateTime): bool
{
    $day = strtoupper($dateTime->format('l'));
    $time = $dateTime->format('H:i:s');

    $statement = db()->prepare(
        'SELECT availability_id
         FROM therapist_availability
         WHERE therapist_id = :therapist_id
           AND day_of_week = :day_of_week
           AND is_active = 1
           AND start_time <= :start_bound
           AND end_time > :end_bound
         LIMIT 1'
    );
    $statement->execute([
        'therapist_id' => $therapistId,
        'day_of_week' => $day,
        'start_bound' => $time,
        'end_bound' => $time,
    ]);

    return (bool) $statement->fetch();
}
