<?php
session_start();
include "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

$token = trim($_POST['token'] ?? '');
$email = strip_tags(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($email)) {
    header("Location: ../forget-password/index.php?error=" . urlencode("رابط غير صالح"));
    exit;
}

if (empty($password) || strlen($password) < 8) {
    header("Location: ../reset-password/index.php?token=" . urlencode($token) . "&email=" . urlencode($email) . "&error=" . urlencode("كلمة المرور يجب أن تكون 8 أحرف على الأقل"));
    exit;
}

if ($password !== $confirm_password) {
    header("Location: ../reset-password/index.php?token=" . urlencode($token) . "&email=" . urlencode($email) . "&error=" . urlencode("كلمات المرور غير متطابقة"));
    exit;
}

$hashed_token = hash('sha256', $token);
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE email = ? AND token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$stmt->bind_param("ss", $email, $hashed_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../forget-password/index.php?error=" . urlencode("الرابط غير صالح أو منتهي الصلاحية"));
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

header("Location: ../login/index.php?reset=1");
exit;
?>
