<?php
declare(strict_types=1);

require_once __DIR__ . '/../Database/helpers.php';
require_once __DIR__ . '/../Database/auth.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$errors = process_full_signup($_POST);

if ($errors !== []) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = [
        'fullName' => trim((string) ($_POST['fullName'] ?? '')),
        'email'    => trim((string) ($_POST['email'] ?? '')),
        'phone'    => trim((string) ($_POST['phone'] ?? '')),
        'username' => trim((string) ($_POST['username'] ?? '')),
    ];
    redirect('/That-Copy/Public/sendpage/index.php');
}

// Success
redirect('/That-Copy/Public/login/index.php');
