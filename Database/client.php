<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
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
 * @return array{
 *   id:int,
 *   name:string,
 *   image:string,
 *   special:string,
 *   degree:string,
 *   experience:string,
 *   email:string
 * }|null
 */
function client_get_therapist_by_id(int $therapistId): ?array
{
    if ($therapistId <= 0) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT
            u.user_id,
            u.name,
            u.email,
            u.avatar,
            t.specialization,
            t.certification,
            t.bio,
            ta.day_of_week,
            ta.start_time,
            ta.end_time
         FROM users u
         LEFT JOIN therapists t ON t.therapist_id = u.user_id
         LEFT JOIN therapist_availability ta ON ta.therapist_id = u.user_id
         WHERE u.user_id = :therapist_id'
    );

    $stmt->execute([
        'therapist_id' => $therapistId,
    ]);

    $rows = $stmt->fetchAll();

    if (!$rows) {
        return null;
    }

    $work = [];
    foreach ($rows as $r) {
        if (!empty($r['day_of_week'])) {
          $daysMap = [
    'SUNDAY' => 'الأحد',
    'MONDAY' => 'الاثنين',
    'TUESDAY' => 'الثلاثاء',
    'WEDNESDAY' => 'الأربعاء',
    'THURSDAY' => 'الخميس',
    'FRIDAY' => 'الجمعة',
    'SATURDAY' => 'السبت',
];

$day = $daysMap[$r['day_of_week']] ?? $r['day_of_week'];


$start = substr($r['start_time'], 0, 5);
$end = substr($r['end_time'], 0, 5);

$work[] = "$day $start - $end";
        }
    }

    $row = $rows[0];
    $row['work'] = !empty($work) ? implode(' | ', $work) : 'غير متاح';

    return client_map_therapist_row_to_booking_doctor($row);
}

/**
 * Backward-compatible alias.
 *
 * @return array{
 *   id:int,
 *   name:string,
 *   image:string,
 *   special:string,
 *   degree:string,
 *   experience:string,
 *   email:string
 * }|null
 */
