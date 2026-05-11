<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';

const THERAPIST_COLOR_FALLBACK = '#9CA3AF';

function therapistDisplayColor(?string $dbColor): string
{
    if ($dbColor === null) {
        return THERAPIST_COLOR_FALLBACK;
    }

    $trimmed = trim($dbColor);

    return $trimmed === '' ? THERAPIST_COLOR_FALLBACK : $trimmed;
}

/**
 *
 * @return list<array{name: string, color: string, therapist_id: int}>
 */
function getTherapistLegend(PDO $pdo): array
{
    $statement = $pdo->query("
        SELECT
            t.therapist_id,
            u.name AS name,
            t.color AS color
        FROM therapists t
        INNER JOIN users u ON u.user_id = t.therapist_id
        ORDER BY t.therapist_id ASC
        LIMIT 6
    ");

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    $legend = [];

    foreach ($rows as $row) {
        $legend[] = [
            'therapist_id' => (int) $row['therapist_id'],
            'name' => (string) $row['name'],
            'color' => therapistDisplayColor($row['color'] ?? null),
        ];
    }

    return $legend;
}

/**
 * @return list<array{id: int, date: string, time: string, mode: string, status: string, client_name: string, therapist_name: string|null, therapist_id: int|null, color: string}>
 */
function getAllAppointments(PDO $pdo): array
{
    $sql = "
        SELECT
            a.appointment_id,
            a.date_time,
            a.mode,
            a.status,
            a.therapist_id,
            client_user.name AS client_name,
            therapist_user.name AS therapist_name,
            t.color AS therapist_color
        FROM appointments a
        INNER JOIN clients c ON a.client_id = c.client_id
        INNER JOIN users client_user ON c.client_id = client_user.user_id
        LEFT JOIN therapists t ON a.therapist_id = t.therapist_id
        LEFT JOIN users therapist_user ON t.therapist_id = therapist_user.user_id
        ORDER BY a.date_time ASC
    ";

    $statement = $pdo->query($sql);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    $appointments = [];

    foreach ($rows as $row) {
        $dateTime = new DateTime($row['date_time']);
        $status = strtolower((string) $row['status']);

        if ($status === 'requested') {
            $status = 'pending';
        }

        $therapistId = $row['therapist_id'] !== null && $row['therapist_id'] !== ''
            ? (int) $row['therapist_id']
            : null;

        $appointments[] = [
            'id' => (int) $row['appointment_id'],
            'date' => $dateTime->format('Y-m-d'),
            'time' => $dateTime->format('H:i'),
            'mode' => strtoupper((string) $row['mode']),
            'status' => $status,
            'client_name' => (string) $row['client_name'],
            'therapist_name' => $row['therapist_name'] !== null && $row['therapist_name'] !== ''
                ? (string) $row['therapist_name']
                : null,
            'therapist_id' => $therapistId,
            'color' => therapistDisplayColor($row['therapist_color'] ?? null),
        ];
    }

    return $appointments;
}

function getAppointmentStats(PDO $pdo): array
{
    $sql = "
        SELECT
            COUNT(*) AS total_appointments,
            SUM(CASE WHEN UPPER(status) = 'CONFIRMED' THEN 1 ELSE 0 END) AS confirmed_appointments,
            SUM(CASE WHEN UPPER(status) IN ('PENDING', 'REQUESTED') THEN 1 ELSE 0 END) AS pending_appointments,
            SUM(CASE WHEN UPPER(mode) = 'ONLINE' THEN 1 ELSE 0 END) AS online_appointments
        FROM appointments
    ";

    $statement = $pdo->query($sql);
    $stats = $statement->fetch(PDO::FETCH_ASSOC);

    return [
        'total' => (int) ($stats['total_appointments'] ?? 0),
        'confirmed' => (int) ($stats['confirmed_appointments'] ?? 0),
        'pending' => (int) ($stats['pending_appointments'] ?? 0),
        'online' => (int) ($stats['online_appointments'] ?? 0),
    ];
}

$pdo = db();
$appointments = getAllAppointments($pdo);
$stats = getAppointmentStats($pdo);
$therapistLegend = getTherapistLegend($pdo);
