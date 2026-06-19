<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

try {
    // Get unread count
    $stmtCount = $conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmtCount->bind_param("i", $user_id);
    $stmtCount->execute();
    $unreadResult = $stmtCount->get_result()->fetch_assoc();
    $unread_count = (int)($unreadResult['unread_count'] ?? 0);
    $stmtCount->close();

    // Get new notifications since last_id
    // Order by created_at DESC strictly
    $stmt = $conn->prepare("
        SELECT notification_id, title, body, type, priority, is_read, created_at
        FROM notifications
        WHERE user_id = ? AND notification_id > ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("ii", $user_id, $last_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $new_notifications = [];
    while ($row = $res->fetch_assoc()) {
        $new_notifications[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'unread_count' => $unread_count,
        'new_notifications' => $new_notifications
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
