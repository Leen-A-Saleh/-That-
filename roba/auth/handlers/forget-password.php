<?php
session_start();
include "../../connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

$email = strip_tags(trim($_POST['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../forget-password/index.php?sent=1");
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $token = bin2hex(random_bytes(32));
    $hashed_token = hash('sha256', $token);

    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashed_token);
    $stmt->execute();

    $reset_link = APP_URL . "/auth/reset-password/index.php?token=$token&email=" . urlencode($email);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Port       = MAIL_PORT;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email);
        $mail->CharSet = 'UTF-8';

        $mail->isHTML(true);
        $mail->Subject = 'إعادة تعيين كلمة المرور';
        $mail->Body    = "
            <div dir='rtl' style='font-family: Cairo, sans-serif;'>
                <h2>إعادة تعيين كلمة المرور</h2>
                <p>لقد طلبت إعادة تعيين كلمة المرور. اضغط على الرابط أدناه:</p>
                <a href='$reset_link' style='display:inline-block; padding:10px 20px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:5px;'>إعادة تعيين كلمة المرور</a>
                <p style='margin-top:15px; color:#888;'>إذا لم تطلب ذلك، تجاهل هذا البريد.</p>
            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        header("Location: ../forget-password/index.php?error=" . urlencode("حدث خطأ ما, حاول مرة أخرى"));
        exit;
    }
}

header("Location: ../forget-password/index.php?sent=1");
exit;
?>
