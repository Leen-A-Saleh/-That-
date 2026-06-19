<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/chat-storage.php';
require_once __DIR__ . '/../../Database/avatar-storage.php';

start_secure_session();
require_role(['CLIENT']);

header('Content-Type: application/json; charset=utf-8');

try {
    $clientId = chat_current_client_id();
} catch (Throwable) {
    http_response_code(401);
    echo json_encode(['error' => 'غير مصرح.']);
    exit;
}

$action = trim((string) ($_POST['action'] ?? $_GET['action'] ?? ''));

if ($action === 'badge_state') {
    echo json_encode([
        'success' => true,
        'unread_conversations' => client_unread_chat_conversations_count($clientId),
    ]);
    exit;
}

switch ($action) {

    case 'get_conversations':
        echo json_encode(chat_get_conversations($clientId));
        break;

    case 'search':
        $query = trim((string) ($_POST['query'] ?? ''));
        echo json_encode(chat_search($clientId, $query));
        break;

    case 'get_messages':
        $therapistId = (int) ($_POST['therapistId'] ?? 0);
        if ($therapistId <= 0 || !chat_is_valid_therapist($therapistId)) {
            http_response_code(400);
            echo json_encode(['error' => 'معرّف المعالج غير صحيح.']);
            break;
        }
        echo json_encode(chat_get_messages($clientId, $therapistId));
        break;

    case 'send_message':
        $therapistId = (int) ($_POST['therapistId'] ?? 0);
        $content = trim((string) ($_POST['content'] ?? ''));

        if ($therapistId <= 0 || !chat_is_valid_therapist($therapistId)) {
            http_response_code(400);
            echo json_encode(['error' => 'معرّف المعالج غير صحيح.']);
            break;
        }
        if ($content === '') {
            http_response_code(400);
            echo json_encode(['error' => 'لا يمكن إرسال رسالة فارغة.']);
            break;
        }
        if (mb_strlen($content) > 5000) {
            http_response_code(400);
            echo json_encode(['error' => 'الرسالة طويلة جداً.']);
            break;
        }

        try {
            $messageId = chat_insert_message($clientId, $therapistId, $content, 'TEXT');
            echo json_encode(['success' => true, 'message_id' => $messageId]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'فشل إرسال الرسالة، يرجى المحاولة مجدداً.']);
        }
        break;

    case 'send_file':
        $therapistId = (int) ($_POST['therapistId'] ?? 0);
        if ($therapistId <= 0 || !chat_is_valid_therapist($therapistId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'معرّف المعالج غير صحيح.']);
            break;
        }

        $upload = chat_store_message_upload($clientId, $_FILES['file'] ?? []);
        if (!$upload['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $upload['message'],
            ]);
            break;
        }

        try {
            $messageId = chat_insert_message(
                $clientId,
                $therapistId,
                (string) ($upload['label'] ?? ''),
                (string) $upload['type'],
                (string) $upload['stored_path']
            );
            $storedPath = (string) $upload['stored_path'];
            echo json_encode([
                'success' => true,
                'message_id' => $messageId,
                'type' => $upload['type'],
                'file_path' => chat_public_file_url($storedPath),
                'file_name' => $upload['label'] ?? '',
                'sent_at' => chat_format_time(date('Y-m-d H:i:s')),
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'فشل إرسال الملف، يرجى المحاولة مجدداً.']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'طلب غير معروف.']);
        break;
}
exit;

// ─── Conversations

/**
 * @return list<array{therapist_id:int, therapist_name:string, therapist_avatar:string|null, last_message:string, last_message_time:string, unread_count:int}>
 */
