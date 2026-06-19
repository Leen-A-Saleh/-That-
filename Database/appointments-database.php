<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notifications-database.php';

const EXPIRED_REQUEST_CANCEL_REASON = 'انتهت صلاحية الطلب دون تأكيد';

const APPOINTMENT_EFFECTIVE_STATUS_SQL = "
    CASE
        WHEN UPPER(a.status) = 'REQUESTED' AND a.date_time < NOW() THEN 'CANCELLED'
        WHEN UPPER(a.status) = 'AWAITING_PAYMENT' AND a.payment_expiration_at < NOW() THEN 'PAYMENT_EXPIRED'
        ELSE a.status
    END
";

/**
 * Persist CANCELLED for REQUESTED appointments whose date_time is in the past.
 *
 * @return int number of rows updated
 */
function cancelExpiredRequestedAppointments(?int $clientId = null): int
{
    $sql = "
        UPDATE appointments
        SET status = 'CANCELLED',
            cancel_reason = :cancel_reason
        WHERE status = 'REQUESTED'
          AND date_time < NOW()
    ";
    $params = ['cancel_reason' => EXPIRED_REQUEST_CANCEL_REASON];

    if ($clientId !== null && $clientId > 0) {
        $sql .= ' AND client_id = :client_id';
        $params['client_id'] = $clientId;
    }

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->rowCount();
}

function expireAwaitingPaymentAppointments(?int $clientId = null): int
{
    $selectSql = "SELECT appointment_id, client_id, therapist_id FROM appointments WHERE status = 'AWAITING_PAYMENT' AND payment_expiration_at < NOW()";
    $params = [];
    if ($clientId !== null && $clientId > 0) {
        $selectSql .= ' AND client_id = :client_id';
        $params['client_id'] = $clientId;
    }
    
    $stmt = db()->prepare($selectSql);
    $stmt->execute($params);
    $expiredAppointments = $stmt->fetchAll();
    
    if (empty($expiredAppointments)) {
        return 0;
    }

    $sql = "
        UPDATE appointments
        SET status = 'PAYMENT_EXPIRED'
        WHERE status = 'AWAITING_PAYMENT'
          AND payment_expiration_at < NOW()
    ";
    
    if ($clientId !== null && $clientId > 0) {
        $sql .= ' AND client_id = :client_id';
    }

    $statement = db()->prepare($sql);
    $statement->execute($params);
    $count = $statement->rowCount();
    
    if ($count > 0) {
        require_once __DIR__ . '/notifications-database.php';
        foreach ($expiredAppointments as $row) {
            create_user_notification(
                (int)$row['client_id'],
                'انتهاء صلاحية الدفع',
                'انتهت المهلة المحددة للدفع لموعدك مع الأخصائي. يرجى تقديم طلب موعد جديد.',
                'ALERT',
                'URGENT'
            );
        }
    }

    return $count;
}

function hasConfirmedAppointmentAt(int $therapistId, string $dateTimeText): bool
{
    $statement = db()->prepare(
        'SELECT appointment_id
         FROM appointments
         WHERE therapist_id = :therapist_id
           AND date_time = :date_time
           AND status = :status
         LIMIT 1'
    );
    $statement->execute([
        'therapist_id' => $therapistId,
        'date_time' => $dateTimeText,
        'status' => 'CONFIRMED',
    ]);

    return (bool) $statement->fetch();
}

/**
 * @return array<string, mixed>|null
 */
function getTherapistAppointmentById(int $therapistId, int $appointmentId): ?array
{
    if ($therapistId <= 0 || $appointmentId <= 0) {
        return null;
    }

    $statement = db()->prepare(
        'SELECT appointment_id, therapist_id, client_id, date_time, status
         FROM appointments
         WHERE appointment_id = :appointment_id
           AND therapist_id = :therapist_id
         LIMIT 1'
    );
    $statement->execute([
        'appointment_id' => $appointmentId,
        'therapist_id' => $therapistId,
    ]);
    $row = $statement->fetch();

    return $row !== false ? $row : null;
}

/**
 * @return array{success:bool, message:string}
 */
