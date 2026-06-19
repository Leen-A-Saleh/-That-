<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

function client_display_name(): string
{
    start_secure_session();

    $user = current_user();
    if (is_array($user)) {
        $name = trim((string) ($user['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $username = trim((string) ($user['username'] ?? ''));
        if ($username !== '') {
            return $username;
        }

        $email = trim((string) ($user['email'] ?? ''));
        if ($email !== '') {
            return $email;
        }
    }

    return 'مستخدم';
}

/**
 * @return int
 */
function client_current_user_id(): int
{
    start_secure_session();

    $user = current_user();
    if (!is_array($user) || !isset($user['user_id'])) {
        throw new RuntimeException('Client is not authenticated.');
    }

    return (int) $user['user_id'];
}

function client_unread_chat_conversations_count(int $clientId = 0): int
{
    if ($clientId <= 0) {
        try {
            $clientId = client_current_user_id();
        } catch (Throwable) {
            return 0;
        }
    }

    $stmt = db()->prepare(
        'SELECT COUNT(DISTINCT sender_id) AS cnt
         FROM messages
         WHERE receiver_id = :client_id AND is_read = 0'
    );
    $stmt->execute(['client_id' => $clientId]);

    return (int) ($stmt->fetchColumn() ?: 0);
}

/**
 * @return list<array{
 *   id:int,
 *   name:string,
 *   image:string,
 *   special:string,
 *   degree:string,
 *   experience:string,
 *   work:string,
 *   consultPrice:string,
 *   therapyPrice:string,
 *   email:string
 * }>
 */
function client_get_therapist_listings(): array
{
    $availabilityByTherapistId = client_get_therapist_availability_map();
    $availabilitySlotsByTherapistId = client_get_therapist_availability_slots();

    $stmt = db()->prepare(
        'SELECT
            u.user_id,
            u.name,
            u.email,
            u.avatar,
            t.specialization,
            t.bio,
            t.certification,
            t.experience_years
         FROM users u
         INNER JOIN therapists t ON t.therapist_id = u.user_id
         WHERE u.role = :role AND u.is_active = 1
         ORDER BY u.name ASC'
    );
    $stmt->execute([
        'role' => 'THERAPIST',
    ]);

    /** @var list<array<string,mixed>> $rows */
    $rows = $stmt->fetchAll();
    $cards = [];

    foreach ($rows as $row) {
        $cards[] = client_map_therapist_row_to_doctor_card($row, $availabilityByTherapistId, $availabilitySlotsByTherapistId);
    }

    return $cards;
}

/**
 * @param array<string,mixed> $row
 * @param array<int,string> $availabilityByTherapistId
 * @return array{
 *   id:int,
 *   name:string,
 *   image:string,
 *   special:string,
 *   degree:string,
 *   experience:string,
 *   work:string,
 *   consultPrice:string,
 *   therapyPrice:string,
 *   email:string
 * }
 */
function client_map_therapist_row_to_doctor_card(array $row, array $availabilityByTherapistId, array $availabilitySlotsByTherapistId = []): array
{
    $therapistId = (int) ($row['user_id'] ?? 0);

    return [
        'id' => $therapistId,
        'name' => trim((string) ($row['name'] ?? '')),
        'image' => client_normalize_therapist_avatar((string) ($row['avatar'] ?? '')),
        'special' => trim((string) ($row['specialization'] ?? '')),
        'degree' => trim((string) ($row['certification'] ?? '')),
        'experience' => client_build_experience_text((int) ($row['experience_years'] ?? 0), (string) ($row['bio'] ?? '')),
        'work' => $availabilityByTherapistId[$therapistId] ?? 'غير متاح',
        'availability' => $availabilitySlotsByTherapistId[$therapistId] ?? [],
        'consultPrice' => '150 شيكل',
        'therapyPrice' => '120 شيكل',
        'email' => trim((string) ($row['email'] ?? '')),
    ];
}

/**
 * @return array<int,string>
 */
function client_get_therapist_availability_map(): array
{
    $stmt = db()->prepare(
        'SELECT therapist_id, day_of_week, start_time, end_time
         FROM therapist_availability
         WHERE is_active = 1
         ORDER BY therapist_id ASC, day_of_week ASC, start_time ASC'
    );
    $stmt->execute();

    /** @var list<array<string,mixed>> $rows */
    $rows = $stmt->fetchAll();
    $map = [];

    foreach ($rows as $row) {
        $therapistId = (int) ($row['therapist_id'] ?? 0);
        $day = client_translate_day((string) ($row['day_of_week'] ?? ''));
        $start = substr((string) ($row['start_time'] ?? ''), 0, 5);
        $end = substr((string) ($row['end_time'] ?? ''), 0, 5);
        $slot = trim($day . ' ' . $start . '-' . $end);

        if ($slot === '') {
            continue;
        }

        if (!isset($map[$therapistId])) {
            $map[$therapistId] = $slot;
            continue;
        }

        $map[$therapistId] .= ' | ' . $slot;
    }

    return $map;
}

function client_translate_day(string $day): string
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

function client_normalize_therapist_avatar(string $avatar): string
{
    $avatar = trim($avatar);
    if ($avatar === '') {
        return '../../storage/avatars/user.png';
    }

    return $avatar;
}

function client_build_experience_text(int $experienceYears, string $bio): string
{
    if ($experienceYears > 0) {
        return 'خبرة ' . $experienceYears . ' سنوات';
    }

    $bio = trim($bio);
    if ($bio !== '') {
        return $bio;
    }

    return 'خبرة مهنية متنوعة';
}

function client_get_therapist_availability_slots(): array
{
    $stmt = db()->prepare(
        'SELECT therapist_id, day_of_week, start_time, end_time
         FROM therapist_availability
         WHERE is_active = 1
         ORDER BY therapist_id ASC, day_of_week ASC, start_time ASC'
    );
    $stmt->execute();

    $map = [];

    foreach ($stmt->fetchAll() as $row) {
        $therapistId = (int) ($row['therapist_id'] ?? 0);

        if (!isset($map[$therapistId])) {
            $map[$therapistId] = [];
        }

        $map[$therapistId][] = [
            'day' => (string) ($row['day_of_week'] ?? ''),
            'day_label' => client_translate_day((string) ($row['day_of_week'] ?? '')),
            'start' => substr((string) ($row['start_time'] ?? ''), 0, 5),
            'end' => substr((string) ($row['end_time'] ?? ''), 0, 5),
        ];
    }

    return $map;
}