function chat_get_conversations(int $clientId): array
{
    $stmt = db()->prepare(
        'SELECT
            CASE WHEN m.sender_id = :client_id_1 THEN m.receiver_id ELSE m.sender_id END AS therapist_id,
            u.name AS therapist_name,
            u.avatar AS therapist_avatar,
            m.content AS last_message,
            m.type AS last_type,
            m.file_path AS last_file_path,
            m.sent_at AS last_message_time,
            COALESCE(unread.unread_count, 0) AS unread_count
         FROM messages m
         INNER JOIN (
            SELECT
                CASE WHEN sender_id = :client_id_2 THEN receiver_id ELSE sender_id END AS other_id,
                MAX(message_id) AS last_message_id
            FROM messages
            WHERE sender_id = :client_id_3 OR receiver_id = :client_id_4
            GROUP BY CASE WHEN sender_id = :client_id_5 THEN receiver_id ELSE sender_id END
         ) last_messages ON last_messages.last_message_id = m.message_id
         INNER JOIN users u ON u.user_id = last_messages.other_id AND u.role = :role
         LEFT JOIN (
            SELECT sender_id, COUNT(*) AS unread_count
            FROM messages
            WHERE receiver_id = :client_id_6 AND is_read = 0
            GROUP BY sender_id
         ) unread ON unread.sender_id = last_messages.other_id
         ORDER BY m.sent_at DESC, m.message_id DESC'
    );

    $stmt->execute([
        'client_id_1' => $clientId,
        'client_id_2' => $clientId,
        'client_id_3' => $clientId,
        'client_id_4' => $clientId,
        'client_id_5' => $clientId,
        'client_id_6' => $clientId,
        'role' => 'THERAPIST',
    ]);

    $raw = $stmt->fetchAll();

    $result = [];
    foreach ($raw as $row) {
        $lastMessage = trim((string) ($row['last_message'] ?? ''));
        $type = strtoupper((string) ($row['last_type'] ?? 'TEXT'));

        if ($type === 'IMAGE') {
            $lastMessage = 'صورة';
        } elseif ($type === 'FILE') {
            $lastMessage = 'ملف';
        } elseif ($type === 'VOICE') {
            $lastMessage = 'رسالة صوتية';
        }

        $result[] = [
            'therapist_id' => (int) $row['therapist_id'],
            'therapist_name' => (string) $row['therapist_name'],
            'therapist_avatar' => chat_therapist_avatar_url(
                ($row['therapist_avatar'] ?? null) !== null ? (string) $row['therapist_avatar'] : null
            ),
            'last_message' => $lastMessage,
            'last_message_time' => chat_format_time($row['last_message_time']),
            'unread_count' => (int) $row['unread_count'],
        ];
    }

    return $result;
}

// ─── Search

/**
 * @return array{
 *   conversations:list<array{therapist_id:int, therapist_name:string, therapist_avatar:string|null, last_message:string, last_message_time:string, unread_count:int}>,
 *   therapists:list<array{therapist_id:int, therapist_name:string, therapist_avatar:string|null}>
 * }
 */
function chat_search(int $clientId, string $query): array
{
    $conversations = chat_get_conversations($clientId);

    if ($query === '') {
        return [
            'conversations' => $conversations,
            'therapists' => [],
        ];
    }

    $needle = mb_strtolower($query, 'UTF-8');
    $filteredConversations = [];

    foreach ($conversations as $conv) {
        $name = mb_strtolower((string) ($conv['therapist_name'] ?? ''), 'UTF-8');
        if (mb_strpos($name, $needle, 0, 'UTF-8') !== false) {
            $filteredConversations[] = $conv;
        }
    }

    $existingIds = [];
    foreach ($conversations as $conv) {
        $existingIds[(int) $conv['therapist_id']] = true;
    }

    $therapists = [];
    foreach (chat_find_therapists_by_query($query) as $row) {
        $therapistId = (int) $row['therapist_id'];
        if (isset($existingIds[$therapistId])) {
            continue;
        }

        $therapists[] = [
            'therapist_id' => $therapistId,
            'therapist_name' => (string) $row['therapist_name'],
            'therapist_avatar' => chat_therapist_avatar_url(
                ($row['therapist_avatar'] ?? null) !== null ? (string) $row['therapist_avatar'] : null
            ),
        ];
    }

    return [
        'conversations' => $filteredConversations,
        'therapists' => $therapists,
    ];
}

/**
 * @return list<array{therapist_id:int, therapist_name:string, therapist_avatar:string|null}>
 */
function chat_find_therapists_by_query(string $query): array
{
    $query = trim($query);
    if ($query === '') {
        return [];
    }

    $like = '%' . chat_escape_like($query) . '%';

    $stmt = db()->prepare(
        'SELECT user_id AS therapist_id, name AS therapist_name, avatar AS therapist_avatar
         FROM users
         WHERE role = :role AND is_active = 1 AND name LIKE :q ESCAPE \'\\\\\'
         ORDER BY name ASC
         LIMIT 30'
    );
    $stmt->execute([
        'role' => 'THERAPIST',
        'q' => $like,
    ]);

    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[] = [
            'therapist_id' => (int) ($row['therapist_id'] ?? 0),
            'therapist_name' => (string) ($row['therapist_name'] ?? ''),
            'therapist_avatar' => chat_therapist_avatar_url(
                ($row['therapist_avatar'] ?? null) !== null ? (string) $row['therapist_avatar'] : null
            ),
        ];
    }

    return $result;
}

