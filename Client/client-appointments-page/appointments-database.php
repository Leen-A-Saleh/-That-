<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/avatar-storage.php';
require_once __DIR__ . '/../../Database/appointments-database.php';

function getClientAppointments(int $clientId): array
{
    expireAwaitingPaymentAppointments($clientId);
    $effectiveStatus = APPOINTMENT_EFFECTIVE_STATUS_SQL;

    $stmt = db()->prepare("
        SELECT
            a.appointment_id,
            a.date_time,
            a.mode,
            {$effectiveStatus} AS status,
            therapist_user.name AS therapist_name,
            therapist_user.avatar AS therapist_avatar
        FROM appointments a
        INNER JOIN therapists t
               ON a.therapist_id = t.therapist_id
        INNER JOIN users therapist_user
               ON t.therapist_id = therapist_user.user_id
        WHERE a.client_id = :client_id
        ORDER BY a.date_time ASC
    ");
    $stmt->execute(['client_id' => $clientId]);

    $appointments = [];

    foreach ($stmt->fetchAll() as $row) {
        $dt = new DateTime($row['date_time']);

        $avatarPath = trim((string) ($row['therapist_avatar'] ?? ''));

        $appointments[] = [
            'id' => (int) $row['appointment_id'],
            'date' => $dt->format('Y-m-d'),
            'time' => $dt->format('H:i'),
            'mode' => strtoupper((string) $row['mode']),
            'status' => strtolower((string) $row['status']),
            'therapist_name' => (string) $row['therapist_name'],
            'therapist_avatar' => $avatarPath !== ''
                ? (avatar_is_managed_storage_path($avatarPath) ? avatar_public_url($avatarPath) : $avatarPath)
                : '',
        ];
    }

    return $appointments;
}

function getClientAppointmentStats(int $clientId): array
{
    $stmt = db()->prepare("
        SELECT COUNT(*)
        FROM appointments
        WHERE client_id = :client_id
          AND UPPER(status) = 'CONFIRMED'
          AND date_time     > NOW()
    ");
    $stmt->execute(['client_id' => $clientId]);
    $upcoming = (int) $stmt->fetchColumn();

    $stmt = db()->prepare("
        SELECT COUNT(*)
        FROM appointments
        WHERE client_id  = :client_id
          AND UPPER(status) = 'COMPLETED' 
    ");

    $stmt->execute(['client_id' => $clientId]);
    $completed = (int) $stmt->fetchColumn();

    return [
        'upcoming'  => $upcoming,
        'completed' => $completed,
    ];
}