function client_get_therapist_booking_profile_by_id(int $therapistId): ?array
{
    return client_get_therapist_by_id($therapistId);
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
 * @param array<string,mixed> $row
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
function client_map_therapist_row_to_booking_doctor(array $row): array
{
    return [
        'id' => (int) ($row['user_id'] ?? 0),
        'name' => trim((string) ($row['name'] ?? '')),
        'image' => client_normalize_therapist_avatar((string) ($row['avatar'] ?? '')),
        'special' => trim((string) ($row['specialization'] ?? '')),
        'degree' => trim((string) ($row['certification'] ?? '')),
        'experience' => trim((string) ($row['bio'] ?? '')),
        'work' => $row['work'] ?? 'غير متاح',
        'consultPrice' => '25 دينار',
        'therapyPrice' => '15 دينار',
           
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
        return '../images/default-doctor.png';
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


/**
 * Chat: returns all conversation peers for current user (derived from messages table).
 *
 * @return list<array{
 *   other_user_id:int,
 *   other_user_name:string,
 *   other_user_avatar:string|null,
 *   last_message:string,
 *   last_message_time:string,
 *   unread_count:int
 * }>
 */
function get_user_conversations(int $currentUserId): array
{
    if ($currentUserId <= 0) {
        return [];
    }

    $stmt = db()->prepare(
        'SELECT
            conv.other_user_id,
            u.name AS other_user_name,
            u.avatar AS other_user_avatar,
            m.content AS last_message_content,
            m.type AS last_message_type,
            m.file_path AS last_message_file_path,
            m.sent_at AS last_message_time,
            COALESCE(unread.unread_count, 0) AS unread_count
         FROM (
            SELECT CASE WHEN client_id = :u1 THEN therapist_id ELSE client_id END AS other_user_id
            FROM cases
            WHERE client_id = :u2 OR therapist_id = :u3
            UNION
            SELECT CASE WHEN sender_id = :u4 THEN receiver_id ELSE sender_id END AS other_user_id
            FROM messages
            WHERE sender_id = :u5 OR receiver_id = :u6
         ) AS peers
         INNER JOIN users u ON u.user_id = peers.other_user_id
         LEFT JOIN (
            SELECT CASE WHEN sender_id = :u7 THEN receiver_id ELSE sender_id END AS other_user_id,
                   MAX(message_id) AS last_message_id
            FROM messages
            WHERE sender_id = :u8 OR receiver_id = :u9
            GROUP BY CASE WHEN sender_id = :u10 THEN receiver_id ELSE sender_id END
         ) AS conv ON conv.other_user_id = peers.other_user_id
         LEFT JOIN messages m ON m.message_id = conv.last_message_id
         LEFT JOIN (
            SELECT sender_id AS other_user_id, COUNT(*) AS unread_count
            FROM messages
            WHERE receiver_id = :u11 AND is_read = 0
            GROUP BY sender_id
         ) AS unread ON unread.other_user_id = peers.other_user_id
         ORDER BY m.sent_at DESC, u.name ASC'
    );
    $stmt->execute([
        'u1' => $currentUserId,
        'u2' => $currentUserId,
        'u3' => $currentUserId,
        'u4' => $currentUserId,
        'u5' => $currentUserId,
        'u6' => $currentUserId,
        'u7' => $currentUserId,
        'u8' => $currentUserId,
        'u9' => $currentUserId,
        'u10' => $currentUserId,
        'u11' => $currentUserId,
    ]);

    /** @var list<array<string,mixed>> $rows */
    $rows = $stmt->fetchAll();
    $items = [];

    foreach ($rows as $row) {
        $type = strtoupper(trim((string) ($row['last_message_type'] ?? 'TEXT')));
        $content = trim((string) ($row['last_message_content'] ?? ''));
        $filePath = trim((string) ($row['last_message_file_path'] ?? ''));

        if ($type === 'IMAGE') {
            $lastMessage = '📷 صورة';
        } elseif ($type === 'FILE') {
            $lastMessage = '📎 ملف';
        } elseif ($type === 'VOICE') {
            $lastMessage = '🎤 تسجيل صوتي';
        } else {
            $lastMessage = $content;
        }

        $items[] = [
            'other_user_id' => (int) ($row['other_user_id'] ?? 0),
            'other_user_name' => trim((string) ($row['other_user_name'] ?? '')),
            'other_user_avatar' => ($row['other_user_avatar'] ?? null) !== null ? (string) $row['other_user_avatar'] : null,
            'last_message' => $lastMessage,
            'last_message_time' => (string) ($row['last_message_time'] ?? ''),
            'unread_count' => (int) ($row['unread_count'] ?? 0),
        ];
    }

    return $items;
}

/**
 * Chat: returns messages between current user and other user; also marks incoming unread messages as read.
 *
 * @return list<array{
 *   message_id:int,
 *   sender_id:int,
 *   receiver_id:int,
 *   content:string|null,
 *   type:string,
 *   file_path:string|null,
 *   is_read:int,
 *   sent_at:string
 * }>
 */
function get_messages_between_users(int $currentUserId, int $otherUserId): array
{
    if ($currentUserId <= 0 || $otherUserId <= 0 || $currentUserId === $otherUserId) {
        return [];
    }

    $connection = db();

    $markRead = $connection->prepare(
        'UPDATE messages
         SET is_read = 1
         WHERE receiver_id = :current_user_id
           AND sender_id = :other_user_id
           AND is_read = 0'
    );
    $markRead->execute([
        'current_user_id' => $currentUserId,
        'other_user_id' => $otherUserId,
    ]);

    $stmt = $connection->prepare(
        'SELECT message_id, sender_id, receiver_id, content, type, file_path, is_read, sent_at
         FROM messages
         WHERE (sender_id = :u1 AND receiver_id = :o1)
            OR (sender_id = :o2 AND receiver_id = :u2)
         ORDER BY sent_at ASC, message_id ASC'
    );
    $stmt->execute([
        'u1' => $currentUserId,
        'o1' => $otherUserId,
        'o2' => $otherUserId,
        'u2' => $currentUserId,
    ]);

    /** @var list<array<string,mixed>> $rows */
    $rows = $stmt->fetchAll();
    $messages = [];
    foreach ($rows as $row) {
        $messages[] = [
            'message_id' => (int) ($row['message_id'] ?? 0),
            'sender_id' => (int) ($row['sender_id'] ?? 0),
            'receiver_id' => (int) ($row['receiver_id'] ?? 0),
            'content' => ($row['content'] ?? null) !== null ? (string) $row['content'] : null,
            'type' => strtoupper(trim((string) ($row['type'] ?? 'TEXT'))),
            'file_path' => ($row['file_path'] ?? null) !== null ? (string) $row['file_path'] : null,
            'is_read' => (int) ($row['is_read'] ?? 0),
            'sent_at' => (string) ($row['sent_at'] ?? ''),
        ];
    }

    return $messages;
}

/**
 * Chat: inserts new message.
 */
function send_message(int $senderId, int $receiverId, string $content, string $type, ?string $filePath = null): int
{
    if ($senderId <= 0 || $receiverId <= 0 || $senderId === $receiverId) {
        throw new InvalidArgumentException('Invalid sender/receiver.');
    }

    $type = strtoupper(trim($type));
    if (!in_array($type, ['TEXT', 'IMAGE', 'FILE'], true)) {
        throw new InvalidArgumentException('Invalid message type.');
    }

    $content = trim($content);
    $filePath = $filePath !== null ? trim($filePath) : null;

    if ($type === 'TEXT') {
        if ($content === '') {
            throw new InvalidArgumentException('Message content is required.');
        }
        $filePath = null;
    } else {
        if ($filePath === null || $filePath === '') {
            throw new InvalidArgumentException('File path is required for this message type.');
        }
        $content = '';
    }

    $stmt = db()->prepare(
        'INSERT INTO messages (sender_id, receiver_id, content, type, file_path, is_read)
         VALUES (:sender_id, :receiver_id, :content, :type, :file_path, 0)'
    );
    $stmt->execute([
        'sender_id' => $senderId,
        'receiver_id' => $receiverId,
        'content' => ($content === '' ? null : $content),
        'type' => $type,
        'file_path' => $filePath,
    ]);

    return (int) db()->lastInsertId();
}

/**
 * Chat: handles file upload to /storage and returns public path.
 *
 * @return string public path like /That-Copy/storage/filename.ext
 */
function handle_file_upload(): string
{
    start_secure_session();
    require_auth();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        throw new RuntimeException('Invalid request method.');
    }

    if (!isset($_FILES['file'])) {
        throw new RuntimeException('No file uploaded.');
    }

    $file = $_FILES['file'];
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? 'file');
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $safeExt = preg_match('/^[a-z0-9]{1,8}$/', $ext) ? $ext : 'bin';
    $unique = bin2hex(random_bytes(16));
    $filename = $unique . '.' . $safeExt;

    $projectRoot = dirname(__DIR__);
    $storageDir = $projectRoot . DIRECTORY_SEPARATOR . 'storage';
    if (!is_dir($storageDir)) {
        if (!mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
            throw new RuntimeException('Failed to create storage directory.');
        }
    }

    $dest = $storageDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Failed to save uploaded file.');
    }

    return '/That-Copy/storage/' . $filename;
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