// ─── Helpers

function chat_escape_like(string $value): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
}

function chat_therapist_avatar_url(?string $avatar): string
{
    $avatar = trim((string) ($avatar ?? ''));
    if ($avatar !== '') {
        if (preg_match('#^https?://#i', $avatar)) {
            return $avatar;
        }

        if (str_starts_with($avatar, '../')) {
            $config = require __DIR__ . '/../../Database/config.php';
            $base = rtrim((string) ($config['app_url'] ?? ''), '/');
            $relative = ltrim(str_replace('../', '', $avatar), '/');

            return $base . '/Client/' . $relative;
        }

        $public = avatar_public_url($avatar);
        if ($public !== '') {
            return $public;
        }
    }

    $config = require __DIR__ . '/../../Database/config.php';
    $base = rtrim((string) ($config['app_url'] ?? ''), '/');

    return $base . '/Client/images/default-doctor.png';
}

// ─── Messages

/**
 * @return list<array{message_id:int, content:string|null, type:string, file_path:string|null, isMe:bool, time:string, is_read:int}>
 */
function chat_get_messages(int $clientId, int $therapistId): array
{
    $raw = chat_fetch_messages_between_users($clientId, $therapistId);

    $result = [];
    foreach ($raw as $row) {
        $storedPath = $row['file_path'];
        $result[] = [
            'message_id' => $row['message_id'],
            'content' => $row['content'],
            'type' => $row['type'],
            'file_path' => $storedPath,
            'file_url' => chat_public_file_url($storedPath),
            'isMe' => ($row['sender_id'] === $clientId),
            'time' => chat_format_time($row['sent_at']),
            'is_read' => $row['is_read'],
        ];
    }

    return $result;
}

function chat_is_valid_therapist(int $therapistId): bool
{
    if ($therapistId <= 0) {
        return false;
    }

    $stmt = db()->prepare(
        'SELECT 1
         FROM users
         WHERE user_id = :id AND role = :role AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute(['id' => $therapistId, 'role' => 'THERAPIST']);

    return (bool) $stmt->fetchColumn();
}

function chat_format_time(string $datetime): string
{
    if ($datetime === '') {
        return '';
    }

    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }

    return date('H:i', $ts);
}

function chat_current_client_id(): int
{
    $user = current_user();
    if (!is_array($user) || !isset($user['user_id'])) {
        throw new RuntimeException('Client is not authenticated.');
    }

    return (int) $user['user_id'];
}

/**
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
function chat_fetch_messages_between_users(int $clientId, int $therapistId): array
{
    if ($clientId <= 0 || $therapistId <= 0 || $clientId === $therapistId) {
        return [];
    }

    $connection = db();

    $markRead = $connection->prepare(
        'UPDATE messages
         SET is_read = 1
         WHERE receiver_id = :client_id
           AND sender_id = :therapist_id
           AND is_read = 0'
    );
    $markRead->execute([
        'client_id' => $clientId,
        'therapist_id' => $therapistId,
    ]);

    $stmt = $connection->prepare(
        'SELECT message_id, sender_id, receiver_id, content, type, file_path, is_read, sent_at
         FROM messages
         WHERE (sender_id = :client_id_1 AND receiver_id = :therapist_id_1)
            OR (sender_id = :therapist_id_2 AND receiver_id = :client_id_2)
         ORDER BY sent_at ASC, message_id ASC'
    );
    $stmt->execute([
        'client_id_1' => $clientId,
        'therapist_id_1' => $therapistId,
        'therapist_id_2' => $therapistId,
        'client_id_2' => $clientId,
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

function chat_insert_message(int $senderId, int $receiverId, string $content, string $type, ?string $filePath = null): int
{
    if ($senderId <= 0 || $receiverId <= 0 || $senderId === $receiverId) {
        throw new InvalidArgumentException('Invalid sender/receiver.');
    }

    $type = strtoupper(trim($type));
    if (!in_array($type, ['TEXT', 'IMAGE', 'FILE', 'VOICE'], true)) {
        throw new InvalidArgumentException('Invalid message type.');
    }

    $content = trim($content);
    $filePath = $filePath !== null ? trim(str_replace('\\', '/', $filePath)) : null;

    if ($type === 'TEXT') {
        if ($content === '') {
            throw new InvalidArgumentException('Message content is required.');
        }
        $filePath = null;
    } else {
        if ($filePath === null || $filePath === '') {
            throw new InvalidArgumentException('File path is required for this message type.');
        }
        if ($content === '') {
            $content = basename($filePath);
        }
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
