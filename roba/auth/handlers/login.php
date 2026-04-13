<?php
session_start();
include "../../connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
} else {
    $email = strip_tags(trim($_POST['email'] ?? ''));
    $password = strip_tags(trim($_POST['password'] ?? ''));

    $errors = [];

    if (empty($email)) {
        $errors['email'] = "البريد الإلكتروني مطلوب";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "البريد الإلكتروني غير صالح";
    }

    if (empty($password)) {
        $errors['password'] = "كلمة المرور مطلوبة";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_email'] = $email;
        header("Location: ../login/index.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['errors'] = ['email' => 'البريد الإلكتروني غير مسجل'];
        $_SESSION['old_email'] = $email;
        header("Location: ../login/index.php");
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user['password'])) {
        $_SESSION['errors'] = ['password' => 'كلمة المرور غير صحيحة'];
        $_SESSION['old_email'] = $email;
        header("Location: ../login/index.php");
        exit;
    }

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    if ($user['role'] === 'THERAPIST') {
        header("Location: ../../therapist/therapist-dashboard/index.php");
        exit;
    } 
    header("Location: ../../homepage/index.php");
    exit;

}

?>