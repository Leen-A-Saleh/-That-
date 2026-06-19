<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$errors = process_reset_password_request($_POST);

if ($errors !== []) {
    $token = trim((string) ($_POST['token'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $_SESSION['errors'] = $errors;
    redirect('/That-Copy/Public/reset-password/index.php?token=' . urlencode($token) . '&email=' . urlencode($email));
}

redirect('/That-Copy/Public/login/index.php');
