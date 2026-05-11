<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/auth.php';
require_once __DIR__ . '/../../Database/client.php';

start_secure_session();
require_role(['CLIENT']);

header('Content-Type: application/json; charset=utf-8');

try {
    $clientId = client_current_user_id();
} catch (Throwable) {
    http_response_code(401);
    echo json_encode(['error' => 'غير مصرح.']);
    exit;
}

$action = trim((string) ($_POST['action'] ?? ''));

switch ($action) {

    case 'get_conversations':
        echo json_encode(chat_get_conversations($clientId));
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
            $messageId = send_message($clientId, $therapistId, $content, 'TEXT');
            echo json_encode(['success' => true, 'message_id' => $messageId]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'فشل إرسال الرسالة، يرجى المحاولة مجدداً.']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'طلب غير معروف.']);
        break;
}
exit;

/**
 *
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
        }

        $result[] = [
            'therapist_id' => (int) $row['therapist_id'],
            'therapist_name' => (string) $row['therapist_name'],
            'therapist_avatar' => $row['therapist_avatar'],
            'last_message' => $lastMessage,
            'last_message_time' => chat_format_time($row['last_message_time']),
            'unread_count' => (int) $row['unread_count'],
        ];
    }

    return $result;
}

/**
 *
 * @return list<array{message_id:int, content:string|null, type:string, file_path:string|null, isMe:bool, time:string, is_read:int}>
 */
function chat_get_messages(int $clientId, int $therapistId): array
{
    $raw = get_messages_between_users($clientId, $therapistId);

    $result = [];
    foreach ($raw as $row) {
        $result[] = [
            'message_id' => $row['message_id'],
            'content' => $row['content'],
            'type' => $row['type'],
            'file_path' => $row['file_path'],
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