function confirmTherapistAppointmentRequest(int $therapistId, int $appointmentId): array
{
    $appointment = getTherapistAppointmentById($therapistId, $appointmentId);

    if ($appointment === null) {
        return ['success' => false, 'message' => 'تعذر العثور على طلب الموعد.'];
    }

    if ((string) ($appointment['status'] ?? '') !== 'REQUESTED') {
        return ['success' => false, 'message' => 'هذا الطلب لم يعد قيد الانتظار.'];
    }

    $dateTimeText = (string) $appointment['date_time'];
    $clientId = (int) ($appointment['client_id'] ?? 0);
    $competingClientIds = get_competing_requested_client_ids(
        $therapistId,
        $dateTimeText,
        $appointmentId
    );

    if (hasConfirmedAppointmentAt($therapistId, $dateTimeText)) {
        return [
            'success' => false,
            'message' => 'هذا الموعد مؤكد بالفعل لطلب آخر.',
        ];
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();

        $confirmStatement = $pdo->prepare(
            'UPDATE appointments
             SET status = :status,
                 payment_expiration_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
             WHERE appointment_id = :appointment_id
               AND therapist_id = :therapist_id
               AND status = :requested'
        );
        $confirmStatement->execute([
            'status' => 'AWAITING_PAYMENT',
            'appointment_id' => $appointmentId,
            'therapist_id' => $therapistId,
            'requested' => 'REQUESTED',
        ]);

        if ($confirmStatement->rowCount() === 0) {
            $pdo->rollBack();

            return ['success' => false, 'message' => 'تعذر تأكيد الطلب. ربما تمت معالجته مسبقا.'];
        }

        $cancelStatement = $pdo->prepare(
            'UPDATE appointments
             SET status = :status,
                 cancel_reason = :cancel_reason
             WHERE therapist_id = :therapist_id
               AND date_time = :date_time
               AND status = :requested
               AND appointment_id <> :appointment_id'
        );
        $cancelStatement->execute([
            'status' => 'CANCELLED',
            'cancel_reason' => 'تم إلغاء الطلب لأن موعداً آخر أكّد لهذا الوقت',
            'therapist_id' => $therapistId,
            'date_time' => $dateTimeText,
            'requested' => 'REQUESTED',
            'appointment_id' => $appointmentId,
        ]);

        $pdo->commit();
    } catch (Throwable) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['success' => false, 'message' => 'حدث خطأ أثناء تأكيد الطلب.'];
    }

    try {
        if ($clientId > 0) {
            notify_client_appointment_awaiting_payment($clientId, $therapistId, $dateTimeText);
        }

        foreach ($competingClientIds as $competingClientId) {
            notify_client_appointment_cancelled_slot_taken(
                $competingClientId,
                $therapistId,
                $dateTimeText
            );
        }
    } catch (Throwable) {
    }

    return ['success' => true, 'message' => 'تم تأكيد الموعد بنجاح.'];
}

/**
 * @return array{success:bool, message:string}
 */
function rejectTherapistAppointmentRequest(int $therapistId, int $appointmentId): array
{
    $appointment = getTherapistAppointmentById($therapistId, $appointmentId);

    if ($appointment === null) {
        return ['success' => false, 'message' => 'تعذر العثور على طلب الموعد.'];
    }

    if ((string) ($appointment['status'] ?? '') !== 'REQUESTED') {
        return ['success' => false, 'message' => 'هذا الطلب لم يعد قيد الانتظار.'];
    }

    $statement = db()->prepare(
        'UPDATE appointments
         SET status = :status,
             cancel_reason = :cancel_reason
         WHERE appointment_id = :appointment_id
           AND therapist_id = :therapist_id
           AND status = :requested'
    );
    $statement->execute([
        'status' => 'CANCELLED',
        'cancel_reason' => 'رفض من قبل المعالج',
        'appointment_id' => $appointmentId,
        'therapist_id' => $therapistId,
        'requested' => 'REQUESTED',
    ]);

    if ($statement->rowCount() === 0) {
        return ['success' => false, 'message' => 'تعذر رفض الطلب. ربما تمت معالجته مسبقا.'];
    }

    $clientId = (int) ($appointment['client_id'] ?? 0);
    $dateTimeText = (string) ($appointment['date_time'] ?? '');

    try {
        if ($clientId > 0 && $dateTimeText !== '') {
            notify_client_appointment_rejected($clientId, $therapistId, $dateTimeText);
        }
    } catch (Throwable) {
    }

    return ['success' => true, 'message' => 'تم رفض الطلب.'];
}

/**
 * @return array{success:bool, message:string}
 */
function handleTherapistAppointmentRequestAction(
    int $therapistId,
    string $action,
    int $appointmentId
): array {
    if ($therapistId <= 0 || $appointmentId <= 0) {
        return ['success' => false, 'message' => 'بيانات الطلب غير صحيحة.'];
    }

    if ($action === 'accept') {
        return confirmTherapistAppointmentRequest($therapistId, $appointmentId);
    }

    if ($action === 'reject') {
        return rejectTherapistAppointmentRequest($therapistId, $appointmentId);
    }

    return ['success' => false, 'message' => 'إجراء غير معروف.'];
}
