<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/db.php';
require_once __DIR__ . '/../../Database/auth.php';

start_secure_session();

header('Content-Type: application/json; charset=UTF-8');

if (!is_authenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = (int)($_SESSION['auth']['user_id'] ?? 0);
$role = strtoupper((string)($_SESSION['auth']['role'] ?? 'CLIENT'));

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

try {
    // 1. Unread Notifications Count
    $notificationFilterSql = '';
    if ($role === 'CLIENT') {
        require_once __DIR__ . '/../../Database/profile-database.php';
        $notificationFilterSql = client_notification_preferences_sql_filter();
    }
    
    $stmtNotif = db()->prepare(
        'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0' . $notificationFilterSql
    );
    $stmtNotif->execute(['user_id' => $userId]);
    $unreadNotif = (int) $stmtNotif->fetchColumn();

    // 2. Unread Messages Count
    $unreadMsg = 0;
    if ($role === 'CLIENT') {
        require_once __DIR__ . '/../../Database/profile-database.php';
        $prefs = client_get_notification_preferences_for_current_user();
        if (!$prefs['message_notifications']) {
            goto skip_messages;
        }
    }
    
    // Using COUNT(DISTINCT sender_id) as per existing logic in client.php
    $stmtMsg = db()->prepare(
        'SELECT COUNT(DISTINCT sender_id) FROM messages WHERE receiver_id = :user_id AND is_read = 0'
    );
    $stmtMsg->execute(['user_id' => $userId]);
    $unreadMsg = (int) $stmtMsg->fetchColumn();
    
    skip_messages:

    echo json_encode([
        'success' => true,
        'unread_notifications' => $unreadNotif,
        'unread_messages' => $unreadMsg
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
