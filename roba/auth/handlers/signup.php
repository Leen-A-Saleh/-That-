<?php
session_start();
include "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

$fullName = strip_tags(trim($_POST['fullName'] ?? ''));
$email = strip_tags(trim($_POST['email'] ?? ''));
$phone = strip_tags(trim($_POST['phone'] ?? ''));
$username = strip_tags(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$treatmentType = strip_tags(trim($_POST['treatment_type'] ?? ''));
$sessionType = strip_tags(trim($_POST['session_type'] ?? ''));
$sessionTime = strip_tags(trim($_POST['session_time'] ?? ''));

$errors = [];

if (empty($fullName)) {
    $errors[] = "الاسم الكامل مطلوب";
}
if (empty($email)) {
    $errors[] = "البريد الإلكتروني مطلوب";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "البريد الإلكتروني غير صالح";
}
if (empty($phone)) {
    $errors[] = "رقم الهاتف مطلوب";
}
if (empty($username)) {
    $errors[] = "اسم المستخدم مطلوب";
}
if (empty($password)) {
    $errors[] = "كلمة المرور مطلوبة";
} elseif (strlen($password) < 8) {
    $errors[] = "كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل";
}
if ($password !== $confirmPassword) {
    $errors[] = "كلمتا المرور غير متطابقتين";
}

$oldData = [
    'fullName' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'username' => $username,
    'treatment_type' => $treatmentType,
    'session_type' => $sessionType,
    'session_time' => $sessionTime,
];

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $oldData;
    header("Location: ../sendpage/index.php");
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['errors'] = ["البريد الإلكتروني مسجل مسبقاً"];
    $_SESSION['old'] = $oldData;
    header("Location: ../sendpage/index.php");
    exit;
}
$stmt->close();

$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['errors'] = ["اسم المستخدم مستخدم مسبقاً"];
    $_SESSION['old'] = $oldData;
    header("Location: ../sendpage/index.php");
    exit;
}
$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = 'CLIENT';

$stmt = $conn->prepare("INSERT INTO users (name, email, phone, username, password, role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $fullName, $email, $phone, $username, $hashedPassword, $role);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['user_name'] = $fullName;
    header("Location: ../../homepage/index.php");
    exit;
} else {
    $_SESSION['errors'] = ["حدث خطأ أثناء التسجيل، حاول مرة أخرى"];
    header("Location: ../sendpage/index.php");
    exit;
}

$stmt->close();
$conn->close();
?>
