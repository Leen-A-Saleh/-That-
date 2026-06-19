<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/appointments-database.php';

const THERAPIST_COLOR_PALETTE = [
    '#EC4899',
    '#6366F1',
    '#14B8A6',
    '#F59E0B',
    '#8B5CF6',
    '#10B981',
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Builds a map of therapist_id => color.
 * Uses the stored DB color when set; otherwise assigns from the palette in order.
 *
 * @return array<int, string>
 */
function buildTherapistColorMap(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT therapist_id, color FROM therapists ORDER BY therapist_id ASC');
    $map = [];
    $paletteIndex = 0;

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int) $row['therapist_id'];
        $stored = trim((string) ($row['color'] ?? ''));
        $map[$id] = $stored !== ''
            ? $stored
            : THERAPIST_COLOR_PALETTE[$paletteIndex++ % count(THERAPIST_COLOR_PALETTE)];
    }

    return $map;
}

// ─── Queries ──────────────────────────────────────────────────────────────────

/**
 * @param array<int, string> $colorMap
 * @return list<array{name: string, color: string, therapist_id: int}>
 */
function getTherapistLegend(PDO $pdo, array $colorMap): array
{
    $statement = $pdo->query("
        SELECT
            t.therapist_id,
            u.name AS name
        FROM therapists t
        INNER JOIN users u ON u.user_id = t.therapist_id
        ORDER BY t.therapist_id ASC
        LIMIT 6
    ");

    $legend = [];

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int) $row['therapist_id'];
        $legend[] = [
            'therapist_id' => $id,
            'name'         => (string) $row['name'],
            'color'        => $colorMap[$id] ?? '#9CA3AF',
        ];
    }

    return $legend;
}

/**
 * @param array<int, string> $colorMap
 * @return list<array{id: int, date: string, time: string, mode: string, status: string, client_name: string, therapist_name: string|null, therapist_id: int|null, color: string}>
 */
function getAllAppointments(PDO $pdo, array $colorMap): array
{
    cancelExpiredRequestedAppointments();

    $effectiveStatus = APPOINTMENT_EFFECTIVE_STATUS_SQL;

    $sql = "
        SELECT
            a.appointment_id,
            a.date_time,
            a.mode,
            {$effectiveStatus} AS status,
            a.therapist_id,
            client_user.name  AS client_name,
            therapist_user.name AS therapist_name
        FROM appointments a
        INNER JOIN clients c ON a.client_id = c.client_id
        INNER JOIN users client_user ON c.client_id = client_user.user_id
        LEFT JOIN therapists t ON a.therapist_id = t.therapist_id
        LEFT JOIN users therapist_user ON t.therapist_id = therapist_user.user_id
        ORDER BY a.date_time ASC
    ";

    $appointments = [];

    foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $dateTime = new DateTime($row['date_time']);
        $status   = strtolower((string) $row['status']);

        if ($status === 'requested') {
            $status = 'pending';
        }

        $therapistId = $row['therapist_id'] !== null && $row['therapist_id'] !== ''
            ? (int) $row['therapist_id']
            : null;

        $appointments[] = [
            'id'             => (int) $row['appointment_id'],
            'date'           => $dateTime->format('Y-m-d'),
            'time'           => $dateTime->format('H:i'),
            'mode'           => strtoupper((string) $row['mode']),
            'status'         => $status,
            'client_name'    => (string) $row['client_name'],
            'therapist_name' => $row['therapist_name'] !== null && $row['therapist_name'] !== ''
                ? (string) $row['therapist_name']
                : null,
            'therapist_id'   => $therapistId,
            'color'          => $therapistId !== null ? ($colorMap[$therapistId] ?? '#9CA3AF') : '#9CA3AF',
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
            SUM(CASE
                WHEN UPPER(status) IN ('PENDING', 'REQUESTED')
                 AND date_time >= NOW()
                THEN 1 ELSE 0
            END) AS pending_appointments,
            SUM(CASE WHEN UPPER(mode) = 'ONLINE' THEN 1 ELSE 0 END) AS online_appointments
        FROM appointments
    ";

    $stats = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

    return [
        'total'     => (int) ($stats['total_appointments'] ?? 0),
        'confirmed' => (int) ($stats['confirmed_appointments'] ?? 0),
        'pending'   => (int) ($stats['pending_appointments'] ?? 0),
        'online'    => (int) ($stats['online_appointments'] ?? 0),
    ];
}

// ─── Bootstrap ────────────────────────────────────────────────────────────────

$pdo             = db();
$colorMap        = buildTherapistColorMap($pdo);
$appointments    = getAllAppointments($pdo, $colorMap);
$stats           = getAppointmentStats($pdo);
$therapistLegend = getTherapistLegend($pdo, $colorMap);
