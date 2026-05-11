<?php

declare(strict_types=1);

function getClientAppointments(int $clientId): array
{
    $stmt = db()->prepare("
        SELECT
            a.appointment_id,
            a.date_time,
            a.mode,
            a.status,
            therapist_user.name AS therapist_name
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

        $appointments[] = [
            'id' => (int) $row['appointment_id'],
            'date' => $dt->format('Y-m-d'),
            'time' => $dt->format('H:i'),
            'mode' => strtoupper((string) $row['mode']),
            'status' => strtolower((string) $row['status']),
            'therapist_name' => (string) $row['therapist_name'],
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

    $totalSessions = 0;
    try {
        $stmt = db()->prepare("
            SELECT COUNT(*)
            FROM sessions s
            INNER JOIN cases c ON s.case_id = c.case_id
            WHERE c.client_id = :client_id
        ");
        $stmt->execute(['client_id' => $clientId]);
        $totalSessions = (int) $stmt->fetchColumn();
    } catch (\Throwable $e) {
        $totalSessions = 0;
    }

    return [
        'upcoming'  => $upcoming,
        'completed' => $completed,
        'total'     => $totalSessions,
    ];
}
