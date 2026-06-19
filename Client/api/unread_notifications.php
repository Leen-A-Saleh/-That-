<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'CLIENT') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $unread_count = (int)($result['unread_count'] ?? 0);
    $stmt->close();

    echo json_encode(['success' => true, 'unread_count' => $unread_count]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
